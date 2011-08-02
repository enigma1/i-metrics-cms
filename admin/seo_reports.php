<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: SEO-G List and Reports of generated URLs
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Featuring:
// - Display Recorded SEO-G URLs
// - Delete/Edit individual SEO-G URLs
// - Google XML Sitemap Generator
// - URL validator
// - Added sorting (02/06/2008)
// - Added sorting by frequency (01/22/2009)
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

  if( isset($_POST['delete_x']) || isset($_POST['delete_y'])) {
    $action='delete';
  } elseif( isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
    $action='edit';
  } elseif( isset($_POST['redirect_x']) || isset($_POST['redirect_y'])) {
    $action='redirect';
  } elseif( isset($_POST['google_xml_x']) || isset($_POST['google_xml_y'])) {
    $action='google_xml';
  } elseif( isset($_POST['older_than_x']) || isset($_POST['older_than_y'])) {
    $action='delete_older';
  } elseif( isset($_POST['filter_x']) || isset($_POST['filter_y'])) {
    $action='default';
  }

  switch($action) {
    case 'delete_confirm':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      foreach ($_POST['mark'] as $key=>$val) {
        $cache_query = $g_db->query("select osc_url_key from " . TABLE_SEO_URL . " WHERE seo_url_key = '" . $g_db->input($key) . "'");
        if( $cache_array = $g_db->fetch_array($cache_query) ) {
          $g_db->query("delete from " . TABLE_SEO_CACHE . " where osc_url_key = '" . $g_db->input($cache_array['osc_url_key']) . "'");
        }
        $g_db->query("delete from " . TABLE_SEO_URL . " where seo_url_key = '" . $g_db->input($key) . "'");
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    case 'delete_all_confirm':
      $g_db->query("truncate table " . TABLE_SEO_URL);
      $g_db->query("truncate table " . TABLE_SEO_CACHE);
      $messageStack->add_session(SUCCESS_URLS_CLEARED, 'warning');
      $messageStack->add_session(SUCCESS_CACHE_CLEARED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    case 'delete_cache':
      $g_db->query("truncate table " . TABLE_SEO_CACHE);
      $messageStack->add_session(SUCCESS_CACHE_CLEARED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    case 'delete':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      $keys_array = array();
      foreach ($_POST['mark'] as $key=>$val) {
        $keys_array[] = $g_db->filter($key);
      }
      break;

    case 'delete_older':
      if( !isset($_POST['older']) || !tep_not_null($_POST['older']) ) {
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      $keys_array = array();
      $time_diff = time() - ($_POST['older']*24*3600);
      $clear_query = $g_db->query("select seo_url_key from " . TABLE_SEO_URL . " where( unix_timestamp(last_modified) ) < " . $time_diff);
      $clear_array = array();
      while($clear = $g_db->fetch_array($clear_query) ) {
        $keys_array[] = $clear['seo_url_key'];
      }
      $action = 'delete';
      break;

    case 'edit':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      $keys_array = array();
      foreach ($_POST['mark'] as $key=>$val) {
        $keys_array[] = $g_db->filter($key);
      }
      $frequency_array = array();
      $frequency_query = $g_db->query("select seo_frequency_id as id, seo_frequency_name as text from " . TABLE_SEO_FREQUENCY . "");
      while( $frequency_array[] = $g_db->fetch_array($frequency_query) );
      array_pop($frequency_array);
      break;
    case 'update':
      foreach ($_POST['org'] as $key=>$val) {
        if( !tep_not_null($val) || !tep_not_null($_POST['seo'][$key]) || $val == $_POST['seo'][$key] ) {
          $messageStack->add_session('URL fields cannot be empty and must be different - ' . $_POST['seo'][$key], 'error');
          continue;
        }

        $cache_query = $g_db->query("select osc_url_key from " . TABLE_SEO_URL . " WHERE seo_url_key = '" . $g_db->input($key) . "'");
        if( $cache_array = $g_db->fetch_array($cache_query) ) {
          $g_db->query("delete from " . TABLE_SEO_CACHE . " WHERE osc_url_key = '" . $g_db->input($cache_array['osc_url_key']) . "'");
        }

        $g_db->query("delete from " . TABLE_SEO_URL . " where seo_url_key='" . $g_db->input($key) . "'");

        $md5_key = md5($_POST['seo'][$key]);
        $check_query = $g_db->query("select seo_url_key from " . TABLE_SEO_URL . " where seo_url_key='" . $g_db->input($md5_key) . "'");
        if( $g_db->num_rows($check_query) )
          continue;

        $key_osc = md5($_POST['org'][$key]);
        $sql_data_array = array(
          'seo_url_org' => $g_db->prepare_input($_POST['org'][$key]),
          'seo_url_get' => $g_db->prepare_input($_POST['seo'][$key]),
          'seo_url_key' => $g_db->prepare_input($md5_key),
          'osc_url_key' => $g_db->prepare_input($key_osc),
          'seo_url_priority' => $g_db->prepare_input($_POST['priority'][$key]),
          'seo_frequency_id' => $g_db->prepare_input($_POST['frequency'][$key]),
          'date_added' => 'now()',
          'last_modified' => 'now()',
        );
        $g_db->perform(TABLE_SEO_URL, $sql_data_array);
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    case 'google_xml':
      require_once(DIR_FS_CLASSES . 'xml_core.php');
      require_once(DIR_FS_CLASSES . 'xml_google_sitemap.php');
      $seo_xml =  new xml_google_sitemap;
      $seo_xml->build_map();
      $xml_string = $seo_xml->get_xml_string();
      $xml_filename = SEO_SITEMAP_FILENAME;

      if( SEO_DEFAULT_COMPRESS == 'true' ) {
        $final_string = gzencode($xml_string, 9);
        $xml_filename .= '.gz';
      } else {
        $final_string = $xml_string;
      }

      $sitemap_url = HTTP_CATALOG_SERVER . DIR_WS_CATALOG . $xml_filename;
      $file_location = DIR_FS_CATALOG . $xml_filename;
      if( isset($_POST['google_notify']) ) {
        $handle = @fopen($file_location, 'w+');
        if( $handle ) {
          fwrite($handle, $final_string);
          fclose($handle);
          chmod($file_location, 0644);
          $messageStack->add_session('File <b>' . $file_location . '</b> successfully created', 'success');
        } else {
          $messageStack->add_session('Could not create/write file: <b>' . $file_location . '</b>', 'error');
          tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
        }

        $handle = fsockopen("www.google.com", 80, $errno, $errstr, 15);
        if( $handle ) {
          fputs($handle, "GET " . '/webmasters/sitemaps/ping?sitemap=' . $sitemap_url . " HTTP/1.0\r\n");
          fputs($handle, "Host: " . HTTP_CATALOG_SERVER . "\r\n");
          fputs($handle, "Referer: " . HTTP_CATALOG_SERVER . "\r\n");
          fputs($handle, "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n\r\n");
          while (!feof($handle)) {
            $buf .= fgets($handle,128);
          }
          fclose($handle);

          if(stristr($buf, 'successfully added') === false) {
            $messageStack->add_session('Google refused the sitemap submission - check your configure.php', 'error');
            echo $buf;
            exit();
          } else {
            $messageStack->add_session('Google Notification successfully sent', 'success');
          }
        } else {
          $messageStack->add_session('Could not notify Google - check your internet connection', 'error');
        }
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      } else {
        header("Expires: 0");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-Disposition: attachment; filename="' . $xml_filename . '"');
        header('Content-Length: '. strlen($final_string) );
        header("Content-Type: application/octet-stream");
        echo $final_string;
        exit();
      }
      break;
    case 'redirect':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      $move_flag = false;
      foreach ($_POST['mark'] as $key => $val) {
        $check_query = $g_db->query("select * from " . TABLE_SEO_URL . " where seo_url_key='" . $g_db->input($key) . "'");
        if( $check_array = $g_db->fetch_array($check_query) ) {
          $redirect_query = $g_db->query("select seo_url_key from " . TABLE_SEO_REDIRECT . " where seo_url_key='" . $g_db->input($key) . "'");
          if( $g_db->num_rows($redirect_query) ) {
            $cache_query = $g_db->query("select osc_url_key from " . TABLE_SEO_URL . " where seo_url_key = '" . $g_db->input($key) . "'");
            if( $cache_array = $g_db->fetch_array($cache_query) ) {
              $g_db->query("delete from " . TABLE_SEO_CACHE . " where osc_url_key = '" . $g_db->input($cache_array['osc_url_key']) . "'");
            }
            $g_db->query("delete from " . TABLE_SEO_URL . " where seo_url_key = '" . $g_db->input($key) . "'");
            continue;
          }

          $sql_data_array = array(
            'seo_url_org' => $g_db->prepare_input($check_array['seo_url_org']),
            'seo_url_get' => $g_db->prepare_input($check_array['seo_url_get']),
            'seo_url_key' => $g_db->prepare_input($key),
            'last_modified' => 'now()',
            'seo_redirect' => '301'
          );
          $g_db->perform(TABLE_SEO_REDIRECT, $sql_data_array);
          $g_db->query("DELETE FROM " . TABLE_SEO_URL . " WHERE seo_url_key = '" . $g_db->input($key) . "'");
          $move_flag = true;
        }
      }
      if( $move_flag ) {
        $messageStack->add_session(SUCCESS_URLS_MOVED, 'success');
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    default:
      $sort_array_list = array(
        array('id' => 'seo_url_get', 'text' => TEXT_SORT_SEO_URL),
        array('id' => 'seo_url_org', 'text' => TEXT_SORT_ORG_URL),
        array('id' => 'date_added', 'text' => TEXT_SORT_DATE_ADDED),
        array('id' => 'last_modified', 'text' => TEXT_SORT_LAST_MODIFIED),
        array('id' => 'seo_url_hits', 'text' => TEXT_SORT_HITS),
        array('id' => 'seo_frequency_id', 'text' => TEXT_SORT_FREQUENCY),
      );
      $sort_by = (isset($_GET['sort_by']) ? $_GET['sort_by'] : 'seo_url_get');
      $sort_by = (isset($_POST['sort_by']) ? $_POST['sort_by'] : $sort_by);
      break;
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
          <div class="maincell wider">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
              <div><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
<?php
  if( $action == 'delete_all') {
?>
            <div class="comboHeading">
              <div><?php echo TEXT_INFO_DELETE_ALL_URLS; ?></div>
            </div>
            <div class="formButtons"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>&nbsp;<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=delete_all_confirm') . '">' . tep_image_button('button_confirm.gif', 'Truncate SEO-G URLs table') . '</a>'; ?></div>
<?php
  } elseif( $action == 'delete') {
?>
            <div class="comboHeading">
              <div><?php echo TEXT_INFO_DELETE_URLS; ?></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form("seo_reports", $g_script, 'action=delete_confirm', 'post'); ?><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_CONVERTED; ?></th>
              </tr>
<?php
    $rows = 0;
    $seo_url_query = $g_db->query("select seo_url_key, seo_url_get from " . TABLE_SEO_URL . " where seo_url_key in ('" . implode("','", $keys_array) . "') order by seo_url_get");
    while($seo_url = $g_db->fetch_array($seo_url_query) ) {
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
      echo '                      <tr class="' . $row_class . '">';
?>
                <td class="dataTableContent"><?php echo $seo_url['seo_url_get'] . tep_draw_hidden_field('mark[' . $seo_url['seo_url_key'] . ']', $seo_url['seo_url_get']); ?></td>
              </tr>
<?php
    }
?>
              <tr>
                <td class="formButtons"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>&nbsp;' . tep_image_submit('button_confirm.gif', 'Confirm deletion of the following SEO-G URLs'); ?></td>
              </tr>
            </table></form></div>
<?php
  } elseif( $action == 'edit') {
?>
            <div class="comboHeading">
              <div><?php echo TEXT_INFO_EDIT_URLS; ?></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form("seo_reports", $g_script, 'action=update', 'post'); ?><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_ORIGINAL; ?></th>
                <th><?php echo TABLE_HEADING_CONVERTED; ?></th>
                <th class="calign"><?php echo '<a href="#priority" class="input_select" title="Replicate Priority Value from the first entry to subsequent entries">' . TABLE_HEADING_PRIORITY . '</a>'; ?></th>
                <th class="calign"><?php echo '<a href="#frequency" class="combo_select" title="Replicate Frequency Value from the first entry to subsequent entries">' . TABLE_HEADING_FREQUENCY . '</a>'; ?></th>
              </tr>
<?php
    $rows = 0;
    $seo_url_query = $g_db->query("select su.seo_url_key, su.seo_url_get, su.seo_url_org, su.seo_url_priority, su.seo_frequency_id from " . TABLE_SEO_URL . " su where su.seo_url_key in ('" . implode("','", $keys_array) . "') order by su.seo_url_get");

    while($seo_url = $g_db->fetch_array($seo_url_query) ) {
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
      echo '                      <tr class="' . $row_class . '">';
?>
                <td><div class="rpad"><?php echo tep_draw_input_field('org[' . $seo_url['seo_url_key'] . ']', $seo_url['seo_url_org']); ?></div></td>
                <td><div class="rpad"><?php echo tep_draw_input_field('seo[' . $seo_url['seo_url_key'] . ']', $seo_url['seo_url_get']); ?></div></td>
                <td class="calign"><?php echo tep_draw_input_field('priority[' . $seo_url['seo_url_key'] . ']', $seo_url['seo_url_priority'], 'size="3", maxlength="3"'); ?></td>
                <td class="calign"><?php echo tep_draw_pull_down_menu('frequency[' . $seo_url['seo_url_key'] . ']', $frequency_array, $seo_url['seo_frequency_id']); ?></td>
              </tr>
<?php
    }
    $buttons = array(
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
      tep_image_submit('button_update.gif', 'Update changes for the listed SEO-G URLs')
    );
?>
            </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
<?php
  } elseif( $action == 'validate') {
    $error_flag = false;

    $osc_url_query = $g_db->query("select seo_url_key, seo_url_org, seo_url_get from " . TABLE_SEO_URL . " group by seo_url_org having count(*) > 1  order by seo_url_org");
    if( !$g_db->num_rows($osc_url_query) ) {
?>
            <div class="comboHeading">
              <div><?php echo TEXT_INFO_NO_OSC_ERRORS; ?></div>
            </div>
<?php
    } else {
      $error_flag = true;
    }
    $seo_url_query = $g_db->query("select seo_url_key, seo_url_org, seo_url_get from " . TABLE_SEO_URL . " group by seo_url_get having count(*) > 1  order by seo_url_get");
    if( !$g_db->num_rows($seo_url_query) ) {
?>
            <div class="comboHeading">
              <div><?php echo TEXT_INFO_NO_SEO_ERRORS; ?></div>
            </div>
<?php
    } else {
      $error_flag = true;
    }
    if( !$error_flag ) {
?>
            <div class="splitLine">
              <div><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_back.gif', IMAGE_CANCEL) . '</a>'; ?></div>
            </div>
<?php
    } else {
?>
            <div class="comboHeading">
              <div><?php echo TEXT_INFO_DUPLICATED_URLS; ?></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form("seo_reports", $g_script, tep_get_all_get_params('action') . 'action=delete_confirm', 'post'); ?><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th class="calign"><?php echo '<a href="#mark" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
                <th><?php echo TABLE_HEADING_ORIGINAL; ?></th>
                <th><?php echo TABLE_HEADING_CONVERTED; ?></th>
              </tr>
<?php
      $rows = 0;
      while($seo_url = $g_db->fetch_array($osc_url_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        echo '                      <tr class="' . $row_class . '">';
?>
                <td class="calign"><?php echo tep_draw_checkbox_field('mark['.$seo_url['seo_url_key'].']', 1) ?></td>
                <td><?php echo $seo_url['seo_url_org']; ?></td>
                <td><?php echo $seo_url['seo_url_get']; ?></td>
              </tr>
<?php
      }
?>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
      while($seo_url = $g_db->fetch_array($seo_url_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        echo '                      <tr class="' . $row_class . '">';
?>
                <td class="calign"><?php echo tep_draw_checkbox_field('mark['.$seo_url['seo_url_key'].']', 1) ?></td>
                <td><?php echo $seo_url['seo_url_org']; ?></td>
                <td><?php echo $seo_url['seo_url_get']; ?></td>
              </tr>
<?php
      }
?>
              <tr>
                <td colspan="3" class="formButtons"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>&nbsp;' . tep_image_submit('button_delete.gif', 'Remove selected entries'); ?></td>
              </tr>
            </table></form></div>
<?php
    }
  } else {
?>
            <div class="comboHeading">
              <div><?php echo TEXT_INFO_SEO_G; ?></div>
            </div>
<?php
    switch($sort_by) {
      case 'seo_url_get':
      case 'seo_url_org':
      case 'seo_frequency_id':
        $filter_by = 'su.' . $sort_by;
        break;
      case 'date_added':
      case 'last_modified':
      case 'seo_url_hits':
        $filter_by = 'su.' . $sort_by . ' desc';
        break;
      default:
        $filter_by = 'su.seo_url_get';
        break;

    }
    $rows = 0;
    $seo_url_query_raw = "select su.seo_url_key, su.seo_url_get, su.seo_url_org, su.seo_url_hits, su.seo_url_priority, su.date_added, su.last_modified, sf.seo_frequency_name from " . TABLE_SEO_URL . " su left join " . TABLE_SEO_FREQUENCY . " sf on (sf.seo_frequency_id=su.seo_frequency_id) order by " . $filter_by;
    $seo_url_split = new splitPageResults($seo_url_query_raw, SEO_PAGE_SPLIT);
    if( $seo_url_split->number_of_rows > 0 ) {
?>
            <div class="splitLine">
              <div class="floater"><?php echo $seo_url_split->display_count(TEXT_DISPLAY_NUMBER_OF_SEO_SCRIPTS); ?></div>
              <div class="floatend"><?php echo $seo_url_split->display_links(tep_get_all_get_params('action', 'page', 'sort_by') . 'sort_by=' . $sort_by); ?></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form("seo_reports", $g_script, 'action=delete', 'post'); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="comboHeading"><table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="140"><?php echo tep_draw_checkbox_field('google_notify', 'on') ?>&nbsp;<b>Notify Google</b></td>
                    <td><?php echo tep_image_submit('button_google_xml.gif', 'Click to generate the google sitemap from the stored SEO-G URLs', 'name="google_xml"'); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td class="comboHeading"><table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="tinysep">
<?php
      $buttons = array(
         tep_image_submit('button_delete.gif', 'Delete selected SEO-G URLs', 'name="delete"'),
         tep_image_submit('button_edit.gif', 'Edit selected SEO-G URLs','name="edit"'),
         '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=delete_all' ) . '">' . tep_image_button('button_delete_all.gif', 'Truncate recorded SEO-G URLs') . '</a>',
         '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=delete_cache' ) . '">' . tep_image_button('button_flush_cache.gif', 'Flush SEO-G Cache') . '</a>',
         tep_image_submit('button_redirect.gif', 'Move selected URLs to SEO-G Redirection table', 'name="redirect"'),
         '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=validate') . '">' . tep_image_button('button_validate.gif', 'Validate recorded SEO-G URLs') . '</a>',
      );
      echo implode('', $buttons);
?>
                    </td>
                    <td align="right"><table border="0" cellspacing="0" cellpadding="4">
                      <tr>
                        <td class="heavy"><?php echo 'Delete Older than:'; ?></td>
                        <td><?php echo tep_draw_input_field('older', '180', 'style="width: 36px;" size="3" maxlength="3"'); ?></td>
                        <td class="heavy"><?php echo 'Days'; ?></td>
                        <td><?php echo tep_image_submit('button_go.gif', 'Delete Older', 'name="older_than"'); ?></td>
                      </tr>
                    </table></td>
                    <td class="ralign"><table border="0" cellspacing="0" cellpadding="4">
                      <tr>
                        <td class="heavy"><?php echo 'Sort by:'; ?></td>
                        <td><?php echo tep_draw_pull_down_menu('sort_by', $sort_array_list); ?></td>
                        <td><?php echo tep_image_submit('button_go.gif', 'Execute selected filter', 'name="filter"'); ?></td>
                      </tr>
                    </table></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td valign="top"><table class="tabledata">
                  <tr class="dataTableHeadingRow">
                    <th class="calign"><?php echo '<a href="#mark" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
                    <th class="calign"><?php echo TABLE_HEADING_PRIORITY; ?></th>
                    <th class="calign"><?php echo TABLE_HEADING_FREQUENCY; ?></th>
                    <th class="calign"><?php echo TABLE_HEADING_HITS; ?></th>
                    <th><?php echo TABLE_HEADING_ORIGINAL; ?></th>
                    <th><?php echo TABLE_HEADING_CONVERTED; ?></th>
                    <th class="calign"><?php echo TABLE_HEADING_DATE_ADDED; ?></th>
                    <th class="calign"><?php echo TABLE_HEADING_LAST_MODIFIED; ?></th>
                  </tr>
<?php
      $seo_url_query = $g_db->query($seo_url_split->sql_query);
      $bCheck = false;
      while ($seo_url = $g_db->fetch_array($seo_url_query)) {
        $rows++;
        $seo_url['seo_url_org'] = $g_crelpath . $seo_url['seo_url_org'];
        $seo_url['seo_url_get'] = $g_crelpath . $seo_url['seo_url_get'];
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        echo '                      <tr class="' . $row_class . '">';
?>
                    <td class="calign"><?php echo tep_draw_checkbox_field('mark['.$seo_url['seo_url_key'].']', 1) ?></td>
                    <td class="calign"><?php echo $seo_url['seo_url_priority']; ?></td>
                    <td class="calign"><?php echo $seo_url['seo_frequency_name']; ?></td>
                    <td class="calign"><?php echo $seo_url['seo_url_hits']; ?></td>
                    <td><?php echo '<a href="' .  $seo_url['seo_url_org'] . '" target="_blank">' . htmlspecialchars(utf8_encode($seo_url['seo_url_org'])) . '</a>'; ?></td>
                    <td><?php echo '<a href="' .  $seo_url['seo_url_get'] . '" target="_blank">' . $seo_url['seo_url_get'] . '</a>'; ?></td>
                    <td class="calign"><?php echo tep_date_short($seo_url['date_added']); ?></td>
                    <td class="calign"><?php echo tep_date_short($seo_url['last_modified']); ?></td>
                  </tr>
<?php
      }
?>
                </table></td>
              </tr>
            </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
            <div class="listArea splitLine">
              <div class="floater"><?php echo $seo_url_split->display_count(TEXT_DISPLAY_NUMBER_OF_SEO_SCRIPTS); ?></div>
              <div class="floatend"><?php echo $seo_url_split->display_links(tep_get_all_get_params('action', 'page', 'sort_by') . 'sort_by=' . $sort_by); ?></div>
            </div>
<?php
    }
  }
?>
          </div>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
