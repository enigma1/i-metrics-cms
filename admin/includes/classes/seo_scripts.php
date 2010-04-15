<?php
/*
//----------------------------------------------------------------------------
//-------------- SEO-G by Asymmetrics (Renegade Edition) ---------------------
//----------------------------------------------------------------------------
// Copyright (c) 2006-2008 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO-G Scripts class for Admin
// This is a Bridge for SEO-G
// Processes filenames table generates seo urls.
//----------------------------------------------------------------------------
// I-Metrics Layer
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  class seo_scripts extends seo_zones {
    var $error_array, $scripts_array;

// class constructor
    function seo_scripts() {
      $this->m_ssID = isset($_GET['ssID'])?$_GET['ssID']:'';
      $this->m_mcpage = isset($_GET['mcpage'])?$_GET['mcpage']:'';
      $this->m_mppage = isset($_GET['mppage'])?$_GET['mppage']:'';
      parent::seo_zones();
      $this->get_scripts();
    }

    function generate_name($filename, $separator=SEO_DEFAULT_WORDS_SEPARATOR) {
      $filename = str_replace('_', $separator, $filename);
      $string = $this->create_safe_string($filename, $separator);
      return $this->adapt_lexico($string, $separator);
    }

    function get_scripts() {
      $dir = dir(DIR_FS_CATALOG);
      $this->scripts_array = array();
      $this->files_array = array();
      while ($script = $dir->read()) {
        if( strlen($script) < 5 || substr($script, -4, 4) != '.php')
          continue;

        $script = strtolower($script);
        $this->scripts_array[$script] = array(
                                              'id' => substr($script, 0, -4), 
                                              'text' => $script
                                             );
      }
      $dir->close();
      ksort($this->scripts_array, SORT_STRING);
      $this->scripts_array = array_values($this->scripts_array);
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
        case 'insert_multi_scripts':
          $result = parent::validate_array_selection('script', 'multi_scripts');
          return $this->insert_multi_scripts();
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
      $this->error_array = array();
      return $this->error_array;
    }

    function validate_confirm() {
      tep_redirect(tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=validate'));
    }

    function update_multizone() {
      global $g_db;

      foreach ($_POST['pc_id'] as $script => $val) {
        $seo_name = $this->create_safe_string($_POST['name'][$script]);
        if( SEO_PROXIMITY_CLEANUP == 'true' ) {
          $g_db->query("delete from " . TABLE_SEO_URL . " where seo_url_org like '%" . $g_db->filter($script) . "%'");
          $g_db->query("truncate table " . TABLE_SEO_CACHE . "");
        }
        $sql_data_array = array(
                                'seo_name' => $g_db->prepare_input($seo_name)
                               );

        $g_db->perform(TABLE_SEO_TO_SCRIPTS, $sql_data_array, 'update', "script = '" . $g_db->filter($script) . "'");
      }
      tep_redirect(tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
    }

    function insert_multi_scripts() {
      global $g_db, $messageStack;

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');
      switch( $multi_form ) {
        case 'insert_multi_scripts':
          $tmp_array = array();
          foreach ($_POST['script'] as $script=>$val) {
            $check_query = $g_db->query("select script from " . TABLE_SEO_TO_SCRIPTS . " where script = '" . $g_db->filter($script) . "'");
            if( $g_db->num_rows($check_query) > 0 ) continue;
            $seo_name = $this->generate_name($script);
            if( !isset($tmp_array[$seo_name]) ) {
              $tmp_array[$seo_name] = 1;
            } else {
              $tmp_array[$seo_name]++;
              $seo_name .= $tmp_array[$seo_name];
            }

            if( SEO_PROXIMITY_CLEANUP == 'true' ) {
              $g_db->query("delete from " . TABLE_SEO_URL . " where seo_url_org like '%" . $g_db->filter($script . SEO_DEFAULT_EXTENSION) . "%'");
              $g_db->query("truncate table " . TABLE_SEO_CACHE . "");
            }

            $sql_data_array = array(
                                    'script' => $g_db->prepare_input($script),
                                    'seo_name' => $g_db->prepare_input($seo_name),
                                    );
            $g_db->perform(TABLE_SEO_TO_SCRIPTS, $sql_data_array);
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
        $script = $_POST['pc_id'][$i];
        if( SEO_PROXIMITY_CLEANUP == 'true' ) {
          $check_query = $g_db->query("select seo_name from " . TABLE_SEO_TO_SCRIPTS . " where script = '" . $g_db->filter($script) . "'");
          if( $check_array = $g_db->fetch_array($check_query) ) {
            $check_name = $check_array['seo_name'];
            $g_db->query("delete from " . TABLE_SEO_URL . " where seo_url_get like '%" . $g_db->filter($check_name) . "%'");
          }
        }
        $g_db->query("delete from " . TABLE_SEO_TO_SCRIPTS . " where script = '" . $g_db->filter($script) . "'");
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
        case 'multi_scripts':
          $result = $this->display_multi_scripts();
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
      '            <td><a href="' . tep_href_link(FILENAME_SEO_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a></td>' . "\n" . 
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
      $zones_query_raw = "select script, seo_name from " . TABLE_SEO_TO_SCRIPTS . " order by script";
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
        '            <td class="dataTableHeadingContent"><a href="javascript:void(0)" onclick="copy_checkboxes(document.rl, \'pc_id\')" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_SCRIPTS . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent" align="center">' . TABLE_HEADING_NAME . '</td>' . "\n" . 
        '          </tr>' . "\n";

        $zones_query = $g_db->query($zones_split->sql_query);
        $bCheck = false;
        while( $zones_array = $g_db->fetch_array($zones_query) ) {
          $tmp_file = DIR_FS_CATALOG . $zones_array['script'] . '.php';
          if( !file_exists($tmp_file) || filesize($tmp_file) <= 0 ) {
            $final_name = '<font color="#ff0000"><b>' . TEXT_INFO_NA . ' - ' . $zones_array['script'] . '.php</b></font>';
          } else {
            $final_name = '<b>' . $tmp_file . '</b>';
          }

          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_checkbox_field('pc_id[' . $zones_array['script'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . $final_name . '</td>' . "\n" . 
          '            <td class="dataTableContent" align="center">' . tep_draw_input_field('name[' . $zones_array['script'] . ']', $zones_array['seo_name'], 'style="width: 300px"') . SEO_DEFAULT_EXTENSION . '</td>' . "\n" . 
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
      if( empty($this->saction) ) {
        $html_string .= 
        '        <div class="formButtons"><a href="' . tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=multi_scripts') . '">' . tep_image_button('button_scripts.gif', TEXT_SWITCH_SCRIPTS) . '</a></div>' . "\n";
      }
      return $html_string;
    }


    function display_multi_scripts() {
      global $g_db;

      $html_string = '';
      $html_string .=
      '        <div class="comboHeading">' . "\n" . 
      '          <div class="smallText">' . TEXT_SELECT_MULTISCRIPTS . '</div>' . "\n" . 
      '        </div>' . "\n" . 
      '        <div class="formArea">' . tep_draw_form('mc', FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=insert_multi_scripts', 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent"><a href="javascript:void(0)" onclick="copy_checkboxes(document.mc, \'script\')" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></td>' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_SCRIPTS . '</td>' . "\n" . 
      '            </tr>' . "\n"; 
      $rows = 0;

      $script_query_raw = "select script from " . TABLE_SEO_TO_SCRIPTS . "";
      $total_items = $g_db->query_to_array($script_query_raw, 'script');
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
        '              <td class="dataTableContent">' . $value . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .=
      '              <tr>' . "\n" . 
      '                <td colspan="3" class="formButtons"><a href="' . tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action', 'mcpage')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_draw_hidden_field('multi_form', 'insert_multi_scripts') . '&nbsp;' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '</td>' . "\n" . 
      '              </tr>' . "\n" . 
      '            </table></form></div>' . "\n";
      return $html_string;
    }

    function display_delete_multizone() {
      global $g_db;

      $html_string = '';
      $zone_query = $g_db->query("select seo_types_name from " . TABLE_SEO_TYPES . " where seo_types_id = '" . (int)$this->m_zID . "'");
      $zone_array = $g_db->fetch_array($zone_query);
      $html_string .= 
      '          <div class="comboHeading">' . "\n" . 
      '            <div class="smallText">' . sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zone_array['seo_types_name']) . '</div>' . "\n" . 
      '          </div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=deleteconfirm_multizone', 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_SCRIPTS . '</td>' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_NAME . '</td>' . "\n" . 
      '            </tr>' . "\n";
      $rows = 0;
      foreach ($_POST['pc_id'] as $key => $val) {
        $delete_query = $g_db->query("select script as final_name, seo_name from " . TABLE_SEO_TO_SCRIPTS . " where script = '" . $g_db->filter($key) . "'");

        if( $g_db->num_rows($delete_query) ) {
          $delete_array = $g_db->fetch_array($delete_query);
        } else {
          $delete_array = array(
                                'final_name' => 'N/A',
                                'seo_name' => $key,
                               );
        }
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        $html_string .= 
        '          <tr class="' . $row_class . '">' . "\n" . 
        '            <td class="dataTableContent">' . tep_draw_hidden_field('pc_id[]', $key) . $delete_array['final_name'] . '</td>' . "\n" . 
        '            <td class="dataTableContent">' . $delete_array['seo_name'] . '</td>' . "\n" . 
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