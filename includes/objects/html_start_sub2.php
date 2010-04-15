<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: HTML Lower Section
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
  $html_start_sub2 = array(
    DIR_WS_TEMPLATE . 'html_start_sub2.tpl'
  );
  $g_plugins->invoke('html_start_sub2');
  for($i=0, $j=count($html_start_sub2); $i<$j; $i++) {
    require($html_start_sub2[$i]);
  }
?>