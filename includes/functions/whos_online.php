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
    extract(tep_load('http_validator', 'database', 'sessions'));

    if( $http->bot ) {
      $wo_full_name = 'Bot: ' . $http->bot_name;
    } else {
      $wo_full_name = 'Visitor:' . $http->ua;
    }

    $wo_session_id = $cSessions->id;
    $wo_ip_address = $http->ip_string;
    $wo_last_page_url = getenv('REQUEST_URI');

    $current_time = time();
    $xx_mins_ago = ($current_time - MAX_WHOS_ONLINE_TIME);

    // remove entries that have expired
    $db->query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

    $stored_query = $db->query("select count(*) as count from " . TABLE_WHOS_ONLINE . " where ip_address = '" . $db->filter($wo_ip_address) . "'");
    $stored_array = $db->fetch_array($stored_query);
    if ($stored_array['count'] > 0) {
      $sql_data_array = array(
        'full_name' => $db->prepare_input($wo_full_name),
        'session_id' => $db->prepare_input($wo_session_id),
        'time_last_click' => $db->prepare_input($current_time),
        'last_page_url' => $db->prepare_input($wo_last_page_url),
        'cookie_sent' => $cSessions->has_cookie()?1:0
      );
      $db->perform(TABLE_WHOS_ONLINE, $sql_data_array, 'update', "ip_address = '" . $db->filter($wo_ip_address) . "'");
    } else {
      $sql_data_array = array(
        'full_name' => $db->prepare_input($wo_full_name),
        'ip_address' => $db->prepare_input($wo_ip_address),
        'time_entry' => $db->prepare_input($current_time),
        'time_last_click' => $db->prepare_input($current_time),
        'last_page_url' => $db->prepare_input($wo_last_page_url),
        'session_id' => $db->prepare_input($wo_session_id),
        'cookie_sent' => $cSessions->has_cookie()?1:0
      );
      $db->perform(TABLE_WHOS_ONLINE, $sql_data_array, 'insert');
    }
  }
?>
