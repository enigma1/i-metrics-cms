<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Super Zones class
// This is a Bridge for the Abstract Zones front-end
// Support class for groups of various zones other than Super Zones
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class image_front extends abstract_front {
    var $entries_array, $entries_string;

// class constructor
    function image_front() {
      parent::abstract_front();
      $this->entries_array = array();
      $this->entries_string = '';
    }

    function get_entries($zone, $tflag=true, $dflag=true, $raw=false) {
      global $g_db;

      $this->entries_array = array();
      $zone_id = $this->get_zone($zone);
      if( !$zone_id) {
        return $this->entries_array;
      }

      $select_string = '';
      if( $tflag ) {
        $select_string .= ', image_alt_title';
      }
      if( $dflag ) {
        $select_string .= ', image_title';
      }

      if( $raw ) {
        $this->entries_string = "select image_file" . $select_string . " from " . TABLE_IMAGE_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "' order by sequence_order";
        return $this->entries_string;
      } else {
        $zone_query = $g_db->query("select image_file" . $select_string . " from " . TABLE_IMAGE_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "' order by sequence_order");
        if( !$g_db->num_rows($zone_query) ) {
          return $this->entries_array;
        }
        while( $zone = $g_db->fetch_array($zone_query) ) {
          $this->entries_array[] = $zone;
        }
        return $this->entries_array;
      }
    }
  }
?>