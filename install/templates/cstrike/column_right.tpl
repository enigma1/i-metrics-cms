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
        <div>
          <div class="b1" style="margin-right: 6px;"></div>
          <div class="b1" style="margin-right: 4px;"></div>
          <div class="b2" style="margin-right: 2px;"></div>
          <div class="b2" style="margin-right: 1px;"></div>
          <div class="b1"></div>
        </div>
        <div id="rightcontent">
<?php
  $g_plugins->invoke('html_right');

  for($i=0, $j=count($box_array); $i<$j; $i++) {
    include(DIR_WS_TEMPLATE . $box_array[$i]);
  }
?>
        </div>
        <div>
          <div class="b1"></div>
          <div class="b2" style="margin-right: 1px;"></div>
          <div class="b2" style="margin-right: 2px;"></div>
          <div class="b1" style="margin-right: 4px;"></div>
          <div class="b1" style="margin-right: 6px;"></div>
        </div>
