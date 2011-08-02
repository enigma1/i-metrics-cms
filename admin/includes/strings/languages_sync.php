<?php
$copyright_string = '
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Automatically Generated Multi-Lingual Table Control and Synch File
// Do not Modify - Use the admin->languages settings to manage it
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
';

define('HEADING_TITLE', 'Database Language Control Tables and Assignments');
define('HEADING_VERIFY', 'Primary Keys to Verify Table');
define('HEADING_REBUILD_SELECTED', 'Make Tables Language Dependent');
define('HEADING_DELETE_SELECTED', 'Remove Language Dependency');

define('TABLE_HEADING_DB_DEFINITION', 'Definition');
define('TABLE_HEADING_DB_STRING', 'Base Table');
define('TABLE_HEADING_LANGUAGE_TABLES', 'Language Tables');
define('TABLE_HEADING_REMOVE_TABLES', 'Removing Tables');
define('TABLE_HEADING_PRI_KEY', 'Primary Table Keys');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_STATUS', 'Status');

define('TEXT_PAGE_SELECT', 'Select All');
define('TEXT_INFO_REBUILD_SELECTED', 'Rebuild Selected Tables - Make them language dependent');
define('TEXT_INFO_DROP_SELECTED', 'Drop Selected Tables - Remove language dependency');
define('TEXT_INFO_RESTORE', 'Synchronize Tables (will add missing entries from primary to all languages)');
define('TEXT_INFO_NOT_ASSIGNED', 'Not Assigned');
define('TEXT_INFO_NOT_DB', 'Needs DB Synch');
define('TEXT_INFO_TABLE_ASSIGNMENT', 'Tables Assignments:');
define('TEXT_INFO_TABLE_NONE', '%s is not a language table');
define('TEXT_INFO_TABLE_DEF', '%s although defined as a language table, does not exist in the database.<br />Use the rebuild button to generate the missing tables.');
define('TEXT_INFO_TABLE_INFO', 'Tables Information');
define('TEXT_INFO_ENTRIES', 'Entries:');
define('TEXT_INFO_AUTO', 'Auto Increment:');
define('TEXT_INFO_STRUCTURE', 'Structure:');
define('TEXT_INFO_INTEGRITY', 'Tables Integrity Check');
define('TEXT_INFO_OK', 'OK');
define('TEXT_INFO_FAILED', 'Failed');
define('TEXT_INFO_TABLE', 'TABLE:');
define('TEXT_INFO_HEADING_NEW_LANGUAGE', 'New Language');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new language details');
define('TEXT_INFO_VERIFY', 'Verify Table');
define('TEXT_INFO_FIELD', 'Table Fields');
define('TEXT_INFO_OVERRIDE_KEY', 'Override Entry with key %s');

define('TEXT_INFO_HEADING_EDIT_LANGUAGE', 'Edit Language');
define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_LANGUAGE', 'Delete Language');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this language?');

define('TEXT_INFO_REBUILD_SELECTED_MAIN', 
  'The following database tables will become language dependent. Table rows of the <b>default language table will be copied</b> into the newly created <b>secondary language tables</b>.<br />' .
  'In order for this operation to be successful ensure the selected database tables have at least <b>one primary key</b>. ' . 
  'The front file <b>includes/database_language.php</b> will be modified to include the new tables if necessary, therefore make sure I have <b>write access</b> to it.'
);

define('TEXT_INFO_DELETE_SELECTED_MAIN', 
  'Language dependency will be removed from the following database tables. Table rows of the primary table will remain while the secondary language tables will be deleted.<br />' .
  'The front-end file <b>includes/database_language.php</b> will be modified to remove the language tables if necessary, therefore make sure I have <b>write access</b> to it.'
);

define('ERROR_INVALID_KEY_SELECTED', 'Invalid key - nothing to change');
define('ERROR_LANGUAGE_SYNCH_FAILED', 'Cannot retrieve primary record from table %s. Switch language if possible and retry');
define('ERROR_INVALID_TABLE', 'Invalid Table cannot be verified');
define('ERROR_INVALID_LANGUAGE_TABLE', 'Only Tables with primary keys and multiple languages assigned can be setup or verified');
define('ERROR_INVALID_NOTHING_SELECTED', 'You must select at least one table to synchronize, use the checkboxes on the left!');
define('ERROR_LANGUAGE_INVALID', 'Invalid Language Specified');
define('ERROR_LANGUAGE_PARAMS', 'Invalid Language Parameters Path, Code and Name must be unique for all languages');

define('SUCCESS_LANGUAGE_TABLE_REMOVED', 'Secondary language tables for %s were removed');
define('SUCCESS_LANGUAGE_SYNCH', '%s processed');
?>
