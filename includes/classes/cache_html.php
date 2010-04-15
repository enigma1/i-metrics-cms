<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
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

  class cacheHTML {
    var $tags_array, $script, $script_type, $script_duration, $script_params, $script_signature;

    function cacheHTML() {
      global $g_script;
      $this->script = $g_script;
      $this->md5_script = md5($this->script);
      $this->tags_array = array();
    }

    function check_script() {
      global $g_script, $g_session, $g_db;

      if( SCRIPTS_HTML_CACHE_ENABLE == 'false' )
        return;

      $message_array =& $g_session->register('g_message_stack');
      // Abort cacheing on errors
      if( is_array($message_array) && count($message_array) ) {
        return;
      }

      // Flush on post
      if( count($_POST) ) {
        $this->flush_cache();
        return;
      }

      $this->script = $g_script;
      $this->md5_script = md5($this->script);
      $check_query = $g_db->query("select cache_html_type, cache_html_duration, cache_html_params from " . TABLE_CACHE_HTML . " where cache_html_key = '" . $g_db->filter($this->md5_script) . "'");
      if( !$g_db->num_rows($check_query) ) {
        return;
      }

      $check_array = $g_db->fetch_array($check_query);
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

    function flush_cache() {
      $this->tags_array = array();
    }

    function flush_tag($tag) {
      unset($this->tags_array[$tag]);
    }

    function check_cache() {
      global $g_validator;
      $this->script_signature = md5($this->script . implode('', array_keys($g_validator->get_array)) . implode('', $g_validator->get_array));

      if( !isset($this->tags_array[$this->script_signature]) ) {
        $this->tags_array[$this->script_signature] = $this->script;
      } else {
        $this->set_cache();
      }
      $this->set_headers();
    }

    function set_cache() {
      global $g_session;
      $oldtime = time() - $this->script_duration;
      if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
        $expiry = strtotime($if_modified_since);
        if($expiry > $oldtime) {
          $this->set_cache_record(true);
          $expiry = tep_get_time_offset($expiry+$this->script_duration, false);
          header('Pragma: private');
          header('Expires: ' . $expiry);
          header('Cache-Control: must-revalidate, max-age=0, s-maxage=0, private');
          header('HTTP/1.1 304 Not Modified');
          $g_session->close();
        }
      }
    }

    function set_headers() {
      $this->set_cache_record();
      $now = tep_get_time_offset(0);
      $expiry = tep_get_time_offset($this->script_duration);
      header('Pragma: private');
      header('Last-Modified: ' . $now);
      header('Expires: ' . $expiry);
      header('ETag: "' . $this->script_signature . '"');
      header('Cache-Control: must-revalidate, max-age=0, s-maxage=0, private');
    }

    function set_cache_record($hit = false) {
      global $g_db;

      if( SCRIPTS_HTML_CACHE_HITS == 'false' )
        return;

      $md5_script = md5($this->script);
      $check_query = $g_db->query("select cache_html_key from " . TABLE_CACHE_HTML_REPORTS . " where cache_html_key = '" . $g_db->filter($md5_script) . "'");
      if( $g_db->num_rows($check_query) ) {
        if( $hit == false ) {
          $g_db->query("update " . TABLE_CACHE_HTML_REPORTS . " set cache_misses = cache_misses+1 where cache_html_key = '" . $g_db->filter($md5_script) . "'");
        } else {
          $g_db->query("update " . TABLE_CACHE_HTML_REPORTS . " set cache_hits  = cache_hits+1 where cache_html_key = '" . $g_db->filter($md5_script) . "'");
        }
      } else {
        $sql_data_array = array(
          'cache_html_key' => $g_db->prepare_input($md5_script),
          'cache_html_script' => $g_db->prepare_input($this->script)
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
        $g_db->perform(TABLE_CACHE_HTML_REPORTS, $sql_data_array);
      }
    }
  }
?>
