<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Comments System Runtime processing script
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
------------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
//
*/
  require('includes/application_top.php');
  $g_navigation->remove_current_page();

  header("Cache-Control: no-cache");
  header("Content-Type: text/css"); 

  $storage = $g_session->register('comments_system');
  if( !is_array($storage) ) $storage = array();
  if( !isset($storage['css_buttons']) ) {
    $storage['css_buttons'] = array();
  }
  $html = implode("\n", $storage['css_buttons']);
  if( !empty($html) ) echo $html;
  $g_session->close();
?>
