<?php
/*
  $Id: html_output.php,v 1.56 2003/07/09 01:15:48 hpdl Exp $

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
// - 03/05/2009: Integrated SEO-G and image thumbnailer
// - 03/05/2009: HTML tag fixes for XHTML compliant code generation
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
////
// The HTML href link wrapper function
  function tep_href_link($page = '', $parameters = '', $connection = '', $add_session_id = true, $search_engine_safe = true, $url_encode=true) {
//-MS- SEO-G Added
    global $request_type, $g_session, $SID, $g_seo_url;
//-MS- SEO-G Added EOM

    $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
    if( $connection == 'SSL' && ENABLE_SSL == true) {
      $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
    } else {
      $connection = 'NONSSL';
    }

    if($page == FILENAME_DEFAULT) $page ='';

    if( !empty($parameters) ) {
      $parameters = tep_sort_parameter_string($parameters);
      $link .= $page . '?' . tep_output_string($parameters);
      $separator = '&';
    } else {
      $link .= $page;
      $separator = '?';
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if( SESSION_FORCE_COOKIE_USE == 'False' && $add_session_id == true && isset($g_session) && is_object($g_session) && $g_session->has_started() ) {
      if( !empty($SID) ) {
        $_sid = $SID;
      } elseif( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if( HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN ) {
          $_sid = $g_session->name() . '=' . $g_session->id();
        }
      }
    }

//-MS- SEO-G Added
    if( isset($g_seo_url) && is_object($g_seo_url) ) {
      if( $connection == 'NONSSL' || SEO_PROCESS_SSL == 'true' )
        $link = $g_seo_url->get_seo_url($link, $separator, $search_engine_safe);
    }
//-MS- SEO-G Added EOM

    if( isset($_sid) ) {
      $link .= $separator . tep_output_string($_sid);
    }

    if( $url_encode && !empty($parameters) ) {
      $link = str_replace('&', '&amp;', $link);
    }
    return $link;
  }

// -MS- Requires further optimization, get rid of the getimagesize calls, pass them to flythumb
  function tep_image($src, $alt = '', $width = '', $height = '', $params = '', $bForce = false, $full_path=false) { 
    global $g_relpath, $g_external_path;

    if( !$full_path ) {
      $image = '<img src="' . $src . '"';
    } else {
      $image = '<img src="' . $g_relpath . $src . '"';
    }

    $resize = true;

    if( $bForce == true ) {
      $resize = false;
    } elseif( strstr($width,'%') !== false || strstr($height,'%') !== false ) { 
      $resize = false; 
    } elseif( empty($width) && empty($height) ) { 
      $resize = false; 
    }

    if( !$resize ) {
      return tep_image_params($image, $alt, $width, $height, $params);
    }

    if( !$full_path && !empty($g_external_path) ) {
      return tep_external_image($src, $alt, $width, $height, $params, $g_external_path);
    }

    $image_filesize = @filesize($src);
    if( $image_filesize < 1024 ) {
      if( empty($image_filesize) && IMAGE_REQUIRED == 'false' ) {
        return '';
      }
      return tep_image_params($image, $alt, $width, $height, $params);
    }   

    // Get the image's information
    if( $image_size = @getimagesize($src) ) { 
      if( empty($image_size) && IMAGE_REQUIRED == 'false') { 
        return '';
      }
      if( !is_array($image_size) || count($image_size) < 2 || !$image_size[0] || !$image_size[1] ) {
        $image = '<img src="' . DIR_WS_TEMPLATE . 'design/' .  IMAGE_NOT_AVAILABLE . '"';
        return tep_image_params($image, $alt, $width, $height, $params);
      }

      if( tep_image_dimensions($width, $height, $image_size[0], $image_size[1]) ) {
        if( !$full_path ) {
          $premade = false;
          $tmp_array = explode('/',$src);
          if( is_array($tmp_array) && count($tmp_array) ) {
            $new_name = $tmp_array[count($tmp_array)-1];
            $new_name = substr($new_name, 0, -4);
            $base_image = FLY_THUMB_FOLDER . $new_name . FLY_THUMB_POSTFIX . $width . 'x' . $height;
            $check_image = $base_image . '.jpg';

            if( file_exists(DIR_FS_CATALOG . $check_image) ) {
              $image = '<img src="' . $check_image .'"';
              $premade = true;
            }
          }
          if( !$premade ) {
            $image = '<img src="fly_thumb.php?img='.$src.'&amp;w='.tep_output_string($width).'&amp;h='.tep_output_string($height).'"';
          }
        } else {
          $image = '<img src="' .  $g_relpath . 'fly_thumb.php?img='.$src.'&amp;w='.tep_output_string($width).'&amp;h='.tep_output_string($height).'"';
        }
        $width=$height=0;
      }
    } elseif( IMAGE_REQUIRED == 'false' ) { 
      return ''; 
    } 
    return tep_image_params($image, $alt, $width, $height, $params);
  }


  function tep_external_image($src, $alt = '', $width = '', $height = '', $params = '', $path='') { 
    $image = '<img src="' . $src . '"';
    $resize = false;
    if (strstr($width,'%') == false && strstr($height,'%') == false) { 
      $resize = true; 
    }

    if( !$resize ) {
      return tep_image_params($image, $alt, $width, $height, $params);
    }

    // Get the image's information
    if( $image_size = @getimagesize($path . $src) ) { 
      if( empty($image_size) && IMAGE_REQUIRED == 'false') { 
        return '';
      }
      if( !is_array($image_size) || count($image_size) < 2 || !$image_size[0] || !$image_size[1] ) {
        return tep_image_params($image, $alt, $width, $height, $params);
      }

      if( tep_image_dimensions($width, $height, $image_size[0], $image_size[1]) ) {
        $image = '<img src="' . $path . 'fly_thumb.php?img='.$src.'&amp;w='.tep_output_string($width).'&amp;h='.tep_output_string($height).'"';
        $width=$height=0;
      }
    } elseif( IMAGE_REQUIRED == 'false' ) { 
      return ''; 
    } 
    return tep_image_params($image, $alt, $width, $height, $params);
  }


  function tep_calculate_image($src, &$width, &$height) {
    $resize = true;
    // Get the image's information
    if( $image_size = @getimagesize($src)) {

      if( !is_array($image_size) || count($image_size) < 2 || !$image_size[0] || !$image_size[1] ) {
        $image = '<img src="' . DIR_WS_TEMPLATE . 'design/' .  IMAGE_NOT_AVAILABLE . '"';
        return tep_image_params($image, $alt, $width, $height, $params);
      }

      if( tep_image_dimensions($width, $height, $image_size[0], $image_size[1]) ) {
        $image = 'fly_thumb.php?img='.$src.'&amp;w='.tep_output_string($width).'&amp;h='.tep_output_string($height);
        return $image;
      }
    } 
    return ''; 
  }

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
    //$image .= ' border="0" />'; // use css
    $image .= ' />';
    return $image;
  }

  function tep_href_image_link($image, $check_external=true) {
    global $g_relpath, $g_external_path;

    $path = $g_relpath;
    if( !empty($g_external_path) && $check_external ) {
      $path = $g_external_path;
    }

    $link = $path . DIR_WS_IMAGES . $image;
    return $link;
  }

  function tep_link_to_form($name, $link, $type='image', $image_name='', $width='', $height='', $parameters='') {
    global $g_counter;
    $form_name = tep_create_safe_string(strtolower($name), '_') . $g_counter;
    $result = tep_draw_form($form_name, $link);
    if($type == 'image') {
      $result .= tep_main_image_submit($image_name, $name, $width, $height, $parameters);
    } else {
      $result .= tep_text_submit($form_name, $name);
    }
    $result .= '</form>';
    $g_counter++;
    return $result;
  }

////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function tep_image_submit($image, $alt = '', $parameters = '', $path = false) {
    if( !$path ) {
      $path = DIR_WS_STRINGS . 'images/buttons/';
    } else {
      $path = '';
    }
    $image_submit = '<input type="image" src="' . tep_output_string($path . $image) . '" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) $image_submit .= ' title="' . tep_output_string($alt) . '"';

    if (tep_not_null($parameters)) {
      $image_submit .= ' ' . $parameters;
    }

    $image_submit .= ' />';

    return $image_submit;
  }

////
// The HTML form submit image wrapper function
// Outputs a button in the selected language
  function tep_main_image_submit($image, $alt = '', $width = '', $height = '', $parameters = '') {
    global $language;
    $string = tep_calculate_image($image,$width,$height);
    if( empty($string) ) {
      $string = $image;
    }
    $image_submit = '<input type="image" src="' . $string . '" alt="' . tep_output_string($alt) . '"';

    //if (tep_not_null($alt)) $image_submit .= ' title="' . tep_output_string($alt) . '"';

    if( !empty($parameters) ) $image_submit .= ' ' . $parameters;

    $image_submit .= ' />';

    return $image_submit;
  }

////
// The HTML form submit text wrapper function
// Outputs a text for a form
  function tep_text_submit($name, $value, $parameters = 'class="inputResults"') {
    global $language;

    $text_submit = '<input type="submit" name="' . $name . '"' . ' value="' . $value . '"';

    if( !empty($parameters) ) $text_submit .= ' ' . $parameters;

    $text_submit .= ' />';

    return $text_submit;
  }

////
// Output a function button in the selected language
  function tep_image_button($image, $alt = '', $parameters = '') {
    global $language;

    return tep_image(DIR_WS_STRINGS . 'images/buttons/' . $image, $alt, '', '', $parameters);
  }

////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1', $full_path=false) {
    return tep_image(DIR_WS_IMAGES . $image, '', $width, $height, '', false, $full_path);
  }

////
// Output a form
  function tep_draw_form($name, $action, $method = '', $parameters = '') {
    if( empty($method) ) $method = 'post';
    $action = str_replace('&amp;', '&', $action);
    $action = str_replace('&', '&amp;', $action);
    $form = '<form name="' . tep_output_string($name) . '" action="' . tep_output_string($action) . '" method="' . tep_output_string($method) . '"';

    if( !empty($parameters) ) $form .= ' ' . $parameters;

    $form .= '>';

    return $form;
  }

////
// Output a form input field
  function tep_draw_input_field($name, $value = '', $parameters = 'class="txtInput"', $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if ( (isset($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    }

    if( !empty($parameters) ) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

////
// Output a form password field
  function tep_draw_password_field($name, $value = '', $parameters = 'maxlength="40" class="txtInput"') {
    return tep_draw_input_field($name, $value, $parameters, 'password', false);
  }

////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
  function tep_draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '') {
    $selection = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) $selection .= ' value="' . tep_output_string($value) . '"';

    if ( ($checked == true) || ( isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && ( ($GLOBALS[$name] == 'on') || (isset($value) && (stripslashes($GLOBALS[$name]) == $value)) ) ) ) {
      $selection .= ' checked="checked"';
    }

    if( !empty($parameters) ) $selection .= ' ' . $parameters;

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
  function tep_draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
//    $field = '<textarea name="' . tep_output_string($name) . '" wrap="' . tep_output_string($wrap) . '" cols="' . tep_output_string($width) . '" rows="' . tep_output_string($height) . '"';
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

    if ( (isset($GLOBALS[$name])) && ($reinsert_value == true) ) {
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

    if (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    } elseif (isset($GLOBALS[$name])) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    }

    if( !empty($parameters) ) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

////
// Output a form pull down menu
  function tep_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . tep_output_string($name) . '"';

    if( !empty($parameters) ) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && isset($GLOBALS[$name])) $default = stripslashes($GLOBALS[$name]);

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
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

  function tep_check_submit($button_name, $buttons_array=array(), $action=false ) {
    global $g_session;

    $result = false;
    if( !isset($_POST[$button_name . '_x']) || !isset($_POST[$button_name . '_y']) ||
        !tep_not_null($_POST[$button_name . '_x']) || !tep_not_null($_POST[$button_name . '_y']) ) {
      if( $action == true ) {
        $g_session->destroy();
        tep_redirect();
      }
    } else {
      $result = true;
      for( $i=0, $j=count($buttons_array); $i<$j; $i++) {
        if( isset($_POST[$buttons_array[$i] . '_x']) || isset($_POST[$buttons_array[$i] . '_y']) ) {
          $result = false;
          break;
        }
      }
    }
    return $result;
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
