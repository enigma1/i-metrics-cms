<?php
/*
  $Id: general.php,v 1.231 2003/07/09 01:15:48 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
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
////
// Redirect to another page or site
  function tep_redirect($url='', $type_redirect='') {
    global $g_session;

    // Will not redirect if headers already sent
    if( headers_sent() ) {
      if( isset($g_session) && is_object($g_session) ) {
        $g_session->close();
      }
      exit();
    }

    if( empty($url) || strstr($url, "\n") != false || strstr($url, "\r") != false ) { 
      tep_redirect(tep_href_link('', '', 'NONSSL', false), '301');
    }

    if( !isset($g_session) || !is_object($g_session) || !$g_session->has_started() ) {
      $type_redirect='301';
    }

    if( isset($g_session) && is_object($g_session) ) {
      $g_session->close(false);
    }

    $url = str_replace('&amp;', '&', $url);
    if( tep_not_null($type_redirect)) {
      header("HTTP/1.1 " . $type_redirect);
    }
    header('P3P: CP="NOI ADM DEV PSAi COM NAV STP IND"');
    header('Location: ' . $url);
    exit();
  }

  function tep_get_site_path() {
    global $g_relpath;
    $tmp_array = explode('://', $g_relpath);
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
    global $g_db;

    $random_entry = '';
    $random_query = $g_db->query($query);
    $num_rows = $g_db->num_rows($random_query);
    if ($num_rows > 0) {
      $random_row = tep_rand(0, ($num_rows - 1));
      $g_db->data_seek($random_query, $random_row);
      $random_entry = $g_db->fetch_array($random_query);
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
// Returns the clients browser
  function tep_browser_detect($component) {
    return stristr($_SERVER['HTTP_USER_AGENT'], $component);
  }

////
// Wrapper function for round()
  function tep_round($n, $d = 0) {
    $n = $n - 0;
    if ($d === NULL) $d = 2;

    $f = pow(10, $d);
    $n += pow(10, - ($d + 1));
    $n = round($n * $f) / $f;
    $n += pow(10, - ($d + 1));
    $n += '';

    if ( $d == 0 ) {
      return substr($n, 0, strpos($n, '.'));
    } else {
      return substr($n, 0, strpos($n, '.') + $d + 1);
    }
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
      return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
      return ereg_replace('2037' . '$', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));
    }
  }

////
// Parse search string into indivual objects
  function tep_parse_search_string($search_str = '', &$objects) {
    $search_str = trim(strtolower($search_str));

// Break up $search_str on whitespace; quoted string will be reconstructed later
    $pieces = split('[[:space:]]+', $search_str);
    $objects = array();
    $tmpstring = '';
    $flag = '';
    for ($k=0; $k<count($pieces); $k++) {
      while (substr($pieces[$k], 0, 1) == '(') {
        $objects[] = '(';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 1);
        } else {
          $pieces[$k] = '';
        }
      }

      $post_objects = array();

      while (substr($pieces[$k], -1) == ')')  {
        $post_objects[] = ')';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 0, -1);
        } else {
          $pieces[$k] = '';
        }
      }

// Check individual words
      if ( (substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"') ) {
        //$objects[] = trim($pieces[$k]);
        $objects[] = trim(ereg_replace('"', ' ', stripslashes($pieces[$k])));
        for ($j=0; $j<count($post_objects); $j++) {
          $objects[] = $post_objects[$j];
        }
      } else {
/* This means that the $piece is either the beginning or the end of a string.
   So, we'll slurp up the $pieces and stick them together until we get to the
   end of the string or run out of pieces.
*/

// Add this word to the $tmpstring, starting the $tmpstring      
        $tmpstring = trim(ereg_replace('"', ' ', stripslashes($pieces[$k])));
// Check for one possible exception to the rule. That there is a single quoted word.
        if (substr($pieces[$k], -1 ) == '"') {
// Turn the flag off for future iterations
          $flag = 'off';

          //$objects[] = trim($pieces[$k]);
          $objects[] = $tmpstring;

          for ($j=0; $j<count($post_objects); $j++) {
            $objects[] = $post_objects[$j];
          }

          unset($tmpstring);

// Stop looking for the end of the string and move onto the next word.
          continue;
        }
        $pieces[$k] = $tmpstring;

// Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.
        $flag = 'on';

// Move on to the next word
        $k++;

// Keep reading until the end of the string as long as the $flag is on

        while ( ($flag == 'on') && ($k < count($pieces)) ) {
          while (substr($pieces[$k], -1) == ')') {
            $post_objects[] = ')';
            if (strlen($pieces[$k]) > 1) {
              $pieces[$k] = substr($pieces[$k], 0, -1);
            } else {
              $pieces[$k] = '';
            }
          }

// If the word doesn't end in double quotes, append it to the $tmpstring.
          if (substr($pieces[$k], -1) != '"') {
// Tack this word onto the current string entity
            $tmpstring .= ' ' . $pieces[$k];

// Move on to the next word
            $k++;
            continue;
          } else {
/* If the $piece ends in double quotes, strip the double quotes, tack the
   $piece onto the tail of the string, push the $tmpstring onto the $haves,
   kill the $tmpstring, turn the $flag "off", and return.
*/
            $tmpstring .= ' ' . trim(ereg_replace('"', ' ', $pieces[$k]));

// Push the $tmpstring onto the array of stuff to search for
            $objects[] = trim($tmpstring);

            for ($j=0; $j<count($post_objects); $j++) {
              $objects[] = $post_objects[$j];
            }

            unset($tmpstring);

// Turn off the flag to exit the loop
            $flag = 'off';
          }
        }
      }
    }

// add default logical operators if needed
    $temp = array();
    for($i=0; $i<(count($objects)-1); $i++) {
      $temp[] = $objects[$i];
      if ( ($objects[$i] != 'and') &&
           ($objects[$i] != 'or') &&
           ($objects[$i] != '(') &&
           ($objects[$i+1] != 'and') &&
           ($objects[$i+1] != 'or') &&
           ($objects[$i+1] != ')') ) {
        $temp[] = ADVANCED_SEARCH_DEFAULT_OPERATOR;
      }
    }
    $temp[] = $objects[$i];
    $objects = $temp;

    $keyword_count = 0;
    $operator_count = 0;
    $balance = 0;
    for($i=0; $i<count($objects); $i++) {
      if ($objects[$i] == '(') $balance --;
      if ($objects[$i] == ')') $balance ++;
      if ( ($objects[$i] == 'and') || ($objects[$i] == 'or') ) {
        $operator_count ++;
      } elseif ( ($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')') ) {
        $keyword_count ++;
      }
    }

    if ( ($operator_count < $keyword_count) && ($balance == 0) ) {
      return true;
    } else {
      return false;
    }
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
    $temp_array = split($needle, $string);

    return sizeof($temp_array);
  }

  function tep_create_random_value($length, $type = 'mixed') {
    if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) return false;

    $rand_value = '';
    while (strlen($rand_value) < $length) {
      if ($type == 'digits') {
        $char = tep_rand(0,9);
      } else {
        $char = chr(tep_rand(0,255));
      }
      if ($type == 'mixed') {
        if (eregi('^[a-z0-9]$', $char)) $rand_value .= $char;
      } elseif ($type == 'chars') {
        if (eregi('^[a-z]$', $char)) $rand_value .= $char;
      } elseif ($type == 'digits') {
        if (ereg('^[0-9]$', $char)) $rand_value .= $char;
      }
    }

    return $rand_value;
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

  function tep_get_ip_address() {
    return $_SERVER['REMOTE_ADDR'];
  }

  function tep_ip2_long($ip='') {
    if( empty($ip) ) {
      $ip = tep_get_ip_address();
    }
    if (is_numeric($ip)){
      return sprintf("%u", floatval($ip));
    } else {
      return sprintf("%u", floatval(ip2long($ip)));
    }
  }

// nl2br() prior PHP 4.2.0 did not convert linefeeds on all OSs (it only converted \n)
  function tep_convert_linefeeds($from, $to, $string) {
    if ((PHP_VERSION < "4.0.5") && is_array($from)) {
      return ereg_replace('(' . implode('|', $from) . ')', $to, $string);
    } else {
      return str_replace($from, $to, $string);
    }
  }

  function tep_get_script_name($page='') {
    global $PHP_SELF;
    if( !tep_not_null($page) ) {
      $page = basename($PHP_SELF);
    }
    if( strlen($page) > 4 ) {
      $page = substr(basename($page), 0, -4);
    }
    return $page;
  }

