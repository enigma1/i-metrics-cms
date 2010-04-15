<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2007 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO-G Redirects for osC Admin
// Featuring:
// - Display Redirection SEO-G URLs
// - Delete/Edit redirection SEO-G URLs
// - Redirection Validator
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
define('HEADING_TITLE', 'SEO-G Redirects');

define('TABLE_HEADING_SELECT', 'Select');
define('TABLE_HEADING_ORIGINAL', 'Target URL');
define('TABLE_HEADING_CONVERTED', 'Source URL');
define('TABLE_HEADING_HITS', 'Hits');
define('TABLE_HEADING_LAST_MODIFIED', 'Last Seen');
define('TABLE_HEADING_REDIRECT', 'Redirection');

define('TEXT_DISPLAY_NUMBER_OF_SEO_SCRIPTS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> SEO-G URLs)');

define('TEXT_INFO_SEO_G', 'This list contains all redirection URLs in use by SEO-G. The <b>delete all</b> button erases the current list. The regular <b>delete</b> button with the check boxes can be used to delete individual entries. Using the <b>edit</b> button you can edit entries those selected/ticked. The <b>validate</b> button checks the redirected urls listed here for duplicates vs the recorded URLs');
define('TEXT_INFO_DELETE_ALL_URLS', 'This operation will erase all redirection SEO-G urls. Note this does not remove products, categories or other entities created by the G-Controller. Listed URLs are those used on the catalog.');
define('TEXT_INFO_DELETE_URLS', 'This operation will delete the redirection SEO-G urls shown below. Listed URLs are those used on the catalog end to signify a redirection.');
define('TEXT_INFO_EDIT_URLS', 'Use the edit fields below to adjust the SEO URLs parameters. Listed URLs are those used on the catalog end for redirection purposes. Select the redirection option from the drop-down list below.');
define('TEXT_INFO_NO_ERRORS', 'No errors found with the redirection URLs');
define('TEXT_INFO_DUPLICATED_URLS', 'Common URLs found in both reports and redirection tables and are shown below. Use the tick-boxes select the URLs you want to delete then click the delete button. Entries will be removed from the redirection table only.');
?>
