<?php
/*
  $Id: configuration.php,v 1.43 2003/06/29 22:50:51 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Common Configuration Script
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - Moved common configuration code into a common script
// - Replaced Database functions with Database Class
// - Added action controls to edit the configuration options for each row
// - PHP5 Register Globals off and Long Arrays Off support added
// - HTML Outer tables replaced with CSS driven divs
// - Common configuration code, moved from the main configuration file
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  $action = (isset($_GET['action']) ? $g_db->prepare_input($_GET['action']) : '');
  $cID = (isset($_GET['cID']) ? (int)$_GET['cID']:'');
  if( empty($gID) ) {
    $gID = (isset($_GET['gID']) ? (int)$_GET['gID'] : '');
  }

  if( empty($gID) ) {
    $check_query = $g_db->query("select configuration_group_id from " . TABLE_CONFIGURATION . " where configuration_id = '" . (int)$cID . "'");
    if( !$g_db->num_rows($check_query) ) {
      $gID = 1;
    } else {
      $check_array = $g_db->fetch_array($check_query);
      $gID = $check_array['configuration_group_id'];
    }
  }

  switch ($action) {
    case 'save':
      $configuration_value = $g_db->prepare_input($_POST['configuration_value']);
      $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $g_db->input($configuration_value) . "', last_modified = now() where configuration_id = '" . (int)$cID . "'");
      tep_redirect(tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cID));
      break;
    default:
      break;
  }

  $cfg_group_query = $g_db->query("select configuration_group_title from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$gID . "'");
  $cfg_group = $g_db->fetch_array($cfg_group_query);
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php
  $set_focus = true;
  require('includes/objects/html_start_sub2.php'); 
  if( empty($cfg_group['configuration_group_title']) ) {
    $heading_title = HEADING_TITLE;
  } else {
    $heading_title = $cfg_group['configuration_group_title'];
  }
?>
          <div class="maincell">
            <div class="comboHeading">
              <div class="pageHeading"><h1><?php echo $heading_title; ?></h1></div>
            </div>
            <div class="listArea"><table class="tabledata" cellspacing="1">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></th>
                <th><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
<?php
  $rows = 0;
  $configuration_query = $g_db->query("select configuration_id, configuration_title, configuration_value, use_function from " . TABLE_CONFIGURATION . " where configuration_group_id = '" . (int)$gID . "' order by sort_order");
  while ($configuration = $g_db->fetch_array($configuration_query)) {
    $rows++;
    $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

    if( !empty($configuration['use_function']) ) {
      $use_function = $configuration['use_function'];
      if( ereg('->', $use_function) ) {
        $class_method = explode('->', $use_function);
        if( !is_object(${$class_method[0]}) ) {
          include(DIR_WS_CLASSES . $class_method[0] . '.php');
          ${$class_method[0]} = new $class_method[0]();
        }
        $cfgValue = tep_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
      } else {
        $cfgValue = tep_call_function($use_function, $configuration['configuration_value']);
      }
    } else {
      $cfgValue = $configuration['configuration_value'];
    }

    if( !empty($cID) && $cID == $configuration['configuration_id'] ) {
      $cfg_extra_query = $g_db->query("select configuration_key, configuration_description, date_added, last_modified, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_id = '" . (int)$configuration['configuration_id'] . "'");
      $cfg_extra = $g_db->fetch_array($cfg_extra_query);

      $cInfo_array = array_merge($configuration, $cfg_extra);
      $cInfo = new objectInfo($cInfo_array, false);
    }

    if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) {
      echo '                  <tr class="dataTableRowSelected" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="' . $row_class . '" onclick="document.location.href=\'' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $configuration['configuration_id']) . '\'">' . "\n";
    }
?>
                <td><?php echo $configuration['configuration_title']; ?></td>
                <td><?php echo $cfgValue; ?></td>
                <td class="tinysep calign">
<?php
    echo '<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $configuration['configuration_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $configuration['configuration_title']) . '</a>';
    if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) {
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
      $heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');

      if ($cInfo->set_function) {
        eval('$value_field = ' . $cInfo->set_function . '"' . $cInfo->configuration_value . '");');
      } else {
        $value_field = tep_draw_input_field('configuration_value', $cInfo->configuration_value);
      }

      $contents[] = array('form' => tep_draw_form('configuration', basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=save'));
      $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
      $contents[] = array('text' => $cInfo->configuration_description . '<br />' . $value_field);
      $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_update.gif', IMAGE_UPDATE) . '<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cInfo->configuration_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(basename($PHP_SELF), 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
        $contents[] = array('text' => $cInfo->configuration_description);
        $contents[] = array('text' => TEXT_INFO_CONFIGURATION_KEY . '<br />' . $cInfo->configuration_key);
        $contents[] = array('text' => TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added));
        if (tep_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified));
      } else { // create generic_text dummy info
        $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
        $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', TEXT_ERROR) );
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
