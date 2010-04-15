<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Form Fields Generator/Controller
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
/*
if( isset($_GET['test']) ) {
require(DIR_WS_CLASSES . 'form_fields.php');
$g_form_fields = new form_fields();
$g_form_fields->create_from_xml('test_fields.xml');
}
*/

  $form_layout_array = array(
                             array('id' => '1', 'text' => 'LINEAR'),
                             array('id' => '2', 'text' => 'MATRIX'),
                            );

  $action = (isset($_GET['action']) ? $_GET['action'] : 'list');
  $fID = (isset($_GET['fID']) ? $_GET['fID'] : '');
  $oID = (isset($_GET['oID']) ? $_GET['oID'] : '');

  if (isset($_POST['delete_x']) || isset($_POST['delete_y'])) {
    $action='delete';
  } elseif (isset($_POST['delete_option_x']) || isset($_POST['delete_option_y'])) {
    $action='delete_option';
  } elseif (isset($_POST['delete_value_x']) || isset($_POST['delete_value_y'])) {
    $action='delete_value';
  }


  switch ($action) {
    case 'set_flag':
      $sql_data_array = array('status_id' => (int)$_GET['flag']);
      $g_db->perform(TABLE_FORM_FIELDS, $sql_data_array, 'update', 'form_fields_id=' . (int)$_GET['id']);
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag', 'id')) ));
      break;
    case 'insert':
      if( tep_not_null($_POST['name']) && $_POST['limit'] > 0) {
        $sql_data_array = array(
                                'form_fields_name' => $g_db->prepare_input($_POST['name']),
                                'form_fields_description' => $g_db->prepare_input($_POST['description']),
                                'layout_id' => (int)$_POST['layout'],
                                'limit_id' => (int)$_POST['limit'],
                                'sort_id' => (int)$_POST['order']
                               );

        $g_db->perform(TABLE_FORM_FIELDS, $sql_data_array, 'insert');
        $new_form_field_id = $g_db->insert_id();
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'fID')) . 'action=list&fID=' . $new_form_field_id ));
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'fID')) . 'action=list'));
      break;
    case 'update':
      if(isset($_POST['mark']) && is_array($_POST['mark']) ) {
        foreach ($_POST['mark'] as $key => $val) {
          if( !tep_not_null($_POST['name'][$key]) || $_POST['limit'][$key] < 1 ) {
            continue;
          }

          $check_query = $g_db->query("select form_fields_id from " . TABLE_FORM_FIELDS . " where form_fields_id = '" . (int)$key . "'");
          if( $check_array = $g_db->fetch_array($check_query) ) {
            $sql_data_array = array(
                                    'form_fields_name' => $g_db->prepare_input($_POST['name'][$key]),
                                    'form_fields_description' => $g_db->prepare_input($_POST['description'][$key]),
                                    'layout_id' => (int)$_POST['layout'][$key],
                                    'limit_id' => (int)$_POST['limit'][$key],
                                    'sort_id' => (int)$_POST['order'][$key]
                                   );
            $g_db->perform(TABLE_FORM_FIELDS, $sql_data_array, 'update', "form_fields_id= '" . (int)$key . "'");
          }
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=list' ));
      break;
    case 'delete':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=list'));
      }
      break;
    case 'delete_confirm':
      if(isset($_POST['mark']) && is_array($_POST['mark']) ) {
        foreach ($_POST['mark'] as $key=>$val) {
          $check_query = $g_db->query("select form_fields_id from " . TABLE_FORM_FIELDS . " where form_fields_id=" . (int)$key);
          if( $check_array = $g_db->fetch_array($check_query) ) {
            $g_db->query("delete from " . TABLE_FORM_VALUES . " where form_fields_id=" . (int)$key);
            $g_db->query("delete from " . TABLE_FORM_OPTIONS . " where form_fields_id=" . (int)$key);
            $g_db->query("delete from " . TABLE_FORM_FIELDS . " where form_fields_id=" . (int)$key);
          }
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'fID')) . 'action=list'));
      break;

    case 'duplicate':
      $form_fields_query = $g_db->query("select * from " . TABLE_FORM_FIELDS . " where form_fields_id = '" . (int)$fID . "'");
      if( $form_fields_array = $g_db->fetch_array($form_fields_query) ) {
        unset($form_fields_array['form_fields_id']);
        $g_db->perform(TABLE_FORM_FIELDS, $form_fields_array, 'insert');
        $new_form_field_id = $g_db->insert_id();

        $option_fields_query = $g_db->query("select * from " . TABLE_FORM_OPTIONS . " where form_fields_id = '" . (int)$fID . "'");
        while($option_fields_array = $g_db->fetch_array($option_fields_query) ) {
          $org_option_field_id = $option_fields_array['form_options_id'];
          unset($option_fields_array['form_options_id']);
          $option_fields_array['form_fields_id'] = $new_form_field_id;
          $g_db->perform(TABLE_FORM_OPTIONS, $option_fields_array, 'insert');
          $new_option_field_id = $g_db->insert_id();

          $value_fields_query = $g_db->query("select * from " . TABLE_FORM_VALUES . " where form_fields_id = '" . (int)$fID . "' and form_options_id = '" . (int)$org_option_field_id . "'");
          while($value_fields_array = $g_db->fetch_array($value_fields_query) ) {
            unset($value_fields_array['form_values_id']);
            $value_fields_array['form_fields_id'] = $new_form_field_id;
            $value_fields_array['form_options_id'] = $new_option_field_id;
            $g_db->perform(TABLE_FORM_VALUES, $value_fields_array, 'insert');
          }
        }
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'fID')) . 'action=list&fID=' . $new_form_field_id ));
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'fID')) . 'action=list'));
      break;

    case 'set_option_flag':
      $sql_data_array = array('status_id' => (int)$_GET['flag']);
      $g_db->perform(TABLE_FORM_OPTIONS, $sql_data_array, 'update', "form_fields_id = '" . (int)$fID . "' and form_options_id= '" . (int)$_GET['id'] . "'");
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag', 'id')) . 'action=options_list'));
      break;

    case 'insert_option':
      if( tep_not_null($_POST['name']) && $_POST['limit'] > 0) {
        $check_query = $g_db->query("select form_fields_id from " . TABLE_FORM_FIELDS . " where form_fields_id = '" . (int)$fID . "'");
        if( $check_array = $g_db->fetch_array($check_query) ) {
          $sql_data_array = array(
                                  'form_fields_id' => (int)$fID,
                                  'form_types_id' => (int)$_POST['type'],
                                  'form_options_name' => $g_db->prepare_input($_POST['name']),
                                  'image_status' => (isset($_POST['image'])?1:0),
                                  'layout_id' => (int)$_POST['layout'],
                                  'limit_id' => (int)$_POST['limit'],
                                  'sort_id' => (int)$_POST['order']
                                 );
          $g_db->perform(TABLE_FORM_OPTIONS, $sql_data_array, 'insert');
          $new_form_option_id = $g_db->insert_id();
          tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'fID', 'oID')) . 'action=options_list&fID=' . $fID . '&oID=' . $new_form_option_id));
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action','fID','oID')) . 'action=options_list&fID=' . $fID));
      break;
    case 'update_option':
      if(isset($_POST['mark']) && is_array($_POST['mark']) ) {
        foreach ($_POST['mark'] as $key => $val) {
          if( !tep_not_null($_POST['name'][$key]) || $_POST['limit'][$key] < 1 ) {
            continue;
          }

          $sql_data_array = array(
                                  'form_options_name' => $g_db->prepare_input($_POST['name'][$key]),
                                  'form_types_id' => (int)$_POST['type'][$key],
                                  'image_status' => (isset($_POST['image'][$key])?1:0),
                                  'layout_id' => (int)$_POST['layout'][$key],
                                  'limit_id' => (int)$_POST['limit'][$key],
                                  'sort_id' => (int)$_POST['order'][$key]
                                 );
          $g_db->perform(TABLE_FORM_OPTIONS, $sql_data_array, 'update', "form_fields_id = '" . (int)$fID . "' and form_options_id= '" . (int)$key . "'");
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=options_list' ));
      break;
    case 'delete_option':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=options_list'));
      }
      break;
    case 'delete_option_confirm':
      $check_query = $g_db->query("select form_fields_id from " . TABLE_FORM_FIELDS . " where form_fields_id= '" . (int)$fID . "'");
      if( $check_array = $g_db->fetch_array($check_query) ) {
        if(isset($_POST['mark']) && is_array($_POST['mark']) ) {
          foreach ($_POST['mark'] as $key=>$val) {
            $g_db->query("delete from " . TABLE_FORM_OPTIONS . " where form_options_id= '" . (int)$key . "' and form_fields_id= '" . (int)$fID . "'");
            $g_db->query("delete from " . TABLE_FORM_VALUES . " where form_options_id= '" . (int)$key . "' and form_fields_id= '" . (int)$fID . "'");
          }
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'oID')) . 'action=options_list'));
      break;

    case 'duplicate_option':
      $option_fields_query = $g_db->query("select * from " . TABLE_FORM_OPTIONS . " where form_fields_id = '" . (int)$fID . "' and form_options_id = '" . (int)$oID . "'");
      if($option_fields_array = $g_db->fetch_array($option_fields_query) ) {
        unset($option_fields_array['form_options_id']);
        $g_db->perform(TABLE_FORM_OPTIONS, $option_fields_array, 'insert');
        $new_option_field_id = $g_db->insert_id();

        $value_fields_query = $g_db->query("select * from " . TABLE_FORM_VALUES . " where form_fields_id = '" . (int)$fID . "' and form_options_id = '" . (int)$oID . "'");
        while($value_fields_array = $g_db->fetch_array($value_fields_query) ) {
          unset($value_fields_array['form_values_id']);
          $value_fields_array['form_options_id'] = $new_option_field_id;
          $g_db->perform(TABLE_FORM_VALUES, $value_fields_array, 'insert');
        }
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'oID')) . 'action=options_list&oID=' . $new_option_field_id));
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'oID')) . 'action=options_list'));
      break;

    case 'set_value_flag':
      $sql_data_array = array('status_id' => (int)$_GET['flag']);
      $g_db->perform(TABLE_FORM_VALUES, $sql_data_array, 'update', "form_fields_id = '" . (int)$fID . "' and form_options_id = '" . (int)$oID . "' and form_values_id= '" . (int)$_GET['id'] . "'");
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag', 'id')) . 'action=values_list'));
      break;

    case 'insert_value':
      $check_query = $g_db->query("select form_options_id, image_status from " . TABLE_FORM_OPTIONS . " where form_fields_id = '" . (int)$fID . "' and form_options_id = '" . (int)$oID . "'");
      if( $check_array = $g_db->fetch_array($check_query) ) {
        $sql_data_array = array(
                                'form_fields_id' => (int)$fID,
                                'form_options_id' => (int)$oID,
                                'form_values_name' => $g_db->prepare_input($_POST['name']),
                                'sort_id' => (int)$_POST['order']
                               );
        $g_db->perform(TABLE_FORM_VALUES, $sql_data_array, 'insert');
        $new_form_value_id = $g_db->insert_id();

        if( $check_array['image_status'] == '1' ) {
          $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
          $cImage = new upload('image', $images_path);
          if( $cImage->c_result ) {
            $g_db->query("update " . TABLE_FORM_VALUES . " set form_values_image = '" . $g_db->input($cImage->filename) . "' where form_values_id = '" . (int)$new_form_value_id . "'");
          }
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=values_list' ));
      break;
    case 'update_value':
      if(isset($_POST['mark']) && is_array($_POST['mark']) ) {
        foreach ($_POST['mark'] as $key => $val) {
          $sql_data_array = array(
                                  'form_values_name' => $g_db->prepare_input($_POST['name'][$key]),
                                  'sort_id' => (int)$_POST['order'][$key]
                                 );
          $g_db->perform(TABLE_FORM_VALUES, $sql_data_array, 'update', "form_fields_id = '" . (int)$fID . "' and form_options_id= '" . (int)$oID . "' and form_values_id = '" . (int)$key . "'");
          if( isset($_FILES['image_' . $key]) && tep_not_null($_FILES['image_' . $key]['name']) ) {
            $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
            $cImage = new upload('image_' . $key, $images_path);
            if( $cImage->c_result ) {
              $g_db->query("update " . TABLE_FORM_VALUES . " set form_values_image = '" . $g_db->input($cImage->filename) . "' where form_fields_id = '" . (int)$fID . "' and form_options_id= '" . (int)$oID . "' and form_values_id = '" . (int)$key . "'");
            }
          }
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=values_list' ));
      break;
    case 'delete_value':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=values_list'));
      }
      break;
    case 'delete_value_confirm':
      if(isset($_POST['mark']) && is_array($_POST['mark']) ) {
        foreach ($_POST['mark'] as $key=>$val) {
          $g_db->query("delete from " . TABLE_FORM_VALUES . " where form_values_id= '" . (int)$key . "' and form_fields_id= '" . (int)$fID . "' and form_options_id = '" . (int)$oID . "'");
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=values_list'));
      break;
    case 'remove_values_image':
      if( isset($_GET['pm_id']) && tep_not_null($_GET['pm_id']) ) {
        $check_query = $g_db->query("select form_values_image from " . TABLE_FORM_VALUES . " where form_values_id= '" . (int)$_GET['pm_id'] . "' and form_fields_id= '" . (int)$fID . "' and form_options_id = '" . (int)$oID . "'");
        if( $check_array = $g_db->fetch_array($check_query) ) {
          $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
          if(strlen($check_array['form_values_image']) > 4 && file_exists($images_path . $check_array['form_values_image'])) {
            @unlink($images_path . $check_array['form_values_image']);
            clearstatcache();
          }
          $g_db->query("update " . TABLE_FORM_VALUES . " set form_values_image = '' where form_values_id= '" . (int)$_GET['pm_id'] . "' and form_fields_id= '" . (int)$fID . "' and form_options_id = '" . (int)$oID . "'");
        }
      }

      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'pm_id')) . 'action=values_list'));
      break;

    case 'values_list':
      break;
    case 'options_list':
      break;
    case 'list':
      break;
    default:
      $action = 'list';
      break;
  }

