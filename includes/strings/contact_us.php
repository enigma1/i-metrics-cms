<?php
/*
  $Id: contact_us.php,v 1.7 2002/11/19 01:48:08 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Contact Us');
define('NAVBAR_TITLE', 'Contact Us');
define('EMAIL_SUBJECT', 'Enquiry from ' . STORE_NAME);

define('ENTRY_NAME', 'Full Name:');
define('ENTRY_EMAIL', 'E-Mail Address:');
define('ENTRY_ENQUIRY', 'Enquiry:');

define('TEXT_SELECT_DEPARTMENT', 'Select a Department:');
define('TEXT_CONTACT_DETAILS', 'Enquiry Details');
define('ENTRY_SUBJECT', 'Subject:');

define('ERROR_SEND_MAIL', 'Cannot send the email through the specified mail server');
define('ERROR_EMAIL_ADDRESS', 'The email address doesn\'t appear to be valid');
define('ERROR_ENQUIRY_EMPTY', 'The enquiry field is empty');
define('ERROR_NAME_EMPTY', 'The name field is empty');
define('ERROR_SUBJECT_EMPTY', 'The subject field is empty');
define('SUCCESS_ENQUIRY_SENT', 'Your enquiry has been successfully sent to the Website Owner.');
?>