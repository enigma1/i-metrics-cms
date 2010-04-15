<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2007 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO Types for the SEO Zones component for osCommerce Admin
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

define('HEADING_TITLE', 'SEO-G Types');
define('HEADING_SEO_TYPES_ADD', 'Add a New SEO-G Type');
define('HEADING_SEO_TYPES_UPDATE', 'Update SEO-G Types');

define('TABLE_HEADING_SEO_NAME', 'Name');
define('TABLE_HEADING_SEO_HANDLER', 'Handler');
define('TABLE_HEADING_SEO_SUBFIX', 'Subfix');
define('TABLE_HEADING_SEO_LINKAGE', 'Linkage');
define('TABLE_HEADING_SEO_CLASS', 'Class');
define('TABLE_HEADING_SEO_PREFIX', 'Prefix');
define('TABLE_HEADING_SORT_ORDER', 'Sort Order');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_SELECT', 'Select');

define('TEXT_PAGE_SELECT', 'Page Select On/Off');
define('TEXT_INFO_INSERT', 'Insert a new SEO-G Type, where: <br /><b>Name:</b> is the name of the type to be inserted. It\'s used for information purposes.<br /><b>Handler:</b> list of secondary scripts that can handle the same type. For example with products the product_info represents the primary handler where the SEO-G will apply the main prefix. Secondary Handler will be the product_reviews.php. Separate multiple handlers by coma eg: <em><b>product_reviews.php, products_reviews_write.php</b></em>. Do not include the primary handler.<br /><b>Subfix:</b> Each subfix is associated with a secondary handler. The order sub-fixes are entered correspond directly to the secondary handler. Subfixes must be separated with comas. Subfixes replace the secondary handlers when the SEO-G URLs are generated. eg: products_review.php is the secondary handler with "review" the subfix. This will make a product appear like products_name_review.html. Notice the subfix is appended at the end of the url.<br /><b>Class:</b> is the class .php file that generates the SEO-G names in osC Admin. Enter only the name of the file without the .php extension.<br /><b>Prefix:</b> This name represents the primary handler and it\'s appended with the URLs for each field.<br /><b>Sort Order:</b> is the order for SEO-G to process the parameters when it generates the URLs.<br /><b>Linkage:</b> When multiple parameters are encountered by SEO-G those with different linkage are overriden with the one with the lowest linkage value. Parameters that have the same linkage value are maintained. Typically Products should have the lowest linkage values. Default is 1.');
define('TEXT_INFO_UPDATE', 'Update selected SEO-G Types. In addition to the parameters explained earlier there is a <b>Status</b> for each class. When disabled the catalog SEO-G code does not process the converted names and instead uses the default osc parameters to generate the URL.');

define('WARNING_NOTHING_SELECTED', 'No entries selected. Use the checkboxes to select entries first');
define('ERROR_INVALID_INPUT', 'Cannot create entry one or more invalid parameters specified');
define('SUCCESS_ENTRY_CREATE', 'A new entry was created');
define('SUCCESS_ENTRY_REMOVED', 'Selected entries were removed');
?>