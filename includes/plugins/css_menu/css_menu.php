<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Runtime CSS Menu Class
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
  class css_menu extends plugins_base {
    var $menu_template;
    var $options = array(
      'max_drop' => 10, 
      'max_width' => 380,
    );

    // Compatibility constructor
    function css_menu() {
      parent::plugins_base();
      $this->menu_template = $this->web_template_path . 'css_menu.tpl';
    }

    function html_start() {
      global $g_media;
      $g_media[] = '<link rel="stylesheet" type="text/css" href="' . $this->web_template_path . 'css_menu.css" />';
      return true;
    }

    function html_menu() {
      $cSuper = new super_front;
      $zones_array = $cSuper->get_entries(SUPER_MENU_ZONE_ID);
      $menu_string = $this->get_super_css_tree($zones_array);
      if( empty($menu_string) || !file_exists($this->menu_template) ) return false;
      require_once($this->menu_template);
      return true;
    }

    function get_super_css_tree($zones_array) {
      $final_string = "\n";
      $index = 0;
      $total = count($zones_array);

      $cAbstract = new abstract_front();
      foreach($zones_array as $id => $zone) {  
        $index++;

        if( !$cAbstract->get_entries($id) ) continue;

        $zone_data = $cAbstract->get_zone_data($id, true);
        if( empty($zone_data) ) continue;

        $zone_class = $cAbstract->get_zone_class($id);
        switch($zone_class) {
          case 'image_zones':
            $script = FILENAME_IMAGE_PAGES;
            $process_function = 'get_image_css_tree';
            break;
          case 'super_zones':
            $script = FILENAME_SUPER_PAGES;
            $process_function = 'get_mixed_css_tree';
            break;
          default:
            $script = FILENAME_GENERIC_PAGES;
            $process_function = 'get_text_css_tree';
            break;
        }

        $value2 = strtoupper($zone_data['abstract_zone_name']);
        $awidth = (tep_string_length($value2)*9)+16;
        $value2 = htmlspecialchars(stripslashes($value2));
        //$value2 = str_replace(' ', '&nbsp;', $value2 );

        $final_string .= 
          '<div class="css_top floater calign"><a href="' . tep_href_link($script, 'abz_id=' . $id) . '" style="width:' . $awidth . 'px">' . $value2 . '</a>' . "\n";

        $tmp_drop = $this->options['max_drop'];
        $max_width = $this->options['max_width'];
        $tmp_string = $this->$process_function($id, $tmp_drop, $max_width);
        if( !empty($tmp_string) ) { 
          if( $tmp_drop != $this->options['max_drop'] ) {
            $tmp_string .= '    <div class="css_sub" style="width: ' . $max_width . 'px"><a href="' . tep_href_link($script, 'abz_id=' . $id) . '">' . TEXT_SEE_ALL . '</a></div>';
          }
          $contents_array = array(
            'MAX_WIDTH' => $max_width
          );
          $tmp_string = tep_templates_replace_entities($tmp_string, $contents_array);

          $final_string .=
          '  <div class="css_section">' . "\n" . $tmp_string . '  </div>' . "\n";
        }
        $final_string .=
          '</div>' . "\n";

        if( $index < $total ) {
          $last_sep = '    <div class="css_sep floater">' . tep_image($this->web_template_path . 'words_space.gif') . '</div>' . "\n";
          $final_string .= $last_sep;
        }
      }
      return $final_string;
    }

    function get_text_css_tree($id, &$max_drop, &$max_width) {
      $sub_string = '';
      $count = $swidth = 0;
      
      $cText = new gtext_front();
      $text_entries = $cText->get_entries($id, true, false);
      if( !count($text_entries) ) return $sub_string;

      foreach($text_entries as $key => $value) {
        $value['gtext_title'] = strtoupper($value['gtext_title']);
        $awidth = (tep_string_length($value['gtext_title'])*9)+16;
        $value['gtext_title'] = htmlspecialchars(stripslashes($value['gtext_title']));

        if( $awidth > $max_width ) {
          $string_length = (int)($max_width/9);
          $value['gtext_title'] = substr($value['gtext_title'], 0, $string_length) . '...';
          $awidth = $max_width;
        }
        if( $awidth > $swidth ) $swidth = $awidth;
        $sub_string .= '    <div class="css_sub" style="width: [<<<MAX_WIDTH>>>]px"><a href="' . tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $value['gtext_id']) . '">' . $value['gtext_title'] . '</a></div>' . "\n";
        $count++;
        if( $count > $max_drop ) {
          $max_drop = $count;
          $max_width = $swidth;
          return $sub_string;
        }
      }
      $max_width = $swidth;
      return $sub_string;
    }

    function get_image_css_tree($id, &$max_drop, &$max_width) {
      $sub_string = "";
      $count = $swidth = 0;

      $cImage = new image_front();
      $entries_array = $cImage->get_entries($id, true, false);
      if( !count($entries_array) ) return;

      foreach($entries_array as $key => $value) {
        $value['image_alt_title'] = strtoupper($value['image_alt_title']);
        $awidth = (tep_string_length($value['image_alt_title'])*9)+16;
        $value['image_alt_title'] = htmlspecialchars(stripslashes($value['image_alt_title']));

        if( $awidth > $max_width ) {
          $string_length = (int)($max_width/9);
          $value['image_alt_title'] = substr($value['image_alt_title'], 0, $string_length) . '...';
          $awidth = $max_width;
        }
        if( $awidth > $swidth ) $swidth = $awidth;

        $sub_string .= '    <div class="css_sub" style="width: [<<<MAX_WIDTH>>>]px"><a href="' . tep_href_image_link($value['image_file']) . '" target="_blank">' . $value['image_alt_title'] . '</a></div>' . "\n";
        $count++;
        if( $count > $max_drop ) {
          $max_drop = $count;
          $max_width = $swidth;
          return $sub_string;
        }
      }
      $max_width = $swidth;
      return $sub_string;
    }

    function get_mixed_css_tree($id, &$max_drop, &$max_width) {
      $sub_string = '';
      $count = $swidth = 0;

      $cSuper = new super_front();
      $super_entries = $cSuper->get_entries($id, true);
      if( !count($super_entries) ) return $sub_string;

      foreach($super_entries as $key => $value) {
        $value['sub_alt_title'] = strtoupper($value['sub_alt_title']);
        $awidth = (tep_string_length($value['sub_alt_title'])*9)+16;
        $value['sub_alt_title'] = htmlspecialchars(stripslashes($value['sub_alt_title']));

        if( $awidth > $max_width ) {
          $string_length = (int)($max_width/9);
          $value['sub_alt_title'] = substr($value['sub_alt_title'], 0, $string_length) . '...';
          $awidth = $max_width;
        }
        if( $awidth > $swidth ) $swidth = $awidth;

        $zone_class = $cSuper->get_zone_class($key);
        switch($zone_class) {
          case 'image_zones':
            $script = FILENAME_IMAGE_PAGES;
            $process_function = 'tep_get_image_css_tree';
            break;
          case 'super_zones':
            $script = FILENAME_SUPER_PAGES;
            $process_function = 'tep_get_mixed_css_tree';
            break;
          default:
            $script = FILENAME_GENERIC_PAGES;
            $process_function = 'tep_get_text_css_tree';
            break;
        }

        $sub_string .= '    <div class="css_sub" style="width: [<<<MAX_WIDTH>>>]px"><a href="' . tep_href_link($script, 'abz_id=' . $value['subzone_id']) . '">' . $value['sub_alt_title'] . '</a></div>' . "\n";
        $count++;
        if( $count > $max_drop ) {
          $max_drop = $count;
          $max_width = $swidth;
          return $sub_string;
        }
      }
      $max_width = $swidth;
      return $sub_string;
    }
  }
?>
