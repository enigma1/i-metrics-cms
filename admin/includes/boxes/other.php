<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Other Contents Box
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
?>
<?php
  if( defined('HELP_SHOT') ) {
    $images_array = explode(',', HELP_SHOT);
    $main_image = array_shift($images_array);
?>
          <div id="help_image_group" class="menuBoxHeading calign">
<?php
    echo '<a rel="image_group_shot" href="' . $g_relpath . DIR_WS_IMAGES . $main_image . '" target="_blank">' . tep_image(DIR_WS_IMAGES . 'quick_help_shot.png', BOX_OTHER_QUICK_HELP) . '<br />' . BOX_OTHER_QUICK_HELP . '</a>'; 
    foreach($images_array as $key => $value ) {
      $value = trim($value);
      echo '<a rel="image_group_shot" href="' . $g_relpath . DIR_WS_IMAGES . $value . '" style="display: none;"></a>';
    }
?>
          </div>
<?php
  }
?>
          <div class="menuBoxHeading calign"><?php echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_IMAGES . 'admin_home.png', BOX_OTHER_ROOT) . '<br />' . BOX_OTHER_ROOT . '</a>'; ?></div>
          <div class="menuBoxHeading calign"><?php echo '<a href="http://demos.asymmetrics.com" target="_blank">' . tep_image(DIR_WS_IMAGES . 'admin_help.png', BOX_OTHER_DOCUMENTATION) . '<br />' . BOX_OTHER_DOCUMENTATION . '</a>'; ?></div>
