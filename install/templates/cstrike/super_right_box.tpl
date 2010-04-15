<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Right Side Box of Super Zones
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
  $cSuper = new super_front;
  $zones_array = $cSuper->get_entries(SUPER_RIGHT_ZONE_ID);

  $cAbstract = new abstract_front();
  $rows = 0;
  foreach($zones_array as $id => $zone) { 
    $zone_class = $cAbstract->get_zone_class($id);
    //$script = FILENAME_GENERIC_PAGES;
    $script = '';
    switch($zone_class) {
      case 'image_zones':
        $script = FILENAME_IMAGE_PAGES;
        break;
      default:
        break;
    }
    if( empty($script) ) continue;
    $cImage = new image_front;
    $zone_entries_raw = $cImage->get_entries($id, true, true, true);

    if( empty($zone_entries_raw) ) continue;
    $zone_entries_raw .= " limit 3";
    $zone_entries = $g_db->query_to_array($zone_entries_raw);
    $j = count($zone_entries);
    if( !$j ) continue;

    $text_data = $cAbstract->get_zone_data($id);
    $zone = $text_data['abstract_zone_name'];
    echo '<div class="infoBox">' . "\n";
    $rows++;

    echo '<div class="contentBoxHeading"><h2><a href="' . tep_href_link($script, 'abz_id=' . $id) . '" title="' . $zone . '">' . $zone . '</a></h2></div>';
    for($i=0; $i<$j; $i++) {
      $value = $zone_entries[$i];
      echo '<div class="calign" style="width: ' . (SMALL_IMAGE_WIDTH+16) . 'px; margin: 4px auto 0px auto;">';
      echo '  <div class="imagelink"><a href="' . tep_href_image_link($value['image_file']) . '" rel="nofollow" title="' . $value['image_alt_title'] . '" target="_blank" style="height: ' . (SMALL_IMAGE_HEIGHT+16) . 'px; width:' . (SMALL_IMAGE_WIDTH+16) . 'px;">' . tep_image(DIR_WS_IMAGES . $value['image_file'], $value['image_alt_title'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'style="padding: 8px;"') . '</a></div>' . "\n";
      echo '  <div class="imageline"><a href="' . tep_href_image_link($value['image_file']) . '" title="' . $value['image_alt_title'] . '" target="_blank">' . $value['image_title'] . '</a></div>';
      echo '</div>' . "\n";
    }
    echo '<div class="contentBoxHeading"><a href="' . tep_href_link($script, 'abz_id=' . $id) . '" title="' . $zone . '">' . TEXT_READ_MORE . $zone . '</a></div>';
/*
    $zone = $text_data['abstract_zone_name'];
    $info_box_contents = array();
    $info_box_contents[] = array(
                                 'text' => '<h2><a href="' . tep_href_link($script, 'abz_id=' . $id) . '" title="' . $zone . '">' . $zone . '</a></h2>'
                                );
    new contentBoxHeading($info_box_contents);

    $info_box_contents = array();
    $info_box_contents[] = array('text' => $text_data['abstract_zone_desc']);
    $info_box_contents[] = array('text' => '<a href="' . tep_href_link($script, 'abz_id=' . $id) . '" title="' . $zone . '">' . TEXT_READ_MORE . $zone . '</a>');
    $class = ($rows%2)?'infoBoxContents':'infoBoxContents infoBoxContentsAlt';
    new contentBox($info_box_contents, $class);
*/
    echo '</div>' . "\n";
  }
?>