?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php require('includes/objects/html_start_sub2.php'); ?>
<?php
  if($action == 'list') {
?>
        <div class="maincell">
          <div class="comboHeading"><h1><?php echo HEADING_FORM_UPDATE; ?></h1></div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_UPDATE; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('fields_update', $g_script,'action=update', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo '<a href="javascript:void(0)" onclick="copy_checkboxes(document.fields_update,\'mark\')" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_NAME; ?></th>
              <th><?php echo TABLE_HEADING_DESCRIPTION; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_LAYOUT; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_LIMITER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ORDER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
<?php
    $form_fields_query_raw = "select form_fields_id, form_fields_name, form_fields_description, layout_id, limit_id, sort_id, status_id from " . TABLE_FORM_FIELDS . " order by sort_id";
    $form_fields_split = new splitPageResults($form_fields_query_raw);
    $form_fields_query = $g_db->query($form_fields_split->sql_query);
    while ($form_field = $g_db->fetch_array($form_fields_query)) {

      if ((!isset($_GET['fID']) || (isset($_GET['fID']) && ($_GET['fID'] == $form_field['form_fields_id']))) && !isset($gfInfo) ) {
        $form_field['selects'] = array();
        $check_query = $g_db->query("select form_options_name from " . TABLE_FORM_OPTIONS . " where form_fields_id = '" . (int)$form_field['form_fields_id'] . "' order by sort_id");
        if( $g_db->num_rows($check_query) ) {
          while($check_array = $g_db->fetch_array($check_query)) {
            $form_field['selects'][] = $check_array['form_options_name'];
          }
        } else {
          $form_field['selects'][] = 'No Options Set';
        }
        $gfInfo = new objectInfo($form_field);
      }

      if (isset($gfInfo) && is_object($gfInfo) && ($form_field['form_fields_id'] == $gfInfo->form_fields_id)) {
        echo '                  <tr class="dataTableRowSelected">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow">' . "\n";
      }
?>
              <td><?php echo tep_draw_checkbox_field('mark['.$form_field['form_fields_id'].']', 1) ?></td>
              <td><?php echo tep_draw_input_field('name['.$form_field['form_fields_id'].']', $form_field['form_fields_name'], 'size=30');?></td>
              <td><?php echo tep_draw_textarea_field('description['.$form_field['form_fields_id'].']', 'soft', '30', '1', $form_field['form_fields_description']);?></td>
              <td class="calign"><?php echo tep_draw_pull_down_menu('layout['.$form_field['form_fields_id'].']', $g_form_fields->layout_array, $form_field['layout_id']); ?></td>
              <td class="calign"><?php echo tep_draw_input_field('limit['.$form_field['form_fields_id'].']', $form_field['limit_id'], 'size="1"');?></td>
              <td class="calign"><?php echo tep_draw_input_field('order['.$form_field['form_fields_id'].']', $form_field['sort_id'], 'size="5"');?></td>
              <td class="tinysep calign">
<?php
      if ($form_field['status_id'] == '1') {
        echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag', 'id')) . 'action=set_flag&flag=0&id=' . $form_field['form_fields_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
      } else {
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag', 'id')) . 'action=set_flag&flag=1&id=' . $form_field['form_fields_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
      }
?>
              </td>
              <td class="tinysep calign">
<?php 
      if (isset($gfInfo) && is_object($gfInfo) && ($form_field['form_fields_id'] == $gfInfo->form_fields_id)) { 
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'fID')) . 'action=options_list&fID=' . $gfInfo->form_fields_id) . '">' . tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', IMAGE_DETAILS) . '</a>'; 
      } else { 
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'fID') ) . 'fID=' . $form_field['form_fields_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
      } 
