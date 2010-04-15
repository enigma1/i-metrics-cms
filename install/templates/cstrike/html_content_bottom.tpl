<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2007 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// ---------------------------------------------------------------------------
// Common html bottom of page
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  if( !isset($html_lines_array) || !is_array($html_lines_array) || !count($html_lines_array) ) {
    $html_lines_array = array();
    $html_lines_array[] = '<div><a href="' . tep_href_link() . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a></div>' . "\n";
  }
?>
            <div class="cleaner"></div><div class="formButtons cleaner ralign">
<?php
  foreach($html_lines_array as $key => $value) {
    echo $value;
  }
?>
            </div>
<?php
  unset($html_lines_array);
?>