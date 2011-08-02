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
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('cID', 'action') . 'cID=' . $cID));
      break;
    default:
      break;
  }

  $cfg_group_query = $g_db->query("select configuration_group_title from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$gID . "'");
  $cfg_group = $g_db->fetch_array($cfg_group_query);
?>
<?php require(DIR_FS_INCLUDES . 'objects/html_start_sub1.php'); ?>
<?php
  $set_focus = true;
  require(DIR_FS_INCLUDES . 'objects/html_start_sub2.php'); 
  if( empty($cfg_group['configuration_group_title']) ) {
    $heading_title = HEADING_TITLE;
  } else {
    $heading_title = $cfg_group['configuration_group_title'];
  }  
?>
          <div class="maincell">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=' . $gID) . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', BOX_OTHER_QUICK_HELP) . '</a>'; ?></div>
              <div class="floater"><h1><?php echo $heading_title; ?></h1></div>
            </div>
            <div class="formArea"><table class="tabledata">
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

    $cfgValue = tep_cfg_value($configuration);

    if( !empty($cID) && $cID == $configuration['configuration_id'] ) {
      $cfg_extra_query = $g_db->query("select configuration_key, configuration_description, date_added, last_modified, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_id = '" . (int)$configuration['configuration_id'] . "'");
      $cfg_extra = $g_db->fetch_array($cfg_extra_query);

      $cInfo_array = array_merge($configuration, $cfg_extra);
      $cInfo = new objectInfo($cInfo_array, false);
    }

    if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) {
      echo '                  <tr class="dataTableRowSelected row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('cID', 'action') . 'cID=' . $configuration['configuration_id'] . '&action=edit') . '">' . "\n";
    } else {
      echo '                  <tr class="' . $row_class . ' row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('cID', 'action') . 'cID=' . $configuration['configuration_id']) . '">' . "\n";
    }
?>
                <td><?php echo $configuration['configuration_title']; ?></td>
                <td><?php echo $cfgValue; ?></td>
                <td class="tinysep calign">
<?php
    echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('cID', 'action') . 'cID=' . $configuration['configuration_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $configuration['configuration_title']) . '</a>';
    if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) {
      echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', '');
    } else {
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('cID', 'action') . 'cID=' . $configuration['configuration_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>';
    }
?>
                </td>
              </tr>
<?php
  }
?>
            </table></div>
            <div class="listArea splitLine">
              <div class="floater"><?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, min(1, $rows), $rows, $rows); ?></div>
            </div>
          </div>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'edit':
      $heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');
      $value_field = tep_cfg_set($cInfo);

      $contents[] = array('form' => tep_draw_form('configuration', $g_script, 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=save'));
      $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
      $contents[] = array('class' => 'infoBoxSection', 'text' => $cInfo->configuration_description);
      $contents[] = array('text' => TEXT_INFO_CONFIGURATION_KEY . '<br /><span class="required">' . $cInfo->configuration_key . '</span>');
      $contents[] = array('class' => 'rpad', 'section' => '<div>');
      $contents[] = array('text' => '<b>' . $cInfo->configuration_title . ':</b><br />' . $value_field);
      $contents[] = array('section' => '</div>');

      $buttons = array(
        tep_image_submit('button_update.gif', IMAGE_UPDATE),
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('cID', 'action') . 'cID=' . $cInfo->configuration_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'
      );
      $contents[] = array(
        'class' => 'calign', 
        'text' => implode('', $buttons),
      );

      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');
        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('cID', 'action') . 'cID=' . $cInfo->configuration_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>'
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons),
        );

        $contents[] = array('class' => 'infoBoxSection', 'text' => $cInfo->configuration_description);
        $contents[] = array('text' => TEXT_INFO_CONFIGURATION_KEY . '<br /><span class="required">' . $cInfo->configuration_key . '</span>');
        $contents[] = array('text' => TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added));
        if (tep_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified));
      } else {
        $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', TEXT_ERROR) );
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
<?php require(DIR_FS_INCLUDES . 'objects/html_end.php'); ?>
