<?php
/*
  $Id: html_output.php,v 1.29 2003/06/25 20:32:44 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - 07/05/2007: PHP5 Register Globals and Long Arrays Off support added
// - 07/08/2007: PHP5 Long Arrays Off support added
// - 03/05/2009: Added Thumbnailer support OTF and enchantments
// - 03/05/2009: Converted code for CMS removed unused functions
// - 03/05/2009: HTML tag fixes for XHTML compliant code generation
// - 03/05/2009: Added Catalog Calculation Image Functions
// - 03/05/2009: Setup buttons for the Strings Folder
// - 03/05/2009: Text Button Functions Added
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

////
// The HTML href link wrapper function
  function tep_href_link($page = '', $parameters = '', $url_encode=true) {

    $link = HTTP_SERVER . DIR_WS_ADMIN . $page;

    if( !empty($parameters)) {
      $parameters = tep_sort_parameter_string($parameters);
      $link .= '?' . tep_output_string($parameters);
    }

    while( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

    if( $url_encode ) {
      $link = str_replace('&', '&amp;', $link);
    }
    return $link;
  }

  function tep_catalog_href_link($page = '', $parameters = '', $connection = 'NONSSL') {

    $link = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;

    if ($connection == 'SSL' && defined('ENABLE_SSL_CATALOG') && ENABLE_SSL_CATALOG == 'true') {
      $link = HTTPS_CATALOG_SERVER . DIR_WS_CATALOG;
    }
    $link .= $page;

    if( !empty($parameters)) {
      $parameters = tep_sort_parameter_string($parameters);
      $link .= '?' . tep_output_string($parameters);
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

    $link = str_replace('&', '&amp;', $link);
    return $link;
  }

////
// The HTML image wrapper function
  function tep_image($src, $alt = '', $width = '', $height = '', $params = '') {
    $image = '<img src="' . tep_output_string($src) . '"';
    return tep_image_params($image, $alt, $width, $height, $params);
  }


////
// The HTML image wrapper function
  function tep_catalog_image($src, $alt = '', $width = '', $height = '', $params = '') {
    global $g_cserver;

    $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
    $image_filesize = @filesize($images_path.$src);
    $src = $g_cserver . DIR_WS_CATALOG_IMAGES . $src;
    $image = '<img src="' . $src . '"';

    $resize = true;

    if( strstr($width,'%') !== false || strstr($height,'%') !== false ) { 
      $resize = false; 
    } elseif( empty($width) && empty($height) ) { 
      $resize = false; 
    }

    if( !$resize ) {
      return tep_image_params($image, $alt, $width, $height, $params);
    }

    if( $image_filesize < 1024 ) {
      if( empty($image_filesize) && IMAGE_REQUIRED == 'false' ) {
        return '';
      }
      return tep_image_params($image, $alt, $width, $height, $params);
    }   

    // Get the image's information
    $image_size = @getimagesize($src);
    if( empty($image_size) && IMAGE_REQUIRED == 'false') {
      return '';
    }
    if( !is_array($image_size) || count($image_size) < 2 || !$image_size[0] || !$image_size[1] ) {
      if( IMAGE_REQUIRED == 'false' ) {
        return '';
      }
    }

    tep_image_dimensions($width, $height, $image_size[0], $image_size[1]);
    return tep_image_params($image, $alt, $width, $height, $params);
  }


/*
////
// The HTML image wrapper function
  function tep_catalog_image($src, $alt = '', $width = '', $height = '', $parameters = '') {
    global $g_cserver;

    $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
    $image_filesize = @filesize($images_path.$src);
    $src = $g_cserver . DIR_WS_CATALOG_IMAGES . $src;

    if( $image_filesize < 1024 ) {
      $resize = false;
    } elseif( empty($width) || empty($height) ) {
      $resize = false;
    } elseif( strstr($width,'%') == false && strstr($height,'%') == false) { 
      $resize = true; 
    } else {
      $width = $image_size[0];
      $height = $image_size[1];
      $resize = false;
    }

    if( $resize ) {
      $image_size = @getimagesize($src);
      if( empty($image_size) && IMAGE_REQUIRED == 'false') { 
        return '';
      }
      if( !is_array($image_size) || count($image_size) < 2 || !$image_size[0] || !$image_size[1] ) {
        return '';
      }

      $ratio = $image_size[1] / $image_size[0];

      // Set the width and height to the proper ratio
      if( $image_size[0] < $width && $image_size[1] < $height) { 
        $resize = false;
      } elseif (!$width && $height) { 
        $ratio = $height / $image_size[1]; 
        $width = $image_size[0] * $ratio;
      } elseif ($width && !$height) { 
        $ratio = $width / $image_size[0]; 
        $height = $image_size[1] * $ratio;
      } elseif (!$width && !$height) { 
        $width = $image_size[0]; 
        $height = $image_size[1]; 
      } 

      if( $resize && ($image_size[0] != $width || $image_size[1] != $height) ) { 
        $rx = $image_size[0] / $width; 
        $ry = $image_size[1] / $height; 
  
        if ($rx < $ry) { 
          $width = $height / $ratio;
        } else { 
          $height = $width * $ratio;
        }
        $width = intval($width); 
        $height = intval($height);
      } else {
        $width = $image_size[0];
        $height = $image_size[1];
      }
    }

    $image = '<img src="' . tep_output_string($src) . '" border="0" alt="' . tep_output_string($alt) . '"';

    if( !empty($alt) ) {
      $image .= ' title="' . tep_output_string($alt) . '"';
    }

    if( !empty($width) && !empty($height) ) {
      $image .= ' width="' . tep_output_string($width) . '" height="' . tep_output_string($height) . '"';
    }

    if( !empty($parameters)) {
      $image .= ' ' . $parameters;
    }

    $image .= ' />';

    return $image;
  }
*/

  function tep_catalog_calculate_image($src, &$width, &$height, $relative_path = 0) {
    global $g_crelpath, $g_cserver;

    $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);

    if( $relative_path == 1 ) {
      $rel_path = $g_cserver . DIR_WS_CATALOG_IMAGES;
    } elseif($relative_path == 2) {
      $rel_path = '';
    } else {
      $length = strlen(DIR_FS_CATALOG);
      $rel_path = substr($images_path, $length);
    }

    $resize = true;
    // Get the image's information
    if( $image_size = @getimagesize($images_path.$src) ) { 

      if( !is_array($image_size) || count($image_size) < 2 || !$image_size[0] || !$image_size[1] ) {
        $image = '<img src="' . DIR_WS_CATALOG_TEMPLATE . 'design/' .  IMAGE_NOT_AVAILABLE . '"';
        return tep_image_params($image, $alt, $width, $height, $params);
      }

      if( tep_image_dimensions($width, $height, $image_size[0], $image_size[1]) ) {
        $image = $g_crelpath . 'fly_thumb.php?no_cache=1&img='. $rel_path . $src.'&amp;w='.tep_output_string($width).'&amp;h='.tep_output_string($height);
        return $image;
      } else {
        $image = $g_cserver . DIR_WS_CATALOG_IMAGES . $src;
        return $image;
      }
    } 
    return ''; 
  }

