<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
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
//
*/
  class install_comments_system extends plug_manager {
    var $sql_file;

    // Default Options
    var $options_array = array(
      'text_pages'         => 1, 
      'text_collections'   => 1, 
      'image_collections'  => 1, 
      'display_rating'     => true,
      'rating_steps'       => 5,
      'text_include'       => false,
      'collection_include' => true,
      'anti_bot'           => false,
      'anti_bot_strict'    => false,
      'auto_display'       => 1,
      'template'           => 'stock'
    );


    // Compatibility constructor
    function install_comments_system() {
      parent::plug_manager();
      $this->title = 'Comments System';
      $this->author = 'Mark Samios';
      $this->version = '1.00';
      $this->framework = '1.11';
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
        'front/comments_system.php'       => $this->web_path.'comments_system.php',
        'front/files.php'                 => $this->web_path.'files.php',
        'front/tables.php'                => $this->web_path.'tables.php',
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
        'web_strings.php'           => $this->web_template_path.'web_strings.php',
        'comments_form.tpl'         => $this->web_template_path.'comments_form.tpl',
        'comments_posted.tpl'       => $this->web_template_path.'comments_posted.tpl',
        'thumbs-up.png'             => $this->web_template_path.'thumbs-up.png',
        'thumbs-down.png'           => $this->web_template_path.'thumbs-down.png',
      );

      $this->sql_file = $this->admin_path . 'database.sql';
      $this->config_form = $this->admin_path . 'config_form.tpl';
      $this->strings = tep_get_strings($this->admin_path . 'strings.php');
    }

    function set_options() {
      global $g_script;
      $cStrings =& $this->strings;

      $cStrings->TEXT_INCLUSION_TEXT_PAGES = sprintf($cStrings->TEXT_INCLUSION_TEXT_PAGES, tep_href_link(FILENAME_COMMENTS, 'selected_box=abstract_config'));
      $cStrings->TEXT_INCLUSION_COLLECTIONS = sprintf($cStrings->TEXT_INCLUSION_COLLECTIONS, tep_href_link(FILENAME_COMMENTS, 'selected_box=abstract_config'));

      // Read the plugin store options into an array
      $options_array = $this->load_options();

      $text_pages = ($options_array['text_pages']==1)?true:false;
      $text_collections = ($options_array['text_collections']==1)?true:false;
      $image_collections = ($options_array['image_collections']==1)?true:false;
      $text_include = ($options_array['text_include']==1)?true:false;
      $collection_include = ($options_array['collection_include']==1)?true:false;
      $anti_bot = ($options_array['anti_bot']==1)?true:false;
      $anti_bot_strict = ($options_array['anti_bot_strict']==1)?true:false;
      $auto_display = ($options_array['auto_display']==1)?true:false;
      $display_rating = ($options_array['display_rating']==1)?true:false;
      $rating_steps = ($options_array['rating_steps'] > 1)?$options_array['rating_steps']:$this->default_steps;

      $html_string = '';
      if( !file_exists($this->config_form) ) {
        $html_string = sprintf($cStrings->ERROR_PLUGIN_INVALID_CONFIG_TPL, $this->config_form);
        return $html_string;
      }
      require_once($this->config_form);
      return $html_string;
    }

    function process_options() {
      global $g_script, $messageStack;
      $cStrings =& $this->strings;

      // Prepare the options array for storage
      $options_array = array(
        'auto_display' => (isset($_POST['auto_display'])?1:0),
        'anti_bot' => (isset($_POST['anti_bot'])?true:false),
        'anti_bot_strict' => (isset($_POST['anti_bot_strict'])?true:false),
        'text_pages' => (isset($_POST['text_pages'])?1:0),
        'text_collections' => (isset($_POST['text_collections'])?1:0),
        'image_collections' => (isset($_POST['image_collections'])?1:0),
        'text_include' => (isset($_POST['text_include'])?true:false),
        'collection_include' => (isset($_POST['collection_include'])?true:false),
        'display_rating' => (isset($_POST['display_rating'])?true:false),
        'rating_steps' => ((isset($_POST['rating_steps']) && $_POST['rating_steps'] > 0)?(int)$_POST['rating_steps']:$this->default_steps),
      );
      // Store user options
      $this->save_options($options_array);
      $messageStack->add_session(sprintf($cStrings->SUCCESS_PLUGIN_RECONFIGURED, $this->title), 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=set_options'));
    }

    function install() {
      global $g_db, $messageStack;
      $cStrings =& $this->strings;

      $result = false;
      $this->delete_references();

      $result = $g_db->file_exec($this->sql_file);
      if( !$result ) {
        $messageStack->add_session(sprintf($cStrings->ERROR_INVALID_DATABASE_FILE, $this->sql_file));
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

    function pre_uninstall() {
      $options_array = $this->load_options();
      $this->load_template_files($options_array['template']);
      return true;
    }

    function re_copy_front() {
      $this->set_posted_template();
      return parent::re_copy_front();
    }

    function common_select() {
      $cStrings =& $this->strings;

      $tmp_array = $this->get_templates();
      if( !count($tmp_array) ) return false;

      echo '<div class="comboHeading">' . "\n";
      echo '<b>' . $cStrings->TEXT_SELECT_TEMPLATE . '</b>&nbsp;&nbsp;' . tep_draw_pull_down_menu('template', $tmp_array, $this->options_array['template']) . "\n";
      echo '&nbsp;&nbsp;' . $cStrings->TEXT_ADDITIONAL_TEMPLATE_FILES . "\n";
      echo '</div>' . "\n";
      return true;
    }

    function delete_references() {
      global $g_db;

      require_once($this->admin_path . 'back/admin_tables.php');
      $g_db->query("drop table if exists " . TABLE_COMMENTS);
      $g_db->query("drop table if exists " . TABLE_COMMENTS_TO_CONTENT);
    }
  }
?>
