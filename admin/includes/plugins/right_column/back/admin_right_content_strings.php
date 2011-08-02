<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Right Column Strings file
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
define('HEADING_TITLE', 'Right Column Assignments');
define('HEADING_COLLECTIONS', 'Collections Assignment in Right Column System');
define('HEADING_TEXT_PAGES', 'Text Pages Assignment in Right Column System');
define('HEADING_DELETE_ENTRIES', 'Delete Right Column Content Entries');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_NAME', 'Content Name');
define('TABLE_HEADING_BOX_TITLE', 'Box Title');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_TITLE', 'Page');
define('TABLE_HEADING_TYPE', 'Type');
define('TABLE_HEADING_INSTANCES', 'Instances');

define('TEXT_HEADING_DELETE_CONTENT', 'Delete Entry');
define('TEXT_HEADING_EDIT_CONTENT', 'Editing Box Content for <b>%s</b>');
define('TEXT_INFO_EDIT_CONTENT_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_PAGE', 'Text Page');
define('TEXT_INFO_COLLECTION', 'Collection');
define('TEXT_PAGE_SELECT', 'Select/Deselect entire page');

define('TEXT_INFO_ASSIGN_COLLECTIONS', 'Re-Assign Content Collectons');
define('TEXT_INFO_ASSIGN_TEXT', 'Re-Assign Text Pages');
define('TEXT_INFO_NAME', 'Box Name:');
define('TEXT_INFO_TEXT', 'Box Content:');
define('TEXT_INFO_SORT', 'Display Order:');

define('TEXT_INFO_DELETE_CONTENT_INTRO', 'Are you sure you want to delete this entry?');
define('TEXT_INFO_NO_ENTRIES', 'There are no entries to display yet or no valid entry is selected.');
define('TEXT_INFO_NO_ENTRIES_FOUND', 'The database contains no entries at this time.');

define('TEXT_INFO_ENABLED', 'Tick to Enable');
define('TEXT_INFO_NOT_ASSIGNED', 'Needs title/content');
define('TEXT_INFO_EMPTY', 'Empty');

define('TEXT_INFO_MODE_COLLECTIONS_INCLUSIVE', 'Currently, Collections operate in <b>Inclusive Mode</b>. Only Selected/Marked entries listed here <b>will accept comments</b> from visitors.');
define('TEXT_INFO_MODE_COLLECTIONS_EXCLUSIVE', 'Currently, Collections operate in <b>Exclusive Mode</b>. Selected/Marked entries listed here <b>will not accept comments</b> from visitors, while entries <b>not listed</b> here will accept comments from visitors');
define('TEXT_INFO_MODE_TEXT_INCLUSIVE', 'Text pages operate in <b>Inclusive Mode</b>. Selected/Marked entries here <b>will accept comments</b> from visitors.');
define('TEXT_INFO_MODE_TEXT_EXCLUSIVE', 'Text pages operate in <b>Exclusive Mode</b>. Selected/Marked entries here <b>will not accept comments</b> from visitors, while entries <b>not listed</b> here will accept comments from visitors');
define('TEXT_INFO_MODE_MORE', 'To reconfigure the mode of operations go to the plugin page and review the configuration setting of the comments system.');

define('TEXT_INFO_TEXT_DETAILS', 'Select the text page entries to be associated with the right column system');
define('TEXT_INFO_ZONE_DETAILS', 'Select the collections to be associated with the right column system');
define('TEXT_INFO_DELETE_ENTRIES', 'Confirm deletion of the following entries. Please note this operation is irreversible, box content entries will be permanently removed!');
define('ERROR_CONTENT_INVALID', 'Invalid Content to edit specified');
define('ERROR_NOTHING_SELECTED', 'No entries selected. Use the checkboxes to mark entries first');
define('ERROR_COMMENT_BODY_EMPTY', 'The comment message cannot be empty');
define('ERROR_COMMENT_EMAIL_EMPTY', 'The comment email cannot be empty');
define('ERROR_COMMENT_AUTHOR_EMPTY', 'The comment\'s poster field cannot be empty');
define('SUCCESS_ENTRY_REMOVED', 'Selected Entry Removed');
define('SUCCESS_COMMENT_UPDATED', 'Comment is updated');
define('SUCCESS_REMOVE_ASSIGNED', 'Selected content entries removed');
define('SUCCESS_INSERT_ASSIGNED', 'Selected content entries inserted');
?>