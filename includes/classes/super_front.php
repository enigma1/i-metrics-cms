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
  class super_front extends abstract_front {

// class constructor
    function super_front() {
      parent::abstract_front();
    }

    function get_entries($zone, $tflag=true) {
      extract(tep_load('database'));

      $super_array = array();
      $zone_id = $this->get_zone($zone);
      if( !$zone_id) {
        return $super_array;
      }

      $select_string = '';
      if( $tflag ) {
        $select_string .= ', sub_alt_title';
      }

      $super_query_raw = "select subzone_id" . $select_string . " from " . TABLE_SUPER_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "' order by sequence_order";
      $super_array = $db->query_to_array($super_query_raw, 'subzone_id');

      return $super_array;
    }

    function get_parent_zones($subzone_id, $enabled_flag=true) {
      extract(tep_load('database'));

      $zones_array = $db->query_to_array("select abstract_zone_id from " . TABLE_SUPER_ZONES . " where subzone_id = '" . (int)$subzone_id . "' order by sequence_order");

      if( $enabled_flag ) {
        $tmp_array = tep_array_invert_from_element($zones_array, 'abstract_zone_id', 'abstract_zone_id');
        if( !empty($tmp_array) ) {
          $zones_array = $db->query_to_array("select abstract_zone_id from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id in (" . $db->filter(implode(',', $tmp_array)) . ") and status_id='1'");
        }
      }
      return $zones_array;
    }
  }
?>