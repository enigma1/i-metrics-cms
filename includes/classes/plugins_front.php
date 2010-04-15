<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Plugins Invocation Class
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
  class plugins_front {
    var $plugins_array, $keys_array, $objects_array, $log_array;

    function plugins_front() {
      $this->log_array = $this->keys_array = $this->objects_array = array();
      $this->site_index = tep_create_safe_string(tep_get_site_path(), '_', "/[^0-9a-z\-_]+/i");
      $this->enumerate();
    }

    // Interface/Override functions
    function enumerate() {
      global $g_db;

      $plugins_query_raw = "select plugins_key, plugins_data from " . TABLE_PLUGINS . " where front_end='1'";
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
        $executive = DIR_WS_PLUGINS . $plugin_key . '/' . $plugin_key . '.php';
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
      global $g_ajax, $g_development;

      $result = false;
      $args = func_get_args();
      if( !count($args) ) return $result;
      $method = array_shift($args);

      if( $g_ajax && $method != 'init_ajax' ) {
        return $result;
      }

      for($i=0, $j=count($this->plugins_array); $i<$j; $i++) {
        $executive = $this->plugins_array[$i]['plugins_key'];
        if( !isset($this->objects_array[$i]) ) {
          $this->objects_array[$i] = new $executive();
        }
        $handled = $tmp_result = false;
        $plugin = $this->objects_array[$i];
        if( $plugin->active && method_exists($plugin, $method) ) {
          $handled = true;
          $pass_args = array();
          foreach($args as $value) {
            $pass_args[] = $value;
          }
          $tmp_result = call_user_func_array(array(&$plugin, $method), $pass_args);
          if( $tmp_result ) {
            $result = true;
          }
        }
        if( $g_development && $handled ) {
          $name = $plugin->key;
          if( empty($name) ) $name = '<b style="color: #F00">Error: invalid key for ' . $this->plugins_array[$i]['plugins_key'] . '</b>';
          $this->log_array[] = array(
            'plugin_name' => $name,
            'called_on' => $method,
            'response' => $tmp_result?'handled':'not handled',
          );
        }
      }
      if( $g_development && $method == 'final_terminate') {
        echo '<div><pre>' . "\n";
        var_dump($this->log_array);
        echo '</pre></div>' . "\n";
      }
      return $result;
    }

    function is_active($plugin_key) {
      $result = false;
      if( isset($this->keys_array[$plugin_key]) ) {
        $plugin = $this->objects_array[$this->keys_array[$plugin_key]];
        $result = $plugin->active;
      }
      return $result;
    }

    function &get($plugin_key, $exit=false) {
      global $g_db, $g_session;

      $result = null;
      if( isset($this->keys_array[$plugin_key]) ) {
        return $this->plugins_array[$this->keys_array[$plugin_key]];
      }
      if( $exit ) {
        echo '<b style="color: #F00;">Critical: Requested plugin ' . $g_db->prepare_input($plugin_key) . ' is disabled or not properly installed.</b>';
        $g_session->close();
      }
      return $result;
    }

  }
?>
