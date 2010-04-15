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
              <div class="contentBoxHeading infoBoxHeading boxpadding"><?php echo $cStrings->BOX_HEADING_CAST_VOTE; ?></div>
              <div class="infoBoxContents infoBoxContentsAlt">
                <div><?php echo sprintf($cStrings->TEXT_INFO_VOTE_DESC, $desc); ?></div>
                <table align="center">
                  <tr>
                    <td><?php echo tep_image($this->web_template_path . 'thumbs-down.png', $cStrings->TEXT_INFO_VOTE_BAD); ?></td>
<?php
      for( $i=0, $j=$this->options['box_steps']; $i<$j; $i++) {
?>
                    <td><?php echo tep_draw_radio_field('rating', $i); ?></td>
<?php
      }
?>
                    <td><?php echo tep_image($this->web_template_path . 'thumbs-up.png', $cStrings->TEXT_INFO_VOTE_GOOD); ?></td>
                  </tr>
                </table>
                <div class="cleaner calign bspacer"><?php echo tep_image_submit('small_submit.gif', IMAGE_BUTTON_SUBMIT, 'name="' . $this->form_box . '"'); ?></div>
              </div>
            </form></div>
