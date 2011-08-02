<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Multiple Sites Control and Switch strings file
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

define('HEADING_TITLE', 'Mutli Sites Command and Switch Controller');
define('HEADING_MULTI_SITES_ADD', 'Add a New Website Configuration');
define('HEADING_MULTI_SITES_UPDATE', 'Update Configuration of Websites');
define('HEADING_DELETE', 'Delete Configuration File');
define('HEADING_MULTI_DELETE', 'Delete Multiple Configuration Files');
define('HEADING_RESTART', 'Application Restart');

define('TABLE_HEADING_MULTI_NAME', 'Name');
define('TABLE_HEADING_MULTI_HTTP_SERVER', 'Server');
define('TABLE_HEADING_MULTI_HTTPS_SERVER', 'Secure Server');
define('TABLE_HEADING_MULTI_SSL', 'SSL');
define('TABLE_HEADING_MULTI_WS_PATH', 'Relative Path');
define('TABLE_HEADING_MULTI_FS_PATH', 'Physical Path');
define('TABLE_HEADING_MULTI_WS_IMAGES', 'Relative Images');
define('TABLE_HEADING_MULTI_FS_IMAGES', 'Phsyical Images');
define('TABLE_HEADING_MULTI_WS_STRINGS', 'Relative Strings');
define('TABLE_HEADING_MULTI_FS_STRINGS', 'Phsyical Strings');
define('TABLE_HEADING_MULTI_WS_MODULES', 'Relative Modules');
define('TABLE_HEADING_MULTI_FS_MODULES', 'Phsyical Modules');

define('TABLE_HEADING_MULTI_DB_SERVER', 'DB Server');
define('TABLE_HEADING_MULTI_DB_USERNAME', 'DB Username');
define('TABLE_HEADING_MULTI_DB_PASSWORD', 'DB Password');
define('TABLE_HEADING_MULTI_DB_DATABASE', 'Database Name');

define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_SELECT', 'Select');

define('TEXT_PAGE_SELECT', 'Page Select On/Off');
define('TEXT_SITE', 'Website:');
define('TEXT_RESTART', 'Restart');
define('TEXT_DELETE_CONFIG', 'Delete configuration');
define('TEXT_RESTART_USING', 'Restart I-Metrics CMS using configuration');

define('TEXT_INFO_INSERT', 'Insert a new Multi-Site Configuration. The presets are taken from the current configuration. Please modify these settings then click insert to generate a configuration file.');
define('TEXT_INFO_UPDATE', 'Updates the configuration files. Use the checkboxes on the left side to mark the entries for update. Non-checked entries will not be updated. The backup configuration files of the marked entries will be changed.');
define('TEXT_INFO_DELETE', 'Warning this operation will delete the following configuration file:');
define('TEXT_INFO_MULTI_DELETE', 'Warning this operation will delete the following configuration files:');
define('TEXT_INFO_RESTART', 'Warning this operation will change the <b>Administrator Configuration</b> files and restart the I-Metrics CMS to the following configuration:');
define('TEXT_INFO_MARK', 'Tick %s then select an action to update or delete');
define('TEXT_INFO_ADD_NEW_SITE', 'Add a new site');

define('WARNING_NOTHING_SELECTED', 'No entries selected. Use the checkboxes to select entries first');
define('ERROR_EMPTY_CONFIG_NAME', 'The configuration name cannot be empty. Required for the configuration file');
define('ERROR_EMPTY_HTTP_SERVER', 'The server name cannot be empty');
define('ERROR_EMPTY_WS_PATH', 'The relative path to the website cannot be empty');
define('ERROR_EMPTY_FS_PATH', 'The physical path to the website cannot be empty');
define('ERROR_EMPTY_WS_IMAGES', 'The relative path to the website images cannot be empty');
define('ERROR_EMPTY_FS_IMAGES', 'The physical path to the website images cannot be empty');
define('ERROR_EMPTY_WS_STRINGS', 'The relative path to the website strings cannot be empty');
define('ERROR_EMPTY_FS_STRINGS', 'The physical path to the website strings cannot be empty');
define('ERROR_EMPTY_WS_MODULES', 'The relative path to the website modules cannot be empty');
define('ERROR_EMPTY_FS_MODULES', 'The physical path to the website modules cannot be empty');

define('ERROR_EMPTY_DB_SERVER', 'The database server cannot be empty');
define('ERROR_EMPTY_DB_USERNAME', 'The database username cannot be empty');
define('ERROR_EMPTY_DB_PASSWORD', 'The database password cannot be empty');
define('ERROR_EMPTY_DB_DATABASE', 'The database name cannot be empty');

define('ERROR_SITE_CONFIG_WRITE', 'I cannot write the new file to %s - I need write access.');
define('ERROR_SITE_CONFIG_INVALID', 'Invalid Configuration file specified');
define('WARNING_SITE_CONFIG_INVALID', 'File: %s does not exist or it\'s invalid');
define('WARNING_SITE_CONFIG_DELETED', 'Configuration Entries Deleted');
define('SUCCESS_ENTRY_CREATE', 'A new entry was created');
define('SUCCESS_ENTRY_REMOVED', 'Selected configuration files were removed');
define('SUCCESS_ENTRY_UPDATED', 'Selected configuration files were updated');
?>