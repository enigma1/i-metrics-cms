<?php
/*
  $Id: helpdesk_status.php,v 1.5 2005/08/16 20:56:39 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'HelpDesk Status Entries');

define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_STATUSES', 'Statuses:');

define('TEXT_INFO_HEADING_NEW_STATUS', 'New Status');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new status with its related data');

define('TEXT_INFO_HEADING_EDIT_STATUS', 'Edit Status');
define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_STATUS', 'Delete Status');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this helpdesk status?');

define('ERROR_REMOVE_DEFAULT_HELPDESK_STATUS', 'Error: The default helpdesk status can not be removed. Please set another helpdesk status as default, and try again.');
define('ERROR_STATUS_USED_IN_ENTRIES', 'Error: This helpdesk status is currently used in entries.');
?>
