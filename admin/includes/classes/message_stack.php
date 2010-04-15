<?php
/*
  $Id: message_stack.php,v 1.6 2003/06/20 16:23:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
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
  class messageStack extends box {
    var $messages, $script;

    function messageStack() {
      global $g_session, $messageToStack;
      $this->script = tep_get_script_name();

      $message_array =& $g_session->register('g_message_stack');
      if( !$g_session->is_registered('g_message_stack') || !is_array($message_array) ) {
        $message_array = array();
      }
      $this->messages = array();

      for ($i=0, $j=count($message_array); $i<$j; $i++) {
        $this->add($message_array[$i]['text'], $message_array[$i]['type'], $message_array[$i]['class']);
      }
      $message_array = array();
    }

    function add($message, $type = 'error', $class='') {
      if( empty($class) ) {
        $class = $this->script;
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
      global $g_session;

      $message_array =& $g_session->register('g_message_stack');
      if( empty($class) ) {
        $class = $this->script;
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
      if( empty($class) ) {
        $class = $this->script;
      }
      $this->common_data_parameters = 'class="messageBox"';

      $output = array();
      for ($i=0, $j=count($this->messages); $i<$j; $i++) {
        if ($this->messages[$i]['class'] == $class) {
         $this->noticeBox($this->messages[$i]);
        }
      }
    }
  }
?>
