<?php
/*
  $Id: helpdesk_departments.php,v 1.6 2005/08/16 21:14:04 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Helpdesk Departments Scripts
//----------------------------------------------------------------------------
// Converted for the CMS
// Removed register global dependencies
// Added compatibility for PHP4,5
// Enhanced departments tables to hold server details
// Added common HTML sections
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  require('includes/application_top.php');

  switch($action) {
    case 'save':
      if( !isset($_GET['department']) ) {
        tep_redirect(tep_href_link($g_script));
      }
      $department_id = (int)$_GET['department'];
    case 'insert':
      $title = $g_db->prepare_input($_POST['title']);
      $email_address = $g_db->prepare_input($_POST['email_address']);
      $name = $g_db->prepare_input($_POST['name']);
      $password = $g_db->prepare_input($_POST['password']);
      $server_connect = $g_db->prepare_input($_POST['server_connect']);
      $server_protocol = $g_db->prepare_input($_POST['server_protocol']);
      $body_size = (int)$_POST['body_size'];
      $ticket_prefix = $g_db->prepare_input($_POST['ticket_prefix']);

      $sql_data_array = array(
        'title' => $title,
        'email_address' => $email_address,
        'name' => $name,
        'readonly' => isset($_POST['readonly'])?1:0,
        'front' => isset($_POST['front'])?1:0,
        'receive' => isset($_POST['receive'])?1:0,
        'password' => $password,
        'server_connect' => $server_connect,
        'server_protocol' => $server_protocol,
        'body_size' => $body_size,
        'ticket_prefix' => $ticket_prefix,
      );

      if( $action == 'insert' ) {
        $g_db->perform(TABLE_HELPDESK_DEPARTMENTS, $sql_data_array);
        $department_id = $g_db->insert_id();
      } elseif( $action == 'save' ) {
        $g_db->perform(TABLE_HELPDESK_DEPARTMENTS, $sql_data_array, 'update', "department_id = '" . (int)$department_id . "'");
      }

      if( isset($_POST['default']) ) {
        $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . (int)$department_id . "' where configuration_key = 'DEFAULT_HELPDESK_DEPARTMENT_ID'");
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'department') . 'department=' . $department_id));
      break;
    case 'deleteconfirm':
      $department_id = $g_db->prepare_input($_GET['department']);

      $department_query = $g_db->query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_HELPDESK_DEPARTMENT_ID'");
      $department = $g_db->fetch_array($department_query);
      if ($department['configuration_value'] == $department_id) {
        $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_HELPDESK_DEPARTMENT_ID'");
      }

      $tickets_query = $g_db->query("select ticket_id from " . TABLE_HELPDESK_TICKETS . " where department_id = '" . (int)$department_id . "'");
      while( $tickets_array = $g_db->fetch_array($tickets_query) ) {
        $g_db->query("delete from " . TABLE_HELPDESK_ENTRIES . " where ticket_id = '" . (int)$tickets_array['ticket_id'] . "'");
      }
      $g_db->query("delete from " . TABLE_HELPDESK_TICKETS . " where department_id = '" . (int)$department_id . "'");
      $g_db->query("delete from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = '" . (int)$department_id . "'");

      if( $department_id == DEFAULT_HELPDESK_DEPARTMENT_ID ) {
        $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '0' where configuration_key = 'DEFAULT_HELPDESK_DEPARTMENT_ID'");
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'department') ));
      break;
    case 'delete':
      $department_id = (int)$_GET['department'];

      $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where department_id = '" . (int)$department_id . "'");
      $check = $g_db->fetch_array($check_query);

      $remove_department_warn = false;
      if( $department_id == DEFAULT_HELPDESK_DEPARTMENT_ID || $check['count'] ) {
        $remove_department_warn = true;
      }
/*
        $remove_department = false;
        $messageStack->add(ERROR_REMOVE_DEFAULT_HELPDESK_DEPARTMENT, 'error');
        $action = '';
      } elseif ($check['count'] > 0) {
        $remove_department = false;
        $messageStack->add(ERROR_DEPARTMENT_USED_IN_ENTRIES, 'error');
        $action = '';
      }
*/
      break;
    default:
      break;
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
          <div class="maincell">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
              <div><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
            <div class="formArea"><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_DEPARTMENTS; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
