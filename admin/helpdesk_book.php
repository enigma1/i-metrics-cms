<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Helpdesk Address Book
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
  require('includes/application_top.php');

  switch($action) {
    case 'load_all_customer_emails_confirm':
      $customers_query_raw = "select c.customers_email as book_email, c.customers_email as book_name from " . TABLE_CUSTOMERS . " c left join " . TABLE_HELPDESK_BOOK . " hb on (c.customers_email = hb.book_email) where hb.book_email is null";
      $customers_array = $g_db->query_to_array($customers_query_raw);
      if( empty($customers_array) ) {
        $messageStack->add_session(ERROR_EXTERNAL_ADDRESS_SAME);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      $g_db->multi_insert(TABLE_HELPDESK_BOOK, $customers_array);

      $messageStack->add_session(SUCCESS_EXTERNAL_TABLE_LOADED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    case 'load_customer_emails_confirm':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || empty($_POST['mark']) ) {
        $messageStack->add_session(ERROR_EXTERNAL_ADDRESS_SAME);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      $email_ids = implode(',', array_keys($_POST['mark']) );
      $customers_query_raw = "select customers_email as book_email from " . TABLE_CUSTOMERS . " where customers_id in (" . $g_db->filter($email_ids) . ")";
      $customers_array = $g_db->query_to_array($customers_query_raw);

/*
      $book_query_raw = "select book_email from " . TABLE_HELPDESK_BOOK . " where book_email in (" . implode(',', array_keys($customers_array)) . ")";
      $book_array = $g_db->query_to_array($customers_query_raw, 'book_email');

      $customers_array = array_diff(array_keys($customers_array), array_keys($book_array));
      $customers_array = array_values($customers_array);
*/
      if( empty($customers_array) ) {
        $messageStack->add_session(ERROR_EXTERNAL_ADDRESS_SAME, 'error');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=load_customer_emails'));
      }
      $g_db->multi_insert(TABLE_HELPDESK_BOOK, $customers_array);

      $messageStack->add_session(SUCCESS_EXTERNAL_TABLE_LOADED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;

    case 'save':
      if( !isset($_GET['bID']) ) {
        tep_redirect(tep_href_link($g_script));
      }
      $book_id = (int)$_GET['bID'];
    case 'insert':
      $book_notes = $g_db->prepare_input($_POST['book_notes']);
      $book_email = $g_db->prepare_input($_POST['book_email']);
      $book_name = $g_db->prepare_input($_POST['book_name']);
      $book_phone = $g_db->prepare_input($_POST['book_phone']);
      $book_cell = $g_db->prepare_input($_POST['book_cell']);

      $book_email = str_replace('<br />', ',', $book_email);
      $book_phone = str_replace('<br />', ',', $book_phone);
      $book_cell = str_replace('<br />', ',', $book_cell);

      $sql_data_array = array(
        'book_name' => $book_name,
        'book_email' => $book_email,
        'book_phone' => $book_phone,
        'book_cell' => $book_cell,
        'book_notes' => $book_notes,
      );

      if( $action == 'insert' ) {
        $g_db->perform(TABLE_HELPDESK_BOOK, $sql_data_array);
        $book_id = $g_db->insert_id();
      } elseif( $action == 'save' ) {
        $g_db->perform(TABLE_HELPDESK_BOOK, $sql_data_array, 'update', "auto_id = '" . (int)$book_id . "'");
      }

      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'bID') . 'bID=' . $book_id));
      break;
    case 'deleteconfirm':
      $book_id = (int)$_GET['bID'];
      $g_db->query("delete from " . TABLE_HELPDESK_BOOK . " where auto_id = '" . (int)$book_id . "'");
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'bID') ));
      break;
    case 'delete':
      $book_id = (int)$_GET['bID'];
      $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_BOOK . " where auto_id = '" . (int)$book_id . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( !$check_array['count'] ) {
        $messageStack->add_session(ERROR_INVALID_ADDRESS_BOOK_ENTRY, 'error');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'bID') ));
      }
      break;
    default:
      break;
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  if( $action == 'load_all_customer_emails' ) {
?>
          <div class="maincell wider">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=import_all') . '" class="heading_help" title="' . HEADING_IMPORT_ALL_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_IMPORT_ALL_TITLE) . '</a>'; ?></div>
              <div class="floater"><h1><?php echo HEADING_IMPORT_ALL_TITLE; ?></h1></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form('customers_form', $g_script, 'action=load_all_customer_emails_confirm', 'post'); ?><fieldset><legend><?php echo HEADING_IMPORT_ALL; ?></legend>
              <div><?php echo TEXT_INFO_IMPORT_ALL_CUSTOMERS_DETAILS; ?></div>
              <div class="formButtons tmargin">
<?php
    $buttons = array(
      tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=import') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
    );
    echo implode('', $buttons);
?>
              </div>
            </fieldset></form></div>
          </div>
<?php
  } elseif( $action == 'import' ) {
?>
          <div class="maincell wider">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=import') . '" class="heading_help" title="' . HEADING_IMPORT_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_IMPORT_TITLE) . '</a>'; ?></div>
              <div class="floater"><h1><?php echo HEADING_IMPORT_TITLE; ?></h1></div>
            </div>
            <div class="comboHeading">
              <div class="dataTableRowAlt5 spacer floater calign"><?php echo '<a class="blockbox" href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=load_all_customer_emails') . '">' . TEXT_INFO_IMPORT_ALL_CUSTOMERS . '</a>'; ?></div>
              <div class="spacer"><?php echo TEXT_INFO_IMPORT_ALL_CUSTOMERS_HELP; ?></div>
            </div>
            <div class="formArea"><?php echo tep_draw_form('customers_form', $g_script, 'action=load_customer_emails_confirm', 'post'); ?><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th class="calign"><?php echo '<a href="#mark" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
                <th><?php echo TABLE_HEADING_EMAIL; ?></th>
              </tr>
