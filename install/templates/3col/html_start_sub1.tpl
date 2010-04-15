<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: HTML Upper Section template part
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<?php
//-MS- META-G Added
  if( file_exists(DIR_WS_CLASSES . 'meta_g.php') && defined('META_DEFAULT_ENABLE') && META_DEFAULT_ENABLE == 'true' ) {
    require(DIR_WS_CLASSES . 'meta_g.php');
    $cMeta = new metaG();
    echo $cMeta->get_meta_tags($_GET);
  } else {
    echo '<title>' . TITLE . '</title>' . "\n";
  }
//-MS- META-G Added
?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<base href="<?php echo $g_relpath; ?>"></base>
<link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_TEMPLATE . 'stylesheet.css'; ?>" />
<?php
  $g_plugins->invoke('html_start');
  if( !empty($g_media) ) {
    echo '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery-1.3.2.js"></script>' . "\n";
  }
  tep_output_media();
  $g_plugins->invoke('html_ready');
  if( !empty($g_media) ) {
    echo '<script language="javascript" type="text/javascript">' . "\n";
    echo '$(document).ready(function() {' . "\n";
    tep_output_media();
    echo '});' . "\n";
    echo '</script>' . "\n";
  }
?>