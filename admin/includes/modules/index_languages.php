<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
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
    'id' => 'flags',
    'title' => TEXT_INFO_LANGUAGES,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/exclude.png', TEXT_INFO_LANGUAGES),
    'href' => tep_href_link(FILENAME_LANGUAGES, 'selected_box=language_box'),
  );

  $entries_array[] = array(
    'id' => 'blender',
    'title' => TEXT_INFO_LANGUAGES_SYNC,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/blender.png', TEXT_INFO_LANGUAGES_SYNC),
    'href' => tep_href_link(FILENAME_LANGUAGES_SYNC, 'selected_box=language_box'),
  );
  $system_start_count = count($entries_array);

  // Amend plugin options
  extract(tep_load('plugins_admin'));
  $plugin_contents = array();
  $args = array('entries_array' => &$entries_array);
  $cPlug->invoke('html_home_languages', $args);
  $system_end_count = count($entries_array);
?>
            <div id="index_languages">
<?php
  require(DIR_FS_MODULES . 'common_index.php'); 
?>
            </div>
