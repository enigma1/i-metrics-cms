<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Home page index.php script
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
  class admin_index extends system_base {
    // Compatibility constructor
    function admin_index() {}

    function html_start() {
      extract(tep_load('defs'));
      //$cDefs->media[] = '<script src="includes/javascript/jquery/jquery-css-transform.js" type="text/javascript"></script>';
      //$cDefs->media[] = '<script src="includes/javascript/jquery/jquery-animate-css-rotate-scale.js" type="text/javascript"></script>';
      //$cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.easing.js"></script>';
      $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.quicksand.js"></script>';
      $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/home.js"></script>';
      return true;
    }

    function html_end() {
      extract(tep_load('defs'));

      $script_name = tep_get_script_name();

      ob_start();
      require(PLUGINS_ADMIN_PREFIX . $script_name . '.tpl');
      $contents = ob_get_contents();
      ob_end_clean();
      $cDefs->media[] = $contents;

/*
      $contents = '';
      $launcher = DIR_FS_PLUGINS . 'common_help.tpl';
      $result = tep_read_contents($launcher, $contents);
      if( !$result ) return false;

      $contents_array = array(
        'POPUP_TITLE' => HEADING_HELP_TITLE,
        'POPUP_SELECTOR' => 'div.help_page a.' . $script_name,
      );
      $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);
*/
      return true;
    }

    function get_index_plugins() {
      extract(tep_load('sessions', 'plugins_admin'));
      require(DIR_FS_MODULES . 'index_plugins.php');
      $cSessions->close();
      return true;
    }

    function get_index_content() {
      extract(tep_load('sessions', 'plugins_admin'));
      require(DIR_FS_MODULES . 'index_content.php');

      $cSessions->close();
      return true;
    }

    function get_index_marketing() {
      extract(tep_load('sessions', 'plugins_admin'));
      require(DIR_FS_MODULES . 'index_marketing.php');
      $cSessions->close();
      return true;
    }

    function get_index_configuration() {
      extract(tep_load('sessions', 'plugins_admin'));
      require(DIR_FS_MODULES . 'index_configuration.php');
      $cSessions->close();
      return true;
    }

    function get_index_helpdesk() {
      extract(tep_load('sessions', 'plugins_admin'));
      require(DIR_FS_MODULES . 'index_helpdesk.php');
      $cSessions->close();
      return true;
    }

    function get_index_tools() {
      extract(tep_load('sessions', 'plugins_admin'));
      require(DIR_FS_MODULES . 'index_tools.php');
      $cSessions->close();
      return true;
    }

    function get_index_languages() {
      extract(tep_load('sessions', 'plugins_admin'));
      require(DIR_FS_MODULES . 'index_languages.php');
      $cSessions->close();
      return true;
    }

    function get_index_cache() {
      extract(tep_load('sessions', 'plugins_admin'));
      require(DIR_FS_MODULES . 'index_cache.php');
      $cSessions->close();
      return true;
    }

    function get_index_main() {
      extract(tep_load('sessions', 'plugins_admin'));
      require(DIR_FS_MODULES . 'index_main.php');
      $cSessions->close();
      return true;
    }

  }
?>
