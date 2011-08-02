<?php
/*
  $Id: cookie_usage.php,v 1.2 2003/06/05 23:26:23 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Catalog: Cookie Usage page
------------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - 07/05/2007: PHP5 Register Globals and Long Arrays Off support added
// - 07/08/2007: PHP5 Long Arrays Off support added
// - 07/12/2007: Moved HTML Header/Footer to a common section
// - 08/31/2007: HTML Body Common Sections Added
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  require('includes/application_top.php');
  $g_breadcrumb->add(NAVBAR_TITLE, tep_href_link($g_script));
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php 
  // Signal the html_body_header.php we handle the H1 header
  $heading_row = true;
  require(DIR_FS_OBJECTS . 'html_body_header.php'); 

  $gtext_query = $g_db->query("select gtext_id, gtext_title, gtext_description from " . TABLE_GTEXT . " where gtext_id = '" . GTEXT_COOKIES_ID . "'");
  $gtext_array = $g_db->fetch_array($gtext_query);
?>
        <div><h1><?php echo $gtext_array['gtext_title']; ?></h1></div>
<?php
/*
        <div><?php echo $gtext_array['gtext_description']; ?></div>
*/
?>
        <div><?php echo TEXT_INFORMATION; ?></div>
<?php require(DIR_FS_OBJECTS . 'html_content_bottom.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
