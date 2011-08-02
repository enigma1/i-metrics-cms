<?php
/*
  $Id: application_top.php,v 1.280 2003/07/12 09:38:07 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2006-2009 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - PHP5 Register Globals off and Long Arrays Off support added
// - Added SEO-G
// - Added META-G
// - Removed stock friendly URLs
// - Relocated message stack early.
// - Added validator module
// - Removed PHP3 dependencies
// - Added Cache HTML
// - Recoded cookie/session sent.
// - Added Abstract Zones support
// - Fix for non-terminated scripts having incorrect session info
// - Removed Local configuration dependency.
// - DBase - Do not process invalid parameters
// - Relocated nav history at the end of the file
// - Discard requests with /POST when no valid session is present
// - Moved early initialization to a different file
// - Moved session process to a different file
// - Transformed script for CMS
// - Changed navigation history and sessions removed global sessions
// - Added path detection
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
// Path Detection
  if (!function_exists('tep_path')) {
    function tep_path($file='') {
      static $base = '';
      if( empty($base) ) {
        $base = preg_replace('/\w+\/\.\.\//', '', dirname(__FILE__));
        $base = str_replace('\\', '/', $base);
        $base = rtrim($base, '/');
        $tmp_array = explode('/', $base);
        array_pop($tmp_array);
        $base = implode('/', $tmp_array) . '/';
        if( !strlen(ini_get('date.timezone')) && function_exists('date_default_timezone_get')) {
          date_default_timezone_set(@date_default_timezone_get());
        }
      }
      $full = $base . $file;
      return $full;
    }
  }
  tep_path();
  // include server parameters
  if( is_file(tep_path('includes/configure.php')) ) {
    require_once(tep_path('includes/configure.php'));
  }
  if( !defined('DB_SERVER') || strlen(DB_SERVER) < 1 ) {
    if( is_dir(tep_path('install')) ) {
      header('Location: install/index.php');
      exit();
    }
    die('Critical: Invalid configuration file - Could not locate installation folder for the I-Metrics CMS.');
  }
  if( isset($g_exit_path) ) return;

  // Initialize database and basic functions
  require_once(DIR_FS_INCLUDES . 'init_early.php');

  if( defined('HTTP_EXTERNAL_PATH') ) {
    $g_external_path = HTTP_EXTERNAL_PATH;
  }

  define('PLUGINS_AJAX_PREFIX', 'ajax_');
  require(DIR_FS_CLASSES . 'plugins_front.php');
  require(DIR_FS_CLASSES . 'plugins_base.php');

  $tmp_array = tep_load('plugins_front');
  $g_plugins =& $tmp_array['cPlug'];
  $g_plugins->invoke('init_early');

// some code to solve compatibility issues
  require(DIR_FS_FUNCTIONS . 'compatibility.php');

  if( GZIP_COMPRESSION == 'true' ) {
    ob_start();
  }

// include navigation history class
  require(DIR_FS_CLASSES . 'boxes.php');

  //-MS- perform the session process
  extract($g_session->initialize());
  $g_plugins->invoke('init_sessions');

  //-MS- Parameters Validator added
  $tmp_array = tep_load('validator');
  $g_validator =& $tmp_array['cValidator'];
  //-MS- Parameters Validator added EOM

  $g_plugins->invoke('init_language');
  $g_lng->load_strings();

  // navigation history
  $tmp_array = tep_load('history');
  $g_navigation =& $tmp_array['cHistory'];

  $tmp_array = tep_load('message_stack');
  $messageStack =& $tmp_array['msg'];

  //-MS- Set HTML Cache for visitors via 304 after session is started
  $tmp_array = tep_load('cache_html');
  $g_cache_html =& $tmp_array['cache_html'];
  $g_cache_html->check_script();
  //-MS- Set HTML Cache for visitors via 304 after session is started EOM

// include the who's online functions
  require(DIR_FS_FUNCTIONS . 'whos_online.php');
  tep_update_whos_online();

// include validation functions (right now only email address)
  require(DIR_FS_FUNCTIONS . 'validations.php');

// split-page-results
  require(DIR_FS_CLASSES . 'split_page_results.php');

// include the breadcrumb class and start the breadcrumb trail
  require(DIR_FS_CLASSES . 'breadcrumb.php');
  $tmp_array = tep_load('breadcrumb');
  $g_breadcrumb =& $tmp_array['breadcrumb'];


  if( $cDefs->ajax ) {
    $g_plugins->invoke('ajax_start');
    if($g_script != FILENAME_JS_MODULES) {
      //$g_session->close();
    }
  }

  $g_plugins->invoke('init_post');

  if( $cDefs->action == 'plugin_form_process' ) {
    $g_plugins->invoke('plugin_form_process');
  }

  $g_navigation->add_current_page();
  $g_plugins->invoke('init_late');

  $action = $cDefs->action;
?>
