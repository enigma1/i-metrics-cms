<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO-G Class Types for the SEO-G Zones component for osCommerce Admin
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

  if (isset($_POST['remove_x']) || isset($_POST['remove_y'])) $action='remove';

  switch ($action) {
    case 'setflag':
      $sql_data_array = array('seo_types_status' => $g_db->prepare_input($_GET['flag']));
      $g_db->perform(TABLE_SEO_TYPES, $sql_data_array, 'update', 'seo_types_id=' . $_GET['id']);
      tep_redirect(tep_href_link($g_script));
      break;
    case 'add':
      if( !isset($_POST['name']) || empty($_POST['name']) || 
          !isset($_POST['class']) || empty($_POST['class']) ) {
        $messageStack->add_session(ERROR_INVALID_INPUT);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      $sql_data_array = array(
        'seo_types_name' => $g_db->prepare_input($_POST['name']),
        'seo_types_handler' => $g_db->prepare_input($_POST['handler']),
        'seo_types_subfix' => $g_db->prepare_input($_POST['subfix']),
        'seo_types_class' => $g_db->prepare_input($_POST['class']),
        'seo_types_prefix' => $g_db->prepare_input($_POST['prefix']),
        'sort_order' => (int)($_POST['sort']),
        'seo_types_linkage' => (int)($_POST['linkage'])
      );

      $g_db->perform(TABLE_SEO_TYPES, $sql_data_array, 'insert');
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
          'seo_types_name' => $g_db->prepare_input($_POST['name'][$key]),
          'seo_types_handler' => $g_db->prepare_input($_POST['handler'][$key]),
          'seo_types_subfix' => $g_db->prepare_input($_POST['subfix'][$key]),
          'seo_types_class' => $g_db->prepare_input($_POST['class'][$key]),
          'seo_types_prefix' => $g_db->prepare_input($_POST['prefix'][$key]),
          'sort_order' => (int)($_POST['sort'][$key]),
          'seo_types_linkage' => (int)($_POST['linkage'][$key])
        );
        $g_db->perform(TABLE_SEO_TYPES, $sql_data_array, 'update', 'seo_types_id= ' . $key);
      }
      tep_redirect(tep_href_link($g_script));
      break;
    case 'remove':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      foreach ($_POST['mark'] as $key=>$val) {
        $g_db->query("delete from " . TABLE_SEO_TYPES . " where seo_types_id='" . $g_db->input($key) . "'");
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
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&hID=1') . '" class="' . tep_get_script_name() . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', BOX_OTHER_QUICK_HELP) . '</a>'; ?></div>
            <div class="floater"><h1><?php echo HEADING_SEO_TYPES_ADD; ?></h1></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form("add_field", $g_script, 'action=add', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_SEO_NAME; ?></th>
              <th><?php echo TABLE_HEADING_SEO_HANDLER; ?></th>
              <th><?php echo TABLE_HEADING_SEO_SUBFIX; ?></th>
              <th><?php echo TABLE_HEADING_SEO_CLASS; ?></th>
              <th><?php echo TABLE_HEADING_SEO_PREFIX; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_SEO_LINKAGE; ?></th>
            </tr>
            <tr>
              <td><div class="rpad"><?php echo tep_draw_input_field('name'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('handler'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('subfix'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('class'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('prefix'); ?></div></td>
              <td class="calign"><div class="rpad"><?php echo tep_draw_input_field('sort', '', 'size="3" maxlength="3"'); ?></td>
              <td class="calign"><div class="rpad"><?php echo tep_draw_input_field('linkage', '', 'size="3" maxlength="3"'); ?></td>
            </tr>
            <tr>
              <td colspan="7" class="formButtons"><?php echo tep_image_submit('button_insert.gif', IMAGE_INSERT); ?></td>
            </tr>
          </table></form></div>
          <div class="comboHeading">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&hID=1') . '" class="' . tep_get_script_name() . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', BOX_OTHER_QUICK_HELP) . '</a>'; ?></div>
            <div class="floater"><h1><?php echo HEADING_SEO_TYPES_UPDATE; ?></h1></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('seo_types', $g_script,'action=update', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo '<a href="#mark" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_SEO_NAME; ?></th>
              <th><?php echo TABLE_HEADING_SEO_HANDLER; ?></th>
              <th><?php echo TABLE_HEADING_SEO_SUBFIX; ?></th>
              <th><?php echo TABLE_HEADING_SEO_CLASS; ?></th>
              <th><?php echo TABLE_HEADING_SEO_PREFIX; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_SEO_LINKAGE; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></td>
            </tr>
<?php
  $seo_types_query = $g_db->query("select at.* from " . TABLE_SEO_TYPES . " at order by at.sort_order");
  while ($seo_types = $g_db->fetch_array($seo_types_query)) {
?>
            <tr>
              <td class="calign"><?php echo tep_draw_checkbox_field('mark['.$seo_types['seo_types_id'].']', 1) ?></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('name[' . $seo_types['seo_types_id'] . ']', $seo_types['seo_types_name']); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('handler[' . $seo_types['seo_types_id'] . ']', $seo_types['seo_types_handler']); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('subfix[' . $seo_types['seo_types_id'] . ']', $seo_types['seo_types_subfix']); ?></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('class[' . $seo_types['seo_types_id'] . ']', $seo_types['seo_types_class']) ; ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('prefix[' . $seo_types['seo_types_id'] . ']', $seo_types['seo_types_prefix']); ?></div></td>
              <td class="calign"><?php echo tep_draw_input_field('sort[' . $seo_types['seo_types_id'] . ']', $seo_types['sort_order'], 'size=3'); ?></td>
              <td class="calign"><?php echo tep_draw_input_field('linkage['.$seo_types['seo_types_id'] . ']', $seo_types['seo_types_linkage'], 'size=3'); ?></td>
              <td class="medsep calign">
<?php
    if ($seo_types['seo_types_status'] == '1') {
      echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '<a href="' . tep_href_link($g_script, 'action=setflag&flag=0&id=' . $seo_types['seo_types_id'], 'NONSSL') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
    } else {
      echo '<a href="' . tep_href_link($g_script, 'action=setflag&flag=1&id=' . $seo_types['seo_types_id'], 'NONSSL') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
    }
?>
              </td>
            </tr>
<?php
  } 
?>
            <tr>
              <td colspan="9" class="formButtons"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE, 'name="update"') . '&nbsp;' . tep_image_submit('button_delete.gif', IMAGE_DELETE, 'name="remove"') ?></td>
            </tr>
          </table></form></div>
        </div>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>