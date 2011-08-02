<?php
/*
  $Id: tools.php,v 1.21 2003/07/09 01:18:53 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Tools Box
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - Converted for the I-Metrics CMS
// - Added plugin hook
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  $heading = array();
  $contents = array();
  $heading_class = 'class="menuBoxHeading"';

  $box_title = BOX_HEADING_TOOLS;
  $box_id = 'tools_box';

  if ($selected_box == $box_id) {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_BACKUP) . '">' . BOX_TOOLS_BACKUP . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_MULTI_SITES) . '">' . BOX_TOOLS_MULTI_SITES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_FILE_MANAGER) . '">' . BOX_TOOLS_FILE_MANAGER . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_FORM_FIELDS) . '">' . BOX_TOOLS_FORM_FIELDS . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_WHOS_ONLINE) . '">' . BOX_TOOLS_WHOS_ONLINE . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_TEMPLATES) . '">' . BOX_TOOLS_TEMPLATES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_TOTAL_CONFIGURATION) . '">' . BOX_TOOLS_TOTAL_CONFIGURATION . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_EXPLAIN_QUERIES) . '">' . BOX_TOOLS_EXPLAIN_QUERIES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_SERVER_INFO) . '">' . BOX_TOOLS_SERVER_INFO . '</a>');

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
    'link'  => tep_href_link(FILENAME_WHOS_ONLINE, 'selected_box=' . $box_id)
  );

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
