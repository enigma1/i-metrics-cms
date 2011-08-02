<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Stub/Default Plugin for scripts
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
  class admin_stub extends system_base {
    // Compatibility constructor
    function admin_stub() {}

    function html_start() {
      // Load side resource files
      tep_set_lightbox();
      return true;
    }

    function html_end() {
      extract(tep_load('defs'));

      // Setup help script - default js help is loaded by system_base
      $contents = '';
      $launcher = DIR_FS_PLUGINS . 'common_help.tpl';
      $result = tep_read_contents($launcher, $contents);
      if( !$result ) return false;

      $contents_array = array(
        'POPUP_TITLE' => '',
        'POPUP_SELECTOR' => 'div.help_page a.heading_help',
      );
      // process js template
      $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);
      return true;
    }
  }
?>
