<?php
/*
Came from:
  $Id: english.php,v 1.114 2003/07/09 18:13:39 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Language and Country specific configuration
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
$DATE_FORMAT_SHORT                   = '%m/%d/%Y';
$DATE_FORMAT_LONG                    = '%A %d %B, %Y';
$DATE_FORMAT                         = '%m/%d/%Y';
$DATE_TIME_FORMAT                    = '%m/%d/%Y %H:%M:%S';

// Global entries for the <html> tag
$HTML_PARAMS                         = 'dir="ltr" lang="en"';
// charset for web content and emails
$CHARSET                             = 'utf-8';

// default title
$TITLE                               = STORE_NAME;
$HEADER_TITLE_CATALOG                = STORE_NAME;

$BOX_HEADING_SEARCH                  = 'Search this site';

// pull down default text
$PULL_DOWN_DEFAULT                   = 'Please Select';

// Split Pages
$TEXT_RESULT_PAGE                    = 'Pages:';
$TEXT_DISPLAY_NUMBER_OF_ENTRIES      = 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> entries)';

$PREVNEXT_TITLE_FIRST_PAGE           = 'First Page';
$PREVNEXT_TITLE_PREVIOUS_PAGE        = 'Previous Page';
$PREVNEXT_TITLE_NEXT_PAGE            = 'Next Page';
$PREVNEXT_TITLE_LAST_PAGE            = 'Last Page';
$PREVNEXT_TITLE_PAGE_NO              = 'Page %d';
$PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE  = 'Previous Set of %d Pages';
$PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE  = 'Next Set of %d Pages';
$PREVNEXT_BUTTON_FIRST               = '&lt;&lt;FIRST';
$PREVNEXT_BUTTON_PREV                = '&laquo;';
$PREVNEXT_BUTTON_NEXT                = '&raquo;';
$PREVNEXT_BUTTON_LAST                = 'LAST&gt;&gt;';

$IMAGE_BUTTON_BACK                   = 'Back';
$IMAGE_BUTTON_CONTINUE               = 'Continue';
$IMAGE_BUTTON_SUBMIT                 = 'Submit';
$IMAGE_BUTTON_SEARCH                 = 'Search';
$IMAGE_NOT_AVAILABLE                 = 'image_not_available.jpg';

$ICON_ERROR                          = 'Error';
$ICON_WARNING                        = 'Warning';
$ICON_SUCCESS                        = 'Success';

$FOOTER_TEXT_BODY                    = 'Copyright &copy; ' . date('Y') . '&nbsp;' . '<a href="' . tep_href_link() . '" title="' . STORE_NAME . '">' . STORE_NAME . '</a> - My Blog Name, All Rights Reserved.';
$FOOTER_TEXT_BODY_POWERED            = 'E-Commerce Engine Copyright &copy; 2003 <a href="http://sourceforge.net/project/showfiles.php?group_id=31957&amp;package_id=74386&amp;release_id=440294" target="_blank" rel="nofollow">osCommerce</a>';
$FOOTER_TEXT_BODY_CUSTOM             = 'I-Metrics CMS by <a href="http://www.asymmetrics.com" target="_blank">Asymmetric Software</a>';
$FOOTER_TEXT_BODY_COPYRIGHT          = '<a href="http://demos.asymmetrics.com/" target="_blank" rel="nofollow">Template by Asymmetrics</a>';

$TEXT_CONTACT                        = 'Contact';
$TEXT_SEE_ALL                        = 'See All';
$TEXT_READ_MORE                      = '...more on ';
$TEXT_INFO_NA                        = 'N/A';
$TEXT_NEXT                           = '&raquo;';
$TEXT_PREVIOUS                       = '&laquo;';

$TEXT_INVALID_COLLECTION             = 'Invalid Collection';
$TEXT_INVALID_COLLECTION_INFO        = 'Could not find the content for this request. Check the collection types';
$TEXT_INVALID_PAGE                   = 'Missing Text Page Template';
$TEXT_INVALID_PAGE_INFO              = 'Could not find the text page template for this request. Check the general text template file';

// Global Notices
$WARNING_INSTALL_DIRECTORY_EXISTS    = 'Installation directory exists. Please remove the directory for security reasons.';
$WARNING_NOT_SENDING_COOKIES         = 'Not sending Cookies - Headers Already Sent';
$WARNING_ADMIN_PRESENT               = 'Administration presence - Operating in Admin Mode via ip: %s';
?>
