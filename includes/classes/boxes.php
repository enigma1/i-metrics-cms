<?php
/*
  $Id: boxes.php,v 1.33 2003/06/09 22:22:50 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Box Classes
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - Converted tables into DIVs
// - Removed unused classes and added new ones for the new stylesheet
// - Changed arguments order to simplify adding messages
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  class commonBox {
    var $common_border = '0';
    var $common_width = '100%';
    var $common_parameters = '';
    var $common_row_parameters = '';
    var $common_data_parameters = '';

    // class constructor
    function commonBox($contents, $direct_output = false) {
      $commonBox_string = '';
      if( !empty($this->common_parameters)) {
        $commonBox_string .= '<div ' . $this->common_parameters;
        $commonBox_string .= '>' . "\n";
      }

      for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
        if (isset($contents[$i]['form']) && tep_not_null($contents[$i]['form'])) {
          $commonBox_string .= $contents[$i]['form'] . "\n";
        }

        if (isset($contents[$i][0]) && is_array($contents[$i][0]) && count($contents[$i][0]) ) {
          for ($x=0, $n2=sizeof($contents[$i]); $x<$n2; $x++) {
            if (isset($contents[$i][$x]['text']) && tep_not_null($contents[$i][$x]['text'])) {
              $commonBox_string .= '    <div';
              if (isset($contents[$i][$x]['params']) && tep_not_null($contents[$i][$x]['params']) ) {
                if( strpos($contents[$i][$x]['params'], 'class') === false ) {
                  $commonBox_string .= ' style="' . $contents[$i][$x]['params'] . '"';
                } else {
                  $commonBox_string .= ' ' . $contents[$i][$x]['params'];
                }
              }
              $commonBox_string .= '>';
              if (isset($contents[$i][$x]['form']) && tep_not_null($contents[$i][$x]['form'])) $commonBox_string .= $contents[$i][$x]['form'];
              $commonBox_string .= $contents[$i][$x]['text'];
              if (isset($contents[$i][$x]['form']) && tep_not_null($contents[$i][$x]['form'])) $commonBox_string .= '</form>';
              $commonBox_string .= '</div>' . "\n";
            }
          }
        } else {
          $commonBox_string .= '    <div';
          if( isset($contents[$i]['params']) && tep_not_null($contents[$i]['params']) ) {
            if( strpos($contents[$i]['params'], 'class') === false ) {
              $commonBox_string .= ' style="' . $contents[$i]['params'] . '"';
            } else {
              $commonBox_string .= ' ' . $contents[$i]['params'];
            }
          }
          $commonBox_string .= '>' . $contents[$i]['text'] . '</div>' . "\n";
        }

        if (isset($contents[$i]['form']) && tep_not_null($contents[$i]['form'])) $commonBox_string .= '</form>' . "\n";
      }
      if( !empty($this->common_parameters)) {
        $commonBox_string .= '</div>' . "\n";
      }
      if ($direct_output == true) echo $commonBox_string;
      return $commonBox_string;
    }
  }

  class contentBox extends commonBox {
    function contentBox($contents, $class='') {

      if( empty($class) ) {
        $this->common_parameters = 'class="contentBoxContents"';
      }
      for($i=0, $j=count($contents); $i<$j; $i++) {
        if( !isset($contents[$i]['params']) ) {
          $contents[$i]['params'] = 'class="' . $class . '"';
        }
      }
      $this->commonBox($contents, true);
    }

    function contentBoxContents($contents, $class) {
      $this->common_data_parameters = 'class="' . $class . '"';
      return $this->commonBox($contents);
    }
  }

  class contentBoxHeading extends commonBox {
    function contentBoxHeading($contents, $class='contentBoxHeading') {
      $this->common_width = '100%';

      $info_box_contents = array();
      $info_box_contents[] = array(array(
        'params' => 'class="' . $class . '"',
        'text' => $contents[0]['text']
      ));

      $this->commonBox($info_box_contents, true);
    }
  }


  class errorBox extends commonBox {
    function errorBox($contents) {
      $this->common_data_parameters = 'class="errorBox"';
      $this->commonBox($contents, true);
    }
  }

  class noticeBox extends commonBox {
    function noticeBox($contents) {

      $this->common_width = '100%';

      $info_box_contents = array();
      $info_box_contents[] = array(array(
        'params' => $contents['params'],
        'text' => $contents['text']
      ));
      $this->commonBox($info_box_contents, true);
    }
  }
?>