?>
              </td>
            </tr>
<?php
    } 
?>
            <tr>
              <td colspan="8" class="formButtons"><?php echo tep_image_submit('button_update.gif',IMAGE_UPDATE, 'class="dflt" name="update"') . tep_image_submit('button_delete.gif',IMAGE_DELETE,'class="dflt" name="delete"'); ?></td>
            </tr>
          </table></form></div>

          <div class="splitLine">
            <div class="floater"><?php echo $form_fields_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $form_fields_split->display_links(tep_get_all_get_params(array('page')) ); ?></div>
          </div>

          <div class="comboHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_INSERT; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form("insert_filter", $g_script, 'action=insert', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_NAME; ?></td>
              <th><?php echo TABLE_HEADING_DESCRIPTION; ?></td>
              <th class="calign"><?php echo TABLE_HEADING_LAYOUT; ?></td>
              <th class="calign"><?php echo TABLE_HEADING_LIMITER; ?></td>
              <th class="calign"><?php echo TABLE_HEADING_ORDER; ?></td>
            </tr>
            <tr>
              <td><?php echo tep_draw_input_field('name', '', 'size="30"'); ?></td>
              <td><?php echo tep_draw_textarea_field('description', 'soft', '30', '1');?></td>
              <td class="calign"><?php echo tep_draw_pull_down_menu('layout', $g_form_fields->layout_array); ?></td>
              <td class="calign"><?php echo tep_draw_input_field('limit', '1', 'size="1"'); ?></td>
              <td class="calign"><?php echo tep_draw_input_field('order', '1', 'size="5"'); ?></td>
            </tr>
            <tr>
              <td colspan="6" class="formButtons"><?php echo tep_image_submit('button_insert.gif', IMAGE_INSERT); ?></td>
            </tr>
          </table></form></div>
        </div>

