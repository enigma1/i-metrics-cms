<?php
/*
  $Id: box.php,v 1.7 2003/06/20 16:23:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License

  Example usage:

  $heading = array();
  $heading[] = array('params' => 'class="menuBoxHeading"',
                     'text'  => BOX_HEADING_TOOLS,
                     'link'  => tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('selected_box')) . 'selected_box=tools'));

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
      $this->common_row_parameters = 'class="infoBoxHeading"';
      $this->common_data_parameters = 'class="infoBoxHeading"';
      $this->heading = $this->commonBlock($heading);

      $this->common_row_parameters = '';
      $this->common_data_parameters = 'class="infoBoxContent"';
      $this->contents = $this->commonBlock($contents);

      return $this->heading . $this->contents;
    }

    function menuBox($heading, $contents, $hClass='class="menuBoxHeading"', $cClass='class="menuBoxContent"') {
      $this->common_data_parameters = $hClass;
      if (isset($heading[0]['link'])) {
        $heading[0]['text'] = '<a href="' . $heading[0]['link'] . '">' . $heading[0]['text'] . '</a>';
      } else {
        $heading[0]['text'] = '<a href="#">' . $heading[0]['text'] . '</a>';
      }
      $this->heading = $this->commonBlock($heading);

      $this->common_data_parameters = $cClass;
      $this->contents = $this->commonBlock($contents);

      return $this->heading . $this->contents;
    }

    function noticeBox($contents) {
      $this->common_cellpadding = '0';
      $this->common_data_parameters = $contents['params'];

      $info_box_contents = array(
                                  array(
                                        'text' => $contents['text']
                                       )
                                );

      $this->commonBlock($info_box_contents, true);
    }
  }
?>
