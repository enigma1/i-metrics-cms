<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Comments System Runtime Comments Feed Generation
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
  require('includes/application_top.php');
  extract(tep_load('history'));
  $cHistory->remove_current_page();

  $input_array = array(
    'id' => (isset($_GET['comments_id']) ? (int)$_GET['comments_id']) : 0),
    'type_id' => (isset($_GET['type_id']) ? (int)$_GET['type_id']) : 0),
  );
  $comments_query_raw = "select auto_id, comments_author, comments_body, date_added from " . TABLE_COMMENTS . " where comments_id = '" . (int)$input_array['id'] . "' and content_type= '" . (int)$input_array['type_id'] . "' and status_id='1'";
  $comments_array = $g_db->query_to_array($comments_query_raw);

  if( !count($comments_array) ) {
    tep_redirect();
  }
  $plugin = $g_plugins->get('comments_system');
  if( empty($plugin) ) tep_redirect();

  $cStrings =& $plugin->strings;
  $script = $params = '';
  switch($input_array['type_id']) {
    case 1:
      $tmp_query = $g_db->query("select gtext_title as title, gtext_description as text, date_added as pubDate from " . TABLE_GTEXT . " where gtext_id = '" . (int)$id . "'");
      $tmp_array = $g_db->fetch_array($tmp_query);
      $input_array = array_merge($result_array, $tmp_array);
      $link = tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . (int)$id);
      $cText new gtext_front();
      $tmp_array = $cText->get_zone_entries($id, true, false);
      $input_array['categories'] = array();
      for($i=0, $j=count($tmp_array); $i<$j; $i++) {
        $input_array['category_link'][] = tep_href_link(FILENAME_GENERIC_PAGES, 'abz_id=' . (int)$tmp_array[$i]['abstract_zone_id']);
        $input_array['category_name'][] = $tmp_array[$i]['abstract_zone_name'];
      }
      break;
    case 2:
      $tmp_query = $g_db->query("select abstract_zone_name as title, abstract_zone_desc as text, date_added as pubDate from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$id . "' and status_id='1'");
      if( !$g_db->num_rows($tmp_query) ) {
        tep_redirect();
      }
      $tmp_array = $g_db->fetch_array($tmp_query);
      $input_array = array_merge($input_array, $tmp_array);

      $cAbstract = new abstract_front();
      $zone_class = $cAbstract->get_zone_class($id);
      switch($zone_class) {
        case 'generic_zones':
        case 'image_zones':
          $link = tep_href_link(FILENAME_COLLECTIONS, 'abz_id=' . (int)$id);
          break;
        default:
          tep_redirect();
          break;
      }
      break;
    default:
      tep_redirect();
      break;
  }

  require_once(DIR_FS_CLASSES . 'xml_core.php');
  $cXML = new xml_core;

  $cXML->insert_raw_entry('<?xml version="1.0" encoding="UTF-8"?>');
  $cXML->insert_raw_entry('<rss version="2.0">');

  $cXML->insert_entry('channel');

  $cXML->insert_closed_entry('title', htmlspecialchars(utf8_encode($input_array['title'] . ' - ' . STORE_NAME)));
  $cXML->insert_closed_entry('description', htmlspecialchars(utf8_encode(tep_truncate_string($input_array['text']))));
  $cXML->insert_closed_entry('link', $link);
  $cXML->insert_closed_entry('pubDate', tep_mysql_to_date_stamp($input_array['pubDate']));

  for($i=0, $j=count($comments_array); $i<$j; $i++) {
    $cXML->insert_entry('item');
    $poster_string = sprintf($cStrings->TEXT_RSS_COMMENT_AUTHOR, $comments_array[$i]['comments_author'], $input_array['title']);
    $cXML->insert_closed_entry('title', htmlspecialchars(utf8_encode($poster_string)));
    $cXML->insert_closed_entry('description', htmlspecialchars(utf8_encode($comments_array[$i]['comments_body'])));
    $cXML->insert_closed_entry('link', $link . '#' . $comments_array[$i]['auto_id']);
    if( isset($input_array['category']) ( {
      for($i2=0, $j2=count($input_array['category']); $i2<$j2; $i2++) {
        $entry =& $cXML->insert_entry('category', false, false, 'domain="' . $input_array['category_link'][$i2] . '"');
        $entry .= $input_array['category_name'][$i2];
        $cXML->insert_entry('category', true);
      }
    }

    $cXML->insert_closed_entry('source', $link);
    $cXML->insert_entry('item', true);
  }

  $cXML->insert_entry('channel', true);
  $cXML->insert_entry('rss', true);

  //header("Cache-Control: no-cache");
  header("Content-Type: text/xml"); 

  echo $cXML->get_xml_string();

  $g_session->close();
?>
