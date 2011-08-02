<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2009 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Text Groups Display
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
  $abstract_query = $g_db->fly("select abstract_zone_id, abstract_zone_name, abstract_zone_desc from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id='" . GTEXT_FRONT_ZONE_ID . "'");
  $abstract_array = $g_db->fetch_array($abstract_query);
?>
            <div><h1><?php echo $abstract_array['abstract_zone_name']; ?></h1></div>
            <div><?php echo $abstract_array['abstract_zone_desc']; ?></div>
            <div class="bounder">
<?php
  $cText = new gtext_front;
  $home_entries = $cText->get_entries($abstract_array['abstract_zone_id']);

  foreach($home_entries as $key => $value) {
    $gtext_query = $g_db->query("select gtext_id, gtext_title, gtext_description, date_added from " . TABLE_GTEXT . " where gtext_id = '" . (int)$key . "' and status = '1'");
    $gtext_array = $g_db->fetch_array($gtext_query);
?>
              <div class="hider vspacer">
                <div class="floater"><h2><?php echo '<a href="' . tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $key) . '" title="' . $gtext_array['gtext_title'] . '">' . $value['gtext_alt_title'] . '</a>'; ?></h2></div>
                <div class="floatend"><b><?php echo tep_date_long($gtext_array['date_added']); ?></b></div>
              </div>
              <div class="cleaner"><?php echo tep_truncate_string($gtext_array['gtext_description']); ?></div>
<?php
  }
?>
            </div>
