<?php
/*
  $Id: message_stack.php,v 1.1 2003/05/19 19:45:42 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Message Stack Class to display messages
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - 07/05/2007: PHP5 Register Globals Off support added
// - 07/08/2007: PHP5 Long Arrays Off support added
// - 07/11/2007: Message Stack fix to always display messages
// - 09/20/2007: Added self script support
// - 02/15/2010: Moved small icons to CSS
// - 02/15/2010: Changed arguments order to simplify adding messages
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------

  Example usage:

  $messageStack = new messageStack();
  $messageStack->add('Error: Error 1', 'error', 'generic_pages');
  $messageStack->add('Notice: Error 2', 'warning', 'generic_pages');
  $messageStack->output('generic_pages');

  // Same script posting? Do not include the last argument, messageStack will pick up the current script
*/
  class message_stack extends noticeBox {
    var $messages, $script;

    // compatibility constructor
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

    // class methods
    function add($message, $type = 'error', $class='') {
      extract(tep_load('defs'));

      if( empty($class) ) {
        $class = $cDefs->script;
      }
      if( $type == 'error' ) {
        $this->messages[] = array('params' => 'class="messageStackError"', 'class' => $class, 'text' => $message);
      } elseif( $type == 'warning' ) {
        $this->messages[] = array('params' => 'class="messageStackWarning"', 'class' => $class, 'text' => $message);
      } elseif( $type == 'success' ) {
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
      $this->messages = array();
    }

    function output($class='') {
      extract(tep_load('defs'));

      if( empty($class) ) {
        $class = $cDefs->script;
      }

      $this->common_parameters = 'class="messageBox bounder"';

      $output = array();
      for( $i=0, $j=count($this->messages); $i<$j; $i++ ) {
        if( $this->messages[$i]['class'] == $class ) {
          $this->noticeBox($this->messages[$i]);
        }
      }
    }
  }
?>
