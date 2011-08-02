<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
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
  class admin_generic_text extends system_base {
    var $columns_array;
    // Compatibility constructor
    function admin_generic_text() {
      $cols = 6;
      $this->columns_array = range(0, $cols-1);
    }

    function html_start() {
      extract(tep_load('defs'));
      $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/zones_control.js"></script>';

      if( $cDefs->action == 'new_generic_text' || $cDefs->action == 'update_generic_text') {
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/tiny_mce.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/config.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/image_control.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/templates.js"></script>';
      } else {
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
        'POPUP_SELECTOR' => 'div.help_page a.heading_help',
        'POPUP_TITLE' => '',
      );
      $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);

      return true;
    }

    function get_search() {
      extract(tep_load('defs', 'database', 'sessions'));

      $keywords = (isset($_GET['search']) && !empty($_GET['search']))?$db->prepare_input($_GET['search']):'';

      $text_query_raw = "select gtext_id, gtext_title from " . TABLE_GTEXT . " where (gtext_title like '%" . $db->input($keywords) . "%' or gtext_description like '%" . $db->input($keywords) . "%') order by gtext_title limit 10";
      $text_array = $db->query_to_array($text_query_raw);
      $j=count($text_array);

      for($i=0; $i<$j; $i++) {
        echo '<div><a href="' . tep_href_link($cDefs->script, 'gtID=' . $text_array[$i]['gtext_id'] . '&action=new_generic_text') . '">' . $text_array[$i]['gtext_title'] . '</a></div>';
      }

      if( !$j ) {
        echo 'Nothing Found';
      }

      $cSessions->close();
      return true;
    }

    function get_columns() {
      extract(tep_load('sessions'));

      $columns =& $cSessions->register('gtext_th', $this->columns_array);
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

      $columns =& $cSessions->register('gtext_th', $this->columns_array);
      foreach($columns as $key => $value ) {
        if( isset($_POST['columns'][$key]) ) {
          $columns[$key] = (int)$_POST['columns'][$key];
        }
      }
      $cSessions->close();
    }

    function get_template() {
      extract(tep_load('defs', 'database', 'sessions'));

      $template_id = (isset($_GET['template_id']) && !empty($_GET['template_id']))?(int)$_GET['template_id']:'';
      if( empty($template_id) ) return false;

      $template_query = $db->query("select template_content from " . TABLE_TEMPLATES . " where group_id = " . TEMPLATE_CONTENT_GROUP . " and template_id='" . (int)$template_id . "'");
      if( !$db->num_rows($template_query) ) return false;

      $template_array = $db->fetch_array($template_query);
      echo $template_array['template_content'];
      $cSessions->close();
      return true;
    }
  }
?>
