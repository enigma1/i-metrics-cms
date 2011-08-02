<?php
/*
  $Id: general.php,v 1.160 2003/07/12 08:32:47 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: General Functions
//----------------------------------------------------------------------------
// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// - PHP5 Register Globals off and Long Arrays Off support added
// - Added SEO-G support functions
// - Added META-G support functions
// - Removed PHP3 dependencies
// - Changed database, session functions to use the classes
// - Added Abstract Zones support functions
// - Added Array Support Functions
// - Added Template Tagged Support
// - Transformed script for CMS, removed unrelated functions
// - Removed application globals
// - Added dynamic loading of objects
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  function &tep_load() {
    static $_objects = array();
    static $_cross = array(
      'database'      => 'db',
      'languages'     => 'lng',
      'http_headers'  => 'http',
      'message_stack' => 'msg',
      'plugins_admin' => 'cPlug',
      'config'        => 'cfg',
    );

    $result_array = array();
    $args = func_get_args();
    if( empty($args) ) return $_objects;

    foreach( $args as $name ) {
      $result = false;

      $dir = DIR_FS_CLASSES . $name;
      $file = $dir . '.php';

      if( isset($_cross[$name]) ) {
        $cname = $_cross[$name];
      } elseif( strpos($name, '_') !== false) {
        $cname = $name;
      } else {
        $cname = 'c' . ucfirst($name);
      }

      if( !isset($_objects[$cname]) ) {
        include_once($file);

        if( !class_exists($name) ) {
          die('Critical: Invalid Class File: ' . $file);
        }

        // Check for class overrides and load them if exist
        if( is_dir($dir) ) {
          $overrides_array = array_filter(glob($dir . '/' . $name . '*.php'), 'is_file');
          if( !empty($overrides_array) ) {
            sort($overrides_array);
            for($i = 0, $j=count($overrides_array); $i<$j; $i++) {
              include_once($dir . '/' . $overrides_array[$i]);
              if( !class_exists($name) ) {
                die('Critical: Invalid Class File: ' . $overrides_array[$i]);
              }
            }
            $name = tep_get_script_name($overrides_array[$i]);
          }
        }
        $_objects[$cname] = new $name;
      }
      $result_array[$cname] =& $_objects[$cname];
    }
    return $result_array;
  }

  function tep_ref() {
    $result = false;
    if( !function_exists('debug_backtrace') ) return $result;

    $args = func_get_args();
    if( version_compare(PHP_VERSION, '5.2.5', 'ge') ) {
      $stack = debug_backtrace(false);
    } else {
      $stack = debug_backtrace();
    }
    if( !isset($stack[1]['args']) || !count($stack[1]['args']) ) {
      return $result;
    }

    foreach($args as $value ) {
      if( !isset($stack[1]['args'][0][$value]) ) {
        return $result;
      }
    }
    $result = $stack[1]['args'][0];
    return $result;
  }

  function tep_web_files() {
    static $_files = array();

    $args = func_get_args();
    if( empty($_files) || empty($args) ) {
      $fs_includes = tep_front_physical_path(DIR_WS_CATALOG_INCLUDES);
      $_files = tep_get_file_array($fs_includes . 'filenames.php');
      if( empty($args) ) {
        return $_files;
      }
    }
    $result_array = array();
    foreach($args as $def) {
      if( isset($_files[$def]) ) {
        $result_array[$def] = $_files[$def];
      }
    }
    return $result_array;
  }

  function tep_log($entry='') {
    extract(tep_load('debug'));

    static $_log = array();

    if( empty($entry) ) {
      $result = $_log;
      $_log = array();
      return $result;
    }

    if( !$cDebug->log ) return '';

    if( version_compare(PHP_VERSION, '5.2.5', 'ge') ) {
      $stack = debug_backtrace(false);
    } else {
      $stack = debug_backtrace();
    }
    $tmp_array = isset($stack[1])?$stack[1]:$stack[0];
    $tmp_string = '';

    if( isset($tmp_array['line']) ) {
      $tmp_string .= $tmp_array['line'] . '. ';
    }

    if( isset($tmp_array['class']) && isset($tmp_array['function']) ) {
      $tmp_string = $tmp_array['class'] . ':' . $tmp_array['function'];
    } elseif( isset($tmp_array['file']) ) { 
      $tmp_string = $tmp_array['file'];
    }
    $tmp_string .= ' - ' . $entry;
    $_log[] = $tmp_string;
    return $tmp_string;
  }

  function tep_cfg_value($cfg_array) {
    if( empty($cfg_array['use_function']) ) return $cfg_array['configuration_value'];

    $pos = strpos($cfg_array['use_function'], '::');
    if( $pos !== false && !$pos ) {
      extract(tep_load('config'));
      $tmp_array = explode('::', $cfg_array['use_function']);
      $function = array(&$cfg, $tmp_array[1]);
    } else {
      $function = $cfg_array['use_function'];
    }
    //$pass_args = array('configuration_value' => &$cfg_array['configuration_value']);
    return call_user_func_array($function, $cfg_array['configuration_value']);
  }

  function tep_cfg_set($cInfo) {

    if( empty($cInfo->set_function) ) {
      $result = tep_draw_input_field('configuration_value', $cInfo->configuration_value);
    } else {
      $pos = strpos($cInfo->set_function, '::');
      if( $pos !== false && !$pos ) {
        extract(tep_load('config'));
        $tmp_array = explode('::', $cInfo->set_function);
        $function = array(&$cfg, $tmp_array[1]);
        $tmp_array = array_slice($tmp_array, 2);
      } else {
        $tmp_array = explode('::', $cInfo->set_function);
        $function = $tmp_array[0];
        array_shift($tmp_array);
      }
      if( !isset($tmp_array[0]) ) {
        $args = array();
      } else {
        $args = explode(',',$tmp_array[0]);
      }
      array_unshift($args, $cInfo->configuration_value);
      $result = call_user_func_array($function, $args);
    }
    return $result;
  }

  function tep_define_vars($metrics_file) {
    $vars_array = tep_get_file_array($metrics_file);
    foreach( $vars_array as $key => $value ) {
      if( defined($key) ) continue;
      define($key, $value);
    }
    return true;
  }

  function tep_get_file_array($metrics_file) {
    if( !is_file($metrics_file) ) return array();
    require($metrics_file);
    $vars_array = get_defined_vars();
    unset($vars_array['metrics_file']);
    return $vars_array;
  }

  function tep_load_help($name='', $close=true) {
    extract(tep_load('defs', 'sessions'));

    if( empty($name) ) $name = $cDefs->script;
    $filename = DIR_FS_STRINGS . 'help/' . $cDefs->script;
    if( is_file($filename) ) {
      require($filename);
    } else {
      echo '<div>Error: Could not locate file: <b>' . $filename . '</b></div>';
    }
    if( $close ) $cSessions->close();
    exit();
  }

////
// Redirect to another page or site
  function tep_redirect($url='') {
    extract(tep_load('defs', 'sessions', 'logger'));

    $cLogger->timer_stop();

    // Will not redirect if headers already sent
    if( headers_sent() ) {
      echo '<pre style="font-weight:bold; color: #FF0000;">Critical: Cannot Redirect, Headers already sent</pre>';
      $cSessions->has_started()?$cSessions->close():exit();
    }

    if( empty($url) ) $url = $cDefs->relpath;

    // Validate url
    if( strstr($url, "\n") != false || strstr($url, "\r") != false ) {
      $url = $cDefs->relpath;
    }

    if( $cSessions->has_started() ) $cSessions->close(false);
    if( $cDefs->ajax ) exit();

    // No encoded ampersands for redirects
    $url = str_replace('&amp;', '&', $url);
    header('P3P: CP="NOI ADM DEV PSAi COM NAV STP IND"');
    header('Location: ' . $url);
    exit();
  }

  function tep_get_site_path() {
    extract(tep_load('defs'));
    $tmp_array = explode('://', $cDefs->crelpath);
    return $tmp_array[1];
  }
////
// Return date in raw format
// $date should be in format mm/dd/yyyy
// raw date is in format YYYYMMDD, or DDMMYYYY
  function tep_date_raw($date, $reverse = false) {
    if ($reverse) {
      return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
    } else {
      return substr($date, 6, 4) . substr($date, 0, 2) . substr($date, 3, 2);
    }
  }

//-MS- safe string added
  function tep_create_safe_string($string, $separator=' ', $filter="/[^0-9a-z]+/i") {
    $string = preg_replace('/\s\s+/', ' ', trim($string));
    $string = preg_replace($filter, $separator, $string);
    if( !empty($separator) ) {
      $string = trim($string, $separator);
      $string = preg_replace("/\$separator\$separator+/", $separator, trim($string));
      //$string = str_replace($separator . $separator . $separator, $separator, $string);
      //$string = str_replace($separator . $separator, $separator, $string);
    }
    return $string;
  }
//-MS- safe string added EOM

////
// Parse the data used in the html tags to ensure the tags will not break
  function tep_parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
  }

  function tep_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      return htmlspecialchars($string);
    } else {
      if ($translate == false) {
        return tep_parse_input_field_data($string, array('"' => '&quot;'));
      } else {
        return tep_parse_input_field_data($string, $translate);
      }
    }
  }

  function tep_output_string_protected($string) {
    return tep_output_string($string, false, true);
  }

  function tep_sanitize_string($string, $sep='_') {
    $patterns = array ('/ +/','/[<>]/');
    $replace = array (' ', $sep);
    return preg_replace($patterns, $replace, trim($string));
  }


  function tep_get_all_get_params() {
    extract(tep_load('defs', 'sessions'));

    $exclude_array = func_get_args();

    $get_url = '';

    if( count($_GET) > 20 ) return $get_url;

    foreach( $_GET as $key => $value ) {
      if( !empty($cDefs->link_params) && !isset($cDefs->link_params[$key]) ) continue;

      if( $key != $cSessions->name && !in_array($key, $exclude_array) ) {
        $get_url .= $key . '=' . $value . '&';
      }
    }
    return $get_url;
  }

  function tep_get_only_get_params() {

    $args = func_get_args();
    if(empty($args)) return $args;

    $result = '';
    foreach( $args as $value ) {
      if( isset($_GET[$value]) ) {
        $result .= $value . '=' . $_GET[$value] . '&';
      }
    }
    return $result;
  }

  function tep_update_get_array() {
    extract(tep_load('defs'));

    $parse_array = array();
    if( !empty($cDefs->link_params) ) {
      foreach( $cDefs->link_params as $key ) {
        if( !isset($_GET[$key]) ) continue;
        $parse_array[$key] = $_GET[$key];
      }
      $_GET = $parse_array;
    }
  }

  function tep_date_long($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || ($raw_date == '') ) return false;

    $year = (int)substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    return strftime(DATE_FORMAT_LONG, mktime($hour, $minute, $second, $month, $day, $year));
  }

////
// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
// NOTE: Includes a workaround for dates before 01/01/1970 that fail on windows servers
  function tep_date_short($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || ($raw_date == '') ) return false;

    $year = substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    if( @date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
      return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
      return preg_replace('/2037/', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));
    }

  }

  function tep_datetime_short($raw_datetime) {
    if ( ($raw_datetime == '0000-00-00 00:00:00') || ($raw_datetime == '') ) return false;

    $year = (int)substr($raw_datetime, 0, 4);
    $month = (int)substr($raw_datetime, 5, 2);
    $day = (int)substr($raw_datetime, 8, 2);
    $hour = (int)substr($raw_datetime, 11, 2);
    $minute = (int)substr($raw_datetime, 14, 2);
    $second = (int)substr($raw_datetime, 17, 2);

    return strftime(DATE_TIME_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
  }

  function tep_info_image($image, $alt, $width = '', $height = '') {
    extract(tep_load('defs'));

    $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
    if( !empty($image) && (file_exists($images_path.$image)) ) {
      $image = tep_catalog_image($image, $alt, $width, $height);
    } else {
      $image = TEXT_IMAGE_NONEXISTENT;
    }

    return $image;
  }

  function tep_break_string($string, $len, $break_char = '-') {
    $l = 0;
    $output = '';
    for ($i=0, $n=strlen($string); $i<$n; $i++) {
      $char = substr($string, $i, 1);
      if ($char != ' ') {
        $l++;
      } else {
        $l = 0;
      }
      if ($l > $len) {
        $l = 1;
        $output .= $break_char;
      }
      $output .= $char;
    }

    return $output;
  }

  function tep_create_random_value($length, $type = 'mixed', $unique = false) {
    $digits = '1234567890';
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $result = $pattern = '';
    switch($type) {
      case 'digits':
        $pattern = $digits;
        break;
      case 'chars':
        $pattern = $chars . strtoupper($chars);
        break;
      case 'chars_lower':
        $pattern = $chars;
        break;
      case 'mixed_upper':
        $pattern = $digits . strtoupper($chars);
        break;
      case 'mixed_lower':
        $pattern = $digits . $chars;
        break;
      default:
        if( $unique ) {
          $pattern = $digits . $chars . strtoupper($chars);
        } else {
          $pattern = $digits . $chars . strrev($digits) . strtoupper($chars);
        }
        break;
    }
    for($i=0; $i<$length && strlen($pattern); $i++) {
      $index = tep_rand(0, strlen($pattern)-1);
      $result .= substr($pattern, $index, 1);
      if( $unique ) {
        if( $index >= strlen($pattern) ) {
          $pattern = substr($pattern, 0, -1);
        } elseif( !$index ) {
          $pattern = substr($pattern, 1);
        } else {
          $pattern = substr($pattern, 0, $index) . substr($pattern, $index+1);
        }
      }
    }
    return $result;
  }

  function tep_not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if( is_numeric($value) ) return true;
      if( is_string($value) && $value != '' && $value != 'NULL' && strlen(trim($value)) > 0) {
        return true;
      } else {
        return false;
      }
    }
  }

////
// Sets timeout for the current script.
// Cant be used in safe mode.
  function tep_set_time_limit($limit) {
    if (!get_cfg_var('safe_mode')) {
      set_time_limit($limit);
    }
  }

////
// Retrieve server information
  function tep_get_system_information() {
    extract(tep_load('database'));

    $time_query = $db->query("select now() as datetime");
    $time_array = $db->fetch_array($time_query);

    $system = $kernel = '';
    $test_array = preg_split('/[\s,]+/', @exec('uname -a'), 5);

    if( isset($test_array[0])) {
      $system = $test_array[0];
    }
    if( isset($test_array[2])) {
      $kernel = $test_array[2];
    } elseif( isset($_ENV['OS']) ) {
      $kernel = $_ENV['OS'];
    }

    $result_array = array(
      'date' => tep_datetime_short(date('Y-m-d H:i:s')),
      'system' => $system,
      'kernel' => $kernel,
      'host' => $_SERVER['HTTP_HOST'],
      'ip' => getenv('SERVER_ADDR'),
      'uptime' => @exec('uptime'),
      'http_server' => $_SERVER['SERVER_SOFTWARE'],
      'php' => PHP_VERSION,
      'zend' => (function_exists('zend_version') ? zend_version() : ''),
      'db_server' => DB_SERVER,
      'db_ip' => gethostbyname(DB_SERVER),
      'db_version' => 'MySQL ' . (function_exists('mysql_get_server_info') ? mysql_get_server_info() : ''),
      'db_date' => tep_datetime_short($time_array['datetime'])
    );
    return $result_array;
  }

////
// Wrapper function for round()
  function tep_round($n, $d = 0) {
    $result ='';
    $n = $n - 0;
    $org_n = $n;
    $n = abs($n);

    if ($d === NULL) $d = 2;

    $f = pow(10, $d);
    $n += pow(10, - ($d + 1));
    $n = round($n * $f) / $f;
    $n += pow(10, - ($d + 1));
    $n += '';

    if( $d == 0 ) {
      $result = substr($n, 0, strpos($n, '.'));
    } else {
      $result = substr($n, 0, strpos($n, '.') + $d + 1);
    }
    if( $org_n < 0 ) {
      $result = '-' . $result;
    }
    return $result;
  }

////
// Return a random value
  function tep_rand($min = null, $max = null) {
    static $seeded;

    if( !$seeded ) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }

    if( isset($min) && isset($max) ) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }

// nl2br() prior PHP 4.2.0 did not convert linefeeds on all OSs (it only converted \n)
  function tep_convert_linefeeds($from, $to, $string) {
    return str_replace($from, $to, $string);
  }

  function tep_get_script_name($page='') {
    extract(tep_load('defs'));
    if( empty($page) ) {
      $page = $cDefs->script;
    }
    return basename($page,'.php'); 
  }


//-MS- Generic Text Added
  function tep_set_generic_text_status($gtext_id, $status) {
    extract(tep_load('database'));

    if ($status == '1') {
      return $db->query("update " . TABLE_GTEXT . " set status = '1' where gtext_id = '" . (int)$gtext_id . "'");
    } elseif ($status == '0') {
      return $db->query("update " . TABLE_GTEXT . " set status = '0' where gtext_id = '" . (int)$gtext_id . "'");
    } else {
      return -1;
    }
  }

  function tep_set_generic_sub_status($gtext_id, $sub) {
    extract(tep_load('database'));

    if ($sub == '1') {
      return $db->query("update " . TABLE_GTEXT . " set sub = '1' where gtext_id = '" . (int)$gtext_id . "'");
    } elseif ($sub == '0') {
      return $db->query("update " . TABLE_GTEXT . " set sub = '0' where gtext_id = '" . (int)$gtext_id . "'");
    } else {
      return -1;
    }
  }
//-MS- Generic Text Added EOM


//-MS- Tagged Templates Support Added
  function tep_templates_replace_entities($contents, $contents_array, $open_tag='[<<<', $close_tag='>>>]') {
    if( !is_array($contents_array) || !tep_not_null($contents) )
      return false;

    $translation = $contents;
    foreach($contents_array as $key => $value) {
      $translation = str_replace($open_tag . $key . $close_tag, $value, $translation);
    }
    return $translation;
  }
//-MS- Tagged Templates Support Added

  function tep_truncate_string($string, $max_length = 300, $open_tag = '<p>', $close_tag = '</p>') {
    if( !strlen($string) )  return $string;
    $string = trim(strip_tags($string, $open_tag));

    $open_pos = strpos($string, $open_tag);
    $close_pos = strpos($string, $close_tag);
    if( $open_pos !== false && $close_pos !== false && $close_pos > $open_pos ) {
      $open_pos += strlen($open_tag);
      $final_string = substr($string, $open_pos, $close_pos-$open_pos);
    } else {
      $final_string = strip_tags($string);
    }
    $char_size = tep_utf8_size($final_string);
    $max_length *= $char_size;
    
    if( strlen($final_string) > $max_length ) {
      $final_string = substr($final_string, 0, $max_length);
      $pos = strrpos($final_string, '.');
      if( $pos ) {
        $final_string = substr($final_string, 0, $pos+1);
      }
    }
    return $final_string;
  }

  function tep_mysql_to_time_stamp($mysqlDate) {
    if (strlen($mysqlDate) > 10) {
      list($year, $month, $day_time) = explode('-', $mysqlDate);
      list($day, $time) = explode(" ", $day_time);
      list($hour, $minute, $second) = explode(":", $time);
      $ts = mktime($hour, $minute, $second, $month, $day, $year);
    } else {
      $tmp_array = explode('-', $mysqlDate);
      if( count($tmp_array) < 3 ) {
        $tmp_array = array(0,0,0);
      }
      list($year, $month, $day) = $tmp_array;
      $ts = mktime(0, 0, 0, $month, $day, $year);
    }
    if( !(int)$month || !(int)$day || !(int)$year ) $ts = 0;
    return $ts;
  }

//-MS- Array Invert Added
// Function array_invert came from:
// http://www.php.net/manual/en/function.array-flip.php
// By: benles at bldigital dot com
  function tep_array_invert($arr) {
    $res = array();
    foreach(array_keys($arr) as $key) {
      if (!array_key_exists($arr[$key], $res)) {
        $res[$arr[$key]] = array();
      }
      array_push($res[$arr[$key]], $key);
    }
    return $res;
  }
//-MS- Array Invert Added EOM

  function tep_array_invert_element($input_array, $element, $filter=false) {
    $result_array = array();
    foreach($input_array as $key => $value) {
      if( is_array($value) && isset($value[$element]) ) {
        $index = $value[$element];
        if( !isset($result_array[$index]) ) {
          $result_array[$index] = array();
        }
        if( $filter && isset($value[$filter] ) ) {
          $result_array[$index][] = $value[$filter];
        } else {
          $result_array[$index][] = $value;
        }
      }
    }
    return $result_array;
  } 

  function tep_array_invert_flat($input_array, $element, $filter) {
    $result_array = array();
    foreach($input_array as $key => $value) {
      if( is_array($value) && isset($value[$element]) && isset($value[$filter]) ) {
        $index = $value[$element];
        $result_array[$index] = $value[$filter];
      }
    }
    return $result_array;
  }

  function tep_array_invert_from_element($input_array, $element, $filter=false) {
    $result_array = array();
    foreach($input_array as $key => $value) {
      if( is_array($value) && isset($value[$element]) ) {
        $index = $value[$element];
        if( $filter && isset($value[$filter]) ) {
          $result_array[$index] = $value[$filter];
        } else {
          $result_array[$index] = $value;
        }
      }
    }
    return $result_array;
  }

  // Merges 2 array from a given common key
  // The common key is the last key in the trail array and is also present in the lead_array
  // Will remove from lead array all elements till it finds the common key
  // It will then append the lead array into trail array
  function tep_array_splice_from_key($lead_array, $trail_array, $index) {

    end($trail_array);
    $k = key($trail_array);
    array_pop($trail_array);

    foreach($lead_array as $key => $value) {
      if( $key == $index ) break;
      unset($lead_array[$key]);
    }

    $trail_array += $lead_array;
    return $trail_array;
  }

  function tep_swap_array_elements($src_array, $index) {
    if( empty($src_array) || !is_array($src_array) || count($src_array) < 2 ) return $src_array;

    $index = max($index, 1);
    $index = min($index, count($src_array-1));

    $replace = $src_array[$index];
    array_splice($src_array, $index-1, 0, $replace);
    array_splice($src_array, $index+1, 1);
    return $src_array;
  }

  function tep_array_rename_elements($input_array, $data_array) {
    $result_array = array();
    for($i=0, $j=count($input_array); $i<$j; $i++) {
      $tmp_array = array_values($input_array[$i]);

      if( empty($tmp_array) ) continue;
      foreach($data_array as $key) {
        $value = array_shift($tmp_array);
        $result_array[$i][$key] = $value;
        if( empty($tmp_array) ) break;
      }
    }
    return $result_array;
  }

  function tep_sort_parameter_string($string) {
    $tmp_array = tep_get_string_parameters($string);
    ksort($tmp_array);
    $string = tep_params_to_string($tmp_array);
    return $string;
  }

  function tep_get_string_parameters($string) {
    $result = array();
    if( empty($string) || !is_string($string) ) return $result;
    $string = str_replace('&amp;', '&', $string);
    $params_array = explode('&', $string);
    foreach($params_array as $value) {
      $tmp_array = explode('=', $value);
      if( empty($tmp_array[0]) || count($tmp_array) != 2 ) continue;
      $result[$tmp_array[0]] = $tmp_array[1];
    }
    return $result;
  }

  function tep_params_to_string($array, $sep='=', $glue='&') {
    $result = '';
    if( !is_array($array) || !count($array) ) return $result;

    $result_array = array();
    foreach($array as $key => $value) {
      if( !tep_not_null($key) ) continue;
      $result_array[] = $key . $sep . $value;
    }
    $result = implode($glue, $result_array);
    return $result;
  }

  function tep_read_contents($filename, &$contents) {
    $result = false;
    $contents = '';

    if( !is_file($filename) ) return $result;

    $size = filesize($filename);
    if( !$size ) return $result;

    $fp = @fopen($filename, 'rb');
    if( !$fp ) return $result;

    $contents = fread($fp, $size);
    fclose($fp);
    $result = true;
    return $result;
  }

  function tep_write_contents($filename, $contents) {
    $result = false;
    $fp = @fopen($filename, 'w');
    if( $fp ) {
      $size = fwrite($fp, $contents);
      fclose($fp);
      $result = ($size > 0);
    }
    return $result;
  }

  function tep_read_file($filename, $buffer_size = 1048576) {
    $result = false;

    if( !is_file($filename) ) return $result;

    $fp = fopen($filename, 'rb');
    if( !$fp ) return $result;

    while( !feof($fp) ) {
      $buffer = fread($fp, $buffer_size);
      echo $buffer;
    }
    fclose($fp);
    $result = true;
    return $result;
  }

  function tep_erase_dir($path) {
    if( empty($path) || !is_dir($path) ) return;

    closedir(opendir($path));
    $sub_array = glob($path.'*');
    if( empty($sub_array) ) return;
    foreach($sub_array as $sub ) {
      if( is_file($sub) ) {
        @unlink($sub);
      } else {
        tep_erase_dir($sub.'/');
        @rmdir($sub);
      }
    }
  }

  function tep_mkdir($path) {
    if( is_dir($path) ) return true;

    $old_mask = umask(0);
    $result = @mkdir($path, 0777);
    umask($old_mask);
    return $result;
  }

  function tep_copy_dir($src, $dst) {
    extract(tep_load('message_stack'));

    //closedir(opendir($src));
    //closedir(opendir($dst));

    $src = rtrim($src, '/');
    $dst = rtrim($dst, '/');
    if( empty($src) || empty($dst) || !is_dir($src) ) return;

    $result = tep_mkdir($dst);
    if( !$result ) {
      $msg->add_session(sprintf(ERROR_CREATE_DIR, $dst));
      return;
    }

    $sub_array = glob($src.'/*');
    if( empty($sub_array) ) {
      return;
    }

    foreach($sub_array as $sub ) {
      $entry = basename($sub);

      if( is_file($sub) ) {
        $contents = '';
        if( !tep_read_contents($src.'/'.$entry, $contents) ) {
          $msg->add_session(sprintf(ERROR_INVALID_FILE,$src.'/'.$entry));
          continue;
        }
        if( !tep_write_contents($dst . '/' . $entry, $contents) ) {
          $msg->add_session(sprintf(ERROR_WRITING_FILE, $src.'/'.$entry));
          continue;
        }
      } else {
        tep_copy_dir($src.'/'.$entry, $dst.'/'.$entry);
      }
    }
    closedir(opendir($src));
    closedir(opendir($dst));
  }

  function tep_front_physical_path($path='', $trailer=true) {
    $path = trim($path, ' /');
    if( strpos($path, '\'') !== false || strpos($path, '..') !== false || strpos($path, '\\') !== false ) {
      die('Critical: Cannot setup requested path - invalid characters detected');
    }

    $fs_root = substr(DIR_FS_CATALOG, 0, -strlen(DIR_WS_CATALOG) );
    $fs_root = rtrim($fs_root, ' /');
    $fs_root .= '/' . $path;
    $fs_root = rtrim($fs_root, ' /');

    if( $trailer ) {
      $fs_root .= '/';
    }
    return $fs_root;
  }

  function tep_trail_path($path, $right_only=false) {
    if( !empty($path) ) {
      if(!$right_only) {
        $path = trim($path, ' /') . '/';
      } else {
        $path = rtrim($path, ' /') . '/';
      }
    }
    return $path;
  }

  // $area = 0-admin, 1 - webfront
  function tep_read_dir($dir, $area=0, $pulldown=true, $ext = 'php') {
    $scripts_array = array();
    if( $area == 1 ) {
      $fs_dir = tep_front_physical_path($dir);
    } else {
      $fs_dir = $dir;
    }
    rtrim($fs_dir, ' /');
    $cDir = dir($fs_dir);
    if( !is_object($cDir) ) return;
    while( false !== ($script = $cDir->read()) ) {
      if( !empty($ext) ) {
        $check_array = explode('.', $script);
        if( !count($check_array) || $check_array[count($check_array)-1] != $ext) {
          continue;
        }
      }

      $scripts_array[strtolower($script)] = array(
        'id' => $script, 
        'text' => $script
      );
    }
    $cDir->close();
    ksort($scripts_array, SORT_STRING);

    if(!$pulldown) {
      $scripts_array = tep_array_invert_flat($scripts_array, 'text', 'text');
    }
    $scripts_array = array_values($scripts_array);
    return $scripts_array;
  }

  function tep_string_length($string) {
    $size = tep_utf8_size($string);
    $length = (int)(strlen($string)/$size);
    return $length;
  }

  function tep_utf8_size($string) {
    $string = trim($string);
    $size = 1;
    if( empty($string) ) return $size;

    $check_int = ord($string);

    if( ($check_int & 0xE0) == 0xC0 ) {
      $size = 2;
    } elseif( ($check_int & 0xF0) == 0xE0 ) {
      $size = 3;
    } elseif( ($check_int & 0xF8) == 0xF0 ) {
      $size = 4;
    } elseif( ($check_int & 0xFC) == 0xF8 ) {
      $size = 5;
    } elseif( ($check_int & 0xFE) == 0xFC ) {
      $size = 6;
    }
    return $size;
  }

  function tep_set_lightbox() {
    extract(tep_load('defs'));

    //$cDefs->media[] = '<script type="text/javascript" src="' . DIR_WS_JS . 'fancybox/jquery.fancybox.pack.js"></script>';
$cDefs->media[] = '<script type="text/javascript" src="' . DIR_WS_JS . 'fancybox/jquery.fancybox.js"></script>';
    $cDefs->media[] = '<script type="text/javascript" src="' . DIR_WS_JS . 'fancybox/jquery.mousewheel.pack.js"></script>';
    $cDefs->media[] = '<link rel="stylesheet" type="text/css" href="' . DIR_WS_JS . 'fancybox/jquery.fancybox.css" media="screen" />';
  }

?>
