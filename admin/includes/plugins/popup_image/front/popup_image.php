<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Voting system invoke script
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
  class popup_image extends plugins_base {
    // Compatibility constructor
    function popup_image() {
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      // Load plugin configuration settings
      $this->options = $this->load_options();

      $launcher = $this->web_path . 'fancybox/launcher.tpl';
      if( !file_exists($launcher) ) $this->change(false);
    }

    function html_start() {
      global $g_media, $g_script;
      if( $this->options['front_all'] || isset($this->options['front_scripts'][$g_script]) ) {
        $g_media[] = '<script type="text/javascript" src="' . $this->web_path . 'fancybox/jquery.fancybox-1.3.0.pack.js"></script>';
        $g_media[] = '<script type="text/javascript" src="' . $this->web_path . 'fancybox/jquery.mousewheel-3.0.2.pack.js"></script>';
        $g_media[] = '<link rel="stylesheet" type="text/css" href="' . $this->web_path . 'fancybox/jquery.fancybox-1.3.0.css" media="screen" />';
        return true;
      }
      return false;
    }

    function html_end() {
      global $g_script, $g_media;
      $result = false;
      if( $this->options['front_all'] || isset($this->options['front_scripts'][$g_script]) ) {

        $contents = '';
        $launcher = $this->web_path . 'fancybox/launcher.tpl';
        $result = tep_read_contents($launcher, $contents);

        if($result) {
          if( $this->options['front_all'] ) {
            $selector = $this->options['front_common_selector'];
            $contents_array = array(
              'POPUP_IMAGE_SELECTOR' => $selector
            );
            $g_media[] = tep_templates_replace_entities($contents, $contents_array);
          }
          if( isset($this->options['front_scripts'][$g_script]) ) {
            $selector = $this->options['front_scripts'][$g_script];
            $contents_array = array(
              'POPUP_IMAGE_SELECTOR' => $selector
            );
            $g_media[] = tep_templates_replace_entities($contents, $contents_array);
          }
        }
      }
      return $result;
    }
  }
?>
