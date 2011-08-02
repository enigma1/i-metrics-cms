<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Install class for the Comments System
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
  class install_comments_system extends plug_manager {
    // Compatibility constructor
    function install_comments_system() {
      parent::plug_manager();
      tep_define_vars($this->admin_path . 'back/admin_defs.php');

      $this->options_array = array(
        'text_pages'         => 1, 
        'text_collections'   => 1, 
        'image_collections'  => 1, 
        'mixed_collections'  => 0, 
        'display_rating'     => true,
        'rating_steps'       => 5,
        'text_include'       => false,
        'collection_include' => true,
        'anti_bot'           => false,
        'anti_bot_strict'    => false,
        'auto_display'       => 1,
        'rss'                => true,
        'strings'            => 'front',
        'template'           => 'stock',
        'abox'               => true,
        'ascripts'           => array(FILENAME_DEFAULT, FILENAME_COMMENTS)
      );

      $this->title = 'Comments System';
      $this->author = 'Mark Samios';
      $this->icon = 'comments.png';
      $this->version = '1.01';
      $this->framework = '1.12';
      $this->help = '';
      tep_read_contents($this->admin_path.'readme.txt', $this->help);
      $this->front = 1;
      $this->back = 1;
      $this->status = 1;
      $this->template_path = 'front/templates/';

      $this->default_steps = 5;
      // The array of files that operate on the web-front
      // Left(Key)     => Source File with Path (to copy file from)
      // Right(Value)  => Destination Path and File (to copy source file to)
      $this->files_array = array(
        'front/comments_system_css.php'   => DIR_WS_CATALOG.'comments_system_css.php',
        'front/comments_system_rss.php'   => DIR_WS_CATALOG.'comments_system_rss.php',
        'front/comments_system.php'       => $this->web_path.'comments_system.php',
        'front/defs.php'                  => $this->web_path.'defs.php',
        'front/cscss.css'                 => $this->web_path.'cscss.css',
      );

      // The array of files that operate on the administration end
      // Left(Key)     => Source Path/File (to copy file from)
      // Right(Value)  => Destination Path only (to copy source file to)
      $this->admin_files_array = array(
        'back/admin_comments.php'           => 'comments.php',
        'back/admin_comments_strings.php'   => DIR_WS_STRINGS.'comments.php',
      );

      // Common Template filenames
      $this->template_array = array(
        'comments_form.tpl'         => $this->web_template_path.'comments_form.tpl',
        'comments_posted.tpl'       => $this->web_template_path.'comments_posted.tpl',
        'thumbs-up.png'             => $this->web_template_path.'thumbs-up.png',
        'thumbs-down.png'           => $this->web_template_path.'thumbs-down.png',
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

      $cStrings->TEXT_INCLUSION_TEXT_PAGES = sprintf($cStrings->TEXT_INCLUSION_TEXT_PAGES, tep_href_link(FILENAME_COMMENTS, 'selected_box=abstract_config'));
      $cStrings->TEXT_INCLUSION_COLLECTIONS = sprintf($cStrings->TEXT_INCLUSION_COLLECTIONS, tep_href_link(FILENAME_COMMENTS, 'selected_box=abstract_config'));

      // Read the plugin store options into an array
      $options_array = $this->load_options();

      $text_pages = ($options_array['text_pages']==1)?true:false;
      $text_collections = ($options_array['text_collections']==1)?true:false;
      $image_collections = ($options_array['image_collections']==1)?true:false;
      $mixed_collections = ($options_array['mixed_collections']==1)?true:false;
      $text_include = ($options_array['text_include']==1)?true:false;
      $collection_include = ($options_array['collection_include']==1)?true:false;
      $anti_bot = ($options_array['anti_bot']==1)?true:false;
      $anti_bot_strict = ($options_array['anti_bot_strict']==1)?true:false;
      $auto_display = ($options_array['auto_display']==1)?true:false;
      $display_rating = ($options_array['display_rating']==1)?true:false;
      $rating_steps = ($options_array['rating_steps'] > 1)?$options_array['rating_steps']:$this->default_steps;
      $rss = ($options_array['rss']==1)?true:false;

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

      // Prepare the options array for storage
      $options_array = $this->load_options();
      $options_array['auto_display']         = (isset($_POST['auto_display'])?1:0);
      $options_array['anti_bot']             = (isset($_POST['anti_bot'])?true:false);
      $options_array['anti_bot_strict']      = (isset($_POST['anti_bot_strict'])?true:false);
      $options_array['text_pages']           = (isset($_POST['text_pages'])?1:0);
      $options_array['text_collections']     = (isset($_POST['text_collections'])?1:0);
      $options_array['image_collections']    = (isset($_POST['image_collections'])?1:0);
      $options_array['mixed_collections']    = (isset($_POST['mixed_collections'])?1:0);
      $options_array['text_include']         = (isset($_POST['text_include'])?true:false);
      $options_array['collection_include']   = (isset($_POST['collection_include'])?true:false);
      $options_array['display_rating']       = (isset($_POST['display_rating'])?true:false);
      $options_array['rss']                  = (isset($_POST['rss'])?true:false);
      $options_array['rating_steps']         = ((isset($_POST['rating_steps']) && $_POST['rating_steps'] > 0)?(int)$_POST['rating_steps']:$this->default_steps);

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
      extract(tep_load('database'));

      $db->query("drop table if exists " . TABLE_COMMENTS);
      $db->query("drop table if exists " . TABLE_COMMENTS_TO_CONTENT);
    }

    function backup_database() {
      extract(tep_load('database', 'database_backup', 'message_stack'));

      $tables_array = array(
        TABLE_COMMENTS,
        TABLE_COMMENTS_TO_CONTENT,
      );
      
      $database_backup->save_tables($this->admin_path . 'database.sql', $tables_array);
      $msg->add_session(sprintf(SUCCESS_PLUGIN_DATABASE_BACKUP, $db->prepare_input($this->title)), 'success');
      return true;
    }

  }
?>
