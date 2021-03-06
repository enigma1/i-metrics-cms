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
      <div class="totalsize hideflow cleaner" id="maindriver" style="margin-top: 116px;">
        <div class="midsize extend floater" id="midpane" style="margin-left: 180px;">
<?php
  $messageStack->output('header');
  $messageStack->output();
?>
          <div id="midcontent">
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