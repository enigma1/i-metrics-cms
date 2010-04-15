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
  function osc_create_random_string() {
    $ascii_from = 50; // 2
    $ascii_to = 90; // Z
    $exclude = array(58, 59, 60, 61, 62, 63, 64, 73, 79);
    mt_srand((double)microtime() * 1000000);
    $string = '';
    $i = 0;
    while ($i < 7) {
      $randnum = mt_rand($ascii_from, $ascii_to);
      if (!in_array($randnum, $exclude)) {
        $string .= chr($randnum);
        $i++;
      }
    }
    return $string;
  }
  
  function parse_output(&$obj, &$parts, $i) {
    $ctype = $obj->ctype_primary . '/' . $obj->ctype_secondary;
  
    switch ($ctype) {
      case 'text/plain':
        if( !empty($obj->disposition) && $obj->disposition == 'attachment') {
          $names = split(';', $obj->headers["content-disposition"]);
  
          $names = split('=', $names[1]);
          $aux['name'] = $names[1];
          $aux['content-type'] = $obj->headers["content-type"];
          $aux['part'] = $i;
          $parts['attachments'][] = $aux;
        } else {
          $parts['text'][] = $obj->body;
        }
  
        break;
  
      case 'text/html':
        if( !empty($obj->disposition) && $obj->disposition == 'attachment') {
          $names = split(';', $obj->headers["content-disposition"]);
  
          $names = split('=', $names[1]);
          $aux['name'] = $names[1];
          $aux['content-type'] = $obj->headers["content-type"];
          $aux['part'] = $i;
          $parts['attachments'][] = $aux;
        } else {
          $parts['html'][] = $obj->body;
        }
  
        break;
  
      default:
        break;

        $names = split(';', $obj->headers["content-disposition"]);
  
        $names = split('=', $names[1]);
        $aux['name'] = $names[1];
        $aux['content-type'] = $obj->headers["content-type"];
        $aux['part'] = $i;
        $parts['attachments'][] = $aux;
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
    } else {
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


//-MS- Attachments Support
  function help_desk_parsepart($p, $i, $link, $msgid, &$partsarray) {
    //global $link, $msgid,$partsarray;

    //where to write file attachments to:
    $filestore = DIR_FS_ADMIN . HELPDESK_ATTACHMENTS_FOLDER;
    //$filestore = '[full/path/to/attachment/store/(chmod777)]';

    //fetch part
    $part=imap_fetchbody($link,$msgid,$i);
    //if type is not text
    if ($p->type != 0) {
      //DECODE PART
      //decode if base64
      if ($p->encoding==3)
        $part=base64_decode($part);
      //decode if quoted printable
      if ($p->encoding==4)
        $part=quoted_printable_decode($part);
      //no need to decode binary or 8bit!
      
      //get filename of attachment if present
      $filename='';
      // if there are any dparameters present in this part
      if( isset($p->dparameters) && is_array($p->dparameters) && count($p->dparameters) > 0 ) {
        foreach ($p->dparameters as $dparam) {
          if ((strtoupper($dparam->attribute)=='NAME') || (strtoupper($dparam->attribute)=='FILENAME')) 
            $filename = $dparam->value;
        }
      }
      //if no filename found
      if ($filename=='') {
        // if there are any parameters present in this part
        if( isset($p->parameters) && is_array($p->parameters) && count($p->parameters) > 0 ) {
          foreach ($p->parameters as $param) {
            if((strtoupper($param->attribute)=='NAME') || (strtoupper($param->attribute)=='FILENAME')) 
              $filename=$param->value;
          }
        }
      }
      //write to disk and set partsarray variable
      if ($filename!='') {
        $partsarray[$i]['attachment'] = array('filename' => $filename, 'binary' => $part);
        $fp=fopen($filestore.$filename,"w+");
        echo 'Now Saving: ' . $filestore . $filename . '<br />';
        if( !$fp )
          echo 'error: cannot write to the directory specified. Set mode to 777' . '<br />';
        fwrite($fp,$part);
        fclose($fp);
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
      $partsarray[$i]['text'] = array('type' => $p->subtype,
                                      'string' => $part
                                     );
    }
    
    //if subparts... recurse into function and parse them too!
    if( isset($p->parts) && count($p->parts) > 0 ){
      foreach ($p->parts as $pno => $parr){
        help_desk_parsepart($parr, ($i.'.'.($pno+1)), $link, $msgid, $partsarray);
      }
    }
    return;
  }
//-MS- Attachment support EOM
?>