<?php
    $heading = array();
    $contents = array();

    switch ($action) {
      default:
        if( isset($gfInfo) && is_object($gfInfo) ) {
          $heading[] = array('text' => '<b>' . $gfInfo->form_fields_name . '</b>');
          $buttons = array(
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'fID')) . 'action=options_list&fID=' . $gfInfo->form_fields_id) . '">' . tep_image_button('button_details.gif', $gfInfo->form_fields_name . ' ' . IMAGE_DETAILS) . '</a>',
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'fID')) . 'action=duplicate&fID=' . $gfInfo->form_fields_id) . '">' . tep_image_button('button_copy.gif', IMAGE_COPY . ' ' . $gfInfo->form_fields_name) . '</a>',
          );
          $contents[] = array(
            'params' => 'text-align: center', 
            'text' => implode('', $buttons)
          );
          $contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_OPTIONS . ' <br /><hr />' . implode('<br /><hr />', $gfInfo->selects) . '<hr />' );
        } else { // create generic_text dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array(
            'params' => 'text-align: center', 
            'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW_ENTRY_TEXT)
          );
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
  } elseif($action == 'delete') {
?>
        <div class="maincell wider">
          <div class="comboHeading"><h1><?php echo HEADING_FORM_DELETE; ?></h1></div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_UPDATE; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('delete', $g_script, tep_get_all_get_params(array('action')) . 'action=delete_confirm', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_NAME; ?></th>
            </tr>
<?php
    $rows = 0;
    foreach( $_POST['mark'] as $key => $value ) {
      $form_fields_query = $g_db->query("select form_fields_id, form_fields_name from " . TABLE_FORM_FIELDS . " where form_fields_id = '" . (int)$key . "'");
      if( $form_field = $g_db->fetch_array($form_fields_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        echo '                      <tr class="' . $row_class . '">';
?>
              <td><?php echo $form_field['form_fields_name'] . tep_draw_hidden_field('mark[' . $form_field['form_fields_id'] . ']', $form_field['form_fields_id']); ?></td>
            </tr>
<?php
      }
    }
?>
            <tr>
              <td class="formButtons">
<?php 
    echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM);
?>
              </td>
            </tr>
          </table></form></div>
        </div>
<?php
// Options Listings
  } elseif($action == 'options_list') {
?>
        <div class="maincell">
          <div class="comboHeading"><h1>
<?php 
    $name_query = $g_db->query("select form_fields_name from " . TABLE_FORM_FIELDS . " where form_fields_id = '" . (int)$fID . "'");
    if($name_array = $g_db->fetch_array($name_query) ) {
      echo sprintf(HEADING_FORM_OPTIONS_UPDATE, $name_array['form_fields_name']);
    } else {
      echo 'Error';
    }
?>
          </h1></div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_UPDATE_OPTION; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('options_update', $g_script, tep_get_all_get_params(array('action')) . 'action=update_option', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th width="5%"><?php echo '<a href="javascript:void(0)" onclick="copy_checkboxes(document.options_update,\'mark\')" title="Page Select On/Off" class="menuBoxHeadingLink"><span class="dataTableHeadingContent">' . tep_image(DIR_WS_ICONS . 'tick.gif', 'Page Select On/Off') . '</span></a>'; ?></th>
              <th><?php echo TABLE_HEADING_NAME; ?></th>
              <th><?php echo TABLE_HEADING_TYPE; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_IMAGE; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_LAYOUT; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_LIMITER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ORDER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
<?php
    $rows = 0;
    $form_options_query = $g_db->query("select form_options_id, form_options_name, form_types_id, layout_id, limit_id, sort_id, image_status, status_id from " . TABLE_FORM_OPTIONS . " where form_fields_id = '" . (int)$fID . "' order by sort_id");
    while ($form_option = $g_db->fetch_array($form_options_query)) {
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
      if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ($_GET['oID'] == $form_option['form_options_id']))) && !isset($ofInfo) ) {
        $option_field['selects'] = array();
        $check_query = $g_db->query("select form_values_name from " . TABLE_FORM_VALUES . " where form_fields_id = '" . (int)$fID . "' and form_options_id = '" . (int)$form_option['form_options_id'] . "' order by sort_id");
        if( $g_db->num_rows($check_query) ) {
          while($check_array = $g_db->fetch_array($check_query)) {
            $form_option['selects'][] = $check_array['form_values_name'];
          }
        } else {
          $form_option['selects'][] = 'No Options Set';
        }
        $ofInfo = new objectInfo($form_option);
      }

      if (isset($ofInfo) && is_object($ofInfo) && ($form_option['form_options_id'] == $ofInfo->form_options_id)) {
        echo '                  <tr class="dataTableRowSelected">' . "\n";
      } else {
        echo '                      <tr class="' . $row_class . '">';
      }
?>
              <td><?php echo tep_draw_checkbox_field('mark['.$form_option['form_options_id'].']', 1); ?></td>
              <td><?php echo tep_draw_input_field('name['.$form_option['form_options_id'].']', $form_option['form_options_name'], 'size=30');?></td>
              <td><?php echo tep_draw_pull_down_menu('type['.$form_option['form_options_id'].']', $g_form_fields->types_array, $form_option['form_types_id']); ?></td>
              <td class="calign"><?php echo tep_draw_checkbox_field('image['.$form_option['form_options_id'].']', 'on', $form_option['image_status']?true:false); ?></td>
              <td class="calign"><?php echo tep_draw_pull_down_menu('layout['.$form_option['form_options_id'].']', $form_layout_array, $form_option['layout_id']); ?></td>
              <td class="calign"><?php echo tep_draw_input_field('limit['.$form_option['form_options_id'].']', $form_option['limit_id'], 'size="1"');?></td>
              <td class="calign"><?php echo tep_draw_input_field('order['.$form_option['form_options_id'].']', $form_option['sort_id'], 'size="5"');?></td>
              <td class="tinysep calign">
<?php
      if ($form_option['status_id'] == '1') {
        echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag', 'id')) . 'action=set_option_flag&flag=0&id=' . $form_option['form_options_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
      }
      else {
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag', 'id')) . 'action=set_option_flag&flag=1&id=' . $form_option['form_options_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
      }
?>
              </td>
              <td class="tinysep calign">
<?php 
      if (isset($ofInfo) && is_object($ofInfo) && ($form_option['form_options_id'] == $ofInfo->form_options_id)) { 
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'oID')) . 'action=values_list&oID=' . $ofInfo->form_options_id) . '">' . tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', IMAGE_DETAILS) . '</a>';
      } else { 
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'oID')) . 'action=options_list&oID=' . $form_option['form_options_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>';
      } 
