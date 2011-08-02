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
    tep_image_submit('button_confirm.gif', IMAGE_CONFIRM)
  );
?>
      <div class="formArea liner"><?php echo tep_draw_form('ds', $cDefs->script, tep_get_all_get_params('action') . 'action=process_options', 'post'); ?><table class="tabledata">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->HEADING_DISPLAY_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_input_field('email_limit', $email_limit, 'size="3"'); ?></td>
          <td><?php echo $cStrings->TEXT_EMAIL_LIMIT; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('resent', 1, $resent); ?></td>
          <td><?php echo $cStrings->TEXT_EMAIL_RESENT; ?></td>
        </tr>
        <tr class="dataTableRow">
          <td class="calign"><?php echo tep_draw_checkbox_field('statistics', 1, $statistics); ?></td>
          <td><?php echo $cStrings->TEXT_RECORD_STATISTICS; ?></td>
        </tr>
      </table>
      <table class="tabledata">
        <tr class="dataTableHeadingRow">
          <th colspan="2"><?php echo $cStrings->HEADING_CONFIGURATION_OPTIONS; ?></th>
        </tr>
        <tr class="dataTableRow">
          <td>
<?php
  $radio_array = array(
    tep_draw_radio_field('display_col', 0, ($display_col == 0)?true:false, 'id="newsletter_left"') . '<label for="newsletter_left">' . $cStrings->TEXT_DISPLAY_COLUMN_LEFT . '</label>',
    tep_draw_radio_field('display_col', 1, ($display_col == 1)?true:false, 'id="newsletter_right"') . '<label for="newsletter_right">' . $cStrings->TEXT_DISPLAY_COLUMN_RIGHT . '</label>',
  );
  echo implode('', $radio_array);
?>
          </td>
          <td><?php echo $cStrings->TEXT_DISPLAY_COLUMN; ?></td>
        </tr>
      </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
