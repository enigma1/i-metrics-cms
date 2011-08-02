<?php
/*
// Project:     PHPUnzip: A PHP class to read and extract zip archives in a stream based manner<br />
// File:        PHPUnzip.class.php<br />
//
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.<br />
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.<br />
//
// You should have received a copy of the GNU Lesser General Public
// License along with this library; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA<br />
//
// <b>TODO:</b><br />
// Add support for PKZip Encryption to extract password protected files<br />
// Add support for other other encryption methods.<br />
// Check for ZIP64 extensions.<br />
// Add ability to read file from a string instead of a file.<br />
//
// @example example.php PHPUnzip Example Source Code
// @link http://www.NeoProgrammers.com NeoProgrammers
// @link http://www.neoprogrammers.com/download.php?file=PHPUnzip.zip Download Latest Version
// @link http://www.neoprogrammers.com/PHPUnzip_Docs/ Online Documentation
// @copyright 2007 Drew Phillips
// @author drew010 <drew@drew-phillips.com>
// @version 1.0 (June 21 2007)
// @package PHPUnzip
*/
  // Error Constants
  //
  define('E_NO_ERROR', -1);              // No error has been encountered
  define('E_NOOPEN', 0);                 // Unable to open the zip file for reading
  define('E_NOTZIP', 1);                 // The file did not appear to be a zip file
  define('E_UNEXPECTED_END', 2);         // Unexpected end of file encountered
  define('E_EMPTY', 3);                  // Empty zip file
  define('E_DATA_ERROR', 4);             // Unexpected or malformed data
  define('E_FILE_ENCRYPTED', 5);         // The file was encrypted with a method not supported by this library
  define('E_CRC_MISMATCH', 6);           // The extracted file's CRC did not match the stored CRC
  define('E_METHOD_NOT_SUPPORTED', 7);   // Data decompression method not supported
  define('E_INFLATE_ERROR', 8);          // An error occured in the gzinflate function
  define('E_BZIP_ERROR', 9);             // An error occured in the bzdecompress function
  define('E_NO_FILE', 10);               // No ZIP file was opened

  //
  // Processing Option Constants
  //
  // Output the contents of the zip file to individual files and folders based on the file structure in the zip
  define('ZIPOPT_FILE_OUTPUT', 1);
  // Only applies when ZIPOPT_FILE_OUTPUT is set, whether or not to overwrite existing files.
  define('ZIPOPT_OVERWRITE_EXISTING', 2);
  // Only applies when ZIPOPT_FILE_OUTPUT is set, where to extract the files to (Must be writeable!)
  define('ZIPOPT_OUTPUT_PATH', 3);
  // Extract only files specified in the limit_array
  define('ZIPOPT_LIMIT_FILES', 4);

 // File States Processing Constants
  define('S_FILE_HEADER',  1);           // Ready to read a local file header
  define('S_FILE_HEADER_DATA', 2);       // Ready to read the file header information
  define('S_FILE_FILENAME', 3);          // Ready to read the filename from the file header
  define('S_FILE_EXTRA', 4);             // Check for and read any data in the extra field
  define('S_FILE_DATA', 5);              // Read the file data itself
  define('S_DATA_DESCRIPTOR', 6);        // Check for and read from the file data descriptor
  define('S_FILE_PROCESSED', 7);         // File has been processed completely
  define('S_CENTRAL_DIRECTORY', 8);      // Read the central directory record
  define('S_CD_FILENAME', 9);            // Read the filename from the central directory
  define('S_CD_EXTRA', 10);              // Read extra data from the central directory
  define('S_CD_FCOMMENT', 11);           // Read the file comment from the central directory
  define('S_END_CENTRAL', 12);           // End of central directory
  define('S_EOF', 13);                   // At end of file or end of zip structure
  define('S_ERROR', 14);                 // In an error state and will not read anymore

