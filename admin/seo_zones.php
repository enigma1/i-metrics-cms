<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: SEO-G Controller
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Generates url names from parameters
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
  require(DIR_FS_CLASSES . FILENAME_SEO_ZONES);

  $zone_script = 'seo_zones';
  $zID = isset($_GET['zID'])?(int)$_GET['zID']:'';

  // initialize the abstract zone class for different type support
  $zone_query = $g_db->query("select seo_types_class, seo_types_name from " . TABLE_SEO_TYPES . " where seo_types_id = '" . (int)$zID . "'");
  if( $g_db->num_rows($zone_query) ) {
    $zone_array = $g_db->fetch_array($zone_query);
    $zone_script = $zone_array['seo_types_class'];
    require(DIR_FS_CLASSES . $zone_script . '.php');
  }

  $cSEO = new $zone_script();
  $cSEO->process_saction();
  $cSEO->process_action();
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
        <div class="maincell"<?php if(!$cSEO->is_top_level()) echo ' style="width:100%;"';?>>
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&zID=' . $zID) . '" class="' . tep_get_script_name() . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', BOX_OTHER_QUICK_HELP) . '</a>'; ?></div>
            <div class="floater">
<?php
  echo '<h1>';
  echo HEADING_TITLE; 
  if( !empty($zID) ) {
    echo '&nbsp;&raquo;&nbsp;' . $zone_array['seo_types_name'];
  }
  echo '</h1>';
?>
            </div>
          </div>
<?php
  echo $cSEO->display_html();
?>
        </div>
<?php
  echo $cSEO->display_right_box(); 
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>