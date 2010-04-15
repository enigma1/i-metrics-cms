<?php
/*  ----------------------------------------------
    Purpose: Retrieve emails from POP3 server for each helpdesk account (Department)

    v0.2 20-Aug-05 by Lane Roathe (www.ifd.com)
    - display meaningful error messagaes when imap_open fails
    - fix usage of name/email address
    - output is now in a logfile format suitable for CRON usage
    - fixed bug where emails were always deleted (never checked the setting, relied on read-only connecton)
    - cleanup messages
    - cleanup code
    - document code & usage
    - fixed use of depriciated tep_merge_array

    LOG FORMAT:
    [Sat Mar 10 15:16:08 2001 -0600] [INFO|ERROR|WARNING|MISC] message

//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// - Added Attachments support
// - IMap fixes to receive emails for PHP 5
// - osCommerce formating added
// - Common Sections added
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
  include(DIR_WS_INCLUDES . 'classes/mime_decode.php');

  include_once( DIR_WS_FUNCTIONS . 'helpdesk.php' );

    // We need the date for each message, make it easy to maintain
  define('DATETIME', date("D M j G:i:s Y O"));              // Sat Mar 10 15:16:08 2001 -0600

  // Make the output work for both the logfile as well as for the "interactive" use via a web browser
  define('CRLF', "<br />\r\n");
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php require('includes/objects/html_start_sub2.php'); ?>
          <div class="maincell" style="width: 100%">
            <div class="comboHeading">
              <div class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
            <div class="listArea"><table border="0" width="100%" cellspacing="1" cellpadding="3">
<?php
  // display the run
  if( 'true' == DEFAULT_HELPDESK_DISPLAY_HEADER ) {
    echo '<tr class="dataTableRow">';
    echo '  <td class="dataTableContent">['. DATETIME . '] [MISC] +++ Helpdesk Mail retrieval system version 0.2' . '</td>';
    echo '</tr>';
    if( 'true' == DEFAULT_HELPDESK_DELETE_EMAILS ) {
      echo '<tr class="dataTableRow">';
      echo '  <td class="dataTableContent">' . '['. DATETIME . '] [MISC] +++ Emails will be deleted from the server +++' . '</td>';
      echo '</tr>';
    }
  }

  // loop through all of the helpdesk departments so we can query each one's server
  $filter ='';
  if( isset($_GET['department']) ) {
    $filter = " where department_id = '" . (int)$_GET['department'] . "'";
  } else {
    $filter = " where receive = '1'";
  }
  $account_query = $g_db->query("select * from " . TABLE_HELPDESK_DEPARTMENTS . $filter);
  $error_array = array();

  $total_messages = 0;
  while( $account = $g_db->fetch_array($account_query) ) {
    $department = $account['title'];
    $username = $account['name'];
    $password = $account['password'];
    
    // No need to process the department if not setup to do so
    $emailaddress = $account['email_address'];
    if( empty($username) ) {
      echo '<tr class="dataTableRow">';
      echo '  <td class="dataTableContent">' . '['. DATETIME . '] [WARNING] No email account setup for [' . $department . ']' . '</td>';
      echo '</tr>';
      continue;
    }
    echo '<tr class="dataTableRow">';
    echo '  <td class="dataTableContent">' . '[' . DATETIME . '] [INFO] Processing emails for ['. $department . '] ('. $emailaddress . '/' . $username . ')' . '</td>';
    echo '</tr>';
    
    // See if we want to open the connection read-only or not.
    if( 'true' == DEFAULT_HELPDESK_DELETE_EMAILS || 'false' == DEFAULT_HELPDESK_READ_ONLY ) {
      $mode = CL_EXPUNGE;
    } else {
      $mode = OP_READONLY;
    }
    $mailbox = '';
    // Try to open the connection
    if( $conn = @imap_open("{".DEFAULT_HELPDESK_MAILSERVER.DEFAULT_HELPDESK_PROTOCOL_SPECIFICATION."}".$mailbox, $username, $password, $mode) ) {
      // setup the mime decoding for all of the messages
      $params = array();
      $params['decode_headers'] = true;
      $params['crlf']           = "\r\n";
      $params['include_bodies'] = true;
      $params['decode_bodies']  = true;
      
      // if we have any messages, start going through them
      if ($msgCount = imap_num_msg($conn)) {
        $total_messages += $msgCount;
        echo '<tr class="dataTableRow">';
        echo '  <td class="dataTableContent">' . '[' . DATETIME . '] [INFO] Found [' . $msgCount . '] new messages' . '</td>';
        echo '</tr>';
        // Process messages
        for($i = 1; $i <= $msgCount; $i++) {
          //-MS- Check Attachments
          //fetch structure of message
          $attachments_array = array();
          $parts_array = array();
          $mail_struct=imap_fetchstructure($conn,$i);
          //see if there are any parts
          if( isset($mail_struct->parts) && is_array($mail_struct->parts) ) {
            foreach ($mail_struct->parts as $partno => $partarr) {
              echo '<tr class="dataTableRow">';
              echo '  <td class="dataTableContent">' . 'Processing Main Index: ' . $i . '<td>';
              echo '</tr>';
              //parse parts of email
              help_desk_parsepart($partarr, $partno+1, $conn, $i, $parts_array);
              if( isset($parts_array[$partno+1]['attachment']) && !in_array($parts_array[$partno+1]['attachment']['filename'], $attachments_array) ) {
                $attachments_array[] = $parts_array[$partno+1]['attachment']['filename'];
              }
              //$attachment = help_desk_parsepart($partarr, $partno+1, $conn, $i, $parts_array);
              //if( is_string($attachment) && !in_array($attachment, $attachments_array) ) {
                //$attachments_array[] = help_desk_parsepart($partarr, $partno+1, $conn, $i, $parts_array);
              //}
            }
          }
          //-MS- Check Attachments EOM
          // if desired, mark the message we are going to process for deletion
          if( DEFAULT_HELPDESK_DELETE_EMAILS == 'true' ) {
            imap_delete($conn, $i);
          }
          // Get the message header
          $header = imap_fetchheader($conn, $i, FT_PREFETCHTEXT);
          
          // and the body, marking as seen if desired (no effect if messages deleted of course!)
          if ( 'true' == DEFAULT_HELPDESK_MARKSEEN )
            $body = imap_body($conn, $i);
          else
            $body = imap_body($conn, $i, FT_PEEK);
          
          // get some readable text from the mime input
          $params['input'] = $header.$body;

          $tmp_obj = new Mail_mimeDecode($params['input'], "\r\n");
          $output = $tmp_obj->decode($params);

          // create the osc parts for an email
          $parts = array();
          osc_parse_mime_decode_output($output, $parts);
         
          // get rid of things that will confuse php
          //$field_body = trim($parts['text'][0]);
          //$field_body = trim($parts['html'][0]);
          // -MS- Added HTML storage

          $field_body = '';
          if( isset($parts['html']) && isset($parts['html'][0]) ) {
            $field_body = trim($parts['html'][0]);
          } elseif( isset($parts['text']) && isset($parts['text'][0]) ) {
            $field_body = trim($parts['text'][0]);
          }

          // make sure we have a date
          if (empty($output->headers['date'])) {
            $field_date = date("Y-m-d H:i:s");
          } else {
            $field_date = $g_db->prepare_input($output->headers['date']);
            $field_date = date("Y-m-d H:i:s", strtotime($field_date, time()));
          }
          // if there is an IP address present, grab it & the host name as well if we can
          //if (ereg('[0-9]+\.[0-9]+\.[0-9]+\.[0-9]', $output->headers['received'], $regs)) {
          $received_headers = array();
          if( !is_array($output->headers['received']) ) {
            $received_headers[] = $output->headers['received'];
          } else {
            $received_headers = $output->headers['received'];
          }

          foreach( $received_headers as $header ) {
            if (ereg('([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)', $header, $regs)) {
              $field_ip = $g_db->prepare_input($regs[1], true);
              $field_host = @gethostbyaddr($field_ip);
              break;
            } else {
              $field_ip = '';
              $field_host = '';
            }
          }

          // Make sure we try had to have a valid from email address
          if (empty($output->headers['from'])) {
              $field_from = '';
              $field_from_email_address = '';
          } else {
            if (ereg('"([^"]+)" <([^>]+)>', $output->headers['from'], $regs)) {
              $field_from = trim($regs[1]);
              $field_from_email_address = trim($regs[2]);
            } elseif (ereg('([^<]+)<([^>]+)>', $output->headers['from'], $regs)) {
              $field_from = trim($regs[1]);
              $field_from_email_address = trim($regs[2]);
            } elseif (substr($output->headers['from'], 0, 1) == '<') {
              $field_from = substr($output->headers['from'], 1, -1);
              $field_from_email_address = $field_from;
            } else {
              $field_from = $output->headers['from'];
              $field_from_email_address = $field_from;
            }
          }
          
          // Also try hard to fill in the to email address properly
          if (empty($output->headers['to'])) {
            $field_to = '';
            $field_to_email_address = '';
          } else {
            if (ereg('"([^"]+)" <([^>]+)>', $output->headers['to'], $regs)) {
              $field_to = trim($regs[1]);
              $field_to_email_address = trim($regs[2]);
            }elseif (ereg('([^<]+)<([^>]+)>', $output->headers['to'], $regs)) {
              $field_to = trim($regs[1]);
              $field_to_email_address = trim($regs[2]);
            } elseif (substr($output->headers['to'], 0, 1) == '<') {
              $field_to = substr($output->headers['to'], 1, -1);
              $field_to_email_address = $field_to;
            } else {
              $field_to = $output->headers['to'];
              $field_to_email_address = $field_to;
            }
          }
          
          // we are set, setup to store in DB
          $ticket = false;
          $parent_id = '0';
          $status_id = DEFAULT_HELPDESK_STATUS_ID;
          $priority_id = DEFAULT_HELPDESK_PRIORITY_ID;
          
          $department_id = $account['department_id'];
          $field_message_id = '';
          if( isset($output->headers['message-id']) ) {
            $field_message_id = $g_db->prepare_input($output->headers['message-id']);
          }
          $field_subject = $g_db->prepare_input($output->headers['subject'], true);

          // Add spam filtering here before the database queries
          
          // check if the email already in the database
          $existing_query = $g_db->query("select message_id from " . TABLE_HELPDESK_ENTRIES . " where message_id='" . $g_db->input($field_message_id) . "'");
          if(!$g_db->num_rows($existing_query)) {
            // check for existing ticket number
            if (ereg('^.*\['.DEFAULT_HELPDESK_TICKET_PREFIX.'([A-Z0-9]{7})\].*$', $field_subject, $regs)) {
              $ticket = $g_db->prepare_input($regs[1], true);
              $ticket_info_query = $g_db->query("select he.parent_id, ht.department_id, ht.status_id, ht.priority_id from " . TABLE_HELPDESK_ENTRIES . " he left join " . TABLE_HELPDESK_TICKETS . " ht on (he.ticket = ht.ticket) where he.ticket = '" . $g_db->input($ticket) . "' order by he.parent_id desc limit 1");
              // if we find one, setup as our parent ticket (limit 1 w/sort ensures this is correct)
              if ($g_db->num_rows($ticket_info_query)) {
                  $ticket_info = $g_db->fetch_array($ticket_info_query);
                  $parent_id = $ticket_info['parent_id'];
                  $status_id = $ticket_info['status_id'];
                  $priority_id = $ticket_info['priority_id'];
                  $department_id = $ticket_info['department_id'];
              }
            } else {       
              // Create a unique ticked ID and make sure it's available
              while (true) {
                // create & check for dups until unique
                $ticket = osc_create_random_string();
                $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where ticket = '" .  $g_db->input($ticket) . "'");
                $check = $g_db->fetch_array($check_query);

                // if unique we can break the while loop
                if ($check['count'] < 1) {
                  break;
                }
              }
            }
            // how we insert the ticked depends on whether or not this is the first ticket
            // !! this is a completely wasteful DB query; use the data from above to make this decision!
            $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $g_db->input($ticket) . "'");
            $check = $g_db->fetch_array($check_query);
            
            // if this is the first ticket, we need to setup the "master" ticket item for tracking purposes          
            if ($check['count'] < 1) {
              $sql_data_array = array(
                                      'ticket' => $ticket,
                                      'department_id' => (int)$department_id,
                                      'priority_id' => DEFAULT_HELPDESK_PRIORITY_ID,
                                      'status_id' => DEFAULT_HELPDESK_STATUS_ID,
                                      'datestamp_last_entry' => 'now()',
                                     );
              $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array);

              echo '<tr class="dataTableRow">';
              echo '  <td class="dataTableContent">' . '[' . DATETIME . '] [_NEW_] [' . $ticket . '] - ' . $field_subject . '</td>';
              echo '</tr>';
            }

            // FINALLY -- we can now save this email as ticket entry!
            $sql_data_array = array(
                                    'ticket' => $ticket,
                                    'parent_id' => (int)$parent_id,
                                    'message_id' => $field_message_id,
                                    'ip_address' => $field_ip,
                                    'host' => $g_db->prepare_input($field_host),
                                    'datestamp_local' => 'now()',
                                    'datestamp' => $field_date,
                                    'receiver' => $g_db->prepare_input($field_to),
                                    'receiver_email_address' => $g_db->prepare_input($field_to_email_address),
                                    'sender' => $g_db->prepare_input($field_from),
                                    'email_address' => $g_db->prepare_input($field_from_email_address),
                                    'subject' => $field_subject,
                                    'body' => $g_db->prepare_input($field_body),
                                    'entry_read' => '0',
                                   );
            $g_db->perform(TABLE_HELPDESK_ENTRIES, $sql_data_array);
            $helpdesk_entry_id = $g_db->insert_id();

            for($x = 0, $y = count($attachments_array); $x<$y; $x++ ) {
              $sql_data_array = array(
                                      'helpdesk_entries_id' => (int)$helpdesk_entry_id,
                                      'ticket' => $ticket,
                                      'attachment' => $g_db->prepare_input($attachments_array[$x]),
                                     );
              $g_db->perform(TABLE_HELPDESK_ATTACHMENTS, $sql_data_array);
            }

            echo '<tr class="dataTableRow">';
            echo '  <td class="dataTableContent">' . '[' . DATETIME . '] [UPDATE] [' . $ticket . '] - [' . $parent_id . '] - ' . $field_subject . '</td>' . "\n";
            echo '</tr>';
          }
          // !! NOT useful echo "Messages after delete: " . $conn->Nmsgs . "".CRLF;
        }
      } else {
        echo '<tr class="dataTableRow">';
        echo '  <td class="dataTableContent">' . '['. DATETIME . '] [INFO] Found [0] new messages -- No mail for this dept. on the server.' . '</td>';
        echo '</tr>';
      }
      // Cleanup errros
      $error_array[$department] = imap_errors();
      imap_close($conn, $mode);
    } else {
      echo '<tr class="dataTableRow">';
      echo '  <td class="dataTableContent">' . '[' . DATETIME . '] [ERROR] Unable to connect: ' . imap_last_error() . '</td>';
      echo '</tr>';
    }
  }
  // Cleanup errros
  $error_array = imap_errors();

?>
            </table></div>
          </div>
<?php require('includes/objects/html_end.php'); ?>
