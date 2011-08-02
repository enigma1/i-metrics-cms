<?php
/*
Came from:
  $Id: index.php,v 1.19 2003/06/27 09:38:31 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 osCommerce

// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Home Page
//----------------------------------------------------------------------------
// Converted for the CMS
// Removed register global dependencies
// Added compatibility for PHP4,5
// Enhanced navigation by using jscripts
// Added common HTML sections
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  require('includes/application_top.php');
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
      <div id="header">
        <div class="logo" style="height: 38px;">
          <div class="floater" style="padding: 12px 0px 0px 20px;"><?php echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_IMAGES . 'design/logo.png', STORE_NAME) . '</a>'; ?></div>
          <div class="floatend" style="padding: 10px 10px 0px 0px;"><h1><?php echo HEADING_MANAGE_SITE; ?></h1></div>
        </div>
      </div>
<?php
  $messageStack->output('header');
?>
      <div id="lefthomepane">
        <div style="padding: 8px;">
<?php
  $heading = array();
  $contents = array();

  if( DEFAULT_WARNING_PASSWORD_PROTECT_REMIND == 'true' ) {
    $cfq_query = $g_db->query("select configuration_id from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_WARNING_PASSWORD_PROTECT_REMIND'");
    $cfg_array = $g_db->fetch_array($cfq_query);
    $warning_string = '<a href="' . tep_href_link(FILENAME_CONFIGURATION, 'action=edit&cID=' . $cfg_array['configuration_id']) . '"><b style="color: #FF0000">' . WARNING_PASSWORD_PROTECT_REMIND . '</b></a>';

    $contents[] = array(
     'text' => tep_image(DIR_WS_ICONS . 'icon_restrict.png', ICON_UNLOCKED, '', '', 'class="floatend rpad"') . $warning_string
    );
  }

  if( !empty($contents) ) {
    $heading[] = array(
      'text'  => BOX_HEADING_REMINDERS,
    );
    $box = new box;
    echo $box->menuBox($heading, $contents, 'class="altBoxHeading"');
    echo '<div class="vspacer"></div>' . "\n";
  }

  $heading = array();
  $contents = array();

  $heading[] = array(
    'text'  => BOX_HEADING_TOP,
    'link'  => tep_href_link()
  );

  $contents[] = array('text'  => '<a href="http://demos.asymmetrics.com" target="_blank">' . BOX_ENTRY_SUPPORT_SITE . '</a>');
  $contents[] = array('text'  => '<a href="http://demos.asymmetrics.com" target="_blank">' . BOX_ENTRY_DOCUMENTATION . '</a>');
  $contents[] = array('text'  => '<a href="http://demos.asymmetrics.com" target="_blank">' . BOX_ENTRY_FORUMS . '</a>');
  $contents[] = array('text'  => '<a href="http://demos.asymmetrics.com" target="_blank">' . BOX_ENTRY_MODULES . '</a>');


  $box = new box;
  echo $box->menuBox($heading, $contents, 'class="altBoxHeading"');

  echo '<div class="vspacer"></div>' . "\n";


  $total_array = array();
  $contents = array();
  $heading = array();
  $heading[] = array(
    'text'  => BOX_HEADING_CONTENT,
  );

  $entries_query = $g_db->query("select count(*) as total from " . TABLE_GTEXT);
  $entriee_array = $g_db->fetch_array($entries_query);
  $total_array[] = array(
    'text' => BOX_ENTRY_TOTAL_PAGES,
    'count' => $entriee_array['total'],
  );

  $entries_query = $g_db->query("select count(*) as total from " . TABLE_GTEXT . " where sub='0'");
  $entriee_array = $g_db->fetch_array($entries_query);

  $total_array[] = array(
    'text' => BOX_ENTRY_FRONT_PAGES,
    'count' => $entriee_array['total'],
  );

  require_once(DIR_FS_CLASSES . FILENAME_ABSTRACT_ZONES);
  $cAbstract = new abstract_zones();
  $types_array = $cAbstract->get_types();
  for($i=0, $j=count($types_array); $i<$j; $i++) {
    $entries_query = $g_db->query("select count(*) as total from " . TABLE_ABSTRACT_ZONES . " where abstract_types_id='" . (int)$types_array[$i]['abstract_types_id'] . "'");
    $entriee_array = $g_db->fetch_array($entries_query);
    $total_array[] = array(
      'text' => $types_array[$i]['abstract_types_name'],
      'count' => $entriee_array['total'],
    );
  }

  $entries_query = $g_db->query("select count(*) as total from " . TABLE_CUSTOMERS);
  $entriee_array = $g_db->fetch_array($entries_query);

  $total_array[] = array(
    'text' => BOX_ENTRY_CUSTOMERS,
    'count' => $entriee_array['total'],
  );

  for($i=0, $j=count($total_array); $i<$j; $i++) {
    $contents[] = array(
      'text'  => '<span><b>' . $total_array[$i]['count'] . '</b>&nbsp;' . $total_array[$i]['text'] . '</span>'
    );
  }

  $box = new box;
  echo $box->menuBox($heading, $contents, 'class="altBoxHeading"');
  echo '<div class="vspacer"></div>' . "\n";

  $plugin_contents = array();
  $args = array(
    'contents' => &$plugin_contents
  );
  $g_plugins->invoke('html_home_side', $plugin_contents);
  if( !empty($plugin_contents) ) {
    $heading[] = array(
      'text'  => BOX_HEADING_PLUGIN_NOTICES,
    );
    $box = new box;
    echo $box->menuBox($heading, $plugin_contents, 'class="altBoxHeading"');
    echo '<div class="vspacer"></div>' . "\n";
  }

  $heading = array();
  $contents = array();

  if (getenv('HTTPS') == 'on') {
    $size = ((getenv('SSL_CIPHER_ALGKEYSIZE')) ? getenv('SSL_CIPHER_ALGKEYSIZE') . '-bit' : '<i>' . BOX_CONNECTION_UNKNOWN . '</i>');
    $contents[] = array(
      'text' => tep_image(DIR_WS_ICONS . 'locked.gif', ICON_LOCKED, '', '', 'class="floatend rpad"') . sprintf(BOX_CONNECTION_PROTECTED, $size)
    );
  } else {
    $contents[] = array(
      'text' => '<div class="linepad">' . tep_image(DIR_WS_ICONS . 'unlocked.gif', ICON_UNLOCKED, '', '', 'class="floatend"') . BOX_CONNECTION_UNPROTECTED . '</div>'
    );
  }

  $heading[] = array(
    'text'  => BOX_HEADING_WARNINGS,
  );

  $box = new box;
  echo $box->menuBox($heading, $contents, 'class="altBoxHeading"');
?>
        </div>
      </div>
      <div id="mainhomepane">
        <div class="maincell wider">
          <div id="top_level" style="min-height: 610px;">
<?php
  require(DIR_FS_MODULES . 'index_main.php');
?>
          </div>
        </div>
      </div>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
