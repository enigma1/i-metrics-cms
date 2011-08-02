<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
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
      // Load the plugin options
      $this->options = $this->load_options();
      // Load the plugin strings
      $strings_array = array('web_strings.php');
      $this->strings = $this->load_strings($strings_array);
      // Load the database tables for the box content
      tep_define_vars($this->fs_path . 'defs.php');
      // Check the templates if the files are not present disable the plugin
      $this->box_right = $this->fs_template_path . 'right_box.tpl';
      $this->box_text = $this->fs_template_path . 'right_text.tpl';
      $this->box_collection = $this->fs_template_path . 'right_collection.tpl';
      if( !is_file($this->box_text) || !is_file($this->box_collection) || !is_file($this->box_right) ) {
        $this->change(false);
      }
    }

    function html_right() {
      extract(tep_load('defs'));
      extract(tep_ref('box_array'), EXTR_OVERWRITE|EXTR_REFS);

      $result = $this->display_solo_box();
      if($result) {
        $box_array = array();
        return $result;
      }

      if( $cDefs->gtext_id && $this->options['text_pages'] ) {
        $cText = new gtext_front;
        $zones_array = $cText->get_zone_entries($cDefs->gtext_id);
        if( count($zones_array) ) {
          $result = $this->display_ingtext_box();
          if($result) $box_array = array();
          return $result;
        }
      }

      if( $cDefs->abstract_id ) {
        $cSuper = new super_front();
        $zone_class = $cSuper->get_zone_class($cDefs->abstract_id);
        switch($zone_class) {
          case 'image_zones':
            if( $this->options['image_collections'] == 1 ) {
              $zones_array = $cSuper->get_parent_zones($cDefs->abstract_id);
              if( count($zones_array) ) {
                $this->display_filter_box();
                $box_array = array();
                return true;
              }
            }
            break;
          case 'generic_zones':
            if( $this->options['text_collections'] == 1 ) {
              $zones_array = $cSuper->get_parent_zones($cDefs->abstract_id);
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
      extract(tep_load('defs'));

      $cStrings =& $this->strings;

      $result = false;
      $cText = new gtext_front;
      $zones_array = $cText->get_zone_entries($cDefs->gtext_id);
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
      extract(tep_load('defs'));

      $cStrings =& $this->strings;
      $result = false;
      $cSuper = new super_front;
      $zones_array = $cSuper->get_parent_zones($cDefs->abstract_id);
      if( count($zones_array) ) {
        $total_array = array();
        for($i=0, $j=count($zones_array); $i<$j; $i++) {
          $zone_id = $zones_array[$i]['abstract_zone_id'];
          $text_data = $cSuper->get_zone_data($zone_id);
          $total_array[$i] = array(
            'id' => $zone_id,
            'name' => $text_data['abstract_zone_name'],
            'href' => tep_href_link(FILENAME_COLLECTIONS, 'abz_id=' . $zone_id),
            'text' => tep_truncate_string($text_data['abstract_zone_desc']),
          );
        }
        require($this->box_collection);
        $result = true;
      }
      return $result;
    }

    function display_solo_box() {
      extract(tep_load('defs', 'database'));

      $content_id = $type_id = 0;

      if( $cDefs->gtext_id ) {
        $content_id = $cDefs->gtext_id;
        $type_id = 1;
      }
      if( $cDefs->abstract_id ) {
        $content_id = $cDefs->abstract_id;
        $type_id = 2;
      }

      if( !$type_id ) return false;

      $box_query_raw = "select content_name, content_text from " . TABLE_RIGHT_TO_CONTENT . " where content_id = '" . (int)$content_id . "' and content_type='" . (int)$type_id . "' and status_id='1' order by sort_id";
      $box_items = $db->query_to_array($box_query_raw);
      for($i=0, $j=count($box_items); $i<$j; $i++) {
        $name = $box_items[$i]['content_name'];
        $text = $box_items[$i]['content_text'];
        // Skip empty entries
        if( empty($name) && empty($text) ) continue;
        require($this->box_right);
      }
      return ($j>0);
    }
  }
?>
