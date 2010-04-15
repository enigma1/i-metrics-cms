<?php
/*
  $Id: object_info.php,v 1.6 2003/06/20 16:23:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License

//Details:
//       Started            Stopped
//   ---------------------------------------
// |        0          |          0          |    = Has not been used yet, or has been reset
//   ---------------------------------------
// |        0          |          1          |    = Holding time, but paused
//   ---------------------------------------
// |        1          |          0          |    = Currently running
//   ---------------------------------------
*/
  class debug_timer {
    var $timer, $started, $stopped;
    
    function debug_timer() {
      $this->reset();
    }
    
    function start() {
      //leave the function because it is already running
      if($this->started) return; 
      if(!($this->stopped)) {
        $this->started = true;
        $this->timer = microtime(true);
      }
      //stopwatch is currently stopped, begin tracking time again
      if($this->stopped) {
        $this->timer = microtime(true) - $this->timer;
        $this->started = true;
        $this->stopped = false;
      }
    }

    function stop() {
      //make sure that it is running before you stop it
      if(!$this->stopped && $this->started) {
        $this->timer = microtime(true) - $this->timer;
        $this->stopped = true;
        $this->started = false;
      }
    }
    
    function reset() {
      $this->timer = $this->started = $this->stopped = 0;
    }
    
    function display() {
      //still running, use current time.
      if(!($this->stopped) && ($this->started)) $sec = microtime(true) - $this->timer;
      else if(!$this->stopped && !$this->started) $sec = 0;
      else $sec = $this->timer . "";

      //echo '<br />Time: ' . $sec . ' seconds<br />';
      echo '<br />Time: ' . sprintf('%f',$sec) . ' seconds<br />';
    }
  }
?>