<?php
    $customers_query_raw = "select customers_id, customers_email from " . TABLE_CUSTOMERS;
    $customers_split = new splitPageResults($customers_query_raw);
    $customers_query = $g_db->query($customers_split->sql_query);
    $rows = 0;
    while( $customers_array = $g_db->fetch_array($customers_query)) {
      $rows++;

      $check_query = $g_db->query("select count(*) as total from " . TABLE_HELPDESK_BOOK . " where book_email = '" . $g_db->filter($customers_array['customers_email']) . "'");
      $check_array = $g_db->fetch_array($check_query);

      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
      if($check_array['total']) {
        $row_class = 'dataTableRowGreen';
      }
      echo '              <tr class="' . $row_class . '">' . "\n";
?>
                <td class="calign"><?php echo ($check_array['total']?TEXT_INCLUDED:tep_draw_checkbox_field('mark['.$customers_array['customers_id'].']', 1)); ?></td>
                <td><?php echo $customers_array['customers_email']; ?></td>
              </tr>
<?php
    }
    $buttons = array(
      tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
    );
?>
            </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
            <div class="listArea splitLine">
              <div class="floater"><?php echo $customers_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
              <div class="floatend"><?php echo $customers_split->display_links(tep_get_all_get_params('page')); ?></div>
            </div>
          </div>
