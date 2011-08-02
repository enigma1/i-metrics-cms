<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Common HTML header upper section
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

  class html_start_sub1 {

    function html_start_sub1() {
      extract(tep_load('http_headers', 'database', 'message_stack'));

      if( DEFAULT_WARNING_PASSWORD_PROTECT_REMIND == 'true' ) {
        $cfq_query = $db->query("select configuration_id, configuration_group_id from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_WARNING_PASSWORD_PROTECT_REMIND'");
        $cfg_array = $db->fetch_array($cfq_query);
        $warning_string = '<a class="headerLink" href="' . tep_href_link(FILENAME_CONFIGURATION, 'action=edit&gID=' . $cfg_array['configuration_group_id'] . '&cID=' . $cfg_array['configuration_id']) . '">' . WARNING_PASSWORD_PROTECT_REMIND . '</a>';
        $msg->add($warning_string, 'error', 'header');
      }

      // check if the 'install' directory exists, and warn of its existence
      if( DEFAULT_WARNING_INSTALL_EXISTS == 'true' ) {
        $check_dir = DIR_FS_CATALOG . 'install';
        if( file_exists($check_dir) ) {
          $install_string = sprintf(WARNING_INSTALL_DIRECTORY_EXISTS, $check_dir);
          $msg->add($install_string, 'error', 'header');
        }
      }

      if( ((bool)ini_get('file_uploads') == false) ) {
        $msg->add(WARNING_FILE_UPLOADS_DISABLED, 'warning', 'header');
      }

      $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
      if( !is_writeable($images_path) ) {
        $msg->add(WARNING_IMAGE_UPLOADS_DISABLED, 'warning', 'header');
      }

      $images_path = tep_front_physical_path(DIR_WS_CATALOG . FLY_THUMB_FOLDER);
      if( !is_writeable($images_path) ) {
        $msg->add(WARNING_IMAGE_THUMBS_DISABLED, 'warning', 'header');
      }
      $http->send_cookies();
    }

    function set_html() {
      extract(tep_load('defs', 'plugins_admin', 'sessions'));

      if( $cDefs->ajax ) return;
      if( headers_sent() ) {
        echo '<pre style="font-weight:bold; color: #FF0000;">' . ERROR_HEADERS_SENT . '</pre>';
        $cSessions->close();
      } 

      header('Content-Type: text/html; charset=' . CHARSET);
      // Setup privacy header
      header('P3P: CP="NOI ADM DEV PSAi COM NAV STP IND"');
      $media_array = array();
      $media_array[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
      $media_array[] = '<html xmlns="http://www.w3.org/1999/xhtml" ' . HTML_PARAMS . '>';
      $media_array[] = '<head>';
      $media_array[] = '<meta http-equiv="Content-Type" content="text/html; charset=' . CHARSET . '" />';
      $media_array[] = '<base href="' . $cDefs->relpath . '"></base>';
      $html = '<title>';
      if( defined('HEADING_TITLE') && strpos(HEADING_TITLE, '%s') === false ) {
        $html .= HEADING_TITLE . ' - ' . STORE_NAME; 
      } else {
        $html .= TITLE;
      }
      $html .= '</title>';
      $media_array[] = $html;
      $media_array[] = '<link rel="stylesheet" type="text/css" href="stylesheet.css" />';
      $media_array[] = '<link rel="stylesheet" type="text/css" href="includes/javascript/jquery/themes/smoothness/jquery-ui.css" />';
      $media_array[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.js"></script>';
      //$media_array[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.dump.js"></script>';
      $media_array[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.ajaxq.js"></script>';
      $media_array[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.form.js"></script>';
      $media_array[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/ui/jquery-ui.min.js"></script>';
      $media_array[] = '<script language="javascript" type="text/javascript" src="includes/javascript/general.js"></script>';

      $cDefs->media = array_merge($media_array, $cDefs->media);

      $cPlug->invoke('html_start');
      tep_output_media();
      $cPlug->invoke('html_ready');
      tep_output_media();
    }
  }

  $obj = new html_start_sub1();
  $obj->set_html();
  unset($obj);

?>