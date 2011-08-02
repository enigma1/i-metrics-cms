<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Newsletters main script
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

  $plugin = $g_plugins->get('newsletter_system');
  if( empty($plugin) ) {
    $messageStack->add_session(ERROR_PLUGIN_NOT_FOUND);
    tep_redirect();
  }

  if(isset($_POST['remove_customers_x']) || isset($_POST['remove_customers_y'])) $action='customers_remove';

  $customers_storage =& $g_session->register('customers_storage', array('customers'=> array(),'customers_count' => 0, 'email' => ''));

  $gID = 0;
  $nID = (isset($_GET['nID']) ? (int)$_GET['nID'] : '');
  $template_content = '';

  $check_query = $g_db->query("select group_id from " . TABLE_TEMPLATES_GROUPS . " where group_title = '" . $g_db->filter(PLUGIN_NEWSLETTER_TEMPLATE_GROUP) . "'");
  if( $g_db->num_rows($check_query) ) {
    $check_array = $g_db->fetch_array($check_query);
    $gID = $check_array['group_id'];
  }

  if( empty($gID) ) {
    $messageStack->add(ERROR_NEWSLETTER_REINSTALL);
    $action = '';
    $nID = 0;
    $gID = 0;
  }

  $s_sort_id = (isset($_GET['s_sort_id']) ? (int)$_GET['s_sort_id'] : 0);

  switch( $action ) {
    case 'change_wp':
      $g_wp_ifc = (isset($_GET['wp']) && $_GET['wp'] == 1)?true:false;
      $messageStack->add_session(WARNING_WP_CHANGED, 'warning');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=edit'));
      break;

    case 'reset':
     $sql_data_array = array(
       'customers_id' => 0,
       'times_sent' => 0,
       'newsletter_hits' => 0,
       'date_sent' => 'null',
     );
     $g_db->perform(TABLE_NEWSLETTERS, $sql_data_array, 'update', "template_id = '" . (int)$nID . "'");
     $messageStack->add_session(SUCCESS_NEWSLETTER_RESET, 'success');
     tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nID ));
     break;
    case 'insert':
    case 'update':
      $nID = isset($_POST['template_id'])?(int)$_POST['template_id']:0;

      if( empty($_POST['template_description']) ) {
        $messageStack->add(ERROR_NEWSLETTER_DESCRIPTION_EMPTY);
        $action = 'edit';
        break;
      }

      if( empty($_POST['template_subject']) ) {
        $messageStack->add(ERROR_NEWSLETTER_SUBJECT_EMPTY);
        $action = 'edit';
        break;
      }

      $check_query = $g_db->query("select count(*) as total from " . TABLE_NEWSLETTERS . " where template_id = '" . (int)$nID . "'");
      $check_array = $g_db->fetch_array($check_query);
      $check_array['index'] = TEXT_INFO_NEWSLETTER_TITLE . ($check_array['total']+1);

      $sql_data_array = array(
        'group_id' => (int)$gID,
        'template_title' => $check_array['index'],
        'template_subject' => $g_db->prepare_input($_POST['template_subject']),
        'template_content' => $g_db->prepare_input($_POST['template_description']),
      );

      if( empty($nID) || !$check_array['total'] ) {
        $g_db->perform(TABLE_TEMPLATES, $sql_data_array);
        $nID = $g_db->insert_id();
        $insert_data_array = array(
          'template_id' => (int)$nID
        );
        $g_db->perform(TABLE_NEWSLETTERS, $insert_data_array);
        $messageStack->add_session(sprintf(SUCCESS_NEWSLETTER_CREATED, $sql_data_array['template_subject']), 'success');
      } else {
        $g_db->perform(TABLE_TEMPLATES, $sql_data_array, 'update', "template_id = '" . (int)$nID . "' and group_id = '" . (int)$gID . "'");
        $messageStack->add_session(sprintf(SUCCESS_NEWSLETTER_UPDATED, $sql_data_array['template_subject']), 'success');
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nID));
      break;
    case 'copy_confirm':
      if( isset($_POST['template_id']) && tep_not_null($_POST['template_id']) ) {
        $nID = $g_db->prepare_input($_POST['template_id']);

        $check_query = $g_db->query("select count(*) as total from " . TABLE_NEWSLETTERS . " where template_id = '" . (int)$nID . "'");
        $check_array = $g_db->fetch_array($check_query);
        if( !$check_array['total'] ) {
          $messageStack->add_session(ERROR_NEWSLETTER_INVALID);
          tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'nID') ));
        }

        $check_array['index'] = TEXT_INFO_NEWSLETTER_TITLE . ($check_array['total']+1);

        $template_query = $g_db->query("select group_id, template_title, template_subject, template_content from " . TABLE_TEMPLATES . " where template_id = '" . (int)$nID . "' and group_id='" . (int)$gID . "'");
        $template_array = $g_db->fetch_array($template_query);

        if( isset($_POST['template_subject']) && !empty($_POST['template_subject']) ) {
          $template_array['template_subject'] = $g_db->filter($_POST['template_subject']);
        }
        $sql_data_array = array(
          'group_id' => (int)$gID,
          'template_title' => $check_array['index'],
          'template_subject' => $template_array['template_subject'],
          'template_content' => $template_array['template_content'],
        );

        $g_db->perform(TABLE_TEMPLATES, $sql_data_array);
        $nID = $g_db->insert_id();

        $insert_data_array = array(
          'template_id' => (int)$nID
        );
        $g_db->perform(TABLE_NEWSLETTERS, $insert_data_array);
        $messageStack->add_session(sprintf(SUCCESS_NEWSLETTER_CREATED, $sql_data_array['template_subject']), 'success');
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nID));
      break;

    case 'delete_confirm':
      if( isset($_POST['template_id']) && !empty($_POST['template_id']) ) {
        $nID = (int)$_POST['template_id'];
        $g_db->query("delete from " . TABLE_NEWSLETTERS . " where template_id = '" . (int)$nID . "'");
        $g_db->query("delete from " . TABLE_TEMPLATES . " where template_id = '" . (int)$nID . "' and group_id = '" . (int)$gID . "'");
        $messageStack->add_session(SUCCESS_NEWSLETTER_REMOVED, 'success');
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'nID') ));
      break;
    case 'delete_all':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(ERROR_NOTHING_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      break;
    case 'delete_all_confirm':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(ERROR_NOTHING_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      $tmp_array = array();
      foreach ($_POST['mark'] as $key => $val) {
        $tmp_array[] = (int)$key;
      }
      $g_db->query("delete from " . TABLE_NEWSLETTERS . " where template_id in ('" . implode("','", $tmp_array) . "')");
      $g_db->query("delete from " . TABLE_TEMPLATES . " where template_id in ('" . implode("','", $tmp_array) . "') and group_id='" . (int)$gID . "'");
      $messageStack->add_session(SUCCESS_NEWSLETTER_DELETED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'nID') ));
      break;
    case 'send':
      if( empty($nID) ) {
        $messageStack->add_session(ERROR_NEWSLETTER_INVALID);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'nID') ));
      }
      break;
    case 'send_confirm':
      $newsletter_query = $g_db->query("select n.customers_id, n.times_sent, t.template_subject, t.template_content from " . TABLE_NEWSLETTERS . " n left join " . TABLE_TEMPLATES . " t on (t.template_id=n.template_id) where t.template_id = '" . (int)$nID . "' and t.group_id = '" . (int)$gID . "'");
      if( !$g_db->num_rows($newsletter_query) ) {
        $messageStack->add_session(ERROR_NEWSLETTER_INVALID);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'nID') ));
      }

      if( isset($_POST['email_from']) && tep_validate_email($_POST['email_from']) ) {
        $customers_storage['email'] = $g_db->prepare_input($_POST['email_from']);
      }
      if( !isset($customers_storage['email']) || empty($customers_storage['email']) ) {
        $messageStack->add_session(ERROR_NEWSLETTER_EMAIL_FROM_INVALID);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'nID') ));
      }

      $newsletter_array = $g_db->fetch_array($newsletter_query);
      $last_customer_id = $newsletter_array['customers_id'];
      $options = $plugin->load_options();

      $search_string = $nID . '=' . $nID;
      $sent_query_raw = "select customers_id, customers_name, customers_email, newsletter from " . TABLE_CUSTOMERS . " where customers_id > '" . (int)$last_customer_id . "' and newsletter is not null and newsletter not like '% " . $search_string . "%' limit " . (int)$options['email_limit'];

      if( !empty($customers_storage['customers']) ) {
        $sent_query_raw = "select customers_id, customers_name, customers_email, newsletter from " . TABLE_CUSTOMERS . " where customers_id > '" . (int)$last_customer_id . "' and newsletter is not null and customers_id in (" . implode(',', $customers_storage['customers']) . ")";
      } elseif( $options['resent'] ) {
        $sent_query_raw = "select customers_id, customers_name, customers_email, newsletter from " . TABLE_CUSTOMERS . " where customers_id > '" . (int)$last_customer_id . "' and newsletter is not null limit " . (int)$options['email_limit'];
      }
      $check_query = $g_db->query($sent_query_raw);

      if( !$g_db->num_rows($check_query) ) {
        if( !$last_customer_id ) {
          $messageStack->add_session(ERROR_NEWSLETTER_NO_CUSTOMERS);
        } else {
          $sql_data_array = array(
            'customers_id' => 0,
            'date_sent' => 'now()',
            'newsletter_sent' => count($customers_storage['customers_count']),
            'times_sent' => $newsletter_array['times_sent']+1,
          );
          $g_db->perform(TABLE_NEWSLETTERS, $sql_data_array, 'update', "template_id = '" . (int)$nID . "'");
          $messageStack->add_session(SUCCESS_NEWSLETTER_SENT, 'success');
        }
        $g_session->unregister('customers_storage');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action')));
      }

      extract(tep_load('email'));
      $newsletter_sent_array = array();
      $body = $newsletter_array['template_content'];
      $text = strip_tags($body);
      while($check_array = $g_db->fetch_array($check_query) ) {
        $tmp_body = $body;
        $tmp_text = $text;

        $signature = $check_array['customers_id'] . '_' . $nID . '_' . md5($check_array['customers_name'].$check_array['customers_email']);
        $remove_link = '<div style="clear:both; width:100%;"><a href="' . tep_catalog_href_link('newsletter_feedback.php', 'id=' . $signature . '&action=remove') . '">' . TEXT_INFO_NEWSLETTER_REMOVE . '</a></div>';
        $tmp_body .= $remove_link;

        if( $options['statistics'] ) {
          $image_link = tep_catalog_href_link('newsletter_feedback.php', 'id=' . $signature);
          $tmp_body .= '<div><img src="' . $image_link . '"></div>';
        }
        $cEmail->reset();
        $cEmail->add_html($tmp_body, $tmp_text);
        $cEmail->build_message();
        $cEmail->send($check_array['customers_name'], $check_array['customers_email'], STORE_NAME, $customers_storage['email'], $newsletter_array['template_subject']);

        $details = array();
        if( !empty($check_array['newsletter']) ) {
          $details = unserialize($check_array['newsletter']);
        }
/*
        $details[$nID] = (int)$nID;
        $sql_data_array = array(
          'newsletter' => serialize($details),
        );
        $g_db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$check_array['customers_id'] . "'");
*/
        $customers_storage['customers_count']++;
        $last_customer_id = $check_array['customers_id'];
        $newsletter_sent_array[] = array(
          'customers_name' => $check_array['customers_name'],
          'customers_email' => $check_array['customers_email']
        );
      }
      $sql_data_array = array(
        'newsletter_sent' => 'newsletter_sent+' . $g_db->num_rows($check_query),
        'customers_id' => $last_customer_id,
      );
      $g_db->perform(TABLE_NEWSLETTERS, $sql_data_array, 'update', "template_id = '" . (int)$nID . "'");
      break;

    case 'newsletter_upload':
      $cFile = new upload('template_file');
      if( !$cFile->parse() || !tep_read_contents($cFile->tmp_filename, $template_content) ) {
        $messageStack->add_session(ERROR_NEWSLETTER_FILE_READ);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=new_template'));
      }
      $action = 'edit';
      break;
    case 'newsletter_download':
      if( empty($nID) ) {
        $messageStack->add_session(ERROR_NEWSLETTER_INVALID);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'nID') ));
      }
      $template_query = $g_db->query("select template_subject, template_content from " . TABLE_TEMPLATES . " where template_id = '" . (int)$nID . "'");
      if( !$g_db->num_rows($template_query) ) {
        $messageStack->add_session(ERROR_NEWSLETTER_INVALID);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'nID') ));
      }
      $template_array = $g_db->fetch_array($template_query);
      $filename = tep_create_safe_string(strtolower($template_array['template_subject']), '-') . '.html';
      header('Content-type: application/x-octet-stream');
      header('Content-disposition: attachment; filename=' . $filename);
      echo $template_array['template_content'];
      $g_session->close();
      break;
    case 'customers_add':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(ERROR_NOTHING_SELECTED);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=customers'));
      }
      $options = $plugin->load_options();
      if( count($customers_storage['customers']) + count($_POST['mark']) > $options['email_limit'] ) { 
        $messageStack->add_session(ERROR_MAXIMUM_STORAGE);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=customers'));
      }
      foreach ($_POST['mark'] as $key => $val) {
        $customers_storage['customers'][(int)$key] = (int)$key;
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=customers'));
      break;
    case 'customers_remove':
      $cID = isset($_GET['cID'])?(int)$_GET['cID']:0;
      if( !empty($cID) ) {
        unset($customers_storage['customers'][$cID]);
      } elseif( isset($_POST['mark']) && is_array($_POST['mark']) && !empty($_POST['mark']) ) {
        $customers_array = array();
        foreach ($_POST['mark'] as $key => $val) {
          $customers_array[] = (int)$key;
        }

        $sql_data_array = array(
          'newsletter' => 'null',
        );
        $g_db->perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id in (" . implode(',',$customers_array) . ")");
        $messageStack->add(WARNING_NEWSLETTER_CUSTOMERS_REMOVED, 'warning');
      }
      $action = 'customers';
      break;
    case 'customers_clear':
      $g_session->unregister('customers_storage');
      $messageStack->add_session(SUCCESS_NEWSLETTER_CUSTOMERS_CLEARED, 'success');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    default:
      break;
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php
  if( $action == 'send_confirm' && $last_customer_id > 0 ) {
    echo '<meta http-equiv="refresh" content="10">' . "\n";
    //echo '<meta http-equiv="refresh" content="10' . $g_relpath . tep_href_link($g_script, tep_get_all_get_params(), false) . '">' . "\n";
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  // Request Direct Plugin Access
  //$plugin = $g_plugins->get('download_system');
  if( $action == 'send_confirm' ) {
    $template_query = $g_db->query("select template_subject, template_content from " . TABLE_TEMPLATES . " where template_id = '" . (int)$nID . "' and group_id='" . (int)$gID . "'");
    $template = $g_db->fetch_array($template_query);

    if( !empty($customers_storage['customers']) ) {
      $subtitle = HEADING_NEWSLETTER_SELECTED;
    } else {
      $subtitle = HEADING_NEWSLETTER_ALL;
    }
    $title = sprintf(HEADING_NEWSLETTER_SENDING, $template['template_subject'], $subtitle );
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=sending') . '" title="' . $title . '" class="plugins_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', $title) . '</a>'; ?></div>
            <div><h1><?php echo $title; ?></h1></div>
          </div>
          <div class="comboHeading"><?php echo TEXT_INFO_NEWSLETTER_SENDING; ?></div>
          <div class="formArea"><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_CUSTOMERS_NAME; ?></th>
              <th><?php echo TABLE_HEADING_CUSTOMERS_EMAIL; ?></th>
            </tr>
<?php
    for( $i=0, $j=count($newsletter_sent_array); $i<$j; $i++) {
      $class = ($i%2)?'dataTableRow':'dataTableRowAlt';
      echo '              <tr class="' . $class . '">' . "\n";
?>
              <td><?php echo $newsletter_sent_array[$i]['customers_name']; ?></td>
              <td><?php echo $newsletter_sent_array[$i]['customers_email']; ?></td>
            </tr>
<?php
    }
    $buttons = array(
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
    );
?>
          </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></div>
        </div>
<?php
  } elseif ($action == 'edit') {
    $parameters = array(
      'template_id' => '',
      'group_id' => $gID,
      'template_title' => '',
      'template_subject' => '',
      'template_content' => $template_content,
    );
    $nInfo = new objectInfo($parameters);

    if( !empty($nID) ) {
      $template_query = $g_db->query("select template_id, template_subject, template_content from " . TABLE_TEMPLATES . " where template_id = '" . (int)$nID . "' and group_id='" . (int)$gID . "'");
      $template = $g_db->fetch_array($template_query);
      $nInfo->objectInfo($template, false);
      if( !empty($template_content) ) {
        $nInfo->template_content = $template_content;
      }
    }

    if( !empty($nID) ) {
      $form_action = 'nID=' . $nID . '&action=newsletter_upload'; 
      $title = HEADING_NEWSLETTER_EDIT;
    } else {
      $form_action = 'action=newsletter_upload'; 
      $title = HEADING_NEWSLETTER_CREATE;
    }
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=edit') . '" title="' . $title . '" class="plugins_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', $title) . '</a>'; ?></div>
            <div><h1><?php echo $title; ?></h1></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('form_template_text', $g_script, $form_action, 'post', 'enctype="multipart/form-data"'); ?><fieldset><legend><?php echo HEADING_TITLE_UPLOAD; ?></legend>
            <div class="bounder infile vmargin">
              <label class="floater"><?php echo TEXT_INFO_NEWSLETTER_FILE; ?></label>
              <div class="floater lspacer"><?php echo tep_draw_file_field('template_file'); ?></div>
            </div>
            <div class="formButtons"><?php echo tep_image_submit('button_upload.gif', IMAGE_UPLOAD); ?></div>
          </fieldset></form></div>

<?php
    if( !empty($nID) ) {
      $form_action = 'nID=' . $nID . '&action=update';
      $hidden_id = tep_draw_hidden_field('template_id', $nID);
    } else {
      $form_action = 'action=insert';
      $hidden_id = '';
    }
?>
          <div class="formArea"><?php echo tep_draw_form('form_newsletter_text', $g_script, $form_action, 'post', 'enctype="multipart/form-data"'); ?><fieldset><legend><?php echo $title; ?></legend>
            <label for="template_subject"><?php echo TEXT_NEWSLETTER_SUBJECT; ?></label>
            <div class="bspacer"><?php echo tep_draw_input_field('template_subject', $nInfo->template_subject, 'id="template_subject" size="70"') . $hidden_id; ?></div>
            <label class="floater"><?php echo TEXT_NEWSLETTER_CONTENT; ?></label>
            <div class="floatend">
<?php 
    if( $g_wp_ifc ) {
      echo '<a class="dataTableContentRed" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=change_wp&wp=0') . '">' . TEXT_INFO_DISABLE_WP . '</a>';
    } else {
      echo '<a class="dataTableContentRed" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=change_wp&wp=1') . '">' . TEXT_INFO_ENABLE_WP . '</a>';
    }
?>
            </div>
            <div class="cleaner"><?php echo tep_draw_textarea_field('template_description', $nInfo->template_content, '', '15'); ?></div>
            <div class="bounder inimg vmargin">
              <label class="floater"><?php echo TEXT_INFO_INSERT_IMAGES . ':'; ?></label>
              <div class="floater rspacer"><?php echo '<a href="#" id="image_selection">' . tep_image(DIR_WS_ICONS . 'icon_images_head.png', TEXT_INFO_INSERT_IMAGES) . '</a>'; ?></div>
              <label class="floater"><?php echo TEXT_INFO_UPLOAD_IMAGES . ':'; ?></label>
              <div class="floater"><?php echo '<a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a>'; ?></div>
            </div>
            <div class="formButtons">
<?php
    $buttons = array();
    if( !empty($nID) ) {
      $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
      $buttons[] = tep_image_submit('button_update.gif', IMAGE_UPDATE);
    } else {
      $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
      $buttons[] = tep_image_submit('button_insert.gif', IMAGE_INSERT);
    }
    echo implode('', $buttons);
?>
            </div>
          </fieldset></form></div>
        </div>
<?php
  } elseif($action == 'delete_all') {
    $tmp_array = array();
    foreach ($_POST['mark'] as $key => $val) {
      $tmp_array[] = (int)$key;
    }
    $newsletter_query_raw = "select template_id, template_subject from " . TABLE_TEMPLATES . " where template_id in ('" . implode("','", $tmp_array) . "') and group_id='" . (int)$gID . "'";
    $newsletter_array = $g_db->query_to_array($newsletter_query_raw);
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div><h1><?php echo HEADING_DELETE_ENTRIES; ?></h1></div>
          </div>
          <div class="comboHeading"><?php echo '<p>' . TEXT_INFO_DELETE_ENTRIES . '</p>'; ?></div>
          <div class="formArea"><?php echo tep_draw_form('delete_all_form', $g_script, 'action=delete_all_confirm', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_TEMPLATE_NAME; ?></th>
            </tr>
<?php
    for( $i=0, $j=count($newsletter_array); $i<$j; $i++) {
      if( empty($newsletter_array[$i]['cotent_name']) ) {
        $newsletter_array[$i]['content_name'] = TEXT_INFO_EMPTY;
      }
      $class = ($i%2)?'dataTableRow':'dataTableRowAlt';
      echo '              <tr class="' . $class . '">' . "\n";
?>
              <td><?php echo tep_draw_hidden_field('mark[' . $newsletter_array[$i]['template_id'] . ']', $newsletter_array[$i]['template_id']) . $newsletter_array[$i]['template_subject']; ?></td>
            </tr>
<?php
    }
?>
            <tr>
              <td colspan="2" class="formButtons">
<?php
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>';
      echo tep_image_submit('button_confirm.gif', IMAGE_DELETE);
?>
              </td>
            </tr>
          </table></form></div>
        </div>
<?php
  } elseif( $action == 'customers') {
    $title = HEADING_NEWSLETTER_CREATE;
    $options = $plugin->load_options();
    $remaining = $options['email_limit'] - count($customers_storage['customers']);
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=customers') . '" title="' . $title . '" class="plugins_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', $title) . '</a>'; ?></div>
            <div><h1><?php echo $title; ?></h1></div>
          </div>
          <div class="splitLine">
            <div class="floater"><?php echo sprintf(TEXT_INFO_CUSTOMERS_ASSIGNED, '<b>[' . count($customers_storage['customers']). ']</b>'); ?></div>
            <div class="floatend"><?php echo sprintf(TEXT_INFO_CUSTOMERS_REMAINING, '<b>[' . $remaining . ']</b>'); ?></div>
         </div>
<?php
    if( !empty($customers_storage['customers']) ) {
      $customers_query_raw = "select customers_id, customers_name, customers_email from " . TABLE_CUSTOMERS . " where customers_id in (" . implode(',', $customers_storage['customers']) . ") order by customers_id desc";
      $customers_array = $g_db->query_to_array($customers_query_raw);
?>
          <div class="formArea"><table class="tabledata">
<?php
      for($i=0, $j=count($customers_array); $i<$j; $i++) {
?>
            <tr class="dataTableRowAlt3">
              <td><?php echo $customers_array[$i]['customers_name']; ?></div>
              <td><?php echo $customers_array[$i]['customers_email']; ?></div>
              <td class="calign"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'cID') . 'action=customers_remove&cID=' . $customers_array[$i]['customers_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE) . '</a>'; ?></td>
            </tr>
<?php
      }
?>
          </table></div>
<?php
    }
    $filter_string = '';
    if( !empty($nID) ) {
      $check_query = $g_db->query("select customers_id from " . TABLE_NEWSLETTERS . " where template_id = '" . (int)$nID . "'");
      if( $g_db->num_rows($check_query) ) {
        $check_array = $g_db->fetch_array($check_query);
        $filter_string = "and customers_id > '" . (int)$check_array['customers_id'] . "'";
      }
    }
    $customers_query_raw = "select customers_id, customers_name, customers_email from " . TABLE_CUSTOMERS . " where newsletter is not null " . $filter_string . " order by customers_id desc";
    $customers_split = new splitPageResults($customers_query_raw, GTEXT_PAGE_SPLIT, 'customers_id', 'cpage');
    $customers_query = $g_db->query($customers_split->sql_query);
    if( $g_db->num_rows($customers_query) ) {
?>
          <div class="formArea tmargin"><?php echo tep_draw_form('customers_form', $g_script, 'action=customers_add', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign quarter" style="width:50px"><?php echo '<a href="#mark" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_CUSTOMERS_NAME; ?></th>
              <th><?php echo TABLE_HEADING_CUSTOMERS_EMAIL; ?></th>
            </tr>
<?php
      $rows = 0;
      while( $customers_array = $g_db->fetch_array($customers_query) ) {
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if( isset($customers_storage['customers'][$customers_array['customers_id']]) ) {
          $row_class = 'dataTableRowAlt3';
          $selection = TEXT_INFO_INCLUDED;
        } else {
          $selection = tep_draw_checkbox_field('mark['.$customers_array['customers_id'].']', 1);
        }
        $rows++;
        echo '              <tr class="' . $row_class . '">' . "\n";
?>
              <td class="calign"><?php echo $selection; ?></td>
              <td><?php echo $customers_array['customers_name']; ?></td>
              <td><?php echo $customers_array['customers_email']; ?></td>
            </tr>
<?php
      }
      $buttons = array(
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'cpage') ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>',
        tep_image_submit('button_add.gif', TEXT_INFO_ADD_SELECTED),
        tep_image_submit('button_remove.gif', TEXT_INFO_REMOVE_SELECTED, 'class="dflt" name="remove_customers"'),
      );
?>
          </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
          <div class="listArea splitLine">
            <div class="floater"><?php echo $customers_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $customers_split->display_links(tep_get_all_get_params('cpage')); ?></div>
          </div>
<?php
    } else {
      $buttons = array(
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'cpage') ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'
      );
?>
          <div class="splitLine">
            <div class="floater"><?php echo TEXT_INFO_NO_CUSTOMERS_FOUND; ?></div>
            <div class="floater"><?php echo implode('', $buttons); ?></div>
          </div>
<?php
    }
?>
        </div>
<?php
  } else {

    $generic_count = 0;
    $rows = 0;

    $sort_by = '';
    $sortHits = 2;
    $sortDate = 4;
    switch( $s_sort_id) {
      case 1;
        $sort_by = "newsletter_hits";
        break;
      case 2;
        $sortHits = 1;
        $sort_by = "newsletter_hits desc";
        break;
      case 3;
        $sort_by = "date_sent";
        break;
      case 4;
        $sortDate = 3;
        $sort_by = "date_sent desc";
        break;
      default:
        $s_sort_id = 0;
        $sort_by = "template_id desc";
        break;
    }
    $sort_by = " order by " . $sort_by;
?>
        <div class="maincell">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help') . '" title="' . HEADING_TITLE . '" class="plugins_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
            <div><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div class="dataTableRowAlt3 spacer floater"><?php echo '<a class="blockbox" href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=edit') . '">' . TEXT_INFO_CREATE_NEWSLETTER . '</a>'; ?></div>
            <div class="dataTableRowAlt4 spacer floater"><?php echo '<a class="blockbox" href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=customers') . '" title="' . TEXT_INFO_SELECT_CUSTOMERS_HELP . '">' . TEXT_INFO_SELECT_CUSTOMERS . '</a>'; ?></div>
            <div class="dataTableRowSelected spacer floater"><?php echo '<a class="blockbox" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') ) . '" title="' . TEXT_INFO_CLEAR_SELECTION_HELP . '">' . TEXT_INFO_CLEAR_SELECTION . '</a>'; ?></div>
            <div class="dataTableRow spacer floater"><?php echo '<a class="blockbox" href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=customers_clear') . '" title="' . TEXT_INFO_CLEAR_CUSTOMERS_HELP . '">' . TEXT_INFO_CLEAR_CUSTOMERS . '[' . count($customers_storage['customers']) . ']</a>'; ?></div>
          </div>
