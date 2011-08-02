<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front Plugin: Banner System template
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
?>
            <div class="bounder banner_class calign bg1">
              <div class="vpad sep">
<?php
  if( empty($data_array['link']) ) {
    echo $data_array['image'];
  } elseif( strpos($data_array['link'], '<') !== false && strpos($data_array['link'], '>') !== false) {
    echo $data_array['link'];
  } else {
    echo '<a href="' . $data_array['link'] . '" title="' . $data_array['content_name'] . '" class="banner_system" attr="' . $data_array['auto_id'] . '">' . $data_array['image'] . '</a>';
  }
?>
              </div>
            </div>
