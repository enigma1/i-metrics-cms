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
  class messageStack extends noticeBox {
    var $messages, $script;

    // compatibility constructor
    function messageStack() {
      global $g_session;
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

    // class methods
    function add($message, $type = 'error', $class='') {
      if( empty($class) ) {
        $class = $this->script;
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
      $this->messages = array();
    }

    function output($class='') {
      if( empty($class) ) {
        $class = $this->script;
      }

      $this->common_parameters = 'class="messageBox"';

      $output = array();
      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $this->noticeBox($this->messages[$i]);
        }
      }
    }
  }
?>
