<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Plugins Manager Strings
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
define('HEADING_TITLE', 'Plugins Manager');
define('HEADING_INSTALL_TITLE', 'Install Plugin');
define('HEADING_COPY_TITLE', 'Copy Plugin Files');
define('HEADING_UNINSTALL_TITLE', 'Uninstall Plugin');
define('HEADING_REMOVE_TITLE', 'Completely Erase Plugin');
define('HEADING_CONFIGURE_TITLE', 'Configuration Options');

define('TABLE_HEADING_NAME', 'Plugins');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_FILE', 'Path/Filename');
define('TABLE_HEADING_VERSION', 'Version');
define('TABLE_HEADING_FRAMEWORK', 'Release');
define('TABLE_HEADING_AUTHOR', 'Author');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_COMPRESSED', 'Compressed Plugins');

define('TEXT_INFO_COPY_NOTICE', 'About to copy files for the following plugin:');
define('TEXT_INFO_COPY_WARN', 
  '<p>Useful for multiple sites or incomplete installations, this operation will re-install just the web-front files of a plugin. Different configurations for multiple sites can coexist in the same database.</p>' . 
  '<p>If the plugin supports different templates it is important to select the right one during this step. Following the copy operation you should re-configure the plugin if specific options are available.</p>' .
  '<p>If you are using a single admin for multiple sites, you will need to repeat this operation for each site. Use the multi-sites management tool to switch sites. Make sure you backup your web-front files before this operation starts.</p>' . 
  '<p style="color: #EE0000; font-weight: bold;">Important: write permissions must be enabled for the server scripts to properly delete or modify files. Different plugins may require read/write access to different files.</p>'
);

define('TEXT_INFO_INSTALL_NOTICE', 'About to install the following plugin:');
define('TEXT_INFO_INSTALL_WARN', 
  '<p>Make sure you backup your files and database before installing or uninstalling plugins<br />' . 
  'Plugins can write to the database and override files</p>' . 
  '<p style="color: #EE0000; font-weight: bold;">Important: write permissions must be enabled for the server scripts to properly delete or modify files. Different plugins may require read/write access to different files.</p>'
);
define('TEXT_INFO_NEW_FILES', 'The following files will be created, if already exist will be overriden!');
define('TEXT_INFO_COPY_FILES_OVER', 'The following files will be overriden if exist');

define('TEXT_INFO_UNINSTALL_NOTICE', 'About to uninstall the following plugin:');
define('TEXT_INFO_UNINSTALL_WARN', 
  '<p>This operation will remove the files from the current site and drop the database tables related with this plugin.</p>' . 
  '<p>Manual removal of files on the web-front of different sites may required if you are using a single database and multiple sites. Make sure you backup your files and database before installing or uninstalling plugins<br />' . 
  'Plugins can write to the database, override or remove files and change configuration settings. Ensure your site files were recently backed up. There is no reverse process once you confirm this operation.</p>' . 
  '<p style="color: #EE0000; font-weight: bold;">Important: write permissions must be enabled for the server scripts to properly delete or modify files. Different plugins may require read/write access to different files.</p>'
);
define('TEXT_INFO_REMOVE_FILES', 'The following files will be removed');

define('TEXT_INFO_REMOVE_NOTICE', 'About to completely uninstall and remove all references of the following plugin:');
define('TEXT_INFO_REMOVE_WARN', 
  '<p>This operation will remove the files from the current site, all files from the administration end and drop the database tables related with this plugin.</p>' . 
  '<p>Manual removal of files on the web-front of different sites may required if you are using a single database and multiple sites. Make sure you backup your files and database before installing or uninstalling plugins<br />' . 
  '<span style="color: #EE0000; font-weight: bold;">Removing a plugin deletes all references from the database and associated files installed. In addition it erases all installation files and installation paths</span><br />' . 
  'Plugins can write to the database, override or remove files and change configuration settings. Ensure your site files were recently backed up. There is no reverse process once you confirm this operation.</p>' . 
  '<p style="color: #EE0000; font-weight: bold;">Important: write permissions must be enabled for the server scripts to properly delete or modify files. Different plugins may require read/write access to different files.</p>'
);


define('TEXT_FILE_FOLDER', 'Folder:');
define('TEXT_FILE_UPLOAD', 'File to Upload:');
define('TEXT_FILE_UPLOAD_FOLDER', 'Upload in:');

