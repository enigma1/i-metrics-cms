<?php
/*
  $Id: helpdesk.php,v 1.6 2005/08/16 21:14:04 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Helpdesk main script
//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// - Added Attachments support
// - Fixes to run script with PHP 5
// - osCommerce formating added
// - Added templates
// - Added insertion of tickets
// - Removed sessions for departments, priorities, statuses
// - Added html editor
// - Added Image controls
// - Added Help feature
// - Restructured HTML for the I-Metrics CMS
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  require('includes/application_top.php');

  //$change_query = $g_db->query("select ticket_id, ticket from " . TABLE_HELPDESK_TICKETS);
  //while($change_array = $g_db->fetch_array($change_query) ) {
  //  $check_query = $g_db->query("update " . TABLE_HELPDESK_ENTRIES . " set ticket_id = '" . (int)$change_array['ticket_id'] . "' where ticket = '" . $change_array['ticket'] . "' and ticket_id='0'");
  //}

  $subaction = (isset($_GET['subaction']) ? $_GET['subaction'] : '');
  $page = (isset($_GET['page']) ? $_GET['page'] : 1);
  //$ticket = (isset($_GET['ticket']) ? $g_db->prepare_input($_GET['ticket']) : '');

  $he_id = (isset($_GET['he_id']) ? $g_db->prepare_input($_GET['he_id']) : '');
  $ticket_id = (isset($_GET['ticket_id']) ? $g_db->prepare_input($_GET['ticket_id']) : '');

  if( empty($ticket_id) && !empty($he_id) ) {
    $ticket_query = $g_db->query("select ticket_id from " . TABLE_HELPDESK_ENTRIES . " where helpdesk_entries_id = '" . (int)$he_id . "'");
    if( $g_db->num_rows($ticket_query) ) {
      $ticket_array = $g_db->fetch_array($ticket_query);
      $ticket_id = $ticket_array['ticket_id'];
      $check_query = $g_db->query("select ticket from " . TABLE_HELPDESK_TICKETS . " where ticket_id = '" . (int)$ticket_id . "'");
      $check_array = $g_db->fetch_array($check_query);
      $ticket = $check_array['ticket'];
    }
  } elseif( !empty($ticket_id) ) {
    $check_query = $g_db->query("select ticket from " . TABLE_HELPDESK_TICKETS . " where ticket_id = '" . (int)$ticket_id . "'");
    if( $g_db->num_rows($check_query) ) {
      $check_array = $g_db->fetch_array($check_query);
      $ticket = $check_array['ticket'];
    }
  }

  $department_filter = (isset($_GET['department_filter']) ? (int)$_GET['department_filter'] : '');
  $status_filter = (isset($_GET['status_filter']) ? (int)$_GET['status_filter'] : '');
  $priority_filter = (isset($_GET['priority_filter']) ? (int)$_GET['priority_filter'] : '');
  $entry_filter = (isset($_GET['entry_filter']) ? (int)$_GET['entry_filter'] : '');

  switch ($action) {
    case 'change_wp':
      $g_wp_ifc = (isset($_GET['wp']) && $_GET['wp'] == 1)?true:false;
      $messageStack->add_session(WARNING_WP_CHANGED, 'warning');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=view'));
      break;

    case 'reply_confirm':
      $ticket = '';
      $status_id = (int)$_POST['status'];
      $from_name = $g_db->prepare_input($_POST['from_name']);
      $priority_id = (int)$_POST['priority_id'];
      $department_id = (int)$_POST['department_id'];

      $account_query = $g_db->query("select email_address, ticket_prefix from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = '" . (int)$department_id . "'");
      if( !$g_db->num_rows($account_query) ) {
        $messageStack->add_session(ERROR_INVALID_DEPARTMENT);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'subaction') . 'action=view'));
      }
      $account_array = $g_db->fetch_array($account_query);
      $from_email_address = $account_array['email_address'];

      $to_name = $g_db->prepare_input($_POST['to_name']);
      $to_email_address = $g_db->prepare_input($_POST['to_email_address']);
      $subject = $g_db->prepare_input($_POST['subject']);
      $body = $g_db->prepare_input($_POST['body']);

      $error = false;
      if( empty($subject) ) { 
        $messageStack->add(ERROR_EMPTY_SUBJECT);
        $error = true;
      }

      if( empty($body) ) { 
        $messageStack->add(ERROR_EMPTY_BODY);
        $error = true;
      }

      if( !tep_validate_email($from_email_address) || !tep_validate_email($to_email_address) ) {
        $messageStack->add(ERROR_EMAIL_ADDRESS);
        $error = true;
      }

      if( $error ) {
        $action = 'view';
        break;
      }

      $sql_data_array = array(
        'priority_id' => (int)$priority_id,
        'status_id' => (int)$status_id,
        'department_id' => (int)$department_id,
        'datestamp_last_entry' => 'now()'
      );

      if( $subaction == 'new' ) {
        if( !empty($account_array['ticket_prefix']) ) {
          do {
            // create & check for dups until unique
            //$ticket = osc_create_random_string();
            $ticket = tep_create_random_value(7, 'mixed_upper', true);
            $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where ticket = '" .  $g_db->input($ticket) . "'");
            $check_array = $g_db->fetch_array($check_query);
          } while($check_array['count']);
        }
        $sql_data_array['ticket'] = $ticket;
        $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array);
        $ticket_id = $g_db->insert_id();
      } else {
        $check_query = $g_db->query("select ticket from " . TABLE_HELPDESK_TICKETS . " where ticket_id = '" . (int)$ticket_id . "'");
        $check_array = $g_db->fetch_array($check_query);
        $sql_data_array['ticket'] = $check_array['ticket'];
        $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', "ticket_id = '" . (int)$ticket_id . "'");
      }

      $sql_data_array = array(
        'ticket_id' => (int)$ticket_id,
        'parent_id' => (int)$he_id,
        'message_id' => '',
        'ip_address' => getenv('SERVER_ADDR'),
        'host' => getenv('SERVER_NAME'),
        'datestamp_local' => 'now()',
        'datestamp' => 'now()',
        'receiver' => $to_name,
        'receiver_email_address' => $to_email_address,
        'sender' => $from_name,
        'email_address' => $from_email_address,
        'subject' => $subject,
        'body' => $body,
        'entry_read' => '1'
      );
      $g_db->perform(TABLE_HELPDESK_ENTRIES, $sql_data_array);
      $he_id = $g_db->insert_id();

      extract(tep_load('email'));
      $text = strip_tags($body);

      //$images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
      //$cEmail->add_html($body, $text, $images_path);
      $cEmail->add_html($body, $text);

      if( isset($_FILES['attach_file']) && is_array($_FILES['attach_file']) && isset($_FILES['attach_file']['name']) && is_array($_FILES['attach_file']['name']) ) {
        foreach($_FILES['attach_file']['name'] as $key => $file ) {
          if( empty($file) ) continue;

          $check = $_FILES['attach_file']['error'][$key];
          if ($check != UPLOAD_ERR_OK) {
            $messageStack->add_session(sprintf(ERROR_FILE_UPLOAD, $file));
            continue;
          }

          $name = tep_create_safe_string(strtolower(basename($file)), '-', "/[^0-9a-z\/\-.]+/i");
          $tmp_file = $_FILES['attach_file']['tmp_name'][$key];
          $fp = fopen($tmp_file, "r");
          if( $fp ) {
            $attachment = fread($fp, filesize($tmp_file));
            $attach_array = array(
              'attachment' => $attachment,
              'name' => $name,
              'type' => 'application/octet-stream',
            );
            fclose($fp);

            move_uploaded_file($tmp_file, DIR_FS_ADMIN . HELPDESK_ATTACHMENTS_FOLDER . $name);
            $sql_data_array = array(
              'helpdesk_entries_id' => (int)$he_id,
              'attachment' => $name,
            );
            $g_db->perform(TABLE_HELPDESK_ATTACHMENTS, $sql_data_array);

            //@unlink($file);
            $cEmail->add_attachment($attach_array['attachment'], $attach_array['name'], $attach_array['type']);
            $messageStack->add_session(sprintf(SUCCESS_FILE_ATTACH, $name), 'success');
          }
        }
      }
      $cEmail->build_message();
      $cEmail->send($to_name, $to_email_address, $from_name, $from_email_address, $subject);

      //tep_mail($to_name, $to_email_address, $subject, $body, $from_name, $from_email_address);
      $messageStack->add_session(SUCCESS_REPLY_PROCESSED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'subaction') . 'action=view&he_id=' . $he_id));
      break;
    case 'save_entry':
      $department_id = (int)$_POST['department'];
      $status_id = (int)$_POST['status'];
      $priority_id = (int)$_POST['priority'];

      $he_id = (int)$_GET['he_id'];

      $ticket = $g_db->prepare_input($_POST['ticket']);
      $from_name = $g_db->prepare_input($_POST['from_name']);
      $from_email_address = $g_db->prepare_input($_POST['from_email_address']);
      $to_name = $g_db->prepare_input($_POST['to_name']);
      $to_email_address = $g_db->prepare_input($_POST['to_email_address']);
      $subject = $g_db->prepare_input($_POST['subject']);
      $body = $g_db->prepare_input($_POST['body']);

      $entry_query = $g_db->query("select ticket_id, ticket, datestamp_local from " . TABLE_HELPDESK_ENTRIES . " where helpdesk_entries_id = '" . (int)$he_id . "'");
      $entry = $g_db->fetch_array($entry_query);

      $sql_data_array = array(
        'department_id' => (int)$department_id,
        'status_id' => (int)$status_id,
        'priority_id' => (int)$priority_id
      );

      if ($entry['ticket'] == $ticket) {
        $new_ticket = false;
        $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', "ticket = '" . $g_db->input($ticket) . "'");
      } else {
        $new_ticket = true;

        $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $g_db->input($ticket) . "'");
        $check = $g_db->fetch_array($check_query);

        if ($check['count'] > 0) {
          $ticket_date_query = $g_db->query("select datestamp_last_entry from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $g_db->input($ticket) . "'");
          $ticket_date = $g_db->fetch_array($ticket_date_query);

          if ($entry['datestamp_local'] > $ticket_date['datestamp_last_entry']) {
            $sql_data_array['datestamp_last_entry'] = $entry['datestamp_local'];
          }

          $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', "ticket_id = '" . $g_db->input($ticket_id) . "'");
        } else {
          $sql_data_array['ticket'] = $ticket;
          $sql_data_array['datestamp_last_entry'] = $entry['datestamp_local'];

          $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array);
          $ticket_id = $g_db->insert_id();
        }
      }

      $sql_data_array = array(
        'ticket' => $ticket,
        'receiver' => $to_name,
        'receiver_email_address' => $to_email_address,
        'sender' => $from_name,
        'email_address' => $from_email_address,
        'subject' => $subject,
        'body' => $body
      );

      if ($new_ticket == true) $sql_data_array['parent_id'] = '0';

      $g_db->perform(TABLE_HELPDESK_ENTRIES, $sql_data_array, 'update', "helpdesk_entries_id = '" . (int)$he_id . "'");

      $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . $entry['ticket_id'] . "'");
      $check = $g_db->fetch_array($check_query);

      $ticket_exists = true;
      if ($check['count'] < 1) {
        $ticket_exists = false;
        $g_db->query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket_id = '" . $entry['ticket_id'] . "'");
      }

      $messageStack->add_session(SUCCESS_ENTRY_UPDATED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'subaction') . 'action=view'));
      break;

    case 'delete':
      if( !isset($_POST['ticket_id']) || !is_array($_POST['ticket_id']) || empty($_POST['ticket_id']) ) {
        $messageStack->add_session(ERROR_TICKET_DOES_NOT_EXIST, 'error');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'action','subaction') ));
      }
      break;

    case 'delete_confirm':
      if( !isset($_POST['ticket_id']) || !is_array($_POST['ticket_id']) || empty($_POST['ticket_id']) ) {
        $messageStack->add_session(ERROR_TICKET_DOES_NOT_EXIST, 'error');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'action','subaction') ));
      }
      foreach( $_POST['ticket_id'] as $key => $value ) {
        $entries_query_raw = "select helpdesk_entries_id from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$key . "'";
        $entries_array = $g_db->query_to_array($entries_query_raw, 'helpdesk_entries_id');

        $g_db->query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket_id = '" . (int)$key . "'");
        $g_db->query("delete from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$key . "'");

        if( empty($entries_array) ) $entries_array = array(0);

        $attachments_query = $g_db->query("select attachment from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id in (" . implode(',', array_keys($entries_array)) . ")");
        if( $g_db->num_rows($attachments_query) ) {
          while($attachments_array = $g_db->fetch_array($attachments_query) ) {
            if( is_file(HELPDESK_ATTACHMENTS_FOLDER . $attachments_array['attachment']) ) {
              $messageStack->add_session(sprintf(WARNING_ATTACHMENT_REMOVED, $attachments_array['attachment']), 'warning');
              unlink(DIR_FS_ADMIN . HELPDESK_ATTACHMENTS_FOLDER . $attachments_array['attachment']);
            }
          }
          $g_db->query("delete from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id in (" . implode(',', array_keys($entries_array)) . ")");
        }
      }
      $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'subaction', 'he_id', 'ticket_id') ));
      break;

    case 'delete_confirm_single':
      $whole = (isset($_POST['whole']) ? $g_db->prepare_input($_POST['whole']) : '');

      if ($whole == 'true') {
        $entries_query_raw = "select helpdesk_entries_id from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$ticket_id . "'";
        $entries_array = $g_db->query_to_array($entries_query_raw, 'helpdesk_entries_id');

        $g_db->query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket_id = '" . (int)$ticket_id . "'");
        $g_db->query("delete from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$ticket_id . "'");

        $attachments_query = $g_db->query("select attachment from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id in (" . implode(',', array_keys($entries_array)) . ")");
        if( $g_db->num_rows($attachments_query) ) {
          while($attachments_array = $g_db->fetch_array($attachments_query) ) {
            if( is_file(HELPDESK_ATTACHMENTS_FOLDER . $attachments_array['attachment']) ) {
              $messageStack->add_session(sprintf(WARNING_ATTACHMENT_REMOVED, $attachments_array['attachment']), 'warning');
              unlink(DIR_FS_ADMIN . HELPDESK_ATTACHMENTS_FOLDER . $attachments_array['attachment']);
            }
          }
          $g_db->query("delete from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id in (" . implode(',', array_keys($entries_array)) . ")");
        }
        $messageStack->add_session(SUCCESS_WHOLE_THREAD_REMOVED, 'success');
      } else {

        $g_db->query("delete from " . TABLE_HELPDESK_ENTRIES . " where helpdesk_entries_id = '" . (int)$he_id . "'");

        $attachments_query = $g_db->query("select attachment from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id= '" . (int)$he_id . "'");
        if( $g_db->num_rows($attachments_query) ) {
          while($attachments_array = $g_db->fetch_array($attachments_query) ) {
            if( is_file(HELPDESK_ATTACHMENTS_FOLDER . $attachments_array['attachment']) ) {
              $messageStack->add_session(sprintf(WARNING_ATTACHMENT_REMOVED, $attachments_array['attachment']), 'warning');
              unlink(DIR_FS_ADMIN . HELPDESK_ATTACHMENTS_FOLDER . $attachments_array['attachment']);
            }
          }
          $g_db->query("delete from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id = '" . (int)$he_id . "'");
        }

        $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$ticket_id . "' and parent_id='0'");
        $check = $g_db->fetch_array($check_query);

        if ($check['count'] > 0) {
          $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
          tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'subaction', 'he_id') . 'action=view'));
        } else {
          $g_db->query("delete from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$ticket_id . "'");
          $g_db->query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket_id = '" . (int)$ticket_id . "'");
          $messageStack->add_session(SUCCESS_WHOLE_THREAD_REMOVED, 'success');
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'ticket', 'action', 'subaction') ));
      break;

    case 'updatestatus':
      $department_id = (int)$_POST['department'];
      $status_id = (int)$_POST['status'];
      $priority_id = (int)$_POST['priority'];
      $g_db->query("update " . TABLE_HELPDESK_TICKETS . " set department_id = '" . (int)$department_id . "', status_id = '" . (int)$status_id . "', priority_id = '" . (int)$priority_id . "' where ticket_id = '" . (int)$ticket_id . "'");
      $messageStack->add_session(SUCCESS_TICKET_UPDATED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=view' ));
      break;

    case 'view':
      if( $subaction == 'new' ) {
        do {
          // create & check for dups until unique
          $ticket = tep_create_random_value(7, 'mixed_upper', true);
          $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where ticket = '" .  $g_db->input($ticket) . "'");
          $check = $g_db->fetch_array($check_query);
        } while($check['count']);
      } elseif( empty($ticket_id) && isset($_GET['ticket']) && !empty($_GET['ticket']) ) {
        $check_query = $g_db->query("select ticket_id from " . TABLE_HELPDESK_TICKETS . " where ticket = '" .  $g_db->filter($_GET['ticket']) . "'");
        if( $g_db->num_rows($check_query) ) {
          $check_array = $g_db->fetch_array($check_query);
          $ticket_id = $check_array['ticket_id'];
        }
      }
      break;
    case 'updatecomment':
      $comment = $g_db->prepare_input($_POST['comment']);

      $sql_data_array = array(
        'comment' => $comment,
        'datestamp_comment' => 'now()'
      );

      $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', "ticket = '" . $g_db->input($ticket) . "'");

      $messageStack->add_session(SUCCESS_COMMENT_UPDATED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action','subaction') . 'action=view'));
      break;
    default:
      break;
  }

  $statuses_array = array();
  $priorities_array = array();
  $departments_array = array();
  $departments_email_array = array();
  $entries_array = array(
    array('id' => '0', 'text' => TEXT_ALL_ENTRIES),
    array('id' => '1', 'text' => TEXT_ONLY_NEW_ENTRIES)
  );

  if(empty($action) ) {
    $statuses_array[] = array('id' => '0', 'text' => TEXT_ALL_STATUSES);
    $priorities_array[] = array('id' => '0', 'text' => TEXT_ALL_PRIORITIES);
    $departments_array[] = array('id' => '0', 'text' => TEXT_ALL_DEPARTMENTS);
  }

  $statuses_query = $g_db->query("select status_id, title from " . TABLE_HELPDESK_STATUSES . " order by title");
  while ($statuses = $g_db->fetch_array($statuses_query)) {
    $statuses_array[] = array('id' => $statuses['status_id'], 'text' => $statuses['title']);
  }

  $priorities_query = $g_db->query("select priority_id, title from " . TABLE_HELPDESK_PRIORITIES . " order by title");
  while ($priorities = $g_db->fetch_array($priorities_query)) {
    $priorities_array[] = array('id' => $priorities['priority_id'], 'text' => $priorities['title']);
  }

  $departments_query = $g_db->query("select department_id, title, email_address from " . TABLE_HELPDESK_DEPARTMENTS . " order by title");
  while ($departments = $g_db->fetch_array($departments_query)) {
    $departments_array[] = array('id' => $departments['department_id'], 'text' => $departments['title']);
    $departments_email_array[] = array('id' => $departments['department_id'], 'text' => $departments['email_address']);
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
            <div><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
<?php
  if( empty($action) ) {

?>
          <div class="listArea"><table border="0" cellspacing="1" cellpadding="2">
            <tr>
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <td><?php echo 'Keyword'; ?></td>
                  <td><?php echo tep_draw_form('keyword', $g_script, '', 'get') . tep_draw_input_field('keyword', '', 'size="10" maxlength="17"') . '</form>'; ?></td>
                </tr>
                <tr>
                  <td><?php echo TEXT_TICKET_NUMBER; ?></td>
                  <td>
<?php 
    $params_array = tep_get_string_parameters(tep_get_all_get_params('ticket', 'action', 'subaction'));
    echo tep_draw_form('ticket', $g_script, '', 'get') . tep_draw_input_field('ticket', '', 'size="10" maxlength="7"');
    foreach($params_array as $key => $value) {
      echo tep_draw_hidden_field($key, $value);
    }
    echo tep_draw_hidden_field('action', 'view');
    echo '</form>'; 
?>
                  </td>
                </tr>
              </table></td>
              <td align="right"><?php echo tep_draw_form('filter', $g_script, '', 'get'); ?><table border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td class="ralign"><table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                      <td><?php echo TEXT_ENTRIES; ?></td>
                      <td class="ralign"><?php echo tep_draw_pull_down_menu('entry_filter', $entries_array, $entry_filter, 'onchange="this.form.submit();"' . (!empty($entry_filter) ? ' style="background-color: #fedecb;"' : '')); ?></td>
                    </tr>
                    <tr>
                      <td><?php echo TEXT_DEPARTMENT; ?></td>
                      <td class="ralign"><?php echo tep_draw_pull_down_menu('department_filter', $departments_array, $department_filter, 'onchange="this.form.submit();"' . (!empty($department_filter) ? ' style="background-color: #fedecb;"' : '')); ?></td>
                    </tr>
                  </table></td>
                  <td><table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                      <td><?php echo TEXT_STATUS; ?></td>
                      <td class="ralign"><?php echo tep_draw_pull_down_menu('status_filter', $statuses_array, $status_filter, 'onchange="this.form.submit();"' . (!empty($status_filter) ? ' style="background-color: #fedecb;"' : '')); ?></td>
                    </tr>
                    <tr>
                      <td><?php echo TEXT_PRIORITY; ?></td>
                      <td class="ralign"><?php echo tep_draw_pull_down_menu('priority_filter', $priorities_array, $priority_filter, 'onchange="this.form.submit();"' . (!empty($priority_filter) ? ' style="background-color: #fedecb;"' : '')); ?></td>
                    </tr>
                  </table></td>
                </tr>
              </table></form></td>
            </tr>
          </table></div>
<?php
  }
  if($action == 'view') {
    $entry = array();
    if( !empty($he_id) ) {
      $entry_query = $g_db->query("select he.helpdesk_entries_id, ht.ticket_id, ifnull(he.host, he.ip_address) as host, he.ip_address, he.datestamp_local, he.datestamp, he.receiver, he.receiver_email_address, he.sender, he.email_address, he.subject, he.body, he.text_body, he.entry_read, ht.department_id, ht.status_id, ht.priority_id from " . TABLE_HELPDESK_ENTRIES . " he, " . TABLE_HELPDESK_TICKETS . " ht where he.ticket_id = ht.ticket_id and he.ticket_id = '" . (int)$ticket_id . "' and he.helpdesk_entries_id = '" . (int)$he_id . "'");
      if( $g_db->num_rows($entry_query) ) {
        $entry = $g_db->fetch_array($entry_query);
      }
    } else {
      $entry_query = $g_db->query("select he.helpdesk_entries_id, ht.ticket_id, ifnull(he.host, he.ip_address) as host, he.ip_address, he.datestamp_local, he.datestamp, he.receiver, he.receiver_email_address, he.sender, he.email_address, he.subject, he.body, he.text_body, he.entry_read, ht.department_id, ht.status_id, ht.priority_id from " . TABLE_HELPDESK_ENTRIES . " he, " . TABLE_HELPDESK_TICKETS . " ht where he.ticket_id = ht.ticket_id and he.ticket_id = '" . (int)$ticket_id . "' and he.parent_id = '0'");
      if( $g_db->num_rows($entry_query) ) {
        $entry = $g_db->fetch_array($entry_query);
      }
    }

    if( empty($entry) ) {
      $he_id = 0;
      if( !empty($_POST) ) {
        $department_id = $department_id;
      } else {
        $department_id = DEFAULT_HELPDESK_DEPARTMENT_ID;
      }
      $entry = array(
        'helpdesk_entries_id' => '',
        'ticket_id' => $ticket_id,
        'ticket' => '',
        'host' => TEXT_INFO_NA,
        'ip_address' => TEXT_INFO_NA,
        'datestamp_local' => TEXT_INFO_NA,
        'datestamp' => TEXT_INFO_NA,
        'receiver' => TEXT_INFO_NA,
        'receiver_email_address' => TEXT_INFO_NA, 
        'sender' => TEXT_INFO_NEW_NAME, 
        'email_address' => TEXT_INFO_NEW_EMAIL, 
        'subject' => TEXT_INFO_NEW_SUBJECT,
        'body' => '',
        'entry_read' => 1,
        'department_id' => DEFAULT_HELPDESK_DEPARTMENT_ID,
        'status_id' => DEFAULT_HELPDESK_STATUS_ID,
        'priority_id' => DEFAULT_HELPDESK_PRIORITY_ID
      );
      $department_query = $g_db->query("select department_id, title, email_address, name, ticket_prefix from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = '" . (int)$department_id . "'");
      $department = $g_db->fetch_array($department_query);
    } else {
      if( empty($he_id) ) {
        $he_id = $entry['helpdesk_entries_id'];
      }
      if( $entry['entry_read'] != '1') {
        $g_db->query("update " . TABLE_HELPDESK_ENTRIES . " set entry_read = '1' where helpdesk_entries_id = '" . $entry['helpdesk_entries_id'] . "'");
      }
      $department_query = $g_db->query("select hd.department_id, hd.title, hd.email_address, hd.name, hd.ticket_prefix from " . TABLE_HELPDESK_DEPARTMENTS . " hd, " . TABLE_HELPDESK_TICKETS . " ht where ht.ticket_id = '" . (int)$ticket_id . "' and ht.department_id = hd.department_id");
      $department = $g_db->fetch_array($department_query);
    }

    if( empty($department['ticket_prefix']) ) {
      $entry['ticket'] = '';
    } elseif( !empty($ticket) ) {
      $entry['ticket'] = $ticket;
    }

    if( $subaction == 'reply' || $subaction == 'new' ) {
      if( !empty($_POST) ) {
        $entry['ticket'] = $ticket;
        $entry['status_id'] = $status_id;
        $entry['subject'] = $subject;
        $template = $body;
        $department['title'] = $from_name;
        $department['department_id'] = $department_id;
        //$department['email_address'] = $from_email_address;
        $entry['sender'] = $to_name;
        $entry['email_address'] = $to_email_address;
        $entry['priority_id'] = $priority_id;
      }

      if( empty($he_id) && $subaction == 'reply' ) {
        $entry_query = $g_db->query("select helpdesk_entries_id from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "' order by helpdesk_entries_id desc");
        if( $g_db->num_rows($entry_query) ) {
          $entry_array = $g_db->fetch_array($entry_query);
          $he_id = $entry_array['helpdesk_entries_id'];
        }
      }

      $template = '';

      $subject = $entry['subject'];
      if( !empty($department['ticket_prefix']) && !strstr($subject, '['. $department['ticket_prefix'] . $entry['ticket'] . ']')) {
        $subject = '[' . $department['ticket_prefix'] . $entry['ticket'] . '] ' . $subject;
      }

      if( $subaction != 'new' ) {
        $subject = 'RE: ' . $subject;
      }
?>
          <div class="formArea"><?php echo tep_draw_form('reply', $g_script, tep_get_all_get_params('he_id', 'ticket_id', 'action', 'subaction') . 'he_id=' . $he_id . '&action=reply_confirm&subaction=' . $subaction, 'post', 'enctype="multipart/form-data" id="reply_form"'); ?><fieldset><legend><?php echo TEXT_SEND_INTRO; ?></legend>
            <div class="hideflow halfer">
              <label for="reply_subject"><?php echo TEXT_SUBJECT; ?></label>
              <div class="rspacer"><?php echo tep_draw_input_field('subject', $subject, 'id="reply_subject" class="wider"'); ?></div>
            </div>
            <div class="hideflow halfer">
              <div class="lspacer"><label for="reply_status"><?php echo TEXT_STATUS; ?></label><?php echo tep_draw_pull_down_menu('status', $statuses_array, $entry['status_id'], 'id="reply_status" class="wider"'); ?></div>
            </div>
            <div class="cleaner vlinepad"></div>
            <div class="hideflow halfer">
              <label for="reply_from"><?php echo TEXT_FROM_NAME; ?></label>
              <div class="rspacer"><?php echo tep_draw_input_field('from_name', $department['title'], 'id="reply_from" class="wider"'); ?></div>
            </div>
            <div class="hideflow halfer">
              <div class="lspacer"><label for="reply_from_email"><?php echo TEXT_FROM_EMAIL_ADDRESS; ?></label>
                <?php echo tep_draw_pull_down_menu('department_id', $departments_email_array, $department['department_id'], 'id="reply_from_email" class="wider"'); ?>
<?php
/*
                <div class="rspacer"><?php echo tep_draw_input_field('from_email_address', $department['email_address'], 'id="reply_from_email" class="wider"'); ?></div>
*/
?>
              </div>
            </div>
            <div class="cleaner vlinepad"></div>
            <div class="hideflow halfer">
              <label for="reply_to" class="reply_to_label"><?php echo TEXT_TO_NAME; ?></label>
              <div class="rspacer"><?php echo tep_draw_input_field('to_name', $entry['sender'], 'id="reply_to" class="wider"'); ?></div>
            </div>
            <div class="hideflow halfer">
              <div class="lspacer"><label for="reply_to_email" class="reply_to_label"><?php echo TEXT_TO_EMAIL_ADDRESS; ?></label>
                <div class="rspacer"><?php echo tep_draw_input_field('to_email_address', $entry['email_address'], 'id="reply_to_email" class="wider"'); ?></div>
              </div>
            </div>
            <div class="cleaner vlinepad"></div>
            <div class="hideflow halfer">
              <div><label for="reply_priority"><?php echo TEXT_PRIORITY; ?></label><?php echo tep_draw_pull_down_menu('priority_id', $priorities_array, $entry['priority_id'], 'id="reply_priority" class="wider"'); ?></div>
            </div>
            <div class="cleaner vlinepad"></div>
            <div><label class="floater"><?php echo TEXT_BODY; ?></label></div>
            <div class="hideflowend">
