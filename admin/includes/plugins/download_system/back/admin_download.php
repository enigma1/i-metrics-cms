<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Downloads Control for all content types and text pages
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

  $ddID = (isset($_GET['ddID']) ? (int)$_GET['ddID'] : '');
  $s_sort_id = (isset($_GET['s_sort_id']) ? (int)$_GET['s_sort_id'] : '');

  switch( $action ) {
    case 'setflag':
      $g_db->query("update " . TABLE_DOWNLOAD . " set status_id = '" . (int)$_GET['flag'] . "' where auto_id = '" . (int)$ddID . "'");
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'flag')));
      break;
    case 'edit_confirm':
      $check_query = $g_db->query("select count(*) as total from " . TABLE_DOWNLOAD . " where auto_id = '" . (int)$ddID . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( !$check_array['total'] ) {
        $messageStack->add_session(ERROR_CONTENT_INVALID);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'type_id', 'ddID') ));
      }

      $options = $g_plugins->get_options('download_system');
      $path = tep_front_physical_path(DIR_WS_CATALOG . $options['download_path']);

      $cFile = new upload('attach_file', $path);
      if( $cFile->c_result ) {
        $direct_filename = $options['download_path'] . $cFile->filename;
        $direct_full = $path . $cFile->filename;
      } else {
        $direct_filename = $g_db->prepare_input($_POST['direct_filename']);
        $direct_full = tep_front_physical_path(DIR_WS_CATALOG) . $direct_filename;
      }

      if( !is_file($path . basename($direct_filename) ) ) {
        $messageStack->add(ERROR_DOWNLOAD_FILE_INVALID);
        $action = 'edit';
        break;
      }

      $content_name = !empty($_POST['content_name'])?$g_db->prepare_input($_POST['content_name']):basename($direct_filename);

      $sql_data_array = array(
        'content_id' => (int)$_POST['content_id'],
        'content_type' => (int)$_POST['content_type'],
        'content_name' => $content_name,
        'content_text' => $g_db->prepare_input($_POST['content_text']),
        'filename' => $direct_filename,
        'sort_id' => (int)$_POST['sort_id'],
        'status_id' => isset($_POST['status_id'])?1:0
      );
      $g_db->perform(TABLE_DOWNLOAD, $sql_data_array, 'update', "auto_id='" . (int)$ddID . "'");

      $messageStack->add_session(SUCCESS_ENTRY_UPDATED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'type_id', 'ddID') . 'ddID=' . $ddID));
      break;
    case 'delete_confirm':
      if( isset($_POST['auto_id']) && !empty($_POST['auto_id']) ) {
        $auto_id = (int)$_POST['auto_id'];
        $check_query = $g_db->query("select filename from " . TABLE_DOWNLOAD . " where auto_id = '" . (int)$auto_id . "'");
        if( !$g_db->num_rows($check_query) ) {
          $messageStack->add_session(ERROR_DOWNLOAD_INVALID);
          tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'type_id', 'ddID') ));
        }
        $check_array = $g_db->fetch_array($check_query);
        if( !empty($check_array['filename']) ) {
          $path = tep_front_physical_path(DIR_WS_CATALOG);
          unlink($path . $check_array['filename']);
        }
        $g_db->query("delete from " . TABLE_DOWNLOAD . " where auto_id = '" . (int)$auto_id . "'");
        $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'type_id', 'ddID') ));
      break;
    case 'delete_all':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(ERROR_NOTHING_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'type_id') ));
      }
      break;
    case 'delete_all_confirm':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(ERROR_NOTHING_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'type_id') ));
      }
      $tmp_array = array();
      foreach ($_POST['mark'] as $key => $val) {
        $tmp_array[] = (int)$key;
      }
      $g_db->query("delete from " . TABLE_DOWNLOAD . " where auto_id in ('" . implode("','", $tmp_array) . "')");
      $messageStack->add_session(SUCCESS_REMOVE_ASSIGNED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'type_id', 'ddID') ));
      break;
    case 'assign':
      if( !isset($_GET['type_id']) ) {
        $messageStack->add_session(ERROR_CONTENT_TYPE_MISSING);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action')));
      }
      $type_id = (int)$_GET['type_id'];
      if( $type_id != 1 && $type_id != 2 ) {
        $messageStack->add_session(ERROR_CONTENT_TYPE_MISSING);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action')));
      }

      $list_type = $_GET['type_id'] == 1?'list_text':'list_zones';

      if( isset($_POST['mark']) && is_array($_POST['mark']) && count($_POST['mark']) ) {
        foreach ($_POST['mark'] as $key=>$val) {
          $sql_data_array = array(
            'content_id' => (int)$key,
            'content_type' => (int)$type_id,
            'status_id' => 0,
            'date_added' => 'now()',
          );
          $g_db->perform(TABLE_DOWNLOAD, $sql_data_array);
        }
      }
      $messageStack->add_session(SUCCESS_INSERT_ASSIGNED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'type_id') . 'action=' . $list_type));
      break;
    default:
      break;
  }

