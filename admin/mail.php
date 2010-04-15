<?php
/*
  $Id: mail.php,v 1.31 2003/06/20 00:37:51 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2007 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Mail page
// I-Metrics Engine - Version: Renegade I
//----------------------------------------------------------------------------
// Modifications:
// - 07/05/2007: PHP5 Register Globals Off support added
// - 07/08/2007: PHP5 Long Arrays Off support added
// - 07/12/2007: Moved HTML Header/Footer to a common section
// - 08/31/2007: HTML Body Common Sections Added
//----------------------------------------------------------------------------
// Released under the GNU General Public License v3.00
//----------------------------------------------------------------------------
*/
  require('includes/application_top.php');
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if( isset($_POST['back_x']) || isset($_POST['back_y']) ) {
    $action = 'back';
  }

  switch($action) {
    case 'send_email_to_user':
      if( !isset($_POST['customers_list']) || !is_array($_POST['customers_list']) || !count($_POST['customers_list']) ) {
        tep_redirect(tep_href_link(basename($PHP_SELF)));
        break;
      }

      $mail_array = array();
      $mail_sent_to = '';
      foreach( $_POST['customers_list'] as $key => $value ) {
        $mail_array[] = array(
                              'customers_firstname' => '', 
                              'customers_lastname' => '',
                              'customers_email_address' => $g_db->prepare_input($value),
                             );
        $mail_sent_to .= $g_db->prepare_input($value) . '<br />';
      }
      $mail_sent_to = substr($mail_sent_to, 0, -6);

      if( isset($_POST['attach_file']) && is_array($_POST['attach_file']) ) {
        $attach_array = array();
        foreach($_POST['attach_file'] as $key => $file ) {
          $name = $file;
          $file = DIR_FS_BACKUP . $file;
          $fp = fopen($file, "r");
          if( $fp ) {
            $attachment = fread($fp, filesize($file));
            $attach_array[] = array(
                                  'attachment' => $attachment,
                                  'name' => $name,
                                  'type' => 'application/octet-stream',
                                 );
            fclose($fp);
            @unlink($file);
          }
        }
      }

      $email_from = $g_db->prepare_input($_POST['email_from']);
      $subject = $g_db->prepare_input($_POST['subject']);
      $message = $g_db->prepare_input($_POST['message']);

      //Let's build a message object using the email class
      $mimemessage = new email(array('X-Mailer: Asymmetrics Mailer'));

      if (EMAIL_USE_HTML == 'true') {
        $text = strip_tags($message);
        $mimemessage->add_html($message, $text);
      } else {
        $mimemessage->add_text($message);
      }
      if( isset($attach_array) && is_array($attach_array) ) {
        foreach($attach_array as $value) {
          $mimemessage->add_attachment($value['attachment'], $value['name'], $value['type']);
        }
      }

      $mimemessage->build_message();

      foreach($mail_array as $mail) {
        if( tep_not_null($mail['customers_firstname']) && tep_not_null($mail['customers_lastname']) ) {
          $name = $mail['customers_firstname'] . ' ' . $mail['customers_lastname'];
        } else {
          $name = $mail['customers_email_address'];
        }
        $mimemessage->send($name, $mail['customers_email_address'], '', $email_from, $subject);
      }

      $messageStack->add_session(sprintf(NOTICE_EMAIL_SENT_TO, $mail_sent_to), 'success');
      tep_redirect(tep_href_link( basename($PHP_SELF)));
      break;
    case 'preview':
      $error = false;
      if ( !isset($_POST['customers_list']) || !is_array($_POST['customers_list']) ) {
        $messageStack->add_session(ERROR_NO_CUSTOMER_SELECTED, 'error');
        $error = true;
      }

      if ( !isset($_POST['subject']) || !tep_not_null($_POST['subject']) ) {
        $messageStack->add_session(ERROR_NO_SUBJECT, 'error');
        $error = true;
      }

      if( isset($_FILES['attach_file']) && is_array($_FILES['attach_file']) && isset($_FILES['attach_file']['name']) && is_array($_FILES['attach_file']['name']) ) {
        $attach_array = array();
        foreach( $_FILES['attach_file']['name'] as $key => $value ) {
          if( !tep_not_null($value) ) continue;
          $value = basename($value);
          $check = $_FILES["attach_file"]["error"][$key];
          if ($check != UPLOAD_ERR_OK) {
            $messageStack->add_session(sprintf(ERROR_FILE_UPLOAD, $value), 'error');
          }
          if(file_exists(DIR_FS_BACKUP . $value) ) {
            unlink (DIR_FS_BACKUP . $value);
          }
          $tmp_name = $_FILES["attach_file"]["tmp_name"][$key];
          move_uploaded_file($tmp_name, DIR_FS_BACKUP . $value);
          $attach_array[] = $value;
        }
      }
      if( $error ) {
        tep_redirect(tep_href_link(basename($PHP_SELF)));
      }
      break;
    case 'back':
      $action = '';
      break;
  }

  $email_from = EMAIL_FROM;
  $default_query = $g_db->query("select department_id, title, email_address from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = '" . DEFAULT_HELPDESK_DEPARTMENT_ID . "'");
  if( $g_db->num_rows($default_query) ) {
    $default_array = $g_db->fetch_array($default_query);
    $email_from = $default_array['email_address'];
  }

