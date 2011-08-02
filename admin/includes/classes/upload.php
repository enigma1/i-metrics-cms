<?php
/*
  $Id: upload.php,v 1.2 2003/06/20 00:18:30 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2006-2008 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// I-Metrics Layer
//----------------------------------------------------------------------------
// - 07/05/2007: PHP5 Register Globals and Long Arrays Off support added
// - 07/08/2007: PHP5 Long Arrays Off support added
// - 03/12/2008: Added Friendly strings for Images filenames
// - 03/12/2008: Added error control on the constructor
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  class upload {
    var $file, $filename, $destination, $permissions, $extensions, $tmp_filename, $message_location, $c_result;

    function upload($file = '', $destination = '', $permissions = '777', $extensions = '') {
      $this->c_result = false;
      $this->set_file($file);
      $this->set_destination($destination);
      $this->set_permissions($permissions);
      $this->set_extensions($extensions);

      $this->set_output_messages('direct');

      if (tep_not_null($this->file) && tep_not_null($this->destination)) {
        $this->set_output_messages('session');
        if( $this->parse() == true && $this->save() == true ) {
          $this->c_result = true;
        }
      }
    }

    function parse() {
      extract(tep_load('message_stack'));

      if (isset($_FILES[$this->file])) {
        $file = array(
          'name' => $_FILES[$this->file]['name'],
          'type' => $_FILES[$this->file]['type'],
          'size' => $_FILES[$this->file]['size'],
          'tmp_name' => $_FILES[$this->file]['tmp_name']
        );
      } elseif (isset($GLOBALS['_FILES'][$this->file])) {
        $file = array(
          'name' => $_FILES[$this->file]['name'],
          'type' => $_FILES[$this->file]['type'],
          'size' => $_FILES[$this->file]['size'],
          'tmp_name' => $_FILES[$this->file]['tmp_name']
        );
      } else {
        $file = array(
          'name' => (isset($GLOBALS[$this->file . '_name']) ? $GLOBALS[$this->file . '_name'] : ''),
          'type' => (isset($GLOBALS[$this->file . '_type']) ? $GLOBALS[$this->file . '_type'] : ''),
          'size' => (isset($GLOBALS[$this->file . '_size']) ? $GLOBALS[$this->file . '_size'] : ''),
          'tmp_name' => (isset($GLOBALS[$this->file]) ? $GLOBALS[$this->file] : '')
        );
      }

      if ( tep_not_null($file['tmp_name']) && ($file['tmp_name'] != 'none') && is_uploaded_file($file['tmp_name']) ) {
        if (sizeof($this->extensions) > 0) {
          if (!in_array(strtolower(substr($file['name'], strrpos($file['name'], '.')+1)), $this->extensions)) {
            if ($this->message_location == 'direct') {
              $msg->add(ERROR_FILETYPE_NOT_ALLOWED, 'error');
            } else {
              $msg->add_session(ERROR_FILETYPE_NOT_ALLOWED, 'error');
            }
            return false;
          }
        }
        $this->set_filename($file['name']);
        if( isset($_FILES[$this->file]['name']) ) {
          $_FILES[$this->file]['name'] = $file['name'];
        }
        $this->set_tmp_filename($file['tmp_name']);
        $this->set_file($file);

        if( empty($this->destination) ) {
          return true;
        } else {
          return $this->check_destination();
        }
      } else {
        if ($this->message_location == 'direct') {
          $msg->add(WARNING_NO_FILE_UPLOADED, 'warning');
        } else {
          $msg->add_session(WARNING_NO_FILE_UPLOADED, 'warning');
        }
        return false;
      }
    }

    function save() {
      extract(tep_load('message_stack'));

      if (substr($this->destination, -1) != '/') $this->destination .= '/';

      if( filesize($this->file['tmp_name']) <= 0 ) {
        return false;
      }

      if( move_uploaded_file($this->file['tmp_name'], $this->destination . $this->filename) ) {
        chmod($this->destination . $this->filename, $this->permissions);

        if ($this->message_location == 'direct') {
          $msg->add(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
        } else {
          $msg->add_session(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
        }

        return true;
      } else {
        if ($this->message_location == 'direct') {
          $msg->add(ERROR_FILE_NOT_SAVED, 'error');
        } else {
          $msg->add_session(ERROR_FILE_NOT_SAVED, 'error');
        }

        return false;
      }
    }

    function set_file($file) {
      $this->file = $file;
    }

    function set_destination($destination) {
      $this->destination = $destination;
    }

    function set_permissions($permissions) {
      $this->permissions = octdec($permissions);
    }

    function set_filename(&$filename) {
      $filename = $this->create_safe_string($filename);
      $this->filename = $filename;
    }

    function set_tmp_filename($filename) {
      $this->tmp_filename = $filename;
    }

    function set_extensions($extensions) {
      if (tep_not_null($extensions)) {
        if (is_array($extensions)) {
          $this->extensions = $extensions;
        } else {
          $this->extensions = array($extensions);
        }
      } else {
        $this->extensions = array();
      }
    }

    function check_destination() {
      extract(tep_load('message_stack'));

      if (!is_writeable($this->destination)) {
        if (is_dir($this->destination)) {
          if ($this->message_location == 'direct') {
            $msg->add(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
          } else {
            $msg->add_session(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
          }
        } else {
          if ($this->message_location == 'direct') {
            $msg->add(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
          } else {
            $msg->add_session(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
          }
        }

        return false;
      } else {
        return true;
      }
    }

    function set_output_messages($location) {
      switch ($location) {
        case 'session':
          $this->message_location = 'session';
          break;
        case 'direct':
        default:
          $this->message_location = 'direct';
          break;
      }
    }

    function create_safe_string($string) {
      $string = tep_create_safe_string(strtolower($string), '-', "/[^0-9a-z\/\-.]+/i");
      return $string;
    }

  }
?>
