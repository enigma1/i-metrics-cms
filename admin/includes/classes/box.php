<?php
/*
  $Id: box.php,v 1.7 2003/06/20 16:23:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Box Class
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - Modified menubox and infobox functions to accept separate parameters
// - Removed js dependencies
// - Changed root class to commonBlock 
// - Added CSS based boxes removed older table-based ones
// - Added noticebox function
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------


  Example usage:

  $heading = array();
  $heading[] = array('params' => 'class="menuBoxHeading"',
                     'text'  => BOX_HEADING_TOOLS,
                     'link'  => tep_href_link($g_script, tep_get_all_get_params('selected_box') . 'selected_box=tools'));

  $contents = array();
  $contents[] = array('text'  => SOME_TEXT);

  $box = new box;
  echo $box->infoBox($heading, $contents);
*/

  class box extends commonBlock {

    function box() {
      $this->heading = array();
      $this->contents = array();
    }

    function infoBox($heading, $contents) {
      $this->common_parameters = 'class="infoBoxHeading"';
      $this->heading = $this->commonBlock($heading);

      $this->common_parameters = 'class="infoBoxContent"';
      for($i=0, $j=count($contents); $i<$j; $i++) {
        //if( count($contents[$i]) < 2 && isset($contents[$i]['text']) ) {
        if( isset($contents[$i]['section']) ) {
          $contents[$i]['text'] = $contents[$i]['section'];
          unset($contents[$i]['section']);
        } elseif( isset($contents[$i]['text']) ) {
          $contents[$i]['text'] = '<div class="infoLine">' . $contents[$i]['text'] . '</div>';
        }
      }
      $this->contents = $this->commonBlock($contents);

      return $this->heading . $this->contents;
    }

    function menuBox($heading, $contents, $hClass='class="menuBoxHeading"', $cClass='class="menuBoxContent"') {
      $this->common_parameters = $hClass;
      if (isset($heading[0]['link'])) {
        $heading[0]['text'] = '<a href="' . $heading[0]['link'] . '">' . $heading[0]['text'] . '</a>';
      } else {
        $heading[0]['text'] = '<a>' . $heading[0]['text'] . '</a>';
      }
      $this->heading = $this->commonBlock($heading);

      $this->common_parameters = $cClass;
      $this->contents = $this->commonBlock($contents);

      return $this->heading . $this->contents;
    }

    function noticeBox($contents) {
      $this->common_cellpadding = '0';
      $this->common_parameters = $contents['params'];

      $info_box_contents = array(array('text' => $contents['text']));

      $this->commonBlock($info_box_contents, true);
    }
  }
?>
