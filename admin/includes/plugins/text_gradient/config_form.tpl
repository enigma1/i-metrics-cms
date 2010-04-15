<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin Plugin: Color Gradient configuration form
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
  $default_array = array(
   'id' => '', 
   'text' => $cStrings->TEXT_SCRIPT_SELECT,
  );

  $front_dir = tep_read_dir(DIR_WS_CATALOG, 1);
  array_unshift($front_dir, $default_array);
?>
      <div class="formArea"><?php echo tep_draw_form('tgf', $g_script, tep_get_all_get_params(array('action')) . 'action=process_options', 'post'); ?><table class="tabledata" cellspacing="1">
        <tr class="dataTableHeadingRow">
          <th><?php echo (count($options_array['front_scripts'])?$cStrings->HEADING_REMOVE:'&nbsp;'); ?></th>
          <th><?php echo $cStrings->HEADING_SCRIPT; ?></th>
          <th><?php echo $cStrings->HEADING_SELECTOR; ?></th>
          <th><?php echo $cStrings->HEADING_COLOR_TRANSITIONS; ?></th>
        </tr>
<?php
  foreach($options_array['front_scripts'] as $script => $data) {
?>
        <tr class="dataTableRowAlt3">
          <td><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action','front_gradient_remove')) . 'action=process_options&front_gradient_remove=' . $script) . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', IMAGE_DELETE) . '</a>'; ?></td>
          <td><?php echo $script; ?></td>
          <td><?php echo $data['selector']; ?></td>
          <td><?php echo $data['colors']; ?></td>
        </tr>
<?php
  }
?>
        <tr class="dataTableRowAlt3">
          <td><?php echo $cStrings->TEXT_INSERT_FRONT_SCRIPT; ?></td>
          <td><?php echo tep_draw_pull_down_menu('script_entry', $front_dir); ?></td>
          <td><?php echo tep_draw_input_field('script_selector', ''); ?></td>
          <td><?php echo tep_draw_input_field('script_colors', '', 'size="7", maxlength="7"'); ?></td>
        </tr>
        <tr class="dataTableRowAlt3">
          <td colspan="2"><?php echo tep_draw_checkbox_field('front_all', 1, $front_all) . '&nbsp;' . $cStrings->TEXT_ATTACH_FRONT_ALL; ?></td>
          <td><?php echo tep_draw_input_field('front_common_selector', $front_common_selector); ?></td>
          <td><?php echo tep_draw_input_field('front_common_colors', $front_common_colors, 'size="7", maxlength="7"'); ?></td>
        </tr>
        <tr class="dataTableRowAlt3">
          <td colspan="4"><?php echo sprintf($cStrings->HEADING_FRONT_ASSIGNED, count($options_array['front_scripts'])); ?></td>
        </tr>
        <tr>
          <td colspan="4" class="formButtons"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action','front_gradient_remove'))) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM); ?></td>
        </tr>
      </table></form></div>
