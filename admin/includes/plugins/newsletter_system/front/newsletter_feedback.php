<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2009 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Newsletter feedback recording
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
  extract(tep_load('history'));
  $cHistory->remove_current_page();

  $plugin = $g_plugins->get('newsletter_system');
  if( empty($plugin) ) tep_redirect();

  $action = isset($_GET['action'])?$g_db->prepare_input($_GET['action']):'';

  switch($action) {
    case 'remove':
      $plugin->newsletter_remove();
      break;
    default:
      $plugin->newsletter_record();
      $contents = '';
      tep_read_contents(DIR_FS_IMAGES . 'pixel_trans.gif', $contents);
      header('Content-type: image/gif');
      echo $contents;
      break;
  }
  $g_session->close();
?>
