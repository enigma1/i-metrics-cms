<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Super Groups Display
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
  define('DEFAULT_COLUMN_SUPER_SPLIT', 2);

  require('includes/application_top.php');

  if( !$current_abstract_id ) {
    tep_redirect();
  }
  $cAbstract = new abstract_front();
  if( !$cAbstract->is_zone_type($current_abstract_id, 'super_zones') ) {
    tep_redirect();
  }
  $cSuper = new super_front();
  $abstract_array = $cSuper->get_zone_data($current_abstract_id);
  $breadcrumb->add($abstract_array['abstract_zone_name'], tep_href_link($g_script, 'abz_id=' . $current_abstract_id));
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php require('includes/objects/html_start_sub2.php'); ?>
<?php 
  $heading_row = true;
  require('includes/objects/html_body_header.php');
?>
<?php
  $cSuper = new super_front();
  $abstract_array = $cSuper->get_zone_data($current_abstract_id);
?>
        <div><h1><?php echo $abstract_array['abstract_zone_name']; ?></h1></div>
        <div><?php echo $abstract_array['abstract_zone_desc']; ?></div>
<?php
  $zones_array = $cSuper->get_entries($current_abstract_id);

  // Setup the best fit arrays
  $length_array = array();
  for( $i=0, $j=DEFAULT_COLUMN_SUPER_SPLIT; $i<$j; $i++) {
    $length_array[] = 0;
    $content_array[] = '';
  }

  foreach($zones_array as $id => $zone) {
    if( !$cSuper->is_enabled($id) ) continue;

    $zone_class = $cSuper->get_zone_class($id);
    $module = DIR_WS_MODULES . $zone_class . '_mod.php';
    $module_class = $zone_class . '_mod';

    if( file_exists($module) ) {
      require_once($module);
      $cModule = new $module_class($id);
      $result = $cModule->output();

      // Execute a simple best fit routine to balance the columns
      $index = key($length_array);
      $length_array[$index] += tep_string_length(strip_tags($result));
      $content_array[$index] .= '<div class="splitColumn">' . $result . '</div>' . "\n";
      asort($length_array, SORT_NUMERIC);
    }
  }
?>
        <div class="cleaner" style="padding-top: 4px;">
<?php
  foreach( $content_array as $key => $value ) {
?>
          <div class="colsplit floater"><?php echo $content_array[$key]; ?></div>
<?php
  }
?>
        </div>
<?php require('includes/objects/html_end.php'); ?>