/*
  function tep_catalog_calculate_image($src, &$width, &$height, $relative_path = 0) {
    global $g_crelpath, $g_cserver;

    $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);

    if( $relative_path == 1 ) {
      $rel_path = $g_cserver . DIR_WS_CATALOG_IMAGES;
    } elseif($relative_path == 2) {
      $rel_path = '';
    } else {
      $length = strlen(DIR_FS_CATALOG);
      $rel_path = substr($images_path, $length);
    }

    $resize = true;
    // Get the image's information
    if( $image_size = @getimagesize($images_path.$src) ) { 
      $ratio = $image_size[1] / $image_size[0];
      
      // Set the width and height to the proper ratio
      if( $image_size[0] < $width && $image_size[1] < $height) { 
        $resize = false;
      } elseif (!$width && $height) { 
        $ratio = $height / $image_size[1]; 
        $width = $image_size[0] * $ratio;
      } elseif ($width && !$height) { 
        $ratio = $width / $image_size[0]; 
        $height = $image_size[1] * $ratio;
      } elseif (!$width && !$height) { 
        $width = $image_size[0]; 
        $height = $image_size[1]; 
      } 
      // Scale the image if not the original size
      if( $resize && ($image_size[0] != $width || $image_size[1] != $height) ) { 
        $rx = $image_size[0] / $width; 
        $ry = $image_size[1] / $height; 
  
        if ($rx < $ry) { 
          $width = $height / $ratio;
        } else { 
          $height = $width * $ratio;
        }
        $width = intval($width); 
        $height = intval($height); 
        $image = $g_crelpath . 'fly_thumb.php?no_cache=1&img='. $rel_path . $src.'&amp;w='.tep_output_string($width).'&amp;h='.tep_output_string($height);
        return $image;
      } else {
        $width = $image_size[0];
        $height = $image_size[1];
        $image = $g_cserver . DIR_WS_CATALOG_IMAGES . $src;
        return $image;
      }
    } 
    return ''; 
  }
*/

  function tep_image_dimensions(&$x,&$y,$dx,$dy) {
    $result = false;

    if( !$dx || !$dy ) return $result;

    if( $dx < $x && $dy < $y ) {
      $x = $dx;
      $y = $dy;
      return $result;
    }

    $ratio = $dy/$dx;
    // Set the width and height to the proper ratio
    if (!$x && $y) { 
      $ratio = $y / $dy; 
      $x = $dx * $ratio;
    } elseif ($x && !$y) { 
      $ratio = $x / $dx; 
      $y = $dy * $ratio;
    } elseif( !$x && !$y ) { 
      $x = $dx; 
      $y = $dy; 
    }

    // Scale calculations
    if( $x != $dx || $y != $dy ) { 
      $rx = $dx / $x; 
      $ry = $dy / $y; 

      if ($rx < $ry) { 
        $x = $y / $ratio;
      } else { 
        $y = $x * $ratio;
      }
      $x = intval($x); 
      $y = intval($y); 
      $result = true;
    }
    return $result;
  }

  function tep_image_params($image, $alt = '', $width = '', $height = '', $params = '') {
    // Add remaining image parameters if they exist
    if ($width) { 
      $image .= ' width="' . tep_output_string($width) . '"'; 
    } 
    if ($height) { 
      $image .= ' height="' . tep_output_string($height) . '"'; 
    }       
    if (tep_not_null($params)) $image .= ' ' . $params;
    if( !empty($alt) ) {
      $image .= ' title="' . tep_output_string($alt) . '"';
    }
    $image .= ' alt="' . tep_output_string($alt) . '"';
    //$image .= ' border="0" />';
    $image .= ' />';
    return $image;
  }

