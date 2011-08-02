<?php
/*
  $Id: gzip_compression.php,v 1.3 2003/02/11 01:31:02 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - Added fixes in the compression code
// - Removed zlib dependencies
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  function tep_check_gzip() {
    if (headers_sent() || connection_aborted() || !isset($_SERVER['HTTP_ACCEPT_ENCODING']) ) {
      return false;
    }
    if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) return 'x-gzip';
    if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false) return 'gzip';
    return false;
  }

  function tep_gzip_output($level = 5) {
    if( $encoding = tep_check_gzip() ) {
      $contents = ob_get_contents();
      $size = ob_get_length();
      ob_end_clean();
      header('Content-Encoding: ' . $encoding);

      $crc = crc32($contents);
      $contents = gzcompress($contents, $level);
      $contents = substr($contents, 0, strlen($contents) - 4);

      echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
      echo $contents;
      echo pack('V', $crc);
      echo pack('V', $size);
    } else {
      ob_end_flush();
    }
  }
?>
