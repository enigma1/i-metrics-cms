<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO-G Redirection for osC Admin
// Featuring:
// - Display Redirection SEO-G URLs
// - Delete/Edit individual Redirection SEO-G URLs
// - URL Redirection validator
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

  $redirect_types_array = array(
                                array('id' => '301', 'text' => '301'),
                                array('id' => '302', 'text' => '302')
                               );

  if( isset($_POST['delete_x']) || isset($_POST['delete_y'])) {
    $action='delete';
  } elseif( isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
    $action='edit';
  } else {
    $action = (isset($_GET['action']) ? $_GET['action'] : '');
  }

  switch($action) {
    case 'delete_confirm':
      if( isset($_POST['mark']) && is_array($_POST['mark']) ) {
        foreach ($_POST['mark'] as $key=>$val) {
          $g_db->query("delete from " . TABLE_SEO_REDIRECT . " where seo_url_key = '" . $g_db->input($key) . "'");
        }
        tep_redirect(tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) ));
      }
      break;
    case 'delete_all_confirm':
      $g_db->query("truncate table " . TABLE_SEO_REDIRECT . "");
      tep_redirect(tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) ));
      break;
    case 'delete':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) ) {
        tep_redirect(tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) ));
      }
      $keys_array = array();
      foreach ($_POST['mark'] as $key=>$val) {
        $keys_array[] = $g_db->filter($key);
      }
      break;
    case 'edit':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) ) {
        tep_redirect(tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) ));
      }
      $keys_array = array();
      foreach ($_POST['mark'] as $key=>$val) {
        $keys_array[] = $g_db->filter($key);
      }
      break;
    case 'update':
      foreach ($_POST['org'] as $key=>$val) {
        if( !tep_not_null($val) || !tep_not_null($_POST['seo'][$key]) || $val == $_POST['seo'][$key] ) {
          $messageStack->add_session('URL fields cannot be empty and must be different - ' . $_POST['seo'][$key], 'error');
          continue;
        }

/*
        $g_db->query("delete from " . TABLE_SEO_REDIRECT . " where seo_url_key='" . $g_db->input($key) . "'");
        $md5_key = md5($_POST['seo'][$key]);
        $check_query = $g_db->query("select seo_url_key from " . TABLE_SEO_REDIRECT . " where seo_url_key='" . $g_db->input($md5_key) . "'");
        if( $g_db->num_rows($check_query) )
          continue;
*/
        $sql_data_array = array(
                                'seo_url_org' => $g_db->prepare_input($_POST['org'][$key]),
                                'seo_url_get' => $g_db->prepare_input($_POST['seo'][$key]),
                                'seo_redirect' => (int)$_POST['redirect'][$key],
                                'last_modified' => 'now()'
                               );
        $g_db->perform(TABLE_SEO_REDIRECT, $sql_data_array, 'update', "seo_url_key='" . $g_db->filter($key) . "'");
      }
      tep_redirect(tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) ));
      break;
    default:
      break;
  }
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<script language="javascript">
  var g_checkbox2 = 0;
  function copy_checkboxes(form, array_name) {
    for (var i = 0; i < form.elements.length; i++) {
      if( form.elements[i].type == "checkbox" ) {
        check_name = form.elements[i].name;
        if( array_name == check_name.substring(0, array_name.length) ) {
          form.elements[i].checked = g_checkbox2?"":"on";
        }
      }
    }
    g_checkbox2 ^= 1;
  }
  function copy_combos(form, array_name) {
    var hit = 0;
    for (var i = 0; i < form.elements.length; i++) {
      if( form.elements[i].type == "select-one" ) {
        check_name = form.elements[i].name;
        if( array_name == check_name.substring(0, array_name.length) ) {
          if( hit == 0 ) {
            input_value = form.elements[i].value;
          }
          form.elements[i].value = input_value;
          hit++;
        }
      }
    }
  }
</script>
<?php require('includes/objects/html_start_sub2.php'); ?>
          <div class="maincell" style="width: 100%;">
            <div class="comboHeading">
              <div class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
<?php
  if( $action == 'delete_all') {
?>
            <div class="comboHeading">
              <div class="smallText"><?php echo TEXT_INFO_DELETE_ALL_URLS; ?></div>
            </div>
            <div class="splitLine">
              <div><?php echo '<a href="' . tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) . 'action=delete_all_confirm') . '">' . tep_image_button('button_confirm.gif', 'Truncate SEO-G URLs table') . '</a>'; ?></div>
            </div>
