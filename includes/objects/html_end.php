<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: HTML Closing section
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
  class html_end {
    function html_end() {
      extract(tep_load('defs', 'database', 'languages', 'plugins_front', 'sessions', 'message_stack', 'breadcrumb'));

      if( $cDefs->ajax ) {
        $cPlug->invoke('ajax_end');
        echo '    </div>' . "\n";
        echo '  </div>' . "\n";
        require(DIR_FS_INCLUDES . 'application_bottom.php');
        $cSessions->close();
      }
    }

    function set_html() {
      extract(tep_load('defs', 'database', 'languages', 'plugins_front', 'sessions', 'message_stack', 'breadcrumb'));

      $html_end = array(
        DIR_FS_TEMPLATE . 'html_end.tpl'
      );
      for($i=0, $j=count($html_end); $i<$j; $i++) {
        require($html_end[$i]);
      }
      require(DIR_FS_INCLUDES . 'application_bottom.php');
    }
  }

  $obj = new html_end();
  $obj->set_html();
  unset($obj);
?>
