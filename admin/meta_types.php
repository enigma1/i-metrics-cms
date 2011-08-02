<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// META-G Class Types for the META-G Zones component for Admin
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

  if (isset($_POST['remove_x']) || isset($_POST['remove_y'])) $action='remove';

  switch ($action) {
    case 'setflag':
      $sql_data_array = array('meta_types_status' => $g_db->prepare_input($_GET['flag']));
      $g_db->perform(TABLE_META_TYPES, $sql_data_array, 'update', 'meta_types_id=' . $_GET['id']);
      tep_redirect(tep_href_link($g_script));
      break;
    case 'add':
      if( !isset($_POST['name']) || empty($_POST['name']) || 
          !isset($_POST['class']) || empty($_POST['class']) ) {
        $messageStack->add_session(ERROR_INVALID_INPUT);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      $sql_data_array = array(
        'meta_types_name' => $g_db->prepare_input($_POST['name']),
        'meta_types_class' => $g_db->prepare_input($_POST['class']),
        'sort_order' => (int)($_POST['sort']),
        'meta_types_linkage' => (int)($_POST['linkage'])
      );

      $g_db->perform(TABLE_META_TYPES, $sql_data_array, 'insert');
      $messageStack->add_session(SUCCESS_ENTRY_CREATE, 'success');
      tep_redirect(tep_href_link($g_script));
      break;
    case 'update':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      foreach ($_POST['mark'] as $key=>$val) {
        $sql_data_array = array(
          'meta_types_name' => $g_db->prepare_input($_POST['name'][$key]),
          'meta_types_class' => $g_db->prepare_input($_POST['class'][$key]),
          'sort_order' => (int)($_POST['sort'][$key]),
          'meta_types_linkage' => (int)($_POST['linkage'][$key])
        );
        $g_db->perform(TABLE_META_TYPES, $sql_data_array, 'update', 'meta_types_id= ' . $key);
      }
      tep_redirect(tep_href_link($g_script));
      break;
    case 'remove':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      foreach ($_POST['mark'] as $key=>$val) {
        $g_db->query("delete from " . TABLE_META_TYPES . " where meta_types_id='" . $g_db->input($key) . "'");
      }
      $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
      tep_redirect(tep_href_link($g_script));
      break;
    default:
      break;
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
        <div class="maincell" style="width: 100%;">
          <div class="comboHeading">
            <div><h1><?php echo HEADING_META_TYPES_ADD; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_INSERT; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form("add_field", $g_script, 'action=add', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_META_NAME; ?></th>
              <th><?php echo TABLE_HEADING_META_CLASS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_META_LINKAGE; ?></th>
            </tr>
            <tr>
              <td><div class="rpad"><?php echo tep_draw_input_field('name', '', 'class="wider"'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('class', '', 'class="wider"'); ?></div></td>
              <td class="calign"><?php echo tep_draw_input_field('sort', '', 'size=3'); ?></td>
              <td class="calign"><?php echo tep_draw_input_field('linkage', '', 'size=3'); ?></td>
            </tr>
            <tr>
              <td colspan="4" class="formButtons"><?php echo tep_image_submit('button_insert.gif', IMAGE_INSERT); ?></td>
            </tr>
          </table></form></div>
        </div>
        <div class="maincell" style="width: 100%">
          <div class="comboHeading">
            <div><h1><?php echo HEADING_META_TYPES_UPDATE; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_UPDATE; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('meta_types', $g_script,'action=update', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo '<a href="#mark" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_META_NAME; ?></th>
              <th><?php echo TABLE_HEADING_META_CLASS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_META_LINKAGE; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
            </tr>
<?php
  $meta_types_query = $g_db->query("select at.* from " . TABLE_META_TYPES . " at order by at.sort_order");
  while ($meta_types = $g_db->fetch_array($meta_types_query)) {
?>
            <tr>
              <td class="calign"><?php echo tep_draw_checkbox_field('mark['.$meta_types['meta_types_id'].']', 1) ?></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('name[' . $meta_types['meta_types_id'] . ']', $meta_types['meta_types_name'], 'class="wider"'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('class[' . $meta_types['meta_types_id'] . ']', $meta_types['meta_types_class'], 'class="wider"'); ?></div></td>
              <td class="calign"><?php echo tep_draw_input_field('sort[' . $meta_types['meta_types_id'] . ']', $meta_types['sort_order'], 'size=3'); ?></td>
              <td class="calign"><?php echo tep_draw_input_field('linkage['.$meta_types['meta_types_id'] . ']', $meta_types['meta_types_linkage'], 'size=3'); ?></td>
              <td class="medsep calign">
<?php
    if ($meta_types['meta_types_status'] == '1') {
      echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '<a href="' . tep_href_link($g_script, 'action=setflag&flag=0&id=' . $meta_types['meta_types_id'], 'NONSSL') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
    } else {
      echo '<a href="' . tep_href_link($g_script, 'action=setflag&flag=1&id=' . $meta_types['meta_types_id'], 'NONSSL') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
    }
?>
              </td>
            </tr>
<?php
  } 
?>
            <tr>
              <td colspan="6" class="formButtons"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE, 'class="dflt" name="update"') . tep_image_submit('button_delete.gif', IMAGE_DELETE, 'class="dflt" name="remove"') ?></td>
            </tr>
          </table></form></div>
        </div>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
