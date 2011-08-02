<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Generic Text script
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
  class admin_templates extends system_base {
    var $columns_array;
    // Compatibility constructor
    function admin_templates() {
      $cols = 5;
      $this->columns_array = range(0, $cols-1);
    }

    function init_late() {
      // Filter script parameters
      $this->set_get_array('action', 'gID', 'tID', 'page', 'wp', 's_sort_id', 'search');
    }

    function html_start() {
      extract(tep_load('defs'));

      if( $cDefs->action == 'new_template' || $cDefs->action == 'template_upload') {
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/tiny_mce.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/config.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/image_control.js"></script>';
      } elseif( empty($cDefs->action) || $cDefs->action == 'search' ) {
        $cDefs->media[] = '<link rel="stylesheet" type="text/css" href="includes/javascript/livesearch/livesearch.css" />';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/livesearch/livesearch.js"></script>';
        $cDefs->media[] = '<link rel="stylesheet" type="text/css" href="includes/javascript/dragtable/dragtable.css" />';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/dragtable/dragtable.js"></script>';
      }
      tep_set_lightbox();
      return true;
    }

    function html_end() {
      extract(tep_load('defs', 'sessions'));
      $gID = isset($cDefs->link_params['gID'])?(int)$cDefs->link_params['gID']:TEMPLATE_SYSTEM_GROUP;

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
        'POPUP_TITLE' => '',
        'POPUP_SELECTOR' => 'div.help_page a.heading_help',
      );
      $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);
      return true;
    }

    function get_search() {
      extract(tep_load('defs', 'database', 'sessions'));
      $keywords = $db->prepare_input($_GET['search']);

      $text_query_raw = "select template_id, template_title, template_subject from " . TABLE_TEMPLATES . " where (template_title like '%" . $db->input($keywords) . "%' or template_subject like '%" . $db->input($keywords) . "%' or template_content like '%" . $db->input($keywords) . "%') order by template_title limit 10";
      $text_array = $db->query_to_array($text_query_raw);
      $j=count($text_array);

      if( $j ) {
        echo '<div><table class="tabledata">' . "\n";
        echo '<tr class="dataTableHeadingRow">' . "\n";
        echo '<th>' . TABLE_HEADING_SUBJECT . '</th>' . "\n";
        echo '<th>' . TABLE_HEADING_TITLE . '</th>' . "\n";

        for($i=0; $i<$j; $i++) {
          echo '<tr class="dataTableRow"><td><a class="blocker" href="' . tep_href_link($cDefs->script, 'tID=' . $text_array[$i]['template_id'] . '&action=new_template') . '">' . $text_array[$i]['template_subject'] . '</a></td><td><b>' . $text_array[$i]['template_title'] . '</b>' . '</td></tr>' . "\n";
        }
        echo '</tr>' . "\n";
        echo '</table></div>' . "\n";
        echo '<div class="dataTableRowSelected linepad">' . TEXT_INFO_SEARCH_LIMIT . '</div>' . "\n";
      }

      if( !$j ) {
        echo 'Nothing Found';
      }
      $cSessions->close();
      return true;
    }

    function get_columns() {
      extract(tep_load('sessions'));

      $columns =& $cSessions->register('templates_th', $this->columns_array);

      if( count($columns) != count($this->columns_array) ) {
        $columns = $this->columns_array;
      }

      if( empty($columns) ) $columns = $this->columns_array;
      $output = tep_params_to_string($columns);
      echo $output;
      $cSessions->close();
    }

    function get_set_columns() {
      extract(tep_load('sessions'));

      if( empty($_POST) || !isset($_POST['columns']) || !is_array($_POST['columns']) ) return false;

      $columns =& $cSessions->register('templates_th', $this->columns_array);
      foreach($columns as $key => $value ) {
        if( isset($_POST['columns'][$key]) ) {
          $columns[$key] = (int)$_POST['columns'][$key];
        }
      }
      $cSessions->close();
    }

  }
?>
