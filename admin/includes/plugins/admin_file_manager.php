<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: File Manager
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
  class admin_file_manager extends system_base {
    // Compatibility constructor
    function admin_file_manager() {}

    function init_late() {
      // Filter script parameters
      $this->set_get_array('action', 'filename', 'info', 'goto');
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

    function get_file_permissions_string($mode) {
      // determine type
      if ( ($mode & 0xC000) == 0xC000) { // unix domain socket
        $type = 's';
      } elseif ( ($mode & 0x4000) == 0x4000) { // directory
        $type = 'd';
      } elseif ( ($mode & 0xA000) == 0xA000) { // symbolic link
        $type = 'l';
      } elseif ( ($mode & 0x8000) == 0x8000) { // regular file
        $type = '-';
      } elseif ( ($mode & 0x6000) == 0x6000) { //bBlock special file
        $type = 'b';
      } elseif ( ($mode & 0x2000) == 0x2000) { // character special file
        $type = 'c';
      } elseif ( ($mode & 0x1000) == 0x1000) { // named pipe
        $type = 'p';
      } else { // unknown
        $type = '?';
      }

      // determine permissions
      $owner['read']    = ($mode & 00400) ? 'r' : '-';
      $owner['write']   = ($mode & 00200) ? 'w' : '-';
      $owner['execute'] = ($mode & 00100) ? 'x' : '-';
      $group['read']    = ($mode & 00040) ? 'r' : '-';
      $group['write']   = ($mode & 00020) ? 'w' : '-';
      $group['execute'] = ($mode & 00010) ? 'x' : '-';
      $world['read']    = ($mode & 00004) ? 'r' : '-';
      $world['write']   = ($mode & 00002) ? 'w' : '-';
      $world['execute'] = ($mode & 00001) ? 'x' : '-';

      // adjust for SUID, SGID and sticky bit
      if ($mode & 0x800 ) $owner['execute'] = ($owner['execute'] == 'x') ? 's' : 'S';
      if ($mode & 0x400 ) $group['execute'] = ($group['execute'] == 'x') ? 's' : 'S';
      if ($mode & 0x200 ) $world['execute'] = ($world['execute'] == 'x') ? 't' : 'T';

      return $type .
             $owner['read'] . $owner['write'] . $owner['execute'] .
             $group['read'] . $group['write'] . $group['execute'] .
             $world['read'] . $world['write'] . $world['execute'];

    }
  }
?>
