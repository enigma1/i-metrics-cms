<?php
/*
  $Id: helpdesk_priorities.php,v 1.5 2005/08/16 20:56:39 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'HelpDesk Priorities');

define('TABLE_HEADING_PRIORITIES', 'Priorities');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_PRIORITIES', 'Priorities:');

define('TEXT_INFO_HEADING_NEW_PRIORITY', 'New Priority');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new priority with its related data');

define('TEXT_INFO_HEADING_EDIT_PRIORITY', 'Edit Priority');
define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_PRIORITY', 'Delete Priority');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this helpdesk priority?');

define('ERROR_REMOVE_DEFAULT_HELPDESK_PRIORITY', 'Error: The default helpdesk priority can not be removed. Please set another helpdesk priority as default, and try again.');
define('ERROR_PRIORITY_USED_IN_ENTRIES', 'Error: This helpdesk priority is currently used in entries.');
?>
