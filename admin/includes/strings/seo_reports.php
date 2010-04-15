<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO-G Reports for osC Admin
// Featuring:
// - Display Recorded SEO-G URLs
// - Delete/Edit individual SEO-G URLs
// - Google XML Sitemap
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
define('HEADING_TITLE', 'SEO-G Reports');

define('TABLE_HEADING_SELECT', 'Select');
define('TABLE_HEADING_ORIGINAL', 'osC URL');
define('TABLE_HEADING_CONVERTED', 'SEO-G URL');
define('TABLE_HEADING_PRIORITY', 'Priority');
define('TABLE_HEADING_FREQUENCY', 'Frequency');
define('TABLE_HEADING_HITS', 'Hits');
define('TABLE_HEADING_DATE_ADDED', 'Date Added');
define('TABLE_HEADING_LAST_MODIFIED', 'Last Modified');

define('TEXT_SORT_SEO_URL', 'SEO URL');
define('TEXT_SORT_ORG_URL', 'Original URL');
define('TEXT_SORT_DATE_ADDED', 'Date Added');
define('TEXT_SORT_LAST_MODIFIED', 'Last Modified');
define('TEXT_SORT_HITS', 'Hits');
define('TEXT_SORT_FREQUENCY', 'Frequency');

define('TEXT_DISPLAY_NUMBER_OF_SEO_SCRIPTS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> SEO-G URLs)');

define('TEXT_INFO_SEO_G', 'This list contains all URLs in use by SEO-G. The <b>delete all</b> button erases the current list. The regular <b>delete</b> button with the check boxes can be used to delete individual entries. Using the <b>edit</b> button you can edit entries those selected/ticked. The <b>google xml</b> button generates an xml file for the Google sitemap using the listed SEO-G urls. When the <b>notify google</b> checkbox is ticked, the sitemap file is automatically placed on the osCommerce catalog root folder and Google is notified. Otherwise the sitemap file becomes available for download. The <b>redirect</b> button moves the selected URLs into the redirection table. The <b>validate</b> button checks the recorded urls for original (osC URLs) duplicates. Also note, delete operations do not remove products, categories or other entities created by the G-Controller. Listed URLs are those used on the catalog end.');
define('TEXT_INFO_DELETE_ALL_URLS', 'This operation will erase all recorded SEO-G urls. Note this does not remove products, categories or other entities created by the G-Controller. Listed URLs are those used on the catalog.');
define('TEXT_INFO_DELETE_URLS', 'This operation will delete the recorded SEO-G urls shown below. Note this does not remove products, categories or other entities created by the G-Controller. Listed URLs are those used on the catalog.');
define('TEXT_INFO_EDIT_URLS', 'Use the edit fields below to adjust the SEO URLs parameters. Note this does not remove products, categories or other entities created by the G-Controller. Listed URLs are those used on the catalog. Valid Priority values are 0.0 to 1.0');
define('TEXT_INFO_NO_OSC_ERRORS', 'No duplicated URLs found with the original links');
define('TEXT_INFO_NO_SEO_ERRORS', 'No duplicated URLs found with the SEO friendly URLs');
define('TEXT_INFO_DUPLICATED_URLS', 'Duplicated URLs found and are shown below. Use the tick-boxes select the URLs you want to delete then click the delete button.');

define('WARNING_NOTHING_SELECTED', 'No entries selected. Use the checkboxes to select entries first');
define('SUCCESS_CACHE_CLEARED', 'SEO-G Cache Flush Operation Complete');
define('SUCCESS_URLS_CLEARED', 'All SEO-G URLs were removed');
?>
