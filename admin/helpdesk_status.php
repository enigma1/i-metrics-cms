<?php
/*
  $Id: helpdesk_status.php,v 1.6 2005/08/16 21:14:04 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $status_id = (isset($_GET['status_id']) ? (int)$_GET['status_id'] : '');
  switch( $action) {
    case 'insert':
    case 'save':
      $sql_data_array = array(
                              'title' => $g_db->prepare_input($_POST['status']),
                             );
      if( $action == 'insert') {
        if (!tep_not_null($status_id)) {
          $next_id_query = $g_db->query("select max(status_id) as status_id from " . TABLE_HELPDESK_STATUSES . "");
          $next_id = $g_db->fetch_array($next_id_query);
          $status_id = $next_id['status_id'] + 1;
        }
        $insert_sql_data = array(
                                 'status_id' => (int)$status_id,
                                );
        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
        $g_db->perform(TABLE_HELPDESK_STATUSES, $sql_data_array);
      } elseif( $action == 'save') {
        $g_db->perform(TABLE_HELPDESK_STATUSES, $sql_data_array, 'update', "status_id = '" . (int)$status_id . "'");
      }

      if( isset($_POST['default']) ) {
        $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . (int)$status_id . "' where configuration_key = 'DEFAULT_HELPDESK_STATUS_ID'");
      }

      tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'status_id')) . '&status_id=' . $status_id));
      break;
    case 'deleteconfirm':
      $status_id = $g_db->prepare_input($status_id);

      $status_query = $g_db->query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_HELPDESK_STATUS_ID'");
      $status = $g_db->fetch_array($status_query);
      if ($status['configuration_value'] == $status_id) {
        $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_HELPDESK_STATUS_ID'");
      }

      $g_db->query("delete from " . TABLE_HELPDESK_STATUSES . " where status_id = '" . (int)$status_id . "'");

      tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'status_id')) ));
      break;
    case 'delete':
      $status_id = $g_db->prepare_input($status_id);

      $status_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where status_id = '" . (int)$status_id . "'");
      $status = $g_db->fetch_array($status_query);

      $remove_status = true;
      if ($status_id == DEFAULT_HELPDESK_STATUS_ID) {
        $remove_status = false;
        $messageStack->add(ERROR_REMOVE_DEFAULT_HELPDESK_STATUS, 'error');
      } elseif ($status['count'] > 0) {
        $remove_status = false;
        $messageStack->add(ERROR_STATUS_USED_IN_ENTRIES, 'error');
      }
      break;
  }
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php
  $set_focus = true;
  require('includes/objects/html_start_sub2.php'); 
?>
          <div class="maincell">
            <div class="comboHeading">
              <div class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
            <div class="listArea"><table border="0" width="100%" cellspacing="1" cellpadding="3">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ACTION; ?></td>
              </tr>
<?php
  $statuses_query_raw = "select status_id, title from " . TABLE_HELPDESK_STATUSES . " order by title";
  $statuses_split = new splitPageResults($statuses_query_raw);
  $statuses_query = $g_db->query($statuses_split->sql_query);
  while ($statuses = $g_db->fetch_array($statuses_query)) {
    if( ( empty($status_id) || $status_id == $statuses['status_id']) && !isset($sInfo) && (substr($action, 0, 3) != 'new')) {
      $sInfo = new objectInfo($statuses);
    }

    if( isset($sInfo) && is_object($sInfo) && $statuses['status_id'] == $sInfo->status_id ) {
      echo '                  <tr class="dataTableRowSelected" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'status_id')) . '&status_id=' . $sInfo->status_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'status_id')) . '&status_id=' . $statuses['status_id']) . '\'">' . "\n";
    }

    if (DEFAULT_HELPDESK_STATUS_ID == $statuses['status_id']) {
      echo '                <td class="dataTableContent"><b>' . $statuses['title'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $statuses['title'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent" align="center">
<?php
    echo '<a href="' . tep_href_link(basename($PHP_SELF), 'status_id=' . $statuses['status_id'] . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $statuses['title']) . '</a>&nbsp;';
    echo '<a href="' . tep_href_link(basename($PHP_SELF), 'status_id=' . $statuses['status_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $statuses['title']) . '</a>&nbsp;';

    if( isset($sInfo) && is_object($sInfo) && $statuses['status_id'] == $sInfo->status_id ) { 
      echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', IMAGE_SELECT); 
    } else { 
      echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'status_id')) . '&status_id=' . $statuses['status_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
    } 
?>
                </td>
              </tr>
<?php
  }
?>
            </table></div>
<?php
  if( empty($action) ) {
?>
            <div class="formButtons"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></div>
<?php
  }
?>
            <div class="splitLine">
              <div style="float: left;"><?php echo $statuses_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
              <div style="float: right;"><?php echo $statuses_split->display_links(tep_get_all_get_params(array('action', 'page'))); ?></div>
            </div>
          </div>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_STATUS . '</b>');

      $contents[] = array('form' => tep_draw_form('status', basename($PHP_SELF), tep_get_all_get_params(array('action')) . '&action=insert'));
      $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'new_entry.png', IMAGE_NEW) );
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $status_inputs_string = tep_draw_input_field('status');
      $contents[] = array('text' => TEXT_INFO_STATUSES . $status_inputs_string);
      $contents[] = array('text' => tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . ' <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_STATUS . '</b>');

      $contents[] = array('form' => tep_draw_form('status', basename($PHP_SELF), tep_get_all_get_params(array('action', 'status_id')) . '&status_id=' . $sInfo->status_id  . '&action=save'));
      $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

      $status_id = $g_db->prepare_input($status_id);
      $status_inputs_string = '';
      $status_query = $g_db->query("select title from " . TABLE_HELPDESK_STATUSES . " where status_id = '" . (int)$status_id . "'");

      $status = $g_db->fetch_array($status_query);
      $status_inputs_string .= tep_draw_input_field('status', $status['title']);

      $contents[] = array('text' => TEXT_INFO_STATUSES . $status_inputs_string);
      if (DEFAULT_HELPDESK_STATUS_ID != $sInfo->status_id) $contents[] = array('text' => tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'status_id')) . '&status_id=' . $sInfo->status_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;

    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_STATUS . '</b>');

      $contents[] = array('form' => tep_draw_form('status', basename($PHP_SELF), tep_get_all_get_params(array('action', 'status_id')) . '&status_id=' . $sInfo->status_id  . '&action=deleteconfirm'));
      $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<b>' . $sInfo->title . '</b>');
      if ($remove_status) $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . ' <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'status_id')) . '&status_id=' . $sInfo->status_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;

    default:
      if( isset($sInfo) && is_object($sInfo) ) {
        $heading[] = array('text' => '<b>' . $sInfo->title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'status_id')) . '&status_id=' . $sInfo->status_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'status_id')) . '&status_id=' . $sInfo->status_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => $sInfo->title);
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
