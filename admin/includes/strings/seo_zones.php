<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO Zones component for Admin
// Language strings
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

define('HEADING_TITLE', 'SEO-G Zones');
define('HEADING_HELP_TITLE', 'Help with Text Pages');

define('HEADING_SUB_TITLE', 'Multi SEO/Zones Options');
define('TABLE_HEADING_SEO_TYPE', 'Type');
define('TABLE_HEADING_SEO_ZONES', 'Zones');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_COMMENT', 'Comment');

define('TEXT_INFO_HEADING_NEW_ZONE', 'New Zone');
define('TEXT_INFO_NEW_ZONE_INTRO', 'Please enter the new zone information');

define('TEXT_INFO_HEADING_EDIT_ZONE', 'Edit Zone');
define('TEXT_INFO_EDIT_ZONE_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_ZONE', 'Delete Zone');
define('TEXT_INFO_DELETE_ZONE_INTRO', 'Are you sure you want to delete this zone?');

define('TEXT_INFO_HEADING_NEW_SUB_ZONE', 'New Sub Zone');
define('TEXT_INFO_NEW_SUB_ZONE_INTRO', 'Please enter the new sub zone information');

define('TEXT_INFO_HEADING_EDIT_SUB_ZONE', 'Edit Sub Zone');
define('TEXT_INFO_EDIT_SUB_ZONE_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_SUB_ZONE', 'Delete Sub Zone');
define('TEXT_INFO_DELETE_SUB_ZONE_INTRO', 'Are you sure you want to delete this sub zone?');

define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_ZONE_TYPE', 'SEO Zone Type:');
define('TEXT_INFO_ZONE_NAME', 'SEO Zone Name:');
define('TEXT_INFO_ZONE_CLASS', 'Associated Class Script:');
define('TEXT_INFO_ZONE_HANDLER', 'Secondary Handlers:');
define('TEXT_INFO_ZONE_SUBFIX', 'Secondary Subfixes:');
define('TEXT_INFO_ZONE_PREFIX', 'Assigned Prefix:');

define('TEXT_INFO_NUMBER_ENTRIES', 'Number of Entries:');
define('TEXT_INFO_NO_ENTRIES', 'There are no entries defined for this zone.<br />Use the form below to insert entries to this zone. Options are associated with the class type assigned to this zone.');
define('TYPE_BELOW', 'All Zones');
define('PLEASE_SELECT', 'All Zones');

define('TABLE_HEADING_SELECT', 'Select');
define('TABLE_HEADING_MODE', 'Mode');
define('TABLE_HEADING_NAME', 'Name');

define('TEXT_INFO_ASSIGN_TEXT', 'Assign Text Entries');
define('TEXT_INFO_ASSIGN_TEXT_HELP', 'Click the <b>Assign Text Entries box</b> to insert other <b>text page</b> entries into this SEO-G zone. Once you assign text page entries you can change their names using the form below. Alternatively individual friendly names can be edited directly when you edit a text page from the Content Management section');
define('TEXT_INFO_ASSIGN_COLLECTIONS', 'Assign Collections');
define('TEXT_INFO_ASSIGN_COLLECTIONS_HELP', 'Click the <b>Assign Collections box</b> to insert other <b>collections</b> into this SEO-G zone of collections. Once you assign the collections you can change their names using the form below. Alternatively individual friendly names can be edited directly when you edit a collection from the Content Management section');
define('TEXT_INFO_ASSIGN_SCRIPTS', 'Assign Scripts');
define('TEXT_INFO_ASSIGN_SCRIPTS_HELP', 'Click the <b>Assign Scripts box</b> to insert entire <b>scripts</b> into scripts SEO-G zone and modify their names to match your site\'s content. By default SEO-G will create links for scripts loaded based on its configuration settins. Once you assign the scripts you can specify different friendly names for the URLs creation using the form below. If a script operates exclusively via GET parameters there is no reason to add it here as its name alone will never be exposed from the web-front.');


define('TEXT_SELECT_MULTIABSTRACT', 'Select the abstract zones to insert from the following list. Use the header shortcut links for quick and easy selection.');
define('TEXT_SELECT_MULTIZONES', 'Select the entries from the categories form below to insert into this zone. <br />Note: Duplicate entries are filtered.');
define('TEXT_SELECT_MULTIGTEXT', 'Select the text entries to insert from the following list. Use the header shortcut links for quick and easy selection.');
define('TEXT_SELECT_MULTISCRIPTS', 'Select the filenames to process from the following list. SEO-G only processes files from the root directory of the store. You should only include filenames that need a different name exposed on the front end. Filenames that are handled excusively by a parameter (ex: generic_text.php is always handled by gtext_id) do not need exposure');
define('TEXT_DELETE_MULTIZONE_CONFIRM', 'The following entries will be deleted from the <b>%s</b> zone');
define('TEXT_DELETE_MULTIZONE', 'Delete Selected Zones');
define('TEXT_UPDATE_MULTIZONE', 'Update Selected Zones');
define('TEXT_SWITCH_ABSTRACT_ZONES', 'Switch to Abstract Zones Mode');
define('TEXT_SWITCH_GTEXT', 'Switch to Text Entries');
define('TEXT_SWITCH_RANGES', 'Switch to Numeric Ranges');
define('TEXT_SWITCH_SCRIPTS', 'Switch to Scripts/Filenames');
define('TEXT_INSERT_ALL', 'Insert All');

define('TEXT_VALIDATE', 'Validate');
define('TABLE_HEADING_ENTRIES', 'Entry');
define('TABLE_HEADING_GTEXT', 'Generic Text Entries');
define('TABLE_HEADING_SCRIPTS', 'Filenames/Scripts');
define('TABLE_HEADING_ABSTRACT_ZONE', 'Zone');

define('TEXT_PAGE_SELECT', 'Page Select On/Off');
define('TEXT_ALL_VALUES', 'All Values');
define('TEXT_DISPLAY_NUMBER_OF_SEO_ZONES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> seo zones)');

define('TEXT_SELECT_MULTIENTRIES', 'Select the entries to insert from the following list. Entries can then be controlled from the main sub-zone and be related only with this seo zone.');
define('ERROR_INVALID_NAME', 'Invalid name for collection ID: %s');
define('WARNING_NOTHING_SELECTED', 'No entries selected. Use the checkboxes to select entries first');
define('SUCCESS_SELECTED_ADDED', 'Selected Entries Added');
?>
