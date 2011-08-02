<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Common header
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
    <div id="header">
      <div class="bounder lalign">
        <div class="floater" style="padding: 12px 0px 0px 20px;"><?php echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_IMAGES . 'design/logo.png', STORE_NAME) . '</a>'; ?></div>
        <div class="floatend" style="padding: 10px 10px 0px 0px;"><h1><?php echo HEADING_MANAGE_SITE; ?></h1></div>
      </div>
    </div>
<?php
  $msg->output('header');
?>
