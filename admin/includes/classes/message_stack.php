<?php
/*
  $Id: message_stack.php,v 1.6 2003/06/20 16:23:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2007-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Message Stack Class to display messages
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - 07/02/2007: Rewritten class to maintain sessions
// - 07/02/2007: Fix for intermittent session displaying of messages
// - 02/02/2010: Ported code from the front end to support script names
// - 02/03/2010: Modified examples to include the script argument
// - 10/19/2010: Removed global variables
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------

  Example usage:

  $messageStack = new messageStack();
  $messageStack->add('Error: Error 1', 'error', tep_get_script_name(FILENAME_DEFAULT));
  $messageStack->add('Error: Error 2', 'warning');
  $messageStack->output('header'); // Outputs some global messages
  $messageStack->output(); // Outputs the current script messages
*/
  class message_stack extends box {

    // Compatibility Constructor
    function message_stack() {
      extract(tep_load('sessions'));

      $message_array =& $cSessions->register('g_message_stack', array());
      $this->messages = array();

      for ($i=0, $j=count($message_array); $i<$j; $i++) {
        $this->add($message_array[$i]['text'], $message_array[$i]['type'], $message_array[$i]['class']);
      }
      $message_array = array();
    }

    function &get() {
      extract(tep_load('sessions'));

      $message_array =& $cSessions->register('g_message_stack');
      return $message_array;
    }

    function add($message, $type = 'error', $class='') {
      extract(tep_load('defs'));

      if( empty($class) ) {
        $class = $cDefs->script;
      }
      if ($type == 'error') {
        $this->messages[] = array('params' => 'class="messageStackError"', 'class' => $class, 'text' => $message);
      } elseif ($type == 'warning') {
        $this->messages[] = array('params' => 'class="messageStackWarning"', 'class' => $class, 'text' => $message);
      } elseif ($type == 'success') {
        $this->messages[] = array('params' => 'class="messageStackSuccess"', 'class' => $class, 'text' => $message);
      } else {
        $this->messages[] = array('params' => 'class="messageStackError"', 'class' => $class, 'text' => $message);
      }
    }

    function add_session($message, $type = 'error', $class='') {
      extract(tep_load('defs', 'sessions'));

      $message_array =& $cSessions->register('g_message_stack');
      if( empty($class) ) {
        $class = $cDefs->script;
      }
      $message_array[] = array(
        'text' => $message, 
        'type' => $type, 
        'class' => $class
      );
    }

    function reset() {
      $this->errors = array();
    }

    function output($class='') {
      extract(tep_load('defs'));

      if( empty($class) ) {
        $class = $cDefs->script;
      }
      $this->common_parameters = 'class="messageBox"';

      $output = array();
      for ($i=0, $j=count($this->messages); $i<$j; $i++) {
        if ($this->messages[$i]['class'] == $class) {
         $this->noticeBox($this->messages[$i]);
        }
      }
    }
  }
?>
