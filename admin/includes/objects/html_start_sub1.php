<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Common HTML header upper section
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

  if( DEFAULT_WARNING_PASSWORD_PROTECT_REMIND == 'true' ) {
    $cfq_query = $g_db->query("select configuration_id, configuration_group_id from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_WARNING_PASSWORD_PROTECT_REMIND'");
    $cfg_array = $g_db->fetch_array($cfq_query);
    $warning_string = '<a class="headerLink" href="' . tep_href_link(FILENAME_CONFIGURATION, 'action=edit&gID=' . $cfg_array['configuration_group_id'] . '&cID=' . $cfg_array['configuration_id']) . '">' . WARNING_PASSWORD_PROTECT_REMIND . '</a>';
    $messageStack->add($warning_string, 'error', 'header');
  }

// check if the 'install' directory exists, and warn of its existence
  if( DEFAULT_WARNING_INSTALL_EXISTS == 'true' ) {
    $check_dir = DIR_FS_CATALOG . 'install';
    if( file_exists($check_dir) ) {
      $install_string = sprintf(WARNING_INSTALL_DIRECTORY_EXISTS, $check_dir);
      $messageStack->add($install_string, 'error', 'header');
    }
  }

  // set which precautions should be checked
  define('WARN_SESSION_AUTO_START', 'true');

  // check session.auto_start is disabled
  if( function_exists('ini_get') && WARN_SESSION_AUTO_START == 'true' ) {
    if (ini_get('session.auto_start') == '1') {
      $messageStack->add(WARNING_SESSION_AUTO_START, 'error', 'header');
    }
  }

  if (function_exists('ini_get') && ((bool)ini_get('file_uploads') == false) ) {
    $messageStack->add(WARNING_FILE_UPLOADS_DISABLED, 'warning', 'header');
  }

// check if the backup directory exists
  if( DEFAULT_WARNING_CATALOG_IMAGES_WRITE == 'true' ) {
    $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
    if( !is_writeable($images_path) ) {
      $messageStack->add(WARNING_IMAGE_UPLOADS_DISABLED, 'warning', 'header');
    }
  }

// check if the backup directory exists
  if( DEFAULT_WARNING_CATALOG_THUMBS_WRITE == 'true' ) {
    if( !is_writeable(DIR_FS_CATALOG . FLY_THUMB_FOLDER) ) {
      $messageStack->add(WARNING_IMAGE_THUMBS_DISABLED, 'warning', 'header');
    }
  }

// Setup privacy header
  header('P3P: CP="NOI ADM DEV PSAi COM NAV STP IND"');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<base href="<?php echo $g_relpath; ?>"></base>
<title><?php echo defined('HEADING_TITLE')?HEADING_TITLE:TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="stylesheet.css" />
<script language="javascript" type="text/javascript" src="includes/general.js"></script>
<?php
  if( defined('HELP_SHOT') ) {
    $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery-1.3.2.js"></script>';
    $g_media[] = '<script type="text/javascript" src="includes/javascript/fancybox/jquery.mousewheel-3.0.2.pack.js"></script>';
    $g_media[] = '<link rel="stylesheet" type="text/css" href="includes/javascript/fancybox/jquery.fancybox-1.3.0.css" media="screen" />';
  }
  $g_plugins->invoke('html_start');
  if( !empty($g_media) ) {
    echo '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery-1.3.2.js"></script>' . "\n";
  }
  tep_output_media();
  $g_plugins->invoke('html_ready');
  tep_output_media();
?>