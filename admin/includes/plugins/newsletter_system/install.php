<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Install class for the newsletters system
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
  class install_newsletter_system extends plug_manager {
    // Compatibility constructor
    function install_newsletter_system() {
      parent::plug_manager();
      tep_define_vars($this->admin_path . 'back/admin_defs.php');
      $front_defs = tep_web_files('FILENAME_DEFAULT', 'FILENAME_GENERIC_PAGES', 'FILENAME_COLLECTIONS');

      // Default Options
      $this->options_array = array(
        'email_id'             => 0,
        'resent'               => 0,
        'display_col'          => 1, 
        'email_limit'          => 100,
        'statistics'           => 1,
        'template'             => 'stock',
        'strings'              => 'front',
        'abox'                 => true,
        'ascripts'             => array(FILENAME_DEFAULT, FILENAME_NEWSLETTERS),
        'fscripts'             => array_values($front_defs)
      );

      $this->title = 'Newsletter System';
      $this->author = 'Mark Samios';
      $this->icon = 'newsletter.png';
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
        'front/newsletter_feedback.php'   => DIR_WS_CATALOG . 'newsletter_feedback.php',
        'front/newsletter_system.php'     => $this->web_path.'newsletter_system.php',
        'front/tables.php'                => $this->web_path.'tables.php',
      );

      // The array of files that operate on the administration end
      // Left(Key)     => Source Path/File (to copy file from)
      // Right(Value)  => Destination Path only (to copy source file to)
      $this->admin_files_array = array(
        'back/admin_newsletters.php'           => 'newsletters.php',
        'back/admin_newsletters_strings.php'   => DIR_WS_STRINGS.'newsletters.php',
      );

      // Common Template filenames
      $this->template_array = array(
        'newsletter_form.tpl'      => $this->web_template_path.'newsletter_form.tpl',
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
      $options_array = $this->load_options();
      $email_limit            = $options_array['email_limit'];
      $resent                 = $options_array['resent']?1:0;
      $statistics             = $options_array['statistics']?1:0;
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
      if( isset($_POST['display_col']) && $_POST['display_col'] > 0 && $_POST['display_col'] < 2 ) {
        $col = (int)$_POST['display_col'];
      }

      // Prepare the options array for storage
      $options_array = $this->load_options();
      $options_array['email_limit'] = (isset($_POST['email_limit'])?(int)$_POST['email_limit']:100);
      $options_array['resent']      = (isset($_POST['resent'])?1:0);
      $options_array['statistics']  = (isset($_POST['statistics'])?1:0);
      $options_array['display_col']  = $col;

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
      extract(tep_load('database'));

      tep_define_vars($this->admin_path . 'back/admin_defs.php');

      $fields_array = $db->query_to_array("show fields from " . TABLE_CUSTOMERS);
      $fields_names = tep_array_invert_flat($fields_array, 'Field', 'Field');

      if( isset($fields_names['newsletter']) ) {
        $db->query("alter table " . TABLE_CUSTOMERS  . " drop newsletter");
      }
      $db->query("drop table if exists " . TABLE_NEWSLETTERS);
      $check_query = $db->query("select group_id from " . TABLE_TEMPLATES_GROUPS . " where group_title = '" . $db->filter(PLUGIN_NEWSLETTER_TEMPLATE_GROUP) . "'");
      if( $db->num_rows($check_query) ) {
        $check_array = $db->fetch_array($check_query);
        $db->query("delete from " . TABLE_TEMPLATES . " where group_id = '" . (int)$check_array['group_id'] . "'");
        $db->query("delete from " . TABLE_TEMPLATES_GROUPS . " where group_id = '" . (int)$check_array['group_id'] . "'");
      }
    }

    function create_references() {
      extract(tep_load('database'));

      tep_define_vars($this->admin_path . 'back/admin_defs.php');

      $fields_array = $db->query_to_array("show fields from " . TABLE_CUSTOMERS);
      $fields_names = tep_array_invert_flat($fields_array, 'Field', 'Field');

      if( !isset($fields_names['newsletter']) ) {
        $db->query("alter table " . TABLE_CUSTOMERS  . " add newsletter text null");
      }

      $check_query = $db->query("select group_id from " . TABLE_TEMPLATES_GROUPS . " where group_title = '" . $db->filter(PLUGIN_NEWSLETTER_TEMPLATE_GROUP) . "'");
      if( !$db->num_rows($check_query) ) {
        $sql_data_array = array(
          'group_title' => $db->prepare_input(PLUGIN_NEWSLETTER_TEMPLATE_GROUP)
        );
        $db->perform(TABLE_TEMPLATES_GROUPS, $sql_data_array);
      }
    }

    function backup_database() {
      extract(tep_load('database', 'database_backup', 'message_stack'));

      $tables_array = array(
        TABLE_NEWSLETTERS,
      );
      $database_backup->save_tables($this->admin_path . 'database.sql', $tables_array);

      $check_query = $db->query("select group_id from " . TABLE_TEMPLATES_GROUPS . " where group_title = '" . $db->filter(PLUGIN_NEWSLETTER_TEMPLATE_GROUP) . "'");
      if( $db->num_rows($check_query) ) {
        $check_array = $db->fetch_array($check_query);

        $tables_array = array(
          TABLE_TEMPLATES,
          TABLE_TEMPLATES_GROUPS,
        );
        $where_array = array(
          "group_id = '" . (int)$check_array['group_id'] . "'",
          "group_id = '" . (int)$check_array['group_id'] . "'",
        );
        $database_backup->save_records($this->admin_path . 'database.sql', $tables_array, $where_array);
      }
      $msg->add_session(sprintf(SUCCESS_PLUGIN_DATABASE_BACKUP, $db->prepare_input($this->title)), 'success');
      return true;
    }
  }
?>
