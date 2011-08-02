<?php
/*
  $Id: english.php,v 1.106 2003/06/20 00:18:31 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Common Strings
// Inserts scripts to be cached or invalidated
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - Added CMS Strings, removed unrelated strings
// - Changed character set to UTF-8 
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
// look in your $PATH_LOCALE/locale directory for available locales..
// on RedHat6.0 I used 'en_US'
// on FreeBSD 4.0 I use 'en_US.ISO_8859-1'
// this may not work under win32 environments..
//setlocale(LC_TIME, 'en_US.ISO_8859-1');
@setlocale(LC_TIME, 'en_US.UTF-8');
define('DATE_FORMAT_SHORT', '%m/%d/%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%A %d %B, %Y'); // this is used for strftime()
define('DATE_FORMAT', 'm/d/Y'); // this is used for date()
define('PHP_DATE_TIME_FORMAT', 'm/d/Y H:i:s'); // this is used for date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');

// Global entries for the <html> tag
define('HTML_PARAMS','dir="ltr" lang="en"');

// charset for web pages and emails
// charset for web pages and emails
if(!defined('CHARSET') ) {
  //define('CHARSET', 'iso-8859-1');
  define('CHARSET', 'utf-8');
}

// page title
define('TITLE', 'Missing HEADING_TITLE');

define('HEADER_LANGUAGE', '<span style="color: #FFF;">Language: %s</span>');
define('HEADING_MANAGE_SITE', '<span style="color: #FFF;">Website: ' . STORE_NAME . '</span>');
define('TEXT_ERROR', 'Error');
// header text in includes/header.php
define('HEADER_TITLE_TOP', 'Administration');
define('HEADER_TITLE_SUPPORT_SITE', 'Support Site');
define('HEADER_TITLE_ONLINE_CATALOG', 'Online Website');
define('HEADER_TITLE_ADMINISTRATION', 'Administration');

// text for gender
define('MALE', 'Male');
define('FEMALE', 'Female');

// text for date of birth example
define('DOB_FORMAT_STRING', 'mm/dd/yyyy');

// configuration box text in includes/boxes/configuration.php
define('BOX_HEADING_CONFIGURATION', 'Configuration');
define('BOX_CONFIGURATION_MYSTORE', 'My Site');
define('BOX_CONFIGURATION_LOGGING', 'Logging');
define('BOX_CONFIGURATION_CACHE', 'Cache');

define('BOX_HEADING_PLUGINS', 'Plugins');

// tools text in includes/boxes/tools.php
define('BOX_HEADING_TOOLS', 'Tools');
define('BOX_TOOLS_CONNECTOR', 'Updates and News');
define('BOX_TOOLS_TEMPLATES', 'Templates Manager');
define('BOX_TOOLS_BACKUP', 'Database Backup');
define('BOX_TOOLS_MULTI_SITES', 'Sites Manager');
define('BOX_TOOLS_PLUGINS', 'Plugins Manager');
define('BOX_TOOLS_SERVER_INFO', 'Server Info');
define('BOX_TOOLS_WHOS_ONLINE', 'Who\'s Online');
define('BOX_TOOLS_FORM_FIELDS', 'Form Fields');
define('BOX_TOOLS_FILE_MANAGER', 'File Manager');
define('BOX_TOOLS_EXPLAIN_QUERIES', 'Database Queries');

define('CATEGORY_PERSONAL', 'Personal');
define('CATEGORY_ADDRESS', 'Address');
define('CATEGORY_CONTACT', 'Contact');
define('CATEGORY_COMPANY', 'Company');
define('CATEGORY_OPTIONS', 'Options');

// images
define('IMAGE_ADD', 'Add');
define('IMAGE_BACK', 'Back');
define('IMAGE_BACKUP', 'Backup');
define('IMAGE_CANCEL', 'Cancel');
define('IMAGE_CONFIRM', 'Confirm');
define('IMAGE_COPY', 'Copy');
define('IMAGE_COPY_TO', 'Copy To');
define('IMAGE_DETAILS', 'Details');
define('IMAGE_DELETE', 'Delete');
define('IMAGE_EDIT', 'Edit');
define('IMAGE_EMAIL', 'Email');
define('IMAGE_FILE_MANAGER', 'File Manager');
define('IMAGE_ICON_STATUS_GREEN', 'Active');
define('IMAGE_ICON_STATUS_GREEN_LIGHT', 'Set Active');
define('IMAGE_ICON_STATUS_RED', 'Inactive');
define('IMAGE_ICON_STATUS_RED_LIGHT', 'Set Inactive');
define('IMAGE_ICON_INFO', 'Info');
define('IMAGE_INSERT', 'Insert');
define('IMAGE_IMPORT', 'Import');
define('IMAGE_LOCK', 'Lock');
define('IMAGE_MODULE_INSTALL', 'Install Module');
define('IMAGE_MODULE_REMOVE', 'Remove Module');
define('IMAGE_MOVE', 'Move');
define('IMAGE_NEW', 'New');
define('IMAGE_PREVIEW', 'Preview');
define('IMAGE_RESTORE', 'Restore');
define('IMAGE_RESIZE', 'Resize');
define('IMAGE_RESET', 'Reset');
define('IMAGE_SAVE', 'Save');
define('IMAGE_SEARCH', 'Search');
define('IMAGE_SELECT', 'Select');
define('IMAGE_SEND', 'Send');
define('IMAGE_SEND_EMAIL', 'Send Email');
define('IMAGE_SUB_LEVEL', 'Sub-Level');
define('IMAGE_UNLOCK', 'Unlock');
define('IMAGE_UPDATE', 'Update');
define('IMAGE_UPDATE_CURRENCIES', 'Update Exchange Rate');
define('IMAGE_UPLOAD', 'Upload');

define('ICON_CROSS', 'False');
define('ICON_CURRENT_FOLDER', 'Current Folder');
define('ICON_DELETE', 'Delete');
define('ICON_ERROR', 'Error');
define('ICON_FILE', 'File');
define('ICON_FILE_DOWNLOAD', 'Download');
define('ICON_FOLDER', 'Folder');
define('ICON_LOCKED', 'Locked');
define('ICON_PREVIOUS_LEVEL', 'Previous Level');
define('ICON_PREVIEW', 'Preview');
define('ICON_STATISTICS', 'Statistics');
define('ICON_SUCCESS', 'Success');
define('ICON_TICK', 'True');
define('ICON_UNLOCKED', 'Unlocked');
define('ICON_WARNING', 'Warning');

// constants for use in tep_prev_next_display function
define('TEXT_EDIT', 'Edit');
define('TEXT_DELETE', 'Delete');
define('TEXT_SELECTED', 'Selected');
define('TEXT_ENABLED', 'Enabled');
define('TEXT_DISABLED', 'Disabled');
define('TEXT_RESULT_PAGE', 'Page %s of %d');

define('PREVNEXT_TITLE_FIRST_PAGE', 'First Page');
define('PREVNEXT_TITLE_PREVIOUS_PAGE', 'Previous Page');
define('PREVNEXT_TITLE_NEXT_PAGE', 'Next Page');
define('PREVNEXT_TITLE_LAST_PAGE', 'Last Page');
define('PREVNEXT_TITLE_PAGE_NO', 'Page %d');
define('PREVNEXT_BUTTON_PREV', '&laquo;');
define('PREVNEXT_BUTTON_NEXT', '&raquo;');

define('TEXT_DEFAULT', 'default');
define('TEXT_FRONT', 'front');
define('TEXT_SET_DEFAULT', 'Set as default');
define('TEXT_FIELD_REQUIRED', '&nbsp;<span class="fieldRequired">* Required</span>');
define('TEXT_DISPLAY_NUMBER_OF_ENTRIES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> entries)');

define('TEXT_NONE', '--none--');
define('TEXT_TOP', 'Top');

define('ERROR_DESTINATION_DOES_NOT_EXIST', 'Error: Destination does not exist.');
define('ERROR_DESTINATION_NOT_WRITEABLE', 'Error: Destination not writeable.');
define('ERROR_FILE_NOT_SAVED', 'Error: File upload not saved.');
define('ERROR_FILETYPE_NOT_ALLOWED', 'Error: File upload type not allowed.');
define('ERROR_HEADERS_SENT', 'Critical: Headers already sent, cannot continue. Fix the errors and reload the page.');
define('SUCCESS_FILE_SAVED_SUCCESSFULLY', 'Success: File upload saved successfully.');
define('WARNING_NO_FILE_UPLOADED', 'Warning: No file uploaded.');

//-MS- Abstract Zones added
define('BOX_HEADING_ABSTRACT_ZONES', 'Site Content');
define('BOX_ABSTRACT_ZONES', 'Content Management');
define('BOX_ABSTRACT_TYPES', 'Content Types');
define('BOX_ABSTRACT_GENERIC_TEXT', 'Text Pages');
define('BOX_ABSTRACT_GENERIC_TEXT_NEW', 'New Page');
define('BOX_ABSTRACT_CONFIG', 'Configuration');
//-MS- Abstract Zones added EOM

define('BOX_DIRECT_MANAGEMENT', 'Direct Management');

//-MS- Total Configuration added
define('BOX_TOOLS_TOTAL_CONFIGURATION', 'Total Configuration');
//-MS- Total Configuration added

//-MS- SEO-G added
define('BOX_HEADING_MARKETING', 'Marketing');
define('BOX_HEADING_SEO_ZONES', 'SEO-G');
define('BOX_SEO_ZONES', 'G-Controller');
define('BOX_SEO_TYPES', 'G-Types');
define('BOX_SEO_CONFIG', 'Configuration');
define('BOX_SEO_EXCLUDE', 'G-Exclude');
define('BOX_SEO_REPORTS', 'G-Reports');
define('BOX_SEO_REDIRECTS', 'G-Redirects');
//-MS- SEO-G added EOM

//-MS- META-G added
define('BOX_HEADING_META_ZONES', 'META-G');
define('BOX_META_ZONES', 'Meta-Controller');
define('BOX_META_TYPES', 'Meta-Types');
define('BOX_META_CONFIG', 'Configuration');
define('BOX_META_LEXICO', 'Meta-Lexico');
define('BOX_META_REPORTS', 'Meta-Reports');
define('BOX_META_SCRIPTS', 'Meta-Scripts');
define('BOX_META_EXCLUDE', 'Meta-Exclude');
define('BOX_META_FEEDS', 'Meta-Feeds');
//-MS- META-G added EOM

//-MS- Cache Manager
define('BOX_HEADING_CACHE', 'Cache Manager');
define('BOX_CACHE_CONFIG', 'Configuration');
define('BOX_CACHE_HTML', 'HTML Cache');
define('BOX_CACHE_REPORTS', 'Cache Reports');
//-MS- Cache Manager EOM

//-MS- Help Desk Added
define('BOX_HEADING_HELPDESK', 'HelpDesk');
define('BOX_HELPDESK_CONFIG', 'Configuration');
define('BOX_HELPDESK_ENTRIES', 'HelpDesk');
define('BOX_HELPDESK_DEPARTMENTS', 'Departments');
define('BOX_HELPDESK_BOOK', 'Address Book');
define('BOX_HELPDESK_STATUSES', 'Statuses');
define('BOX_HELPDESK_PRIORITIES', 'Priorities');
define('BOX_HELPDESK_POP3', 'Retrieve Mail');
define('IMAGE_REPLY', 'Reply');
define('ICON_INCOMING', 'Incoming');
define('ICON_OUTGOING', 'Outgoing');
define('ICON_UNREAD', 'Unread');
//-MS- Help Desk Added EOM

define('BOX_HEADING_LANGUAGES', 'Languages');
define('BOX_LANGUAGES_EDITOR', 'Create/Edit Languages');
define('BOX_LANGUAGES_SYNC', 'Assign/Synchronize');

define('TEXT_INFO_NA', 'N/A');
define('TEXT_INCLUDED', 'Included');
define('TEXT_NEXT', '&raquo;');
define('TEXT_PREVIOUS', '&laquo;');

define('EMPTY_GENERIC', 'No Entries - Nothing Selected');
define('TEXT_NO_GENERIC', 'Please insert a new entry or select an exisiting one.');
define('TEXT_IMAGE_UPLOAD', 'Upload Image');
define('TEXT_TITLE_FILTER', 'Filter:');
define('TEXT_VIEW_ALL', 'See All');

define('BOX_OTHER_DOCUMENTATION', 'Online Documention');
define('BOX_OTHER_ROOT', 'Administration Root');
define('BOX_OTHER_QUICK_HELP', 'Screenshot/Help');

// Stack Messages
define('WARNING_NEW_SESSION_STARTED', 'New session started. There are %s active sessions who can administer your website');
define('WARNING_FILE_UPLOADS_DISABLED', 'File uploads are disabled in the php.ini configuration file.');
define('WARNING_PASSWORD_PROTECT_REMIND', 'Password Protect your Administration Folder from the host cPanel - If you did, click here to disable this message');

define('WARNING_INSTALL_DIRECTORY_EXISTS', 'Installation directory exists at: %s Please remove this directory for security reasons.');
define('WARNING_IMAGE_UPLOADS_DISABLED', 'Images folder is not writable. You will not be able to upload images');
define('WARNING_IMAGE_THUMBS_DISABLED', 'Image Thumbs folder is not writable. Image thumbnails will not be generated');
define('WARNING_LANGUAGE_SWITCH', 'Admin Language Control for Web-Front - Switched to: %s');
define('WARNING_NOT_SENDING_COOKIES', 'Not sending Cookies - Headers Already Sent.');

define('ERROR_WRITING_FILE', 'Cannot Write to File: %s');
define('ERROR_INVALID_FILE', 'Invalid File: %s');
define('ERROR_INVALID_FILE_NAME', 'File Name: %s has invalid characters');
define('ERROR_CREATE_DIR', 'Error Creating Directory: %s');
define('ERROR_PLUGIN_NOT_FOUND', 'Could not find plugin');
define('ERROR_EMPTY_SEARCH', 'Nothing found for: %s');
?>