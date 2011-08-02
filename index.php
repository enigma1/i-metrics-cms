<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Home Page
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
  require('includes/application_top.php');
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php 
  // Signal the html_body_header.php we handle the H1 header
  $heading_row = true;
  require('includes/objects/html_body_header.php'); 

  $template_file = DIR_WS_TEMPLATE .  'html_home_zones.tpl';
  require($template_file);
/*
  $gtext_query = $g_db->query("select gtext_id, gtext_title, gtext_description from " . TABLE_GTEXT . " where gtext_id = '" . DEFAULT_FRONT_PAGE_ID . "'");
  $gtext_array = $g_db->fetch_array($gtext_query);
?>
        <div><h1><?php echo $gtext_array['gtext_title']; ?></h1></div>
        <div><?php echo $gtext_array['gtext_description']; ?></div>
<?php
*/
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