?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  if( $action == 'list_zones' ) {
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list_zones') . '" title="' . HEADING_COLLECTIONS . '" class="plugins_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_COLLECTIONS) . '</a>'; ?></div>
            <div><h1><?php echo HEADING_COLLECTIONS; ?></h1></div>
          </div>
          <div class="comboHeading">
<?php
   echo '<p>' . TEXT_INFO_ZONE_DETAILS . '</p>';
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
            <div class="floater"><?php echo $list_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $list_split->display_links(tep_get_all_get_params('page')); ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('zones_form', $g_script, 'action=assign&type_id=2', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo '<a href="#mark" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_TITLE; ?></th>
              <th><?php echo TABLE_HEADING_TYPE; ?></th>
              <th><?php echo TABLE_HEADING_INSTANCES; ?></th>
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

        $check_query = $g_db->query("select count(*) as total from " . TABLE_DOWNLOAD . " where content_id = '" . (int)$list_array['abstract_zone_id'] . "' and content_type='2'");
        $check_array = $g_db->fetch_array($check_query);

        $bCheck = false;
        if( $check_array['total'] > 0 ) {
          $bCheck = true;
          $row_class = 'dataTableRowGreen';
        }
        echo '          <tr class="' . $row_class . '">';
?>
              <td class="calign">
<?php 
        echo tep_draw_checkbox_field('mark['.$list_array['abstract_zone_id'].']', 1, false);
?>
              </td>
              <td><?php echo '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $list_array['abstract_zone_id'] . '&action=list') . '" title="' . $list_array['abstract_zone_name'] . '">' . $list_array['abstract_zone_name'] . '</a>'; ?></td>
              <td><?php echo $types_array['abstract_types_name']; ?></td>
              <td><?php echo $check_array['total']; ?></td>
            </tr>
<?php
      }
?>
            <tr>
              <td colspan="4" class="formButtons">
<?php
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'type_id') ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>';
      echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
?>
              </td>
            </tr>
          </table></form></div>
          <div class="listArea splitLine">
            <div class="floater"><?php echo $list_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $list_split->display_links(tep_get_all_get_params('page')); ?></div>
          </div>
<?php
    }
?>
        </div>
<?php
  } elseif( $action == 'list_text' ) {
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list_text') . '" title="' . HEADING_TEXT_PAGES . '" class="plugins_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TEXT_PAGES) . '</a>'; ?></div>
            <div><h1><?php echo HEADING_TEXT_PAGES; ?></h1></div>
          </div>
          <div class="comboHeading">
<?php
   echo '<p>' . TEXT_INFO_TEXT_DETAILS . '</p>';
?>
          </div>
