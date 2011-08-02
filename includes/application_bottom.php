<?php
/*
  $Id: application_bottom.php,v 1.14 2003/02/10 22:30:41 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - PHP5 Register Globals off and Long Arrays Off support added
// - Added SEO-G support functions
// - Changed database, session functions to use the classes
// - Integrated file as part of a class
// - Added Plugins Support
// - Transformed script for CMS, removed unrelated code
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
//-MS- SEO-G Added
  global $g_seo_url;
  if( isset($g_seo_url) && is_object($g_seo_url) ) {
    $g_seo_url->cache_urls();
  }
//-MS- SEO-G Added EOM

// close session (store variables)
  $cSessions->close(false);
  if( STORE_PAGE_PARSE_TIME == 'true' || DISPLAY_PAGE_PARSE_TIME == 'true') {
    $time_start = explode(' ', PAGE_PARSE_START_TIME);
    $time_end = explode(' ', microtime());
    $parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
    if( file_exists(STORE_PAGE_PARSE_TIME_LOG) ) {
      error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' - ' . getenv('REQUEST_URI') . ' (' . $parse_time . 's)' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }
    if (DISPLAY_PAGE_PARSE_TIME == 'true') {
      echo '<span style="font-size: small;">Parse Time: ' . $parse_time . 's</span>';
    }
  }
  $cPlug->invoke('final_terminate');
  if( GZIP_COMPRESSION == 'true' ) {
    include('functions/gzip_compression.php');
    tep_gzip_output(GZIP_LEVEL);
  }
?>
