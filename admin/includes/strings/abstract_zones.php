<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Abstract Zones Strings File - Content Groups
// Controls relationships among pages, collections etc.
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
define('HEADING_TITLE', 'Content Management Control');
define('HEADING_HELP_TITLE', 'Help on Content Collections');

define('TABLE_HEADING_ABSTRACT_TYPE', 'Content Type');
define('TABLE_HEADING_ABSTRACT_ZONES', 'Content Groups');
define('TABLE_HEADING_ABSTRACT_VISIBLE', 'Visibility');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_HEADING_NEW_ZONE', 'New Collection');
define('TEXT_INFO_NEW_ZONE_INTRO', 'Please enter the new Collection information. The Collection Name is required. Once a collection is created you will not be able to change its content type.');

define('TEXT_INFO_HEADING_EDIT_ZONE', 'Edit Collection');
define('TEXT_INFO_EDIT_ZONE_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_ZONE', 'Delete Collection');
define('TEXT_INFO_DELETE_ZONE_INTRO', 'Are you sure you want to delete this Collection?');

define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_ZONE_TYPE', 'Content Type:');
define('TEXT_INFO_ZONE_NAME', 'Collection Name:');
define('TEXT_INFO_ZONE_DESC', 'Description:');
define('TEXT_INFO_ZONE_CLASS', 'Associated Class Script:');
define('TEXT_INFO_ZONE_TABLE', 'Associated DBase Tables:');
define('TEXT_INFO_ZONE_ORDER', 'Sort Order:');
define('TEXT_INFO_ZONE_VISIBILITY', 'Visibility:');

define('TEXT_INFO_ZONE_VISIBLE', 'Visible on Front');
define('TEXT_INFO_ZONE_HIDDEN', 'Hidden from Front');

define('TEXT_INFO_UP_ONE_LEVEL', 'Up One Level');
define('TEXT_INFO_NO_ENTRIES', 'There are no entries defined for this zone.<br />Use the form below to insert new entries.');
define('TYPE_BELOW', 'All Zones');
define('PLEASE_SELECT', 'All Collections');

//-MS- SEO-G Added
define('TEXT_SEO_SECTION', 'SEO Section');
define('TEXT_SEO_NAME', 'SEO-G Name:');
define('TEXT_SEO_NAME_FORCE', 'Immediate Link');
define('TEXT_METAG', 'META-G Tags');
define('TEXT_META_TITLE', 'META Title:');
define('TEXT_META_KEYWORDS', 'META Keywords:');
define('TEXT_META_TEXT', 'META Description:');
define('WARNING_SEO_FRIENDLY_FAILED', 'Friendly Link Generation Failed - Make sure the friendly name is unique');
define('WARNING_META_WRITE_FAILED', 'Writing Meta-Tags Failed');
//-MS- SEO-G Added EOM

define('TABLE_HEADING_SELECT', 'Select');
define('TABLE_HEADING_MODE', 'Mode');
define('TABLE_HEADING_TYPE', 'Type');
define('TABLE_HEADING_PRICE', 'Price');
define('TABLE_HEADING_WEIGHT', 'Weight');
define('TABLE_HEADING_SORT', 'Sort Order');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_LAST_MODIFIED', 'Last Modified');
define('TABLE_HEADING_FILE', 'File');
define('TABLE_HEADING_DESC', 'Description');
define('TABLE_HEADING_ENTRIES', 'Entry');
define('TABLE_HEADING_ALT_TITLE', 'Alternative Title');
define('TABLE_HEADING_SEQUENCE_ORDER', 'Sequence');

define('TEXT_SELECT_MULTIZONES', 'Select the entries below to insert into this Collection. <br />Note: Duplicate entries are filtered.');
define('TEXT_DELETE_MULTIZONE_CONFIRM', 'The following entries will be deleted from the <b>%s</b> Collection');
define('TEXT_DELETE_MULTIZONE', 'Delete Selected Content Entries');
define('TEXT_UPDATE_MULTIZONE', 'Update Selected Content Entries');
define('TEXT_INSERT_ALL', 'Insert All');

define('TEXT_INFO_UPLOAD_IMAGES', 'Upload Images');
define('TEXT_INFO_ZONE_APPLY', 'Apply the selected content to the front-end of the site');
define('TEXT_ALL_VALUES', 'All Values');
define('TEXT_DISPLAY_NUMBER_OF_ABSTRACT_ZONES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> content groups)');

define('TEXT_SELECT_MULTIENTRIES', 'Select the content entries to insert from the following list. Entries can then be controlled from the main content page and be related only with this content group.');
define('TEXT_INFO_ASSIGN_TEXT', 'Assign Text Entries');
define('TEXT_INFO_ASSIGN_TEXT_HELP', 'Click the <b>Assign Text Entries box</b> to insert other text page entries into this collection. Once you assign text page entries to this collection you can rearrange their display order or set an alternative title to override the default title.');
define('TEXT_INFO_ASSIGN_SUPER_ZONES', 'Assign Mixed Collections');
define('TEXT_INFO_ASSIGN_SUPER_HELP', 'Click the <b>Assign Collections box</b> to insert other collections into this collection. Once you assign entries to this collection you can rearrange their display order or set an alternative title to override the default title.');
define('TEXT_INFO_ASSIGN_IMAGE_ZONES', 'Assign Image Collections');
define('TEXT_INFO_ASSIGN_IMAGE_HELP', 'Click the <b>Assign Image Collections box</b> to insert images into this collection. Once you assign images to this collection you can setup a display order or place an alternative title to override the default title.');

define('TEXT_INFO_TITLE_SEARCH', 'Search in Collections:');

define('TEXT_PAGE_SELECT', 'Page Select On/Off');
define('TEXT_INFO_ADDITIONAL_OPTIONS', 'Additional Options for this collection');

define('NOTICE_DELETE_NO_ENTRIES', 'You must select at least one entry to delete');
define('NOTICE_UPDATE_NO_ENTRIES', 'You must select at least one entry to update');

define('ERROR_DUPLICATE_NAME', 'The collection name "%s" already exists, use a different name');
define('ERROR_EMPTY_NAME', 'Collection Name cannot be empty. Enter a valid name');
define('WARNING_NOTHING_SELECTED', 'No entries selected. Use the checkboxes to select entries first');
define('SUCCESS_ZONE_UPDATED', 'Collection %s succesfully updated');
define('SUCCESS_SELECTED_ADDED', 'Selected Entries Added');
?>
