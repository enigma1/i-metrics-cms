<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Popup Image run-time script
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
  class admin_popup_image extends plugins_base {

    // Compatibility constructor
    function admin_popup_image() {
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      // Load plugin configuration settings
      $this->options = $this->load_options();
    }

    function html_start() {
      global $g_media, $g_script;
      if( $this->options['back_all'] || isset($this->options['back_scripts'][$g_script]) ) {
        $g_media[] = '<script type="text/javascript" src="' . $this->admin_path . 'fancybox/jquery.fancybox-1.3.0.pack.js"></script>';
        $g_media[] = '<script type="text/javascript" src="' . $this->admin_path . 'fancybox/jquery.mousewheel-3.0.2.pack.js"></script>';
        $g_media[] = '<link rel="stylesheet" type="text/css" href="' . $this->admin_path . 'fancybox/jquery.fancybox-1.3.0.css" media="screen" />';
        return true;
      }
      return false;
    }

    function html_end() {
      global $g_script, $g_media;
      $result = false;
      if( $this->options['back_all'] || isset($this->options['back_scripts'][$g_script]) ) {

        $contents = '';
        $launcher = $this->admin_path . 'fancybox/launcher.tpl';
        $result = tep_read_contents($launcher, $contents);

        if($result) {
          if( isset($this->options['back_scripts'][$g_script]) ) {
          } else {
            $selector = $this->options['back_common_selector'];
          }
          $contents_array = array(
            'POPUP_IMAGE_SELECTOR' => $selector
          );
          $g_media[] = tep_templates_replace_entities($contents, $contents_array);
        }
      }
      return $result;
    }
  }
?>
