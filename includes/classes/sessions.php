<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Complete Session Replacement using application functions
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
  class sessions {
    // Compatibility Constructor
    function sessions() {
      $this->name = 'imsid';
      $this->max_ip_sessions = 10;
      $this->reset();
      $this->life = 0;
    }

    function reset() {
      $this->storage = array();
      $this->id = 0;
      $this->new_id = false;
      $this->cookie = false;
      $this->started = false;
    }

    function start($check_post=true) {
      extract(tep_load('defs', 'http_validator', 'database'));

      if( isset($cDefs->external) && !empty($cDefs->external) ) {
        $check_post = false;
      }

      $this->id = $this->get_cookie($this->name);
      if( empty($this->id) && SESSION_FORCE_COOKIE_USE != 'true' && isset($_GET[$this->name]) ) {
        $this->id = $_GET[$this->name];
      }

      $result = false;
      if( empty($this->id) ) {
        $result = $this->generate();
      } else {
        $result = $this->validate($this->id);
      }

      if( $check_post && count($_POST) && ($this->new_id || !$result) ) {
        tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE, '', 'NONSSL', false));
      }

      if( $result ) {
        $this->life = MAX_CATALOG_SESSION_TIME;
        $this->started = true;
      } else {
        $this->reset();
      }
    }

    function &initialize() {
      extract(tep_load('http_validator', 'cache_html'));

      if($http->bot) {
        //-MS- Enable Global cache when no session is present
        $cache_html->bot_check_modified_header();
        //-MS- Spiders Cache Check when no session is present EOM
      } else {
        $this->start();
      }
      return $this->storage;
    }

    function generate() {
      extract(tep_load('http_validator', 'database'));

      $result = false;
      $laddress = $http->ip_string;
      $check_query = $db->query("select count(*) as total from " . TABLE_SESSIONS . " where ip_long = '" . $db->filter($laddress) . "'");
      $check_array = $db->fetch_array($check_query);
      if( $check_array['total'] >= $this->max_ip_sessions ) {
        return $result;
      }

      $this->id = tep_create_random_value(64);
      $this->new_id = $result = true;
      $http->set_cookie($this->name);
      //$http->set_cookie($this->name, $this->id, time()+$this->life);
      $http->set_cookie($this->name, $this->id, -1);

      return $result;
    }

    function validate() {
      extract(tep_load('database'));

      $result = false;
      if( $this->id != $db->filter($this->id) ) {
        return $this->generate();
      }

      $check_query = $db->query("select sesskey, expiry from " . TABLE_SESSIONS . " where sesskey = '" . $db->filter($this->id) . "'");
      if( !$db->num_rows($check_query) ) {
        $result = $this->generate();
      } else {
        $check_array = $db->fetch_array($check_query);
        if( $check_array['expiry'] <= time() ) {
          $this->clear($this->id);
          $result = $this->generate();
        } else {
          $this->id = $check_array['sesskey'];
          $this->storage = $this->get($this->id);
          $this->cookie = isset($_COOKIE[$this->name])?true:false;
          $result = true;
        }
      }
      return $result;
    }

    function has_cookie() {
      return $this->cookie;
    }

    function has_started() {
      return $this->started;
    }

    function get_cookie($name) {
      $result = false;
      if( isset($_COOKIE[$name]) ) {
        $result = $_COOKIE[$name];
      }
      return $result;
    }

    function clear_cookie($name) {
      extract(tep_load('http_validator'));

      $this->cookie = false;
      $http->set_cookie($this->name);
      unset($_COOKIE[$name]);
      unset($_GET[$name]);
    }

    function get_string($force=false) {
      $result = '';
      if( (!$this->cookie || $force) && $this->has_started() ) {
        $result = $this->name . '=' . $this->id;
      }
      return $result;
    }

    function get($key) {
      extract(tep_load('database'));

      $result = array();
      if( empty($key) ) return $result;

      $value_query = $db->query("select value from " . TABLE_SESSIONS . " where sesskey = '" . $db->filter($key) . "' and expiry > '" . time() . "'");
      if( $db->num_rows($value_query) ) {
        $value_array = $db->fetch_array($value_query);
        $result = unserialize(base64_decode($value_array['value']));
        if( !is_array($result) ) $result = array();
      }
      return $result;
    }

    function set($key, $input) {
      extract(tep_load('http_validator', 'database'));

      if( !$db->link || !is_array($input) ) {
        echo '<br /><br /><b style="color: #FF0000;">Session call failure. Check your scripts and close the session before exit!</b>';
        return;
      }

      $value = base64_encode(serialize($input));
      $expiry = time() + $this->life;

      $check_query = $db->query("select count(*) as total from " . TABLE_SESSIONS . " where sesskey = '" . $db->input($key) . "'");
      $check = $db->fetch_array($check_query);

      $sql_data_array = array(
        'expiry' => $expiry,
        'value' => $value,
      );
      if( $check['total'] ) {
        $db->perform(TABLE_SESSIONS, $sql_data_array, 'update', "sesskey = '" . $db->input($key) . "'");
      } else {
        $laddress = $http->ip_string;
        $sql_data_array['ip_long'] = $db->prepare_input($laddress);
        $sql_data_array['sesskey'] = $db->prepare_input($key);
        $db->perform(TABLE_SESSIONS, $sql_data_array);
      }
    }

    function clear($key) {
      extract(tep_load('database'));
      $db->query("delete from " . TABLE_SESSIONS . " where sesskey = '" . $db->input($key) . "'");
    }

    function expire() {
      extract(tep_load('database'));

      $value = tep_rand(0,9);
      if( !$value ) {
        $db->query("delete from " . TABLE_SESSIONS . " where expiry <= '" . time() . "'");
      }
    }

    function &register($variable, $value='') {
      if(!$this->is_registered($variable) ) {
        $this->storage[$variable] = $value;
      }
      return $this->storage[$variable];
    }

    function is_registered($variable) {
      return isset($this->storage[$variable]);
    }

    function unregister($variable) {
      unset($this->storage[$variable]);
    }

    function close($exit=true) {
      if( $this->started ) {
        $this->set($this->id, $this->storage);
      }

      $this->expire();
      if( !$exit ) return;
      exit();
    }

    function destroy() {
      if( $this->started == false ) {
        return;
      }

      // Completely remove session
      $this->clear_cookie($this->name);
      $this->clear($this->id);
      $this->reset();
    }

    function recreate() {
      extract(tep_load('history'));

      if( $this->started == false ) {
        return;
      }

      $tmp_storage = $this->storage;
      $this->clear_cookie($this->name);
      $this->clear($this->id);
      $this->start(false);
      $this->storage = $tmp_storage;
      $this->set($this->id, $this->storage);

      $cHistory->update_session();
    }

  }
?>