<?php
  } elseif( $action == 'delete') {
?>
            <div class="comboHeading">
              <div class="smallText"><?php echo TEXT_INFO_DELETE_URLS; ?></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form("seo_redirects", FILENAME_SEO_REDIRECTS, 'action=delete_confirm', 'post'); ?><table border="0" width="100%" cellspacing="1" cellpadding="3">
              <tr>
                <td valign="top"><table border="0" width="100%" cellspacing="1" cellpadding="3">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONVERTED; ?></td>
                  </tr>
<?php
    $rows = 0;
    $seo_url_query = $g_db->query("select seo_url_key, seo_url_get from " . TABLE_SEO_REDIRECT . " where seo_url_key in ('" . implode("','", $keys_array) . "') order by seo_url_get");
    while($seo_url = $g_db->fetch_array($seo_url_query) ) {
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowSelected';
      echo '                      <tr class="' . $row_class . '">';
?>
                    <td class="dataTableContent"><?php echo $seo_url['seo_url_get'] . tep_draw_hidden_field('mark[' . $seo_url['seo_url_key'] . ']', $seo_url['seo_url_get']); ?></td>
<?php
    }
?>
                </table></td>
              </tr>
              <tr>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_SEO_REPORTS, tep_get_all_get_params(array('action')) ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>&nbsp;' . tep_image_submit('button_confirm.gif', 'Confirm deletion of the following SEO-G URLs'); ?></td>
              </tr>
            </table></form></div>
<?php
  } elseif( $action == 'edit') {
?>
            <div class="comboHeading">
              <div class="smallText"><?php echo TEXT_INFO_EDIT_URLS; ?></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form("seo_redirect", FILENAME_SEO_REDIRECTS, 'action=update', 'post'); ?><table border="0" width="100%" cellspacing="1" cellpadding="3">
              <tr>
                <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORIGINAL; ?></td>
                    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONVERTED; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo '<a href="javascript:void(0)" onClick="copy_combos(document.seo_redirect, \'redirect\')" title="Replicate Redirection Value from the first entry to subsequent entries" class="menuBoxHeadingLink"><span class="dataTableHeadingContent">' . TABLE_HEADING_REDIRECT . '</span></a>'; ?></td>
                  </tr>
<?php
    $rows = 0;
    $seo_url_query = $g_db->query("select su.* from " . TABLE_SEO_REDIRECT . " su where su.seo_url_key in ('" . implode("','", $keys_array) . "') order by su.seo_url_get");

    while($seo_url = $g_db->fetch_array($seo_url_query) ) {
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowSelected';
      echo '                      <tr class="' . $row_class . '">';
?>
                    <td class="dataTableContent"><?php echo tep_draw_input_field('org[' . $seo_url['seo_url_key'] . ']', $seo_url['seo_url_org'], 'style="width: 100%"', false, 'text', true); ?></td>
                    <td class="dataTableContent"><?php echo tep_draw_input_field('seo[' . $seo_url['seo_url_key'] . ']', $seo_url['seo_url_get'], 'style="width: 100%"', false, 'text', true); ?></td>
                    <td class="dataTableContent" align="center"><?php echo tep_draw_pull_down_menu('redirect[' . $seo_url['seo_url_key'] . ']', $redirect_types_array, $seo_url['seo_redirect']); ?></td>
<?php
    }
?>
                </table></td>
              </tr>
              <tr>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>&nbsp;' . tep_image_submit('button_update.gif', 'Update changes for the listed SEO-G URLs'); ?></td>
              </tr>
            </table></form></div>
<?php
  } elseif( $action == 'validate') {
    $seo_url_query = $g_db->query("select sr.* from " . TABLE_SEO_REDIRECT . " sr, " . TABLE_SEO_URL . " su where sr.seo_url_key=su.seo_url_key order by sr.seo_url_org");
    if( !$g_db->num_rows($seo_url_query) ) {
?>
            <div class="comboHeading">
              <div class="smallText"><?php echo TEXT_INFO_NO_ERRORS; ?></div>
              <div><?php echo '<a href="' . tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) ) . '">' . tep_image_button('button_back.gif', IMAGE_CANCEL) . '</a>'; ?></div>
            </div>
<?php
    } else {
?>
            <div class="comboHeading">
              <div class="smallText"><?php echo TEXT_INFO_DUPLICATED_URLS; ?></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form("seo_redirects", FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) . 'action=delete_confirm', 'post'); ?><table border="0" width="100%" cellspacing="1" cellpadding="3">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo '<a href="javascript:void(0)" onClick="copy_checkboxes(document.seo_redirects,\'mark\')" title="Page Select On/Off" class="menuBoxHeadingLink"><span class="dataTableHeadingContent">' . TABLE_HEADING_SELECT . '</span></a>'; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_HITS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAST_MODIFIED; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_REDIRECT; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORIGINAL; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONVERTED; ?></td>
              </tr>
