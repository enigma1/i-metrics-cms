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
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
// Initialize current global indices for supported entities
  $g_counter = 0;
  $g_external_path = '';
  $current_abstract_id = 0;
  $current_gtext_id = 0;
  $current_page_id = 0;
  $g_development = false;
  $g_media = array();
  $g_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])?true:false;

//-MS- SEO-G Added
  if( !isset($g_seo_flag) || $g_seo_flag !== true) {
    // Initialize database and basic functions
    require('includes/init_early.php');
  }
//-MS- SEO-G Added EOM
  if( defined('HTTP_EXTERNAL_PATH') ) {
    $g_external_path = HTTP_EXTERNAL_PATH;
  }

  require(DIR_WS_CLASSES . 'plugins_front.php');
  require(DIR_WS_CLASSES . 'plugins_base.php');
  $g_plugins = new plugins_front();
  $g_plugins->invoke('init_early');

  require(DIR_WS_CLASSES . 'sessions.php');
  $g_session = new sessions;

  $g_plugins->invoke('init_record');

// some code to solve compatibility issues
  require(DIR_WS_FUNCTIONS . 'compatibility.php');

  // if gzip_compression is enabled, start to buffer the output
  if ( (GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded = extension_loaded('zlib')) ) {
    if (($ini_zlib_output_compression = (int)ini_get('zlib.output_compression')) < 1) {
      ob_start('ob_gzhandler');
    } else {
      ini_set('zlib.output_compression_level', GZIP_LEVEL);
    }
  }

// set the cookie domain
  $cookie_domain = (($request_type == 'NONSSL') ? HTTP_COOKIE_DOMAIN : HTTPS_COOKIE_DOMAIN);
  $cookie_path = (($request_type == 'NONSSL') ? HTTP_COOKIE_PATH : HTTPS_COOKIE_PATH);

  //-MS- HTML Cache Support Added
  require(DIR_WS_CLASSES . 'cache_html.php');
  //-MS- HTML Cache Support Added EOM

// include navigation history class
  require(DIR_WS_CLASSES . 'navigation_history.php');
  require(DIR_WS_CLASSES . 'boxes.php');
  require(DIR_WS_CLASSES . 'message_stack.php');

//-MS- perform the session process
  $g_session->initialize();
  $g_session->process_agents();

//-MS- Parameters Validator added
  require(DIR_WS_CLASSES . 'validator.php');
  $g_validator = new validator;
//-MS- Parameters Validator added EOM

  require(DIR_WS_STRINGS . FILENAME_COMMON);
  if( file_exists(DIR_WS_STRINGS . '/' . $g_script) ) {
    include(DIR_WS_STRINGS . $g_script);
  }

// navigation history
  $g_navigation =& $g_session->register('g_navigation');
  if( !$g_session->is_registered('g_navigation') || !is_object($g_navigation) ) {
    $g_navigation = new navigationHistory;
  }

//-MS- moved for early error control
  $messageStack = new messageStack;
//-MS- moved for early error control EOM

//-MS- Set HTML Cache for visitors via 304 after session is started
  if( $g_session->has_started() && !empty($g_sid) ) {
    $g_html_cache =& $g_session->register('g_html_cache');
    if( !$g_session->is_registered('g_html_cache') || !is_object($g_html_cache) ) {
      $g_html_cache = new cacheHTML;
    }
    $g_html_cache->check_script();
  }
//-MS- Set HTML Cache for visitors via 304 after session is started EOM

// include the who's online functions
  require(DIR_WS_FUNCTIONS . 'whos_online.php');
  tep_update_whos_online();

// include validation functions (right now only email address)
  require(DIR_WS_FUNCTIONS . 'validations.php');

// split-page-results
  require(DIR_WS_CLASSES . 'split_page_results.php');

// include the breadcrumb class and start the breadcrumb trail
  require(DIR_WS_CLASSES . 'breadcrumb.php');
  $breadcrumb = new breadcrumb;

//-MS- Abstract zones added
  require(DIR_WS_CLASSES . 'abstract_front.php');
  require(DIR_WS_CLASSES . 'gtext_front.php');
  require(DIR_WS_CLASSES . 'super_front.php');
  require(DIR_WS_CLASSES . 'image_front.php');
//-MS- Abstract zones added EOM

  if( $g_ajax ) {
    $g_plugins->invoke('init_ajax');
    if($g_script != FILENAME_JS_MODULES) {
      $g_session->close();
    }
  }

  if( isset($_GET['action']) && $_GET['action'] == 'plugin_form_process' ) {
    $g_plugins->invoke('plugin_form_process');
  }

  $g_navigation->add_current_page();
  $g_plugins->invoke('init_late');
?>
