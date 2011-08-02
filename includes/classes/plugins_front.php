<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
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
    // Compatibility Constructor
    function plugins_front() {
      $this->plugins_array = $this->log_array = $this->keys_array = $this->objects_array = array();
      $this->site_index = tep_create_safe_string(tep_get_site_path(), '_', "/[^0-9a-z\-_]+/i");
      $this->ajax_prefix = PLUGINS_AJAX_PREFIX;
      $this->enumerate();
    }

    // Interface/Override functions
    function enumerate() {
      extract(tep_load('defs', 'database'));

      $plugins_query_raw = "select plugins_key, plugins_data from " . TABLE_PLUGINS . " where front_end='1'";
      $plugins_array = $db->query_to_array($plugins_query_raw);

      for($i=0, $j=count($plugins_array); $i<$j; $i++) {

        $plugins_data = array();
        if( !empty($plugins_array[$i]['plugins_data']) ) {
          $tmp_data = unserialize($plugins_array[$i]['plugins_data']);
          if( !isset($tmp_data[$this->site_index]) ) continue;
          $plugins_data = $tmp_data[$this->site_index];
        }
        if( !isset($plugins_data['status_id']) || !$plugins_data['status_id'] ) continue;
        if( isset($plugins_data['fscripts']) && is_array($plugins_data['fscripts']) ) {
          for($load=false, $i2=0, $j2=count($plugins_data['fscripts']); $i2<$j2; $i2++) {
            if( $plugins_data['fscripts'][$i2] == $cDefs->script ) {
              $load = true;
              break;
            }
          }
          if( !$load ) {
            tep_log($plugin_key . ' request for ' . $cDefs->script . ' is undefined');
            continue;
          }
        }

        $plugin_key = $plugins_array[$i]['plugins_key'];
        $executive = DIR_FS_PLUGINS . $plugin_key . '/' . $plugin_key . '.php';
        if( !is_file($executive) ) {
          tep_log('Cannot find file ' . $executive);
          unset($plugins_array[$i]);
          continue;
        }
        include_once($executive);
        if( !class_exists($plugin_key) ) {
          tep_log('Cannot find class ' . $plugin_key);
          unset($plugins_array[$i]);
          continue;
        }

        $plugins_array[$i]['sort_id'] = $plugins_data['sort_id'] . $plugin_key;
        $this->keys_array[$plugins_data['sort_id'] . $plugin_key] = $plugin_key;
      }
      $plugins_array = tep_array_invert_from_element($plugins_array, 'sort_id');
      ksort($plugins_array, SORT_NUMERIC);
      ksort($this->keys_array, SORT_NUMERIC);
      $this->plugins_array = array_values($plugins_array);
      $this->keys_array = array_flip(array_values($this->keys_array));
    }

    function invoke() {
      extract(tep_load('defs', 'debug'));

      $result = false;
      $args = func_get_args();
      if( !count($args) ) return $result;
      $method = array_shift($args);

      if( $cDefs->ajax && substr($method, 0, strlen($this->ajax_prefix)) != $this->ajax_prefix ) {
        //return $result;
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

        if( $cDebug->development && $handled ) {
          $name = $plugin->key;
          if( empty($name) ) $name = '<b style="color: #F00">Error: invalid key for ' . $this->plugins_array[$i]['plugins_key'] . '</b>';
          $this->log_array[] = array(
            'plugin_name' => $name,
            'called_on' => $method,
            'response' => $tmp_result?'handled':'not handled',
          );
        }
      }
      if( $cDebug->development && $method == 'final_terminate') {
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
      extract(tep_load('database', 'sessions'));

      $result = null;
      if( isset($this->keys_array[$plugin_key]) ) {
        return $this->objects_array[$this->keys_array[$plugin_key]];
      }
      if( $exit ) {
        echo '<b style="color: #F00;">Critical: Requested plugin ' . $db->prepare_input($plugin_key) . ' is disabled or not properly installed.</b>';
        $cSessions->close();
      }
      return $result;
    }

    function get_options($plugin_key, $error_exit=true) {
      extract(tep_load('database', 'sessions'));

      $result = null;
      if( isset($this->keys_array[$plugin_key]) ) {
        $object_array = $this->plugins_array[$this->keys_array[$plugin_key]];
        $data = unserialize($object_array['plugins_data']);
        return current($data);
      }
      if( $error_exit ) {
        echo '<b style="color: #F00;">Critical: Requested plugin data ' . $db->prepare_input($plugin_key, true) . ' - plugin is disabled or not properly installed.</b>';
        $cSessions->close();
      }
      return $result;
    }

  }
?>
