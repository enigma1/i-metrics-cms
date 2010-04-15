<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Image Zones module class
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
*/
  class image_zones_mod {
    var $zone_id, $zones_array, $main_array;

    function image_zones_mod($zone_id, $heding=true) {
      $this->zone_id = $zone_id;
      $cImage = new image_front();
      $this->main_array = $cImage->get_zone_data($zone_id);
      $this->zones_array = $cImage->get_entries($zone_id, true, false);
    }

    function output($display=false) {
      $html_output = '';
      $html_output .=
      '  <div><h2><a href="' . tep_href_link(FILENAME_IMAGE_PAGES, 'abz_id=' . $this->zone_id) . '">' . $this->main_array['abstract_zone_name'] . '</a></h2></div>' . "\n" . 
      '  <div class="contentBoxContents">' . tep_truncate_string($this->main_array['abstract_zone_desc']) . '</div>' . "\n";
/*
      foreach( $this->zones_array as $key => $value ) {
        $html_output .= 
        '  <div class="blocklink"><a href="' . tep_href_link(FILENAME_IMAGE_PAGES, 'gtext_id=' . $key) . '">' . $value['image_alt_title']  . '</a></div>' . "\n";
      }
*/
      if( $display ) echo $html_output;
      return $html_output;
    }
  }
?>
