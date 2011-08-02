<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Abstract Zones root class
//----------------------------------------------------------------------------
// Retrieves Abstract Zones Data
// Provides Interface functions to higher level abstract types
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

  class abstract_front {
    // compatibility constructor
    function abstract_front() {}

    // Get Zones by class
    function get_zones_by_class($class_name, $visible=true) {
      extract(tep_load('database'));

      $zones_array = array();
      $class_id = $this->get_zone_class_id($class_name);
      if( !$class_id ) return $zones_array();

      $enabled_filter = '';
      if( $visible ) {
        $enabled_filter = " and status_id='1'";
      }

      $total_items = $db->query_to_array("select abstract_zone_id, abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_types_id = '" . (int)$class_id . "'" . $enabled_filter . " order by sort_id");
      for($i=0, $j = count($total_items); $i<$j; $i++ ) {
        $zones_array[$total_items[$i]['abstract_zone_id']] = $total_items[$i];
      }
      return $zones_array;
    }

    function get_zone_class_id($class_name) {
      extract(tep_load('database'));

      $result = 0;
      $check_query = $db->fly("select abstract_types_id from " . TABLE_ABSTRACT_TYPES . " where abstract_types_class = '" . $db->filter($class_name) . "' and abstract_types_status = '1'");
      if( $db->num_rows($check_query) ) {
        $check_array = $db->fetch_array($check_query);
        $result = $check_array['abstract_types_id'];
      }
      return $result;
    }

    function get_zone_class_name($class_id) {
      extract(tep_load('database'));

      $result = false;
      $check_query = $db->fly("select abstract_types_class from " . TABLE_ABSTRACT_TYPES . " where abstract_types_id = '" . (int)$class_id . "' and abstract_types_status = '1'");
      if( $db->num_rows($check_query) ) {
        $check_array = $db->fetch_array($check_query);
        $result = $check_array['abstract_types_class'];
      }
      return $result;
    }

    function get_zone($zone) {
      extract(tep_load('database'));

      if( is_numeric($zone) ) {
        $zone_id = $zone;
      } else {
        $zone_id = $this->get_zone_id($zone);
      }

      if( !$zone_id) {
        return 0;
      }

      $check_query = $db->fly("select count(*) as total from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "'");
      $check_array = $db->fetch_array($check_query);
      if( $check_array['total'] ) return $zone_id;

      return 0;
    }

    // Get zone id from name
    function get_zone_name($zone_id) {
      extract(tep_load('database'));

      $zone_query = $db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "'");
      if( $db->num_rows($zone_query) ) {
        $zone = $db->fetch_array($zone_query);
        return $zone['abstract_zone_name'];
      }
      return '';
    }

    function get_zone_class($zone_id) {
      extract(tep_load('database'));

      $result = false;
      $class_query = $db->fly("select abstract_types_id from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "'");
      if( $db->num_rows($class_query) ) {
        $class_array = $db->fetch_array($class_query);
        $result = $this->get_zone_class_name($class_array['abstract_types_id']);
      }
      return $result;
    }

    function is_zone_type($zone_id, $class_name) {
      extract(tep_load('database'));

      $class_id = $this->get_zone_class_id($class_name);
      if( empty($class_id) ) return false;

      $check_query = $db->fly("select count(*) as total from " . TABLE_ABSTRACT_ZONES . " where abstract_types_id = '" . (int)$class_id . "' and abstract_zone_id = '" . (int)$zone_id . "'");
      $check_array = $db->fetch_array($check_query);
      return ($check_array['total'] > 0);
    }

    function is_enabled($zone_id) {
      extract(tep_load('database'));

      $check_query = $db->fly("select count(*) as total from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "' and status_id='1'");
      $check_array = $db->fetch_array($check_query);
      return ($check_array['total'] > 0);
    }

    function get_zone_data($zone, $enabled=false) {
      extract(tep_load('database'));

      $result_array = array();
      $zone_id = $this->get_zone($zone);
      if( !$zone_id ) 
        return $result_array;

      $enabled_filter = '';
      if( $enabled ) {
        $enabled_filter = " and status_id='1'";
      }

      $zone_query = $db->fly("select abstract_zone_id, abstract_zone_name, abstract_zone_desc, status_id from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "'" . $enabled_filter);
      if( $db->num_rows($zone_query) ) {
        $result_array = $db->fetch_array($zone_query);
        return $result_array;
      }
      return $result_array;
    }

    function get_zone_multi_data($zones_array, $tflag=true, $dflag=true, $visible=false, $limit=0) {
      extract(tep_load('database'));

      $result_array = array();
      if( !count($zones_array) ) {
        return $result_array;
      }

      $select_string = '';
      if( $tflag ) {
        $select_string .= ', abstract_zone_name';
      }
      if( $dflag ) {
        $select_string .= ', abstract_zone_desc';
      }

      $enabled_filter = '';
      if( $visible ) {
        $enabled_filter = " and status_id='1'";
      }

      $limit_filter = '';
      if( $limit > 0 ) {
        $limit_filter = " limit " . ((int)$limit);
      }

      $result_array = $db->query_to_array("select abstract_zone_id" . $select_string . " from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id in (" . $db->filter(implode(',', $zones_array)) . ")" . $enabled_filter . " order by sort_id" . $limit_filter);
      return $result_array;
    }

    // Get zone id from name
    function get_zone_id($zone_name) {
      extract(tep_load('database'));

      $zone_query = $db->fly("select abstract_zone_id from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_name = '" . $db->filter($zone_name) . "'");
      if( $zone = $db->fetch_array($zone_query) ) {
        return $zone['abstract_zone_id'];
      }
      return 0;
    }

    function get_entries($zone_id) {
      extract(tep_load('database'));

      $zone_query = $db->fly("select count(*) as total from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$zone_id . "'");
      $zone_array = $db->fetch_array($zone_query);
      return $zone_array['total'];
    }
  }
?>
