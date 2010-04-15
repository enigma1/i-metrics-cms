<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Generic Comments HTML view
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
  $gcID = (isset($_GET['gcID']) ? (int)$_GET['gcID'] : '');
  $unsafe = (isset($_GET['unsafe']) ? (int)$_GET['unsafe'] : 0);

  $entry_query = $g_db->query("select comments_body from " . TABLE_COMMENTS . " where comments_id = '" . (int)$gcID . "'");
  if( $g_db->num_rows($entry_query) ) {
    $entry_array = $g_db->fetch_array($entry_query);
    if( $unsafe == 1 ) {
      echo $entry_array['comments_body'];
    } else {
      echo htmlspecialchars($entry_array['comments_body']);
    }
  }
?>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>