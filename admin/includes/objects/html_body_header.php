<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// ---------------------------------------------------------------------------
// Common html body header section
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  if( isset($body_form) ) {
    echo $body_form;
  }
  if( !isset($heading_row) ) {
?>
    <div class="comboHeading">
      <div class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
    </div>
<?php
  }
?>