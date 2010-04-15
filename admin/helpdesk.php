<?php
/*
  $Id: helpdesk.php,v 1.6 2005/08/16 21:14:04 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $subaction = (isset($_GET['subaction']) ? $_GET['subaction'] : '');
  $page = (isset($_GET['page']) ? $_GET['page'] : 1);
  $ticket = (isset($_GET['ticket']) ? $g_db->prepare_input($_GET['ticket']) : '');
  $template_id = (isset($_GET['template_id']) ? (int)$_GET['template_id'] : '');
  $department_filter = (isset($_GET['department_filter']) ? (int)$_GET['department_filter'] : '');
  $status_filter = (isset($_GET['status_filter']) ? (int)$_GET['status_filter'] : '');
  $priority_filter = (isset($_GET['priority_filter']) ? (int)$_GET['priority_filter'] : '');
  $entry_filter = (isset($_GET['entry_filter']) ? (int)$_GET['entry_filter'] : '');

  switch ($action) {
    case 'reply_confirm':
      $id = $g_db->prepare_input($_GET['id']);
      $status_id = $g_db->prepare_input($_POST['status']);
      $from_name = $g_db->prepare_input($_POST['from_name']);
      $from_email_address = $g_db->prepare_input($_POST['from_email_address']);
      $to_name = $g_db->prepare_input($_POST['to_name']);
      $to_email_address = $g_db->prepare_input($_POST['to_email_address']);
      $subject = $g_db->prepare_input($_POST['subject']);
      $body = $g_db->prepare_input($_POST['body']);

      $sql_data_array = array('ticket' => $ticket,
                              'parent_id' => $id,
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
                              'entry_read' => '1');

      $g_db->perform(TABLE_HELPDESK_ENTRIES, $sql_data_array);
      $sql_data_array = array('status_id' => $status_id,
                              'datestamp_last_entry' => 'now()');
      $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', "ticket = '" . $g_db->input($ticket) . "'");
      tep_mail($to_name, $to_email_address, $subject, $body, $from_name, $from_email_address);

      $messageStack->add_session(SUCCESS_REPLY_PROCESSED, 'success');
      tep_redirect(tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view'));
      break;
    case 'save_entry':
      $department_id = $g_db->prepare_input($_POST['department']);
      $status_id = $g_db->prepare_input($_POST['status']);
      $priority_id = $g_db->prepare_input($_POST['priority']);

      $id = $g_db->prepare_input($_GET['id']);
      $from_name = $g_db->prepare_input($_POST['from_name']);
      $from_email_address = $g_db->prepare_input($_POST['from_email_address']);
      $to_name = $g_db->prepare_input($_POST['to_name']);
      $to_email_address = $g_db->prepare_input($_POST['to_email_address']);
      $subject = $g_db->prepare_input($_POST['subject']);
      $body = $g_db->prepare_input($_POST['body']);

      $entry_query = $g_db->query("select ticket, datestamp_local from " . TABLE_HELPDESK_ENTRIES . " where helpdesk_entries_id = '" . $g_db->input($id) . "'");
      $entry = $g_db->fetch_array($entry_query);

      $sql_data_array = array('department_id' => $department_id,
                              'status_id' => $status_id,
                              'priority_id' => $priority_id);

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

          $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', "ticket = '" . $g_db->input($ticket) . "'");
        } else {
          $sql_data_array['ticket'] = $ticket;
          $sql_data_array['datestamp_last_entry'] = $entry['datestamp_local'];

          $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array);
        }
      }

      $sql_data_array = array('ticket' => $ticket,
                              'receiver' => $to_name,
                              'receiver_email_address' => $to_email_address,
                              'sender' => $from_name,
                              'email_address' => $from_email_address,
                              'subject' => $subject,
                              'body' => $body);

      if ($new_ticket == true) $sql_data_array['parent_id'] = '0';

      $g_db->perform(TABLE_HELPDESK_ENTRIES, $sql_data_array, 'update', "helpdesk_entries_id = '" . $g_db->input($id) . "'");

      $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $entry['ticket'] . "'");
      $check = $g_db->fetch_array($check_query);

      $ticket_exists = true;
      if ($check['count'] < 1) {
         $ticket_exists = false;
        $g_db->query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $entry['ticket'] . "'");
      }

      $messageStack->add_session(SUCCESS_ENTRY_UPDATED, 'success');
      tep_redirect(tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $ticket . '&action=view&id=' . $_GET['id']));
      break;
    case 'delete_confirm':

      foreach( $_POST['ticket'] as $key => $value ) {
        $ticket = $g_db->prepare_input($key);
        $g_db->query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $g_db->input($ticket) . "'");
        $g_db->query("delete from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "'");

        $check_query = $g_db->query("select attachment from " . TABLE_HELPDESK_ATTACHMENTS . " where ticket = '" . $g_db->input($ticket) . "'");
        while($check_array = $g_db->fetch_array($check_query) ) {
          if( file_exists(HELPDESK_ATTACHMENTS_FOLDER . $check_array['attachment']) ) {
            unlink(HELPDESK_ATTACHMENTS_FOLDER . $check_array['attachment']);
          }
        }
        $g_db->query("delete from " . TABLE_HELPDESK_ATTACHMENTS . " where ticket = '" . $g_db->input($ticket) . "'");
      }
      $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
/*
      $ticket = $g_db->prepare_input($_GET['ticket']);
      $id = $g_db->prepare_input($_GET['id']);
      $whole = $g_db->prepare_input($_POST['whole']);

      if ($whole == 'true') {
        $g_db->query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $g_db->input($ticket) . "'");
        $g_db->query("delete from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "'");

        $messageStack->add_session(SUCCESS_WHOLE_THREAD_REMOVED, 'success');
      } else {
        $g_db->query("delete from " . TABLE_HELPDESK_ENTRIES . " where helpdesk_entries_id = '" . $g_db->input($id) . "'");

        $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "'");
        $check = $g_db->fetch_array($check_query);

        if ($check['count'] > 0) {
          tep_redirect(tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view'));
        } else {
          $g_db->query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $g_db->input($ticket) . "'");
        }
        $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
      }
*/
      tep_redirect(tep_href_link(basename($PHP_SELF), 'page=' . $page));
      break;

    case 'delete_confirm_single':
      $id = (int)$_GET['id'];
      $whole = (isset($_POST['whole']) ? $g_db->prepare_input($_POST['whole']) : '');

      if ($whole == 'true') {
        $g_db->query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $g_db->input($ticket) . "'");
        $g_db->query("delete from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "'");
        $check_query = $g_db->query("select attachment from " . TABLE_HELPDESK_ATTACHMENTS . " where ticket = '" . $g_db->input($ticket) . "'");
        while($check_array = $g_db->fetch_array($check_query) ) {
          if( file_exists(HELPDESK_ATTACHMENTS_FOLDER . $check_array['attachment']) ) {
            unlink(HELPDESK_ATTACHMENTS_FOLDER . $check_array['attachment']);
          }
        }
        $g_db->query("delete from " . TABLE_HELPDESK_ATTACHMENTS . " where ticket = '" . $g_db->input($ticket) . "'");

        $messageStack->add_session(SUCCESS_WHOLE_THREAD_REMOVED, 'success');
      } else {

        $g_db->query("delete from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "' and helpdesk_entries_id = '" . (int)$id . "'");

        $check_query = $g_db->query("select attachment from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id= '" . (int)$id . "'");
        while($check_array = $g_db->fetch_array($check_query) ) {
          if( file_exists(HELPDESK_ATTACHMENTS_FOLDER . $check_array['attachment']) ) {
            unlink(HELPDESK_ATTACHMENTS_FOLDER . $check_array['attachment']);
          }
        }
        $g_db->query("delete from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id = '" . (int)$id . "'");

        $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "' and parent_id=0");
        $check = $g_db->fetch_array($check_query);

        if ($check['count'] > 0) {
          $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
          tep_redirect(tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view'));
        } else {
          $g_db->query("delete from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "'");
          $g_db->query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $g_db->input($ticket) . "'");
        }
        $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
      }
      tep_redirect(tep_href_link(basename($PHP_SELF), 'page=' . $page));
      break;
    case 'updatestatus':
      $department_id = $g_db->prepare_input($_POST['department']);
      $status_id = $g_db->prepare_input($_POST['status']);
      $priority_id = $g_db->prepare_input($_POST['priority']);

      $g_db->query("update " . TABLE_HELPDESK_TICKETS . " set department_id = '" . $g_db->input($department_id) . "', status_id = '" . $g_db->input($status_id) . "', priority_id = '" . $g_db->input($priority_id) . "' where ticket = '" . $g_db->input($ticket) . "'");

      $messageStack->add_session(SUCCESS_TICKET_UPDATED, 'success');
      tep_redirect(tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $ticket . '&action=view'));
      break;
    case 'view':
      $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "'");
      $check = $g_db->fetch_array($check_query);

      if ($check['count'] < 1) {
        $messageStack->add_session(ERROR_TICKET_DOES_NOT_EXIST, 'error');
        tep_redirect(tep_href_link(basename($PHP_SELF), 'ticket=' . $ticket));
      }
      break;
    case 'updatecomment':
      $comment = $g_db->prepare_input($_POST['comment']);

      $sql_data_array = array('comment' => $comment,
                              'datestamp_comment' => 'now()');

      $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', "ticket = '" . $g_db->input($ticket) . "'");

      $messageStack->add_session(SUCCESS_COMMENT_UPDATED, 'success');
      tep_redirect(tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view'));
      break;
    case 'delete':
      if( !isset($_POST['ticket']) || !is_array($_POST['ticket']) || !count($_POST['ticket']) ) {
        $messageStack->add_session(ERROR_TICKET_DOES_NOT_EXIST, 'error');
        tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action','subaction','ticket')) . '&action=view'));
      }
      break;
    default:
      break;
  }

  $statuses_array = array();
  $priorities_array = array();
  $departments_array = array();
  $entries_array = array(array('id' => '0', 'text' => TEXT_ALL_ENTRIES),
                         array('id' => '1', 'text' => TEXT_ONLY_NEW_ENTRIES));

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

  $departments_query = $g_db->query("select department_id, title from " . TABLE_HELPDESK_DEPARTMENTS . " order by title");
  while ($departments = $g_db->fetch_array($departments_query)) {
    $departments_array[] = array('id' => $departments['department_id'], 'text' => $departments['title']);
  }
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php
  if( $action == 'view' && ($subaction == 'edit' || $subaction == 'reply') ) {
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/jquery/themes/smoothness/ui.all.css">
<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery-1.3.2.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.ajaxq.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.form.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/jquery/ui/jquery-ui-1.7.2.custom.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/config.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/image_control.js"></script>
<script language="javascript" type="text/javascript">
$(document).ready(function(){
  var jqWrap = tinymce_ifc;
  // Initialize JS variables with PHP parameters to be passed to the js file
  jqWrap.TinyMCE = '<?php echo $g_relpath . DIR_WS_INCLUDES . 'javascript/tiny_mce/tiny_mce.js'; ?>';
  jqWrap.baseFront = '<?php echo $g_server . DIR_WS_CATALOG; ?>';
  jqWrap.cssFront = '<?php echo $g_server . DIR_WS_CATALOG . 'stylesheet.css'; ?>';
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.areas = 'body';
  jqWrap.launch();

  var jqWrap = image_control;
  jqWrap.editObject = tinyMCE;
  jqWrap.baseFront = '<?php echo $g_server . DIR_WS_CATALOG; ?>';
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.launch();
});
</script>
<?php
  }
