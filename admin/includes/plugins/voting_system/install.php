<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Install class for the voting system
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
  class install_voting_system extends plug_manager {
    var $version, $framework, $title, $author, $help, $current_status;

    // Default Options
    var $options_array = array(
      'display_col'        => 1, 
      'display_box'        => 1, 
      'display_mod'        => 1, 
      'text_pages'         => 1, 
      'text_collections'   => 1, 
      'image_collections'  => 1, 
      'box_steps'          => 2,
      'mod_steps'          => 2,
      'template'           => 'stock'
    );

    // Compatibility constructor
    function install_voting_system() {
      parent::plug_manager();
      $this->title = 'Voting System';
      $this->author = 'Mark Samios';
      $this->version = '1.01';
      $this->framework = '1.11';
      tep_read_contents($this->admin_path.'readme.txt', $this->help);
      $this->front = 1;
      $this->back = 1;
      $this->status = 1;

      $this->template_path = 'front/templates/';
      $this->default_box_steps = 2;
      $this->default_mod_steps = 5;

      // The array of files that operate on the web-front
      // Left(Key)     => Source File with Path (to copy file from)
      // Right(Value)  => Destination Path and File (to copy source file to)
      $this->files_array = array(
        'front/voting_system.php'         => $this->web_path.'voting_system.php',
        'front/tables.php'                => $this->web_path.'tables.php',
      );

      // The array of files that operate on the administration end
      // Left(Key)     => Source Path/File (to copy file from)
      // Right(Value)  => Destination Path only (to copy source file to)
      $this->admin_files_array = array(
        'back/admin_votes.php'           => 'votes.php',
        'back/admin_votes_strings.php'   => DIR_WS_STRINGS.'votes.php',
      );

      // Common Template filenames
      $this->template_array = array(
        'web_strings.php'           => $this->web_template_path.'web_strings.php',
        'votes_form.tpl'            => $this->web_template_path.'votes_form.tpl',
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

      // Read the plugin store options into an array
      $options_array = $this->load_options();

      $display_col = $options_array['display_col']==1?1:0;
      $display_box = $options_array['display_box']==1?true:false;
      $display_mod = $options_array['display_mod']==1?true:false;
      $text_pages = $options_array['text_pages']==1?true:false;
      $text_collections = $options_array['text_collections']==1?true:false;
      $image_collections = $options_array['image_collections']==1?true:false;

      $box_steps = $options_array['box_steps'] > 1?$options_array['box_steps']:$this->default_box_steps;
      $mod_steps = $options_array['mod_steps'] > 1?$options_array['mod_steps']:$this->default_mod_steps;

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
        'display_col' => ((isset($_POST['display_col']) && $_POST['display_col'] == 1)?1:0),
        'display_box' => (isset($_POST['display_box'])?1:1),
        'display_mod' => (isset($_POST['display_mod'])?1:0),
        'text_pages' => (isset($_POST['text_pages'])?1:0),
        'text_collections' => (isset($_POST['text_collections'])?1:0),
        'image_collections' => (isset($_POST['image_collections'])?1:0),
        'box_steps' => ((isset($_POST['box_steps']) && $_POST['box_steps'] > 0)?(int)$_POST['box_steps']:$this->default_box_steps),
        'mod_steps' => ((isset($_POST['mod_steps']) && $_POST['mod_steps'] > 0)?(int)$_POST['mod_steps']:$this->default_mod_steps),
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
      $g_db->query("drop table if exists " . TABLE_VOTES);
    }
  }
?>
