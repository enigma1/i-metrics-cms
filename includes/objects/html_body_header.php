<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// ---------------------------------------------------------------------------
// Front: Common html body header section
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  $html_body_header = array(
    DIR_WS_TEMPLATE . 'html_body_header.tpl'
  );
  $g_plugins->invoke('html_body_header');
  for($i=0, $j=count($html_body_header); $i<$j; $i++) {
    require($html_body_header[$i]);
  }
?>
