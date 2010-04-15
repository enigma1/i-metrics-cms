<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Image Zones class
//----------------------------------------------------------------------------
// This is a Bridge for Abstract Zones Class
// Groups and serializes images
// Featuring:
// - Multi-Images instant selection/insertion
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
  class image_zones extends abstract_zones {
    var $m_sID, $m_spage;
// class constructor
    function image_zones() {
      global $g_media;

      $this->m_spage = isset($_GET['spage'])?$_GET['spage']:'';
      $this->m_sID = isset($_GET['sID'])?$_GET['sID']:'';
      parent::abstract_zones();

      if (isset($_POST['delete_multizone_x']) || isset($_POST['delete_multizone_y'])) $this->m_action='delete_multizone';
      if (isset($_POST['update_multizone_x']) || isset($_POST['update_multizone_y'])) $this->m_action='update_multizone';

      // Include the support js files
      $g_media[] = '<link rel="stylesheet" type="text/css" href="includes/javascript/jquery/themes/smoothness/ui.all.css">';
      $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery-1.3.2.js"></script>';
      $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.ajaxq.js"></script>';
      $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.form.js"></script>';
      $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/ui/jquery-ui-1.7.2.custom.js"></script>';
      $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/image_control.js"></script>';
    }

    function emit_scripts() {
      global $g_cserver, $g_crelpath;
      echo '
<script language="javascript" type="text/javascript">
$(document).ready(function(){
  var jqWrap = image_control;
  jqWrap.baseFront = \'' . $g_crelpath . '\';
  jqWrap.baseURL = \'' . tep_href_link(FILENAME_JS_MODULES) . '\';
  jqWrap.launch();
});
</script>' . "\n";
    }

    function process_action() {
      switch( $this->m_action ) {
        case 'update_multizone':
          $result = parent::validate_array_selection('gt_id'); 
          return $this->update_multizone();
        case 'insert_multi_entries':
          $result = parent::validate_array_selection('gt_id', 'multi_entries');
          return $this->insert_multi_entries();
        case 'deleteconfirm_multizone':
          $result = parent::validate_array_selection('gt_id'); 
          return $this->deleteconfirm_multizone();
        case 'deleteconfirm_zone':
          return $this->deleteconfirm_zone();
        case 'delete_multizone':
          $result = parent::validate_array_selection('gt_id'); 
          break;
        default:
          return parent::process_action(); 
          break;
      }
    }

    function update_multizone() {
      global $g_db;

      foreach ($_POST['gt_id'] as $image_key => $val) {
        $sql_data_array = array(
                                'image_title' => $g_db->prepare_input($_POST['short_desc'][$image_key]),
                                'image_alt_title' => $g_db->prepare_input($_POST['alt_title'][$image_key]),
                                'sequence_order' => (int)$_POST['seq'][$image_key],
                               );

        $g_db->perform(TABLE_IMAGE_ZONES, $sql_data_array, 'update', "abstract_zone_id = '" . (int)$this->m_zID . "' and image_key = '" . $g_db->filter($image_key) . "'");
      }
      tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
    }


    function insert_multi_entries() {
      global $g_db;

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');
      switch( $multi_form ) {
        case 'insert_multi_entries':
          $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
          foreach($_POST['gt_id'] as $filekey => $filename) {
            $check_query = $g_db->query("select image_key from " . TABLE_IMAGE_ZONES . " where image_key = '" . $g_db->filter($filekey) . "' and abstract_zone_id = '" . (int)$this->m_zID . "'");
            if( $g_db->num_rows($check_query) )
              continue;

            if( !file_exists($images_path . $filename) ) {
              continue;
            }

            $seq = (isset($_POST['seq']) && isset($_POST['seq'][$filekey]))?$_POST['seq'][$filekey]:1;

            $sql_data_array = array(
                                    'abstract_zone_id' => (int)$this->m_zID,
                                    'image_key' => $g_db->prepare_input($filekey),
                                    'image_file' => $g_db->prepare_input($filename),
                                    'image_title' => $g_db->prepare_input(basename($filename)),
                                    'image_alt_title' => $g_db->prepare_input(basename($filename)),
                                    'sequence_order' => (int)$seq,
                                   );
            $g_db->perform(TABLE_IMAGE_ZONES, $sql_data_array);
          }
          tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action', 'sID')) . 'action=list'));
          break;

        default:
          break;
      }
    }

    function deleteconfirm_multizone() {
      global $g_db;

      foreach($_POST['gt_id'] as $image_key => $val) {
        $g_db->query("delete from " . TABLE_IMAGE_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "' and image_key = '" . $g_db->filter($image_key) . "'");
      }
      tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
    }

    function deleteconfirm_zone() {
      global $g_db;

      $g_db->query("delete from " . TABLE_IMAGE_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "'");
      parent::deleteconfirm_zone();
      tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES));
    }

    function display_html() {
      switch( $this->m_action ) {
        case 'list':
          $result = $this->display_list();
          break;
        case 'multi_entries':
          $result = $this->display_multi_entries();
          break;
        case 'delete_multizone':
          $result = $this->display_delete_multizone();
          break;
        case 'apply_multizone':
          $result = $this->display_apply_multizone();
          break;
        default:
          $result = $this->display_default();
          $result .= $this->display_bottom();
          break;
      }
      return $result;
    }

