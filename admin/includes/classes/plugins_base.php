<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Run-Time Plugins Base Class
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
//
*/
  class plugins_base {

    // Compatibility constructor
    function plugins_base() {
      $this->scripts_array = array();
      $this->site_index = tep_create_safe_string(tep_get_site_path(), '_', "/[^0-9a-z\-_]+/i");
      $string = get_class($this);
      $this->key = substr($string, strlen(PLUGINS_ADMIN_PREFIX));
      $this->admin_path = DIR_FS_PLUGINS.$this->key.'/';
      $this->admin_web_path = DIR_WS_PLUGINS.$this->key.'/';
    }

    // Retrieve plugin configuration data from the database
    function load_options($all=false) {
      extract(tep_load('database'));

      $index = $this->site_index;
      $plugins_query = $db->query("select plugins_data from " . TABLE_PLUGINS . " where plugins_key = '" . $db->filter($this->key) . "'");
      $plugins_array = $db->fetch_array($plugins_query);
      $plugins_data = array();
      if( !empty($plugins_array['plugins_data']) ) {
        $plugins_data = unserialize($plugins_array['plugins_data']);
      }
      if( !$all && isset($plugins_data[$index]) ) {
        $plugins_data = $plugins_data[$index];
      }
      return $plugins_data;
    }

    // Save plugin configuration data from the database
    function save_options($input_array) {
      extract(tep_load('database'));

      $index = $this->site_index;
      $plugins_data = $this->load_options(true);

      if( !isset($input_array['status_id']) ) {
        $input_array['status_id'] = $plugins_data[$index]['status_id'];
      }
      if( !isset($input_array['sort_id']) ) {
        $input_array['sort_id'] = $plugins_data[$index]['sort_id'];
      }
      $plugins_data[$index] = $input_array;
      $store_data = serialize($plugins_data);

      $db->query("update " . TABLE_PLUGINS . " set plugins_data = '" . $db->filter($store_data) . "' where plugins_key = '" . $db->filter($this->key) . "'");
    }

    function check_scripts($tmp_array=array()) {
      extract(tep_load('defs'));

      $result = false;

      if(empty($tmp_array)) $tmp_array = $this->scripts_array;
      if(empty($tmp_array)) return $result;

      foreach($this->scripts_array as $value) {
        if( $value == $cDefs->script ) {
          $result = true;
          break;
        }
      }
      return $result;
    }

    function get_help() {
      extract(tep_load('sessions'));

      $help = (isset($_GET['ajax']) && !empty($_GET['ajax']))?$_GET['ajax']:'';
      if( empty($help) ) {
        $file = $this->admin_path . 'back/help_default.html';
      } else {
        $help = tep_create_safe_string($help, '', "[^0-9a-z\-_]");
        $file = $this->admin_path . 'back/help_' . $help . '.html';
      }

      if( !is_file($file) ) return false;

      $contents = '';
      $result = tep_read_contents($file, $contents);
      if( !$result ) return false;

      echo '<div>' . $contents . '</div>';
      $cSessions->close();
      return true;
    }
  }
?>
