<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Run-Time System Base Class
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
  class system_base {

    // Compatibility constructor
    function system_base() {}

    function ajax_start() {
      extract(tep_load('defs'));
      if( empty($cDefs->action) ) return false;
      $method = 'get_' . $cDefs->action;
      if( !method_exists($this, $method) ) {
        return false;
      }
      return $this->$method();
    }

    function get_help() {
      extract(tep_load('defs', 'database', 'sessions'));

      $help = (isset($_GET['ajax']) && !empty($_GET['ajax']))?$db->prepare_input($_GET['ajax']):'';
      if( empty($help) ) return false;

      if( strpos($help, '_') === false ) {
        $help = tep_get_script_name() . '_' . $help;
      }

      $template_query = $db->query("select template_content from " . TABLE_TEMPLATES . " where group_id = " . TEMPLATE_SYSTEM_GROUP . " and template_title='" . $db->input($help) . "'");
      if( !$db->num_rows($template_query) ) return false;

      $template_array = $db->fetch_array($template_query);
      echo '<div>' . $template_array['template_content'] . '</div>';
      $cSessions->close();
      return true;
    }

    function get_system_help_title($postfix) {
      extract(tep_load('database'));

      $result = TEXT_INFO_NA;

      $help = $postfix;
      if( strpos($postfix, '_') === false ) {
        $help = tep_get_script_name() . '_' . $postfix;
      }

      $template_query = $db->query("select template_subject from " . TABLE_TEMPLATES . " where group_id = " . TEMPLATE_SYSTEM_GROUP . " and template_title='" . $db->input($help) . "'");
      if( !$db->num_rows($template_query) ) return $result;

      $template_array = $db->fetch_array($template_query);
      $result = $template_array['template_subject'];
      return $result;
    }

    function set_get_array() {
      extract(tep_load('defs'));

      $args = func_get_args();
      foreach( $args as $key ) {
        if( !isset($_GET[$key]) ) {
          continue;
        }
        $cDefs->link_params[$key] = $_GET[$key];
      }
      $_GET = $cDefs->link_params;
    }
  }
?>
