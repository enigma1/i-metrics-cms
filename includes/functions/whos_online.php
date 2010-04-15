<?php
/*
  $Id: whos_online.php,v 1.11 2003/06/20 00:12:59 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2008 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Catalog: Whos Online functions
//----------------------------------------------------------------------------
// I-Metrics Layer
//----------------------------------------------------------------------------
// - Modified Script to use the IP address instead of the session
// - Added cookie sent column
// - Corrected visitor time entry
// - Added bot support
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  function tep_update_whos_online() {
    global $g_session, $g_db;

    if( !empty($g_session->spider_name) ) {
      $wo_full_name = 'Bot: ' . $g_session->spider_name;
    } else {
      $wo_full_name = 'Visitor:' . $g_session->user_agent;
    }

    $wo_session_id = $g_session->id();
    $wo_ip_address = getenv('REMOTE_ADDR');
    $wo_last_page_url = getenv('REQUEST_URI');

    $current_time = time();
    $xx_mins_ago = ($current_time - MAX_WHOS_ONLINE_TIME);

    // remove entries that have expired
    $g_db->query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

    $stored_query = $g_db->query("select count(*) as count from " . TABLE_WHOS_ONLINE . " where ip_address = '" . $g_db->filter($wo_ip_address) . "'");
    $stored_array = $g_db->fetch_array($stored_query);
    if ($stored_array['count'] > 0) {
      $sql_data_array = array(
                              'full_name' => $g_db->prepare_input($wo_full_name),
                              'session_id' => $g_db->prepare_input($wo_session_id),
                              'time_last_click' => $g_db->prepare_input($current_time),
                              'last_page_url' => $g_db->prepare_input($wo_last_page_url),
                              'cookie_sent' => $g_session->has_cookie()?1:0
                             );
      $g_db->perform(TABLE_WHOS_ONLINE, $sql_data_array, 'update', "ip_address = '" . $g_db->filter($wo_ip_address) . "'");
    } else {
      $sql_data_array = array(
                              'full_name' => $g_db->prepare_input($wo_full_name),
                              'ip_address' => $g_db->prepare_input($wo_ip_address),
                              'time_entry' => $g_db->prepare_input($current_time),
                              'time_last_click' => $g_db->prepare_input($current_time),
                              'last_page_url' => $g_db->prepare_input($wo_last_page_url),
                              'session_id' => $g_db->prepare_input($wo_session_id),
                              'cookie_sent' => $g_session->has_cookie()?1:0
                             );
      $g_db->perform(TABLE_WHOS_ONLINE, $sql_data_array, 'insert');
    }
  }
?>
