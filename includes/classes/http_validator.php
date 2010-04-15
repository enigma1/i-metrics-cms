<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: HTTP Headers verification script
// Validates incoming headers passed
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

  class http_validator {

// class constructor
    function http_validator() {

    }

    function validate_headers() {
      global $g_user_agent, $g_proxy, $g_protocol, $g_encoding;

      if($_SERVER['REQUEST_METHOD'] != "GET" && $_SERVER['REQUEST_METHOD'] != "POST" ) {
        header("HTTP/1.1 405");
        header("Allow: GET, POST");
        exit();
      }

      $user_agent = '';
      if( isset($_SERVER['HTTP_USER_AGENT']) && is_string($_SERVER['HTTP_USER_AGENT']) ) {
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
      }

      $g_protocol = true;
      if( !isset($_SERVER['SERVER_PROTOCOL']) || $_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1') {
        $g_protocol = false;
      }

      $g_proxy = false;
      $tmp_array = array_keys($_SERVER);
      unset($tmp_array['HTTP_X_REQUESTED_WITH']);
      $proxy_string = implode(',', $tmp_array);
      if( strpos($proxy_string, 'HTTP_X') !== false ) $g_proxy = true;
      if( strpos($proxy_string, 'PROXY') !== false ) $g_proxy = true;
      if( strpos($proxy_string, 'FORWARD') !== false ) $g_proxy = true;
      if( strpos($proxy_string, 'CLIENT') !== false ) $g_proxy = true;

      if( isset($_SERVER['HTTP_VIA']) || isset($_SERVER['HTTP_10_0_0_0']) || 
          isset($_SERVER['HTTP_SP_HOST']) || isset($_SERVER['HTTP_REMOTE_HOST_WP']) || 
          isset($_SERVER['HTTP_COMING_FROM']) || isset($_SERVER['ZHTTP_CACHE_CONTROL']) ) {
        $g_proxy = true;
      }

      $g_encoding = isset($_SERVER['HTTP_ACCEPT_ENCODING'])?strtolower($_SERVER['HTTP_ACCEPT_ENCODING']):'none';
      if( strpos($g_encoding, 'gzip') === false ) {
        $g_encoding = false;
      } else {
        $g_encoding = true;
      }

      if( !$g_encoding || $g_proxy ) {
        require('die.php');
        exit();
      }

      if( !isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strpos($user_agent, 'http://') === false ) {
        require('die.php');
        exit();
      }

      if(strpos($user_agent, 'http://') === false && !$g_protocol ) {
        require('die.php');
        exit();
      }
    }
  }
?>
