<?php
/*
  $Id: logger.php,v 1.3 2003/06/20 16:23:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Log timer class
//----------------------------------------------------------------------------
// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// - PHP5 Register Globals off and Long Arrays Off support added
// - Removed timer start call from the constructor
// - Added initialization of variables
// - Added enabled control variable
// - Transformed script for CMS, removed unrelated functions
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class logger {

    // Compatibility Constructor
    function logger() {
      $this->enabled = (defined('STORE_PAGE_PARSE_TIME') && STORE_PAGE_PARSE_TIME == 'true')?true:false;
      $this->timer_start = $this->timer_stop = $this->timer_total = 0;
    }

    function timer_start() {
      if( !$this->enabled ) return false;

      if( defined("PAGE_PARSE_START_TIME") ) {
        $this->timer_start = PAGE_PARSE_START_TIME;
      } else {
        $this->timer_start = microtime();
      }
    }

    function timer_stop($display = 'false') {
      if( !$this->enabled ) return false;

      $this->timer_stop = microtime();

      $time_start = explode(' ', $this->timer_start);
      $time_end = explode(' ', $this->timer_stop);

      $this->timer_total = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);

      $this->write(getenv('REQUEST_URI'), $this->timer_total . 's');

      if ($display == 'true') {
        return $this->timer_display();
      }
      return false;
    }

    function timer_display() {
      return '<span class="smallText">Parse Time: ' . $this->timer_total . 's</span>';
    }

    function write($message, $type) {
      if( file_exists(STORE_PAGE_PARSE_TIME_LOG) ) {
        error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $type . '] ' . $message . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
      }
    }
  }
?>
