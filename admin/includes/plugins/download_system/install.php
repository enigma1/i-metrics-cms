<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Install class for the download system
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
  class install_download_system extends plug_manager {

    // Compatibility constructor
    function install_download_system() {

      parent::plug_manager();
      tep_define_vars($this->admin_path . 'back/admin_defs.php');
      $front_defs = tep_web_files('FILENAME_GENERIC_PAGES', 'FILENAME_COLLECTIONS');

      // Default Options
      $this->options_array = array(
        'text_pages'           => 1,
        'collections'          => 1,
        'display_col'          => 1, 
        'download_count'       => 1,
        'download_count_show'  => 1,
        'download_path'        => 'pub/',
        'download_method'      => 'get',
        'default_status'       => 1,
        'template'             => 'stock',
        'abox'                 => true,
        'ascripts'             => array(FILENAME_DEFAULT, FILENAME_DOWNLOAD),
        'fscripts'             => array_values($front_defs)
      );

      $this->title = 'Download System';
      $this->author = 'Mark Samios';
      $this->icon = 'download.png';
      $this->version = '1.00';
      $this->framework = '1.12';
      tep_read_contents($this->admin_path.'readme.txt', $this->help);
      $this->front = 1;
      $this->back = 1;
      $this->status = 1;

      $this->template_path = 'front/templates/';

      // The array of files that operate on the web-front
      // Left(Key)     => Source File with Path (to copy file from)
      // Right(Value)  => Destination Path and File (to copy source file to)
      $this->files_array = array(
        'front/download_system.php'       => $this->web_path.'download_system.php',
        'front/tables.php'                => $this->web_path.'tables.php',
      );

      // The array of files that operate on the administration end
      // Left(Key)     => Source Path/File (to copy file from)
      // Right(Value)  => Destination Path only (to copy source file to)
      $this->admin_files_array = array(
        'back/admin_download.php'           => 'download.php',
        'back/admin_download_strings.php'   => DIR_WS_STRINGS.'download.php',
      );

      // Common Template filenames
      $this->template_array = array(
        'web_strings.php'          => $this->web_template_path.'web_strings.php',
        'download_form.tpl'        => $this->web_template_path.'download_form.tpl',
        'download_text.tpl'        => $this->web_template_path.'download_text.tpl',
        //'download.png'             => $this->web_template_path.'download.png',
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

      $download_path          = $options_array['download_path'];
      $download_count         = $options_array['download_count']?1:0;
      $download_count_show    = $options_array['download_count_show']?1:0;
      $download_method        = ($options_array['download_method'] == 'get')?0:1;
      $default_status         = $options_array['default_status']?1:0;
      $text_pages             = $options_array['text_pages']?1:0;
      $collections            = $options_array['collections']?1:0;
      $display_col            = $options_array['display_col'];

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

      $col = 0;
      if( isset($_POST['display_col']) && $_POST['display_col'] > 0 && $_POST['display_col'] < 3 ) {
        $col = (int)$_POST['display_col'];
      }

      $download_method = ($_POST['download_method'] == 0)?'get':'post';
      $download_path = $db->prepare_input($_POST['download_path']);
      $download_path = tep_trail_path($download_path);

      // Prepare the options array for storage
      $options_array = $this->load_options();
      $options_array['download_path']        = $download_path;
      $options_array['download_method']      = $download_method;
      $options_array['text_pages']           = (isset($_POST['text_pages'])?1:0);
      $options_array['collections']          = (isset($_POST['collections'])?1:0);
      $options_array['download_count']       = (isset($_POST['download_count'])?1:0);
      $options_array['default_status']       = (isset($_POST['default_status'])?1:0);
      $options_array['display_col']          = $col;

      // Store user options
      $this->save_options($options_array);
      $msg->add_session(sprintf($cStrings->SUCCESS_PLUGIN_RECONFIGURED, $this->title), 'success');

      if( !$this->create_download_path() ) {
        $msg->add_session(sprintf($cStrings->ERROR_CREATING_PATH, $this->options_array['download_path']));
      }
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
      $this->set_posted_template();
      $result = parent::install();
      $this->save_options($this->options_array);
      if( !$this->create_download_path() ) {
        $msg->add_session(sprintf($cStrings->ERROR_CREATING_PATH, $this->options_array['download_path']));
      }
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
      extract(tep_load('database'));

      $tables_array = $db->get_tables();
      if( isset($tables_array[TABLE_DOWNLOAD]) ) {
        $files_query_raw = "select filename from " . TABLE_DOWNLOAD . " where filename != ''";
        $files_array = $db->query_to_array($files_query_raw, false, false);
        $path = tep_front_physical_path(DIR_WS_CATALOG);
        for( $i=0, $j=count($files_array); $i<$j; $i++) {
          if( is_file($path . $files_array[$i]['filename']) ) {
            unlink($path . $files_array[$i]['filename']);
          }
        }
      }
      $db->query("drop table if exists " . TABLE_DOWNLOAD);
    }

    function create_download_path() {
      $options_array = $this->load_options();

      $result = true;
      $dir = DIR_FS_CATALOG . tep_trail_path($options_array['download_path']);
      if( !empty($dir) ) {
        $result = tep_mkdir($dir);
      }
      return $result;
    }
  }
?>