// List of entries
    function display_list() {
      global $g_db, $g_cserver;

      $html_string = '';
      $rows = 0;
      $zones_query_raw = "select image_key, image_title, image_alt_title, image_file, sequence_order from " . TABLE_IMAGE_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "' order by sequence_order, image_alt_title";
      $zones_split = new splitPageResults($zones_query_raw, ABSTRACT_PAGE_SPLIT, '', 'spage');

      if( $zones_split->number_of_rows > 0 ) {
        $html_string .= 
        '        <div class="formArea">' . tep_draw_form('rl', FILENAME_ABSTRACT_ZONES, 'action=delete_multizone&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage . '&spage=' . $this->m_spage, 'post') . '<table class="tabledata" cellspacing="1">' . "\n";

        if(empty($this->m_saction)) {

          $html_string .= 
          '          <tr>' . "\n" . 
          '            <td colspan="5" class="smallText"><div class=splitLine><div class="floater" style="padding-right: 8px;"><b>' . TEXT_INFO_UPLOAD_IMAGES . ':</b></div><div class="floater"><a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a></div>' . "\n" .
          '              <div id="modalBox" title="Image Selection" style="display:none;">Loading...Please Wait</div>' . "\n" . 
          '              <div id="ajaxLoader" title="Image Manager" style="display:none;"><img src="includes/javascript/jquery/themes/smoothness/images/ajax_load.gif"><p id="ajaxMsg" class="main">Updating, please wait...</p><hr /></div>' . "\n" . 
          '            </td>' . "\n" . 
          '          </tr>' . "\n" . 
          '          <tr>' . "\n" . 
          '            <td colspan="5" class="formButtons"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'class="dflt" name="update_multizone"') . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'class="dflt" name="delete_multizone"') . '</td>' . "\n" . 
          '          </tr>' . "\n"; 
        }
        $html_string .= 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <th><a href="javascript:void(0)" onclick="check_boxes(document.rl)" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
        '            <th>' . TABLE_HEADING_ALT_TITLE . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_DESC . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_FILE . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_SEQUENCE_ORDER . '</th>' . "\n" . 
        '          </tr>' . "\n";
        $zones_query = $g_db->query($zones_split->sql_query);
        $bCheck = false;
        $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
        while( $zones_array = $g_db->fetch_array($zones_query) ) {
          if( !file_exists($images_path . $zones_array['image_file']) ) {
            $final_name = '<font color="FF0000">' . TEXT_INFO_NA . '&nbsp;[' . $zones_array['image_alt_title'] . ']' . '</font>';
          } else {
            $final_name = '<a href="' . $g_cserver . DIR_WS_CATALOG_IMAGES . $zones_array['image_file'] . '" target="_blank">' . $zones_array['image_file'] . '</a>';
          }

          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';


          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td>' . tep_draw_checkbox_field('gt_id[' . $zones_array['image_key'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '            <td>' . tep_draw_input_field('alt_title[' . $zones_array['image_key'] . ']', $zones_array['image_alt_title'], 'size="24" maxlength="255"') . '</td>' . "\n" . 
          '            <td>' . tep_draw_input_field('short_desc[' . $zones_array['image_key'] . ']', $zones_array['image_title'], 'size="24" maxlength="255"') . '</td>' . "\n" . 
          '            <td>' . $final_name . '</td>' . "\n" . 
          '            <td>' . tep_draw_input_field('seq[' . $zones_array['image_key'] . ']', $zones_array['sequence_order'], 'size="3" maxlength="3"') . '</td>' . "\n" . 
          '          </tr>'  . "\n";
        }
        if(empty($this->m_saction)) {
          $html_string .= 
          '          <tr>' . "\n" . 
          '            <td colspan="5" class="formButtons"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'class="dflt" name="update_multizone"') . ' ' . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'class="dflt" name="delete_multizone"') . '</td>' . "\n" . 
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
      if (empty($this->m_saction)) {
        $html_string .= 
        '        <div class="formButtons"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=multi_entries') . '">' . tep_image_button('button_entries.gif', 'Switch to Generic Entries Mode') . '</a></div>' . "\n";
      }
      return $html_string;
    }


    function display_multi_entries() {
      global $g_db, $g_cserver;
      clearstatcache();
      $html_string = '';
      $html_string .=
      '        <div class="comboHeading">' . "\n" . 
      '          <div class="smallText">' . TEXT_SELECT_MULTIENTRIES . '</div>' . "\n" . 
      '        </div>' . "\n" . 
      '        <div class="formArea">' . tep_draw_form('mc', FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=insert_multi_entries', 'post') . '<table class="tabledata" cellspacing="1">' . "\n" . 
      '          <tr>' . "\n" . 
      '            <td colspan="5" class="smallText"><div class=splitLine><div style="float: left; padding-right: 8px;"><b>' . TEXT_INFO_UPLOAD_IMAGES . ':</b></div><div style="float: left"><a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a></div>' . "\n" .
      '              <div id="modalBox" title="Image Selection" style="display:none;">Loading...Please Wait</div>' . "\n" . 
      '              <div id="ajaxLoader" title="Image Manager" style="display:none;"><img src="includes/javascript/jquery/themes/smoothness/images/ajax_load.gif"><p id="ajaxMsg" class="main">Updating, please wait...</p><hr /></div>' . "\n" . 
      '            </td>' . "\n" . 
      '          </tr>' . "\n" . 
      '          <tr class="dataTableHeadingRow">' . "\n" . 
      '            <th><a href="javascript:void(0)" onclick="check_boxes(document.mc)" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
      '            <th>' . TABLE_HEADING_ENTRIES . '</th>' . "\n" . 
      '            <th>' . TABLE_HEADING_LAST_MODIFIED . '</th>' . "\n" . 
      '          </tr>' . "\n"; 
      $rows = 0;

      $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
      $switch_folder = $images_path . $this->m_sID;
      $current_dir = getcwd();
      $dir = dir($switch_folder);

      chdir($switch_folder);
      $files_array = array();
      $subdirs_array = array();

      if( !empty($this->m_sID) ) {
        $subdirs_array[] = '';
      }

      while(false !== ($script = $dir->read()) ) {
        if( substr($script, 0, 1) != '.' && is_dir($script) ) {
          $subdirs_array[] = $switch_folder . $script;
        } elseif( substr($script, 0, 1) != '.' && !is_dir($script) ) {
          if( !empty($this->m_sID) ) {
            $files_array[] = $this->m_sID . '/' . $script;
          } else {
            $files_array[] = $script;
          }
        }
      }
      chdir($current_dir);
      sort($subdirs_array, SORT_STRING);
      sort($files_array, SORT_STRING);

      $j = count($subdirs_array);
      if( $j ) {
        foreach( $subdirs_array as $dirkey => $dirname ) {
          if( empty($dirname) ) {
            $tmp_array = explode('/', $this->m_sID);
            array_pop($tmp_array);
            if( count($tmp_array) ) {
              array_pop($tmp_array);
            }
            $attr = implode('/', $tmp_array);
            $dirname = TEXT_INFO_UP_ONE_LEVEL;
            $folder_image = 'folder_up.png';
          } else {
            $attr = trim(basename($dirname), ' /');
            $folder_image = 'folder_image.png';

            if( !empty($this->m_sID) ) {
              $attr = $this->m_sID . $attr;
            }
            $dirname = $attr;
          }

          $rows++;
          $row_class = ($rows%2)?'dataTableRowYellow':'dataTableRowYellowLow';
          if( !empty($attr) ) {
            $mod_time = date("m/d/Y H:i:s", filemtime($images_path . $attr));
          } else {
            $mod_time = TEXT_INFO_NA;
          }
          $html_string .=
          '            <tr class="' . $row_class . '">' . "\n" . 
          '              <td><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('sID')) . 'sID=' . $attr ) . '">' . tep_image(DIR_WS_ICONS . 'icon_folder.png', $dirname) . '</a></td>' . "\n" . 
          '              <td><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('sID')) . 'sID=' . $attr ) . '">' . $dirname . '</a></td>' . "\n" . 
          '              <td>' . $mod_time . '</td>' . "\n" . 
          '            </tr>' . "\n";
        }
      }

      $bCheck = false;
      foreach( $files_array as $filekey => $filename ) {
        $check_query = $g_db->query("select abstract_zone_id, image_key, image_title from " . TABLE_IMAGE_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "' and image_key = '" . $g_db->filter(md5($filename)) . "'");
        $bCheck  = $g_db->num_rows($check_query)?true:false;

        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if($bCheck) {
          $row_class = 'dataTableRowGreen';
        }
        $final_name = basename($filename);
        $mod_time = date("m/d/Y H:i:s", filemtime($images_path . $filename));
        $html_string .=
        '            <tr class="' . $row_class . '">' . "\n" . 
        '              <td>' . ($bCheck?'Included':tep_draw_checkbox_field('gt_id[' . md5($filename) . ']', $filename)) . '</td>' . "\n" . 
        '              <td><a href="' . $g_cserver . DIR_WS_CATALOG_IMAGES . $filename . '" target="_blank">' . $final_name . '</a></td>' . "\n" . 
        '              <td>' . $mod_time . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .=
      '              <tr>' . "\n" . 
      '                <td colspan="3" class="formButtons"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action', 'sID')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_draw_hidden_field('multi_form', 'insert_multi_entries') . '&nbsp;' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '</td>' . "\n" . 
      '              </tr>' . "\n" . 
      '            </table></form></div>' . "\n" . 
      '            <div class="splitLine">' . "\n" . 
      '              <div style="float: left;">' . sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, 1, count($files_array), count($files_array)) . '</div>' . "\n" . 
      '            </div>' . "\n";
      return $html_string;
    }

    function display_delete_multizone() {
      global $g_db;

      $html_string = '';
      $zones_query = $g_db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "'");
      $zones_array = $g_db->fetch_array($zones_query);
      $html_string .= 
      '          <div class="comboHeading">' . "\n" . 
      '            <div class="smallText">' . sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zones_array['abstract_zone_name']) . '</div>' . "\n" . 
      '          </div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=deleteconfirm_multizone', 'post') . '<table class="tabledata" cellspacing="1">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <th>' . TABLE_HEADING_ENTRIES . '</th>' . "\n" . 
      '              <th>' . TABLE_HEADING_FILE . '</th>' . "\n" . 
      '            </tr>' . "\n";
      $rows = 0;
      foreach( $_POST['gt_id'] as $image_key => $val ) {
        $delete_query = $g_db->query("select image_key, image_title, image_file from " . TABLE_IMAGE_ZONES . " where image_key = '" . $g_db->filter($image_key) . "' and abstract_zone_id = '" . (int)$this->m_zID . "' order by image_alt_title");
        if( $g_db->num_rows($delete_query) ) {
          $delete_array = $g_db->fetch_array($delete_query);
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td>' . tep_draw_hidden_field('gt_id[' . $delete_array['image_key'] . ']', $delete_array['image_key']) . $delete_array['image_title'] . '</td>' . "\n" . 
          '            <td>' . $delete_array['image_file'] . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
      }
      if( count($_POST['gt_id']) ) {
        $html_string .= 
        '            <tr>' . "\n" . 
        '              <td colspan="4" class="formButtons"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '            </table></form></div>' . "\n";
      return $html_string;
    }
  }
?>