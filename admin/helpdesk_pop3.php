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
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
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

 $headers .= "X-Priority: 1 (Higuest)\n";
        $headers .= "X-MSMail-Priority: High\n";
        $headers .= "Importance: High\n"; 
*/
  require('includes/application_top.php');
  require_once(DIR_FS_CLASSES . 'mime_decode.php');
  require_once( DIR_FS_FUNCTIONS . 'helpdesk.php' );
  tep_set_time_limit(0);

    // We need the date for each message, make it easy to maintain
  //define('DATETIME', date("D M j G:i:s Y O"));              
  $datetime = date("D M j G:i:s Y O");  // Sat Mar 10 15:16:08 2001 -0600

  // Make the output work for both the logfile as well as for the "interactive" use via a web browser
  define('CRLF', "<br />\r\n");
?>
<?php require(DIR_FS_INCLUDES . 'objects/html_start_sub1.php'); ?>
<?php require(DIR_FS_INCLUDES . 'objects/html_start_sub2.php'); ?>
          <div class="maincell wider">
            <div class="comboHeadingTop">
              <div><h1><?php echo HEADING_TITLE . ' - ' . $datetime; ?></h1></div>
            </div>
            <div class="formArea">
<?php
  // loop through all of the helpdesk departments so we can query each one's server
  $filter ='';
  if( isset($_GET['department']) ) {
    $filter = " where department_id = '" . (int)$_GET['department'] . "'";
  } else {
    $filter = " where receive = '1'";
  }
  $account_query = $g_db->query("select department_id, title, email_address, name, password, server_connect, server_protocol, server_mailbox, body_size, ticket_prefix, receive, readonly from " . TABLE_HELPDESK_DEPARTMENTS . $filter);
  $error_array = array();
  $rows = $g_db->num_rows($account_query);

  $total_messages = 0;
  while( $account = $g_db->fetch_array($account_query) ) {
    $department = $account['title'];
    $username = $account['name'];
    $password = $account['password'];
    $ticket_prefix = $account['ticket_prefix'];
    
    // No need to process the department if not setup to do so
    $emailaddress = $account['email_address'];
    if( empty($username) ) {
      echo '  <div class="messageStackError">' . sprintf(ERROR_NO_ACCOUNT_SETUP, '<b>' . $department . '</b>') . '</div>';
      continue;
    }

    // See if we want to open the connection read-only or not.
    if( DEFAULT_HELPDESK_DELETE_EMAILS == 'true' || DEFAULT_HELPDESK_READ_ONLY  == 'false') {
      $mode = CL_EXPUNGE;
    } else {
      $mode = OP_READONLY;
    }

    if( $account['readonly']) {
      $mode = OP_READONLY;
    }

    echo '<div class="messageStackWarning">' . sprintf(TEXT_INFO_PROCESSING_EMAILS, '<b>' . $department . '</b>', $emailaddress . '/' . $username) . '</div>';

    if( $mode != OP_READONLY ) {
      echo '<div class="dataTableRowYellow linepad">' . sprintf(TEXT_INFO_WILL_DELETE, '<b>' . $department . '</b>') . '</div>';
    }

    $mail_server = DEFAULT_HELPDESK_MAILSERVER.DEFAULT_HELPDESK_PROTOCOL_SPECIFICATION;
    if( !empty($account['server_connect']) && !empty($account['server_protocol']) ) {
      $mail_server = $account['server_connect'].$account['server_protocol'];
    }
    $mailbox = '';
    if( !empty($account['server_mailbox']) ) {
      $mailbox = $account['server_mailbox'];
    }

    // Try to open the connection
    if( $conn = @imap_open('{' . $mail_server . '}' . $mailbox, $username, $password, $mode) ) {
      // setup the mime decoding for all of the messages
      $params = array();
      $params['decode_headers'] = true;
      $params['crlf']           = "\r\n";
      $params['include_bodies'] = true;
      $params['decode_bodies']  = true;
      
      // if we have any messages, start going through them
      if ($msgCount = imap_num_msg($conn)) {
        $total_messages += $msgCount;
        echo '<div class="messageStackSuccess">' . sprintf(SUCCESS_MESSAGES_FOUND, '<b>[' . $msgCount . ']</b>', $department . '</b>') . '</div>';
        // Process messages
        for($i = 1; $i <= $msgCount; $i++) {
          // if desired, mark the message we are going to process for deletion
          if( DEFAULT_HELPDESK_DELETE_EMAILS == 'true' ) {
            imap_delete($conn, $i);
          }

          //fetch structure of message
          $mail_struct = imap_fetchstructure($conn,$i);
          // Get the message header
          $headers_string = imap_fetchheader($conn, $i, FT_PREFETCHTEXT);
          
          // and the body, marking as seen if desired (no effect if messages deleted of course!)
          if ( 'true' == DEFAULT_HELPDESK_MARKSEEN ) {
            $body = imap_body($conn, $i);
          } else {
            $body = imap_body($conn, $i, FT_PEEK);
          }

          // get some readable text from the mime input
          $params['input'] = $headers_string.$body;

          $tmp_obj = new Mail_mimeDecode($params['input'], "\r\n");
          $output = $tmp_obj->decode($params);

          // create the osc parts for an email
          $parts = array();
          osc_parse_mime_decode_output($output, $parts);

/*
          $field_body = '';
          if( isset($parts['html']) && isset($parts['html'][0]) ) {
            $field_body = trim($parts['html'][0]);
          } elseif( isset($parts['text']) && isset($parts['text'][0]) ) {
            $field_body = nl2br(trim($parts['text'][0]));
          }
*/

          $field_html_body = $field_text_body = '';
          $text_index = $html_index = 0;
          foreach( $parts as $key => $value ) {
            if( $key == 'html' && is_array($value) && isset($value[0]) ) {
              if( $html_index ) $field_html_body .= '<br /><hr />';
              $field_html_body .= trim($value[0]);
              $html_index++;
            } elseif( $key == 'text' && is_array($value) && isset($value[0]) ) {
              if( $text_index ) $field_text_body .= "\r\n\r\n-----------------------------\r\n";
              $field_text_body .= nl2br(trim($value[0]));
              $text_index++;
            }
          }


          // make sure we have a date
          if( empty($output->headers['date']) ) {
            $field_date = date("Y-m-d H:i:s");
          } else {
            $field_date = $g_db->prepare_input($output->headers['date']);
            $field_date = date("Y-m-d H:i:s", strtotime($field_date, time()));
          }
          // if there is an IP address present, grab it & the host name as well if we can
          $received_headers = array();
          if( !is_array($output->headers['received']) ) {
            $received_headers[] = $output->headers['received'];
          } else {
            $received_headers = $output->headers['received'];
          }

          $field_ip = '';
          $field_host = '';
          $field_from = '';
          $field_from_email_address = '';
          $field_to = '';
          $field_to_email_address = '';

          foreach( $received_headers as $key => $header ) {
            if( preg_match_all('([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)', $header, $regs) ) {
              $scan_array = $regs[0];
              $scan_ip = array_pop($scan_array);
              $field_ip = $g_db->prepare_input($scan_ip, true);
              if( $_SERVER['SERVER_ADDR'] == $field_ip ) continue;

              $tmp_array = explode('.', $field_ip);
              if( (int)$tmp_array[0] == 127 ) {
                continue;
              }
              //$field_host = @gethostbyaddr($field_ip);
              break;
            }
          }

          $skip_mail = false;
          if( !empty($field_ip) ) {
            $info_array = array();
            //$info_array[] = tep_check_ip_blacklist($field_ip, 'bl.spamcop.net');
            //$info_array[] = tep_check_ip_blacklist($field_ip, 'cdl.anti-spam.org.cn');
            //$info_array[] = tep_check_ip_blacklist($field_ip, 'cblplus.anti-spam.org.cn');
            //$info_array[] = tep_check_ip_blacklist($field_ip, 'sbl.nszones.com');
            //$tmp_result = tep_check_ip_blacklist($field_ip, 'dnsbl.sorbs.net');
            //if( isset($tmp_result['dnsbl.sorbs.net']) && $tmp_result['dnsbl.sorbs.net'] != '127.0.0.10' ) {
            //  $info_array[] = $tmp_result;
            //}
            //$info_array[] = tep_check_ip_blacklist($field_ip, 'cbl.abuseat.org');
            //$info_array[] = tep_check_ip_blacklist($field_ip, 'dnsbl.njabl.org');
            //$info_array[] = tep_check_ip_blacklist($field_ip, 'sbl-xbl.spamhaus.org');

            for( $i2=0, $j2=count($info_array); $i2<$j2; $i2++ ) {
              if( !empty($info_array[$i2]) ) {
                $skip_mail = true;
                break;
              }
            }
          }

          if( $skip_mail ) {
            echo '<div class="messageStackWarning">' . sprintf(WARNING_SPAM_REMOVED, '<b>[' . $field_ip . ']</b>') . '</div>';

            if( DEFAULT_HELPDESK_DELETE_EMAILS != 'true' ) {
              imap_delete($conn, $i);
            }
            continue;
          }

          //-MS- Check Attachments
          $parts_array = array();
          $attachments_array = array();
          //see if there are any parts
          if( isset($mail_struct->parts) && is_array($mail_struct->parts) ) {
            foreach ($mail_struct->parts as $partno => $partarr) {
              //parse parts of email
              help_desk_parsepart($partarr, $partno+1, $conn, $i, $parts_array, $attachments_array);

              //if( isset($parts_array[$partno+1]['attachment']) && !in_array($parts_array[$partno+1]['attachment']['filename'], $attachments_array) ) {
              //  $attachments_array[] = $parts_array[$partno+1]['attachment']['filename'];
              //}
              //$attachment = help_desk_parsepart($partarr, $partno+1, $conn, $i, $parts_array);
              //if( is_string($attachment) && !in_array($attachment, $attachments_array) ) {
                //$attachments_array[] = help_desk_parsepart($partarr, $partno+1, $conn, $i, $parts_array);
              //}
            }
          }
          //-MS- Check Attachments EOM

          // Make sure we try had to have a valid from email address
          if( !empty($output->headers['from'])) {
		    if( is_array($output->headers['from']) ) {
              $tmp_string = '';
			  foreach( $output->headers['from'] as $value ) {
			    $tmp_string .= $value . ' ';
			  }
              $tmp_string = trim($tmp_string);
			  $output->headers['from'] = $tmp_string;
            }

            if( preg_match('/\"([^"]+)\" \<([^>]+)\>/', $output->headers['from'], $regs)) {
              $field_from = trim($regs[1]);
              $field_from_email_address = trim($regs[2]);
            } elseif( preg_match('/([^<]+)<([^>]+)>/', $output->headers['from'], $regs)) {
              $field_from = trim($regs[1]);
              $field_from_email_address = trim($regs[2]);
            } elseif (substr($output->headers['from'], 0, 1) == '<') {
              $field_from = substr($output->headers['from'], 1, -1);
              $field_from_email_address = $field_from;
            } else {
              $field_from = $output->headers['from'];
              $field_from_email_address = $field_from;
            }
            if( empty($field_from) ) $field_from = 'empty';
          }
          
          // Also try hard to fill in the to email address properly
          if( !empty($output->headers['to']) ) {
		    if( is_array($output->headers['to']) ) {
              $tmp_string = '';
              foreach( $output->headers['to'] as $value ) {
                $tmp_string .= $value . ' ';
              }
              $tmp_string = trim($tmp_string);
			  $output->headers['to'] = $tmp_string;
			}
            if( preg_match('/\"([^"]+)\" \<([^>]+)\>/', $output->headers['to'], $regs)) {
              $field_to = trim($regs[1]);
              $field_to_email_address = trim($regs[2]);
            } elseif( preg_match('/([^<]+)<([^>]+)>/', $output->headers['to'], $regs)) {
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
          $ticket_id = $parent_id = 0;
          $status_id = DEFAULT_HELPDESK_STATUS_ID;
          $priority_id = DEFAULT_HELPDESK_PRIORITY_ID;
          if( isset($output->headers['x-priority']) ) {
            $xpriority = (int)$output->headers['x-priority'];
            $search_priority = 'medium';
            if( $xpriority < 3 ) {
              $search_priority = 'high';
            } elseif( $xpriority > 3 ) {
              $search_priority = 'low';
            }
            $priority_query = $g_db->query("select priority_id from " . TABLE_HELPDESK_PRIORITIES . " where title like '%" . $search_priority . "%'");
            if( $g_db->num_rows($priority_query) ) {
              $priority_array = $g_db->fetch_array($priority_query);
              $priority_id = $priority_array['priority_id'];
            }
          }
          
          $department_id = $account['department_id'];
          $field_message_id = '';
          if( isset($output->headers['message-id']) ) {
            $field_message_id = $g_db->prepare_input($output->headers['message-id']);
          }
          $field_subject = $g_db->prepare_input($output->headers['subject'], true);
          if( empty($field_subject) ) {
            $field_subject = TEXT_INFO_NO_SUBJECT;
          }
          // check if the email already in the database
          //$existing_query = $g_db->query("select message_id from " . TABLE_HELPDESK_ENTRIES . " where message_id='" . $g_db->input($field_message_id) . "'");
          //if(!$g_db->num_rows($existing_query)) {
            // check for existing ticket number
            $ticket = '';
            if( !empty($ticket_prefix) && preg_match('/^.*\['.$ticket_prefix.'([A-Z0-9]{7})\].*$/', $field_subject, $regs)) {
              $ticket = $g_db->prepare_input($regs[1], true);
              //$ticket_info_query = $g_db->query("select he.ticket_id. he.parent_id, ht.department_id, ht.status_id, ht.priority_id from " . TABLE_HELPDESK_ENTRIES . " he left join " . TABLE_HELPDESK_TICKETS . " ht on (he.ticket = ht.ticket) where he.ticket = '" . $g_db->input($ticket) . "' order by he.parent_id desc limit 1");
              $ticket_info_query = $g_db->query("select ticket_id, department_id, status_id, priority_id from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $g_db->input($ticket) . "'");
              // if we find one, setup as our parent ticket (limit 1 w/sort ensures this is correct)
              if( $g_db->num_rows($ticket_info_query) ) {
                $ticket_info = $g_db->fetch_array($ticket_info_query);
                $entries_query = $g_db->query("select parent_id from " . TABLE_HELPDESK_ENTRIES . " order by parent_id desc limit 1");
                $entries_array = $g_db->fetch_array($entries_query);

                $parent_id = $entries_array['parent_id'];
                $ticket_id = $ticket_info['ticket_id'];
                $status_id = $ticket_info['status_id'];
                $priority_id = $ticket_info['priority_id'];
                $department_id = $ticket_info['department_id'];
              }
            } elseif( !empty($ticket_prefix) ) {       
              // Create a unique ticked ID and make sure it's available
              do {
                // create & check for dups until unique
                //$ticket = osc_create_random_string();
                $ticket = tep_create_random_value(7, 'mixed_upper', true);
                $check_query = $g_db->query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where ticket = '" .  $g_db->input($ticket) . "'");
                $check = $g_db->fetch_array($check_query);
              } while($check['count']);
            }

            $count_array = array('total' => 0);
            if( !empty($ticket_prefix) ) {
              // how we insert the ticked depends on whether or not this is the first ticket
              $count_query = $g_db->query("select count(*) as total from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $g_db->input($ticket) . "'");
              $count_array = $g_db->fetch_array($check_query);
            }
            
            // if this is the first ticket, we need to setup the "master" ticket item for tracking purposes          
            if( !$count_array['total'] ) {
              $sql_data_array = array(
                'ticket' => $ticket,
                'department_id' => (int)$department_id,
                'priority_id' => $priority_id,
                'status_id' => DEFAULT_HELPDESK_STATUS_ID,
                'datestamp_last_entry' => 'now()',
              );
              $g_db->perform(TABLE_HELPDESK_TICKETS, $sql_data_array);
              $ticket_id = $g_db->insert_id();
              $ticket_string = !empty($ticket)?'<b>[' . $ticket . ']</b> - ':'';
              echo '<div class="linepad">' . sprintf(SUCCESS_MESSAGE_NEW, $ticket_string . $field_subject) . '</div>';
            }

            $sql_data_array = array(
              'ticket_id' => (int)$ticket_id,
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
              'body' => $g_db->prepare_input($field_html_body),
              'text_body' => $g_db->prepare_input($field_text_body),
              'entry_read' => '0',
              'headers' => $headers_string,
            );

            $g_db->perform(TABLE_HELPDESK_ENTRIES, $sql_data_array);
            $helpdesk_entry_id = $g_db->insert_id();

            $parent_string = '';
            if( $parent_id > 0 ) {
              $parent_string = $parent_id . ' - ';
            }

            for($x = 0, $y = count($attachments_array); $x<$y; $x++ ) {
              $sql_data_array = array(
                'helpdesk_entries_id' => $helpdesk_entry_id,
                'attachment' => $g_db->prepare_input($attachments_array[$x]),
              );
              $g_db->perform(TABLE_HELPDESK_ATTACHMENTS, $sql_data_array);
            }

            if( $count_array['total'] ) {
              $ticket_string = !empty($ticket)?'<b>[' . $ticket . ']</b> - ':'';
              echo '<div class="linepad">' . sprintf(SUCCESS_MESSAGE_UPDATE, $ticket_string . $parent_string . $field_subject) . '</div>';
            }
          //}
        }
      } else {
        echo '<div class="linepad">' . sprintf(TEXT_INFO_NO_MESSAGES, '<b>' . $department . '</b>') . '</div>';
      }
      // Cleanup errros
      $error_array[$department] = imap_errors();
      if( $mode == CL_EXPUNGE ) {
        imap_close($conn, $mode);
      } else {
        imap_close($conn);
      }
    } else {
      echo '<div class="messageStackError">' . sprintf(ERROR_CONNECTION_DETAILS, $department, imap_last_error()) . '</div>';
    }
  }
  // Cleanup errros
  $error_array = imap_errors();
?>
            </div>
            <div class="listArea splitLine">
              <div><?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, min(1, $rows), $rows, $rows); ?></div>
            </div>

          </div>
<?php require(DIR_FS_INCLUDES . 'objects/html_end.php'); ?>