<?php
      if( $g_wp_ifc ) {
        echo '<a class="dataTableContentRed" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=change_wp&wp=0') . '">' . TEXT_INFO_DISABLE_WP . '</a>';
      } else {
        echo '<a class="dataTableContentRed" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=change_wp&wp=1') . '">' . TEXT_INFO_ENABLE_WP . '</a>';
      }
?>
            </div>
            <div class="cleaner"><?php echo tep_draw_textarea_field('body', $template, '', '15'); ?></div>
            <div class="formButtons inimg tmargin">
              <label class="floater"><?php echo TEXT_INFO_INSERT_IMAGES . ':'; ?></label>
              <div class="floater rspacer"><?php echo '<a href="#" id="image_selection">' . tep_image(DIR_WS_ICONS . 'icon_images_head.png', TEXT_INFO_INSERT_IMAGES) . '</a>'; ?></div>
              <label class="floater"><?php echo TEXT_INFO_UPLOAD_IMAGES . ':'; ?></label>
              <div class="floater"><?php echo '<a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a>'; ?></div>
<?php
      $templates_query_raw = "select template_id as id, template_title as text from " . TABLE_TEMPLATES . " where group_id = '" . TEMPLATE_HELPDESK_GROUP . "' order by template_title";
      $templates_array = $g_db->query_to_array($templates_query_raw);
      if( count($templates_array) ) {
?>
              <div class="floatend"><label for="template_list" class="floater"><?php echo TEXT_INFO_TEMPLATES; ?></label>
                <div class="floater hspacer"><?php echo tep_draw_pull_down_menu('template_list', $templates_array, '', 'id="template_list"'); ?></div>
                <div class="floater"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=template') . '" id="set_template">' . tep_image(DIR_WS_ICONS . 'icon_arrow_up.png', TEXT_INFO_INSERT_TEMPLATE) . '</a>'; ?></div>
                <div class="floater rspacer"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=template') . '" id="view_template" target="_blank" title="' . TEXT_INFO_VIEW_TEMPLATE . '">' . tep_image(DIR_WS_ICONS . 'icon_question.png', TEXT_INFO_VIEW_TEMPLATE) . '</a>'; ?></div>
              </div>
<?php
      }
