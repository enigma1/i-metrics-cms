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
        <div class="leftcontent">
<?php
  $g_plugins->invoke('html_left');
  for($i=0, $j=count($box_array); $i<$j; $i++) {
    include(DIR_WS_TEMPLATE . $box_array[$i]);
  }
?>
        </div>
