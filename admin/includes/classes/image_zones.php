<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
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

    // Compatibility constructor
    function image_zones() {}

    function initialize() {
      extract(tep_load('defs'));

      $this->m_spage = isset($_GET['spage'])?$_GET['spage']:'';
      $this->m_sID = isset($_GET['sID'])?$_GET['sID']:'';
      parent::initialize();

      if (isset($_POST['delete_multizone_x']) || isset($_POST['delete_multizone_y'])) $this->m_action='delete_multizone';
      if (isset($_POST['update_multizone_x']) || isset($_POST['update_multizone_y'])) $this->m_action='update_multizone';

      // Include the support js files
      $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/image_control.js"></script>';
    }

    function emit_scripts() {
      extract(tep_load('defs'));
      echo '
<script language="javascript" type="text/javascript">
$(document).ready(function(){
  var jqWrap = image_control;
  jqWrap.baseFront = \'' . $cDefs->crelpath . '\';
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
      extract(tep_load('defs', 'database'));

      foreach ($_POST['gt_id'] as $image_key => $val) {
        $sql_data_array = array(
          'image_title' => $db->prepare_input($_POST['short_desc'][$image_key]),
          'image_alt_title' => $db->prepare_input($_POST['alt_title'][$image_key]),
          'sequence_order' => (int)$_POST['seq'][$image_key],
        );

        $db->perform(TABLE_IMAGE_ZONES, $sql_data_array, 'update', "abstract_zone_id = '" . (int)$this->m_zID . "' and image_key = '" . $db->filter($image_key) . "'");
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
    }


    function insert_multi_entries() {
      extract(tep_load('defs', 'database'));

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');
      switch( $multi_form ) {
        case 'insert_multi_entries':
          $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
          foreach($_POST['gt_id'] as $filekey => $filename) {
            $check_query = $db->query("select image_key from " . TABLE_IMAGE_ZONES . " where image_key = '" . $db->filter($filekey) . "' and abstract_zone_id = '" . (int)$this->m_zID . "'");
            if( $db->num_rows($check_query) )
              continue;

            if( !file_exists($images_path . $filename) ) {
              continue;
            }

            $seq = (isset($_POST['seq']) && isset($_POST['seq'][$filekey]))?$_POST['seq'][$filekey]:1;

            $sql_data_array = array(
                                    'abstract_zone_id' => (int)$this->m_zID,
                                    'image_key' => $db->prepare_input($filekey),
                                    'image_file' => $db->prepare_input($filename),
                                    'image_title' => $db->prepare_input(basename($filename)),
                                    'image_alt_title' => $db->prepare_input(basename($filename)),
                                    'sequence_order' => (int)$seq,
                                   );
            $db->perform(TABLE_IMAGE_ZONES, $sql_data_array);
          }
          tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action', 'sID') . 'action=list'));
          break;

        default:
          break;
      }
    }

    function deleteconfirm_multizone() {
      extract(tep_load('defs', 'database'));

      foreach($_POST['gt_id'] as $image_key => $val) {
        $db->query("delete from " . TABLE_IMAGE_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "' and image_key = '" . $db->filter($image_key) . "'");
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
    }

    function deleteconfirm_zone() {
      extract(tep_load('defs', 'database'));

      $db->query("delete from " . TABLE_IMAGE_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "'");
      parent::deleteconfirm_zone();
      tep_redirect(tep_href_link($cDefs->script));
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
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $rows = 0;
      $zones_query_raw = "select image_key, image_title, image_alt_title, image_file, sequence_order from " . TABLE_IMAGE_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "' order by sequence_order, image_alt_title";
      $zones_split = new splitPageResults($zones_query_raw, ABSTRACT_PAGE_SPLIT, '', 'spage');

      $html_string .= 
      '        <div class="comboHeading splitLine">' . "\n" . 
      '          <div class="floater" style="padding-right: 8px;"><b>' . TEXT_INFO_UPLOAD_IMAGES . ':</b></div>' . "\n" . 
      '          <div class="floater"><a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a></div>' . "\n" .
      '        </div>' . "\n" .
      '        <div class="comboHeading">' . "\n" .
      '          <div class="dataTableRowAlt3 spacer floater"><a class="blockbox" href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=multi_entries') . '">' . TEXT_INFO_ASSIGN_IMAGE_ZONES . '</a></div>'. "\n" . 
      '          <div class="spacer">' . TEXT_INFO_ASSIGN_IMAGE_HELP . '</div>' . "\n" . 
      '        </div>'. "\n";

      if( $zones_split->number_of_rows > 0 ) {
        $buttons = array();
        if(empty($this->m_saction)) {
          $buttons = array(
            '<a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>',
            tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'class="dflt" name="update_multizone"'),
            tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'class="dflt" name="delete_multizone"'),
          );
        }

        $html_string .= 
        '        <div class="formArea">' . tep_draw_form('rl', $cDefs->script, 'action=delete_multizone&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage . '&spage=' . $this->m_spage, 'post') . '<table class="tabledata">' . "\n" . 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <th class="calign"><a href="#gt_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
        '            <th>' . TABLE_HEADING_ALT_TITLE . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_DESC . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_FILE . '</th>' . "\n" . 
        '            <th class="calign">' . TABLE_HEADING_SEQUENCE_ORDER . '</th>' . "\n" . 
        '          </tr>' . "\n";
        $zones_query = $db->query($zones_split->sql_query);
        $bCheck = false;
        $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
        while( $zones_array = $db->fetch_array($zones_query) ) {
          if( !file_exists($images_path . $zones_array['image_file']) ) {
            $final_name = '<font color="FF0000">' . TEXT_INFO_NA . '&nbsp;[' . $zones_array['image_alt_title'] . ']' . '</font>';
          } else {
            $final_name = '<a href="' . $cDefs->cserver . DIR_WS_CATALOG_IMAGES . $zones_array['image_file'] . '" target="_blank">' . $zones_array['image_file'] . '</a>';
          }

          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';


          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="calign">' . tep_draw_checkbox_field('gt_id[' . $zones_array['image_key'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '            <td><div class="rpad">' . tep_draw_input_field('alt_title[' . $zones_array['image_key'] . ']', $zones_array['image_alt_title'], 'maxlength="255"') . '</div></td>' . "\n" . 
          '            <td><div class="rpad">' . tep_draw_input_field('short_desc[' . $zones_array['image_key'] . ']', $zones_array['image_title'], 'maxlength="255"') . '</div></td>' . "\n" . 
          '            <td>' . $final_name . '</td>' . "\n" . 
          '            <td class="calign">' . tep_draw_input_field('seq[' . $zones_array['image_key'] . ']', $zones_array['sequence_order'], 'size="3" maxlength="3"') . '</td>' . "\n" . 
          '          </tr>'  . "\n";
        }
        $html_string .= 
        '          </table><div class="formButtons">' . implode('', $buttons) . '</div></form></div>' . "\n" . 
        '          <div class="listArea splitLine">' . "\n" . 
        '            <div class="floater">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
        '            <div class="floatend">' . $zones_split->display_links(tep_get_all_get_params('action', 'spage') . 'action=list') . '</div>' . "\n" . 
        '          </div>' . "\n";
      }
      return $html_string;
    }


    function display_multi_entries() {
      extract(tep_load('defs', 'database'));

      clearstatcache();
      $html_string = '';
      $html_string .=
      '        <div class="comboHeading splitLine">' . "\n" . 
      '          <div class="floater" style="padding-right: 8px;"><b>' . TEXT_INFO_UPLOAD_IMAGES . ':</b></div>' . "\n" . 
      '          <div class="floater"><a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a></div>' . "\n" .
      '        </div>' . "\n" .
      '        <div class="comboHeading">' . "\n" . 
      '          <div>' . TEXT_SELECT_MULTIENTRIES . '</div>' . "\n" . 
      '        </div>' . "\n" . 
      '        <div class="formArea">' . tep_draw_form('mc', $cDefs->script, tep_get_all_get_params('action') . 'action=insert_multi_entries', 'post') . '<table class="tabledata">' . "\n" . 
      '          <tr class="dataTableHeadingRow">' . "\n" . 
      '            <th class="calign"><a href="#gt_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
      '            <th>' . TABLE_HEADING_ENTRIES . tep_draw_hidden_field('multi_form', 'insert_multi_entries') . '</th>' . "\n" . 
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
          '              <td class="calign"><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('sID') . 'sID=' . $attr ) . '">' . tep_image(DIR_WS_ICONS . 'icon_folder.png', $dirname) . '</a></td>' . "\n" . 
          '              <td><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('sID') . 'sID=' . $attr ) . '">' . $dirname . '</a></td>' . "\n" . 
          '              <td>' . $mod_time . '</td>' . "\n" . 
          '            </tr>' . "\n";
        }
      }

      $bCheck = false;
      foreach( $files_array as $filekey => $filename ) {
        $check_query = $db->query("select abstract_zone_id, image_key, image_title from " . TABLE_IMAGE_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "' and image_key = '" . $db->filter(md5($filename)) . "'");
        $bCheck  = $db->num_rows($check_query)?true:false;

        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if($bCheck) {
          $row_class = 'dataTableRowGreen';
        }
        $final_name = basename($filename);
        $mod_time = date("m/d/Y H:i:s", filemtime($images_path . $filename));
        $html_string .=
        '            <tr class="' . $row_class . '">' . "\n" . 
        '              <td class="calign">' . ($bCheck?'Included':tep_draw_checkbox_field('gt_id[' . md5($filename) . ']', $filename)) . '</td>' . "\n" . 
        '              <td><a href="' . $cDefs->cserver . DIR_WS_CATALOG_IMAGES . $filename . '" target="_blank">' . $final_name . '</a></td>' . "\n" . 
        '              <td>' . $mod_time . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $buttons = array(
        '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'sID') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_insert.gif', IMAGE_INSERT),
      );
      $html_string .=
      '            </table><div class="formButtons">' . implode('', $buttons) . '</div></form></div>' . "\n" . 
      '            <div class="listArea splitLine">' . "\n" . 
      '              <div class="floater">' . sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, 1, count($files_array), count($files_array)) . '</div>' . "\n" . 
      '            </div>' . "\n";
      return $html_string;
    }

    function display_delete_multizone() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $zones_query = $db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "'");
      $zones_array = $db->fetch_array($zones_query);
      $html_string .= 
      '          <div class="comboHeading">' . sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zones_array['abstract_zone_name']) . '</div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', $cDefs->script, tep_get_all_get_params('action') . 'action=deleteconfirm_multizone', 'post') . '<table class="tabledata">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <th>' . TABLE_HEADING_ENTRIES . '</th>' . "\n" . 
      '              <th>' . TABLE_HEADING_FILE . '</th>' . "\n" . 
      '            </tr>' . "\n";

      $buttons = array(
        '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
      );

      $rows = 0;
      foreach( $_POST['gt_id'] as $image_key => $val ) {
        $delete_query = $db->query("select image_key, image_title, image_file from " . TABLE_IMAGE_ZONES . " where image_key = '" . $db->filter($image_key) . "' and abstract_zone_id = '" . (int)$this->m_zID . "' order by image_alt_title");
        if( $db->num_rows($delete_query) ) {
          $delete_array = $db->fetch_array($delete_query);
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td>' . tep_draw_hidden_field('gt_id[' . $delete_array['image_key'] . ']', $delete_array['image_key']) . $delete_array['image_title'] . '</td>' . "\n" . 
          '            <td>' . $delete_array['image_file'] . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
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