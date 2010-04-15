<?php
/*
  $Id: application_bottom.php,v 1.8 2002/03/15 02:40:38 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

// close session (store variables)
  $g_session->close(false);

  if( STORE_PAGE_PARSE_TIME == 'true' || DISPLAY_PAGE_PARSE_TIME == 'true') {
    if(!isset($logger) || !is_object($logger)) $logger = new logger;
    echo $logger->timer_stop(DISPLAY_PAGE_PARSE_TIME);
  }

  if( GZIP_COMPRESSION == 'true' && isset($ext_zlib_loaded) && $ext_zlib_loaded == true && $ini_zlib_output_compression < 1 ) {
    include('functions/gzip_compression.php');
    tep_gzip_output(GZIP_LEVEL);
  }
?>