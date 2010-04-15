<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO-G: Redirection to root
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
//-MS- safe string added
  function tep_create_safe_string($string, $separator='') {
    $string = preg_replace('/\s\s+/', '', trim($string));
    $string = preg_replace("/[^0-9a-z]+/i", $separator, $string);
    return $string;
  }
//-MS- safe string added EOM

  if($_SERVER['REQUEST_METHOD'] != "GET" && $_SERVER['REQUEST_METHOD'] != "POST" ) {
    header("HTTP/1.1 405");
    header("Allow: GET, POST");
    exit();
  }

  if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) || isset($_SERVER['HTTP_VIA']) || isset($_SERVER['HTTP_PROXY_CONNECTION']) ) {
    require('die.php');
    exit();
  }

  $check = rawurldecode($_SERVER['REQUEST_URI']);

  if( strpos($check, '\\') !== false || strpos($check, '//') !== false ) {
    require('die.php');
    exit();
  }

  if( strpos($check, '..') !== false ) {
    require('die.php');
    exit();
  }

  if( strpos($check, '<') !== false || strpos($check, '>') !== false) {
    require('die.php');
    exit();
  }

  if( strpos($check, '(') !== false || strpos($check, ')') !== false ) {
    require('die.php');
    exit();
  }

  $location = $_SERVER['HTTP_HOST'];
  if( strlen($check) > 4 ) {
    $location .= '/';
    $check = tep_create_safe_string($check);
    $check .= '.asp';
    $location .= $check;
  }

  header("HTTP/1.1 301");
  header("Location: " . $location);
  exit();
?>