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
?>
    <div class="totalsize negate" id="mainbody">
      <div class="totalsize hideflow cleaner" id="maindriver">
        <div class="decou coffset">
          <div class="b1" style="margin-left: 6px; margin-right: 6px;"></div>
          <div class="b1" style="margin-left: 4px; margin-right: 4px;"></div>
          <div class="b2" style="margin-left: 2px; margin-right: 2px;"></div>
          <div class="b2" style="margin-left: 1px; margin-right: 1px;"></div>
          <div class="b1"></div>
        </div>
        <div class="decom midsize coffset floater" id="midpane">
<?php
  $messageStack->output('header');
  $messageStack->output();
?>
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
          <div class="scroller" id="midcontent">
<?php
  if( !isset($heading_row) ) {
    $title = HEADING_TITLE;
    if( isset($s_name_params) && !empty($s_name_params) ) {
      $title .= ' ' . $s_name_params;
    }
?>
            <div class="cleaner">
<?php
    if(defined('HEADING_IMAGE') ) {
?>
              <div class="floater"><h1><?php echo $title; ?></h1></div>
              <div class="floater"><h1><?php echo tep_image(DIR_WS_IMAGES . HEADING_IMAGE, HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></h1></div>
<?php
    } else {
?>
              <div><h1><?php echo $title; ?></h1></div>
<?php
    }
?>
            </div>
<?php
  }
?>