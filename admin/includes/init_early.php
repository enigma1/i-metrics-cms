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

    // set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);
  //ini_set('error_reporting', E_ALL);
  //ini_set('display_errors', 1);

  // define the project version
  define('PROJECT_VERSION', 'I-Metrics CMS with osCommerce MS2.2-060817');

  // include server parameters
  require('includes/configure.php');

  // include web-front parameters
  require('includes/configure_site.php');

  // include the list of project filenames
  require(DIR_WS_INCLUDES . 'filenames.php');

  // include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');
  require(DIR_WS_CLASSES . 'database.php');

  // define general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');

  // set the type of request (secure or not)
  //  $request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';
  $request_type = (getenv('SERVER_PORT') == '443') ? 'SSL' : 'NONSSL';

  // set php_self in the local scope
  $PHP_SELF = tep_sanitize_string($_SERVER['PHP_SELF']);

  $g_server = HTTP_SERVER;
  $g_relpath = $g_server . DIR_WS_ADMIN;

  $g_cserver = HTTP_CATALOG_SERVER;
  $g_crelpath = $g_cserver . DIR_WS_CATALOG;

  $g_script = tep_sanitize_string(basename($_SERVER['SCRIPT_NAME']));
  if( !is_string($g_script) || $g_script != basename($PHP_SELF) || !file_exists($g_script) ) {
    tep_redirect();
  }
  if( $g_script == FILENAME_JS_MODULES && !$g_ajax ) {
    //tep_redirect();
  }
  $g_db = new dbase();
  $g_db->connect();

  // set the application parameters
  $configuration_query = $g_db->query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = $g_db->fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
  }

  // Plugins Initialization section
  define('PLUGINS_INSTALL_PREFIX', 'install_');
  define('PLUGINS_ADMIN_PREFIX', 'admin_');
  require(DIR_WS_CLASSES . 'plugins_admin.php');
  require(DIR_WS_CLASSES . 'plugins_base.php');
  $g_plugins = new plugins_admin();
  $g_plugins->invoke('init_early');

  require(DIR_WS_CLASSES . 'sessions.php');
  $g_session = new sessions;
?>
