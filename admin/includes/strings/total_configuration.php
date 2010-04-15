<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Total Configuration module for osCommerce Admin
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
define('HEADING_TITLE', 'Total Configuration');
define('HEADING_ALL', 'Total Configuration');
define('HEADING_ACTION', 'Action');
define('HEADING_INSERT', 'Insert into Configuration');

define('TABLE_HEADING_CONFIGURATION_ID', 'ID');
define('TABLE_HEADING_CONFIGURATION_KEY', 'Key');
define('TABLE_HEADING_CONFIGURATION_TITLE', 'Title');
define('TABLE_HEADING_CONFIGURATION_VALUE', 'Value');
define('TABLE_HEADING_EDIT', 'Edit');
define('TABLE_HEADING_ACTION', 'Action');

define('HEADING_CONFIRM', 'Confirmation - Backup your Database before proceeding');

define('TABLE_HEADING_OPTIMIZE', 'Configuration Table Options');

define('TEXT_EDIT_GROUP', 'Editing');

define('TEXT_INFO_SELECT_GROUP', 'Select Group:');
define('TEXT_INFO_EDIT_INTRO', '<b>Switch limits:</b>' . 
       '<ol><li>Title 64 Chars</li><li>Key 64 Chars</li><li>Value 255 Chars</li><li>Description 255 Chars</li><li>Functions 255 Chars</li></ol>'
       );
define('TEXT_INFO_EDIT_GROUP_INTRO', 'Tick the visible option for groups which you want to appear with the main configuration.<br /><b>Group limits:</b>' . 
       '<ol><li>Title 64 Chars</li><li>Description 255 Chars</li></ol>'
       );

define('TEXT_INFO_DELETE_GROUP_INTRO', '<b>Final Notice</b><br />This operation will remove group %s.');
define('TEXT_INFO_DELETE_GROUP_FINAL', '<b style="color: #FF0000">Deleting configuration switches or groups may render the site inoperable!</b>');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete %s');
define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');

define('TEXT_INFO_INSERT_ENTRY', 'Insert Switch or Group:');
define('TEXT_INFO_OPTIMIZE_SORT', 'Sort by ID');
define('TEXT_INFO_OPTIMIZE_DUPLICATES', 'Remove Duplicates');

define('TEXT_INFO_OPERATION', '<b>Sort by ID:</b> Restructures the configuration table to use the IDs sequentially. <br /><b>Remove Duplicates:</b> Removes duplicated keys from the configuration table.');

define('TEXT_INFO_CONFIRM_DUPLICATES', 'The following duplicates will be removed from the configuration table in the database');
define('TEXT_INFO_CONFIRM_CONFIG', 'The Configuration table will be sorted by configuration_id');
define('TEXT_INFO_CONFIRM_MYSQL', 'Confirm MySql Configuration Table changes');
define('TEXT_INFO_CONFIRM_CONFIG_INSERT', 'Confirm insertion of the configuration switch');
define('TEXT_INFO_CONFIRM_GROUP_INSERT', 'Confirm insertion of the new group');

define('TEXT_INFO_CFG_TITLE', 'Configuration Title:');
define('TEXT_INFO_CFG_KEY', 'Configuration Key:');
define('TEXT_INFO_CFG_VALUE', 'Configuration Value:');
define('TEXT_INFO_CFG_DESCRIPTION', 'Configuration Description:');
define('TEXT_INFO_CFG_USE', 'Use Function:');
define('TEXT_INFO_CFG_SET', 'Set Function:');
define('TEXT_INFO_CFG_SORT', 'Sort Order:');
define('TEXT_INFO_CFG_CUSTOM', 'Custom Group ID:');

define('TEXT_INFO_GROUP_INCLUDE_SWITCHES', 'Include All Switches');
define('TEXT_INFO_GROUP_TITLE', 'Group Title:');
define('TEXT_INFO_GROUP_DESCRIPTION', 'Group Description:');
define('TEXT_INFO_GROUP_SORT', 'Sort Order:');
define('TEXT_INFO_GROUP_VISIBLE', 'Make it Visible');
define('TEXT_INFO_GROUP_ID', 'Group ID:');

define('TEXT_INFO_INSERT_SWITCH', 'Complete the form below to insert a new configuration switch into the selected configuration group');
define('TEXT_INFO_INSERT_GROUP', 'Complete the form below to create a new configuration group');

define('ERROR_CFG_KEY_INVALID', 'Invalid Configuration Key. Use numeric/upper case characters and underscores only, no spaces');
define('ERROR_CFG_KEY_EMPTY', 'The configuration key cannot be empty');
define('ERROR_CFG_KEY_EXISTS', 'The configuration key entered already exists');
define('ERROR_CFG_TITLE_EMPTY', 'The configuration title cannot be empty');
define('ERROR_CFG_DESCRIPTION_EMPTY', 'The configuration description cannot be empty');
define('ERROR_CFG_USE_FUNCTION_INVALID', 'The specified USE function does not exist');
define('ERROR_CFG_SET_FUNCTION_INVALID', 'The specified SET function does not exist');
define('ERROR_CFG_FUNCTION_LENGTH', 'Function Length cannot exceed 255 characters');
define('ERROR_CFG_GROUP_INVALID', 'Invalid Configuration Group specified');
define('ERROR_CFG_ID_INVALID', 'Invalid Configuration Switch selected');
define('SUCCESS_CFG_SWITCH_CREATED', 'Configuration Switch Created');
define('SUCCESS_CFG_SWITCH_UPDATED', 'Configuration Switch Updated');


define('WARNING_GROUP_DELETED', 'Configuration Group Deleted');
define('ERROR_GROUP_EXISTS', 'The configuration group already exists');
define('ERROR_GROUP_TITLE_EMPTY', 'The configuration group title cannot be empty');
define('ERROR_GROUP_DESCRIPTION_EMPTY', 'The configuration group description cannot be empty');
define('SUCCESS_GROUP_CREATED', 'Configuration Group Created');
define('SUCCESS_GROUP_UPDATED', 'Configuration Group Updated');
?>