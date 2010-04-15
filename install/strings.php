<?php
  define('TEXT_INFO_VERSION', 'Version 1.11');
  $copyright_string='
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Configuration Script
//----------------------------------------------------------------------------
// I-Metrics CMS ' . TEXT_INFO_VERSION . '
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
';

define('HEADING_CAPTION', 'I-Metrics CMS by Asymmetrics');
define('HEADING_TITLE', 'I-Metrics CMS Installation');

// Temporary Files
define('FILE_TMP_FRONT_SERVER', 'tmp_front_server.php');
define('FILE_TMP_ADMIN_SERVER', 'tmp_admin_server.php');
define('FILE_TMP_ADMIN_FRONT', 'tmp_admin_front.php');
define('FILE_TMP_DBASE', 'tmp_database.php');
define('FILE_TMP_CONFIG', 'tmp_config.php');
define('FILE_I_METRICS_CMS_DBASE', 'i-metrics-cms.sql');
define('FILE_LICENSE', 'license');
define('FILE_LICENSE_AMENDMENT', 'license_amendment');

define('TEXT_LEGEND_DBASE_UPLOAD', 'Uploading Database Tables');
define('TEXT_LEGEND_SERVER_INFO', 'Server Information');
define('TEXT_LEGEND_DBASE_INFO', 'Database Information');
define('TEXT_LEGEND_CONFIG_REVIEW', 'Final Configuration Review');
define('TEXT_LEGEND_CONFIG_INFO', 'Site Configuration');
define('TEXT_LEGEND_LICENSE', 'License Agreement');

define('TEXT_INFO_HTTP_SERVER', 'Web Server Address:');
define('TEXT_INFO_HTTPS_SERVER', 'Secure Server Address:');
define('TEXT_INFO_DIR_FS_CATALOG', 'Server Physical Path:');
define('TEXT_INFO_DIR_WS_HTTP_CATALOG', 'Web Front Path:');
define('TEXT_INFO_DIR_WS_HTTPS_CATALOG', 'Secure Front Path:');
define('TEXT_INFO_HTTP_COOKIE_DOMAIN', 'Web Cookies Domain:');
define('TEXT_INFO_HTTPS_COOKIE_DOMAIN', 'Secure Cookies Domain:');
define('TEXT_INFO_HTTP_COOKIE_PATH', 'Web Cookies Path:');
define('TEXT_INFO_HTTPS_COOKIE_PATH', 'Secure Cookies Path:');
define('TEXT_INFO_LICENSE_AGREE', 'I have read, understand and I agree to all terms of the GNU/GPL license listed here.');

define('TEXT_INFO_TEMPLATE', 'Website Template to use:');
define('TEXT_INFO_TEMPLATE_HELP', 'All templates are DIV based');
define('TEXT_INFO_OS_TYPE', 'Site Operating System:');
define('TEXT_INFO_OS_UNIX', 'Unix type');
define('TEXT_INFO_OS_OTHER', 'Other');
define('TEXT_INFO_SITE_NAME', 'Website Name:');
define('TEXT_INFO_EMAIL_ADDRESS', 'Site E-Mail Address:');
define('TEXT_INFO_EMAIL_PASSWORD', 'Site E-Mail Password:');
define('TEXT_INFO_HELPDESK_MAILSERVER', 'Mail Server:');
define('TEXT_INFO_SEO_URLS', 'Enable Friendly Links:');
define('TEXT_INFO_SEO_NOTICE', 'Friendly URLs can be set on Apache Server only!');
define('TEXT_INFO_EMAIL_PASSWORD_NOTICE', 'Password to retrieve email if you use the helpdesk');

define('TEXT_INFO_YES', 'Yes');
define('TEXT_INFO_NO', 'No');

define('TEXT_INFO_DB_SERVER', 'Database Server:');
define('TEXT_INFO_DB_SERVER_USERNAME', 'Database Username:');
define('TEXT_INFO_DB_SERVER_PASSWORD', 'Database Password:');
define('TEXT_INFO_DB_DATABASE', 'Database Name:');

define('TEXT_INFO_ADMIN_ACCESS', 'Administration');
define('TEXT_INFO_FRONT_ACCESS', 'Website');

// Button Help Titles
define('BUTTON_INFO_CANCEL', 'Cancel this operation and return to the Server Setup.');
define('BUTTON_INFO_SERVER_SETUP', 'Click here to submit the Server information.');
define('BUTTON_INFO_DBASE_SETUP', 'Click here to submit the Database information.');
define('BUTTON_INFO_FINAL_SETUP', 'Click here to begin uploading the database.');
define('BUTTON_INFO_DBASE_COMPLETE', 'Click here to finish the installation process of the I-Metrics CMS.');
define('BUTTON_INFO_CONFIG_SETUP', 'Click here to submit Site Configration.');

