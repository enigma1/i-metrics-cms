<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
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
  // check if the install directory exists, and warn of its existence
  if( DEFAULT_WARNING_INSTALL_EXISTS == 'true') {
    $check_dir = DIR_FS_CATALOG . 'install';
    if( file_exists($check_dir) ) {
      $install_string = sprintf(WARNING_INSTALL_DIRECTORY_EXISTS, $check_dir);
      $messageStack->add($install_string, 'error', 'header');
    }
  }

  // set which precautions should be checked
  define('WARN_SESSION_AUTO_START', 'true');
  // check session.auto_start is disabled
  if ( (function_exists('ini_get')) && (WARN_SESSION_AUTO_START == 'true') ) {
    if (ini_get('session.auto_start') == '1') {
      $messageStack->add(WARNING_SESSION_AUTO_START, 'warning', 'header');
    }
  }

  switch( $g_script ) {
    default:
      break;
  }

// Setup privacy header
  header('P3P: CP="NOI ADM DEV PSAi COM NAV STP IND"');

  $html_start_sub1 = array(
    DIR_WS_TEMPLATE . 'html_start_sub1.tpl'
  );
  $g_plugins->invoke('html_start_sub1');
  for($i=0, $j=count($html_start_sub1); $i<$j; $i++) {
    require($html_start_sub1[$i]);
  }
?>