?>
            </div>
            <div class="bounder inimg vmargin add_field_section">
              <label class="floater"><?php echo TEXT_INFO_ATTACH_FILE . ':'; ?></label>
              <div class="floater rspacer"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params()) . '" class="add_button">' . tep_image(DIR_WS_ICONS . 'icon_arrow_down.png', TEXT_INFO_ADD_FILES) . '</a>'; ?></div>
              <div class="floater add_field"><?php echo tep_draw_file_field('attach_file[]', 'class="wider"'); ?></div>
            </div>

            <div class="formButtons">
<?php
      if( $subaction != 'new' ) {
        $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'action', 'subaction') . 'action=view&he_id=' . $entry['helpdesk_entries_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
      } else {
        $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('he_id', 'action', 'subaction') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
      }
      $buttons[] = tep_image_submit('button_confirm.gif', IMAGE_CONFIRM, 'class="dflt" name="update"');
      echo implode('', $buttons);
?>
            </div>

          </fieldset></form></div>
          <div class="vlinepad"></div>
<?php
    } elseif( $subaction == 'edit') {
?>
          <div class="formArea"><?php echo tep_draw_form('edit', $g_script, tep_get_all_get_params('action', 'subaction') . 'action=save_entry'); ?><fieldset><legend><?php echo TEXT_UPDATE_INTRO; ?></legend>
            <div class="floater halfer">
              <label for="edit_department"><?php echo TEXT_DEPARTMENT; ?></label><?php echo tep_draw_pull_down_menu('department', $departments_array, $entry['department_id'], 'id="edit_department" class="wider"'); ?>
            </div>
            <div class="floater halfer">
              <div class="lspacer"><label for="edit_status"><?php echo TEXT_STATUS; ?></label><?php echo tep_draw_pull_down_menu('status', $statuses_array, $entry['status_id'], 'id="edit_status" class="wider"'); ?></div>
            </div>
            <div class="cleaner vlinepad"></div>
            <div class="floater halfer">
              <label for="edit_subject"><?php echo TEXT_SUBJECT; ?></label>
              <div class="rspacer"><?php echo tep_draw_input_field('subject', $entry['subject'], 'id="edit_subject" class="wider"'); ?></div>
            </div>
            <div class="floater halfer">
              <div class="lspacer"><label for="edit_priority"><?php echo TEXT_PRIORITY; ?></label><?php echo tep_draw_pull_down_menu('priority', $priorities_array, $entry['priority_id'], 'id="edit_priority" class="wider"'); ?></div>
            </div>
            <div class="cleaner vlinepad"></div>
            <div class="floater halfer">
              <label for="edit_from"><?php echo TEXT_FROM_NAME; ?></label>
              <div class="rspacer"><?php echo tep_draw_input_field('from_name', $department['title'], 'id="edit_from" class="wider"'); ?></div>
            </div>
            <div class="floater halfer">
              <div class="lspacer"><label for="edit_from_email"><?php echo TEXT_FROM_EMAIL_ADDRESS; ?></label>
                <div class="rspacer"><?php echo tep_draw_input_field('from_email_address', $department['email_address'], 'id="edit_from_email" class="wider"'); ?></div>
              </div>
            </div>
            <div class="cleaner vlinepad"></div>
            <div class="floater halfer">
              <label for="edit_to"><?php echo TEXT_TO_NAME; ?></label>
              <div class="rspacer"><?php echo tep_draw_input_field('to_name', $entry['sender'], 'id="reply_to" class="wider"'); ?></div>
            </div>
            <div class="floater halfer">
              <div class="lspacer"><label for="edit_to_email"><?php echo TEXT_TO_EMAIL_ADDRESS; ?></label>
                <div class="rspacer"><?php echo tep_draw_input_field('to_email_address', $entry['email_address'], 'id="edit_to_email" class="wider"'); ?></div>
              </div>
            </div>
            <div class="cleaner vlinepad"></div>
            <label><?php echo TEXT_BODY; ?></label>
            <div class="cleaner"><?php echo tep_draw_textarea_field('body', $entry['body'], '', '15'); ?></div>

            <div class="formButtons inimg tmargin">
              <label class="floater"><?php echo TEXT_INFO_INSERT_IMAGES . ':'; ?></label>
              <div class="floater rspacer"><?php echo '<a href="#" id="image_selection">' . tep_image(DIR_WS_ICONS . 'icon_images_head.png', TEXT_INFO_INSERT_IMAGES) . '</a>'; ?></div>
              <label class="floater"><?php echo TEXT_INFO_UPLOAD_IMAGES . ':'; ?></label>
              <div class="floater"><?php echo '<a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a>'; ?></div>
<?php
      $templates_query_raw = "select template_id as id, template_title as text from " . TABLE_TEMPLATES . " where group_id = '" . TEMPLATE_HELPDESK_GROUP . "' order by template_title";
      $templates_array = $g_db->query_to_array($templates_query_raw);
      if( count($templates_array) ) {
?>
              <div class="floatend"><label for="template_list" class="floater"><?php echo TEXT_INFO_TEMPLATES; ?></label>
                <div class="floater hspacer"><?php echo tep_draw_pull_down_menu('template_list', $templates_array, '', 'id="template_list"'); ?></div>
                <div class="floater"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=template') . '" id="set_template">' . tep_image(DIR_WS_ICONS . 'icon_arrow_up.png', TEXT_INFO_INSERT_TEMPLATE) . '</a>'; ?></div>
                <div class="floater rspacer"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=template') . '" id="view_template" target="_blank" title="' . TEXT_INFO_VIEW_TEMPLATE . '">' . tep_image(DIR_WS_ICONS . 'icon_question.png', TEXT_INFO_VIEW_TEMPLATE) . '</a>'; ?></div>
              </div>
<?php
      }
?>
            </div>

          </fieldset></form></div>
<?php
    } elseif ($subaction == 'delete') {
      $check_query = $g_db->query("select subject from " . TABLE_HELPDESK_ENTRIES . " where helpdesk_entries_id = '" . (int)$he_id . "' and parent_id = '0'");
      if( $g_db->num_rows($check_query) ) {
        $check_array = $g_db->fetch_array($check_query);
?>
          <div class="formArea"><?php echo tep_draw_form('delete_single', $g_script, tep_get_all_get_params('action', 'subaction') . 'action=delete_confirm_single'); ?><fieldset><legend><?php echo TEXT_INFO_DELETE_INTRO; ?></legend>
            <div class="floater halfer heavy"><?php echo $check_array['subject']; ?></div>
            <div class="floater halfer"><?php echo tep_draw_checkbox_field('whole', 'true') . '&nbsp;' . TEXT_INFO_DELETE_WHOLE_THREAD; ?></div>
            <div class="formButtons inimg tmargin">
              <div class="floater">
<?php
        $buttons_array = array(
          tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'action', 'subaction') . 'action=view&he_id=' . $entry['helpdesk_entries_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'
        );
        echo implode('', $buttons_array);
?>
              </div>
            </div>
          </fieldset></form></div>
<?php
      } else {
?>
          <div class="comboHeading"><?php echo TEXT_INFO_DELETE_INVALID; ?></div>
          <div class="formButtons inimg tmargin">
            <div class="floater">
<?php
        $buttons_array = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'action', 'subaction') ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'
        );
        echo implode('', $buttons_array);
