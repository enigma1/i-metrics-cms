<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Total Configuration module
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

  $action = (isset($_GET['action']) ? $g_db->prepare_input($_GET['action']) : '');
  $gID = (isset($_GET['gID']) ? (int)$_GET['gID'] : '');
  $newID = (isset($_GET['newID']) ? (int)$_GET['newID'] : '');

  switch($action) {
    case 'modify_confirm':
      if( $_POST['sort_duplicates'] == 'on' ) {
        $duplicates_query = $g_db->query("select configuration_key, configuration_id, count(*) as total from " . TABLE_CONFIGURATION . " group by configuration_key having count(*) > 1");
        $duplicates_array = array();
        while($duplicates = $g_db->fetch_array($duplicates_query) ) {
          $duplicates_array[$duplicates['configuration_key']] = $duplicates;
        }

        foreach($duplicates_array as $key => $value) {
          $g_db->query("delete from " . TABLE_CONFIGURATION . " where configuration_id = '" . (int)$value['configuration_id'] ."'");
        }
      }
      if($_POST['sort_config'] == 'on') {
        $g_db->query("alter table " . TABLE_CONFIGURATION . " drop configuration_id");
        $g_db->query("alter table " . TABLE_CONFIGURATION . " add configuration_id INT( 11 ) not null auto_increment first, add primary key (configuration_id)");
      }
      tep_redirect(tep_href_link(basename($PHP_SELF)));
      break;
    case 'modify':
      if( $_POST['sort_duplicates'] == '0' && $_POST['sort_config'] == '0' ) {
        $action = '';
      }
      break;
    case 'update_switch_confirm':
      $cID = (isset($_GET['cID']) ? (int)$_GET['cID'] : '');
      $check_query = $g_db->query("select count(*) as total from " . TABLE_CONFIGURATION . " where configuration_id = '" . (int)$cID . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( !$check_array['total'] ) {
        $messageStack->add_session(ERROR_CFG_ID_INVALID);
        tep_redirect(tep_href_link(basename($PHP_SELF)));
        break;
      }
    case 'insert_switch_confirm':
      $error = false;
      $custom_group_id = (int)$_POST['custom_group_id'];
      $configuration_group_id = (int)$_POST['configuration_group_id'];

      if( $custom_group_id > 0 ) {
        $configuration_group_id = $custom_group_id;
      }

      $check_query = $g_db->query("select count(*) as total from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$configuration_group_id . "' or configuration_group_id = '" . (int)$custom_group_id . "' ");
      $check_array = $g_db->fetch_array($check_query);
      if( !$custom_group_id && !$check_array['total'] ) {
        $messageStack->add(ERROR_CFG_GROUP_INVALID);
        $error = true;
      }

      $set_function = $g_db->prepare_input($_POST['set_function']);
      $pos = strpos($set_function,'(');
      if( $pos && !function_exists(substr($set_function, 0, $pos)) ) {
        $messageStack->add(ERROR_CFG_SET_FUNCTION_INVALID);
        $error = true;
      } elseif( !$pos && !empty($set_function) ) {
        $messageStack->add(ERROR_CFG_SET_FUNCTION_INVALID);
        $error = true;
      } elseif( strlen($set_function) > 255 ) {
        $messageStack->add(ERROR_CFG_FUNCTION_LENGTH);
        $error = true;
      } else {
        $set_function = trim($set_function);
      }

      $use_function = $g_db->prepare_input($_POST['use_function']);
      if( !empty($use_function) && !function_exists(substr($use_function, 0, $pos)) ) {
        $messageStack->add(ERROR_CFG_USE_FUNCTION_INVALID);
        $error = true;
      } elseif( strlen($use_function) > 255 ) {
        $messageStack->add(ERROR_CFG_FUNCTION_LENGTH);
        $error = true;
      } else {
        $use_function = trim($use_function);
      }

      $configuration_title = $g_db->prepare_input($_POST['configuration_title']);
      if( empty($configuration_title) ) {
        $messageStack->add(ERROR_CFG_TITLE_EMPTY);
        $error = true;
      }

      $configuration_key =  $g_db->prepare_input($_POST['configuration_key']);
      if( empty($configuration_key) ) {
        $messageStack->add(ERROR_CFG_KEY_EMPTY);
        $error = true;
      } elseif( strtoupper($configuration_key) != $configuration_key) {
        $messageStack->add(ERROR_CFG_KEY_INVALID);
        $error = true;
      }

      $configuration_description = $g_db->prepare_input($_POST['configuration_description']);
      if( empty($configuration_description) ) {
        $messageStack->add(ERROR_CFG_DESCRIPTION_EMPTY);
        $error = true;
      }

      $configuration_value =  $g_db->prepare_input($_POST['configuration_value']);

      if( $action == 'insert_switch_confirm' ) {
        $check_query = $g_db->query("select count(*) as total from " . TABLE_CONFIGURATION . " where configuration_key = '" . $g_db->input($configuration_key) . "'");
        $check_array = $g_db->fetch_array($check_query);
        if( $check_array['total'] ) {
          $messageStack->add(ERROR_CFG_KEY_EXISTS);
          $error = true;
        }
      }

      if( $error ) {
        if( $action == 'insert_switch_confirm' ) {
          $action = 'insert';
          $newID = 1;
        } else {
          $action = 'edit';
        }
        break;
      }

      if( $custom_group_id ) {
        $configuration_group_id = $custom_group_id;
      }

      $sql_data_array = array('configuration_title' => $configuration_title,
                              'configuration_description' => $configuration_description,
                              'configuration_group_id' => $configuration_group_id,
                              'configuration_key' => $configuration_key,
                              'configuration_value' => $configuration_value,
                              'use_function' => $use_function,
                              'set_function' => $set_function,
                              'sort_order' => (int)$_POST['sort_order'],
                              'date_added' => 'now()',
                              'last_modified' => 'now()',
                             );

      if( $action == 'insert_switch_confirm' ) {
        $g_db->perform(TABLE_CONFIGURATION, $sql_data_array);
        $cID = $g_db->insert_id();
        $messageStack->add_session(SUCCESS_CFG_SWITCH_CREATED, 'success');
      } else {
        $g_db->perform(TABLE_CONFIGURATION, $sql_data_array, 'update', "configuration_id = '" . (int)$cID . "'");
        $messageStack->add_session(SUCCESS_CFG_SWITCH_UPDATED, 'success');
      }
      tep_redirect(tep_href_link(basename($PHP_SELF), 'gID=' . $configuration_group_id . '&cID=' . $cID));
      break;

    case 'update_group_confirm':
      $configuration_group_id = (int)$_POST['configuration_group_id'];
    case 'insert_group_confirm':
      $error = false;

      $visible = isset($_POST['visible'])?1:0;

      $configuration_group_title = $g_db->prepare_input($_POST['configuration_group_title']);
      $check_query = $g_db->query("select count(*) as total from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_title = '" . $g_db->input($configuration_group_title) . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( $action == 'insert_group_confirm' && $check_array['total'] ) {
        $messageStack->add(ERROR_GROUP_EXISTS);
        $error = true;
      } elseif( $visible && empty($configuration_group_title) ) {
        $messageStack->add(ERROR_GROUP_TITLE_EMPTY);
        $error = true;
      }

      $configuration_group_description = $g_db->prepare_input($_POST['configuration_group_description']);
      if( $visible && empty($configuration_group_description) ) {
        $messageStack->add(ERROR_GROUP_DESCRIPTION_EMPTY);
        $error = true;
      }

      if( $error ) {
        if( $action == 'insert_group_confirm') {
          $action = 'insert';
          $newID = 2;
        } else {
          $action = 'edit_group';
        }
        break;
      }

      $sql_data_array = array('configuration_group_title' => $configuration_group_title,
                              'configuration_group_description' => $configuration_group_description,
                              'sort_order' => (int)$_POST['sort_order'],
                              'visible' => (int)$visible,
                             );

      if( $action == 'insert_group_confirm') {
        $g_db->perform(TABLE_CONFIGURATION_GROUP, $sql_data_array);
        $configuration_group_id = $g_db->insert_id();
        $messageStack->add_session(SUCCESS_GROUP_CREATED, 'success');
      } else {

        if( !$visible && empty($configuration_group_title) ) {
          $g_db->query("delete from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$configuration_group_id ."'");
        }

        $check_query = $g_db->query("select count(*) as total from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$configuration_group_id . "'");
        $check_array = $g_db->fetch_array($check_query);
        if( $check_array['total'] || !$visible ) {
          $g_db->perform(TABLE_CONFIGURATION_GROUP, $sql_data_array, 'update', "configuration_group_id = '" . (int)$gID . "'");
        } else {
          $sql_data_array['configuration_group_id'] = (int)$configuration_group_id;
          $g_db->perform(TABLE_CONFIGURATION_GROUP, $sql_data_array);
          $configuration_group_id = $g_db->insert_id();
        }
        $messageStack->add_session(SUCCESS_GROUP_UPDATED, 'success');
      }
      tep_redirect(tep_href_link(basename($PHP_SELF), 'gID=' . $configuration_group_id));

      break;

    case 'delete_confirm':
      $cID = (isset($_GET['cID']) ? $_GET['cID'] : '');
      $g_db->query("delete from " . TABLE_CONFIGURATION . " where configuration_id = '" . (int)$cID ."'");
      tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'cID'))));
      break;
    case 'delete_group_confirm':
      $whole = isset($_POST['whole'])?1:0;
      if( $whole ) {
        $g_db->query("delete from " . TABLE_CONFIGURATION . " where configuration_group_id = '" . (int)$gID ."'");
      }
      $g_db->query("delete from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$gID ."'");
      $messageStack->add_session(WARNING_GROUP_DELETED, 'warning');
      tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'gID', 'cID'))));
      break;

    default:
      break;
  }

  $cfg_group_query = $g_db->query("select distinct configuration_group_id as id, CONCAT('Group','-',configuration_group_id) as text from " . TABLE_CONFIGURATION . " order by configuration_group_id");
  $group_array = array(
                       array('id' => '0', 'text' => 'Show All')
                      );

  //while($group_array[]=$g_db->fetch_array($cfg_group_query));
  //array_pop($group_array);

  while( $group = $g_db->fetch_array($cfg_group_query)) {
    $group_array[$group['id']] = $group;
  }

  $cfg_group_query = $g_db->query("select configuration_group_id as id, CONCAT('Group','-',configuration_group_id) as text from " . TABLE_CONFIGURATION_GROUP . "");
  while( $group = $g_db->fetch_array($cfg_group_query)) {
    $group_array[$group['id']] = $group;
  }

  $group_name = '';
  if( $gID != 0 ) {
    $cfg_group_query = $g_db->query("select configuration_group_title from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$gID . "'");
    if( $cfg_group = $g_db->fetch_array($cfg_group_query) ) {
      $group_name = $cfg_group['configuration_group_title'];
    } else {
      $group_name = 'Unnamed Group-ID=' . $gID;
    }
  }
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php 
  $set_focus = true;
  require('includes/objects/html_start_sub2.php'); 
  if( $action == 'insert') {
?>
          <div class="maincell" style="width: 100%;">
            <div class="comboHeading">
              <div class="pageHeading"><h1><?php echo HEADING_INSERT; ?></h1></div>
            </div>
<?php
    if( $newID == 1 ) {
    $group_array[0]['text'] = 'Custom';
?>
            <div class="comboHeading">
              <div class="smallText"><?php echo TEXT_INFO_INSERT_SWITCH; ?></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form('insert_form', basename($PHP_SELF), tep_get_all_get_params(array('action', 'gID')) . 'action=insert_switch_confirm') ?><table border="0" width="100%" cellspacing="1" cellpadding="3">
              <tr>
                <td class="main"><table border="0" cellspacing="0" cellpadding="2">
                  <tr>
                    <td><?php echo TEXT_INFO_SELECT_GROUP; ?></td>
                    <td><?php echo tep_draw_pull_down_menu('configuration_group_id', $group_array, $gID); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo TEXT_INFO_CFG_CUSTOM; ?></td>
                    <td><?php echo tep_draw_input_field('custom_group_id', ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo TEXT_INFO_CFG_DESCRIPTION; ?></td>
                    <td><?php echo tep_draw_textarea_field('configuration_description', 'soft', 20, 5); ?></td>
                  </tr>
                </table></td>
                <td><table border="0" cellspacing="0" cellpadding="2">
                  <tr>
                    <td><?php echo TEXT_INFO_CFG_TITLE; ?></td>
                    <td><?php echo tep_draw_input_field('configuration_title', ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo TEXT_INFO_CFG_KEY; ?></td>
                    <td><?php echo tep_draw_input_field('configuration_key', ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo TEXT_INFO_CFG_VALUE; ?></td>
                    <td><?php echo tep_draw_input_field('configuration_value', ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo TEXT_INFO_CFG_USE; ?></td>
                    <td><?php echo tep_draw_input_field('use_function', ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo TEXT_INFO_CFG_SET; ?></td>
                    <td><?php echo tep_draw_input_field('set_function', ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo TEXT_INFO_CFG_SORT; ?></td>
                    <td><?php echo tep_draw_input_field('sort_order', ''); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td colspan="2" class="formButtons"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF)) .'">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_confirm.gif', TEXT_INFO_CONFIRM_CONFIG_INSERT); ?></td>
              </tr>
            </table></form></div>
<?php
    } elseif($newID == 2 ) {
?>
            <div class="comboHeading">
              <div><?php echo TEXT_INFO_INSERT_GROUP; ?></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form('insert_form', basename($PHP_SELF), tep_get_all_get_params(array('action', 'gID')) . 'action=insert_group_confirm') ?><table border="0" width="100%" cellspacing="1" cellpadding="3">
              <tr>
                <td><table border="0" cellspacing="0" cellpadding="2">
                  <tr>
                    <td><?php echo TEXT_INFO_GROUP_DESCRIPTION; ?></td>
                    <td><?php echo tep_draw_textarea_field('configuration_group_description', 'soft', 20, 3); ?></td>
                  </tr>
                </table></td>
                <td><table border="0" cellspacing="0" cellpadding="2">
                  <tr>
                    <td><?php echo TEXT_INFO_GROUP_TITLE; ?></td>
                    <td><?php echo tep_draw_input_field('configuration_group_title', ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo TEXT_INFO_GROUP_SORT; ?></td>
                    <td><?php echo tep_draw_input_field('sort_order', ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo TEXT_INFO_GROUP_VISIBLE; ?></td>
                    <td><?php echo tep_draw_checkbox_field('visible', false, false); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td colspan="2" class="formButtons"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF)) .'">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_confirm.gif', TEXT_INFO_CONFIRM_GROUP_INSERT); ?></td>
              </tr>
            </table></form></div>
<?php
    }
?>
          </div>
<?php
  // Modify/Confirm screen
  } elseif($action == 'modify' && $gID == 0) {
?>
          <div class="maincell" style="width: 100%;">
            <div class="comboHeading">
              <div class="pageHeading"><h1><?php echo HEADING_CONFIRM; ?></h1></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form('global_form', basename($PHP_SELF), tep_get_all_get_params(array('action', 'gID')) . 'action=modify_confirm', 'post') ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php
    if($_POST['sort_duplicates'] == 'on') {
?>
              <tr>
                <td><?php echo TEXT_INFO_CONFIRM_DUPLICATES; ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '8'); ?></td>
              </tr>
              <tr>
                <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_ID; ?></td>
                    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_KEY; ?></td>
                  </tr>
<?php
      $duplicates_query = $g_db->query("select configuration_key, configuration_id, count(*) as total from " . TABLE_CONFIGURATION . " group by configuration_key having count(*) > 1");
      $duplicates_array = array();

      while($duplicates = $g_db->fetch_array($duplicates_query) ) {
        $duplicates_array[$duplicates['configuration_key']] = $duplicates;
      }
      foreach($duplicates_array as $key => $value) {
?>
                  <tr class="dataTableRow">
                    <td class="dataTableContent"><?php echo $value['configuration_id']; ?></td>
                    <td class="dataTableContent"><?php echo $value['configuration_key'];; ?></td>
                  </tr>
<?php
      }
?>
                </table></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '8'); ?></td>
              </tr>
<?php 
    }
    if($_POST['sort_config'] == 'on') {
?>
              <tr>
                <td class="smallText"><b><?php echo TEXT_INFO_CONFIRM_CONFIG; ?></b></td>
              </tr>
<?php
    }
    foreach( $_POST as $key => $value ) {
      echo tep_draw_hidden_field($key, $value);
    }
?>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '6'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo tep_image_submit('button_confirm.gif', TEXT_INFO_CONFIRM_MYSQL) . ' <a href="' . tep_href_link(basename($PHP_SELF)) .'">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
              </tr>
            </table></form></div>
          </div>
<?php
  // Show them all
  } elseif($gID == 0) {
?>
          <div class="maincell" style="width: 100%;">
            <div class="comboHeading">
              <div class="pageHeading"><h1><?php echo HEADING_ALL; ?></h1></div>
            </div>
            <div class="comboHeading">
              <div class="floater">
<?php
    echo tep_draw_form('group', basename($PHP_SELF), '', 'get');
    echo TEXT_INFO_SELECT_GROUP . '&nbsp;' . tep_draw_pull_down_menu('gID', $group_array, $gID, 'onchange="this.form.submit();"');
    echo '</form>';
?>
              </div>
            </div>
<?php
    $insert_array = array(
                          array('id' => 1, 'text' => 'Configuration Switch'),
                          array('id' => 2, 'text' => 'Configuration Group'),
                         );
?>
            <div class="comboHeading"><?php echo tep_draw_form('insertid', basename($PHP_SELF), '', 'get'); ?><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td><?php echo TEXT_INFO_INSERT_ENTRY . '&nbsp;' . tep_draw_pull_down_menu('newID', $insert_array) . '&nbsp;'; ?></td>
                <td><?php echo tep_draw_hidden_field('action', 'insert') . tep_image_submit('button_insert.gif', 'Process Global Options'); ?></td>
              </tr>
            </table></form></div>
            <div class="comboHeading">
              <div class="smallText"><b><?php echo TABLE_HEADING_OPTIMIZE; ?></b></div>
              <div class="smallText"><?php echo TEXT_INFO_OPERATION ?></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form('global_form', basename($PHP_SELF), 'action=modify', 'post') ?><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td><table border="0" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText">&nbsp;<?php echo 'No Changes&nbsp;' . tep_draw_radio_field('sort_config', '0', true); ?></td>
                    <td class="smallText"><?php echo 'Enable&nbsp;' . tep_draw_radio_field('sort_config', 'on', false); ?></td>
                    <td class="smallText"><b><?php echo TEXT_INFO_OPTIMIZE_SORT; ?></b></td>
                  </tr>
                  <tr>
                    <td class="smallText">&nbsp;<?php echo 'No Changes&nbsp;' . tep_draw_radio_field('sort_duplicates', '0', true); ?></td>
                    <td class="smallText"><?php echo 'Enable&nbsp;' . tep_draw_radio_field('sort_duplicates', 'on', false); ?></td>
                    <td class="smallText"><b><?php echo TEXT_INFO_OPTIMIZE_DUPLICATES; ?></b></td>
                  </tr>
                </table></td>
                <td class="smallText"><?php echo tep_image_submit('button_submit.gif', 'Process Global Options'); ?></td>
              </tr>
            </table></form></div>
<?php
    unset($group_array[0]);

    foreach($group_array as $key => $value) {
      $cfg_group_query = $g_db->query("select configuration_group_title from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$value['id'] . "'");
      if( $g_db->num_rows($cfg_group_query) ) {
        $cfg_group = $g_db->fetch_array($cfg_group_query);
        $group_name = $cfg_group['configuration_group_title'];
      } else {
        $group_name = 'Unnamed';
      }
?>
            <div class="comboHeading">
              <div class="pageHeading"><h1><?php echo $value['id'] . '.&nbsp;' . $group_name; ?></h1></div>
            </div>
            <div class="listArea"><table border="0" width="100%" cellspacing="1" cellpadding="3">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_KEY; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ACTION; ?></td>
              </tr>
<?php
      $configuration_query = $g_db->query("select c.configuration_id, c.configuration_group_id, c.configuration_key, c.configuration_title, c.configuration_value, c.use_function from " . TABLE_CONFIGURATION . " c where c.configuration_group_id = '" . (int)$value['id'] . "' order by c.configuration_key");
      while ($configuration = $g_db->fetch_array($configuration_query)) {
        if (tep_not_null($configuration['use_function'])) {
          $use_function_check = $configuration['use_function'];
          if (ereg('->', $use_function_check)) {
            $class_method = explode('->', $use_function_check);
            if (!is_object(${$class_method[0]})) {
              include(DIR_WS_CLASSES . $class_method[0] . '.php');
              ${$class_method[0]} = new $class_method[0]();
            }
            $cfgValue = tep_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
          } else {
            $cfgValue = tep_call_function($use_function_check, $configuration['configuration_value']);
          }
        } else {
          $cfgValue = $configuration['configuration_value'];
        }

        if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $configuration['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
          $cfg_extra_query = $g_db->query("select configuration_key, configuration_description, date_added, last_modified, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_id = '" . (int)$configuration['configuration_id'] . "'");
          $cfg_extra = $g_db->fetch_array($cfg_extra_query);

          $cInfo_array = array_merge($configuration, $cfg_extra);
          $cInfo = new objectInfo($cInfo_array, false);
        }

        if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) {
          echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), 'gID=' . $configuration['configuration_group_id'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'">' . "\n";
        } else {
          echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), 'gID=' . $configuration['configuration_group_id'] . '&cID=' . $configuration['configuration_id']) . '\'">' . "\n";
        }
?>
                <td class="dataTableContent"><?php echo $configuration['configuration_id']; ?></td>
                <td class="dataTableContent"><?php echo $configuration['configuration_key']; ?></td>
                <td class="dataTableContent"><?php echo $configuration['configuration_title']; ?></td>
                <td class="dataTableContent"><?php echo $cfgValue; ?></td>
                <td class="dataTableContent" align="center">
<?php 
        if( isset($cInfo) && is_object($cInfo) && $configuration['configuration_id'] == $cInfo->configuration_id ) { 
          echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', IMAGE_SELECT); 
        } else { 
          echo '<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $configuration['configuration_group_id'] . '&cID=' . $configuration['configuration_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', IMAGE_ICON_INFO) . '</a>';
        } 
?>
                </td>
              </tr>
<?php
      }
?>
            </table></div>
<?php
    }