<?php
    $list_query_raw = "select gtext_id, gtext_title from " . TABLE_GTEXT . " where sub='0' and status = '1' order by gtext_title";
    $list_split = new splitPageResults($list_query_raw, GTEXT_PAGE_SPLIT);
    $list_query = $g_db->query($list_split->sql_query);
    if( $g_db->num_rows($list_query) ) {
?>
          <div class="splitLine">
            <div class="floater"><?php echo $list_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $list_split->display_links(tep_get_all_get_params('page')); ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('text_form', $g_script, 'action=assign&type_id=1', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo '<a href="#mark" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_TITLE; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_INSTANCES; ?></th>
            </tr>
<?php
      $rows = 0;
      while( $list_array = $g_db->fetch_array($list_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

        $check_query = $g_db->query("select count(*) as total from " . TABLE_DOWNLOAD . " where content_id = '" . (int)$list_array['gtext_id'] . "' and content_type='1'");
        $check_array = $g_db->fetch_array($check_query);

        $bCheck = false;
        if( $check_array['total'] > 0 ) {
          $bCheck = true;
          $row_class = 'dataTableRowGreen';
        }

        echo '          <tr class="' . $row_class . '">';
?>
              <td class="calign">
<?php 
        echo tep_draw_checkbox_field('mark['.$list_array['gtext_id'].']', 1, false); 
?>
              </td>
              <td><?php echo '<a href="' . tep_href_link(FILENAME_GENERIC_TEXT, 'gtID=' . $list_array['gtext_id'] . '&action=new_generic_text') . '" title="' . $list_array['gtext_title'] . '">' . $list_array['gtext_title'] . '</a>'; ?></td>
              <td class="calign"><?php echo $check_array['total']; ?></td>
            </tr>
<?php
      }
?>
            <tr>
              <td colspan="4" class="formButtons">
<?php
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'type_id') ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>';
      echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
?>
              </td>
            </tr>
          </table></form></div>
          <div class="splitLine">
            <div class="floater"><?php echo $list_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $list_split->display_links(tep_get_all_get_params('page')); ?></div>
          </div>
<?php
    }
?>
        </div>
<?php
  } elseif($action == 'delete_all') {
    $tmp_array = array();
    foreach ($_POST['mark'] as $key => $val) {
      $tmp_array[] = (int)$key;
    }
    $content_query_raw = "select auto_id, content_name from " . TABLE_DOWNLOAD . " where auto_id in ('" . implode("','", $tmp_array) . "')";
    $content_array = $g_db->query_to_array($content_query_raw);
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=delete_all') . '" title="' . HEADING_DELETE_ENTRIES . '" class="plugins_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_DELETE_ENTRIES) . '</a>'; ?></div>
            <div><h1><?php echo HEADING_DELETE_ENTRIES; ?></h1></div>
          </div>
          <div class="comboHeading"><?php echo '<p>' . TEXT_INFO_DELETE_ENTRIES . '</p>'; ?></div>
          <div class="formArea"><?php echo tep_draw_form('content_form', $g_script, 'action=delete_all_confirm', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_NAME; ?></th>
            </tr>
<?php
    for( $i=0, $j=count($content_array); $i<$j; $i++) {
      if( empty($content_array[$i]['cotent_name']) ) {
        $content_array[$i]['content_name'] = TEXT_INFO_EMPTY;
      }
      $class = ($i%2)?'dataTableRow':'dataTableRowAlt';
      echo '              <tr class="' . $class . '">' . "\n";
?>
              <td><?php echo tep_draw_hidden_field('mark[' . $content_array[$i]['auto_id'] . ']', $content_array[$i]['auto_id']) . $content_array[$i]['content_name']; ?></td>
            </tr>
<?php
    }
?>
            <tr>
              <td colspan="2" class="formButtons">
<?php
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>';
      echo tep_image_submit('button_confirm.gif', IMAGE_DELETE);
?>
              </td>
            </tr>
          </table></form></div>
        </div>
<?php
  } else {

    $generic_count = 0;
    $rows = 0;

    $sort_by = '';
    $sortName = 4;
    $sortType = 3;
    switch( $s_sort_id) {
      case 1;
        $sort_by = "content_name";
        break;
      case 2;
        $sortName = 1;
        $sort_by = "content_name desc";
        break;
      case 3;
        $sortType = 4;
        $sort_by = "content_type";
        break;
      case 4;
        $sort_by = "content_type desc";
        break;
      default:
        $sort_by = "content_type, sort_id, content_id";
        break;
    }
    $sort_by = "order by " . $sort_by;
?>
        <div class="maincell">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help') . '" title="' . HEADING_TITLE . '" class="plugins_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
            <div><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div class="dataTableRowAlt3 spacer floater"><?php echo '<a class="blockbox" href="' . tep_href_link($g_script, 'action=list_text') . '">' . TEXT_INFO_ASSIGN_TEXT . '</a>'; ?></div>
            <div class="dataTableRowAlt4 spacer floater"><?php echo '<a class="blockbox" href="' . tep_href_link($g_script, 'action=list_zones') . '">' . TEXT_INFO_ASSIGN_COLLECTIONS . '</a>'; ?></div>
          </div>
<?php
    $content_query_raw = "select auto_id, content_id, content_type, content_name, content_text, status_id from " . TABLE_DOWNLOAD . " "  . $sort_by . "";
    $content_split = new splitPageResults($content_query_raw, GTEXT_PAGE_SPLIT);
    $content_query = $g_db->query($content_split->sql_query);

    if( $g_db->num_rows($content_query) ) {
?>
          <div class="splitLine">
            <div class="floater"><?php echo $content_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $content_split->display_links(tep_get_all_get_params('page')); ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('content_form', $g_script, 'action=delete_all', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo '<a href="#mark" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 's_sort_id') . 's_sort_id=' . $sortName) . '">' . TABLE_HEADING_NAME . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_LINK_TITLE; ?></th>
              <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 's_sort_id') . 's_sort_id=' . $sortType) . '">' . TABLE_HEADING_TYPE . '</a>'; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
<?php
      $rows = 0;
      while( $content_array = $g_db->fetch_array($content_query) ) {
        $class = 'dataTableRow';
        if( $content_array['content_type'] == 1 ) {
          $types_query = $g_db->query("select gtext_title as title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$content_array['content_id'] . "'");
          $link = tep_href_link(FILENAME_GENERIC_TEXT, 'gtID=' . $content_array['content_id'] . '&action=new_generic_text');
          $class = 'dataTableRowAlt3';
        } elseif( $content_array['content_type'] == 2 ) {
          $types_query = $g_db->query("select abstract_zone_name as title from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$content_array['content_id'] . "'");
          $link = tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $content_array['content_id'] . '&action=list');
          $class = 'dataTableRowAlt4';
        }

        if( $g_db->num_rows($types_query) ) {
          $types_array = $g_db->fetch_array($types_query);
          $content_array = array_merge($content_array, $types_array);
        } else {
          $content_array['title'] = TEXT_INFO_NA;
        }

        $generic_count++;
        $rows++;

        $sel_link = tep_href_link($g_script, tep_get_all_get_params('action', 'ddID') . 'ddID=' . $content_array['auto_id'] . '&action=edit');
        $inf_link = tep_href_link($g_script, tep_get_all_get_params('action', 'ddID') . 'ddID=' . $content_array['auto_id']);

        if( !empty($ddID) && $ddID == $content_array['auto_id'] ) {
          $ddInfo = new objectInfo($content_array);
          echo '              <tr class="dataTableRowSelected row_link" href="' . $sel_link . '">' . "\n";
        } else {
          echo '              <tr class="' . $class . ' row_link" href="' . $inf_link . '">' . "\n";
        }
?>
              <td class="calign"><?php echo tep_draw_checkbox_field('mark['.$content_array['auto_id'].']', 1); ?></td>
              <td><?php echo '<a href="' . $link . '" title="' . $content_array['title'] . '">' . $content_array['title'] . '</a>'; ?></td>
              <td>
<?php 
        if( empty($content_array['content_name']) ) {
          echo '<b style="color: #F00">' . TEXT_INFO_NOT_ASSIGNED . '</b>';
        } else {
          echo $content_array['content_name']; 
        }
?>
              </td>
              <td><?php echo ($content_array['content_type']==1?TEXT_INFO_PAGE:TEXT_INFO_COLLECTION); ?></td>
              <td class="tinysep calign">
<?php
        if( $content_array['status_id'] == '1' ) {
          echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'flag', 'ddID') . 'action=setflag&flag=0&ddID=' . $content_array['auto_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
        } else {
          echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'flag', 'ddID') . 'action=setflag&flag=1&ddID=' . $content_array['auto_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
        }
?>
              </td>
              <td class="tinysep calign">
<?php
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'ddID') . 'ddID=' . $content_array['auto_id'] . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE) . '</a>';
        echo '<a href="' . $sel_link . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT) . '</a>';
        if (isset($ddInfo) && is_object($ddInfo) && ($content_array['auto_id'] == $ddInfo->auto_id)) { 
          echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', TEXT_SELECTED); 
        } else { 
          echo '<a href="' . $inf_link . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
        }
