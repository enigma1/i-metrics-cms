<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin Plugin: Comments System configuration form
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
      <div class="listArea"><?php echo tep_draw_form('rc', $g_script, tep_get_all_get_params(array('action')) . 'action=process_options', 'post'); ?><table class="tabledata" cellspacing="1">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->HEADING_DISPLAY_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_checkbox_field('text_pages', 1, $text_pages); ?></td>
          <td><?php echo $cStrings->TEXT_TEXT_PAGES; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_checkbox_field('text_collections', 1, $text_collections); ?></td>
          <td><?php echo $cStrings->TEXT_TEXT_COLLECTIONS; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_checkbox_field('image_collections', 1, $image_collections); ?></td>
          <td><?php echo $cStrings->TEXT_IMAGE_COLLECTIONS; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_checkbox_field('text_include', 1, $text_include); ?></td>
          <td><?php echo $cStrings->TEXT_INCLUSION_TEXT_PAGES; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_checkbox_field('collection_include', 1, $collection_include); ?></td>
          <td><?php echo $cStrings->TEXT_INCLUSION_COLLECTIONS; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_checkbox_field('display_rating', 1, $display_rating); ?></td>
          <td><?php echo $cStrings->TEXT_DISPLAY_RATING; ?></td>
        </tr>
      </table>
      <table class="tabledata" cellspacing="1">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->TEXT_FORM_PROTECTION_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_checkbox_field('auto_display', 1, $auto_display); ?></td>
          <td><?php echo $cStrings->TEXT_AUTO_DISPLAY; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_checkbox_field('anti_bot', 1, $anti_bot); ?></td>
          <td><?php echo $cStrings->TEXT_ANTI_BOT; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_checkbox_field('anti_bot_strict', 1, $anti_bot_strict); ?></td>
          <td><?php echo $cStrings->TEXT_ANTI_BOT_STRICT; ?></td>
        </tr>
      </table>
      <table class="tabledata" cellspacing="1">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->TEXT_OTHER_CONFIGURATION_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_input_field('rating_steps', $rating_steps, 'size=2'); ?></td>
          <td><?php echo $cStrings->TEXT_RATING_STEPS; ?></td>
        </tr>
        <tr>
          <td colspan="2" class="formButtons"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM); ?></td>
        </tr>
      </table></form></div>