//-MS- Added Cache HTML
// These HTML Cache functions used only for spiders
  function tep_check_modified_header() {
    global $g_db, $PHP_SELF;
    if( SPIDERS_HTML_CACHE_ENABLE == 'false' )
      return;

    if( SPIDERS_HTML_CACHE_GLOBAL == 'true' ) {
      tep_send_304_header(SPIDERS_HTML_CACHE_TIMEOUT);
      return;
    }

    $script = basename($PHP_SELF);
    $md5_script = md5($script);
    $check_query = $g_db->query("select cache_html_duration from " . TABLE_CACHE_HTML . " where cache_html_type !='2' and cache_html_key = '" . $g_db->filter($md5_script) . "'");
    if($check_array = $g_db->fetch_array($check_query) ) {
      tep_send_304_header($check_array['cache_html_duration']);
    }
  }

  function tep_send_304_header($timeout) {
    global $PHP_SELF, $g_session;

    $oldtime = time() - $timeout;
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
      $if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
      $expiry = strtotime($if_modified_since);
      if($expiry > $oldtime) {
        tep_set_cache_record(true);
        $expiry = tep_get_time_offset($expiry+$timeout, false);
        header('Pragma: public');
        header('Expires: ' . $expiry);
        header('Cache-Control: must-revalidate, max-age=' . $timeout . ', s-maxage=' . $timeout . ', public');
        header('HTTP/1.1 304 Not Modified');
        $g_session->close();
      }
    }
    tep_set_cache_record();
    $script_signature = md5(basename($PHP_SELF) . implode('', array_keys($_GET)) . implode('', $_GET));
    $now = tep_get_time_offset(0);
    $expiry = tep_get_time_offset($timeout);
    header('Pragma: public');
    header('Last-Modified: ' . $now);
    header('Expires: ' . $expiry);
    header('ETag: "' . $script_signature . '"');
    header('Cache-Control: must-revalidate, max-age=' . $timeout . ', s-maxage=' . $timeout . ', public');
  }

  function tep_get_time_offset($offset, $now=true) {
    $newtime = $offset;
    if( $now ) {
      $newtime += time();
    }
    $gmt_time = gmdate('D, d M Y H:i:s', $newtime).' GMT';
    return $gmt_time;
  }

  function tep_set_cache_record($hit = false) {
    global $g_db, $PHP_SELF;
    if( SPIDERS_HTML_CACHE_HITS == 'false' )
      return;
    $script = basename($PHP_SELF);
    $md5_script = md5($script);
    $check_query = $g_db->query("select cache_html_key from " . TABLE_CACHE_HTML_REPORTS . " where cache_html_key = '" . $g_db->filter($md5_script) . "'");
    if( $g_db->num_rows($check_query) ) {
      if( $hit == false ) {
        $g_db->query("update " . TABLE_CACHE_HTML_REPORTS . " set cache_spider_misses = cache_spider_misses+1 where cache_html_key = '" . $g_db->filter($md5_script) . "'");
      } else {
        $g_db->query("update " . TABLE_CACHE_HTML_REPORTS . " set cache_spider_hits  = cache_spider_hits+1 where cache_html_key = '" . $g_db->filter($md5_script) . "'");
      }
    } else {
      $sql_data_array = array(
                              'cache_html_key' => $g_db->prepare_input($md5_script),
                              'cache_html_script' => $g_db->prepare_input($script)
                             );
      if( $hit == false ) {
        $sql_insert_array = array(
                                  'cache_spider_misses' => '1'
                                 );
      } else {
        $sql_insert_array = array(
                                  'cache_spider_hits' => '1'
                                 );
      }
      $sql_data_array = array_merge($sql_data_array, $sql_insert_array);
      $g_db->perform(TABLE_CACHE_HTML_REPORTS, $sql_data_array);
    }
  }
//-MS- Added Cache HTML EOM


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

    $fp = @fopen($filename, 'r');
    if( $fp ) {
      $contents = fread($fp, filesize($filename));
      fclose($fp);
      $result = true;
    }
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

  function tep_string_length($string) {
    $size = tep_utf8_size($string);
    $length = (int)(strlen($string)/$size);
    return $length;
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
?>
