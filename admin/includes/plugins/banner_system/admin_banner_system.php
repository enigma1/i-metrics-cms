<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Banners System runtime script
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
  class admin_banner_system extends plugins_base {

    // Compatibility constructor
    function admin_banner_system() {
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      $this->options = $this->load_options();

      tep_define_vars($this->admin_path . 'back/admin_defs.php');
      $this->strings = tep_get_strings($this->admin_path . 'back/admin_strings.php');
      //$this->scripts_array[] = FILENAME_BANNERS;
      $this->main_script = FILENAME_BANNERS;
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

    function tools_box() {
      extract(tep_ref('contents'), EXTR_OVERWRITE|EXTR_REFS);
      $cStrings =& $this->strings;

      $contents[] = array('text' => '<a href="' . tep_href_link($this->main_script) . '">' . $cStrings->BOX_BANNERS . '</a>');
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

    function languages_sync() {
      extract(tep_ref('tables'), EXTR_OVERWRITE|EXTR_REFS);
      extract(tep_load('languages'));

      $tables['TABLE_BANNERS'] = $lng->get_default_table('TABLE_BANNERS');
      return true;
    }

    function html_start() {
      extract(tep_load('defs'));

      if( !$this->check_scripts() ) return false;
      tep_set_lightbox();
      return true;
    }

    function html_end() {
      extract(tep_load('defs'));

      if( !$this->check_scripts($this->scripts_array) ) {
        return false;
      }

      $cStrings =& $this->strings;

      $contents = '';
      $launcher = $this->admin_path . 'back/launcher.tpl';
      $result = tep_read_contents($launcher, $contents);
      if( !$result ) return false;

      $script_name = tep_get_script_name();
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
        'image' => tep_image($this->admin_web_path . 'banner.png', $cStrings->TEXT_INFO_MESSAGE),
        'href' => tep_href_link(FILENAME_PLUGINS, 'action=set_options&plgID=' . $this->key . '&selected_box=plugins_box'),
      );
      return true;
    }

    function html_home_tools() {
      extract(tep_ref('entries_array'), EXTR_OVERWRITE|EXTR_REFS);
      $cStrings =& $this->strings;

      $entries_array[] = array(
        'id' => $this->key,
        'title' => $cStrings->TEXT_INFO_MESSAGE,
        'image' => tep_image($this->admin_web_path . 'banner.png', $cStrings->TEXT_INFO_MESSAGE),
        'href' => tep_href_link($this->main_script, 'selected_box=tools_box'),
      );
      return true;
    }

    function html_home_side() {
      return true;
    }

  }
?>
