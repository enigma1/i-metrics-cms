<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Install: Support Functions
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
  function pre_configure_site() {
    global $g_db;

    $os = INSTALL_OS_TYPE;
    $transport = ($os == 1)?'smtp':'sendmail';
    $linefeed = ($os == 1)?'CRLF':'LF';

    $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $g_db->input($transport) . "' where configuration_key = 'EMAIL_TRANSPORT'");
    $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $g_db->input($linefeed) . "' where configuration_key = 'EMAIL_LINEFEED'");
    $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . INSTALL_HELPDESK_MAILSERVER . "' where configuration_key = 'DEFAULT_HELPDESK_MAILSERVER'");
    $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . INSTALL_EMAIL_ADDRESS . "' where configuration_key = 'EMAIL_FROM' || configuration_key = 'STORE_OWNER_EMAIL_ADDRESS'");
    $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . INSTALL_SITE_NAME . "' where configuration_key = 'STORE_NAME' || configuration_key = 'STORE_OWNER'");

    $helpdesk = INSTALL_HELPDESK_MAILSERVER;
    $password = INSTALL_EMAIL_PASSWORD;
    $seo = INSTALL_SEO_URLS == 1?'true':'false';

    $g_db->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $g_db->input($seo) . "' where configuration_key = 'SEO_DEFAULT_ENABLE'");

    if( !empty($helpdesk) && !empty($password) ) {
      $sql_data_array = array(
        'title' => 'Help',
        'email_address' => INSTALL_EMAIL_ADDRESS,
        'name' => INSTALL_EMAIL_ADDRESS,
        'front' => 1,
        'receive' => 1,
        'password' => INSTALL_EMAIL_PASSWORD,
      );
      $g_db->perform(TABLE_HELPDESK_DEPARTMENTS, $sql_data_array);
    }

    $relpath = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
    $tmp_array = explode('://',$relpath);

    $index = create_safe_string($tmp_array[1], '_', "/[^0-9a-z\-_]+/i");
    $plugins_data = array( $index => array(
      'status_id' => 1,
      'sort_id' => 1,
      'template' => INSTALL_TEMPLATE,
    ));

    switch(INSTALL_TEMPLATE) {
      case 'stock':
      case '3col':
      case 'ebooks':
        $store_data = serialize($plugins_data);
        erase_dir(DIR_FS_CATALOG . DIR_WS_PLUGINS . 'css_menu');
        copy_dir('install/plugins/css_menu/', DIR_FS_CATALOG . DIR_WS_PLUGINS . 'css_menu');
        $g_db->query("update " . TABLE_PLUGINS . " set plugins_data = '" . $g_db->input($store_data) . "' where plugins_key = 'css_menu'");

        $tmp_data = $plugins_data;
        $tmp_data[$index]['front_scripts'] = array(
          'index.php' => 'div a.pg_link',
          'generic_pages.php' => 'div.desc a.pg_link'
        );
        $tmp_data[$index]['front_all'] = true;
        $tmp_data[$index]['front_common_selector'] = 'div.imagelink a';
        $tmp_data[$index]['back_scripts'] = array();
        $tmp_data[$index]['back_all'] = false;
        $tmp_data[$index]['back_common_selector'] = 'div#help_image_group a';
        $store_data = serialize($tmp_data);
        $g_db->query("update " . TABLE_PLUGINS . " set plugins_data = '" . $g_db->input($store_data) . "' where plugins_key = 'popup_image'");
        break;
      default:
        $g_db->query("delete from " . TABLE_PLUGINS . " where plugins_key = 'css_menu'");

        $tmp_data = $plugins_data;
        $tmp_data[$index]['front_scripts'] = array(
          'index.php' => 'div a.pg_link',
          'generic_pages.php' => 'div.desc a.pg_link'
        );
        $tmp_data[$index]['front_all'] = true;
        $tmp_data[$index]['front_common_selector'] = 'div.imagelink a';
        $tmp_data[$index]['back_scripts'] = array();
        $tmp_data[$index]['back_all'] = false;
        $tmp_data[$index]['back_common_selector'] = 'div#help_image_group a';
        $store_data = serialize($tmp_data);
        $g_db->query("update " . TABLE_PLUGINS . " set plugins_data = '" . $g_db->input($store_data) . "' where plugins_key = 'popup_image'");
        break;
    }
  }

  function redirect($url) {
    // No encoded ampersands for redirect
    $url = str_replace('&amp;', '&', $url);
    header('P3P: CP="NOI ADM DEV PSAi COM NAV STP IND"');
    header('Location: ' . $url);
    exit();
  }

  function prepare_input($string, $sanitize = true) {
    if (is_string($string)) {
      if( $sanitize ) {
        return trim(sanitize_string(stripslashes($string)));
      } else {
        return trim(stripslashes($string));
      }
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }

  function sanitize_string($string, $sep='_') {
    $patterns = array ('/ +/','/[<>]/');
    $replace = array (' ', $sep);
    return preg_replace($patterns, $replace, trim($string));
  }

  function create_safe_string($string, $separator=' ', $filter="/[^0-9a-z]+/i") {
    $string = preg_replace('/\s\s+/', ' ', trim($string));
    $string = preg_replace($filter, $separator, $string);
    if( !empty($separator) ) {
      $string = trim($string, $separator);
      $string = str_replace($separator . $separator . $separator, $separator, $string);
      $string = str_replace($separator . $separator, $separator, $string);
    }
    return $string;
  }

  function read_contents($filename, &$contents) {
    $result = false;
    $contents = '';

    $fp = @fopen($filename, 'r');
    if( $fp ) {
      $contents = fread($fp, filesize($filename));
      fclose($fp);
      $result = true;
    }
    return $result;
  }

  function write_contents($filename, $contents) {
    $result = false;

    $fp = @fopen($filename, 'w');
    if( $fp ) {
      $size = fwrite($fp, $contents);
      fclose($fp);
      $result = ($size > 0);
    }
    return $result;
  }

  function parse_mysql_dump($filename, $display=true) {
    global $g_db;

    $file_content = file($filename);

    $query = "";
    foreach($file_content as $sql_line) {
      $line = trim($sql_line);
      if( !empty($line) && (substr($line, 0, 2) != "--") && (substr($line, 0, 1) != "#")) {
        $query .= $sql_line;
        if(preg_match("/;\s*$/", $sql_line)) {
          if( $display ) {
            echo '<b style="color: #CC0000; font-weight: bold">DB-Exec: </b><b>' . htmlspecialchars($query) . '</b><br />' . "\n";
          }
          $g_db->query($query);
          $query = "";
        }
      }
    }
  }

  function erase_dir($path) {
    if( !file_exists($path) ) return;
    closedir(opendir($path));
    $sub_array = glob($path.'*');
    if( empty($sub_array) ) return;
    foreach($sub_array as $sub ) {
      if( is_file($sub) ) {
        @unlink($sub);
      } else {
        erase_dir($sub.'/');
        @rmdir($sub);
      }
    }
  }

  function copy_dir($src, $dst) {

    $src = rtrim($src, '/');
    $dst = rtrim($dst, '/');
    if( empty($src) || empty($dst) || !is_dir($src) ) return;

    if( !is_dir($dst) ) {
      @mkdir($dst);
    }
    $sub_array = glob($src.'/*');
    if( empty($sub_array) ) {
      return;
    }

    foreach($sub_array as $sub ) {
      $entry = basename($sub);

      if( is_file($sub) ) {
        $contents = '';
        if( !read_contents($src.'/'.$entry, $contents) ) {
          continue;
        }
        if( !write_contents($dst . '/' . $entry, $contents) ) {
          continue;
        }
      } else {
        copy_dir($src.'/'.$entry, $dst.'/'.$entry);
      }
    }
    closedir(opendir($src));
    closedir(opendir($dst));
  }

  function remove_directory($dir) {
    if(basename($dir) !== 'install') return;
    closedir(opendir($dir));
    $files = glob('*', GLOB_MARK );
    foreach( $files as $file ){
      @unlink( $file );
    }
    @rmdir( $dir );
  }
?>