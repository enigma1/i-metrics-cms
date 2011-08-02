<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Multi-Lingual Support Class
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
      extract(tep_load('http_validator', 'database'));

      if( !$this->reset() ) return;

      $languages_query_raw = "select language_id, language_name, language_path, language_code from " . TABLE_LANGUAGES . " order by sort_id";
      $this->languages = $db->query_to_array($languages_query_raw, 'language_id');

      $language = isset($_POST[$this->name])?(int)$_POST[$this->name]:0;
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
        unset($_POST[$this->name]);
        $http->set_cookie($this->name);
        $http->set_cookie($this->name, $this->current, -1);
      }
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
      $dir_array = array_filter(glob(DIR_WS_STRINGS . '*'), 'is_dir');

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

    function load_early() {
      if( $this->default ) {
        $this->define_tables(DIR_FS_INCLUDES . 'database_language.php');
      }
      tep_define_vars(DIR_FS_INCLUDES . 'database_tables.php');
    }
/*
    function define_file($metrics_file) {
      $this->tables = array();
      if( !file_exists($metrics_file) ) return false;
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
*/
    function define_tables($metrics_file) {
      $this->tables = array();
      if( !file_exists($metrics_file) ) return false;
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

    function set_db_tables() {
      extract(tep_load('database'));

      if( empty($this->database_tables) ) {
        $this->database_tables =& $db->get_tables();
      }
    }

    function load_strings() {
      extract(tep_load('defs'));

      $path = $this->path;
      if( !empty($path) ) $path .= '/';

      $file = DIR_FS_STRINGS . $path . FILENAME_COMMON;
      tep_define_vars($file);
      $file = DIR_FS_STRINGS . $path . $cDefs->script;

      if( is_file($file) ) {
        require_once(DIR_FS_STRINGS . $path . FILENAME_LCONFIG);
        tep_define_vars($file);
      }
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
      extract(tep_load('http_validator'));

      $result = false;
      if( !isset($this->languages[$language_id]) ) {
        return $result;
      }

      $http->set_cookie($this->name);
      $http->set_cookie($this->name, $language_id, -1);
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
  }
?>
