<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Banners Strings file
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

define('HEADING_TITLE', 'Banners Management');
define('HEADING_COLLECTIONS', 'Collections Assignment in Banner System');
define('HEADING_TEXT_PAGES', 'Text Pages Assignment in Banner System');
define('HEADING_DELETE_ENTRIES', 'Delete Banners');
define('HEADING_GROUPS', 'Banner Groups');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_NAME', 'Content Name');
define('TABLE_HEADING_LINK_TITLE', 'Banner Title');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_TITLE', 'Page');
define('TABLE_HEADING_GROUP', 'Group');
define('TABLE_HEADING_IMPRESSIONS', 'Impressions');
define('TABLE_HEADING_CLICKS', 'Clicks');
define('TABLE_HEADING_TYPE', 'Type');
define('TABLE_HEADING_INSTANCES', 'Instances');

define('TABLE_HEADING_GROUP_NAME', 'Group Name');
define('TABLE_HEADING_GROUP_WIDTH', 'Group Width');
define('TABLE_HEADING_GROUP_HEIGHT', 'Group Height');

define('TEXT_INFO_BANNER_GROUPS', 'Banner Groups');
define('TEXT_INFO_GROUP_INSERT', 'Create Banner Group');
define('TEXT_INFO_BANNER_NEW', 'Create New Banner');
define('TEXT_INFO_GROUP_INSERT_HELP', 
'Click the <b>Create Banner Group</b> box to create a new banners group ' . 
'A banners group includes a group name, a height and a width in pixels. The dimensions define how the banner resources will be fitted in the template on the front end ' . 
'It is recommended the banner images used for each group to have dimensions be as close as possible to the group dimensions defined. Specify the banner dimensions in pixels.'
);
define('TEXT_INFO_ASSIGN_COLLECTIONS', 'Re-Assign Content Collectons');
define('TEXT_INFO_ASSIGN_TEXT', 'Re-Assign Text Pages');
define('TEXT_INFO_NAME', 'Banner Title:');
define('TEXT_INFO_LINK', 'Direct Link or HTML for media:');
define('TEXT_INFO_SORT', 'Display Order:');
define('TEXT_INFO_FILENAME', 'Or set front Path and File:');
define('TEXT_INFO_CONTENT_ID', 'Content ID:');
define('TEXT_INFO_CONTENT_TYPE', 'Content Type:');
define('TEXT_INFO_ADVANCED_SECTION', 'Advanced Configuration');

define('TEXT_INFO_GROUP', 'Group:');
define('TEXT_INFO_GROUP_NAME', 'Group Name:');
define('TEXT_INFO_GROUP_WIDTH', 'Group Width:');
define('TEXT_INFO_GROUP_HEIGHT', 'Group Height:');
define('TEXT_INFO_GROUP_EDIT', 'Modify this banner\'s group properties');
define('TEXT_INFO_GROUP_DIMENSIONS', 'Group Dimensions:');
define('TEXT_INFO_GROUP_DELETE', 'Delete Selected Banners Group');
define('TEXT_INFO_GROUP_DELETE_WARN', 'Banners associated with this group will be removed. Are you sure you want to that?');
define('TEXT_INFO_GROUP_DELETE_FILES', 'Remove Images');
define('TEXT_INFO_GROUP_STRING', 'Group');
define('TEXT_INFO_GROUP_POSITION', 'Group Position:');
define('TEXT_INFO_GROUP_TYPE', 'Group Type:');
define('TEXT_INFO_GROUP_TYPE_RANDOM', 'Single/Random');
define('TEXT_INFO_GROUP_TYPE_MULTIPLE', 'Multiple/Cascade');
define('TEXT_INFO_POS_TOP', 'Top');
define('TEXT_INFO_POS_RIGHT', 'Right');
define('TEXT_INFO_POS_BOTTOM', 'Bottom');
define('TEXT_INFO_POS_LEFT', 'Left');

