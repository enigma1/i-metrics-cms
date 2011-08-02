<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2009 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Text Pages Display
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
  if( !$current_gtext_id ) {
    tep_redirect();
  }
  $generic_query = $g_db->fly("select gtext_title, gtext_description, date_added from " . TABLE_GTEXT . " where gtext_id='" . (int)$current_gtext_id . "'");
  $generic_array = $g_db->fetch_array($generic_query);
  $g_breadcrumb->add($generic_array['gtext_title'], tep_href_link($g_script, 'gtext_id=' . $current_gtext_id));
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  $heading_row = true;
  require(DIR_FS_OBJECTS . 'html_body_header.php');

  $template_file = DIR_WS_TEMPLATE .  'html_' . tep_get_script_name($g_script) . '.tpl';
  if( is_file($template_file) ) {
    include($template_file);
  } else {
?>
            <div><h1><?php echo TEXT_INVALID_PAGE; ?></h1></div>
            <div><?php echo TEXT_INVALID_PAGE_INFO; ?></div>
<?php
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
