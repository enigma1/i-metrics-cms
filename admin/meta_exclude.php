<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: META-G Exclusion Script
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
      $sql_data_array = array('meta_exclude_status' => (int)$_GET['flag']);
      $g_db->perform(TABLE_META_EXCLUDE, $sql_data_array, 'update', "meta_exclude_key='" . $g_db->prepare_input($_GET['tag_id']) . "'");
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action','flag','tag_id') ));
      break;

    case 'insert':
      $phrase_key = md5($_POST['phrase']);
      $check_query = $g_db->query("select count(*) as total from " . TABLE_META_EXCLUDE . " where meta_exclude_key = '" . $g_db->filter($phrase_key) . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( !$check_array['total'] ) {
        $sql_data_array = array(
          'meta_exclude_key' => $g_db->prepare_input($phrase_key),
          'meta_exclude_text' => $g_db->prepare_input($_POST['phrase'])
        );
        $g_db->perform(TABLE_META_EXCLUDE, $sql_data_array);
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;

    case 'update_multi':
      if( !isset($_POST['tag_id']) || !is_array($_POST['tag_id']) || !count($_POST['tag_id']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      foreach ($_POST['tag_id'] as $key=>$value) {
        $g_db->query("delete from " . TABLE_META_EXCLUDE . " where meta_exclude_key = '" . $g_db->filter($key) . "'");
        $sql_data_array = array(
          'meta_exclude_key' => $g_db->prepare_input(md5($_POST['phrase'][$key])),
          'meta_exclude_text' => $g_db->prepare_input($_POST['phrase'][$key])
        );

        $g_db->perform(TABLE_META_EXCLUDE, $sql_data_array);
        //$g_db->perform(TABLE_META_EXCLUDE, $sql_data_array, 'update', "meta_exclude_key = '" . $g_db->filter($key) . "'");
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
        $g_db->query("delete from " . TABLE_META_EXCLUDE . " where meta_exclude_key = '" . $g_db->filter($value) . "'");
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;

    default:
      break;
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
        <div class="maincell wider">
          <div class="comboHeading">
            <div><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
<?php
  if( $action == 'delete_multi' ) {
?>
          <div class="comboHeading">
            <div class="smallText"><?php echo TEXT_INFO_DELETE; ?></div>
          </div>

          <div class="formArea"><?php echo tep_draw_form('rl_confirm', $g_script, tep_get_all_get_params('action') . 'action=delete_confirm_multi', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_PHRASE; ?></th>
            </tr>
<?php
    $rows = 0;
    foreach ($_POST['tag_id'] as $key=>$value) {
      $delete_query = $g_db->query("select meta_exclude_text from " . TABLE_META_EXCLUDE . " where meta_exclude_key = '" . $g_db->filter($key) . "'");
      if( $delete_array = $g_db->fetch_array($delete_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowSelected';
        echo '                  <tr class="' . $row_class . '">';    
?>
              <td><?php echo tep_draw_hidden_field('tag_id[]', $key) . $delete_array['meta_exclude_text']; ?></td>
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
    $meta_query_raw = "select * from " . TABLE_META_EXCLUDE . " order by meta_exclude_text";
    $meta_split = new splitPageResults($meta_query_raw, META_PAGE_SPLIT, '', 'meta_exclude_key');
    if( $meta_split->number_of_rows > 0 ) {
?>
          <div class="formArea"><?php echo tep_draw_form('rl', $g_script, tep_get_all_get_params('action') . 'action=delete_multi', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo '<a href="#tag_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_PHRASE; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
            </tr>
<?php
      $meta_query = $g_db->query($meta_split->sql_query);
      $bCheck = false;
      while( $meta = $g_db->fetch_array($meta_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        echo '                      <tr class="' . $row_class . '">';
?>
              <td class="calign"><?php echo tep_draw_checkbox_field('tag_id[' . $meta['meta_exclude_key'] . ']', ($bCheck?'on':''), $bCheck ); ?></td>
              <td><?php echo tep_draw_input_field('phrase[' . $meta['meta_exclude_key'] . ']', $meta['meta_exclude_text'], '', false, 'text', true); ?></td>
              <td class="tinysep calign">
<?php
        if ($meta['meta_exclude_status'] == '1') {
          echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '<a href="' . tep_href_link($g_script, 'action=setflag&flag=0&tag_id=' . $meta['meta_exclude_key'], 'NONSSL') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
        } else {
          echo '<a href="' . tep_href_link($g_script, 'action=setflag&flag=1&tag_id=' . $meta['meta_exclude_key'], 'NONSSL') . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
        }
?>
              </td>
            </tr>
<?php
      }
?>
            <tr>
              <td colspan="3" class="formButtons">
<?php
      echo tep_image_submit('button_update.gif', TEXT_UPDATE, 'class="dflt" onclick="this.form.action=' . '\'' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=update_multi') . '\'' . '"') . tep_image_submit('button_delete.gif', TEXT_DELETE, 'class="dflt" onclick="this.form.action=' . '\'' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=delete_multi') . '\'' . '"');
?>
              </td>
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
          <div class="formArea"><?php echo tep_draw_form('mz', $g_script, tep_get_all_get_params('action') . 'action=insert', 'post'); ?><table class="tabledata">
            <tr>
<?php
    echo '<td><b>Exclude Word:</b></td>' . "\n";
    echo '<td>' . tep_draw_input_field('phrase') . '</td>' . "\n";
    echo '<td>' . tep_image_submit('button_insert.gif', 'Enter the word to exclude') . '</td>' . "\n";
?>
            </tr>
          </table></form></div>
<?php
  }
?>
        </div>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
