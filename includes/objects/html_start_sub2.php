<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: HTML Lower Section
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
      extract(tep_load('defs', 'database', 'languages', 'sessions', 'plugins_front', 'message_stack', 'breadcrumb'));

      $html = '';
      if( $cDefs->ajax ) {
        $html .= '  <div id="ajax">' . "\n";
        $html .= '    <div id="wrapper">' . "\n";
        echo $html;
        return;
      }
    }

    function set_html() {
      extract(tep_load('defs', 'database', 'languages', 'sessions', 'plugins_front', 'message_stack', 'breadcrumb'));

      $html_start_sub2 = array(
        DIR_FS_TEMPLATE . 'html_start_sub2.tpl'
      );
      $cPlug->invoke('html_start_sub2');
      for($i=0, $j=count($html_start_sub2); $i<$j; $i++) {
        require($html_start_sub2[$i]);
      }

    }
  }

  $obj = new html_start_sub2();
  $obj->set_html();
  unset($obj);
?>