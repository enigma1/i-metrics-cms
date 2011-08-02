<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Image Zones class
//----------------------------------------------------------------------------
// Front: The main footer code
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
  $cPlug->invoke('html_footer_pre');
?>
    <div class="calign" id="footer">
<?php
  $sitemap = '';
  $cSuper = new super_front();
  if( $cSuper->is_enabled(SUPER_SITEMAP_ZONE_ID) ) {
    $super_array = $cSuper->get_zone_data(SUPER_SITEMAP_ZONE_ID);
    $sitemap = '<a href="' . tep_href_link(FILENAME_COLLECTIONS, 'abz_id=' . SUPER_SITEMAP_ZONE_ID) . '">' . $super_array['abstract_zone_name'] . '</a>' . "\n";
  }

  $contact_array = array('gtext_title' => TEXT_INFO_NA);
  $contact_query = $db->fly("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . GTEXT_CONTACT_ID . "' and status='1'");
  if( $db->num_rows($contact_query) ) {
    $contact_array = $db->fetch_array($contact_query);
  }

  $zones_array = array(
    DEFAULT_FOOTER_TEXT_ZONE_ID => 'Footer Links',
  );

  $cText = new gtext_front();
  $lines_array = array();
  
  foreach($zones_array as $id => $zone) {  
    $text_entries = $cText->get_entries($id, true, false);
    if( count($text_entries) ) {
      $info_box_contents = array();
      foreach($text_entries as $key => $value) {
        if( empty($value['gtext_alt_title']) ) {
          $value['gtext_alt_title'] = $value['gtext_title'];
        }
        $info_box_contents[] = '<a href="' . tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $key) . '">' . $value['gtext_alt_title'] . '</a>';
      }
      $lines_array[] = implode('&nbsp;&nbsp;|&nbsp;&nbsp;', $info_box_contents);
    }
  }
  for( $i=0, $j=count($lines_array); $i<$j; $i++ ) {
    if( !$i ) {
      if( !empty($sitemap) ) {
        $lines_array[$i] = $sitemap . '&nbsp;&nbsp;|&nbsp;&nbsp;' . $lines_array[$i];
      }
      if( !empty($contact_array) ) {
        $lines_array[$i] = '<a href="' . tep_href_link(FILENAME_CONTACT_US, '', 'SSL') . '" title="' . $contact_array['gtext_title'] . '" rel="nofollow">' . $contact_array['gtext_title'] . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;' . $lines_array[$i];
      }
    }
    echo '<div>' . $lines_array[$i] . '</div>' . "\n";
  }
?>
      <div style="height: 12px;"></div>
      <div><?php echo FOOTER_TEXT_BODY_CUSTOM; ?></div>
      <div><?php echo FOOTER_TEXT_BODY_POWERED; ?></div>
      <div><?php echo FOOTER_TEXT_BODY; ?></div>
    </div>
<?php
  $cPlug->invoke('html_footer_post');
?>