?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php
  if (!tep_not_null($action) ) {
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/jquery/themes/smoothness/ui.all.css">
<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery-1.3.2.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.ajaxq.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.form.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/jquery/ui/jquery-ui-1.7.2.custom.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/config.js"></script>
<script language="javascript" type="text/javascript" src="includes/javascript/image_control.js"></script>
<?php
  }
?>
<script language="javascript" type="text/javascript"><!--
$(document).ready(function(){
  var jqWrap = tinymce_ifc;
  // Initialize JS variables with PHP parameters to be passed to the js file
  jqWrap.TinyMCE = '<?php echo $g_relpath . DIR_WS_INCLUDES . 'javascript/tiny_mce/tiny_mce.js'; ?>';
  jqWrap.baseFront = '<?php echo $g_server . DIR_WS_CATALOG; ?>';
  jqWrap.cssFront = '<?php echo $g_server . DIR_WS_CATALOG . 'stylesheet.css'; ?>';
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.areas = 'message';
  jqWrap.launch();

  var jqWrap = image_control;
  jqWrap.editObject = tinyMCE;
  jqWrap.baseFront = '<?php echo $g_server . DIR_WS_CATALOG; ?>';
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.launch();
});

function setList(source, target) {
  found = false;
  if( source.type == 'text' && source.value.length > 6 ) {
    text = source.value;
    value = source.value;
    found = 2;
  } else if(source.type == 'select-one') {
    for( i=0, j=source.options.length; i<j; i++) {
      if( source.options[i].selected ) {
        text = source.options[i].text;
        value = source.options[i].value;
        found = true;
        break;
      }
    }
  }
  if( !found ) {
    return;
  }

  if( text == 'Select Customer' || text == 'All Customers' || text == 'To All Newsletter Subscribers') {
    return;
  }
  for( i=0, j=target.options.length; i<j; i++) {
    if(target.options[i].text == text && target.options[i].value == value) {
      found = false;
      break;
    }
  }

  if( !found ) {
    return;
  }

  var entry = document.createElement('option');
  entry.text = text;
  entry.value = value;
  var entry_old;
  if( found == 2 ) {
    entry_old = null;
  } else {
    entry_old = target.options[source.selectedIndex];
  }

  try {
    target.add(entry, entry_old); // non-IE support
  }
  catch(ex) {
    select = target.options.length;
    target.add(entry, select); // IE support
  }
  // Select entire list
  for( i=0, j=target.options.length; i<j; i++) {
    target.options[i].selected = true;
  }

}

function removeList(object) {
  var i;
  for (i = object.length - 1; i>=0; i = i-1) {
    if (object.options[i].selected) {
      object.remove(i);
    }
  }
}

g_rows_index = 1;
function addFileRows(name) {
  html = '<input type="file" size="44" name="attach_file['+g_rows_index+']" /><br />';
  newElem = document.getElementById("extrarows");
  newElem.innerHTML += html;

  g_rows_index++;
}
//--></script>
<?php require('includes/objects/html_start_sub2.php'); ?>
        <div class="maincell" style="width: 100%;">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
