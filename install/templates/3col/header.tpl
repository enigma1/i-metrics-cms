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
  <div class="totalsize cleaner" id="header">
    <div class="leftlogo floater"><?php echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_TEMPLATE . 'design/logo.png', STORE_NAME) . '</a>'; ?></div>
    <div class="floater" style="height: 38px; background: #000; padding: 0px 0px 0px 20px; width: 630px;">
      <div class="quicksearch floater"><?php echo tep_draw_form('quick_find', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'post'); ?>
<?php 
  echo tep_draw_input_field('keywords', 'Search this site', 'class="search" size="50" maxlength="100" style="width: 260px"') . tep_image_submit(DIR_WS_TEMPLATE . 'design/search.png', IMAGE_BUTTON_SEARCH, 'style="margin: 0px 0px -4px 8px;"', true);
?>
      </form></div>
    </div>
    <div class="floater" style="height: 21px; line-height: 21px; width: 620px; background: #4D625D; color: #FFC; font-size: 10px; font-weight:bold; padding: 0px 10px 0px 20px;">
      <div class="floater"><?php echo 'I-Metrics CMS (Version 1.11)'; ?></div>
      <div class="floatend">
<?php
  $cSuper = new super_front();
  $super_array = $cSuper->get_zones_by_class('super_zones');
  $links_array = array();
  foreach( $super_array as $key => $value ) {
    $links_array[] = '<a href="' . tep_href_link(FILENAME_SUPER_PAGES, 'abz_id=' . $key) . '" title="' . $value['abstract_zone_name'] . '">' . $value['abstract_zone_name'] . '</a>';
  }

  $contact_query = $g_db->fly("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . GTEXT_CONTACT_ID . "' and status='1'");
  if( $g_db->num_rows($contact_query) ) {
    $contact_array = $g_db->fetch_array($contact_query);
    $links_array[] = '<a href="' . tep_href_link(FILENAME_CONTACT_US, '', 'SSL') . '">' . $contact_array['gtext_title'] . '</a>';
  }
  if( count($links_array) ) {
    echo implode('&nbsp;&nbsp;|&nbsp;&nbsp;', $links_array);
  }
?>
      </div>
    </div>
    <div class="cleaner">
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
      <div class="calign" style="height: 200px;"><?php echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_TEMPLATE . 'design/banner1.png', STORE_NAME) . '</a>'; ?></div>
    </div>
  </div>
