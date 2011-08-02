<?php
/*
  $Id: table_block.php,v 1.8 2003/06/20 15:51:18 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: common box class
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - Ported for the I-Metrics CMS
// - Converted tables to divs
// - Added optional sections
// - Added support for class, style, param arguments
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  class commonBlock {
    var $common_border = '0';
    var $common_width = '100%';
    var $common_cellspacing = '0';
    var $common_cellpadding = '2';
    var $common_parameters = '';
    var $common_row_parameters = '';
    var $common_data_parameters = '';
    var $section = false;

    // class constructor
    function commonBlock($contents, $direct_output = false) {
      $form_string = '';
      $commonBox_string = '';
      
      if( empty($contents) ) return $commonBox_string;

      for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
        if (isset($contents[$i]['form']) && tep_not_null($contents[$i]['form'])) {
          $form_string = $contents[$i]['form'] . "\n";
        }

        if (isset($contents[$i][0]) && is_array($contents[$i][0]) && count($contents[$i][0]) ) {
          for ($x=0, $n2=sizeof($contents[$i]); $x<$n2; $x++) {
            $tmp_string = $params_string = '';
            if (isset($contents[$i][$x]['text']) && tep_not_null($contents[$i][$x]['text'])) {
              if( isset($contents[$i][$x]['class']) && !empty($contents[$i][$x]['class']) ) {
                $params_string .= ' class="' . $contents[$i][$x]['class'] . '"';
              } elseif( isset($contents[$i][$x]['style']) && !empty($contents[$i][$x]['style']) ) {
                $params_string .= ' style="' . $contents[$i][$x]['style'] . '"';
              } elseif( isset($contents[$i][$x]['params']) && !empty($contents[$i][$x]['params']) ) {
                $params_string .= ' ' . $contents[$i][$x]['params'] . '"';
              }

              if( !empty($params_string) ) {
                $tmp_string = '<div' . $params_string . '>' . "\n";
                if (isset($contents[$i][$x]['form']) && !empty($contents[$i][$x]['form'])) {
                  $form_string = $contents[$i][$x]['form'];
                }
                if( isset($contents[$i][$x]['text']) ) {
                  $tmp_string .= $contents[$i][$x]['text'] . "\n";
                }
                $tmp_string .= '</div>' . "\n";
              } else {
                $tmp_string =  $contents[$i][$x]['text'];
              }
              $commonBox_string .= $tmp_string;
            }
          }
        } else {
          $tmp_string = $params_string = '';
          if( isset($contents[$i]['style']) && !empty($contents[$i]['style']) ) {
            $params_string .= ' style="' . $contents[$i]['style'] . '"';
          } elseif( isset($contents[$i]['class']) && !empty($contents[$i]['class']) ) {
            $params_string .= ' class="' . $contents[$i]['class'] . '"';
          } elseif( isset($contents[$i]['params']) && !empty($contents[$i]['params']) ) {
            $params_string .= ' ' . $contents[$i]['params'];
          }
          if( isset($contents[$i]['text']) ) {
            if( !empty($params_string) ) {
              $tmp_string = '<div' . $params_string . '>';
              if( $contents[$i]['text'] != '<div>' ) {
                $tmp_string .= "\n" . $contents[$i]['text'] . "\n" . '</div>' . "\n";
              }
            } else {
              $tmp_string =  $contents[$i]['text'];
            }
          }
          $commonBox_string .= $tmp_string;
        }
      }

      if( !empty($form_string)) {
         $commonBox_string = $form_string . $commonBox_string . '</form>';
      }

      if( !empty($this->common_parameters)) {
        $commonBox_string = '<div ' . $this->common_parameters . '>' . $commonBox_string . '</div>' . "\n";
      }

      if ($direct_output == true) echo $commonBox_string;
      return $commonBox_string;
    }

  }
?>