<?php
  if ( ($action == 'preview') && isset($_POST['customers_list']) ) {
    $mail_sent_to = '';
    foreach($_POST['customers_list'] as $key => $value) {
      $mail_sent_to .= $value . '<br />';
    }
?>
            <div class="formArea"><?php echo tep_draw_form('mail', basename($PHP_SELF), 'action=send_email_to_user'); ?><table border="0" width="100%" cellpadding="0" cellspacing="2">
              <tr>
                <td class="smallText"><b><?php echo TEXT_CUSTOMER; ?></b>
<?php
    if( isset($_POST['customers_list']) && is_array($_POST['customers_list']) ) {
      foreach($_POST['customers_list'] as $key2 => $value2) {
        $value2 = htmlspecialchars(stripslashes($value2));
        echo '<br />' . $value2 . tep_draw_hidden_field('customers_list[' . $key2 . ']', $value2);
      }
    } else {
      echo '<br />' . htmlspecialchars(stripslashes($mail_sent_to));
    }
?>
                </td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_FROM; ?></b><br /><?php echo htmlspecialchars(stripslashes($_POST['email_from'])); ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_SUBJECT; ?></b><br /><?php echo htmlspecialchars(stripslashes($_POST['subject'])); ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_MESSAGE; ?></b><br />
<?php 
    if (EMAIL_USE_HTML == 'true') {
      echo $g_db->prepare_input($_POST['message']);
    } else {
      echo nl2br(htmlspecialchars(stripslashes($_POST['message']))); 
    }
?>
                </td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
    if( isset($attach_array) && is_array($attach_array) && count($attach_array) ) {
?>
              <tr>
                <td class="smallText"><b><?php echo TEXT_ATTACHMENTS; ?></b>
<?php
      foreach($attach_array as $key2 => $value2) {
        $value2 = htmlspecialchars(stripslashes($value2));
        echo '<br />' . $value2 . tep_draw_hidden_field('attach_file[' . $key2 . ']', $value2);
      }
?>

              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
    }
?>
              <tr>
                <td class="formButtons">

<?php
/* Re-Post all POST'ed variables */
    foreach($_POST as $key => $value ) {
      if( $key == 'message' ) {
        echo tep_draw_hidden_field($key, $g_db->prepare_input($value) );
      } elseif (!is_array($_POST[$key])) {
        echo tep_draw_hidden_field($key, htmlspecialchars(stripslashes($value)));
      }
    }
/*
    if( isset($_POST['customers_list']) && is_array($_POST['customers_list']) ) {
      foreach($_POST['customers_list'] as $key2 => $value2) {
        echo tep_draw_hidden_field('customers_list[' . $key2 . ']', htmlspecialchars(stripslashes($value2)));
      }
    }
    if( isset($attach_array) && is_array($attach_array) && count($attach_array) ) {
      foreach($attach_array as $key2 => $value2) {
        echo tep_draw_hidden_field('attach_file[' . $key2 . ']', htmlspecialchars(stripslashes($value2)));
      }
    }
*/
    echo tep_image_submit('button_back.gif', IMAGE_BACK, 'name="back"') . '&nbsp;' . '<a href="' . tep_href_link(basename($PHP_SELF)) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_confirm.gif', IMAGE_SEND_EMAIL);
?>
                </td>
              </tr>
            </table></form></div>
<?php
  } else {
?>
          <div id="modalBox" title="Image Selection" style="display:none;">Loading...Please Wait</div>
          <div id="ajaxLoader" title="Image Manager" style="display:none;"><img src="includes/javascript/jquery/themes/smoothness/images/ajax_load.gif"><p id="ajaxMsg" class="main">Updating, please wait...</p><hr /></div>
            <div class="formArea"><?php echo tep_draw_form('mail_form', basename($PHP_SELF), 'action=preview', 'post', 'enctype="multipart/form-data"'); ?><table width="100%" border="0" cellpadding="0" cellspacing="2">
<?php
    $customers = $customers_list2 = array();
?>
              <tr>
                <td width="60" class="main"><?php echo TEXT_FROM; ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_input_field('email_from', '', 'size="48"'); ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_SUBJECT; ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_input_field('subject', '', 'size="48"'); ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_CUSTOMER_BLANK; ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_input_field('customers_blank', (isset($_GET['customers_blank']) ? $_GET['customers_blank'] : ''), 'size="48" id="customers_blank"'); ?></td>
                <td><?php echo '<a href="javascript:void(0)" onclick="setList(document.mail_form.customers_blank, document.mail_form.customers_list);">' . tep_image(DIR_WS_ICONS . 'icon_arrow_down.png', 'Specify your own email address to the receipient list') . '</a>'; ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_CUSTOMER_LIST; ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_pull_down_menu('customers_list[]', $customers_list2, '', 'multiple="multiple" size="6" style="width: 306px" id="customers_list"'); ?></td>
                <td><?php echo '<a href="javascript:void(0)" onclick="removeList(document.mail_form.customers_list);">' . tep_image(DIR_WS_ICONS . 'cross.gif', 'Remove selected entries from the receipient list') . '</a>'; ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_ATTACH_FILE; ?></td>
              </tr>
              <tr>
                <td class="main"><div id="extrarows" style="position:relative; padding:0; margin:0"><?php echo tep_draw_file_field('attach_file[0]', 'size="44"'); ?></div></td>
                <td><?php echo '<a href="javascript:void(0)" onclick="addFileRows(document.mail_form.extrarows);">' . tep_image(DIR_WS_ICONS . 'icon_arrow_down.png', 'Add more rows for file attachments') . '</a>'; ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td valign="top" class="main"><?php echo TEXT_MESSAGE; ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_textarea_field('message', 'soft', '60', '15'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><table border="0" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><b><?php echo TEXT_INFO_INSERT_IMAGES . ':'; ?></b></td>
                    <td><?php echo '<a href="#" id="image_selection">' . tep_image(DIR_WS_ICONS . 'icon_images_head.png', TEXT_INFO_INSERT_IMAGES) . '</a>'; ?></td>
                    <td><?php echo tep_draw_separator('pixel_trans.gif', '30', '1'); ?></td>
                    <td class="smallText"><b><?php echo TEXT_INFO_UPLOAD_IMAGES . ':'; ?></b></td>
                    <td><?php echo '<a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a>'; ?></td>
                  </tr>
                </table></td>
              </tr>

              <tr>
                <td colspan="2" class="formButtons"><?php echo tep_image_submit('button_send.gif', IMAGE_SEND_EMAIL); ?></td>
              </tr>
            </table></form></div>
<?php
  }
?>
        </div>
<?php require('includes/objects/html_end.php'); ?>
