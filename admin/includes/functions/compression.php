<?php
/*
  $Id: gzip_compression.php,v 1.3 2003/02/11 01:31:02 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  function tep_check_gzip() {
    if (headers_sent() || connection_aborted()) {
      return false;
    }
    if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) return 'x-gzip';
    if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false) return 'gzip';
    return false;
  }

/* $level = compression level 0-9, 0=none, 9=max */
  function tep_gzip_output($level = 5) {
    if ($encoding = tep_check_gzip()) {
      $contents = ob_get_contents();
      ob_end_clean();

      header('Content-Encoding: ' . $encoding);

      $size = strlen($contents);
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

  function tep_decompress($filename, $target) {
    $result_array = array();

    if( !file_exists($filename) ) {
      $result_array[] = 'File does not exist';
      return $result_array;
    }

    $cZip = new pkunzip();
    $result = $cZip->Open($filename);
    if( !$result ) {
      $result_array[] = 'Invalid ZIP File or the Archive is corrupted';
      return $result_array;
    }

    $target = rtrim($target, ' /') . '/';
    $cZip->SetOption(ZIPOPT_FILE_OUTPUT, true); // save data to files, instead reading to memory
    $cZip->SetOption(ZIPOPT_OUTPUT_PATH, $target); // where to save the files, include trailing /
    $cZip->SetOption(ZIPOPT_OVERWRITE_EXISTING, true); // overwrite files with the same name

    $result = $cZip->Read();
    if( !$result ) {
      $result_array[] = 'Could not Read ZIP File check the archive';
      return $result_array;
    }

    if( !count($cZip->files) ) {
      $result_array[] = 'There are no files in the ZIP archive';
      return $result_array;
    }

    foreach($cZip->files as $file) {
      if( $file->error != E_NO_ERROR) {
        $result_array[] = $file->error;
      }
    }
    return $result_array;
  }

?>
