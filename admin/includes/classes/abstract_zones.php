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
   var $m_zID, $m_zpage, $m_saction, $m_action, $m_type, $m_zInfo, $m_sID, $m_spage, $m_filter, $cols_array;
// class constructor
    function abstract_zones() {}

    function initialize() {
      extract(tep_load('database'));

      $this->m_zID = isset($_GET['zID'])?(int)$_GET['zID']:'';
      $this->m_zpage = isset($_GET['zpage'])?(int)$_GET['zpage']:'';
      $this->m_saction = isset($_GET['saction'])?$db->prepare_input($_GET['saction']):'';
      $this->m_action = isset($_GET['action'])?$db->prepare_input($_GET['action']):'';
      $this->m_sID = isset($_GET['sID'])?$db->prepare_input($_GET['sID']):'';
      $this->m_spage = isset($_GET['spage'])?(int)$_GET['spage']:'';
      $this->m_filter = isset($_GET['filter'])?(int)$_GET['filter']:'';

      $zones_query = $db->fly("select abstract_types_id from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "'");
      $zones_array = $db->fetch_array($zones_query);
      $this->m_type = $zones_array['abstract_types_id'];
    }

    function emit_scripts() {}

    function is_top_level() {
      extract(tep_load('defs'));

      if( empty($cDefs->action) ) {
        return true;
      }
      switch($cDefs->action) {
        case 'edit_zone':
        case 'delete_zone':
        case 'new_zone':
          return true;
      }
      return false;
    }

    function validate_array_selection($entity, $action='list') {
      extract(tep_load('defs','message_stack'));

      if( !isset($_POST[$entity]) || !is_array($_POST[$entity]) || !count($_POST[$entity]) ) {
        $msg->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=' . $action));
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

    function deleteconfirm_zone() {
      extract(tep_load('database'));

      $zones_array = func_get_args();

      if( empty($zone_array) ) {
        $zones_array[] = $this->m_zID;
      }

      for($i=0, $j=count($zones_array); $i<$j; $i++) {
        $zone_id = $zones_array[$i];

        // Synchronize related tables with abstract_zone_id in them
        $db->query("delete from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$zone_id . "'");
        $db->query("delete from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$zone_id . "'");
        $db->query("delete from " . TABLE_SUPER_ZONES . " where subzone_id = '" . (int)$zone_id . "'");
        $db->query("delete from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "'");
      }
    }

    function deleteconfirm_type_zone($type_id) {
      extract(tep_load('database'));

      // Synchronize related tables with abstract_types_id
      $delete_query = $db->query("select abstract_zone_id from " . TABLE_ABSTRACT_ZONES . " where abstract_types_id = '" . (int)$type_id . "'");
      if( !$db->num_rows($delete_query) ) return;
      while( $delete_array = $db->fetch_array($delete_query) ) {
        $this->deleteconfirm_zone($delete_array['abstract_zone_id']);
      }
      $db->query("delete from " . TABLE_ABSTRACT_ZONES . " where abstract_types_id = '" . (int)$type_id . "'");
    }

    function process_saction() {
      switch( $this->m_saction ) {
        default:
          break;
      }
    }

    function set_flag() {
      extract(tep_load('defs', 'database'));

      $flag = isset($_GET['flag'])?(int)$_GET['flag']:0;
      $db->query("update " . TABLE_ABSTRACT_ZONES . " set status_id = '" . (int)$flag . "' where abstract_zone_id = '" . (int)$this->m_zID . "'");
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID', 'flag') . 'zID=' . $this->m_zID));
    }

    function insert_zone() {
      extract(tep_load('defs', 'database', 'message_stack'));

      $zone_name = $db->prepare_input($_POST['abstract_zone_name']);
      $check_query = $db->query("select count(*) as total from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_name = '" . $db->input($zone_name) . "'");
      $check_array = $db->fetch_array($check_query);
      if( $check_array['total'] ) {
        $msg->add_session(sprintf(ERROR_DUPLICATE_NAME, $zone_name));
        tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') ));
      }

      if( empty($zone_name) ) {
        $msg->add_session(ERROR_EMPTY_NAME);
        tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') ));
      }
      $sql_data_array = array(
        'abstract_zone_name' => $zone_name,
        'abstract_zone_desc' => $db->prepare_input($_POST['abstract_zone_desc']),
        'abstract_types_id' => (int)$_POST['abstract_types_id'],
        'sort_id' => (int)$_POST['sort_id'],
        'date_added' => 'now()',
        'last_modified' => 'now()',
        'status_id' => isset($_POST['status_id'])?1:0
      );
      $db->perform(TABLE_ABSTRACT_ZONES, $sql_data_array);
      $new_zone_id = $db->insert_id();

      //-MS- SEO-G Added
      require_once(DIR_FS_CLASSES . 'seo_url.php');
      $cLink = new seoURL;
      $seo_name = $cLink->create_safe_string($_POST['seo_name']);

      if(empty($seo_name) ) {
        $db->query("delete from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$new_zone_id . "'");
        $db->query("delete from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$new_zone_id . "'");
        tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $new_zone_id));
      }

      require_once(DIR_FS_CLASSES . 'seo_zones.php');
      require_once(DIR_FS_CLASSES . 'seo_abstract.php');
      $cAbstractSEO = new seo_abstract;
      $seo_name = $cAbstractSEO->insert_update($new_zone_id, $seo_name, 'check_insert');

      if( isset($_POST['seo_name_force']) && !$cLink->generate_collection_link($new_zone_id) ) {
        $msg->add_session(WARNING_SEO_FRIENDLY_FAILED, 'warning');
      }
      //-MS- SEO-G Added EOM

      //-MS- META-G Added
      $metag_title = $db->prepare_input($_POST['meta_title']);
      $metag_keywords = $db->prepare_input($_POST['meta_keywords']);
      $metag_text = $db->prepare_input($_POST['meta_text']);

      if( empty($metag_title) ) {
        $metag_title = $zone_name;
      }

      if( empty($metag_keywords) ) {
        $metag_keywords = $zone_name;
      }

      if( empty($metag_text) ) {
        $metag_text = $zone_name;
      }

      $metag_array = array(
        'abstract_zone_id' => (int)$new_zone_id,
        'meta_title' => strip_tags($metag_title),
        'meta_keywords' => strip_tags($metag_keywords),
        'meta_text' => strip_tags($metag_text),
      );
      $db->perform(TABLE_META_ABSTRACT, $sql_data_array);
      //-MS- META-G Added EOM
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $new_zone_id));
    }

    function save_zone() {
      extract(tep_load('defs', 'database', 'message_stack'));

      $zone_name = $db->prepare_input($_POST['abstract_zone_name']);
      $check_query = $db->query("select count(*) as total from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id != '" . (int)$this->m_zID . "' and abstract_zone_name = '" . $db->input($zone_name) . "'");
      $check_array = $db->fetch_array($check_query);
      if( $check_array['total'] ) {
        $msg->add_session(sprintf(ERROR_DUPLICATE_NAME, $zone_name));
        tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action')));
      }

      if( empty($zone_name) ) {
        $msg->add_session(ERROR_EMPTY_NAME);
        tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action')));
      }
      $sql_data_array = array(
        'abstract_zone_name' => $zone_name,
        'abstract_zone_desc' => $db->prepare_input($_POST['abstract_zone_desc']),
        'sort_id' => (int)$_POST['sort_id'],
        'last_modified' => 'now()',
        'status_id' => isset($_POST['status_id'])?1:0
      );

      $msg->add_session(sprintf(SUCCESS_ZONE_UPDATED, $zone_name), 'success');
      $db->perform(TABLE_ABSTRACT_ZONES, $sql_data_array, 'update', "abstract_zone_id = '" . (int)$this->m_zID . "'");

      //-MS- SEO-G Added
      require_once(DIR_FS_CLASSES . 'seo_url.php');
      $cLink = new seoURL;
      $seo_name = $cLink->create_safe_string($_POST['seo_name']);

      if(empty($seo_name) ) {
        $db->query("delete from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$this->m_zID . "'");
        $db->query("delete from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$this->m_zID . "'");
        tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $this->m_zID));
      }

      require_once(DIR_FS_CLASSES . 'seo_zones.php');
      require_once(DIR_FS_CLASSES . 'seo_abstract.php');
      $cAbstractSEO = new seo_abstract;
      $seo_name = $cAbstractSEO->insert_update($this->m_zID, $seo_name, 'check_insert');

      if( isset($_POST['seo_name_force']) && !$cLink->generate_collection_link($this->m_zID) ) {
        $msg->add_session(WARNING_SEO_FRIENDLY_FAILED, 'warning');
      }
      //-MS- SEO-G Added EOM

      //-MS- META-G Added
      require_once(DIR_FS_CLASSES . 'meta_zones.php');
      require_once(DIR_FS_CLASSES . 'meta_abstract.php');
      $cAbstractMETA = new meta_abstract;

      $metag_array = array(
        'meta_title' => $db->prepare_input($_POST['meta_title']),
        'meta_keywords' => $db->prepare_input($_POST['meta_keywords']),
        'meta_text' => $db->prepare_input($_POST['meta_text']),
      );
      $result = $cAbstractMETA->insert_update($this->m_zID, $metag_array, 'check_insert');

      if( !$result ) {
        $msg->add_session(WARNING_META_WRITE_FAILED, 'warning');
      }
      //-MS- META-G Added EOM
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $this->m_zID));
    }


    function get_zone_name($abstract_zone_id, $default_zone='Unknown Zone') {
      extract(tep_load('database'));

      $zone_query = $db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      if ($db->num_rows($zone_query)) {
        $zone = $db->fetch_array($zone_query);
        return $zone['abstract_zone_name'];
      } else {
        return $default_zone;
      }
    }

    function get_types() {
      extract(tep_load('database'));

      $types_query_raw = "select abstract_types_id, abstract_types_name from " . TABLE_ABSTRACT_TYPES . " order by sort_order";
      $types_array = $db->query_to_array($types_query_raw);
      return $types_array;
    }

    function get_classes() {
      extract(tep_load('database'));

      $classes_query_raw = "select abstract_types_id, abstract_class_name from " . TABLE_ABSTRACT_TYPES . " order by sort_order";
      $classes_array = $db->query_to_array($classes_query_raw);
      return $classes_array;
    }  


    function get_zone_type($abstract_zone_id, $default_zone='Unknown Type') {
      extract(tep_load('database'));

      $zone_query = $db->query("select at.abstract_types_name from " . TABLE_ABSTRACT_TYPES . " at left join " . TABLE_ABSTRACT_ZONES . " az on (az.abstract_types_id=at.abstract_types_id) where az.abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      if ($db->num_rows($zone_query)) {
        $zone = $db->fetch_array($zone_query);
        return $zone['abstract_types_name'];
      } else {
        return $default_zone;
      }
    }  

    function get_zone_class($abstract_zone_id, $default_zone='Unknown Type') {
      extract(tep_load('database'));

      $result = $default_zone;
      $zone_query = $db->query("select at.abstract_types_class from " . TABLE_ABSTRACT_TYPES . " at left join " . TABLE_ABSTRACT_ZONES . " az on (az.abstract_types_id=at.abstract_types_id) where az.abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      if ($db->num_rows($zone_query)) {
        $zone = $db->fetch_array($zone_query);
        $result = $zone['abstract_types_class'];
      }
      return $result;
    }  

    function display_html() {
      extract(tep_load('defs'));
      $html_string = '';
      if (!$this->m_action) {
        $html_string = $this->display_default();
        if( !tep_not_null($this->m_zID) ) {

          $html_string .= 
          '             <div class="comboHeading">' . "\n" . 
          '               <div class="smallText"><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=new_zone') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a></div>' . "\n" . 
          '             </div>' . "\n";
        }
      }
      return $html_string;
    }

    function display_default() {
      extract(tep_load('defs', 'database'));

      $html_string = $filter_string = '';

      $filter_array = $this->get_types();
      array_unshift($filter_array, array('id' => '', 'text' => TEXT_VIEW_ALL));
      $filter_array = tep_array_rename_elements($filter_array, array('id', 'text'));

      $html_string .= 
      '          <div class="comboHeading">' . "\n";


      $html_string .= 
      '            <div class="floater textadj rspacer">' . tep_draw_form('search_form', $cDefs->script, '', 'get', 'id="search_abstract"') . "\n" . 
      '              <label for="collections_search">' . TEXT_INFO_TITLE_SEARCH . '</label>' . tep_draw_input_field('search', '', 'size="40" id="collections_search"');

      $params_string = tep_get_all_get_params('action', 'search', 'zpage') . 'action=search_collections';
      $params_array = tep_get_string_parameters($params_string);
      foreach($params_array as $key => $value ) {
        $html_string .= tep_draw_hidden_field($key, $value);
      }
      $html_string .= 
      '            </form></div>' . "\n";

      $html_string .= 
      '            <div class="floater textadj">'  . tep_draw_form("filter_form", $cDefs->script, '', 'get', 'id="abstract_filter"') . '<label for="abstract_filter_menu">' . TEXT_TITLE_FILTER . '</label>' . tep_draw_pull_down_menu('filter', $filter_array, $this->m_filter, 'onchange="this.form.submit()"') . "\n";
      $params_string = tep_get_all_get_params('zID', 'action', 'filter', 'zpage');
      $params_array = tep_get_string_parameters($params_string);
      foreach($params_array as $key => $value ) {
        $html_string .=  tep_draw_hidden_field($key, $value);
      }
      $html_string .= 
      '            </form></div>' . "\n";

      $html_string .= 
      '          </div>' . "\n";

      $html_string .= 
      '          <div class="formArea"><table class="tabledata" id="abstract_table">' . "\n" . 
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
      $zones_query = $db->query($zones_split->sql_query);
      $row_type = 0;
      $row_array = array('dataTableRowAlt2', 'dataTableRowAlt3', 'dataTableRowAlt4', 'dataTableRowAlt5');
      $row_counter = count($row_array);
      $row_class = 'dataTableRow';
      while( $zones = $db->fetch_array($zones_query) ) {
        $types_query = $db->fly("select abstract_types_name, abstract_types_class, abstract_types_table from " . TABLE_ABSTRACT_TYPES . " where abstract_types_id = '" . (int)$zones['abstract_types_id'] . "'");
        $types_array = $db->fetch_array($types_query);
        $zones = array_merge($zones, $types_array);

        if( $row_type != $zones['abstract_types_id'] ) {
          $row_class = $row_array[$zones['abstract_types_id']%$row_counter];
        }

        if( (empty($this->m_zID) || (!empty($this->m_zID) && $this->m_zID == $zones['abstract_zone_id'])) && !isset($this->m_zInfo) && (substr($this->m_action, 0, 3) != 'new') ) {
          $this->m_zInfo = new objectInfo($zones);
          $this->m_zID = $zones['abstract_zone_id'];
        }

        if (isset($this->m_zInfo) && is_object($this->m_zInfo) && ($zones['abstract_zone_id'] == $this->m_zInfo->abstract_zone_id)) {
          $html_string .= 
          '          <tr class="dataTableRowSelected row_link" href="' . tep_href_link($cDefs->script, tep_get_all_get_params('zID', 'action') . 'zID=' . $this->m_zInfo->abstract_zone_id . '&action=list') . '">' . "\n";
        } else {
          $html_string .= 
          '          <tr class="' . $row_class . ' row_link" href="' . tep_href_link($cDefs->script, tep_get_all_get_params('zID') . 'zID=' . $zones['abstract_zone_id']) . '">' . "\n";
        }
        $html_string .= 
        '              <td><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $zones['abstract_zone_id'] . '&action=list') . '">' . tep_image(DIR_WS_ICONS . 'icon_folder.png', ICON_FOLDER) . '</a>&nbsp;' . $zones['abstract_zone_name'] . '</td>' . "\n" . 
        '              <td>' . $zones['abstract_types_name'] . '</td>' . "\n" . 
        '              <td class="tinysep calign">' . "\n";

        if( $zones['status_id'] == '1' ) {
          $html_string .= tep_image(DIR_WS_ICONS . 'icon_status_green.png', TEXT_INFO_ZONE_VISIBLE) . '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('zID', 'action', 'flag') . 'zID=' . $zones['abstract_zone_id'] . '&action=set_flag&flag=0') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
        } else {
          $html_string .= '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('zID', 'action', 'flag') . 'zID=' . $zones['abstract_zone_id'] . '&action=set_flag&flag=1') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', TEXT_INFO_ZONE_HIDDEN);
        }
        $html_string .= 
        '              </td>' . "\n" . 
        '              <td class="tinysep calign">';
        $html_string .= '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('zID', 'action') . 'zID=' . $zones['abstract_zone_id'] . '&action=delete_zone') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $zones['abstract_zone_name']) . '</a>';
        $html_string .= '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('zID', 'action') . 'zID=' . $zones['abstract_zone_id'] . '&action=edit_zone') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $zones['abstract_zone_name']) . '</a>';
        if (isset($this->m_zInfo) && is_object($this->m_zInfo) && ($zones['abstract_zone_id'] == $this->m_zInfo->abstract_zone_id) && tep_not_null($this->m_zID) ) { 
          $html_string .= tep_image(DIR_WS_ICONS . 'icon_arrow_right.png'); 
        } else { 
          $html_string .= '<a href="' . tep_href_link($cDefs->script,  tep_get_all_get_params('zID', 'action') . 'zID=' . $zones['abstract_zone_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
        } 
        $html_string .= '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '          </table></div>' . "\n" . 
      '          <div class="listArea splitLine">' . "\n" . 
      '            <div class="floater">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
      '            <div class="floatend">' . $zones_split->display_links(tep_get_all_get_params('action', 'zpage', 'zID') ) . '</div>' . "\n" . 
      '          </div>' . "\n";
      return $html_string;
    }

    function display_right_box() {
      extract(tep_load('defs', 'database'));

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
          $azone_array = $db->query_to_array($azone_query);
          $contents[] = array('form' => tep_draw_form('zones', $cDefs->script, tep_get_all_get_params('action') . 'action=insert_zone'));
          $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'new_entry.png', IMAGE_NEW) );
          $contents[] = array('text' => TEXT_INFO_NEW_ZONE_INTRO);
          $contents[] = array('class' => 'rpad', 'text' => TEXT_INFO_ZONE_NAME . '<br />' . tep_draw_input_field('abstract_zone_name'));
          $contents[] = array('text' => TEXT_INFO_ZONE_TYPE . '<br />' . tep_draw_pull_down_menu('abstract_types_id', $azone_array));
          $contents[] = array('class' => 'rpad', 'text' => TEXT_INFO_ZONE_DESC . '<br />' . tep_draw_textarea_field('abstract_zone_desc', '', 5));
          $contents[] = array('text' => TEXT_INFO_ZONE_ORDER . '<br />' . tep_draw_input_field('sort_id', $this->m_zInfo['sort_id'], 'size="3"'));
          $contents[] = array('text' => tep_draw_checkbox_field('status_id', 'on', false) . '&nbsp;' . TEXT_INFO_ZONE_VISIBLE);
          $contents[] = array('class' => 'infoBoxSection', 'section' => '<div>');

          $contents[] = array('text' => '<b>' . TEXT_SEO_SECTION . '</b>');
          $contents[] = array('class' => 'rpad', 'text' => TEXT_SEO_NAME . '<br />' . tep_draw_input_field('seo_name'));
          $contents[] = array('text' => tep_draw_checkbox_field('seo_name_force', 'on') . '&nbsp;' . TEXT_SEO_NAME_FORCE);
          $contents[] = array('class' => 'heavy', 'text' => TEXT_METAG);
          $contents[] = array('class' => 'rpad', 'text' => TEXT_META_TITLE . '<br />' . tep_draw_input_field('meta_title'));
          $contents[] = array('class' => 'rpad', 'text' => TEXT_META_KEYWORDS . '<br />' . tep_draw_textarea_field('meta_keywords', '', '', '2') );
          $contents[] = array('class' => 'rpad', 'text' => TEXT_META_TEXT . '<br />' . tep_draw_textarea_field('meta_text', '', '', '2') );
          $contents[] = array('section' => '</div>');
          $contents[] = array('class' => 'calign', 'text' => tep_image_submit('button_insert.gif', IMAGE_INSERT) . '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $this->m_zID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
          break;
        case 'edit_zone':
          $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ZONE . '</b>');
          $contents[] = array('form' => tep_draw_form('zones', $cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $this->m_zInfo->abstract_zone_id . '&action=save_zone'));
          $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
          $contents[] = array('text' => TEXT_INFO_EDIT_ZONE_INTRO);
          $contents[] = array('class' => 'rpad', 'text' => TEXT_INFO_ZONE_NAME . '<br />' . tep_draw_input_field('abstract_zone_name', $this->m_zInfo->abstract_zone_name));
          $contents[] = array('class' => 'rpad', 'text' => TEXT_INFO_ZONE_DESC . '<br />' . tep_draw_textarea_field('abstract_zone_desc', $this->m_zInfo->abstract_zone_desc, '', 8));
          $contents[] = array('text' => TEXT_INFO_ZONE_ORDER . '<br />' . tep_draw_input_field('sort_id', $this->m_zInfo->sort_id, 'size="3"'));
          $contents[] = array('text' => tep_draw_checkbox_field('status_id', 'on', ($this->m_zInfo->status_id == 1)?true:false) . '&nbsp;' . TEXT_INFO_ZONE_VISIBLE);
          $contents[] = array('class' => 'infoBoxSection', 'section' => '<div>');

          $seog_array = array(
            'seo_name' => '',
          );
          $seog_query = $db->query("select seo_name from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$this->m_zInfo->abstract_zone_id . "'");
          if( $db->num_rows($seog_query) ) {
            $seog_array = $db->fetch_array($seog_query);
          }
          $contents[] = array('class' => 'heavy', 'text' => TEXT_SEO_SECTION);
          $contents[] = array('class' => 'rpad', 'text' => TEXT_SEO_NAME . '<br />' . tep_draw_input_field('seo_name', $seog_array['seo_name']));
          $contents[] = array('text' => tep_draw_checkbox_field('seo_name_force', 'on') . '&nbsp;' . TEXT_SEO_NAME_FORCE);

          $metag_title = '';
          $metag_keywords = '';
          $metag_text = '';

          $metag_query = $db->query("select meta_title, meta_keywords, meta_text from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$this->m_zInfo->abstract_zone_id .  "'");
          if( $db->num_rows($metag_query) ) {
            $metag_array = $db->fetch_array($metag_query);
            $metag_title = stripslashes($metag_array['meta_title']);
            $metag_keywords = stripslashes($metag_array['meta_keywords']);
            $metag_text = stripslashes($metag_array['meta_text']);
          }
          $contents[] = array('class' => 'heavy', 'text' => TEXT_METAG);
          $contents[] = array('class' => 'rpad', 'text' => TEXT_META_TITLE . '<br />' . tep_draw_input_field('meta_title', $metag_title));
          $contents[] = array('class' => 'rpad', 'text' => TEXT_META_KEYWORDS . '<br />' . tep_draw_textarea_field('meta_keywords', $metag_keywords, '', '2') );
          $contents[] = array('class' => 'rpad', 'text' => TEXT_META_TEXT . '<br />' . tep_draw_textarea_field('meta_text', $metag_text, '', '2') );
          $contents[] = array('section' => '</div>');
          $contents[] = array('class' => 'calign', 'text' => tep_image_submit('button_update.gif', IMAGE_UPDATE) . '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $this->m_zInfo->abstract_zone_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
          break;
        case 'delete_zone':
          $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ZONE . '</b>');

          $contents[] = array('form' => tep_draw_form('zones', $cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $this->m_zInfo->abstract_zone_id . '&action=deleteconfirm_zone'));
          $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
          $contents[] = array('text' => TEXT_INFO_DELETE_ZONE_INTRO);
          $contents[] = array('text' => '<b>' . $this->m_zInfo->abstract_zone_name . '</b>');
          $contents[] = array('class' => 'calign', 'text' => tep_image_submit('button_delete.gif', IMAGE_DELETE) . '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $this->m_zInfo->abstract_zone_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
          break;

        default:
          if (isset($this->m_zInfo) && is_object($this->m_zInfo) && !empty($this->m_zID) ) {
            $heading[] = array('text' => '<b>' . $this->m_zInfo->abstract_zone_name . '</b>');

            $buttons = array(
              '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $this->m_zID . '&action=edit_zone') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>',
              '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $this->m_zID . '&action=delete_zone') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>', 
              '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $this->m_zID . '&action=new_zone') . '">' . tep_image_button('button_new.gif', IMAGE_INSERT) . '</a>', 
              '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'zID') . 'zID=' . $this->m_zID . '&action=list') . '">' . tep_image_button('button_details.gif', IMAGE_DETAILS) . '</a>'
            );
            $contents[] = array(
              'class' => 'calign', 
              'text' => implode('', $buttons),
            );

            $contents[] = array('text' => TEXT_INFO_ZONE_TYPE . '<br /><b>' . $this->m_zInfo->abstract_types_name . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_CLASS . '<br /><b>' . $this->m_zInfo->abstract_types_class . '.php</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_TABLE . '<br /><b>' . $this->m_zInfo->abstract_types_table . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_VISIBILITY . '<br /><b>' . (($this->m_zInfo->status_id == 1)?TEXT_INFO_ZONE_VISIBLE:TEXT_INFO_ZONE_HIDDEN) . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_DESC . '<br />' . $this->m_zInfo->abstract_zone_desc);
            $contents[] = array('text' => TEXT_INFO_ZONE_ORDER . '<br />' . $this->m_zInfo->sort_id);
            $contents[] = array('text' => TEXT_INFO_DATE_ADDED . '<br />' . tep_date_short($this->m_zInfo->date_added));
            if (tep_not_null($this->m_zInfo->last_modified)) {
              $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . '<br />' . tep_date_short($this->m_zInfo->last_modified));
            }
          } else { // create generic_text dummy info
            $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
            $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', TEXT_ERROR) );
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
