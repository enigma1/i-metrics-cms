<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Static Configuration support functions for the database config columns
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
  class config {
    // class constructor
    function config() {}

    function radio_option() {
      $string = '';
      $select_array = func_get_args();
      $key_value = array_shift($select_array);

      for ($i=0, $j=count($select_array); $i<$j; $i++) {
        $checked = ($key_value == $select_array[$i])?true:false;
        $string .= '<div>' . tep_draw_radio_field('configuration_value', $select_array[$i], $checked);
        $string .= $select_array[$i] . '</div>';
      }
      return $string;
    }

    function textarea($text) {
      $args = func_get_args();
      $text = array_shift($args);
      return tep_draw_textarea_field('configuration_value', $text, '', 8);
    }

    function pull_down_gtext_entries($gtext_id) {
      extract(tep_load('database'));

      $gtext_query = "select gtext_id as id, gtext_title as text from " . TABLE_GTEXT . " order by gtext_title";
      $gtext_array = $db->query_to_array($gtext_query);
      return tep_draw_pull_down_menu('configuration_value', $gtext_array, $gtext_id);
    }

    function pull_down_template_groups($group_id) {
      extract(tep_load('database'));

      $group_query = "select group_id as id, group_title as text from " . TABLE_TEMPLATES_GROUPS . " order by group_title";
      $group_array = $db->query_to_array($group_query);
      return tep_draw_pull_down_menu('configuration_value', $group_array, $group_id);
    }

    function get_gtext_title($gtext_id) {
      extract(tep_load('database'));

      $gtext_query = $db->query("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
      $gtext = $db->fetch_array($gtext_query);
      return $gtext['gtext_title'];
    }

    function pull_down_text_zones($abstract_zone_id) {
      extract(tep_load('database'));

      $abstract_query = "select az.abstract_zone_id as id, az.abstract_zone_name as text from " . TABLE_ABSTRACT_ZONES . " az, " . TABLE_ABSTRACT_TYPES . " abt where abstract_types_class='generic_zones' and abt.abstract_types_id=az.abstract_types_id";
      $abstract_array = $db->query_to_array($abstract_query);
      return tep_draw_pull_down_menu('configuration_value', $abstract_array, $abstract_zone_id);
    }

    function pull_down_super_zones($abstract_zone_id) {
      extract(tep_load('database'));

      $abstract_query = "select az.abstract_zone_id as id, az.abstract_zone_name as text from " . TABLE_ABSTRACT_ZONES . " az, " . TABLE_ABSTRACT_TYPES . " abt where abstract_types_class='super_zones' and abt.abstract_types_id=az.abstract_types_id";
      $abstract_array = $db->query_to_array($abstract_query);
      return tep_draw_pull_down_menu('configuration_value', $abstract_array, $abstract_zone_id);
    }

    function pull_down_image_zones($abstract_zone_id) {
      extract(tep_load('database'));

      $abstract_query = "select az.abstract_zone_id as id, az.abstract_zone_name as text from " . TABLE_ABSTRACT_ZONES . " az, " . TABLE_ABSTRACT_TYPES . " abt where abstract_types_class='image_zones' and abt.abstract_types_id=az.abstract_types_id";
      $abstract_array = $db->query_to_array($abstract_query);
      return tep_draw_pull_down_menu('configuration_value', $abstract_array, $abstract_zone_id);
    }

    function get_abstract_zone_name($abstract_zone_id) {
      extract(tep_load('database'));

      $abstract_query = $db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      $abstract = $db->fetch_array($abstract_query);
      return $abstract['abstract_zone_name'];
    }

    function get_template_group_title($group_id) {
      extract(tep_load('database'));

      $group_query = $db->query("select group_title from " . TABLE_TEMPLATES_GROUPS . " where group_id = '" . (int)$group_id . "'");
      $group_array = $db->fetch_array($group_query);
      return $group_array['group_title'];
    }

  }
?>
