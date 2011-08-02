<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Index Content Level Module
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
  $level = 'content_level';
  $entries_array = array();

  $entries_array[] = array(
    'id' => 'index_main',
    'sub' => 'index_main',
    'title' => TEXT_INFO_BACK,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/root.png', TEXT_INFO_BACK),
    'href' => tep_href_link(),
  );

  $entries_array[] = array(
    'id' => 'configuration2',
    'title' => TEXT_INFO_CFG_STORE,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/configuration.png', TEXT_INFO_CFG_STORE),
    'href' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration_box'),
  );

  $entries_array[] = array(
    'id' => 'configuration_collections',
    'title' => TEXT_INFO_COLLECTIONS_CONFIG,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/configure.png', TEXT_INFO_COLLECTIONS_CONFIG),
    'href' => tep_href_link(FILENAME_ABSTRACT_ZONES_CONFIG, 'selected_box=abstract_box'),
  );

  $entries_array[] = array(
    'id' => 'blender',
    'title' => TEXT_INFO_CFG_CACHE,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/blender.png', TEXT_INFO_CFG_CACHE),
    'href' => tep_href_link(FILENAME_CACHE_CONFIG, 'selected_box=cache_box'),
  );

  $entries_array[] = array(
    'id' => 'helpdesk',
    'title' => TEXT_INFO_CFG_HELPDESK,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/configure_helpdesk.png', TEXT_INFO_CFG_HELPDESK),
    'href' => tep_href_link(FILENAME_HELPDESK_CONFIG, 'selected_box=helpdesk_box'),
  );

  $entries_array[] = array(
    'id' => 'configuration',
    'title' => TEXT_INFO_CFG_SEO, 
    'image' => tep_image(DIR_WS_IMAGES . 'categories/configure_global.png', TEXT_INFO_CFG_SEO),
    'href' => tep_href_link(FILENAME_SEO_ZONES_CONFIG, 'selected_box=seog_box'),
  );

  $entries_array[] = array(
    'id' => 'plugins',
    'title' => TEXT_INFO_CFG_META, 
    'image' => tep_image(DIR_WS_IMAGES . 'categories/plugins.png', TEXT_INFO_CFG_META),
    'href' => tep_href_link(FILENAME_META_ZONES_CONFIG, 'selected_box=metag_box'),
  );
  $system_start_count = count($entries_array);
  // Amend plugin options
  extract(tep_load('plugins_admin'));
  $plugin_contents = array();
  $args = array('entries_array' => &$entries_array);
  $cPlug->invoke('html_home_configuration', $args);
  $system_end_count = count($entries_array);
?>
            <div id="index_configuration">
<?php 
  require(DIR_FS_MODULES . 'common_index.php'); 
?>
            </div>
