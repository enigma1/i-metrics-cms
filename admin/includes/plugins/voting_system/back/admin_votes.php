<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Voting system for all content types and text pages
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
  $vtID = (isset($_GET['vtID']) ? (int)$_GET['vtID'] : '');

  $s_sort_id = (isset($_GET['s_sort_id']) ? (int)$_GET['s_sort_id'] : '');

  switch( $action ) {
    case 'delete_all_confirm':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(ERROR_NOTHING_SELECTED);
        tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) ));
      }
      foreach ($_POST['mark'] as $key=>$val) {
        $g_db->query("delete from " . TABLE_VOTES . " where auto_id = '" . (int)$key . "'");
      }
      $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
      tep_redirect(tep_href_link(basename($PHP_SELF)));
      break;
    case 'delete_confirm':
      if( isset($_POST['auto_id']) && !empty($_POST['auto_id']) ) {
        $auto_id = (int)$_POST['auto_id'];
        $g_db->query("delete from " . TABLE_VOTES . " where auto_id = '" . (int)$auto_id . "'");
        $messageStack->add_session(WARNING_ENTRY_REMOVED, 'warning');
      }
      tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'vtID'))));
      break;

    default:
      break;
  }
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php require('includes/objects/html_start_sub2.php'); ?>
        <div class="maincell">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
<?php
    $generic_count = 0;
    $rows = 0;

    $sort_by = '';
    $sortIP = 4;
    $sortDate = 3;
    $sortRate = 5;
    switch( $s_sort_id) {
      case 1;
        $sort_by = "ip_address";
        break;
      case 2;
        $sortIP = 1;
        $sort_by = "ip_address desc";
        break;
      case 3;
        $sortDate = 4;
        $sort_by = "date_added";
        break;
      case 4;
        $sort_by = "date_added desc";
        break;
      case 5;
        $sortRate = 6;
        $sort_by = "rating";
        break;
      case 6;
        $sort_by = "rating desc";
        break;
      default:
        $sort_by = "auto_id desc";
        break;
    }

    if( !empty($filter_string) ) {
      $filter_string = "where " . $filter_string;
    }
    $sort_by = "order by " . $sort_by;
    $votes_query_raw = "select auto_id, votes_id, votes_type, rating, resolution, ip_address, date_added from " . TABLE_VOTES . " "  . $sort_by . "";

    $votes_split = new splitPageResults($votes_query_raw, GTEXT_PAGE_SPLIT);
    $votes_query = $g_db->query($votes_split->sql_query);
    if( $g_db->num_rows($votes_query) ) {
?>
          <div class="splitLine">
            <div style="float: left;"><?php echo $votes_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $votes_split->display_links(tep_get_all_get_params(array('page'))); ?></div>
          </div>
          <div class="listArea"><?php echo tep_draw_form('votes_form', basename($PHP_SELF),'action=delete_all_confirm', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo '<a href="javascript:void(0)" onclick="copy_checkboxes(document.votes_form,\'mark\')" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_TITLE; ?></th>
              <th><?php echo TABLE_HEADING_TYPE; ?></th>
              <th><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 's_sort_id')) . 's_sort_id=' . $sortIP) . '">' . TABLE_HEADING_IP . '</a>'; ?></th>
              <th><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 's_sort_id')) . 's_sort_id=' . $sortRate) . '">' . TABLE_HEADING_RATING . '</a>'; ?></th>
              <th><?php echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 's_sort_id')) . 's_sort_id=' . $sortDate) . '">' . TABLE_HEADING_DATE_ADDED . '</a>'; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
