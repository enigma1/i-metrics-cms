<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Image Zones class
//----------------------------------------------------------------------------
// Front: Main Header Section
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
  $cPlug->invoke('html_header_pre');
?>
  <div class="totalsize" id="header">
    <div class="bg1 bounder">
      <div class="leftlogo floater"><?php echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_TEMPLATE . 'design/logo.png', STORE_NAME) . '</a>'; ?></div>
      <div class="floater" style="color: #FFC; font-size: 10px; padding-left: 2px; padding-top: 18px; font-weight:bold;"><?php echo 'Version 1.12'; ?></div>
      <div class="floatend"><?php $cPlug->invoke('html_header_top'); ?></div>
    </div>
    <div class="bounder">
      <div class="breadcrumb lcharsep">
<?php
  $string = $g_breadcrumb->trail();
  if( empty($string) ) {
    $g_breadcrumb->add(HEADER_TITLE_CATALOG, tep_href_link());
    $string = $g_breadcrumb->trail();
  }
  echo $string; 
?>
      </div>
      <div class="mainlogo calign"><?php $cPlug->invoke('html_header_post'); ?></div>
    </div>
<?php
  $cPlug->invoke('html_menu');
?>
  </div>