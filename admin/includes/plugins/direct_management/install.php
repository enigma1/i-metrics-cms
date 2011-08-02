<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Install class for the Direct Management
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
  class install_direct_management extends plug_manager {
    // Compatibility constructor
    function install_direct_management() {
      $this->admin_min_key_length  = 10;

      parent::plug_manager();
      $this->options_array = array(
        'text_pages'           => 1, 
        'text_collections'     => 1, 
        'abox'                 => true,
        'ascripts'             => array(FILENAME_DEFAULT),
        'admin_key'            => 'admin_key',
        'admin_key_length'     => $this->admin_min_key_length,
      );
      // Never set the key member
      $this->title = 'Direct Management'; // Text title of the Plugin shows on the admin
      $this->author = 'Mark Samios'; // Your name/author
      $this->version = '1.00'; // Plugin Version
      $this->framework = '1.12'; // Minimum version of the I-Metrics CMS required
      $this->help = ''; // Brief description of a plugin or use a file
      tep_read_contents($this->admin_path.'readme.txt', $this->help);
      $this->front = 1;  // Operates on front-end
      $this->back = 1;   // Do not operates on admin-end
      $this->status = 1; // enable plugin after installation

      $this->files_array = array(
        'front/direct_management.php' => $this->web_path.'direct_management.php',
        'front/direct_management.tpl' => $this->web_path.'direct_management.tpl',
        'front/web_strings.php'       => $this->web_path.'web_strings.php',
        'front/admin.js'              => $this->web_path.'admin.js',
        'front/admin.tpl'             => $this->web_path.'admin.tpl',
        'front/dm_edit.png'           => $this->web_path.'dm_edit.png',
      );

      // The array of files that operate on the administration end
      // Left(Key)     => Source Path/File (to copy file from)
      // Right(Value)  => Destination Path only (to copy source file to)
      $this->admin_files_array = array();

      // Setup plugin configuration options using a template file
      $this->config_form = $this->admin_path . 'config_form.tpl';
      // Load string for the installation/configuration from a file
      $this->strings = tep_get_strings($this->admin_path . 'strings.php');
    }

    function set_options() {
      extract(tep_load('defs'));
      $cStrings =& $this->strings;

      // Read the plugin store options into an array
      $options_array = $this->load_options();
      $text_pages                 = $options_array['text_pages']?1:0;
      $text_collections           = $options_array['text_collections']?1:0;
      $admin_key                  = $options_array['admin_key'];
      $admin_key_length           = $options_array['admin_key_length']?max($this->admin_min_key_length,(int)$options_array['admin_key_length']):$this->admin_min_key_length;

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

      if( !isset($_POST['admin_key_length']) || (int)$_POST['admin_key_length'] < $this->admin_min_key_length ) {
        $msg->add_session(sprintf($cStrings->WARNING_PLUGIN_ADMIN_KEY_LENGTH, $this->admin_min_key_length), 'warning');
        $_POST['admin_key_length'] = $this->admin_min_key_length;
      }

      // Prepare the options array for storage
      $options_array = array(
        'text_pages'            => (isset($_POST['text_pages'])?1:0),
        'text_collections'      => (isset($_POST['text_collections'])?1:0),
        'admin_key'             => tep_create_random_value((int)$_POST['admin_key_length'], 'chars_lower'),
        'admin_key_length'      => (int)$_POST['admin_key_length'],
      );
      // Store user options
      $this->save_options($options_array);
      $msg->add_session(sprintf($cStrings->SUCCESS_PLUGIN_RECONFIGURED, $this->title), 'success');
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=set_options'));
    }

    function install() {
      $result = parent::install();
      $this->options_array['admin_key'] = tep_create_random_value($this->options_array['admin_key_length'], 'chars_lower');
      $this->save_options($this->options_array);
      return $result;
    }

    function uninstall() {
      extract(tep_load('database', 'sessions'));
      $result = parent::uninstall();
      $db->query("delete from " . TABLE_SESSIONS . " where sesskey = '" . $db->filter($cSessions->id) . "'");
      return $result;
    }
  }
?>
