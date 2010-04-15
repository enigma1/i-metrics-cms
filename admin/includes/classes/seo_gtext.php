<?php
/*
//----------------------------------------------------------------------------
//-------------- SEO-G by Asymmetrics (Renegade Edition) ---------------------
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: SEO-G GText class
// This is a Bridge for SEO-G
// Processes text entries table generates generic text seo urls.
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  class seo_gtext extends seo_zones {
    var $error_array;

// class constructor
    function seo_gtext() {
      $this->m_ssID = isset($_GET['ssID'])?$_GET['ssID']:'';
      $this->m_mcpage = isset($_GET['mcpage'])?$_GET['mcpage']:'';
      $this->m_mppage = isset($_GET['mppage'])?$_GET['mppage']:'';
      parent::seo_zones();
    }

    function generate_name($gtext_id, $separator=SEO_DEFAULT_WORDS_SEPARATOR) {
      global $g_db;

      $string = '';
      $name_query = $g_db->query("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
      if( !$g_db->num_rows($name_query) )
        return $string;

      $names_array = $g_db->fetch_array($name_query);
      $string =  $this->create_safe_string($names_array['gtext_title'], $separator);
      return $this->adapt_lexico($string, $separator);
    }

    function process_action() {
      switch( $this->m_action ) {
        case 'validate':
          return $this->validate();
        case 'validate_confirm':
          $result = parent::validate_array_selection('pc_id', 'validate'); 
          return $this->validate_confirm();
        case 'update_multizone':
          $result = parent::validate_array_selection('pc_id'); 
          return $this->update_multizone();
        case 'insert_multi_entries':
          $result = parent::validate_array_selection('gtext_id', 'multi_entries');
          return $this->insert_multi_entries();
        case 'deleteconfirm_multizone':
          $result = parent::validate_array_selection('pc_id'); 
          return $this->deleteconfirm_multizone();
        case 'delete_multizone':
          $result = parent::validate_array_selection('pc_id'); 
        default:
          return parent::process_action(); 
          break;
      }
    }

    function validate() {
      global $g_db;

      $this->error_array = array();
      // First pass check for missing gtext entries from seo table
      $check_query = $g_db->query("select g.gtext_id, g.gtext_title as name, '0' as missing_id from " . TABLE_GTEXT . " g left join " . TABLE_SEO_TO_GTEXT . " s2g on (s2g.gtext_id = g.gtext_id) where s2g.gtext_id is null order by g.gtext_id desc limit " . SEO_PAGE_SPLIT);
      while( $check_array = $g_db->fetch_array($check_query) ) {
        $this->error_array[] = $check_array;
      }
      // Second pass check for redundant entries in the seo table
      $check_query = $g_db->query("select s2g.gtext_id, s2g.seo_name as name, '-1' as missing_id from " . TABLE_SEO_TO_GTEXT . " s2g left join " . TABLE_GTEXT . " g on (s2g.gtext_id = g.gtext_id) where g.gtext_id is null order by s2g.gtext_id desc limit " . SEO_PAGE_SPLIT);
      while( $check_array = $g_db->fetch_array($check_query) ) {
        $this->error_array[] = $check_array;
      }
      return $this->error_array;
    }

    function validate_confirm() {
      global $g_db;

      foreach($_POST['pc_id'] as $gtext_id => $val) {
        if( $_POST['missing'][$gtext_id] == -1 ) {
          $g_db->query("delete from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
        } elseif( $_POST['missing'][$gtext_id] == 0 ) {
          $seo_name = $this->generate_name($gtext_id);
          $sql_data_array = array(
                                  'gtext_id' => (int)$gtext_id,
                                  'seo_name' => $g_db->prepare_input($seo_name),
                                  );
          $g_db->perform(TABLE_SEO_TO_GTEXT, $sql_data_array, 'insert');
        }
      }
      tep_redirect(tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=validate'));
    }

    function update_multizone() {
      global $g_db;

      foreach ($_POST['pc_id'] as $gtext_id => $val) {
        $seo_name = $this->create_safe_string($_POST['name'][$gtext_id]);
        if( SEO_PROXIMITY_CLEANUP == 'true' ) {
          $check_query = $g_db->query("select seo_name from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
          if( $check_array = $g_db->fetch_array($check_query) ) {
            $check_name = $check_array['seo_name'];
            $g_db->query("delete from " . TABLE_SEO_URL . " where seo_url_get like '%" . $g_db->filter($check_name) . "%'");
          }
        }
        $sql_data_array = array(
                                'seo_name' => $g_db->prepare_input($seo_name)
                               );

        $g_db->perform(TABLE_SEO_TO_GTEXT, $sql_data_array, 'update', "gtext_id = '" . (int)$gtext_id . "'");
      }
      tep_redirect(tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
    }

    function insert_multi_entries() {
      global $g_db, $messageStack;

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');
      switch( $multi_form ) {
        case 'insert_multi_entries':
          $tmp_array = array();
          foreach ($_POST['gtext_id'] as $gtext_id=>$val) {
            $check_query = $g_db->query("select gtext_id from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
            if( $g_db->num_rows($check_query) > 0 ) continue;
            $seo_name = $this->generate_name($gtext_id);

            if( !isset($tmp_array[$seo_name]) ) {
              $tmp_array[$seo_name] = 1;
            } else {
              $tmp_array[$seo_name]++;
              $seo_name .= $tmp_array[$seo_name];
            }

            $sql_data_array = array(
                                    'gtext_id' => (int)$gtext_id,
                                    'seo_name' => $g_db->prepare_input($seo_name),
                                    );
            $g_db->perform(TABLE_SEO_TO_GTEXT, $sql_data_array);
          }
          $messageStack->add_session(SUCCESS_SELECTED_ADDED, 'success');
          tep_redirect(tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
          break;
        default:
          break;
      }
    }

    function deleteconfirm_multizone() {
      global $g_db;

      for($i=0, $j=count($_POST['pc_id']); $i<$j; $i++ ) {
        $gtext_id = $_POST['pc_id'][$i];
        if( SEO_PROXIMITY_CLEANUP == 'true' ) {
          $check_query = $g_db->query("select seo_name from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
          if( $check_array = $g_db->fetch_array($check_query) ) {
            $check_name = $check_array['seo_name'];
            $g_db->query("delete from " . TABLE_SEO_URL . " where seo_url_get like '%" . $g_db->filter($check_name) . "%'");
          }
        }
        $g_db->query("delete from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
      }
      tep_redirect(tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
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
      $html_string = '';
      if( count($this->error_array) ) {
        $html_string .= 
        '        <div class="comboHeading">' . "\n" . 
        '          <div class="splitLine">' . "\n" . 
        '            <div class="dataTableRowHighBorder" style="float: left; width: 24px;">&nbsp;</div>' . "\n" . 
        '            <div class="smallText" style="float: left;"><b>&nbsp;-&nbsp;Text Page present in the generic table but not present in the SEO-G table</b></div>' . "\n" . 
        '          </div>' . "\n" . 
        '          <div class="splitLine">' . "\n" . 
        '            <div class="dataTableRowImpactBorder" style="float: left; width: 24px;">&nbsp;</div>' . "\n" . 
        '            <div class="smallText" style="float: left;"><b>&nbsp;-&nbsp;Text Entry present in the SEO-G table but it is not present in the generic text table</b></div>' . "\n" . 
        '          </div>' . "\n" . 
        '        </div>' . "\n";

        $html_string .= 
        '        <div class="formArea">' . tep_draw_form('rl', FILENAME_SEO_ZONES, 'action=validate_confirm&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage, 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <td class="dataTableHeadingContent" width="40"><a href="javascript:void(0)" onClick="copy_checkboxes(document.rl, \'pc_id\')" title="Page Select On/Off" class="menuBoxHeadingLink"><span class="dataTableHeadingContent">' . TABLE_HEADING_SELECT . '</span></a></td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_ID . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_NAME . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_COMMENT . '</td>' . "\n" . 
        '          </tr>' . "\n";
        for($i=0, $j=count($this->error_array); $i<$j; $i++ ) {
          $row_class = ($this->error_array[$i]['missing_id'])?'dataTableRowImpact':'dataTableRowHigh';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_checkbox_field('pc_id[' . $this->error_array[$i]['gtext_id'] . ']', 'on', false ) . tep_draw_hidden_field('missing[' . $this->error_array[$i]['gtext_id'] . ']', $this->error_array[$i]['missing_id']) . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . $this->error_array[$i]['gtext_id'] . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . $this->error_array[$i]['name'] . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . (($this->error_array[$i]['missing_id'])?'Missing from Text Table':'Missing from SEO-G') . '</td>' . "\n" . 
          '          </tr>'  . "\n";
        }
        $html_string .= 
        '          <tr>' . "\n" . 
        '            <td colspan="4" class="formButtons"><a href="' . tep_href_link(FILENAME_SEO_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_fix_errors.gif', 'Fix Listed Errors') . '</td>' . "\n" . 
        '          </tr>' . "\n" .
        '        </table></form></div>' . "\n";
      } else {
        $html_string .= 
        '        <div class="comboHeading">' . "\n" . 
        '          <div class="smallText"><b>No Errors Found</b></div>' . "\n" . 
        '          <div class="smallText"><a href="' . tep_href_link(FILENAME_SEO_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a></div>' . "\n" . 
        '        </div>' . "\n";
      }
      return $html_string;
    }

// Default List
    function display_list() {
      global $g_db;

      $html_string = '';
      $rows = 0;

      $zones_query_raw = "select gtext_id, seo_name from " . TABLE_SEO_TO_GTEXT . " order by seo_name";
      $zones_split = new splitPageResults($zones_query_raw, SEO_PAGE_SPLIT, '', 'spage');

      if( $zones_split->number_of_rows > 0 ) {
        $html_string .= 
        '      <div class="formArea">' . tep_draw_form('rl', FILENAME_SEO_ZONES, 'action=delete_multizone&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage . '&spage=' . $this->m_spage, 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n";
        if(empty($this->saction)) {
          $html_string .= 
          '          <tr>' . "\n" . 
          '            <td colspan="5" class="formButtons"><a href="' . tep_href_link(FILENAME_SEO_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=update_multizone') . '\'' . '"') . ' ' . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=delete_multizone') . '\'' . '"') . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
        $html_string .= 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <td class="dataTableHeadingContent"><a href="javascript:void(0)" onClick="copy_checkboxes(document.rl, \'pc_id\')" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink"><span class="dataTableHeadingContent">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</span></a></td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_GTEXT . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_NAME . '</td>' . "\n" . 
        '          </tr>' . "\n";
        $zones_query = $g_db->query($zones_split->sql_query);
        $bCheck = false;
        while( $zones_array = $g_db->fetch_array($zones_query)) {
          $extra_query = $g_db->query("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$zones_array['gtext_id'] . "'");

          if( $g_db->num_rows($extra_query) ) {
            $extra_array = $g_db->fetch_array($extra_query);
            $final_name = '<a href="' . tep_href_link(FILENAME_GENERIC_TEXT, 'gtID=' . $zones_array['gtext_id'] . '&action=new_generic_text') . '">' . $extra_array['gtext_title'] . '</a>';
          } else {
            $final_name = '<font color="FF0000">' . TEXT_INFO_NA . '&nbsp;[' . $zones_array['seo_name'] . ']' . '</font>';
          }

          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_checkbox_field('pc_id[' . $zones_array['gtext_id'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . $final_name . '</td>' . "\n";
          if( $final_name == TEXT_INFO_NA ) {
            $html_string .= 
            '            <td class="dataTableContent">' . TEXT_ERROR . '</td>' . "\n";
          } else {
            $html_string .= 
            '            <td class="dataTableContent">' . tep_draw_input_field('name[' . $zones_array['gtext_id'] . ']', $zones_array['seo_name'], 'style="width: 300px"') . '</td>' . "\n";
          }
          $html_string .= 
          '          </tr>'  . "\n";
        }
        if(empty($this->saction)) {
          $html_string .= 
          '          <tr>' . "\n" . 
          '            <td colspan="5" class="formButtons"><a href="' . tep_href_link(FILENAME_SEO_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=update_multizone') . '\'' . '"') . ' ' . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=delete_multizone') . '\'' . '"') . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
        $html_string .= 
        '          </table></form></div>' . "\n" . 
        '          <div class="splitLine">' . "\n" . 
        '            <div style="float: left;">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
        '            <div style="float: right;">' . $zones_split->display_links(tep_get_all_get_params(array('action', 'spage')) . 'action=list') . '</div>' . "\n" . 
        '          </div>' . "\n";

      } else {
        $html_string .= 
        '        <div class="comboHeading">' . "\n" . 
        '          <div class="smallText">' . TEXT_INFO_NO_ENTRIES . '</div>' . "\n" . 
        '        </div>' . "\n";
      }
      if (empty($this->saction)) {
        $html_string .= 
        '        <div class="formButtons"><a href="' . tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=multi_entries') . '">' . tep_image_button('button_entries.gif', TEXT_SWITCH_GTEXT) . '</a></div>' . "\n";
      }
      return $html_string;
    }

    function display_multi_entries() {
      global $g_db;

      $html_string = '';
      $html_string .=
      '        <div class="comboHeading">' . "\n" . 
      '          <div class="smallText">' . TEXT_SELECT_MULTIGTEXT . '</div>' . "\n" . 
      '        </div>' . "\n" . 
      '        <div class="formArea">' . tep_draw_form('mc', FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=insert_multi_entries', 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent"><a href="javascript:void(0)" onClick="copy_checkboxes(document.mc, \'gtext_id\')" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></td>' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_GTEXT . '</td>' . "\n" . 
      '            </tr>' . "\n"; 
      $rows = 0;
      $zones_query_raw = "select gt.gtext_id, gt.gtext_title from " . TABLE_GTEXT . " gt order by gt.gtext_title";
      $zones_split = new splitPageResults($zones_query_raw, SEO_PAGE_SPLIT, '', 'mcpage');
      $zones_query = $g_db->query($zones_split->sql_query);
      $bCheck = false;
      while( $zones_array = $g_db->fetch_array($zones_query) ) {
        $check_query = $g_db->query("select count(*) as total from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$zones_array['gtext_id'] . "'");
        $check_array = $g_db->fetch_array($check_query);
        $bCheck  = $check_array['total']?true:false;
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if($bCheck) {
          $row_class = 'dataTableRowGreen';
        }
        $html_string .=
        '            <tr class="' . $row_class . '">' . "\n" . 
        '              <td class="dataTableContent">' . ($bCheck?'Included':tep_draw_checkbox_field('gtext_id[' . $zones_array['gtext_id'] . ']')) . '</td>' . "\n" . 
        '              <td class="dataTableContent">' . $zones_array['gtext_title'] . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .=
      '              <tr>' . "\n" . 
      '                <td colspan="3" class="formButtons"><a href="' . tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action', 'mcpage')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_draw_hidden_field('multi_form', 'insert_multi_entries') . '&nbsp;' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '</td>' . "\n" . 
      '              </tr>' . "\n" . 
      '            </table></form></div>' . "\n" . 
      '            <div class="splitLine">' . "\n" . 
      '              <div style="float: left;">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
      '              <div style="float: right;">' . $zones_split->display_links(tep_get_all_get_params(array('action', 'mcpage')) . 'action=multi_entries') . '</div>' . "\n" . 
      '            </div>' . "\n";

      return $html_string;
    }

    function display_delete_multizone() {
      global $g_db;

      $html_string = '';
      $zones_query = $g_db->query("select seo_types_name from " . TABLE_SEO_TYPES . " where seo_types_id = '" . (int)$this->m_zID . "'");
      $zones_array = $g_db->fetch_array($zones_query);
      $html_string .= 
      '          <div class="comboHeading">' . "\n" . 
      '            <div class="smallText">' . sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zones_array['seo_types_name']). '</div>' . "\n" . 
      '          </div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=deleteconfirm_multizone', 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_GTEXT . '</td>' . "\n" . 
      '            </tr>' . "\n";
      $rows = 0;
      foreach ($_POST['pc_id'] as $key => $val) {
        $delete_query = $g_db->query("select m.gtext_title as final_name from " . TABLE_SEO_TO_GTEXT . " s2m left join " . TABLE_GTEXT . " m on (s2m.gtext_id=m.gtext_id) where s2m.gtext_id = '" . (int)$key . "' order by m.gtext_title");

        if( $g_db->num_rows($delete_query) ) {
          $delete_array = $g_db->fetch_array($delete_query);
        } else {
          $delete_array = array('final_name' => 'N/A - ' . $key);
        }
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        $html_string .= 
        '          <tr class="' . $row_class . '">' . "\n" . 
        '            <td class="dataTableContent">' . tep_draw_hidden_field('pc_id[]', $key) . $delete_array['final_name'] . '</td>' . "\n" . 
        '          </tr>' . "\n";
      }
      if( count($_POST['pc_id']) ) {
        $html_string .= 
        '            <tr>' . "\n" . 
        '              <td colspan="4" class="formButtons"><a href="' . tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '          </table></form></div>' . "\n";
      return $html_string;
    }
  }
?>