<?php
      while( $votes_array = $g_db->fetch_array($votes_query) ) {
       if( $votes_array['votes_type'] == 1 ) {
         $types_query = $g_db->query("select gtext_title as title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$votes_array['votes_id'] . "'");
         $link = tep_href_link(FILENAME_GENERIC_TEXT, 'gtID=' . $votes_array['votes_id'] . '&action=new_generic_text');
       } elseif( $votes_array['votes_type'] == 2 ) {
         $types_query = $g_db->query("select abstract_zone_name as title from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$votes_array['votes_id'] . "'");
         $link = tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $votes_array['votes_id'] . '&action=edit_zone');
       }

       if( $g_db->num_rows($types_query) ) {
         $types_array = $g_db->fetch_array($types_query);
         $votes_array = array_merge($votes_array, $types_array);
       } else {
         $votes_array['title'] = TEXT_INFO_NA;
       }

       if( !empty($vtID) && $vtID == $votes_array['auto_id'] ) {
          $vtInfo = new objectInfo($votes_array);
        }
        $generic_count++;
        $rows++;

        echo '              <tr class="dataTableRow">' . "\n";
?>
              <td><?php echo tep_draw_checkbox_field('mark['.$votes_array['auto_id'].']', 1); ?></td>
              <td><?php echo '<a href="' . $link . '" title="' . $votes_array['title'] . '">' . $votes_array['title'] . '</a>'; ?></td>
              <td><?php echo ($votes_array['votes_type']==1?TEXT_INFO_PAGE:TEXT_INFO_COLLECTION) . '</a>'; ?></td>
              <td><?php echo $votes_array['ip_address']; ?></td>
              <td><?php echo 100*ceil($votes_array['rating']/$votes_array['resolution']) . '%'; ?></td>
              <td><?php echo tep_datetime_short($votes_array['date_added']); ?></td>
              <td class="tinysep calign">
<?php
        echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'vtID')) . 'vtID=' . $votes_array['auto_id'] . '&action=delete_comment') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE) . '</a>';
        if (isset($vtInfo) && is_object($vtInfo) && ($votes_array['auto_id'] == $vtInfo->auto_id)) { 
          echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', TEXT_SELECTED); 
        } else { 
          echo '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'vtID')) . 'vtID=' . $votes_array['auto_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
        }
?>
              </td>
            </tr>
<?php
      }
?>
            <tr>
              <td colspan="8" class="formButtons"><?php echo tep_image_submit('button_delete.gif', IMAGE_DELETE) ?></td>
            </tr>
          </table></form></div>
          <div class="splitLine">
            <div style="float: left;"><?php echo $votes_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $votes_split->display_links(tep_get_all_get_params(array('page'))); ?></div>
          </div>
<?php
    }
?>
        </div>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'delete_comment':
        if( $rows > 0 && isset($vtInfo) && is_object($vtInfo) ) {
          $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_VOTE . '</b>');
          $contents[] = array('form' => tep_draw_form('form_comment', basename($PHP_SELF), tep_get_all_get_params(array('action', 'vtID')) . 'vtID=' . $vtInfo->auto_id . '&action=delete_confirm') . tep_draw_hidden_field('auto_id', $vtInfo->auto_id));
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
          $contents[] = array('text' => TEXT_INFO_DELETE_VOTE_INTRO);
          $contents[] = array('text' => '<b>' . tep_datetime_short($vtInfo->date_added) . '</b>');
          $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_delete.gif', IMAGE_DELETE) . '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'vtID')) . 'vtID=' . $vtInfo->auto_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        } else { // create comment dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_SELECT));
          $contents[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
      default:
        if( $rows > 0 && isset($vtInfo) && is_object($vtInfo) ) {
          $heading[] = array('text' => '<b>' . $vtInfo->ip_address . '</b>');
          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'vtID')) . 'vtID=' . $vtInfo->auto_id . '&action=delete_comment') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
          $contents[] = array('text' => TEXT_IP_ADDRESS . '<br />' . $vtInfo->ip_address);
          $contents[] = array('text' => TEXT_DATE_ADDED . '<br />' . tep_datetime_short($vtInfo->date_added));
        } else { // create comment dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('params' => 'text-align: center', 'text' => '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'vtID')) . 'action=new_comment') . '">' . tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW) . '</a>');
          $contents[] = array('text' => TEXT_INFO_NO_VOTES);
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
