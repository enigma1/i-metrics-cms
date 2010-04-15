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
        </div>
        <div class="rightsize extend floater" id="rightpane"><?php include(DIR_WS_TEMPLATE . 'column_right.tpl'); ?></div>
      </div>

      <div class="cleaner">
<!-- footer //-->
<?php require(DIR_WS_TEMPLATE . 'footer.tpl'); ?>
<!-- footer_eof //-->
      </div>

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
