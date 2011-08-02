<?php
/*
//----------------------------------------------------------------------------
//---------------------- SEO-G by Asymmetrics --------------------------------
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO-G Abstract Zones class for Admin
// This is a Bridge for SEO-G
// Processes Abstract Zones tables generates friendly seo urls.
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class seo_abstract extends seo_zones {

    // Compatibility constructor
    function seo_abstract() {
      $this->error_array = array();
      $this->m_ssID = isset($_GET['ssID'])?(int)$_GET['ssID']:'';
      $this->m_mcpage = isset($_GET['mcpage'])?(int)$_GET['mcpage']:'';
      $this->m_mppage = isset($_GET['mppage'])?(int)$_GET['mppage']:'';
      parent::seo_zones();
    }

    function generate_name($abstract_zone_id, $separator=SEO_DEFAULT_WORDS_SEPARATOR) {
      extract(tep_load('database'));

      $string = '';
      $name_query = $db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");

      if( !$db->num_rows($name_query) )
        return $string;

      $names_array = $db->fetch_array($name_query);
      $string =  $this->create_safe_string($names_array['abstract_zone_name'], $separator);
      return $this->adapt_lexico($string, $separator);
    }

    function process_action() {
      switch( $this->m_action ) {
        case 'validate':
          return $this->validate();
        case 'validate_confirm':
          return $this->validate_confirm();
        case 'update_multizone':
          $result = parent::validate_array_selection('pc_id');
          return $this->update_multizone();
        case 'insert_multi_entries':
          $result = parent::validate_array_selection('pc_id', 'multi_entries'); 
          return $this->insert_multi_entries();
        case 'deleteconfirm_multizone':
          $result = parent::validate_array_selection('pc_id'); 
          return $this->deleteconfirm_multizone();
        case 'delete_multizone':
          $result = parent::validate_array_selection('pc_id'); 
          break;
        default:
          return parent::process_action(); 
          break;
      }
    }

    function validate() {
      extract(tep_load('database'));
      $this->error_array = array();
      // First pass check for missing abstract from seo table
      $check_query = $db->query("select az.abstract_zone_id, az.abstract_zone_name as name, '0' as missing_id from " . TABLE_ABSTRACT_ZONES . " az left join " . TABLE_SEO_TO_ABSTRACT . " s2a on (s2a.abstract_zone_id = az.abstract_zone_id) where s2a.abstract_zone_id is null order by az.abstract_zone_id desc limit " . SEO_PAGE_SPLIT);
      while( $check_array = $db->fetch_array($check_query) ) {
        $this->error_array[] = $check_array;
      }
      // Second pass check for redundant entries in the seo table
      $check_query = $db->query("select s2a.abstract_zone_id, s2a.seo_name as name, '-1' as missing_id from " . TABLE_SEO_TO_ABSTRACT . " s2a left join " . TABLE_ABSTRACT_ZONES . " az on (s2a.abstract_zone_id = az.abstract_zone_id) where az.abstract_zone_id is null order by s2a.abstract_zone_id desc limit " . SEO_PAGE_SPLIT);
      while( $check_array = $db->fetch_array($check_query) ) {
        $this->error_array[] = $check_array;
      }
      return $this->error_array;
    }

    function validate_confirm() {
      extract(tep_load('defs', 'database'));

      foreach($_POST['pc_id'] as $abstract_zone_id => $val) {
        if( $_POST['missing'][$abstract_zone_id] == -1 ) {
          $db->query("delete from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
        } elseif( $_POST['missing'][$abstract_zone_id] == 0 ) {
          $this->insert_update($abstract_zone_id);
        }
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=validate'));
    }

    function update_multizone() {
      extract(tep_load('defs', 'database', 'message_stack'));

      foreach ($_POST['pc_id'] as $abstract_id => $val) {
        $seo_name = $this->create_safe_string($_POST['name'][$abstract_id]);

        if( SEO_PROXIMITY_CLEANUP == 'true' ) {
          $check_query = $db->query("select seo_name from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$abstract_id . "'");
          if( $check_array = $db->fetch_array($check_query) ) {
            $check_name = $check_array['seo_name'];
            $db->query("delete from " . TABLE_SEO_URL . " where seo_url_get like '%" . $db->filter($check_name) . "%'");
          }
        }
        $seo_name = $this->insert_update($abstract_id);
        if( empty($seo_name) ) {
          $error = true;
          $msg->add_session(sprintf(ERROR_INVALID_NAME, $abstract_id));
        }
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
    }


    function insert_multi_entries() {
      extract(tep_load('defs', 'database', 'message_stack'));

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');
      switch( $multi_form ) {
        case 'multi_entries':
          $error = false;
          $tmp_array = array();
          foreach ($_POST['pc_id'] as $abstract_id=>$val) {
            $check_query = $db->query("select abstract_zone_id from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$abstract_id . "'");
            if( $db->num_rows($check_query) ) continue;
            $this->insert_update($abstract_id);
            $seo_name = $this->insert_update($abstract_id);
            if( empty($seo_name) ) {
              $error = true;
              $msg->add_session(sprintf(ERROR_INVALID_NAME, $abstract_id));
            }
          }
          if( !$error ) {
            $msg->add_session(SUCCESS_SELECTED_ADDED, 'success');
          }
          tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
          break;
        default:
          break;
      }
    }

    function insert_update($abstract_id, $seo_name='', $op='') {
      extract(tep_load('database'));

      if( empty($op) ) {
        $op = 'update';
      } elseif($op == 'check_insert' && !empty($seo_name) ) {
        $check_query = $db->query("select count(*) as total from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$abstract_id . "'");
        $check_array = $db->fetch_array($check_query);
        if( $check_array['total'] ) {
          $op = 'update';
        } else {
          $op = 'insert';
        }
      }

      if( empty($seo_name) ) {
        $op = 'insert';
        $seo_name = $this->generate_name($abstract_id);
      }
      if( empty($abstract_id) || empty($seo_name) ) return $seo_name;

      if( $op == 'insert') {
        $sql_data_array = array(
          'abstract_zone_id' => (int)$abstract_id,
          'seo_name' => $db->prepare_input($seo_name),
        );
        $db->perform(TABLE_SEO_TO_ABSTRACT, $sql_data_array);
      } elseif( $op == 'check_insert' && !empty($seo_name) ) {
        $check_query = $db->query("select count(*) as total from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$abstract_id . "'");

      } else {
        $sql_data_array = array(
          'seo_name' => $db->prepare_input($seo_name),
        );
        $db->perform(TABLE_SEO_TO_ABSTRACT, $sql_data_array, 'update', "abstract_zone_id = '" . (int)$abstract_id . "'");
      }
      return $seo_name;
    }


    function deleteconfirm_multizone() {
      extract(tep_load('defs', 'database'));

      for($i=0, $j=count($_POST['pc_id']); $i<$j; $i++ ) {
        $abstract_zone_id = $_POST['pc_id'][$i];

        if( SEO_PROXIMITY_CLEANUP == 'true' ) {
          $check_query = $db->query("select seo_name from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
          if( $check_array = $db->fetch_array($check_query) ) {
            $check_name = $check_array['seo_name'];
            $db->query("delete from " . TABLE_SEO_URL . " where seo_url_get like '%" . $db->filter($check_name) . "%'");
          }
        }
        $db->query("delete from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
    }

    function display_html() {
      switch( $this->m_action ) {
        case 'validate':
          $result = $this->display_validation();
          break;
        case 'list':
          $result = $this->display_list();
          break;
        case 'multi_entries':
          $result = $this->display_multi_entries();
          break;
        case 'delete_multizone':
          $result = $this->display_delete_multizone();
          break;
        default:
          $result = $this->display_default();
          $result .= $this->display_bottom();
          break;
      }
      return $result;
    }

    function display_validation() {
      extract(tep_load('defs'));

      $html_string = '';
      $html_string .= 
      '      <tr>' . "\n" . 
      '        <td><hr /></td>' . "\n" . 
      '      </tr>' . "\n";
      if( count($this->error_array) ) {
        $html_string .= 
        '      <tr>' . "\n" . 
        '        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n" . 
        '          <tr>' . "\n" . 
        '            <td class="dataTableRowHighBorder" width="16">&nbsp;</td>' . "\n" . 
        '            <td><b>&nbsp;-&nbsp;Zone present in the abstract zones table but not present in the SEO-G table</b></td>' . "\n" . 
        '          </tr>' . "\n" . 
        '          <tr>' . "\n" . 
        '            <td colspan="2">' . tep_draw_separator('pixel_trans.gif', '100%', '1') . '</td>' . "\n" . 
        '          </tr>' . "\n" . 
        '          <tr>' . "\n" . 
        '            <td class="dataTableRowImpactBorder" width="16">&nbsp;</td>' . "\n" . 
        '            <td><b>&nbsp;-&nbsp;Zone present in the SEO-G table but it is not present in the Abstract Zones table</b></td>' . "\n" . 
        '          </tr>' . "\n" . 
        '        </table></td>' . "\n" . 
        '      </tr>' . "\n" .
        '      <tr>' . "\n" . 
        '        <td>' . tep_draw_separator('pixel_trans.gif', '100%', '10') . '</td>' . "\n" . 
        '      </tr>' . "\n";
        $html_string .= 
        '      <tr>' . "\n" . 
        '        <td valign="top">' . tep_draw_form('rl', $cDefs->script, 'action=validate_confirm&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage, 'post') . '<table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n" . 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <th class="calign"><a href="#pc_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
        '            <th>' . TABLE_HEADING_ID . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_NAME . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_COMMENT . '</th>' . "\n" . 
        '          </tr>' . "\n";
        for($i=0, $j=count($this->error_array); $i<$j; $i++ ) {
          $row_class = ($this->error_array[$i]['missing_id'])?'dataTableRowImpact':'dataTableRowHigh';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="calign">' . tep_draw_checkbox_field('pc_id[' . $this->error_array[$i]['abstract_zone_id'] . ']', 'on', false ) . tep_draw_hidden_field('missing[' . $this->error_array[$i]['abstract_zone_id'] . ']', $this->error_array[$i]['missing_id']) . '</td>' . "\n" . 
          '            <td>' . $this->error_array[$i]['abstract_zone_id'] . '</td>' . "\n" . 
          '            <td>' . $this->error_array[$i]['name'] . '</td>' . "\n" . 
          '            <td>' . (($this->error_array[$i]['missing_id'])?'Missing from Abstract Zones':'Missing from SEO-G') . '</td>' . "\n" . 
          '          </tr>'  . "\n";
        }
        $html_string .= 
        '          <tr>' . "\n" . 
        '            <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n" . 
        '              <tr>' . "\n" . 
        '                <td><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_fix_errors.gif', 'Fix Listed Errors') . '</td>' . "\n" . 
        '              </tr>' . "\n" . 
        '            </table></td>' . "\n" . 
        '          </tr>' . "\n" .
        '        </table></form></td>' . "\n" . 
        '      </tr>' . "\n";
      } else {
        $html_string .= 
        '      <tr>' . "\n" . 
        '        <td class="smallText">' . 'No Errors Found' . '</td>' . "\n" . 
        '      </tr>' . "\n" . 
        '      <tr>' . "\n" . 
        '        <td colspan="2">' . tep_draw_separator('pixel_trans.gif', '100%', '4') . '</td>' . "\n" . 
        '      </tr>' . "\n" . 
        '      <tr>' . "\n" . 
        '        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">' . "\n" . 
        '          <tr>' . "\n" . 
        '            <td><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a></td>' . "\n" . 
        '          </tr>' . "\n" . 
        '        </table></td>' . "\n" . 
        '      </tr>' . "\n";
      }
      return $html_string;
    }

// Default List
    function display_list() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $rows = 0;

      $zones_query_raw = "select abstract_zone_id, seo_name from " . TABLE_SEO_TO_ABSTRACT . " order by seo_name";
      $zones_split = new splitPageResults($zones_query_raw, SEO_PAGE_SPLIT, '', 'spage');

      $html_string .= 
      '        <div class="comboHeading">' . "\n" .
      '          <div class="dataTableRowAlt3 spacer floater"><a class="blockbox" href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=multi_entries') . '">' . TEXT_INFO_ASSIGN_COLLECTIONS . '</a></div>'. "\n" . 
      '          <div class="spacer">' . TEXT_INFO_ASSIGN_COLLECTIONS_HELP . '</div>' . "\n" . 
      '        </div>'. "\n";

      if( $zones_split->number_of_rows > 0 ) {
        $html_string .= 
        '      <div class="formArea">' . tep_draw_form('rl', $cDefs->script, 'action=delete_multizone&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage . '&spage=' . $this->m_spage, 'post') . "\n";

        //if(empty($this->saction)) {
        //  $html_string .= 
        //  '          <tr>' . "\n" . 
        //  '            <td colspan="3" class="formButtons"><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'class="dflt" onclick="this.form.action=' . '\'' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=update_multizone') . '\'' . '"') . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'class="dflt" onclick="this.form.action=' . '\'' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=delete_multizone') . '\'' . '"') . '</td>' . "\n" . 
        //  '          </tr>' . "\n";
        //}
        $html_string .= 
        '      <table class="tabledata">' . "\n" . 
        '        <tr class="dataTableHeadingRow">' . "\n" . 
        '          <th class="calign"><a href="#pc_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
        '          <th>' . TABLE_HEADING_ABSTRACT_ZONE . '</th>' . "\n" . 
        '          <th class="halfer">' . TABLE_HEADING_NAME . '</th>' . "\n" . 
        '        </tr>' . "\n";
        $zones_query = $db->query($zones_split->sql_query);
        $bCheck = false;
        while( $zones_array = $db->fetch_array($zones_query)) {
          $extra_query = $db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$zones_array['abstract_zone_id'] . "'");
          if( $db->num_rows($extra_query) ) {
            $extra_array = $db->fetch_array($extra_query);
            $final_name = '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $zones_array['abstract_zone_id'] . '&action=edit_zone') . '">' . $extra_array['abstract_zone_name'] . '</a>';
          } else {
            $final_name = '<font color="FF0000">' . TEXT_INFO_NA . '&nbsp;[' . $zones_array['seo_name'] . ']' . '</font>';
          }
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '        <tr class="' . $row_class . '">' . "\n" . 
          '          <td class="calign">' . tep_draw_checkbox_field('pc_id[' . $zones_array['abstract_zone_id'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '          <td>' . $final_name . '</td>' . "\n";
          if( $final_name == TEXT_INFO_NA ) {
            $html_string .= 
            '          <td>' . TEXT_ERROR . '</td>' . "\n";
          } else {
            $html_string .= 
            '          <td><div class="rpad">' . tep_draw_input_field('name[' . $zones_array['abstract_zone_id'] . ']', $zones_array['seo_name']) . '</div></td>' . "\n";
          }
          $html_string .= 
          '        </tr>'  . "\n";
        }

        $html_string .= 
        '      </table>' . "\n";

        if(empty($this->m_saction)) {
          $html_string .= '<div class="formButtons"><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'class="dflt" onclick="this.form.action=' . '\'' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=update_multizone') . '\'' . '"') . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'class="dflt" onclick="this.form.action=' . '\'' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=delete_multizone') . '\'' . '"') . '</div>' . "\n";
        }

        $html_string .= 
        '      </form></div>' . "\n" . 
        '      <div class="listArea splitLine">' . "\n" . 
        '        <div class="floater">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
        '        <div class="floatend">' . $zones_split->display_links(tep_get_all_get_params('action', 'spage') . 'action=list') . '</div>' . "\n" . 
        '      </div>' . "\n";
      } else {
        $html_string .= 
        '      <div class="comboHeading">' . "\n" . 
        '        <div>' . TEXT_INFO_NO_ENTRIES . '</div>' . "\n" . 
        '      </div>' . "\n";
      }
      return $html_string;
    }

    function display_multi_entries() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $html_string .=
      '        <div class="comboHeading">' . TEXT_SELECT_MULTIABSTRACT . '</div>' . "\n" . 
      '        <div class="formArea">' . tep_draw_form('mc', $cDefs->script, tep_get_all_get_params('action') . 'action=insert_multi_entries', 'post') . "\n" . 
      '        <table class="tabledata">' . "\n" .
      '          <tr class="dataTableHeadingRow">' . "\n" . 
      '            <th class="calign"><a href="#pc_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
      '            <th>' . TABLE_HEADING_ABSTRACT_ZONE . '</th>' . "\n" . 
      '          </tr>' . "\n"; 
      $rows = 0;
      $zones_query_raw = "select abstract_zone_id, abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " order by abstract_zone_name";
      $zones_split = new splitPageResults($zones_query_raw, SEO_PAGE_SPLIT, '', 'mcpage');
      $zones_query = $db->query($zones_split->sql_query);
      $bCheck = false;
      while( $zones_array = $db->fetch_array($zones_query) ) {
        $check_query = $db->query("select count(*) as total from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$zones_array['abstract_zone_id'] . "'");
        $check_array = $db->fetch_array($check_query);
        $bCheck  = $check_array['total']?true:false;
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if($bCheck) {
          $row_class = 'dataTableRowGreen';
        }
        $html_string .=
        '          <tr class="' . $row_class . '">' . "\n" . 
        '            <td class="calign">' . ($bCheck?'Included':tep_draw_checkbox_field('pc_id[' . $zones_array['abstract_zone_id'] . ']')) . '</td>' . "\n" . 
        '            <td>' . $zones_array['abstract_zone_name'] . '</td>' . "\n" . 
        '          </tr>' . "\n";
      }
      $html_string .=
      '        </table>' . "\n" . 
      '        <div class="formButtons"><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'mcpage') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_draw_hidden_field('multi_form', 'multi_entries') . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '</div>' . "\n" . 
      '        </form></div>' . "\n" . 
      '        <div class="listArea splitLine">' . "\n" . 
      '          <div class="floater">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
      '          <div class="floatend">' . $zones_split->display_links($this->m_mcpage, tep_get_all_get_params('action', 'mcpage') . 'action=multi_entries') . '</div>' . "\n" . 
      '        </div>' . "\n";
      return $html_string;
    }

    function display_delete_multizone() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $zones_query = $db->query("select seo_types_name from " . TABLE_SEO_TYPES . " where seo_types_id = '" . (int)$this->m_zID . "'");
      $zones_array = $db->fetch_array($zones_query);
      $html_string .= 
      '          <div class="comboHeading">' . sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zones_array['seo_types_name']) . '</div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', $cDefs->script, tep_get_all_get_params('action') . 'action=deleteconfirm_multizone', 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_ABSTRACT_ZONE . '</td>' . "\n" . 
      '            </tr>' . "\n";

      $buttons = array(
        '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
      );

      $rows = 0;
      foreach ($_POST['pc_id'] as $key => $val) {
        $delete_query = $db->query("select m.abstract_zone_name as final_name from " . TABLE_SEO_TO_ABSTRACT . " s2m left join " . TABLE_ABSTRACT_ZONES . " m on (s2m.abstract_zone_id=m.abstract_zone_id) where s2m.abstract_zone_id = '" . (int)$key . "' order by m.abstract_zone_name");

        if( $db->num_rows($delete_query) ) {
          $delete_array = $db->fetch_array($delete_query);
        } else {
          $delete_array = array('final_name' => 'N/A - ' . $key);
        }

        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        $html_string .= 
        '          <tr class="' . $row_class . '">' . "\n" . 
        '            <td>' . tep_draw_hidden_field('pc_id[]', $key) . $delete_array['final_name'] . '</td>' . "\n" . 
        '          </tr>' . "\n";
      }

      $html_string .= 
      '          </table><div class="formButtons">' . implode('', $buttons) . '</div></form></div>' . "\n" . 
      '          <div class="listArea splitLine">' . "\n" . 
      '            <div class="floater">' . sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, 1, $rows, $rows) . '</div>' . "\n" . 
      '          </div>' . "\n";
      return $html_string;
    }
  }
?>