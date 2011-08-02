<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Index Languages Level Module
//----------------------------------------------------------------------------
// I-Metrics CMS
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
  $entries_array = array();

  $entries_array[] = array(
    'id' => 'index_main',
    'sub' => 'index_main',
    'title' => TEXT_INFO_BACK,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/root.png', TEXT_INFO_BACK),
    'href' => tep_href_link(),
  );

  $entries_array[] = array(
    'id' => 'plugins',
    'title' => TEXT_INFO_PLUGINS,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/plugins.png', TEXT_INFO_PLUGINS),
    'href' => tep_href_link(FILENAME_PLUGINS, 'selected_box=plugins_box'),
  );
  $system_start_count = count($entries_array);

  // Amend plugin options
  extract(tep_load('plugins_admin'));
  $plugin_contents = array();
  $args = array('entries_array' => &$entries_array);
  $cPlug->invoke('html_home_plugins', $args);
  $system_end_count = count($entries_array);
/*
  $entries_array[] = array(
    'id' => 'remote',
    'title' => TEXT_INFO_TOOLS_UPDATES,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/remote.png', TEXT_INFO_TOOLS_UPDATES),
    'href' => tep_href_link(FILENAME_CONNECTOR, 'selected_box=tools_box'),
  );
*/
?>
            <div id="index_plugins">
<?php
  require(DIR_FS_MODULES . 'common_index.php'); 
?>
            </div>