<?php
  $departments_query_raw = "select department_id, title, email_address, name, password, front, receive, readonly from " . TABLE_HELPDESK_DEPARTMENTS . " order by title";
  $departments_split = new splitPageResults($departments_query_raw);
  $departments_query = $g_db->query($departments_split->sql_query);
  $rows = 0;
  while ($departments = $g_db->fetch_array($departments_query)) {
    $rows++;
    $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

    if((!isset($_GET['department']) || $_GET['department'] == $departments['department_id']) && !isset($dInfo) && (substr($action, 0, 3) != 'new')) {
      $dInfo = new objectInfo($departments);
    }

    if( (isset($dInfo) && is_object($dInfo)) && ($departments['department_id'] == $dInfo->department_id) ) {
      echo '                  <tr class="dataTableRowSelected row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'department') . 'department=' . $dInfo->department_id . '&action=edit') . '">' . "\n";
    } else {
      echo '                  <tr class="' . $row_class . ' row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'department') . 'department=' . $departments['department_id']) . '">' . "\n";
    }

    if (DEFAULT_HELPDESK_DEPARTMENT_ID == $departments['department_id']) {
      echo '                <td><b>' . $departments['title'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
    } else {
      echo '                <td>' . $departments['title'] . '</td>' . "\n";
    }
?>
                <td class="tinysep calign">
<?php
    if( $departments['receive'] ) {
      echo '<a href="' . tep_href_link(FILENAME_HELPDESK_POP3, 'department=' . $departments['department_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_green_flag.png', TEXT_RECEIVE_ENABLED) . '</a>';
    } else {
      echo '<a href="' . tep_href_link(FILENAME_HELPDESK_POP3, 'department=' . $departments['department_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_red_flag.png', TEXT_RECEIVE_DISABLED) . '</a>';
    }

    echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'department') . 'department=' . $departments['department_id'] . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $departments['title']) . '</a>';
    echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'department') . 'department=' . $departments['department_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $departments['title']) . '</a>';

    if(isset($dInfo) && is_object($dInfo) && ($departments['department_id'] == $dInfo->department_id) ) { 
      echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', TEXT_SELECTED); 
    } else { 
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'department') . 'department=' . $departments['department_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>';
    } 
?>
                </td>
              </tr>
<?php
  }
  $buttons = array();
  if( !tep_not_null($action) ) {
    $buttons = array(
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>',
    );
  }
?>
            </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></div>
            <div class="listArea splitLine">
              <div class="floater"><?php echo $departments_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
              <div class="floatend"><?php echo $departments_split->display_links(tep_get_all_get_params('action', 'page') ); ?></div>
            </div>
          </div>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_DEPARTMENT . '</b>');

      $contents[] = array('form' => tep_draw_form('departments', $g_script, tep_get_all_get_params('action') . 'action=insert'));
      $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'new_entry.png', IMAGE_NEW) );
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('class' => 'rpad', 'section' => '<div>');
      $contents[] = array('text' => TEXT_INFO_DEPARTMENT . '<br />' . tep_draw_input_field('title'));
      $contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . '<br />' . tep_draw_input_field('email_address'));
      $contents[] = array('text' => TEXT_INFO_PASSWORD . '<br />' . tep_draw_input_field('password'));
      $contents[] = array('text' => TEXT_INFO_NAME . '<br />' . tep_draw_input_field('name'));
      $contents[] = array('text' => TEXT_INFO_SERVER_CONNECT . '<br />' . tep_draw_input_field('server_connect'));
      $contents[] = array('text' => TEXT_INFO_SERVER_PROTOCOL . '<br />' . tep_draw_input_field('server_protocol'));
      $contents[] = array('text' => TEXT_INFO_SERVER_MAILBOX . '<br />' . tep_draw_input_field('server_mailbox'));
      $contents[] = array('text' => TEXT_INFO_TICKET_PREFIX . '<br />' . tep_draw_input_field('ticket_prefix'));
      $contents[] = array('section' => '</div>');
      $contents[] = array('text' => TEXT_INFO_BODY_LIMIT . '<br />' . tep_draw_input_field('body_size', '0', 'size="8"'));

      $contents[] = array('text' => tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('text' => tep_draw_checkbox_field('front') . ' ' . TEXT_INFO_CATALOG);
      $contents[] = array('text' => tep_draw_checkbox_field('readonly') . ' ' . TEXT_INFO_READ);

      $buttons = array(
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_insert.gif', IMAGE_INSERT),
      );
      $contents[] = array(
        'class' => 'calign', 
        'text' => implode('', $buttons),
      );
      break;
    case 'edit':
      $department_query = $g_db->query("select title, email_address, name, password, server_connect, server_protocol, server_mailbox, body_size, ticket_prefix, front, receive, readonly from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = '" . (int)$dInfo->department_id . "'");
      $department_array = $g_db->fetch_array($department_query);

      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_DEPARTMENT . '</b>');

      $contents[] = array('form' => tep_draw_form('departments', $g_script, tep_get_all_get_params('action', 'department') . 'department=' . $dInfo->department_id . '&action=save'));
      $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('class' => 'rpad', 'section' => '<div>');
      $contents[] = array('text' => TEXT_INFO_DEPARTMENT . '<br />' . tep_draw_input_field('title', $department_array['title']));
      $contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . '<br />' . tep_draw_input_field('email_address', $department_array['email_address']));
      $contents[] = array('text' => TEXT_INFO_PASSWORD . '<br />' . tep_draw_input_field('password', $department_array['password']));
      $contents[] = array('text' => TEXT_INFO_NAME . '<br />' . tep_draw_input_field('name', $department_array['name']));
      $contents[] = array('text' => TEXT_INFO_SERVER_CONNECT . '<br />' . tep_draw_input_field('server_connect', $department_array['server_connect']));
      $contents[] = array('text' => TEXT_INFO_SERVER_PROTOCOL . '<br />' . tep_draw_input_field('server_protocol', $department_array['server_protocol']));
      $contents[] = array('text' => TEXT_INFO_SERVER_MAILBOX . '<br />' . tep_draw_input_field('server_mailbox', $department_array['server_mailbox']));
      $contents[] = array('text' => TEXT_INFO_TICKET_PREFIX . '<br />' . tep_draw_input_field('ticket_prefix', $department_array['ticket_prefix']));
      $contents[] = array('section' => '</div>');
      $contents[] = array('text' => TEXT_INFO_BODY_LIMIT . '<br />' . tep_draw_input_field('body_size', $department_array['body_size'], 'size="8"'));

      if (DEFAULT_HELPDESK_DEPARTMENT_ID != $dInfo->department_id) {
        $contents[] = array('text' => tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      }
      $contents[] = array('text' => tep_draw_checkbox_field('front', 1, $department_array['front']?true:false) . ' ' .  TEXT_INFO_CATALOG);
      $contents[] = array('text' => tep_draw_checkbox_field('receive', 1, $department_array['receive']?true:false) . ' ' .  TEXT_INFO_RECEIVE);
      $contents[] = array('text' => tep_draw_checkbox_field('readonly', 1, $department_array['readonly']?true:false) . ' ' .  TEXT_INFO_READ);

      $buttons = array(
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'department') . 'department=' . $dInfo->department_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_update.gif', IMAGE_UPDATE),
      );
      $contents[] = array(
        'class' => 'calign', 
        'text' => implode('', $buttons),
      );
      break;

    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_DEPARTMENT . '</b>');

      $contents[] = array('form' => tep_draw_form('departments', $g_script, tep_get_all_get_params('action', 'department') . 'department=' . $dInfo->department_id  . '&action=deleteconfirm'));
      $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('class' => 'calign', 'text' => '<b>' . $dInfo->title . '</b>');

      if( $remove_department_warn ) {
        $contents[] = array('class' => 'heavy', 'text' => TEXT_INFO_DELETE_WARN);
      }

      $buttons = array(
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'department') . 'department=' . $dInfo->department_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_confirm.gif', IMAGE_CONFIRM)
      );

      $contents[] = array('class' => 'calign', 'text' => implode('', $buttons) );
      break;
    default:
      if(isset($dInfo) && is_object($dInfo)) {
        $info_query = $g_db->query("select title, email_address, name, password, front, receive, server_connect, server_protocol, server_mailbox, ticket_prefix, body_size from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = '" . (int)$dInfo->department_id . "'");
        $info_array = $g_db->fetch_array($info_query);

        $heading[] = array('text' => '<b>' . $info_array['title'] . '</b>');

        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'department') . 'department=' . $dInfo->department_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>',
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'department') . 'department=' . $dInfo->department_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>',
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons),
        );
        $contents[] = array('class' => 'infoBoxSection', 'section' => '<div>');
        $contents[] = array('text' => TEXT_INFO_DEPARTMENT . '<br />' . $info_array['title']);
        $contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . '<br />' . $info_array['email_address']);
        $contents[] = array('text' => TEXT_INFO_PASSWORD . '<br />' . $info_array['password']);
        $contents[] = array('text' => TEXT_INFO_NAME . '<br />' . $info_array['name']);
        $contents[] = array('text' => TEXT_INFO_SERVER_CONNECT . '<br />' . $info_array['server_connect']);
        $contents[] = array('text' => TEXT_INFO_SERVER_PROTOCOL . '<br />' . $info_array['server_protocol']);
        $contents[] = array('text' => TEXT_INFO_SERVER_MAILBOX . '<br />' . $info_array['server_mailbox']);
        $contents[] = array('text' => TEXT_INFO_BODY_LIMIT . '<br />' . (empty($info_array['body_size'])?TEXT_INFO_UNLIMITED:$info_array['body_size']));
        $contents[] = array('text' => TEXT_INFO_TICKET_PREFIX . '<br />' . (empty($info_array['ticket_prefix'])?TEXT_INFO_NOT_SET:$info_array['ticket_prefix']));
        if( $dInfo->front) $contents[] = array('text' => '<b>' . TEXT_DISPLAY_FRONT . '</b>');
        if( $dInfo->receive) $contents[] = array('text' => '<b>' . TEXT_RECEIVES_EMAILS . '</b>');
        $contents[] = array('section' => '</div>');
      } else { // create generic_text dummy info
        $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
        $contents[] = array('class' => 'calign', 'text' => '<a href="' . tep_href_link($g_script, 'action=new') . '">' . tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW) . '</a>');
        $contents[] = array('text' => TEXT_NO_GENERIC);
      }
      break;
  }

  if( !empty($heading) && !empty($contents) ) {
    echo '             <div class="rightcell">';
    $box = new box;
    echo $box->infoBox($heading, $contents);
    echo '             </div>' . "\n";
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
