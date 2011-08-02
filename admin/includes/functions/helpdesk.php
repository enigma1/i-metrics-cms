<?php
/*
// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// - Added Attachments support
// - Added parts loop with array check for the osc_parse_mime_decode_output
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
  function tep_check_ip_blacklist($ip, $service_domain) {
    $ip = trim($ip);
    $info_array = array();
    $lookup = implode('.', array_reverse(explode  ('.', $ip ))) . '.' . $service_domain;
    $rIP = gethostbyname($lookup);
    $result = explode( '.', $rIP);
    if( $result[0] == 127 ) {
      $info_array = array($service_domain => $rIP);
    }
    return $info_array;
  }

  function parse_output(&$obj, &$parts, $i) {
    $ctype = $obj->ctype_primary . '/' . $obj->ctype_secondary;

    switch ($ctype) {
      case 'text/plain':
        if( !empty($obj->disposition) && $obj->disposition == 'attachment') {
          $names = preg_split('/\;/', $obj->headers["content-disposition"]);
  
          $names = preg_split('/\=/', $names[1]);
          $aux['name'] = $names[1];
          $aux['content-type'] = $obj->headers["content-type"];
          $aux['part'] = $i;
          $parts['attachments'][] = $aux;
        } elseif( isset($obj->body) ) {
          $parts['text'][] = $obj->body;
        }
        break;
      case 'text/html':
        if( !empty($obj->disposition) && $obj->disposition == 'attachment') {
          $names = preg_split('/\;/', $obj->headers["content-disposition"]);
          $names = preg_split('/\=/', $names[1]);

          $aux['name'] = $names[1];
          $aux['content-type'] = $obj->headers["content-type"];
          $aux['part'] = $i;
          $parts['attachments'][] = $aux;
        } elseif( isset($obj->body) ) {
          $parts['html'][] = $obj->body;
        }
        break;
      default:
        break;
    }
  }  
  
  function osc_parse_mime_decode_output(&$obj, &$parts){
    if (!empty($obj->parts)) {
      for ($i=0; $i<count($obj->parts); $i++) {
        if( isset($obj->parts[$i]->parts) && count($obj->parts[$i]->parts) > 0 ) {
          osc_parse_mime_decode_output($obj->parts[$i], $parts);
        } else {
          parse_output($obj->parts[$i], $parts, $i);
        }
      }
    } elseif( isset($obj->body) ) {
      $ctype = $obj->ctype_primary.'/'.$obj->ctype_secondary;
      switch ($ctype) {
        case 'text/plain':
          if (!empty($obj->disposition) AND $obj->disposition == 'attachment') {
            $parts['attachments'][] = $obj->body;
          } else {
            $parts['text'][] = $obj->body;
          }
          break;
        case 'text/html':
          if (!empty($obj->disposition) AND $obj->disposition == 'attachment') {
            $parts['attachments'][] = $obj->body;
          } else {
            $parts['html'][] = $obj->body;
          }
          break;
        default:
          $parts['attachments'][] = $obj->body;
          break;
      }
    }
  }

  function helpdesk_decode_string($input) {
    // Remove white space between encoded-words
    $input = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $input);

    // For each encoded-word...
    while (preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $input, $matches)) {

      $encoded  = $matches[1];
      $charset  = strtoupper($matches[2]);
      $encoding = $matches[3];
      $text     = $matches[4];

      switch (strtolower($encoding)) {
        case 'b':
          $text = base64_decode($text);
          break;

        case 'q':
          $text = str_replace('_', ' ', $text);
          preg_match_all('/=([a-f0-9]{2})/i', $text, $matches);
          foreach($matches[1] as $value) {
            $text = str_replace('='.$value, chr(hexdec($value)), $text);
          }
          break;
      }
      if( $charset != CHARSET ) {
        $text = iconv($charset, CHARSET . '//IGNORE//TRANSLIT', $text);
      }
      $input = str_replace($encoded, $text, $input);
    }
    return $input;
  }


//-MS- Attachments Support
  function help_desk_parsepart($p, $i, $link, $msgid, &$partsarray, &$attachments_array) {
    //global $link, $msgid,$partsarray;

    //where to write file attachments to:
    $filestore = DIR_FS_ADMIN . HELPDESK_ATTACHMENTS_FOLDER;

    //fetch part
    $part=imap_fetchbody($link,$msgid,$i);
    //if type is not text
    if ($p->type != 0 || ($p->type == 0 && isset($p->disposition)) ) {
    //if ($p->type != 0 ) {

      //DECODE PART
      switch($p->encoding) {
        case 3:
          //decode if base64
          $part = base64_decode($part);
          break;
        case 4:
          //decode if quoted printable
          $part = quoted_printable_decode($part);
          break;
        default:
          //no need to decode binary or 8bit!
          break;
      }

      //get filename of attachment if present
      $filename = '';

      // if there are any dparameters present in this part
      if( isset($p->dparameters) && is_array($p->dparameters) && count($p->dparameters) > 0 ) {
        foreach ($p->dparameters as $dparam) {
          if( strtoupper($dparam->attribute)=='NAME' || strtoupper($dparam->attribute)=='FILENAME' ) {
            $filename = $dparam->value;
          }
        }
      }
      //if no filename found
      if( $filename == '' ) {
        // if there are any parameters present in this part
        if( isset($p->parameters) && is_array($p->parameters) && count($p->parameters) > 0 ) {
          foreach( $p->parameters as $param ) {
            if( strtoupper($param->attribute)=='NAME' || strtoupper($param->attribute)=='FILENAME' ) {
              $filename = $param->value;
            }
          }
        }
      }

      //write to disk and set partsarray variable
      if( $filename != '') {

        $filename = helpdesk_decode_string($filename);
        $filename = basename(strtolower($filename));
        $filename = tep_create_safe_string($filename, '-', '/[^0-9a-z_\-\.]+/');

        if( strlen($filename) < 5 ) {
          $filename = tep_create_random_value(32, 'mixed_lower');
        }

        $partsarray[$i]['attachment'] = array(
          'filename' => $filename, 
          'binary' => $part
        );

        $index = 0;
        $org_filename = $filename;
        while( is_file($filestore.$filename) ) {
          $index++;
          $filename = 'copy' . $index . '-' . $org_filename;
        }

        $fp=fopen($filestore.$filename,"w+");
        if( !$fp ) { 
          echo '<div class="messageStackError">' . sprintf(ERROR_WRITE_ATTACHMENT, $filestore) . '</div>';
        } else {
          echo '<div class="linepad heavy">' . sprintf(TEXT_INFO_ATTACHMENT_WRITE, $filestore . $filename) . '</div>';
          fwrite($fp,$part);
          fclose($fp);
          if( !in_array($filename, $attachments_array) ) {
            $attachments_array[] = $filename;
          }
        } 
      }
    //end if type!=0        
    //elseif part is text
    } elseif( $p->type == 0 ) {
      //decode text
      //if QUOTED-PRINTABLE
      if($p->encoding==4) 
        $part=quoted_printable_decode($part);
      //if base 64
      if($p->encoding==3) 
        $part=base64_decode($part);
      //OPTIONAL PROCESSING e.g. nl2br for plain text
      //if plain text
      if (strtoupper($p->subtype)=='PLAIN') {
        1;
      //if HTML
      } elseif (strtoupper($p->subtype)=='HTML') {
        1;
      }
      $partsarray[$i]['text'] = array(
        'type' => $p->subtype,
        'string' => $part
      );
    }
    
    //if subparts... recurse into function and parse them too!
    if( isset($p->parts) && count($p->parts) > 0 ){
      foreach ($p->parts as $pno => $parr){
        help_desk_parsepart($parr, ($i.'.'.($pno+1)), $link, $msgid, $partsarray, $attachments_array);
      }
    }
    return;
  }
//-MS- Attachment support EOM
?>