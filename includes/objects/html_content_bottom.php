<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2007 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// ---------------------------------------------------------------------------
// Front: Common html bottom of page
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  $html_content_bottom = array(
    DIR_FS_TEMPLATE . 'html_content_bottom.tpl'
  );
  $g_plugins->invoke('html_content_bottom');
  for($i=0, $j=count($html_content_bottom); $i<$j; $i++) {
    require($html_content_bottom[$i]);
  }
?>