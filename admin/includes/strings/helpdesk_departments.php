<?php
/*
  $Id: helpdesk_departments.php,v 1.5 2005/08/16 20:56:39 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'HelpDesk Departments');

define('TABLE_HEADING_DEPARTMENTS', 'Departments');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_DEPARTMENT', 'Department:');
define('TEXT_INFO_EMAIL_ADDRESS', 'E-Mail Address:');
define('TEXT_INFO_NAME', 'Name:');
define('TEXT_INFO_PASSWORD', 'Password:');
define('TEXT_INFO_CATALOG', 'Show On Front:');
define('TEXT_INFO_RECEIVE', 'Mail-Box Receives:');

define('TEXT_INFO_HEADING_NEW_DEPARTMENT', 'New Department');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new department with its related data');

define('TEXT_INFO_HEADING_EDIT_DEPARTMENT', 'Edit Department');
define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_DEPARTMENT', 'Delete Department');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this helpdesk department?');

define('TEXT_RECEIVE_ENABLED', 'Include this email when synchronizing. Click to synchronize now');
define('TEXT_RECEIVE_DISABLED', 'Do not include this email when synchronizing. Click to synchronize now');

define('TEXT_RECEIVES_EMAILS', 'Active Mail-Box');
define('TEXT_DISPLAY_FRONT', 'Displays on Front');

define('ERROR_REMOVE_DEFAULT_HELPDESK_DEPARTMENT', 'Error: The default helpdesk department can not be removed. Please set another helpdesk department as default, and try again.');
define('ERROR_DEPARTMENT_USED_IN_ENTRIES', 'Error: This helpdesk department is currently used in entries.');
?>
