<?php
/**
  * CSV class
  * Basic CSV import/export
  * Usage: Set delimiter and quote character according to
  * current needs, i.e. it's possible (and often sensible)
  * to change delimiter and quote character between import
  * and export using the interface (setDelimiter, setQuote).
  * @author Jens Hatlak <jh@junetz.de>
  * @version 1.21 7/15/2003
  * @package Junetz
  */
/*
// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// MySQL Support Class
// - Ported for osCommerce
// - Converted mysql calls to tep_* wrapper functions
// - Removed Connect support - already connected to the database
// - Column Distribution 2nd pass added
// - String length calculations fixes added
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class csv_core {

    // Compatibility Constructor
    function csv_core($del=";", $quote='"', $lineend="\n") {
      $this->setDelimiter($del);
      $this->setQuote($quote);
      $this->setLineEnd($lineend);

      // 2D Data array of lines (second dimension: fields).
      $this->data = array();
      // Line end character. Default: "\n"
      $this->lineend = $lineend;
      // Display/return/export header line
      $this->head = true;
      // No. of columns. Default: 0 (no limit)
      $this->cols = 0;
      // No. of preview lines. Default: 5
      $this->prelines = 5;
      // Quote character. Default: '"'
      $this->quote = $quote;
      // Field delimiter. Default: ';'
      // For tab separation set this to '\t'
      $this->delimiter = ';';
    }

    function Error($msg) {
      //Fatal error
      die('<b>CSV error: </b>'.$msg);
    }

    // Set Delimiter
    // @param string $del New delimiter
    function setDelimiter($del) {
      if (!empty($del)) $this->delimiter = $del;
    }

    // Set Quote character
    // @param mixed $q New quote character (string or FALSE)
    function setQuote($q) {
      if (empty($q))
        $q = "";
      else if (is_string($q))
        $this->quote = $q;
    }

    // Set no. of columns (for next import)
    // @param int $cols New no. of columns
    function setCols($cols) {
      if (is_numeric($cols)) $this->cols = $cols;
    }

    // Set no. of preview lines
    // @param int $lines New no. of lines
    function setPreLines($lines) {
      if (is_numeric($lines)) $this->prelines = $lines;
    }

    // Set heading switch.
    // Affects preview, reSort, exStream
    // @param boolean $head Activate heading switch
    function setHead($head) {
      $this->head = (bool)$head;
    }


    // Set line end character
    // @param string $lineend Line end character
    function setLineEnd($lineend) {
      if (is_string($lineend)) $this->lineend = $lineend;
    }

    // Get current delimiter
    // @return string Delimiter
    function getDelimiter() {
      return $this->delimiter;
    }

    // Get current quote character
    // @return string Quote character
    function getQuote() {
      return $this->quote;
    }

    // Get current column limit
    // @return int No. of cols
    function getCols() {
      return $this->cols;
    }

    // Get current no. of preview lines
    // @return int No. of preview lines
    function getPreLines() {
      return $this->prelines;
    }

    // Get current heading switch state
    // @return boolean Heading switch value
    function getHead() {
      return $this->head;
    }

    // Get current line end character
    // @return string Line end character
    function getLineEnd() {
      return $this->lineend;
    }

    // Get data
    // @return array 2D array of lines containing field arrays
    function getData() {
      if ($this->head) return array_slice($this->data, 1);
      return $this->data;
    }

    // Add a line containing fields as array or complete string
    // to the internal data (line) array
    // @param mixed Line contents
    function addLine($data) {
      if (is_array($data)) $this->data[] = $data;
      else if (is_string($data)) {
        $this->data[] = $this->parseLine($data);
      }
    }

    // Parse a line string according to current settings
    // (delimiter/quote)
    // @return array Parsed data
    function parseLine($str) {
      $data = explode($this->delimiter, $str);
      foreach ($data as $key=>$val)
        $data[$key] = str_replace($this->quote, '', $val);
      return $data;
    }

    // Returns the smaller value of
    // no. of preview lines and actual no. of lines.
    // Neither of the values is being altered
    // @return int Smaller value
    function checkLineCount() {
      $reallines = count($this->data);
      if ($this->prelines>$reallines)
        return $reallines;
      return $this->prelines;
    }

    // Formats a line according to current settings
    // (delimiter/quote)
    // @param array $data Array of fields (strings)
    // @return string Formatted line
    function formatLine($data) {
      $line = '';
      foreach ($data as $str)
        $line .= sprintf('%s%s%s%s', $this->quote, $str,
                         $this->quote, $this->delimiter);
      return substr($line,0,-1).$this->lineend;
    }

    function formatInt($data) {
      $line = '';
      foreach ($data as $str)
        $line .= sprintf('%s%s', $str, $this->delimiter);
      return $line;
    }

    function formatString($data) {
      $line = '';
      foreach ($data as $str)
        $line .= sprintf('%s%s%s%s', $this->quote, $str, $this->quote, $this->delimiter);
      return $line;
    }

    // Re-sort internal data array using field $field
    // @param int $field Field index
    // @param mixed $dir Direction (const. SORT_ASC or SORT_DESC)
    function reSort($field, $dir=SORT_ASC) {
      if (is_numeric($field) && ($dir==SORT_ASC || $dir==SORT_DESC)) {
        foreach ($this->data as $key=>$val) {
          if ($this->head && $key==0) {
            // make sure heading is the first line
            if ($dir==SORT_ASC) $sortarray[] = -2147483647;
            else if ($dir==SORT_DESC) $sortarray[] = "zzz";
          } else
            $sortarray[] = $val[$field];
        }
        array_multisort($this->data, SORT_STRING, $sortarray, $dir);
      }
    }

    // Import data from arbitrary MySQL query
    // @param resource $res MySQL result resource
    function queryImport($res) {
      extract(tep_load('database'));

      $fc = $db->num_fields($res);
      if ($fc==0) return;
      for ($i=0; $i < $fc; $i++)
        $data[] = $db->field_name($res,$i);
      $this->addLine($data);
      while ($row = $db->fetch_array($res)) {
        $data = array();
        for ($i=0; $i < $fc; $i++)
          $data[] = $row[$i];
        $this->addLine($data);
      }
    }

    // Export data from arbitrary MySQL query
    // @param resource $res MySQL result resource
    // @param string $name Preset file name
    // @param string $ext Extension (default: "csv")
    // @param boolean $nameContainsExt Wether $name contains $ext (default: FALSE)
    function queryExport($sql_raw, $name, $ext=".csv", $nameContainsExt=false) {
      extract(tep_load('database'));

      $this->buffer = '';
      $sql_raw = stripslashes($sql_raw);
      $result_query = $db->query($sql_raw);
      $fc = $db->num_fields($result_query);

      if ($fc==0) return;
      if ($this->head) {
        for ($i=0; $i < $fc; $i++) {
          $data[$i]['Name'] = $db->field_name($result_query,$i);
          $data[$i]['Type'] = $db->field_type($result_query,$i);

          if( $fc == $i+1) {
            $str = sprintf('%s%s%s%s', $this->quote, $data[$i]['Name'], $this->quote, $this->lineend);
          } else {
            $str = sprintf('%s%s%s%s', $this->quote, $data[$i]['Name'], $this->quote, $this->delimiter);
          }
          $this->buffer .= $str;
        }
      }

      //while ($row = mysql_fetch_row($res))
      while ($row = $db->fetch_array($result_query)) {
        $i = 0;
        $fc = count($row);

        foreach( $row as $key => $value ) {
          $value = preg_replace("/\r\n/", "\n", $value);
          $str = sprintf('%s%s%s%s', $this->quote, $value, $this->quote, $this->delimiter);
          $i++;
          if( $fc == $i) {
            $str = substr($str,0,-1) . $this->lineend;

          }
          $this->buffer .= $str;
        }
      }
      $this->buffer = trim($this->buffer);
      $this->sendHeaders($name, $ext, $nameContainsExt);
      exit();
    }

/*
    function write_header($cols) {
      $index = 0;
      $max = count($cols);
      if( !$max ) return;

      foreach( $cols as $key => $value ) {
        $value = preg_replace("/\r\n/", " ", $value);
        $data[$i]['Name'] = $value;
        $index++;
        if( $max == $index) {
          $str = sprintf('%s%s%s%s', $this->quote, $data[$i]['Name'], $this->quote, $this->lineend);
        } else {
          $str = sprintf('%s%s%s%s', $this->quote, $data[$i]['Name'], $this->quote, $this->delimiter);
        }
        $this->buffer .= $str;
      }
    }
*/
    function write_header($cols) {
      $index = 0;
      $max = count($cols);
      if( !$max ) return;

      foreach( $cols as $key => $value ) {
        $value = preg_replace("/\r\n/", " ", $value);
        $index++;
        if( $max == $index) {
          $str = sprintf('%s%s%s%s', $this->quote, $value, $this->quote, $this->lineend);
        } else {
          $str = sprintf('%s%s%s%s', $this->quote, $value, $this->quote, $this->delimiter);
        }
        $this->buffer .= $str;
      }
    }

    function write_data($data) {
      $index = 0;
      $max = count($data);
      if( !$max ) return;
      foreach( $data as $key => $value ) {
        $value = preg_replace("/\r\n/", " ", $value);
        $str = sprintf('%s%s%s%s', $this->quote, $value, $this->quote, $this->delimiter);
        $index++;
        if( $max == $index) {
          $str = substr($str,0,-1) . $this->lineend;
        }
        $this->buffer .= $str;
      }
      $this->buffer = trim($this->buffer);
    }

    function write_segment($cols, $data) {
      $this->write_header($cols);
      $this->write_data($data);
    }

    function insert_line($lines=1) {
      for($i=0; $i<$lines; $i++ ) {
        $this->buffer .= $this->lineend;
      }
    }
    function reset_buffer() {
      $this->buffer = '';
    }

    function output($name) {
      $this->buffer = trim($this->buffer);
      $this->sendHeaders($name);
      exit();
    }

    // Returns preview data
    // (up to no. of preview lines)
    // @return array 2D Array of lines containing fields
    function preview() {
      if ($this->head) $start = 0;
      else $start = 1;
      for ($i=$start;$i<$this->checkLineCount();$i++)
        $data[] = $this->data[$i];
      return $data;
    }

    // Import uploaded file
    // @param string $field Name of fileselect field
    // @param int $length Optional maximal line length (default: 1024)
    function uplImport($field, $length=1024) {
      if (!$GLOBALS["HTTP_POST_FILES"][$field]["error"])
        $this->fimport($GLOBALS["HTTP_POST_FILES"][$field]["tmp_name"], $length);
    }

    // Import file
    // @param string $file Name of file to be imported
    // @param int $length Optional maximal line length (default: 1024)
    function fImport($file="", $length=1024) {
      if ($file!="" && file_exists($file)) {
        $fp = fopen($file,"r");
        while ($data = fgetcsv($fp, $length, $this->delimiter)) {
          if ($this->cols!=0)
            $data = array_slice($data, 0, $this->cols);
          $this->data[] = $data;
        }
      }
    }

    // Open export file stream (HTTP download)
    // @param string $name Preset file name
    // @param string $ext Extension (default: "csv")
    // @param boolean $nameContainsExt Wether $name contains $ext (default: FALSE)
    function exStream($name, $ext=".csv", $nameContainsExt=false) {
      if (empty($this->data)) return;
      $this->sendHeaders($name, $ext, $nameContainsExt);

      foreach ($this->data as $nr=>$line) {
        if ($this->head || $nr!=0)
          $datastr .= $this->formatLine($line);
      }

      header("Content-Length: ".strlen($datastr));

      echo $datastr;
      exit();
    }

    // Send appropriate headers
    // @param string $name File name (may already contain extension,
    // in which case the second parameter is ignored)
    // @param string $ext Extension (default: "csv")
    function sendHeaders($name, $ext=".csv") {
      header("Expires: 0");
      header("Cache-Control: no-cache, must-revalidate");
      header("Pragma: no-cache");
      header('Content-Type: application/octet-stream');

      if(headers_sent())
        $this->Error('Some data has already been output to browser, can\'t send CSV file');
      header('Content-Length: '.strlen($this->buffer));

      header('Content-disposition: attachment; filename="'. $name.$ext . '"');

      echo $this->buffer;
      return '';
    }
  }
?>