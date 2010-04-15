<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2007 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// ---------------------------------------------------------------------------
// Common html Footer section
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
<!-- body_text_eof //-->
<?php 
  if( basename($PHP_SELF) != FILENAME_DEFAULT ) {
    if( isset($body_form) ) echo '</form>'; 
?>
      </div>
    </div>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<?php
  } else {
?>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
    </div>
<?php
  }
?>
  </div>
<?php
  $g_plugins->invoke('html_end');
  tep_output_media();
?>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>