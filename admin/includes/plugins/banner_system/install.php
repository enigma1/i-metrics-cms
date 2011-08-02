<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Install class for the banners system
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
  class install_banner_system extends plug_manager {
    // Compatibility constructor
    function install_banner_system() {
      parent::plug_manager();
      tep_define_vars($this->admin_path . 'back/admin_defs.php');

      // Default Options
      $this->options_array = array(
        'banners_path'         => 'images/banners/',
        'display_left'         => 1, 
        'display_right'        => 1, 
        'display_top'          => 1, 
        'display_bottom'       => 1, 
        'clicks'               => 1,
        'impressions'          => 1,
        'template'             => 'stock',
        'strings'              => 'front',
        'abox'                 => true,
        'ascripts'             => array(FILENAME_DEFAULT, FILENAME_LANGUAGES_SYNC, FILENAME_BANNERS)
      );

      $this->title = 'Banner System';
      $this->author = 'Mark Samios';
      $this->icon = 'banner.png';
      $this->version = '1.00';
      $this->framework = '1.12';
      $this->help = '';
      tep_read_contents($this->admin_path.'readme.txt', $this->help);
      $this->front = 1;
      $this->back = 1;
      $this->status = 1;

      $this->template_path = 'front/templates/';

      // The array of files that operate on the web-front
      // Left(Key)     => Source File with Path (to copy file from)
      // Right(Value)  => Destination Path and File (to copy source file to)
      $this->files_array = array(
        'front/banner_system.php'     => $this->web_path . 'banner_system.php',
        'front/defs.php'              => $this->web_path . 'defs.php',
        'front/banner_system.js'      => $this->web_path . 'banner_system.js',
        'front/launcher.tpl'          => $this->web_path . 'launcher.tpl',
      );

      // The array of files that operate on the administration end
      // Left(Key)     => Source Path/File (to copy file from)
      // Right(Value)  => Destination Path only (to copy source file to)
      $this->admin_files_array = array(
        'back/admin_banners.php'           => 'banners.php',
        'back/admin_banners_strings.php'   => DIR_WS_STRINGS.'banners.php',
      );

      // Common Template filenames
      $this->template_array = array(
        //'web_strings.php'          => $this->web_template_path.'web_strings.php',
        'banner.tpl'               => $this->web_template_path.'banner.tpl',
      );

      $this->front_strings_array = array(
        'web_strings.php'
      );

      $this->sql_file = $this->admin_path . 'database.sql';
      $this->config_form = $this->admin_path . 'config_form.tpl';
      $this->strings = tep_get_strings($this->admin_path . 'strings.php');
    }

    function set_options() {
      extract(tep_load('defs'));

      $cStrings =& $this->strings;

      // Read the plugin store options into an array
      $options_array = $this->load_options();

      $banners_path           = $options_array['banners_path'];
      $impressions            = $options_array['impressions']?1:0;
      $clicks                 = $options_array['clicks']?1:0;
      $display_left           = $options_array['display_left']?1:0;
      $display_right          = $options_array['display_right']?1:0;
      $display_top            = $options_array['display_top']?1:0;
      $display_bottom         = $options_array['display_bottom']?1:0;

      $html_string = '';
      if( !is_file($this->config_form) ) {
        $html_string = sprintf($cStrings->ERROR_PLUGIN_INVALID_CONFIG_TPL, $this->config_form);
        return $html_string;
      }
      require_once($this->config_form);
      return $html_string;
    }

    function process_options() {
      extract(tep_load('defs', 'database', 'message_stack'));

      $cStrings =& $this->strings;
      $banners_path = $db->prepare_input($_POST['banners_path']);
      $banners_path = str_replace('\\', '/', $banners_path);
      $banners_path = ltrim($banners_path, '/.');
      $path = tep_front_physical_path(DIR_WS_CATALOG . $banners_path);

      if( !empty($banners_path) ) {
        $result = tep_mkdir($path);
        if( !$result ) {
          $msg->add_session(sprintf(ERROR_CREATE_DIR, $banners_path) );
          tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=set_options'));
        }
      }
      // Prepare the options array for storage
      $options_array = $this->load_options();
      $options_array['banners_path']   = $banners_path;
      $options_array['impressions']    = (isset($_POST['impressions'])?1:0);
      $options_array['clicks']         = (isset($_POST['clicks'])?1:0);
      $options_array['display_left']   = (isset($_POST['display_left'])?1:0);
      $options_array['display_right']  = (isset($_POST['display_right'])?1:0);
      $options_array['display_top']    = (isset($_POST['display_top'])?1:0);
      $options_array['display_bottom'] = (isset($_POST['display_bottom'])?1:0);

      // Store user options
      $this->save_options($options_array);
      $msg->add_session(sprintf($cStrings->SUCCESS_PLUGIN_RECONFIGURED, $this->title), 'success');
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=set_options'));
    }

    function install() {
      extract(tep_load('database', 'message_stack'));
      $cStrings =& $this->strings;

      $result = false;
      $this->delete_references();

      $result = $db->file_exec($this->sql_file);
      if( !$result ) {
        $msg->add_session(sprintf($cStrings->ERROR_INVALID_DATABASE_FILE, $this->sql_file));
        return $result;
      }

      $this->create_references();
      $this->set_posted_template();
      $result = parent::install();
      $this->save_options($this->options_array);
      return $result;
    }

    function uninstall() {
      $options_array = $this->load_options();
      $this->load_template_files($options_array['template']);
      $this->delete_references();
      parent::uninstall();
      return true;
    }

    function pre_install() {
      return $this->common_select();
    }
    function pre_copy_front() {
      return $this->common_select();
    }
    function pre_revert() {
      return $this->common_select(true);
    }
    function pre_uninstall() {
      $options_array = $this->load_options();
      $this->load_template_files($options_array['template']);
      return true;
    }
    function re_copy_front() {
      $this->set_posted_template();
      return parent::re_copy_front();
    }
    function revert_files() {
      $this->set_posted_template(false);
      return parent::revert_files();
    }

    function common_select($revert=false) {
      $cStrings =& $this->strings;

      $tmp_array = $this->get_templates();
      if( !count($tmp_array) ) return false;

      echo '<div class="comboHeading">' . "\n";
      echo '<label class="heavy" for="template_select">' . $cStrings->TEXT_SELECT_TEMPLATE . '</label><span class="hpad">' . tep_draw_pull_down_menu('template', $tmp_array, $this->options_array['template'], 'id="template_select"') . '</span>';
      if( $revert ) {
        echo $cStrings->TEXT_ADDITIONAL_TEMPLATE_FILES_REV;
      } else {
        echo $cStrings->TEXT_ADDITIONAL_TEMPLATE_FILES;
      }
      echo '</div>' . "\n";
      return true;
    }

    function delete_references() {
      extract(tep_load('languages', 'database'));

      $tables_array = $db->get_tables();
      if( isset($tables_array[TABLE_BANNERS]) ) {
        $files_query_raw = "select filename from " . TABLE_BANNERS . " where filename != ''";
        $files_array = $db->query_to_array($files_query_raw, false, false);
        $path = tep_front_physical_path(DIR_WS_CATALOG);
        for( $i=0, $j=count($files_array); $i<$j; $i++) {
          if( is_file($path . $files_array[$i]['filename']) ) {
            @unlink($path . $files_array[$i]['filename']);
          }
        }
      }

      $lng->delete_table('TABLE_BANNERS');

      $db->query("drop table if exists " . TABLE_BANNERS_GROUP);
      $db->query("drop table if exists " . TABLE_BANNERS);
    }

    function create_references() {
      extract(tep_load('database', 'languages'));

      $options_array = $this->load_options();
      //$lng->create_plugin_folders($this);

      $lng->create_table('TABLE_BANNERS');

      $result = true;
      $dir = trim($options_array['banners_path'], '/');
      $path = tep_front_physical_path(DIR_WS_CATALOG . $dir);

      if( !empty($dir) ) {
        $result = tep_mkdir($path);
      }

      $path = tep_front_physical_path(DIR_WS_CATALOG);
      $files_query_raw = "select filename from " . TABLE_BANNERS . " where filename != ''";
      $files_array = $db->query_to_array($files_query_raw, false, false);
      for($i=0, $j=count($files_array); $i<$j; $i++) {
        $src = $this->admin_path . 'front/images/' . basename($files_array[$i]['filename']);
        $dst = $path . $files_array[$i]['filename'];
        if( is_file($src) ) {
          copy($src, $dst);
        }
      }
      return $result;
    }

    function backup_database() {
      extract(tep_load('languages', 'database', 'database_backup', 'message_stack'));

      $path = tep_front_physical_path(DIR_WS_CATALOG);

      $files_query_raw = "select filename from " . TABLE_BANNERS;
      $files_array = $db->query_to_array($files_query_raw, false, false);

      for($i=0, $j=count($files_array); $i<$j; $i++) {
        $src = $path . $files_array[$i]['filename'];
        $dst = $this->admin_path . 'front/images/' . basename($files_array[$i]['filename']);

        if( is_file($src) ) {
          copy($src, $dst);
        }
      }

      $tables_array = array(
        TABLE_BANNERS_GROUP
      );
      
      $tmp_array = $lng->get_language_tables(TABLE_BANNERS);
      $tables_array = array_merge($tables_array, $tmp_array);
      $database_backup->save_tables($this->admin_path . 'database.sql', $tables_array);
      $msg->add_session(sprintf(SUCCESS_PLUGIN_DATABASE_BACKUP, $db->prepare_input($this->title)), 'success');
      return true;
    }
  }
?>
