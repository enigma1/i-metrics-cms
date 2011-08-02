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
?>
            <div class="bounder"><?php echo tep_draw_form($this->form_box, $link); ?>
              <div class="infoBoxHeading boxpadding"><?php echo $cStrings->BOX_HEADING_NEWSLETTER; ?></div>
              <div class="infoBoxContents infoBoxContentsAlt">
                <div><?php echo $cStrings->TEXT_INFO_NEWSLETTER_DESC; ?></div>
                <div>
                  <label><?php echo $cStrings->TEXT_INFO_EMAIL; ?></label>
                  <div class="rpad"><?php echo tep_draw_input_field('email', '', 'class="wider"'); ?></div>
                </div>
                <div class="cleaner">
                  <div class="floater rpad"><?php echo tep_draw_checkbox_field('remove', 'on'); ?></div>
                  <label><?php echo $cStrings->TEXT_INFO_REMOVE; ?></label>
                </div>
                <div class="cleaner calign bspacer"><?php echo tep_image_submit('small_submit.gif', IMAGE_BUTTON_SUBMIT, 'name="' . $this->form_box . '"'); ?></div>
              </div>
            </form></div>
