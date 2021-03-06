<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Generic Text Zones class for osCommerce Catalog
// This is a Bridge for the Abstract Zones front-end
// Support class for text pages via abstract zones
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
  class gtext_front extends abstract_front {
    var $text_array, $text_string;

// class constructor
    function gtext_front() {
      parent::abstract_front();
      $this->text_array = array();
      $this->text_string = '';
      $this->text_ids_array = array();
    }

    function get_entries($zone, $tflag=true, $dflag = true, $raw = false) {
      extract(tep_load('database'));

      $this->text_array = array();
      $zone_id = $this->get_zone($zone);
      if( !$zone_id) {
        return $this->text_array;
      }

      $select_string = '';
      if( $tflag ) {
        $select_string .= ', gt.gtext_title, gt2d.gtext_alt_title';
      }
      if( $dflag ) {
        $select_string .= ', gt.gtext_description, gt.date_added';
      }

      $this->text_string = "select gt.gtext_id" . $select_string . " from " . TABLE_GTEXT . " gt left join " . TABLE_GTEXT_TO_DISPLAY . " gt2d on (gt.gtext_id=gt2d.gtext_id) where gt2d.abstract_zone_id = '" . (int)$zone_id . "' and gt.status= '1' order by gt2d.sequence_order";
      if( $raw ) {
        return $this->text_string;
      } else {
        $tmp_array = $db->query_to_array($this->text_string, 'gtext_id');
        if( !count($tmp_array) ) {
          return $this->text_array;
        }
        $this->text_array = $tmp_array;
        return $this->text_array;
      }
    }

    function get_zone_entries($gtext_id, $tflag=true, $dflag = true, $visible=true, $limit=0) {
      extract(tep_load('database'));

      $result_array = array();
      if( $gtext_id <= 0 ) return $result_array;

      $type_id = $this->get_zone_class_id('generic_zones');
      $zones_query_raw = "select abstract_zone_id from " . TABLE_GTEXT_TO_DISPLAY . " where gtext_id= '" . (int)$gtext_id . "'";
      $zones_array = $db->query_to_array($zones_query_raw, 'abstract_zone_id');
      $result_array = $this->get_zone_multi_data(array_keys($zones_array), $tflag, $dflag, $visible, $limit);
      return $result_array;
    }


    function get_gtext_ids($zone) {
      extract(tep_load('database'));

      $this->text_ids_array = array();
      $zone_id = $this->get_zone($zone);
      if( !$zone_id) {
        return $this->text_ids_array;
      }

      $gtext_query_raw = "select gtext_id " . TABLE_GTEXT_TO_DISPLAY . " where abstract_zone_id = '" . (int)$zone_id . "' order by sequence_order";
      $this->text_ids_array = $db->query_to_array($gtext_query_raw);
      return $this->text_ids_array;
    }

  }
?>