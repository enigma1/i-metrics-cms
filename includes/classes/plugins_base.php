<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
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
    var $key;
    var $active=true;
    var $scripts_array = array();
    var $strings_array = array();

    // Compatibility constructor
    function plugins_base() {
      $this->site_index = tep_create_safe_string(tep_get_site_path(), '_', "/[^0-9a-z\-_]+/i");
      $this->key = get_class($this);
      $this->web_path = DIR_WS_PLUGINS.$this->key.'/';
      $this->web_template_path = DIR_WS_TEMPLATE.$this->key.'/';
      $this->active = $this->scripts_check();
    }

    // Retrieve plugin configuration data from the database
    function load_options($all=false) {
      global $g_db;

      $key = get_class($this);
      $index = $this->site_index;
      $plugins_query = $g_db->query("select plugins_data from " . TABLE_PLUGINS . " where plugins_key = '" . $g_db->filter($key) . "'");
      $plugins_array = $g_db->fetch_array($plugins_query);
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
      global $g_db;

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
      $g_db->query("update " . TABLE_PLUGINS . " set plugins_data = '" . $g_db->filter($store_data) . "' where plugins_key = '" . $g_db->filter($key) . "'");
    }

    function scripts_check() {
      global $g_script;
      if( empty($this->scripts_array) ) return true;
      return in_array($g_script, $this->scripts_array);
    }

    function change($state) {
      $this->active = ($state==true);
    }
  }
?>
