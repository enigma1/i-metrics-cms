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
  $cmID = (isset($_GET['cmID']) ? (int)$_GET['cmID'] : '');

  $s_sort_id = (isset($_GET['s_sort_id']) ? (int)$_GET['s_sort_id'] : '');

  switch( $action ) {
    case 'setflag':
      $g_db->query("update " . TABLE_COMMENTS . " set status_id = '" . (int)$_GET['flag'] . "' where auto_id = '" . (int)$cmID . "'");
      tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'flag'))));
      break;

    case 'delete_all_confirm':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(ERROR_NOTHING_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) ));
      }
      foreach ($_POST['mark'] as $key=>$val) {
        $g_db->query("delete from " . TABLE_COMMENTS . " where auto_id = '" . (int)$key . "'");
      }
      $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
      tep_redirect(tep_href_link($g_script));
      break;
    case 'delete_confirm':
      if( isset($_POST['auto_id']) && !empty($_POST['auto_id']) ) {
        $auto_id = (int)$_POST['auto_id'];
        $g_db->query("delete from " . TABLE_COMMENTS . " where auto_id = '" . (int)$auto_id . "'");
        $messageStack->add_session(WARNING_COMMENT_REMOVED, 'warning');
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'cmID'))));
      break;

    case 'edit_confirm':
      $error = false;
      if( !isset($_POST['auto_id']) || empty($_POST['auto_id']) ) {
        $messageStack->add_session(ERROR_COMMENT_INVALID);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action'))));
      }
      if( !isset($_POST['comments_email']) || empty($_POST['comments_email']) ) {
        $messageStack->add_session(ERROR_COMMENT_EMAIL_EMPTY);
        $error = true;
      }
      if( !isset($_POST['comments_body']) || empty($_POST['comments_body']) ) {
        $messageStack->add_session(ERROR_COMMENT_BODY_EMPTY);
        $error = true;
      }
      if( $error ) {
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) . 'action=edit'));
      }
      $status_id = isset($_POST['status_id'])?1:0;

      $sql_data_array = array(
        'comments_email' => $g_db->prepare_input($_POST['comments_email']),
        'comments_body' => $g_db->prepare_input($_POST['comments_body']),
        'comments_url' => $g_db->prepare_input($_POST['comments_url']),
        'comments_author' => empty($_POST['comments_author'])?TEXT_INFO_GUEST:$g_db->prepare_input($_POST['comments_author']),
        'status_id' => $g_db->prepare_input((int)$status_id),
      );
      $g_db->perform(TABLE_COMMENTS, $sql_data_array, 'update', "auto_id='" . (int)$_POST['auto_id'] . "'");
      $messageStack->add_session(SUCCESS_COMMENT_UPDATED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action'))));

      break;
    case 'assign':
      if( !isset($_GET['type_id']) ) {
        $messageStack->add_session(ERROR_COMMENT_TYPE_MISSING);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action'))));
      }
      $type_id = (int)$_GET['type_id'];
      if( $type_id != 1 && $type_id != 2 ) {
        $messageStack->add_session(ERROR_COMMENT_TYPE_MISSING);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action'))));
      }

      $list_type = $_GET['type_id'] == 1?'list_text':'list_zones';

      if( isset($_POST['reset']) && is_array($_POST['reset']) && count($_POST['reset']) ) {
        $tmp_array = array();
        foreach ($_POST['reset'] as $key => $val) {
          $tmp_array[] = (int)$key;
        }
        if( count($tmp_array) ) {
          $g_db->query("delete from " . TABLE_COMMENTS_TO_CONTENT . " where comments_id in ('" . implode("','", $tmp_array) . "') and content_type = '" . (int)$type_id . "'");
        }
      }
      if( isset($_POST['mark']) && is_array($_POST['mark']) && count($_POST['mark']) ) {
        foreach ($_POST['mark'] as $key=>$val) {
          $check_query = $g_db->query("select count(*) as total from " . TABLE_COMMENTS_TO_CONTENT . " where comments_id = '" . (int)$key . "' and content_type= '" . (int)$type_id . "'");
          $check_array = $g_db->fetch_array($check_query);
          if( $check_array['total'] ) continue;

          $sql_data_array = array(
            'comments_id' => (int)$key,
            'content_type' => (int)$type_id,
          );
          $g_db->perform(TABLE_COMMENTS_TO_CONTENT, $sql_data_array);
        }
      }
      $messageStack->add_session(SUCCESS_INSERT_ASSIGNED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'type_id')) . 'action=' . $list_type));
      break;
    default:
      break;
  }
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php require('includes/objects/html_start_sub2.php'); ?>
<?php
  // Request Direct Plugin Access
  $plugin = $g_plugins->get('comments_system');

  if( $action == 'list_zones' ) {
?>
        <div class="maincell wider">
          <div class="comboHeading">
            <div><h1><?php echo HEADING_COLLECTIONS; ?></h1></div>
          </div>
          <div class="comboHeading">
<?php
   if( !isset($plugin->options['collection_include']) || $plugin->options['collection_include'] ) {
     echo TEXT_INFO_MODE_COLLECTIONS_INCLUSIVE; 
   } else {
     echo TEXT_INFO_MODE_COLLECTIONS_EXCLUSIVE;
   }
   echo '<p>' . TEXT_INFO_MODE_MORE . '</p>';
?>
          </div>
<?php
    $filter_string = "where status_id='1'";
    if( !empty($s_type_id) ) {
      $filter_string = " and abstract_types_id= '" . (int)$s_type_id . "'";
    }

    $list_query_raw = "select abstract_zone_id, abstract_types_id, abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " "  . $filter_string . " order by sort_id, abstract_zone_name";
    $list_split = new splitPageResults($list_query_raw, ABSTRACT_PAGE_SPLIT);
    $list_query = $g_db->query($list_split->sql_query);
    if( $g_db->num_rows($list_query) ) {
?>
          <div class="splitLine">
            <div style="float: left;"><?php echo $list_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $list_split->display_links(tep_get_all_get_params(array('page'))); ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('zones_form', $g_script, 'action=assign&type_id=2', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo '<a href="javascript:void(0)" onclick="copy_checkboxes(document.zones_form,\'mark\')" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_TITLE; ?></th>
              <th><?php echo TABLE_HEADING_TYPE; ?></th>
              <th><?php echo TABLE_HEADING_PROCESSED; ?></th>
            </tr>
<?php
      $row_type = 0;
      $row_array = array(
        'dataTableRowAlt2', 
        'dataTableRowAlt3', 
        'dataTableRowAlt4', 
        'dataTableRowAlt5',
      );
      $row_counter = count($row_array);
      $row_class = 'dataTableRow';

      while( $list_array = $g_db->fetch_array($list_query) ) {
        $types_query = $g_db->fly("select abstract_types_name from " . TABLE_ABSTRACT_TYPES . " where abstract_types_id = '" . (int)$list_array['abstract_types_id'] . "'");
        $types_array = $g_db->fetch_array($types_query);

        if( $row_type != $list_array['abstract_types_id'] ) {
          $row_class = $row_array[$list_array['abstract_types_id']%$row_counter];
        }

        $check_query = $g_db->query("select count(*) as total from " . TABLE_COMMENTS_TO_CONTENT . " where comments_id = '" . (int)$list_array['abstract_zone_id'] . "' and content_type='2'");
        $check_array = $g_db->fetch_array($check_query);

        $bCheck = false;
        if( $check_array['total'] > 0 ) {
          $bCheck = true;
          $row_class = 'dataTableRowGreen';
        }

        $comments_query = $g_db->query("select count(*) as total, if(sum(read_id), sum(read_id), 0) as total_read from " . TABLE_COMMENTS . " where comments_id = '" . (int)$list_array['abstract_zone_id'] . "' and content_type='2'");
        $comments_array = $g_db->fetch_array($comments_query);
        if( $comments_array['total_read'] != $comments_array['total'] ) {
          $comments_processed = '<b style="color: #FF0000;">' . $comments_array['total_read'] . '/' . $comments_array['total'] . '</b>';
        } else {
          $comments_processed = $comments_array['total'];
        }

        echo '          <tr class="' . $row_class . '">';
?>
              <td>
<?php 
        echo tep_draw_checkbox_field('mark['.$list_array['abstract_zone_id'].']', ($bCheck?'on':''), $bCheck);
        echo tep_draw_hidden_field('reset['.$list_array['abstract_zone_id'].']', $list_array['abstract_zone_id']); 
?>
              </td>
              <td><?php echo '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $list_array['abstract_zone_id'] . '&action=list') . '" title="' . $list_array['abstract_zone_name'] . '">' . $list_array['abstract_zone_name'] . '</a>'; ?></td>
              <td><?php echo $types_array['abstract_types_name']; ?></td>
              <td><?php echo $comments_processed; ?></td>
            </tr>
<?php
      }
?>
            <tr>
              <td colspan="4" class="formButtons">
<?php
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'type_id')) ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>';
      echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
?>
              </td>
            </tr>
          </table></form></div>
          <div class="splitLine">
            <div style="float: left;"><?php echo $list_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $list_split->display_links(tep_get_all_get_params(array('page'))); ?></div>
          </div>
<?php
    }
