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
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
// Number of rows to hold in memory
define('MAX_SQL_ARRAY_SIZE', 5000);

  class dbase {
    var $link, $queries_array;

    function connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE) {
      $this->reset();

      if( USE_PCONNECT == 'true') {
        $this->link = @mysql_pconnect($server, $username, $password, true);
      } else {
        $this->link = @mysql_connect($server, $username, $password, true);
      }
      if( !$this->link ) {
        return false;
      }

      $this->select($database);
      return true;
    }

    function select($database, $charset='utf8') {
      $this->query("set names " . $charset);
      $result = mysql_select_db($database);
      return $result;
    }

    function close() {
      return mysql_close($this->link);
    }

    function reset() {
      $this->queries_array = array();
    }

    function error($query, $errno, $error) {
      die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br /><br />' . $query . '<br /><br /><small><font color="#ff0000">[TEP STOP]</font></small><br /><br /></b></font>');
    }

    function fly($query) {
      return $this->query($query, true);
    }

    function query($query, $set_flag = false) {
      if( !$set_flag ) {
        $this->clear_key(md5($query));
        $result = mysql_query($query, $this->link) or $this->error($query, mysql_errno(), mysql_error());
        return $result;
      }

      $md5_key = md5($query);
      if( !isset($this->queries_array[$md5_key]) || !isset($this->queries_array[$md5_key]['data']) ) {
        if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {
          error_log('QUERY ' . $query . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
        }

        $this->queries_array[$md5_key] = array();
        $this->queries_array[$md5_key]['result'] = mysql_query($query, $this->link) or $this->error($query, mysql_errno(), mysql_error());

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
      $set_flag = false;
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
        $set_flag = true;
      }
      return $this->query($query, $set_flag);
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
        if( $index <= MAX_SQL_ARRAY_SIZE ) {
          $this->queries_array[$md5_key]['data'][$index] = $result;
        }
      }

      $this->queries_array[$md5_key]['index']++;
      return $result;
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

      if( $this->queries_array[$md5_key]['index'] > MAX_SQL_ARRAY_SIZE ) {
        $result = mysql_data_seek($this->queries_array[$md5_key]['result'], $row_number);
      } else {
        $result = true;
      }
      return $result;
    }

    function insert_id() {
      return mysql_insert_id();
    }

    function free_result($md5_key) {
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

    function prepare_input($string, $sanitize = true) {
      if (is_string($string)) {
        if( $sanitize ) {
          return trim($this->sanitize_string(stripslashes($string)));
        } else {
          return trim(stripslashes($string));
        }
      } elseif (is_array($string)) {
        reset($string);
        while (list($key, $value) = each($string)) {
          $string[$key] = $this->prepare_input($value);
        }
        return $string;
      } else {
        return $string;
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
  }
?>
