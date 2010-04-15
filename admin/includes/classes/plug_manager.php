<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Install Plugins Manager Base Class
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
  class plug_manager {
    var $files_array, $admin_files_array, $title, $plugins_group_id, $cfg_sort_id, $key, $web_path, $admin_path, $template_path, $options_array;
    var $version, $framework, $author, $help;

    function plug_manager() {
      global $g_db;

      //$this->default_table_structure = ' TYPE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
      $this->author = 'N/A';
      $this->version = 'N/A';
      $this->framework = 'N/A';
      $this->help = '';

      $this->front = 0;
      $this->back = 0;
      $this->status = 0;
      $this->files_array = array();
      $this->admin_files_array = array();
      $this->template_array = array();

      $this->site_index = tep_create_safe_string(tep_get_site_path(), '_', "/[^0-9a-z\-_]+/i");
      $string = get_class($this);
      $this->key = $this->title = substr($string, strlen(PLUGINS_INSTALL_PREFIX));

      $this->web_path = DIR_WS_CATALOG_PLUGINS.$this->key.'/';
      $this->web_template_path = DIR_WS_CATALOG_TEMPLATE.$this->key.'/';
      $this->admin_path = DIR_WS_PLUGINS.$this->key.'/';
      $this->template_path = '';
      if( empty($this->options_array)) $this->options_array = array();
    }

    function validate_options($options_array) {
      $stored_options = $this->load_options();
      unset(
        $options_array['sort_id'],
        $options_array['status_id'],
        $stored_options['sort_id'],
        $stored_options['status_id']
      );

      if( count($stored_options) != count($options_array) ) {
        $this->save_options($options_array);
        $stored_options = $options_array;
      }
      return $stored_options;
    }

    // Retrieve plugin configuration data from the database
    function load_options($all=false) {
      global $g_db;
      $index = $this->site_index;
      $plugins_query = $g_db->query("select plugins_data from " . TABLE_PLUGINS . " where plugins_key = '" . $g_db->filter($this->key) . "'");
      $plugins_array = $g_db->fetch_array($plugins_query);
      $plugins_data = array();
      if( !empty($plugins_array['plugins_data']) ) {
        $plugins_data = unserialize($plugins_array['plugins_data']);
      }

      if( !$all ) {
        if( isset($plugins_data[$index]) ) {
          $plugins_data = $plugins_data[$index];
        } elseif( isset($this->options_array) )  {
          $plugins_data = $this->options_array;
        }
      }
      return $plugins_data;
    }

    // Save plugin configuration data from the database
    function save_options($input_array) {
      global $g_db;

      if( empty($input_array) || !is_array($input_array) ) $input_array = array();

      $index = $this->site_index;
      $plugins_data = $this->load_options(true);

      if( !isset($plugins_data[$index]['status_id']) ) {
        $plugins_data[$index]['status_id'] = 0;
      }
      if( !isset($plugins_data[$index]['sort_id']) ) {
        $plugins_data[$index]['sort_id'] = 100;
      }
      if( !isset($plugins_data[$index]['template']) ) {
        $plugins_data[$index]['template'] = 'stock';
      }

      if( !isset($input_array['status_id']) ) {
        $input_array['status_id'] = $plugins_data[$index]['status_id'];
      }
      if( !isset($input_array['sort_id']) ) {
        $input_array['sort_id'] = $plugins_data[$index]['sort_id'];
      }
      if( !isset($input_array['template']) ) {
        $input_array['template'] = $plugins_data[$index]['template'];
      }

      $plugins_data[$index] = $input_array;
      $store_data = serialize($plugins_data);
      $g_db->query("update " . TABLE_PLUGINS . " set plugins_data = '" . $g_db->filter($store_data) . "' where plugins_key = '" . $g_db->filter($this->key) . "'");
    }

    // Interface/Override/Base functions
    function install() {
      global $g_db, $messageStack;

      $result_array = $this->copy_files();

      $check_query = $g_db->query("select count(*) as total from " . TABLE_PLUGINS . " where plugins_key = '" . $g_db->filter($this->key) . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( $check_array['total'] ) {
        $g_db->query("delete from " . TABLE_PLUGINS . " where plugins_key = '" . $g_db->filter($this->key) . "'");
        $messageStack->add_session(sprintf(WARNING_PLUGIN_REINSERT, $g_db->filter($this->title)), 'warning');
      }

      $index = $this->site_index;
      $plugins_data = array( $this->site_index => array(
        'status_id' => $this->status,
        'sort_id' => $check_array['total'],
      ));
      $store_data = serialize($plugins_data);

      $sql_data_array = array(
        'plugins_key' => $g_db->prepare_input($this->key),
        'plugins_name' => $g_db->prepare_input($this->title),
        'plugins_data' => $g_db->prepare_input($store_data),
        'plugins_version' => $g_db->prepare_input($this->version),
        'plugins_author' => $g_db->prepare_input($this->author),
        'date_added' => 'now()',
        'front_end' => (int)$this->front,
        'back_end' => (int)$this->back,
      );
      $g_db->perform(TABLE_PLUGINS, $sql_data_array);   

      $files_counter = count($this->files_array)+count($this->admin_files_array);
      if( count($result_array) != $files_counter ) {
        $messageStack->add_session(sprintf(ERROR_PLUGIN_PARTIAL_INSTALL, $g_db->prepare_input($this->title)));
      } else {
        $messageStack->add_session(sprintf(SUCCESS_PLUGIN_INSTALLED, $g_db->prepare_input($this->title)), 'success');
      }
      return $result_array;
    }

    function uninstall() {
      global $g_db, $messageStack;

      $result_array = $this->delete_files();
      $g_db->query("delete from " . TABLE_PLUGINS . " where plugins_key = '" . $g_db->filter($this->key) . "'");

      $files_counter = count($this->files_array)+count($this->admin_files_array);
      if( count($result_array) != $files_counter ) {
        $messageStack->add_session(sprintf(ERROR_PLUGIN_PARTIAL_UNINSTALL, $g_db->prepare_input($this->title)));
      } else {
        $messageStack->add_session(sprintf(SUCCESS_PLUGIN_UNINSTALLED, $g_db->prepare_input($this->title)), 'success');
      }
      return $result_array;
    }

    function re_copy_front() {
      global $g_db, $messageStack;

      $result_array = $this->copy_files(true, false);

      $files_counter = count($this->files_array);
      if( count($result_array) != $files_counter ) {
        $messageStack->add_session(sprintf(ERROR_PLUGIN_PARTIAL_INSTALL, $g_db->prepare_input($this->title)));
      } else {
        $messageStack->add_session(sprintf(SUCCESS_PLUGIN_INSTALLED, $g_db->prepare_input($this->title)), 'success');
      }
      // Make sure the plugin has valid options
      $this->save_options($this->load_options());
      return $result_array;
    }

    function pre_install() { return true; }
    function pre_uninstall() { return true; }
    function pre_copy_front() { return true; }

    function is_installed() {
      global $g_db;
      $check_query = $g_db->fly("select count(*) as total from " . TABLE_PLUGINS . " where plugins_key = '" . $g_db->filter($this->key) . "'");
      $check_array = $g_db->fetch_array($check_query);
      return ($check_array['total'] > 0);
    }

    function change($status, $sort='') {
      $plugins_data = $this->load_options();
      $plugins_data['status_id'] = ($status==1)?1:0;
      if( !empty($sort) ) {
        $plugins_data['sort_id'] = (int)$sort;
      }
      $this->save_options($plugins_data);
    }

    function is_enabled() {
      $plugins_data = $this->load_options();
      return (isset($plugins_data['status_id']) && $plugins_data['status_id'] == 1);
    }

    function is_present() {
      $plugins_data = $this->load_options();
      return isset($plugins_data['status_id']);
    }

    // Helper functions
    function copy_files($front=true, $back=true) {
      $result_front = $result_admin = array();

      if( $front ) {
        $fs_dir = tep_front_physical_path('', false);
        $result_front = $this->copy_paths($this->files_array, $fs_dir);
      }
      if( $back ) {
        $fs_dir = '';
        $result_admin = $this->copy_paths($this->admin_files_array, $fs_dir);
      }
      $result_array = array_merge($result_front, $result_admin);
      return $result_array;
    }

    function copy_paths($files_array, $fs_dir) {
      global $messageStack;

      $result_array = array();
      $install_path = DIR_WS_PLUGINS.$this->key.'/';
      $cleanup_array = array('/\\\\/', '/\/{2,}/');
      $filter = "/[^0-9a-z\-_\/\.]+/i";
      foreach($files_array as $key => $value) {
        $key = trim(preg_replace($cleanup_array, '/', $key), '/');
        $value = rtrim(preg_replace($cleanup_array, '/', $value), '/');
        if( !empty($key) ) {
          $org_key = $key;
          $key = tep_create_safe_string($key, '', $filter);
          if( $key != $org_key ) {
            $messageStack->add_session(sprintf(ERROR_INVALID_FILE_NAME, $org_key));
            continue;
          }
          $srctype = 'file';
        }
        $input_file = $install_path . $key;
        if( $srctype == 'file') {
          if( file_exists($fs_dir.$value) ) {
            @unlink($fs_dir.$value);
          }
          $tmp_array = explode('/', ltrim($value, '/'));
          array_pop($tmp_array);

          if( is_array($tmp_array) && count($tmp_array) ) {
            $sub_dir = '';
            if( substr($value,0,1)=='/' ) {
              $sub_dir = $fs_dir . '/';
            }
            for($i2=0, $j2=count($tmp_array); $i2<$j2; $i2++) {
              $sub_dir .= $tmp_array[$i2];
              $old_mask = umask(0);
              if( !is_dir($sub_dir) && !@mkdir($sub_dir, 0777) ) {
                umask($old_mask);
                $messageStack->add_session(sprintf(ERROR_CREATE_DIR, $sub_dir));
                return $result_array;
              }
              umask($old_mask);
              $sub_dir .= '/';
            }
          }
          $contents = '';
          if( !tep_read_contents($input_file, $contents) ) {
            $messageStack->add_session(sprintf(ERROR_INVALID_FILE, $input_file));
            continue;
          }

          if( !tep_write_contents($fs_dir.$value, $contents) ) {
            $messageStack->add_session(sprintf(ERROR_WRITING_FILE, $value));
            continue;
          }
          $result_array[$input_file] = $value;
        }
      }
      return $result_array;
    }


    function remove_admin_plugin() {
      tep_erase_dir(DIR_WS_PLUGINS . $this->key);
    }

    function delete_files() {
      $result_array = array();
      $fs_dir = tep_front_physical_path('', false);
      $result_front = $this->delete_paths($this->files_array, $fs_dir, ($this->front==1));
      $fs_dir = '';
      $result_admin = $this->delete_paths($this->admin_files_array, $fs_dir);
      $result_array = array_merge($result_front, $result_admin);
      return $result_array;
    }

    function delete_paths($input_array, $fs_dir, $erase_front=false) {
      $result_array = array();
      $cleanup_array = array('/\\\\/', '/\/{2,}/');

      foreach($input_array as $key => $value) {
        $value = rtrim(preg_replace($cleanup_array, '/', $value), '/');
        @unlink($fs_dir . $value);
        $result_array[$key] = $value;
      }

      if( $erase_front ) {
        $dir = $fs_dir . DIR_WS_CATALOG_PLUGINS . $this->key;
        if( is_dir($dir) ) {
          tep_erase_dir($dir);
        }
        $dir = $fs_dir . DIR_WS_CATALOG_TEMPLATE . $this->key;
        if( is_dir($dir) ) {
          tep_erase_dir($dir);
        }
      }

      return $result_array;
    }

    function delete_configuration_references($config_array) {
      global $g_db;
      $g_db->query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . $g_db->filter(implode("', '", $config_array)) . "')");
    }

    function get_templates() {
      $result_array = array();
      if( empty($this->template_path ) ) return $result_array;
      $dir_array = array_filter(glob($this->admin_path . $this->template_path . '*'), 'is_dir');
      foreach($dir_array as $key => $value) {
        $value = tep_create_safe_string($value, '_', "/[^0-9a-z\-_\/]+/i");
        $result_array[] = array(
          'id' => basename($value),
          'text' => basename($value),
        );
      }
      return $result_array;
    }

    function load_template_files($template) {
      if( empty($this->template_path ) ) return;

      foreach( $this->template_array as $key => $value ) {
        $key = $this->template_path . $template . '/' .  $key;
        if( file_exists($this->admin_path . $key) ) {
          $this->files_array[$key] = $value;
        }
      }
    }

    function set_posted_template() {
      if( !isset($this->options_array['template']) ) {
        $this->options_array['template'] = 'stock';
      }
      $template = $this->options_array['template'];
      if( isset($_POST['template']) ) {
        $template = tep_create_safe_string($_POST['template'], '_', "/[^0-9a-z\-_\/]+/i");
        $this->options_array['template'] = $template;
      }
      $this->load_template_files($template);
      $options = $this->load_options();
      $options['template'] = $template;
      $this->save_options($options);
      return $template;
    }

  }
?>