?>
        </div>
<?php
  } elseif( $action == 'list_text' ) {
?>
        <div class="maincell wider">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_TEXT_PAGES; ?></h1></div>
          </div>
          <div class="comboHeading">
<?php
   if( !isset($plugin->options['text_include']) || $plugin->options['text_include'] ) {
     echo TEXT_INFO_MODE_TEXT_INCLUSIVE; 
   } else {
     echo TEXT_INFO_MODE_TEXT_EXCLUSIVE;
   }
   echo '<p>' . TEXT_INFO_MODE_MORE . '</p>';
?>
          </div>
<?php
    $list_query_raw = "select gtext_id, gtext_title from " . TABLE_GTEXT . " where sub='0' and status = '1' order by gtext_title";
    $list_split = new splitPageResults($list_query_raw, GTEXT_PAGE_SPLIT);
    $list_query = $g_db->query($list_split->sql_query);
    if( $g_db->num_rows($list_query) ) {
?>
          <div class="splitLine">
            <div style="float: left;"><?php echo $list_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $list_split->display_links(tep_get_all_get_params(array('page'))); ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('text_form', $g_script, 'action=assign&type_id=1', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo '<a href="javascript:void(0)" onclick="copy_checkboxes(document.text_form,\'mark\')" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_TITLE; ?></th>
              <th><?php echo TABLE_HEADING_PROCESSED; ?></th>
            </tr>
<?php
      $rows = 0;
      while( $list_array = $g_db->fetch_array($list_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

        $check_query = $g_db->query("select count(*) as total from " . TABLE_COMMENTS_TO_CONTENT . " where comments_id = '" . (int)$list_array['gtext_id'] . "' and content_type='1'");
        $check_array = $g_db->fetch_array($check_query);

        $bCheck = false;
        if( $check_array['total'] > 0 ) {
          $bCheck = true;
          $row_class = 'dataTableRowGreen';
        }

        $comments_query = $g_db->query("select count(*) as total, if(sum(read_id), sum(read_id), 0) as total_read from " . TABLE_COMMENTS . " where comments_id = '" . (int)$list_array['gtext_id'] . "' and content_type='1'");
        $comments_array = $g_db->fetch_array($comments_query);
        if( $comments_array['total_read'] != $comments_array['total'] ) {
          $comments_processed = '<b style="color: #FF0000;">' . $comments_array['total_read'] . '/' . $comments_array['total'] . '</b>';
        } else {
          $comments_processed = $comments_array['total'];
        }

        echo '          <tr class="' . $row_class . '">';
?>
              <td>
<?php 
        echo tep_draw_checkbox_field('mark['.$list_array['gtext_id'].']', ($bCheck?'on':''), $bCheck); 
        echo tep_draw_hidden_field('reset['.$list_array['gtext_id'].']', $list_array['gtext_id']);
?>
              </td>
              <td><?php echo '<a href="' . tep_href_link(FILENAME_GENERIC_TEXT, 'gtID=' . $list_array['gtext_id'] . '&action=new_generic_text') . '" title="' . $list_array['gtext_title'] . '">' . $list_array['gtext_title'] . '</a>'; ?></td>
              <td><?php echo $comments_processed; ?></td>
            </tr>
<?php
      }
?>
            <tr>
              <td colspan="4" class="formButtons">
<?php
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'type_id')) ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>';
      echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
?>
              </td>
            </tr>
          </table></form></div>
          <div class="splitLine">
            <div style="float: left;"><?php echo $list_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $list_split->display_links(tep_get_all_get_params(array('page'))); ?></div>
          </div>
<?php
    }
?>
        </div>
<?php
  } else {
?>
        <div class="maincell">
          <div class="comboHeading">
            <div><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
          <div class="bounder">
            <div class="dataTableRowAlt3 colorblock floater"><?php echo '<a href="' . tep_href_link($g_script, 'action=list_text') . '">' . TEXT_INFO_ASSIGN_TEXT . '</a>'; ?></div>
            <div class="dataTableRowAlt4 colorblock floater"><?php echo '<a href="' . tep_href_link($g_script, 'action=list_zones') . '">' . TEXT_INFO_ASSIGN_COLLECTIONS . '</a>'; ?></div>
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
        $sort_by = "comments_rating";
        break;
      case 6;
        $sort_by = "comments_rating desc";
        break;
      default:
        $sort_by = "auto_id desc";
        break;
    }

    if( !empty($filter_string) ) {
      $filter_string = "where " . $filter_string;
    }
    $sort_by = "order by " . $sort_by;
    $comments_query_raw = "select auto_id, comments_id, content_type, comments_rating, resolution, ip_address, date_added, read_id, status_id from " . TABLE_COMMENTS . " "  . $sort_by . "";

    $comments_split = new splitPageResults($comments_query_raw, GTEXT_PAGE_SPLIT);
    $comments_query = $g_db->query($comments_split->sql_query);
    if( $g_db->num_rows($comments_query) ) {
?>
          <div class="splitLine">
            <div style="float: left;"><?php echo $comments_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $comments_split->display_links(tep_get_all_get_params(array('page'))); ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('comments_form', $g_script,'action=delete_all_confirm', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo '<a href="javascript:void(0)" onclick="copy_checkboxes(document.comments_form,\'mark\')" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_TITLE; ?></th>
              <th><?php echo TABLE_HEADING_TYPE; ?></th>
              <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 's_sort_id')) . 's_sort_id=' . $sortIP) . '">' . TABLE_HEADING_IP . '</a>'; ?></th>
              <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 's_sort_id')) . 's_sort_id=' . $sortRate) . '">' . TABLE_HEADING_RATING . '</a>'; ?></th>
              <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 's_sort_id')) . 's_sort_id=' . $sortDate) . '">' . TABLE_HEADING_DATE_ADDED . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_STATUS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
