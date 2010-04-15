<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Plugins Run-Time Invocation Class
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
  class plugins_admin {
    var $plugins_array, $keys_array, $objects_array, $prefix;

    function plugins_admin() {
      $this->site_index = tep_create_safe_string(tep_get_site_path(), '_', "/[^0-9a-z\-_]+/i");
      $this->prefix = PLUGINS_ADMIN_PREFIX;
      $this->keys_array = $this->objects_array = array();
      $this->enumerate();
    }

    // Interface/Override functions
    function enumerate() {
      global $g_db;

      $plugins_query_raw = "select plugins_key, plugins_data from " . TABLE_PLUGINS . " where back_end='1'";
      $plugins_array = $g_db->query_to_array($plugins_query_raw);

      for($i=0, $j=count($plugins_array); $i<$j; $i++) {

        $plugins_data = array();
        if( !empty($plugins_array[$i]['plugins_data']) ) {
          $tmp_data = unserialize($plugins_array[$i]['plugins_data']);
          if( !isset($tmp_data[$this->site_index]) ) continue;
          $plugins_data = $tmp_data[$this->site_index];
        }
        if( !isset($plugins_data['status_id']) || !$plugins_data['status_id'] ) continue;

        $plugin_key = $plugins_array[$i]['plugins_key'];
        $executive = DIR_WS_PLUGINS . $plugin_key . '/' . $this->prefix . $plugin_key . '.php';
        if( !file_exists($executive) ) {
          unset($plugins_array[$i]);
          continue;
        }
        require_once($executive);

        $plugins_array[$i]['sort_id'] = $plugins_data['sort_id'] . $plugin_key;
        $this->keys_array[$i] = $plugin_key;
      }
      $plugins_array = tep_array_invert_from_element($plugins_array, 'sort_id');
      ksort($plugins_array);
      $this->plugins_array = array_values($plugins_array);
      $this->keys_array = array_flip(array_values($this->keys_array));
    }

    function invoke() {
      global $g_ajax;

      $result = false;
      $args = func_get_args();
      if( !count($args) ) return $result;
      $method = array_shift($args);

      if( $g_ajax && $method != 'init_ajax' ) {
        return $result;
      }

      for($i=0, $j=count($this->plugins_array); $i<$j; $i++) {
        $executive = $this->prefix . $this->plugins_array[$i]['plugins_key'];
        if( !isset($this->objects_array[$i]) ) {
          $this->objects_array[$i] = new $executive();
        }
        // To pass argument references you need stack access first PHP >= 4.3
        $plugin = $this->objects_array[$i];
        if( method_exists($plugin, $method) ) {
          $pass_args = array();
          foreach($args as $value) {
            $pass_args[] = $value;
          }
          $tmp_result = call_user_func_array(array(&$plugin, $method), $pass_args);
          if( $tmp_result ) {
            $result = true;
          }
        }
      }
      return $result;
    }

    function is_loaded($plugin_key) {
      return isset($this->keys_array[$plugin_key]);
    }

    function is_active($plugin_key) {
      $result = false;
      if( isset($this->keys_array[$plugin_key]) ) {
        $plugin = $this->objects_array[$this->keys_array[$plugin_key]];
        $result = $plugin->active;
      }
      return $result;
    }

    function &get($plugin_key, $exit=true) {
      global $g_db, $g_session;

      $result = null;
      if( isset($this->keys_array[$plugin_key]) ) {
        return $this->plugins_array[$this->keys_array[$plugin_key]];
      }
      if( $exit ) {
        echo '<b style="color: #F00;">Critical: Requested plugin ' . $g_db->prepare_input($plugin_key, true) . ' is disabled or not properly installed.</b>';
        $g_session->close();
      }
      return $result;
    }
  }
?>
