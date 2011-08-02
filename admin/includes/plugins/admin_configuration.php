<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
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
  class admin_configuration extends system_base {
    // Compatibility constructor
    function admin_configuration() {}

    function init_late() {
      // Filter script parameters
      $this->set_get_array('action', 'cID', 'gID');
    }

    function html_start() {
      extract(tep_load('defs'));
      // Load side resource files
      tep_set_lightbox();
      return true;
    }

    function html_end() {
      extract(tep_load('defs', 'database'));

      $contents = '';
      $launcher = DIR_FS_PLUGINS . 'common_help.tpl';
      $result = tep_read_contents($launcher, $contents);
      if( !$result ) return false;

      $title = HEADING_HELP_TITLE;
      $gID = (isset($_GET['gID']) ? (int)$_GET['gID'] : 0);
      $group_query = $db->query("select configuration_group_title from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$gID . "'");
      if( $db->num_rows($group_query) ) {
        $group_array = $db->fetch_array($group_query);
        $title .= ' - ' . $group_array['configuration_group_title'];
      }
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
