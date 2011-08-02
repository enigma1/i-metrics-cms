<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2009 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Text Groups and Text Pages Display
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

  if( !$current_abstract_id ) {
    tep_redirect();
  }
  $abstract_query = $g_db->fly("select abstract_zone_id, abstract_zone_name, abstract_zone_desc from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id='" . (int)$current_abstract_id . "'");
  $abstract_array = $g_db->fetch_array($abstract_query);
  $g_breadcrumb->add($abstract_array['abstract_zone_name'], tep_href_link($g_script, 'abz_id=' . $current_abstract_id));
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  $heading_row = true;
  require(DIR_FS_OBJECTS . 'html_body_header.php');

  $cAbstract = new abstract_front();
  $class = $cAbstract->get_zone_class($current_abstract_id);
  $template_file = DIR_FS_TEMPLATE .  'html_' . $class . '.tpl';
  if( is_file($template_file) ) {
    include($template_file);
  } else {
?>
            <div><h1><?php echo TEXT_INVALID_COLLECTION; ?></h1></div>
            <div><?php echo TEXT_INVALID_COLLECTION_INFO; ?></div>
<?php
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
