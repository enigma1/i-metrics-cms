<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin Plugin: Newsletters System Configuration form
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
    '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
    tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
  );
?>
      <div class="listArea"><?php echo tep_draw_form('ds', $cDefs->script, tep_get_all_get_params('action') . 'action=process_options', 'post'); ?><table class="tabledata">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->HEADING_DISPLAY_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('display_left', 1, $display_left); ?></td>
          <td><?php echo $cStrings->TEXT_DISPLAY_LEFT; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('display_right', 1, $display_right); ?></td>
          <td><?php echo $cStrings->TEXT_DISPLAY_RIGHT; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('display_top', 1, $display_top); ?></td>
          <td><?php echo $cStrings->TEXT_DISPLAY_TOP; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('display_bottom', 1, $display_bottom); ?></td>
          <td><?php echo $cStrings->TEXT_DISPLAY_BOTTOM; ?></td>
        </tr>
      </table>
      <table class="tabledata">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->HEADING_CONFIGURATION_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRow">
          <td><div class="rpad"><?php echo tep_draw_input_field('banners_path', $banners_path); ?></div></td>
          <td><?php echo $cStrings->TEXT_PATH; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('impressions', 1, $impressions); ?></td>
          <td><?php echo $cStrings->TEXT_RECORD_IMPRESSIONS; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('clicks', 1, $clicks); ?></td>
          <td><?php echo $cStrings->TEXT_RECORD_CLICKS; ?></td>
        </tr>
      </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