/**
 * A stream based PHP 4 class for reading the contents of a ZIP Archive.<br />
 * Supports no compression, deflate, and bzip storage modes.<br />
 * Inspiration derived from the SimpleUnzip class contributed to phpMyAdmin by Holger Boskugel <vbwebprofi@gmx.de>
 *
 * @version    1.0
 * @author     Drew Phillips <drew@drew-phillips.com>
 * @package    PHPUnzip
 * @subpackage Unzip
 * @uses       ZipFileEntry
 *
 */
  class pkunzip {
    /**
     * Class constructor - Initialize variables
     *
     * @return ReadZip
     */
    function pkunzip() {
      // The current state of file processing
      $this->state = S_ERROR;
      // Indicates if a zip file was successfully opened for reading
      $this->open            = false;
      // File pointer to the current location in the zip file
      $this->fp              = false;

      // Array of {@link ZipFileEntry} objects representing the files and directories in the archive
      $this->files           = array();

      // The ZIP file comment text, if any
      $this->comment         = '';

      // If in a break state and should break from the current operation. Means an error was encountered
      $this->break           = false;
      //The error encountered while parsing the file
      $this->error           = E_NO_ERROR;
      //The error message associated with the error
      $this->error_str       = '';

      $this->limit_array     = array();

      //Minimum version required to extract
      $this->min_version     = '';

      // Output contents to file or leave in file object
      $this->read_to_file             = false;
      // Overwrite existing files
      $this->overwrite_existing_files = false;
      // Where to output the files
      $this->output_file_path         = './';
    }

    /**
     * Set an option for the ZIP file processing
     *
     * @param int $long_option    The option constant to set
     * @param mixed $value        The value to set
     * @return boolean            true if the value was set, false if not
     */
    function SetOption($long_option, $value) {
      switch($long_option) {
        //case ZIPOPT_READSIZE: $this->read_chunk_size = $value; break;
        case ZIPOPT_FILE_OUTPUT: $this->read_to_file = $value == true; break;
        case ZIPOPT_OVERWRITE_EXISTING: $this->overwrite_existing_files = $value == true; break;
        case ZIPOPT_OUTPUT_PATH: $this->output_file_path = $value; break;
        case ZIPOPT_LIMIT_FILES: $this->limit_array = $value; break;

        default: return false;
      }
      return true;
    }

    /**
     * Open a zip file for reading
     * Note: Individual zip file entries can contain errors and this can still return true
     *
     * @param string $file  The ZIP file to open
     * @return boolean  true if file was opened, false on error
     */
    function Open($file) {
      $this->fp = @fopen($file, 'rb');
      if (!$this->fp) {
        trigger_error('Failed to open file "' . $file . '"', E_USER_WARNING);
        $this->open = false;
        $this->error = E_NOOPEN;
        $this->error_str = 'Failed to open file';
        return false;
      }
      $this->open = true;
      return true;
    }

    /**
     * Read the contents of the ZIP file
     * If set for file output, the ZipFileEntry object's data will be null
     *
     * @return boolean
     */
    function Read() {
      if (!$this->open) {
        $this->error = E_NO_FILE;
        $this->error = 'No ZIP file has been opened';
        return false;
      }

      $this->state = S_FILE_HEADER; // ready to read for the header
      $p           =& $this->fp;    // shorthand
      $file        = null;          // file object
      $first_read  = true;          // haven't read any data, to check for a non zip file

      while ( !feof($p) && $this->state != S_EOF ) { // read to end or until we say we have reached the end
        switch( $this->state ) {
          case S_FILE_HEADER:
            $header = fread($p, 4);  // 4 bytes for header data
            // end of file check used throughout, sets $this->break to true and the error to unexpected eof
            $this->check_end($p, 4);
            if ($this->break == true) break;

            if (strcmp($header, "\x50\x4b\x03\x04") == 0) { // local file header
              $this->state = S_FILE_HEADER_DATA;
            } else if (strcmp($header, "\x50\x4b\x01\x02") == 0) { // central directory record
              $this->state = S_CENTRAL_DIRECTORY;
            } else if (strcmp($header, "\x50\x4b\x05\x06") == 0) { // end of central directory record
              $this->state = S_END_CENTRAL;
            } else {
              if ($first_read == true) { // first 4 bytes were not a zip header
                $this->error = E_NOTZIP;
                $this->error_str = 'File does not appear to be a zipfile';
              } else { // junk data or we did something wrong somewhere else
                $this->error = E_DATA_ERROR;
                $this->error_str = 'Unexpected data encountered while reading file';
              }
              $this->state = S_EOF; // break out
            }

            $first_read = false;
            $file       = new ZipFileEntry(); // initialize for next file
            break;

          case S_FILE_HEADER_DATA:
            $data = fread($p, 26);
            $this->check_end($data, 26);
            if ($this->break == true) break;

            // get the header data
            $fields = unpack("vVER/vGPF/vCM/vMTIME/vMDATE/VCRC/VCSIZE/VUSIZE/vFNLEN/vEFLEN", $data);

            $file->crc = sprintf('%u', $fields['CRC']);

            $file->time = mktime(  
              ($fields['MTIME'] >> 11) & 0x0f,
              ($fields['MTIME'] >> 5)  & 0x1f,
              $fields['MTIME'] & 0x0f,
              ($fields['MDATE'] >>  5) & 0x0f,
              $fields['MDATE'] & 0x1f,
              (($fields['MDATE'] >>  9) & 0x7f) + 1980
            );

            $file->size              = $fields['USIZE'];
            $file->compressed_size   = $fields['CSIZE'];

            $this->min_version = $fields['VER'];

            if ($fields['USIZE'] == 0) {
              $file->compression_ratio = 0;
            } else {
              $file->compression_ratio = number_format( ($fields['CSIZE'] / $fields['USIZE']) * 100, 2);
            }

            $this->state = S_FILE_FILENAME; // read file header, ready to get the file name
            break;

          case S_FILE_FILENAME:
            $fname = fread($p, $fields['FNLEN']);
            $this->check_end($fname, $fields['FNLEN']);
            if ($this->break == true) break;

            // set up path and file name
            $file->name = basename($fname);
            $file->path = (dirname($fname) == '.' || dirname($fname) == '') ? '' : dirname($fname);

            $file->full = $file->path;
            if( !empty($file->full) ) {
              $file->full .= '/';
            }
            $file->full .= $file->name;

            $this->state = S_FILE_EXTRA; // extra data?

            break;

          case S_FILE_EXTRA:
            if ($fields['EFLEN'] > 0) {
              $extra = fread($p, $fields['EFLEN']);
              $this->check_end($extra, $fields['EFLEN']);
              if ($this->break == true) break;
            } else {
              $extra = '';
            }
            $this->state = S_FILE_DATA; // read the file itself
            break;

          case S_FILE_DATA:
            if ($fields['CSIZE'] > 0) { // there is file data
              $file->data = fread($p, $fields['CSIZE']);
              $this->check_end($file->data, $fields['CSIZE']);

              if ($fields['GPF'] & 0x01) { // File is encrypted
                $file->error = E_FILE_ENCRYPTED;


                $tmp = substr($file->data, 0, 12);
                for ($i = 0; $i < 12; ++$i) echo dechex(ord($tmp{$i})) . ' ';
                echo "encrypted buffer\n";
                //0C C9 34 67 C7 61 F6 1C D0 F0 06 81
                $this->InitPassword('password');
                $this->PKEncDecryptHeader( substr($file->data, 0, 12 ) );
                echo dechex($file->crc+0) . " file crc\n";
                echo "\n";


                $file->data = substr($file->data, 12);
                for ($i = 0; $i < strlen($file->data); ++$i) {
                  $c   = $file->data{$i};
                  $tmp = ord($c) ^ $this->PKEncDecryptByte();
                  $this->PKEncUpdateKeys($tmp);
                  $file->data{$i} = chr($tmp);
                }
                echo $file->data;

                exit;

              } else if ($fields['CM'] == 8) { // inflate

                if (extension_loaded('zlib')) { // check for gzinflate
                  $file->data = @gzinflate($file->data);
                  if ($file->data === false) { // error during inflation
                    $file->data = null;
                    $file->error = E_INFLATE_ERROR;
                  }
                } else {
                  $file->error = E_METHOD_NOT_SUPPORTED;
                }

              } else if ($fields['CM'] == 12) { // bzip

                if (extension_loaded('bz2')) { // check for bzdecompress
                  $file->data = @bzdecompress($file->data);
                  if (!is_string($file->data)) { // error returns an integer
                    $file->data == null;
                    $file->error = E_BZIP_ERROR;
                  }
                } else {
                  $file->error = E_METHOD_NOT_SUPPORTED;
                }

              }

              if ($file->error == E_NO_ERROR) { // there was no file error, encryption, inflate error, check crc
                if ( sprintf('%u', crc32($file->data)) != sprintf('%u', $fields['CRC']) ) {
                  $file->error = E_CRC_MISMATCH;
                }
              }

              if( $this->read_to_file == true ) {
                if( empty($this->limit_array) || in_array($file->full, $this->limit_array) ) {
                // write the data out and free up the memory
                  $this->WriteDataToFile($file->data, $file->name, $file->path, $file->time);
                }
                $file->data = null;
              }
            } else { /* size > 0 */
              $file->data = ''; // no file data
            }

            if ($this->break == false) { // if no errors encountered, ready to continue
              $this->state = S_DATA_DESCRIPTOR;
            }

            break;

          case S_DATA_DESCRIPTOR:
            if ($fields['GPF'] & 0x03 > 0) { // data descriptor exists, would only exist if data written to std out or something non seekable
              $desc_data = fread($p, 4);
              $this->check_end($desc_data, 4);
              if ($this->break == true) break;

              if (strcmp($desc_data, "\x50\x4b\x07\x08") == 0) { // non standard descriptor signature is present
                $desc_data = fread($p, 12); // read data and discard signature
                $this->check_end($desc_data, 12);
                if ($this->break == true) break;
              } else { // no signature
                $desc_data .= fread($p, 8); // read the remaining bytes for the descriptor
                $this->check_end($desc_data, 8);
                if ($this->break == true) break;
              }

              $desc_fields = unpack("VCRC/VCSIZE/VUSIZE", $desc_data);
            } else {
              $desc_data = '';
            }
            $this->state = S_FILE_PROCESSED;
            break;

          case S_FILE_PROCESSED:
            // completely processed the current file, add to files and move back to a header read
            if( empty($this->limit_array) || in_array($file->full, $this->limit_array) ) {
              $this->files[$file->full] = $file;
            }
            $this->state = S_FILE_HEADER;
            break;

          case S_CENTRAL_DIRECTORY:
            // read over central directory records
            $cd_fields = fread($p, 42);
            $this->check_end($cd_fields, 42);
            if ($this->break == true) break;

            // not doing much with this data as of version 1
            $cd_fields = unpack("vVER/vVEXT/vGPF/vCM/vMTIME/vMDATE/VCRC/VCSIZE/VUSIZE/vFNLEN/vEFLEN/vFCLEN/vDSTART/vFATTR/vEATTR/VOFFSET", $cd_fields);
            if ($cd_fields['FNLEN'] > 0) { $tmp = fread($p, $cd_fields['FNLEN']); $this->check_end($tmp, $cd_fields['FNLEN']); if ($this->break == true) break; }
            if ($cd_fields['EFLEN'] > 0) { $tmp = fread($p, $cd_fields['EFLEN']); $this->check_end($tmp, $cd_fields['EFLEN']); if ($this->break == true) break; }
            if ($cd_fields['FCLEN'] > 0) { $tmp = fread($p, $cd_fields['FCLEN']); $this->check_end($tmp, $cd_fields['FCLEN']); if ($this->break == true) break; }

            $this->state = S_FILE_HEADER;
            break;

        case S_END_CENTRAL:
          // last part of file
          $ecd_fields = fread($p, 18);
          $this->check_end($ecd_fields, 18);
          if ($this->break == true) break;

          // all we really care about as of version 1 is the zip file comment if any

          $ecd_fields = unpack("vDNUM/vDNUMSCD/vNUMENTRIES/vTNUMENTRIES/VCDSIZE/VOFFSET/vCLEN", $ecd_fields);
          if ($ecd_fields['CLEN'] > 0) {
            $this->comment = fread($p, $ecd_fields['CLEN']);
          }

          $this->state = S_EOF; // should be at eof, set this state to prevent error if more data is present for some reason
          break;
        }
      }

      fclose($this->fp);

      if ($this->error != E_NO_ERROR) { // no major read error was encountered
        return false;
      } else {
        return true;
      }
    }

    /**
     * Checks for premature end of data
     *
     * @access private
     * @param string $str  String read
     * @param int $length  Expected length
     */
    function check_end($str, $length) {
      if (strlen($str) < $length) {
        $this->error = E_UNEXPECTED_END;
        $this->error_str = 'Unexpected end of file';
        $this->state = S_EOF;
        $this->break = true;
      }
    }

    /**
     * Writes a file inside the ZIP to a local file
     *
     * @access private
     * @param string $data  The file data
     * @param string $name  The file name
     * @param string $path  The file path (if any)
     * @param int $time     The file mod time
     * @return boolean
     */
    function WriteDataToFile($data, $name, $path, $time = null) {
      if (substr($this->output_file_path, -1) != '/' && $this->output_file_path != '') $this->output_file_path .= '/';

      if (!is_writeable($this->output_file_path)) { // make sure we can write to the output directory
        trigger_error("Unable to write to output file path \"$this->output_file_path\"", E_USER_WARNING);
        return false;
      } else {
        $directory_string = ''; // used for paths in the zip file
        if ($path != '') {
          clearstatcache();
          $directories = explode('/', $path); // get a path for the zip file
          foreach($directories as $directory) { // iterate over each directory and concatenate the directory string
            $directory_string .= $directory;
            if( !tep_mkdir($this->output_file_path . $directory_string) ) {
              // tried to make the folder, failed, cant write this file, sorry
              trigger_error("Unable to create directory \"{$this->output_file_path}$directory_string\"", E_USER_WARNING);
              return false;
            }
            $directory_string .= '/'; // append trailing slash for next round
          }
        }

        $filename = $this->output_file_path . $directory_string . $name;
        if (file_exists($filename) && $this->overwrite_existing_files == false) { // check existance and overwrite directive
          trigger_error("File \"$filename\" already exists", E_USER_WARNING);
          return false;
        }

        $fp = @fopen($filename, 'w+b'); // open output file
        if (!$fp) {
          trigger_error("Failed to open \"{$this->output_file_path}$directory_string\" for writing", E_USER_WARNING);
          return false;
        }
        fwrite($fp, $data);
        fclose($fp);
        if ($time != null) touch($filename, $time); // set mtime
        return true;
      }
    }

    function get_file($filename) {
      if( isset($this->files[$filename]) ) {
        return $this->files[$filename];
      }
      return false;
    }

  }

  // Object for a file in the ZIP archive
  class ZipFileEntry {
    function ZipFileEntry() {
      // The stored CRC32 from the archive in hex format
      $this->crc = '';
      // The uncompressed size of the file
      $this->size = 0;
      // The compressed size of the file
      $this->compressed_size = 0;
      // The compression ratio for compressed files
      $this->compression_ratio = 0;
      // Any error encountered while processing the file
      $this->error = E_NO_ERROR;
      // The decompressed file data or null if outputting to files
      $this->data = '';
      // The file name
      $this->name = '';
      // The path of the file
      $this->path = '';
      // The file modification timestamp
      $this->time = 0;
    }
  }
?>