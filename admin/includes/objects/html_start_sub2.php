<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Common html header-lower section
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
  class html_start_sub2 {
    function html_start_sub2() {
      extract(tep_load('defs', 'database', 'languages', 'sessions', 'plugins_admin', 'message_stack'));
      $selected_box =& $cSessions->register('selected_box');

      $html = '';
      if( $cDefs->ajax ) {
        $html .= '  <div id="ajax">' . "\n";
        $html .= '    <div id="wrapper">' . "\n";
        echo $html;
        return;
      }
      tep_output_media();
      $html .= '</head>' . "\n";
      $html .= '<body>' . "\n";
      $html .= '  <div id="wrapper">' . "\n";
      echo $html;
      if( $cDefs->script != FILENAME_DEFAULT ) {
        require(DIR_FS_INCLUDES . 'header.php');
        echo '    <div class="cleaner"></div>' . "\n";
        echo '      <div id="leftpane">' . "\n";
        require(DIR_FS_INCLUDES . 'column_left.php');
        echo '      </div>' . "\n";
        echo '      <div id="mainpane">' . "\n";
      } else {
        echo '    <div class="homepane main">' . "\n";
      }
      // Display Script specific notices
      $msg->output();
    }
  }
  new html_start_sub2();
?>