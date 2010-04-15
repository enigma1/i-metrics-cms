<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Install class for Left Column System
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
  class install_left_column extends plug_manager {
    var $options_array = array(
      'text_pages' => 1, 
      'text_collections' => 1, 
      'image_collections' => 1,
      'template' => 'stock'
    );

    // Compatibility constructor
    function install_left_column() {
      parent::plug_manager();
      $this->title = 'Left Column';
      $this->author = 'Mark Samios';
      $this->version = '1.00';
      $this->framework = '1.11';
      $this->help = '';
      tep_read_contents($this->admin_path.'readme.txt', $this->help);
      $this->front = 1;
      $this->back = 0;
      $this->status = 1;

      $this->template_path = 'front/templates/';
      // $this->web_path points to the plugins folder of the webfront
      // Key/Left => Source Path/File (to copy file from)
      // Value/Right => Destination Path only (to copy source file to)
      $this->files_array = array(
        'front/left_column.php'      => $this->web_path.'left_column.php',
      );

      // Common Template filenames
      $this->template_array = array(
        'left_collection.tpl'        => $this->web_template_path.'left_collection.tpl',
        'left_text.tpl'              => $this->web_template_path.'left_text.tpl',
        'web_strings.php'            => $this->web_template_path.'web_strings.php',
      );

      $this->config_form = $this->admin_path . 'config_form.tpl';
      $this->strings = tep_get_strings($this->admin_path . 'strings.php');
    }

    function set_options() {
      global $g_script;
      $cStrings =& $this->strings;
      // Read the plugin store options into an array
      $options_array = $this->load_options();

      $text_pages = ($options_array['text_pages']==1)?true:false;
      $text_collections = ($options_array['text_collections']==1)?true:false;
      $image_collections = ($options_array['image_collections']==1)?true:false;

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
        'text_pages' => (isset($_POST['text_pages'])?1:0),
        'text_collections' => (isset($_POST['text_collections'])?1:0),
        'image_collections' => (isset($_POST['image_collections'])?1:0),
      );
      // Store user options
      $this->save_options($options_array);
      $messageStack->add_session(sprintf($cStrings->SUCCESS_PLUGIN_RECONFIGURED, $this->title), 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=set_options'));
    }


    function install() {
      $this->set_posted_template();
      $result = parent::install();
      $this->save_options($this->options_array);
      return $result;
    }

    function uninstall() {
      $options_array = $this->load_options();
      $this->load_template_files($options_array['template']);
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
  }
?>
