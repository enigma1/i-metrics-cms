<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Right Column selection processing script
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
  class right_column extends plugins_base {
    // Compatibility constructor
    function right_column() {
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      $this->options = $this->load_options();
      $this->strings = tep_get_strings($this->web_template_path . 'web_strings.php');

      $this->box_text = $this->web_template_path . 'right_text.tpl';
      $this->box_collection = $this->web_template_path . 'right_collection.tpl';
      if( !file_exists($this->box_text) || !file_exists($this->box_collection) ) $this->change(false);
    }

    function html_right() {
      global $current_gtext_id, $current_abstract_id, $box_array;

      if( $current_gtext_id && $this->options['text_pages'] ) {
        $cText = new gtext_front;
        $zones_array = $cText->get_zone_entries($current_gtext_id);
        if( count($zones_array) ) {
          $result = $this->display_ingtext_box();
          if($result) $box_array = array();
          return $result;
        }
      }

      if( $current_abstract_id ) {
        $cSuper = new super_front();
        $zone_class = $cSuper->get_zone_class($current_abstract_id);
        switch($zone_class) {
          case 'image_zones':
            if( $this->options['image_collections'] == 1 ) {
              $zones_array = $cSuper->get_parent_zones($current_abstract_id);
              if( count($zones_array) ) {
                $this->display_filter_box();
                $box_array = array();
                return true;
              }
            }
            break;
          case 'generic_zones':
            if( $this->options['text_collections'] == 1 ) {
              $zones_array = $cSuper->get_parent_zones($current_abstract_id);
              if( count($zones_array) ) {
                $this->display_filter_box();
                $box_array = array();
                return true;
              }
            }
            break;
          default:
            break;
        }
      }
      return false;
    }
    
    function display_ingtext_box() {
      global $current_gtext_id;

      $cStrings =& $this->strings;

      $result = false;
      $cText = new gtext_front;
      $zones_array = $cText->get_zone_entries($current_gtext_id);
      if( count($zones_array) ) {
        $i = 0;
        $total_array = array();
        foreach($zones_array as $id => $zone) { 
          $total_array[$i] = array(
            'id' => $zone['abstract_zone_id'],
            'name' => $zone['abstract_zone_name'],
            'href' => tep_href_link(FILENAME_GENERIC_PAGES, 'abz_id=' . $id),
            'text' => $zone['abstract_zone_desc'],
            'entries' => array(),
          );
          $text_array = $cText->get_entries($zone['abstract_zone_id'], true, false);
          $i2 = 0;
          foreach($text_array as $key => $value) {
            $total_array[$i]['entries'][$i2] = array(
              'id' => $key,
              'name' => $value['gtext_title'],
              'href' => tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $key),
            );
            $i2++;
          }
          $i++;
        }
        require($this->box_text);
        $result = true;
      }
      return $result;
    }

    function display_filter_box() {
      global $current_abstract_id;

      $cStrings =& $this->strings;
      $result = false;
      $cSuper = new super_front;
      $zones_array = $cSuper->get_parent_zones($current_abstract_id);
      if( count($zones_array) ) {
        $total_array = array();
        for($i=0, $j=count($zones_array); $i<$j; $i++) {
          $zone_id = $zones_array[$i]['abstract_zone_id'];
          $text_data = $cSuper->get_zone_data($zone_id);
          $total_array[$i] = array(
            'id' => $zone_id,
            'name' => $text_data['abstract_zone_name'],
            'href' => tep_href_link(FILENAME_SUPER_PAGES, 'abz_id=' . $zone_id),
            'text' => tep_truncate_string($text_data['abstract_zone_desc']),
          );
        }
        require($this->box_collection);
        $result = true;
      }
      return $result;
    }
  }
?>
