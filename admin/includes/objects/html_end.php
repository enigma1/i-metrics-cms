<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// ---------------------------------------------------------------------------
// Common html Footer section
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
      extract(tep_load('defs', 'plugins_admin', 'sessions'));

      if( $cDefs->ajax ) {
        $cPlug->invoke('ajax_end');
        echo '    </div>' . "\n";
        echo '  </div>' . "\n";
        require(DIR_FS_INCLUDES . 'application_bottom.php');
        return;
      }
      if( $cDefs->script != FILENAME_DEFAULT ) {
        echo '    </div>' . "\n";
        require(DIR_FS_INCLUDES . 'footer.php');
      } else {
        echo '    </div>' . "\n";
        echo '    <div class="homepane main">' . "\n";
        require(DIR_FS_INCLUDES . 'footer.php');
        echo '    </div>' . "\n";
      }
      echo '  </div>' . "\n";
      $cPlug->invoke('html_end');

      if( $cDefs->script != FILENAME_DEFAULT ) {
        $cDefs->media[] = 
          '<div><script language="javascript" type="text/javascript">' . "\n" . 
          '  var jqWrap = general;' . "\n" . 
          '  jqWrap.launch();' . "\n" .
          '</script></div>';
      }
      tep_output_media();
      echo '</body>' . "\n";
      echo '</html>' . "\n";
      require(DIR_FS_INCLUDES . 'application_bottom.php');
    }

  }
  new html_end();
  $g_debug->get('stop_timer');
  $g_debug->get('show_timer');
?>