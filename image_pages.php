<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Image Groups Display
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
  $cImage = new image_front;
  if( !$cImage->is_zone_type($current_abstract_id, 'image_zones') ) {
    tep_redirect();
  }
  $cImage = new image_front;
  $abstract_array = $cImage->get_zone_data($current_abstract_id);
  $breadcrumb->add($abstract_array['abstract_zone_name'], tep_href_link($g_script, 'abz_id=' . $current_abstract_id));
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php require('includes/objects/html_start_sub2.php'); ?>
<?php 
  $heading_row = true;
  require('includes/objects/html_body_header.php');
?>
<?php
  $cImage = new image_front;
  $abstract_array = $cImage->get_zone_data($current_abstract_id);
?>
            <div><h1><?php echo $abstract_array['abstract_zone_name']; ?></h1></div>
            <div class="desc"><?php echo $abstract_array['abstract_zone_desc']; ?></div>
<?php
    $cImage = new image_front;

    $cText = new gtext_front;
    $listing_sql = $cImage->get_entries($current_abstract_id, true, true, true);
    $listing_split = new splitPageResults($listing_sql, IMAGE_PAGE_SPLIT);

    if( $listing_split->number_of_rows > 0) {
      $split_params = 'abz_id=' . $current_abstract_id;
      $zones_array = $g_db->query_to_array($listing_split->sql_query);

      if( $listing_split->number_of_rows > IMAGE_PAGE_SPLIT && (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3') ) {
?>
          <div class="splitLine bspacer">
            <div class="floater"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, $split_params); ?></div>
          </div>
<?php
      }
      echo '<div class="collection cleaner">';

      foreach($zones_array as $key => $value ) { 
        echo '<div class="floater calign" style="width: ' . (SMALL_IMAGE_WIDTH+16) . 'px; margin: 0px 4px 22px 4px;">';
        echo '  <div class="imagelink"><a href="' . tep_href_image_link($value['image_file']) . '" rel="nofollow" title="' . $value['image_alt_title'] . '" target="_blank" style="height: ' . (SMALL_IMAGE_HEIGHT+16) . 'px; width:' . (SMALL_IMAGE_WIDTH+16) . 'px;">' . tep_image(DIR_WS_IMAGES . $value['image_file'], $value['image_alt_title'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'style="padding: 8px;"') . '</a></div>' . "\n";
        echo '  <div class="imageline"><a href="' . tep_href_image_link($value['image_file']) . '" title="' . $value['image_alt_title'] . '" target="_blank">' . $value['image_title'] . '</a></div>';
        echo '</div>' . "\n";
      }
      echo '</div>';
      if( $listing_split->number_of_rows > IMAGE_PAGE_SPLIT && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3') ) {
?>
          <div class="splitLine">
            <div class="floater"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, $split_params); ?></div>
          </div>
<?php
      }
    }
?>
<?php require('includes/objects/html_end.php'); ?>
