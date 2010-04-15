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

  if ($selected_box == 'plugins') {
    $box_plugins_string = '';
    $box_plugins_query = $g_db->query("select plugins_name, plugins_key from " . TABLE_PLUGINS);
    while($box_plugins_array = $g_db->fetch_array($box_plugins_query) ) {
      $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_PLUGINS, 'selected_box=plugins&plgID=' . $box_plugins_array['plugins_key']) . '">' . $box_plugins_array['plugins_name'] . '</a>');
    }
    $heading_class = 'class="menuBoxHeading menuBoxLit"';
  }
  $heading[] = array('text'  => BOX_HEADING_PLUGINS,
                     'link'  => tep_href_link(FILENAME_PLUGINS, 'selected_box=plugins'));

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);
?>
