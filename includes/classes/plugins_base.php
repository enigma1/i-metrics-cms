<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Plugins Runtime Base Class
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
  class plugins_base {

    // Compatibility constructor
    function plugins_base() {
      extract(tep_load('languages'));

      $this->scripts_array = $this->strings_array = array();
      $this->site_index = tep_create_safe_string(tep_get_site_path(), '_', "/[^0-9a-z\-_]+/i");

      $this->key = get_class($this);
      $key_path = tep_trail_path($this->key);
      $this->web_path = DIR_WS_PLUGINS . $key_path;
      $this->fs_path = DIR_FS_PLUGINS . $key_path;
      $this->web_template_path = DIR_WS_TEMPLATE . $key_path;
      $this->fs_template_path = DIR_FS_TEMPLATE . $key_path;
      $this->fs_language_path = DIR_FS_STRINGS . tep_trail_path($lng->path) . $key_path;
      $this->active = $this->scripts_check();
    }

    function load_strings($files_array) {
      $strings_array = array();
      foreach($files_array as $value) {
        $file = $this->fs_language_path . $value;
        $strings_array = array_merge($strings_array, tep_get_strings_array($file));
      }
      return new objectInfo($strings_array, false);
    }

    // Retrieve plugin configuration data from the database
    function load_options($all=false) {
      extract(tep_load('database'));

      $key = get_class($this);
      $index = $this->site_index;
      $plugins_query = $db->query("select plugins_data from " . TABLE_PLUGINS . " where plugins_key = '" . $db->filter($key) . "'");
      $plugins_array = $db->fetch_array($plugins_query);
      $plugins_data = array();
      if( !empty($plugins_array['plugins_data']) ) {
        $tmp_data = unserialize($plugins_array['plugins_data']);
        if( isset($tmp_data[$index]) ) {
          $plugins_data = $tmp_data[$index];
        }
      }
      if( !$all && isset($plugins_data[$index]) ) {
        $plugins_data = $plugins_data[$index];
      }
      return $plugins_data;
    }

    // Save plugin configuration data from the database
    function save_options($input_array) {
      extract(tep_load('database'));

      $key = get_class($this);
      $index = $this->site_index;
      $plugins_data = $this->load_options(true);

      if( !isset($plugins_data[$index]['status_id']) ) {
        $plugins_data[$index]['status_id'] = 0;
      }
      if( !isset($plugins_data[$index]['sort_id']) ) {
        $plugins_data[$index]['sort_id'] = 100;
      }

      if( !isset($input_array['status_id']) ) {
        $input_array['status_id'] = $plugins_data[$index]['status_id'];
      }
      if( !isset($input_array['sort_id']) ) {
        $input_array['sort_id'] = $plugins_data[$index]['sort_id'];
      }
      $plugins_data[$index] = $input_array;
      $store_data = serialize($plugins_data);
      $db->query("update " . TABLE_PLUGINS . " set plugins_data = '" . $db->filter($store_data) . "' where plugins_key = '" . $db->filter($key) . "'");
    }

    function scripts_check() {
      extract(tep_load('defs'));

      if( empty($this->scripts_array) ) return true;
      return in_array($cDefs->script, $this->scripts_array);
    }

    function change($state) {
      $this->active = ($state==true);
    }
  }
?>
