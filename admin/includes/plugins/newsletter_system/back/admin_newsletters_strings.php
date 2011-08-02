<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Newsletter Strings file
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

define('HEADING_TITLE', 'Newsletters Management');
define('HEADING_NEWSLETTER_CREATE', 'Create Newsletter');
define('HEADING_NEWSLETTER_EDIT', 'Edit Newsletter');
define('HEADING_NEWSLETTER_CUSTOMERS', 'Sent newsletter to selected customers');
define('HEADING_NEWSLETTER_SENDING', 'Now sending %s to %s customers');
define('HEADING_NEWSLETTER_SELECTED', 'Selected');
define('HEADING_NEWSLETTER_ALL', 'All');
define('HEADING_TITLE_UPLOAD', 'Upload Template');
define('HEADING_DELETE_ENTRIES', 'Delete Download Entries');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_HITS', 'Hits');
define('TABLE_HEADING_TEMPLATE_NAME', 'Newsletter Name');
define('TABLE_HEADING_LAST_SENT', 'Last Sent');
define('TABLE_HEADING_TIMES_SENT', 'Times Sent');
define('TABLE_HEADING_CUSTOMERS_SENT', 'Customers Sent');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_CUSTOMERS_NAME', 'Customer Name');
define('TABLE_HEADING_CUSTOMERS_EMAIL', 'Customer E-Mail');

define('TEXT_NEWSLETTER_SUBJECT', 'Newsletter Title');
define('TEXT_NEWSLETTER_CONTENT', 'Newsletter Content');
define('TEXT_HEADING_DELETE_ENTRY', 'Delete Entry');
define('TEXT_INFO_ADD_SELECTED', 'Add Selected');
define('TEXT_INFO_REMOVE_SELECTED', 'Remove selected customers from the subscription list');
define('TEXT_INFO_HEADING_COPY', 'Copy %s');
define('TEXT_INFO_HEADING_SEND', 'Send %s');
define('TEXT_INFO_COPY_INTRO', 'Enter a different title to duplicate this newsletter');
define('TEXT_INFO_SEND_INTRO', 'Are you sure you want to send this email?');
define('TEXT_INFO_SEND_SELECTED', 'This e-mail will be send to selected customers only');
define('TEXT_INFO_SEND_ALL', 'This e-mail will be send to all customers subscribed to newsletters');
define('TEXT_INFO_SEND_CONTINUE', 'This newsletter has an incomplete sent process. The newsletter process will continue from the last stored customer');
define('TEXT_INFO_NEWSLETTER_SENDING', 'Sending newsletter. Do not refresh the browser and do not hit the back. This page will refresh automatically. If you have to cancel this operation hit the cancel button at the end of the page');

define('TEXT_INFO_ENABLE_WP', 'Enable Word-Processor Interface');
define('TEXT_INFO_DISABLE_WP', 'Disable Word-Processor Interface');
define('TEXT_INFO_NOT_SENT', 'Not Sent');
define('TEXT_INFO_INSERT_IMAGES', 'Insert Image');
define('TEXT_INFO_UPLOAD_IMAGES', 'Upload Image');
define('TEXT_PAGE_SELECT', 'Select/Deselect entire page');

define('TEXT_INFO_NEWSLETTER_TITLE', 'newsletter-');
define('TEXT_INFO_NEWSLETTER_FILE', 'Use a Ready Template as Newsletter');
define('TEXT_INFO_SEND_NEWSLETTER', 'Send a newsletter');
define('TEXT_INFO_CREATE_NEWSLETTER', 'Create Newsletter');
define('TEXT_INFO_SELECT_CUSTOMERS', 'Select Customers');
define('TEXT_INFO_SELECT_CUSTOMERS_HELP', 'Select specific customers to send the newsletter to');
define('TEXT_INFO_CLEAR_CUSTOMERS', 'Clear Customers');
define('TEXT_INFO_CLEAR_CUSTOMERS_HELP', 'Clear the temporary customer assignments');
define('TEXT_INFO_CLEAR_SELECTION', 'Clear Selection');
define('TEXT_INFO_CLEAR_SELECTION_HELP', 'Customer listing depends on the newsletter selection');
define('TEXT_INFO_NAME', 'Newsletter Name:');
define('TEXT_INFO_HITS', 'Times people accessed it:');
define('TEXT_INFO_SENT', 'Number of people send to:');
define('TEXT_INFO_DATE', 'Last sent:');
define('TEXT_INFO_ERROR', 'Error - Delete');

