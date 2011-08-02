<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Common Index Rendering Module
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
//
*/
  for($i=0, $j=count($entries_array); $i<$j; $i++) {
    $href_id = $data_id = $attr = '';
    if( isset($entries_array[$i]['href_id']) ) {
      $href_id = ' id="' . $entries_array[$i]['href_id'] . '"';
    }

    if( isset($entries_array[$i]['sub']) ) {
      $attr = 'class="sandbox" attr="' . $entries_array[$i]['sub'] . '"';
    }
    if( isset($entries_array[$i]['id']) ) {
      $data_id = ' data-id="' . $entries_array[$i]['id'] . '"';
    }
    if( $i >= $system_start_count && $i < $system_end_count) {
      $class = 'plugin colorblock floater calign';
    } else {
      $class = 'homeCell colorblock floater calign';
    }
?>
              <div class="<?php echo $class; ?>" style="width: 132px; height: 160px;"<?php echo $data_id; ?>><?php echo '<a href="' . $entries_array[$i]['href'] . '" title="' . $entries_array[$i]['title'] . '"' . $href_id . $attr . '>' . $entries_array[$i]['image'] . '<br />' . $entries_array[$i]['title'] . '</a>'; ?></div>
<?php
  }
?>
