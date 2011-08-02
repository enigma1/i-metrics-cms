<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin Plugin: CSS Menu configuration form
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
    tep_image_submit('button_confirm.gif', IMAGE_CONFIRM)
  );
?>
      <div class="formArea"><?php echo tep_draw_form('csm', $cDefs->script, tep_get_all_get_params('action') . 'action=process_options', 'post'); ?><table class="tabledata">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->HEADING_DISPLAY_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRowAlt3">
          <td class="calign"><?php echo tep_draw_input_field('max_drop', $max_drop, 'size="3", maxlength="3"'); ?></td>
          <td><?php echo $cStrings->TEXT_MAX_DROP; ?></td>
        </tr>
        <tr class="dataTableRowAlt3">
          <td class="calign"><?php echo tep_draw_input_field('max_cols', $max_cols, 'size="3", maxlength="2"'); ?></td>
          <td><?php echo $cStrings->TEXT_MAX_COLS; ?></td>
        </tr>
        <tr class="dataTableRowAlt3">
          <td class="calign"><?php echo tep_draw_input_field('max_width', $max_width, 'size="3", maxlength="3"'); ?></td>
          <td><?php echo $cStrings->TEXT_MAX_WIDTH; ?></td>
        </tr>
        <tr class="dataTableRowAlt3">
          <td class="calign"><?php echo tep_draw_input_field('border_width', $border_width, 'size="3", maxlength="2"'); ?></td>
          <td><?php echo $cStrings->TEXT_BORDER_WIDTH; ?></td>
        </tr>
        <tr class="dataTableRowAlt3">
          <td class="calign"><?php echo tep_draw_input_field('font_size', $font_size, 'size="3", maxlength="3"'); ?></td>
          <td><?php echo $cStrings->TEXT_FONT_SIZE; ?></td>
        </tr>
        <tr class="dataTableRowAlt3">
          <td class="calign"><?php echo tep_draw_input_field('font_pad', $font_pad, 'size="3", maxlength="2"'); ?></td>
          <td><?php echo $cStrings->TEXT_FONT_PAD; ?></td>
        </tr>
      </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
