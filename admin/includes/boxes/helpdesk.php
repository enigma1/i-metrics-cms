<?php
/*
  $Id: helpdesk.php,v 1.5 2005/08/16 20:56:39 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/
  $heading = array();
  $contents = array();
  $heading_class = 'class="menuBoxHeading"';

  if ($selected_box == 'helpdesk') {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK) . '" class="menuBoxContentLink">' . BOX_HELPDESK_ENTRIES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_DEPARTMENTS) . '" class="menuBoxContentLink">' . BOX_HELPDESK_DEPARTMENTS . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_STATUS) . '" class="menuBoxContentLink">' . BOX_HELPDESK_STATUSES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_PRIORITIES) . '" class="menuBoxContentLink">' . BOX_HELPDESK_PRIORITIES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_MAIL) . '" class="menuBoxContentLink">' . BOX_TOOLS_MAIL . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_POP3) . '" class="menuBoxContentLink">' . BOX_HELPDESK_POP3 . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_CONFIG, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_HELPDESK_CONFIG . '</a>');

    $heading_class = 'class="menuBoxHeading menuBoxLit"';
  }

  $heading[] = array('text'  => BOX_HEADING_HELPDESK,
                     'link'  => tep_href_link(FILENAME_HELPDESK, 'selected_box=helpdesk'));

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
