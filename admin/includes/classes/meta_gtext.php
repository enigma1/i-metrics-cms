<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// META-G Text Entries class for Admin
// This is a Bridge for META-G
// Processes text pages generates meta-tag segments.
// Featuring:
// - Multi-Text Entries Listings with Meta-Tags
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
  class meta_gtext extends meta_zones {

    // Compatibility Constructor
    function meta_gtext() {
      $this->error_array = array();
      $this->m_ssID = isset($_GET['ssID'])?$_GET['ssID']:'';
      $this->m_mcpage = isset($_GET['mcpage'])?$_GET['mcpage']:'';
      $this->m_mppage = isset($_GET['mppage'])?$_GET['mppage']:'';
      parent::meta_zones();
    }

    function generate_name($gtext_id) {
      extract(tep_load('database'));

      $name = '';
      $name_query = $db->query("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
      if( $names_array = $db->fetch_array($name_query) ) {
        $name = $names_array['gtext_title'];
        $name =  $this->create_safe_string($name);
      }
      return $name;
    }

    function generate_lexico($index=0) {
      extract(tep_load('database'));

      $gtext_query = $db->query("select gtext_title from " . TABLE_GTEXT . "");
      while( $gtext_array = $db->fetch_array($gtext_query) ) {

        $phrase = $this->create_safe_string($gtext_array['gtext_title']);
        $md5_key = md5($phrase);
        $check_query = $db->query("select count(*) as total from " . TABLE_META_LEXICO . " where meta_lexico_key = '" . $db->filter($md5_key) . "'");
        $check_array = $db->fetch_array($check_query);
        if( !$check_array['total'] ) {
          $sql_data_array = array(
            'meta_lexico_key' => $db->prepare_input($md5_key),
            'meta_lexico_text' => $db->prepare_input($phrase)
          );
          $db->perform(TABLE_META_LEXICO, $sql_data_array);
        }
      }
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
        case 'insert_multientries':
          $result = parent::validate_array_selection('pc_id', 'multi_entries'); 
          return $this->insert_multientries();
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
      // First pass check for missing gtext entries from seo table
      $check_query = $db->query("select g.gtext_id, g.gtext_title as name, '0' as missing_id from " . TABLE_GTEXT . " g left join " . TABLE_META_GTEXT . " s2g on (s2g.gtext_id = g.gtext_id) where s2g.gtext_id is null order by g.gtext_id desc limit " . META_PAGE_SPLIT);
      while( $check_array = $db->fetch_array($check_query) ) {
        $this->error_array[] = $check_array;
      }
      // Second pass check for redundant entries in the seo table
      $check_query = $db->query("select s2g.gtext_id, s2g.meta_name as name, '-1' as missing_id from " . TABLE_META_GTEXT . " s2g left join " . TABLE_GTEXT . " g on (s2g.gtext_id = g.gtext_id) where g.gtext_id is null order by s2g.gtext_id desc limit " . META_PAGE_SPLIT);
      while( $check_array = $db->fetch_array($check_query) ) {
        $this->error_array[] = $check_array;
      }
      return $this->error_array;
    }

    function validate_confirm() {
      extract(tep_load('defs', 'database'));

      foreach($_POST['pc_id'] as $gtext_id => $val) {
        if( $_POST['missing'][$gtext_id] == -1 ) {
          $db->query("delete from " . TABLE_META_GTEXT . " where meta_types_id = '" . (int)$this->m_zID . "' and gtext_id = '" . (int)$gtext_id . "'");
        } elseif( $_POST['missing'][$gtext_id] == 0 ) {
          $meta_name = $this->generate_name($gtext_id);
          $sql_data_array = array(
            'meta_types_id' => (int)$this->m_zID,
            'gtext_id' => (int)$gtext_id,
            'meta_name' => $db->prepare_input($meta_name),
          );
          $db->perform(TABLE_META_GTEXT, $sql_data_array, 'insert');
        }
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=validate'));
    }

    function update_multizone() {
      extract(tep_load('defs', 'database'));

      foreach($_POST['pc_id'] as $gtext_id => $val) {

        $meta_title = $_POST['title'][$gtext_id];
        $meta_keywords = $_POST['keywords'][$gtext_id];
        $meta_text = $_POST['text'][$gtext_id];

        $sql_data_array = array(
          'meta_title' => $db->prepare_input($meta_title),
          'meta_keywords' => $db->prepare_input($meta_keywords),
          'meta_text' => $db->prepare_input($meta_text)
        );

        $db->perform(TABLE_META_GTEXT, $sql_data_array, 'update', "gtext_id = '" . (int)$gtext_id . "'");
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
    }


    function insert_multientries() {
      extract(tep_load('defs', 'database', 'message_stack'));

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');

      switch( $multi_form ) {
        case 'multi_entries':
          $tmp_array = array();
          foreach ($_POST['pc_id'] as $gtext_id=>$val) {
            $multi_query = $db->query("select gtext_id, gtext_title, gtext_description from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
            if( $multi = $db->fetch_array($multi_query) ) {
              $check_query = $db->query("select gtext_id from " . TABLE_META_GTEXT . " where gtext_id = '" . (int)$multi['gtext_id'] . "'");
              if( $db->num_rows($check_query) )
                continue;

              $meta_name = $this->create_safe_string($multi['gtext_title']);

              $meta_keywords = $this->create_keywords_lexico($multi['gtext_description']);
              $meta_text = $this->create_safe_description($multi['gtext_description']);

              $sql_data_array = array(
                'gtext_id' => (int)$gtext_id,
                'meta_title' => $db->prepare_input($meta_name),
                'meta_keywords' => $db->prepare_input($meta_keywords),
                'meta_text' => $db->prepare_input($meta_text)
              );
              $db->perform(TABLE_META_GTEXT, $sql_data_array, 'insert');
            }
          }
          $msg->add_session(SUCCESS_SELECTED_ADDED, 'success');
          tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
          break;
        default:
          break;
      }
    }

    function deleteconfirm_multizone() {
      extract(tep_load('defs', 'database'));

      for($i=0, $j=count($_POST['pc_id']); $i<$j; $i++ ) {
        $gtext_id = $_POST['pc_id'][$i];
        $db->query("delete from " . TABLE_META_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
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
        '            <td><b>&nbsp;-&nbsp;Entry present in the entries table but not present in the SEO-G table</b></td>' . "\n" . 
        '          </tr>' . "\n" . 
        '          <tr>' . "\n" . 
        '            <td colspan="2">' . tep_draw_separator('pixel_trans.gif', '100%', '1') . '</td>' . "\n" . 
        '          </tr>' . "\n" . 
        '          <tr>' . "\n" . 
        '            <td class="dataTableRowImpactBorder" width="16">&nbsp;</td>' . "\n" . 
        '            <td><b>&nbsp;-&nbsp;Entry present in the SEO-G table but it is not present in the entries table</b></td>' . "\n" . 
        '          </tr>' . "\n" . 
        '        </table></td>' . "\n" . 
        '      </tr>' . "\n" .
        '      <tr>' . "\n" . 
        '        <td>' . tep_draw_separator('pixel_trans.gif', '100%', '10') . '</td>' . "\n" . 
        '      </tr>' . "\n";
        $html_string .= 
        '      <tr>' . "\n" . 
        '        <td valign="top">' . tep_draw_form('rl', $cDefs->script, 'action=validate_confirm&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage, 'post') . '<table class="tabledata">' . "\n" . 
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
          '            <td class="calign">' . tep_draw_checkbox_field('pc_id[' . $this->error_array[$i]['gtext_id'] . ']', 'on', false ) . tep_draw_hidden_field('missing[' . $this->error_array[$i]['gtext_id'] . ']', $this->error_array[$i]['missing_id']) . '</td>' . "\n" . 
          '            <td>' . $this->error_array[$i]['gtext_id'] . '</td>' . "\n" . 
          '            <td>' . $this->error_array[$i]['name'] . '</td>' . "\n" . 
          '            <td>' . (($this->error_array[$i]['missing_id'])?'Missing from Entries':'Missing from SEO-G') . '</td>' . "\n" . 
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

      $zones_query_raw = "select * from " . TABLE_META_GTEXT . " order by gtext_id desc";
      $zones_split = new splitPageResults($zones_query_raw, META_PAGE_SPLIT, '', 'spage');

      if( $zones_split->number_of_rows > 0 ) {
        $buttons = array();
        if(empty($this->m_saction)) {
          $buttons = array(
            '<a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>',
            tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=update_multizone') . '\'' . '"'),
            tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=delete_multizone') . '\'' . '"')
          );
        }

        $html_string .= 
        '        <div class="formArea">' . tep_draw_form('rl', $cDefs->script, 'action=delete_multizone&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage . '&spage=' . $this->m_spage, 'post') . '<div class="comboHeading">' . implode('', $buttons) . '</div><table class="tabledata">' . "\n";
        $html_string .= 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <th class="calign"><a href="#pc_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
        '            <th>' . TABLE_HEADING_TITLE . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_KEYWORDS . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_DESCRIPTION . '</th>' . "\n" . 
        '          </tr>' . "\n";
        $zones_query = $db->query($zones_split->sql_query);
        $bCheck = false;
        while( $zones_array = $db->fetch_array($zones_query) ) {

          $extra_query = $db->query("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$zones_array['gtext_id'] . "'");
          if( $extra_array = $db->fetch_array($extra_query) ) {
            $final_name = '<a href="' . tep_href_link(FILENAME_GENERIC_TEXT, 'gtID=' . $zones_array['gtext_id'] . '&action=new_generic_text') . '">' . $extra_array['gtext_title'] . '</a>';
          } else {
            $final_name = '<font color="#FF0000"><b>' . TEXT_INFO_NA . '&nbsp;[' . $zones_array['meta_title'] . ']' . '</b></font>';
          }
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="calign">' . tep_draw_checkbox_field('pc_id[' . $zones_array['gtext_id'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '            <td><div class="rpad">' . $final_name . '<br />' . tep_draw_input_field('title[' . $zones_array['gtext_id'] . ']', $zones_array['meta_title']) . '</div></td>' . "\n" . 
          '            <td><div class="rpad">' . tep_draw_textarea_field('keywords[' . $zones_array['gtext_id'] . ']', $zones_array['meta_keywords'], '','2') . '</div></td>' . "\n" . 
          '            <td><div class="rpad">' . tep_draw_textarea_field('text[' . $zones_array['gtext_id'] . ']', $zones_array['meta_text'], '','2') . '</div></td>' . "\n" . 
          '          </tr>'  . "\n";
        }
        $html_string .= 
        '          </table><div class="formButtons">' . implode('', $buttons) . '</div></form></div>' . "\n" . 
        '          <div class="listArea splitLine">' . "\n" . 
        '            <div class="floater">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
        '            <div class="floatend">' . $zones_split->display_links(tep_get_all_get_params('action', 'spage') . 'action=list') . '</div>' . "\n" . 
        '          </div>' . "\n";
      } else {
        $html_string .= 
        '        <div class="comboHeading">' . "\n" . 
        '          <div class="smallText">' . TEXT_INFO_NO_ENTRIES . '</div>' . "\n" . 
        '        </div>' . "\n";
      }
      if (empty($this->saction)) {
        $html_string .= 
        '        <div class="formButtons"><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=multi_entries') . '">' . tep_image_button('button_zones.gif', TEXT_SWITCH_ZONES) . '</a></div>' . "\n";
      }
      return $html_string;
    }


    function display_multi_entries() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $html_string .=
      '          <div class="comboHeading">' . "\n" . 
      '            <div>' . TEXT_SELECT_MULTIENTRIES . '</div>' . "\n" . 
      '          </div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('mc', $cDefs->script, tep_get_all_get_params('action') . 'action=insert_multientries', 'post') . '<table class="tabledata">' . "\n" . 
      '            <tr>' . "\n" . 
      '              <td colspan="2" class="formButtons"><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'mcpage') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_draw_hidden_field('multi_form', 'multi_entries') . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '</td>' . "\n" . 
      '            </tr>' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <th class="calign"><a href="#pc_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
      '              <th>' . TABLE_HEADING_ENTRIES . '</th>' . "\n" . 
      '            </tr>' . "\n"; 
      $rows = 0;
      //$gtext_query_raw = "select gtext_id, gtext_title from " . TABLE_GTEXT . " order by gtext_title";
      $zones_query_raw = "select m.gtext_id, m.gtext_title, if(mm.gtext_id, '1', '0') as checkbox from " . TABLE_GTEXT . " m left join " . TABLE_META_GTEXT . " mm on ((mm.gtext_id = if(mm.gtext_id, m.gtext_id,0))) order by m.gtext_id, m.gtext_title";
      $zones_split = new splitPageResults($zones_query_raw, META_PAGE_SPLIT, '', 'mcpage');
      $zones_query = $db->query($zones_split->sql_query);
      $bCheck = false;
      while( $zones_array = $db->fetch_array($zones_query)) {
        $bCheck = ($zones_array['checkbox'] == '1')?true:false;
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if($bCheck)
          $row_class = 'dataTableRowGreen';

        $html_string .=
        '              <tr class="' . $row_class . '">' . "\n" . 
        '                <td class="calign">' . ($bCheck?'Included':tep_draw_checkbox_field('pc_id[' . $zones_array['gtext_id'] . ']')) . '</td>' . "\n" . 
        '                <td><a href="' . tep_href_link(FILENAME_GENERIC_TEXT, 'gtID=' . $zones_array['gtext_id'] . '&action=new_generic_text') . '"><b>' . $zones_array['gtext_title'] . '</b></a></td>' . "\n" . 
        '              </tr>' . "\n";
      }
      $html_string .=
      '            <tr>' . "\n" . 
      '              <td colspan="2" class="formButtons"><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'mcpage') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_draw_hidden_field('multi_form', 'multi_entries') . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '</td>' . "\n" . 
      '            </tr>' . "\n" . 
      '          </table></form></div>' . "\n" . 
      '          <div class="splitLine">' . "\n" . 
      '            <div class="floater">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
      '            <div class="floatend">' . $zones_split->display_links($this->m_mcpage, tep_get_all_get_params('action', 'mcpage') . 'action=multi_entries') . '</div>' . "\n" . 
      '          </div>' . "\n";

      return $html_string;
    }

    function display_delete_multizone() {
      extract(tep_load('defs', 'database'));

      if( !isset($_POST['pc_id']) || !is_array($_POST['pc_id']) ) {
        return '';
      }

      $html_string = '';
      $zones_query = $db->query("select meta_types_name from " . TABLE_META_TYPES . " where meta_types_id = '" . (int)$this->m_zID . "'");
      $zones_array = $db->fetch_array($zones_query);
      $html_string .= 
      '          <div class="comboHeading">' . "\n" . 
      '            <div>' . sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zones_array['meta_types_name']) . '</div>' . "\n" . 
      '          </div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', $cDefs->script, tep_get_all_get_params('action') . 'action=deleteconfirm_multizone', 'post') . '<table class="tabledata">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <th>' . TEXT_DELETE_MULTIZONE_CONFIRM . '</th>' . "\n" . 
      '            </tr>' . "\n";
      $rows = 0;

      foreach($_POST['pc_id'] as $key => $value) {
        $gtext_id = $key;
        $delete_query = $db->query("select gtext_id, meta_title from " . TABLE_META_GTEXT . " where gtext_id = '" . (int)$key . "' order by meta_title, gtext_id");
        if( $delete_array = $db->fetch_array($delete_query) ) {
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td>' . tep_draw_hidden_field('pc_id[]', $delete_array['gtext_id']) . $delete_array['meta_title'] . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
      }
      if( count($_POST['pc_id']) ) {
        $html_string .= 
        '            <tr>' . "\n" . 
        '              <td class="formButtons"><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '            </table></form></div>' . "\n";
      return $html_string;
    }
  }
?>