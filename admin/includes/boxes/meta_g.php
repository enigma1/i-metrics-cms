<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: META-G box
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  $heading = array();
  $contents = array();
  $heading_class = 'class="menuBoxHeading"';

  if ($selected_box == 'meta_config') {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_META_ZONES_CONFIG, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_META_CONFIG . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_META_TYPES, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_META_TYPES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_META_ZONES, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_META_ZONES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_META_LEXICO, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_META_LEXICO . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_META_EXCLUDE, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_META_EXCLUDE . '</a>');

    $heading_class = 'class="menuBoxHeading menuBoxLit"';
  }

  $heading[] = array('text'  => BOX_HEADING_META_ZONES,
                     'link'  => tep_href_link(FILENAME_META_ZONES_CONFIG, 'selected_box=meta_config'));

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
