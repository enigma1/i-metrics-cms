<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Comments system invoke script
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
  class admin_comments_system extends plugins_base {

    // Compatibility constructor
    function admin_comments_system() {
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      $options = $this->load_options();

      require_once($this->admin_path . 'back/admin_tables.php');
      require_once($this->admin_path . 'back/admin_files.php');
      require_once($this->admin_path . 'back/admin_strings.php');
    }

    function abstract_box() {
      global $contents;
      if( isset($contents) && !empty($contents) ) {
        $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_COMMENTS, 'selected_box=abstract_config') . '">' . BOX_COMMENTS . '</a>');
        return true;
      }
      return false;
    }
  }
?>
