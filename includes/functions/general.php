<?php
/*
  $Id: general.php,v 1.231 2003/07/09 01:15:48 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - PHP5 Register Globals off and Long Arrays Off support added
// - Added SEO-G support functions
// - Added META-G support functions
// - Removed PHP3 dependencies
// - Added Cache HTML support functions
// - Changed database, session functions to use the classes
// - Added Abstract Zones support functions
// - Added Array Support Functions
// - Added Template Tagged Support
// - Transformed script for CMS, removed unrelated functions
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  function tep_load() {
    static $_objects = array();
    static $_cross = array(
      'database'       => 'db',
      'languages'      => 'lng',
      'http_validator' => 'http',
      'message_stack'  => 'msg',
      'breadcrumb'     => 'breadcrumb',
      'plugins_front'  => 'cPlug',
    );

    $result_array = array();
    $args = func_get_args();
    if( empty($args) ) return $_objects;

    foreach( $args as $name ) {
      $file = DIR_FS_CLASSES . $name . '.php';
      if( is_file($file) ) {
        require_once($file);
      }
      if( !class_exists($name) ) {
        die('Critical: Invalid Class File: ' . $file);
      }
      if( isset($_cross[$name]) ) {
        $cname = $_cross[$name];
      } elseif( strpos($name, '_') !== false) {
        $cname = $name;
      } else {
        $cname = 'c' . ucfirst($name);
      }
      if( !isset($_objects[$cname]) ) {
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

  function tep_log($entry) {
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

  function tep_define_vars($metrics_file) {
    $vars_array = tep_get_file_array($metrics_file);
    foreach( $vars_array as $key => $value ) {
      if( defined($key) ) continue;
      define($key, $value);
    }
    return true;
  }

  function tep_get_file_array($metrics_file) {
    if( !file_exists($metrics_file) ) return array();
    require($metrics_file);
    $vars_array = get_defined_vars();
    unset($vars_array['metrics_file']);
    return $vars_array;
  }

////
// Redirect to another page or site
  function tep_redirect($url='', $type_redirect='') {
    extract(tep_load('defs', 'http_validator', 'sessions'));

    // Will not redirect if headers already sent
    if( headers_sent() ) {
      if( $cSessions->has_started() ) {
        $cSessions->close(false);
      }
      echo 'Cannot redirect - headers already sent';
      exit();
    }

    if( empty($url) || strstr($url, "\n") != false || strstr($url, "\r") != false ) { 
      tep_redirect(tep_href_link('', '', 'NONSSL', false), '301');
    }

    if( !$cSessions->has_started() ) {
      $type_redirect='301';
    } else {
      $cSessions->close(false);
    }

    if( $cDefs->ajax ) {
      exit();
    }

    $url = str_replace('&amp;', '&', $url);
    if( !empty($type_redirect)) {
      $http->set_headers("HTTP/1.1 " . $type_redirect);
    }
    $http->set_headers(
      'P3P: CP="NOI ADM DEV PSAi COM NAV STP IND"',
      'Location: ' . $url
    );
    $http->send_headers(true);
    exit();
  }

  function tep_get_site_path() {
    extract(tep_load('defs'));

    $tmp_array = explode('://', $cDefs->relpath);
    return $tmp_array[1];
  }

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

////
// Return a random row from a database query
  function tep_random_select($query) {
    extract(tep_load('database'));

    $random_entry = '';
    $random_query = $db->query($query);
    $num_rows = $db->num_rows($random_query);
    if( $num_rows > 0 ) {
      $random_row = tep_rand(0, ($num_rows - 1));
      $db->data_seek($random_query, $random_row);
      $random_entry = $db->fetch_array($random_query);
    }
    return $random_entry;
  }

////
// Break a word in a string if it is longer than a specified length ($len)
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

// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
  function tep_date_long($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || ($raw_date == '') ) return false;

    $year = (int)substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    return strftime(DATE_FORMAT_LONG, mktime($hour,$minute,$second,$month,$day,$year));
  }

////
// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
// NOTE: Includes a workaround for dates before 01/01/1970 that fail on windows servers
  function tep_date_short($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || empty($raw_date) ) return false;

    $year = substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
      return strftime(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
      return preg_replace('/2037$/', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));
    }
  }

  function tep_parse_search_string($search_str = '', &$result_array, &$exclude_array) {
    if( empty($search_str) ) return false;

    if( !is_array($exclude_array) ) $exclude_array = array();
    if( !is_array($result_array) ) $result_array = array();

    $result_array = $exclude_array = array();

    $phrases_array = explode('"', $search_str);

    for($i=0, $j=count($phrases_array); $i<$j; $i++) {
      $phrases_array[$i] = trim($phrases_array[$i]);
      if( empty($phrases_array[$i]) ) continue;

      $mod = $i%2;
      if( !$mod ) {
        $words_array = explode(' ', $phrases_array[$i]);
        for($i2=0, $j2=count($words_array); $i2<$j2; $i2++) {
          if( empty($words_array[$i2]) ) continue;
          $pos = strpos($words_array[$i2], '-');
          if( $pos !== false && $pos == 0 ) {
            $exclude_array[] = substr($words_array[$i2], 1);
            continue;
          }
          $result_array[] = $words_array[$i2];
        }
      } else {
        $result_array[] = trim($phrases_array[$i]);
      }
    }
    return true;
  }

////
// Check if year is a leap year
  function tep_is_leap_year($year) {
    if ($year % 100 == 0) {
      if ($year % 400 == 0) return true;
    } else {
      if (($year % 4) == 0) return true;
    }

    return false;
  }

////
// Get the number of times a word/character is present in a string
  function tep_word_count($string, $needle) {
    $temp_array = preg_split($needle, $string);

    return sizeof($temp_array);
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
      if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }

  function tep_empty($value, $filter=' ') {
    if( is_array($value) ) {
      return empty($value);
    } else {
      if( strlen($filter) ) {
        $value = trim($value, $filter);
      }
      if( strtolower($value) == 'null' ) return true;
      if( strlen($value) ) return false;
      return empty($value);
    }
  }


////
// Return a random value
  function tep_rand($min = null, $max = null) {
    static $seeded;

    if (!isset($seeded)) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }

    if (isset($min) && isset($max)) {
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

//-MS- safe string added
  function tep_create_safe_string($string, $separator=' ', $filter="/[^0-9a-z]+/i") {
    $string = preg_replace('/\s\s+/', ' ', trim($string));
    $string = preg_replace($filter, $separator, $string);
    if( !empty($separator) ) {
      $string = trim($string, $separator);
      $string = str_replace($separator . $separator . $separator, $separator, $string);
      $string = str_replace($separator . $separator, $separator, $string);
    }
    return $string;
  }
//-MS- safe string added EOM

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

  function tep_array_merge_keys(){
    $args = func_get_args();
    $result = array();
    foreach($args as $array){
      if( is_array($array) ) {
        foreach($array as $key => $value){
          $result[$key] = $value;
        }
      }
    }
    return $result;
  }

  function tep_read_contents($filename, &$contents) {
    $result = false;
    $contents = '';
    if( !file_exists($filename) ) return $result;

    $size = filesize($filename);
    if( !$size ) return $result;

    $fp = @fopen($filename, 'r');
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
      if( count($tmp_array) != 2 ) continue;
      $result[$tmp_array[0]] = $tmp_array[1];
    }
    return $result;
  }

  function tep_params_to_string($array, $sep='=', $glue='&') {
    $result = '';
    if( !is_array($array) || !count($array) ) return $result;

    $result_array = array();
    foreach($array as $key => $value) {
      if( empty($key) ) continue;
      $result_array[] = $key . $sep . $value;
    }
    $result = implode($glue, $result_array);
    return $result;
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

  function tep_string_length($string) {
    $size = tep_utf8_size($string);
    $length = (int)(strlen($string)/$size);
    return $length;
  }

  function tep_string_to_array($s){
    $s.=' ';
    $f = preg_replace( '/[^a-z0-9]/i', "-", $s);  
    $f = preg_replace('/--(.*?)--/', '--"$1"--', $f); 
    $f = explode('--',$f);
    $v = array();
    foreach($f as $a){
      $a = str_replace('-',' ',$a);
      if( substr($a,0,1)!='"' ) { 
        $a=explode(' ',$a); 
        foreach($a as $k){ $v[]=$k; } 
      } else { 
        $a = str_replace('"','',$a); 
        $v[] = $a; 
      }
    }
    return $v;
  }

  function tep_js_encode($input_array, $glue_key=':', $glue_value='*') {
    if( !is_array($input_array) || empty($input_array) ) {
      $input_array = array();
    }

    $result_array = array();
    foreach($input_array as $key => $value) {
      $result_array[] = $key . $glue_key . base64_encode($value);
    }

    $result = implode($glue_value, $result_array);
    return $result;
  }

  function tep_utf8_size($string) {
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

  function tep_random_buttons_css(&$selection, $selector, $count=10) {
    $entries_array = array();
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $hidden = 'none;' . "\n";
    $visible = 'inline;' . "\n";
    $k = tep_rand(0, $count);
    $css = array();

    for($i=0; $i<$count; $i++) {
      for($entry='', $i2=0; $i2<6; $i2++ ) {
        $entry .= $chars{tep_rand(0, strlen($chars)-1)};
      }
      $precount = tep_rand(0, 3);
      //$comment_start = tep_rand(0, 5);
      //$comment_end = tep_rand($comment_start, 5);
      if( isset($entries_array[$entry]) ) continue;

      $css[$entry] = $selector . ' .' . $entry . ' {' . "\n";
      $entries_array[$entry] = '';

      for($i2=0; $i2<$precount; $i2++ ) {
        $pre_random  = (tep_rand(0,1) == 1)?$visible:$hidden;
        $css[$entry] .= 'display: ' . $pre_random;
      }
      if( $i == $k ) {
        $selection = $entry;
        $css[$entry] .= 'display: ' . $visible;
      } else {
        $css[$entry] .= 'display: ' . $hidden;
      }
      $css[$entry] .= '}' . "\n";
    }
    return $css;
  }

  function tep_set_lightbox() {
    extract(tep_load('defs'));

    //$cDefs->media[] = '<script type="text/javascript" src="' . DIR_WS_JS . 'fancybox/jquery.fancybox.pack.js"></script>';
$cDefs->media[] = '<script type="text/javascript" src="' . DIR_WS_JS . 'fancybox/jquery.fancybox.js"></script>';
    $cDefs->media[] = '<script type="text/javascript" src="' . DIR_WS_JS . 'fancybox/jquery.mousewheel.pack.js"></script>';
    $cDefs->media[] = '<link rel="stylesheet" type="text/css" href="' . DIR_WS_JS . 'fancybox/jquery.fancybox.css" media="screen" />';
  }
?>