////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function tep_image_submit($image, $alt = '', $parameters = 'class="dflt"') {

    $image_submit = '<input type="image" src="' . tep_output_string(DIR_WS_STRINGS . 'images/buttons/' . $image) . '" alt="' . tep_output_string($alt) . '"';
    if( !empty($alt) ) {
      $image_submit .= ' title="' . tep_output_string($alt) . '"';
    }
    if( !empty($parameters) ) {
      $image_submit .= ' ' . $parameters;
    }
    $image_submit .= ' />';
    return $image_submit;
  }

////
// The HTML form submit main image wrapper function
// Outputs a button in the selected language
  function tep_main_image_submit($image, $alt = '', $parameters = '') {

    $image_submit = '<input type="image" src="' . $image . '" alt="' . tep_output_string($alt) . '"';
    if( !empty($alt)) {
      $image_submit .= ' title="' . tep_output_string($alt) . '"';
    }
    if( !empty($parameters) ) {
      $image_submit .= ' ' . $parameters;
    }
    $image_submit .= ' />';
    return $image_submit;
  }

////
// The HTML form submit text wrapper function
// Outputs a text for a form
  function tep_text_submit($name, $value, $parameters = '') {
    $text_submit = '<input type="submit" name="' . $name . '"' . ' value="' . $value . '"';

    if( !empty($parameters)) {
      $text_submit .= ' ' . $parameters;
    }
    $text_submit .= ' />';
    return $text_submit;
  }

////
// Draw a 1 pixel black line
  function tep_black_line() {
    return tep_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '1');
  }

////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return tep_image(DIR_WS_IMAGES . $image, '', $width, $height);
  }

