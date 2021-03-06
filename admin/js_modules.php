<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Ajax callback modules handler/switch do not call it directly
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
------------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
//
*/
  require('includes/application_top.php');
  $module = (isset($_POST['module']) ? $g_db->prepare_input($_POST['module'], true) : '');
  $module = tep_create_safe_string($module, '', "[^0-9a-z\-_]");
  $file_module = 'js_' . $module . '.php';
  if( !empty($module) && is_file(DIR_FS_MODULES . $file_module) ) {
    require(DIR_FS_MODULES . $file_module);
  } else {
    echo 'invalid module request ' . $module;
  }
?>
<?php require(DIR_FS_INCLUDES . 'application_bottom.php'); ?>