define('BUTTON_SERVER_SETUP', 'Setup Server');
define('BUTTON_DBASE_SETUP', 'Setup Database');
define('BUTTON_DBASE_COMPLETE', 'Complete Installation');
define('BUTTON_CONFIRM_CONFIG', 'Confirm Configuration');
define('BUTTON_BEGIN', 'Begin Installation');
define('BUTTON_FINISH', 'Finish Installation');
define('BUTTON_CANCEL', 'Abort Installation');
define('BUTTON_CONFIG_SETUP', 'Setup Website');

define('DEFAULT_TEMPLATE', 'stock');
$default_templates_array = array(
  'stock' => 'Stock - 2 Columns SEO Fixed Layout',
  'ebooks' => 'E-Books - 3 Columns SEO Fluent Layout',
  '3col' => 'Traditional - 3 Columns Standard Layout',
  'cstrike' => 'Counter-Strike - 3 Cols SEO Fixed/Compact',
);

// Error Strings
define('ERROR_EMPTY_HTTP_SERVER', 'Invalid Server Address');
define('ERROR_EMPTY_HTTPS_SERVER', 'Invalid Secure Server Address');
define('ERROR_EMPTY_DIR_FS_CATALOG', 'Invalid Physical Server Path');
define('ERROR_EMPTY_HTTP_CATALOG_PATH', 'Invalid Web Front Path');
define('ERROR_EMPTY_HTTPS_CATALOG_PATH', 'Invalid Secure Web Front Path');

define('ERROR_EMPTY_HTTP_COOKIE_DOMAIN', 'Invalid Cookie Domain');
define('ERROR_EMPTY_HTTPS_COOKIE_DOMAIN', 'Invalid Secure Cookie Domain');
define('ERROR_EMPTY_HTTP_COOKIE_PATH', 'Invalid Cookie Path');
define('ERROR_EMPTY_HTTPS_COOKIE_PATH', 'Invalid Secure Cookie Path');

define('ERROR_EMPTY_DB_SERVER', 'Invalid Database Server Address');
define('ERROR_EMPTY_DB_SERVER_USERNAME', 'Invalid Database User');
define('ERROR_EMPTY_DB_SERVER_PASSWORD', 'Invalid Database Password');
define('ERROR_EMPTY_DB_DATABASE', 'Invalid Database Name');

define('ERROR_EMPTY_OS_TYPE', 'You must select the O/S');
define('ERROR_EMAIL_ADDRESS', 'Invalid E-Mail Address');
define('ERROR_SITE_NAME', 'Enter the name of your website');


