<?php
/*
  $Id: helpdesk_departments.php,v 1.6 2005/08/16 21:14:04 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  switch($action) {
    case 'save':
      if( !isset($_GET['department']) ) {
        tep_redirect(tep_href_link(basename($PHP_SELF)));
      }
      $department_id = (int)$_GET['department'];
    case 'insert':
      $title = $g_db->prepare_input($_POST['title']);
      $email_address = $g_db->prepare_input($_POST['email_address']);
      $name = $g_db->prepare_input($_POST['name']);
      $password = $g_db->prepare_input($_POST['password']);

      $sql_data_array = array('title' => $title,
                              'email_address' => $email_address,
                              'name' => $name,
                              'front' => isset($_POST['front'])?1:0,
                              'receive' => isset($_POST['receive'])?1:0,
                              'password' => $password);

      if ($_GET['action'] == 'insert') {
        $g_db->perform(TABLE_HELPDESK_DEPARTMENTS, $sql_data_array);
        $department_id = $g_db->insert_id();
      } elseif ($_GET['action'] == 'save') {
        $g_db->perform(TABLE_HELPDESK_DEPARTMENTS, $sql_data_array, 'update', "department_id = '" . (int)$department_id . "'");
      }

      if( isset($_POST['default']) ) {
        $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . (int)$department_id . "' where configuration_key = 'DEFAULT_HELPDESK_DEPARTMENT_ID'");
      }
      tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'department')) . '&department=' . $department_id));
      break;
    case 'deleteconfirm':
      $department_id = $g_db->prepare_input($_GET['department']);

      $department_query = $g_db->query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_HELPDESK_DEPARTMENT_ID'");
      $department = $g_db->fetch_array($department_query);
      if ($department['configuration_value'] == $department_id) {
        $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_HELPDESK_DEPARTMENT_ID'");
      }

      $g_db->query("delete from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = '" . (int)$department_id . "'");

      tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'department')) ));
      break;
    case 'delete':
      $department_id = (int)$_GET['department'];

      $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where department_id = '" . (int)$department_id . "'");
      $check = $g_db->fetch_array($check_query);

      $remove_department = true;
      if ($department_id == DEFAULT_HELPDESK_DEPARTMENT_ID) {
        $remove_department = false;
        $messageStack->add(ERROR_REMOVE_DEFAULT_HELPDESK_DEPARTMENT, 'error');
        unset($_GET['action']);
      } elseif ($check['count'] > 0) {
        $remove_department = false;
        $messageStack->add(ERROR_DEPARTMENT_USED_IN_ENTRIES, 'error');
        unset($_GET['action']);
      }
      break;
    default:
      break;
  }
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php
  $set_focus = true;
  require('includes/objects/html_start_sub2.php'); 
?>
          <div class="maincell">
            <div class="comboHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
            <div class="listArea"><table class="tabledata" cellspacing="1">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_DEPARTMENTS; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
<?php
  $departments_query_raw = "select department_id, title, email_address, name, password, front, receive from " . TABLE_HELPDESK_DEPARTMENTS . " order by title";
  $departments_split = new splitPageResults($departments_query_raw);
  $departments_query = $g_db->query($departments_split->sql_query);
  while ($departments = $g_db->fetch_array($departments_query)) {
    if((!isset($_GET['department']) || $_GET['department'] == $departments['department_id']) && !isset($dInfo) && (substr($action, 0, 3) != 'new')) {
      $dInfo = new objectInfo($departments);
    }

    if( (isset($dInfo) && is_object($dInfo)) && ($departments['department_id'] == $dInfo->department_id) ) {
      echo '                  <tr class="dataTableRowSelected" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'department')) . '&department=' . $dInfo->department_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'department')) . '&department=' . $departments['department_id']) . '\'">' . "\n";
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

    echo '<a href="' . tep_href_link(basename($PHP_SELF), 'department=' . $departments['department_id'] . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $departments['title']) . '</a>';
    echo '<a href="' . tep_href_link(basename($PHP_SELF), 'department=' . $departments['department_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $departments['title']) . '</a>';

    if(isset($dInfo) && is_object($dInfo) && ($departments['department_id'] == $dInfo->department_id) ) { 
      echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', TEXT_SELECTED); 
    } else { 
      echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'department')) . '&department=' . $departments['department_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>';
    } 
?>
                </td>
              </tr>
<?php
  }
?>
            </table></div>
<?php
  if( !tep_not_null($action) ) {
?>
            <div class="formButtons"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></div>
<?php
  }
?>
            <div class="splitLine">
              <div class="floater"><?php echo $departments_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
              <div class="floatend"><?php echo $departments_split->display_links(tep_get_all_get_params(array('action', 'page')) ); ?></div>
            </div>
          </div>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_DEPARTMENT . '</b>');

      $contents[] = array('form' => tep_draw_form('departments', basename($PHP_SELF), tep_get_all_get_params(array('action')) . '&action=insert'));
      $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'new_entry.png', IMAGE_NEW) );
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => TEXT_INFO_DEPARTMENT . '<br />' . tep_draw_input_field('title'));
      $contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . '<br />' . tep_draw_input_field('email_address'));
      $contents[] = array('text' => TEXT_INFO_PASSWORD . '<br />' . tep_draw_input_field('password'));
      $contents[] = array('text' => TEXT_INFO_NAME . '<br />' . tep_draw_input_field('name'));
      $contents[] = array('text' => tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('text' => tep_draw_checkbox_field('front') . ' ' . TEXT_INFO_CATALOG);
      $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_DEPARTMENT . '</b>');

      $contents[] = array('form' => tep_draw_form('departments', basename($PHP_SELF), tep_get_all_get_params(array('action', 'department')) . '&department=' . $dInfo->department_id . '&action=save'));
      $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => TEXT_INFO_DEPARTMENT . '<br />' . tep_draw_input_field('title', $dInfo->title));
      $contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . '<br />' . tep_draw_input_field('email_address', $dInfo->email_address));
      $contents[] = array('text' => TEXT_INFO_PASSWORD . '<br />' . tep_draw_input_field('password', $dInfo->password));
      $contents[] = array('text' => TEXT_INFO_NAME . '<br />' . tep_draw_input_field('name', $dInfo->name));
      if (DEFAULT_HELPDESK_DEPARTMENT_ID != $dInfo->department_id) $contents[] = array('text' => tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('text' => tep_draw_checkbox_field('front', 1, $dInfo->front?true:false) . ' ' .  TEXT_INFO_CATALOG);
      $contents[] = array('text' => tep_draw_checkbox_field('receive', 1, $dInfo->receive?true:false) . ' ' .  TEXT_INFO_RECEIVE);
      $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'department')) . '&department=' . $dInfo->department_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_DEPARTMENT . '</b>');

      $contents[] = array('form' => tep_draw_form('departments', basename($PHP_SELF), tep_get_all_get_params(array('action', 'department')) . '&department=' . $dInfo->department_id  . '&action=deleteconfirm'));
      $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<b>' . $dInfo->title . '</b>');
      if ($remove_department) $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . ' <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'department')) . '&department=' . $dInfo->department_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($dInfo)) {
        $heading[] = array('text' => '<b>' . $dInfo->title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'department')) . '&department=' . $dInfo->department_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'department')) . '&department=' . $dInfo->department_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => TEXT_INFO_DEPARTMENT . '<br />' . $dInfo->title);
        $contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . '<br />' . $dInfo->email_address);
        $contents[] = array('text' => TEXT_INFO_PASSWORD . '<br />' . $dInfo->password);
        $contents[] = array('text' => TEXT_INFO_NAME . '<br />' . $dInfo->name);
        if( $dInfo->front) $contents[] = array('text' => '<b>' . TEXT_DISPLAY_FRONT . '</b>');
        if( $dInfo->receive) $contents[] = array('text' => '<b>' . TEXT_RECEIVES_EMAILS . '</b>');
      } else { // create generic_text dummy info
        $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
        $contents[] = array('text' => TEXT_NO_GENERIC);
      }
      break;
  }

  if( !empty($heading) && !empty($contents) ) {
    echo '             <div class="rightcell">';
    $box = new box;
    echo $box->infoBox($heading, $contents);
    echo '             </div>';
  }
?>
<?php require('includes/objects/html_end.php'); ?>
