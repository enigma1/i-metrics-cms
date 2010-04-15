<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Configuration Script
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
  ini_set('error_reporting', E_ALL);
  ini_set('display_errors', 1);
  //error_reporting(E_ALL & ~E_NOTICE);

  require('strings.php');
  require('functions.php');

  $current_dir = getcwd();
  $physical_path = preg_replace('/\w+\/\.\.\//', '', dirname(__FILE__));
  $physical_path = str_replace('\\', '/', $physical_path);

  $action = (isset($_GET['action']) ? prepare_input($_GET['action']) : '');
  $errors_array = array();
  $error_string = (isset($_GET['error_string']) ? prepare_input($_GET['error_string']) : '');
  $contents = '';

  $stylesheet = '';
  read_contents($physical_path . '/stylesheet.css', $stylesheet);

  switch($action) {
    case 'server_detect':
      $HTTP_SERVER_INFO = $HTTPS_SERVER_INFO = $DIR_FS_CATALOG_INFO = $HTTP_CATALOG_PATH_INFO = $HTTPS_CATALOG_PATH_INFO = $HTTP_COOKIE_DOMAIN_INFO = $HTTPS_COOKIE_DOMAIN_INFO = $HTTP_COOKIE_PATH_INFO = $HTTPS_COOKIE_PATH_INFO = '';
      $root = $_SERVER['HTTP_HOST'];
      if( $root == 'localhost' ) {
        $root = '127.0.0.1';
      }
      $HTTP_SERVER = 'http://' . $root;
      $cookie_domain = $root;
      $pos = strpos($cookie_domain, 'www.');
      if( $pos !== false && !$pos ) {
        $HTTPS_COOKIE_DOMAIN = $HTTP_COOKIE_DOMAIN = substr($cookie_domain, 4);
      } else {
        $HTTPS_COOKIE_DOMAIN = $HTTP_COOKIE_DOMAIN = $cookie_domain;
      }

      $pos = strpos($physical_path, 'install');
      if( $pos !== false ) {
        $DIR_FS_CATALOG = substr($physical_path, 0, $pos);
      } else {
        $DIR_FS_CATALOG = $physical_path . '/';
      }

      $pos = strpos($_SERVER['REQUEST_URI'], 'install');
      if( $pos !== false ) {
        $cookies_path = substr($_SERVER['REQUEST_URI'], 0, $pos);
      } else {
        $cookies_path = $_SERVER['REQUEST_URI'];
      }

      $path_array = explode('/', $cookies_path);
      array_pop($path_array);
      $HTTP_CATALOG_PATH = $HTTPS_CATALOG_PATH = $HTTPS_COOKIE_PATH = $HTTP_COOKIE_PATH = implode('/', $path_array) . '/';
      break;
    case 'server_process':
      $HTTP_SERVER_INFO = $HTTPS_SERVER_INFO = $DIR_FS_CATALOG_INFO = $HTTP_CATALOG_PATH_INFO = $HTTPS_CATALOG_PATH_INFO = $HTTP_COOKIE_DOMAIN_INFO = $HTTPS_COOKIE_DOMAIN_INFO = $HTTP_COOKIE_PATH_INFO = $HTTPS_COOKIE_PATH_INFO = '';
      $error = false;

      $HTTP_SERVER = (isset($_POST['HTTP_SERVER']) ? prepare_input($_POST['HTTP_SERVER']) : '');
      $HTTPS_SERVER = (isset($_POST['HTTPS_SERVER']) ? prepare_input($_POST['HTTPS_SERVER']) : '');
      $DIR_FS_CATALOG = (isset($_POST['DIR_FS_CATALOG']) ? prepare_input($_POST['DIR_FS_CATALOG']) : '');
      $HTTP_CATALOG_PATH = (isset($_POST['HTTP_CATALOG_PATH']) ? prepare_input($_POST['HTTP_CATALOG_PATH']) : '');
      $HTTPS_CATALOG_PATH = (isset($_POST['HTTPS_CATALOG_PATH']) ? prepare_input($_POST['HTTPS_CATALOG_PATH']) : '');
      $HTTP_COOKIE_DOMAIN = (isset($_POST['HTTP_COOKIE_DOMAIN']) ? prepare_input($_POST['HTTP_COOKIE_DOMAIN']) : '');
      $HTTPS_COOKIE_DOMAIN = (isset($_POST['HTTPS_COOKIE_DOMAIN']) ? prepare_input($_POST['HTTPS_COOKIE_DOMAIN']) : '');
      $HTTP_COOKIE_PATH = (isset($_POST['HTTP_COOKIE_PATH']) ? prepare_input($_POST['HTTP_COOKIE_PATH']) : '');
      $HTTPS_COOKIE_PATH = (isset($_POST['HTTPS_COOKIE_PATH']) ? prepare_input($_POST['HTTPS_COOKIE_PATH']) : '');

      if( empty($HTTP_SERVER) ) {
        $HTTP_SERVER_INFO = ERROR_EMPTY_HTTP_SERVER;
        $error = true;
      }
      if( empty($HTTPS_SERVER) ) {
        $HTTPS_SERVER_INFO = ERROR_EMPTY_HTTPS_SERVER;
        $error = true;
      }
      if( empty($DIR_FS_CATALOG) ) {
        $DIR_FS_CATALOG_INFO = ERROR_EMPTY_DIR_FS_CATALOG;
        $error = true;
      }
      if( empty($HTTP_CATALOG_PATH) ) {
        $HTTP_CATALOG_PATH_INFO = ERROR_EMPTY_HTTP_CATALOG_PATH;
        $error = true;
      }
      if( empty($HTTPS_CATALOG_PATH) ) {
        $HTTPS_CATALOG_PATH_INFO = ERROR_EMPTY_HTTPS_CATALOG_PATH;
        $error = true;
      }

      if( empty($HTTP_COOKIE_DOMAIN) ) {
        $HTTP_COOKIE_DOMAIN_INFO = ERROR_EMPTY_HTTP_COOKIE_DOMAIN;
        $error = true;
      }
      if( empty($HTTPS_COOKIE_DOMAIN) ) {
        $HTTPS_COOKIE_DOMAIN_INFO = ERROR_EMPTY_HTTPS_COOKIE_DOMAIN;
        $error = true;
      }
      if( empty($HTTP_COOKIE_PATH) ) {
        $HTTP_COOKIE_PATH_INFO = ERROR_EMPTY_HTTP_COOKIE_PATH;
        $error = true;
      }
      if( empty($HTTPS_COOKIE_PATH) ) {
        $HTTPS_COOKIE_PATH_INFO = ERROR_EMPTY_HTTPS_COOKIE_PATH;
        $error = true;
      }

      if( $error ) {
        $action = '';
        $errors_array[] = ERROR_GLOBAL_SERVER_CONFIG;
        break;
      }

      if( file_exists(FILE_TMP_FRONT_SERVER) ) {
        @unlink(FILE_TMP_FRONT_SERVER);
      }
      if( file_exists(FILE_TMP_ADMIN_SERVER) ) {
        @unlink(FILE_TMP_ADMIN_SERVER);
      }
      if( file_exists(FILE_TMP_ADMIN_FRONT) ) {
        @unlink(FILE_TMP_ADMIN_FRONT);
      }

      $contents = '  define(\'HTTP_SERVER\', \'' . $HTTP_SERVER . '\');' . "\n" .
                  '  define(\'HTTPS_SERVER\', \'' . $HTTPS_SERVER . '\');' . "\n" .
                  '  define(\'ENABLE_SSL\', false);' . "\n" .
                  '  define(\'HTTP_COOKIE_DOMAIN\', \'' . $HTTP_COOKIE_DOMAIN . '\');' . "\n" .
                  '  define(\'HTTPS_COOKIE_DOMAIN\', \'' . $HTTPS_COOKIE_DOMAIN . '\');' . "\n" .
                  '  define(\'HTTP_COOKIE_PATH\', \'' . $HTTP_COOKIE_PATH . '\');' . "\n" .
                  '  define(\'HTTPS_COOKIE_PATH\', \'' . $HTTPS_COOKIE_PATH . '\');' . "\n" .
                  '  define(\'DIR_WS_HTTP_CATALOG\', \'' . $HTTP_CATALOG_PATH . '\');' . "\n" .
                  '  define(\'DIR_WS_HTTPS_CATALOG\', \'' . $HTTPS_CATALOG_PATH . '\');' . "\n" .
                  '  define(\'DIR_WS_IMAGES\', \'images/\');' . "\n" .
                  '  define(\'DIR_WS_INCLUDES\', \'includes/\');' . "\n" .
                  '  define(\'DIR_WS_FUNCTIONS\', DIR_WS_INCLUDES . \'functions/\');' . "\n" .
                  '  define(\'DIR_WS_CLASSES\', DIR_WS_INCLUDES . \'classes/\');' . "\n" .
                  '  define(\'DIR_WS_MODULES\', DIR_WS_INCLUDES . \'modules/\');' . "\n" .
                  '  define(\'DIR_WS_STRINGS\', DIR_WS_INCLUDES . \'strings/\');' . "\n" .
                  '  define(\'DIR_WS_PLUGINS\', DIR_WS_INCLUDES . \'plugins/\');' . "\n" .
                  '  define(\'DIR_WS_TEMPLATE\', DIR_WS_INCLUDES . \'template/\');' . "\n\n" .
                  '  define(\'DIR_WS_DOWNLOAD_PUBLIC\', \'pub/\');' . "\n" .
                  '  define(\'DIR_FS_CATALOG\', \'' . $DIR_FS_CATALOG . '\');' . "\n" .
                  '  define(\'DIR_FS_DOWNLOAD\', DIR_FS_CATALOG . \'download/\');' . "\n" .
                  '  define(\'DIR_FS_DOWNLOAD_PUBLIC\', DIR_FS_CATALOG . \'pub/\');' . "\n\n";

      if( !write_contents(FILE_TMP_FRONT_SERVER, $contents) ) {
        $error = true;
        $errors_array[] = ERROR_GLOBAL_TMP_WRITE_CONFIG;
      }

      $contents = '  define(\'HTTP_SERVER\', \'' . $HTTP_SERVER . '\');' . "\n" .
                  '  define(\'DIR_FS_DOCUMENT_ROOT\', \'' . $DIR_FS_CATALOG . '\');' . "\n" .
                  '  define(\'DIR_WS_ADMIN\', \'' . $HTTP_CATALOG_PATH . 'admin/\');' . "\n" .
                  '  define(\'DIR_FS_ADMIN\', DIR_FS_DOCUMENT_ROOT . \'admin/\');' . "\n" .
                  '  define(\'DIR_WS_IMAGES\', \'images/\');' . "\n" .
                  '  define(\'DIR_WS_ICONS\', DIR_WS_IMAGES . \'icons/\');' . "\n" .
                  '  define(\'DIR_WS_INCLUDES\', \'includes/\');' . "\n" .
                  '  define(\'DIR_WS_BOXES\', DIR_WS_INCLUDES . \'boxes/\');' . "\n" .
                  '  define(\'DIR_WS_FUNCTIONS\', DIR_WS_INCLUDES . \'functions/\');' . "\n" .
                  '  define(\'DIR_WS_CLASSES\', DIR_WS_INCLUDES . \'classes/\');' . "\n" .
                  '  define(\'DIR_WS_MODULES\', DIR_WS_INCLUDES . \'modules/\');' . "\n" .
                  '  define(\'DIR_WS_STRINGS\', DIR_WS_INCLUDES . \'strings/\');' . "\n" .
                  '  define(\'DIR_WS_PLUGINS\', DIR_WS_INCLUDES . \'plugins/\');' . "\n" .
                  '  define(\'DIR_FS_BACKUP\', DIR_FS_ADMIN . \'backups/\');' . "\n\n";

      if( !write_contents(FILE_TMP_ADMIN_SERVER, $contents) ) {
        $error = true;
        $errors_array[] = ERROR_GLOBAL_TMP_WRITE_CONFIG;
      }

      $contents = '  define(\'HTTP_CATALOG_SERVER\', \'' . $HTTP_SERVER . '\');' . "\n" .
                  '  define(\'HTTPS_CATALOG_SERVER\', \'' . $HTTPS_SERVER . '\');' . "\n" .
                  '  define(\'ENABLE_SSL_CATALOG\', \'false\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG\', \'' . $HTTP_CATALOG_PATH . '\');' . "\n" .
                  '  define(\'DIR_FS_CATALOG\', DIR_FS_DOCUMENT_ROOT);' . "\n" .
                  '  define(\'DIR_WS_CATALOG_IMAGES\', DIR_WS_CATALOG . \'images/\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG_ICONS\', DIR_WS_CATALOG_IMAGES . \'icons/\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG_BANNERS\', DIR_WS_CATALOG_IMAGES . \'banners/\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG_STRINGS\', DIR_WS_CATALOG . \'includes/strings/\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG_MODULES\', DIR_WS_CATALOG . \'includes/modules/\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG_PLUGINS\', DIR_WS_CATALOG . \'includes/plugins/\');' . "\n" . 
                  '  define(\'DIR_WS_CATALOG_TEMPLATE\', DIR_WS_CATALOG . \'includes/template/\');' . "\n\n";

      if( !write_contents(FILE_TMP_ADMIN_FRONT, $contents) ) {
        $error = true;
        $errors_array[] = ERROR_GLOBAL_TMP_WRITE_CONFIG_SITE;
      }

      if( $error ) { 
        $action = 'server_process';
        break;
      }
      redirect( $_SERVER['SCRIPT_NAME'] . '?action=database');
      break;

    case 'database':
    case 'database_setup':
      $DB_SERVER_INFO = $DB_SERVER_USERNAME_INFO = $DB_SERVER_PASSWORD_INFO = $DB_DATABASE_INFO = '';
      $DB_SERVER = 'localhost';
      $DB_SERVER_USERNAME = '';
      $DB_SERVER_PASSWORD = '';
      $DB_DATABASE = '';
      if( $action == 'database' ) {
        break;
      }

      $error = false;

      $DB_SERVER = (isset($_POST['DB_SERVER']) ? prepare_input($_POST['DB_SERVER']) : '');
      $DB_SERVER_USERNAME = (isset($_POST['DB_SERVER_USERNAME']) ? prepare_input($_POST['DB_SERVER_USERNAME']) : '');
      $DB_SERVER_PASSWORD = (isset($_POST['DB_SERVER_PASSWORD']) ? prepare_input($_POST['DB_SERVER_PASSWORD']) : '');
      $DB_DATABASE = (isset($_POST['DB_DATABASE']) ? prepare_input($_POST['DB_DATABASE']) : '');

      if( empty($DB_SERVER) ) {
        $DB_SERVER_INFO = ERROR_EMPTY_DB_SERVER;
        $error = true;
      }
      if( empty($DB_SERVER_USERNAME) ) {
        $DB_SERVER_USERNAME_INFO = ERROR_EMPTY_DB_SERVER_USERNAME;
        $error = true;
      }
      if( empty($DB_SERVER_PASSWORD) ) {
        $DB_SERVER_PASSWORD_INFO = ERROR_EMPTY_DB_SERVER_PASSWORD;
        $error = true;
      }
      if( empty($DB_DATABASE) ) {
        $DB_DATABASE_INFO = ERROR_EMPTY_DB_DATABASE;
        $error = true;
      }

      if( $error ) {
        $errors_array[] = ERROR_GLOBAL_DBASE_CONFIG;
        $action = 'database';
        break;
      }

      if( file_exists(FILE_TMP_DBASE) ) {
        @unlink(FILE_TMP_DBASE);
      }

      $contents = '  define(\'DB_SERVER\', \'' . $DB_SERVER . '\');' . "\n" .
                  '  define(\'DB_SERVER_USERNAME\', \'' . $DB_SERVER_USERNAME . '\');' . "\n" .
                  '  define(\'DB_SERVER_PASSWORD\', \'' . $DB_SERVER_PASSWORD . '\');' . "\n" .
                  '  define(\'DB_DATABASE\', \'' . $DB_DATABASE . '\');' . "\n" .
                  '  define(\'USE_PCONNECT\', \'false\');' . "\n" .
                  '  define(\'STORE_SESSIONS\', \'mysql\');' . "\n\n";

      if( !write_contents(FILE_TMP_DBASE, $contents) ) {
        $error = true;
        $errors_array[] = ERROR_GLOBAL_DBASE_CREATE_CONFIG;
        $action = 'database';
        break;
      }
      redirect( $_SERVER['SCRIPT_NAME'] . '?action=config');
      break;
    case 'config':
      $contents = '';
      read_contents(FILE_TMP_FRONT_SERVER, $contents);
      eval($contents);

      $INSTALL_TEMPLATE = DEFAULT_TEMPLATE;
      $INSTALL_OS_TYPE_INFO = $INSTALL_EMAIL_ADDRESS_INFO = $INSTALL_SITE_NAME_INFO = '';
      $root = $_SERVER['HTTP_HOST'];
      if( $root == 'localhost' ) {
        $root = '127.0.0.1';
      }
      $mailserver = $root;

      $pos = strpos($mailserver, 'www.');
      if( $pos !== false && !$pos ) {
        $INSTALL_HELPDESK_MAILSERVER = 'mail.' . substr($mailserver, 4) . ':110';
      } else {
        $INSTALL_HELPDESK_MAILSERVER = 'mail.' . $mailserver . ':110';
      }

      $INSTALL_OS_TYPE = 0;
      $pos = strpos(dirname(__FILE__), '\\');
      if( $pos !== false ) {
        $INSTALL_OS_TYPE = 1;
      }
      $INSTALL_EMAIL_ADDRESS = '';
      $INSTALL_SEO_URLS = 0;

      chdir(DIR_FS_CATALOG);
      erase_dir(DIR_WS_TEMPLATE);
      chdir($current_dir);

      $templates_array = array();
      $dir_array = array_filter(glob('templates/' . '*'), 'is_dir');
      foreach($dir_array as $key => $value) {
        $templates_array[] = array(
          'id' => basename($value),
          'text' => basename($value),
        );
      }

    case 'config_setup':
      $INSTALL_OS_TYPE_INFO = $INSTALL_EMAIL_ADDRESS_INFO = $INSTALL_SITE_NAME_INFO = '';
      if( file_exists(FILE_TMP_CONFIG) ) {
        @unlink(FILE_TMP_CONFIG);
      }

      if( $action == 'config' ) {
        break;
      }

      $error = false;
      $INSTALL_TEMPLATE = (isset($_POST['TEMPLATE']) ? prepare_input($_POST['TEMPLATE']) : DEFAULT_TEMPLATE);
      $INSTALL_OS_TYPE = (isset($_POST['INSTALL_OS_TYPE']) ? (int)$_POST['INSTALL_OS_TYPE'] : 0);
      $INSTALL_SEO_URLS = (isset($_POST['INSTALL_SEO_URLS']) ? 1 : 0);
      $INSTALL_EMAIL_ADDRESS = (isset($_POST['INSTALL_EMAIL_ADDRESS']) ? prepare_input($_POST['INSTALL_EMAIL_ADDRESS']) : '');
      $INSTALL_EMAIL_PASSWORD = (isset($_POST['INSTALL_EMAIL_PASSWORD']) ? prepare_input($_POST['INSTALL_EMAIL_PASSWORD']) : '');
      $INSTALL_SITE_NAME = (isset($_POST['INSTALL_SITE_NAME']) ? prepare_input($_POST['INSTALL_SITE_NAME']) : '');
      $INSTALL_HELPDESK_MAILSERVER = (isset($_POST['INSTALL_HELPDESK_MAILSERVER']) ? prepare_input($_POST['INSTALL_HELPDESK_MAILSERVER']) : '');

      if( empty($INSTALL_EMAIL_ADDRESS) ) {
        $INSTALL_EMAIL_ADDRESS_INFO = ERROR_EMAIL_ADDRESS;
        $error = true;
      }

      if( empty($INSTALL_SITE_NAME) ) {
        $INSTALL_SITE_NAME_INFO = ERROR_SITE_NAME;
        $error = true;
      }

      $contents = '';
      read_contents(FILE_TMP_FRONT_SERVER, $contents);
      eval($contents);

      $templates_array = array();
      $dir_array = array_filter(glob('templates/' . '*'), 'is_dir');
      $template_found = false;

      foreach($dir_array as $key => $value) {
        if( $INSTALL_TEMPLATE == basename($value) ) {
          copy_dir($value, DIR_FS_CATALOG . DIR_WS_TEMPLATE);
          $template_found = true;
        }
        $templates_array[] = array(
          'id' => basename($value),
          'text' => basename($value),
        );
      }

      if( !$template_found ) {
        $error = true;
        $errors_array[] = ERROR_GLOBAL_COPY_TEMPLATE;
      }

      if( $INSTALL_SEO_URLS ) {
        chdir(DIR_FS_CATALOG);
        $contents = '#';
        if( !write_contents('.htaccess', $contents) ) {
          $error = true;
          $errors_array[] = ERROR_GLOBAL_WRITE_HTACCESS;
        }
        chdir($current_dir);
      }


      if( $error ) {
        $action = 'config';
        $errors_array[] = ERROR_GLOBAL_SITE_CONFIG;
        break;
      }

      $contents = '  define(\'INSTALL_OS_TYPE\', \'' . $INSTALL_OS_TYPE . '\');' . "\n" .
                  '  define(\'INSTALL_SITE_NAME\', \'' . $INSTALL_SITE_NAME . '\');' . "\n" .
                  '  define(\'INSTALL_TEMPLATE\', \'' . $INSTALL_TEMPLATE . '\');' . "\n" .
                  '  define(\'INSTALL_PLUGINS\', \'admin/includes/plugins/\');' . "\n" .
                  '  define(\'INSTALL_HELPDESK_MAILSERVER\', \'' . $INSTALL_HELPDESK_MAILSERVER . '\');' . "\n" .
                  '  define(\'INSTALL_EMAIL_ADDRESS\', \'' . $INSTALL_EMAIL_ADDRESS . '\');' . "\n" .
                  '  define(\'INSTALL_EMAIL_PASSWORD\', \'' . $INSTALL_EMAIL_PASSWORD . '\');' . "\n" .
                  '  define(\'INSTALL_SEO_URLS\', \'' . $INSTALL_SEO_URLS . '\');' . "\n";

      if( !write_contents(FILE_TMP_CONFIG, $contents) ) {
        $error = true;
        $errors_array[] = ERROR_GLOBAL_SITE_WRITE_CONFIG;
        $action = 'config';
        break;
      }

      redirect( $_SERVER['SCRIPT_NAME'] . '?action=database_pre_upload');
      break;
    case 'database_pre_upload':
    case 'database_upload':
      $DB_SERVER_INFO = $DB_SERVER_USERNAME_INFO = $DB_SERVER_PASSWORD_INFO = $DB_DATABASE_INFO = '';

      $error = false;

      if( !read_contents(FILE_TMP_FRONT_SERVER, $contents) ) {
        $error = true;
        $error_string = ERROR_GLOBAL_SERVER_READ_CONFIG;
      } else {
        eval($contents);
      }

      if( !read_contents(FILE_TMP_DBASE, $contents) ) {
        $error = true;
        $error_string = ERROR_GLOBAL_DBASE_READ_CONFIG;
      } else {
        eval($contents);
      }

      if( !read_contents(FILE_TMP_CONFIG, $contents) ) {
        $error = true;
        $error_string = ERROR_GLOBAL_SITE_READ_CONFIG;
      } else {
        eval($contents);
      }

      if( $error ) {
        redirect( $_SERVER['SCRIPT_NAME'] . '?action=database&error_string=' . $error_string);
      }

      if( $action == 'database_pre_upload' ) {
        break;
      }

      if( !file_exists(FILE_I_METRICS_CMS_DBASE) ) {
        $error_string = ERROR_GLOBAL_DBASE_READ_MAIN;
        redirect( $_SERVER['SCRIPT_NAME'] . '?action=database&error_string=' . $error_string);
      }

      $buffer1 = '<?php' . $copyright_string . "\n";
      read_contents(FILE_TMP_FRONT_SERVER, $contents);
      $buffer1 .= $contents;
      read_contents(FILE_TMP_DBASE, $contents);
      $buffer1 .= $contents;
      $buffer1 .= '?>' . "\n";

      $buffer2 = '<?php' . $copyright_string . "\n";
      read_contents(FILE_TMP_ADMIN_SERVER, $contents);
      $buffer2 .= $contents;
      $buffer2 .= '?>' . "\n";

      $buffer3 = '<?php' . $copyright_string . "\n";
      read_contents(FILE_TMP_ADMIN_FRONT, $contents);
      $buffer3 .= $contents;
      read_contents(FILE_TMP_DBASE, $contents);
      $buffer3 .= $contents;
      $buffer3 .= '?>' . "\n";

      chdir(DIR_FS_CATALOG);

      if( !write_contents(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'configure.php', $buffer1) ) {
        $errors_string = ERROR_GLOBAL_FRONT_WRITE_MAIN_CONFIG;
        redirect( $_SERVER['SCRIPT_NAME'] . '?action=database&error_string=' . $error_string);
      }

      if( !write_contents(DIR_FS_CATALOG . 'admin/' . DIR_WS_INCLUDES . 'configure.php', $buffer2) ) {
        $errors_string = ERROR_GLOBAL_ADMIN_WRITE_MAIN_CONFIG;
        redirect( $_SERVER['SCRIPT_NAME'] . '?action=database&error_string=' . $error_string);
      }

      if( !write_contents(DIR_FS_CATALOG . 'admin/' . DIR_WS_INCLUDES . 'configure_site.php', $buffer3) ) {
        $errors_string = ERROR_GLOBAL_ADMIN_WRITE_SITE_CONFIG;
        redirect( $_SERVER['SCRIPT_NAME'] . '?action=database&error_string=' . $error_string);
      }

      require(DIR_WS_CLASSES . 'database.php');
      chdir($current_dir);

      $g_db = new dbase();
      $result = $g_db->connect();
      if( !$result ) {
        $error_string = ERROR_GLOBAL_DBASE_CONNECT;
        redirect( $_SERVER['SCRIPT_NAME'] . '?action=database&error_string=' . $error_string);
      }

      $result = $g_db->select(DB_DATABASE);
      if( !$result ) {
        $g_db->query("create database " . $g_db->input(DB_DATABASE) . " DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci");
        $result = $g_db->select(DB_DATABASE);
        if( !$result ) {
          $error_string = ERROR_GLOBAL_DBASE_CREATE;
          redirect( $_SERVER['SCRIPT_NAME'] . '?action=database&error_string=' . $error_string);
        }
      }
      $errors_array[] = ERROR_GLOBAL_UPLOADING_DATABASE;
      break;

    case 'finish':
      read_contents(FILE_TMP_FRONT_SERVER, $contents);
      eval($contents);
      read_contents(FILE_TMP_DBASE, $contents);
      eval($contents);
      read_contents(FILE_TMP_CONFIG, $contents);
      eval($contents);

      chdir(DIR_FS_CATALOG);
      require(DIR_WS_CLASSES . 'database.php');
      require(DIR_WS_INCLUDES . 'database_tables.php');

      $g_db = new dbase();
      $g_db->connect();
      pre_configure_site();

      if(INSTALL_SEO_URLS == 1) {
        $contents = '#-MS- SEO-G Added' . "\n" .
                    'Options +FollowSymLinks' . "\n" . 
                    'RewriteEngine On' . "\n" . 
                    'RewriteBase ' . DIR_WS_HTTP_CATALOG . "\n" .
                    'RewriteRule ^(.*).asp$ root.php?$1.asp&%{QUERY_STRING}' . "\n" . 
                    '#-MS- SEO-G Added EOM' . "\n";

        $result = write_contents('.htaccess', $contents);
        if( !$result ) {
          $errors_array[] = ERROR_GLOBAL_WRITE_HTACCESS;
        }
      }
      chdir($current_dir);

      remove_directory($current_dir);
      chdir(DIR_FS_CATALOG);
      break;
    case 'license_agreement':
      $amend = '';
      if( !read_contents(FILE_LICENSE, $contents) || !read_contents(FILE_LICENSE_AMENDMENT, $amend) ) {
        redirect($_SERVER['SCRIPT_NAME']);
      }
      if( !isset($_POST['license']) ) {
        $error_string = ERROR_GLOBAL_LICENSE_AGREE;
        redirect( $_SERVER['SCRIPT_NAME'] . '?error_string=' . $error_string);
      }
      redirect($_SERVER['SCRIPT_NAME'] . '?action=server_detect');
      break;
    default:
      if( !read_contents(FILE_LICENSE, $contents) || !read_contents(FILE_LICENSE_AMENDMENT, $amend) ) {
        $errors_array[] = ERROR_GLOBAL_LICENSE_AGREE;
      }
      $contents = nl2br(htmlspecialchars($amend  . "\n\n" . $contents));
      break;
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo HEADING_CAPTION; ?></title>
<?php
  if( $action == 'finish' ) {
?>
<style type="text/css"><?php echo $stylesheet; ?></style>
<?php
  } else {
?>
<link rel="stylesheet" type="text/css" href="stylesheet.css" />
<?php
  }
?>
</head>
<body>
  <div id="wrapper">
      <div id="header"><h1><?php echo HEADING_TITLE; ?></h1><br /><?php echo TEXT_INFO_VERSION; ?></div>
<?php
  for($i=0, $j=count($errors_array); $i<$j; $i++) {
    echo '          <div class="messageStackError">' . $errors_array[$i] . '</div>' . "\n";
  }
  if( !empty($error_string) ) {
    echo '          <div class="messageStackError">' . $error_string . '</div>' . "\n";
  }
?>
      <div id="mainbody">
<?php
  if( $action == 'finish' ) {
?>
        <div class="innerContent" style="background: #F4FFF4">
          <div><?php echo TEXT_INSTALLATION_COMPLETE; ?></div>
          <div><?php echo '<a href="' . HTTP_SERVER . DIR_WS_HTTP_CATALOG . '">' . TEXT_INFO_FRONT_ACCESS . '</a>'; ?></div>
          <div><?php echo '<a href="' . HTTP_SERVER . DIR_WS_HTTP_CATALOG . 'admin">' . TEXT_INFO_ADMIN_ACCESS . '</a>'; ?></div>
        </div>
<?php
  } elseif( $action == 'config' || $action == 'config_setup') {
?>
        <div style="clear: both"><form name="install_form" action="<?php echo $_SERVER['SCRIPT_NAME'] . '?action=config_setup'?>" method="post">
          <fieldset>
            <legend><?php echo TEXT_LEGEND_CONFIG_INFO; ?></legend>
            <div class="splitColumn"><?php echo TEXT_CONTENT_CONFIG_SETUP; ?></div>
            <div class="innerContent">
              <div class="form_label"><?php echo TEXT_INFO_OS_TYPE; ?></div>
              <div class="form_input"><input type="radio" name="INSTALL_OS_TYPE" value="0" <?php echo ($INSTALL_OS_TYPE == 0)?'checked="checked"':''; ?> />&nbsp;<?php echo TEXT_INFO_OS_UNIX; ?><input type="radio" name="INSTALL_OS_TYPE" value="1" <?php echo ($INSTALL_OS_TYPE == 1)?'checked="checked"':''; ?> />&nbsp;<?php echo TEXT_INFO_OS_OTHER; ?></div>
              <div class="form_label"><?php echo TEXT_INFO_SITE_NAME; ?></div>
              <div class="form_input"><input type="text" name="INSTALL_SITE_NAME" class="txtInput" /><?php echo $INSTALL_SITE_NAME_INFO; ?></div>
              <div class="form_label"><?php echo TEXT_INFO_EMAIL_ADDRESS; ?></div>
              <div class="form_input"><input type="text" name="INSTALL_EMAIL_ADDRESS" class="txtInput" /><?php echo $INSTALL_EMAIL_ADDRESS_INFO; ?></div>
              <div class="form_label"><?php echo TEXT_INFO_EMAIL_PASSWORD; ?></div>
              <div class="form_input"><input type="text" name="INSTALL_EMAIL_PASSWORD" class="txtInput" /><?php echo TEXT_INFO_EMAIL_PASSWORD_NOTICE; ?></div>
              <div class="form_label"><?php echo TEXT_INFO_HELPDESK_MAILSERVER; ?></div>
              <div class="form_input"><input type="text" name="INSTALL_HELPDESK_MAILSERVER" value="<?php echo $INSTALL_HELPDESK_MAILSERVER; ?>" class="txtInput" /></div>
              <div class="form_label"><?php echo TEXT_INFO_TEMPLATE; ?></div>
              <div class="form_input"><select name="TEMPLATE">
<?php
    for($i=0, $j=count($templates_array); $i<$j; $i++) {
      echo '<option value="' . $templates_array[$i]['id'] . '"';
      if( $INSTALL_TEMPLATE == $templates_array[$i]['text'] ) {
        echo ' selected="selected"';
      }
      if( isset($default_templates_array[$templates_array[$i]['id']] ) ) {
        $templates_array[$i]['text'] = $default_templates_array[$templates_array[$i]['id']];
      }
      echo '>' . $templates_array[$i]['text'] . '</option>';
    }
?>
              </select><?php echo '&nbsp;&nbsp;' . TEXT_INFO_TEMPLATE_HELP; ?></div>
              <div class="form_label"><?php echo TEXT_INFO_SEO_URLS; ?></div>
              <div class="form_input"><input type="checkbox" name="INSTALL_SEO_URLS" /><?php echo '<b style="color: #FF0000">' . TEXT_INFO_SEO_NOTICE . '</b>'; ?></div>
            </div>
          </fieldset>
          <div class="formButtons">
            <div><input type="submit" title="<?php echo BUTTON_INFO_CONFIG_SETUP; ?>" name="submit_config" value="<?php echo BUTTON_CONFIG_SETUP; ?>" /></div>
          </div>
        </form></div>
<?php
  } elseif( $action == 'database' || $action == 'database_setup') {
?>
        <div style="clear: both"><form name="install_form" action="<?php echo $_SERVER['SCRIPT_NAME'] . '?action=database_setup'?>" method="post">
          <fieldset>
            <legend><?php echo TEXT_LEGEND_DBASE_INFO; ?></legend>
            <div class="splitColumn"><?php echo TEXT_CONTENT_DATABASE_SETUP; ?></div>
            <div class="innerContent">
              <div class="form_label"><?php echo TEXT_INFO_DB_SERVER; ?></div>
              <div class="form_input"><input type="text" name="DB_SERVER" value="localhost" class="txtInput" /><?php echo $DB_SERVER_INFO; ?></div>
              <div class="form_label"><?php echo TEXT_INFO_DB_SERVER_USERNAME; ?></div>
              <div class="form_input"><input type="text" name="DB_SERVER_USERNAME" class="txtInput" /><?php echo $DB_SERVER_USERNAME_INFO; ?></div>
              <div class="form_label"><?php echo TEXT_INFO_DB_SERVER_PASSWORD; ?></div>
              <div class="form_input"><input type="text" name="DB_SERVER_PASSWORD" class="txtInput" /><?php echo $DB_SERVER_PASSWORD_INFO; ?></div>
              <div class="form_label"><?php echo TEXT_INFO_DB_DATABASE; ?></div>
              <div class="form_input"><input type="text" name="DB_DATABASE" class="txtInput" /><?php echo $DB_DATABASE_INFO; ?></div>
            </div>
          </fieldset>
          <div class="formButtons">
            <div><input type="submit" title="<?php echo BUTTON_INFO_DBASE_SETUP; ?>" name="submit_database" value="<?php echo BUTTON_DBASE_SETUP; ?>" /></div>
          </div>
        </form></div>
<?php
  } elseif( $action == 'database_upload') {
?>
        <div>
          <fieldset>
            <legend><?php echo TEXT_LEGEND_DBASE_UPLOAD; ?></legend>
            <div class="splitColumn"><b><?php echo TEXT_CONTENT_DATABASE_UPLOAD; ?></b></div>
            <div class="scrollContent" style="height: 400px; overflow: auto; padding: 0px 14px 10px 14px; width: 820px;">
<?php
    parse_mysql_dump(FILE_I_METRICS_CMS_DBASE);
?>
            </div>
          </fieldset>
          <div class="innerContent" style="background: #F4FFF4"><h2><?php echo TEXT_CONTENT_DATABASE_COMPLETE; ?></h2></div>
          <div style="clear: both"><form name="install_form" action="<?php echo $_SERVER['SCRIPT_NAME'] . '?action=finish'?>" method="post">
            <div class="formButtons" style="text-align: right;">
              <div style="text-align: left"><input type="submit" title="<?php echo BUTTON_INFO_DBASE_COMPLETE; ?>" name="installation" value="<?php echo BUTTON_FINISH; ?>" /></div>
            </div>
          </form></div>
        </div>
<?php
  } elseif( $action == 'database_pre_upload') {
?>
        <div style="clear: both">
          <fieldset>
            <legend><?php echo TEXT_LEGEND_CONFIG_REVIEW; ?></legend>
            <div class="splitColumn"><?php echo TEXT_CONTENT_DATABASE_PRE_UPLOAD; ?></div>
            <div class="innerContent">
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_HTTP_SERVER; ?></div>
                <div class="labelFloat2"><?php echo HTTP_SERVER; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_HTTPS_SERVER; ?></div>
                <div class="labelFloat2"><?php echo HTTPS_SERVER; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_DIR_WS_HTTP_CATALOG; ?></div>
                <div class="labelFloat2"><?php echo DIR_WS_HTTP_CATALOG; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_DIR_WS_HTTPS_CATALOG; ?></div>
                <div class="labelFloat2"><?php echo DIR_WS_HTTPS_CATALOG; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_HTTP_COOKIE_DOMAIN; ?></div>
                <div class="labelFloat2"><?php echo HTTP_COOKIE_DOMAIN; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_HTTPS_COOKIE_DOMAIN; ?></div>
                <div class="labelFloat2"><?php echo HTTPS_COOKIE_DOMAIN; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_HTTP_COOKIE_PATH; ?></div>
                <div class="labelFloat2"><?php echo TEXT_INFO_HTTPS_COOKIE_PATH; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_HTTPS_COOKIE_PATH; ?></div>
                <div class="labelFloat2"><?php echo HTTPS_COOKIE_PATH; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_DIR_FS_CATALOG; ?></div>
                <div class="labelFloat2"><?php echo DIR_FS_CATALOG; ?></div>
              </div>
            </div>
            <div class="innerContent">
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_DB_SERVER; ?></div>
                <div class="labelFloat2"><?php echo DB_SERVER; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_DB_SERVER_USERNAME; ?></div>
                <div class="labelFloat2"><?php echo DB_SERVER_USERNAME; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_DB_SERVER_PASSWORD; ?></div>
                <div class="labelFloat2"><?php echo DB_SERVER_PASSWORD; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_DB_DATABASE; ?></div>
                <div class="labelFloat2"><?php echo DB_DATABASE; ?></div>
              </div>
            </div>

            <div class="innerContent">
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_OS_TYPE; ?></div>
                <div class="labelFloat2"><?php echo (INSTALL_OS_TYPE==0)?TEXT_INFO_OS_UNIX:TEXT_INFO_OS_OTHER; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_SITE_NAME; ?></div>
                <div class="labelFloat2"><?php echo INSTALL_SITE_NAME; ?></div>
              </div>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_EMAIL_ADDRESS; ?></div>
                <div class="labelFloat2"><?php echo INSTALL_EMAIL_ADDRESS; ?></div>
              </div>
<?php
    $mailserver = INSTALL_HELPDESK_MAILSERVER;
    if( !empty($mailserver) ) {
?>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_HELPDESK_MAILSERVER; ?></div>
                <div class="labelFloat2"><?php echo INSTALL_HELPDESK_MAILSERVER; ?></div>
              </div>
<?php
    }
?>
              <div class="innerFloat" style="width: 48%">
                <div class="labelFloat"><?php echo TEXT_INFO_SEO_URLS; ?></div>
                <div class="labelFloat2"><?php echo (INSTALL_SEO_URLS==1)?TEXT_INFO_YES:TEXT_INFO_NO; ?></div>
              </div>

            </div>

          </fieldset>
          <div class="spacer"></div>
          <div class="formButtons">
            <form class="floatForm" name="install_form" action="<?php echo $_SERVER['SCRIPT_NAME'] . '?action=server_detect'?>" method="post"><input type="submit" title="<?php echo BUTTON_INFO_CANCEL; ?>" name="cancel" value="<?php echo BUTTON_CANCEL; ?>" /></form>
            <form class="floatForm" name="install_form" action="<?php echo $_SERVER['SCRIPT_NAME'] . '?action=database_upload'?>" method="post"><input type="submit" title="<?php echo BUTTON_INFO_FINAL_SETUP; ?>" name="submit_database" value="<?php echo BUTTON_CONFIRM_CONFIG; ?>" /></form>
          </div>
        </form></div>
<?php
  } elseif( $action == 'server_detect' || $action == 'server_process') {
?>
        <div style="clear: both"><form name="install_form" action="<?php echo $_SERVER['SCRIPT_NAME'] . '?action=server_process'?>" method="post">
          <fieldset>
            <legend><?php echo TEXT_LEGEND_SERVER_INFO; ?></legend>
            <div class="splitColumn"><?php echo TEXT_CONTENT_SERVER_SETUP; ?></div>
            <div class="innerContent">
              <div class="innerFloat">
                <div class="form_label"><?php echo TEXT_INFO_HTTP_SERVER; ?></div>
                <div class="form_input"><input type="text" name="HTTP_SERVER" value="<?php echo $HTTP_SERVER; ?>" class="txtInput" /><?php echo $HTTP_SERVER_INFO; ?></div>
                <div class="form_label"><?php echo TEXT_INFO_HTTPS_SERVER; ?></div>
                <div class="form_input"><input type="text" name="HTTPS_SERVER" value="<?php echo $HTTP_SERVER; ?>" class="txtInput" /><?php echo $HTTPS_SERVER_INFO; ?></div>
                <div class="form_label"><?php echo TEXT_INFO_DIR_WS_HTTP_CATALOG; ?></div>
                <div class="form_input"><input type="text" name="HTTP_CATALOG_PATH"  value="<?php echo $HTTP_CATALOG_PATH; ?>" class="txtInput" /><?php echo $HTTP_CATALOG_PATH_INFO; ?></div>
                <div class="form_label"><?php echo TEXT_INFO_DIR_WS_HTTPS_CATALOG; ?></div>
                <div class="form_input"><input type="text" name="HTTPS_CATALOG_PATH" value="<?php echo $HTTPS_CATALOG_PATH; ?>" class="txtInput" /><?php echo $HTTPS_CATALOG_PATH_INFO; ?></div>
              </div>
              <div class="innerFloat">
                <div class="form_label"><?php echo TEXT_INFO_HTTP_COOKIE_DOMAIN; ?></div>
                <div class="form_input"><input type="text" name="HTTP_COOKIE_DOMAIN" value="<?php echo $HTTP_COOKIE_DOMAIN; ?>" class="txtInput" /><?php echo $HTTP_COOKIE_DOMAIN_INFO; ?></div>
                <div class="form_label"><?php echo TEXT_INFO_HTTPS_COOKIE_DOMAIN; ?></div>
                <div class="form_input"><input type="text" name="HTTPS_COOKIE_DOMAIN" value="<?php echo $HTTPS_COOKIE_DOMAIN; ?>" class="txtInput" /><?php echo $HTTPS_COOKIE_DOMAIN_INFO; ?></div>
                <div class="form_label"><?php echo TEXT_INFO_HTTP_COOKIE_PATH; ?></div>
                <div class="form_input"><input type="text" name="HTTP_COOKIE_PATH"  value="<?php echo $HTTP_COOKIE_PATH; ?>" class="txtInput" /><?php echo $HTTP_COOKIE_PATH_INFO; ?></div>
                <div class="form_label"><?php echo TEXT_INFO_HTTPS_COOKIE_PATH; ?></div>
                <div class="form_input"><input type="text" name="HTTPS_COOKIE_PATH" value="<?php echo $HTTPS_COOKIE_PATH; ?>" class="txtInput" /><?php echo $HTTPS_COOKIE_PATH_INFO; ?></div>
              </div>
              <div class="innerFloat">
                <div class="form_label"><?php echo TEXT_INFO_DIR_FS_CATALOG; ?></div>
                <div class="form_input"><input type="text" name="DIR_FS_CATALOG" value="<?php echo $DIR_FS_CATALOG; ?>" class="txtInput" /><?php echo $DIR_FS_CATALOG_INFO; ?></div>
              </div>
            </div>
          </fieldset>
          <div class="formButtons" style="text-align: right;">
            <div style="text-align: left"><input type="submit" title="<?php echo BUTTON_INFO_SERVER_SETUP; ?>" name="submit_server" value="<?php echo BUTTON_SERVER_SETUP; ?>" /></div>
          </div>
        </form></div>
<?php
  } else {
?>
        <div style="clear: both"><form name="install_form" action="<?php echo $_SERVER['SCRIPT_NAME'] . '?action=license_agreement'?>" method="post">
          <fieldset>
            <legend><?php echo TEXT_LEGEND_LICENSE; ?></legend>
            <div class="scrollcontent"><?php echo $contents; ?></div>
            <div class="spacer"></div>
            <div class="messageStackWarning"><input type="checkbox" name="license" />&nbsp;<?php echo TEXT_INFO_LICENSE_AGREE; ?></div>
          </fieldset>
          <div class="formButtons" style="text-align: right;">
            <div style="text-align: left"><input type="submit" title="<?php echo BUTTON_INFO_SERVER_SETUP; ?>" name="submit_server" value="<?php echo BUTTON_BEGIN; ?>" /></div>
          </div>
        </form></div>
<?php
  }
?>
      </div>
      <div id="footer"><?php echo TEXT_CONTENT_FOOTER; ?></div>
  </div>
</body> 
</html>