<?php
  } else {
?>
          <div class="maincell">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
              <div class="floater"><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
            <div class="formArea"><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_NAME; ?></th>
                <th><?php echo TABLE_HEADING_EMAIL; ?></th>
                <th><?php echo TABLE_HEADING_PHONE; ?></th>
                <th><?php echo TABLE_HEADING_CELL; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
<?php
    $book_query_raw = "select auto_id, book_email, book_name, book_phone, book_cell from " . TABLE_HELPDESK_BOOK . " order by book_name";
    $book_split = new splitPageResults($book_query_raw);
    $book_query = $g_db->query($book_split->sql_query);
    $rows = 0;
    while ($addresses = $g_db->fetch_array($book_query)) {
      $rows++;
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

      if((!isset($_GET['bID']) || $_GET['bID'] == $addresses['auto_id']) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
        $bInfo = new objectInfo($addresses);
      }

      if( (isset($bInfo) && is_object($bInfo)) && ($addresses['auto_id'] == $bInfo->auto_id) ) {
        echo '                  <tr class="dataTableRowSelected row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'bID') . 'bID=' . $bInfo->auto_id . '&action=edit') . '">' . "\n";
      } else {
        echo '                  <tr class="' . $row_class . ' row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'bID') . 'bID=' . $addresses['auto_id']) . '">' . "\n";
      }

      $book_name_string = $addresses['book_name'];
      $book_email_string = str_replace(',', '<br />', $addresses['book_email']);
      $book_phone_string = str_replace(',', '<br />', $addresses['book_phone']);
      $book_cell_string = str_replace(',', '<br />', $addresses['book_cell']);
?>
                <td><?php echo $book_name_string; ?></td>
                <td><?php echo $book_email_string; ?></td>
                <td><?php echo $book_phone_string; ?></td>
                <td><?php echo $book_cell_string; ?></td>
                <td class="tinysep calign">
<?php
      echo '<a href="' . tep_href_link($g_script, 'bID=' . $addresses['auto_id'] . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $addresses['book_name']) . '</a>';
      echo '<a href="' . tep_href_link($g_script, 'bID=' . $addresses['auto_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $addresses['book_name']) . '</a>';

      if(isset($bInfo) && is_object($bInfo) && ($addresses['auto_id'] == $bInfo->auto_id) ) { 
        echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', TEXT_SELECTED); 
      } else { 
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'bID') . 'bID=' . $addresses['auto_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>';
      } 
?>
                </td>
              </tr>
<?php
    }
?>
<?php
    $buttons = array();
    if( empty($action) ) {
      $buttons = array(
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>',
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=import') . '">' . tep_image_button('button_import.gif', IMAGE_IMPORT) . '</a>',
      );
    }
?>

            </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></div>
            <div class="listArea splitLine">
              <div class="floater"><?php echo $book_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
              <div class="floatend"><?php echo $book_split->display_links(tep_get_all_get_params('action', 'page') ); ?></div>
            </div>
          </div>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'new':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_ADDRESS_BOOK . '</b>');

        $contents[] = array('form' => tep_draw_form('address', $g_script, tep_get_all_get_params('action') . 'action=insert'));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'new_entry.png', IMAGE_NEW) );
        $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
        $contents[] = array('class' => 'rpad', 'section' => '<div>');
        $contents[] = array('text' => TEXT_INFO_BOOK_NAME . '<br />' . tep_draw_input_field('book_name'));
        $contents[] = array('text' => TEXT_INFO_BOOK_EMAIL . '<br />' . tep_draw_input_field('book_email'));
        $contents[] = array('text' => TEXT_INFO_BOOK_PHONE . '<br />' . tep_draw_input_field('book_phone'));
        $contents[] = array('text' => TEXT_INFO_BOOK_CELL . '<br />' . tep_draw_input_field('book_cell'));
        $contents[] = array('text' => TEXT_INFO_BOOK_NOTES . '<br />' . tep_draw_textarea_field('book_notes', '', '', 6, 'id="book_notes"'));
        $contents[] = array('section' => '</div>');

        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          tep_image_submit('button_insert.gif', IMAGE_INSERT),
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons),
        );
        break;
      case 'edit':
        $book_query = $g_db->query("select book_notes from " . TABLE_HELPDESK_BOOK . " where auto_id = '" . (int)$bInfo->auto_id . "'");
        $book_array = $g_db->fetch_array($book_query);

        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ADDRESS_BOOK . '</b>');

        $contents[] = array('form' => tep_draw_form('address', $g_script, tep_get_all_get_params('action', 'bID') . 'bID=' . $bInfo->auto_id . '&action=save'));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );

        $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
        $contents[] = array('class' => 'rpad', 'section' => '<div>');
        $contents[] = array('text' => TEXT_INFO_BOOK_NAME . '<br />' . tep_draw_input_field('book_name', $bInfo->book_name ));
        $contents[] = array('text' => TEXT_INFO_BOOK_EMAIL . '<br />' . tep_draw_textarea_field('book_email', str_replace(',', '<br />', $bInfo->book_email), '', 3, 'id="book_email"'));
        $contents[] = array('text' => TEXT_INFO_BOOK_PHONE . '<br />' . tep_draw_textarea_field('book_phone', str_replace(',', '<br />', $bInfo->book_phone), '', 3, 'id="book_phone"'));
        $contents[] = array('text' => TEXT_INFO_BOOK_CELL . '<br />' . tep_draw_textarea_field('book_cell', str_replace(',', '<br />', $bInfo->book_cell), '', 3, 'id="book_cell"'));
        $contents[] = array('text' => TEXT_INFO_BOOK_NOTES . '<br />' . tep_draw_textarea_field('book_notes', $book_array['book_notes'], '', 6, 'id="book_notes"'));
        $contents[] = array('section' => '</div>');

        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'bID') . 'bID=' . $bInfo->auto_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          tep_image_submit('button_update.gif', IMAGE_UPDATE),
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons),
        );
        break;

      case 'delete':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ADDRESS_BOOK . '</b>');
        $contents[] = array('form' => tep_draw_form('address', $g_script, tep_get_all_get_params('action', 'bID') . 'bID=' . $bInfo->auto_id  . '&action=deleteconfirm'));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
        $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
        $contents[] = array('text' => '<b>' . $bInfo->book_name . '</b>');
        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'bID') . 'bID=' . $bInfo->auto_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons),
        );
        break;
      default:
        if(isset($bInfo) && is_object($bInfo)) {
          $book_query = $g_db->query("select book_notes from " . TABLE_HELPDESK_BOOK . " where auto_id = '" . (int)$bInfo->auto_id . "'");
          $book_array = $g_db->fetch_array($book_query);

          $heading[] = array('text' => '<b>' . $bInfo->book_name . '</b>');

          $buttons = array(
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'bID') . 'bID=' . $bInfo->auto_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>',
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'bID') . 'bID=' . $bInfo->auto_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>',
          );
          $contents[] = array(
            'class' => 'calign', 
            'text' => implode('', $buttons),
          );
          $contents[] = array('text' => TEXT_INFO_BOOK_NAME . '<br />' . $bInfo->book_name);
          $contents[] = array('text' => TEXT_INFO_BOOK_EMAIL . '<br />' . str_replace(',', '<br />', $bInfo->book_email) );
          $contents[] = array('text' => TEXT_INFO_BOOK_PHONE . '<br />' . str_replace(',', '<br />', $bInfo->book_phone) );
          $contents[] = array('text' => TEXT_INFO_BOOK_CELL . '<br />' . str_replace(',', '<br />', $bInfo->book_cell) );
          $contents[] = array('text' => TEXT_INFO_BOOK_NOTES . '<br />' . $book_array['book_notes']);
        } else { // create generic_text dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('class' => 'calign', 'text' => '<a href="' . tep_href_link($g_script, 'action=new') . '">' . tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW) . '</a>');
          $contents[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
    }

    if( !empty($heading) && !empty($contents) ) {
      echo '             <div class="rightcell">';
      $box = new box;
      echo $box->infoBox($heading, $contents);
      echo '             </div>' . "\n";
    }
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