?>
            </div>
          </div>
<?php
      }
    } else {
?>
          <div class="comboHeading"><?php echo tep_draw_form('ticket', $g_script, tep_get_all_get_params('action', 'subaction') . 'action=updatestatus'); ?><table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td><?php echo TEXT_DEPARTMENT; ?></td>
              <td><?php echo tep_draw_pull_down_menu('department', $departments_array, $entry['department_id']); ?></td>
              <td><?php echo TEXT_STATUS; ?></td>
              <td><?php echo tep_draw_pull_down_menu('status', $statuses_array, $entry['status_id']); ?></td>
              <td><?php echo TEXT_PRIORITY; ?></td>
              <td><?php echo tep_draw_pull_down_menu('priority', $priorities_array, $entry['priority_id']); ?></td>
              <td><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
              <td class="ralign"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'ticket', 'action', 'subaction') . 'action=view&subaction=new') . '">' . tep_image_button('button_new.gif', IMAGE_NEW) . '</a>'; ?></td>
            </tr>
          </table></form></div>
<?php
    }
    if( $subaction != 'delete' && $subaction != 'new' ) {

?>
          <div class="formArea"><fieldset><legend><?php echo TEXT_MESSAGE_INTRO; ?></legend>
            <div class="hbound linepad" style="background: #FFC">
              <div class="floater quarter"><?php echo TEXT_TO; ?></div>
              <div class="floater quarter3"><?php echo $entry['receiver'] . ' (' . $entry['receiver_email_address'] . ')'; ?></div>
              <div class="floater quarter"><?php echo TEXT_FROM; ?></div>
              <div class="floater quarter3"><?php echo $entry['sender'] . ' (' . $entry['email_address'] . ') (' . $entry['host'] . ')'; ?></div>
              <div class="floater quarter"><?php echo TEXT_IP; ?></div>
              <div class="floater quarter3"><?php echo $entry['ip_address']; ?></div>
              <div class="floater quarter"><?php echo TEXT_DATE; ?></div>
              <div class="floater quarter3"><?php echo $entry['datestamp_local'] . ' (' . TEXT_REMOTE . ' ' . $entry['datestamp'] . ')'; ?></div>
<?php
      $attachments_query = $g_db->query("select attachment from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id = '" . (int)$he_id . "'");
      if( $g_db->num_rows($attachments_query) ) {
?>
              <div class="floater quarter"><?php echo TEXT_ATTACHMENTS; ?></div>
              <div class="floater quarter3">
<?php
        while($attachments_array = $g_db->fetch_array($attachments_query) ) {
?>
                <div><?php echo '<a href="' . $g_relpath . HELPDESK_ATTACHMENTS_FOLDER . basename($attachments_array['attachment']) . '" class="heavy" target="_blank">' . basename($attachments_array['attachment']) . '</a>'; ?></div>
<?php
        }
?>
              </div>
              <div class="cleaner vlinepad"></div>
<?php
      }
?>
              <div class="floater quarter"><?php echo TEXT_MESSAGE; ?></div>
              <div class="required floater quarter3">
<?php
      echo $entry['subject']; 
      if( !empty($entry['ticket']) ) {
        echo ' [' . $entry['ticket'] . ']';
      }
?>
              </div>
            </div>
            <div class="cleaner vlinepad"></div>
            <div class="listArea linepad" style="height: 400px;">
<?php
       if( !empty($entry['text_body']) ) {
         $body = trim($entry['text_body']);
       } else {
         $body = trim(strip_tags($entry['body']));
       }
       echo $body;
       //echo nl2br($entry['body']); 
?>
            </div>
            <div class="formButtons inimg tmargin">
              <div class="floater">
<?php
      $buttons = array(
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'subaction') ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>',
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'ticket', 'action', 'subaction') . 'action=view&he_id=' . $entry['helpdesk_entries_id'] . '&subaction=reply') . '">' . tep_image_button('button_reply.gif', IMAGE_REPLY) . '</a>',
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'ticket', 'action', 'subaction') . 'action=view&he_id=' . $entry['helpdesk_entries_id'] . '&subaction=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>',
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'ticket', 'action', 'subaction') . 'action=view&he_id=' . $entry['helpdesk_entries_id'] . '&subaction=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>',
      );