define('ERROR_GLOBAL_SITE_CONFIG', 'There are errors gathering the site information. Correct them and re-submit this form');
define('ERROR_GLOBAL_SERVER_CONFIG', 'There are errors setting up the server configuration. Correct them and re-submit this form');
define('ERROR_GLOBAL_DBASE_CONFIG', 'There are errors setting up the database configuration. Correct them and re-submit this form');
define('ERROR_GLOBAL_DBASE_CREATE_CONFIG', 'I cannot create/write the Configuration File for the Database. Check the installation folder for write permission');
define('ERROR_GLOBAL_DBASE_CONNECT', 'I cannot connect to the database with the given credentials. Please re-enter the database information.');
define('ERROR_GLOBAL_DBASE_CREATE', 'Seems like I do not have privileges to create a New Database with the given info. Use your host\'s cPanel to create the database then resubmit the database information.');
define('ERROR_GLOBAL_SERVER_READ_CONFIG', 'I cannot read the Server Temporary Configuration file. Have you gone through the Server Configuration first?');
define('ERROR_GLOBAL_DBASE_READ_CONFIG', 'I cannot read the Database Temporary Configuration file. Have you gone through the Database Configuration first?');
define('ERROR_GLOBAL_DBASE_READ_MAIN', 'I cannot find and read the Main Database File for the I-Metrics CMS.');
define('ERROR_GLOBAL_FRONT_WRITE_MAIN_CONFIG', 'I cannot write to the main configuration file. Make sure I have access rights to change it or to create it.');
define('ERROR_GLOBAL_ADMIN_WRITE_MAIN_CONFIG', 'I cannot write to the admin configuration file. Make sure I have access rights to change it or to create it.');
define('ERROR_GLOBAL_ADMIN_WRITE_SITE_CONFIG', 'I cannot write to the admin site configuration file. I need to have access rights to change it or to create it.');
define('ERROR_GLOBAL_LICENSE_AGREE', 'You must agree to the full terms of the GNU/GPL license to continue the installation.');
define('ERROR_GLOBAL_UPLOADING_DATABASE', 'Now Uploading the Database Contents. Please do not interrupt this process.');
define('ERROR_GLOBAL_SITE_WRITE_CONFIG', 'I cannot create/write to a temporary site configuration file. I need write permission to the installation folder');
define('ERROR_GLOBAL_SITE_READ_CONFIG', 'I cannot read the Website Temporary Configuration File. Have you gone through the Site Configuration first?');
define('ERROR_GLOBAL_TMP_WRITE_CONFIG', 'I Cannot Create/Write temporary files. Check the installation folder I need write permission');
define('ERROR_GLOBAL_TMP_WRITE_CONFIG_SITE', 'I Cannot Create/Write temporary configuration site file. Check the installation folder I need write permission');
define('ERROR_GLOBAL_WRITE_HTACCESS', 'I could not create/write the .htaccess file for the friendly URLs. Check the I-Metrics folder I need write permission');
define('ERROR_GLOBAL_COPY_TEMPLATE', 'I cannot copy the template files. Check the write permissions for the template directory and if the templete files exist under the install directory.');
// HTML body Strings
define('TEXT_CONTENT_FOOTER', '
        <div>I-Metrics CMS by <a href="http://demos.asymmetrics.com" target="_blank" rel="nofollow">Asymmetric Software</a></div>
        <div>E-Commerce Engine Copyright &copy; 2003 <a href="http://sourceforge.net/project/showfiles.php?group_id=31957&amp;package_id=74386&amp;release_id=440294" target="_blank" rel="nofollow">osCommerce</a></div>
        <div>Copyright &copy; 2010&nbsp;<a href="http://www.asymmetrics.com" title="Asymmetric Software">Asymmetric Software</a>, All Rights Reserved.</div>
'
);

define('TEXT_CONTENT_SERVER_SETUP', 
  '<p>Please Review the Server Information. The presets should be accurate but if you need to make changes, modify the form and submit the server information to setup the I-Metrics CMS application.</p>' . 
  '<p style="color: #EE0000; font-style: italic; font-weight: bold;">Also note I will need write access to the installation and main folder of the I-Metrics application in order to generate the necessary configuration files.</p>'
);

define('TEXT_CONTENT_DATABASE_SETUP', 
  '<p>Please enter the Database Information in this form and make any necessary changes to the presets. Then click the submit button to setup the database configuration file for the I-Metrics CMS.</p>' . 
  '<p style="color: #EE0000; font-style: italic; font-weight: bold;">Also note I will the necessary database privileges to create the database you want in case it does not exist.</p>'
);

define('TEXT_CONTENT_DATABASE_PRE_UPLOAD', 
  '<p>Please review the Final Configuration Settings, then click Confirm if everything is correct. If you want to make changes click back to reset the current settings.</p>'
);

define('TEXT_CONTENT_CONFIG_SETUP', 
  '<p>Please enter the site information including an email address and mail server if you are planning to receive emails on your site. For the SEO URLs to operate the server must be Apache. If you are not sure do not tick the SEO checkbox.</p>'
);

define('TEXT_CONTENT_DATABASE_UPLOAD', 
  '<p>Uploading the Database Contents. Please do not interrupt this process.</p>'
);

define('TEXT_CONTENT_DATABASE_COMPLETE', 
  '<p>Database uploaded successfully.</p>'
);

define('TEXT_INSTALLATION_COMPLETE', 
  '<p>The installation is now complete. Thank you for choosing I-Metrics CMS for your Website. For further information and updates for this package please visit <a href="http://demos.asymmetrics.com">Asymmetrics I-Metrics CMS</a></p>' . 
  '<h2 style="color: #FF0000">You should now Protect Your Admin Folder</h2>' . 
  '<p>Make sure you password protect your administration folder from your <b>host\'s cPanel</b>. This installation cannot protect it automatically because the administrator can receive emails with attachments, store database files on the server, therefore these files can be accessed directly from the outside</p>' . 
  '<p>Typically your host should have an option called Password Protect Folders or Password Protect Directories. Use that option and protect the admin folder of the I-Metrics CMS.</p>' .
  '<p>For maximum security utilize <b>your host\'s cPanel tools</b>. The I-Metrics CMS includes <b>Apache server-side scripts to protect its sub-folders</b>, from being directly accessed. If you are on a different type of server check with your host the proper way of protecting sub-folders</p>' . 
  '<p>You can now access the following:</p>'
);

?>