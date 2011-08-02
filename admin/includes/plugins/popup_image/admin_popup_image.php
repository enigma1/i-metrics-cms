<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
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
      extract(tep_load('defs'));

      if( $this->options['back_all'] || isset($this->options['back_scripts'][$cDefs->script]) ) {
        tep_set_lightbox();
        return true;
      }
      return false;
    }

    function html_end() {
      extract(tep_load('defs'));

      $result = false;
      if( $this->options['back_all'] || isset($this->options['back_scripts'][$cDefs->script]) ) {

        $contents = '';
        $launcher = $this->admin_path . 'launcher.tpl';
        $result = tep_read_contents($launcher, $contents);

        if($result) {
          if( isset($this->options['back_scripts'][$cDefs->script]) ) {
          } else {
            $selector = $this->options['back_common_selector'];
          }
          $contents_array = array(
            'POPUP_IMAGE_SELECTOR' => $selector
          );
          $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);
        }
      }
      return $result;
    }
  }
?>