?>
              </div>
              <div class="floater"><?php echo implode('', $buttons); ?></div>
            </div>
<?php
      $threads_query = $g_db->query("select helpdesk_entries_id, ticket_id, subject, sender, datestamp_local, datestamp, entry_read from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$ticket_id . "' order by helpdesk_entries_id desc");
?>

            <div class="listArea tmargin"><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_SUBJECT; ?></th>
                <th><?php echo TABLE_HEADING_SENDER; ?></h>
                <th class="calign"><?php echo TABLE_HEADING_DATE; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_ATTACHMENTS; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
<?php
      while( $threads = $g_db->fetch_array($threads_query) ) {
        if ($entry['helpdesk_entries_id'] == $threads['helpdesk_entries_id']) {
          echo '                  <tr class="dataTableRowSelected">' . "\n";
        } else {
          echo '                  <tr class="dataTableRow row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'action', 'subaction') . 'he_id=' . $threads['helpdesk_entries_id'] . '&action=view') . '">' . "\n";
        }
?>
                <td><?php echo $threads['subject']; ?></td>
                <td><?php echo $threads['sender']; ?></td>
                <td class="calign"><?php echo $threads['datestamp_local']; ?></td>
                <td class="calign">
<?php 
        $attachments_query = $g_db->query("select count(*) as total from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id = '" . (int)$threads['helpdesk_entries_id'] . "'");
        $attachments_array = $g_db->fetch_array($attachments_query);
        echo $attachments_array['total']; 
?>
                </td>
                <td class="tinysep calign">
<?php
        echo '<a href="' . tep_href_link(FILENAME_HELPDESK_HTML, 'he_id=' . $threads['helpdesk_entries_id']) . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_html_source.png', TEXT_VIEW_HTML_CODE_DATE . ' ' . $threads['datestamp_local']) . '</a>';
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'action', 'subaction') . 'he_id=' . $threads['helpdesk_entries_id'] . '&action=view&subaction=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' - ' . $threads['datestamp_local']) . '</a>';
        echo '<a href="' . tep_href_link(FILENAME_HELPDESK_HTML, 'he_id=' . $threads['helpdesk_entries_id'] . '&unsafe=1') . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_html.png', TEXT_VIEW_HTML_DATE . ' ' . $threads['datestamp_local']) . '</a>';
        $entry_icon = (($threads['entry_read'] != '1') ? tep_image(DIR_WS_ICONS . 'icon_unread.png', ICON_UNREAD) : tep_image(DIR_WS_ICONS . 'icon_preview.png', ICON_PREVIEW));
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('he_id', 'ticket_id', 'action', 'subaction') . 'he_id=' . $threads['helpdesk_entries_id'] . '&action=view') . '">' . $entry_icon . '</a>';
?>
                </td>
              </tr>
