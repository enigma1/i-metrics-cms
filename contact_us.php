<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Image Zones class
//----------------------------------------------------------------------------
// Front: Contact us Page
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

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $g_validator->post_validate(array('email', 'name', 'subject', 'enquiry'));
  $l_form_name = 'contact_us';

  $error = false;
  switch($action) {
    case 'send':
      if( !isset($_POST[$l_form_name . '_x']) || !isset($_POST[$l_form_name . '_y']) ||
          !tep_not_null($_POST[$l_form_name . '_x']) || !tep_not_null($_POST[$l_form_name . '_y']) ) {
        $g_session->destroy();
        require('die.php');
        exit();
      }

      $email = $g_db->prepare_input($_POST['email']);
      $enquiry = $g_db->prepare_input($_POST['enquiry']);
      $subject = $g_db->prepare_input($_POST['subject']);
      $name = $g_db->prepare_input($_POST['name']);

      if( empty($enquiry) ) {
        $messageStack->add(ERROR_ENQUIRY_EMPTY);
        $error = true;
      }

      if( empty($subject) ) {
        $messageStack->add(ERROR_SUBJECT_EMPTY);
        $error = true;
      }

      if( empty($name) ) {
        $messageStack->add(ERROR_NAME_EMPTY);
        $error = true;
      }

	  $email_subject = $subject . ' ' . EMAIL_SUBJECT;

      if( !$error ) {
        if( tep_validate_email($email) ) {
          // Help Desk
          $department_query = $g_db->query("select email_address, name from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = '" . (int)$_POST['department_id'] . "' and front='1'");
          if( $g_db->num_rows($department_query) ) {
            $department = $g_db->fetch_array($department_query);

            require_once(DIR_WS_CLASSES . 'email.php');
            $mailer = new email();
            $result = $mailer->send_mail($department['name'], $department['email_address'], $email_subject, $_POST['enquiry'], $_POST['name'], $_POST['email'], '');
            if( !$result ) {
              $messageStack->add_session(ERROR_SEND_MAIL);
            } else {
              $messageStack->add_session(SUCCESS_ENQUIRY_SENT, 'success');
            }
            tep_redirect(tep_href_link(FILENAME_CONTACT_US, 'action=success'));
            break;
          } else {
            $error = true;
            $messageStack->add(ERROR_EMAIL_ADDRESS);
          }
        } else {
          $error = true;
          $messageStack->add(ERROR_EMAIL_ADDRESS);
        }
      }
      break;
    default:
      break;
  }
  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(basename($PHP_SELF)));
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php require('includes/objects/html_start_sub2.php'); ?>
<?php
  $heading_row = true;
  require('includes/objects/html_body_header.php');
  if( $action == 'success' ) {
    $gtext_query = $g_db->query("select gtext_title, gtext_description from " . TABLE_GTEXT . " where gtext_id = '" . GTEXT_CONTACT_SUCCESS_ID . "'");
    $gtext_array = $g_db->fetch_array($gtext_query);
?>
        <div><h1><?php echo $gtext_array['gtext_title']; ?></h1></div>
        <div><?php echo $gtext_array['gtext_description']; ?></div>
<?php
    $html_lines_array = array();
    $html_lines_array[] = '<div class="ralign"><a href="' . tep_href_link() . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a></div>' . "\n";
    require('includes/objects/html_content_bottom.php'); 

  } else {
    $gtext_query = $g_db->query("select gtext_title, gtext_description from " . TABLE_GTEXT . " where gtext_id = '" . GTEXT_CONTACT_ID . "'");
    $gtext_array = $g_db->fetch_array($gtext_query);
?>
        <div><h1><?php echo $gtext_array['gtext_title']; ?></h1></div>
        <div class="contentBoxContents contentBoxContentsAlt"><?php echo $gtext_array['gtext_description']; ?></div>
        <div class="cleaner"><?php echo tep_draw_form($l_form_name, tep_href_link(basename($PHP_SELF), 'action=send')); ?><fieldset><legend><?php echo TEXT_CONTACT_DETAILS; ?></legend>
<?php
    $departments_array = array();
    $departments_query = $g_db->query("select department_id, title from " . TABLE_HELPDESK_DEPARTMENTS . " where front='1' order by title desc");
    while ($departments = $g_db->fetch_array($departments_query)) {
      $departments_array[] = array('id' => $departments['department_id'], 'text' => $departments['title']);
    }
?>
          <div class="floater">
            <div class="form_label"><?php echo TEXT_SELECT_DEPARTMENT; ?></div>
            <div class="form_input"><?php echo tep_draw_pull_down_menu('department_id', $departments_array); ?></div>
            <div class="form_label"><?php echo ENTRY_NAME; ?></div>
            <div class="form_input"><?php echo tep_draw_input_field('name'); ?></div>
            <div class="form_label"><?php echo ENTRY_EMAIL; ?></div>
            <div class="form_input"><?php echo tep_draw_input_field('email'); ?></div>
            <div class="form_label"><?php echo ENTRY_SUBJECT; ?></div>
            <div class="form_input"><?php echo tep_draw_input_field('subject'); ?></div>
            <div class="form_label"><?php echo ENTRY_ENQUIRY; ?></div>
            <div class="form_texta"><?php echo tep_draw_textarea_field('enquiry', 'soft', '40', '10'); ?></div>
          </div>
<?php
  $html_lines_array = array();
  $html_lines_array[] = '<div class="lalign">' . tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE, 'name="' . $l_form_name . '"') . '</div>' . "\n";
  require('includes/objects/html_content_bottom.php'); 
?>
      </fieldset></form></div>
<?php
  }
?>
<?php require('includes/objects/html_end.php'); ?>
