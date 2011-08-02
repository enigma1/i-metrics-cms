<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2007 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// META-G Types for the META Zones component for Admin end
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

define('HEADING_TITLE', 'META-G Types');
define('HEADING_META_TYPES_ADD', 'Add a New META-G Type');
define('HEADING_META_TYPES_UPDATE', 'Update META-G Types');

define('TABLE_HEADING_META_NAME', 'Name');
define('TABLE_HEADING_META_HANDLER', 'Handler');
define('TABLE_HEADING_META_LINKAGE', 'Linkage');
define('TABLE_HEADING_META_CLASS', 'Class (.php)');
define('TABLE_HEADING_SORT_ORDER', 'Sort Order');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_SELECT', 'Select');

define('TEXT_PAGE_SELECT', 'Page Select On/Off');
define('TEXT_INFO_INSERT', 'Insert a new META-G Type, where: <br /><b>Name:</b> is the name of the type to be inserted. It\'s used for information purposes.<br /><b>Class:</b> is the class .php file that generates the META-G names in osC Admin. Enter only the name of the file without the .php extension.<br /><b>Sort Order:</b> is the order for META-G to process the parameters when it generates the Meta-Tags.<br /><b>Linkage:</b> When multiple parameters are encountered by META-G those with different linkage are overriden with the one with the lowest linkage value. Parameters that have the same linkage value are maintained. Typically Products should have the lowest linkage values. Default is 1.');
define('TEXT_INFO_UPDATE', 'Update selected META-G Types. In addition to the parameters explained earlier there is a <b>Status</b> for each class. When disabled the catalog META-G code does not process the converted names and instead uses the default osc parameters to generate the Meta Tag.');

define('WARNING_NOTHING_SELECTED', 'No entries selected. Use the checkboxes to select entries first');
define('ERROR_INVALID_INPUT', 'Cannot create entry one or more invalid parameters specified');
define('SUCCESS_ENTRY_CREATE', 'A new entry was created');
define('SUCCESS_ENTRY_REMOVED', 'Selected entries were removed');
?>