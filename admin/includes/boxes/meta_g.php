<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: META-G box
//----------------------------------------------------------------------------
// I-Metrics CMS
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

  $box_title = BOX_HEADING_META_ZONES;
  $box_id = 'metag_box';

  if ($selected_box == $box_id) {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_META_ZONES_CONFIG) . '">' . BOX_META_CONFIG . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_META_TYPES) . '">' . BOX_META_TYPES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_META_ZONES) . '">' . BOX_META_ZONES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_META_LEXICO) . '">' . BOX_META_LEXICO . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_META_EXCLUDE) . '">' . BOX_META_EXCLUDE . '</a>');

    // Hook to call plugins
    $plugin_contents = array();
    $args = array('contents' => &$plugin_contents );
    $cPlug->invoke($box_id, $args);

    if( !empty($plugin_contents) ) {
      array_unshift($plugin_contents, array(
        'text' => '<div>',
        'class' => 'leftBoxSection'
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
    'link'  => tep_href_link(FILENAME_META_ZONES_CONFIG, 'selected_box=' . $box_id)
  );
  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
