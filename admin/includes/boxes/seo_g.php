<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: SEO-G box
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

  if ($selected_box == 'seo_config') {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_SEO_ZONES_CONFIG, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_SEO_CONFIG . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_SEO_TYPES, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_SEO_TYPES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_SEO_ZONES, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_SEO_ZONES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_SEO_EXCLUDE, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_SEO_EXCLUDE . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_SEO_REPORTS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_SEO_REPORTS . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_SEO_REDIRECTS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_SEO_REDIRECTS . '</a>');

    $heading_class = 'class="menuBoxHeading menuBoxLit"';
  }

  $heading[] = array('text'  => BOX_HEADING_SEO_ZONES,
                     'link'  => tep_href_link(FILENAME_SEO_ZONES_CONFIG, 'selected_box=seo_config'));

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
