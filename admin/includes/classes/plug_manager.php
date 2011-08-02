<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
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
*/
  class plug_manager {

    function plug_manager() {
      //$this->default_table_structure = ' ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
      $this->author = TEXT_INFO_NA;
      $this->version = TEXT_INFO_NA;
      $this->framework = TEXT_INFO_NA;
      $this->help = '';

      $this->front = 0;
      $this->back = 0;
      $this->status = 0;
      $this->install_progress = false;
      $this->files_array = array();
      $this->admin_files_array = array();
      $this->front_strings_array = array();
      $this->template_array = array();

      $this->site_index = tep_create_safe_string(tep_get_site_path(), '_', "/[^0-9a-z\-_]+/i");
      $string = get_class($this);
      $this->key = $this->title = substr($string, strlen(PLUGINS_INSTALL_PREFIX));

      $key_path = tep_trail_path($this->key);
      $this->web_path = DIR_WS_CATALOG_PLUGINS . $key_path;
      $this->web_template_path = DIR_WS_CATALOG_TEMPLATE . $key_path;
      $this->admin_path = DIR_FS_PLUGINS . $key_path;
      $this->template_path = '';

      if( !isset($this->options_array) || empty($this->options_array)) $this->options_array = array();
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
      extract(tep_load('database'));

      $index = $this->site_index;
      $plugins_query = $db->query("select plugins_data from " . TABLE_PLUGINS . " where plugins_key = '" . $db->filter($this->key) . "'");
      $plugins_array = $db->fetch_array($plugins_query);
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
      extract(tep_load('database'));

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
      $db->query("update " . TABLE_PLUGINS . " set plugins_data = '" . $db->filter($store_data) . "' where plugins_key = '" . $db->filter($this->key) . "'");
    }

    function backup_options() {
      extract(tep_load('database'));

      $result_array = array();
      $plugins_query = $db->query("select * from " . TABLE_PLUGINS . " where plugins_key = '" . $db->filter($this->key) . "'");
      if( !$db->num_rows($plugins_query) ) return $result_array;

      $plugins_array = $db->fetch_array($plugins_query);
      $result_array[] = "delete from " . TABLE_PLUGINS . " where plugins_key = '" . $db->filter($this->key) . "'";
      $result_array[] = "insert into " . TABLE_PLUGINS . " (" . implode(',',array_keys($plugins_array)) . ") values (" . implode(',',array_values($plugins_array)) . ")";
      return $result_array;
    }

    // Interface/Override/Base functions
    function install() {
      extract(tep_load('languages', 'database', 'message_stack'));

      $result_array = array();
      if( !$this->generate_string_folders() ) return $result_array;

      $result_array = $this->copy_files();

      $check_query = $db->query("select count(*) as total from " . TABLE_PLUGINS . " where plugins_key = '" . $db->filter($this->key) . "'");
      $check_array = $db->fetch_array($check_query);
      if( $check_array['total'] ) {
        $db->query("delete from " . TABLE_PLUGINS . " where plugins_key = '" . $db->filter($this->key) . "'");
        $msg->add_session(sprintf(WARNING_PLUGIN_REINSERT, $db->filter($this->title)), 'warning');
      }

      $index = $this->site_index;
      $plugins_data = array( $this->site_index => array(
        'status_id' => $this->status,
        'sort_id' => $check_array['total'],
      ));
      $store_data = serialize($plugins_data);

      $sql_data_array = array(
        'plugins_key' => $db->prepare_input($this->key),
        'plugins_name' => $db->prepare_input($this->title),
        'plugins_data' => $db->prepare_input($store_data),
        'plugins_version' => $db->prepare_input($this->version),
        'plugins_author' => $db->prepare_input($this->author),
        'date_added' => 'now()',
        'front_end' => (int)$this->front,
        'back_end' => (int)$this->back,
      );
      $db->perform(TABLE_PLUGINS, $sql_data_array);   

      $files_counter = count($this->files_array)+count($this->admin_files_array);
      if( !empty($this->front_strings_array) ) {
        $files_counter += count($lng->languages)*count($this->front_strings_array);
      }

      if( count($result_array) != $files_counter ) {
        $msg->add_session(sprintf(ERROR_PLUGIN_PARTIAL_INSTALL, $db->prepare_input($this->title)));
      } else {
        $msg->add_session(sprintf(SUCCESS_PLUGIN_INSTALLED, $db->prepare_input($this->title)), 'success');
      }
      return $result_array;
    }

    function uninstall() {
      extract(tep_load('languages', 'database', 'message_stack'));

      $result_array = $this->delete_files();
      $db->query("delete from " . TABLE_PLUGINS . " where plugins_key = '" . $db->filter($this->key) . "'");

      $files_counter = count($this->files_array)+count($this->admin_files_array);
      if( count($result_array) != $files_counter ) {
        $msg->add_session(sprintf(ERROR_PLUGIN_PARTIAL_UNINSTALL, $db->prepare_input($this->title)));
      } else {
        $msg->add_session(sprintf(SUCCESS_PLUGIN_UNINSTALLED, $db->prepare_input($this->title)), 'success');
      }
      return $result_array;
    }

    function re_copy_front() {
      extract(tep_load('languages', 'database', 'message_stack'));

      $result_array = $this->copy_files(true, false);

      $files_counter = count($this->files_array);
      if( !empty($this->front_strings_array) ) {
        $files_counter += count($lng->languages)*count($this->front_strings_array);
      }

      if( count($result_array) != $files_counter ) {
        $msg->add_session(sprintf(ERROR_PLUGIN_PARTIAL_INSTALL, $db->prepare_input($this->title)));
      } else {
        $msg->add_session(sprintf(SUCCESS_PLUGIN_INSTALLED, $db->prepare_input($this->title)), 'success');
      }
      // Make sure the plugin has valid options
      $this->save_options($this->load_options());
      return $result_array;
    }

    function revert_files() {
      extract(tep_load('languages', 'database', 'message_stack'));

      if( isset($_POST['database']) ) {
        $this->backup_database();
      }

      $result_array = $this->revert_copy(true, true);
      $files_counter = count($this->files_array)+count($this->admin_files_array)+count($this->template_array);

      if( !empty($this->front_strings_array) ) {
        $files_counter += count($lng->languages)*count($this->front_strings_array);
      }

      if( count($result_array) != $files_counter ) {
        $msg->add_session(sprintf(ERROR_PLUGIN_PARTIAL_REVERT, $db->prepare_input($this->title)));
      } else {
        if( isset($_POST['zip']) ) {
          $msg->add_session(sprintf(SUCCESS_PLUGIN_REVERT_ZIP, $db->prepare_input($this->title)), 'success');
        } else {
          $msg->add_session(sprintf(SUCCESS_PLUGIN_REVERTED, $db->prepare_input($this->title)), 'success');
        }
      }
      // Make sure the plugin has valid options
      $this->save_options($this->load_options());
      return $result_array;
    }

    function pre_install() { return true; }
    function pre_uninstall() { return true; }
    function pre_copy_front() { return true; }
    function pre_revert() { return true; }

    function is_installed() {
      extract(tep_load('database'));

      $check_query = $db->fly("select count(*) as total from " . TABLE_PLUGINS . " where plugins_key = '" . $db->filter($this->key) . "'");
      $check_array = $db->fetch_array($check_query);
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
    function revert_copy($front=true, $back=true) {
      extract(tep_load('languages', 'message_stack'));

      $result_front = $result_admin = $zip_array = array();

      if( $front ) {
        $front_files = $this->files_array;
        if( isset($this->options_array['template']) && !empty($this->options_array['template']) ) {
          $template_files = array();
          foreach($this->template_array as $key => $value) {
            $template_files[$this->template_path . tep_trail_path($this->options_array['template']) . $key] = $value;
          }
          $front_files = array_merge($front_files, $template_files);
        }

        if( !empty($this->front_strings_array) ) {
          foreach($lng->languages as $id => $params) {
            $fs_dir = tep_front_physical_path(DIR_WS_CATALOG_STRINGS . $params['language_path']);
            $string_files = array();
            foreach($this->front_strings_array as $dst) {
              $string_files[tep_trail_path($this->options_array['strings']) . tep_trail_path($params['language_path']) . $dst] = DIR_WS_CATALOG_STRINGS . tep_trail_path($params['language_path']) . tep_trail_path($this->key) . $dst;
            }
            if( !empty($string_files) ) {
              $front_files = array_merge($front_files, $string_files);
            }
          }
        }
        $fs_dir = tep_front_physical_path('', false);
        $result_front = $this->revert_paths($front_files, $fs_dir, $zip_array);
      }

      if( $back ) {
        $fs_dir = '';
        $result_admin = $this->revert_paths($this->admin_files_array, $fs_dir, $zip_array);
      }

      $result_array = array_merge($result_front, $result_admin);

      //if( isset($_POST['zip']) && !empty($zip_array) ) {
      //  $this->create_zip_file($zip_array);
      //}
      if( isset($_POST['zip']) ) {
        $this->create_zip_plugin();
      }
      return $result_array;
    }

    function copy_files($front=true, $back=true) {
      extract(tep_load('languages'));

      $result_front = $result_admin = $result_strings = array();

      if( $front ) {
        $front_files = $this->files_array;

        if( !empty($this->front_strings_array) ) {
          $lng->create_plugin_folders($this);

          foreach($lng->languages as $id => $params) {
            $fs_dir = tep_front_physical_path(DIR_WS_CATALOG_STRINGS . $params['language_path']);
            $tmp_array = array();

            foreach($this->front_strings_array as $dst) {
              $key = tep_trail_path($this->options_array['strings']) . tep_trail_path($params['language_path']) . $dst;
              if( is_file($this->admin_path . $key) ) {
                 $tmp_array[$key] = DIR_WS_CATALOG_STRINGS . tep_trail_path($params['language_path']) . tep_trail_path($this->key) . $dst;
                 $string_files[$key] = $tmp_array[$key];
              }
            }
            if( !empty($tmp_array) ) {
              $front_files = array_merge($front_files, $string_files);
            }
          }
        }
        $fs_dir = tep_front_physical_path('', false);
        $result_front = $this->copy_paths($front_files, $fs_dir);
      }
      if( $back ) {
        $fs_dir = DIR_FS_ADMIN;
        $result_admin = $this->copy_paths($this->admin_files_array, $fs_dir);
      }
      $result_array = array_merge($result_front, $result_admin);
      return $result_array;
    }

    function copy_paths($files_array, $fs_dir) {
      $zip_array = array();
      return $this->common_copy($files_array, $fs_dir, false, $zip_array);
    }

    function revert_paths($files_array, $fs_dir, &$zip_array) {
      return $this->common_copy($files_array, $fs_dir, true, $zip_array);
    }

    function common_copy($files_array, $fs_dir, $revert=false, &$zip_array) {
      extract(tep_load('message_stack'));

      $install_path = DIR_FS_PLUGINS.$this->key.'/';
      $result_array = array();
      $cleanup_array = array('/\\\\/', '/\/{2,}/');
      $filter = "/[^0-9a-z\-_\/\.]+/i";
      foreach($files_array as $key => $value) {
        $key = trim(preg_replace($cleanup_array, '/', $key), '/');
        $value = rtrim(preg_replace($cleanup_array, '/', $value), '/');
        if( !empty($key) ) {
          $org_key = $key;
          $key = tep_create_safe_string($key, '', $filter);
          if( $key != $org_key ) {
            $msg->add_session(sprintf(ERROR_INVALID_FILE_NAME, $org_key));
            continue;
          }
          $srctype = 'file';
        }
        $input_file = $install_path . $key;

        if( $srctype == 'file') {
          if( !$revert ) {
            //$install_path = DIR_FS_PLUGINS.$this->key.'/';
            //$input_file = $install_path . $key;

            if( is_file($fs_dir.$value) ) {
              @unlink($fs_dir.$value);
            }
            $tmp_array = explode('/', ltrim($value, '/'));
            array_pop($tmp_array);

            if( is_array($tmp_array) && count($tmp_array) ) {
              $sub_dir = '';
              if( substr($value,0,1) == '/' ) {
                $sub_dir = $fs_dir . '/';
              }
              for($i2=0, $j2=count($tmp_array); $i2<$j2; $i2++) {
                $sub_dir .= $tmp_array[$i2];

                if( !tep_mkdir($sub_dir) ) {
                  $msg->add_session(sprintf(ERROR_CREATE_DIR, $sub_dir));
                  return $result_array;
                }
                $sub_dir .= '/';
              }
            }

            if( !tep_read_contents($input_file, $contents) ) {
              $msg->add_session(sprintf(ERROR_INVALID_FILE, $input_file));
              continue;
            }

            if( !tep_write_contents($fs_dir.$value, $contents) ) {
              $msg->add_session(sprintf(ERROR_WRITING_FILE, $value));
              continue;
            }
          } else {
            if( !tep_read_contents($fs_dir.$value, $contents) ) {
              $msg->add_session(sprintf(ERROR_INVALID_FILE, $input_file));
              continue;
            }

            if( isset($_POST['zip']) ) {
              $zip_array[$key] = $contents;
            }
            if( !tep_write_contents($input_file, $contents) ) {
              $msg->add_session(sprintf(ERROR_WRITING_FILE, $value));
              continue;
            }
          }
          $result_array[$input_file] = $value;
        }
      }
      return $result_array;
    }


    function remove_admin_plugin() {
      tep_erase_dir(DIR_FS_PLUGINS . $this->key);
    }

    function delete_files() {

      $result_array = array();

      $fs_dir = tep_front_physical_path('', false);
      $result_front = $this->delete_paths($this->files_array, $fs_dir, ($this->front==1));
      $fs_dir = '';
      $result_admin = $this->delete_paths($this->admin_files_array, $fs_dir);
      $result_array = array_merge($result_front, $result_admin);

      if( isset($_POST['zip']) ) {
        if( !$this->create_zip_plugin() ) return $result_array;

        $pdir = DIR_FS_PLUGINS.$this->key.'/';
        $root_array = glob($pdir . '*');

        foreach($root_array as $value) {
          if( is_file($value) ) {
            if( $this->key.'.zip' == basename($value) ) continue;
            @unlink($value);
          } else {
            tep_erase_dir($value);
          }
        }
      }
      return $result_array;
    }

    function delete_paths($input_array, $fs_dir, $erase_front=false) {
      extract(tep_load('languages'));

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
        $lng->delete_plugin_folders($this->key);
      }

      return $result_array;
    }

    function delete_configuration_references($config_array) {
      extract(tep_load('database'));
      $db->query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . $db->filter(implode("', '", $config_array)) . "')");
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
        if( is_file($this->admin_path . $key) ) {
          $this->files_array[$key] = $value;
        }
      }
    }

    function set_posted_template($load=true) {
      if( !isset($this->options_array['template']) ) {
        $this->options_array['template'] = 'stock';
      }
      $template = $this->options_array['template'];
      if( isset($_POST['template']) ) {
        $template = tep_create_safe_string($_POST['template'], '_', "/[^0-9a-z\-_\/]+/i");
        $this->options_array['template'] = $template;
      }
      if( !$load ) return $template;

      $this->load_template_files($template);
      $options = $this->load_options();
      $options['template'] = $template;
      $this->save_options($options);
      return $template;
    }


    function create_zip_file($zip_array) {
      extract(tep_load('message_stack'));

      if( !isset($_POST['zip']) || empty($zip_array) ) return;

      $pdir = DIR_FS_PLUGINS.$this->key.'/';
      $zip_file = $pdir.$this->key.'.zip';

      $root_array = array_filter(glob($pdir . '*'), 'is_file');
      foreach($root_array as $value) {
        $value = strtolower(basename($value));
        $value = preg_replace('/\s\s+/', ' ', trim($value));
        $value = preg_replace("/[^0-9a-z\-_.\/]+/i", '_', strtolower($value));
        if( substr($value, -4) == '.zip' ) continue;

        $input_file = $pdir . $value;
        if( !tep_read_contents($pdir.$value, $contents) ) {
          $msg->add_session(sprintf(ERROR_INVALID_FILE, $input_file));
          continue;
        }
        $zip_array[$value] = $contents;
      }

      $zip_data = tep_compress($zip_array);
      if( !tep_write_contents($zip_file, $zip_data) ) {
        $msg->add_session(sprintf(ERROR_WRITING_FILE, $zip_file));
      }
    }

    function create_zip_plugin() {
      $result = false;
      $pdir = DIR_FS_PLUGINS.$this->key.'/';
      $zip_file = $pdir.$this->key.'.zip';

      $cZip = new pkzip;
      $cZip->addDir($pdir);

      if( tep_write_contents($zip_file, $cZip->file()) ) {
        $result = true;
      }
      return $result;
    }

    function backup_database() {
      extract(tep_load('database', 'message_stack'));
      $msg->add_session(sprintf(WARNING_PLUGIN_NO_DATABASE, $db->prepare_input($this->title)), 'warning');
      return false;
    }

    function create_language_tables($tables_array) {
      extract(tep_load('languages'));
      foreach($tables_array as $key => $value) {
        $lng->create_table($value);
      }
    }

    function delete_language_tables($tables_array) {
      extract(tep_load('languages'));
      foreach($tables_array as $key => $value) {
        $lng->delete_table($value);
      }
    }

    function generate_string_folders() {
      extract(tep_load('languages', 'database', 'message_stack'));

      $result = true;
      if( empty($this->front_strings_array) && !isset($this->options_array['strings']) ) return $result;

      $result = false;     
      $present_folders = array();
      $missing_folders = array();
      foreach($lng->languages as $id => $params) {
        $check_path = $this->admin_path . tep_trail_path($this->options_array['strings']) . $params['language_path'];
        if( !is_dir($check_path) ) {
          $missing_folders[] = $check_path;
        } else {
          $file_path = tep_trail_path($check_path, true);
          for($i=0, $j=count($this->front_strings_array); $i<$j; $i++) {
            $file = $file_path . $this->front_strings_array[$i];
            if( is_file($file) ) {
              $present_folders[] = $check_path;
              break;
            }
          }
        }
      }
      if( empty($present_folders) ) {
        $msg->add_session(sprintf(ERROR_PLUGIN_MISSING_STRINGS, $db->prepare_input($this->title)) );
        return $result;
      }

      if( !empty($missing_folders) ) {
        $present = $present_folders[0];
        for($i=0, $j=count($missing_folders); $i<$j; $i++) {
          tep_copy_dir($present, $missing_folders[$i]);
        }
        $msg->add_session(sprintf(WARNING_PLUGIN_MISSING_STRINGS, $db->prepare_input($this->title)), 'warning');
      }
      $result = true;
      return $result;
    }
  }
?>
