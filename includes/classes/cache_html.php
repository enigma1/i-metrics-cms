<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Cache History class
// Cache history for HTML pages. Sends a 304 header on cache hits.
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class cache_html {
    // Compatibility constructor
    function cache_html() {
      $this->reset();
    }

    function reset() {
      $this->tags_array = array();
      $this->script_type = $this->script_duration = $this->script_params = $this->script_signature = '';
      $this->bot_cacheable = false;
      $this->declines = 0;
    }

    function load() {
      extract(tep_load('sessions'));
      $this->tags_array =& $cSessions->register('cache_html_tags_array', array());
      $this->declines =& $cSessions->register('cache_html_declines', 0);
    }

    function was_bot_cacheable() {
      return $this->bot_cacheable;
    }

    function flush_cache() {
      $this->tags_array = array();
    }

    function flush_tag($tag) {
      unset($this->tags_array[$tag]);
    }

    function get_time_offset($offset, $now=true) {
      $newtime = $offset;
      if( $now ) {
        $newtime += time();
      }
      $gmt_time = gmdate('D, d M Y H:i:s', $newtime).' GMT';
      return $gmt_time;
    }

    //-MS- Sessions Cache HTML
    function check_script() {
      extract(tep_load('defs', 'http_validator', 'database', 'sessions', 'message_stack'));

      if( SCRIPTS_HTML_CACHE_ENABLE == 'false' || !$cSessions->has_started() )
        return;

      $this->load();

      // Flush caching on POST
      if( $http->req == 'POST' ) {
        $this->flush_cache();
        return;
      }

      // Abort cacheing on errors
      $message_array = $msg->get();
      if( count($message_array) ) {
        return;
      }

      $md5_script = md5($cDefs->script);
      $check_query = $db->query("select cache_html_type, cache_html_duration, cache_html_params from " . TABLE_CACHE_HTML . " where cache_html_key = '" . $db->filter($md5_script) . "'");
      if( !$db->num_rows($check_query) ) {
        return;
      }

      $check_array = $db->fetch_array($check_query);
      $this->script_type = $check_array['cache_html_type'];
      $this->script_duration = $check_array['cache_html_duration'];
      $this->script_params = $check_array['cache_html_params'];

      if( $this->script_type == 1 ) {
        $this->check_cache();
      } elseif($this->script_type == 2) {
        $this->flush_cache();
      } elseif($this->script_type == 3) {
        if( SCRIPTS_HTML_CACHE_PARAMS == 'false' ) {
          $this->flush_cache();
          return;
        }
        $params_array = explode(',', $check_array['cache_html_params']);
        foreach($params_array as $key => $value) {
          if( isset($_GET[trim($value)]) ) {
            $this->flush_cache();
            return;
          }
        }
        $this->check_cache();
      }
    }

    function check_cache() {
      extract(tep_load('defs', 'sessions', 'validator'));
      $this->script_signature = md5($cDefs->script . implode('', array_keys($cValidator->get_array)) . implode('', $cValidator->get_array));

      if( !isset($this->tags_array[$this->script_signature]) ) {
        $this->tags_array[$this->script_signature] = $cDefs->script;
      } else {
        $this->set_cache();
      }
      $this->set_headers();
    }

    function set_cache() {
      extract(tep_load('http_validator'));

      $oldtime = time() - $this->script_duration;
      if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);

        $expiry = strtotime($if_modified_since);
        if($expiry > $oldtime) {
          $this->set_cache_record(true);
          $expiry = $this->get_time_offset($expiry+$this->script_duration, false);
          if( GZIP_COMPRESSION == 'true' ) {
            ob_end_clean();
          }
          $http->set_headers(
            'Pragma: private', 
            'Expires: ' . $expiry, 
            'Cache-Control: must-revalidate, max-age=0, s-maxage=0, private',
            'HTTP/1.1 304 Not Modified'
          );
          $http->send_headers(true);
        }
      } else {
        // Browser doesn't want to cache content
        $this->declines++;
      }
    }

    function set_headers() {
      extract(tep_load('http_validator'));

      $this->set_cache_record();
      $now = $this->get_time_offset(0);
      $expiry = $this->get_time_offset($this->script_duration);

      $http->set_headers(
        'Pragma: private', 
        'Last-Modified: ' . $now,
        'Expires: ' . $expiry,
        'ETag: "' . $this->script_signature . '"',
        'Cache-Control: must-revalidate, max-age=0, s-maxage=0, private'
      );
      $http->send_headers();
    }

    function set_cache_record($hit = false) {
      extract(tep_load('defs', 'database'));

      if( SCRIPTS_HTML_CACHE_HITS == 'false' )
        return;

      $md5_script = md5($cDefs->script);
      $check_query = $db->query("select cache_html_key from " . TABLE_CACHE_HTML_REPORTS . " where cache_html_key = '" . $db->filter($md5_script) . "'");
      if( $db->num_rows($check_query) ) {
        if( $hit == false ) {
          $db->query("update " . TABLE_CACHE_HTML_REPORTS . " set cache_misses = cache_misses+1 where cache_html_key = '" . $db->filter($md5_script) . "'");
        } else {
          $db->query("update " . TABLE_CACHE_HTML_REPORTS . " set cache_hits  = cache_hits+1 where cache_html_key = '" . $db->filter($md5_script) . "'");
        }
      } else {
        $sql_data_array = array(
          'cache_html_key' => $db->prepare_input($md5_script),
          'cache_html_script' => $db->prepare_input($cDefs->script)
        );

        if( $hit == false ) {
          $sql_insert_array = array(
            'cache_misses' => '1'
          );
        } else {
          $sql_insert_array = array(
            'cache_hits' => '1'
          );
        }
        $sql_data_array = array_merge($sql_data_array, $sql_insert_array);
        $db->perform(TABLE_CACHE_HTML_REPORTS, $sql_data_array);
      }
    }
    //-MS- Sessions Cache HTML

    //-MS- Spiders Cache HTML
    // These HTML Cache functions used only for spiders
    function bot_check_modified_header() {
      extract(tep_load('defs', 'database'));

      if( SPIDERS_HTML_CACHE_ENABLE == 'false' )
        return;

      if( SPIDERS_HTML_CACHE_GLOBAL == 'true' ) {
        $this->bot_send_304_header(SPIDERS_HTML_CACHE_TIMEOUT);
        return;
      }

      $md5_script = md5($cDefs->script);
      $check_query = $db->query("select cache_html_duration from " . TABLE_CACHE_HTML . " where cache_html_type !='2' and cache_html_key = '" . $db->filter($md5_script) . "'");
      if( $db->num_rows($check_query) ) {
        $check_array = $db->fetch_array($check_query);
        $this->bot_send_304_header($check_array['cache_html_duration']);
      }
    }

    function bot_send_304_header($timeout) {
      extract(tep_load('defs', 'http_validator'));

      $oldtime = time() - $timeout;
      if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
        $expiry = strtotime($if_modified_since);
        if($expiry > $oldtime) {
          $this->bot_set_cache_record(true);
          $expiry = $this->get_time_offset($expiry+$timeout, false);
          if( GZIP_COMPRESSION == 'true' ) {
            ob_end_clean();
          }
          $http->set_headers(
            'Pragma: public',
            'Expires: ' . $expiry,
            'Cache-Control: must-revalidate, max-age=' . $timeout . ', s-maxage=' . $timeout . ', public',
            'HTTP/1.1 304 Not Modified'
          );
          $http->send_headers(true);
        }
      }
      $this->bot_set_cache_record();
      $script_signature = md5($cDefs->script . implode('', array_keys($_GET)) . implode('', $_GET));
      $now = $this->get_time_offset(0);
      $expiry = $this->get_time_offset($timeout);
      $this->bot_cacheable = true;
      $http->set_headers(
        'Pragma: public',
        'Last-Modified: ' . $now,
        'Expires: ' . $expiry,
        'ETag: "' . $script_signature . '"',
        'Cache-Control: must-revalidate, max-age=' . $timeout . ', s-maxage=' . $timeout . ', public'
      );
      $http->send_headers();
    }

    function bot_set_cache_record($hit = false) {
      extract(tep_load('defs', 'database'));

      if( SPIDERS_HTML_CACHE_HITS == 'false' ) return;

      $md5_script = md5($cDefs->script);
      $check_query = $db->query("select cache_html_key from " . TABLE_CACHE_HTML_REPORTS . " where cache_html_key = '" . $db->filter($md5_script) . "'");
      if( $db->num_rows($check_query) ) {
        if( $hit == false ) {
          $db->query("update " . TABLE_CACHE_HTML_REPORTS . " set cache_spider_misses = cache_spider_misses+1 where cache_html_key = '" . $db->filter($md5_script) . "'");
        } else {
          $db->query("update " . TABLE_CACHE_HTML_REPORTS . " set cache_spider_hits  = cache_spider_hits+1 where cache_html_key = '" . $db->filter($md5_script) . "'");
        }
      } else {
        $sql_data_array = array(
          'cache_html_key' => $db->prepare_input($md5_script),
          'cache_html_script' => $db->prepare_input($cDefs->script)
        );
        if( $hit == false ) {
          $sql_insert_array = array(
            'cache_spider_misses' => '1'
          );
        } else {
          $sql_insert_array = array(
            'cache_spider_hits' => '1'
          );
        }
        $sql_data_array = array_merge($sql_data_array, $sql_insert_array);
        $db->perform(TABLE_CACHE_HTML_REPORTS, $sql_data_array);
      }
    }
    //-MS- Spiders Cache HTML EOM
  }
?>