<?php
      $rows = 0;
      while( $comments_array = $g_db->fetch_array($comments_query) ) {
        $class = 'dataTableRow';
        if( $comments_array['content_type'] == 1 ) {
          $types_query = $g_db->query("select gtext_title as title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$comments_array['comments_id'] . "'");
          $link = tep_href_link(FILENAME_GENERIC_TEXT, 'gtID=' . $comments_array['comments_id'] . '&action=new_generic_text');
          $class = 'dataTableRowAlt3';
        } elseif( $comments_array['content_type'] == 2 ) {
          $types_query = $g_db->query("select abstract_zone_name as title from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$comments_array['comments_id'] . "'");
          $link = tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $comments_array['comments_id'] . '&action=list');
          $class = 'dataTableRowAlt4';
        }

        if( $g_db->num_rows($types_query) ) {
          $types_array = $g_db->fetch_array($types_query);
          $comments_array = array_merge($comments_array, $types_array);
        } else {
          $comments_array['title'] = TEXT_INFO_NA;
        }

        $generic_count++;
        $rows++;

        if( !empty($cmID) && $cmID == $comments_array['auto_id'] ) {
          $extra_query = $g_db->query("select comments_email, comments_author, comments_url, comments_body from " . TABLE_COMMENTS . " where auto_id= '" . (int)$comments_array['auto_id'] . "'");
          $extra_array = $g_db->fetch_array($extra_query);
          $comments_array = array_merge($comments_array, $extra_array);
          $cmInfo = new objectInfo($comments_array);
          echo '              <tr class="dataTableRowSelected">' . "\n";
        } else {
          echo '              <tr class="' . $class . '">' . "\n";
        }
?>
              <td><?php echo tep_draw_checkbox_field('mark['.$comments_array['auto_id'].']', 1); ?></td>
              <td><?php echo '<a href="' . $link . '" title="' . $comments_array['title'] . '">' . $comments_array['title'] . '</a>'; ?></td>
              <td><?php echo ($comments_array['content_type']==1?TEXT_INFO_PAGE:TEXT_INFO_COLLECTION); ?></td>
              <td><?php echo $comments_array['ip_address']; ?></td>
              <td><?php echo (100*ceil($comments_array['comments_rating'])/$comments_array['resolution']) . '%'; ?></td>
              <td><?php echo tep_datetime_short($comments_array['date_added']); ?></td>
              <td class="tinysep calign">
<?php
      if( $comments_array['status_id'] == '1' ) {
        echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag', 'gcID')) . 'action=setflag&flag=0&gcID=' . $comments_array['comments_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
      } else {
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag', 'gcID')) . 'action=setflag&flag=1&gcID=' . $comments_array['comments_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
      }
?>
              </td>
              <td class="tinysep calign">
<?php
        if( $comments_array['read_id'] ) {
          echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'cmID')) . 'cmID=' . $comments_array['auto_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_read.png', ICON_PREVIEW) . '</a>';
        } else {
          echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'cmID')) . 'cmID=' . $comments_array['auto_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_unread.png', ICON_UNREAD) . '</a>';
        }
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'cmID')) . 'cmID=' . $comments_array['auto_id'] . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE) . '</a>';
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'cmID')) . 'cmID=' . $comments_array['auto_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT) . '</a>';
        if (isset($cmInfo) && is_object($cmInfo) && ($comments_array['auto_id'] == $cmInfo->auto_id)) { 
          echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', TEXT_SELECTED); 
        } else { 
          echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'cmID')) . 'cmID=' . $comments_array['auto_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
        }
