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
  $level = 'main_level';
  $entries_array = array();

  $entries_array[] = array(
    'id' => 'text_content',
    'sub' => 'index_content',
    'title' => TEXT_INFO_TEXT, 
    'image' => tep_image(DIR_WS_IMAGES . 'categories/zones.png', TEXT_INFO_TEXT),
    'href' => tep_href_link(FILENAME_GENERIC_TEXT, 'action=new_generic_text&selected_box=abstract_box'),
    'children' => array(
      array('title' => BOX_ABSTRACT_GENERIC_TEXT, 'link' => tep_href_link(FILENAME_GENERIC_TEXT, 'selected_box=abstract_box')),
      array('title' => BOX_TITLE_GROUP_PAGES, 'link' => tep_href_link(FILENAME_ABSTRACT_ZONES, 'selected_box=abstract_box')),
      array('title' => BOX_ABSTRACT_CONFIG, 'link' => tep_href_link(FILENAME_ABSTRACT_ZONES_CONFIG, 'selected_box=abstract_box')),
    ),
  );

  $entries_array[] = array(
    'id' => 'plugins',
    'sub' => 'index_plugins',
    'title' => TEXT_INFO_PLUGINS, 
    'image' => tep_image(DIR_WS_IMAGES . 'categories/plugins.png', TEXT_INFO_PLUGINS),
    'href' => tep_href_link(FILENAME_PLUGINS, 'selected_box=plugins'),
    'children' => array(
      array('title' => BOX_TOOLS_PLUGINS, 'link' => tep_href_link(FILENAME_PLUGINS, 'selected_box=tools_box')),
      array('title' => BOX_TOOLS_BACKUP, 'link' => tep_href_link(FILENAME_BACKUP, 'selected_box=tools_box')),
      array('title' => BOX_TOOLS_MULTI_SITES, 'link' => tep_href_link(FILENAME_MULTI_SITES, 'selected_box=tools_box')),
      array('title' => TOOLS_WHOS_ONLINE, 'link' => tep_href_link(FILENAME_WHOS_ONLINE, 'selected_box=tools_box')),
      array('title' => BOX_TOOLS_TOTAL_CONFIGURATION, 'link' => tep_href_link(FILENAME_TOTAL_CONFIGURATION, 'selected_box=tools_box')),
    ),
  );

  $entries_array[] = array(
    'id' => 'helpdesk',
    'sub' => 'index_helpdesk',
    'title' => BOX_HEADING_HELPDESK,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/helpdesk.png', BOX_HEADING_HELPDESK),
    'href' => tep_href_link(FILENAME_HELPDESK, 'selected_box=helpdesk_box'),
  );

  $entries_array[] = array(
    'id' => 'configuration',
    'sub' => 'index_configuration',
    'title' => BOX_HEADING_CONFIGURATION,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/configure_global.png', BOX_HEADING_CONFIGURATION),
    'href' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration_box'),
    'children' => array(
      array('title' => BOX_CONFIGURATION_MYSTORE, 'link' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=1')),
      array('title' => BOX_CONFIGURATION_LOGGING, 'link' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=10')),
    ),
  );

  $entries_array[] = array(
    'id' => 'marketing',
    'sub' => 'index_marketing',
    'title' => BOX_HEADING_MARKETING, 
    'image' => tep_image(DIR_WS_IMAGES . 'categories/marketing.png', BOX_HEADING_MARKETING),
    'href' => tep_href_link(FILENAME_SEO_ZONES, 'selected_box=seog_box'),
    'children' => array(
      array('title' => BOX_SEO_ZONES, 'link' => tep_href_link(FILENAME_SEO_ZONES, 'selected_box=seog_box')),
      array('title' => BOX_META_ZONES, 'link' => tep_href_link(FILENAME_META_ZONES, 'selected_box=metag_box')),
    ),
  );

  $entries_array[] = array(
    'id' => 'blender',
    'sub' => 'index_cache',
    'title' => BOX_HEADING_CACHE,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/blender.png', BOX_HEADING_CACHE),
    'href' => tep_href_link(FILENAME_CACHE_REPORTS, 'selected_box=cache_box'),
    'children' => array(
      array('title' => BOX_CACHE_CONFIG, 'link' => tep_href_link(FILENAME_CACHE_CONFIG, 'selected_box=cache_box')),
      array('title' => BOX_CACHE_HTML, 'link' => tep_href_link(FILENAME_CACHE_HTML, 'selected_box=cache_box')),
    ),
  );

  $entries_array[] = array(
    'id' => 'items',
    'sub' => 'index_tools',
    'title' => BOX_HEADING_TOOLS, 
    'image' => tep_image(DIR_WS_IMAGES . 'categories/items.png', BOX_HEADING_TOOLS),
    'href' => tep_href_link(FILENAME_WHOS_ONLINE, 'selected_box=tools_box'),
    'children' => array(
      array('title' => BOX_TOOLS_PLUGINS, 'link' => tep_href_link(FILENAME_PLUGINS, 'selected_box=tools_box')),
      array('title' => BOX_TOOLS_BACKUP, 'link' => tep_href_link(FILENAME_BACKUP, 'selected_box=tools_box')),
      array('title' => BOX_TOOLS_MULTI_SITES, 'link' => tep_href_link(FILENAME_MULTI_SITES, 'selected_box=tools_box')),
      array('title' => TOOLS_WHOS_ONLINE, 'link' => tep_href_link(FILENAME_WHOS_ONLINE, 'selected_box=tools_box')),
      array('title' => BOX_TOOLS_TOTAL_CONFIGURATION, 'link' => tep_href_link(FILENAME_TOTAL_CONFIGURATION, 'selected_box=tools_box')),
    ),
  );

  $entries_array[] = array(
    'id' => 'flags',
    'sub' => 'index_languages',
    'title' => BOX_HEADING_LANGUAGES,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/exclude.png', BOX_HEADING_LANGUAGES),
    'href' => tep_href_link(FILENAME_LANGUAGES, 'selected_box=language_box'),
  );
  $system_start_count = count($entries_array);

  // Amend plugin options
  extract(tep_load('plugins_admin'));
  $args = array('entries_array' => &$entries_array);
  $cPlug->invoke('html_home_main', $args);
  $system_end_count = count($entries_array);
?>
            <div id="index_main">
<?php
  require(DIR_FS_MODULES . 'common_index.php'); 
?>
            </div>
