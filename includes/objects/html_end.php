<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: HTML Closing section
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  $html_end = array(
    DIR_WS_TEMPLATE . 'html_end.tpl'
  );
  $g_plugins->invoke('html_end');
  for($i=0, $j=count($html_end); $i<$j; $i++) {
    require($html_end[$i]);
  }
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
