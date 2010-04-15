/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Quick notes for the plugin system
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
------------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
   Notes: 
   There are 3 types of plugin classes. At least 2 required by all plugins
   1.The install class that is called for installation, removal,
     configuring and deleting plugins. The install class must have the 
     install_ prefix in front the plugin name. 
     eg: The demo_message plugin should have the install_demo_message class
     The base class of the install class is called plug_manager located here:
     admin/includes/classes/plug_manager.php
     Certain member variables should be used with install classes
     - front=1: The plugin operates on the webfront
     - back=1: The plugin operates on the admin
     - status=1: The plugin should be activated right after installation
     - title: A short title for the plugin
     - author: The name of the person who wrote the plugin
     - version: The version of the plugin
     - framework: The package version this plugin was tested last
     - files_array: The files to copy to the web-front if any
     - admin_files_array: The files to copy to the admin-end if any
     - key: Never touch that
     If the plugin has configuration options you need:
     - set_options member function to setup the form
     - process_options member function to process the configuration
     The base class of an install class is plug_manager
     The plug_manager has several helper functions including:
     - copying/deleting files and paths
     - loading or storing configuration settings
     - deleting entries from the configuration table - plugins section
     If you have files for the webfront in order to be used you need
     a file to be named the same as the plugin folder
     eg: demo_message folder needs demo_message.php in the files_array
     If you have files for the admin in order to be used you need
     a file to be named the same as the plugin folder along with the
     admin_ prefix
     eg: demo_message folder needs admin_demo_message.php in the admin_files_array
     Once your file is invoked you can call or process other files your plugin
     may require
   2.The plugins_admin.php is the second type of class and operates on the admin
     The plugins_admin.php class is located here:
     admin/includes/classes/plugins_admin.php
     Its purpose is to invoke the main admin plugins code during run-time
     Loading of plugins takes places via the $g_plugin->invoke(method) call
     Where method is the method a plugin relies upon to do something.
     The admin_history_system.php can have a method called add_current_page so
     it can be called as $g_plugin->invoke('add_current_page', $arg1, $arg2)
     Optional arguments can be passed with the $g_plugin->invoke following 
     the method name.
     If the plugin operates on the admin then in its folder it should include
     a file called admin_history_system.php that extends into plugins_base
     plugins_base is the base class for the admin plugins (runtime code) and
     includes functions to load and save configuration options during run-time
   3.The third type of class is similar to the second but operates on the webfront
     Two classes files one of invocation the other for run-time are dedicated
     for plugins which operate on the web-front. The class files are:
     includes/classes/plugins_front.php - Invokes the plugin for processing
     includes/classes/plugins_base.php - The run-time base class the plugin extends to
     If the plugin is inside the voting_system folder and operates on the webfront 
     then in its folder it should include a file called voting_system.php 
     that extends into plugins_base and should be set in the files_array of the
     install class to be copied to the web-front.
   For a basic functionality see how the demo_message plugin operates
//----------------------------------------------------------------------------
*/