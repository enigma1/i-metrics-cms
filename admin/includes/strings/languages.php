<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Multi-Lingual Support and Configurations Script
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
define('HEADING_TITLE', 'Languages and Definitions');

define('TABLE_HEADING_LANGUAGES', 'Languages');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_LANGUAGE_NAME', 'Language Name:');
define('TEXT_INFO_LANGUAGE_CODE', 'Language Code:');
define('TEXT_INFO_LANGUAGE_PATH', 'Language Path:');
define('TEXT_INFO_SORT_ORDER', 'Sort Order:');
define('TEXT_INFO_ENABLED', 'Enabled');

define('TEXT_INFO_HEADING_NEW_LANGUAGE', 'New Language');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new language details');

define('TEXT_INFO_HEADING_EDIT_LANGUAGE', 'Edit Language');
define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_LANGUAGE', 'Delete Language');
define('TEXT_INFO_DELETE_INTRO', 'Please note deleting a language <b>removes</b> the associated database <b>language specific tables</b>.<br />Are you sure you want to delete this language?');

define('TEXT_INFO_CANNOT_DELETE_INTRO', 
  '%s seems to be the default language you have set and its code must be renamed before deletion.<br />Also there should always be at least one language present.<br />' . 
  'To remove the default language you must first setup a different language to be the default, by setting the language code to blank.<br />' . 
  'You should then rename the code of the language you want to delete to a 2-character string forming a non-existing language code and then you should be able to delete it.'
);

define('ERROR_LANGUAGE_DELETE', 'Cannot delete language. Make sure the language is valid and not the default one');
define('ERROR_LANGUAGE_INVALID', 'Invalid Language Specified');
define('ERROR_LANGUAGE_CODE', 'The language code must be a 2-character string (eg: sp fr etc) and must not collide with the trailing strings of the database tables');
define('ERROR_LANGUAGE_PARAMS', 'Invalid Language Parameters. Path, Code and Name must be unique for all languages. Path and Name cannot be empty');

define('WARNING_LANGUAGE_DEFAULT_UPDATE', 'Default Language Update - Make sure there is only one language with empty code');
define('WARNING_LANGUAGE_CODE_INVALID', 'Non-Existing language code detected - Manual updates of Database Language Tables may required');
?>
