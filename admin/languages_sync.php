<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Multi-Lingual Support - Language Tables Management Script
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

  $dID = isset($_GET['dID'])?$g_db->prepare_input($_GET['dID']):'';
  if(isset($_POST['rebuild_selected_x']) || isset($_POST['rebuild_selected_y'])) $action='rebuild_selected';
  if(isset($_POST['delete_selected_x']) || isset($_POST['delete_selected_y'])) $action='delete_selected';

  switch($action) {
    case 'help':
      break;

    case 'verify':
    case 'verify_selected':
    case 'verify_selected_confirm':
      if( empty($dID) ) {
        $messageStack->add_session(ERROR_INVALID_TABLE);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'dID') ));
      }
      $lArray = $g_lng->get_tables($dID);
      if( count($lArray) < 2 ) {
        $messageStack->add_session(ERROR_INVALID_LANGUAGE_TABLE);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      $error = true;
      $fields_array = $g_db->query_to_array("show fields from " . constant($dID));
      for($i2=0, $j2=count($fields_array); $i2<$j2; $i2++) {
        if( $fields_array[$i2]['Key'] == 'PRI' ) {
          $error = false;
        }
      }
      if( $error ) {
        $messageStack->add_session(ERROR_INVALID_LANGUAGE_TABLE);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      if( $action == 'verify' ) {
        break;
      }

      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || empty($_POST['mark']) ) {
        $messageStack->add_session(ERROR_INVALID_NOTHING_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=verify'));
      }
      if( $action == 'verify_selected' ) {
        break;
      }

      $language_tables = $g_lng->get_language_tables_detailed($dID);

      if( !isset($language_tables[$dID]) ) {
        $messageStack->add_session(ERROR_INVALID_LANGUAGE_TABLE);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      $table_string = constant($dID);
      $data_array = $g_db->get_table_fields($dID);

      $last_keys = array_keys($data_array['primary_keys_array']);

      foreach($_POST['mark'] as $pkey => $value ) {
        $last_values = explode('_', $pkey);

        if( count($last_keys) != count($last_values) || !count($last_keys) ) {
          $messageStack->add_session(ERROR_INVALID_KEY_SELECTED);
          continue;
        }

        $where_string = " where 1";
        for($i=0, $j=count($last_keys); $i<$j; $i++) {
          $where_string .= " and " . $last_keys[$i] . '=' . $g_db->input($last_values[$i]);
        }

        $data_query = $g_db->query("select * from " . $table_string . $where_string);
        if( !$g_db->num_rows($data_query) ) {
          continue;
        }

        $data_array = $g_db->fetch_array($data_query);
        $g_db->query("delete from " . $table_string . $where_string);
        $g_db->perform($table_string, $data_array);
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=verify'));
      break;

    case 'fix_entry':
      $pkey = isset($_GET['pkey'])?$g_db->prepare_input($_GET['pkey']):'';
      if( empty($dID) || empty($pkey) ) {
        $messageStack->add_session(ERROR_INVALID_KEY_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      $language_tables = $g_lng->get_language_tables_detailed($dID);

      if( !isset($language_tables[$dID]) ) {
        $messageStack->add_session(ERROR_INVALID_LANGUAGE_TABLE);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      $table_string = constant($dID);
      $data_array = $g_db->get_table_fields($dID);

      $last_keys = array_keys($data_array['primary_keys_array']);
      $last_values = explode('_', $pkey);

      if( count($last_keys) != count($last_values) ) {
        $messageStack->add_session(ERROR_INVALID_KEY_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=verify'));
      }

      $where_string = " where 1";
      for($i=0, $j=count($last_keys); $i<$j; $i++) {
        $where_string .= " and " . $last_keys[$i] . '=' . $g_db->input($last_values[$i]);
      }

      $data_query = $g_db->query("select * from " . $table_string . $where_string);
      if( !$g_db->num_rows($data_query) ) {
        $messageStack->add_session(ERROR_LANGUAGE_SYNCH_FAILED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=verify'));
      }

      $data_array = $g_db->fetch_array($data_query);
      $g_db->query("delete from " . $table_string . $where_string);
      $g_db->perform($table_string, $data_array);

      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=verify'));
      break;
    case 'delete_selected':
    case 'rebuild_selected':
      if( !isset($_POST['def_id']) || !is_array($_POST['def_id']) || empty($_POST['def_id']) ) {
        $messageStack->add_session(ERROR_INVALID_NOTHING_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      break;
    case 'rebuild_selected_confirm':
      if( !isset($_POST['def_id']) || !is_array($_POST['def_id']) || empty($_POST['def_id']) ) {
        $messageStack->add_session(ERROR_INVALID_NOTHING_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      foreach($_POST['def_id'] as $key => $value) {
        $lArray = $g_lng->get_tables($key);
        if( empty($lArray) ) continue;
        $result = $g_lng->create_table($key);
        if( $result ) {
          $messageStack->add_session($result);
        } else {
          $messageStack->add_session(sprintf(SUCCESS_LANGUAGE_SYNCH, $key), 'success');
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    case 'delete_selected_confirm':
      if( !isset($_POST['def_id']) || !is_array($_POST['def_id']) || empty($_POST['def_id']) ) {
        $messageStack->add_session(ERROR_INVALID_NOTHING_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      foreach($_POST['def_id'] as $key => $value) {
        $result = $g_lng->delete_table($key);
        if( $result ) {
          $messageStack->add_session($result);
        } else {
          $messageStack->add_session(sprintf(SUCCESS_LANGUAGE_TABLE_REMOVED, $key), 'success');
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;

    default:
      break;
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  if( $action == 'verify' ) {
?>
          <div class="maincell wider">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=verify') . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', BOX_OTHER_QUICK_HELP) . '</a>'; ?></div>
              <div class="floater"><h1><?php echo HEADING_VERIFY . ' ' . $dID; ?></h1></div>
            </div>
<?php
    $table_string = constant($dID);

    $data_array = $g_db->get_table_fields($dID);
    $fields_array = $data_array['fields_array'];
    $primary_array = $data_array['primary_array'];
    $last_keys = $data_array['primary_keys_array'];

    $primary_string = implode(',', $primary_array);
    $table_query_raw = "select " . $primary_string . " from " . $table_string . " order by " . $primary_string;
    $table_split = new splitPageResults($table_query_raw);
    $table_query = $g_db->query($table_split->sql_query);

    $rows = 0;
    $lArray = $g_lng->get_tables($dID);
    $lArray = array_flip($lArray);
    unset($lArray[$table_string]);
    $lArray = array_values(array_flip($lArray));
    $default_language_array = $g_lng->get_table_language($table_string);
    $base_language = $default_language_array['language_name'];

    $field_names = tep_array_invert_flat($fields_array, 'Field', 'Field');
    for( $i=0, $j=count($primary_array); $i<$j; $i++) {
      $field_names[$primary_array[$i]] = '<span class="required">' . $field_names[$primary_array[$i]] . '</span>';
    }
?>
            <div class="comboHeading">
              <div><h2><?php echo TEXT_INFO_FIELD; ?></h2></div>
              <div><b><?php echo implode(' | ', $field_names); ?></b></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form('verify', $g_script, tep_get_all_get_params('action') . 'action=verify_selected'); ?><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th class="calign"><?php echo '<a href="#mark" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</span></a>'; ?></th>
                <th><?php echo $base_language . ' - ' . TABLE_HEADING_DB_STRING . ' [' . $table_string . ']'; ?></th>
<?php
    for($i=0, $j=count($lArray); $i<$j; $i++) {
      $second_language_array = $g_lng->get_table_language($lArray[$i]);
      $second_language = $second_language_array['language_name'];
?>
                <th><?php echo $second_language . ' [' . $lArray[$i] . ']'; ?></th>
<?php
    }
?>
                <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
              </tr>
<?php
    while( $table_array = $g_db->fetch_array($table_query)) {
      foreach($last_keys as $key => $value) { 
        $last_keys[$key] = $table_array[$key];
      }

      $pri_key = implode('_', $last_keys);
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

      echo '              <tr class="' . $row_class . '">';
?>
                <td class="calign"><?php echo tep_draw_checkbox_field('mark[' . $pri_key . ']', 1, false); ?></td>
                <td><?php echo '<b>' . $pri_key . '</b>'; ?></td>
<?php
      $where_string = ' where 1';
      foreach($last_keys as $key => $value) {
        $where_string .= " and " . $key . "='" . $table_array[$key] . "'";
      }

      $error = false;
      for($i=0, $j=count($lArray); $i<$j; $i++) {
        $check_query = $g_db->query("select " . $primary_string . " from " . $lArray[$i] . $where_string);
        if( $g_db->num_rows($check_query) ) {
          $check_array = $g_db->fetch_array($check_query);
          $check_key = implode('_', array_values($check_array));
        } else {
          $check_key = TEXT_INFO_NA;
        }

        if( $check_key != $pri_key ) {
          $error = true;
          echo '<td class="dataTableRowAlt4">' . $check_key . ' ' . TEXT_INFO_FAILED . '</td>' . "\n";
        } else {
          echo '<td>' . $check_key . ' ' . TEXT_INFO_OK . '</td>' . "\n";
        }
      }
?>
                <td class="tinysep calign">
<?php
      if( $error ) {
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'pkey') . 'action=fix_entry&pkey=' . $pri_key) . '">' . tep_image(DIR_WS_ICONS . 'icon_reload.png', sprintf(TEXT_INFO_OVERRIDE_KEY, $pri_key) ) . '</a>';
      } else {
        echo tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_INFO_OK ) . '</a>';
      }
?>
                </td>
              </tr>
<?php
    }
    $buttons_array = array(
      tep_image_submit('button_restore.gif', TEXT_INFO_RESTORE),
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>',
    );
?>
            </table><div class="formButtons"><?php echo implode('', $buttons_array); ?></div></form></div>
            <div class="listArea splitLine">
              <div class="floater"><?php echo $table_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
              <div class="floatend"><?php echo $table_split->display_links(tep_get_all_get_params('page')); ?></div>
            </div>
          </div>
<?php
  } elseif($action == 'verify_selected') {
?>
          <div class="maincell wider">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=verify') . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', BOX_OTHER_QUICK_HELP) . '</a>'; ?></div>
              <div class="floater"><h1><?php echo HEADING_VERIFY . ' ' . $dID; ?></h1></div>
            </div>
            <div class="listArea"><?php echo tep_draw_form('verify', $g_script, tep_get_all_get_params('action') . 'action=verify_selected_confirm'); ?><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_DB_STRING; ?></th>
              </tr>
<?php
    $rows = 0;
    foreach($_POST['mark'] as $pkey => $value ) {
      $pkey = $g_db->prepare_input($pkey);

      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
      echo '              <tr class="' . $row_class . '">';

?>
                <td><?php echo $pkey . tep_draw_hidden_field('mark[' . $pkey . ']', $pkey); ?></td>
              </tr>
<?php
    }
?>
            </table>
            <div class="formButtons">
<?php 
    $buttons_array = array(
      tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=verify') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
    );
    echo implode('', $buttons_array);
?>
            </div>
            </form></div>
          </div>
<?php
  } elseif($action == 'rebuild_selected') {
?>
          <div class="maincell wider">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', BOX_OTHER_QUICK_HELP) . '</a>'; ?></div>
              <div class="floater"><h1><?php echo HEADING_REBUILD_SELECTED; ?></h1></div>
            </div>
            <div class="comboHeading"><?php echo TEXT_INFO_REBUILD_SELECTED_MAIN; ?></div>
            <div class="listArea"><?php echo tep_draw_form('rebuild_selected', $g_script, tep_get_all_get_params('action') . 'action=rebuild_selected_confirm'); ?><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_DB_DEFINITION; ?></th>
                <th><?php echo TABLE_HEADING_DB_STRING; ?></th>
              </tr>
<?php
    $rows = 0;
    foreach($_POST['def_id'] as $def => $value) {
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
      echo '              <tr class="' . $row_class . '">';
?>
                <td><?php echo $def; ?></td>
                <td><?php echo constant($def) . tep_draw_hidden_field('def_id[' . $def . ']', $def); ?></td>
              </tr>
<?php
    }
?>
            </table>
            <div class="formButtons">
<?php 
    $buttons_array = array(
      tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
    );
    echo implode('', $buttons_array);
?>
            </div>
            </form></div>
          </div>
<?php
  } elseif($action == 'delete_selected') {
?>
          <div class="maincell wider">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', BOX_OTHER_QUICK_HELP) . '</a>'; ?></div>
              <div class="floater"><h1><?php echo HEADING_DELETE_SELECTED; ?></h1></div>
            </div>
            <div class="comboHeading"><?php echo TEXT_INFO_DELETE_SELECTED_MAIN; ?></div>
            <div class="listArea"><?php echo tep_draw_form('delete_selected', $g_script, tep_get_all_get_params('action') . 'action=delete_selected_confirm'); ?><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_DB_DEFINITION; ?></th>
                <th><?php echo TABLE_HEADING_REMOVE_TABLES; ?></th>
              </tr>
<?php
    $rows = 0;
    foreach($_POST['def_id'] as $def => $value) {
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
      $language_tables = $g_lng->get_language_tables_detailed($def);
      if( empty($language_tables) || count($language_tables[$def]) < 2 ) {
        $language_table_string = ERROR_INVALID_TABLE;
        $row_class = 'dataTableRowImpact';
      } else {
        $tmp_array = array_flip($language_tables[$def]);
        unset($tmp_array[$language_tables['current']]);
        $language_table_string = implode('<br />', array_keys($tmp_array));
      }
      $language_table_string .= tep_draw_hidden_field('def_id[' . $def . ']', $def);
      echo '              <tr class="' . $row_class . '">';
?>
                <td><?php echo $def; ?></td>
                <td><?php echo $language_table_string; ?></td>
              </tr>
<?php
    }
?>
            </table>
            <div class="formButtons">
<?php 
    $buttons_array = array(
      tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
    );
    echo implode('', $buttons_array);
?>
            </div>
            </form></div>

          </div>
<?php
  } else {
?>
          <div class="maincell">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', BOX_OTHER_QUICK_HELP) . '</a>'; ?></div>
              <div class="floater"><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form('rebuild_selected', $g_script, tep_get_all_get_params('action') . 'action=rebuild_selected'); ?><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th class="calign"><?php echo '<a href="#def_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</span></a>'; ?></th>
                <th><?php echo TABLE_HEADING_DB_DEFINITION; ?></th>
                <th><?php echo TABLE_HEADING_DB_STRING; ?></th>
                <th><?php echo TABLE_HEADING_LANGUAGE_TABLES; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
<?php
    $rows = 0;
    $fs_includes = tep_front_physical_path(DIR_WS_CATALOG_INCLUDES);
    $all_tables = tep_get_file_array($fs_includes . 'database_tables.php');
    $tables = array();
    $args = array(
      'tables' => &$tables
    );
    $g_plugins->invoke('languages_sync', $args);
    $all_tables = array_merge($all_tables, $args['tables']);

    unset($all_tables['TABLE_LANGUAGES']);
    ksort($all_tables);
    $language_tables = $g_lng->get_all_tables();

    foreach( $all_tables as $def => $table_string ) {
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
      $bCheck = false;
      $tables_array = $g_lng->get_tables($def);

      if( (empty($dID) || $dID == $def) && !isset($dInfo) ) {
        $tmp_array = array(
          'def' => $def,
          'tables' => $tables_array,
        );
        $dInfo = new objectInfo($tmp_array);
        echo '              <tr class="dataTableRowSelected">' . "\n";
      } else {
        if( isset($language_tables[$def]) && (empty($tables_array) || (count($tables_array) == 1 && $tables_array[0] == $table_string)) ) {
          $row_class = 'dataTableRowAlt4';
        } elseif( isset($language_tables[$def]) ) {
          if( isset($args['tables'][$def]) ) {
            $row_class = 'dataTableRowPlug';
          } else {
            $row_class = 'dataTableRowAlt3';
          }
        }
        echo '              <tr class="' . $row_class . ' row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'dID') . 'dID=' . $def) . '">';
      }

      if( isset($language_tables[$def]) ) {
        $bCheck = true;
      }
?>
                <td class="calign"><?php echo tep_draw_checkbox_field('def_id[' . $def . ']', ($bCheck?'on':''), $bCheck ); ?></td>
                <td class="transtwenties"><?php echo $def; ?></td>
                <td><?php echo $table_string; ?></td>
                <td class="transtwenties">
<?php
      if( empty($tables_array) || (count($tables_array) != count($g_lng->languages) && $tables_array[0] == $table_string) ) {
        if( isset($language_tables[$def]) ) {
          echo TEXT_INFO_NOT_DB;
        } else {
          echo TEXT_INFO_NOT_ASSIGNED; 
        }
      } else {
        echo implode('<br />', $tables_array); 
      }
?>
                </td>
                <td class="tinysep calign">
<?php
      if( isset($dInfo) && is_object($dInfo) && $def == $dInfo->def ) { 
        echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', $def . ' ' . TEXT_SELECTED);
      } else { 
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'dID') . 'dID=' . $def) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_SELECT . ' ' . $def) . '</a>';
      }
?>
                </td>
              </tr>
<?php
    }
    $buttons_array = array(
      tep_image_submit('button_rebuild.gif', TEXT_INFO_REBUILD_SELECTED, 'name="rebuild_selected"'),
      tep_image_submit('button_drop.gif', TEXT_INFO_DROP_SELECTED, 'name="delete_selected"'),
    );
?>
            </table><div class="formButtons"><?php echo implode('', $buttons_array); ?></div></form></div>
            <div class="listArea splitLine">
               <div><?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, min(1, $rows), $rows, $rows); ?></div>
            </div>
          </div>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'rebuild':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_LANGUAGE . '</b>');

        $contents[] = array('form' => tep_draw_form('languages', $g_script, tep_get_all_get_params('action', 'dID') . 'action=insert'));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'new_entry.png', IMAGE_NEW) );
        $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_NAME . '<br />' . tep_draw_input_field('language_name'));
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . '<br />' . tep_draw_input_field('language_code'));
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_PATH . '<br />' . tep_draw_input_field('language_path'));
        $contents[] = array('text' => TEXT_INFO_SORT_ORDER . '<br />' . tep_draw_input_field('sort_id'));
        $contents[] = array('text' => tep_draw_checkbox_field('status_id') . ' ' . TEXT_INFO_ENABLED);

        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          tep_image_submit('button_insert.gif', IMAGE_INSERT),
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons),
        );
        break;
      case 'edit':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_LANGUAGE . '</b>');

        $contents[] = array('form' => tep_draw_form('languages', $g_script, tep_get_all_get_params('action', 'dID') . 'dID=' . $dInfo->language_id . '&action=save'));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
        $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_NAME . '<br />' . tep_draw_input_field('language_name', $dInfo->language_name));
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . '<br />' . tep_draw_input_field('language_code', $dInfo->language_code));
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_PATH . '<br />' . tep_draw_input_field('language_path', $dInfo->language_path));
        $contents[] = array('text' => TEXT_INFO_SORT_ORDER . '<br />' . tep_draw_input_field('sort_id', $dInfo->sort_id));

        $contents[] = array('text' => tep_draw_checkbox_field('status_id', 1, $dInfo->status_id?true:false) . ' ' .  TEXT_INFO_ENABLED);

        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          tep_image_submit('button_update.gif', IMAGE_UPDATE)
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons),
        );
        break;

      case 'delete':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_LANGUAGE . '</b>');

        $contents[] = array('form' => tep_draw_form('languages', $g_script, tep_get_all_get_params('action', 'dID') . 'dID=' . $dInfo->language_id  . '&action=deleteconfirm'));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
        $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
        $contents[] = array('text' => '<b>' . $dInfo->language_name . '</b>');

        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons),
        );
        break;

      default:
        if( isset($dInfo) && is_object($dInfo)) {
          $heading[] = array(
            'class' => 'calign',
            'text' => '<b>' . $dInfo->def . '</b>'
          );

          $buttons = array(
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'dID') . 'action=verify&dID=' . $dInfo->def) . '">' . tep_image_button('button_validate.gif', TEXT_INFO_VERIFY) . '</a>',
          );

          $contents[] = array(
            'class' => 'calign', 
            'text' => implode('', $buttons),
          );

          $tables_array = $dInfo->tables;
          if( count($tables_array) != count($g_lng->languages) ) {
            if( isset($language_tables[$dInfo->def]) ) {
              $contents[] = array('text' => sprintf(TEXT_INFO_TABLE_DEF, ('<b>' . $tables_array[0]) . '</b>'));
            } else { 
              $contents[] = array('text' => sprintf(TEXT_INFO_TABLE_NONE, ('<b>' . $tables_array[0]) . '</b>'));
            }
          } else {
            $auto_count = array();
            $valid_count = array();

            for( $i=0, $j=count($tables_array); $i<$j; $i++ ) {
              $count_query = $g_db->query("select count(*) as total from " . $tables_array[$i]);
              $count_array = $g_db->fetch_array($count_query);
              $valid_count[$count_array['total']] = $tables_array[$i];

              $strings_array = array(TEXT_INFO_TABLE . ' <b>' . $tables_array[$i] . '</b>');
              $strings_array[] = TEXT_INFO_ENTRIES . '&nbsp<b>' . $count_array['total'] . '</b><br />';
              $strings_array[] = TEXT_INFO_STRUCTURE;
              $fields_array = $g_db->query_to_array("show fields from " . $tables_array[$i]);
              for($i2=0, $j2=count($fields_array); $i2<$j2; $i2++) {
                $field = $fields_array[$i2]['Field'] . ' ' . $fields_array[$i2]['Type'];
                if( $fields_array[$i2]['Key'] == 'PRI' ) {
                  if( !empty($fields_array[$i]['Extra']) ) {
                    $auto_query = $g_db->query("show table status like '" . $tables_array[$i] . "'");
                    $auto_array = $g_db->fetch_array($auto_query);
                    $auto_count[$tables_array[$i]] = $auto_array['Auto_increment'];
                  }
                  $field = '<span class="required">' . $field . '</span>';
                } elseif( !empty($fields_array[$i2]['Key']) ) {
                  $field = '<span style="color: #0000CC">' . $field . '</span>';
                }
                $strings_array[] = '<b>' . $field . '</b>';
              }
              $contents[] = array('text' => implode('<br />', $strings_array));
              $contents[] = array('text' => '<hr />');
            }

            $tmp_array = array_flip($valid_count);
            $contents[] = array('text' => '<b>' . TEXT_INFO_INTEGRITY . '</b>');
            $check_string = '<b style="color: #009900">' . TEXT_INFO_OK . '</b>';
            if( count($tmp_array) != 1 ) {
              $check_string = '<span class="required">' . TEXT_INFO_FAILED . '</span>';
              foreach($valid_count as $table => $value ) {
                $check_string .= '<br />' . $table . '=' . $value;
              }
            }
            $contents[] = array('text' => TEXT_INFO_ENTRIES . '&nbsp;' . $check_string);

            $tmp_array = array_flip($auto_count);
            $check_string = '<b style="color: #009900">' . TEXT_INFO_OK . '</b>';
            if( !count($tmp_array) ) {
              $check_string = TEXT_INFO_NA . '<br />';
            } elseif( count($tmp_array) != 1 ) {
              $check_string = '<span class="required">' . TEXT_INFO_FAILED . '</span><br />';
              foreach($auto_count as $table => $value ) {
                $check_string = $table . '=' . $value;
              }
            }
            $contents[] = array('text' => TEXT_INFO_AUTO . '&nbsp;' . $check_string);

          }
        } else { // create generic_text dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
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
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
