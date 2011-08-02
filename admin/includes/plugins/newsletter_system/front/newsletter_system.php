<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Newsletter processing script
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
  class newsletter_system extends plugins_base {
    // Compatibility constructor
    function newsletter_system() {
      $this->form_box = 'process_newsletter_system_box';

      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      // Load the plugin options
      $this->options = $this->load_options();
      // Load the plugin strings
      // Load the plugin strings
      $strings_array = array('web_strings.php');
      $this->strings = $this->load_strings($strings_array);
      //$this->strings = tep_get_strings($this->fs_template_path . 'web_strings.php');
      // Load the database tables for the box content
      tep_define_vars($this->fs_path . 'tables.php');

      // Check the templates if the files are not present disable the plugin
      $this->newsletter_form = $this->fs_template_path . 'newsletter_form.tpl';
      if( !is_file($this->newsletter_form) ) {
        $this->change(false);
      }
    }

    function plugin_form_process() {
      extract(tep_load('defs', 'database', 'validator', 'message_stack'));

      $cStrings =& $this->strings;

      // self-check
      if( !tep_check_submit($this->form_box) ) {
        return false;
      }

      $this->newsletter_subscribe();
      return true;

      $email = isset($_POST['email'])?$db->prepare_input($_POST['email']):'';
      if( empty($email) ) {
        $msg->add($cStrings->ERROR_PLUGIN_INVALID_EMAIL);
        return true;
      }

      // Get the validated parameters only
      $params = $cValidator->convert_to_get();

      if( isset($_POST['remove']) ) {
        $check_query = $db->query("select customers_id, customers_email from " . TABLE_CUSTOMERS . " where newsletter is not null and customers_email = '" . $db->input($email) . "'");
        if( !$db->num_rows($check_query) ) {
          $msg->add($cStrings->ERROR_PLUGIN_INVALID_EMAIL);
          return true;
        }
        $check_array = $db->fetch_array($check_query);

        $sql_data_array = array(
          'newsletter' => 'null',
        );
        $db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id='" . (int)$check_array['customers_id'] . "'");
        $msg->add_session(sprintf($cStrings->SUCCESS_PLUGIN_EMAIL_REMOVED, $check_array['customers_email']), 'success');
      } else {
        $check_query = $db->query("select customers_id, newsletter from " . TABLE_CUSTOMERS . " where customers_email = '" . $db->input($email) . "'");
        if( !$db->num_rows($check_query) ) {
          if( !tep_validate_email($email) ) {
            $msg->add($cStrings->ERROR_PLUGIN_INVALID_EMAIL);
            return true;
          }
          $sql_data_array = array(
            'customers_name' => $email,
            'customers_email' => $email,
            'newsletter' => serialize(array()),
          );
          $msg->add_session($cStrings->SUCCESS_PLUGIN_EMAIL_SUBSCRIBED);
          $db->perform(TABLE_CUSTOMERS, $sql_data_array);
        } else {
          $check_array = $db->fetch_array($check_query);
          if( !empty($check_array['newsletter']) )  {
            $msg->add($cStrings->ERROR_PLUGIN_EXISTING_EMAIL);
            return true;
          }

          $sql_data_array = array(
            'newsletter' => serialize(array()),
          );
          $db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id='" . (int)$check_array['customers_id'] . "'");
        }
      }
      tep_redirect(tep_href_link($cDefs->script, $params));
    }

    function html_left() {
      extract(tep_ref('box_array'), EXTR_OVERWRITE|EXTR_REFS);
      if( $this->options['display_col'] == 0 ) {
        return $this->display_common($box_array);
      }
      return false;
    }

    function html_right() {
      extract(tep_ref('box_array'), EXTR_OVERWRITE|EXTR_REFS);
      if( $this->options['display_col'] == 1 ) {
        return $this->display_common($box_array);
      }
      return false;
    }

    function display_common(&$box_array) {
      extract(tep_load('defs', 'database', 'validator', 'message_stack'));

      $cStrings =& $this->strings;

      $params = $cValidator->convert_to_get();
      if( !empty($params) ) {
        $params .= '&';
      }
      $params .= 'action=plugin_form_process';
      $link = tep_href_link($cDefs->script, $params);
      require($this->newsletter_form);

      $result = true;
      return $result;
    }

    // newsletter subscription
    function newsletter_subscribe() {
      extract(tep_load('defs', 'database', 'validator', 'message_stack'));

      $cStrings =& $this->strings;

      // Get the validated parameters only
      $params = $cValidator->convert_to_get();

      if( !isset($_POST['email']) || !tep_validate_email($_POST['email']) ) {
        $msg->add_session($cStrings->ERROR_PLUGIN_INVALID_EMAIL);
        tep_redirect(tep_href_link($cDefs->script, $params));
      }

      $customer_id = $mode = 0;
      $check_query = $db->query("select customers_id, customers_email, newsletter from " . TABLE_CUSTOMERS . " where customers_email = '" . $db->filter($_POST['email']) . "'");
      if( !$db->num_rows($check_query) ) {
        $mode = 1;
      } else {
        $check_array = $db->fetch_array($check_query);
        $customer_id = $check_array['customers_id'];
        $mode = 2;

        if( !empty($check_array['newsletter']) ) {
          $mode = 4;
        }
      }

      if( isset($_POST['remove']) && $mode == 2 ) {
        $mode = 5;
      } elseif( isset($_POST['remove']) && $mode != 1 ) {
        $mode = 3;
      } elseif( isset($_POST['remove']) && $mode == 1 ) {
        $mode = 6;
      }

      switch($mode) {
        case 1:
          $sql_data_array = array(
            'customers_email' => $db->prepare_input($_POST['email']),
            'newsletter' => serialize(array()),
          );
          $db->perform(TABLE_CUSTOMERS, $sql_data_array);
          break;
        case 2:
          $sql_data_array = array(
            'newsletter' => serialize(array()),
          );
          $db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id= '" . (int)$customer_id . "'");
          $msg->add_session($cStrings->SUCCESS_PLUGIN_EMAIL_SUBSCRIBED, 'success');
          break;
        case 3:
          $sql_data_array = array(
            'newsletter' => 'null',
          );
          $db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id= '" . (int)$customer_id . "'");
          $msg->add_session(sprintf($cStrings->SUCCESS_PLUGIN_EMAIL_REMOVED, $check_array['customers_email']), 'success');
          break;
        case 4:
          $msg->add_session($cStrings->ERROR_PLUGIN_EXISTING_EMAIL);
          break;
        case 5:
          $msg->add_session($cStrings->ERROR_PLUGIN_ALREADY_REMOVED_EMAIL);
          break;
        default:
          break;
      }
      tep_redirect(tep_href_link($cDefs->script, $params));

/*
      $check_array = $db->fetch_array($check_query);
      
      if( $check_array['total'] ) {
        $msg->add_session($cStrings->ERROR_PLUGIN_EXISTING_EMAIL);
        tep_redirect(tep_href_link($cDefs->script, $params));
      }

      $sql_data_array = array(
        'customers_email' => $db->prepare_input($_POST['email']),
        'newsletter' => serialize(array()),
      );
      $db->perform(TABLE_CUSTOMERS, $sql_data_array);

      $msg->add_session($cStrings->SUCCESS_PLUGIN_EMAIL_SUBSCRIBED);
      tep_redirect(tep_href_link($cDefs->script, $params));
*/
    }

    // Records access if a newsletter is read
    function newsletter_record() {
      extract(tep_load('database'));

      $record_id = isset($_GET['id'])?$db->prepare_input($_GET['id']):0;
      $record_array = explode('_', $record_id);
      if( count($record_array) != 3 ) tep_redirect();

      $customer_id = (int)$record_array[0];
      $nID = (int)$record_array[1];
      $signature = $db->prepare_input($record_array[2]);

      $customer_query = $db->query("select customers_name, customers_email, newsletter from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "' and newsletter is not null");
      if( !$db->num_rows($customer_query) ) {
        return;
      }

      $customer_array = $db->fetch_array($customer_query);

      $newsletter_query = $db->query("select date_sent from " . TABLE_NEWSLETTERS . " where template_id = '" . (int)$nID . "'");
      if( !$db->num_rows($newsletter_query) ) {
        return;
      }
      $newsletter_array = $db->fetch_array($newsletter_query);

      if( $signature != md5($customer_array['customers_name'] . $customer_array['customers_email']) ) {
        return;
      }

      $details_array = unserialize($customer_array['newsletter']);
      if( isset($details_array[$nID]) ) return;

      $db->query("update " . TABLE_NEWSLETTERS . " set newsletter_hits=newsletter_hits+1 where template_id = '" . (int)$nID . "'");

      $details_array[$nID] = $nID;
      if( count($details_array) > 10 ) {
        array_shift($details_array);
      }
      $sql_data_array = array(
        'newsletter' => serialize($details_array),
      );
      $db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id='" . (int)$customer_id . "'");
    }

    // Remove customer from newsletter subscription if he click the remove link of the newsletter
    function newsletter_remove() {
      extract(tep_load('database', 'message_stack'));

      $cStrings =& $this->strings;

      $remove_id = isset($_GET['id'])?$db->prepare_input($_GET['id']):0;
      $remove_array = explode('_', $remove_id);
      if( count($remove_array) != 3 ) tep_redirect();

      $customer_id = (int)$remove_array[0];
      $nID = (int)$remove_array[1];
      $signature = $db->prepare_input($remove_array[2]);

      $customer_query = $db->query("select customers_name, customers_email from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "' and newsletter is not null");
      if( !$db->num_rows($customer_query) ) {
        $msg->add_session($cStrings->ERROR_CUSTOMER_NOT_FOUND, 'error', tep_get_script_name(FILENAME_DEFAULT));
        tep_redirect();
      }
      $customer_array = $db->fetch_array($customer_query);

      $newsletter_query = $db->query("select date_sent from " . TABLE_NEWSLETTERS . " where template_id = '" . (int)$nID . "'");
      if( !$db->num_rows($newsletter_query) ) {
        $msg->add_session($cStrings->ERROR_CUSTOMER_NOT_FOUND);
        tep_redirect();
      }
      $newsletter_array = $db->fetch_array($newsletter_query);

      if( $signature != md5($customer_array['customers_name'] . $customer_array['customers_email']) ) {
        $msg->add_session($cStrings->ERROR_CUSTOMER_NOT_FOUND);
        tep_redirect();
      }

      $sql_data_array = array(
        'newsletter' => 'null',
      );
      $db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id='" . (int)$customer_id . "'");
      $msg->add_session(sprintf($cStrings->SUCCESS_PLUGIN_EMAIL_REMOVED, $customer_array['customers_email']), 'success');
      tep_redirect();
    }
  }
?>