<?php
      $rows = 0;
      while($seo_url = $g_db->fetch_array($seo_url_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowSelected';
        echo '                      <tr class="' . $row_class . '">';
?>
                <td width="20"><?php echo tep_draw_checkbox_field('mark['.$seo_url['seo_url_key'].']', 1) ?></td>
                <td class="dataTableContent" align="center"><?php echo $seo_url['seo_url_hits']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $seo_url['last_modified']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $seo_url['seo_redirect']; ?></td>
                <td class="dataTableContent"><?php echo $seo_url['seo_url_org']; ?></td>
                <td class="dataTableContent"><?php echo $seo_url['seo_url_get']; ?></td>
<?php
      }
?>
              <tr>
                <td colspan="8"><?php echo '<a href="' . tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>&nbsp;' . tep_image_submit('button_delete.gif', 'Remove selected entries'); ?></td>
              </tr>
            </table></form></div>
<?php
    }
  } else {
?>
            <div class="comboHeading">
              <div class="smallText"><?php echo TEXT_INFO_SEO_G; ?></div>
            </div>
<?php
// Get Scripts info from the database
    $rows = 0;
    $seo_url_query_raw = "select * from " . TABLE_SEO_REDIRECT . " order by seo_url_get";
    $seo_url_split = new splitPageResults($seo_url_query_raw, SEO_PAGE_SPLIT);
    if( $seo_url_split->number_of_rows > 0 ) {
?>
            <div class="splitLine">
              <div style="float: left;"><?php echo $seo_url_split->display_count(TEXT_DISPLAY_NUMBER_OF_SEO_SCRIPTS); ?></div>
              <div style="float: right;"><?php echo $seo_url_split->display_links(tep_get_all_get_params(array('action', 'page'))); ?></div>
            </div>
            <div class="formArea" style="clear:both"><?php echo tep_draw_form("seo_redirects", FILENAME_SEO_REDIRECTS, 'action=delete', 'post'); ?><table border="0" width="100%" cellspacing="1" cellpadding="3">
              <tr>
                <td><?php echo tep_image_submit('button_delete.gif', 'Delete selected SEO-G URLs', 'name="delete"') . '&nbsp;' . tep_image_submit('button_edit.gif', 'Edit selected SEO-G URLs','name="edit"') . '&nbsp;' . '<a href="' . tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) . 'action=delete_all' ) . '">' . tep_image_button('button_delete_all.gif', 'Truncate redirection SEO-G URLs') . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) . 'action=validate') . '">' . tep_image_button('button_validate.gif', 'Validate redirection SEO-G URLs') . '</a>'; ?></td>
              </tr>
              <tr>
                <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent"><?php echo '<a href="javascript:void(0)" onClick="copy_checkboxes(document.seo_redirects,\'mark\')" title="Page Select On/Off" class="menuBoxHeadingLink"><span class="dataTableHeadingContent">' . TABLE_HEADING_SELECT . '</span></a>'; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_HITS; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAST_MODIFIED; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_REDIRECT; ?></td>
                    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORIGINAL; ?></td>
                    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONVERTED; ?></td>
                  </tr>
<?php
      $seo_url_query = $g_db->query($seo_url_split->sql_query);
      $bCheck = false;
      while ($seo_url = $g_db->fetch_array($seo_url_query)) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowSelected';
        echo '                      <tr class="' . $row_class . '">';
?>
                    <td width="20"><?php echo tep_draw_checkbox_field('mark['.$seo_url['seo_url_key'].']', 1) ?></td>
                    <td class="dataTableContent" align="center"><?php echo $seo_url['seo_url_hits']; ?></td>
                    <td class="dataTableContent" align="center"><?php echo $seo_url['last_modified']; ?></td>
                    <td class="dataTableContent" align="center"><?php echo $seo_url['seo_redirect']; ?></td>
                    <td class="dataTableContent"><?php echo '<a href="' .  $seo_url['seo_url_org'] . '" target="_blank">' . htmlspecialchars(utf8_encode($seo_url['seo_url_org'])) . '</a>'; ?></td>
                    <td class="dataTableContent"><?php echo '<a href="' .  $seo_url['seo_url_get'] . '" target="_blank">' . $seo_url['seo_url_get'] . '</a>'; ?></td>
                  </tr>
<?php
      }
?>
                </table></td>
              </tr>
              <tr>
                <td><?php echo tep_image_submit('button_delete.gif', 'Delete selected SEO-G URLs', 'name="delete"') . '&nbsp;' . tep_image_submit('button_edit.gif', 'Edit selected SEO-G URLs','name="edit"') . '&nbsp;' . '<a href="' . tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) . 'action=delete_all' ) . '">' . tep_image_button('button_delete_all.gif', 'Truncate redirection SEO-G URLs') . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_SEO_REDIRECTS, tep_get_all_get_params(array('action')) . 'action=validate') . '">' . tep_image_button('button_validate.gif', 'Validate redirection SEO-G URLs') . '</a>'; ?></td>
              </tr>
            </table></form></div>
            <div class="splitLine">
              <div style="float: left;"><?php echo $seo_url_split->display_count(TEXT_DISPLAY_NUMBER_OF_SEO_SCRIPTS); ?></div>
              <div style="float: right;"><?php echo $seo_url_split->display_links(tep_get_all_get_params(array('action', 'page'))); ?></div>
            </div>
<?php 
    }
  }
?>
          </div>
<?php require('includes/objects/html_end.php'); ?>
