<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Banner System Runtime Class
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
  class banner_system extends plugins_base {
    // Compatibility constructor
    function banner_system() {

      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      // Load the plugin options
      $this->options = $this->load_options();
      // Load the plugin strings
      $strings_array = array('web_strings.php');
      $this->strings = $this->load_strings($strings_array);

      //$this->strings = tep_get_strings($this->fs_template_path . 'web_strings.php');
      // Load the database tables for the box content
      tep_define_vars($this->fs_path . 'defs.php');
      $this->banners_array = $this->get_banners();

      $this->banner_template = $this->fs_template_path . 'banner.tpl';
      if( empty($this->banners_array) ) {
        $this->change(false);
      }
      $this->group_array = array();
    }

    function ajax_start() {
      extract(tep_load('defs', 'sessions'));
      if( !isset($_POST[$this->key]) || $_POST[$this->key] != $cDefs->action || !isset($_POST['attr']) ) return false;
      
      switch($cDefs->action) {
        case 'click':
          $this->set_click($_POST['attr']);
          break;
        case 'impression':
          $this->set_impression($_POST['attr']);
          break;
      }
      $cSessions->close();
      return true;
    }

    function html_start() {
      extract(tep_load('defs'));

      $cDefs->media[] = '<script type="text/javascript" src="' . $this->web_path . 'banner_system.js"></script>';
      return true;
    }

    function html_end() {
      extract(tep_load('defs'));

      $contents = '';
      $launcher = $this->web_path . 'launcher.tpl';
      $result = tep_read_contents($launcher, $contents);

      if(!$result) {
        return $result;
      }
      $contents_array = array(
        'BANNER_ATAG' => 'a.banner_system',
        'BANNER_SEL' => '.banner_class',
        'BASE_URL' => FILENAME_DEFAULT
      );
      $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);
      return true;
    }

    function html_header_post() {
      if( !$this->options['display_top'] ) return false;

      $banners_array = $this->get_location_banners(1);
      return $this->display_common($banners_array);
    }

    function html_footer_pre() {
      if( !$this->options['display_bottom'] ) return false;

      $banners_array = $this->get_location_banners(3);
      return $this->display_common($banners_array);
    }

    function html_left() {
      extract(tep_ref('box_array'), EXTR_OVERWRITE|EXTR_REFS);
      if( !$this->options['display_left'] ) return false;

      $banners_array = $this->get_location_banners(4);
      return $this->display_common($banners_array);
    }

    function html_right_end() {
      extract(tep_ref('box_array'), EXTR_OVERWRITE|EXTR_REFS);
      if( !$this->options['display_right'] ) return false;
      $banners_array = $this->get_location_banners(2);
      return $this->display_common($banners_array);
    }

    function display_common($banners_array) {
      extract(tep_load('defs', 'database'));

      $cStrings =& $this->strings;

      if( empty($banners_array) ) return false;

      foreach($banners_array as $key => $banner ) {
        $group_array = $this->get_group_properties($banner['group_id']);
        $data_array = array_merge($banner, $group_array);
        $data_array['image'] = tep_image($data_array['filename'], $data_array['content_name'], $data_array['group_width'], $data_array['group_height']);
        $content_type = $data_array['content_type'];

        if( empty($data_array['content_id']) ) {
          $content_type = 0;
        }

        if( $content_type == 1 ) {
          $link = tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $data_array['content_id']);
        } elseif($content_type == 2) {
          $link = tep_href_link(FILENAME_COLLECTIONS, 'abz_id=' . $data_array['content_id']);
        } else {
          $link = $data_array['content_link'];
        }

        $data_array['link'] = $link;
        require($this->banner_template);
      }
      $result = true;
      return $result;
    }

    function get_banners() {
      extract(tep_load('defs', 'database'));
      $result_array = array();
      $content_type = 0;

      switch($cDefs->script) {
        case FILENAME_GENERIC_PAGES:
          $content_type = 1;
          break;
        case FILENAME_COLLECTIONS:
          $content_type = 2;
          break;
        default:
          $content_type = 0;
          break;
      }
      $result_array = $db->query_to_array("select auto_id, group_id, filename, content_id, content_name, content_type, content_link from " . TABLE_BANNERS . " where (content_type = '" . (int)$content_type . "' or content_type = '0') and status_id = 1 order by sort_id");
      if( empty($result_array) ) return $result_array;

      $tmp_array = tep_array_invert_flat($result_array, 'group_id', 'group_id');
      $groups_array = $db->query_to_array(
        "select group_id, group_pos, group_type, group_width, group_height from " . TABLE_BANNERS_GROUP . " where group_id in (" . implode(',', array_keys($tmp_array)) . ")",
        'group_id'
      );

      $tmp_array = array();
      for($i=0, $j=count($result_array); $i<$j; $i++) {
        $group_id = $result_array[$i]['group_id'];
        $tmp_array[$group_id] = isset($tmp_array[$group_id])?count($tmp_array[$group_id]):0;
        $result_array[$i]['group_pos'] = $groups_array[$group_id]['group_pos'];
      }

      foreach($tmp_array as $group_id => $count) {
        if( $groups_array[$group_id]['group_type'] == 1 && $count ) {
          $index = 0;
          $keep = tep_rand(0, $count);
          for($i=0, $j=count($result_array); $i<$j; $i++) {
            if($result_array[$i]['group_id'] == $group_id ) {
              if( $keep != $index ) {
                unset($result_array[$i]);
              }
              $index++;
            }
          }
          $result_array = array_values($result_array);
        }
      }
      return $result_array;
    }

    function get_group_properties($group_id=0) {
      extract(tep_load('database'));

      if( empty($group_id) ) return $this->group_array;

      if( isset($this->group_array[$group_id]) ) {
        return $this->group_array[$group_id];
      }
      $group_query = $db->fly("select group_pos, group_width, group_height from " . TABLE_BANNERS_GROUP . " where group_id = '" . (int)$group_id . "'");
      if( !$db->num_rows($group_query) ) {
        return array();
      }

      $this->group_array[$group_id] = $db->fetch_array($group_query);
      return $this->group_array[$group_id];
    }


    function get_location_banners($loc) {
      $result_array = tep_array_invert_element($this->banners_array, 'group_pos');
      if( !isset($result_array[$loc]) ) $result_array[$loc] = array();

      return $result_array[$loc];
    }

    function set_impression($banner_id) {
      extract(tep_load('database'));
      $db->query("update " . TABLE_BANNERS . " set impressions=impressions+1 where auto_id = '" . (int)$banner_id . "' and status_id='1'");
    }

    function set_click($banner_id) {
      extract(tep_load('database'));
      $db->query("update " . TABLE_BANNERS . " set clicks=clicks+1 where auto_id = '" . (int)$banner_id . "' and status_id='1'");
    }
  }
?>
