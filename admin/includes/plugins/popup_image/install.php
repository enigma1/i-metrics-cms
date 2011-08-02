<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Install class for the popup image
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
  class install_popup_image extends plug_manager {
    // Compatibility constructor
    function install_popup_image() {
      parent::plug_manager();

      $this->options_array = array(
        'front_scripts' => array(), 
        'front_all' => true,
        'front_common_selector' => 'div.imagelink a',
        'back_scripts' => array(), 
        'back_all' => false,
        'back_common_selector' => 'div#help_image_group a',
      );
      $this->front_common_selector = 'div.imagelink a';
      $this->back_common_selector = 'div#help_image_group a';

      // Never set the key member
      $this->title = 'Fancybox Popup Image';
      $this->author = 'Mark Samios';
      $this->icon = 'popup.png';
      $this->version = '1.01';
      $this->framework = '1.12';
      $this->help = '';
      tep_read_contents($this->admin_path.'readme.txt', $this->help);
      $this->front = 1;
      $this->back = 1;
      $this->status = 1;

      // The array of files that operate on the web-front
      // Left(Key)     => Source File with Path relative to the plugins directory (to copy file from)
      // Right(Value)  => Destination Path and File (to copy source file to)
      $this->files_array = array(
        'front/popup_image.php'                  => $this->web_path.'popup_image.php',
        'front/launcher.tpl'                     => $this->web_path.'launcher.tpl',
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
      extract(tep_load('defs'));

      $cStrings =& $this->strings;
      // Read the plugin store options into an array
      $options_array = $this->load_options();

      $front_all = isset($options_array['front_all'])?$options_array['front_all']:false;
      $back_all = isset($options_array['back_all'])?$options_array['back_all']:false;

      $html_string = '';
      if( !is_file($this->config_form) ) {
        $html_string = sprintf($cStrings->ERROR_PLUGIN_INVALID_CONFIG_TPL, $this->config_form);
        return $html_string;
      }

      $front_common_selector = isset($options_array['front_common_selector'])?$options_array['front_common_selector']:$this->front_common_selector;
      $back_common_selector = isset($options_array['back_common_selector'])?$options_array['back_common_selector']:$this->back_common_selector;

      require_once($this->config_form);
      return false;
    }

    // Configuration Form Processing Options
    function process_options() {
      extract(tep_load('defs', 'database', 'message_stack'));
      $cStrings =& $this->strings;

      $remove_flag = $error = false;

      // Load existing options
      $options_array = $this->load_options();

      $front_popup_remove = (isset($_GET['front_popup_remove']) ? $db->prepare_input($_GET['front_popup_remove']) : '');
      $back_popup_remove = (isset($_GET['back_popup_remove']) ? $db->prepare_input($_GET['back_popup_remove']) : '');

      if( isset($options_array['front_scripts']) && !empty($front_popup_remove) ) {
        unset($options_array['front_scripts'][$front_popup_remove]);
        $remove_flag = true;
      }
      if( isset($options_array['back_scripts']) && !empty($back_popup_remove) ) {
        unset($options_array['back_scripts'][$back_popup_remove]);
        $remove_flag = true;
      }

      if( $remove_flag ) {
        // Store user options
        $this->save_options($options_array);
        $msg->add_session(sprintf($cStrings->SUCCESS_PLUGIN_RECONFIGURED, $this->title), 'success');
        tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action', 'front_popup_remove', 'back_popup_remove') . 'action=set_options'));
      }

      $front_common_selector = (isset($_POST['front_common_selector']) && !empty($_POST['front_common_selector']))?$db->prepare_input($_POST['front_common_selector']):$this->front_common_selector;
      $back_common_selector = (isset($_POST['back_common_selector']) && !empty($_POST['back_common_selector']))?$db->prepare_input($_POST['back_common_selector']):$this->back_common_selector;

      // Prepare the options array for storage
      $options_array = array(
        'front_all' => isset($_POST['front_all'])?true:false,
        'back_all' => isset($_POST['back_all'])?true:false,
        'front_scripts' => isset($options_array['front_scripts'])?$options_array['front_scripts']:array(),
        'back_scripts' => isset($options_array['back_scripts'])?$options_array['back_scripts']:array(),
        'front_common_selector' => $front_common_selector,
        'back_common_selector' => $back_common_selector,
      );

      $filter = "/[^0-9a-z\#\-_\.\s]+/i";
      if( isset($_POST['script_entry']) && !empty($_POST['script_entry']) ) {
        $key = $db->prepare_input($_POST['script_entry']);
        $selector = tep_create_safe_string($_POST['script_selector'], '', $filter);
        if( empty($selector) ) {
          $selector = $this->front_common_selector;
          $msg->add_session(sprintf($cStrings->WARNING_PLUGIN_SELECTOR_EMPTY, $key), 'warning');
        }
        $options_array['front_scripts'][$key] = $selector;
      }
      if( isset($_POST['admin_entry']) && !empty($_POST['admin_entry']) ) {
        $key = $db->prepare_input($_POST['admin_entry']);
        $selector = tep_create_safe_string($_POST['admin_selector'], '', $filter);
        if( empty($selector) ) {
          $selector = $this->front_common_selector;
          $msg->add_session(sprintf($cStrings->WARNING_PLUGIN_SELECTOR_EMPTY, $key), 'warning');
        }
        $options_array['back_scripts'][$key] = $selector;
      }

      // Store user options
      $this->save_options($options_array);
      if( !$error ) {
        $msg->add_session(sprintf($cStrings->SUCCESS_PLUGIN_RECONFIGURED, $this->title), 'success');
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action', 'front_popup_remove', 'back_popup_remove') . 'action=set_options'));
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