<?php
        }
?>
            </table></div>
          </fieldset></div>

<?php
      if( empty($subaction) ) {
        $internal_query = $g_db->query("select comment, datestamp_comment from " . TABLE_HELPDESK_TICKETS . " where ticket_id = '" . $g_db->input($ticket_id) . "'");
        $internal = $g_db->fetch_array($internal_query);
?>
          <div class="formArea"><?php echo tep_draw_form('internal', $g_script, tep_get_all_get_params('action', 'subaction') . 'action=updatecomment'); ?><fieldset><legend><?php echo TEXT_INTERNAL_COMMENTS; ?></legend>
<?php
        if( tep_not_null($internal['datestamp_comment']) ) {
?>
            <div><?php echo TEXT_LAST_UPDATE . ' ' . $internal['datestamp_comment']; ?></div>
<?php
        }
?>
            <div class="rspacer"><?php echo tep_draw_textarea_field('comment', $internal['comment'], '', '10', 'class="wider"'); ?></div>
            <div class="formButtons"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></div>
          </fieldset></form></div>

<?php
      }
    }
  } elseif($action == 'delete') {
?>
          <div class="formArea"><?php echo tep_draw_form('delete', $g_script, tep_get_all_get_params('action','subaction','ticket') . 'action=delete_confirm'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo TABLE_HEADING_TICKET; ?></th>
              <th><?php echo TABLE_HEADING_SENDER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_LAST_POST; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_PRIORITY; ?></th>
            </tr>

<?php
      foreach($_POST['ticket_id'] as $key => $value) {
        $ticket_name = (isset($_POST['ticket_name'][$key]) && !empty($_POST['ticket_name'][$key]))?$g_db->prepare_input($_POST['ticket_name'][$key]):'--';
?>
            <tr class="dataTableRow">
              <td class="calign"><?php echo $ticket_name . tep_draw_hidden_field('ticket_id[' . $key . ']', $key); ?></td>
              <td><?php echo $g_db->prepare_input($_POST['sender'][$key]); ?></td>
              <td class="calign"><?php echo tep_date_short($_POST['datestamp_last_entry'][$key]); ?></td>
              <td class="calign"><?php echo $_POST['status'][$key]; ?></td>
              <td class="calign"><?php echo $_POST['priority'][$key]; ?></td>
            </tr>
<?php
      }
?>
            <tr>
              <td colspan="5" class="formButtons">
<?php 
      $buttons_array = array(
        tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
       '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action','subaction','ticket') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'
      );
      echo implode('', $buttons_array);
?>
              </td>
            </tr>
          </table></form></div>
<?php
  } else {
?>
          <div class="formArea"><?php echo tep_draw_form('cross', $g_script, tep_get_all_get_params('action','subaction') . 'action=delete', 'post', 'enctype="multipart/form-data"'); ?>
          <div class="comboHeading">
<?php
    $buttons_array = array(
       tep_image_submit('button_delete.gif', IMAGE_DELETE),
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'subaction') . 'action=view&subaction=new') . '">' . tep_image_button('button_new.gif', IMAGE_NEW) . '</a>'
    );
    echo implode('', $buttons_array);
