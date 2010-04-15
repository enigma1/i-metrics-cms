<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Abstract Types for the Abstract Zones component
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (isset($_POST['remove_x']) || isset($_POST['remove_y'])) $action='remove';

  switch ($action) {
    case 'setflag':
      $sql_data_array = array('abstract_types_status' => $g_db->prepare_input($_GET['flag']));
      $g_db->perform(TABLE_ABSTRACT_TYPES, $sql_data_array, 'update', 'abstract_types_id=' . $_GET['id']);
      tep_redirect(tep_href_link($g_script));
      break;
    case 'add':
      if( !isset($_POST['name']) || empty($_POST['name']) || 
          !isset($_POST['class']) || empty($_POST['class']) ||
          !isset($_POST['table']) || empty($_POST['table']) ) {

        $messageStack->add_session(ERROR_INVALID_INPUT);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) ));
      }
      $sql_data_array = array(
                              'abstract_types_name' => $g_db->prepare_input($_POST['name']),
                              'abstract_types_class' => $g_db->prepare_input($_POST['class']),
                              'abstract_types_table' => $g_db->prepare_input($_POST['table']),
                              'sort_order' => $g_db->prepare_input($_POST['sort'])
                             );

      $g_db->perform(TABLE_ABSTRACT_TYPES, $sql_data_array, 'insert');
      $messageStack->add_session(SUCCESS_ENTRY_CREATE, 'success');
      tep_redirect(tep_href_link($g_script));
      break;
    case 'update':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) ));
      }
      foreach ($_POST['mark'] as $key=>$val) {
        $sql_data_array = array(
                                'abstract_types_name' => $g_db->prepare_input($_POST['name'][$key]),
                                'abstract_types_class' => $g_db->prepare_input($_POST['class'][$key]),
                                'abstract_types_table' => $g_db->prepare_input($_POST['table'][$key]),
                                'sort_order' => $g_db->prepare_input($_POST['sort'][$key]),
                               );
          $g_db->perform(TABLE_ABSTRACT_TYPES, $sql_data_array, 'update', 'abstract_types_id= ' . $key);
      }
      tep_redirect(tep_href_link($g_script));
      break;
    case 'remove':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) ));
      }
      require_once(DIR_WS_CLASSES . FILENAME_ABSTRACT_ZONES);
      $cAbstract = new abstract_zones();
      foreach ($_POST['mark'] as $key=>$val) {
        $cAbstract->deleteconfirm_type_zone($key);
        $g_db->query("delete from " . TABLE_ABSTRACT_TYPES . " WHERE abstract_types_id=" . (int)$key);
      }
      $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
      tep_redirect(tep_href_link($g_script));
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
        <div class="maincell" style="width: 100%">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_ABSTRACT_TYPES_ADD; ?></h1></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form("add_field", $g_script, 'action=add', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_ABSTRACT_NAME; ?></th>
              <th><?php echo TABLE_HEADING_ABSTRACT_CLASS; ?></th>
              <th><?php echo TABLE_HEADING_ABSTRACT_TABLE; ?></th>
              <th><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
            </tr>
            <tr>
              <td><?php echo tep_draw_input_field('name', '', 'maxlength=64'); ?></td>
              <td><?php echo tep_draw_input_field('class', '', 'maxlength=64') . '.php'; ?></td>
              <td><?php echo tep_draw_input_field('table', '', 'maxlength=64'); ?></td>
              <td><?php echo tep_draw_input_field('sort', '', 'size=3, maxlength=3'); ?></td>
            </tr>
            <tr>
              <td class="formButtons" colspan="5"><?php echo tep_image_submit('button_insert.gif', IMAGE_INSERT); ?></td>
            </tr>
          </table></form></div>
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_ABSTRACT_TYPES_UPDATE; ?></h1></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('abstract_types', $g_script,'action=update', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo '<a href="javascript:void(0)" onclick="copy_checkboxes(document.abstract_types,\'mark\')" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_ABSTRACT_NAME; ?></th>
              <th><?php echo TABLE_HEADING_ABSTRACT_CLASS; ?></th>
              <th><?php echo TABLE_HEADING_ABSTRACT_TABLE; ?></th>
              <th><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
            </tr>
<?php
    $rows=0;
    $abstract_types_query = $g_db->query("select at.abstract_types_id, at.abstract_types_name, at.abstract_types_class, at.abstract_types_table, at.abstract_types_status, at.sort_order from " . TABLE_ABSTRACT_TYPES . " at order by at.sort_order");
    while( $abstract_types = $g_db->fetch_array($abstract_types_query) ) {
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
      echo '                      <tr class="' . $row_class . '">';
?>
              <td><?php echo tep_draw_checkbox_field('mark['.$abstract_types['abstract_types_id'].']', 1); ?></td>
              <td><?php echo tep_draw_input_field('name['.$abstract_types['abstract_types_id'] . ']', $abstract_types['abstract_types_name'], '', false, 'text', true); ?></td>
              <td><?php echo tep_draw_input_field('class['.$abstract_types['abstract_types_id'] . ']', $abstract_types['abstract_types_class'], '', false, 'text', true) . '.php'; ?></td>
              <td><?php echo tep_draw_input_field('table['.$abstract_types['abstract_types_id'] . ']', $abstract_types['abstract_types_table'], '', false, 'text', true); ?></td>
              <td><?php echo tep_draw_input_field('sort['.$abstract_types['abstract_types_id'] . ']', $abstract_types['sort_order'], 'size=3', false, 'text', true); ?></td>
              <td class="calign">
<?php
      if ($abstract_types['abstract_types_status'] == '1') {
        echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '&nbsp;&nbsp;<a href="' . tep_href_link($g_script, 'action=setflag&flag=0&id=' . $abstract_types['abstract_types_id'], 'NONSSL') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
      } else {
        echo '<a href="' . tep_href_link($g_script, 'action=setflag&flag=1&id=' . $abstract_types['abstract_types_id'], 'NONSSL') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
      }
?>
              </td>
            </tr>
<?php
    } 
?>
            <tr>
              <td colspan="6" class="formButtons"><?php echo tep_image_submit('button_update.gif',IMAGE_UPDATE, 'class="dflt" name="update"') . tep_image_submit('button_delete.gif', IMAGE_DELETE, 'class="dflt" name="remove"') ?></td>
            </tr>
          </table></form></div>
        </div>
<?php require('includes/objects/html_end.php'); ?>
