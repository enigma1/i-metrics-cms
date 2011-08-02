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

  $box_title = BOX_HEADING_CACHE;
  $box_id = 'cache_box';

  if ($selected_box == $box_id) {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_CACHE_REPORTS) . '">' . BOX_CACHE_REPORTS . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_CACHE_HTML) . '">' . BOX_CACHE_HTML . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_CACHE_CONFIG) . '">' . BOX_CACHE_CONFIG . '</a>');
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
    'text'  => BOX_HEADING_CACHE,
    'link'  => tep_href_link(FILENAME_CACHE_REPORTS, 'selected_box=' . $box_id)
  );
  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
