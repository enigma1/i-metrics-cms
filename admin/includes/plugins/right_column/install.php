<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Install class for Right Column System
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
  class install_right_column extends plug_manager {
    // Compatibility constructor
    function install_right_column() {
      parent::plug_manager();
      tep_define_vars($this->admin_path . 'back/admin_defs.php');

      $this->options_array = array(
        'text_pages'           => 1, 
        'text_collections'     => 1, 
        'image_collections'    => 1,
        'template'             => 'stock',
        'strings'              => 'front',
        'abox'                 => true,
        'ascripts'             => array(FILENAME_DEFAULT, FILENAME_LANGUAGES_SYNC, FILENAME_RIGHT_CONTENT)
      );

      $this->title = 'Right Column';
      $this->author = 'Mark Samios';
      $this->icon = 'right_column.png';
      $this->version = '1.02';
      $this->framework = '1.12';
      $this->help = '';
      tep_read_contents($this->admin_path.'readme.txt', $this->help);
      $this->front = 1;
      $this->back = 1;
      $this->status = 1;

      $this->template_path = 'front/templates/';
      // $this->web_path points to the plugins folder of the webfront
      // Key/Left => Source Path/File (to copy file from)
      // Value/Right => Destination Path only (to copy source file to)
      $this->files_array = array(
        'front/right_column.php'     => $this->web_path.'right_column.php',
        'front/defs.php'             => $this->web_path.'defs.php',
      );

      // The array of files that operate on the administration end
      // Left(Key)     => Source Path/File (to copy file from)
      // Right(Value)  => Destination Path only (to copy source file to)
      $this->admin_files_array = array(
        'back/admin_right_content.php'           => 'right_content.php',
        'back/admin_right_content_strings.php'   => DIR_WS_STRINGS.'right_content.php',
      );

      // Common Template filenames
      $this->template_array = array(
        'right_collection.tpl'       => $this->web_template_path.'right_collection.tpl',
        'right_text.tpl'             => $this->web_template_path.'right_text.tpl',
        'right_box.tpl'              => $this->web_template_path.'right_box.tpl',
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
      $options_array = $this->validate_options($this->options_array);

      $text_pages = ($options_array['text_pages']==1)?true:false;
      $text_collections = ($options_array['text_collections']==1)?true:false;
      $image_collections = ($options_array['image_collections']==1)?true:false;

      $html_string = '';
      if( !is_file($this->config_form) ) {
        $html_string = sprintf($cStrings->ERROR_PLUGIN_INVALID_CONFIG_TPL, $this->config_form);
        return $html_string;
      }
      require_once($this->config_form);
      return $html_string;
    }

    function process_options() {
      extract(tep_load('defs', 'message_stack'));
      $cStrings =& $this->strings;

      // Prepare the options array for storage
      $options_array = $this->load_options();
      $options_array['text_pages']         = (isset($_POST['text_pages'])?1:0);
      $options_array['text_collections']   = (isset($_POST['text_collections'])?1:0);
      $options_array['image_collections']  = (isset($_POST['image_collections'])?1:0);

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

      $lng->delete_table('TABLE_RIGHT_TO_CONTENT');
      $db->query("drop table if exists " . TABLE_RIGHT_TO_CONTENT);
    }

    function create_references() {
      extract(tep_load('database', 'languages'));

      $lng->create_table('TABLE_RIGHT_TO_CONTENT');
      $result = true;
      return $result;
    }

    function backup_database() {
      extract(tep_load('languages', 'database', 'database_backup', 'message_stack'));
    
      $tables_array = array(
        TABLE_RIGHT_TO_CONTENT
      );

      $tmp_array = $lng->get_language_tables(TABLE_RIGHT_TO_CONTENT);
      $tables_array = array_merge($tables_array, $tmp_array);
      $database_backup->save_tables($this->admin_path . 'database.sql', $tables_array);
      $msg->add_session(sprintf(SUCCESS_PLUGIN_DATABASE_BACKUP, $db->prepare_input($this->title)), 'success');
      return true;
    }

  }
?>
