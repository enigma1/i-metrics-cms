<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Runtime Text Color Gradient Class
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
  class text_gradient extends plugins_base {

    // Compatibility constructor
    function text_gradient() {
      global $g_script;

      parent::plugins_base();
      // Load plugin configuration settings
      $this->options = $this->load_options();

      if( !$this->options['front_all'] && !isset($this->options['front_scripts'][$g_script]) ) {
        $this->change(false);
      }
    }

    function html_start() {
      global $g_media;

      $g_media[] = '<script type="text/javascript" src="' . $this->web_path . 'gradient/jquery.textgrad0.js"></script>';
      return true;
    }

    function html_end() {
      global $g_script, $g_media;

      $contents = '';
      $launcher = $this->web_path . 'gradient/launcher.tpl';
      $result = tep_read_contents($launcher, $contents);

      if($result) {
        if( isset($this->options['front_scripts'][$g_script]) ) {
          $selector = $this->options['front_scripts'][$g_script]['selector'];
          $colors = $this->options['front_scripts'][$g_script]['colors'];

          $colors_array = explode('|',$colors);
          $contents_array = array(
            'TEXT_GRADIENT_SELECTOR' => $selector,
            'TEXT_GRADIENT_START' => $colors_array[0],
            'TEXT_GRADIENT_END' => $colors_array[1],
          );
          $g_media[] = tep_templates_replace_entities($contents, $contents_array);
        }
        if( $this->options['front_all'] ) {
          $selector = $this->options['front_common_selector'];
          $colors = $this->options['front_common_colors'];
          $colors_array = explode('|',$colors);
          $contents_array = array(
            'TEXT_GRADIENT_SELECTOR' => $selector,
            'TEXT_GRADIENT_START' => $colors_array[0],
            'TEXT_GRADIENT_END' => $colors_array[1],
          );
          $g_media[] = tep_templates_replace_entities($contents, $contents_array);
        }
      }
      return $result;
    }
  }
?>