define('TEXT_INFO_NEWSLETTER_REMOVE', 'Click here to remove this email from our newsletter list');
define('TEXT_INFO_DELETE_CONTENT_INTRO', 'Are you sure you want to delete this entry?');
define('TEXT_INFO_NO_ENTRIES', 'There are no entries to display yet or no valid entry is selected.');
define('TEXT_INFO_NO_ENTRIES_FOUND', 'There are no newsletters to display. First create one.');
define('TEXT_INFO_NO_CUSTOMERS_FOUND', 'There are no customers subscribed to your newsletters. There is nothing to select from.');
define('TEXT_INFO_CUSTOMERS_ASSIGNED', 'Customers assigned %s');
define('TEXT_INFO_CUSTOMERS_REMAINING', 'Customers remaining %s');
define('TEXT_INFO_CUSTOMERS_SENT', 'Total Customers this newletter send to  %s - hit efficiency [%s]');
define('TEXT_INFO_ENABLED', 'Tick to Enable');
define('TEXT_INFO_NOT_ASSIGNED', 'Needs title/content');
define('TEXT_INFO_INCLUDED', 'Added');
define('TEXT_INFO_EMPTY', 'Empty');
define('TEXT_INFO_DELETE_ENTRIES', 'Confirm deletion of the following entries. Please note this operation is irreversible, box content entries will be permanently removed!');
define('TEXT_INFO_FROM_EMAIL', 'Send using E-Mail:');

define('ERROR_NEWSLETTER_REINSTALL', 'The newsletter system was not properly installed. Uninstall and Reinstall!');
define('ERROR_NEWSLETTER_NO_CUSTOMERS', 'There are no subscribed customers to send the newsletter');
define('ERROR_NEWSLETTER_INVALID', 'Invalid Newsletter specified');
define('ERROR_NEWSLETTER_EMAIL_FROM_INVALID', 'You need a valid E-Mail address to send Newsletters');
define('ERROR_NEWSLETTER_SUBJECT_EMPTY', 'The newsletter subject cannot be left empty');
define('ERROR_NEWSLETTER_DESCRIPTION_EMPTY', 'The newsletter description cannot be left empty');
define('ERROR_NOTHING_SELECTED', 'No entries selected. Use the checkboxes to mark entries first');
define('ERROR_MAXIMUM_STORAGE', 'Maximum number of customers in temporary storage reached, cannot add more');
define('WARNING_NEWSLETTER_CUSTOMERS_REMOVED', 'The selected Customers were removed from the newsletter list');
define('WARNING_WP_CHANGED', 'Word-Processor configuration changed');
define('SUCCESS_NEWSLETTER_RESET', 'Newsletter tracking fields cleared');
define('SUCCESS_NEWSLETTER_REMOVED', 'Selected Entry Removed');
define('SUCCESS_NEWSLETTER_DELETED', 'Newsletters Removed');
define('SUCCESS_NEWSLETTER_CREATED', 'Newsletter %s was created');
define('SUCCESS_NEWSLETTER_UPDATED', 'Newsletter %s updated');
define('SUCCESS_NEWSLETTER_CUSTOMERS_CLEARED', 'Temporary customers storage cleared');
define('SUCCESS_NEWSLETTER_SENT', 'Selected Newsletter was mailed to all chosen customers');
define('SUCCESS_INSERT_ASSIGNED', 'Selected content entries inserted');
?>