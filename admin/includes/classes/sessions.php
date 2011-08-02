<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Complete Session Replacement using application functions
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

Session Variables Usage:
Call the register, unregister and is_registered functions to initialize, remove 
or retrieve a session value. Examples:
1. Initialize a variable called  $temp_template containing an array 
which is initially blank, using the template session.
$temp_template =& $cSessions->register('template', array());
Now $temp_template is a reference pointing to the session storage
Any changes to the $temp_template will change the session contents for 'template'
If the session is already registered the blank array we passed in, is ignored.
Therefore doing two separate calls
$temp_template =& $cSessions->register('template', array('test'));
$temp_template =& $cSessions->register('template', array());
The first call will setup $temp_template = array('test');
The second call will also have the same value $temp_template[0] = 'test';
If we wanted to change the template session we could do it directly from the
$temp_template variable following the first call:
$temp_template = array('min' => 0, 'max' => 100);
2. Use function is_registered to check if a variable is registered only
$cSessions->is_registered('template');
3. Use function unregister to remove a variable from the sessions storage.
$cSessions->unregister('template');
*/
  class sessions {

    function sessions() {
      $this->name = 'imsid';
      $this->new_id = false;
      $this->length = 64;
      $this->life = 0;
      $this->reset();
    }

    function reset() {
      $this->storage = array();
      $this->id = 0;
      $this->started = false;
    }

    function start() {
      $this->life = MAX_ADMIN_SESSION_TIME;

      $this->id = $this->get_cookie($this->name);
      $result = false;
      if( empty($this->id) ) {
        $this->generate();
      } else {
        $this->validate($this->id);
      }

      $this->started = true;
    }

    function &initialize() {
      $this->start();
      return $this->storage;
    }

    function generate() {
      extract(tep_load('http_headers'));

      $this->id = tep_create_random_value($this->length);
      $this->new_id = true;
      $http->set_cookie($this->name);
      //$http->set_cookie($this->name, $this->id, time()+$this->life);
      $http->set_cookie($this->name, $this->id, -1);
    }

    function validate() {
      extract(tep_load('database'));

      if( $this->id != $db->filter($this->id) || strlen($this->id) != $this->length ) {
        $this->generate();
        return;
      }

      $check_query = $db->query("select sesskey, expiry from " . TABLE_SESSIONS_ADMIN . " where sesskey = '" . $db->filter($this->id) . "'");
      if( !$db->num_rows($check_query) ) {
        $this->generate();
      } else {
        $check_array = $db->fetch_array($check_query);
        if( $check_array['expiry'] <= time() ) {
          $this->clear($this->id);
          $this->generate();
        } else {
          $this->id = $check_array['sesskey'];
          $this->storage = $this->get($this->id);
        }
      }
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

    function get_life($life='') {
      if( empty($life) ) {
        $life = $this->life;
      }
      return time() + abs($life);
    }

    function get($key) {
      extract(tep_load('database'));

      $result = array();

      $value_query = $db->query("select value from " . TABLE_SESSIONS_ADMIN . " where sesskey = '" . $db->filter($key) . "' and expiry > '" . time() . "'");
      if( $db->num_rows($value_query) ) {
        $value_array = $db->fetch_array($value_query);
        $result = unserialize(base64_decode($value_array['value']));
        if( !is_array($result) ) $result = array();
      }
      return $result;
    }

    function set($key, $input) {
      extract(tep_load('database'));

      if( !is_array($input) ) {
        echo '<br /><br /><b style="color: #FF0000;">Session call failure. Check your scripts and close the session before exit!</b>';
        return;
      }

      $value = base64_encode(serialize($input));
      $expiry = $this->get_life();

      $check_query = $db->query("select count(*) as total from " . TABLE_SESSIONS_ADMIN . " where sesskey = '" . $db->input($key) . "'");
      $check = $db->fetch_array($check_query);

      $sql_data_array = array(
        'expiry' => $expiry,
        'value' => $value,
      );

      if( $check['total'] ) {
        $db->perform(TABLE_SESSIONS_ADMIN, $sql_data_array, 'update', "sesskey = '" . $db->input($key) . "'");
      } else {
        $sql_data_array['sesskey'] = $db->prepare_input($key);
        $db->perform(TABLE_SESSIONS_ADMIN, $sql_data_array);
      }
    }

    function clear($key) {
      extract(tep_load('database'));
      $db->query("delete from " . TABLE_SESSIONS_ADMIN . " where sesskey = '" . $db->input($key) . "'");
    }

    function expire() {
      extract(tep_load('database'));
      $value = tep_rand(0,19);
      if( !$value ) {
        $db->query("delete from " . TABLE_SESSIONS_ADMIN . " where expiry < '" . time() . "'");
      }
    }

    function destroy() {
      extract(tep_load('http_headers'));

      if( $this->started == false ) {
        return;
      }

      // Completely remove session
      $http->set_cookie($this->name);
      unset($_COOKIE[$name]);

      $this->clear($this->id);
      $this->reset();
    }

    function get_active_sessions() {
      extract(tep_load('database'));

      $check_query = $db->query("select count(*) as total from " . TABLE_SESSIONS_ADMIN);
      $check_array = $db->fetch_array($check_query);
      return $check_array['total'];
    }

    // Application Functions
    function &register($variable, $value='') {
      if(!$this->is_registered($variable) ) {
        $this->storage[$variable] = $value;
      }
      return $this->storage[$variable];
    }

    function is_registered($variable) {
      return (isset($this->storage[$variable]));
    }

    function unregister($variable) {
      unset($this->storage[$variable]);
    }

    // Creates a random session name not allocated and initializes it to a given value.
    function create_random_string($value) {
      do {
        $variable = tep_create_random_value(16);
      } while( isset($GLOBALS[$name]) || isset($this->storage[$variable]) );
      $this->register($variable, $value);
      return $variable;
    }

    function close($exit=true) {
      if( $this->started ) {
        $this->set($this->id, $this->storage);
      }

      $this->expire();
      if( !$exit ) return;
      exit();
    }
    // Application Functions EOM
  }
?>
