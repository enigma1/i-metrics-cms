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
  require(DIR_WS_CLASSES . FILENAME_SEO_ZONES);

  unset($seozone_script);
// initialize the abstract zone class for different type support
  if( isset($_GET['zID']) && !empty($_GET['zID']) ) {
    $seozone_query = $g_db->query("select seo_types_class, seo_types_name from " . TABLE_SEO_TYPES . " where seo_types_id = '" . (int)$_GET['zID'] . "'");
    if( $seozone = $g_db->fetch_array($seozone_query) ) {
      $seozone_script = $seozone['seo_types_class'];
    }
  }

  if( isset($seozone_script) && !empty($seozone_script) ) {
    require(DIR_WS_CLASSES . $seozone_script . '.php');
  } else {
    $seozone_script = 'seo_zones';
  }

  $cSEO = new $seozone_script();
  $cSEO->process_saction();
  $cSEO->process_action();

  $s_inner_flag = false;
  if (isset($_GET['zID']) && tep_not_null($_GET['zID']) ) {
    $s_inner_flag = true;
  }
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php require('includes/objects/html_start_sub2.php'); ?>
        <div class="maincell"<?php if(!$cSEO->is_top_level()) echo ' style="width:100%;"';?>>
          <div class="comboHeading">
            <div class="pageHeading">
<?php
  echo '<h1>';
  echo HEADING_TITLE; 
  if( $s_inner_flag ) {
    echo '&nbsp;&raquo;&nbsp;' . $seozone['seo_types_name'];
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
<?php require('includes/objects/html_end.php'); ?>