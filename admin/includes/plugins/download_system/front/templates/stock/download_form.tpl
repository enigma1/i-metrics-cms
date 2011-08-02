<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front Plugin: Comments System template form
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
  $common_text = '';
?>
            <div class="bounder">
              <div class="infoBoxHeading boxpadding"><?php echo $cStrings->BOX_HEADING_DOWNLOAD; ?></div>
<?php
  for( $i=0, $j=count($input_array); $i<$j; $i++) {
?>
              <div class="infoBoxContents infoBoxContentsAlt">
<?php
    if( empty($common_text) && empty($input_array[$i]['content_text']) ) {
      $input_array[$i]['content_text'] = $cStrings->TEXT_INFO_DOWNLOAD_DESC;
      $common_text = $input_array[$i]['content_text'];
    }
    if( !empty($input_array[$i]['content_text']) ) {
?>
                <div><?php echo $input_array[$i]['content_text']; ?></div>
<?php
    }
?>
                <div class="cleaner calign vspacer">
<?php
    if( $method == 'get' ) {
      echo '<a href="' . $input_array[$i]['href'] . '">' . $input_array[$i]['content_name'] . '</a>';
    } else {
      echo tep_draw_form($this->form_name . '[' . $i . ']', $input_array[$i]['href']) . tep_image_submit('button_download.gif', IMAGE_BUTTON_SUBMIT, 'name="' . $this->form_name . '_' . $input_array[$i]['auto_id'] . '"') . '</form>';
    }
?>
                </div>
              </div>
<?php
  }
?>
            </div>
