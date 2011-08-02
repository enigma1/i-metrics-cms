<?php
/*
  $Id: application_top.php,v 1.162 2003/07/12 09:39:03 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - PHP5 Register Globals off and Long Arrays Off support added
// - Fix for navigation history
// - Relocated message stack early.
// - Added validator module
// - Removed PHP3 dependencies
// - Added Cache HTML
// - Recoded cookie/session sent.
// - Added Abstract Zones support
// - Fix for non-terminated scripts having incorrect session info
// - Removed Local configuration dependency.
// - Relocated nav history at the end of the file
// - Discard requests with /POST when no valid session is present
// - Moved early initialization to a different file
// - Moved session process to a different file
// - Database and Session functions converted to classes
// - Transformed script for CMS
// - Added plugin calls
// - Added dynamic path detection
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  ini_set('error_reporting', E_ALL|E_STRICT);
  ini_set('display_errors', 1);

  if( !function_exists('tep_path') ) {
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
    // include web-front parameters
    include_once(DIR_FS_INCLUDES . 'configure_site.php');
  }
  if( !defined('DB_SERVER') || strlen(DB_SERVER) < 1 ) {
    die('<b>Critical</b>: Invalid configuration file - Make sure you installed the I-Metrics CMS');
  }

  $g_media = array();
  $g_ajax = false;

  require(DIR_FS_INCLUDES . 'init_early.php');

  $g_debug->get('reset_timer', 'start_timer');

  // Compatibility functions
  require_once(DIR_FS_FUNCTIONS . 'compatibility.php');

  if( GZIP_COMPRESSION == 'true' ) {
    //ob_start('ob_gzhandler');
    ob_start();
  }

  // setup boxes
  require(DIR_FS_CLASSES . 'common_block.php');
  require(DIR_FS_CLASSES . 'box.php');

  // split-page-results
  require(DIR_FS_CLASSES . 'split_page_results.php');

  require(DIR_FS_CLASSES . 'form_fields.php');
  $g_form_fields = new form_fields();
  // perform the session process
  $g_session->initialize();
  $g_plugins->invoke('init_sessions');

  // include the string files
  require(DIR_FS_STRINGS . FILENAME_COMMON);
  if( is_file(DIR_FS_STRINGS . '/' . $g_script)) {
    include(DIR_FS_STRINGS . $g_script);
  }

  // file uploading class
  require(DIR_FS_CLASSES . 'upload.php');

  // Include validation functions (right now only email address)
  require(DIR_FS_FUNCTIONS . 'validations.php');

  // initialize the message stack for output messages
  $tmp_array = tep_load('message_stack');
  $messageStack =& $tmp_array['msg'];

  // Word-Processor interface
  $g_wp_ifc =& $g_session->register('g_wp_ifc', WORD_PROCESSOR_SWITCH);

  // Make the common action global
  $tmp_array = tep_load('defs');
  $cDefs = $tmp_array['cDefs'];

  if( $cDefs->ajax ) {
    $g_plugins->invoke('ajax_start');
  }

  if( $g_session->new_id ) {
    $messageStack->add(sprintf(WARNING_NEW_SESSION_STARTED, $g_session->get_active_sessions()+1), 'warning', 'header');
  }

  if( $g_lng->new_id && $g_lng->default ) {
    $messageStack->add(sprintf(WARNING_LANGUAGE_SWITCH, $g_lng->get_language_name()), 'warning', 'header');
  }

// default open navigation box
  $selected_box =& $g_session->register('selected_box', 'configuration');

  if( isset($_GET['selected_box']) ) {
    $selected_box = $g_db->prepare_input($_GET['selected_box'], true);
  }
  $g_plugins->invoke('init_late');
  $action = $cDefs->action;
?>
