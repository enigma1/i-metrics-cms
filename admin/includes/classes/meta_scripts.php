<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: META-G Scripts class
// Processes php script filenames generates keywords.
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

  class meta_scripts extends meta_zones {
    var $error_array, $scripts_array, $files_array;

// class constructor
    function meta_scripts() {
      $this->m_ssID = isset($_GET['ssID'])?$_GET['ssID']:'';
      $this->m_mcpage = isset($_GET['mcpage'])?$_GET['mcpage']:'';
      $this->m_mppage = isset($_GET['mppage'])?$_GET['mppage']:'';
      parent::meta_zones();
      $this->get_scripts();
    }

    function get_scripts() {
      $dir = dir(DIR_FS_CATALOG);
      $this->scripts_array = array();
      $this->files_array = array();
      while ($script = $dir->read()) {
        if( strlen($script) < 5 || substr($script, -4, 4) != '.php')
          continue;

        //$md5_key = md5($this->create_safe_string($script));
        $md5_key = md5($script);
        $this->scripts_array[strtolower($script)] = array(
                                                          'id' => $md5_key, 
                                                          'text' => $script
                                                         );

        $this->files_array[$md5_key] = $script;

      }
      $dir->close();
      ksort($this->scripts_array, SORT_STRING);
      $this->scripts_array = array_values($this->scripts_array);
    }

    function generate_lexico($index=0) {
      global $g_db;

      foreach($this->scripts_array as $key => $value) {
        $script = substr($value['text'], 0, -4);
        $script = $this->create_safe_string($script);
        $md5_key = md5($script);

        $check_query = $g_db->query("select count(*) as total from " . TABLE_META_LEXICO . " where meta_lexico_key = '" . $g_db->filter($value['id']) . "'");
        $check_array = $g_db->fetch_array($check_query);
        if( !$check_array['total'] ) {
          $sql_data_array = array(
                                  'meta_lexico_key' => $g_db->prepare_input($md5_key),
                                  'meta_lexico_text' => $g_db->prepare_input($script)
                                 );
          $g_db->perform(TABLE_META_LEXICO, $sql_data_array);
        }
      }
      return false;
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
          $result = parent::validate_array_selection('script', 'multi_entries'); 
          return $this->insert_multi_entries();
        case 'deleteconfirm_multizone':
          $result = parent::validate_array_selection('pc_id'); 
          return $this->deleteconfirm_multizone();
        default:
          return parent::process_action(); 
          break;
      }
    }

    function validate() {
      $this->error_array = array();
      return $this->error_array;
    }

    function validate_confirm() {
      tep_redirect(tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=validate'));
    }

    function update_multizone() {
      global $g_db;

      foreach ($_POST['pc_id'] as $pc_id => $val) {
        $md5_key = $pc_id;
        $sql_data_array = array(
                                'meta_title' => $g_db->prepare_input($_POST['title'][$pc_id]),
                                'meta_keywords' => $g_db->prepare_input($_POST['keywords'][$pc_id]),
                                'meta_text' => $g_db->prepare_input($_POST['desc'][$pc_id]),
                               );

        $g_db->perform(TABLE_META_SCRIPTS, $sql_data_array, 'update', "meta_scripts_key = '" . $g_db->filter($md5_key) . "'");
      }
      tep_redirect(tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
    }


    function insert_multi_entries() {
      global $g_db, $messageStack;

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');
      switch( $multi_form ) {
        case 'insert_multi_entries':
          $tmp_array = array();
          foreach ($_POST['script'] as $key=>$value) {
            $check_query = $g_db->query("select meta_scripts_key from " . TABLE_META_SCRIPTS . " where meta_scripts_key = '" . $g_db->filter($key) . "'");
            if( $g_db->num_rows($check_query) > 0 ) continue;

            $meta_name = $this->create_safe_string($_POST['name'][$key]);
            $meta_file = $_POST['file'][$key];
            $meta_keywords = $this->create_keywords_string($meta_name);

            $sql_data_array = array(
                                    'meta_scripts_key' => $g_db->prepare_input($key),
                                    'meta_scripts_file' => $g_db->prepare_input($meta_file),
                                    'meta_title' => $g_db->prepare_input($meta_name),
                                    'meta_keywords' => $g_db->prepare_input($meta_keywords),
                                    'meta_text' => $g_db->prepare_input($meta_name),
                                    );
            $g_db->perform(TABLE_META_SCRIPTS, $sql_data_array);
          }
          $messageStack->add_session(SUCCESS_SELECTED_ADDED, 'success');
          tep_redirect(tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
          break;
        default:
          break;
      }
    }

    function deleteconfirm_multizone() {
      global $g_db;

      foreach ($_POST['pc_id'] as $pc_id=>$val) {
        $md5_key = $pc_id;
        $g_db->query("delete from " . TABLE_META_SCRIPTS . " where meta_scripts_key = '" . $g_db->filter($md5_key) . "'");
      }
      tep_redirect(tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
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
      $html_string .= 
      '      <tr>' . "\n" . 
      '        <td><hr /></td>' . "\n" . 
      '      </tr>' . "\n";
      $html_string .= 
      '      <tr>' . "\n" . 
      '        <td class="smallText">' . 'Filenames are not validated' . '</td>' . "\n" . 
      '      </tr>' . "\n" . 
      '      <tr>' . "\n" . 
      '        <td colspan="2">' . tep_draw_separator('pixel_trans.gif', '100%', '4') . '</td>' . "\n" . 
      '      </tr>' . "\n" . 
      '      <tr>' . "\n" . 
      '        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">' . "\n" . 
      '          <tr>' . "\n" . 
      '            <td><a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a></td>' . "\n" . 
      '          </tr>' . "\n" . 
      '        </table></td>' . "\n" . 
      '      </tr>' . "\n";
      return $html_string;
    }


// Default List
    function display_list() {
      global $g_db;

      $html_string = '';
      $rows = 0;
      $zones_query_raw = "select * from " . TABLE_META_SCRIPTS . " order by meta_scripts_file";
      $zones_split = new splitPageResults($zones_query_raw, META_PAGE_SPLIT, '', 'spage');

      if( $zones_split->number_of_rows > 0 ) {
        $html_string .= 
        '        <div class="formArea">' . tep_draw_form('rl', FILENAME_META_ZONES, 'action=delete_multizone&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage . '&spage=' . $this->m_spage, 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" ;

        if(empty($this->saction)) {
          $html_string .= 
          '          <tr>' . "\n" . 
          '            <td colspan="5" class="formButtons"><a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=update_multizone') . '\'' . '"') . ' ' . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=delete_multizone') . '\'' . '"') . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }

        $html_string .= 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <td class="dataTableHeadingContent"><a href="javascript:void(0)" onclick="copy_checkboxes(document.rl, \'pc_id\')" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_TITLE_FILE . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_KEYWORDS . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_DESCRIPTION . '</td>' . "\n" . 
        '          </tr>' . "\n";

        $zones_query = $g_db->query($zones_split->sql_query);
        $bCheck = false;
        while ($zones_array = $g_db->fetch_array($zones_query)) {
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_checkbox_field('pc_id[' . $zones_array['meta_scripts_key'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '            <td class="dataTableContent" valign="top">File:&nbsp;<b>' . $zones_array['meta_scripts_file'] . '</b><br />' . tep_draw_input_field('title[' . $zones_array['meta_scripts_key'] . ']', $zones_array['meta_title'], 'style="width: 300px"') . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_textarea_field('keywords[' . $zones_array['meta_scripts_key'] . ']', 'soft', '40','2', $zones_array['meta_keywords']) . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_textarea_field('desc[' . $zones_array['meta_scripts_key'] . ']', 'soft', '40','2', $zones_array['meta_text']) . '</td>' . "\n" . 
          '          </tr>'  . "\n";
        }
        if(empty($this->saction)) {
          $html_string .= 
          '          <tr>' . "\n" . 
          '            <td colspan="5" class="formButtons"><a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=update_multizone') . '\'' . '"') . ' ' . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=delete_multizone') . '\'' . '"') . '</td>' . "\n" . 
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
        '        <div class="formButtons"><a href="' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=multi_entries') . '">' . tep_image_button('button_scripts.gif', TEXT_SWITCH_SCRIPTS) . '</a></div>' . "\n";
      }
      return $html_string;
    }

    function display_multi_entries() {
      global $g_db;

      $html_string = '';
      $html_string .=
      '        <div class="comboHeading">' . "\n" . 
      '          <div class="smallText">' . TEXT_SELECT_MULTIENTRIES . '</div>' . "\n" . 
      '        </div>' . "\n" . 
      '        <div class="formArea">' . tep_draw_form('mc', FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=insert_multi_entries', 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent"><a href="javascript:void(0)" onclick="copy_checkboxes(document.mc, \'script\')" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></td>' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_SCRIPTS . '</td>' . "\n" . 
      '            </tr>' . "\n"; 
      $rows = 0;

      $script_query_raw = "select meta_scripts_key from " . TABLE_META_SCRIPTS . "";
      $total_items = $g_db->query_to_array($script_query_raw, 'meta_scripts_key');
      for( $i=0, $j=count($this->scripts_array); $i<$j; $i++) {
        $key = $this->scripts_array[$i]['id'];
        $value = $this->scripts_array[$i]['text'];
        $bCheck = false;
        if( isset($total_items[$key]) ) {
          $bCheck = true;;
        }

        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if($bCheck)
          $row_class = 'dataTableRowGreen';

        $html_string .=
        '            <tr class="' . $row_class . '">' . "\n" . 
        '              <td class="dataTableContent">' . ($bCheck?'Included':tep_draw_checkbox_field('script[' . $key . ']')) . '</td>' . "\n" . 
        '              <td class="dataTableContent">' . tep_draw_hidden_field('file[' . $key . ']', $this->files_array[$key]) . tep_draw_hidden_field('name[' . $key . ']', substr($value, 0, -4)) . $value . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .=
      '              <tr>' . "\n" . 
      '                <td colspan="3" class="formButtons"><a href="' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action', 'mcpage')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_draw_hidden_field('multi_form', 'insert_multi_entries') . '&nbsp;' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '</td>' . "\n" . 
      '              </tr>' . "\n" . 
      '            </table></form></div>' . "\n";
      return $html_string;
    }

    function display_delete_multizone() {
      global $g_db;

      $html_string = '';
      $zone_query = $g_db->query("select meta_types_name from " . TABLE_META_TYPES . " where meta_types_id = '" . (int)$this->m_zID . "'");
      $zone_array = $g_db->fetch_array($zone_query);
      $html_string .= 
      '          <div class="comboHeading">' . "\n" . 
      '            <div class="smallText">' .  sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zone_array['meta_types_name']) . '</div>' . "\n" . 
      '          </div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=deleteconfirm_multizone', 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_SCRIPTS . '</td>' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_NAME . '</td>' . "\n" . 
      '            </tr>' . "\n";
      $rows = 0;
      foreach ($_POST['pc_id'] as $pc_id => $val) {
        $md5_key = $pc_id;
        $delete_query = $g_db->query("select meta_scripts_file, meta_title from " . TABLE_META_SCRIPTS . " where meta_scripts_key = '" . $g_db->filter($md5_key) . "' order by meta_scripts_file");

        if( $g_db->num_rows($delete_query) ) {
          $delete_array = $g_db->fetch_array($delete_query);
        } else {
          $delete_array = array(
                                'final_name' => 'N/A',
                                'meta_title' => $key,
                               );
        }

        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        $html_string .= 
        '          <tr class="' . $row_class . '">' . "\n" . 
        '            <td class="dataTableContent">' . tep_draw_hidden_field('pc_id[' . $pc_id . ']', $pc_id) . $delete_array['meta_scripts_file'] . '</td>' . "\n" . 
        '            <td class="dataTableContent">' . $delete_array['meta_title'] . '</td>' . "\n" . 
        '          </tr>' . "\n";
      }
      if( count($_POST['pc_id']) ) {
        $html_string .= 
        '            <tr>' . "\n" . 
        '              <td colspan="4" class="formButtons"><a href="' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '          </table></form></div>' . "\n";
      return $html_string;
    }
  }
?>