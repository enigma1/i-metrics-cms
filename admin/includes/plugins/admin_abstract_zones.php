<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Collections System script
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
//
*/
  class admin_abstract_zones extends system_base {

    // Compatibility constructor
    function admin_abstract_zones() {
      $cols = 4;
      $this->columns_array = range(0, $cols-1);
    }

    function html_start() {
      extract(tep_load('defs'));

      $cDefs->media[] = '<link rel="stylesheet" type="text/css" href="includes/javascript/dragtable/dragtable.css" />';
      $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/dragtable/dragtable.js"></script>';
      tep_set_lightbox();
      return true;
    }

    function html_end() {
      extract(tep_load('defs'));

      $script_name = tep_get_script_name();

      ob_start();
      require(PLUGINS_ADMIN_PREFIX . $script_name . '.tpl');
      $contents = ob_get_contents();
      ob_end_clean();
      $cDefs->media[] = $contents;

      $contents = '';
      $launcher = DIR_FS_PLUGINS . 'common_help.tpl';
      $result = tep_read_contents($launcher, $contents);
      if( !$result ) return false;

      $contents_array = array(
        'POPUP_TITLE' => HEADING_HELP_TITLE,
        'POPUP_SELECTOR' => 'div.help_page  a.heading_help',
      );
      $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);

      return true;
    }

    function get_search_collections() {
      extract(tep_load('defs', 'database', 'sessions'));

      $cAbstract = new abstract_zones;
      $classes_array = $cAbstract->get_classes();
      foreach( $classes_array as $key => $value ) {
         $cObject = new $value;
      }

      $keywords = (isset($_GET['search']) && !empty($_GET['search']))?$db->prepare_input($_GET['search']):'';

      $text_query_raw = "select gtext_id, gtext_title from " . TABLE_GTEXT . " where (gtext_title like '%" . $db->input($keywords) . "%' or gtext_alt_title like '%" . $db->input($keywords) . "%') order by gtext_title limit 10";
      $text_array = $db->query_to_array($text_query_raw);
      $j=count($text_array);

      if($j) {
        echo '<div><table class="tabledata">' . "\n";

        for($i=0; $i<$j; $i++) {
          echo '<tr>' . "\n";
          echo '  <td><a href="' . tep_href_link(FILENAME_GENERIC_TEXT, 'gtID=' . $text_array[$i]['gtext_id'] . '&action=new_generic_text') . '">' . $text_array[$i]['gtext_title'] . '</a></td>' . "\n";
          echo '  <td><a href="' . tep_href_link($cDefs->script, 'cID=' . $text_array[$i]['abstract_zone_id'] . '&action=list') . '">' . $text_array[$i]['abstract_zone_name'] . '</a></td>' . "\n";
          echo '<tr>' . "\n";
        }
        echo '</table></div>' . "\n";
      } else {
        echo 'Nothing Found';
      }

      $cSessions->close();
      return true;
    }

    function get_columns() {
      extract(tep_load('sessions'));

      $columns =& $cSessions->register('abstract_th', $this->columns_array);

      if( empty($columns) ) $columns = $this->columns_array;
      $output = tep_params_to_string($columns);
      echo $output;
      $cSessions->close();
    }

    function get_set_columns() {
      extract(tep_load('sessions'));

      if( empty($_POST) || !isset($_POST['columns']) || !is_array($_POST['columns']) ) return false;

      $columns =& $cSessions->register('abstract_th', $this->columns_array);
      foreach($columns as $key => $value ) {
        if( isset($_POST['columns'][$key]) ) {
          $columns[$key] = (int)$_POST['columns'][$key];
        }
      }
      $cSessions->close();
    }

  }
?>
