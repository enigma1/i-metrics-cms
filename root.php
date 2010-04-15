<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2008 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Catalog: SEO-G Root page
// Main handler script
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  require('includes/init_early.php');

  if( !isset($g_seo_url) || !is_object($g_seo_url) ) {
    echo '<b>SEO-G Error</b>: Unable to initialize - Missing or incorrect SEO-G files';
    exit();
  }

  $osc_url = $osc_params = $osc_parse = '';
  if( $g_seo_url->get_osc_url($g_server . $_SERVER['REQUEST_URI'], $osc_url, $osc_params, $osc_parse) ) {

    $PHP_SELF = $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'] = $osc_parse['path'];
    if(basename($_SERVER['PHP_SELF']) == 'root.php' ) {
      echo '<b>SEO-G Error</b>: Invalid Self-Request - Check recorded URLs';
      exit();
    }
    $tmp_array = array();

    for($i=0, $j=count($osc_params); $i<$j; $i++ ) {
      $array_equal = explode('=', $osc_params[$i]);
      if( is_array($array_equal) ) {
        if( isset($array_equal[1]) ) {
          $tmp_array[$array_equal[0]] = $array_equal[1];
        } else {
          $tmp_array[$array_equal[0]] = '';
        }
        if( isset($g_seo_url->query_array[$array_equal[0]]) ) {
          unset($g_seo_url->query_array[$array_equal[0]]);
        }
      }
    }
    if( isset($_GET) && is_array($_GET) ) {
      $tmp_array = array_merge($_GET,$tmp_array);
    }

    // Synchronize global arrays.
    $_GET = $tmp_array;
    unset($tmp_array);
    // Synchronize query string variables
    $_SERVER['QUERY_STRING'] = implode('&',$osc_params);

    $g_script = basename($PHP_SELF);
    // Signal SEO-G translation.
    $g_seo_flag = true;
    $g_seo_url->cache_init($g_seo_url->osc_key);
    require(basename($PHP_SELF));

  } elseif( file_exists(basename($osc_parse['path']))) {
    $PHP_SELF = $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'] = $osc_parse['path'];
    if(basename($_SERVER['PHP_SELF']) == 'root.php' ) {
      echo '<b>SEO-G Error</b>: Invalid Self-Request, passed URI Request: ' . $_SERVER['REQUEST_URI'];
      exit();
    }
    $g_script = basename($PHP_SELF);
    $g_seo_flag = true;
    require(basename($osc_parse['path']));
  } else {
    // Script not found. Initiate redirection
    header("HTTP/1.1 " . SEO_DEFAULT_ERROR_HEADER);
    header('Location: ' . $g_relpath . SEO_DEFAULT_ERROR_REDIRECT);
  }
  exit();
?>