?>
              </td>
            </tr>
<?php
    } 
?>
            <tr>
              <td colspan="12" class="formButtons"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'oID')) . 'action=list') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>' . tep_image_submit('button_update.gif', IMAGE_UPDATE, 'class="dflt" name="update_option"') . tep_image_submit('button_delete.gif', IMAGE_DELETE, 'class="dflt" name="delete_option"'); ?></td>
            </tr>
          </table></form></div>
          <div class="comboHeading"><h1>
<?php 
    $name_query = $g_db->query("select form_fields_name from " . TABLE_FORM_FIELDS . " where form_fields_id = '" . (int)$fID . "'");
    if($name_array = $g_db->fetch_array($name_query) ) {
      echo sprintf(HEADING_TITLE_OPTION, $name_array['form_fields_name']);
    } else {
      echo 'Error';
    }
?>
          </h1></div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_INSERT_OPTION; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form("insert_option", $g_script, tep_get_all_get_params(array('action')) . 'action=insert_option', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_NAME; ?></th>
              <th><?php echo TABLE_HEADING_TYPE; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_IMAGE; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_LAYOUT; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_LIMITER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ORDER; ?></th>
            </tr>

            <tr>
              <td><?php echo tep_draw_input_field('name', '', 'size="30"'); ?></td>
              <td><?php echo tep_draw_pull_down_menu('type', $g_form_fields->types_array); ?></td>
              <td class="calign"><?php echo tep_draw_checkbox_field('image'); ?></td>
              <td class="calign"><?php echo tep_draw_pull_down_menu('layout', $form_layout_array); ?></td>
              <td class="calign"><?php echo tep_draw_input_field('limit', '1', 'size="1"'); ?></td>
              <td class="calign"><?php echo tep_draw_input_field('order', '1', 'size="5"'); ?></td>
            </tr>
            <tr>
              <td colspan="6" class="formButtons"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'oID')) . 'action=list') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>' . tep_image_submit('button_insert.gif', IMAGE_INSERT); ?></td>
            </tr>
          </table></form></div>
        </div>
