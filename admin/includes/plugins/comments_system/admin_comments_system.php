<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Comments system runtime script
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
      $this->box = 'abstract_box';
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      $options = $this->load_options();

      tep_define_vars($this->admin_path . 'back/admin_defs.php');
      $this->strings = tep_get_strings($this->admin_path . 'back/admin_strings.php');

      $this->scripts_array = array(
        FILENAME_COMMENTS,
        FILENAME_DEFAULT
      );
    }

    function abstract_box() {
      extract(tep_ref('contents'), EXTR_OVERWRITE|EXTR_REFS);
      $cStrings =& $this->strings;
      $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_COMMENTS, 'selected_box=' . $this->box) . '">' . $cStrings->BOX_COMMENTS . '</a>');
      return true;
    }

    function ajax_start() {
      extract(tep_load('defs', 'sessions'));

      $result = false;
      if( !$this->check_scripts($this->scripts_array) || $cDefs->action != 'help' ) {
        return $result;
      }

      $result = $this->get_help();
      return $result;
    }

    function html_start() {
      extract(tep_load('defs'));

      if( !$this->check_scripts() ) {
        return false;
      }
      tep_set_lightbox();
      return true;
    }

    function html_end() {
      extract(tep_load('defs'));

      if( !$this->check_scripts() ) return false;

      $contents = '';
      $launcher = $this->admin_path . 'back/launcher.tpl';
      $result = tep_read_contents($launcher, $contents);
      if( !$result ) return false;

      $contents_array = array(
        'POPUP_TITLE' => '',
        'POPUP_SELECTOR' => 'div.help_page a.plugins_help',
      );
      $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);
      return true;
    }

    function html_home_plugins() {
      extract(tep_ref('entries_array'), EXTR_OVERWRITE|EXTR_REFS);
      return $this->common_home($entries_array);
    }
    function html_home_collections() {
      extract(tep_ref('entries_array'), EXTR_OVERWRITE|EXTR_REFS);
      return $this->common_home($entries_array);
    }

    function common_home(&$entries_array) {
      $cStrings =& $this->strings;

      $entries_array[] = array(
        'id' => $this->key,
        'title' => $cStrings->TEXT_INFO_COMMENTS2,
        'image' => tep_image($this->admin_web_path . 'comments.png', $cStrings->TEXT_INFO_COMMENTS2),
        'href' => tep_href_link(FILENAME_COMMENTS, 'selected_box=' . $this->box),
      );
      return true;
    }
  }
?>
