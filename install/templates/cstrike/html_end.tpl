<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: HTML Closing section
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
  $g_plugins->invoke('html_main_content_end');
?>
          </div>
<!-- footer //-->
<?php require(DIR_WS_TEMPLATE . 'footer.tpl'); ?>
<!-- footer_eof //-->
        </div>
        <div class="rightsize floatend" id="rightpane"><?php include(DIR_WS_TEMPLATE . 'column_right.tpl'); ?></div>
        <div class="leftsize floater" id="leftpane" style="margin-left: -722px;"><?php include(DIR_WS_TEMPLATE . 'column_left.tpl'); ?></div>
      </div>
      <div class="decol coffset cleaner">
        <div class="b1"></div>
        <div class="b2" style="margin-left: 1px; margin-right: 1px;"></div>
        <div class="b2" style="margin-left: 2px; margin-right: 2px;"></div>
        <div class="b1" style="margin-left: 4px; margin-right: 4px;"></div>
        <div class="b1" style="margin-left: 6px; margin-right: 6px;"></div>
      </div>
    </div>
    <div class="midsize negate" style="margin: 57px 0px 0px 216px;">
<!-- header //-->
<?php require(DIR_WS_TEMPLATE . 'header.tpl'); ?>
<!-- header_eof //-->
    </div>
<?php
  $g_plugins->invoke('html_content_end');
?>
  </div>
<?php
  $g_plugins->invoke('html_end');
  tep_output_media();
?>
</body>
<!-- body_eof //-->
</html>
