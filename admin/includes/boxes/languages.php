<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Language Navigation Box
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

  $box_title = BOX_HEADING_LANGUAGES;
  $box_id = 'language_box';

  if ($selected_box == $box_id) {
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_LANGUAGES) . '">' . BOX_LANGUAGES_EDITOR . '</a>');
    $contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_LANGUAGES_SYNC) . '">' . BOX_LANGUAGES_SYNC . '</a>');

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
    'link'  => tep_href_link(FILENAME_LANGUAGES, 'selected_box=' . $box_id)
  );

  $box = new box;
  echo $box->menuBox($heading, $contents, $heading_class);

  if( count($lng->languages) > 1 ) {
?>
          <div class="menuBoxHeading bounder calign">
<?php
    foreach($lng->languages as $key => $value ) {
      $name = $value['language_name'];
      if( $lng->current == $value['language_id'] ) {
        $name = '<span class="dataTableContentYellow">' . $name . '</span>';
      }
      echo '<div class="floater calign halfer"><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('language') . 'language=' . $value['language_id']) . '">' . tep_image($cDefs->cserver . DIR_WS_CATALOG_STRINGS . $value['language_path'] . '/images/icon.png', $value['language_name']) . '<br />' . $name . '</a></div>';
    }
?>
          </div>
<?php
  }
?>