?>
              </td>
            </tr>
<?php
      }
?>
            <tr>
              <td colspan="12" class="formButtons"><?php echo tep_image_submit('button_delete.gif', IMAGE_DELETE) ?></td>
            </tr>
          </table></form></div>
          <div class="splitLine">
            <div style="float: left;"><?php echo $comments_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $comments_split->display_links(tep_get_all_get_params(array('page'))); ?></div>
          </div>
<?php
    } else {
?>
          <div class="comboHeading"><?php echo TEXT_INFO_NO_COMMENTS_FOUND; ?></div>
<?php
    }
?>
        </div>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'edit':
        $heading[] = array('text' => '<b>' . sprintf(TEXT_HEADING_EDIT_COMMENT, $comments_array['title']) . '</b>');
        $contents[] = array('form' => tep_draw_form('comment_edit', $g_script, tep_get_all_get_params(array('action', 'cmID')) . 'cmID=' . $cmInfo->auto_id . '&action=edit_confirm') . tep_draw_hidden_field('auto_id', $cmInfo->auto_id));
        $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
        $contents[] = array('text' => TEXT_INFO_EDIT_COMMENT_INTRO);
        $contents[] = array('text' => TEXT_INFO_EMAIL . '<br />' . tep_draw_input_field('comments_email', $cmInfo->comments_email));
        $contents[] = array('text' => TEXT_INFO_AUTHOR . '<br />' . tep_draw_input_field('comments_author', $cmInfo->comments_author));
        $contents[] = array('text' => TEXT_INFO_URL . '<br />' . tep_draw_input_field('comments_url', $cmInfo->comments_url));
        $contents[] = array('text' => TEXT_INFO_COMMENT . '<br />' . tep_draw_textarea_field('comments_body', true, '', 12, $cmInfo->comments_body));

        $contents[] = array('text' => TEXT_INFO_IP_ADDRESS . '<br />' . $cmInfo->ip_address);
        $contents[] = array('text' => tep_draw_checkbox_field('status_id', 'on', ($cmInfo->status_id == 1)?true:false) . '&nbsp;' . TEXT_INFO_APPROVED);

        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'cmID')) . 'cmID=' . $cmInfo->auto_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          tep_image_submit('button_update.gif', IMAGE_UPDATE),
        );
        $contents[] = array(
          'params' => 'text-align: center',
          'text' => implode('', $buttons),            
        );
        break;
      case 'delete':
        if( $rows > 0 && isset($cmInfo) && is_object($cmInfo) ) {
          $heading[] = array('text' => '<b>' . sprintf(TEXT_HEADING_DELETE_COMMENT, $comments_array['title']) . '</b>');
          $contents[] = array('form' => tep_draw_form('form_comment', $g_script, tep_get_all_get_params(array('action', 'cmID')) . 'cmID=' . $cmInfo->auto_id . '&action=delete_confirm') . tep_draw_hidden_field('auto_id', $cmInfo->auto_id));
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
          $contents[] = array('text' => TEXT_INFO_DELETE_COMMENT_INTRO);
          $contents[] = array('text' => '<b>' . tep_datetime_short($cmInfo->date_added) . '</b>');
          $contents[] = array('align' => 'center', 'text' => tep_image_submit('button_delete.gif', IMAGE_DELETE) . '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'cmID')) . 'cmID=' . $cmInfo->auto_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        } else { // create comment dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_SELECT));
          $contents[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
      default:
        if( $rows > 0 && isset($cmInfo) && is_object($cmInfo) ) {
          $heading[] = array('text' => '<b>' . $cmInfo->ip_address . '</b>');
          $buttons = array(
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'cmID')) . 'cmID=' . $cmInfo->auto_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>',
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'cmID')) . 'cmID=' . $cmInfo->auto_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>',
          );
          $contents[] = array(
            'params' => 'text-align:center', 
            'text' => implode('', $buttons),
          );
          $contents[] = array('text' => TEXT_INFO_EMAIL . '<br />' . $cmInfo->comments_email);
          $contents[] = array('text' => TEXT_INFO_IP_ADDRESS . '<br />' . $cmInfo->ip_address);
          $contents[] = array('text' => TEXT_INFO_AUTHOR . '<br />' . $cmInfo->comments_author);
          $contents[] = array('text' => TEXT_INFO_URL . '<br />' . $cmInfo->comments_url);
          $contents[] = array('text' => TEXT_DATE_ADDED . '<br />' . tep_datetime_short($cmInfo->date_added));
          $contents[] = array('text' => TEXT_INFO_COMMENT . '<br />' . $cmInfo->comments_body);
          if( !$cmInfo->read_id ) {
            $g_db->query("update " . TABLE_COMMENTS . " set read_id='1' where auto_id = '" . (int)$cmInfo->auto_id . "'");
          }
        } else { // create comment dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('params' => 'text-align: center', 'text' => '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'cmID')) . 'action=new_comment') . '">' . tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW) . '</a>');
          $contents[] = array('text' => TEXT_INFO_NO_COMMENTS);
        }
        break;
    }

    if( !empty($heading) && !empty($contents) ) {
      echo '             <div class="rightcell">';
      $box = new box;
      echo $box->infoBox($heading, $contents);
      echo '             </div>';
    }
  }
?>
<?php require('includes/objects/html_end.php'); ?>
