<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
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
  $entries_array = array();

  $entries_array[] = array(
    'id' => 'index_main',
    'sub' => 'index_main',
    'title' => TEXT_INFO_BACK,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/root.png', TEXT_INFO_BACK),
    'href' => tep_href_link(),
  );

  $entries_array[] = array(
    'id' => 'reports',
    'title' => TEXT_INFO_MARKETING_REPORTS,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/reports.png', TEXT_INFO_MARKETING_REPORTS),
    'href' => tep_href_link(FILENAME_SEO_REPORTS, 'selected_box=seog_box'),
  );

  $entries_array[] = array(
    'id' => 'plugins',
    'title' => TEXT_INFO_MARKETING_CONTROL,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/plugins.png', TEXT_INFO_MARKETING_CONTROL),
    'href' => tep_href_link(FILENAME_SEO_ZONES, 'selected_box=seog_box'),
  );

  $entries_array[] = array(
    'id' => 'marketing',
    'title' => TEXT_INFO_MARKETING_META_CONTROL,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/marketing.png', TEXT_INFO_MARKETING_META_CONTROL),
    'href' => tep_href_link(FILENAME_META_ZONES, 'selected_box=metag_box'),
  );

  $entries_array[] = array(
    'id' => 'blender',
    'title' => TEXT_INFO_MARKETING_TYPES,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/blender.png', TEXT_INFO_MARKETING_TYPES),
    'href' => tep_href_link(FILENAME_SEO_TYPES, 'selected_box=seog_box'),
  );

  $entries_array[] = array(
    'id' => 'types',
    'title' => TEXT_INFO_MARKETING_META_TYPES,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/types.png', TEXT_INFO_MARKETING_META_TYPES),
    'href' => tep_href_link(FILENAME_META_TYPES, 'selected_box=metag_box'),
  );

  $entries_array[] = array(
    'id' => 'slabflag',
    'title' => TEXT_INFO_MARKETING_EXCLUDE,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/slabflag.png', TEXT_INFO_MARKETING_EXCLUDE),
    'href' => tep_href_link(FILENAME_SEO_EXCLUDE, 'selected_box=seog_box'),
  );

  $entries_array[] = array(
    'id' => 'words',
    'title' => TEXT_INFO_MARKETING_LEXICO,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/words.png', TEXT_INFO_MARKETING_LEXICO),
    'href' => tep_href_link(FILENAME_META_LEXICO, 'selected_box=metag_box'),
  );

  $entries_array[] = array(
    'id' => 'flags',
    'title' => TEXT_INFO_MARKETING_META_EXCLUDE,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/exclude.png', TEXT_INFO_MARKETING_META_EXCLUDE),
    'href' => tep_href_link(FILENAME_META_EXCLUDE, 'selected_box=metag_box'),
  );

  $entries_array[] = array(
    'id' => 'switching',
    'title' => TEXT_INFO_MARKETING_REDIRECT,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/switching.png', TEXT_INFO_MARKETING_REDIRECT),
    'href' => tep_href_link(FILENAME_SEO_REDIRECTS, 'selected_box=seog_box'),
  );

  $entries_array[] = array(
    'id' => 'configuration2',
    'title' => TEXT_INFO_MARKETING_CONFIG,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/configuration.png', TEXT_INFO_MARKETING_CONFIG),
    'href' => tep_href_link(FILENAME_SEO_ZONES_CONFIG, 'selected_box=seog_box'),
  );

  $entries_array[] = array(
    'id' => 'configuration',
    'title' => TEXT_INFO_MARKETING_META_CONFIG,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/configure_global.png', TEXT_INFO_MARKETING_META_CONFIG),
    'href' => tep_href_link(FILENAME_META_ZONES_CONFIG, 'selected_box=metag_box'),
  );
  $system_start_count = count($entries_array);

  // Amend plugin options
  extract(tep_load('plugins_admin'));
  $plugin_contents = array();
  $args = array('entries_array' => &$entries_array);
  $cPlug->invoke('html_home_marketing', $args);
  $system_end_count = count($entries_array);
?>
            <div id="index_marketing">
<?php
  require(DIR_FS_MODULES . 'common_index.php'); 
?>
            </div>
