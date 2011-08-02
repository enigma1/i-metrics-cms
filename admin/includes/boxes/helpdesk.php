<?php
/*
  $Id: helpdesk.php,v 1.5 2005/08/16 20:56:39 lane Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Helpdesk Box
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

  $box_title = BOX_HEADING_HELPDESK;
  $box_id = 'helpdesk_box';

  if ($selected_box == $box_id) {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK) . '">' . BOX_HELPDESK_ENTRIES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_DEPARTMENTS) . '">' . BOX_HELPDESK_DEPARTMENTS . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_BOOK) . '">' . BOX_HELPDESK_BOOK . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_STATUS) . '">' . BOX_HELPDESK_STATUSES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_PRIORITIES) . '">' . BOX_HELPDESK_PRIORITIES . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_POP3) . '">' . BOX_HELPDESK_POP3 . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_CONFIG, '', 'NONSSL') . '">' . BOX_HELPDESK_CONFIG . '</a>');

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
    'link'  => tep_href_link(FILENAME_HELPDESK, 'selected_box=' . $box_id)
  );

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
