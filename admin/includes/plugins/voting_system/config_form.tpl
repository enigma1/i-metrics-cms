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
          <td><table cellpadding="2">
            <tr>
              <td>
<?php
  $left_status = ($display_col == 1)?0:1;
  $right_status = ($display_col == 0)?0:1;
  $radio_array = array(
    tep_draw_radio_field('display_col', 0, ($display_col == 1)?false:true) . $cStrings->TEXT_DISPLAY_COLUMN_LEFT,
    tep_draw_radio_field('display_col', 1, ($display_col == 1)?true:false) . $cStrings->TEXT_DISPLAY_COLUMN_RIGHT,
  );
  echo implode('&nbsp;&nbsp', $radio_array);
?>
              </td>
          </table></td>
          <td><?php echo $cStrings->TEXT_TEXT_PAGES; ?></td>
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
      </table>
      <table class="tabledata" cellspacing="1">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->HEADING_CONFIGURATION_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRow">
          <td><?php echo tep_draw_input_field('box_steps', $box_steps, 'size=2'); ?></td>
          <td><?php echo $cStrings->TEXT_BOX_STEPS; ?></td>
        </tr>
        <tr>
          <td colspan="2" class="formButtons"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM); ?></td>
        </tr>
      </table></form></div>
