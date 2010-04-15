<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Install Class for Text Color Gradient
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
  class install_text_gradient extends plug_manager {
    // Class member variables
    var $options_array = array(
      'front_scripts' => array(), 
      'front_all' => false,
      'front_common_selector' => 'h1',
      'front_common_colors' => '#000|#700',
    );

    var $front_common_selector = 'h1';
    var $front_common_colors = '#000|#700';

    // Compatibility constructor
    function install_text_gradient() {
      parent::plug_manager();
      // Never set the key member
      $this->title = 'Text Gradient';
      $this->author = 'Mark Samios';
      $this->version = '1.00';
      $this->framework = '1.11';
      $this->help = '';
      tep_read_contents($this->admin_path.'readme.txt', $this->help);
      $this->front = 1;
      $this->back = 0;
      $this->status = 1;

      // The array of files that operate on the web-front
      // Left(Key)     => Source File with Path relative to the plugins directory (to copy file from)
      // Right(Value)  => Destination Path and File (to copy source file to)
      $this->files_array = array(
        'front/text_gradient.php'             => $this->web_path.'text_gradient.php',
        'gradient/launcher.tpl'               => $this->web_path.'gradient/launcher.tpl',
        'gradient/jquery.textgrad0.js'        => $this->web_path.'gradient/jquery.textgrad0.js',
      );

      // The array of files that operate on the administration end
      // Left(Key)     => Source Path/File (to copy file from)
      // Right(Value)  => Destination Path only (to copy source file to)
      $this->admin_files_array = array(
      );

      $this->config_form = $this->admin_path . 'config_form.tpl';
      $this->strings = tep_get_strings($this->admin_path . 'strings.php');
    }

    // Configuration Form Display Options
    function set_options() {
      global $g_script;

      $cStrings =& $this->strings;
      // Read the plugin store options into an array
      $options_array = $this->load_options();

      $front_all = ($options_array['front_all']==1)?true:false;
      $front_common_selector = $options_array['front_common_selector'];
      $front_common_colors = $options_array['front_common_colors'];

      $html_string = '';
      if( !file_exists($this->config_form) ) {
        $html_string = sprintf($cStrings->ERROR_PLUGIN_INVALID_CONFIG_TPL, $this->config_form);
        return $html_string;
      }

      require_once($this->config_form);
      return $html_string;
    }

    // Configuration Form Processing Options
    function process_options() {
      global $g_db, $g_script, $messageStack;
      $cStrings =& $this->strings;

      $remove_flag = $error = false;

      // Load existing options
      $options_array = $this->load_options();

      $front_gradient_remove = (isset($_GET['front_gradient_remove']) ? $g_db->prepare_input($_GET['front_gradient_remove']) : '');

      if( isset($options_array['front_scripts']) && !empty($front_gradient_remove) ) {
        unset($options_array['front_scripts'][$front_gradient_remove]);
        $remove_flag = true;
      }
      if( $remove_flag ) {
        // Store user options
        $this->save_options($options_array);
        $messageStack->add_session(sprintf($cStrings->SUCCESS_PLUGIN_RECONFIGURED, $this->title), 'success');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'front_gradient_remove')) . 'action=set_options'));
      }

      $front_common_selector = (isset($_POST['front_common_selector']) && !empty($_POST['front_common_selector']))?$g_db->prepare_input($_POST['front_common_selector']):$this->front_common_selector;
      $front_common_colors = (isset($_POST['front_common_colors']) && !empty($_POST['front_common_colors']))?$g_db->prepare_input($_POST['front_common_colors']):$this->front_common_colors;
      $front_common_colors = preg_replace("/[^0-9a-f\|]+/i", '', $front_common_colors);

      // Prepare the options array for storage
      $options_array = array(
        'front_all' => isset($_POST['front_all'])?true:false,
        'front_scripts' => isset($options_array['front_scripts'])?$options_array['front_scripts']:array(),
        'front_common_selector' => $front_common_selector,
        'front_common_colors' => $front_common_colors,
      );

      if( isset($_POST['script_entry']) && !empty($_POST['script_entry']) ) {
        $key = $g_db->prepare_input($_POST['script_entry']);
        $selector = preg_replace("/[^0-9a-z\|\#\-_\.\s\>]+/i", '', $_POST['script_selector']);
        if( empty($selector) ) {
          $selector = $front_common_selector;
          $messageStack->add_session(sprintf($cStrings->WARNING_PLUGIN_SELECTOR_EMPTY, $key), 'warning');
        }
        $colors = preg_replace("/[^0-9a-f\|]/i", '', $_POST['script_colors']);
        if( empty($colors) || strlen($colors) != 7 ) {
          $colors = $front_common_colors;
          $messageStack->add_session(sprintf($cStrings->WARNING_PLUGIN_COLORS_EMPTY, $key), 'warning');
        }
        $options_array['front_scripts'][$key] = array(
          'selector' => $selector,
          'colors' => $colors,
        );
      }

      // Store user options
      $this->save_options($options_array);
      if( !$error ) {
        $messageStack->add_session(sprintf($cStrings->SUCCESS_PLUGIN_RECONFIGURED, $this->title), 'success');
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'popup_image_remove')) . 'action=set_options'));
    }

    function install() {
      $result = parent::install();
      $this->save_options($this->options_array);
      return $result;
    }

    function uninstall() {
      $result = parent::uninstall();
      return $result;
    }
  }
?>
