<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Helpdesk HTML view
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
  $id = (isset($_GET['id']) ? (int)$_GET['id'] : '');
  $ticket = (isset($_GET['ticket']) ? $g_db->prepare_input($_GET['ticket']) : '');
  $unsafe = (isset($_GET['unsafe']) ? (int)$_GET['unsafe'] : 0);

  $entry_query = $g_db->query("select body from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $g_db->input($ticket) . "' and helpdesk_entries_id = '" . (int)$id . "'");
  if( $g_db->num_rows($entry_query) ) {
    $entry_array = $g_db->fetch_array($entry_query);
    if( $unsafe == 1 ) {
      echo $entry_array['body'];
    } else {
      echo htmlspecialchars($entry_array['body']);
    }
  }
?>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>