<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Catalog: Validator for /GET /POST parameters class
// Validates parameters passed
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

  class validator {
    var $get_array, $get_keys, $post_array, $post_keys;
    var $default_filter='/<\?((?!\?>).)*\?>/s';

    // class constructor
    function validator() {
      extract(tep_load('sessions'));

      $this->get_array = array();
      $this->post_array = array();

      $this->get_keys = array(
        'abz_id' => 'abz_id', 
        'gtext_id' => 'gtext_id', 
        'page' => 'page', 
        'action' => '',
      );

      $this->post_keys = array(
      );

      $this->forbid_keys = array(
        'GLOBALS' => 'GLOBALS',
        '_SESSION' => '_SESSION',
        '_COOKIE' => '_COOKIE',
        '_GET' => '_GET',
        '_POST' => '_POST',
        '_REQUEST' => '_REQUEST',
        '_SERVER' => '_SERVER',
        '_ENV' => '_ENV',
        '_FILES' => '_FILES',
        'g_external' => 'g_external',
      );

      foreach($this->forbid_keys as $key => $value) {
        if( isset($_REQUEST[$key]) ) {
          $this->process_error();
        }
      }

      foreach($this->get_keys as $key => $value) {
        if( !isset($_GET[$key]) ) continue;
        $method = 'get_' . $key;
        if( method_exists($this, 'get_' . $key) ) {
          $result = $this->$method($_GET[$key]);
          if($result !== false ) {
            if( tep_not_null($value) ) {
              $this->get_array[$key] = $result;
            }
          } else {
            $this->process_error();
          }
        }
      }

      if( $cSessions->has_started() ) {
        foreach($this->post_keys as $key => $value) {
          if( !isset($_POST[$key]) ) continue;
          $method = 'post_' . $key;
          if( method_exists($this, 'post_' . $key) ) {
            $result = $this->$method($value);
            if($result !== false ) {
              if( tep_not_null($value) ) {
                $this->post_array[$key] = $result;
              }
            } else {
              $this->process_error();
            }
          }
        }
      }
    }

    function remove_get_entry($key) {
      unset($this->get_array[$key]);
    }

    function remove_post_entry($key) {
      unset($this->post_array[$key]);
    }

    function process_error() {
      extract(tep_load('sessions'));

      $cSessions->destroy();
      tep_redirect();
    }

    function common_strip($value, $default_filter='') {
      $result = '';
      $strip = false;
      if( empty($default_filter) ) {
        $default_filter = $this->default_filter;
        $strip = true;
      }
      if( !is_array($value) && !empty($value) ) {
        $search = array($default_filter);
        $result = preg_replace($search, '', $value);
        if( $strip ) {
          $result = strip_tags($result);
        }
      }
      return $result;
    }

    function common_validate($input, $filter) {
      $result = '';
      $tmp_input = $this->common_strip($input, $filter);
      if( $input == $tmp_input ) {
        $result = $tmp_input;
      }
      return $result;
    }

    function post_validate($input_array) {
      extract(tep_load('sessions'));

      $result_array = array();
      if( !is_array($input_array) || !count($input_array) ) {
        return $result_array;
      }

      $default_array = array(
        'max' => 1000,
        'min' => 10,
        'filter' => $this->default_filter,
        'type' => 'string',
      );

      foreach($input_array as $key => $value) {
        $result_array[$key] = array();
        $property_array = $default_array;

        if( is_array($value) ) {
          foreach($value as $property => $pvalue ) {
            if( isset($property_array[$property]) ) {
              $property_array[$property] = $pvalue;
            }
          }
        }

        if( isset($_POST[$key]) ) {
          $handled = false;
          switch($property_array['type']) {
            case 'range':
              if( $_POST[$key] < $property_array['min'] ) {
                $result_array[$key]['min'] = $property_array['min'];
              }
              if( $_POST[$key] > $property_array['max'] ) {
                $result_array[$key]['max'] = $property_array['max'];
              }
              break;
            case 'string':
            default:
              settype($_POST[$key], "string");
              $_POST[$key] = $this->convert_chars(stripslashes($_POST[$key]));
              if( tep_string_length($_POST[$key]) > $property_array['max'] ) {
                $result_array[$key]['max'] = $property_array['max'];
              }
              if( tep_string_length($_POST[$key]) < $property_array['min'] ) {
                $result_array[$key]['min'] = $property_array['min'];
              }
              $handled = true;
              break;
          }
          if( !$handled ) {
            $_POST[$key] = $this->common_validate($_POST[$key], $property_array['filter']);
          }
          // In case a parameter conflicts with a session one skip
          if( !$cSessions->is_registered($_POST[$key]) ) {
            $GLOBALS[$key] = $_POST[$key];
          }
        } else {
          $result_array[$key]['check'] = false;
        }
      }
      return $result_array;
    }

    function convert_chars($input, $filter = array('<','>') ){
      if( in_array('<', $filter) ) $input = str_replace('<','&lt;', $input);
      if( in_array('>', $filter) ) $input = str_replace('>','&gt;', $input);
      if( in_array('"', $filter) ) $input = str_replace('"','&quot;', $input);
      if( in_array('\'', $filter) ) $input = str_replace('\'','&#039;', $input);
      if( in_array('&', $filter) ) $input = str_replace('&','&amp;', $input);
      return $input;
    }

    function convert_to_get($exclude_array=array(), $include_array=array()) {
      $tmp_array = $this->get_array;
      foreach($exclude_array as $value) {
        unset($tmp_array[$value]);
      }
      foreach($include_array as $key => $value) {
        $tmp_array[$key] = $value;
      }
      ksort($tmp_array);
      return tep_params_to_string($tmp_array);
    } 

    function get_abz_id($id) {
      extract(tep_load('defs', 'database'));

      if( !defined('TABLE_ABSTRACT_ZONES') ) {
        return false;
      }

      $org_id = $id;
      $check_query = $db->query("select abstract_zone_id from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$id . "' and status_id='1'");
      $id = false;
      if( !$db->num_rows($check_query) ) {
        return false;
      }

      $check_array = $db->fetch_array($check_query);
      $id = $check_array['abstract_zone_id'];

      if( $id != $org_id ) {
        $id = false;
      } else {
        $cDefs->abstract_id = $id;
      }
      return $id;    
    }

    function get_gtext_id($id) {
      extract(tep_load('defs', 'database'));

      if( !defined('TABLE_GTEXT') ) {
        return false;
      }

      $org_id = $id;
      $check_query = $db->query("select gtext_id from " . TABLE_GTEXT . " where gtext_id = '" . (int)$id . "' and sub='0' and status='1'");
      $id = false;

      if( !$db->num_rows($check_query) ) {
        return false;
      }

      $check_array = $db->fetch_array($check_query);
      $id = $check_array['gtext_id'];

      if( $id != $org_id ) {
        $id = false;
      } else {
        $cDefs->gtext_id = $id;
      }
      return $id;    
    }

    function get_page($id) {
      extract(tep_load('defs'));

      $org_id = (int)$id;
      if( $org_id <= 0 ) {
        return false;
      }
      $org_id .= "";
      if( $org_id == $id ) {
        $cDefs->page_id = $id;
        return $id;
      }
      return false;
    }

    function get_action($action) {
      $result = false;
      $tmp_action = $this->common_strip($action);
      if( $action == $tmp_action ) {
        $result = $tmp_action;
      }
      return $result;
    }

    function update_get($param, $value) {
      extract(tep_load('history'));

      $old_array = $this->get_array;
      $this->get_array[$param] = $value;
      $cHistory->update_get_entry($old_array, $this->get_array);
    }

    function update_post($param, $value) {
      extract(tep_load('history'));

      $old_array = $this->post_array;
      $this->post_array[$param] = $value;
      $cHistory->update_post_entry($old_array, $this->post_array);
    }
  }
?>
