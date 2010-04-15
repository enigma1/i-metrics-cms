<?php
/*
  $Id: navigation_history.php,v 1.6 2003/06/09 22:23:43 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2007-2008 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Catalog: Navigation History Class tracks user page visits
//----------------------------------------------------------------------------
// I-Metrics Layer
//----------------------------------------------------------------------------
// Modifications:
// - 07/05/2007: PHP5 Register Globals Off support added
// - 07/08/2007: PHP5 Long Arrays Off support added
// - 10/03/2007: Navigation History fix to maintain the history slots
// - 02/04/2008: Changed structure to use validator services
//----------------------------------------------------------------------------
// Released under the GNU General Public License v3.00
//----------------------------------------------------------------------------
*/

  class navigationHistory {
    var $path, $snapshot;

    function navigationHistory() {
      $this->reset();
    }

    function reset() {
      $this->path = array();
      $this->snapshot = array();
    }

    function add_current_page() {
      global $PHP_SELF, $request_type, $g_validator;

      $set = 'true';
      $get_params = $g_validator->get_array;
      for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
        if( ($this->path[$i]['page'] == basename($PHP_SELF)) && $this->path[$i]['get'] == $get_params ) {

          unset($this->path[$i]);
          $this->path[] = array('page' => basename($PHP_SELF),
                                'mode' => $request_type,
                                'get' => $get_params,
                                'post' => '');
          $this->path = array_values($this->path);
          $set = 'false';
          break;
        }
      }

      if ($set == 'true') {
        $this->path[] = array('page' => basename($PHP_SELF),
                              'mode' => $request_type,
                              'get' => $get_params,
                              'post' => '');
      }
    }

    function update_session() {
      global $g_session;

      $name = $g_session->name();
      $session_id = $g_session->id();

      for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
        if(isset($this->path[$i]['get'][$name]) && tep_not_null($this->path[$i]['get'][$name]) ) {
          $this->path[$i]['get'][$name] = $session_id;
        }
        if(isset($this->path[$i]['post'][$name]) && tep_not_null($this->path[$i]['post'][$name]) ) {
          $this->path[$i]['post'][$name] = $session_id;
        }
      }
    }


    function remove_current_page() {
      global $PHP_SELF;

      $last_entry_position = sizeof($this->path) - 1;
      if ($this->path[$last_entry_position]['page'] == basename($PHP_SELF)) {
        unset($this->path[$last_entry_position]);
      }
    }

    function set_snapshot($page = '') {
      global $PHP_SELF, $request_type, $g_validator;

      if (is_array($page)) {
        $this->snapshot = array('page' => $page['page'],
                                'mode' => isset($page['mode'])?$page['mode']:'NONSSL',
                                'get' => isset($page['get'])?$page['get']:'',
                                'post' => isset($page['post'])?$page['post']:''
                               );
      } else {
        $get_params = $g_validator->get_array;
        $post_params = $g_validator->post_array;
        $this->snapshot = array('page' => basename($PHP_SELF),
                                'mode' => $request_type,
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
      global $PHP_SELF;

      for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
        if( ($this->path[$i]['page'] == basename($PHP_SELF)) && $this->path[$i]['get'] == $get_params ) {
          $this->path[$i]['get'] = $new_params;
        }
      }
    }

    function update_post_entry($post_params, $new_params) {
      global $PHP_SELF;

      for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
        if( ($this->path[$i]['page'] == basename($PHP_SELF)) && $this->path[$i]['post'] == $post_params ) {
          $this->path[$i]['post'] = $new_params;
        }
      }
    }

  }
?>
