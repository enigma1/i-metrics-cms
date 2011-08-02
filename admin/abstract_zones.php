<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Abstract Zones Controller
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Controls relationships among different types, invokes selected class
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
  require_once(DIR_FS_CLASSES . FILENAME_ABSTRACT_ZONES);

  // initialize the abstract zone class for the different types supported
  if( isset($_GET['zID']) && tep_not_null($_GET['zID']) ) {
    $azone_query = $g_db->query("select at.abstract_types_class from " . TABLE_ABSTRACT_ZONES . " az left join " . TABLE_ABSTRACT_TYPES . " at on (az.abstract_types_id=at.abstract_types_id) where az.abstract_zone_id = '" . (int)$_GET['zID'] . "'");
    if( $azone = $g_db->fetch_array($azone_query) ) {
      $azone_script = $azone['abstract_types_class'];
    }
  }

  if( isset($azone_script) && tep_not_null($azone_script) ) {
    require_once(DIR_FS_CLASSES . $azone_script . '.php');
  } else {
    $azone_script = 'abstract_zones';
  }

  $cAbstract = new $azone_script();
  $cAbstract->initialize();
  $cAbstract->process_saction();
  $cAbstract->process_action();
  $s_inner_flag = false;
  if (isset($_GET['zID']) && tep_not_null($_GET['zID']) ) {
    $s_inner_flag = true;
  }

  if( !empty($_POST) ) {
    $g_db->query("truncate table " . TABLE_SEO_CACHE);
  }
?>
<?php require(DIR_FS_INCLUDES . 'objects/html_start_sub1.php'); ?>
<?php
  $cAbstract->emit_scripts();
  $set_focus = true;
  require(DIR_FS_INCLUDES . 'objects/html_start_sub2.php');
?> 
        <div class="maincell<?php if(!$cAbstract->is_top_level()) echo ' wider'; ?>">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
            <div>
<?php
  if( $s_inner_flag ) {
    $title = $cAbstract->get_zone_type($_GET['zID']) . '&nbsp;&raquo;&nbsp;' . $cAbstract->get_zone_name($_GET['zID']);
  } else {
    $title = HEADING_TITLE;
  }
  echo '<h1>' . $title . '</h1>';
  if( empty($_POST) ) {
    $g_plugins->invoke('add_current_page', $title, tep_get_all_get_params());
  }
?>
            </div>
          </div>
<?php
  echo $cAbstract->display_html();
?>
        </div>
<?php 
  echo $cAbstract->display_right_box(); 
?>
<?php require(DIR_FS_INCLUDES . 'objects/html_end.php'); ?>
