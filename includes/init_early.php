<?php
/*
This file is part of includes/application_top.php v1.280

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Catalog: Early initialization process
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Early initialization intends to perform and includes the following: 
// - Installation Service
// - Database connection and database support functions inclusion
// - Critical functions and definitions inclusion
// - Definitions for SEO-G so it can decode/translate incoming URLs
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  $g_script = '';
  require('includes/classes/http_validator.php');
  $g_http_validator = new http_validator();
  $g_http_validator->validate_headers();

  // start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());

    // set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);
  //ini_set('error_reporting', E_ALL);
  //ini_set('display_errors', 1);

  // define the project version
  define('PROJECT_VERSION', 'I-Metrics CMS with MS2.2-060817');

  // include server parameters
  if( file_exists('includes/configure.php') ) {
    require('includes/configure.php');
  }

  if( !defined('DB_SERVER') || strlen(DB_SERVER) < 1 ) {
    if (is_dir('install')) {
      header('Location: install/index.php');
      exit();
    }
    die('Invalid configuration file - Could not locate installation folder. ' . PROJECT_VERSION . ' cannot continue.');
  }
  require(DIR_WS_CLASSES . 'object_info.php');
  // include the list of project filenames
  require(DIR_WS_INCLUDES . 'filenames.php');

  // include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');
  // make a connection to the database... now
  require(DIR_WS_CLASSES . 'database.php');

  // define general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');

  // set the type of request (secure or not)
  //  $request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';
  $request_type = (getenv('SERVER_PORT') == '443') ? 'SSL' : 'NONSSL';

  // set php_self in the local scope
  $PHP_SELF =  tep_sanitize_string(basename($_SERVER['PHP_SELF']));

  if ($request_type == 'NONSSL') {
    define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);
    $g_relpath = HTTP_SERVER . DIR_WS_CATALOG;
    $g_server = HTTP_SERVER;
  } else {
    define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);
    $g_relpath = HTTPS_SERVER . DIR_WS_CATALOG;
    $g_server = HTTPS_SERVER;
  }

  $g_script = tep_sanitize_string(basename($_SERVER['SCRIPT_NAME']));
  if( !is_string($g_script) || $g_script != basename($PHP_SELF) || !file_exists($g_script) ) {
    tep_redirect();
  }
  if( $g_script == FILENAME_JS_MODULES && !$g_ajax ) {
    tep_redirect();
  }


  $g_db = new dbase();
  $g_db->connect();

  // set the application parameters
  $configuration_query = $g_db->query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = $g_db->fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
  }

  //-MS- SEO URLs Support Added
  if( file_exists(DIR_WS_CLASSES . 'seo_url.php') ) {
    require(DIR_WS_CLASSES . 'seo_url.php');
    $g_seo_url = new seoURL;
  }
  //-MS- SEO URLs Support Added EOM
?>
