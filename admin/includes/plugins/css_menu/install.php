<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Install Class for CSS Menu
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
  class install_css_menu extends plug_manager {

    // Compatibility constructor
    function install_css_menu() {
      parent::plug_manager();

      $this->options_array = array(
        'max_cols' => 2, 
        'max_drop' => 10, 
        'max_width' => 380,
        'border_width' => 2,
        'font_size' => 10, 
        'font_pad' => 0, 
        'template' => 'stock'
      );

      // Never set the key member
      $this->title = 'CSS Menu';
      $this->author = 'Mark Samios';
      $this->version = '1.01';
      $this->framework = '1.12';
      $this->help = ''; // Brief description of a plugin or use a file
      tep_read_contents($this->admin_path.'readme.txt', $this->help);
      $this->front = 1;
      $this->back = 0;
      $this->status = 1;

      $this->template_path = 'front/templates/';
      // The array of files that operate on the web-front
      // Left(Key)     => Source File with Path relative to the plugins directory (to copy file from)
      // Right(Value)  => Destination Path and File (to copy source file to)
      $this->files_array = array(
        'front/css_menu.php'             => $this->web_path.'css_menu.php',
      );

      // Common Template filenames
      $this->template_array = array(
        'css_menu.css'                   => $this->web_template_path.'css_menu.css',
        'css_menu.tpl'                   => $this->web_template_path.'css_menu.tpl',
        'words_space.gif'                => $this->web_template_path.'words_space.gif',
        //'back.png'                       => $this->web_template_path.'back.png',
      );
      $this->config_form = $this->admin_path . 'config_form.tpl';
      $this->strings = tep_get_strings($this->admin_path . 'strings.php');
    }

    // Configuration Form Display Options
    function set_options() {
      extract(tep_load('defs'));
      $cStrings =& $this->strings;

      // Read the plugin store options into an array
      $options_array = $this->load_options();

      $max_cols = $options_array['max_cols'];
      $max_drop = $options_array['max_drop'];
      $max_width = $options_array['max_width'];
      $border_width = $options_array['border_width'];
      $font_size = $options_array['font_size'];
      $font_pad = $options_array['font_pad'];

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
      $options_array = array(
        'max_cols' => ((isset($_POST['max_cols']) && $_POST['max_cols'] > 0)?(int)$_POST['max_cols']:$options_array['max_cols']),
        'max_drop' => ((isset($_POST['max_drop']) && $_POST['max_drop'] > 0)?(int)$_POST['max_drop']:$options_array['max_drop']),
        'max_width' => ((isset($_POST['max_width']) && $_POST['max_width'] > 0)?(int)$_POST['max_width']:$options_array['max_width']),
        'border_width' => ((isset($_POST['border_width']))?(int)$_POST['border_width']:$options_array['border_width']),
        'font_size' => ((isset($_POST['font_size']) && $_POST['font_size'] > 0)?(int)$_POST['font_size']:$options_array['font_size']),
        'font_pad' => ((isset($_POST['font_pad']))?(int)$_POST['font_pad']:$options_array['font_pad']),
      );
      // Store user options
      $this->save_options($options_array);
      $msg->add_session(sprintf($cStrings->SUCCESS_PLUGIN_RECONFIGURED, $this->title), 'success');
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=set_options'));
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
  }
?>
