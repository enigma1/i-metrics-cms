<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2009 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Text Groups Display
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
  define('DEFAULT_GTEXT_CELL_SPLIT', 3);
  $abstract_query = $g_db->fly("select abstract_zone_id, abstract_zone_name, abstract_zone_desc from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id='" . (int)$current_abstract_id . "'");
  $abstract_array = $g_db->fetch_array($abstract_query);
?>
            <div class="pageHeader"><h1><?php echo $abstract_array['abstract_zone_name']; ?></h1></div>
            <div class="pageContent"><?php echo $abstract_array['abstract_zone_desc']; ?></div>
<?php
  $layout = GTEXT_LISTING_LAYOUT;
  $cText = new gtext_front;
  $listing_sql = $cText->get_entries($abstract_array['abstract_zone_id'], true, ($layout == 1), true);
  $listing_split = new splitPageResults($listing_sql, GTEXT_PAGE_SPLIT, 'gt.gtext_id');
  if( $listing_split->number_of_rows > 0) {
    $split_params = 'abz_id=' . $current_abstract_id;
    $zones_array = $g_db->query_to_array($listing_split->sql_query);
    if( $listing_split->number_of_rows > GTEXT_PAGE_SPLIT && (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3') ) {
?>
          <div class="bounder splitLine">
            <div class="floater"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, $split_params); ?></div>
          </div>
<?php
    }
    if( !$layout ) {
      $zcount = 0;
      $zmax = count($zones_array);
      $width = (int)(97/DEFAULT_GTEXT_CELL_SPLIT);
      foreach($zones_array as $key => $value ) { 
        if( is_int($zcount/DEFAULT_GTEXT_CELL_SPLIT) ) {
          echo '<div class="bounder cleaner" style="padding: 2px 0px 2px 0px;">';
        }
        echo '<div class="blocklink floater" style="width: ' . $width . '%;"><a href="' . tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $key) . '" rel="nofollow">' . ($zcount+1) . '.&nbsp;' . $value['gtext_alt_title'] . '</a></div>' . "\n";
        if( is_int(($zcount+1)/DEFAULT_GTEXT_CELL_SPLIT) || ($zcount+1) == $zmax ) {
          echo '</div>';
        }
        $zcount++;
      }
    } else {
      foreach($zones_array as $key => $value ) { 
        $short_description = strip_tags(tep_truncate_string($value['gtext_description']));
        $html_output = 
        '  <div class="splitColumn bspacer">' . "\n" . 
        '    <div class="floater"><h2><a href="' . tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $value['gtext_id']) . '" title="' . $value['gtext_title'] . '">' . $value['gtext_alt_title'] . '</a></h2></div>' . "\n" .
        '    <div class="floatend">' . tep_date_short($value['date_added']) . '</div>' . "\n" . 
        '    <div class="cleaner">' . $short_description . '</div>' . "\n" . 
        '  </div>' . "\n";
        echo $html_output;
      }
    }
    if( $listing_split->number_of_rows > GTEXT_PAGE_SPLIT && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3') ) {
?>
        <div class="splitLine bounder">
          <div class="floater"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
          <div class="floatend"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, $split_params); ?></div>
        </div>
<?php
      }
    }
?>

