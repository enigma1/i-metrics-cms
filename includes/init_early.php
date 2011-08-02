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
  // start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());

  // set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);
  //error_reporting(E_ALL|E_STRICT);
  //ini_set('display_errors', 1);

  // define the project version
  define('PROJECT_VERSION', 'I-Metrics CMS with MS2.2');

  require_once(DIR_FS_CLASSES . 'object_info.php');

  // define general functions used application-wide
  require_once(DIR_FS_FUNCTIONS . 'general.php');
  require_once(DIR_FS_FUNCTIONS . 'html_output.php');

  // include the list of project filenames and tables
  tep_define_vars(DIR_FS_INCLUDES . 'filenames.php');
  //tep_define_vars(DIR_WS_INCLUDES . 'database_tables.php');

  $tmp_array = tep_load('http_validator');
  $g_http =& $tmp_array['http'];
  $g_http->bypass = isset($g_bypass)?(bool)$g_bypass:false;
  $g_http->validate_headers();

  $tmp_array = tep_load('defs');
  $cDefs = $tmp_array['cDefs'];

  $g_server =& $cDefs->server;
  $g_relpath =& $cDefs->relpath;
  $g_script =& $cDefs->script;
  $g_media =& $cDefs->media;
  $g_external_path =& $cDefs->external_path;
  $g_external =& $cDefs->external;

  $g_cookie_domain =& $cDefs->cookie_domain;
  $g_cookie_path =& $cDefs->cookie_path;
  $g_seo_flag =& $cDefs->seo;
  $current_abstract_id =& $cDefs->abstract_id;
  $current_gtext_id =& $cDefs->gtext_id;
  $current_page_id =& $cDefs->page_id;

  $check_host = ($cDefs->request_type == 'SSL')?'https://':'http://';
  $check_host .= $_SERVER['HTTP_HOST'];
  if( $check_host != $g_server ) tep_redirect();
  $check_host = substr($_SERVER['REQUEST_URI'], 0, strlen(DIR_WS_CATALOG));
  if( $check_host != DIR_WS_CATALOG ) tep_redirect();
  unset($check_host);

  $g_script = tep_sanitize_string(basename($_SERVER['SCRIPT_NAME']));
  if( !is_string($g_script) || $g_script != basename($_SERVER['SCRIPT_NAME']) || !file_exists($g_script) ) {
    tep_redirect();
  }
  if( $g_script == FILENAME_JS_MODULES && !$cDefs->ajax ) {
    tep_redirect();
  }

  $tmp_array = tep_load('database');
  $g_db = $tmp_array['db'];
  $g_db->connect();

  $tmp_array = tep_load('languages');
  $g_lng = $tmp_array['lng'];
  $g_lng->initialize();
  $g_lng->load_early();

  // set the application parameters
  $configuration_query = $g_db->query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = $g_db->fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
  }

  $tmp_array = tep_load('sessions');
  $g_session = $tmp_array['cSessions'];

  //-MS- SEO URLs Support Added
  if( is_file(DIR_FS_CLASSES . 'seo_url.php') ) {
    require(DIR_FS_CLASSES . 'seo_url.php');
    $g_seo_url = new seoURL;
  }
  //-MS- SEO URLs Support Added EOM

//-MS- Abstract zones added
  require(DIR_FS_CLASSES . 'abstract_front.php');
  require(DIR_FS_CLASSES . 'gtext_front.php');
  require(DIR_FS_CLASSES . 'super_front.php');
  require(DIR_FS_CLASSES . 'image_front.php');
//-MS- Abstract zones added EOM
?>