define('TEXT_INFO_SORT_ORDER', 'Order to Process:');
define('TEXT_INFO', 'Information');
define('TEXT_INFO_INSTALL', 'Install');
define('TEXT_INFO_UNINSTALL', 'Uninstall');
define('TEXT_INFO_REMOVE', 'Completely Remove Plugin');
define('TEXT_INFO_CONFIGURE', 'Configure Plugin');
define('TEXT_INFO_BASIC_SETTINGS', 'Basic Settings');
define('TEXT_INFO_EDIT', 'Enable/Disable Plugin');
define('TEXT_INFO_COPY_FILES', 'Copy Web-Front Files again');
define('TEXT_INFO_ENABLE', 'Enable');
define('TEXT_INFO_DISABLE', 'Disable');

define('TEXT_INFO_SYNOPSIS', 'Synopsis:');
define('TEXT_INFO_SIDE', 'Operates in:');
define('TEXT_INFO_WEB_FRONT', 'Web-Front');
define('TEXT_INFO_ADMIN', 'Admin-End');
define('TEXT_INFO_CODE_CLASS', 'Code Class:');
define('TEXT_INFO_KEY', 'Plugin Folder:');
define('TEXT_INFO_CURRENT_STATUS', 'Current Status:');
define('TEXT_INFO_EDIT_PLUGIN_INTRO', 
  '<p>The default configuration of the plugins manager can enable or disable the selected plugin. Disabled plugins are not invoked but they can still have active dependencies with other plugins or with core functions.</p>' . 
  '<p>Also note this operation will not copy files to the web-front. If you are switching sites for the first time, use the Copy operation to copy web-front files of a specific template and then re-configure the plugin.</p>'
);

define('TEXT_INFO_SELECTED', 'Selected Plugin');
define('TEXT_INFO_EMPTY', 'No Plugins Selected');
define('TEXT_INFO_OPTIONS', 'Options');

define('TEXT_INFO_NO_HELP', 'There is no brief description about this plugin');
define('TEXT_INFO_DECOMPRESS', 'Decompress %s into %s');
define('TEXT_INFO_DECOMPRESS_TITLE', 'Extract');

define('TEXT_INFO_DECOMPRESS_INTRO', 'This operation will decompress the following archive:');
define('TEXT_INFO_INTO_FOLDER', 'Into the following administration folder:');

define('TEXT_INFO_ADMIN_USE', 'Uses Admin Only');
define('TEXT_INFO_FRONT_USE', 'Uses Front Only');
define('TEXT_INFO_BOTH_USE', 'Uses Both Admin/Front');
define('TEXT_INFO_COMPRESS_USE', 'Compressed Plugin');
define('TEXT_INFO_PREINSTALL_FAILED', 'Addition plugin options have halted this installation. Check if the all the plugin files are present');
define('TEXT_INFO_PRECOPY_FAILED', 'Addition plugin options have halted copying of web-front files. Check if the all the plugin files are present');

define('ERROR_PLUGIN_PARTIAL_INSTALL', 'Installation of %s is incomplete, check the file permissions and plugin files');
define('ERROR_PLUGIN_PARTIAL_UNINSTALL', 'Could not remove all files of %s, check the file permissions');
define('WARNING_PLUGIN_ALREADY_INSTALLED', 'The plugin is already installed, will be best to uninstall it before re-installing it!');
define('WARNING_PLUGIN_NOT_INSTALLED', 'The plugin you are trying to uninstall does not seem to be installed!');
define('WARNING_PLUGIN_EDIT_NOT_INSTALLED', 'The plugin you are trying to edit does not seem to be installed!');
define('WARNING_PLUGIN_NOT_CONFIGURABLE', 'The plugin you are trying to access is either not installed or it is not configurable!');
define('WARNING_PLUGIN_STATUS_CHANGE', 'Plugin status change completed!');
define('WARNING_PLUGIN_REINSERT', 'Plugin %s exists in the database, Re-Inserting');
define('WARNING_PLUGIN_FILES_COPIED', 'Web-Front files copied for Plugin %s');
define('SUCCESS_PLUGIN_INSTALLED', 'Installation of %s is complete');
define('SUCCESS_PLUGIN_UNINSTALLED', 'Uninstall of %s completed');
?>