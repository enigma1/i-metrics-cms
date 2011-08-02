<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Helpdesk script
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
  class admin_helpdesk extends system_base {
    var $columns_array;
    // Compatibility constructor
    function admin_helpdesk() {
      $cols = 6;
      $this->columns_array = range(0, $cols-1);
    }

    function html_start() {
      extract(tep_load('defs'));
      $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/zones_control.js"></script>';
      $subaction = (isset($_GET['subaction']) ? $_GET['subaction'] : '');

        $cDefs->media[] = '<link rel="stylesheet" type="text/css" href="includes/javascript/livesearch/livesearch.css" />';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/livesearch/livesearch.js"></script>';

      if( $cDefs->action == 'view' && ($subaction == 'edit' || $subaction == 'reply' || $subaction == 'new') ) {
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/tiny_mce.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/config.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/image_control.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/templates.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/add_entry.js"></script>';
        $cDefs->media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/helpdesk.js"></script>';
      } else {
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

      $title = $this->get_system_help_title('list');
      $contents_array = array(
        'POPUP_TITLE' => $title,
        'POPUP_SELECTOR' => 'div.help_page a.heading_help',
      );
      $cDefs->media[] = tep_templates_replace_entities($contents, $contents_array);

      return true;
    }

    function get_search_book_names() {
      extract(tep_load('defs', 'database', 'sessions'));

      $keywords = (isset($_POST['to_name']) && !empty($_POST['to_name']))?$db->prepare_input($_POST['to_name']):'';

      $book_query_raw = "select book_name from " . TABLE_HELPDESK_BOOK . " where book_name like '%" . $db->input($keywords) . "%' order by book_name limit 10";
      $book_array = $db->query_to_array($book_query_raw);
      $j=count($book_array);

      for($i=0; $i<$j; $i++) {
        //echo '<div><a href="' . tep_href_link($cDefs->script, 'gtID=' . $text_array[$i]['gtext_id'] . '&action=new_generic_text') . '">' . $text_array[$i]['gtext_title'] . '</a></div>';
        echo '<div>' . $book_array[$i]['book_name'] . '</div>';
      }

      if( !$j ) {
        echo sprintf(ERROR_EMPTY_SEARCH, $keywords);
      }

      $cSessions->close();
      return true;
    }

    function get_search_book_emails() {
      extract(tep_load('defs', 'database', 'sessions'));

      $keywords = (isset($_POST['to_name']) && !empty($_POST['to_name']))?$db->prepare_input($_POST['to_name']):'';

      $book_query_raw = "select book_email from " . TABLE_HELPDESK_BOOK . " where book_email like '%" . $db->input($keywords) . "%' order by book_email limit 10";
      $book_array = $db->query_to_array($book_query_raw);
      $j=count($book_array);

      for($i=0; $i<$j; $i++) {
        //echo '<div><a href="' . tep_href_link($cDefs->script, 'gtID=' . $text_array[$i]['gtext_id'] . '&action=new_generic_text') . '">' . $text_array[$i]['gtext_title'] . '</a></div>';
        echo '<div>' . $book_array[$i]['book_email'] . '</div>';
      }

      if( !$j ) {
        echo sprintf(ERROR_EMPTY_SEARCH, $keywords);
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

      $template_query = $db->query("select template_content from " . TABLE_TEMPLATES . " where group_id = " . TEMPLATE_HELPDESK_GROUP . " and template_id='" . (int)$template_id . "'");
      if( !$db->num_rows($template_query) ) return false;

      $template_array = $db->fetch_array($template_query);
      echo $template_array['template_content'];
      $cSessions->close();
      return true;
    }

    function get_reply_from_email() {
      extract(tep_load('defs', 'database', 'sessions'));
      $dID = (isset($_GET['dID']) && !empty($_GET['dID']))?(int)$_GET['dID']:'';
      $check_query = $db->query("select title from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = '" . (int)$dID . "'");
      if( $db->num_rows($check_query) ) {
        $check_array = $db->fetch_array($check_query);
        echo $check_array['title'];
      }

      $cSessions->close();
      return true;
    }

    function get_reply_to_label() {
      extract(tep_load('defs', 'database', 'sessions'));
?>
            <script language="javascript" type="text/javascript" src="includes/javascript/reload_events.js"></script>
            <div class="listArea"><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_EMAIL; ?></th>
                <th><?php echo TABLE_HEADING_NAME; ?></th>
                <th><?php echo TABLE_HEADING_PHONE; ?></th>
                <th><?php echo TABLE_HEADING_CELL; ?></th>
              </tr>
<?php
      $book_query_raw = "select book_email, book_name, book_phone, book_cell from " . TABLE_HELPDESK_BOOK . " order by book_name";
      $book_split = new splitPageResults($book_query_raw);
      $book_query = $db->query($book_split->sql_query);
      $rows = 0;

      while( $addresses = $db->fetch_array($book_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

        $book_name_string = $addresses['book_name'];
        $book_email_string = str_replace(',', '<br />', $addresses['book_email']);
        $book_phone_string = str_replace(',', '<br />', $addresses['book_phone']);
        $book_cell_string = str_replace(',', '<br />', $addresses['book_cell']);
        echo '                  <tr class="book_data ' . $row_class . '" email="' . $addresses['book_email'] . '" name="' . $addresses['book_name'] . '">' . "\n";
?>
                <td><?php echo $book_name_string; ?></a></td>
                <td><?php echo $book_email_string; ?></td>
                <td><?php echo $book_phone_string; ?></td>
                <td><?php echo $book_cell_string; ?></td>
<?php
      }
?>
            </table></div>
            <div class="listArea splitLine">
              <div class="floater"><?php echo $book_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
              <div class="floatend"><?php echo $book_split->display_links(tep_get_all_get_params('page') ); ?></div>
            </div>
<?php
      include_once(DIR_FS_MODULES . 'reload_events.php');
      $cSessions->close();
      return true;

    }

  }
?>
