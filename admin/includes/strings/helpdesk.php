<?php
/*
  $Id: helpdesk.php,v 1.5 2005/08/16 20:56:39 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'HelpDesk');
define('HEADING_HELP_TITLE', 'Help with Helpdesk');

define('TABLE_HEADING_TICKET', 'Ticket');
define('TABLE_HEADING_SUBJECT', 'Subject');
define('TABLE_HEADING_SENDER', 'Sender');
define('TABLE_HEADING_LAST_POST', 'Last Post');
define('TABLE_HEADING_DATE', 'Date');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_PRIORITY', 'Priority');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_SELECT', 'Select');
define('TABLE_HEADING_ATTACHMENTS', 'Attachments');
define('TABLE_HEADING_IP', 'IP');
define('TABLE_HEADING_NAME', 'Name');
define('TABLE_HEADING_EMAIL', 'E-Mail');
define('TABLE_HEADING_PHONE', 'Phone');
define('TABLE_HEADING_CELL', 'Cellphone');


define('TEXT_HELPDESK', 'HelpDesk');
define('TEXT_TICKET_NUMBER', 'Ticket #:');
define('TEXT_FROM_NAME', 'From (Name):');
define('TEXT_FROM_EMAIL_ADDRESS', 'From (E-Mail Address):');
define('TEXT_TO_NAME', 'To (Name):');
define('TEXT_TO_EMAIL_ADDRESS', 'To (E-Mail Address):');
define('TEXT_SUBJECT', 'Subject:');
define('TEXT_BODY', 'Body:');
define('TEXT_TO', 'To:');
define('TEXT_FROM', 'From:');
define('TEXT_DATE', 'Date:');
define('TEXT_IP', 'IP:');
define('TEXT_ATTACHMENTS', 'Attachments:');
define('TEXT_MESSAGE', 'Message:');
define('TEXT_INFO_ATTACH_FILE', 'Attach Files');
define('TEXT_INFO_ADD_FILES', 'Add more rows for file attachments');
define('TEXT_INFO_NEW_SUBJECT', 'Enter Subject');
define('TEXT_INFO_NEW_EMAIL', 'Enter E-Mail Address');
define('TEXT_INFO_NEW_NAME', 'Enter Name of Receipient');

define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this entry?');
define('TEXT_INFO_DELETE_WHOLE_THREAD', 'Remove whole thread');
define('TEXT_INFO_DELETE_INVALID', 'The given entry does not exist');

define('TEXT_SEND_INTRO', 'Send the E-Mail written below');
define('TEXT_MESSAGE_INTRO', 'Original Message');
define('TEXT_UPDATE_INTRO', 'Update the E-Mail written below?');

define('TEXT_ALL_STATUSES', 'All Statuses');
define('TEXT_ALL_PRIORITIES', 'All Priorities');
define('TEXT_ALL_DEPARTMENTS', 'All Departments');
define('TEXT_ALL_ENTRIES', 'All Entries');
define('TEXT_ONLY_NEW_ENTRIES', 'Unread Entries');

define('TEXT_STATUS', 'Status:');
define('TEXT_PRIORITY', 'Priority:');
define('TEXT_DEPARTMENT', 'Department:');
define('TEXT_ENTRIES', 'Entries:');

define('TEXT_REMOTE', 'remote:');

define('TEXT_PAGE_SELECT', 'Page Select On/Off');
define('TEXT_TEMPLATES', 'Templates:');
define('TEXT_PLEASE_SELECT', 'Please Select');

define('TEXT_INFO_TEMPLATES', 'Ready Templates:');
define('TEXT_INFO_INSERT_TEMPLATE', 'Insert Template');
define('TEXT_INFO_VIEW_TEMPLATE', 'View Template');

define('TEXT_INTERNAL_COMMENTS', 'Internal Comments');
define('TEXT_LAST_UPDATE', 'Last Update:');
define('TEXT_VIEW_HTML_DATE', 'View Mail in HTML Format - Entry on:');
define('TEXT_VIEW_HTML_CODE_DATE', 'View HTML Source Code - Entry on:');

define('TEXT_INFO_INSERT_IMAGES', 'Insert Images');
define('TEXT_INFO_UPLOAD_IMAGES', 'Upload Images');

define('TEXT_INFO_ENABLE_WP', 'Enable Word-Processor Interface');
define('TEXT_INFO_DISABLE_WP', 'Disable Word-Processor Interface');

define('ERROR_FILE_UPLOAD', 'Error: Cannot upload file %s');
define('ERROR_TICKET_DOES_NOT_EXIST', 'Error: Ticket does not exist or you didn\'t select anything.');
define('ERROR_EMAIL_ADDRESS', 'Error: Invalid E-Mail Address specified');
define('ERROR_INVALID_DEPARTMENT', 'Error: Invalid Department Specified');
define('ERROR_EMPTY_SUBJECT', 'Error: The subject field cannot be empty');
define('ERROR_EMPTY_BODY', 'Error: The mail content cannot be empty');

define('WARNING_WP_CHANGED', 'Word-Processor configuration changed');
define('WARNING_ATTACHMENT_REMOVED', 'Removing Attachment %s');

define('SUCCESS_FILE_ATTACH', 'Success: File %s suceessfully attached');
define('SUCCESS_ENTRY_UPDATED', 'Success: Entry successfully updated.');
define('SUCCESS_REPLY_PROCESSED', 'Success: Reply successfully processed.');
define('SUCCESS_WHOLE_THREAD_REMOVED', 'Success: Whole thread successfully removed.');
define('SUCCESS_ENTRY_REMOVED', 'Success: Entry successfully removed.');
define('SUCCESS_TICKET_UPDATED', 'Success: Ticket has been successfully updated.');
define('SUCCESS_COMMENT_UPDATED', 'Success: Comment has been successfully updated.');
?>
