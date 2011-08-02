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

    // Compatibility Constructor
    function meta_scripts() {
      $this->error_array = array();
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
      extract(tep_load('database'));

      foreach($this->scripts_array as $key => $value) {
        $script = substr($value['text'], 0, -4);
        $script = $this->create_safe_string($script);
        $md5_key = md5($script);

        $check_query = $db->query("select count(*) as total from " . TABLE_META_LEXICO . " where meta_lexico_key = '" . $db->filter($value['id']) . "'");
        $check_array = $db->fetch_array($check_query);
        if( !$check_array['total'] ) {
          $sql_data_array = array(
            'meta_lexico_key' => $db->prepare_input($md5_key),
            'meta_lexico_text' => $db->prepare_input($script)
          );
          $db->perform(TABLE_META_LEXICO, $sql_data_array);
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
      extract(tep_load('defs'));

      $this->error_array = array();
      return $this->error_array;
    }

    function validate_confirm() {
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=validate'));
    }

    function update_multizone() {
      extract(tep_load('defs', 'database'));

      foreach ($_POST['pc_id'] as $pc_id => $val) {
        $md5_key = $pc_id;
        $sql_data_array = array(
          'meta_title' => $db->prepare_input($_POST['title'][$pc_id]),
          'meta_keywords' => $db->prepare_input($_POST['keywords'][$pc_id]),
          'meta_text' => $db->prepare_input($_POST['desc'][$pc_id]),
        );

        $db->perform(TABLE_META_SCRIPTS, $sql_data_array, 'update', "meta_scripts_key = '" . $db->filter($md5_key) . "'");
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
    }


    function insert_multi_entries() {
      extract(tep_load('defs', 'database', 'message_stack'));

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');
      switch( $multi_form ) {
        case 'insert_multi_entries':
          $tmp_array = array();
          foreach ($_POST['script'] as $key=>$value) {
            $check_query = $db->query("select meta_scripts_key from " . TABLE_META_SCRIPTS . " where meta_scripts_key = '" . $db->filter($key) . "'");
            if( $db->num_rows($check_query) > 0 ) continue;

            $meta_name = $this->create_safe_string($_POST['name'][$key]);
            $meta_file = $_POST['file'][$key];
            $meta_keywords = $this->create_keywords_string($meta_name);

            $sql_data_array = array(
              'meta_scripts_key' => $db->prepare_input($key),
              'meta_scripts_file' => $db->prepare_input($meta_file),
              'meta_title' => $db->prepare_input($meta_name),
              'meta_keywords' => $db->prepare_input($meta_keywords),
              'meta_text' => $db->prepare_input($meta_name),
            );
            $db->perform(TABLE_META_SCRIPTS, $sql_data_array);
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

      foreach ($_POST['pc_id'] as $pc_id=>$val) {
        $md5_key = $pc_id;
        $db->query("delete from " . TABLE_META_SCRIPTS . " where meta_scripts_key = '" . $db->filter($md5_key) . "'");
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
      '            <td><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a></td>' . "\n" . 
      '          </tr>' . "\n" . 
      '        </table></td>' . "\n" . 
      '      </tr>' . "\n";
      return $html_string;
    }


// Default List
    function display_list() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $rows = 0;
      $zones_query_raw = "select * from " . TABLE_META_SCRIPTS . " order by meta_scripts_file";
      $zones_split = new splitPageResults($zones_query_raw, META_PAGE_SPLIT, '', 'spage');

      if( $zones_split->number_of_rows > 0 ) {

        if(empty($this->m_saction)) {
          $buttons = array(
            '<a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>',
            tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=update_multizone') . '\'' . '"'),
            tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=delete_multizone') . '\'' . '"'),
          );
        }

        $html_string .= 
        '        <div class="formArea">' . tep_draw_form('rl', $cDefs->script, 'action=delete_multizone&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage . '&spage=' . $this->m_spage, 'post') . '<table class="tabledata">' . "\n" ;
        $html_string .= 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <th class="calign"><a href="#pc_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
        '            <th>' . TABLE_HEADING_TITLE_FILE . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_KEYWORDS . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_DESCRIPTION . '</th>' . "\n" . 
        '          </tr>' . "\n";

        $zones_query = $db->query($zones_split->sql_query);
        $bCheck = false;
        while ($zones_array = $db->fetch_array($zones_query)) {
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="calign">' . tep_draw_checkbox_field('pc_id[' . $zones_array['meta_scripts_key'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '            <td><div class="rpad">File:&nbsp;<b>' . $zones_array['meta_scripts_file'] . '</b><br />' . tep_draw_input_field('title[' . $zones_array['meta_scripts_key'] . ']', $zones_array['meta_title']) . '</div></td>' . "\n" . 
          '            <td><div class="rpad">' . tep_draw_textarea_field('keywords[' . $zones_array['meta_scripts_key'] . ']', $zones_array['meta_keywords'], '40','2') . '</div></td>' . "\n" . 
          '            <td><div class="rpad">' . tep_draw_textarea_field('desc[' . $zones_array['meta_scripts_key'] . ']', $zones_array['meta_text'], '40','2') . '</div></td>' . "\n" . 
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
        '        <div class="comboHeading">' . TEXT_INFO_NO_ENTRIES . '</div>' . "\n";
      }
      if (empty($this->saction)) {
        $html_string .= 
        '        <div class="formButtons"><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=multi_entries') . '">' . tep_image_button('button_scripts.gif', TEXT_SWITCH_SCRIPTS) . '</a></div>' . "\n";
      }
      return $html_string;
    }

    function display_multi_entries() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $html_string .=
      '        <div class="comboHeading">' . TEXT_SELECT_MULTIENTRIES . '</div>';
      $html_string .=
      '        <div class="formArea">' . tep_draw_form('mc', $cDefs->script, tep_get_all_get_params('action') . 'action=insert_multi_entries', 'post') . '<table class="tabledata">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <th class="calign"><a href="#script" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
      '              <th>' . TABLE_HEADING_SCRIPTS . '</th>' . "\n" . 
      '            </tr>' . "\n"; 

      $buttons = array(
        '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'mcpage') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_insert.gif', IMAGE_INSERT),
        tep_draw_hidden_field('multi_form', 'insert_multi_entries')
      );

      $rows = 0;

      $script_query_raw = "select meta_scripts_key from " . TABLE_META_SCRIPTS . "";
      $total_items = $db->query_to_array($script_query_raw, 'meta_scripts_key');
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
        '              <td class="calign">' . ($bCheck?'Included':tep_draw_checkbox_field('script[' . $key . ']')) . '</td>' . "\n" . 
        '              <td>' . tep_draw_hidden_field('file[' . $key . ']', $this->files_array[$key]) . tep_draw_hidden_field('name[' . $key . ']', substr($value, 0, -4)) . $value . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .=
      '            </table><div class="formButtons">' . implode('', $buttons) . '</div></form></div>' . "\n";
      return $html_string;
    }

    function display_delete_multizone() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $zone_query = $db->query("select meta_types_name from " . TABLE_META_TYPES . " where meta_types_id = '" . (int)$this->m_zID . "'");
      $zone_array = $db->fetch_array($zone_query);
      $html_string .= 
      '          <div class="comboHeading">' . "\n" . 
      '            <div>' .  sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zone_array['meta_types_name']) . '</div>' . "\n" . 
      '          </div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', $cDefs->script, tep_get_all_get_params('action') . 'action=deleteconfirm_multizone', 'post') . '<table class="tabledata">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <th>' . TABLE_HEADING_SCRIPTS . '</th>' . "\n" . 
      '              <th>' . TABLE_HEADING_NAME . '</th>' . "\n" . 
      '            </tr>' . "\n";

      $buttons = array(
        '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_confirm.gif', IMAGE_CONFIRM)
      );

      $rows = 0;
      foreach ($_POST['pc_id'] as $pc_id => $val) {
        $md5_key = $pc_id;
        $delete_query = $db->query("select meta_scripts_file, meta_title from " . TABLE_META_SCRIPTS . " where meta_scripts_key = '" . $db->filter($md5_key) . "' order by meta_scripts_file");

        if( $db->num_rows($delete_query) ) {
          $delete_array = $db->fetch_array($delete_query);
        } else {
          $delete_array = array(
            'final_name' => TEXT_INFO_NA,
            'meta_title' => $key,
          );
        }

        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        $html_string .= 
        '          <tr class="' . $row_class . '">' . "\n" . 
        '            <td>' . tep_draw_hidden_field('pc_id[' . $pc_id . ']', $pc_id) . $delete_array['meta_scripts_file'] . '</td>' . "\n" . 
        '            <td>' . $delete_array['meta_title'] . '</td>' . "\n" . 
        '          </tr>' . "\n";
      }
      $html_string .= 
      '          </table><div class="formButtons">' . implode('', $buttons) . '</div></form></div>' . "\n";
      return $html_string;
    }
  }
?>