define('TEXT_INFO_NOT_ASSIGNED', 'Not Assigned');
define('TEXT_INFO_GLOBAL_COLLECTION', 'All Collections');
define('TEXT_INFO_GLOBAL_TEXT', 'All Text Pages');
define('TEXT_INFO_GLOBAL_ALL', 'Global Banner');
define('TEXT_INFO_PAGE', 'Text Page');
define('TEXT_INFO_COLLECTION', 'Collection');
define('TEXT_INFO_EDIT_CONTENT_INTRO', 'Please enter the banner details below');
define('TEXT_HEADING_EDIT_CONTENT', 'Editing Banner');
define('TEXT_HEADING_NEW_CONTENT', 'New Global Banner');
define('TEXT_INFO_ATTACH_FILE', 'Upload Banner file');
define('TEXT_INFO_ENABLED', 'Enable Banner');

define('TEXT_INFO_DELETE_BANNER', 'Delete %s');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this banner?');

define('TEXT_INFO_TEXT_DETAILS', 'Select the text page entries to be associated with the banners system');
define('TEXT_INFO_ZONE_DETAILS', 'Select the collections to be associated with the banners system');
define('TEXT_INFO_DELETE_ENTRIES', 'Confirm deletion of the following entries. Please note this operation is irreversible, box content entries will be permanently removed!');

define('TEXT_INFO_INSERT_IMAGES', 'Insert Image');
define('TEXT_INFO_UPLOAD_IMAGES', 'Upload Image');
define('TEXT_PAGE_SELECT', 'Select/Deselect entire page');

define('TEXT_INFO_ERROR', 'Error - Delete');
define('TEXT_INFO_NO_ENTRIES', 'There are no entries to display yet or no valid entry is selected.');
define('TEXT_INFO_NO_ENTRIES_FOUND', 'The database contains no entries at this time.');
define('TEXT_INFO_EMPTY', 'No Name');

define('ERROR_BANNERS_REINSTALL', 'The banner system was not properly installed. Uninstall and Reinstall!');
define('ERROR_BANNERS_DIMENSIONS_INVALID', 'Group Banners Dimensions are invalid');
define('ERROR_BANNERS_GROUP_INVALID', 'Invalid Banners Group specified');

define('WARNING_BANNERS_GROUP_REMOVED', 'Banners Group was removed. Associated banner entries were also removed');
define('SUCCESS_BANNERS_GROUP_CREATED', 'A new Banners Group is now available');
define('SUCCESS_BANNERS_GROUP_UPDATED', 'Selected Banners Group was updated');

define('ERROR_BANNERS_INVALID', 'Invalid Banner specified');
define('ERROR_BANNERS_FILE_INVALID', 'Banner File or Path is invalid');
define('ERROR_BANNERS_NEW_GROUP_REQUIRED', 'A valid group must be assigned to each banner. Create a valid group first.');
define('ERROR_NOTHING_SELECTED', 'No entries selected. Use the checkboxes to mark entries first');
define('ERROR_MAXIMUM_STORAGE', 'Maximum number of customers in temporary storage reached, cannot add more');
define('WARNING_BANNERS_CUSTOMERS_REMOVED', 'The selected Customers were removed from the newsletter list');
define('SUCCESS_BANNERS_RESET', 'Newsletter tracking fields cleared');
define('SUCCESS_BANNERS_REMOVED', 'Selected Entry Removed');
define('SUCCESS_BANNERS_DELETED', 'Banners Removed');
define('SUCCESS_BANNERS_CREATED', 'Banner %s was created');
define('SUCCESS_BANNERS_UPDATED', 'Properties of %s updated');
define('SUCCESS_BANNERS_CUSTOMERS_CLEARED', 'Temporary customers storage cleared');
define('SUCCESS_BANNERS_SENT', 'Selected Newsletter was mailed to all chosen customers');
define('SUCCESS_INSERT_ASSIGNED', 'Selected content entries inserted');
?>