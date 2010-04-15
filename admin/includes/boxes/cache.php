<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Cache Manager for osC Admin
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  $heading = array();
  $contents = array();
  $heading_class = 'class="menuBoxHeading"';

  if ($selected_box == 'cache') {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_CACHE_CONFIG, '', 'NONSSL') . '">' . BOX_CACHE_CONFIG . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_CACHE_HTML, '', 'NONSSL') . '">' . BOX_CACHE_HTML . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_CACHE_REPORTS, '', 'NONSSL') . '">' . BOX_CACHE_REPORTS . '</a>');

    $heading_class = 'class="menuBoxHeading menuBoxLit"';
  }

  $heading[] = array('text'  => BOX_HEADING_CACHE,
                     'link'  => tep_href_link(FILENAME_CACHE_CONFIG, 'selected_box=cache'));

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
