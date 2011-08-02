<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
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
      extract(tep_load('database'));

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

      $this->entries_string = "select image_file" . $select_string . " from " . TABLE_IMAGE_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "' order by sequence_order";

      if( $raw ) {
        return $this->entries_string;
      } else {
        $tmp_array = $db->query_to_array($this->entries_string);
        if( !count($tmp_array) ) {
          return $this->entries_array;
        }
        $this->entries_array = $tmp_array;
        return $this->entries_array;
      }
    }
  }
?>