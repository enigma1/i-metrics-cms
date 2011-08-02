<?php
/**
 * Zip file creation class.
 * Makes zip files.
 *
 * Based on :
 *
 *  http://www.zend.com/codex.php?id=535&single=1
 *  By Eric Mueller <eric@themepark.com>
 *
 *  http://www.zend.com/codex.php?id=470&single=1
 *  by Denis125 <webmaster@atlant.ru>
 *
 *  a patch from Peter Listiak <mlady@users.sourceforge.net> for last modified
 *  date and time of the compressed file
 *
 * Official ZIP file format: http://www.pkware.com/appnote.txt
 *
 * @access  public
 */
class pkzip {

    function pkzip() {
      // Last offset position
      $this->old_offset = 0;
      // End of central directory record
      $this->eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
      // Central directory
      $this->ctrl_dir = array();
      // Array to store compressed data
      $this->datasec = array();
    }

    // Converts an Unix timestamp to a four byte DOS date and time format (date
    // in high two bytes, time in low two bytes allowing magnitude comparison).
    //
    // @param  integer  the current Unix timestamp
    //
    // @return integer  the current date in a four byte DOS format
    //
    // @access private
    function unix2DosTime($unixtime = 0) {
      $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

      if ($timearray['year'] < 1980) {
        $timearray['year']    = 1980;
        $timearray['mon']     = 1;
        $timearray['mday']    = 1;
        $timearray['hours']   = 0;
        $timearray['minutes'] = 0;
        $timearray['seconds'] = 0;
      } // end if

      return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
              ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    } // end of the 'unix2DosTime()' method


    function splitFile($src, $dst) {
      $fp_src = fopen($src, "rb");
      if( !$fp_src ) return false;

      $fp_dst = fopen($dst, "w");
      if(!$fp_dst) {
        fclose($fp_src);
        return false;
      }

      $total_length = 0;
      $buffer_size = 10485760;
      $index = 1;
      while( !feof($fp_src) ) {
        $buffer = fread($fp_src, $buffer_size);
        $name = 's' . $index . '_' . basename($src);
        $this->datasec = array();
        $this->addFile($buffer, $name);
        $total_length += strlen($this->datasec[0]);
        fwrite($fp_dst, $this->datasec[0]);
        $index++;
      }
      fclose($fp_src);

      $ctrldir = implode('', $this->ctrl_dir);
      $end_string = 
        $this->eof_ctrl_dir .
        pack('v', count($this->ctrl_dir)) .           // total # of entries "on this disk"
        pack('v', count($this->ctrl_dir)) .           // total # of entries overall
        pack('V', strlen($ctrldir)) .                 // size of central dir
        pack('V', $total_length) .                    // offset to start of central dir
        "\x00\x00";                                   // .zip file comment length

      fwrite($fp_dst, $ctrldir);
      fwrite($fp_dst, $end_string);
      fclose($fp_dst);
      return true;
    }