////
// Output a function button in the selected language
  function tep_image_button($image, $alt = '', $params = 'class="dflt"') {
    return tep_image(DIR_WS_STRINGS . 'images/buttons/' . $image, $alt, '', '', $params);
  }

////
// Output a form
  function tep_draw_form($name, $action, $parameters = '', $method = '', $params = '') {
    $method = strtolower($method);
    $action = basename($action);
    if($method != 'post' && $method != 'get') $method = 'post';

    $form = '<form name="' . tep_output_string($name) . '"';
    $form .= ' action="' . ($method == 'post'?tep_href_link($action, $parameters):$action) . '"';
    $form .= ' method="' . $method . '"';
    if( !empty($params) ) {
      $form .= ' ' . $params;
    }
    $form .= '>';

    return $form;
  }

////
// Output a form input field
  function tep_draw_input_field($name, $value = '', $parameters = '', $required = false, $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if( isset($GLOBALS[$name]) && $reinsert_value == true ) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif( !empty($value) ) {
      $field .= ' value="' . tep_output_string($value) . '"';
    }

    if( !empty($parameters) ) {
      $field .= ' ' . $parameters;
    }

    $field .= ' />';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

////
// Output a form password field
  function tep_draw_password_field($name, $value = '', $required = false) {
    $field = tep_draw_input_field($name, $value, 'maxlength="40"', $required, 'password', false);

    return $field;
  }

////
// Output a form filefield
  function tep_draw_file_field($name, $parameters='', $required = false) {
    $field = tep_draw_input_field($name, '', $parameters, $required, 'file');

    return $field;
  }


////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
  function tep_draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '') {
    $selection = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) $selection .= ' value="' . tep_output_string($value) . '"';

    if ( ($checked == true) || ( isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && ( ($GLOBALS[$name] == 'on') || (isset($value) && (stripslashes($GLOBALS[$name]) == $value)) ) ) ) {
      $selection .= ' checked="checked"';
    }

    if (tep_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= ' />';

    return $selection;
  }

////
// Output a form checkbox field
  function tep_draw_checkbox_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'checkbox', $value, $checked, $parameters);
  }

////
// Output a form radio field
  function tep_draw_radio_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'radio', $value, $checked, $parameters);
  }


////
// Output a form textarea field
  function tep_draw_textarea_field($name, $wrap, $width='', $height='', $text = '', $parameters = '', $reinsert_value = true) {
    $field = '<textarea name="' . tep_output_string($name) . '"';

    if( !empty($width) ) {
      $field .= ' cols="' . tep_output_string($width) . '"';
    }

    if( !empty($height) ) {
      $field .= ' rows="' . tep_output_string($height) . '"';
    }

    if( !empty($parameters) ) {
      $field .= ' ' . $parameters;
    }

    $field .= '>';

    if( isset($GLOBALS[$name]) && $reinsert_value == true ) {
      $field .= tep_output_string_protected(stripslashes($GLOBALS[$name]));
    } elseif( !empty($text) ) {
      $field .= tep_output_string_protected($text);
    }

    $field .= '</textarea>';

    return $field;
  }

////
// Output a form hidden field
  function tep_draw_hidden_field($name, $value = '', $parameters = '') {
    $field = '<input type="hidden" name="' . tep_output_string($name) . '"';

    if( !empty($value) ) {
      $field .= ' value="' . tep_output_string($value) . '"';
    } elseif (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    }

    if( !empty($parameters) ) {
      $field .= ' ' . $parameters;
    }

    $field .= ' />';

    return $field;
  }

////
// Output a form pull down menu
  function tep_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . tep_output_string($name) . '"';

    if( !empty($parameters) ) {
      $field .= ' ' . $parameters;
    }

    $field .= '>';

    if (empty($default) && isset($GLOBALS[$name])) $default = stripslashes($GLOBALS[$name]);

    $values = array_values($values);
    for ($i=0, $j=count($values); $i<$j; $i++) {
      $field .= '<option value="' . tep_output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

  function tep_output_media($reset=true) {
    global $g_media;
    if( empty($g_media) ) return;

    $g_media = array_values(array_unique($g_media));
    for($i=0, $j=count($g_media); $i<$j; $i++) {
      echo $g_media[$i] . "\n";
    }
    if( $reset ) $g_media = array();
  }
?>
