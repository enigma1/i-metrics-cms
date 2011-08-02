<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
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
      $this->cookies_array = array();
      $this->exclude_array = array();
      $this->bot_pattern = '://';
      $this->bypass = false;

      if( !strlen(ini_get('date.timezone')) && function_exists('date_default_timezone_get')) {
        date_default_timezone_set(@date_default_timezone_get());
      }

      $this->ip_string = '';
      $this->ip = $_SERVER['REMOTE_ADDR'];
      //$this->ip = 'fe80:0:0:0:202:b3ff:fe1e:8329';
      $this->port = $_SERVER['REMOTE_PORT'];
      $this->ip_sep = '.';
      $this->ip_pad = '03';

      $tmp_array = $this->ip2n($this->ip);
      if( !empty($tmp_array['ip_string']) ) {
        $this->ip = $tmp_array['ip'];
        $this->ip_string = $tmp_array['ip_string'];
        $this->ip_sep = $tmp_array['ip_sep'];
        $this->ip_pad = $tmp_array['ip_pad'];
      }

      $this->ua = '';
      if( isset($_SERVER['HTTP_USER_AGENT']) && is_string($_SERVER['HTTP_USER_AGENT']) ) {
        $this->ua = strtolower(trim(strip_tags($_SERVER['HTTP_USER_AGENT'])));
        $this->ua = substr($this->ua, 0, 250);
      }

      $this->protocol = true;
      if( !isset($_SERVER['SERVER_PROTOCOL']) || $_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1') {
        $this->protocol = false;
      }
      $this->proxy = false;
      $tmp_array = $_SERVER;
      unset($tmp_array['HTTP_X_REQUESTED_WITH']);
      $proxy_string = implode(',', $tmp_array);

      //if( strpos($proxy_string, 'HTTP_X') !== false ) $this->proxy = true;
      if( strpos($proxy_string, 'PROXY') !== false ) $this->proxy = true;
      if( strpos($proxy_string, 'FORWARD') !== false ) $this->proxy = true;
      if( strpos($proxy_string, 'CLIENT') !== false ) $this->proxy = true;

      if( isset($_SERVER['HTTP_VIA']) || isset($_SERVER['HTTP_10_0_0_0']) || 
          isset($_SERVER['HTTP_SP_HOST']) || isset($_SERVER['HTTP_REMOTE_HOST_WP']) || 
          isset($_SERVER['HTTP_COMING_FROM']) || isset($_SERVER['ZHTTP_CACHE_CONTROL']) ) {
        $this->proxy = true;
      }
      $this->encoding = isset($_SERVER['HTTP_ACCEPT_ENCODING'])?strtolower($_SERVER['HTTP_ACCEPT_ENCODING']):false;
      if( strpos($this->encoding, 'gzip') !== false ) {
        $this->encoding = true;
      }

      $this->lang = false;
      if( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
        $this->lang = true;
      }

      $this->bot_name = '';
      $this->bot = false;
      $pos = strpos($this->ua, $this->bot_pattern);

      if( $pos !== false ) { 
        $this->bot = true;
        $this->bot_name = substr($this->ua, $pos+strlen($this->bot_pattern));
      }

      // set the type of request (secure or not)
      //$this->ssl = $_SERVER['SERVER_PORT']=='on'?true:false;
      $this->ssl = $_SERVER['SERVER_PORT']=='443'?true:false;
      $this->req = isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'';
    }

    function validate_headers() {
      if( $this->bypass ) return;

      $rip_array = explode($this->ip_sep, $this->ip);

      for($i=0, $j=count($this->exclude_array); $i<$j; $i++) {
        $check_array = explode($this->ip_sep, $this->exclude_array[$i]);
        for($i2=0, $j2=count($check_array); $i2<$j2; $i2++) {
          if( $check_array[$i2] != $rip_array[$i2] ) {
            break;
          }
        }
        if($i2 == $j2) {
          return;
        }
      }

      if( $this->req != "GET" && $this->req != "POST" ) {
        header("HTTP/1.1 405");
        header("Allow: GET, POST");
        exit();
      }

      $check = rawurldecode($_SERVER['REQUEST_URI']);

      if( strpos($check, '\'') !== false || strpos($check, '.php/') !== false || strpos($check, '..') !== false || strpos($check, '\\') !== false || strpos($check, '//') !== false ) {
        require('die.php');
        exit();
      }

      // Die on proxy detection
      if( $this->proxy ) {
        require('die.php');
        exit();
      }

      if( !$this->lang && !$this->bot ) {
        require('die.php');
        exit();
      }

      if( !$this->protocol && !$this->bot ) {
        require('die.php');
        exit();
      }
    }

    function set_headers() {
      $args = func_get_args();
      if( empty($args) ) {
        $this->headers_array = array_unique($this->headers_array);
        return $this->headers_array;
      }

      foreach( $args as $header ) {
        if( !empty($header) ) {
          $this->headers_array[] = $header;
        }
      }
    }

    function send_headers($exit = false) {
      extract(tep_load('sessions'));

      if( !count($this->headers_array) ) {
        return;
      }

      if( headers_sent() ) {
        $this->headers_array = array();
        if( $cSessions->has_started() ) {
          $cSessions->close();
        }
        echo 'Cannot send more headers - output already sent';
        exit();
      }

      $this->headers_array = array_values(array_unique($this->headers_array));

      for($i=0, $j=count($this->headers_array); $i<$j; $i++) {
        header($this->headers_array[$i]);
      }

      $this->headers_array = array();
      if( !$exit ) return;

      if( $cSessions->has_started() ) {
        $cSessions->close();
      }
      exit();
    }

    function set_cookie($name, $value = '', $expire = 0, $secure = 0) {
      if( empty($name) ) return false;

      $this->cookies_array[] = array(
        'name' => $name,
        'value' => $value,
        'expire' => $expire,
        'secure' => $secure
      );
      return true;
    }

    function send_cookies() {
      extract(tep_load('message_stack'));

      if( !count($this->cookies_array) ) return;

      if( headers_sent() ) {
        $this->cookies_array = array();
        $msg->add(WARNING_NOT_SENDING_COOKIES, 'warning', 'header');
        return;
      }

      for($i=0, $j=count($this->cookies_array); $i<$j; $i++) {
        $this->header_cookie(
          $this->cookies_array[$i]['name'],
          $this->cookies_array[$i]['value'],
          $this->cookies_array[$i]['expire'],
          $this->cookies_array[$i]['secure']
        );
      }
      $this->cookies_array = array();
    }

    function header_cookie($name, $value = '', $expire = 0, $secure = 0) {
      extract(tep_load('defs'));

      $hd_string = 'Set-Cookie: ' . $name . '=';
      if( empty($value) ) {
        $value = 'deleted';
      }
      if( empty($expire) ) {
        $old_time = gmdate('D, d M Y H:i:s', time()-86400) . ' GMT';
      } elseif( $expire > 0 ) {
        $old_time = gmdate('D, d M Y H:i:s', $expire) . ' GMT';
      }

      $hd_string .= $value . '; ';

      if( $expire >= 0 ) {
        $hd_string .= 'expires=' . $old_time . '; ';
      }

      $hd_string .= 'path=' . $cDefs->cookie_path . '; domain=' . $cDefs->cookie_domain;

      if( !empty($secure) ) {
        $hd_string .= ' secure;';
      }
      header(trim($hd_string), false);
      return true;
    }

    function ip2n($ip) {
      $result = array();

      $result['ip'] = $ip;
      $result['ip_sep'] = (strpos($ip, '.')!==false)?'.':':';
      $result['ip_pad'] = $result['ip_sep']=='.'?'03':'04';
      $result['ip_string'] = '';

      $tmp_array = explode('::ffff:', $ip);

      if( count($tmp_array) == 2 ) {
        $result['ip'] = $tmp_array[1];
        $result['ip_sep'] = '.';
        $result['ip_pad'] = '03';
      }
      $rip_array = explode($result['ip_sep'], $result['ip']);

      for($i=0, $j=count($rip_array); $i<$j; $i++) {
        $result['ip_string'] .= sprintf('%' . $result['ip_pad'] . 's', $rip_array[$i]);
      }
      return $result;
    }

  }
?>
