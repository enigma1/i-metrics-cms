<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Right Column Driver
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
  $box_array = array('super_right_box.tpl');
?>
        <div id="rightcontent">
          <div class="infoBoxHeading boxpadding"><?php echo BOX_HEADING_SEARCH; ?></div>
          <div class="quicksearch calign"><?php echo tep_draw_form('quick_find', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL'), 'post'); ?>
<?php 
  echo tep_draw_input_field('keywords', BOX_HEADING_SEARCH, 'class="search" size="50" maxlength="100" style="width: 160px"') . tep_image_submit(DIR_WS_TEMPLATE . 'design/search.png', IMAGE_BUTTON_SEARCH, 'style="margin: 0px 0px -4px 8px;"', true);
?>
          </form></div>

<?php
  $args = array('box_array' => &$box_array);
  $cPlug->invoke('html_right', $args);

  for($i=0, $j=count($box_array); $i<$j; $i++) {
    include(DIR_FS_TEMPLATE . $box_array[$i]);
  }

  $box_array = array();
  $args = array('box_array' => &$box_array);
  $cPlug->invoke('html_right_end', $args);

  for($i=0, $j=count($box_array); $i<$j; $i++) {
    include(DIR_FS_TEMPLATE . $box_array[$i]);
  }

?>
          <div class="calign" id="rightfooter"><?php echo FOOTER_TEXT_BODY_COPYRIGHT; ?></div>
        </div>
