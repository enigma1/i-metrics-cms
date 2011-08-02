<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Abstract Zones Box
//----------------------------------------------------------------------------
// I-Metrics CMS
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
  $box_id = 'abstract_box';

  if ($selected_box == $box_id) {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_GENERIC_TEXT) . '">' . BOX_ABSTRACT_GENERIC_TEXT . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES) . '">' . BOX_ABSTRACT_ZONES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_ABSTRACT_TYPES) . '">' . BOX_ABSTRACT_TYPES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES_CONFIG) . '">' . BOX_ABSTRACT_CONFIG . '</a>');

    // Hook to call plugins
    $plugin_contents = array();
    $args = array(
      'contents' => &$plugin_contents
    );
    $cPlug->invoke($box_id, $args);

    if( !empty($plugin_contents) ) {
      array_unshift($plugin_contents, array(
        'text' => '<div class="leftBoxSection">',
      ));
      array_push($plugin_contents, array(
        'text'  => '</div>'
      ));
      $contents = array_merge($contents, $plugin_contents);
    }
    $heading_class = 'class="menuBoxHeading menuBoxLit"';
  }

  $heading[] = array(
    'text'  => $box_title,
    'link'  => tep_href_link(FILENAME_GENERIC_TEXT, 'selected_box=' . $box_id)
  );

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