?>
<?php
  $set_focus = true;
  require('includes/objects/html_start_sub2.php'); 
?>
<?php
  if( $action == 'view' && ($subaction == 'edit' || $subaction == 'reply') ) {
?>
          <div id="modalBox" title="Image Selection" style="display:none;">Loading...Please Wait</div>
          <div id="ajaxLoader" title="Image Manager" style="display:none;"><img src="includes/javascript/jquery/themes/smoothness/images/ajax_load.gif"><p id="ajaxMsg" class="main">Updating, please wait...</p><hr /></div>
<?php
  }
?>
        <div class="maincell" style="width: 100%;">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
<?php
  if( empty($action) ) {
?>
          <div class="listArea"><table border="0" cellspacing="1" cellpadding="2">
            <tr>
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="smallText"><?php echo 'Keyword'; ?></td>
                  <td class="smallText"><?php echo tep_draw_form('keyword', basename($PHP_SELF), '', 'get') . tep_draw_input_field('keyword', '', 'size="10" maxlength="17"') . '</form>'; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo TEXT_TICKET_NUMBER; ?></td>
                  <td class="smallText"><?php echo tep_draw_form('ticket', basename($PHP_SELF), '', 'get') . tep_draw_hidden_field('page', $page) . tep_draw_input_field('ticket', '', 'size="10" maxlength="7"') . tep_draw_hidden_field('action', 'view') . '</form>'; ?></td>
                </tr>
              </table></td>
              <td align="right"><?php echo tep_draw_form('filter', basename($PHP_SELF), '', 'get'); ?><table border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td align="right"><table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                      <td class="smallText"><?php echo TEXT_ENTRIES; ?></td>
                      <td class="smallText" align="right"><?php echo tep_draw_pull_down_menu('entry_filter', $entries_array, $entry_filter, 'onchange="this.form.submit();"' . (!empty($entry_filter) ? ' style="background-color: #fedecb;"' : '')); ?></td>
                    </tr>
                    <tr>
                      <td class="smallText"><?php echo TEXT_DEPARTMENT; ?></td>
                      <td class="smallText" align="right"><?php echo tep_draw_pull_down_menu('department_filter', $departments_array, $department_filter, 'onChange="this.form.submit();"' . (!empty($department_filter) ? ' style="background-color: #fedecb;"' : '')); ?></td>
                    </tr>
                  </table></td>
                  <td><table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                      <td class="smallText"><?php echo TEXT_STATUS; ?></td>
                      <td class="smallText" align="right"><?php echo tep_draw_pull_down_menu('status_filter', $statuses_array, $status_filter, 'onchange="this.form.submit();"' . (!empty($status_filter) ? ' style="background-color: #fedecb;"' : '')); ?></td>
                    </tr>
                    <tr>
                      <td class="smallText"><?php echo TEXT_PRIORITY; ?></td>
                      <td class="smallText" align="right"><?php echo tep_draw_pull_down_menu('priority_filter', $priorities_array, $priority_filter, 'onchange="this.form.submit();"' . (!empty($priority_filter) ? ' style="background-color: #fedecb;"' : '')); ?></td>
                    </tr>
                  </table></td>
                </tr>
              </table></form></td>
            </tr>
          </table></div>
<?php
  }
  if($action == 'view') {
    $id = (isset($_GET['id']) ? (int)$_GET['id'] : '');

    if( !empty($id) ) {
      $entry_query = $g_db->query("select he.helpdesk_entries_id, he.ticket, ifnull(he.host, he.ip_address) as host, he.ip_address, he.datestamp_local, he.datestamp, he.receiver, he.receiver_email_address, he.sender, he.email_address, he.subject, he.body, he.entry_read, ht.department_id, ht.status_id, ht.priority_id from " . TABLE_HELPDESK_ENTRIES . " he left join " . TABLE_HELPDESK_TICKETS . " ht on (he.ticket = ht.ticket) where he.ticket = '" . $g_db->input($ticket) . "' and he.helpdesk_entries_id = '" . (int)$id . "'");
    } else {
      $entry_query = $g_db->query("select he.helpdesk_entries_id, he.ticket, ifnull(he.host, he.ip_address) as host, he.ip_address, he.datestamp_local, he.datestamp, he.receiver, he.receiver_email_address, he.sender, he.email_address, he.subject, he.body, he.entry_read, ht.department_id, ht.status_id, ht.priority_id from " . TABLE_HELPDESK_ENTRIES . " he left join " . TABLE_HELPDESK_TICKETS . " ht on (he.ticket = ht.ticket) where he.ticket = '" . $g_db->input($ticket) . "' and he.parent_id = '0'");
    }
    $entry = $g_db->fetch_array($entry_query);

// mark entry as read
    if ($entry['entry_read'] != '1') {
      $g_db->query("update " . TABLE_HELPDESK_ENTRIES . " set entry_read = '1' where ticket = '" . $entry['ticket'] . "' and helpdesk_entries_id = '" . $entry['helpdesk_entries_id'] . "'");
    }

    $department_query = $g_db->query("select hd.title, hd.email_address, hd.name from " . TABLE_HELPDESK_DEPARTMENTS . " hd, " . TABLE_HELPDESK_TICKETS . " ht where ht.ticket = '" . $g_db->input($ticket) . "' and ht.department_id = hd.department_id");
    $department = $g_db->fetch_array($department_query);

    if( $subaction == 'reply') {
      if( empty($id) ) {
        $entry_query = $g_db->query("select helpdesk_entries_id from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "' order by helpdesk_entries_id desc");
        $entry_array = $g_db->fetch_array($entry_query);
        $id = $entry_array['helpdesk_entries_id'];
      }
      $templates_array = $g_db->query_to_array("select gt.gtext_id as id, gt.gtext_title as text from " . TABLE_GTEXT . " gt left join " . TABLE_GTEXT_TO_DISPLAY . " g2d on (gt.gtext_id = g2d.gtext_id) where g2d.abstract_zone_id = " . GTEXT_HELPDESK_ZONE_ID . " order by g2d.sequence_order, gt.gtext_title");
?>
          <div class="comboHeading">
            <div><?php echo tep_draw_form('ticket', basename($PHP_SELF), '', 'get') . tep_draw_hidden_field('page', $page) . tep_draw_hidden_field('ticket', $_GET['ticket']) . tep_draw_hidden_field('action', 'view') . tep_draw_hidden_field('id', $id) . tep_draw_hidden_field('subaction', 'reply') . TEXT_TEMPLATES . ' ' . tep_draw_pull_down_menu('template_id', $templates_array, $template_id, 'onchange="this.form.submit();"') . '</form>'; ?></div>
          </div>
<?php
      $template = '';

      if( !empty($template_id) ) {
        $template_query = $g_db->query("select gtext_description from " . TABLE_GTEXT . " where gtext_id = '" . (int)$template_id . "'");
        $template = $g_db->fetch_array($template_query);
        $template = $template['gtext_description'];
      }

      $subject = $entry['subject'];
      if (!strstr($subject, '['. DEFAULT_HELPDESK_TICKET_PREFIX . $entry['ticket'] . ']')) {
        $subject = '[' .DEFAULT_HELPDESK_TICKET_PREFIX. $entry['ticket'] . '] ' . $subject;
      }
      $subject = 'RE: ' . $subject;
?>
          <div class="formArea"><?php echo tep_draw_form('reply', basename($PHP_SELF), 'page=' . $page . '&ticket=' . $ticket . '&id=' . $id . '&action=reply_confirm'); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="smallText"><?php echo TEXT_SEND_INTRO; ?></td>
                  <td align="right"><?php echo tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="smallText"><?php echo TEXT_STATUS; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_pull_down_menu('status', $statuses_array, $entry['status_id']); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo TEXT_FROM_NAME; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('from_name', $department['title']); ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo TEXT_FROM_EMAIL_ADDRESS; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('from_email_address', $department['email_address']); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo TEXT_TO_NAME; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('to_name', $entry['sender']); ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo TEXT_TO_EMAIL_ADDRESS; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('to_email_address', $entry['email_address']); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo TEXT_SUBJECT; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('subject', $subject); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="smallText" valign="top"><?php echo TEXT_BODY; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_textarea_field('body', 'virtual', '60', '10', $template, 'style="width: 100%"'); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                      <td class="smallText"><b><?php echo TEXT_INFO_INSERT_IMAGES . ':'; ?></b></td>
                      <td><?php echo '<a href="#" id="image_selection">' . tep_image(DIR_WS_ICONS . 'icon_images_head.png', TEXT_INFO_INSERT_IMAGES) . '</a>'; ?></td>
                      <td><?php echo tep_draw_separator('pixel_trans.gif', '30', '1'); ?></td>
                      <td class="smallText"><b><?php echo TEXT_INFO_UPLOAD_IMAGES . ':'; ?></b></td>
                      <td><?php echo '<a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a>'; ?></td>
                    </tr>
                  </table></td>
                </tr>
              </table></td>
            </tr>          
          </table></form></div>
<?php
    } elseif( $subaction == 'edit') {
?>
          <div class="formArea"><?php echo tep_draw_form('edit', basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&id=' . $_GET['id'] . '&action=save_entry'); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td><table border="0" width="100%" cellspacing="0" cellpadding="2" class="columnLeft">
                <tr>
                  <td class="smallText"><?php echo TEXT_UPDATE_INTRO; ?></td>
                  <td align="right"><?php echo tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
            <tr>
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="smallText"><?php echo TEXT_DEPARTMENT; ?></td>
                  <td class="smallText"><?php echo tep_draw_pull_down_menu('department', $departments_array, $entry['department_id']); ?></td>
                  <td class="smallText"><?php echo TEXT_STATUS; ?></td>
                  <td class="smallText"><?php echo tep_draw_pull_down_menu('status', $statuses_array, $entry['status_id']); ?></td>
                  <td class="smallText"><?php echo TEXT_PRIORITY; ?></td>
                  <td class="smallText"><?php echo tep_draw_pull_down_menu('priority', $priorities_array, $entry['priority_id']); ?></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
            <tr>
              <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="smallText"><?php echo TEXT_TICKET_NUMBER; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('ticket', $entry['ticket'], 'maxlength="7"'); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo TEXT_FROM_NAME; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('from_name', $entry['sender']); ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo TEXT_FROM_EMAIL_ADDRESS; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('from_email_address', $entry['email_address']); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo TEXT_TO_NAME; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('to_name', $entry['receiver']); ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo TEXT_TO_EMAIL_ADDRESS; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('to_email_address', $entry['receiver_email_address']); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo TEXT_SUBJECT; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('subject', $entry['subject']); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="smallText" valign="top"><?php echo TEXT_BODY; ?></td>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_textarea_field('body', 'virtual', '60', '10', $entry['body'], 'style="width: 100%"'); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                      <td class="smallText"><b><?php echo TEXT_INFO_INSERT_IMAGES . ':'; ?></b></td>
                      <td><?php echo '<a href="#" id="image_selection">' . tep_image(DIR_WS_ICONS . 'icon_images_head.png', TEXT_INFO_INSERT_IMAGES) . '</a>'; ?></td>
                      <td><?php echo tep_draw_separator('pixel_trans.gif', '30', '1'); ?></td>
                      <td class="smallText"><b><?php echo TEXT_INFO_UPLOAD_IMAGES . ':'; ?></b></td>
                      <td><?php echo '<a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a>'; ?></td>
                    </tr>
                  </table></td>
                </tr>
              </table></td>
            </tr>
          </table></form></div>
<?php
    } elseif ($subaction == 'delete') {
?>
          <div class="formArea"><?php echo tep_draw_form('delete_single', basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&id=' . $id . '&action=delete_confirm_single'); ?><table border="0" width="100%" cellspacing="0" cellpadding="2" class="columnLeft">
            <tr>
              <td class="smallText"><?php echo TEXT_DELETE_INTRO; ?></td>
              <td class="smallText"><?php echo tep_draw_checkbox_field('whole', 'true') . '&nbsp;' . TEXT_DELETE_WHOLE_THREAD; ?></td>
              <td align="right"><?php echo tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
            </tr>
          </table></form></div>
<?php
    } else {
?>

<?php
/*
          <div class="comboHeading">
            <div class="smallText" style="float: left;"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=reply') . '">' . tep_image_button('button_reply.gif', IMAGE_REPLY) . '</a>&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>'; ?></div>
            <div class="smallText" style="float: left; padding-left: 20px;"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></div>
          </div>
*/
?>
          <div><?php echo tep_draw_form('ticket', basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=updatestatus'); ?><table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td class="smallText"><?php echo TEXT_DEPARTMENT; ?></td>
              <td class="smallText"><?php echo tep_draw_pull_down_menu('department', $departments_array, $entry['department_id']); ?></td>
              <td class="smallText"><?php echo TEXT_STATUS; ?></td>
              <td class="smallText"><?php echo tep_draw_pull_down_menu('status', $statuses_array, $entry['status_id']); ?></td>
              <td class="smallText"><?php echo TEXT_PRIORITY; ?></td>
              <td class="smallText"><?php echo tep_draw_pull_down_menu('priority', $priorities_array, $entry['priority_id']); ?></td>
              <td class="smallText"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
            </tr>
          </table></form></div>
<?php
    }
    if( $subaction != 'delete' ) {
?>
          <div class="comboHeading">
            <div class="smallText floater"><b><?php echo $entry['subject']; ?></b></div>
            <div class="smallText floater charsep"><b><?php echo '[' . $entry['ticket'] . ']'; ?></b></div>
          </div>
          <div class="listArea smallText" style="background: #FFC;"><table border="0" cellspacing="0" cellpadding="3">
            <tr>
              <td><?php echo TEXT_TO; ?></td>
              <td><?php echo $entry['receiver'] . ' (' . $entry['receiver_email_address'] . ')'; ?></td>
            </tr>
            <tr>
              <td><?php echo TEXT_FROM; ?></td>
              <td><?php echo $entry['sender'] . ' (' . $entry['email_address'] . ') (' . $entry['host'] . ')'; ?></td>
            </tr>
            <tr>
              <td><?php echo TEXT_IP; ?></td>
              <td><?php echo $entry['ip_address']; ?></td>
            </tr>
            <tr>
              <td><?php echo TEXT_DATE; ?></td>
              <td><?php echo $entry['datestamp_local'] . ' (' . TEXT_REMOTE . ' ' . $entry['datestamp'] . ')'; ?></td>
            </tr>
<?php
      $attachments_query = $g_db->query("select attachment from " . TABLE_HELPDESK_ATTACHMENTS . " where ticket = '" . $g_db->input($ticket) . "' and helpdesk_entries_id = '" . (int)$id . "'");
      if( $g_db->num_rows($attachments_query) ) {
?>
            <tr>
              <td><?php echo TEXT_ATTACHMENTS; ?></td>
              <td>
<?php
        while($attachments_array = $g_db->fetch_array($attachments_query) ) {
          echo '<a href="' . HTTP_SERVER . DIR_WS_ADMIN . HELPDESK_ATTACHMENTS_FOLDER . basename($attachments_array['attachment']) . '"><b>' . basename($attachments_array['attachment']) . '</b></a><br/ >';
        }
?>
            </tr>
<?php
      }
?>
            <tr>
              <td colspan="2" class="smallText"><b><?php echo TEXT_MESSAGE; ?></b></td>
            </tr>
          </table></div>
          <div class="listArea"><table border="0" cellspacing="0" cellpadding="3">
            <tr>
              <td><div class="scroller" style="height: 400px;">
<?php
       $body = nl2br(trim(strip_tags($entry['body'])));
       echo $body;
       //echo nl2br($entry['body']); 
?>
              </div></td>
            </tr>
          </table></div>
          <div class="formButtons">
            <div class="smallText" style="float: left;"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=reply') . '">' . tep_image_button('button_reply.gif', IMAGE_REPLY) . '</a>&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>'; ?></div>
            <div class="smallText" style="float: left; padding-left: 20px;"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></div>
          </div>
<?php
      $threads_query = $g_db->query("select helpdesk_entries_id, ticket, subject, sender, datestamp_local, datestamp, entry_read from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "' order by datestamp_local desc");
?>

        <div class="listArea"><table border="0" width="100%" cellspacing="1" cellpadding="3">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SUBJECT; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SENDER; ?></td>
            <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE; ?></td>
            <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ACTION; ?></td>
          </tr>
<?php
        while ($threads = $g_db->fetch_array($threads_query)) {
          if ($entry['helpdesk_entries_id'] == $threads['helpdesk_entries_id']) {
            echo '                  <tr class="dataTableRowSelected">' . "\n";
          } else {
            echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $threads['ticket'] . '&id=' . $threads['helpdesk_entries_id'] . '&action=view') . '\'">' . "\n";
          }
?>
            <td class="dataTableContent"><?php echo $threads['subject']; ?></td>
            <td class="dataTableContent"><?php echo $threads['sender']; ?></td>
            <td class="dataTableContent" align="center"><?php echo $threads['datestamp_local']; ?></td>
            <td class="dataTableContent" align="center">
<?php
    echo '<a href="' . tep_href_link(FILENAME_HELPDESK_HTML, 'id=' . $threads['helpdesk_entries_id'] . '&ticket=' . $threads['ticket']) . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_html_source.png', TEXT_VIEW_HTML_CODE_DATE . ' ' . $threads['datestamp_local']) . '</a>&nbsp;';
    echo '<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $threads['ticket'] . '&id=' . $threads['helpdesk_entries_id'] . '&action=view&subaction=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' - ' . $threads['datestamp_local']) . '</a>&nbsp;';
    echo '<a href="' . tep_href_link(FILENAME_HELPDESK_HTML, 'id=' . $threads['helpdesk_entries_id'] . '&ticket=' . $threads['ticket'] . '&unsafe=1') . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_html.png', TEXT_VIEW_HTML_DATE . ' ' . $threads['datestamp_local']) . '</a>&nbsp;';
    $entry_icon = (($threads['entry_read'] != '1') ? tep_image(DIR_WS_ICONS . 'icon_unread.png', ICON_UNREAD) : tep_image(DIR_WS_ICONS . 'icon_preview.png', ICON_PREVIEW));
    echo '<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $threads['ticket'] . '&id=' . $threads['helpdesk_entries_id'] . '&action=view') . '">' . $entry_icon . '</a>';
?>
            </td>
          </tr>
<?php
        }
?>
        </table></div>
<?php
      if( empty($subaction) ) {
        $internal_query = $g_db->query("select comment, datestamp_comment from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $g_db->input($ticket) . "'");
        $internal = $g_db->fetch_array($internal_query);
?>

          <div class="formArea"><?php echo tep_draw_form('internal', basename($PHP_SELF), 'page=' . $page . '&ticket=' . $_GET['ticket'] . '&action=updatecomment'); ?><table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="smallText"><b><?php echo TEXT_INTERNAL_COMMENTS; ?></b></td>
<?php
        if (tep_not_null($internal['datestamp_comment'])) {
          echo '                <td class="smallText" align="right"><b>' . TEXT_LAST_UPDATE . '</b> ' . $internal['datestamp_comment'] . '</td>' . "\n";
        }
?>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td class="smallText"><?php echo tep_draw_textarea_field('comment', 'virtual', '60', '10', $internal['comment'], 'style="width: 100%;"'); ?></td>
            </tr>
            <tr>
              <td class="formButtons"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
            </tr>
          </table></form></div>
<?php
      }
    }
  } elseif($action == 'delete') {
?>
          <div class="formArea"><?php echo tep_draw_form('delete', basename($PHP_SELF), tep_get_all_get_params(array('action','subaction','ticket')) . 'action=delete_confirm'); ?><table border="0" width="100%" cellspacing="1" cellpadding="3">
            <tr class="dataTableHeadingRow">
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TICKET; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SENDER; ?></td>
              <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAST_POST; ?></td>
              <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
              <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRIORITY; ?>&nbsp;</td>
            </tr>

<?php
      foreach($_POST['ticket'] as $key => $value) {
?>
            <tr class="dataTableRow">
              <td class="dataTableContent"><?php echo $key . tep_draw_hidden_field('ticket[' . $key . ']', $key); ?></td>
              <td class="dataTableContent"><?php echo $_POST['sender'][$key]; ?></td>
              <td class="dataTableContent" align="center"><?php echo tep_date_short($_POST['datestamp_last_entry'][$key]); ?></td>
              <td class="dataTableContent" align="right"><?php echo $_POST['status'][$key]; ?></td>
              <td class="dataTableContent" align="right"><?php echo $_POST['priority'][$key]; ?></td>
            </tr>
<?php
      }
?>
            <tr>
              <td colspan="8" class="formButtons"><?php echo tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action','subaction','ticket')) ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
            </tr>
          </table></form></div>
<?php
  } else {
?>
          <div class="formArea"><?php echo tep_draw_form('cross', basename($PHP_SELF), tep_get_all_get_params(array('action','subaction')) . 'action=delete', 'post', 'enctype="multipart/form-data"'); ?><table border="0" width="100%" cellspacing="1" cellpadding="3">
            <tr>
              <td colspan="10"><?php echo tep_image_submit('button_delete.gif', IMAGE_DELETE) . '</a>'; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td class="dataTableHeadingContent"><?php echo '<a href="javascript:void(0)" onclick="copy_checkboxes(document.cross,\'ticket\')" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TICKET; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SUBJECT; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SENDER; ?></td>
              <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAST_POST; ?></td>
              <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_STATUS; ?></td>
              <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_PRIORITY; ?></td>
              <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_IP; ?></td>
              <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ATTACHMENTS; ?></td>
              <td class="dataTableHeadingContent" align="center" width="60"><?php echo TABLE_HEADING_ACTION; ?></td>
            </tr>
<?php
    if( !empty($entry_filter) ) {
      $unread_array = array();
      $unread_entries_query = $g_db->query("select distinct he.ticket from " . TABLE_HELPDESK_ENTRIES . " he, " . TABLE_HELPDESK_TICKETS . " ht where he.entry_read = '0' and he.ticket = ht.ticket order by ht.datestamp_last_entry desc limit " . MAX_DISPLAY_ADMIN_HELP_DESK);
      while ($unread_entries = $g_db->fetch_array($unread_entries_query)) {
        $unread_array[] = $unread_entries['ticket'];
      }
    }

    $entries_query_raw = "select ht.ticket, hd.email_address, ht.datestamp_last_entry, hs.title as status, hp.title as priority from " . TABLE_HELPDESK_TICKETS . " ht, " . TABLE_HELPDESK_STATUSES . " hs, " . TABLE_HELPDESK_PRIORITIES . " hp, " . TABLE_HELPDESK_DEPARTMENTS . " hd where ht.status_id = hs.status_id and ht.priority_id = hp.priority_id and ht.department_id = hd.department_id";
    if( !empty($department_filter) ) {
      $entries_query_raw .= " and ht.department_id = '" . $g_db->input($department_filter) . "'";
    }
    if( !empty($status_filter) ) {
      $entries_query_raw .= " and ht.status_id = '" . $g_db->input($status_filter) . "'";
    }
    if( !empty($priority_filter) ) {
      $entries_query_raw .= " and ht.priority_id = '" . $g_db->input($priority_filter) . "'";
    }
    if( !empty($entry_filter) ) {
      $entries_query_raw .= " and ht.ticket in ('" . implode("', '", $unread_array) . "')";
    }
    $entries_query_raw .= " order by ht.datestamp_last_entry desc";
    $entries_split = new splitPageResults($entries_query_raw, MAX_DISPLAY_ADMIN_HELP_DESK);
    $entries_query = $g_db->query($entries_split->sql_query);
    while( $entries = $g_db->fetch_array($entries_query) ) {
      $ticket_query = $g_db->query("select helpdesk_entries_id, sender, subject, ip_address from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $entries['ticket'] . "' and parent_id = '0'");
      $ticket_array = $g_db->fetch_array($ticket_query);
        if( isset($_GET['keyword']) ) {
          if( !preg_match("/{$_GET['keyword']}/i", $ticket_array['sender'] . $ticket_array['subject']) ) {
            continue;
          }
        }

      $postings_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $entries['ticket'] . "' and helpdesk_entries_id != '" . $ticket_array['helpdesk_entries_id'] . "'");
      $postings = $g_db->fetch_array($postings_query);

      $unread_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $entries['ticket'] . "' and entry_read = '0'");
      $unread = $g_db->fetch_array($unread_query);

      $last_post_query = $g_db->query("select email_address from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $entries['ticket'] . "' order by datestamp_local desc limit 1");
      $last_post = $g_db->fetch_array($last_post_query);
      if( (empty($ticket) || $ticket == $entries['ticket']) && !isset($tInfo) && substr($action, 0, 3) != 'new') {
        $tInfo = new objectInfo(array_merge($entries, $ticket_array));
      }

/*
      if( isset($tInfo) && is_object($tInfo) && ($entries['ticket'] == $tInfo->ticket) ) {
        echo '                  <tr class="dataTableRowSelected" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $tInfo->ticket . '&action=view') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $entries['ticket']) . '\'">' . "\n";
      }
*/

      if( isset($tInfo) && is_object($tInfo) && ($entries['ticket'] == $tInfo->ticket) ) {
        echo '                  <tr class="dataTableRowSelected">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow">' . "\n";
      }

?>
              <td class="dataTableContent">
<?php 
      echo tep_draw_checkbox_field('ticket[' . $entries['ticket'] . ']', '', false) . ' ' . tep_draw_hidden_field('sender[' . $entries['ticket'] . ']', $ticket_array['sender']) . tep_draw_hidden_field('datestamp_last_entry[' . $entries['ticket'] . ']', $entries['datestamp_last_entry']) . tep_draw_hidden_field('status[' . $entries['ticket'] . ']', $entries['status']) . tep_draw_hidden_field('priority[' . $entries['ticket'] . ']', $entries['priority']);
/*
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $entries['ticket'] . '&action=view') . '">' . $entry_icon . '</a>&nbsp;' . $entries['ticket']; ?></td>
*/
?>
              </td>
              <td class="dataTableContent"><?php echo $entries['ticket']; ?></td>
              <td class="dataTableContent"><?php echo $ticket_array['subject'] . ' (' . $postings['count'] . ')'; ?></td>
              <td class="dataTableContent"><?php echo $ticket_array['sender']; ?></td>
              <td class="dataTableContent" align="center"><?php echo tep_date_short($entries['datestamp_last_entry']); ?></td>
              <td class="dataTableContent"><?php echo $entries['status']; ?></td>
              <td class="dataTableContent"><?php echo $entries['priority']; ?></td>
              <td class="dataTableContent" align="center"><?php echo $ticket_array['ip_address']; ?></td>
              <td class="dataTableContent" align="center">
<?php 
      $attachments_query = $g_db->query("select count(*) as total from " . TABLE_HELPDESK_ATTACHMENTS . " where ticket = '" . $g_db->input($entries['ticket']) . "'");
      $attachments_array = $g_db->fetch_array($attachments_query);
      echo $attachments_array['total'];
?>
              </td>
              <td class="dataTableContent" align="center">
<?php
      $entry_icon = (($unread['count'] > 0) ? tep_image(DIR_WS_ICONS . 'icon_unread.png', ICON_UNREAD) : tep_image(DIR_WS_ICONS . 'icon_read.png', ICON_PREVIEW));
      echo '<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $entries['ticket'] . '&action=view') . '">' . $entry_icon . '</a>&nbsp;';

//. '<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $entries['ticket'] . '&action=view') . '"></a>'

      echo '<a href="' . tep_href_link(basename($PHP_SELF), 'page=' . $page . '&ticket=' . $entries['ticket'] . '&action=view&subaction=reply') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $entries['ticket']) . '</a>&nbsp;';

      if ($entries['email_address'] == $last_post['email_address']) {
        $entry_icon = tep_image(DIR_WS_ICONS . 'icon_outgoing.png', ICON_OUTGOING);
      } else {
        $entry_icon = tep_image(DIR_WS_ICONS . 'icon_incoming.png', ICON_INCOMING);
      }
      echo $entry_icon;
?>
              </td>
            </tr>
<?php
    }
?>
            <tr>
              <td colspan="10" class="formButtons"><?php echo tep_image_submit('button_delete.gif', IMAGE_DELETE) . '</a>'; ?></td>
            </tr>
          </table></form></div>
          <div class="splitLine">
            <div style="float: left;"><?php echo $entries_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $entries_split->display_links(tep_get_all_get_params(array('action', 'page')) ); ?></div>
          </div>
<?php
  }
?>
        </div>
<?php require('includes/objects/html_end.php'); ?>
