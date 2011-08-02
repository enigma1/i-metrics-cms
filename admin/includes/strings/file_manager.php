<?php
/*
  $Id: file_manager.php,v 1.13 2002/08/19 01:45:58 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'File Manager');

define('TABLE_HEADING_FILENAME', 'Name');
define('TABLE_HEADING_SIZE', 'Size');
define('TABLE_HEADING_PERMISSIONS', 'Permissions');
define('TABLE_HEADING_USER', 'User');
define('TABLE_HEADING_GROUP', 'Group');
define('TABLE_HEADING_LAST_MODIFIED', 'Last Modified');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_HEADING_UPLOAD', 'Upload');
define('TEXT_INFO_FILENAME', 'Filename:');
define('TEXT_FILE_SIZE', 'Size:');
define('TEXT_FILE_CONTENTS', 'File Contents:');
define('TEXT_LAST_MODIFIED', 'Last Modified:');
define('TEXT_NEW_FOLDER', 'New Folder');
define('TEXT_NEW_FOLDER_INTRO', 'Enter the name for the new folder:');
define('TEXT_DELETE_INTRO', 'Are you sure you want to delete this file?');
define('TEXT_UPLOAD_INTRO', 'Please select the files to upload.');

define('TEXT_INFO_NEW_FILE', 'New File');
define('TEXT_INFO_NEW_FOLDER', 'New Folder');
define('TEXT_INFO_EDIT_FILE', 'Editing File:');
define('TEXT_INFO_BROWSING', 'Browsing Folder:');
define('TEXT_INFO_IN_FOLDER', 'In Folder:');

define('ERROR_DIRECTORY_NOT_WRITEABLE', 'Error: I can not write to this directory. Please set the right user permissions on: %s');
define('ERROR_FILE_NOT_WRITEABLE', 'Error: I can not write to this file. Please set the right user permissions on: %s');
define('ERROR_DIRECTORY_NOT_REMOVEABLE', 'Error: I can not remove this directory. Please set the right user permissions on: %s');
define('ERROR_FILE_NOT_REMOVEABLE', 'Error: I can not remove this file. Please set the right user permissions on: %s');
define('ERROR_DIRECTORY_DOES_NOT_EXIST', 'Error: Directory does not exist: %s');
define('ERROR_INVALID_PATH_NAME', 'Invalid Path specified: %s');
define('ERROR_FILE_INVALID', 'Invalid File Specified');
define('ERROR_CREATE_DIR_EXISTS', 'Directory: %s already exists');
define('WARNING_FILE_LENGTH', 'Write Operation Failed and/or Empty File');
define('WARNING_FILE_REMOVED', 'File: %s deleted');
define('SUCCESS_DIR_CREATED', 'Folder: %s created');
?>