?>
          </div>
<?php
  // Show selected group
  } else {
?>
          <div class="maincell">
            <div class="comboHeading">
              <div class="pageHeading"><h1><?php echo HEADING_TITLE . '&nbsp;&raquo;&nbsp;' . $group_name; ?></h1></div>
            </div>
            <div class="comboHeading">
              <div class="smallText" style="float: left;">
<?php
    echo tep_draw_form('group', basename($PHP_SELF), '', 'get');
    echo TEXT_INFO_SELECT_GROUP . '&nbsp;' . tep_draw_pull_down_menu('gID', $group_array, $gID, 'onchange="this.form.submit();"');
    echo '</form>';
?>
              </div>
              <div style="float: left; padding-left: 10px;"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=delete_group') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', IMAGE_DELETE . '&nbsp;' . $group_name) . '</a>'; ?></div>
              <div style="float: left; padding-left: 10px;"><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=edit_group') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', IMAGE_EDIT . '&nbsp;' . $group_name) . '</a>'; ?></div>
            </div>
            <div class="listArea"><table border="0" width="100%" cellspacing="1" cellpadding="3">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_KEY; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ACTION; ?></td>
              </tr>
<?php
    $configuration_query = $g_db->query("select configuration_id, configuration_key, configuration_title, configuration_value, use_function from " . TABLE_CONFIGURATION . " c where c.configuration_group_id = '" . (int)$gID . "' order by configuration_key");
    while ($configuration = $g_db->fetch_array($configuration_query)) {
      if (tep_not_null($configuration['use_function'])) {
        $use_function_check = $configuration['use_function'];
        if (ereg('->', $use_function_check)) {
          $class_method = explode('->', $use_function_check);
          if (!is_object(${$class_method[0]})) {
            include(DIR_WS_CLASSES . $class_method[0] . '.php');
            ${$class_method[0]} = new $class_method[0]();
          }
          $cfgValue = tep_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
        } else {
          $cfgValue = tep_call_function($use_function_check, $configuration['configuration_value']);
        }
      } else {
        $cfgValue = $configuration['configuration_value'];
      }

      if( (!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $configuration['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new') ) {
        $cfg_extra_query = $g_db->query("select configuration_description, date_added, last_modified, set_function, sort_order from " . TABLE_CONFIGURATION . " where configuration_id = '" . (int)$configuration['configuration_id'] . "'");
        $cfg_extra = $g_db->fetch_array($cfg_extra_query);

        $cInfo_array = array_merge($configuration, $cfg_extra);
        $cInfo = new objectInfo($cInfo_array, false);
        unset($cInfo_array, $cfg_extra);
      }

      if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $configuration['configuration_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo $configuration['configuration_id']; ?></td>
                <td class="dataTableContent"><?php echo $configuration['configuration_key']; ?></td>
                <td class="dataTableContent"><?php echo $configuration['configuration_title']; ?></td>
                <td class="dataTableContent"><?php echo $cfgValue; ?></td>
                <td class="dataTableContent" align="center">
<?php 
      echo '<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $configuration['configuration_id'] . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $configuration['configuration_title']) . '</a>&nbsp;';
      echo '<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $configuration['configuration_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $configuration['configuration_title']) . '</a>&nbsp;';
      if( isset($cInfo) && is_object($cInfo) && $configuration['configuration_id'] == $cInfo->configuration_id ) { 
        echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', ''); 
      } else { 
        echo '<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $configuration['configuration_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>';
      } 
?>
                </td>
              </tr>
<?php
    }
?>
            </table></div>
          </div>
<?php
    $heading = array();
    $contents = array();

    switch ($action) {
      case 'edit':
        if (isset($cInfo) && is_object($cInfo)) {
          $heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');
          $contents[] = array('form' => tep_draw_form('configuration', basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=update_switch_confirm'));
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
          $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
          $contents[] = array('text' => TEXT_INFO_CFG_TITLE . '<br />' . tep_draw_input_field('configuration_title', $cInfo->configuration_title) );
          $contents[] = array('text' => TEXT_INFO_CFG_KEY . '<br />' . tep_draw_input_field('configuration_key', $cInfo->configuration_key) );
          $contents[] = array('text' => TEXT_INFO_CFG_VALUE . '<br />' . tep_draw_textarea_field('configuration_value', 'soft', '', 2, $cInfo->configuration_value) );

          $contents[] = array('text' => TEXT_INFO_CFG_USE . '<br />' . tep_draw_input_field('use_function', $cInfo->use_function) );
          $contents[] = array('text' => TEXT_INFO_CFG_SET . '<br />' . tep_draw_input_field('set_function', $cInfo->set_function) );

          $contents[] = array('text' => TEXT_INFO_SELECT_GROUP . '<br />' . tep_draw_pull_down_menu('configuration_group_id', $group_array, $gID) );
          $contents[] = array('text' => TEXT_INFO_CFG_CUSTOM . '<br />' . tep_draw_input_field('custom_group_id', $gID) );
          $contents[] = array('text' => TEXT_INFO_CFG_DESCRIPTION . '<br />' . tep_draw_textarea_field('configuration_description', 'soft', '', 3, $cInfo->configuration_description) );
          $contents[] = array('text' => TEXT_INFO_CFG_SORT . '<br />' . tep_draw_input_field('sort_order', $cInfo->sort_order) );
          $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cInfo->configuration_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        } else { // create generic_text dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', TEXT_ERROR) );
          $contents[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
      case 'delete':
        if (isset($cInfo) && is_object($cInfo)) {
          $heading[] = array('text' => '<b>' . TEXT_DELETE . '&nbsp;' . $cInfo->configuration_title . '</b>');
          $contents[] = array('form' => tep_draw_form('configuration', basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=delete_confirm'));
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
          $contents[] = array('text' => sprintf(TEXT_INFO_DELETE_INTRO, '<b>' . $cInfo->configuration_title . '</b>'));
          $contents[] = array('text' => TEXT_INFO_DELETE_GROUP_FINAL);
          $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cInfo->configuration_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        } else { // create generic_text dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', TEXT_ERROR) );
          $contents[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
      case 'edit_group':
        $cID = (isset($_GET['cID']) ? $_GET['cID'] : '');
        $group_query = $g_db->query("select configuration_group_title, configuration_group_description, sort_order, visible from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$gID . "'");
        $group_array = $g_db->fetch_array($group_query);

        $heading[] = array('text' => '<b>' . TEXT_EDIT_GROUP . '&nbsp;' . $group_name . '</b>');
        $contents[] = array('form' => tep_draw_form('configuration_group', basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cID . '&action=update_group_confirm'));
        $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );

        $contents[] = array('text' => TEXT_INFO_EDIT_GROUP_INTRO);
        $contents[] = array('text' => TEXT_INFO_GROUP_TITLE . '<br />' . tep_draw_input_field('configuration_group_title', $group_array['configuration_group_title']) );
        $contents[] = array('text' => TEXT_INFO_GROUP_DESCRIPTION . '<br />' . tep_draw_textarea_field('configuration_group_description', 'soft', '', 3, $group_array['configuration_group_description']) );

        $contents[] = array('text' => TEXT_INFO_GROUP_ID . '<br />' . tep_draw_input_field('configuration_group_id', $gID) );
        $contents[] = array('text' => TEXT_INFO_GROUP_SORT . '<br />' . tep_draw_input_field('sort_order', $group_array['sort_order']) );
        $contents[] = array('text' => tep_draw_checkbox_field('visible', $group_array['visible'], ($group_array['configuration_group_title'])?true:false) . '&nbsp;' . TEXT_INFO_GROUP_VISIBLE);
        $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete_group':
        $cID = (isset($_GET['cID']) ? $_GET['cID'] : '');
        $heading[] = array('text' => '<b>' . TEXT_DELETE . '&nbsp;' . $group_name . '</b>');
        $contents[] = array('form' => tep_draw_form('configuration', basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cID . '&action=delete_group_confirm'));
        $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
        $contents[] = array('text' => sprintf(TEXT_INFO_DELETE_GROUP_INTRO, '<b>' . $group_name . '</b>'));
        $contents[] = array('text' => TEXT_INFO_DELETE_GROUP_FINAL);
        $contents[] = array('text' => tep_draw_checkbox_field('whole', false, false) . '&nbsp;' . TEXT_INFO_GROUP_INCLUDE_SWITCHES);
        $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if (isset($cInfo) && is_object($cInfo)) {
          $heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');

          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>&nbsp;<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
          $contents[] = array('text' => $cInfo->configuration_description);
          $contents[] = array('text' => TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added));
          if (tep_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified));
        } else { // create generic_text dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', TEXT_ERROR) );
          $contents[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
    }

    if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
      echo '             <div class="rightcell">';
      $box = new box;
      echo $box->infoBox($heading, $contents);
      echo '             </div>';
    }
  }
?>
<?php require('includes/objects/html_end.php'); ?>
