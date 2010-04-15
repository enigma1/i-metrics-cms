<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2008 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Catalog: Groups Front class
// Manages the Group fields for products
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class form_fields {
    var $types_array = array(
      array('id' => '1', 'text' => 'STATIC-TEXT'),
      array('id' => '2', 'text' => 'RADIO'),
      array('id' => '3', 'text' => 'RADIO-IMAGE'),
      array('id' => '4', 'text' => 'DROP-DOWN'),
      array('id' => '5', 'text' => 'INPUT-LINE'),
      array('id' => '6', 'text' => 'TEXT-AREA'),
      array('id' => '7', 'text' => 'CHECK-BOX'),
    );
    var $layout_array = array(
      array('id' => '1', 'text' => 'LINEAR'),
      array('id' => '2', 'text' => 'MATRIX'),
    );
    var $flat_array;

    // compatibility constructor
    function form_fields() {
      $this->flat_array = tep_array_invert_flat($this->types_array, 'id', 'text');
    }

    function get_field_info($fID) {
      global $g_db;

      $result = array();
      $form_fields_query = $g_db->query("select form_fields_id, form_fields_name, form_fields_description, layout_id, limit_id from " . TABLE_FORM_FIELDS . " where form_fields_id = '" . (int)$fID . "' and status_id='1'");
      if( !$g_db->num_rows($form_fields_query) ) return $result;
      $form_fields_array = $g_db->fetch_array($form_fields_query);
      return $form_fields_array;

    }

    function get_options($fID) {
      global $g_db;

      $result = array();
      $options_query_raw = "select form_options_id, form_types_id, form_options_name, image_status, layout_id, limit_id from " . TABLE_FORM_OPTIONS . " where form_fields_id = '" . (int)$fID . "' and status_id = '1' order by sort_id";
      $result = $g_db->query_to_array($options_query_raw, 'form_options_id');
      return $result;
    }

    function get_values($oID) {
      global $g_db;

      $result = array();
      $values_query_raw = "select form_values_id, form_values_name, form_values_image from " . TABLE_FORM_OPTIONS . " where form_options_id = '" . (int)$oID . "' and status_id = '1' order by sort_id";
      $result = $g_db->query_to_array($values_query_raw, 'form_values_id');
      return $result;
    }

    function get($fID) {
      $result = array(
        'data' => $this->get_field_info($fID),
        'options' => $this->get_options($fID),
      );
      $options_array = $result['options'];
      foreach($options_array as $option => $options_data ) {
        $values_array = $this->get_values($option);
        $html_type = 0;
        if( isset($this->flat_array[$options_data['form_types_id']]) ) {
          $html_type = $this->flat_array[$options_data['form_types_id']];
        }
        $drop_down_array = array();
        foreach( $values_array as $key => $value) {
          switch($html_type) {
            case 'CHECK-BOX':
              $result['options'][$option]['values'][$key] = array(
                'name' => $value['form_values_name'],
                'default' => $value['form_values_default'],
                'html' => tep_draw_checkbox_field('checkbox[' . $value['form_values_id'] . ']', 'on') . '<sep>' . $value['form_values_name']
              );
              break;
            case 'DROP_DOWN':
              $drop_down_array[] = array('id' => $value['form_values_id'], 'text' => $value['form_values_name']);
              break;
            case 'INPUT-LINE':
              $result['options'][$option]['values'][$key] = array(
                'name' => $value['form_values_name'],
                'default' => $value['form_values_default'],
                'html' => $value['form_values_name'] . '<sep>' . tep_draw_input_field('input[' . $value['form_values_id'] . ']')
              );
              break;
            case 'RADIO':
              $selection = false;
              if( $value['form_values_name'] == $value['form_values_default']) {
                $selection = true;
              }
              $result['options'][$option]['values'][$key] = array(
                'name' => $value['form_values_name'],
                'default' => $value['form_values_default'],
                'html' => tep_draw_radio_field('radio[' . $option['form_options_id'] . ']', $value['form_values_name'], $selection) . '<sep>' .  $value['form_values_name']
              );
              break;
            case 'TEXT-AREA':
              $result['options'][$option]['values'][$key] = array(
                'name' => $value['form_values_name'],
                'default' => $value['form_values_default'],
                'html' => ''
              );
              break;
            default: 
              if( empty($result['options'][$option]['values'][$key]) ) {
                $result['options'][$option]['values'][$key] = array(
                  'name' => $value['form_values_name'],
                  'default' => $value['form_values_default'],
                  'html' => $value['form_values_name'] . '<sep>' . $value['form_values_default']
                );
              }
              break;
          }
        }
        if( $html_type == 'DROP_DOWN' ) {
          $result['options'][$option]['values'][$key] = array(
            'name' => $option['form_options_name'],
            'default' => '',
            'html' => $option['form_options_name'] . '<sep>' . tep_draw_pull_down_menu('dropdown[' . $option['form_options_id'] . ']', $drop_down_array)
          );
        }
      }
      return $result;
    }

    function insert_field($input) {
      global $g_db;

      $result = 0;
      if( !isset($input['form_fields_name']) ) return $result;

      if( !isset($input['form_fields_description']) ) $input['form_fields_description'] = '';
      if( !isset($input['layout']) || $input['layout'] <= 0 ) $input['layout'] = 1;
      if( !isset($input['limit']) || $input['limit'] <= 0 ) $input['limit'] = 1;
      if( !isset($input['sort_id']) ) $input['sort_id'] = 1;
      if( !isset($input['status_id']) ) $input['status_id'] = 1;

      $sql_data_array = array(
        'form_fields_name' => $g_db->prepare_input($input['form_fields_name']),
        'form_fields_description' => $g_db->prepare_input($input['form_fields_description']),
        'layout_id' => (int)$input['layout'],
        'limit_id' => (int)$input['limit'],
        'sort_id' => (int)$input['sort_id'],
        'status_id' => (int)$input['status_id'],
      );
      $g_db->perform(TABLE_FORM_FIELDS, $sql_data_array);
      $field_id = $g_db->insert_id();
      return $field_id;
    }

    function insert_option($input) {
      global $g_db;

      $result = 0;
      if( !isset($input['form_fields_id']) || !isset($input['form_types_id']) || empty($input['form_types_id']) ) return $result;
      if( !isset($input['form_options_name']) || empty($input['form_options_name']) ) return $result;

      $check_query = $g_db->query("select count(*) as total from " . TABLE_FORM_FIELDS . " where form_fields_id = '" . (int)$input['form_fields_id'] . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( !$check_array['total'] ) return $result;

      if( !isset($input['image_status']) ) $input['image_status'] = 0;
      if( !isset($input['layout']) || $input['layout'] <= 0 ) $input['layout'] = 1;
      if( !isset($input['limit']) || $input['limit'] <= 0 ) $input['limit'] = 1;
      if( !isset($input['sort_id']) ) $input['sort_id'] = 1;
      if( !isset($input['status_id']) ) $input['status_id'] = 1;

      $sql_data_array = array(
        'form_fields_id' => (int)$input['form_fields_id'],
        'form_options_name' => $g_db->prepare_input($input['form_options_name']),
        'image_status' => (int)$input['image_status'],
        'layout_id' => (int)$input['layout'],
        'limit_id' => (int)$input['limit'],
        'sort_id' => (int)$input['sort_id'],
        'status_id' => (int)$input['status_id'],
      );

      $g_db->perform(TABLE_FORM_OPTIONS, $sql_data_array);
      $option_id = $g_db->insert_id();
      return $option_id;
    }

    function insert_value($input) {
      global $g_db;

      $result = 0;
      if( !isset($input['form_options_id']) || !isset($input['form_fields_id']) ) return $result;

      $check_query = $g_db->query("select count(*) as total from " . TABLE_FORM_OPTIONS . " where form_fields_id = '" . (int)$input['form_fields_id'] . "' and form_options_id = '" . (int)$input['form_options_id'] . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( !$check_array['total'] ) return $result;

      if( !isset($input['form_values_name']) ) $input['form_values_name'] = null;
      if( !isset($input['form_values_image']) ) $input['form_values_image'] = null;

      if( !isset($input['sort_id']) ) $input['sort_id'] = 1;
      if( !isset($input['status_id']) ) $input['status_id'] = 1;

      $sql_data_array = array(
        'form_fields_id' => (int)$input['form_fields_id'],
        'form_options_id' => (int)$input['form_options_id'],
        'form_values_name' => $g_db->prepare_input($input['form_values_name']),
        'form_values_image' => $g_db->prepare_input($input['form_values_image']),
        'sort_id' => (int)$input['sort_id'],
        'status_id' => (int)$input['status_id'],
      );

      $g_db->perform(TABLE_FORM_VALUES, $sql_data_array);
      $value_id = $g_db->insert_id();
      return $value_id;
    }

    function create_from_xml($file) {
      $result = false;

      $result_array = array();

      require_once(DIR_WS_CLASSES . 'xml_core.php');
      $obj = new xml_parse();
      $parse_array = $obj->xml_file_parse($file);
      if( empty($parse_array) || !isset($parse_array['forms']) ) return $result_array;

      $options_array = $this->insert($parse_array['forms'], 'insert_field', 'form_options');
      foreach($options_array as $field => $options_data) {
        if( !isset($options_data[0]) ) {
          $options_data['form_fields_id'] = $field;
        } else {
          for($i=0, $j=count($value_data); $i<$j; $i++) {
            $options_data[$i]['form_fields_id'] = $field;
          }
        }
        $result_array[] = $field;

        $tmp_data = array($field => $options_data);
        $values_array = $this->insert($tmp_data, 'insert_option', 'form_values');

        foreach($values_array as $option => $values_data) {
          if( !isset($values_data[0]) ) {
            $values_data['form_fields_id'] = $field;
            $values_data['form_options_id'] = $option;
          } else {
            for($i=0, $j=count($values_data); $i<$j; $i++) {
              $values_data[$i]['form_fields_id'] = $field;
              $values_data[$i]['form_options_id'] = $option;
            }
          }
          $tmp_data = array( $option => $values_data);
          $this->insert($tmp_data, 'insert_value');
        }
      }
      return $result_array;
    }

    function insert($parse_array, $method='', $index='') {
      $result_array = array();

      foreach($parse_array as $key => $value) {
        if( !isset($value[0]) ) {
          $result = $this->$method($value);
          if( !$result ) return $result_array;
          $result_array[$result] = (!empty($index) && isset($value[$index]))?$value[$index]:$value;
          break;
        } else {
          for($i=0, $j=count($value); $i<$j; $i++) {
            $result = $this->$method($value[$i]);
            if( !$result ) continue;
            $result_array[$result] = (!empty($index) && isset($value[$i][$index]))?$value[$i][$index]:$value[$i];
          }
        }
      }
      return $result_array;
    }
  }
?>