?>
           </div>
           <table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo '<a href="#ticket_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_TICKET; ?></th>
              <th><?php echo TABLE_HEADING_SUBJECT; ?></th>
              <th><?php echo TABLE_HEADING_SENDER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_LAST_POST; ?></th>
              <th><?php echo TABLE_HEADING_STATUS; ?></th>
              <th><?php echo TABLE_HEADING_PRIORITY; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_IP; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ATTACHMENTS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
<?php
    $ticket = isset($_GET['ticket'])?$g_db->prepare_input($_GET['ticket']):'';
    $keyword = isset($_GET['keyword'])?$g_db->prepare_input($_GET['keyword']):'';
    $group_string = '';
    $tickets_query_raw = "select ht.ticket_id, ht.ticket, ht.datestamp_last_entry, ht.department_id, ht.status_id, ht.priority_id from";
    $where_array = $order_array = array();
    $tables_array = array(TABLE_HELPDESK_TICKETS . " ht");

    if( !empty($entry_filter) || !empty($keyword) ) {
      $tables_array[] = TABLE_HELPDESK_ENTRIES . " he";
      $group_string = " group by ht.ticket_id ";
      $where_array[] = "ht.ticket_id = he.ticket_id";

      if( !empty($entry_filter) ) {
        $where_array[] = "he.entry_read = '0'";
      }
      if( !empty($keyword) ) {
        $where_array[] = "(he.subject like '" . $g_db->input($keyword) . "%' or he.subject like '% " . $g_db->input($keyword) . "%' or he.body like '" . $g_db->input($keyword) . "%' or he.body like '% " . $g_db->input($keyword) . "%')";
      }
    }

    if( !empty($ticket) ) {
      $where_array[] = "ht.ticket = '" . $g_db->input($ticket) . "'";
    }

    if( !empty($department_filter) ) {
      $where_array[] = "ht.department_id = '" . $g_db->input($department_filter) . "'";
    }
    if( !empty($status_filter) ) {
      $where_array[] = "ht.status_id = '" . $g_db->input($status_filter) . "'";
    }
    if( !empty($priority_filter) ) {
      $where_array[] = "ht.priority_id = '" . $g_db->input($priority_filter) . "'";
    }
    $order_array[] = "ht.ticket_id desc";

    $tables_string = implode(', ', $tables_array);
    $where_string = '';


    if( !empty($where_array) ) {
      $where_string = "where " . implode(' and ', $where_array);
    }
    $order_string = "order by " . implode(',', $order_array);

    $tickets_query_raw .= "  " . $tables_string . "  " . $where_string . $group_string . $order_string;

    $entries_split = new splitPageResults($tickets_query_raw, MAX_DISPLAY_ADMIN_HELP_DESK);
    $entries_query = $g_db->query($entries_split->sql_query);

    $entry_status_array = tep_array_invert_flat($statuses_array, 'id', 'text');
    $entry_priority_array = tep_array_invert_flat($priorities_array, 'id', 'text');
    $entry_department_email_array = tep_array_invert_flat($departments_email_array, 'id', 'text');

    $row_array = array('dataTableRowGreenLite', 'dataTableRowYellowLow', 'dataTableRowHigh', 'dataTableRowBlueLite');
    $rows = 0;

    while( $entries = $g_db->fetch_array($entries_query) ) {

      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

      $ticket_query = $g_db->query("select helpdesk_entries_id, sender, subject, ip_address, datestamp from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$entries['ticket_id'] . "' and parent_id = '0'");
      $ticket_array = $g_db->fetch_array($ticket_query);

      $entries_query_raw = "select helpdesk_entries_id from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$entries['ticket_id'] . "'";
      $entries_array = $g_db->query_to_array($entries_query_raw, 'helpdesk_entries_id');

      $postings_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$entries['ticket_id'] . "' and parent_id != '0'");
      $postings = $g_db->fetch_array($postings_query);

      $unread_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$entries['ticket_id'] . "' and entry_read = '0'");
      $unread = $g_db->fetch_array($unread_query);

      $last_post_query = $g_db->query("select email_address from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$entries['ticket_id'] . "' order by helpdesk_entries_id desc limit 1");
      $last_post = $g_db->fetch_array($last_post_query);

      if( ( empty($ticket_id) || $ticket_id == $entries['ticket_id']) && !isset($tInfo) && substr($action, 0, 3) != 'new') {
        $tInfo = new objectInfo(array_merge($entries, $ticket_array));
      }

      if( $postings['count'] ) {
        $row_class = $row_array[0];
      }

      if( $postings['count'] > 5 ) {
        $row_class = $row_array[1];
      }

      if( $postings['count'] > 10 ) {
        $row_class = $row_array[2];
      }

      if( $unread['count'] ) {
        $row_class .=  ' heavy';
      }

      $sel_link = tep_href_link($g_script, tep_get_all_get_params('ticket_id', 'action', 'subaction') . 'ticket_id=' . $entries['ticket_id'] . '&action=view');
      $inf_link = tep_href_link($g_script, tep_get_all_get_params('ticket_id', 'action', 'subaction') . 'ticket_id=' . $entries['ticket_id']);

      if( isset($tInfo) && is_object($tInfo) && $entries['ticket_id'] == $tInfo->ticket_id ) {
        echo '                  <tr class="dataTableRowSelected row_link" href="' . $sel_link . '">' . "\n";
      } else {
        echo '                  <tr class="' . $row_class . ' row_link" href="' . $inf_link . '">' . "\n";
      }

?>
              <td class="calign">
<?php 
      echo tep_draw_checkbox_field('ticket_id[' . $entries['ticket_id'] . ']', '', false) . tep_draw_hidden_field('ticket_name[' . $entries['ticket_id'] . ']', $entries['ticket']);
      $status_name = $entry_status_array[$entries['status_id']];
      $priority_name = $entry_priority_array[$entries['priority_id']];
      $department_email = $entry_department_email_array[$entries['department_id']];

      $hidden_array = array(
        tep_draw_hidden_field('sender[' . $entries['ticket_id'] . ']', $ticket_array['sender']),
        tep_draw_hidden_field('datestamp_last_entry[' . $entries['ticket_id'] . ']', $entries['datestamp_last_entry']),
        tep_draw_hidden_field('status[' . $entries['ticket_id'] . ']', $status_name),
        tep_draw_hidden_field('priority[' . $entries['ticket_id'] . ']', $priority_name),
      );
      echo implode('', $hidden_array);
?>
              </td>
              <td class="calign"><?php echo !empty($entries['ticket'])?$entries['ticket']:'--'; ?></td>
              <td><?php echo $ticket_array['subject'] . ' (' . $postings['count'] . ')'; ?></td>
              <td><?php echo $ticket_array['sender']; ?></td>
              <td class="calign"><?php echo tep_datetime_short($ticket_array['datestamp']); ?></td>
              <td><?php echo $status_name; ?></td>
              <td><?php echo $priority_name; ?></td>
              <td class="calign"><?php echo $ticket_array['ip_address']; ?></td>
              <td class="calign">
<?php
      $attachments_array = array('total' => 0);
      if( !empty($entries_array) ) {
        $attachments_query = $g_db->query("select count(*) as total from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id in (" . implode(',', array_keys($entries_array)) . ")");
        $attachments_array = $g_db->fetch_array($attachments_query);
      }
      echo $attachments_array['total'];
?>
              </td>
              <td class="calign tinysep">
<?php
      $icons = array();
      $entry_icon = (($unread['count'] > 0) ? tep_image(DIR_WS_ICONS . 'icon_unread.png', ICON_UNREAD) : tep_image(DIR_WS_ICONS . 'icon_read.png', ICON_PREVIEW));
      $icons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('ticket_id', 'action', 'subaction') . 'ticket_id=' . $entries['ticket_id'] . '&action=view') . '">' . $entry_icon . '</a>';
      $icons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('ticket_id', 'action', 'subaction') . 'ticket_id=' . $entries['ticket_id'] . '&action=view&subaction=reply') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT) . '</a>';

      if( $department_email == $last_post['email_address']) {
        $icons[] = tep_image(DIR_WS_ICONS . 'icon_outgoing.png', ICON_OUTGOING);
      } else {
        $icons[] = tep_image(DIR_WS_ICONS . 'icon_incoming.png', ICON_INCOMING);
      }
      echo implode('', $icons);
?>
              </td>
            </tr>
<?php
    }
    $buttons_array = array(
       tep_image_submit('button_delete.gif', IMAGE_DELETE),
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'subaction') . 'action=view&subaction=new') . '">' . tep_image_button('button_new.gif', IMAGE_NEW) . '</a>'
    );
?>
          </table><div class="formButtons"><?php echo implode('', $buttons_array); ?></div></form></div>
          <div class="listArea splitLine">
            <div class="floater"><?php echo $entries_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $entries_split->display_links(tep_get_all_get_params('action', 'page') ); ?></div>
          </div>
<?php
  }
?>
        </div>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
