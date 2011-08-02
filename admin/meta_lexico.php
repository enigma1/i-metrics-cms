<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: META-G Dictionary Script
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
  require(DIR_FS_CLASSES . FILENAME_META_ZONES);
  $cMeta = new meta_zones();

  switch($action) {
    case 'setflag':
      $sql_data_array = array('meta_lexico_status' => (int)$_GET['flag']);
      $g_db->perform(TABLE_META_LEXICO, $sql_data_array, 'update', "meta_lexico_key='" . $g_db->prepare_input($_GET['tag_id']) . "'");
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action','flag','tag_id') ));
      break;

    case 'insert':
      $phrase_key = md5($_POST['phrase']);
      $check_query = $g_db->query("select count(*) as total from " . TABLE_META_LEXICO . " where meta_lexico_key = '" . $g_db->filter($phrase_key) . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( !$check_array['total'] ) {
        $sql_data_array = array(
          'meta_lexico_key' => $g_db->prepare_input($phrase_key),
          'meta_lexico_text' => $g_db->prepare_input($_POST['phrase']),
          'sort_id' => (int)$_POST['sort']
        );
        $g_db->perform(TABLE_META_LEXICO, $sql_data_array);
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;

    case 'update_multi':
      if( !isset($_POST['tag_id']) || !is_array($_POST['tag_id']) || !count($_POST['tag_id']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      foreach ($_POST['tag_id'] as $key=>$value) {
        $g_db->query("delete from " . TABLE_META_LEXICO . " where meta_lexico_key = '" . $g_db->filter($key) . "'");
        $md5_key = md5($_POST['phrase'][$key]);
        $g_db->query("delete from " . TABLE_META_LEXICO . " where meta_lexico_key = '" . $g_db->filter($md5_key) . "'");
        $sql_data_array = array(
          'meta_lexico_key' => $g_db->prepare_input($md5_key),
          'meta_lexico_text' => $g_db->prepare_input($_POST['phrase'][$key]),
          'sort_id' => (int)$_POST['sort'][$key]
        );

        $g_db->perform(TABLE_META_LEXICO, $sql_data_array);
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;

    case 'delete_multi':
      if( !isset($_POST['tag_id']) || !is_array($_POST['tag_id']) || !count($_POST['tag_id']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      break;

    case 'delete_confirm_multi':
      foreach ($_POST['tag_id'] as $key=>$value) {
        $g_db->query("delete from " . TABLE_META_LEXICO . " where meta_lexico_key = '" . $g_db->filter($value) . "'");
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;

    case 'generate':
      if( !isset($_POST['types_id']) ) {
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      $params = '';
      $check_query = $g_db->query("select meta_types_class from " . TABLE_META_TYPES . " where meta_types_id = '" . (int)$_POST['types_id'] . "' order by sort_order");
      if($check_array = $g_db->fetch_array($check_query) ) {
        require(DIR_FS_CLASSES . $check_array['meta_types_class'] . '.php');
        $cMeta = new $check_array['meta_types_class'];
        $params = $cMeta->generate_lexico();
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . $params ));
      break;

    default:
      break;
  }

  $lexico_query = $g_db->query("select meta_types_id as id, meta_types_name as text from " . TABLE_META_TYPES . " where meta_types_status='1' order by sort_order");
  while($lexico_array[] = $g_db->fetch_array($lexico_query) );
  array_pop($lexico_array);

?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
        <div class="maincell" style="width: 100%;">
          <div class="comboHeading">
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
              <th><?php echo TABLE_HEADING_PHRASE; ?></th>
            </tr>
<?php
    $rows = 0;
    foreach ($_POST['tag_id'] as $key=>$value) {
      $delete_query = $g_db->query("select meta_lexico_text from " . TABLE_META_LEXICO . " where meta_lexico_key = '" . $g_db->filter($key) . "'");
      if( $delete_array = $g_db->fetch_array($delete_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        echo '                  <tr class="' . $row_class . '">';    
?>
              <td><?php echo tep_draw_hidden_field('tag_id[]', $key) . $delete_array['meta_lexico_text']; ?></td>
            </tr>
<?php
      }
    }
?>

            <tr>
              <td class="formButtons">
<?php 
    if( count($_POST['tag_id']) ) {
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM);
    }
?>
              </td>
            </tr>
        </table></form></div>
<?php
  } else {
?>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_MAIN; ?></div>
          </div>

<?php
// Catalog File List Stored in the database
    $rows = 0;
    $meta_query_raw = "select * from " . TABLE_META_LEXICO . " order by meta_lexico_status desc, sort_id, meta_lexico_text";
    $meta_split = new splitPageResults($meta_query_raw, META_PAGE_SPLIT, 'meta_lexico_key');
    if( $meta_split->number_of_rows > 0 ) {
?>
          <div class="formArea"><?php echo tep_draw_form('rl', $g_script, tep_get_all_get_params('action') . 'action=delete_multi', 'post'); ?><table class="tabledata">
            <tr>
              <td colspan="4" class="formButtons"><?php echo tep_image_submit('button_update.gif', TEXT_UPDATE, 'onclick="this.form.action=' . '\'' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=update_multi') . '\'' . '"') . ' ' . tep_image_submit('button_delete.gif', TEXT_DELETE, 'onclick="this.form.action=' . '\'' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=delete_multi') . '\'' . '"'); ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo '<a href="#tag_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_PHRASE; ?></th>
              <th><?php echo TABLE_HEADING_SORT; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
            </tr>
<?php
      $meta_query = $g_db->query($meta_split->sql_query);
      $bCheck = false;
      while ($meta = $g_db->fetch_array($meta_query)) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        echo '                      <tr class="' . $row_class . '">';
?>
              <td class="calign"><?php echo tep_draw_checkbox_field('tag_id[' . $meta['meta_lexico_key'] . ']', ($bCheck?'on':''), $bCheck ); ?></td>
              <td><?php echo tep_draw_input_field('phrase[' . $meta['meta_lexico_key'] . ']', $meta['meta_lexico_text']); ?></td>
              <td><?php echo tep_draw_input_field('sort[' . $meta['meta_lexico_key'] . ']', $meta['sort_id']); ?></td>
              <td class="tinysep calign">
<?php
    if ($meta['meta_lexico_status'] == '1') {
      echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '<a href="' . tep_href_link($g_script, 'action=setflag&flag=0&tag_id=' . $meta['meta_lexico_key'], 'NONSSL') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
    } else {
      echo '<a href="' . tep_href_link($g_script, 'action=setflag&flag=1&tag_id=' . $meta['meta_lexico_key'], 'NONSSL') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
    }
?>
              </td>
            </tr>
<?php
      }
?>
            <tr>
              <td colspan="4" class="formButtons"><?php echo tep_image_submit('button_update.gif', TEXT_UPDATE, 'onclick="this.form.action=' . '\'' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=update_multi') . '\'' . '"') . ' ' . tep_image_submit('button_delete.gif', TEXT_DELETE, 'onclick="this.form.action=' . '\'' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=delete_multi') . '\'' . '"'); ?></td>
            </tr>
          </table></form></div>
          <div class="splitLine">
            <div class="floater"><?php echo $meta_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $meta_split->display_links(tep_get_all_get_params('action', 'page')); ?></div>
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
          <div class="formArea"><?php echo tep_draw_form('mz', $g_script, tep_get_all_get_params('action') . 'action=generate', 'post'); ?><table border="0" cellspacing="1" cellpadding="3">
<?php
      echo '<td><b>Select Script:</b></td>' . "\n";
      echo '<td>' . tep_draw_pull_down_menu('types_id', $lexico_array) . '</td>' . "\n";
      echo '<td>' . tep_image_submit('button_generate.gif', TEXT_GENERATE) . '</td>' . "\n";
?>
          </table></form></div>
          <div class="comboHeading">
            <div><h1><?php echo HEADING_TITLE3; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_MAIN3; ?></div>
          </div>

          <div class="formArea"><?php echo tep_draw_form('mz', $g_script, tep_get_all_get_params('action') . 'action=insert', 'post'); ?><table border="0" cellspacing="1" cellpadding="3">
<?php
      echo '<td><b>Enter Phrase:</b></td>' . "\n";
      echo '<td>' . tep_draw_input_field('phrase') . '</td>' . "\n";
      echo '<td><b>Sort Order:</b></td>' . "\n";
      echo '<td>' . tep_draw_input_field('sort', '0') . '</td>' . "\n";
      echo '<td>' . tep_image_submit('button_insert.gif', TEXT_INSERT) . '</td>' . "\n";
?>
          </table></form></div>
<?php
  }
?>
        </div>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
