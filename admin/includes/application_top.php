<?php
/*
  $Id: application_top.php,v 1.162 2003/07/12 09:39:03 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
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
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  $g_script = '';
  $g_media = array();
  $g_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])?true:false;

  require('includes/init_early.php');

  // initialize the logger class
  require(DIR_WS_CLASSES . 'logger.php');

  // some code to solve compatibility issues
  require(DIR_WS_FUNCTIONS . 'compatibility.php');

// email classes
  require(DIR_WS_CLASSES . 'mime.php');
  require(DIR_WS_CLASSES . 'email.php');

// setup our boxes
  require(DIR_WS_CLASSES . 'common_block.php');
  require(DIR_WS_CLASSES . 'box.php');

  require(DIR_WS_CLASSES . 'message_stack.php');
  //require(DIR_WS_CLASSES . 'navigation_history.php');

// split-page-results
  require(DIR_WS_CLASSES . 'split_page_results.php');

// entry/item info classes
  require(DIR_WS_CLASSES . 'object_info.php');
  require(DIR_WS_CLASSES . 'form_fields.php');
  $g_form_fields = new form_fields();
  // perform the session process
  $g_session->initialize();
  $g_session->process_agents();

// include the language translations
  require(DIR_WS_STRINGS . FILENAME_COMMON);
  if (file_exists(DIR_WS_STRINGS . '/' . $g_script)) {
    include(DIR_WS_STRINGS . $g_script);
  }

// file uploading class
  require(DIR_WS_CLASSES . 'upload.php');

// Include validation functions (right now only email address)
  require(DIR_WS_FUNCTIONS . 'validations.php');

// initialize the message stack for output messages
  $messageStack = new messageStack;

  if( $g_ajax ) {
    $g_plugins->invoke('init_ajax');
    if($g_script != FILENAME_JS_MODULES) {
      $g_session->close();
    }
  }

  if( $g_session->new_id ) {
    $messageStack->add(sprintf(WARNING_NEW_SESSION_STARTED, $g_session->get_active_sessions()+1), 'warning', 'header');
  }

// default open navigation box
  $selected_box =& $g_session->register('selected_box');
  if( !$g_session->is_registered('selected_box') || empty($selected_box) ) {
    $selected_box = 'configuration';
  }

  if( isset($_GET['selected_box']) ) {
    $selected_box = $g_db->prepare_input($_GET['selected_box'], true);
  }
  $g_plugins->invoke('init_late');
?>
