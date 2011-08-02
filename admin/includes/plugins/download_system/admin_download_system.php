<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Download System invoke script
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
  class admin_download_system extends plugins_base {

    // Compatibility constructor
    function admin_download_system() {

      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      $options = $this->load_options();

      tep_define_vars($this->admin_path . 'back/admin_defs.php');
      $this->strings = tep_get_strings($this->admin_path . 'back/admin_strings.php');
      $this->main_script = FILENAME_DOWNLOAD;
      $this->scripts_array[] = $this->main_script;
    }

    function help_link() {
      extract(tep_ref('key', 'action', 'link'), EXTR_OVERWRITE|EXTR_REFS);

      $result = false;
      if( $key != $this->key ) return $result;

      $cStrings =& $this->strings;

      switch($action) {
        case 'set_options':
          $link = '<a href="' . tep_href_link($this->main_script, 'action=help&ajax=plugin_options') . '" title="' . $cStrings->TEXT_HELP_OPTIONS . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', $cStrings->TEXT_HELP_OPTIONS) . '</a>';
          $result = true;
          break;
        default:
          break;
      }
      return $result;
    }

    function abstract_box() {
      extract(tep_ref('contents'), EXTR_OVERWRITE|EXTR_REFS);
      $cStrings =& $this->strings;

      $contents[] = array('text' => '<a href="' . tep_href_link($this->main_script) . '">' . $cStrings->BOX_DOWNLOAD . '</a>');
      return true;
    }

    function ajax_start() {
      extract(tep_load('defs', 'sessions'));

      $result = false;
      if( !$this->check_scripts() || $cDefs->action != 'help' ) {
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

      $hID = (isset($_GET['hID']) ? (int)$_GET['hID'] : '');

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
      $cStrings =& $this->strings;

      $entries_array[] = array(
        'id' => $this->key,
        'title' => $cStrings->TEXT_INFO_MESSAGE,
        'image' => tep_image($this->admin_web_path . 'download.png', $cStrings->TEXT_INFO_MESSAGE),
        'href' => tep_href_link($this->main_script, 'action=set_options&plgID=' . $this->key . '&selected_box=plugins'),
      );
      return true;
    }

    function html_home_collections() {
      extract(tep_ref('entries_array'), EXTR_OVERWRITE|EXTR_REFS);
      $cStrings =& $this->strings;

      $entries_array[] = array(
        'id' => $this->key,
        'title' => $cStrings->TEXT_INFO_MESSAGE,
        'image' => tep_image($this->admin_web_path . 'download.png', $cStrings->TEXT_INFO_MESSAGE),
        'href' => tep_href_link($this->main_script, 'selected_box=abstract_box'),
      );
      return true;
    }

  }
?>
