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
            <div class="form_group cleaner"><?php echo tep_draw_form($this->form_box, $link); ?><fieldset><legend><?php echo $cStrings->TEXT_ENTER_COMMENT; ?></legend>
              <div class="form_label"><?php echo $cStrings->TEXT_ENTRY_NAME; ?></div>
              <div class="form_input"><?php echo tep_draw_input_field('name'); ?></div>
              <div class="form_label"><?php echo $cStrings->TEXT_ENTRY_EMAIL; ?></div>
              <div class="form_input"><?php echo tep_draw_input_field('email'); ?></div>
              <div class="form_label"><?php echo $cStrings->TEXT_ENTRY_URL; ?></div>
              <div class="form_input"><?php echo tep_draw_input_field('url'); ?></div>
              <div class="form_label"><?php echo $cStrings->TEXT_ENTRY_COMMENT; ?></div>
              <div class="form_texta"><?php echo tep_draw_textarea_field('comment', 'soft', '40', '10'); ?></div>
              <div class="formButtons" style="margin-top: 8px; padding-top: 16px;">
<?php
  if( $this->options['display_rating'] ) {
?>
                <div class="charsep floater"><b><?php echo $cStrings->TEXT_ENTRY_RATE; ?></b></div>
                <div class="floater"><?php echo tep_image($this->web_template_path . 'thumbs-down.png', $cStrings->TEXT_INFO_COMMENT_BAD); ?></div>
<?php
    for( $i=0, $j=$this->options['rating_steps']; $i<$j; $i++) {
      echo '<div class="charsep floater">' . tep_draw_radio_field('rating', $i) . '</div>' . "\n";
    }
?>
                <div class="floater"><?php echo tep_image($this->web_template_path . 'thumbs-up.png', $cStrings->TEXT_INFO_COMMENT_GOOD); ?></div>
<?php
  }
?>
                <div class="floatend" id="cscss_buttons">
<?php
  if( $this->options['anti_bot'] ) {
    foreach($this->storage['css_buttons'] as $key => $value) {
      echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_SUBMIT, 'class="' . $key . '" name="' . $key . '"'); 
    }
  } else {
    echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_SUBMIT, 'name="' . $this->form_box . '"');
  }
?>
                </div>
              </div>
            </fieldset></form></div>
