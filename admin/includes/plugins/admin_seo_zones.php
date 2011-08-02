<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: SEO-G Zones
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
  class admin_seo_zones {
    // Compatibility constructor
    function admin_seo_zones() {}

    function ajax_start() {
      extract(tep_load('defs'));
      if( empty($cDefs->action) ) return false;
      $method = 'get_' . $cDefs->action;
      if( !method_exists($this, $method) ) return false;
      return $this->$method();
    }

    function html_start() {
      tep_set_lightbox();
      return true;
    }

    function html_end() {
      extract(tep_load('defs'));
      $script_name = tep_get_script_name();

      $contents = '';
      $launcher = DIR_FS_PLUGINS . 'common_help.tpl';
      $result = tep_read_contents($launcher, $contents);
      if( !$result ) return false;

      $contents_array = array(
        'POPUP_TITLE' => HEADING_HELP_TITLE,
        'POPUP_SELECTOR' => 'div.help_page a.' . $script_name,
      );
      $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);
      return true;
    }

    function get_help() {
      extract(tep_load('defs', 'database', 'sessions'));

      $result = false;
      $zone_script = '';
      if( isset($_GET['zID']) && tep_not_null($_GET['zID']) ) {
        $zone_query = $db->query("select seo_types_class from " . TABLE_SEO_TYPES . " where seo_types_id = '" . (int)$_GET['zID'] . "'");
        if( $db->num_rows($zone_query) ) {
          $zone_array = $db->fetch_array($zone_query);
          $zone_script = $zone_array['seo_types_class'];
        }
      }

      if( !empty($zone_script) ) {
        $file = DIR_FS_STRINGS . 'help/' . $zone_script . '.php';
      } else {
        $file = DIR_FS_STRINGS . 'help/' . $cDefs->script;
      }

      $result = tep_read_contents($file, $contents);
      if( !$result ) return $result;

      echo $contents;
      $cSessions->close();
      return true;
    }
  }
?>
