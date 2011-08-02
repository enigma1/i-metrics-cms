<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front Plugin: Direct Management Editor template form
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
  $buttons = array(
    tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE, 'id="dm_button_update"'),
  );
?>
            <div class="formArea" style="width: 720px"><?php echo tep_draw_form('dm_editor', $link); ?><fieldset><legend><?php echo $cStrings->TEXT_EDIT_CONTENT; ?></legend>
              <div class="form_label"><?php echo $cStrings->TEXT_TITLE; ?></div>
              <div class="form_input rpad"><?php echo tep_draw_input_field('content_title', $content_array['content_title'], 'class="wider"'); ?></div>
              <div class="form_label"><?php echo $cStrings->TEXT_DESCRIPTION; ?></div>
              <div class="form_texta rpad"><?php echo tep_draw_textarea_field('content_description', $content_array['content_description'], '', '20'); ?></div>
              <div class="formButtons"><?php echo implode('', $buttons); ?></div>
            </fieldset></form></div>
