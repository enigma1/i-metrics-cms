<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Cache Reports Strings File
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
define('HEADING_TITLE', 'Cache Reports');

define('TABLE_HEADING_FILENAME', 'Filename');
define('TABLE_HEADING_HITS', 'Script Hits');
define('TABLE_HEADING_MISSES', 'Script Misses');
define('TABLE_HEADING_EFFICIENCY', 'Efficiency');
define('TABLE_HEADING_SPIDER_HITS', 'Spider Hits');
define('TABLE_HEADING_SPIDER_MISSES', 'Spider Misses');
define('TABLE_HEADING_SPIDER_EFFICIENCY', 'Spider Efficiency');

define('TEXT_DISPLAY_NUMBER_OF_CACHE_SCRIPTS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> scripts)');

define('TEXT_INFO_HTML', 'Select the script entries to update or delete from the following list.<br />Type Notes: \'<b>Cache</b>\' script will be cached in visitor\'s browser for his current visit. \'<b>Flush</b>\' Script will invalidate the history cache.  \'<b>Parametric</b>\' Script will check for the parameters specified in the $HTTP_GET_VARS global array before flushing the cache, otherwise it will cache the page for the specified period. Use coma separated parameters for example <em><b>action,saction</b></em> Non-listed scripts will not be cached nor will invalidate the cache.');
?>
