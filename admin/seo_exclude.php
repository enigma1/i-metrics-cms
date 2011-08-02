<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: SEO-G Exclusion List
// Scripts to be excluded by SEO-G from links generation.
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

  switch($action) {
    case 'insert':
      $script_key = md5($_POST['scripts_list']);
      $check_query = $g_db->query("select count(*) as total from " . TABLE_SEO_EXCLUDE . " where seo_exclude_key = '" . $g_db->filter($script_key) . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( !$check_array['total'] ) {
        $sql_data_array = array(
          'seo_exclude_key' => $g_db->prepare_input(md5($_POST['scripts_list'])),
          'seo_exclude_script' => $g_db->prepare_input($_POST['scripts_list'])
        );
        $g_db->perform(TABLE_SEO_EXCLUDE, $sql_data_array);
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
        $g_db->query("delete from " . TABLE_SEO_EXCLUDE . " where seo_exclude_key = '" . $g_db->filter($value) . "'");
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
              <th><?php echo TABLE_HEADING_FILENAME; ?></th>
            </tr>
<?php
    $rows = 0;
    foreach ($_POST['tag_id'] as $key=>$value) {
      $delete_query = $g_db->query("select seo_exclude_script from " . TABLE_SEO_EXCLUDE . " where seo_exclude_key = '" . $g_db->filter($key) . "'");
      if( $delete_array = $g_db->fetch_array($delete_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowSelected';
        echo '                  <tr class="' . $row_class . '">';    
?>
              <td><?php echo tep_draw_hidden_field('tag_id[]', $key) . $delete_array['seo_exclude_script']; ?></td>
            </tr>

<?php
      }
    }
?>
            <tr>
              <td>
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
    $rows = 0;
    $seo_exclude_query_raw = "select * from " . TABLE_SEO_EXCLUDE . " order by seo_exclude_script";
    $seo_exclude_split = new splitPageResults($seo_exclude_query_raw, SEO_PAGE_SPLIT, '', 'seo_exclude_key');
    if( $seo_exclude_split->number_of_rows > 0 ) {
?>
          <div class="formArea"><?php echo tep_draw_form('rl', $g_script, tep_get_all_get_params('action') . 'action=delete_multi', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo '<a href="#tag_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_FILENAME; ?></th>
            </tr>
<?php
      $seo_exclude_query = $g_db->query($seo_exclude_split->sql_query);
      $bCheck = false;
      while ($seo_exclude = $g_db->fetch_array($seo_exclude_query)) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        echo '                      <tr class="' . $row_class . '">';
?>
              <td class="calign"><?php echo tep_draw_checkbox_field('tag_id[' . $seo_exclude['seo_exclude_key'] . ']', ($bCheck?'on':''), $bCheck ); ?></td>
              <td><?php echo $seo_exclude['seo_exclude_script']; ?></td>
            </tr>
<?php
      }
      $buttons = array(
         tep_image_submit('button_delete.gif', TEXT_DELETE, 'onclick="this.form.action=' . '\'' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=delete_multi') . '\'' . '"')
      );
?>
          </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
          <div class="listArea splitLine">
            <div class="floater"><?php echo $seo_exclude_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $seo_exclude_split->display_links(tep_get_all_get_params('action', 'page')); ?></div>
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
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
