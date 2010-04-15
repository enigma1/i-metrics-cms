<?php
/*
  $Id: helpdesk_priorities.php,v 1.6 2005/08/16 21:14:04 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $priority_id = (isset($_GET['priority_id']) ? (int)$_GET['priority_id'] : '');

  switch($action) {
    case 'insert':
    case 'save':
      $priority_id = (int)$priority_id;
      $sql_data_array = array('title' => $g_db->prepare_input($_POST['priority']));

      if( $action == 'insert' ) {
        if (!tep_not_null($priority_id)) {
          $next_id_query = $g_db->query("select max(priority_id) as priority_id from " . TABLE_HELPDESK_PRIORITIES . "");
          $next_id = $g_db->fetch_array($next_id_query);
          $priority_id = $next_id['priority_id'] + 1;
        }
        $insert_sql_data = array(
                                 'priority_id' => $priority_id,
                                );

        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
        $g_db->perform(TABLE_HELPDESK_PRIORITIES, $sql_data_array);
      } elseif( $action == 'save') {
        $g_db->perform(TABLE_HELPDESK_PRIORITIES, $sql_data_array, 'update', "priority_id = '" . (int)$priority_id . "'");
      }

      if( isset($_POST['default']) ) {
        $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . (int)$priority_id . "' where configuration_key = 'DEFAULT_HELPDESK_PRIORITY_ID'");
      }

      tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) . '&priority_id=' . $priority_id));
      break;
    case 'deleteconfirm':
      $priority_id = (int)$priority_id;

      $priority_query = $g_db->query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_HELPDESK_PRIORITY_ID'");
      $priority = $g_db->fetch_array($priority_query);
      if ($priority['configuration_value'] == $priority_id) {
        $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_HELPDESK_PRIORITY_ID'");
      }

      $g_db->query("delete from " . TABLE_HELPDESK_PRIORITIES . " where priority_id = '" . (int)$priority_id . "'");

      tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) ));
      break;
    case 'delete':
      $priority_id = (int)$priority_id;
      $priority_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where priority_id = '" . (int)$priority_id . "'");
      $priority = $g_db->fetch_array($priority_query);

      $remove_priority = true;
      if ($priority_id == DEFAULT_HELPDESK_PRIORITY_ID) {
        $remove_priority = false;
        $messageStack->add(ERROR_REMOVE_DEFAULT_HELPDESK_PRIORITY, 'error');
      } elseif ($priority['count'] > 0) {
        $remove_priority = false;
        $messageStack->add(ERROR_PRIORITY_USED_IN_ENTRIES, 'error');
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRIORITIES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ACTION; ?></td>
              </tr>
<?php
  $priorities_query_raw = "select priority_id, title from " . TABLE_HELPDESK_PRIORITIES . " order by title";
  $priorities_split = new splitPageResults($priorities_query_raw);
  $priorities_query = $g_db->query($priorities_split->sql_query);
  while( $priorities = $g_db->fetch_array($priorities_query) ) {
    if( (empty($priority_id) || $priority_id == $priorities['priority_id']) && !isset($pInfo) && substr($action, 0, 3) != 'new') {
      $pInfo = new objectInfo($priorities);
    }

    if( isset($pInfo) && is_object($pInfo) && ($priorities['priority_id'] == $pInfo->priority_id) ) {
      echo '                  <tr class="dataTableRowSelected" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) . '&priority_id=' . $pInfo->priority_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) . '&priority_id=' . $priorities['priority_id']) . '\'">' . "\n";
    }

    if (DEFAULT_HELPDESK_PRIORITY_ID == $priorities['priority_id']) {
      echo '                <td class="dataTableContent"><b>' . $priorities['title'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $priorities['title'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent" align="center">
<?php
    echo '<a href="' . tep_href_link(basename($PHP_SELF), 'priority_id=' . $priorities['priority_id'] . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $priorities['title']) . '</a>&nbsp;';
    echo '<a href="' . tep_href_link(basename($PHP_SELF), 'priority_id=' . $priorities['priority_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $priorities['title']) . '</a>&nbsp;';

    if( isset($pInfo) && is_object($pInfo) && $priorities['priority_id'] == $pInfo->priority_id ) { 
      echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', IMAGE_SELECT); 
    } else { 
      echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) . '&priority_id=' . $priorities['priority_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
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
            <div class="formButtons"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></div>
<?php
  }
?>
            <div class="splitLine">
              <div style="float: left;"><?php echo $priorities_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
              <div style="float: right;"><?php echo $priorities_split->display_links(tep_get_all_get_params(array('action', 'page'))); ?></div>
            </div>
          </div>
<?php
  $heading = array();
  $contents = array();
  switch( $action ) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_PRIORITY . '</b>');

      $contents[] = array('form' => tep_draw_form('priority', basename($PHP_SELF), tep_get_all_get_params(array('action')) . '&action=insert'));
      $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'new_entry.png', IMAGE_NEW) );
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => TEXT_INFO_PRIORITIES . '<br />' . tep_draw_input_field('priority'));
      $contents[] = array('text' => tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . ' <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;

    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_PRIORITY . '</b>');

      $contents[] = array('form' => tep_draw_form('priority', basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) . '&priority_id=' . $pInfo->priority_id  . '&action=save'));
      $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

      $contents[] = array('text' => TEXT_INFO_PRIORITIES . '<br />' . tep_draw_input_field('priority', $pInfo->title));
      if (DEFAULT_HELPDESK_PRIORITY_ID != $pInfo->priority_id) $contents[] = array('text' => tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) . '&priority_id=' . $pInfo->priority_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');

      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_PRIORITY . '</b>');

      $contents[] = array('form' => tep_draw_form('priority', basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) . '&priority_id=' . $pInfo->priority_id  . '&action=deleteconfirm'));
      $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<b>' . $pInfo->title . '</b>');
      if ($remove_priority) $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . ' <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) . '&priority_id=' . $pInfo->priority_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;

    default:
      if (is_object($pInfo)) {
        $heading[] = array('text' => '<b>' . $pInfo->title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) . '&priority_id=' . $pInfo->priority_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'priority_id')) . '&priority_id=' . $pInfo->priority_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<b>' . $pInfo->title . '</b>');
        if (DEFAULT_HELPDESK_PRIORITY_ID == $pInfo->priority_id) $contents[] = array('text' => '<b>' . TEXT_DEFAULT . '</b>');

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
