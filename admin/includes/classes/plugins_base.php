<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Run-Time Plugins Base Class
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
    var $key, $admin_path;

    // Compatibility constructor
    function plugins_base() {
      $this->site_index = tep_create_safe_string(tep_get_site_path(), '_', "/[^0-9a-z\-_]+/i");
      $string = get_class($this);
      $this->key = substr($string, strlen(PLUGINS_ADMIN_PREFIX));
      $this->admin_path = DIR_WS_PLUGINS.$this->key.'/';
    }

    // Retrieve plugin configuration data from the database
    function load_options($all=false) {
      global $g_db;

      $index = $this->site_index;
      $plugins_query = $g_db->query("select plugins_data from " . TABLE_PLUGINS . " where plugins_key = '" . $g_db->filter($this->key) . "'");
      $plugins_array = $g_db->fetch_array($plugins_query);
      $plugins_data = array();
      if( !empty($plugins_array['plugins_data']) ) {
        $plugins_data = unserialize($plugins_array['plugins_data']);
      }
      if( !$all && isset($plugins_data[$index]) ) {
        $plugins_data = $plugins_data[$index];
      }
      return $plugins_data;
    }

    // Save plugin configuration data from the database
    function save_options($input_array) {
      global $g_db;

      $index = $this->site_index;
      $plugins_data = $this->load_options(true);

      if( !isset($input_array['status_id']) ) {
        $input_array['status_id'] = $plugins_data[$index]['status_id'];
      }
      if( !isset($input_array['sort_id']) ) {
        $input_array['sort_id'] = $plugins_data[$index]['sort_id'];
      }
      $plugins_data[$index] = $input_array;
      $store_data = serialize($plugins_data);

      $g_db->query("update " . TABLE_PLUGINS . " set plugins_data = '" . $g_db->filter($store_data) . "' where plugins_key = '" . $g_db->filter($this->key) . "'");
    }
  }
?>
