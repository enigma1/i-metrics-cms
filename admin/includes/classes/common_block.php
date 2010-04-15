<?php
/*
  $Id: table_block.php,v 1.8 2003/06/20 15:51:18 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class commonBlock {
    var $common_border = '0';
    var $common_width = '100%';
    var $common_cellspacing = '0';
    var $common_cellpadding = '2';
    var $common_parameters = '';
    var $common_row_parameters = '';
    var $common_data_parameters = '';


// class constructor
    function commonBlock($contents, $direct_output = false) {
      //$commonBox_string = '<div';
      $commonBox_string = '';
      $form_flag = false;
      $form_start = '<form';
      $form_end = '</form';
      //if (tep_not_null($this->common_parameters)) $commonBox_string .= ' ' . $this->common_parameters;
      //$commonBox_string .= '>' . "\n";

      for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
        if( !$form_flag && isset($contents[$i]['form']) && tep_not_null($contents[$i]['form']) && substr($contents[$i]['form'], 0, strlen($form_start)) == $form_start ) {
          $commonBox_string .= $contents[$i]['form'] . "\n";
          $form_flag = true;
          continue;
        } elseif( $form_flag && isset($contents[$i]['form']) && tep_not_null($contents[$i]['form']) && substr($contents[$i]['form'], 0, strlen($form_end)) == $form_end ) {
          $commonBox_string .= '</form>' . "\n";
          $form_flag = false;
        }
/*
        $commonBox_string .= '  <div';
        if (tep_not_null($this->common_row_parameters)) $commonBox_string .= ' ' . $this->common_row_parameters;
        if (isset($contents[$i]['div_params']) && tep_not_null($contents[$i]['div_params'])) {
          $commonBox_string .= ' style="' . $contents[$i]['div_params'] . '"';
        }
        $commonBox_string .= '>' . "\n";
*/
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
              } elseif (tep_not_null($this->common_data_parameters)) {
                $commonBox_string .= ' ' . $this->common_data_parameters;
              }
              $commonBox_string .= '>';
              //if (isset($contents[$i][$x]['form']) && tep_not_null($contents[$i][$x]['form'])) $commonBox_string .= $contents[$i][$x]['form'];
              //$commonBox_string .= $contents[$i][$x]['text'];
              //if (isset($contents[$i][$x]['form']) && tep_not_null($contents[$i][$x]['form'])) $commonBox_string .= '</form>';
              $commonBox_string .= '</div>' . "\n";
            }
          }
        } else {
if( empty($contents[$i]['text']) ) {
  $contents[$i]['text'] = '&nbsp;';
}
          $commonBox_string .= '    <div';

          if (isset($contents[$i]['params']) && tep_not_null($contents[$i]['params'])) {
            if( strpos($contents[$i]['params'], 'class') === false ) {
              $commonBox_string .= ' style="' . $contents[$i]['params'] . '"';
            }
          }
          if (tep_not_null($this->common_data_parameters)) {
            $commonBox_string .= ' ' . $this->common_data_parameters;
          }
          $commonBox_string .= '>' . $contents[$i]['text'] . '</div>' . "\n";
        }

        //$commonBox_string .= '  </div>' . "\n";
        //if (isset($contents[$i]['form']) && tep_not_null($contents[$i]['form'])) $commonBox_string .= '</form>' . "\n";
      }

      if( $form_flag ) {
        $commonBox_string .= '</form>' . "\n";
      }

      //$commonBox_string .= '</div>' . "\n";
      if ($direct_output == true) echo $commonBox_string;
      return $commonBox_string;
    }

  }
?>
