<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Index Content Level Module
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
------------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
//
*/
  $level = 'content_level';
  $entries_array = array();

  $entries_array[] = array(
    'id' => 'index_main',
    'sub' => 'index_main',
    'title' => TEXT_INFO_BACK,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/root.png', TEXT_INFO_BACK),
    'href' => tep_href_link(),
  );

  $entries_array[] = array(
    'id' => 'text_content',
    'title' => TEXT_INFO_PAGE_NEW,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/new_page.png', TEXT_INFO_PAGE_NEW),
    'href' => tep_href_link(FILENAME_GENERIC_TEXT, 'action=new_generic_text&selected_box=abstract_box'),
  );

  $entries_array[] = array(
    'id' => 'list_pages',
    'title' => TEXT_INFO_PAGE_LIST,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/content_pages.png', TEXT_INFO_PAGE_LIST),
    'href' => tep_href_link(FILENAME_GENERIC_TEXT, 'selected_box=abstract_box'),
  );

  $entries_array[] = array(
    'id' => 'internal_pages',
    'title' => TEXT_INFO_PAGE_LIST_INTERNAL,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/content_internal.png', TEXT_INFO_PAGE_LIST_INTERNAL),
    'href' => tep_href_link(FILENAME_GENERIC_TEXT, 'filter_id=1&selected_box=abstract_box'),
  );

  $entries_array[] = array(
    'id' => 'fill_pages',
    'title' => TEXT_INFO_PAGE_LIST_FULL,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/content_complete.png', TEXT_INFO_PAGE_LIST_FULL),
    'href' => tep_href_link(FILENAME_GENERIC_TEXT, 'filter_id=2&selected_box=abstract_box'),
  );

  $entries_array[] = array(
    'id' => 'published_pages',
    'title' => TEXT_INFO_PAGE_LIST_PUBLISHED,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/content_published.png', TEXT_INFO_PAGE_LIST_PUBLISHED),
    'href' => tep_href_link(FILENAME_GENERIC_TEXT, 'filter_id=3&selected_box=abstract_box'),
  );

  $entries_array[] = array(
    'id' => 'unpublished_pages',
    'title' => TEXT_INFO_PAGE_LIST_UNPUBLISHED,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/content_unpublished.png', TEXT_INFO_PAGE_LIST_UNPUBLISHED),
    'href' => tep_href_link(FILENAME_GENERIC_TEXT, 'filter_id=4&selected_box=abstract_box'),
  );

  $entries_array[] = array(
    'id' => 'items',
    'title' => TEXT_INFO_COLLECTIONS_LIST,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/items.png', TEXT_INFO_COLLECTIONS_LIST),
    'href' => tep_href_link(FILENAME_ABSTRACT_ZONES, 'selected_box=abstract_box'),
  );
  $system_start_count = count($entries_array);

  // Amend plugin options
  extract(tep_load('plugins_admin'));
  $plugin_contents = array();
  $args = array('entries_array' => &$entries_array);
  $cPlug->invoke('html_home_collections', $args);
  $system_end_count = count($entries_array);

  $entries_array[] = array(
    'id' => 'types',
    'title' => TEXT_INFO_COLLECTIONS_TYPES,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/types.png', TEXT_INFO_COLLECTIONS_TYPES),
    'href' => tep_href_link(FILENAME_ABSTRACT_TYPES, 'selected_box=abstract_box'),
  );

  $entries_array[] = array(
    'id' => 'configuration',
    'title' => TEXT_INFO_COLLECTIONS_CONFIG,
    'image' => tep_image(DIR_WS_IMAGES . 'categories/configuration.png', TEXT_INFO_COLLECTIONS_CONFIG),
    'href' => tep_href_link(FILENAME_ABSTRACT_ZONES_CONFIG, 'selected_box=abstract_box'),
  );
?>
            <div id="index_content">
<?php
  for($i=0, $j=count($entries_array); $i<$j; $i++) {
    $data_id = $attr = '';
    if( isset($entries_array[$i]['sub']) ) {
      $attr = 'class="sandbox" attr="' . $entries_array[$i]['sub'] . '"';
    }
    if( isset($entries_array[$i]['id']) ) {
      $data_id = ' data-id="' . $entries_array[$i]['id'] . '"';
    }

    if( $i >= $system_start_count && $i < $system_end_count) {
      $class = 'plugin colorblock floater calign';
    } else {
      $class = 'homeCell colorblock floater calign';
    }
?>
              <div class="<?php echo $class; ?>" style="width: 132px; height: 160px;"<?php echo $data_id; ?>><?php echo '<a href="' . $entries_array[$i]['href'] . '" title="' . $entries_array[$i]['title'] . '"' . $attr . '>' . $entries_array[$i]['image'] . '<br />' . $entries_array[$i]['title'] . '</a>'; ?></div>
<?php
  }
?>
            </div>

