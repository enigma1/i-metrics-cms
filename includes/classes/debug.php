<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Debug Class
// Prints info of application specific variables
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
  class debug {
    // class constructor
    function debug() {
      $this->development = false;
      $this->log = false;
      $this->reset();
    }

    function reset() {
      $this->timer = $this->started = $this->stopped = 0;
    }

    function get() {
      if( !$this->development) return;

      $args = func_get_args();
      foreach($args as $value) {
        $method = 'dbg_' . $value;
        if( method_exists($this, $method) ) {
          $this->$method();
        } else {
          die('Invalid Debug Method - <b>' . $method . '</b>');
        }
      }
    }

    function dbg_log() {
      $log = tep_log();
    }

    function dbg_write_log() {

    }

    function dbg_helpers() {
      $this->display(tep_load());
    }

    function dbg_trace() {
      $args = func_get_args();
      echo '<pre>';
      if( empty($args) ) {
        debug_print_backtrace();
      } else {
        foreach($args as $value) {
          var_dump($value);
        }
      }
      echo '</pre>';
    }

    function dbg_reset_timer() {
      $this->timer = $this->started = $this->stopped = 0;
    }

    function dbg_start_timer() {
      //leave the function because it is already running
      if($this->started) return; 
      if( !$this->stopped ) {
        $this->started = true;
        $this->timer = microtime(true);
      }
      //stopwatch is currently stopped, begin tracking time again
      if( $this->stopped ) {
        $this->timer = microtime(true) - $this->timer;
        $this->started = true;
        $this->stopped = false;
      }
    }

    function dbg_stop_timer() {
      //make sure that it is running before you stop it
      if( !$this->stopped && $this->started) {
        $this->timer = microtime(true) - $this->timer;
        $this->stopped = true;
        $this->started = false;
      }
    }

    function dbg_show_timer() {
      //still running, use current time.
      if( !$this->stopped && $this->started) {
        $sec = microtime(true) - $this->timer;
      } elseif( !$this->stopped && !$this->started ) {
        $sec = 0;
      } else {
        $sec = $this->timer . "";
      }
      echo '<br />Time: ' . sprintf('%f', $sec) . ' seconds<br />';
    }
  }
?>
