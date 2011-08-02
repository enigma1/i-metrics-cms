<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Super Groups Display Template File
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

  $cSuper = new super_front();
  $abstract_array = $cSuper->get_zone_data($current_abstract_id);
?>
        <div class="pageHeader"><h1><?php echo $abstract_array['abstract_zone_name']; ?></h1></div>
        <div class="pageContent"><?php echo $abstract_array['abstract_zone_desc']; ?></div>
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
    $module = DIR_FS_MODULES . $zone_class . '_mod.php';
    $module_class = $zone_class . '_mod';

    if( is_file($module) ) {
      require_once($module);
      $cModule = new $module_class($id);
      $result = $cModule->output();

      // Execute a simple best fit routine to balance the columns
      $index = key($length_array);
      $length_array[$index] += tep_string_length(strip_tags($result));
      $content_array[$index] .= '<div class="splitColumn bspacer">' . $result . '</div>' . "\n";
      asort($length_array, SORT_NUMERIC);
    }
  }
?>
        <div class="bounder">
<?php
  foreach( $content_array as $key => $value ) {
?>
          <div class="halfer floater"><div class="rspacer"><?php echo $content_array[$key]; ?></div></div>
<?php
  }
?>
        </div>

