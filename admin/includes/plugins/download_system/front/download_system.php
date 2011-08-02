<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Download processing script
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
  class download_system extends plugins_base {

    // Compatibility constructor
    function download_system() {
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      // Load the plugin options
      $this->options = $this->load_options();

      // Load the plugin strings
      $this->strings = tep_get_strings($this->fs_template_path . 'web_strings.php');
      // Load the database tables for the box content
      tep_define_vars($this->fs_path . 'tables.php');

      // Check the templates if the files are not present disable the plugin
      $this->form_box = $this->fs_template_path . 'download_form.tpl';
      $this->form_text = $this->fs_template_path . 'download_text.tpl';

      if( !is_file($this->form_box) || !is_file($this->form_text) ) {
        $this->change(false);
      }

      $this->form_name = 'process_download_system_box';
      $this->download_entries = array();
    }

    function plugin_form_process() {
      extract(tep_load('defs', 'database', 'sessions'));

      $down_id = isset($_GET['down_id'])?(int)$_GET['down_id']:0;
      if( empty($down_id) ) return false;
      if( !tep_check_submit($this->form_name . '_' . $down_id) ) return false;

      $check_query = $db->query("select filename from " . TABLE_DOWNLOAD . " where auto_id = '" . (int)$down_id . "' and status_id='1'");
      if( !$db->num_rows($check_query) ) return false;

      $check_array = $db->fetch_array($check_query);
      $filename = $check_array['filename'];

      if( !empty($filename) && is_file($filename) ) {
        header('Content-type: application/x-octet-stream');
        header('Content-disposition: attachment; filename=' . $filename);
        readfile($filename);
      }
      $cSessions->close();
      return true;
    }

    function html_start() {
      $this->download_entries = $this->related_entries();
    }

    function html_left() {
      if( empty($this->download_entries) || $this->options['display_col'] != 0 ) return false;
      $this->display_common_content($this->download_entries, $this->options['display_col']);
      return true;
    }

    function html_right() {
      if( empty($this->download_entries) || $this->options['display_col'] != 1 ) return false;
      $this->display_common_content($this->download_entries, $this->options['display_col']);
      return true;
    }

    function html_content_bottom() {
      if( empty($this->download_entries) || $this->options['display_col'] != 2 ) return false;
      $this->display_common_content($this->download_entries, $this->options['display_col']);
      return true;
    }

    function display_common_content($input_array, $pos) {

      $cStrings =& $this->strings;
      $method = $this->options['download_method'];

      for($i=0, $j=count($input_array); $i<$j; $i++) {
        $name = $input_array[$i]['content_name'];
        $text = $input_array[$i]['content_text'];
        $filename = $input_array[$i]['filename'];
        $downloads = $input_array[$i]['downloads'];
        // Clear empty entries
        if( empty($filename) || !is_file(tep_path($filename)) ) {
          unset($input_array[$i]);
        }
      }

      if( $pos == 0 || $pos == 1 ) {
        require($this->form_box);
      } else {
        require($this->form_text);
      }
    }

    function related_entries() {
      extract(tep_load('defs', 'database', 'sessions'));

      $content_id = $type_id = 0;

      if( $cDefs->gtext_id && $this->options['text_pages'] ) {
        $content_id = $cDefs->gtext_id;
        $type_id = 1;
      } elseif($cDefs->abstract_id && $this->options['collections'] ) {
        $content_id = $cDefs->abstract_id;
        $type_id = 2;
      }

      if( !$type_id ) return false;

      $box_query_raw = "select auto_id, content_name, content_text, filename, downloads from " . TABLE_DOWNLOAD . " where content_id = '" . (int)$content_id . "' and content_type='" . (int)$type_id . "' and status_id='1' order by sort_id";
      $box_items = $db->query_to_array($box_query_raw);
      for($i=0, $j=count($box_items); $i<$j; $i++) {
        if( $this->options['download_method'] == 'post')  {
          if( $type_id == 1 ) {
            $box_items[$i]['href'] = tep_href_link(FILENAME_GENERIC_PAGES, 'action=plugin_form_process&gtext_id=' . $cDefs->gtext_id . '&down_id=' . $box_items[$i]['auto_id'] );
          } elseif( $type_id == 2 ) {
            $box_items[$i]['href'] = tep_href_link(FILENAME_COLLECTIONS, 'action=plugin_form_process&abz_id=' . $cDefs->abstract_id . '&down_id=' . $box_items[$i]['auto_id'] );
          }
        } else {
          $box_items[$i]['href'] = $cDefs->relpath . $box_items[$i]['filename'];
        }
      }
      return $box_items;
    }
  }
?>
