<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
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
*/
  class plugins_admin {

    // Compatibility constructor
    function plugins_admin() {
      require_once(DIR_FS_CLASSES . 'plugins_base.php');
      $this->site_index = tep_create_safe_string(tep_get_site_path(), '_', "/[^0-9a-z\-_]+/i");
      $this->prefix = PLUGINS_ADMIN_PREFIX;
      $this->ajax_prefix = PLUGINS_AJAX_PREFIX;
      $this->plugins_array = $this->keys_array = $this->objects_array = array();
      $this->enumerate();
    }

    // Interface/Override functions
    function enumerate() {
      extract(tep_load('defs', 'database'));

      $plugins_query_raw = "select plugins_key, plugins_data from " . TABLE_PLUGINS . " where back_end='1'";
      $plugins_array = $db->query_to_array($plugins_query_raw);

      for($i=0, $j=count($plugins_array); $i<$j; $i++) {
        $plugins_array[$i]['display_box'] = false;
        $plugins_array[$i]['status'] = true;
        $plugins_data = array();

        if( !empty($plugins_array[$i]['plugins_data']) ) {
          $tmp_data = unserialize($plugins_array[$i]['plugins_data']);
          if( !isset($tmp_data[$this->site_index]) ) continue;
          $plugins_data = $tmp_data[$this->site_index];
        }
        if( !isset($plugins_data['status_id']) || !$plugins_data['status_id'] ) continue;

        if( $cDefs->script != FILENAME_PLUGINS && isset($plugins_data['ascripts']) && !in_array($cDefs->script, $plugins_data['ascripts']) ) {
          //if( !$cDefs->ajax && isset($plugins_data['abox']) ) {
         if( isset($plugins_data['abox']) ) {
            $plugins_array[$i]['display_box'] = true;
          } else {
            continue;
          }
        }

        $plugin_key = $plugins_array[$i]['plugins_key'];
        $executive = DIR_FS_PLUGINS . $plugin_key . '/' . $this->prefix . $plugin_key . '.php';
        if( !is_file($executive) ) {
          tep_log('Cannot find file ' . $executive);
          unset($plugins_array[$i]);
          continue;
        }
        include_once($executive);
        if( !class_exists($this->prefix . $plugin_key) ) {
          tep_log('Cannot find class ' . $this->prefix . $plugin_key);
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

    // Application processing flow by the main scripts into the plugins
    function invoke() {
      extract(tep_load('defs'));
      $result = false;
      $args = func_get_args();
      if( !count($args) ) return $result;
      $method = array_shift($args);

      if( $cDefs->ajax && substr($method, 0, strlen($this->ajax_prefix)) != $this->ajax_prefix ) {
        //return $result;
      }
      for($i=0, $j=count($this->plugins_array); $i<$j; $i++) {
        $executive = $this->prefix . $this->plugins_array[$i]['plugins_key'];
        if( !isset($this->objects_array[$i]) ) {
          if( $this->plugins_array[$i]['display_box'] && $method != 'init_sessions') continue;

          if( $this->plugins_array[$i]['display_box'] && $method == 'init_sessions') {
            extract(tep_load('sessions'));

            $box_method =& $cSessions->register('selected_box', 'config_box');
            if( isset($_GET['selected_box']) && !empty($_GET['selected_box']) ) {
              $box_method = tep_sanitize_string($_GET['selected_box']);
            }

            if( !method_exists($executive, $box_method) ) {
              $this->plugins_array[$i]['status'] = false;
            }
          }
          if( $this->plugins_array[$i]['status'] ) {
            $this->objects_array[$i] = new $executive();
          } else {
            tep_log('Invalid Plugin ' . $executive);
            continue;
          }
        }
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

    function &get($plugin_key='', $error_exit=true) {
      extract(tep_load('database', 'sessions'));

      $result = null;

      if( empty($plugin_key) ) {
        $plugin_key = tep_get_script_name();
      }

      if( isset($this->keys_array[$plugin_key]) ) {
        return $this->objects_array[$this->keys_array[$plugin_key]];
      }

      if( $error_exit ) {
        echo '<b style="color: #F00;">Critical: Requested plugin ' . $db->prepare_input($plugin_key, true) . ' is disabled or not properly installed.</b>';
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

        $tmp_data = unserialize($object_array['plugins_data']);
        if( isset($tmp_data[$this->site_index]) ) {
          $data = $tmp_data[$this->site_index];
          return $data;
        }
      }
      if( $error_exit ) {
        echo '<b style="color: #F00;">Critical: Requested plugin data ' . $db->prepare_input($plugin_key, true) . ' - plugin is disabled or not properly installed.</b>';
        $cSessions->close();
      }
      return $result;
    }

    // Use to add system components to the plugins queue
    function set($plugin_key='') {
      extract(tep_load('system_base'));

      if( empty($plugin_key) ) {
        $plugin_key = tep_get_script_name();
      }

      if( isset($this->keys_array[$plugin_key]) ) return true;

      $executive = DIR_FS_PLUGINS . $this->prefix . $plugin_key . '.php';

      if( !is_file($executive) ) {
        $plugin_key = 'stub';
        $executive = DIR_FS_PLUGINS . $this->prefix . $plugin_key . '.php';
      }

      include_once($executive);
      if( !class_exists($this->prefix . $plugin_key) ) {
        return false;
      }

      $index = count($this->plugins_array);
      $this->keys_array[$plugin_key] = $index;
      $this->plugins_array[$index] = array(
        'plugins_key' => $plugin_key,
        'status' => true,
        'display_box' => false
      );
      $result = true;
      return $result;
    }
  }
?>
