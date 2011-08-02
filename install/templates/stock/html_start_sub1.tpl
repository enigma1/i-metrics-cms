<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2011 Asymmetric Software - Innovation & Excellence
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
  if( is_file(DIR_FS_CLASSES . 'meta_g.php') && defined('META_DEFAULT_ENABLE') && META_DEFAULT_ENABLE == 'true' ) {
    require(DIR_FS_CLASSES . 'meta_g.php');
    $cMeta = new metaG();
    echo $cMeta->get_meta_tags($_GET);
  } else {
    echo '<title>' . TITLE . '</title>' . "\n";
  }
//-MS- META-G Added
?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<base href="<?php echo $cDefs->relpath; ?>"></base>
<link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_TEMPLATE . 'stylesheet.css'; ?>" />
<?php
  // Plugins that require side resources, stylesheets, js includes or any type of resource
  // Should hook the html_start and include their resource in the $cDefs->media global array.
  // For example if your plugin has an extra stylesheet style2.css then in your plugin code in html_start
  // $cDefs->media[] = '<link rel="stylesheet" type="text/css" href="' . DIR_WS_TEMPLATE . 'my_plugin/style2.css" />';
  // The next line collects the resources from the plugins we are in the <head> HTML section
  $cPlug->invoke('html_start');
  // If one or more plugins require resources include the jquery resource
  if( !empty($cDefs->media) ) {
    array_unshift($cDefs->media, '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.min.js"></script>');
    //$cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.dump.js"></script>';
  }
  // Outputs the plugins resources collected from the html_start here and resets the g_media array.
  tep_output_media();

  // jQuery plugins for the document ready can use the html_ready function here
  // or hook the html_end instead (html_end is prefered for SEO purposes).
  // You only need to include the pure js code of the plugin as everything else
  // is pre-set for you from the code below:
  // Collect the js code from the plugins from the html_ready hook
  $cPlug->invoke('html_ready');
  // If there are plugins the g_media contains the js code to emit
  if( !empty($cDefs->media) ) {
    // setup the basic js code enclosure for the ready
    echo '<script language="javascript" type="text/javascript">' . "\n";
    echo '$(document).ready(function() {' . "\n";
    // Outputs the plugins js code collected from the html_ready here.
    tep_output_media();
    // Finalize/close the ready section
    echo '});' . "\n";
    echo '</script>' . "\n";
  }
?>