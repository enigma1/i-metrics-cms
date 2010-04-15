<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Abstract Zones root class
// Controls relationships among various content types, text pages etc.
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
  class abstract_zones {
   var $m_zID, $m_zpage, $m_saction, $m_action, $m_type, $m_zInfo, $m_sID, $m_spage, $m_filter;
// class constructor
    function abstract_zones() {
      global $g_db;

      $this->m_zID = isset($_GET['zID'])?(int)$_GET['zID']:'';
      $this->m_zpage = isset($_GET['zpage'])?(int)$_GET['zpage']:'';
      $this->m_saction = isset($_GET['saction'])?$g_db->prepare_input($_GET['saction']):'';
      $this->m_action = isset($_GET['action'])?$g_db->prepare_input($_GET['action']):'';
      $this->m_sID = isset($_GET['sID'])?$g_db->prepare_input($_GET['sID']):'';
      $this->m_spage = isset($_GET['spage'])?(int)$_GET['spage']:'';
      $this->m_filter = isset($_GET['filter'])?(int)$_GET['filter']:'';

      $zones_query = $g_db->fly("select abstract_types_id from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "'");
      $zones_array = $g_db->fetch_array($zones_query);
      $this->m_type = $zones_array['abstract_types_id'];
    }

    function emit_scripts() {
    }

    function is_top_level() {
      if( !isset($_GET['action']) ) {
        return true;
      }
      switch($_GET['action']) {
        case 'edit_zone':
        case 'delete_zone':
        case 'new_zone':
          return true;
      }
      return false;
    }

    function validate_array_selection($entity, $action='list') {
      global $messageStack;
      if( !isset($_POST[$entity]) || !is_array($_POST[$entity]) || !count($_POST[$entity]) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=' . $action));
      }
    }

    function process_action() {
      switch( $this->m_action ) {
        case 'set_flag':
          return $this->set_flag();
        case 'insert_zone':
          return $this->insert_zone();
        case 'save_zone':
          return $this->save_zone();
        case 'deleteconfirm_zone':
          return $this->deleteconfirm_zone();
        default:
          break;
      }
    }

    function deleteconfirm_zone($zone_id='') {
      global $g_db;

      if( empty($zone_id) ) {
        $zone_id = $this->m_zID;
      }
      // Synchronize related tables with abstract_zone_id in them
      $g_db->query("delete from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$zone_id . "'");
      $g_db->query("delete from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$zone_id . "'");
      $g_db->query("delete from " . TABLE_SUPER_ZONES . " where subzone_id = '" . (int)$zone_id . "'");
      $g_db->query("delete from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "'");
    }

    function deleteconfirm_type_zone($type_id) {
      global $g_db;
      // Synchronize related tables with abstract_types_id
      $delete_query = $g_db->query("select abstract_zone_id from " . TABLE_ABSTRACT_ZONES . " where abstract_types_id = '" . (int)$type_id . "'");
      if( !$g_db->num_rows($delete_query) ) return;
      while( $delete_array = $g_db->fetch_array($delete_query) ) {
        $this->deleteconfirm_zone($delete_array['abstract_zone_id']);
      }
      $g_db->query("delete from " . TABLE_ABSTRACT_ZONES . " where abstract_types_id = '" . (int)$type_id . "'");
    }

    function process_saction() {
      switch( $this->m_saction ) {
        default:
          break;
      }
    }

    function set_flag() {
      global $g_db;
      $flag = isset($_GET['flag'])?(int)$_GET['flag']:0;
      $g_db->query("update " . TABLE_ABSTRACT_ZONES . " set status_id = '" . (int)$flag . "' where abstract_zone_id = '" . (int)$this->m_zID . "'");
      tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action', 'zID', 'flag')) . 'zID=' . $this->m_zID));
    }

    function insert_zone() {
      global $g_db, $messageStack;

      $zone_name = $g_db->prepare_input($_POST['abstract_zone_name']);
      $check_query = $g_db->query("select count(*) as total from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_name = '" . $g_db->input($zone_name) . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( $check_array['total'] ) {
        $messageStack->add_session(sprintf(ERROR_DUPLICATE_NAME, $zone_name));
        tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action'))));
      }

      if( empty($zone_name) ) {
        $messageStack->add_session(ERROR_EMPTY_NAME);
        tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action'))));
      }
      $sql_data_array = array(
                              'abstract_zone_name' => $zone_name,
                              'abstract_zone_desc' => $g_db->prepare_input($_POST['abstract_zone_desc']),
                              'abstract_types_id' => (int)$_POST['abstract_types_id'],
                              'sort_id' => (int)$_POST['sort_id'],
                              'date_added' => 'now()',
                              'last_modified' => 'now()',
                              'status_id' => isset($_POST['status_id'])?1:0
                             );
      $g_db->perform(TABLE_ABSTRACT_ZONES, $sql_data_array);
      $new_zone_id = $g_db->insert_id();
      tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action', 'zID')) . 'zID=' . $new_zone_id));
    }

    function save_zone() {
      global $g_db, $messageStack;

      $zone_name = $g_db->prepare_input($_POST['abstract_zone_name']);
      $check_query = $g_db->query("select count(*) as total from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id != '" . (int)$this->m_zID . "' and abstract_zone_name = '" . $g_db->input($zone_name) . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( $check_array['total'] ) {
        $messageStack->add_session(sprintf(ERROR_DUPLICATE_NAME, $zone_name));
        tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action'))));
      }

      if( empty($zone_name) ) {
        $messageStack->add_session(ERROR_EMPTY_NAME);
        tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action'))));
      }
      $sql_data_array = array(
                              'abstract_zone_name' => $zone_name,
                              'abstract_zone_desc' => $g_db->prepare_input($_POST['abstract_zone_desc']),
                              'sort_id' => (int)$_POST['sort_id'],
                              'last_modified' => 'now()',
                              'status_id' => isset($_POST['status_id'])?1:0
                             );

      $messageStack->add_session(sprintf(SUCCESS_ZONE_UPDATED, $zone_name), 'success');
      $g_db->perform(TABLE_ABSTRACT_ZONES, $sql_data_array, 'update', "abstract_zone_id = '" . (int)$this->m_zID . "'");
      tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action', 'zID', 'zpage')) . 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID));
    }

    function save_sub() {
      tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES));
    }

    function redirect_default() {
      tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES));
    }


    function get_zone_name($abstract_zone_id, $default_zone='Unknown Zone') {
      global $g_db;

      $zone_query = $g_db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      if ($g_db->num_rows($zone_query)) {
        $zone = $g_db->fetch_array($zone_query);
        return $zone['abstract_zone_name'];
      } else {
        return $default_zone;
      }
    }

    function get_types() {
      global $g_db;

      $types_query_raw = "select abstract_types_id, abstract_types_name from " . TABLE_ABSTRACT_TYPES . " order by sort_order";
      $types_array = $g_db->query_to_array($types_query_raw);
      return $types_array;
    }  

    function get_zone_type($abstract_zone_id, $default_zone='Unknown Type') {
      global $g_db;

      $zone_query = $g_db->query("select at.abstract_types_name from " . TABLE_ABSTRACT_TYPES . " at left join " . TABLE_ABSTRACT_ZONES . " az on (az.abstract_types_id=at.abstract_types_id) where az.abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      if ($g_db->num_rows($zone_query)) {
        $zone = $g_db->fetch_array($zone_query);
        return $zone['abstract_types_name'];
      } else {
        return $default_zone;
      }
    }  

    function display_html() {
      $html_string = '';
      if (!$this->m_action) {
        $html_string = $this->display_default();
        if( !tep_not_null($this->m_zID) ) {

          $html_string .= 
          '             <div class="comboHeading">' . "\n" . 
          '               <div class="smallText"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&action=new_zone') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a></div>' . "\n" . 
          '             </div>' . "\n";
        }
      }
      return $html_string;
    }

    function display_default() {
      global $g_db;

      $html_string = $filter_string = '';

      $filter_array = $this->get_types();
      array_unshift($filter_array, array('id' => '', 'text' => TEXT_VIEW_ALL));
      $filter_array = tep_array_rename_elements($filter_array, array('id', 'text'));

      $html_string .= 
      '          <div class="comboHeading">'  . tep_draw_form("filter_form", FILENAME_ABSTRACT_ZONES, '', 'get') . "\n" . 
      '            <div>' . TEXT_TITLE_FILTER . '&nbsp;' . tep_draw_pull_down_menu('filter', $filter_array, $this->m_filter, 'onchange="this.form.submit()"') . "\n";
      $params_string = tep_get_all_get_params(array('zID', 'action', 'filter', 'page'));
      $params_array = tep_get_string_parameters($params_string);
      foreach($params_array as $key => $value ) {
        $html_string .=  tep_draw_hidden_field($key, $value);
      }
      $html_string .= 
      '            </div>' . "\n" . 
      '          </form></div>' . "\n";

      $html_string .= 
      '          <div class="listArea"><table class="tabledata" cellspacing="1">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <th>' . TABLE_HEADING_ABSTRACT_ZONES . '</th>' . "\n" . 
      '              <th>' . TABLE_HEADING_ABSTRACT_TYPE . '</th>' . "\n" . 
      '              <th class="calign">' . TABLE_HEADING_ABSTRACT_VISIBLE . '</th>' . "\n" . 
      '              <th class="calign">' . TABLE_HEADING_ACTION . '</th>' . "\n" . 
      '            </tr>' . "\n";

      if( !empty($this->m_filter) ) {
        $filter_string = " where abstract_types_id = '" . (int)$this->m_filter . "'";
      }
      $zones_query_raw = "select abstract_zone_id, abstract_zone_name, abstract_zone_desc, sort_id, last_modified, date_added, status_id, abstract_types_id from " . TABLE_ABSTRACT_ZONES . $filter_string . " order by sort_id, abstract_types_id, status_id desc, abstract_zone_name";
      $zones_split = new splitPageResults($zones_query_raw, ABSTRACT_PAGE_SPLIT, '', 'zpage');
      $zones_query = $g_db->query($zones_split->sql_query);
      $row_type = 0;
      $row_array = array('dataTableRowAlt2', 'dataTableRowAlt3', 'dataTableRowAlt4', 'dataTableRowAlt5');
      $row_counter = count($row_array);
      $row_class = 'dataTableRow';
      while( $zones = $g_db->fetch_array($zones_query) ) {
        $types_query = $g_db->query("select abstract_types_name, abstract_types_class, abstract_types_table from " . TABLE_ABSTRACT_TYPES . " where abstract_types_id = '" . $zones['abstract_types_id'] . "'");
        $types_array = $g_db->fetch_array($types_query);
        $zones = array_merge($zones, $types_array);

        if( $row_type != $zones['abstract_types_id'] ) {
          $row_class = $row_array[$zones['abstract_types_id']%$row_counter];
        }

        if( (!tep_not_null($this->m_zID) || (tep_not_null($this->m_zID) && ($this->m_zID == $zones['abstract_zone_id']))) && !isset($this->m_zInfo) && (substr($this->m_action, 0, 3) != 'new')) {
          $this->m_zInfo = new objectInfo($zones);
          $this->m_zID = $this->m_zInfo->abstract_zone_id;
        }

        if (isset($this->m_zInfo) && is_object($this->m_zInfo) && ($zones['abstract_zone_id'] == $this->m_zInfo->abstract_zone_id)) {
          $html_string .= 
          '          <tr class="dataTableRowSelected" onclick="document.location.href=\'' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('zpage', 'zID', 'action')) . 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zInfo->abstract_zone_id . '&action=list') . '\'">' . "\n";
        } else {
          $html_string .= 
          '          <tr class="' . $row_class . '" onclick="document.location.href=\'' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('zpage', 'zID')) . 'zpage=' . $this->m_zpage . '&zID=' . $zones['abstract_zone_id']) . '\'">' . "\n";
        }
        $html_string .= 
        '              <td><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $zones['abstract_zone_id'] . '&action=list') . '">' . tep_image(DIR_WS_ICONS . 'icon_folder.png', ICON_FOLDER) . '</a>&nbsp;' . $zones['abstract_zone_name'] . '</td>' . "\n" . 
        '              <td>' . $zones['abstract_types_name'] . '</td>' . "\n" . 
        '              <td class="tinysep calign">' . "\n";

        if( $zones['status_id'] == '1' ) {
          $html_string .= tep_image(DIR_WS_ICONS . 'icon_status_green.png', TEXT_INFO_ZONE_VISIBLE) . '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('zpage', 'zID', 'action', 'flag')) . 'zpage=' . $this->m_zpage . '&zID=' . $zones['abstract_zone_id'] . '&action=set_flag&flag=0') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
        } else {
          $html_string .= '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('zpage', 'zID', 'action', 'flag')) . 'zpage=' . $this->m_zpage . '&zID=' . $zones['abstract_zone_id'] . '&action=set_flag&flag=1') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', TEXT_INFO_ZONE_HIDDEN);
        }
        $html_string .= 
        '              </td>' . "\n" . 
        '              <td class="tinysep calign">';
        $html_string .= '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('zpage', 'zID', 'action')) . 'zpage=' . $this->m_zpage . '&zID=' . $zones['abstract_zone_id'] . '&action=delete_zone') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $zones['abstract_zone_name']) . '</a>';
        $html_string .= '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('zpage', 'zID', 'action')) . 'zpage=' . $this->m_zpage . '&zID=' . $zones['abstract_zone_id'] . '&action=edit_zone') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $zones['abstract_zone_name']) . '</a>';
        if (isset($this->m_zInfo) && is_object($this->m_zInfo) && ($zones['abstract_zone_id'] == $this->m_zInfo->abstract_zone_id) && tep_not_null($this->m_zID) ) { 
          $html_string .= tep_image(DIR_WS_ICONS . 'icon_arrow_right.png'); 
        } else { 
          $html_string .= '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $zones['abstract_zone_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
        } 
        $html_string .= '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '              <tr>' . "\n" . 
      '                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n" . 
      '                  <tr>' . "\n" . 
      '                    <td>' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ABSTRACT_ZONES) . '</td>' . "\n" . 
      '                    <td align="right">' . $zones_split->display_links(tep_get_all_get_params(array('zpage'))) . '</td>' . "\n" . 
      '                  </tr>' . "\n" . 
      '                </table></td>' . "\n" . 
      '              </tr>' . "\n" . 
      '            </table></div>' . "\n";
      return $html_string;
    }

    function display_right_box() {
      global $g_db;

      $html_string = '';

      if( !$this->is_top_level() ) {
        return $html_string;
      }

      $heading = array();
      $contents = array();
      switch( $this->m_action ) {
        case 'list':
          break;
        case 'new_zone':
          $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_ZONE . '</b>');
          $azone_array = array();
          $azone_query = "select at.abstract_types_id as id, at.abstract_types_name as text from " . TABLE_ABSTRACT_TYPES . " at where at.abstract_types_status = '1' order by at.sort_order";
          $azone_array = $g_db->query_to_array($azone_query);
          $contents[] = array('form' => tep_draw_form('zones', FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID . '&action=insert_zone'));
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'new_entry.png', IMAGE_NEW) );
          $contents[] = array('text' => TEXT_INFO_NEW_ZONE_INTRO);
          $contents[] = array('text' => TEXT_INFO_ZONE_NAME . '<br />' . tep_draw_input_field('abstract_zone_name'));
          $contents[] = array('text' => TEXT_INFO_ZONE_TYPE . '<br />' . tep_draw_pull_down_menu('abstract_types_id', $azone_array,''));
          $contents[] = array('text' => TEXT_INFO_ZONE_DESC . '<br />' . tep_draw_textarea_field('abstract_zone_desc', true, '', 5));
          $contents[] = array('text' => TEXT_INFO_ZONE_ORDER . '<br />' . tep_draw_input_field('sort_id', $this->m_zInfo['sort_id']));
          $contents[] = array('text' => tep_draw_checkbox_field('status_id', 'on', false) . '&nbsp;' . TEXT_INFO_ZONE_VISIBLE);
          $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
          break;
        case 'edit_zone':
          $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ZONE . '</b>');
          $contents[] = array('form' => tep_draw_form('zones', FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zInfo->abstract_zone_id . '&action=save_zone'));
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
          $contents[] = array('text' => TEXT_INFO_EDIT_ZONE_INTRO);
          $contents[] = array('text' => TEXT_INFO_ZONE_NAME . '<br />' . tep_draw_input_field('abstract_zone_name', $this->m_zInfo->abstract_zone_name));
          $contents[] = array('text' => TEXT_INFO_ZONE_DESC . '<br />' . tep_draw_textarea_field('abstract_zone_desc', true, '', 8, $this->m_zInfo->abstract_zone_desc));
          $contents[] = array('text' => TEXT_INFO_ZONE_ORDER . '<br />' . tep_draw_input_field('sort_id', $this->m_zInfo->sort_id));
          $contents[] = array('text' => tep_draw_checkbox_field('status_id', 'on', ($this->m_zInfo->status_id == 1)?true:false) . '&nbsp;' . TEXT_INFO_ZONE_VISIBLE);
          $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zInfo->abstract_zone_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
          break;
        case 'delete_zone':
          $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ZONE . '</b>');

          $contents[] = array('form' => tep_draw_form('zones', FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zInfo->abstract_zone_id . '&action=deleteconfirm_zone'));
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
          $contents[] = array('text' => TEXT_INFO_DELETE_ZONE_INTRO);
          $contents[] = array('text' => '<b>' . $this->m_zInfo->abstract_zone_name . '</b>');
          $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zInfo->abstract_zone_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
          break;

        default:
          if (isset($this->m_zInfo) && is_object($this->m_zInfo) && !empty($this->m_zID) ) {
            $heading[] = array('text' => '<b>' . $this->m_zInfo->abstract_zone_name . '</b>');

            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zInfo->abstract_zone_id . '&action=edit_zone') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zInfo->abstract_zone_id . '&action=delete_zone') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zInfo->abstract_zone_id . '&action=new_zone') . '">' . tep_image_button('button_new.gif', IMAGE_INSERT) . '</a><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&spage=1' . '&zID=' . $this->m_zInfo->abstract_zone_id . '&action=list') . '">' . tep_image_button('button_details.gif', IMAGE_DETAILS) . '</a>');
            $contents[] = array('text' => TEXT_INFO_ZONE_TYPE . '<br /><b>' . $this->m_zInfo->abstract_types_name . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_CLASS . '<br /><b>' . $this->m_zInfo->abstract_types_class . '.php</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_TABLE . '<br /><b>' . $this->m_zInfo->abstract_types_table . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_VISIBILITY . '<br /><b>' . (($this->m_zInfo->status_id == 1)?TEXT_INFO_ZONE_VISIBLE:TEXT_INFO_ZONE_HIDDEN) . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_DESC . '<br />' . $this->m_zInfo->abstract_zone_desc);
            $contents[] = array('text' => TEXT_INFO_ZONE_ORDER . '<br />' . $this->m_zInfo->sort_id);
            $contents[] = array('text' => TEXT_INFO_DATE_ADDED . '<br />' . tep_date_short($this->m_zInfo->date_added));
            if (tep_not_null($this->m_zInfo->last_modified)) 
              $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . '<br />' . tep_date_short($this->m_zInfo->last_modified));
          } else { // create generic_text dummy info
            $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
            $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', TEXT_ERROR) );
            $contents[] = array('text' => TEXT_NO_GENERIC);
          }
          break;
      }

      if( !empty($heading) && !empty($contents) ) {
        $html_string .= '            <div class="rightcell">' . "\n";
        $box = new box;
        $html_string .= $box->infoBox($heading, $contents);
        $html_string .= '            </div>' . "\n";
      }
      return $html_string;
    }

    function display_bottom() {
       return '';
    }
  }
?>
