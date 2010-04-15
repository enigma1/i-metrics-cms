<?php
/*
  $Id: english.php,v 1.114 2003/07/09 18:13:39 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

// look in your $PATH_LOCALE/locale directory for available locales
// or type locale -a on the server.
// Examples:
// on RedHat try 'en_US'
// on FreeBSD try 'en_US.ISO_8859-1'
// on Windows try 'en', or 'English'
// @setlocale(LC_TIME, 'en_US.ISO_8859-1');
@setlocale(LC_TIME, 'en_US.UTF-8');

define('DATE_FORMAT_SHORT', '%m/%d/%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%A %d %B, %Y'); // this is used for strftime()
define('DATE_FORMAT', 'm/d/Y'); // this is used for date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');

////
// Return date in raw format
// $date should be in format mm/dd/yyyy
// raw date is in format YYYYMMDD, or DDMMYYYY
function tep_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . substr($date, 0, 2) . substr($date, 3, 2);
  }
}

// Global entries for the <html> tag
define('HTML_PARAMS','dir="ltr" lang="en"');

// charset for web pages and emails
if(!defined('CHARSET') ) {
  //define('CHARSET', 'iso-8859-1');
  define('CHARSET', 'utf-8');
}

// page title
define('TITLE', STORE_NAME);

// header text in includes/header.php
define('HEADER_TITLE_TOP', 'Top');
define('HEADER_TITLE_CATALOG', STORE_NAME);

// quick_find box text in includes/boxes/quick_find.php
define('BOX_HEADING_SEARCH', 'Search our website');
define('BOX_SEARCH_TEXT', 'Use keywords to find what you are looking for.');
define('BOX_SEARCH_ADVANCED_SEARCH', 'Advanced Search');

define('BOX_HEADING_NEWSLETTER', 'Newsletter');

// reviews box text in includes/boxes/reviews.php
define('BOX_HEADING_REVIEWS', 'Reviews');
define('BOX_REVIEWS_WRITE_REVIEW', 'Write a review');
define('BOX_REVIEWS_NO_REVIEWS', 'There are currently no reviews');
define('BOX_REVIEWS_TEXT_OF_5_STARS', '%s of 5 Stars!');

// pull down default text
define('PULL_DOWN_DEFAULT', 'Please Select');
define('TYPE_BELOW', 'Type Below');

define('JS_ERROR_NO_PAYMENT_MODULE_SELECTED', '* Please select a payment method for your order.\n');

define('JS_ERROR_SUBMITTED', 'This form has already been submitted. Please press Ok and wait for this process to be completed.');

define('FORM_REQUIRED_INFORMATION', '* Required information');

// constants for use in tep_prev_next_display function
define('TEXT_RESULT_PAGE', 'Pages:');
define('TEXT_DISPLAY_NUMBER_OF_ENTRIES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> entries)');

define('PREVNEXT_TITLE_FIRST_PAGE', 'First Page');
define('PREVNEXT_TITLE_PREVIOUS_PAGE', 'Previous Page');
define('PREVNEXT_TITLE_NEXT_PAGE', 'Next Page');
define('PREVNEXT_TITLE_LAST_PAGE', 'Last Page');
define('PREVNEXT_TITLE_PAGE_NO', 'Page %d');
define('PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE', 'Previous Set of %d Pages');
define('PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE', 'Next Set of %d Pages');
define('PREVNEXT_BUTTON_FIRST', '&lt;&lt;FIRST');
define('PREVNEXT_BUTTON_PREV', '&laquo;');
define('PREVNEXT_BUTTON_NEXT', '&raquo;');
define('PREVNEXT_BUTTON_LAST', 'LAST&gt;&gt;');

define('IMAGE_BUTTON_BACK', 'Back');
define('IMAGE_BUTTON_CONTINUE', 'Continue');
define('IMAGE_BUTTON_SUBMIT', 'Submit');

define('IMAGE_BUTTON_QUICK_FIND', 'Quick Find');
define('IMAGE_BUTTON_REVIEWS', 'Reviews');
define('IMAGE_BUTTON_SEARCH', 'Search');
define('IMAGE_BUTTON_WRITE_REVIEW', 'Write Review');

define('ICON_ARROW_RIGHT', 'more');
define('ICON_ERROR', 'Error');
define('ICON_SUCCESS', 'Success');
define('ICON_WARNING', 'Warning');

define('TEXT_BY', ' by ');
define('TEXT_REVIEW_BY', 'by %s');
define('TEXT_REVIEW_WORD_COUNT', '%s words');
define('TEXT_REVIEW_RATING', 'Rating: %s [%s]');
define('TEXT_REVIEW_DATE_ADDED', 'Date Added: %s');
define('TEXT_NO_REVIEWS', 'There are currently no product reviews.');

define('TEXT_REQUIRED', '<span class="errorText">Required</span>');

define('FOOTER_TEXT_BODY', 'Copyright &copy; ' . date('Y') . '&nbsp;' . '<a href="' . tep_href_link() . '" title="' . STORE_NAME . '">' . STORE_NAME . '</a> - My Blog Name, All Rights Reserved.');
define('FOOTER_TEXT_BODY_POWERED', 'E-Commerce Engine Copyright &copy; 2003 <a href="http://sourceforge.net/project/showfiles.php?group_id=31957&amp;package_id=74386&amp;release_id=440294" target="_blank" rel="nofollow">osCommerce</a>');
define('FOOTER_TEXT_BODY_CUSTOM', 'I-Metrics CMS by <a href="http://www.asymmetrics.com" target="_blank">Asymmetric Software</a>');
define('FOOTER_TEXT_BODY_COPYRIGHT', '<a href="http://www.freewebsitetemplates.com/" target="_blank" rel="nofollow">Template by freewebsitetemplates</a>');

define('TEXT_HOME', 'Home');
define('TEXT_ACCOUNT', 'My Account');
define('TEXT_CONTACT', 'Contact');

define('TEXT_ALT_HOME', STORE_NAME . ' - Home');
define('TEXT_ALT_ACCOUNT', 'My Account');
define('TEXT_ALT_CONTACT', 'Contact ' . STORE_NAME );

define('IMAGE_BUTTON_DETAILS', 'Details');
define('TEXT_SORT_BY', 'Sort By:');

define('TEXT_ADVANCED_SEARCH', 'SEARCH:');
define('TEXT_HELP_ADVANCED_SEARCH', STORE_NAME . ' search options');
define('TEXT_HELP_QUICK_SEARCH', STORE_NAME . ' quick search');

define('TEXT_PLEASE_SELECT', 'Please Select');
define('TEXT_SEE_ALL', 'See All');
define('TEXT_READ_MORE', '...more on ');

define('TEXT_NEXT', '&raquo;');
define('TEXT_PREVIOUS', '&laquo;');
define('IMAGE_NOT_AVAILABLE', 'image_not_available.jpg');

// Global Notices
define('WARNING_SESSION_AUTO_START', 'Warning: session.auto_start is enabled - please disable this php feature in php.ini and restart the web server.');
define('WARNING_INSTALL_DIRECTORY_EXISTS', 'Installation directory exists. Please remove the directory for security reasons.');
?>
