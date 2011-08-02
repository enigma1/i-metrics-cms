<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// HTML Cache for osC Admin
// Inserts scripts to be cached or invalidated
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

  $dir = dir(DIR_FS_CATALOG);
  $scripts_array = array();
  while ($script = $dir->read()) {
    if( strlen($script) < 5 || substr($script, -4, 4) != '.php')
      continue;

    $scripts_array[strtolower($script)] = array(
      'id' => $script, 
      'text' => $script
    );
  }
  $dir->close();

  ksort($scripts_array, SORT_STRING);
  $scripts_array = array_values($scripts_array);

  $modes_array = array(
    array('id' => '1', 'text' => 'Cache'),
    array('id' => '2', 'text' => 'Flush'),
    array('id' => '3', 'text' => 'Parametric')
  );

  switch($action) {
    case 'insert':
      $script_key = md5($_POST['scripts_list']);
      $check_query = $g_db->query("select count(*) as total from " . TABLE_CACHE_HTML . " where cache_html_key = '" . $g_db->filter($script_key) . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( !$check_array['total'] ) {
        $sql_data_array = array(
          'cache_html_key' => $g_db->prepare_input(md5($_POST['scripts_list'])),
          'cache_html_script' => $g_db->prepare_input($_POST['scripts_list']),
          'cache_html_duration' => DEFAULT_HTML_CACHE_TIMEOUT,
          'cache_html_type' => '1'
        );
        $g_db->perform(TABLE_CACHE_HTML, $sql_data_array);
        $messageStack->add_session(SUCCESS_ENTRY_INSERT, 'success');
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;

    case 'update_multi':
      if( !isset($_POST['tag_id']) || !is_array($_POST['tag_id']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      foreach ($_POST['tag_id'] as $key=>$value) {
        $sql_data_array = array(
          'cache_html_params' => $g_db->prepare_input($_POST['params'][$key]),
          'cache_html_duration' => $g_db->prepare_input($_POST['duration'][$key]),
          'cache_html_type' => $g_db->prepare_input($_POST['mode'][$key])
        );
        $g_db->perform(TABLE_CACHE_HTML, $sql_data_array, 'update', "cache_html_key = '" . $g_db->filter($key) . "'");
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;

    case 'delete_multi':
      if( !isset($_POST['tag_id']) || !is_array($_POST['tag_id']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      break;

    case 'delete_confirm_multi':
      if( !isset($_POST['tag_id']) || !is_array($_POST['tag_id']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      foreach ($_POST['tag_id'] as $key => $value) {
        $g_db->query("delete from " . TABLE_CACHE_HTML . " where cache_html_key = '" . $g_db->filter($value) . "'");
        $g_db->query("delete from " . TABLE_CACHE_HTML_REPORTS . " where cache_html_key = '" . $g_db->filter($value) . "'");
      }
      $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    default:
      break;
  }

?>
<?php require(DIR_FS_INCLUDES . 'objects/html_start_sub1.php'); ?>
<?php require(DIR_FS_INCLUDES . 'objects/html_start_sub2.php'); ?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
            <div><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
<?php
  if( $action == 'delete_multi' ) {
?>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_DELETE; ?></div>
          </div>

          <div class="formArea"><?php echo tep_draw_form('rl_confirm', $g_script, tep_get_all_get_params('action') . 'action=delete_confirm_multi', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_FILENAME; ?></th>
            </tr>
<?php
    $rows = 0;
    foreach ($_POST['tag_id'] as $key => $value) {
      $delete_query = $g_db->query("select cache_html_script from " . TABLE_CACHE_HTML . " where cache_html_key = '" . $g_db->filter($key) . "'");
      if( $delete_array = $g_db->fetch_array($delete_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        echo '                  <tr class="' . $row_class . '">';    
?>
              <td><?php echo tep_draw_hidden_field('tag_id[]', $key) . $delete_array['cache_html_script']; ?></td>
            </tr>
<?php
      }
    }
    $buttons = array(
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
      tep_image_submit('button_confirm.gif', IMAGE_CONFIRM)
    );
?>
        </table><div class="formButtons"><?php if(count($_POST['tag_id'])) echo implode('', $buttons); ?></div></form></div>
<?php
  } else {
?>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_MAIN; ?></div>
          </div>
<?php
// Catalog File List Stored in the database
    $rows = 0;
    $cache_html_query_raw = "select * from " . TABLE_CACHE_HTML . " order by cache_html_script";
    $cache_html_split = new splitPageResults($cache_html_query_raw, MAX_DISPLAY_HTML_CACHE_SCRIPTS, '', 'cache_html_key');
    if( $cache_html_split->number_of_rows > 0 ) {
?>
          <div class="formArea"><?php echo tep_draw_form('rl', $g_script, tep_get_all_get_params('action') . 'action=delete_multi', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo '<a href="#tag_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_FILENAME; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_TYPE; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_DURATION; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_PARAMETERS; ?></th>
            </tr>
<?php
      $cache_html_query = $g_db->query($cache_html_split->sql_query);
      $bCheck = false;
      while( $cache_html = $g_db->fetch_array($cache_html_query) ) {
        $rows++;
        if( $cache_html['cache_html_type'] == 3 ) {
          $row_class = 'dataTableRowHigh';
        } elseif($cache_html['cache_html_type'] == 2) {
          $row_class = 'dataTableRowImpact';
        } else {
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        }
        echo '                      <tr class="' . $row_class . '">';
?>
              <td class="calign"><?php echo tep_draw_checkbox_field('tag_id[' . $cache_html['cache_html_key'] . ']', ($bCheck?'on':''), $bCheck ); ?></td>
              <td><?php echo $cache_html['cache_html_script']; ?></td>
              <td class="calign"><?php echo tep_draw_pull_down_menu('mode[' . $cache_html['cache_html_key'] . ']', $modes_array, $cache_html['cache_html_type']); ?></td>
              <td class="calign"><?php echo tep_draw_input_field('duration[' . $cache_html['cache_html_key'] . ']', $cache_html['cache_html_duration'], 'style="width: 100px"') . '&nbsp;(Secs)'; ?></td>
              <td class="calign"><?php echo tep_draw_input_field('params[' . $cache_html['cache_html_key'] . ']', $cache_html['cache_html_params'], 'style="width: 140px"'); ?></td>
            </tr>
<?php
      }
      $buttons = array(
        tep_image_submit('button_update.gif', TEXT_UPDATE, 'onclick="this.form.action=' . '\'' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=update_multi') . '\'' . '"'),
        tep_image_submit('button_delete.gif', TEXT_DELETE, 'onclick="this.form.action=' . '\'' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=delete_multi') . '\'' . '"'),
      );
?>
          </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
          <div class="listArea splitLine">
            <div class="floater"><?php echo $cache_html_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $cache_html_split->display_links(tep_get_all_get_params('action', 'page')); ?></div>
          </div>
<?php 
    }
?>
          <div class="comboHeading">
            <div><h1><?php echo HEADING_TITLE2; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_MAIN2; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('mz', $g_script, tep_get_all_get_params('action') . 'action=insert', 'post'); ?><table border="0" cellspacing="0" cellpadding="4">
            <tr>
<?php
      echo '<td><b>Select Script:</b></td>' . "\n";
      echo '<td>' . tep_draw_pull_down_menu('scripts_list', $scripts_array) . '</td>' . "\n";
      echo '<td>' . tep_image_submit('button_insert.gif', TEXT_INSERT) . '</td>' . "\n";
?>
            </tr>
          </table></form></div>
<?php
  }
?>
        </div>
<?php require(DIR_FS_INCLUDES . 'objects/html_end.php'); ?>
