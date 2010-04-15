<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin HTML Cache strings
// Inserts scripts to be cached or invalidated
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
define('HEADING_TITLE', 'Cache HTML');
define('HEADING_TITLE2', 'Insert Script Entries');

define('TABLE_HEADING_FILENAME', 'Filename');
define('TABLE_HEADING_DURATION', 'Duration');
define('TABLE_HEADING_SELECT', 'Select');
define('TABLE_HEADING_TYPE', 'Script Type');
define('TABLE_HEADING_PARAMETERS', 'Parameters');
define('TEXT_INSERT', 'Insert script entry into the cache list');
define('TEXT_UPDATE', 'Update selected script entries');

define('TEXT_PAGE_SELECT', 'Page Select On/Off');
define('ERROR_CACHE_DIRECTORY_DOES_NOT_EXIST', 'Error: Cache directory does not exist. Please set this Configuration->Cache.');
define('ERROR_CACHE_DIRECTORY_NOT_WRITEABLE', 'Error: Cache directory is not writeable.');

define('TEXT_INFO_DELETE', 'Are you sure you want to remove these script entries? <br />Note: No files are removed only the database table for the html cache is updated');
define('TEXT_INFO_MAIN', 'Select the script entries to update or delete from the following list.<br />Type Notes: \'<b>Cache</b>\' script will be cached in visitor\'s browser for his current visit. \'<b>Flush</b>\' Script will invalidate the history cache.  \'<b>Parametric</b>\' Script will check for the parameters specified in the $HTTP_GET_VARS global array before flushing the cache, otherwise it will cache the page for the specified period. Use coma separated parameters for example <em><b>action,saction</b></em> Non-listed scripts will not be cached nor will invalidate the cache.');
define('TEXT_INFO_MAIN2', 'This operation simply inserts script entries to the database. These entries are used by the HTML cache on the catalog end to signal whether a script should be cached or not. No files are modified.');

define('WARNING_NOTHING_SELECTED', 'No entries selected. Use the checkboxes to select entries first');
define('SUCCESS_ENTRY_INSERT', 'Selected entry was inserted');
define('SUCCESS_ENTRY_REMOVED', 'Selected entries were removed');
?>
