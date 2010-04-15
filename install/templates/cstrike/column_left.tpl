<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Left Column Driver
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
  $box_array = array('super_left_box.tpl');
?>
        <div>
          <div class="b1" style="margin-left: 6px;"></div>
          <div class="b1" style="margin-left: 4px;"></div>
          <div class="b2" style="margin-left: 2px;"></div>
          <div class="b2" style="margin-left: 1px;"></div>
          <div class="b1"></div>
        </div>
        <div id="leftcontent">
          <div class="infoBox">
            <div class="contentBoxHeading infoBoxHeading"><?php echo '<span>' . BOX_HEADING_SEARCH . '</span>'; ?></div>
            <div class="infoBoxContents infoBoxContentsAlt" style="padding-top: 8px; padding-bottom: 8px;">
<?php 
  echo tep_draw_form('quick_find', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'post');
  echo tep_draw_input_field('keywords', '', 'class="search" size="50" maxlength="100" style="width: 150px;"');
  echo tep_image_submit(DIR_WS_TEMPLATE . 'design/search.png', IMAGE_BUTTON_SEARCH, 'style="margin: 0px 0px -4px 8px;"', true);
  echo '</form>';
?>
            </div>
          </div>
<?php
  $g_plugins->invoke('html_left');
  for($i=0, $j=count($box_array); $i<$j; $i++) {
    include(DIR_WS_TEMPLATE . $box_array[$i]);
  }
?>
        </div>
        <div>
          <div class="b1"></div>
          <div class="b2" style="margin-left: 1px;"></div>
          <div class="b2" style="margin-left: 2px;"></div>
          <div class="b1" style="margin-left: 4px;"></div>
          <div class="b1" style="margin-left: 6px;"></div>
        </div>
