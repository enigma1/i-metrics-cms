<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: HTML Upper Section
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
  class html_start_sub1 {

    function html_start_sub1() {
      extract(tep_load('defs', 'http_validator', 'database', 'message_stack'));

      // check if the install directory exists, and warn of its existence
      if( DEFAULT_WARNING_INSTALL_EXISTS == 'true') {
        $check_dir = tep_path() . 'install';
        if( is_dir($check_dir) ) {
          $install_string = sprintf(WARNING_INSTALL_DIRECTORY_EXISTS, $check_dir);
          $msg->add($install_string, 'error', 'header');
        }
      }

      switch( $cDefs->script ) {
        default:
          break;
      }
      $http->send_cookies();
      $http->set_headers(
        'Content-Type: text/html; charset=' . CHARSET,
        'P3P: CP="NOI ADM DEV PSAi COM NAV STP IND"'
      );
      $http->send_headers();
    }

    function set_html() {
      extract(tep_load('defs', 'plugins_front', 'sessions'));

      $html_start_sub1 = array(
        DIR_FS_TEMPLATE . 'html_start_sub1.tpl'
      );

      $cPlug->invoke('html_start_sub1');
      for($i=0, $j=count($html_start_sub1); $i<$j; $i++) {
        require($html_start_sub1[$i]);
      }
    }
  }
  $obj = new html_start_sub1();
  $obj->set_html();
  unset($obj);
?>