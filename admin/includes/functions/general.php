<?php
/*
  $Id: general.php,v 1.160 2003/07/12 08:32:47 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
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
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

////
// Redirect to another page or site
  function tep_redirect($url) {
    global $logger, $g_session;

    // Will not redirect if headers already sent
    if( headers_sent() ) {
      if( isset($g_session) && is_object($g_session) ) {
        $g_session->close();
      }
      exit();
    }

    if( empty($url) || strstr($url, "\n") != false || strstr($url, "\r") != false ) {
      tep_redirect(tep_href_link());
    }

    if( class_exists('logger') && defined('STORE_PAGE_PARSE_TIME') && STORE_PAGE_PARSE_TIME == 'true') {
      if( !isset($logger) || !is_object($logger)) $logger = new logger;
      $logger->timer_stop();
    }

    if( isset($g_session) && is_object($g_session) ) {
      $g_session->close(false);
    }

    // No encoded ampersands for redirect
    $url = str_replace('&amp;', '&', $url);
    header('P3P: CP="NOI ADM DEV PSAi COM NAV STP IND"');
    header('Location: ' . $url);
    exit();
  }

  function tep_get_site_path() {
    global $g_crelpath;
    $tmp_array = explode('://', $g_crelpath);
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
      $string = str_replace($separator . $separator . $separator, $separator, $string);
      $string = str_replace($separator . $separator, $separator, $string);
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


  function tep_get_all_get_params($exclude_array = '') {
    global $g_session;
    if ($exclude_array == '') $exclude_array = array();

    $get_url = '';

    foreach( $_GET as $key => $value ) {
      if( $key != $g_session->name() && $key != 'error' && !in_array($key, $exclude_array) ) {
        $get_url .= $key . '=' . $value . '&';
      }
    }

    return $get_url;
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

    if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
      return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
      return ereg_replace('2037' . '$', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));
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
    global $g_cserver;

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

  function tep_not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if ( (is_string($value) || is_int($value)) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }

  function tep_browser_detect($component) {
    if( isset($_SERVER['HTTP_USER_AGENT']) ) {
      return stristr($_SERVER['HTTP_USER_AGENT'], $component);
    } else {
      return false;
    } 
  }

////
// Get list of address_format_id's
  function tep_get_address_formats() {
    global $g_db;
    $address_format_query = $g_db->query("select address_format_id from " . TABLE_ADDRESS_FORMAT . " order by address_format_id");
    $address_format_array = array();
    while ($address_format_values = $g_db->fetch_array($address_format_query)) {
      $address_format_array[] = array('id' => $address_format_values['address_format_id'],
                                      'text' => $address_format_values['address_format_id']);
    }
    return $address_format_array;
  }

////
// Function to read in text area in admin
 function tep_cfg_textarea($text) {
    return tep_draw_textarea_field('configuration_value', false, '', 8, $text);
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
// Alias function for Store configuration values in the Administration Tool
  function tep_cfg_select_option($select_array, $key_value, $key = '') {
    $string = '';

    for ($i=0, $n=sizeof($select_array); $i<$n; $i++) {
      $name = ((tep_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');

      $string .= '<br /><input type="radio" name="' . $name . '" value="' . $select_array[$i] . '"';

      if ($key_value == $select_array[$i]) $string .= ' CHECKED';

      $string .= '> ' . $select_array[$i];
    }

    return $string;
  }

////
// Alias function for module configuration keys
  function tep_mod_select_option($select_array, $key_name, $key_value) {
    reset($select_array);
    while (list($key, $value) = each($select_array)) {
      if (is_int($key)) $key = $value;
      $string .= '<br /><input type="radio" name="configuration[' . $key_name . ']" value="' . $key . '"';
      if ($key_value == $key) $string .= ' CHECKED';
      $string .= '> ' . $value;
    }

    return $string;
  }

////
// Retrieve server information
  function tep_get_system_information() {
    global $g_db;
    $db_query = $g_db->query("select now() as datetime");
    $db = $g_db->fetch_array($db_query);

    $system=$kernel='';
    $test_array = preg_split('/[\s,]+/', @exec('uname -a'), 5);

    if( isset($test_array[0])) {
      $system = $test_array[0];
    }
    if( isset($test_array[2])) {
      $kernel = $test_array[2];
    } else {
      $kernel = $_ENV['OS'];
    }

    //list($system, $host, $kernel) = preg_split('/[\s,]+/', exec('uname -a'), 5);

    return array('date' => tep_datetime_short(date('Y-m-d H:i:s')),
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
                 'db_date' => tep_datetime_short($db['datetime']));
  }


  function tep_get_file_permissions($mode) {
// determine type
    if ( ($mode & 0xC000) == 0xC000) { // unix domain socket
      $type = 's';
    } elseif ( ($mode & 0x4000) == 0x4000) { // directory
      $type = 'd';
    } elseif ( ($mode & 0xA000) == 0xA000) { // symbolic link
      $type = 'l';
    } elseif ( ($mode & 0x8000) == 0x8000) { // regular file
      $type = '-';
    } elseif ( ($mode & 0x6000) == 0x6000) { //bBlock special file
      $type = 'b';
    } elseif ( ($mode & 0x2000) == 0x2000) { // character special file
      $type = 'c';
    } elseif ( ($mode & 0x1000) == 0x1000) { // named pipe
      $type = 'p';
    } else { // unknown
      $type = '?';
    }

// determine permissions
    $owner['read']    = ($mode & 00400) ? 'r' : '-';
    $owner['write']   = ($mode & 00200) ? 'w' : '-';
    $owner['execute'] = ($mode & 00100) ? 'x' : '-';
    $group['read']    = ($mode & 00040) ? 'r' : '-';
    $group['write']   = ($mode & 00020) ? 'w' : '-';
    $group['execute'] = ($mode & 00010) ? 'x' : '-';
    $world['read']    = ($mode & 00004) ? 'r' : '-';
    $world['write']   = ($mode & 00002) ? 'w' : '-';
    $world['execute'] = ($mode & 00001) ? 'x' : '-';

// adjust for SUID, SGID and sticky bit
    if ($mode & 0x800 ) $owner['execute'] = ($owner['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x400 ) $group['execute'] = ($group['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x200 ) $world['execute'] = ($world['execute'] == 'x') ? 't' : 'T';

    return $type .
           $owner['read'] . $owner['write'] . $owner['execute'] .
           $group['read'] . $group['write'] . $group['execute'] .
           $world['read'] . $world['write'] . $world['execute'];
  }

  function tep_remove($source) {
    global $messageStack, $tep_remove_error;

    if (isset($tep_remove_error)) $tep_remove_error = false;

    if (is_dir($source)) {
      $dir = dir($source);
      while ($file = $dir->read()) {
        if ( ($file != '.') && ($file != '..') ) {
          if (is_writeable($source . '/' . $file)) {
            tep_remove($source . '/' . $file);
          } else {
            $messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source . '/' . $file), 'error');
            $tep_remove_error = true;
          }
        }
      }
      $dir->close();

      if (is_writeable($source)) {
        rmdir($source);
      } else {
        $messageStack->add(sprintf(ERROR_DIRECTORY_NOT_REMOVEABLE, $source), 'error');
        $tep_remove_error = true;
      }
    } else {
      if (is_writeable($source)) {
        unlink($source);
      } else {
        $messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source), 'error');
        $tep_remove_error = true;
      }
    }
  }


  function tep_mail($to_name, $to_email_address, $email_subject, $email_text, $from_email_name, $from_email_address) {
    if (SEND_EMAILS != 'true') return false;

    // Instantiate a new mail object
    $message = new email(array('X-Mailer: I-Metrics Mailer'));

    // Build the text version
    $text = strip_tags($email_text);
    if (EMAIL_USE_HTML == 'true') {
      $message->add_html($email_text, $text);
    } else {
      $message->add_text($text);
    }

    // Send message
    $message->build_message();
    $message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject);
  }


  function tep_banner_image_extension() {
    if (function_exists('imagetypes')) {
      if (imagetypes() & IMG_PNG) {
        return 'png';
      } elseif (imagetypes() & IMG_JPG) {
        return 'jpg';
      } elseif (imagetypes() & IMG_GIF) {
        return 'gif';
      }
    } elseif (function_exists('imagecreatefrompng') && function_exists('imagepng')) {
      return 'png';
    } elseif (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
      return 'jpg';
    } elseif (function_exists('imagecreatefromgif') && function_exists('imagegif')) {
      return 'gif';
    }

    return false;
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

    if ( $d == 0 )
      return substr($n, 0, strpos($n, '.'));
    else
      return substr($n, 0, strpos($n, '.') + $d + 1);
  }


  function tep_call_function($function, $parameter, $object = '') {
    if ($object == '') {
      return call_user_func($function, $parameter);
    } elseif (PHP_VERSION < 4) {
      return call_user_method($function, $object, $parameter);
    } else {
      return call_user_func(array($object, $function), $parameter);
    }
  }


////
// Return a random value
  function tep_rand($min = null, $max = null) {
    static $seeded;

    if (!$seeded) {
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

// nl2br() prior PHP 4.2.0 did not convert linefeeds on all OSs (it only converted \n)
  function tep_convert_linefeeds($from, $to, $string) {
    if ((PHP_VERSION < "4.0.5") && is_array($from)) {
      return ereg_replace('(' . implode('|', $from) . ')', $to, $string);
    } else {
      return str_replace($from, $to, $string);
    }
  }

  function tep_get_script_name($page='') {
    global $g_script;
    if( empty($page) ) {
      $page = $g_script;
    }
    if( strlen($page) > 4 ) {
      $page = substr($page, 0, -4);
    }
    return $page;
  }


//-MS- Generic Text Added
  function tep_set_generic_text_status($gtext_id, $status) {
    global $g_db;
    if ($status == '1') {
      return $g_db->query("update " . TABLE_GTEXT . " set status = '1' where gtext_id = '" . (int)$gtext_id . "'");
    } elseif ($status == '0') {
      return $g_db->query("update " . TABLE_GTEXT . " set status = '0' where gtext_id = '" . (int)$gtext_id . "'");
    } else {
      return -1;
    }
  }

  function tep_set_generic_sub_status($gtext_id, $sub) {
    global $g_db;
    if ($sub == '1') {
      return $g_db->query("update " . TABLE_GTEXT . " set sub = '1' where gtext_id = '" . (int)$gtext_id . "'");
    } elseif ($sub == '0') {
      return $g_db->query("update " . TABLE_GTEXT . " set sub = '0' where gtext_id = '" . (int)$gtext_id . "'");
    } else {
      return -1;
    }
  }
//-MS- Generic Text Added EOM

//-MS- Abstract Zones support functions added
  function tep_cfg_pull_down_gtext_entries($gtext_id, $key = '') {
    global $g_db;

    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $gtext_array = array();
    $gtext_query = $g_db->query("select gtext_id, gtext_title from " . TABLE_GTEXT . "");
    while ($gtext = $g_db->fetch_array($gtext_query)) {
      $gtext_array[] = array('id' => $gtext['gtext_id'],
                             'text' => $gtext['gtext_title']);
    }

    return tep_draw_pull_down_menu($name, $gtext_array, $gtext_id, 'style="width: 100%"');
  }

  function tep_get_gtext_title($gtext_id) {
    global $g_db;
    $gtext_query = $g_db->query("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
    $gtext = $g_db->fetch_array($gtext_query);

    return $gtext['gtext_title'];
  }


  function tep_cfg_pull_down_text_zones($abstract_zone_id, $key = '') {
    global $g_db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $abstract_array = array();
    $abstract_query = $g_db->query("select az.abstract_zone_id, az.abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " az left join " . TABLE_ABSTRACT_TYPES . " abt on (abt.abstract_types_id=az.abstract_types_id) where abstract_types_class='generic_zones'");
    while ($abstract = $g_db->fetch_array($abstract_query)) {
      $abstract_array[] = array('id' => $abstract['abstract_zone_id'],
                                'text' => $abstract['abstract_zone_name']);
    }

    return tep_draw_pull_down_menu($name, $abstract_array, $abstract_zone_id, 'style="width: 100%"');
  }

  function tep_cfg_pull_down_super_zones($abstract_zone_id, $key = '') {
    global $g_db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $abstract_array = array();
    $abstract_query = $g_db->query("select az.abstract_zone_id, az.abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " az left join " . TABLE_ABSTRACT_TYPES . " abt on (abt.abstract_types_id=az.abstract_types_id) where abstract_types_class='super_zones'");
    while ($abstract = $g_db->fetch_array($abstract_query)) {
      $abstract_array[] = array('id' => $abstract['abstract_zone_id'],
                                'text' => $abstract['abstract_zone_name']);
    }

    return tep_draw_pull_down_menu($name, $abstract_array, $abstract_zone_id, 'style="width: 100%"');
  }

  function tep_cfg_pull_down_image_zones($zone_id, $key = '') {
    global $g_db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $abstract_array = array();
    $abstract_query = $g_db->query("select az.abstract_zone_id, az.abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " az left join " . TABLE_ABSTRACT_TYPES . " abt on (abt.abstract_types_id=az.abstract_types_id) where abstract_types_class='image_zones'");
    while ($abstract = $g_db->fetch_array($abstract_query)) {
      $abstract_array[] = array('id' => $abstract['abstract_zone_id'],
                                'text' => $abstract['abstract_zone_name']);
    }

    return tep_draw_pull_down_menu($name, $abstract_array, $abstract_zone_id, 'style="width: 100%"');
  }


  function tep_get_abstract_zone_name($abstract_zone_id) {
    global $g_db;
    $abstract_query = $g_db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
    $abstract = $g_db->fetch_array($abstract_query);

    return $abstract['abstract_zone_name'];
  }
//-MS- Abstract Zones support functions added EOM



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
      if( count($tmp_array) != 2) continue;
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

  function tep_erase_dir($path) {
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

  function tep_copy_dir($src, $dst) {
    global $messageStack;

    //closedir(opendir($src));
    //closedir(opendir($dst));

    $src = rtrim($src, '/');
    $dst = rtrim($dst, '/');
    if( empty($src) || empty($dst) || !is_dir($src) ) return;

    if( !is_dir($dst) ) {
      @mkdir($dst);
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
          $messageStack->add_session(ERROR_INVALID_FILE);
          continue;
        }
        if( !tep_write_contents($dst . '/' . $entry, $contents) ) {
          $messageStack->add_session(ERROR_WRITING_FILE);
          continue;
        }
      } else {
        tep_copy_dir($src.'/'.$entry, $dst.'/'.$entry);
      }
    }
    closedir(opendir($src));
    closedir(opendir($dst));
  }

  // Converts relative path to physical targeting the webfront
  function tep_front_physical_path($path='', $trailer=true) {
    $fs_root = substr(DIR_FS_CATALOG, 0, -strlen(DIR_WS_CATALOG) );
    $fs_root = rtrim($fs_root, ' /');
    if( !empty($path) ) {
      $fs_root .= $path;
      $fs_root = rtrim($fs_root, ' /');
    }
    if( $trailer ) {
      $fs_root .= '/';
    }
    return $fs_root;
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
    $check_int = ord($string);
    $size = 1;
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

?>
