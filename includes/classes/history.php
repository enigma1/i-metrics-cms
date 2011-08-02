<?php
/*
Came from:
  $Id: navigation_history.php,v 1.6 2003/06/09 22:23:43 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2007-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Navigation History Class tracks user page visits
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - PHP5 Register Globals Off support added
// - PHP5 Long Arrays Off support added
// - Navigation History fix to maintain the history slots
// - Changed structure to use validator services
// - Ported for the CMS
// - Converted to history class
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  class history {
    // Compatibility Constructor
    function history() {
      extract(tep_load('sessions'));

      $this->reset();
      $this->path =& $cSessions->register('g_navigation_history', $this->path);
    }

    function reset() {
      $this->path = array();
      $this->snapshot = array();
    }

    function add_current_page() {
      extract(tep_load('defs', 'validator'));

      $set = 'true';
      $get_params = $cValidator->get_array;
      for ($i=0, $j=count($this->path); $i<$j; $i++) {
        if( $this->path[$i]['page'] == $cDefs->script && $this->path[$i]['get'] == $get_params ) {

          unset($this->path[$i]);
          $this->path[] = array(
            'page' => $cDefs->script,
            'mode' => $cDefs->request_type,
            'get' => $get_params,
            'post' => ''
          );
          $this->path = array_values($this->path);
          $set = 'false';
          break;
        }
      }

      if ($set == 'true') {
        $this->path[] = array(
          'page' => $cDefs->script,
          'mode' => $cDefs->request_type,
          'get' => $get_params,
          'post' => ''
        );
      }
    }

    function update_session() {
      extract(tep_load('sessions'));

      $name = $cSessions->name;
      $session_id = $cSessions->id;

      for( $i=0, $j=count($this->path); $i<$j; $i++) {
        if(isset($this->path[$i]['get'][$name]) && tep_not_null($this->path[$i]['get'][$name]) ) {
          $this->path[$i]['get'][$name] = $session_id;
        }
        if(isset($this->path[$i]['post'][$name]) && tep_not_null($this->path[$i]['post'][$name]) ) {
          $this->path[$i]['post'][$name] = $session_id;
        }
      }
    }

    function remove_current_page() {
      extract(tep_load('defs'));

      $last_entry_position = sizeof($this->path) - 1;
      if ($this->path[$last_entry_position]['page'] == $cDefs->script) {
        unset($this->path[$last_entry_position]);
      }
    }

    function set_snapshot($page = '') {
      extract(tep_load('defs', 'validator'));

      if (is_array($page)) {
        $this->snapshot = array(
          'page' => $page['page'],
          'mode' => isset($page['mode'])?$page['mode']:'NONSSL',
          'get' => isset($page['get'])?$page['get']:'',
          'post' => isset($page['post'])?$page['post']:''
        );
      } else {
        $get_params = $cValidator->get_array;
        $post_params = $cValidator->post_array;
        $this->snapshot = array(
          'page' => $cDefs->script,
          'mode' => $cDefs->request_type,
          'get' => $get_params,
          'post' => $post_params
        );
      }
    }

    function clear_snapshot() {
      $this->snapshot = array();
    }

    function remove_get_entry($get_params) {
      for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
        if( $this->path[$i]['get'] == $get_params ) {
          unset($this->path[$i]);
        }
      }
    }

    function update_get_entry($get_params, $new_params) {
      extract(tep_load('defs'));

      for ($i=0, $j=count($this->path); $i<$j; $i++) {
        if( $this->path[$i]['page'] == $cDefs->script && $this->path[$i]['get'] == $get_params ) {
          $this->path[$i]['get'] = $new_params;
        }
      }
    }

    function update_post_entry($post_params, $new_params) {
      extract(tep_load('defs'));

      for ($i=0, $j=count($this->path); $i<$j; $i++) {
        if( $this->path[$i]['page'] == $cDefs->script && $this->path[$i]['post'] == $post_params ) {
          $this->path[$i]['post'] = $new_params;
        }
      }
    }

  }
?>