    /**
     * Adds "file" to archive
     *
     * @param  string   file contents
     * @param  string   name of the file in the archive (may contains the path)
     * @param  integer  the current timestamp
     *
     * @access public
     */
    function addFile($data, $name, $time = 0) {
      if( empty($name) ) die('PKZip; Invalid Name Specified');
      $name     = str_replace('\\', '/', $name);

      $dtime    = dechex($this->unix2DosTime($time));
      $hexdtime = '\x' . $dtime[6] . $dtime[7]
                . '\x' . $dtime[4] . $dtime[5]
                . '\x' . $dtime[2] . $dtime[3]
                . '\x' . $dtime[0] . $dtime[1];
      eval('$hexdtime = "' . $hexdtime . '";');

      $fr   = "\x50\x4b\x03\x04";
      $fr   .= "\x14\x00";            // ver needed to extract
      $fr   .= "\x00\x00";            // gen purpose bit flag
      $fr   .= "\x08\x00";            // compression method
      $fr   .= $hexdtime;             // last mod time and date

      // "local file header" segment
      $unc_len = strlen($data);
      $crc     = crc32($data);
      $zdata   = gzcompress($data);
      $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
      $c_len   = strlen($zdata);
      $fr      .= pack('V', $crc);             // crc32
      $fr      .= pack('V', $c_len);           // compressed filesize
      $fr      .= pack('V', $unc_len);         // uncompressed filesize
      $fr      .= pack('v', strlen($name));    // length of filename
      $fr      .= pack('v', 0);                // extra field length
      $fr      .= $name;

      // "file data" segment
      $fr .= $zdata;

      // "data descriptor" segment (optional but necessary if archive is not
      // served as file)
      // nijel(2004-10-19): this seems not to be needed at all and causes
      // problems in some cases (bug #1037737)
      //$fr .= pack('V', $crc);                 // crc32
      //$fr .= pack('V', $c_len);               // compressed filesize
      //$fr .= pack('V', $unc_len);             // uncompressed filesize

      // add this entry to array
      $this->datasec[] = $fr;

      // now add to central directory record
      $cdrec = "\x50\x4b\x01\x02";
      $cdrec .= "\x00\x00";                // version made by
      $cdrec .= "\x14\x00";                // version needed to extract
      $cdrec .= "\x00\x00";                // gen purpose bit flag
      $cdrec .= "\x08\x00";                // compression method
      $cdrec .= $hexdtime;                 // last mod time & date
      $cdrec .= pack('V', $crc);           // crc32
      $cdrec .= pack('V', $c_len);         // compressed filesize
      $cdrec .= pack('V', $unc_len);       // uncompressed filesize
      $cdrec .= pack('v', strlen($name) ); // length of filename
      $cdrec .= pack('v', 0 );             // extra field length
      $cdrec .= pack('v', 0 );             // file comment length
      $cdrec .= pack('v', 0 );             // disk number start
      $cdrec .= pack('v', 0 );             // internal file attributes
      $cdrec .= pack('V', 32 );            // external file attributes - 'archive' bit set

      $cdrec .= pack('V', $this->old_offset ); // relative offset of local header
      $this->old_offset += strlen($fr);

      $cdrec .= $name;

      // optional extra field, file comment goes here
      // save to central directory
      $this->ctrl_dir[] = $cdrec;
    } // end of the 'addFile()' method

    // Dumps out file
    // @return  string  the zipped file
    // @access public
    function file() {

      $data    = implode('', $this->datasec);
      $ctrldir = implode('', $this->ctrl_dir);

      return
          $data .
          $ctrldir .
          $this->eof_ctrl_dir .
          pack('v', count($this->ctrl_dir)) .  // total # of entries "on this disk"
          pack('v', count($this->ctrl_dir)) .  // total # of entries overall
          pack('V', strlen($ctrldir)) .           // size of central dir
          pack('V', strlen($data)) .              // offset to start of central dir
          "\x00\x00";                             // .zip file comment length
    } // end of the 'file()' method

    function addDir($fs_dir, $path='', $exclude_extensions=array('.zip') ) {

      $fs_dir = rtrim($fs_dir, '/');
      $path = rtrim($path, '/');
      if( !empty($path) ) $path .= '/';

      $fs_dir .= '/';
      $org_dir = $fs_dir;

      $fs_dir .= '/'.$path;

      $sub_array = glob($fs_dir.'*');

      $contents = '';

      foreach($sub_array as $sub ) {
        $entry = basename($sub);

        $process = true;
        if( is_file($sub) ) {
          foreach($exclude_extensions as $ext) {
            $len = strlen($ext);
            if( substr($entry, -$len) == $ext ) {
              $process = false;
              break;
            }
          }

          if( !$process || !tep_read_contents($fs_dir.'/'.$entry, $contents) ) {
            continue;
          }
          $this->addFile($contents, $path.$entry);
        } else {
          $this->addDir($org_dir, $path.$entry);
        }
      }
    }

    function addArray(&$files_array) {
      foreach($files_array as $filename => $data) {
        $this->addFile($data, $filename);
      }
    }
  }
?>
