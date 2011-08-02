<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Configuration script
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
  class admin_languages extends system_base {
    // Compatibility constructor
    function admin_languages() {}

    function init_late() {
      // Filter script parameters
      $this->set_get_array('action', 'lID', 'page');
    }

    function html_start() {
      extract(tep_load('defs'));
      // Load side resource files
      tep_set_lightbox();
      return true;
    }

    function html_end() {
      extract(tep_load('defs', 'database'));

      // Setup help script - default js help is loaded by system_base
      $script_name = tep_get_script_name();
      $contents = '';
      $launcher = DIR_FS_PLUGINS . 'common_help.tpl';
      $result = tep_read_contents($launcher, $contents);
      if( !$result ) return false;

      $title = $this->get_system_help_title('list');
      $contents_array = array(
        'POPUP_TITLE' => $title,
        'POPUP_SELECTOR' => 'div.help_page a.heading_help',
      );
      // process js template
      $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);
      return true;
    }
  }
?>
