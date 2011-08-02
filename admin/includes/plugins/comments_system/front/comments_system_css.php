<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
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
  extract(tep_load('history'));
  $cHistory->remove_current_page();

  $g_http->set_headers(
    "Cache-Control: no-cache",
    "Content-Type: text/css"
  );
  $g_http->send_headers();

  $storage = $g_session->register('comments_system');
  if( !is_array($storage) ) $storage = array();
  if( !isset($storage['css_buttons']) ) {
    $storage['css_buttons'] = array();
  }
  $html = implode("\n", $storage['css_buttons']);
  if( !empty($html) ) echo $html;
  $g_session->close();
?>
