<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Multi-Lingual Support Class
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
  class languages {
    // Compatibility Constructor
    function languages() {
      $this->cookie = false;
      $this->default = 0;
      $this->code = '';
      $this->current = '';
      $this->path = '';
      $this->postfix = '';
      $this->languages = array();
      $this->tables = array();
      $this->default_tables = array();
      $this->database_tables = array();
      $this->name = 'language';
      $this->new_id = false;
      $this->started = false;
    }

    function initialize() {
      extract(tep_load('http_headers', 'database'));

      if( !$this->reset() ) return;

      $languages_query_raw = "select language_id, language_name, language_path, language_code from " . TABLE_LANGUAGES . " order by sort_id";
      $this->languages = $db->query_to_array($languages_query_raw, 'language_id');

      $language = isset($_GET[$this->name])?(int)$_GET[$this->name]:0;
      if( !$language ) {
        $language = (int)$this->get_cookie($this->name);
      }

      $error = false;
      if( empty($language) || !isset($this->languages[$language]) ) {
        if( empty($this->languages) ) return;

        $tmp_array = array(
          'language_id' => $this->default,
          'language_path' => $this->path,
          'language_code' => $this->code,
        );
        $error = true;
      } else {
        $tmp_array = $this->languages[$language];
      }

      $this->current = $tmp_array['language_id'];
      $this->path = $tmp_array['language_path'];
      $this->code = $tmp_array['language_code'];

      if( !$error && isset($_COOKIE[$this->name]) && $this->current == $_COOKIE[$this->name] ) {
        $this->cookie = true;
      } else {
        $this->new_id = true;
        unset($_COOKIE[$this->name]);
        unset($_GET[$this->name]);
        $http->set_cookie($this->name);
        $http->set_cookie($this->name, $this->current, -1);
      }

      if( $this->default ) {
        $fs_includes = tep_front_physical_path(DIR_WS_CATALOG_INCLUDES);
        $this->define_file($fs_includes . 'database_language.php');
      }
      tep_define_vars(DIR_FS_INCLUDES . 'database_tables.php');
      $this->started = true;
    }

    function reset() {
      extract(tep_load('database'));

      if( !defined('TABLE_LANGUAGES') ) {
        define('TABLE_LANGUAGES', 'languages');
      }

      $default_query = $db->fly("select language_id, language_path from " . TABLE_LANGUAGES . " where language_code = ''");
      if( !$db->num_rows($default_query) ) {
        return false;
      }

      $default_array = $db->fetch_array($default_query);

      $error = true;
      $fs_strings = tep_front_physical_path(DIR_WS_CATALOG_STRINGS);
      $dir_array = array_filter(glob($fs_strings . '*'), 'is_dir');

      foreach($dir_array as $key => $value ) {
        if( basename($value) == $default_array['language_path'] ) {
          $error = false;
          break;
        }
      }
      if( $error ) return false;

      $this->path = $default_array['language_path'];
      $this->default = $this->current = $default_array['language_id'];
      return true;
    }

    function define_file($metrics_file) {
      $this->tables = array();
      if( !is_file($metrics_file) ) return false;
      require($metrics_file);
      $vars_array = get_defined_vars();
      unset($vars_array['metrics_file'], $vars_array['this']);

      foreach( $vars_array as $key => $value ) {
        $this->default_tables[$key] = $value;
        if( !defined($key) ) {
          if( !empty($this->code) ) {
            $value .= '_' . $this->code;
          }
          define($key, $value);
          $this->tables[$key] = $value;
        }
      }
      $this->tables = array_flip($this->tables);
      return true;
    }

    function get_language_name($language_id='') {
      if( empty($language_id) ) {
        $language_id = $this->current;
      }
      $language_name = '';
      if( isset($this->languages[$language_id]) ) {
        $language_name = $this->languages[$language_id]['language_name'];
      }
      return $language_name;
    }

    function get_table_language($table) {
      extract(tep_load('database'));

      $result_array = array();
      $parts_array = explode('_', $table);
      $code = array_pop($parts_array);
      if( !empty($parts_array) && strlen($code) == 2) {
        $code_array = tep_array_invert_from_element($this->languages, 'language_code');
        if( !empty($code_array) && isset($code_array[$code]) ) {
          $result_array = $code_array[$code];
        }
      }

      if( empty($result_array) ) {
        $check_array = array_flip($this->default_tables);
        if( isset($check_array[$table]) ) {
          $default_query = $db->query("select language_id, language_name, language_path, language_code from " . TABLE_LANGUAGES . " where language_id = '" . (int)$this->default . "'");
          $result_array = $db->fetch_array($default_query);
        }
      }
      return $result_array;
    }

    // For a given database table get all associated language tables
    function get_language_tables($table) {
      if( !isset($this->tables[$table]) ) return array();
      $key = $this->tables[$table];
      return $this->get_tables($key);
    }

    // For a given database table definition key get all associated language tables
    function get_tables($key) {
      $result_array = array();
      if( !defined($key) || !$this->default ) return $result_array;

      $tmp_keys = $this->default_tables;

      if( empty($tmp_keys) || !isset($tmp_keys[$key]) ) {
        $result_array[] = constant($key);
        return $result_array;
      }

      $this->set_db_tables();

      $base = $tmp_keys[$key];
      foreach($this->languages as $id => $value) {
        $table = $base;
        if( !empty($value['language_code']) ) {
          $table .= '_' . $value['language_code'];
        }
        if( !isset($this->database_tables[$table]) ) {
          continue;
        }
        $result_array[] = $table;
      }
      return $result_array;
    }

    function get_language_tables_detailed($def_table) {
      $result_array = array();
      $table = constant($def_table);

      if( !isset($this->default_tables[$def_table]) ) return $result_array;
      if( !defined($def_table) || !$this->default ) return $result_array;

      $tmp_keys = $this->default_tables;
      if( empty($tmp_keys) || !isset($tmp_keys[$def_table]) ) {
        return $result_array;
      }
      $this->set_db_tables();

      $base = $tmp_keys[$def_table];

      foreach($this->languages as $id => $value) {
        $tmp_table = $base;
        if( !empty($value['language_code']) ) {
          $tmp_table .= '_' . $value['language_code'];
        }

        if( !isset($this->database_tables[$tmp_table]) ) {
          continue;
        }

        if( empty($value['language_code']) ) {
          $result_array['default'] = $tmp_table;
        }

        if( $this->current == $id ) {
          $result_array['current'] = $tmp_table;
        }
        $result_array[$def_table][] = $tmp_table;
      }
      return $result_array;
    }

    function get_all_tables() {
      $result_array = array();

      $this->set_db_tables();

      foreach( $this->default_tables as $def => $base ) {
        $result_array[$def] = array();
        foreach($this->languages as $id => $value) {
          $table = $base;
          if( !empty($value['language_code']) ) {
            $table .= '_' . $value['language_code'];
          }
          if( !isset($this->database_tables[$table]) ) {
            continue;
          }
          $result_array[$def][] = $table;
        }
      }
      return $result_array;
    }

    function set_db_tables() {
      extract(tep_load('database'));

      if( empty($this->database_tables) ) {
        $this->database_tables =& $db->get_tables();
      }
    }

    function create($language_id) {
      extract(tep_load('database'));

      $result = false;

      if( $language_id == $this->current ) return $result;

      $language_query = $db->query("select language_id, language_path, language_code from " . TABLE_LANGUAGES . " where language_id = '" . (int)$language_id . "'");
      if( !$db->num_rows($language_query) ) return $result;
      $language_array = $db->fetch_array($language_query);

      $fs_strings = tep_front_physical_path(DIR_WS_CATALOG_STRINGS);
      tep_copy_dir($fs_strings . $this->path, $fs_strings . $language_array['language_path']);

      $fs_includes = tep_front_physical_path(DIR_WS_CATALOG_INCLUDES);
      $tables_array = tep_get_file_array($fs_includes . 'database_language.php');

      foreach( $tables_array as $language_table => $table ) {
        $new_table = $table;
        if( empty($language_array['language_code']) ) continue;
        $new_table .= '_' . $language_array['language_code'];

        if( isset($this->database_tables[$new_table]) ) {
          continue;
        }

        $db->query("create table if not exists " . $new_table . " ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci select * from " . $table);
        $fields_array = $db->query_to_array("show fields from " . $table);
        $new_fields_array = $db->query_to_array("show fields from " . $new_table);

        $extra = '';
        $auto = $extra = '';
        $pri_array = array();

        for($i=0, $j=count($fields_array); $i<$j; $i++) {
          if( isset($new_fields_array[$i]) && $new_fields_array[$i]['Key'] == $fields_array[$i]['Key'] ) continue;

          if( !empty($fields_array[$i]['Key']) && $fields_array[$i]['Key'] == 'PRI' ) {
            $pri_array[] = $fields_array[$i]['Field'];

            if( !empty($fields_array[$i]['Extra']) ) {
              $auto = " change  " . $fields_array[$i]['Field'] . " " . $fields_array[$i]['Field'] . " " . $fields_array[$i]['Type'] . " not null auto_increment first,";
            }
          }
        }

        if( !empty($pri_array) ) {
          $query = "alter table " . $new_table;
          if( !empty($auto) ) {
            $extra = $auto;
          }
          $extra .= "add primary key (" . implode(',', $pri_array) . ")";
          $db->query("alter table " . $new_table . ' ' .  $extra);
        }

        for($i=0, $j=count($fields_array); $i<$j; $i++) {
          if( !empty($fields_array[$i]['Key']) && $fields_array[$i]['Key'] != 'PRI' ) {
            $extra = " add index (" . $fields_array[$i]['Field'] . ")";
            $db->query("alter table " . $new_table . ' ' . $extra);
          }
        }
      }
      $result = true;
      return $result;
    }

    // Creates secondary languages tables
    function create_table($def_table) {
      extract(tep_load('database', 'message_stack'));

      $error = false;
      $update_language_file = false;

      $language_tables = $this->get_language_tables_detailed($def_table);
      if( empty($language_tables) || !isset($language_tables['default']) ) {
        $table = constant($def_table);
      } else {
        $table = $language_tables['default'];
      }

      $fs_includes = tep_front_physical_path(DIR_WS_CATALOG_INCLUDES);
      $tables_array = tep_get_file_array($fs_includes . 'database_language.php');

      foreach($this->languages as $language_id => $language_array ) {
        $new_table = $table;
        if( empty($language_array['language_code']) ) { 
          continue;
        }
        $new_table .= '_' . $language_array['language_code'];

        if( !isset($tables_array[$def_table]) ) {
          $tables_array[$def_table] = $table;
          $update_language_file = true;
        }

        if( isset($this->database_tables[$new_table]) ) {
          continue;
        }
        $db->query("create table if not exists " . $new_table . " ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci select * from " . $table);
        $fields_array = $db->query_to_array("show fields from " . $table);
        $new_fields_array = $db->query_to_array("show fields from " . $new_table);

        $extra = '';
        $auto = $extra = '';
        $pri_array = array();

        for($i=0, $j=count($fields_array); $i<$j; $i++) {
          if( isset($new_fields_array[$i]) && $new_fields_array[$i]['Key'] == $fields_array[$i]['Key'] ) continue;

          if( !empty($fields_array[$i]['Key']) && $fields_array[$i]['Key'] == 'PRI' ) {
            $pri_array[] = $fields_array[$i]['Field'];

            if( !empty($fields_array[$i]['Extra']) ) {
              $auto = " change " . $fields_array[$i]['Field'] . " " . $fields_array[$i]['Field'] . " " . $fields_array[$i]['Type'] . " not null auto_increment first,";
            }
          }
        }

        if( !empty($pri_array) ) {
          $query = "alter table " . $new_table;
          if( !empty($auto) ) {
            $extra = $auto;
          }
          $extra .= "add primary key (" . implode(',', $pri_array) . ")";
          $db->query("alter table " . $new_table . ' ' . $extra);
        }

        for($i=0, $j=count($fields_array); $i<$j; $i++) {
          if( !empty($fields_array[$i]['Key']) && $fields_array[$i]['Key'] != 'PRI' ) {
            $extra = " add index (" . $fields_array[$i]['Field'] . ")";
            $db->query("alter table " . $new_table . ' ' . $extra);
          }
        }
      }

      if( $update_language_file ) {
        $old_contents = $header_string = '';
        $result = tep_read_contents($fs_includes . 'database_language.php', $old_contents);
        $matches = array();
        if( $result && !empty($old_contents) && preg_match('!/\*.*?\*/!s', $old_contents, $matches) ) {
          $header_string = $matches[0];
        }

        $contents = '<?php' . "\r\n" . $header_string . "\r\n\r\n";
        foreach($tables_array as $key => $value) {
          $contents .= '$' . $key . ' = \'' . $value . '\';' . "\r\n";
        }
        $contents .= '?>' . "\r\n";
        $result = tep_write_contents($fs_includes . 'database_language.php', $contents);
        if( !$result ) {
          $error = sprintf(ERROR_WRITING_FILE, $fs_includes . 'database_language.php');
        }
      }
      return $error;
    }

    function delete($language_id) {
      extract(tep_load('database'));

      $result = false;

      if( $language_id == $this->default ) return $result;

      $language_query = $db->query("select language_id, language_path, language_code from " . TABLE_LANGUAGES . " where language_id = '" . (int)$language_id . "'");

      if( !$db->num_rows($language_query) ) return $result;
      $language_array = $db->fetch_array($language_query);

      if( empty($language_array['language_code']) ) {
        return $result;
      }

      $fs_strings = tep_front_physical_path(DIR_WS_CATALOG_STRINGS);
      tep_erase_dir($fs_strings . $language_array['language_path']);

      $fs_includes = tep_front_physical_path(DIR_WS_CATALOG_INCLUDES);
      $tables_array = tep_get_file_array($fs_includes . 'database_language.php');

      foreach( $tables_array as $language_table => $table ) {
        $new_table = $table;
        $new_table .= '_' . $language_array['language_code'];
        $db->query("drop table if exists " . $new_table);
      }
      $result = true;
      return $result;
    }

    // Removes secondary languages from a specific table
    function delete_table($def_table) {
      extract(tep_load('database'));

      $error = false;
      $fs_includes = tep_front_physical_path(DIR_WS_CATALOG_INCLUDES);
      $tables_array = tep_get_file_array($fs_includes . 'database_language.php');

      $update_language_file = isset($tables_array[$def_table])?true:false;

      $language_tables = $this->get_language_tables_detailed($def_table);
      if( empty($language_tables) || !isset($language_tables['default']) ) {
        $table = constant($def_table);
      } else {
        $table = $language_tables['default'];
      }

      foreach($this->languages as $language_id => $language_array ) {
        $new_table = $table;
        if( empty($language_array['language_code']) ) continue;

        $new_table .= '_' . $language_array['language_code'];
        $db->query("drop table if exists " . $new_table);
      }

      if( $update_language_file ) {
        $old_contents = $header_string = '';
        $result = tep_read_contents($fs_includes . 'database_language.php', $old_contents);
        $matches = array();
        if( $result && !empty($old_contents) && preg_match('!/\*.*?\*/!s', $old_contents, $matches) ) {
          $header_string = $matches[0];
        }
        unset($tables_array[$def_table]);

        $contents = '<?php' . "\r\n" . $header_string . "\r\n\r\n";
        foreach($tables_array as $key => $value) {
          $contents .= '$' . $key . ' = \'' . $value . '\';' . "\r\n";
        }
        $contents .= '?>' . "\r\n";
        $result = tep_write_contents($fs_includes . 'database_language.php', $contents);
        if( !$result ) {
          $error = sprintf(ERROR_WRITING_FILE, $fs_includes . 'database_language.php');
        }
      }
      return $error;
    }

    function synchronize_auto_increment($table) {
      extract(tep_load('database'));

      $auto_flag = false;
      $count_array = array();
      $tables_array = $this->get_language_tables($table);
      if( empty($tables_array) ) return;

      for( $i=0, $j=count($tables_array); $i<$j; $i++ ) {
        $auto_query = $db->query("show table status like '" . $tables_array[$i] . "'");
        $auto_array = $db->fetch_array($auto_query);
        if( $auto_array['Auto_increment'] <= 0 ) {
          return;
        }
        $count_array[] = $auto_array['Auto_increment'];
      }

      // Check autoincrement integrity
      $count_array = array_keys(array_flip($count_array));
      if( count($count_array) == 1 ) return;

      // Find the highest autoincrement to synch the language tables
      sort($count_array, SORT_NUMERIC);
      $auto_increment = array_pop($count_array);

      for( $i=0, $j=count($tables_array); $i<$j; $i++ ) {
        $db->query("alter table " . $tables_array[$i] . " AUTO_INCREMENT=" . (int)$auto_increment);
      }
    }

    function set($language_id) {
      extract(tep_load('defs', 'http_headers','message_stack'));

      $result = false;
      if( empty($default) || !isset($this->languages[$language_id]) ) {
        return $result;
      }
      $http->set_cookie($this->name);
      $http->set_cookie($this->name, $language_id, -1);

      $msg->add_session(WARNING_LANGUAGE_SWITCH, 'warning');
      if( count($_POST) ) {
        tep_redirect(tep_href_link($cDefs->script));
      } else {
        tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params() ));
      }
      return true;
    }

    function has_cookie() {
      return $this->cookie;
    }

    function has_started() {
      return $this->started;
    }

    function get_cookie($name) {
      $result = false;
      if( isset($_COOKIE[$name]) ) {
        $result = $_COOKIE[$name];
      }
      return $result;
    }

    function get_string($force=false) {
      $result = '';
      if( !empty($this->current) && (!$this->cookie || $force) ) {
        $result = 'language=' . $this->current;
      }
      return $result;
    }

    // For a given definition return the default table
    function get_default_table($def) {
      $result = '';
      if( isset($this->default_tables[$def]) ) {
        $result = $this->default_tables[$def];
      }
      return $result;
    }

    function create_plugin_folders($plugin) {
      if( !is_object($plugin) || empty($plugin->key) || !isset($plugin->options_array['strings']) || empty($plugin->front_strings_array) ) return false;

      $valid_folder = false;
      $missing_folders = array();
      $admin_path = $plugin->options_array['strings'];

      foreach($this->languages as $id => $value) {
        $path = DIR_FS_PLUGINS . tep_trail_path($plugin->key) . tep_trail_path($admin_path) . $value['language_path'];
        if( !is_dir($path) ) {
          $missing_folders[] = $path;
        } elseif(empty($valid_folder) ) {
          $valid_folder = $path;
        }
      }
      if( empty($valid_folder) && !empty($missing_folders) ) return false;

      for($i=0, $j=count($missing_folders); $i<$j; $i++) {
        tep_copy_dir($valid_folder, $missing_folders[$i]);
      }

      $fs_plugins = tep_front_physical_path(DIR_WS_CATALOG_STRINGS);
      foreach($this->languages as $id => $value) {
        $path = $fs_plugins . tep_trail_path($value['language_path']) . $plugin->key;
        tep_mkdir($path);
      }
      return true;
    }

    function delete_plugin_folders($plugin_key) {
      if( empty($plugin_key) ) return false;

      $fs_plugins = tep_front_physical_path(DIR_WS_CATALOG_STRINGS);
      foreach($this->languages as $id => $value) {
        $path = $fs_plugins . tep_trail_path($value['language_path']) . $plugin_key;
        tep_erase_dir($path);
      }
      return true;
    }

    function get_string_file_path($plugin_name, $file, $physical = false) {
      $result_array = array();

      $fs_plugins = $physical?tep_front_physical_path(DIR_WS_CATALOG_STRINGS):DIR_WS_CATALOG_STRINGS;
      foreach($this->languages as $id => $value) {
        $path = $fs_plugins . tep_trail_path($value['language_path']) . tep_trail_path($plugin_name);
        $result_array[] = $path . $file;
      }
      return $result_array;
    }
  }
?>