<?php
    if( $s_sort_id ) {
      $newsletter_query_raw = "select template_id, newsletter_hits, newsletter_sent, date_sent, times_sent from " . TABLE_NEWSLETTERS . $sort_by . "";
    } else {
      $newsletter_query_raw = "select template_id, template_subject from " . TABLE_TEMPLATES . " where group_id = '" . (int)$gID . "'"  . $sort_by . "";
    }
    $newsletter_split = new splitPageResults($newsletter_query_raw, GTEXT_PAGE_SPLIT);
    $newsletter_query = $g_db->query($newsletter_split->sql_query);

    if( $g_db->num_rows($newsletter_query) ) {
?>
          <div class="formArea"><?php echo tep_draw_form('content_form', $g_script, 'action=delete_all', 'post'); ?><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo '<a href="#mark" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a>'; ?></th>
              <th class="calign"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 's_sort_id') . 's_sort_id=' . $sortDate) . '">' . TABLE_HEADING_LAST_SENT . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_TEMPLATE_NAME; ?></th>
              <th class="calign"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 's_sort_id') . 's_sort_id=' . $sortHits) . '">' . TABLE_HEADING_HITS . '</a>'; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_CUSTOMERS_SENT; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_TIMES_SENT; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
<?php
      $rows = 0;
      $row_array = array('dataTableRowGreenLite', 'dataTableRowYellowLow', 'dataTableRowHigh', 'dataTableRowBlueLite');

      while( $newsletter_array = $g_db->fetch_array($newsletter_query) ) {
        if( $s_sort_id ) {
          $details_query = $g_db->query("select template_subject from " . TABLE_TEMPLATES . " where group_id = '" . (int)$gID . "' and template_id = '" . (int)$newsletter_array['template_id'] . "'");
          if( !$g_db->num_rows($details_query) ) {
            $details_array = array(
              'template_subject' => TEXT_INFO_ERROR
            );
          } else {
            $details_array = $g_db->fetch_array($details_query);
          }
        } else {
          $details_query = $g_db->query("select newsletter_hits, newsletter_sent, date_sent, times_sent from " . TABLE_NEWSLETTERS . " where template_id = '" . (int)$newsletter_array['template_id'] . "'");
          if( !$g_db->num_rows($details_query) ) {
            $details_array = array(
              'newsletter_sent' => 0,
              'newsletter_hits' => TEXT_INFO_ERROR,
              'times_sent' => TEXT_INFO_ERROR,
              'date_sent' => ''
            );
          } else {
            $details_array = $g_db->fetch_array($details_query);
          }
        }

        $newsletter_array = array_merge($newsletter_array, $details_array);
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        $rows++;

        if( $newsletter_array['newsletter_sent'] > 0 ) {
          $rate = 100*(floor($newsletter_array['newsletter_hits'] / $newsletter_array['newsletter_sent']));

          if( $rate > 10 ) {
            $row_class = $row_array[0];
          }

          if( $rate > 40  ) {
            $row_class = $row_array[1];
          }

          if( $rate > 70  ) {
            $row_class = $row_array[2];
          }
        }

        if( !empty($nID) && $nID == $newsletter_array['template_id'] ) {
          $nInfo = new objectInfo($newsletter_array);
          echo '              <tr class="dataTableRowSelected row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'action=edit&nID=' . $newsletter_array['template_id']) . '">' . "\n";
        } else {
          echo '              <tr class="' . $row_class . ' row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $newsletter_array['template_id']) . '">' . "\n";
        }
?>
              <td class="calign"><?php echo tep_draw_checkbox_field('mark['.$newsletter_array['template_id'].']', 1); ?></td>
              <td class="calign">
<?php
        if( !empty($newsletter_array['date_sent']) ) {
          echo tep_date_short($newsletter_array['date_sent']);
        } else {
          echo TEXT_INFO_NOT_SENT;
        }
?>
              </td>
              <td><?php echo $newsletter_array['template_subject']; ?></td>
              <td class="calign"><?php echo $newsletter_array['newsletter_hits']; ?></td>
              <td class="calign">
<?php 
        if( $newsletter_array['newsletter_sent'] > 0 ) {
          $rate = 100*(floor($newsletter_array['newsletter_hits'] / $newsletter_array['newsletter_sent']));
          echo '<a title="' . sprintf(TEXT_INFO_CUSTOMERS_SENT, $newsletter_array['newsletter_sent'], $rate) . '">' . $newsletter_array['newsletter_sent'] . '[' . $rate .'%]</a>';
        } else {
          echo TEXT_INFO_NA;
        }
?>
              </td>
              <td class="calign"><?php echo $newsletter_array['times_sent']; ?></td>
              <td class="tinysep calign">
<?php
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $newsletter_array['template_id'] . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE) . '</a>';
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $newsletter_array['template_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT) . '</a>';
        echo '<a href="' . tep_href_link($g_script, 'nID=' . $newsletter_array['template_id'] . '&action=newsletter_download') . '">' . tep_image(DIR_WS_ICONS . 'icon_download.png', ICON_FILE_DOWNLOAD . ' ' . $newsletter_array['template_subject']) . '</a>';
        if (isset($nInfo) && is_object($nInfo) && ($newsletter_array['template_id'] == $nInfo->template_id)) { 
          echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', TEXT_SELECTED); 
        } else { 
          echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $newsletter_array['template_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
        }
?>
              </td>
            </tr>
<?php
      }
      $buttons = array(
        tep_image_submit('button_delete.gif', IMAGE_DELETE),
        //'<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'action=edit') . '">' . tep_image_button('button_new.gif', IMAGE_NEW) . '</a>'
      );
?>
          </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
          <div class="listArea splitLine">
            <div class="floater"><?php echo $newsletter_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $newsletter_split->display_links(tep_get_all_get_params('page')); ?></div>
          </div>
<?php
    } else {
      $buttons = array(
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'action=edit') . '">' . tep_image_button('button_new.gif', IMAGE_NEW) . '</a>'
      );
