<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Index Tools Level Module
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
/*
  $entries_array[] = array(
    'id' => 'remote',
    'title' => TEXT_INFO_TOOLS_UPDATES,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/remote.png', TEXT_INFO_TOOLS_UPDATES),
    'href' => tep_href_link(FILENAME_CONNECTOR, 'selected_box=tools_box'),
  );
*/
  $entries_array[] = array(
    'id' => 'people',
    'title' => TEXT_INFO_TOOLS_ONLINE,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/people.png', TEXT_INFO_TOOLS_ONLINE),
    'href' => tep_href_link(FILENAME_WHOS_ONLINE, 'selected_box=tools_box'),
  );

  $entries_array[] = array(
    'id' => 'backup',
    'title' => TEXT_INFO_TOOLS_BACKUP,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/download.png', TEXT_INFO_TOOLS_BACKUP),
    'href' => tep_href_link(FILENAME_BACKUP, 'selected_box=tools_box'),
  );

  $entries_array[] = array(
    'id' => 'blender',
    'title' => TEXT_INFO_TOOLS_TEMPLATES,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/blender.png', TEXT_INFO_TOOLS_TEMPLATES),
    'href' => tep_href_link(FILENAME_TEMPLATES, 'selected_box=tools_box'),
  );

  $entries_array[] = array(
    'id' => 'flags',
    'title' => TEXT_INFO_TOOLS_SITES,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/slabflag.png', TEXT_INFO_TOOLS_SITES),
    'href' => tep_href_link(FILENAME_MULTI_SITES, 'selected_box=tools_box'),
  );

  $entries_array[] = array(
    'id' => 'files',
    'title' => TEXT_INFO_TOOLS_FILES,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/files.png', TEXT_INFO_TOOLS_FILES),
    'href' => tep_href_link(FILENAME_FILE_MANAGER, 'selected_box=tools_box'),
  );

  $entries_array[] = array(
    'id' => 'database',
    'title' => TEXT_INFO_TOOLS_DB_QUERIES,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/database.png', TEXT_INFO_TOOLS_DB_QUERIES),
    'href' => tep_href_link(FILENAME_EXPLAIN_QUERIES, 'selected_box=tools_box'),
  );

  $entries_array[] = array(
    'id' => 'configuration',
    'title' => TEXT_INFO_TOOLS_CONFIGURATION,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/configuration.png', TEXT_INFO_TOOLS_CONFIGURATION),
    'href' => tep_href_link(FILENAME_TOTAL_CONFIGURATION, 'selected_box=tools_box'),
  );

  $entries_array[] = array(
    'id' => 'server',
    'title' => TEXT_INFO_TOOLS_SERVER,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/server.png', TEXT_INFO_TOOLS_SERVER),
    'href' => tep_href_link(FILENAME_SERVER_INFO, 'selected_box=tools_box'),
  );
  $system_start_count = count($entries_array);

  // Amend plugin options
  extract(tep_load('plugins_admin'));
  $args = array('entries_array' => &$entries_array);
  $cPlug->invoke('html_home_tools', $args);
  $system_end_count = count($entries_array);
?>
            <div id="index_tools">
<?php
  require(DIR_FS_MODULES . 'common_index.php'); 
?>
            </div>
