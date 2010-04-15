<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Abstract Zones Box
// Controls Content Relationships Display Box
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

  $box_title = BOX_HEADING_ABSTRACT_ZONES;

  if ($selected_box == 'abstract_config') {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_GENERIC_TEXT, 'selected_box=abstract_config') . '">' . BOX_ABSTRACT_GENERIC_TEXT . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'selected_box=abstract_config') . '">' . BOX_ABSTRACT_ZONES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES_CONFIG, 'selected_box=abstract_config') . '">' . BOX_ABSTRACT_CONFIG . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_ABSTRACT_TYPES, 'selected_box=abstract_config') . '">' . BOX_ABSTRACT_TYPES . '</a>');

    // Hook to call plugins
    $g_plugins->invoke('abstract_box');
    $heading_class = 'class="menuBoxHeading menuBoxLit"';
  }

  $heading[] = array(
                     'text'  => $box_title,
                     'link'  => tep_href_link(FILENAME_ABSTRACT_ZONES, 'selected_box=abstract_config')
                    );

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
