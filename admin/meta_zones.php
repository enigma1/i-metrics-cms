<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: META-G Controller
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Generates meta tags from parameters
// Featuring Support for:
// - Generic Pages, Abstract Zones etc
// - Multi-Pages instant selection/editing
// - Multi-Zones instant selection/editing
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
  require(DIR_FS_CLASSES . FILENAME_META_ZONES);

  unset($meta_zone_script);
  // initialize the abstract zone class for different type support
  if( isset($_GET['zID']) && !empty($_GET['zID']) ) {
    $meta_zone_query = $g_db->fly("select meta_types_class, meta_types_name from " . TABLE_META_TYPES . " where meta_types_id = '" . (int)$_GET['zID'] . "'");
    if( $meta_zone = $g_db->fetch_array($meta_zone_query) ) {
      $meta_zone_script = $meta_zone['meta_types_class'];
    }
  }

  if( isset($meta_zone_script) && !empty($meta_zone_script) ) {
    require(DIR_FS_CLASSES . $meta_zone_script . '.php');
  } else {
    $meta_zone_script = 'meta_zones';
  }

  $cMETA = new $meta_zone_script();
  $cMETA->process_saction();
  $cMETA->process_action();

  $s_inner_flag = false;
  if (isset($_GET['zID']) && !empty($_GET['zID']) ) {
    $s_inner_flag = true;
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
        <div class="maincell<?php if(!$cMETA->is_top_level()) echo ' wider';?>">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
            <div>
<?php
  echo '<h1>';
  echo HEADING_TITLE; 
  if( $s_inner_flag ) {
    echo '&nbsp;&raquo;&nbsp;' . $meta_zone['meta_types_name'];
  }
  echo '</h1>';
?>
            </div>
          </div>
<?php
  echo $cMETA->display_html();
?>
        </div>
<?php
  echo $cMETA->display_right_box();
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