?>
              </td>
            </tr>
<?php
      }
      $buttons = array(
        tep_image_submit('button_delete.gif', IMAGE_DELETE),
      );
?>
          </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
          <div class="listArea splitLine">
            <div class="floater"><?php echo $content_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $content_split->display_links(tep_get_all_get_params('page')); ?></div>
          </div>
<?php
    } else {
?>
          <div class="comboHeading"><?php echo TEXT_INFO_NO_ENTRIES_FOUND; ?></div>
<?php
    }
?>
        </div>
<?php
    $heading = array();
    $contents = array();
    switch( $action ) {
      case 'edit':
        $content_query = $g_db->query("select auto_id, content_id, content_name, content_text, content_type, filename, sort_id, status_id from " . TABLE_DOWNLOAD . " where auto_id = '" . (int)$ddID . "'");
        $content_array = $g_db->fetch_array($content_query);
        $heading[] = array('text' => '<b>' . sprintf(TEXT_HEADING_EDIT_CONTENT, $content_array['content_name']) . '</b>');
        $contents[] = array('form' => tep_draw_form('content_edit', $g_script, tep_get_all_get_params('action', 'ddID') . 'ddID=' . $content_array['auto_id'] . '&action=edit_confirm', 'post', 'enctype="multipart/form-data"') . tep_draw_hidden_field('auto_id', $content_array['auto_id']));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
        $contents[] = array('text' => TEXT_INFO_EDIT_CONTENT_INTRO);
        $contents[] = array('class' => 'rpad', 'text' => TEXT_INFO_NAME . '<br />' . tep_draw_input_field('content_name', $content_array['content_name'], 'class="wider"'));
        $contents[] = array('class' => 'rpad', 'text' => TEXT_INFO_TEXT . '<br />' . tep_draw_textarea_field('content_text', $content_array['content_text'], '', 12));

        $contents[] = array(
          'text' => TEXT_INFO_ATTACH_FILE . '<br />' . tep_draw_file_field('attach_file', 'class="wider"')
        );

        $contents[] = array('class' => 'rpad', 'text' => TEXT_INFO_FILENAME . '<br />' . tep_draw_input_field('direct_filename', $content_array['filename'], 'class="wider"'));
        $contents[] = array('text' => TEXT_INFO_SORT . '<br />' . tep_draw_input_field('sort_id', $content_array['sort_id'], 'size="2" maxlength="2"'));
        $contents[] = array('text' => tep_draw_hidden_field('content_type', $content_array['content_type']) . tep_draw_hidden_field('content_id', $content_array['content_id']) . tep_draw_checkbox_field('status_id', 'on', ($content_array['status_id'] == 1)?true:false) . '&nbsp;' . TEXT_INFO_ENABLED);

        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'ddID') . 'ddID=' . $content_array['auto_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          tep_image_submit('button_update.gif', IMAGE_UPDATE),
        );
        $contents[] = array(
          'class' => 'calign',
          'text' => implode('', $buttons),            
        );
        break;
      case 'delete':
        if( $rows > 0 && isset($ddInfo) && is_object($ddInfo) ) {
          $content_query = $g_db->query("select auto_id, content_name from " . TABLE_DOWNLOAD . " where auto_id = '" . (int)$ddID . "'");
          $content_array = $g_db->fetch_array($content_query);
          $heading[] = array('text' => '<b>' . sprintf(TEXT_HEADING_DELETE_CONTENT, $content_array['content_name']) . '</b>');
          $contents[] = array('form' => tep_draw_form('form_content', $g_script, tep_get_all_get_params('action', 'ddID') . 'ddID=' . $ddID . '&action=delete_confirm') . tep_draw_hidden_field('auto_id', $content_array['auto_id']));
          $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
          $contents[] = array('text' => TEXT_INFO_DELETE_CONTENT_INTRO);
          $contents[] = array('text' => TEXT_INFO_NAME . '<br /><b>' . $content_array['content_name'] . '</b>');

          $buttons = array(
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'ddID') . 'ddID=' . $content_array['auto_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
            tep_image_submit('button_confirm.gif', IMAGE_DELETE),
          );
          $contents[] = array(
            'class' => 'calign', 
            'text' => implode('', $buttons)
          );
        } else { // create content dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_SELECT));
          $contents[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
      default:
        if( $rows > 0 && isset($ddInfo) && is_object($ddInfo) ) {
          $heading[] = array('text' => '<b>' . $ddInfo->title . '</b>');
          $buttons = array(
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'ddID') . 'ddID=' . $ddInfo->auto_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>',
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'ddID') . 'ddID=' . $ddInfo->auto_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>',
          );
          $contents[] = array(
            'class' => 'calign', 
            'text' => implode('', $buttons)
          );
          $contents[] = array('text' => TEXT_INFO_NAME . '<br />' . $ddInfo->content_name);
          $contents[] = array('text' => TEXT_INFO_TEXT . '<br />' . $ddInfo->content_text);
        } else { // create content dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('class' => 'calign', 'text' => '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'ddID') . 'action=new_content') . '">' . tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW) . '</a>');
          $contents[] = array('text' => TEXT_INFO_NO_ENTRIES);
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
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
