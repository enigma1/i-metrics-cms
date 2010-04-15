<?php
/*
  $Id: whos_online.php,v 1.32 2003/06/29 22:50:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  $xx_mins_ago = (time() - MAX_WHOS_ONLINE_TIME);

  $action = (isset($_GET['action']) ? $g_db->prepare_input($_GET['action']) : '');
  $info = (isset($_GET['info']) ? $g_db->prepare_input($_GET['info']) : '');
  switch($action) {
    case 'delete_all':
      $g_db->query("truncate table " . TABLE_WHOS_ONLINE . "");
      tep_redirect(tep_href_link(FILENAME_WHOS_ONLINE, tep_get_all_get_params(array('action')) ));
      break;
    default:
      break;
  }
// remove entries that have expired
  $g_db->query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php require('includes/objects/html_start_sub2.php'); ?>
          <div class="maincell" style="width: 100%">
            <div class="comboHeading">
              <div class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
            <div class="splitLine">
              <div class="dataTableRowImpactBorder" style="float: left; width: 24px;">&nbsp;</div>
              <div class="smallText" style="float: left;"><b>&nbsp;-&nbsp;Signifies Spider presense</b></div>
            </div>
            <div class="listArea"><table border="0" width="100%" cellspacing="1" cellpadding="3">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FULL_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_IP_ADDRESS; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_HOST; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COOKIE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ENTRY_TIME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAST_CLICK; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ONLINE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LAST_PAGE_URL; ?>&nbsp;</td>
              </tr>
<?php
  $whos_online_query = $g_db->query("select full_name, ip_address, time_entry, time_last_click, last_page_url, session_id, cookie_sent from " . TABLE_WHOS_ONLINE . " order by cookie_sent desc, time_last_click desc");
  $total_count = $g_db->num_rows($whos_online_query);
  while ($whos_online = $g_db->fetch_array($whos_online_query)) {
    $time_online = (time() - $whos_online['time_last_click']);
    $class= 'dataTableRow';

    $whos_online['full_name'] = str_replace('Visitor:', 'Guest:<br>', $whos_online['full_name']);
    $whos_online['full_name'] = str_replace('Bot:', 'Bot:<br>', $whos_online['full_name']);

    if( isset($info) && !empty($info) && $whos_online['session_id'] == $info ) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected">' . "\n";
    } else {
      echo '              <tr class="' . $class . '" onclick="document.location.href=\'' . tep_href_link(FILENAME_WHOS_ONLINE, tep_get_all_get_params(array('info', 'action')) . 'info=' . $whos_online['session_id'], 'NONSSL') . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $whos_online['full_name']; ?></td>
                <td class="dataTableContent"><?php echo $whos_online['ip_address']; ?></td>
                <td class="dataTableContent">
<?php
    if( $action == 'host' ) {
      $result = gethostbyaddr($whos_online['ip_address']);
      if( $result == $whos_online['ip_address'] )
        echo 'Cannot Resolve';
      else
        echo $result;
    } else {
      echo 'N/A';
    }
?>
                </td>
                <td class="dataTableContent"><?php echo ($whos_online['cookie_sent'])?'Enabled':'Disabled'; ?></td>
                <td class="dataTableContent" align="center"><?php echo date('H:i:s', $whos_online['time_entry']); ?></td>
                <td class="dataTableContent" align="center"><?php echo date('H:i:s', $whos_online['time_last_click']); ?></td>
                <td class="dataTableContent" align="center"><?php echo gmdate('H:i:s', $time_online); ?></td>
                <td class="dataTableContent"><?php if (eregi('^(.*)' . $g_session->name() . '=[a-f,0-9]+[&]*(.*)', $whos_online['last_page_url'], $array)) { echo $array[1] . $array[2]; } else { echo $whos_online['last_page_url']; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="9"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, min(1, $total_count), $total_count, $total_count); ?></td>
                    <td class="smallText" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_WHOS_ONLINE, tep_get_all_get_params(array('action')) . 'action=host' ) . '">' . tep_image_button('button_hosts.gif', 'Resolve Host Information - May take some time') . '&nbsp;<a href="' . tep_href_link(FILENAME_WHOS_ONLINE, tep_get_all_get_params(array('action')) . 'action=delete_all' ) . '">' . tep_image_button('button_delete_all.gif', 'Flush Whos Online Information') . '</a>'; ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></div>
          </div>
<?php require('includes/objects/html_end.php'); ?>
