<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
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
    <div class="totalsize balancer" id="mainbody">
      <div class="totalsize hideover" id="maindriver">
<!-- header //-->
<?php require(DIR_FS_TEMPLATE . 'header.tpl'); ?>
<!-- header_eof //-->
        <div class="leftsize floater" id="leftpane">
<?php
  $messageStack->output('header');
  $messageStack->output();
?>
          <div id="leftcontent">
<?php
  if( !isset($heading_row) ) {
    $title = HEADING_TITLE;
    if( isset($s_name_params) && !empty($s_name_params) ) {
      $title .= ' ' . $s_name_params;
    }
?>
            <div class="bounder">
<?php
    if(defined('HEADING_IMAGE') ) {
?>
              <div class="floater"><h1><?php echo $title; ?></h1></div>
              <div class="floater"><?php echo tep_image(DIR_WS_IMAGES . HEADING_IMAGE, HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></div>
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