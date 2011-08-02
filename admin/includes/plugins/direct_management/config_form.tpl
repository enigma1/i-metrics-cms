<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin Plugin: Direct Management configuration form
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
      <div class="listArea"><?php echo tep_draw_form('dm', $cDefs->script, tep_get_all_get_params('action') . 'action=process_options', 'post'); ?><table class="tabledata">
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
      </table>
      <table class="tabledata">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->TEXT_OTHER_CONFIGURATION_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_input_field('admin_key_length', $admin_key_length); ?></td>
          <td><?php echo $cStrings->TEXT_ADMIN_KEY_LENGTH; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo $admin_key; ?></td>
          <td><?php echo $cStrings->TEXT_ADMIN_REQUEST_KEY; ?></td>
        </tr>
      </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>