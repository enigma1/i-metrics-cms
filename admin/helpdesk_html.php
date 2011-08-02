<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Helpdesk HTML view
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
  $he_id = (isset($_GET['he_id']) ? (int)$_GET['he_id'] : '');
  $unsafe = (isset($_GET['unsafe']) ? (int)$_GET['unsafe'] : 0);

  $main_body = '';
  $entry_query = $g_db->query("select helpdesk_entries_id, headers, body, text_body from " . TABLE_HELPDESK_ENTRIES . " where helpdesk_entries_id = '" . (int)$he_id . "'");
  if( $g_db->num_rows($entry_query) ) {
    header('Content-Type: text/html; charset=' . CHARSET);
    $entry_array = $g_db->fetch_array($entry_query);

    if( !empty($entry_array['body']) ) {
      $main_body .= $entry_array['body'];
    } else {
      $main_body .= $entry_array['text_body'];
    }

    if( $unsafe == 1 ) {
      echo $main_body;
    } else {
      $contents = '';
      $attachments_query = $g_db->query("select attachment from " . TABLE_HELPDESK_ATTACHMENTS . " where helpdesk_entries_id= '" . (int)$he_id . "'");
      if( $g_db->num_rows($attachments_query) ) {
        while( $attachments_array = $g_db->fetch_array($attachments_query) ) {
          $file = HELPDESK_ATTACHMENTS_FOLDER . $attachments_array['attachment'];
          $tmp_contents = '';
          tep_read_contents($file, $tmp_contents);
          if( !empty($tmp_contents) ) {
            $contents .= chunk_split(base64_encode($tmp_contents));
          }
        }
      }

      $tmp_array = explode("\n", $entry_array['headers']);
      for($i=0, $dindex=-1, $j=count($tmp_array); $i<$j; $i++) {
        if( $dindex == -1 ) {
          $pos = strpos($tmp_array[$i], 'Delivered-To: ');
          if( $pos !== false ) {
            $dindex = $i;
          }
        }

        $pos = strpos($tmp_array[$i], 'Received: ');
        if( $pos !== false ) {
          if(preg_match_all('([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)', $tmp_array[$i], $regs)) {
            $scan_array = $regs[0];
            $ip = array_pop($scan_array);
            $ip = $g_db->prepare_input($ip, true);
            if( $_SERVER['SERVER_ADDR'] == $ip ) continue;

            $ip_array = explode('.', $ip);
            if( (int)$ip_array[0] == 127 ) {
              $dindex = -1;
            } else {
              break;
            }
          }
        }
      }

      if( $dindex > 0 ) {
        $tmp_array = array_slice($tmp_array, $dindex);
        $entry_array['headers'] = implode("\n", $tmp_array);
      }

      $entry_array['headers'] = trim($entry_array['headers']);
      $message = $entry_array['headers'] . "\r\n\r\n";

      $message .= $main_body;
      if( !empty($contents) ) {
        $message .= "\r\n\r\n" . $contents;
      }
      echo tep_draw_textarea_field('message', $message, 80, 20);
    }
  }
?>
<?php require(DIR_FS_INCLUDES . 'application_bottom.php'); ?>