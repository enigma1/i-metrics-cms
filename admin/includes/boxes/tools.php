<?php
/*
  $Id: tools.php,v 1.21 2003/07/09 01:18:53 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/
  $heading = array();
  $contents = array();
  $heading_class = 'class="menuBoxHeading"';

  if ($selected_box == 'tools') {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_BACKUP) . '" class="menuBoxContentLink">' . BOX_TOOLS_BACKUP . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_MULTI_SITES) . '" class="menuBoxContentLink">' . BOX_TOOLS_MULTI_SITES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_FORM_FIELDS) . '" class="menuBoxContentLink">' . BOX_TOOLS_FORM_FIELDS . '</a>');
    //$contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_FILE_MANAGER) . '" class="menuBoxContentLink">' . BOX_TOOLS_FILE_MANAGER . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_WHOS_ONLINE) . '" class="menuBoxContentLink">' . BOX_TOOLS_WHOS_ONLINE . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_TOTAL_CONFIGURATION) . '" class="menuBoxContentLink">' . BOX_TOOLS_TOTAL_CONFIGURATION . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_SERVER_INFO) . '" class="menuBoxContentLink">' . BOX_TOOLS_SERVER_INFO . '</a>');

    $heading_class = 'class="menuBoxHeading menuBoxLit"';
  }

  $heading[] = array('text'  => BOX_HEADING_TOOLS,
                     'link'  => tep_href_link(FILENAME_WHOS_ONLINE, 'selected_box=tools'));

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
