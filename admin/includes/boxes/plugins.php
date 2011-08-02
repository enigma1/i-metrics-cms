<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Plugins Box
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

  $box_title = BOX_HEADING_PLUGINS;
  $box_id = 'plugins_box';

  if( $selected_box == $box_id ) {

    $contents[] = array(
      'text' => '<div>',
      'class' => 'leftBoxSection'
    );
    $box_plugins_array = $db->query_to_array("select plugins_name, plugins_key from " . TABLE_PLUGINS);
    for( $i=0, $j=count($box_plugins_array); $i<$j; $i++) {
      $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_PLUGINS, 'plgID=' . $box_plugins_array[$i]['plugins_key']) . '">' . $box_plugins_array[$i]['plugins_name'] . '</a>');
    }
    $contents[] = array(
      'text'  => '</div>'
    );
    $heading_class = 'class="menuBoxHeading menuBoxLit"';
  }
  $heading[] = array(
    'text'  => $box_title,
    'link'  => tep_href_link(FILENAME_PLUGINS, 'selected_box=' . $box_id)
  );

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
