<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Side Box of Super Zones
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
------------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  $base = 'infoBoxHeading infoBoxRightHeading';
  $max = 2;

  $cSuper = new super_front;
  $zones_array = $cSuper->get_entries(SUPER_RIGHT_ZONE_ID);

  $cAbstract = new abstract_front();
  $rows = 0;
  foreach($zones_array as $id => $zone) { 
    $zone_class = $cAbstract->get_zone_class($id);
    $script = FILENAME_GENERIC_PAGES;
    switch($zone_class) {
      case 'image_zones':
        $script = FILENAME_IMAGE_PAGES;
        break;
      default:
        break;
    }
    $zone_entries = $cAbstract->get_entries($id);
    if( count($zone_entries) ) {
      $class_contents = $base . ($rows%$max);
      echo '<div class="infoBox">' . "\n";
      $rows++;
      $text_data = $cAbstract->get_zone_data($id);
      $zone = $text_data['abstract_zone_name'];

      $info_box_contents = array();
      $info_box_contents[] = array(
        'text' => '<h2><a href="' . tep_href_link($script, 'abz_id=' . $id) . '" title="' . $zone . '">' . $zone . '</a></h2>'
      );
      new contentBoxHeading($info_box_contents, $class_contents);
      $info_box_contents = array();
      $info_box_contents[] = array('text' => tep_truncate_string($text_data['abstract_zone_desc']));
      $info_box_contents[] = array('text' => '<a href="' . tep_href_link($script, 'abz_id=' . $id) . '" title="' . $zone . '">' . TEXT_READ_MORE . $zone . '</a>');
      new contentBox($info_box_contents, 'infoBoxContents');
      echo '</div>' . "\n";
    }
  }
?>
