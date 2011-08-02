<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO-G: 404 to 301 conversion handler and early request security check
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  $terminator = '/';

  $g_script = '';

  require_once('includes/classes/http_validator.php');
  $g_http = new http_validator();
  $g_http->validate_headers();

//-MS- safe string added
  function tep_create_safe_string($string, $separator='') {
    $string = preg_replace('/\s\s+/', '', trim($string));
    $string = preg_replace("/[^0-9a-z\-_]+/i", $separator, $string);
    return $string;
  }
//-MS- safe string added EOM

  $check = rawurldecode($_SERVER['REQUEST_URI']);

  if( strpos($check, '<') !== false || strpos($check, '>') !== false) {
    require('die.php');
    exit();
  }

  if( strpos($check, '(') !== false || strpos($check, ')') !== false ) {
    require('die.php');
    exit();
  }

  // include server parameters
  if( !file_exists('includes/configure.php') ) {
    require('die.php');
    exit();
  }

  require('includes/configure.php');

  $check = basename($check);
  $location = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
  $length = strlen($terminator);

  if( strlen($check) > strlen($terminator) && substr($check, -$length) != $terminator ) {

    $check = tep_create_safe_string($check);
    if( !empty($check) ) {
      $check .= $terminator;
    }
    $location .= $check;
  }

  header("HTTP/1.1 301");
  header("Location: " . $location);
  exit();
?>