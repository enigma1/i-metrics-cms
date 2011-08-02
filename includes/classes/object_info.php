<?php
/*
  $Id: object_info.php,v 1.6 2003/06/20 16:23:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Added strip parameter to the constructor to optionally bypass filtering.
// Set the strip parameter to false when the input comes from a 
// trusted source. For example direct database access. This avoids cases
// and code complexity, having multiple prepare_input statements to be 
// applied to the same string.
// Added strings support file with variables into an object
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class objectInfo {

// class constructor
    function objectInfo($object_array, $strip=true) {

      foreach($object_array as $key => $value) {
        if( $strip ) {
          $this->$key = tep_sanitize_string($value);
        } else {
          $this->$key = $value;
        }
      }
    }
  }

  function tep_get_strings($metrics_file) {
    if( !is_file($metrics_file) ) return false;

    require($metrics_file);
    $strings = get_defined_vars();
    unset($strings['metrics_file']);
    return new objectInfo($strings, false);
  }

  function tep_get_strings_array($metrics_file) {
    if( !is_file($metrics_file) ) return array();
    require($metrics_file);
    $strings = get_defined_vars();
    unset($strings['metrics_file']);
    return $strings;
  }
?>
