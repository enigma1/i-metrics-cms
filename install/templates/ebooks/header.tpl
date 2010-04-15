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
?>
  <div class="totalsize negate cleaner" id="header">
    <div class="leftlogo floater"><?php echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_TEMPLATE . 'design/logo.jpg', STORE_NAME) . '</a>'; ?></div>
    <div class="floatend" style="height: 56px;">
      <div class="quicksearch floater" style="padding-top: 10px;"><?php echo tep_draw_form('quick_find', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'post'); ?>
<?php 
  echo tep_draw_input_field('keywords', 'Search this site', 'class="search" size="50" maxlength="100" style="width: 260px"');
  echo tep_image_submit(DIR_WS_TEMPLATE . 'design/search.png', IMAGE_BUTTON_SEARCH, 'style="margin: 0px 0px -4px 8px;"', true);
?>
      </form></div>
    </div>
    <div class="cleaner floater">
      <div class="breadcrumb lcharsep">
<?php
  $string = $breadcrumb->trail();
  if( empty($string) ) {
    $breadcrumb->add(HEADER_TITLE_CATALOG, tep_href_link());
    $string = $breadcrumb->trail();
  }
  echo $string; 
?>
      </div>
    </div>
  </div>
