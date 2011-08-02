<?php
/*
  $Id: database.php,v 1.21 2003/06/09 21:21:59 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Services Database requests
//----------------------------------------------------------------------------
// Modifications:
// - Moved functions from original file into a class and
//   converted individual functions into class member functions
// - Added memory cache for the database queries.
// - Added field support functions
// - Added sanitize string function so the class is independent
// - Added option for the prepare_input whether to call the sanitize function
// - Ported security fixes from the osC RC2
// - Added Language support functions
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class database {

    // Compatibility constructor
    function database() {
      $this->link = false;
      $this->max_sql_array_size = 500;
      $this->action_array = array(
        'insert' => 'insert into ',
        'delete' => 'delete from ',
        'truncate' => 'truncate table ',
      );
      $this->reset();
    }

    function connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE) {
      if( USE_PCONNECT == 'true') {
        $this->link = @mysql_pconnect($server, $username, $password, true);
      } else {
        $this->link = @mysql_connect($server, $username, $password, true);
      }
      if( !$this->link ) {
        return false;
      }

      if( !defined('TABLE_EXPLAIN_QUERIES') ) {
        define('TABLE_EXPLAIN_QUERIES', 'explain_queries');
      }
      return $this->select($database);
    }

    function select($database, $charset='utf8') {
      $this->query("set character set " . $charset);
      $this->query("set names " . $charset);
      $result = mysql_select_db($database);
      return $result;
    }

    function close() {
      return mysql_close($this->link);
    }

    function reset() {
      $this->queries_array = array();
      $this->last_insert = 0;
      $this->tables_array = array();
    }

    function error($query, $errno, $error) {
      die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br /><br />' . $query . '<br /><br /><small><font color="#ff0000">[TEP STOP]</font></small><br /><br /></b></font>');
    }

    function fly($query) {
      return $this->query($query, true);
    }

    function synchronize($query) {
      extract(tep_load('languages'));

      $result = false;
      $tmp_string = preg_replace('/\s\s+/', ' ', $query);
      if( !$lng->has_started() ) return $result;

      $table = false;
      foreach($this->action_array as $sql_action => $pattern) {
        if( strtolower(substr($tmp_string, 0, strlen($pattern))) == $pattern ) {
          $pos_start = strlen($pattern);
          $pos_end = strpos($query, ' ', $pos_start);
          if( $pos_end === false ) {
            $pos_end = strlen($tmp_string);
          }
          $table = substr($tmp_string, $pos_start, $pos_end-$pos_start);
          $table = trim($table, "`");
          break;
        }
      }

      if( !$table ) {
        return false;
      }

      $tables_array = $lng->get_language_tables($table);
      if( count($tables_array) < 2 ) {
        return false;
      }

      //$new_query = 'start transaction; ';
      $queries_array = array();
      for($i=0, $j=count($tables_array); $i<$j; $i++) {
        $queries_array[] = substr($query, 0, $pos_start) . $tables_array[$i] . substr($query, $pos_end);
      }
      // Make sure if auto-increment exists is in synch for all language tables
      if( $sql_action == 'insert' ) {
        $lng->synchronize_auto_increment($table);
      }

      mysql_query("start transaction", $this->link) or $this->error($query, mysql_errno(), mysql_error());
      mysql_query("begin", $this->link) or $this->error($query, mysql_errno(), mysql_error());
      for($i=0, $j=count($queries_array); $i<$j; $i++) {
        $result = mysql_query($queries_array[$i], $this->link) or $this->error($query, mysql_errno(), mysql_error());
        if( $sql_action == 'insert' && empty($result)) break;
      }

      $this->last_insert = 0;
      if( $sql_action == 'insert' && empty($result) ) {
        mysql_query("rollback", $this->link) or $this->error($query, mysql_errno(), mysql_error());
        $result = -1;
      } else {
        mysql_query("commit", $this->link) or $this->error($query, mysql_errno(), mysql_error());
        $result = true;
      }
      return $result;
    }

    function &get_tables() {
      if( empty($this->tables_array) ) {
        $this->load_tables();
      }
      return $this->tables_array;
    }

    function load_tables() {
      $database_tables = $this->query_to_array('show tables');
      for($i=0, $j=count($database_tables); $i<$j; $i++) {
        list(,$table) = each($database_tables[$i]);
        $this->tables_array[$table] = $table;
      }
    }

    function get_table_fields($def_table) {

      $table = constant($def_table);

      $result_array = array(
        'fields_array' => array(),
        'primary_array' => array(),
        'primary_keys_array' => array(),
      );

      $fields_array = $this->query_to_array("show fields from " . $table);
      $primary_array = array();
      for($i2=0, $j2=count($fields_array); $i2<$j2; $i2++) {
        if( $fields_array[$i2]['Key'] == 'PRI' ) {
          $primary_array[] = $fields_array[$i2]['Field'];
        }
      }
      $result_array['fields_array'] = $fields_array;
      $result_array['primary_array'] = $primary_array;
      $result_array['primary_keys_array'] = array_flip($primary_array);
      return $result_array;
    }

    function query($query, $set_flag = false, $sync_flag = true) {
      $query = trim($query);

      if( !$set_flag ) {
        $this->clear_key(md5($query));
        $result = false;
        if( $sync_flag ) {
          $result = $this->synchronize($query);
        }
        if( $result === false ) {
          $result = $this->explain_query($query);
          //$result = mysql_query($query, $this->link) or $this->error($query, mysql_errno(), mysql_error());
        } elseif($result === true ) {
        } elseif($result == -1 ) {
          $this->error($query, -1, 'Multi Query Failed');
        }
        return $result;
      }

      $md5_key = md5($query);
      if( !isset($this->queries_array[$md5_key]) || !isset($this->queries_array[$md5_key]['data']) ) {
        if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {
          error_log('QUERY ' . $query . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
        }

        $this->queries_array[$md5_key] = array();
        $this->queries_array[$md5_key]['result'] = $this->explain_query($query);
        //$this->queries_array[$md5_key]['result'] = mysql_query($query, $this->link) or $this->error($query, mysql_errno(), mysql_error());

        if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {
          $result_error = mysql_error();
          error_log('RESULT ' . $result . ' ' . $result_error . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
        }
      }

      $this->queries_array[$md5_key]['index'] = 0;
      $result = $md5_key;
      return $result;
    }

    function perform($table, $data, $action = 'insert', $parameters = '') {

      reset($data);
      if ($action == 'insert') {
        $query = 'insert into ' . $table . ' (';
        while (list($columns, ) = each($data)) {
          $query .= $columns . ', ';
        }
        $query = substr($query, 0, -2) . ') values (';
        reset($data);
        while (list(, $value) = each($data)) {
          switch ((string)$value) {
            case 'now()':
              $query .= 'now(), ';
              break;
            case 'null':
              $query .= 'null, ';
              break;
            default:
              $query .= '\'' . $this->input($value) . '\', ';
              break;
          }
        }
        $query = substr($query, 0, -2) . ')';
      } elseif ($action == 'update') {
        $query = 'update ' . $table . ' set ';
        while (list($columns, $value) = each($data)) {
          switch ((string)$value) {
            case 'now()':
              $query .= $columns . ' = now(), ';
              break;
            case 'null':
              $query .= $columns .= ' = null, ';
              break;
            default:
              $query .= $columns . ' = \'' . $this->input($value) . '\', ';
              break;
          }
        }
        $query = substr($query, 0, -2) . ' where ' . $parameters;
      } else {
        die('Invalid Database Request - <b>' . $action . '</b> - for table <b>' . $table . '</b><br /><small><font color="#ff0000">[TEP STOP]</font></small><br /><br />');
      }
      return $this->query($query);
    }


    function fetch_array($md5_key) {
      if( !$this->validate_key($md5_key) ) {
        $result = mysql_fetch_array($md5_key, MYSQL_ASSOC);
        return $result;
      }

      $index = $this->queries_array[$md5_key]['index'];

      if( isset($this->queries_array[$md5_key]['data'][$index]) ) {
        $result = $this->queries_array[$md5_key]['data'][$index];
      } else {
        if( !isset($this->queries_array[$md5_key]['rows']) ) {
          $rows = $this->num_rows($md5_key);
        } else {
          $rows = $this->queries_array[$md5_key]['rows'];
        }
        if( !$rows ) {
          return false;
        }
        $result = mysql_fetch_array($this->queries_array[$md5_key]['result'], MYSQL_ASSOC);
        if( $index <= $this->max_sql_array_size ) {
          $this->queries_array[$md5_key]['data'][$index] = $result;
        }
      }

      $this->queries_array[$md5_key]['index']++;
      return $result;
    }

    function fetch_object($md5_key, $class='') {
      if( empty($class) ) {
        return (object)$this->fetch_array($md5_key);
      }
      $tmp_array = $this->fetch_array($md5_key);
      foreach($tmp_array as $key => $value) {
        $class->$key = $value;
      }
      return $class;
    }

    function num_rows($md5_key) {
      if( !$this->validate_key($md5_key) ) {
        $result = mysql_num_rows($md5_key);
        return $result;
      }

      if( !isset($this->queries_array[$md5_key]['rows']) ) {
        $this->queries_array[$md5_key]['rows'] = mysql_num_rows($this->queries_array[$md5_key]['result']);
      }

      return $this->queries_array[$md5_key]['rows'];
    }

    function data_seek($md5_key, $row_number) {
      if( !$this->validate_key($md5_key) ) {
        $result = mysql_data_seek($md5_key, $row_number);
        return $result;
      }

      if( !isset($this->queries_array[$md5_key]['rows']) ) {
        $this->num_rows($md5_key);
      }

      if( $row_number < 0 || $row_number >= $this->queries_array[$md5_key]['rows'] ) {
        $row_number = 0;
      }

      $this->queries_array[$md5_key]['index'] = $row_number;

      if( $this->queries_array[$md5_key]['index'] > $this->max_sql_array_size ) {
        $result = mysql_data_seek($this->queries_array[$md5_key]['result'], $row_number);
      } else {
        $result = true;
      }
      return $result;
    }

    function insert_id() {
      $result = mysql_insert_id();
      // For commit operations adjust the last insert based on a previously stored value
      if( empty($result) && !empty($this->last_insert) ) {
        $result = $this->last_insert;
        $this->last_insert = 0;
      }
      return $result;
    }

    function free_result($md5_key) {
      if( $this->num_rows($md5_key) <= $this->max_sql_array_size ) return true;

      if( !$this->validate_key($md5_key) ) {
        return mysql_free_result($md5_key);
      }

      $result = mysql_free_result($this->queries_array[$md5_key]['result']);
      unset($this->queries_array[$md5_key]);
      return $result;
    }

    function input($string) {
      if (function_exists('mysql_real_escape_string')) {
        return mysql_real_escape_string($string, $this->link);
      } elseif (function_exists('mysql_escape_string')) {
        return mysql_escape_string($string);
      }
      return addslashes($string);
    }

    function prepare_input($input, $sanitize = true, $type='') {
      if (is_string($input)) {
        if( !empty($type) && $type != 'string') return '';
        if( $sanitize ) {
          return trim($this->sanitize_string(stripslashes($input)));
        } else {
          return trim(stripslashes($input));
        }
      } elseif (is_array($input)) {
        if( !empty($type) && $type != 'array') return '';
        foreach( $input as $key => $value ) {
          $input[$key] = $this->prepare_input($value, $sanitize);
        }
        return $input;
      } else {
        return $input;
      }
    }

    function filter($string) {
      $string = $this->prepare_input($string);
      $string = $this->input($string);
      return $string;
    }

    function sanitize_string($string, $sep='_') {
      if( function_exists('tep_sanitize_string') ) {
        return tep_sanitize_string($string, $sep);
      } else {
        $patterns = array ('/ +/','/[<>]/');
        $replace = array (' ', $sep);
        return preg_replace($patterns, $replace, trim($string));
      }
    }

    function validate_key($md5_key) {
      return isset($this->queries_array[$md5_key]);

      if( !isset($this->queries_array[$md5_key]) ) {
        exit('<font color="#000000"><b>Invalid DBase Key - ' . $md5_key . '</b></font><br />');
      }
    }

    function clear_key($md5_key) {
      unset($this->queries_array[$md5_key]);
    }

    function query_to_array( $string_query, $index=false, $keep=true) {
      $result_array = array();
      $query = $this->query($string_query, $keep);

      if( $this->num_rows($query) ) {
        if( !$index ) {
          while( $result_array[] = $this->fetch_array($query) );
          array_pop($result_array);
        } else {
          while( $tmp_array = $this->fetch_array($query) ) {
            $result_array[$tmp_array[$index]] = $tmp_array;
          }
        }
      }
      $this->free_result($query);
      return $result_array;
    }

    // Explain queries Added
    function wrap(&$array, $wrapwith="'"){
      foreach ($array as $index => $value){
        if ($value!='') {
          $array[$index] = $wrapwith . $this->input($value) . $wrapwith;
        } else { 
          $array[$index] = "''" ;
        }
      }   
    }

    function explain_query($query) {
      extract(tep_load('defs'));
      $result = false;

      if( !defined('EXPLAIN_QUERIES') || EXPLAIN_QUERIES != 'true' ) {
        $result = mysql_query($query, $this->link) or $this->error($query, mysql_errno(), mysql_error());
        return $result;
      }
      //if( !stristr($query, 'select') ) {
      $pattern = 'select ';
      $tmp_string = strtolower(substr($query, 0, strlen($pattern)));
      if( $tmp_string != $pattern ) {
        $result = mysql_query($query, $this->link) or $this->error($query, mysql_errno(), mysql_error());
        return $result;
      }

      $_start = explode(' ', microtime());
      $result = mysql_query($query, $this->link) or $this->error($query, mysql_errno(), mysql_error());
      $_end = explode(' ', microtime());
      $_time = number_format(($_end[1] + $_end[0] - ($_start[1] + $_start[0])), 8);

      // Add the EXPLAIN to the query
      $explain_query = 'EXPLAIN ' . $query;
      $_query = array(
        'explain_id' => '', // Leave blank to get an autoincrement
        'explain_md5query' => md5($query), // MD5() the query to get a unique that can be indexed
        'explain_query' => $query, // Actual query
        'explain_time' => $_time*1000, // Multiply by 1000 to get milliseconds
        'explain_script' => $cDefs->script, // Script name
        'explain_request_string' => $this->filter($_SERVER['QUERY_STRING']) // Query string since some pages are constructed from parameters
      );

      // Merge the _query and explain arrays
      $container = array_merge($_query, mysql_fetch_assoc(mysql_query($explain_query, $this->link)));

      // Break the array into components so elements can be wrapped
      foreach($container as $column => $value) {
        $columns[] = $column;
        $values[] = $value;
      }       
      // Wrap the columns and values
      $this->wrap($columns, '`');
      $this->wrap($values);
      // Implode the columns so they can be used for the insert query below
      $_columns = implode(', ', $columns);
      $_values = implode(', ', $values);

      // -MS- v1.1 Setup the Aliases for the supported columns to fix dbase backup issue
      $org = array("`id`", "`table`", "`rows`", "`select_type`", "`type`", "`possible_keys`", "`key`", "`key_len`", "`ref`", "`Extra`", "`Comment`");
      $mod  = array("`explain_query_id`", "`explain_table`", "`explain_rows`", "`explain_select_type`", "`explain_type`", "`explain_possible_keys`", "`explain_key`", "`explain_key_len`", "`explain_ref`", "`explain_extra`", "`explain_comment`");
      $_columns = str_replace($org, $mod, $_columns);
      // -MS- ends

      // Insert the data
      $explain_insert = "INSERT into " . TABLE_EXPLAIN_QUERIES . " ($_columns) VALUES ($_values)";
      mysql_query($explain_insert, $this->link) or $this->error($explain_insert, mysql_errno(), mysql_error());
      return $result;

    }
    // Explain queries Added EOM
  }
?>
