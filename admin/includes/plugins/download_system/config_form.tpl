<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin Plugin: Download System Configuration form
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
      <div class="formArea"><?php echo tep_draw_form('ds', $cDefs->script, tep_get_all_get_params('action') . 'action=process_options', 'post'); ?><table class="tabledata">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->HEADING_DISPLAY_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('text_pages', 1, $text_pages); ?></td>
          <td><?php echo $cStrings->TEXT_TEXT_PAGES; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('collections', 1, $collections); ?></td>
          <td><?php echo $cStrings->TEXT_COLLECTIONS; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('download_count', 1, $download_count); ?></td>
          <td><?php echo $cStrings->TEXT_DOWNLOAD_COUNT; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('download_count_show', 1, $download_count_show); ?></td>
          <td><?php echo $cStrings->TEXT_DOWNLOAD_COUNT_SHOW; ?></td>
        </tr>
      </table>
      <table class="tabledata">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->HEADING_CONFIGURATION_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRow">
          <td class="tinysep">
<?php
  //$left_status = ($display_col == 1)?0:1;
  //$right_status = ($display_col == 0)?0:1;
  $radio_array = array(
    tep_draw_radio_field('display_col', 0, ($display_col == 0)?true:false, 'id="download_left"') . '<label for="download_left">' . $cStrings->TEXT_DISPLAY_COLUMN_LEFT . '</label>',
    tep_draw_radio_field('display_col', 1, ($display_col == 1)?true:false, 'id="download_right"') . '<label for="download_right">' . $cStrings->TEXT_DISPLAY_COLUMN_RIGHT . '</label>',
    tep_draw_radio_field('display_col', 2, ($display_col == 2)?true:false, 'id="download_end"') . '<label for="download_end">' . $cStrings->TEXT_DISPLAY_PAGE . '</label>',
  );
  echo implode('', $radio_array);
?>
          </td>
          <td><?php echo $cStrings->TEXT_DISPLAY_COLUMN; ?></td>
        </tr>

        <tr class="dataTableRow">
          <td class="tinysep">
<?php
  $radio_array = array(
    tep_draw_radio_field('download_method', 0, ($download_method == 1)?false:true, 'id="download_get"') . '<label for="download_get">' . $cStrings->TEXT_DISPLAY_METHOD_GET . '</label>',
    tep_draw_radio_field('download_method', 1, ($download_method == 1)?true:false, 'id="download_post"') . '<label for="download_post">' . $cStrings->TEXT_DISPLAY_METHOD_POST . '</label>',
  );
  echo implode('', $radio_array);
?>
          <td><?php echo $cStrings->TEXT_METHODS; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td><div class="rpad"><?php echo tep_draw_input_field('download_path', $download_path); ?></div></td>
          <td><?php echo $cStrings->TEXT_PATH; ?></td>
        </tr>
      </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
