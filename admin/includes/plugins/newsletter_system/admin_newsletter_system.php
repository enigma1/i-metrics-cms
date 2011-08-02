<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Newsletters System invoke script
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
  class admin_newsletter_system extends plugins_base {
    var $strings;

    // Compatibility constructor
    function admin_newsletter_system() {
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      $this->options = $this->load_options();

      tep_define_vars($this->admin_path . 'back/admin_defs.php');
      $this->strings = tep_get_strings($this->admin_path . 'back/admin_strings.php');

      $this->main_script = FILENAME_NEWSLETTERS;
      $this->scripts_array[] = $this->main_script;
    }

    function tools_box() {
      extract(tep_ref('contents'), EXTR_OVERWRITE|EXTR_REFS);
      $cStrings =& $this->strings;

      $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_NEWSLETTERS) . '">' . $cStrings->BOX_NEWSLETTERS . '</a>');
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

      if( !$this->check_scripts($this->scripts_array) ) {
        return false;
      }

      if( $cDefs->script == FILENAME_NEWSLETTERS && ($cDefs->action == 'edit' || $cDefs->action == 'template_upload') ) {
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/tiny_mce.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/config.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/image_control.js"></script>';
      }
      tep_set_lightbox();
      return true;
    }

    function html_end() {
      extract(tep_load('defs', 'sessions'));

      if( !$this->check_scripts($this->scripts_array) ) {
        return false;
      }

      $cStrings =& $this->strings;

      ob_start();
      require($this->admin_path . 'back/jscripts.tpl');
      $contents = ob_get_contents();
      ob_end_clean();
      $cDefs->media[] = $contents;

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
        'image' => tep_image($this->admin_web_path . 'newsletter.png', $cStrings->TEXT_INFO_MESSAGE),
        'href' => tep_href_link(FILENAME_NEWSLETTERS, 'action=set_options&plgID=' . $this->key . '&selected_box=plugins_box'),
      );
      return true;
    }

    function html_home_tools() {
      extract(tep_ref('entries_array'), EXTR_OVERWRITE|EXTR_REFS);
      $cStrings =& $this->strings;

      $entries_array[] = array(
        'id' => $this->key,
        'title' => $cStrings->TEXT_INFO_MESSAGE,
        'image' => tep_image($this->admin_web_path . 'newsletter.png', $cStrings->TEXT_INFO_MESSAGE),
        'href' => tep_href_link(FILENAME_NEWSLETTERS, 'selected_box=tools_box'),
      );
      return true;
    }
  }
?>
