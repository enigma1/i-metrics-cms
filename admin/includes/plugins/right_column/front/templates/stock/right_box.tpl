<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front Plugin: Right Column template form
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
            <div class="infoBox">
<?php
  if( !empty($name) ) {
?>
              <div class="infoBoxHeading boxpadding"><?php echo $name; ?></div>
<?php
  }
  if( !empty($text) ) {
?>

              <div class="infoBoxContents"><?php echo $text; ?></div>
<?php
  }
?>
            </div>
