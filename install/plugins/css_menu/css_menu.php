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
*/
  class css_menu extends plugins_base {
    // Compatibility constructor
    function css_menu() {
      parent::plugins_base();
      $this->options = $this->load_options();
      $this->menu_template = $this->fs_template_path . 'css_menu.tpl';
    }

    function html_start() {
      extract(tep_load('defs'));
      $cDefs->media[] = '<link rel="stylesheet" type="text/css" href="' . $this->web_template_path . 'css_menu.css" />';
      return true;
    }

    function html_menu() {
      $cSuper = new super_front;
      $zones_array = $cSuper->get_entries(SUPER_MENU_ZONE_ID);
      $menu_string = $this->get_super_css_tree($zones_array);
      if( empty($menu_string) || !is_file($this->menu_template) ) return false;
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
        $script = FILENAME_COLLECTIONS;
        switch($zone_class) {
          case 'image_zones':
            $process_function = 'get_image_css_tree';
            break;
          case 'super_zones':
            $process_function = 'get_mixed_css_tree';
            break;
          default:
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
        $tmp_array = $this->$process_function($id, $tmp_drop, $max_width);
        if( !empty($tmp_array) ) { 

          $extra_style = ' style="width: ' . $max_width . 'px"';
          for($i=1, $width=0; $i<=$this->options['max_cols']; $i++) {
            if( (int)($tmp_drop/$i) <= $this->options['max_drop'] ) {
              break;
            }
            $width = $i;
          }

          if( $i > 1) {
            if( $width > $this->options['max_cols'] ) {
              $width = $this->options['max_cols'];
            }
            $extra_style = ' style="width: ' . ($width*($max_width+$this->options['border_width'])) . 'px;"';
          }

          if( $tmp_drop != $this->options['max_drop'] ) {
            $tmp_array = array_slice($tmp_array, 0, $this->options['max_drop']*$this->options['max_cols']);
            $tmp_string = '<div class="css_sub hideflow"';
            if( $i > 1 ) {
              $tmp_string .= ' style="width: ' . $max_width . 'px"';
            }
            $tmp_string .= '><a href="' . tep_href_link($script, 'abz_id=' . $id) . '">' . TEXT_SEE_ALL . '</a></div>';
            $tmp_array[] = $tmp_string;
          }

          $tmp_string = implode('    ', $tmp_array);
          $max_width_string = '';

          if( $i > 1) {
            $max_width_string = ' style="width: ' . $max_width . 'px;"';
          }

          $contents_array = array(
            'MAX_WIDTH' => $max_width_string,
          );

          $tmp_string = tep_templates_replace_entities($tmp_string, $contents_array);
          $final_string .=
          '  <div class="css_section"' . $extra_style . '>' . "\n" . $tmp_string . '  </div>' . "\n";
        }
        $final_string .=
          '</div>' . "\n";

        if( $index < $total ) {
          $last_sep = '    <div class="css_sep hideflow">' . tep_image($this->web_template_path . 'words_space.gif') . '</div>' . "\n";
          $final_string .= $last_sep;
        }
      }
      return $final_string;
    }

    function get_text_css_tree($id, &$max_drop, &$max_width) {
      $sub_array = array();
      $count = $swidth = 0;
      
      $cText = new gtext_front();
      $text_entries = $cText->get_entries($id, true, false);
      if( !count($text_entries) ) return $sub_array;

      foreach($text_entries as $key => $value) {
        $value['gtext_title'] = strtoupper($value['gtext_title']);

        $tmp_value = str_replace(' ', '', $value['gtext_title']);
        $awidth = (tep_string_length($tmp_value)*9)+16;

        $value['gtext_title'] = htmlspecialchars(stripslashes($value['gtext_title']));

        if( $awidth > $max_width ) {
          $string_length = (int)($max_width/9);
          $value['gtext_title'] = substr($value['gtext_title'], 0, $string_length) . '...';
          $awidth = $max_width;
        }
        if( $awidth > $swidth ) $swidth = $awidth;
        $sub_array[] = '<div class="css_sub hideflow"[<<<MAX_WIDTH>>>]><a href="' . tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $value['gtext_id']) . '">' . $value['gtext_title'] . '</a></div>' . "\n";
        $count++;

        if( $count > $max_drop ) {
          $max_drop = $count;
          //$max_width = $swidth;
          //return $sub_array;
        }
      }
      $max_width = $swidth;
      return $sub_array;
    }

    function get_image_css_tree($id, &$max_drop, &$max_width) {
      $sub_array = array();
      $count = $swidth = 0;

      $cImage = new image_front();
      $entries_array = $cImage->get_entries($id, true, false);
      if( !count($entries_array) ) return;

      foreach($entries_array as $key => $value) {
        $value['image_alt_title'] = strtoupper($value['image_alt_title']);
        $tmp_value = str_replace(' ', '', $value['image_alt_title']);
        $awidth = (tep_string_length($tmp_value)*9)+16;
        $value['image_alt_title'] = htmlspecialchars(stripslashes($value['image_alt_title']));

        if( $awidth > $max_width ) {
          $string_length = (int)($max_width/9);
          $value['image_alt_title'] = substr($value['image_alt_title'], 0, $string_length) . '...';
          $awidth = $max_width;
        }
        if( $awidth > $swidth ) $swidth = $awidth;

        $sub_array[] = '<div class="css_sub hideflow"[<<<MAX_WIDTH>>>]><a href="' . tep_href_image_link($value['image_file']) . '" target="_blank">' . $value['image_alt_title'] . '</a></div>' . "\n";
        $count++;
        if( $count > $max_drop ) {
          $max_drop = $count;
          //$max_width = $swidth;
          //return $sub_array;
        }
      }
      $max_width = $swidth;
      return $sub_array;
    }

    function get_mixed_css_tree($id, &$max_drop, &$max_width) {
      $sub_array = array();
      $count = $swidth = 0;

      $cSuper = new super_front();
      $super_entries = $cSuper->get_entries($id, true);
      if( !count($super_entries) ) return $sub_array;

      foreach($super_entries as $key => $value) {
        $value['sub_alt_title'] = strtoupper($value['sub_alt_title']);

        $tmp_value = str_replace(' ', '', $value['sub_alt_title']);
        $awidth = (tep_string_length($tmp_value)*9)+16;

        $value['sub_alt_title'] = htmlspecialchars(stripslashes($value['sub_alt_title']));

        if( $awidth > $max_width ) {
          $string_length = (int)($max_width/9);
          $value['sub_alt_title'] = substr($value['sub_alt_title'], 0, $string_length) . '...';
          $awidth = $max_width;
        }
        if( $awidth > $swidth ) $swidth = $awidth;

        $zone_class = $cSuper->get_zone_class($key);
        $script = FILENAME_COLLECTIONS;
        switch($zone_class) {
          case 'image_zones':
            $process_function = 'tep_get_image_css_tree';
            break;
          case 'super_zones':
            $process_function = 'tep_get_mixed_css_tree';
            break;
          default:
            $process_function = 'tep_get_text_css_tree';
            break;
        }

        $sub_array[] = '<div class="css_sub floater"[<<<MAX_WIDTH>>>]><a href="' . tep_href_link($script, 'abz_id=' . $value['subzone_id']) . '">' . $value['sub_alt_title'] . '</a></div>' . "\n";
        $count++;
        if( $count > $max_drop ) {
          $max_drop = $count;
          //$max_width = $swidth;
          //return $sub_array;
        }
      }
      $max_width = $swidth;
      return $sub_array;
    }
  }
?>
