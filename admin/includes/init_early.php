<?php
/*
Part of this file came from includes/application_top.php v1.280

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Early initialization process
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Early initialization intends to perform and includes the following: 
// - Installation Service
// - Database connection and database support functions inclusion
// - Critical functions and definitions inclusion
// - Definitions for SEO-G so it can decode/translate incoming URLs
// - Added Plugins support
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  // start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());
  $g_script = '';

  // set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);
  //ini_set('error_reporting', E_ALL|E_STRICT);
  //ini_set('error_reporting', E_ALL);
  //ini_set('display_errors', 1);

  // define the project version
  define('PROJECT_VERSION', 'I-Metrics CMS with osCommerce MS2.2');

  require(DIR_FS_FUNCTIONS . 'general.php');

  $tmp_array = tep_load('debug');
  $g_debug =& $tmp_array['cDebug'];

  require(DIR_FS_CLASSES . 'object_info.php');

  $tmp_array = tep_load('http_headers');
  $g_http =& $tmp_array['http'];

  // define general functions used application-wide
  require(DIR_FS_FUNCTIONS . 'html_output.php');

  // include the list of project filenames and tables
  tep_define_vars(DIR_FS_INCLUDES . 'filenames.php');

  $tmp_array = tep_load('defs');
  $cDefs = $tmp_array['cDefs'];
  $g_server =& $cDefs->server;
  $g_relpath =& $cDefs->relpath;
  $g_script =& $cDefs->script;
  $g_media =& $cDefs->media;
  $g_ajax =& $cDefs->ajax;

  $g_cookie_domain =& $cDefs->cookie_domain;
  $g_cookie_path =& $cDefs->cookie_path;

  $check_host = ($cDefs->request_type == 'SSL')?'https://':'http://';
  $check_host .= $_SERVER['HTTP_HOST'];
  if( $check_host != $cDefs->server ) tep_redirect();
  $check_host = substr($_SERVER['REQUEST_URI'], 0, strlen(DIR_WS_ADMIN));
  if( $check_host != DIR_WS_ADMIN ) tep_redirect();
  unset($check_host);

  $g_cserver =& $cDefs->cserver;
  $g_crelpath =& $cDefs->crelpath;

  if( !is_string($cDefs->script) || $cDefs->script != basename($_SERVER['PHP_SELF']) || !is_file($cDefs->script) ) {
    tep_redirect();
  }
  if( $cDefs->script == FILENAME_JS_MODULES && !$cDefs->ajax ) {
    //tep_redirect();
  }

  $tmp_array = tep_load('database');
  $g_db =& $tmp_array['db'];
  //$g_db->connect();

  $tmp_array = tep_load('languages');
  $g_lng =& $tmp_array['lng'];
  $g_lng->initialize();

  // set the application parameters
  $configuration_query = $g_db->query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = $g_db->fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
  }

  // Plugins Initialization section
  define('PLUGINS_INSTALL_PREFIX', 'install_');
  define('PLUGINS_ADMIN_PREFIX', 'admin_');
  define('PLUGINS_AJAX_PREFIX', 'ajax_');

  $tmp_array = tep_load('plugins_admin');
  $g_plugins =& $tmp_array['cPlug'];

  // Add current script to the plugins queue
  $g_plugins->set();

  $g_plugins->invoke('init_early');
  $tmp_array = tep_load('sessions');
  $g_session =& $tmp_array['cSessions'];
?>
