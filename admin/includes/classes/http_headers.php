<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: HTTP Headers verification script
// Validates incoming headers passed / sends cookies
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
  class http_headers {

    // Compatibility constructor
    function http_headers() {
      $this->cookies_array = array();
      $this->secure = false;
      $this->ip = $_SERVER['REMOTE_ADDR'];
      $this->port = $_SERVER['REMOTE_PORT'];
      $this->ip_string = '';
      $this->ip_sep = '.';
      $this->ip_pad = '03';

      $tmp_array = $this->ip2n($this->ip);
      if( !empty($tmp_array['ip_string']) ) {
        $this->ip = $tmp_array['ip'];
        $this->ip_string = $tmp_array['ip_string'];
        $this->ip_sep = $tmp_array['ip_sep'];
        $this->ip_pad = $tmp_array['ip_pad'];
      }

      $pos = strpos(HTTP_SERVER, 'https://'); 
      if( $pos !== false && !$pos ) $this->secure = true;
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

    function header_cookie($name, $value = '', $expire = 0) {
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

      if( $this->secure ) {
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

    function ip2s($ip) {
      $result = '';
      $tmp_array = array();

      for($p=$i=0, $j=strlen($ip); $i<$j; $i+=$this->ip_pad, $p++) {
        $tmp_array[$p] = ltrim(substr($ip, $i, $this->ip_pad), '0');
        if( empty($tmp_array[$p]) && $this->ip_sep == '.') $tmp_array[$p] = 0;
      }
      $result = implode($this->ip_sep, $tmp_array);
      return $result;
    }
  }
?>
