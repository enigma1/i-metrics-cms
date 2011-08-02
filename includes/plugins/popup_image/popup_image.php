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

      $launcher = $this->web_path . 'launcher.tpl';
      if( !file_exists($launcher) ) $this->change(false);
    }

    function html_start() {
      extract(tep_load('defs'));

      if( $this->options['front_all'] || isset($this->options['front_scripts'][$cDefs->script]) ) {
        tep_set_lightbox();
        return true;
      }
      return false;
    }

    function html_end() {
      extract(tep_load('defs'));

      $result = false;
      if( $this->options['front_all'] || isset($this->options['front_scripts'][$cDefs->script]) ) {

        $contents = '';
        $launcher = $this->web_path . 'fancybox/launcher.tpl';
        $result = tep_read_contents($launcher, $contents);

        if($result) {
          if( $this->options['front_all'] ) {
            $selector = $this->options['front_common_selector'];
            $contents_array = array(
              'POPUP_IMAGE_SELECTOR' => $selector
            );
            $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);
          }
          if( isset($this->options['front_scripts'][$cDefs->script]) ) {
            $selector = $this->options['front_scripts'][$cDefs->script];
            $contents_array = array(
              'POPUP_IMAGE_SELECTOR' => $selector
            );
            $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);
          }
        }
      }
      return $result;
    }
  }
?>
