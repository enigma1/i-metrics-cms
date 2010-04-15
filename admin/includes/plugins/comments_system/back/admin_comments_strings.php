<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Voting System Strings file
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

define('HEADING_TITLE', 'Comments System');
define('HEADING_COLLECTIONS', 'Collections Assignment in Comments System');
define('HEADING_TEXT_PAGES', 'Text Pages Assignment in Comments System');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_PROCESSED', 'Processed');
define('TABLE_HEADING_EMAIL', 'E-Mail');
define('TABLE_HEADING_IP', 'IP Address');
define('TABLE_HEADING_AUTHOR', 'Poster');
define('TABLE_HEADING_URL', 'URL');
define('TABLE_HEADING_RATING', 'Rating');
define('TABLE_HEADING_DATE_ADDED', 'Date Added');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_TITLE', 'Page');
define('TABLE_HEADING_TYPE', 'Type');

define('TEXT_HEADING_DELETE_COMMENT', 'Delete Comment');
define('TEXT_HEADING_EDIT_COMMENT', 'Editing Comment for <b>%s</b>');
define('TEXT_INFO_EDIT_COMMENT_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_PAGE', 'Text Page');
define('TEXT_INFO_COLLECTION', 'Collection');
define('TEXT_PAGE_SELECT', 'Select/Deselect entire page');

define('TEXT_INFO_ASSIGN_COLLECTIONS', 'Re-Assign Content Collectons');
define('TEXT_INFO_ASSIGN_TEXT', 'Re-Assign Text Pages');

define('TEXT_INFO_EMAIL', 'E-Mail:');
define('TEXT_INFO_IP_ADDRESS', 'IP Address:');
define('TEXT_INFO_AUTHOR', 'Poster:');
define('TEXT_INFO_URL', 'URL:');
define('TEXT_INFO_COMMENT', 'Comment:');
define('TEXT_INFO_APPROVED', 'Approved:');
define('TEXT_DATE_ADDED', 'Date Added:');

define('TEXT_INFO_DELETE_COMMENT_INTRO', 'Are you sure you want to delete this comment?');
define('TEXT_NO_COMMENTS', 'No Votes');
define('TEXT_INFO_NO_COMMENTS', 'There are no comments to display yet or no valid comment is selected.');
define('TEXT_INFO_NO_COMMENTS_FOUND', 'The database contains no comments at this time.');

define('TEXT_INFO_MODE_COLLECTIONS_INCLUSIVE', 'Currently, Collections operate in <b>Inclusive Mode</b>. Only Selected/Marked entries listed here <b>will accept comments</b> from visitors.');
define('TEXT_INFO_MODE_COLLECTIONS_EXCLUSIVE', 'Currently, Collections operate in <b>Exclusive Mode</b>. Selected/Marked entries listed here <b>will not accept comments</b> from visitors, while entries <b>not listed</b> here will accept comments from visitors');
define('TEXT_INFO_MODE_TEXT_INCLUSIVE', 'Text pages operate in <b>Inclusive Mode</b>. Selected/Marked entries here <b>will accept comments</b> from visitors.');
define('TEXT_INFO_MODE_TEXT_EXCLUSIVE', 'Text pages operate in <b>Exclusive Mode</b>. Selected/Marked entries here <b>will not accept comments</b> from visitors, while entries <b>not listed</b> here will accept comments from visitors');
define('TEXT_INFO_MODE_MORE', 'To reconfigure the mode of operations go to the plugin page and review the configuration setting of the comments system.');

define('TEXT_INFO_GUEST', 'Guest');
define('ERROR_COMMENT_INVALID', 'Invalid Comment to edit specified');
define('ERROR_NOTHING_SELECTED', 'No entries selected. Use the checkboxes to mark entries first');
define('ERROR_COMMENT_BODY_EMPTY', 'The comment message cannot be empty');
define('ERROR_COMMENT_EMAIL_EMPTY', 'The comment email cannot be empty');
define('ERROR_COMMENT_AUTHOR_EMPTY', 'The comment\'s poster field cannot be empty');
define('SUCCESS_ENTRY_REMOVED', 'Selected Comments Removed');
define('SUCCESS_COMMENT_UPDATED', 'Comment is updated');
define('SUCCESS_REMOVE_ASSIGNED', 'Selected content entries removed');
define('SUCCESS_INSERT_ASSIGNED', 'Selected content entries inserted');
?>