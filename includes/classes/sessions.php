<?php
/*
  $Id: sessions.php,v 1.19 2003/07/02 22:10:34 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Catalog: Session fixes, for session read, session recreate,
//----------------------------------------------------------------------------
// session destroy, session integrity, cookie check and globals reference. 
// Added configurable session life setting.
// Added session callback handling switch for Manual or PHP driven.
// Rewritten session start function altered validation method
// Removed session recreate switch
// Removed optional session files generation, MySQL handles sessions always
// Converted functions file into a class
// Added dbase global object - modified dbase access
// Removed Test Cookie
// Added IP to the sessions table
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class sessions {
    var $started, $cookie, $spider_flag, $life;
    var $pseudo = array();

    function sessions() {
      global $SID, $g_sid;

      //ini_set('session.gc_probability', 100);
      //ini_set('session.gc_divisor', 100);

      $this->started = false;
      $this->cookie = false;

      $this->spider_pattern = '://';
      $this->spider_flag = false;
      $this->spider_name = '';
      $this->user_agent = strtolower(trim(strip_tags(getenv('HTTP_USER_AGENT'))));

      // Initialize SID. The SID definition is setup by PHP automatically when cookies are active, should be left empty
      $SID = '';
      $this->life = MAX_CATALOG_SESSION_TIME;
    }

    function initialize() {
      global $cookie_domain, $cookie_path, $g_sid, $g_db, $g_external;

      if( SESSION_PHP_HANDLING == 'true') {
        session_set_save_handler(
                                 array(&$this, '_sess_open'), 
                                 array(&$this, '_sess_close'), 
                                 array(&$this, '_sess_read'), 
                                 array(&$this, '_sess_write'), 
                                 array(&$this, '_sess_destroy'), 
                                 array(&$this, '_sess_gc')
                                );
      }
      $this->name('imsid');
      //$path = SESSION_WRITE_DIRECTORY;
      //if( !empty($path) ) {
      //  $this->save_path($path);
      //}

      // set the session cookie parameters
      if (function_exists('session_set_cookie_params')) {
        session_set_cookie_params(0, $cookie_path, $cookie_domain);
      } elseif (function_exists('ini_set')) {
        ini_set('session.cookie_lifetime', '0');
        ini_set('session.cookie_path', $cookie_path);
        ini_set('session.cookie_domain', $cookie_domain);
      }

      $g_sid = 0;

      if( isset($_COOKIE[$this->name()]) ) {
        $g_sid = $_COOKIE[$this->name()];
      }
      if( !$g_sid && SESSION_FORCE_COOKIE_USE != 'True' && isset($_GET[$this->name()]) ) {
        $g_sid = $_GET[$this->name()];
      }

      $check_query = $g_db->query("select sesskey from " . TABLE_SESSIONS . " where sesskey = '" . $g_db->filter($g_sid) . "'");
      if( !$g_db->num_rows($check_query) ) {
        $g_sid = 0;
        if( count($_POST) && !isset($g_external) ) {
          tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE, '', 'NONSSL', false));
        }
      }

      if( count($_POST) && !isset($g_external) && empty($g_sid) ) {
        tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE, '', 'NONSSL', false));
      }
    }


    function process_agents() {
      global $cookie_domain, $cookie_path, $SID, $g_sid, $g_external;

      if(!empty($this->user_agent)) {
        $scan_pattern = '://';
        $pos = strpos($this->user_agent, $this->spider_pattern);
        if( $pos !== false ) {
          $this->spider_name = substr($this->user_agent, $pos+strlen($this->spider_pattern));
          //-MS- Enable Global cache when no session is present
          tep_check_modified_header();
          //-MS- Spiders Cache Check when no session is present EOM
          $this->spider_flag = true;
        }
      }

      if($this->spider_flag == false) {
        $this->start();
      }

      //-MS- Accept POST vars with session only
      if( count($_POST) && $this->started == false ) {
        //tep_redirect(tep_href_link('', '', 'NONSSL', false));
        tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE, '', 'NONSSL', false));
      }
      //-MS- Accept POST vars with session only EOM
      // If the initial cookie is not send re-send it.
      if( $this->started == true ){
        if( !isset($_COOKIE[$this->name()]) ) {
          $this->setcookie($this->name(), $this->id(), 0, $cookie_path, $cookie_domain);
          // Set SID once, even if empty. The SID definition is setup by PHP automatically
          if( defined('SID') ) {
            $SID = SID;
          }
        }
      }
    }

    function has_cookie() {
      return $this->cookie;
    }

    function has_started() {
      return $this->started;
    }

    function set_started($started=true) {
      $this->started = $started;
    }

    function setcookie($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = 0) {
      setcookie($name, $value, $expire, $path, (!empty($domain) ? $domain : ''), $secure);
    }

    function start() {
      global $g_db, $g_sid;

      if( empty($g_sid) ) {
        $session_data = session_get_cookie_params();
        setcookie($this->name(), '', 0, $session_data['path'], $session_data['domain']);
        unset($_COOKIE[$this->name()]);
        unset($_GET[$this->name()]);

        $laddress = tep_ip2_long(tep_get_ip_address());

        $check_query = $g_db->query("select sesskey, value from " . TABLE_SESSIONS . " where ip_long = '" . $g_db->filter($laddress) . "'");
        if( $g_db->num_rows($check_query) ) {
          $g_db->query("delete from " . TABLE_SESSIONS . " where ip_long = '" . $g_db->filter($laddress) . "'");

/*
          $check_array = $g_db->fetch_array($check_query);
          if( !empty($check_array['value']) ) {
            $g_db->query("update " . TABLE_SESSIONS . " set value='' where ip_long = '" . $g_db->filter($laddress) . "'");
          }
          $this->id($check_array['sesskey']);
          $this->setcookie($this->name(), $this->id(), 0, $session_data['path'], $session_data['domain']);
          return false;
          //$g_db->query("delete from " . TABLE_SESSIONS . " where ip_long = '" . $g_db->filter($laddress) . "'");
          //tep_redirect(tep_href_link('', '', 'NONSSL', false));
          //$this->spider_flag = true;
*/
        }
      } else {
        $this->cookie = true;
      }

      $success = session_start();
      if( $success ) {

        if( SESSION_PHP_HANDLING == 'false') {
          $id = $this->id();
          $_SESSION = _sess_read($id);
        }
      }
      $this->started = true;
      return $success;
    }

    function &register($variable, $value='') {
      if( $this->started == true ) {
        if(!$this->is_registered($variable) ) {
          $_SESSION[$variable] = $value;
        }
        return $_SESSION[$variable];
      } else {
        if( !isset($this->pseudo[$variable]) ) {
          $this->pseudo[$variable] = $value;
        }
        return $this->pseudo[$variable];
      } 
    }

    function is_registered($variable) {
      if( $this->started == false ) {
        return false;
      }
      return isset($_SESSION[$variable]);
    }

    function unregister($variable) {
      if( $this->started == false ) {
        unset($this->pseudo[$variable]);
        return false;
      }
      if( isset($_SESSION[$variable]) ) {
        unset($_SESSION[$variable]);
        return true;
      }
      return false;
    }

    function id($sessid = '') {
      if (!empty($sessid)) {
        return session_id($sessid);
      } else {
        return session_id();
      }
    }

    function name($name = '') {
      if (!empty($name)) {
        return session_name($name);
      } else {
        return session_name();
      }
    }

    function close($exit=true) {
      if( $this->started == false ) {
        if( !$exit ) return;
        exit();
      }

      if( isset($_SESSION) && is_array($_SESSION) ) {
        if( SESSION_PHP_HANDLING == 'false') {
          $serial = serialize($_SESSION);
          $this->_sess_write($this->id(), $serial);
        } else {
          session_write_close();
        }
      }
      if( !$exit ) return;
      exit();
    }

    function destroy() {
      if( $this->started == false ) {
        return false;
      }

      // Unset all of the session variables.
      $_SESSION = array();

      // If its desired to kill the session, also delete the session cookie.
      // Note: This will destroy the session, and not just the session data!
      if (isset($_COOKIE[session_name()])) {
        $session_data = session_get_cookie_params();
        setcookie($this->name(), '', 0, $session_data['path'], $session_data['domain']);
        unset($_COOKIE[$this->name()]);
      }

      $this->started = false;
      // Finally, destroy the session.
      return session_destroy();
    }

    function save_path($path = '') {
      if (!empty($path)) {
        return session_save_path($path);
      } else {
        return session_save_path();
      }
    }

    function recreate() {
      global $SID, $g_navigation;

      if( $this->started == false ) {
        return;
      }

      $old_session = $_SESSION;
      $old_session_id = session_id();
      session_regenerate_id();
      $new_session_id = session_id();
      session_id($old_session_id);
      $this->destroy();

      // Establish session handlers
      if( SESSION_PHP_HANDLING == 'true') {
        session_set_save_handler(
                                 array(&$this, '_sess_open'), 
                                 array(&$this, '_sess_close'), 
                                 array(&$this, '_sess_read'), 
                                 array(&$this, '_sess_write'), 
                                 array(&$this, '_sess_destroy'), 
                                 array(&$this, '_sess_gc')
                                );
      } else {
        $this->_sess_gc(0);
      }

      session_id($new_session_id);
      session_start();
      $_SESSION = $old_session;
      // set SID once, even if empty
      $SID = (defined('SID') ? SID : '');
      $g_navigation->update_session();
    }

    //-Session Handlers-----------------------------------------------------------
    function _sess_open($save_path, $session_name) {
      return true;
    }

    function _sess_close() {
      return true;
    }

    function _sess_read($key) {
      global $g_db;
      $value_query = $g_db->query("select value from " . TABLE_SESSIONS . " where sesskey = '" . $g_db->input($key) . "' and expiry > '" . time() . "'");
      if( $value = $g_db->fetch_array($value_query) ) {
        if( SESSION_PHP_HANDLING == 'false') {
          $result = unserialize($value['value']);
          return $result;
        } else {
          return $value['value'];
        }
      }
      return ("");
    }

    function _sess_write($key, $val) {
      global $g_db;

      if( !isset($g_db) || !is_object($g_db) ) {
        echo '<br /><br /><b style="color: #FF0000;">Late session call failure. Check your scripts and close the session before exit!</b>';
        return;
      }

      $expiry = time() + $this->life;
      $value = $val;

      $check_query = $g_db->query("select count(*) as total from " . TABLE_SESSIONS . " where sesskey = '" . $g_db->input($key) . "'");
      $check = $g_db->fetch_array($check_query);

      $laddress = tep_ip2_long(tep_get_ip_address());

      if ($check['total'] > 0) {
        return $g_db->query("update " . TABLE_SESSIONS . " set expiry = '" . $g_db->input($expiry) . "', value = '" . $g_db->input($value) . "' where sesskey = '" . $g_db->input($key) . "'");
      } else {
        return $g_db->query("insert into " . TABLE_SESSIONS . " values ('" . $g_db->input($key) . "', '" . $g_db->input($expiry) . "', '" . $g_db->input($value) . "', '" . $g_db->input($laddress) . "')");
      }
    }

    function _sess_destroy($key) {
      global $g_db;
      return $g_db->query("delete from " . TABLE_SESSIONS . " where sesskey = '" . $g_db->input($key) . "'");
    }

    function _sess_gc($maxlifetime) {
      global $g_db;
      $g_db->query("delete from " . TABLE_SESSIONS . " where expiry < '" . time() . "'");

      return true;
    }
    //-Session Handlers EOM-------------------------------------------------------
  }
?>