<?php
    $heading = array();
    $contents = array();

    switch ($action) {
      default:
        if (isset($ofInfo) && is_object($ofInfo)) {
          $heading[] = array('text' => '<b>' . $ofInfo->form_options_name . '</b>');
          $buttons = array(
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'oID')) . 'action=list') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>',
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'oID')) . 'action=values_list&oID=' . $ofInfo->form_options_id) . '">' . tep_image_button('button_details.gif', $ofInfo->form_options_name . ' ' . IMAGE_DETAILS) . '</a>',
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'oID')) . 'action=duplicate_option&oID=' . $ofInfo->form_options_id) . '">' . tep_image_button('button_copy.gif', IMAGE_COPY . ' ' . $ofInfo->form_options_name) . '</a>'
          );
          $contents[] = array(
            'params' => 'text-align: center', 
            'text' => implode('', $buttons),
          );
          $contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_VALUES . ' <br /><hr />' . implode('<hr />', $ofInfo->selects) . '<hr />' );
        } else { // create generic_text dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array(
            'params' => 'text-align: center', 
            'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW_ENTRY_TEXT)
          );
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
  } elseif($action == 'delete_option') {
?>
        <div class="maincell wider">
          <div class="comboHeading"><h1><?php echo HEADING_FORM_OPTIONS_DELETE; ?></h1></div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_DELETE_OPTION; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('delete_option', $g_script, tep_get_all_get_params(array('action')) . 'action=delete_option_confirm', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_NAME; ?></td>
            </tr>
<?php
    $rows = 0;
    foreach( $_POST['mark'] as $key => $value ) {
      $form_options_query = $g_db->query("select form_options_id, form_options_name from " . TABLE_FORM_OPTIONS . " where form_fields_id = '" . (int)$fID . "' and form_options_id = '" . (int)$key . "'");
      if( $form_option = $g_db->fetch_array($form_options_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        echo '                      <tr class="' . $row_class . '">';
?>
              <td><?php echo $form_option['form_options_name'] . tep_draw_hidden_field('mark[' . $form_option['form_options_id'] . ']', $form_option['form_options_id']); ?></td>
            </tr>
<?php
      }
    }
?>
            <tr>
              <td class="formButtons">
<?php 
    echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=options_list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM);
?>
              </td>
            </tr>
          </table></form></div>
        </div>
<?php
  } elseif($action == 'values_list') {
?>
        <div class="maincell wider">
          <div class="comboHeading"><h1>
<?php 
    $name_query = $g_db->query("select gf.form_fields_name, go.form_options_name, go.image_status from " . TABLE_FORM_OPTIONS . " go left join " . TABLE_FORM_FIELDS . " gf on (gf.form_fields_id=go.form_fields_id) where gf.form_fields_id = '" . (int)$fID . "' and go.form_options_id = '" . (int)$oID . "'");
    if($name_array = $g_db->fetch_array($name_query) ) {
      echo sprintf(HEADING_FORM_VALUES_UPDATE, $name_array['form_fields_name'], $name_array['form_options_name']);
    } else {
      echo('Error - Invalid Arguments Passed');
    }
?>
          </h1></div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_UPDATE_VALUE; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('values_update', $g_script, tep_get_all_get_params(array('action')) . 'action=update_value', 'post', 'enctype="multipart/form-data"'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo '<a href="javascript:void(0)" onclick="copy_checkboxes(document.values_update,\'mark\')" title="Page Select On/Off" class="menuBoxHeadingLink"><span class="dataTableHeadingContent">' . tep_image(DIR_WS_ICONS . 'tick.gif', 'Page Select On/Off') . '</span></a>'; ?></th>
              <th><?php echo TABLE_HEADING_NAME; ?></th>
<?php
    if( $name_array['image_status'] == '1' ) {
      echo '<th>' . TABLE_HEADING_IMAGE . '</th>' . "\n";
    }
?>
              <th class="calign"><?php echo TABLE_HEADING_ORDER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
            </tr>
<?php
    $rows = 0;
    $form_values_query = $g_db->query("select form_values_id, form_values_name, form_values_image, sort_id, status_id from " . TABLE_FORM_VALUES . " where form_fields_id = '" . (int)$fID . "' and form_options_id = '" . (int)$oID . "' order by sort_id");
    while ($form_value = $g_db->fetch_array($form_values_query)) {
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
      echo '                      <tr class="' . $row_class . '">';
?>
              <td><?php echo tep_draw_checkbox_field('mark['.$form_value['form_values_id'].']', $form_value['form_values_id']); ?></td>
              <td><?php echo tep_draw_input_field('name['.$form_value['form_values_id'].']', $form_value['form_values_name'], 'size="30"');?></td>
<?php
    if( $name_array['image_status'] == '1' ) {
      $previous_extra = TEXT_INFO_IMAGE_NOT_SET;
      $delete_link = '';
      if( tep_not_null($form_value['form_values_image']) ) {
        $previous_extra = '<b><font color="#BF0000">' . $form_value['form_values_image'] . '</font></b>';
        $delete_link = '<a href="' . tep_href_link($g_script, 'fID=' . $fID . '&oID=' . $oID . '&action=remove_values_image&pm_id=' . $form_value['form_values_id']) . '" title="Click to remove the image">' . tep_image(DIR_WS_ICONS . 'delete.gif', IMAGE_DELETE . ' ' . $form_value['form_values_image']) . '</a>';
      }
      $old_image = '<br />' . $previous_extra;
      echo '<td>' . tep_draw_file_field('image_'.$form_value['form_values_id']) . '&nbsp;' . $delete_link . $old_image . '</td>' . "\n";
    }
?>
              <td class="calign"><?php echo tep_draw_input_field('order['.$form_value['form_values_id'].']', $form_value['sort_id'], 'size="5"', false, 'text', true);?></td>
              <td class="tinysep calign">
<?php
        if ($form_value['status_id'] == '1') {
          echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag', 'id')) . 'action=set_value_flag&flag=0&id=' . $form_value['form_values_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
        }
        else {
          echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag', 'id')) . 'action=set_value_flag&flag=1&id=' . $form_value['form_values_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
        }
?>
              </td>
            </tr>
<?php
    } 
?>
            <tr>
              <td colspan="8" class="formButtons"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=options_list') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>' . tep_image_submit('button_update.gif', IMAGE_UPDATE, 'class="dflt" name="update_value"') . tep_image_submit('button_delete.gif',IMAGE_DELETE,'class="dflt" name="delete_value"'); ?></td>
            </tr>
          </table></form></div>
          <div class="comboHeading"><h1>
<?php 
    $name_query = $g_db->query("select gf.form_fields_name, go.form_options_name, go.image_status from " . TABLE_FORM_OPTIONS . " go left join " . TABLE_FORM_FIELDS . " gf on (gf.form_fields_id=go.form_fields_id) where gf.form_fields_id = '" . (int)$fID . "' and go.form_options_id = '" . (int)$oID . "'");
    if($name_array = $g_db->fetch_array($name_query) ) {
      echo sprintf(HEADING_TITLE_VALUE, $name_array['form_fields_name'], $name_array['form_options_name']);
    } else {
      echo 'Error';
    }
?>
          </h1></div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_INSERT_VALUE; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form("insert_value", $g_script, tep_get_all_get_params(array('action')) . 'action=insert_value', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_NAME; ?></th>
<?php
    if( $name_array['image_status'] == '1' ) {
      echo '    <th>' . TABLE_HEADING_IMAGE . '</th>' . "\n";
    }
?>
              <th class="calign"><?php echo TABLE_HEADING_ORDER; ?></th>
            </tr>
            <tr>
              <td><?php echo tep_draw_input_field('name', '', 'size="30"'); ?></td>
<?php
    if( $name_array['image_status'] == '1' ) {
      echo '  <td>' . tep_draw_file_field('image') . '</td>' . "\n";
    }
?>
              <td class="calign"><?php echo tep_draw_input_field('order', '', 'size="5"'); ?></td>
            </tr>
            <tr>
              <td colspan="6" class="formButtons"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=options_list') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>' . tep_image_submit('button_insert.gif', IMAGE_INSERT); ?></td>
            </tr>
          </table></form></div>
        </div>
<?php
  } elseif($action == 'delete_value') {
?>
        <div class="maincell wider">
          <div class="comboHeading"><h1><?php echo HEADING_FORM_OPTIONS_DELETE; ?></h1></div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_DELETE_VALUE; ?></div>
          </div>

          <div class="formArea"><?php echo tep_draw_form('delete_value', $g_script, tep_get_all_get_params(array('action')) . 'action=delete_value_confirm', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_NAME; ?></th>
            </tr>
<?php
    $rows = 0;
    foreach( $_POST['mark'] as $key => $value ) {
      $form_values_query = $g_db->query("select form_values_id, form_values_name from " . TABLE_FORM_VALUES . " where form_fields_id = '" . (int)$fID . "' and form_options_id = '" . (int)$oID . "' and form_values_id = '" . (int)$key . "'");
      if( $form_value = $g_db->fetch_array($form_values_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowSelected';
        echo '                      <tr class="' . $row_class . '">';
?>
              <td><?php echo $form_value['form_values_name'] . tep_draw_hidden_field('mark[' . $form_value['form_values_id'] . ']', $form_value['form_values_id']); ?></td>
            </tr>
<?php
      }
    }
?>
            <tr>
              <td class="formButtons">
<?php 
    echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=values_list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM);
?>
              </td>
            </tr>
          </table></form></div>
        </div>
<?php
  }
?>
<?php require('includes/objects/html_end.php'); ?>
