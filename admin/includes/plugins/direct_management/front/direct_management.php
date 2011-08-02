<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Direct Management Handling
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
------------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class direct_management extends plugins_base {
    // Compatibility constructor
    function direct_management() {

      $this->admin = false;
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      // Load plugin configuration settings
      $this->options = $this->load_options();
      // Load the plugin strings

      $this->strings = tep_get_strings($this->fs_path . 'web_strings.php');
      $this->direct_management_form = $this->fs_path . 'direct_management.tpl';

      if( !is_file($this->direct_management_form) ) $this->change(false);
    }

    function init_sessions() {
      extract(tep_load('defs', 'database', 'http_validator', 'sessions', 'message_stack'));

      $cStrings =& $this->strings;
      $this->admin =& $cSessions->register('admin', false);
      if( !$this->admin || ($cDefs->script != FILENAME_GENERIC_PAGES && $cDefs->script != FILENAME_COLLECTIONS) ) {
        $this->change(false);
      }

      $key = $this->options['admin_key'];
      if( $this->admin && isset($_GET[$key]) ) {
        $msg->add_session($cStrings->SUCCESS_ADMIN_INIT, 'success', 'header');
        $http->send_cookies();
        tep_redirect(tep_href_link());
        return true;
      }

      if( !isset($_GET[$key]) || empty($_GET[$key]) || strlen($key) != $this->options['admin_key_length'] || $this->admin ) {
        return false;
      }

      $db->query("delete from " . TABLE_SESSIONS . " where expiry <= '" . time() . "'");
      $db->query("delete from " . TABLE_SESSIONS_ADMIN . " where expiry <= '" . time() . "'");

      $check_query = $db->query("select count(*) as total from " . TABLE_SESSIONS . " where sesskey = '" . $db->filter($_GET[$key]) . "' and ip_long = '" . $db->filter($http->ip_string) . "'");
      $check_array = $db->fetch_array($check_query);
      if( $check_array['total'] ) {

        $check_query = $db->query("select count(*) as total from " . TABLE_SESSIONS_ADMIN . " where sesskey = '" . $db->filter($_GET[$key]) . "'");
        $check_array = $db->fetch_array($check_query);
        if( $check_array['total'] ) {
          $db->query("delete from " . TABLE_SESSIONS . " where sesskey = '" . $db->filter($_GET[$key]) . "' and ip_long = '" . $db->filter($http->ip_string) . "'");

          $this->admin = true;
          $this->options['admin_key'] = tep_create_random_value($this->options['admin_key_length'], 'chars_lower');
          $this->save_options($this->options);
          $msg->add_session($cStrings->SUCCESS_ADMIN_INIT, 'success', 'header');
          $http->send_cookies();
          tep_redirect(tep_href_link());
        }
      }
      return true;
    }

    function ajax_start() {
      extract(tep_load('defs', 'database', 'sessions'));

      if( !$this->admin || ($cDefs->script != FILENAME_GENERIC_PAGES && $cDefs->script != FILENAME_COLLECTIONS) ) {
        return false;
      }

      switch($cDefs->action) {
        case 'edit_content':
          $this->dm_edit_content();
          break;
        case 'update_content':
          $this->dm_update_content();
          break;
        default:
          break;
      }
      
      $cSessions->close();
      return true;
    }

    function html_start() {

      extract(tep_load('defs', 'http_validator', 'message_stack'));
      $cStrings =& $this->strings;

      //$cDefs->media[] = '<script language="javascript" type="text/javascript" src="' . DIR_WS_JS . 'jquery/jquery.ajaxq.js"></script>';
      //$cDefs->media[] = '<script language="javascript" type="text/javascript" src="' . DIR_WS_JS . 'jquery/jquery.form.js"></script>';
      //$cDefs->media[] = '<script language="javascript" type="text/javascript" src="' . DIR_WS_JS . 'jquery/ui/jquery-ui.min.js"></script>';
      $cDefs->media[] = '<script language="javascript" type="text/javascript" src="' . DIR_WS_JS . 'jquery/jquery.base64.js"></script>';
      $cDefs->media[] = '<script type="text/javascript" src="' . $this->web_path . 'admin.js"></script>';

      $msg->add(sprintf($cStrings->WARNING_ADMIN_PRESENT, $http->ip), 'warning', 'header');
      return true;
    }

    function html_end() {
      extract(tep_load('defs'));

      $contents = '';
      $launcher = $this->web_path . 'admin.tpl';
      $result = tep_read_contents($launcher, $contents);

      if(!$result) {
        return $result;
      }
      $contents_array = array(
        'BASE_TEMPLATE' => $this->web_path,
      );
      $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);
      return true;
    }

    function display_form($content_array) {
      extract(tep_load('defs', 'validator'));

      $params = $cValidator->convert_to_get(array('action'), array('action' => 'update_content'));
      $link = tep_href_link($cDefs->script, $params);

      $cStrings =& $this->strings;
      require($this->direct_management_form);
    }

    function dm_edit_content() {
      extract(tep_load('defs', 'database'));

      switch($cDefs->script) {
        case FILENAME_GENERIC_PAGES:
          $content_query = $db->query("select gtext_title as content_title, gtext_description as content_description from " . TABLE_GTEXT . " where gtext_id = '" . (int)$cDefs->gtext_id . "'");
          $content_array = $db->fetch_array($content_query);
          $this->display_form($content_array);
          break;
        case FILENAME_COLLECTIONS:
          $content_query = $db->query("select abstract_zone_name as content_title, abstract_zone_desc as content_description from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$cDefs->abstract_id . "'");
          $content_array = $db->fetch_array($content_query);
          $this->display_form($content_array);
          break;
        default:
          break;
      }
    }

    function dm_update_content() {
      extract(tep_load('defs', 'database', 'sessions'));

      $sql_data_array = array();
      $content_title = isset($_POST['content_title'])?$db->prepare_input($_POST['content_title'], false):'';
      $content_description = isset($_POST['content_title'])?$db->prepare_input($_POST['content_description'], false):'';

      switch($cDefs->script) {
        case FILENAME_GENERIC_PAGES:
          if( !empty($content_title) ) $sql_data_array['gtext_title'] = $content_title;
          if( !empty($content_description) ) $sql_data_array['gtext_description'] = $content_description;

          if( !empty($sql_data_array) ) {
            $db->perform(TABLE_GTEXT, $sql_data_array, 'update', "gtext_id = '" . (int)$cDefs->gtext_id . "'");
          }
          $content_query = $db->query("select gtext_title as content_title, gtext_description as content_description from " . TABLE_GTEXT . " where gtext_id = '" . (int)$cDefs->gtext_id . "'");
          $content_array = $db->fetch_array($content_query);
          break;
        case FILENAME_COLLECTIONS:
          if( !empty($content_title) ) $sql_data_array['abstract_zone_name'] = $content_title;
          if( !empty($content_description) ) $sql_data_array['abstract_zone_desc'] = $content_description;

          if( !empty($sql_data_array) ) {
            $db->perform(TABLE_ABSTRACT_ZONES, $sql_data_array, 'update', "abstract_zone_id = '" . (int)$cDefs->abstract_id . "'");
          }
          $content_query = $db->query("select abstract_zone_name as content_title, abstract_zone_desc as content_description from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$cDefs->abstract_id . "'");
          $content_array = $db->fetch_array($content_query);
          break;
        default:
          $content_array = array();
          break;
      }

      $result = tep_js_encode($content_array);
      echo $result;
      $cSessions->close();
    }

  }
?>
