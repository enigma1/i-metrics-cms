<?php
/*
  $Id: whos_online.php,v 1.32 2003/06/29 22:50:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: online monitor script
//----------------------------------------------------------------------------
// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// - PHP5 Register Globals off and Long Arrays Off support added
// - Transformed script for CMS, removed unrelated functions
// - Removed application globals
// - Replaced Database functions with Database Class
// - Moved HTML Header/Footer to a common section
// - HTML Body Common Sections Added
// - Added host resolution and black list databases checkup
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  require('includes/application_top.php');
  $xx_mins_ago = (time() - MAX_WHOS_ONLINE_TIME);

  $info = (isset($_GET['info']) ? $g_db->prepare_input($_GET['info']) : '');
  $ip_check = (isset($_POST['ip_check']) ? $g_db->prepare_input($_POST['ip_check']) : '');

  switch($action) {
    case 'delete_all':
      $g_db->query("truncate table " . TABLE_WHOS_ONLINE);
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    case 'check_ip':
      require_once(DIR_FS_FUNCTIONS . 'helpdesk.php');

      $ip = $ip_check;
      $ip_check_result = '';

      if( empty($ip_check) ) break;
      $tmp_array = explode('.', $ip);
      if( count($tmp_array) != 4 ) break;
      for($i=0; $i<4; $i++) {
        if( $tmp_array[$i] < 0 || $tmp_array[$i] > 255 ) {
          $ip = '';
          break;
        }
      }

      if( empty($ip) ) break;
      $info_array = array();
      $info_array[] = tep_check_ip_blacklist($ip, 'bl.spamcop.net');

      $info_array[] = tep_check_ip_blacklist($ip, 'cdl.anti-spam.org.cn');
      $info_array[] = tep_check_ip_blacklist($ip, 'cblplus.anti-spam.org.cn');
      $info_array[] = tep_check_ip_blacklist($ip, 'sbl.nszones.com');

      $tmp_result = tep_check_ip_blacklist($ip, 'dnsbl.sorbs.net');
      $info_array[] = $tmp_result;
      if( isset($tmp_result['dnsbl.sorbs.net']) && $tmp_result['dnsbl.sorbs.net'] != '127.0.0.10' ) {
      //  $info_array[] = $tmp_result;
      }

      $info_array[] = tep_check_ip_blacklist($ip, 'cbl.abuseat.org');
      $info_array[] = tep_check_ip_blacklist($ip, 'dnsbl.njabl.org');
      $info_array[] = tep_check_ip_blacklist($ip, 'sbl-xbl.spamhaus.org');

      $g_plugins->invoke('ip_blacklist', $info_array);

      $result = '';
      for( $i=0, $j=count($info_array); $i<$j; $i++ ) {
        foreach($info_array[$i] as $key => $value ) {
          $result .= TEXT_INFO_LISTED_IN . ' ' . $key . ' [' . $value . ']' . '<br />';
        }
      }
      if( !empty($result) ) {
        $ip_check_result = '<font color="#EE0000">' . $result . '</font>';
      } else {
        $ip_check_result = TEXT_INFO_NOTHING_FOUND;
      }

      break;
    default:
      break;
  }
  // remove entries that have expired
  $g_db->query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
          <div class="maincell wider">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
              <div><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
            <div class="comboHeading"><?php echo tep_draw_form('form_generic_text', $g_script, 'action=check_ip'); ?><div>
              <div><?php echo TEXT_INFO_CHECK_IP; ?></div>
              <div><?php echo tep_draw_input_field('ip_check', $ip_check); ?></div>
<?php
  if( !empty($ip_check_result) && !empty($ip_check) ) {
?>
              <div><?php echo $ip_check_result; ?></div>
<?php
  }
?>
            </div></form></div>
<?php
  $whos_online_query = $g_db->query("select full_name, ip_address, time_entry, time_last_click, last_page_url, session_id, cookie_sent from " . TABLE_WHOS_ONLINE . " order by cookie_sent desc, time_last_click desc");
  $total_count = $g_db->num_rows($whos_online_query);
  if( $total_count ) {
?>
            <div class="comboHeading">
              <div class="dataTableRowImpactBorder floater" style="width: 24px;">&nbsp;</div>
              <div class="floater"><b>&nbsp;-&nbsp;Signifies Spider presense</b></div>
            </div>
            <div class="formArea"><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_FULL_NAME; ?></th>
                <th><?php echo TABLE_HEADING_IP_ADDRESS; ?></th>
                <th><?php echo TABLE_HEADING_HOST; ?></th>
                <th><?php echo TABLE_HEADING_COOKIE; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_ENTRY_TIME; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_LAST_CLICK; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_ONLINE; ?></th>
                <th><?php echo TABLE_HEADING_LAST_PAGE_URL; ?></th>
              </tr>
<?php
    while( $whos_online = $g_db->fetch_array($whos_online_query) ) {
      $time_online = (time() - $whos_online['time_last_click']);
      $whos_online['ip_address'] = $g_http->ip2s($whos_online['ip_address']);
      $class= 'dataTableRow';

      $whos_online['full_name'] = str_replace('Visitor:', 'Guest:<br />', $whos_online['full_name']);
      $whos_online['full_name'] = str_replace('Bot:', 'Bot:<br>', $whos_online['full_name']);

      if( isset($info) && !empty($info) && $whos_online['session_id'] == $info ) {
        echo '              <tr class="dataTableRowSelected">' . "\n";
      } else {
        echo '              <tr class="' . $class . ' row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('info', 'action') . 'info=' . $whos_online['session_id'], 'NONSSL') . '">' . "\n";
      }
?>
                <td><?php echo $whos_online['full_name']; ?></td>
                <td><?php echo $whos_online['ip_address']; ?></td>
                <td>
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
                <td><?php echo ($whos_online['cookie_sent'])?'Enabled':'Disabled'; ?></td>
                <td class="calign"><?php echo date('H:i:s', $whos_online['time_entry']); ?></td>
                <td class="calign"><?php echo date('H:i:s', $whos_online['time_last_click']); ?></td>
                <td class="calign"><?php echo gmdate('H:i:s', $time_online); ?></td>
                <td><?php $whos_online['last_page_url']; ?></td>
              </tr>
<?php
    }
    $buttons = array(
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=host') . '">' . tep_image_button('button_hosts.gif', 'Resolve Host Information - May take some time') . '</a>',
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=delete_all' ) . '">' . tep_image_button('button_delete_all.gif', 'Flush Whos Online Information') . '</a>'
    );
?>
            </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></div>
<?php
  }
?>
            <div class="listArea splitLine">
              <div class="floater"><?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, min(1, $total_count), $total_count, $total_count); ?></div>
            </div>
          </div>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