?>
          <div class="splitLine">
            <div class="floater"><?php echo TEXT_INFO_NO_ENTRIES_FOUND; ?></div>
            <div class="floatend"><?php echo implode('', $buttons); ?></div>
         </div>
<?php
    }
?>
        </div>
<?php
    $heading = array();
    $newsletters = array();
    switch ($action) {
      case 'send':
        if( $rows > 0 && isset($nInfo) && is_object($nInfo) ) {
          $heading[] = array('text' => '<b>' . sprintf(TEXT_INFO_HEADING_SEND, $nInfo->template_subject) . '</b>');
          $newsletters[] = array('form' => tep_draw_form('form_send', $g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nID . '&action=send_confirm'));
          $newsletters[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_SEND) );
          $newsletters[] = array('text' => TEXT_INFO_SEND_INTRO);
          $newsletters[] = array('text' => TEXT_INFO_NAME . '<br /><b>' . $nInfo->template_subject . '</b>');

          $check_array = $g_db->query_to_array("select email_address as id, title as text from " . TABLE_HELPDESK_DEPARTMENTS . " order by title");
          if( empty($check_array) ) {
            $check_array = array(
              array('id' => EMAIL_FROM, 'text' => STORE_NAME)
            );
          }
          $newsletters[] = array('text' => TEXT_INFO_FROM_EMAIL . '<br />' . tep_draw_pull_down_menu('email_from', $check_array));

          $check_query = $g_db->query("select customers_id from " . TABLE_NEWSLETTERS . " where template_id = '" . (int)$nInfo->template_id . "'");
          $check_array = $g_db->fetch_array($check_query);
          if( !empty($customers_storage['customers']) ) {
            $selected_string = TEXT_INFO_SEND_SELECTED;
          } elseif( $check_array['customers_id'] ) {
            $selected_string = TEXT_INFO_SEND_CONTINUE;
          } else {
            $selected_string = TEXT_INFO_SEND_ALL;
          }
          $newsletters[] = array('class' => 'heavy required', 'text' => $selected_string);
          $buttons = array(
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
           tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
          );
          $newsletters[] = array(
            'class' => 'tmargin calign', 
            'text' => implode('', $buttons)
          );
        } else {
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $newsletters[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_SELECT));
          $newsletters[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
      case 'copy':
        if( $rows > 0 && isset($nInfo) && is_object($nInfo) ) {
          $heading[] = array('text' => '<b>' . sprintf(TEXT_INFO_HEADING_COPY, $nInfo->template_subject) . '</b>');
          $newsletters[] = array('form' => tep_draw_form('form_copy', $g_script, 'action=copy_confirm') . tep_draw_hidden_field('template_id', $nInfo->template_id));
          $newsletters[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'copy_entry.png', IMAGE_COPY) );
          $newsletters[] = array('text' => TEXT_INFO_COPY_INTRO);
          $newsletters[] = array(
            'class' => 'rpad',
            'text' => TEXT_INFO_NAME . '<br />' . tep_draw_input_field('template_subject', $nInfo->template_subject, 'class="wider"')
          );
          $buttons = array(
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
           tep_image_submit('button_copy.gif', IMAGE_COPY),
          );
          $newsletters[] = array(
            'class' => 'tmargin calign', 
            'text' => implode('', $buttons)
          );
        } else {
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $newsletters[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_SELECT));
          $newsletters[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
      case 'delete':
        if( $rows > 0 && isset($nInfo) && is_object($nInfo) ) {
          $heading[] = array('text' => '<b>' . sprintf(TEXT_HEADING_DELETE_ENTRY, $nInfo->template_subject) . '</b>');
          $newsletters[] = array('form' => tep_draw_form('form_content', $g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nID . '&action=delete_confirm') . tep_draw_hidden_field('template_id', $nInfo->template_id));
          $newsletters[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
          $newsletters[] = array('text' => TEXT_INFO_DELETE_CONTENT_INTRO);
          $newsletters[] = array('text' => TEXT_INFO_NAME . '<br /><b>' . $nInfo->template_subject . '</b>');

          $buttons = array(
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
           tep_image_submit('button_confirm.gif', IMAGE_DELETE),
          );
          $newsletters[] = array(
            'class' => 'calign', 
            'text' => implode('', $buttons)
          );
        } else { // create content dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $newsletters[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_SELECT));
          $newsletters[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
      default:
        if( $rows > 0 && isset($nInfo) && is_object($nInfo) ) {
          $heading[] = array('text' => '<b>' . $nInfo->template_subject . '</b>');
          $buttons = array(
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nInfo->template_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>',
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nInfo->template_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>',
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nInfo->template_id . '&action=copy') . '">' . tep_image_button('button_copy.gif', IMAGE_COPY) . '</a>',
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nInfo->template_id . '&action=reset') . '">' . tep_image_button('button_reset.gif', IMAGE_RESET) . '</a>',
            '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'nID=' . $nInfo->template_id . '&action=send') . '">' . tep_image_button('button_send.gif', IMAGE_SEND) . '</a>',
          );
          $newsletters[] = array(
            'class' => 'calign', 
            'text' => implode('', $buttons)
          );
          $newsletters[] = array('text' => TEXT_INFO_NAME . '<br /><b>' . $nInfo->template_subject . '</b>');
          $newsletters[] = array('text' => TEXT_INFO_HITS . '<br />' . $nInfo->newsletter_hits);
          $newsletters[] = array('text' => TEXT_INFO_SENT . '<br />' . $nInfo->times_sent);

          $date_sent = TEXT_INFO_NOT_SENT;
          if( !empty($nInfo->date_sent) ) {
            tep_date_short($nInfo->date_sent);
          }
          $newsletters[] = array('text' => TEXT_INFO_DATE . '<br />' . $date_sent );
        } else { // create content dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $newsletters[] = array('class' => 'calign', 'text' => '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'nID') . 'action=edit') . '">' . tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW) . '</a>');
          $newsletters[] = array('text' => TEXT_INFO_NO_ENTRIES);
        }
        break;
    }

    if( !empty($heading) && !empty($newsletters) ) {
      echo '             <div class="rightcell">';
      $box = new box;
      echo $box->infoBox($heading, $newsletters);
      echo '             </div>';
    }
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
