<?php
/*
  $Id: application_bottom.php,v 1.8 2002/03/15 02:40:38 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

// close session (store variables)
  extract(tep_load('http_headers', 'sessions', 'logger'));

  $cSessions->close(false);
  echo $cLogger->timer_stop(DISPLAY_PAGE_PARSE_TIME);

/*
  if( GZIP_COMPRESSION == 'true' && isset($ext_zlib_loaded) && $ext_zlib_loaded == true && $ini_zlib_output_compression < 1 ) {
    require_once(DIR_FS_FUNCTIONS . 'compression.php');
    tep_gzip_output(GZIP_LEVEL);
  }
*/
  if( GZIP_COMPRESSION == 'true' ) {
    require_once(DIR_FS_FUNCTIONS . 'compression.php');
    tep_gzip_output(GZIP_LEVEL);
  }
?>