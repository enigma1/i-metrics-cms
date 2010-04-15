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
  $default_array = array(
   'id' => '', 
   'text' => $cStrings->TEXT_SCRIPT_SELECT,
  );

  $front_dir = tep_read_dir(DIR_WS_CATALOG, 1);
  $back_dir = tep_read_dir(DIR_FS_ADMIN);
  array_unshift($front_dir, $default_array);
  array_unshift($back_dir, $default_array);
?>
      <div class="formArea"><?php echo tep_draw_form('popup_image_form', $g_script, tep_get_all_get_params(array('action')) . 'action=process_options', 'post'); ?><table class="tabledata" cellspacing="1">
        <tr class="dataTableHeadingRow">
          <th><?php echo (count($options_array['front_scripts'])?$cStrings->HEADING_REMOVE:'&nbsp;'); ?></th>
          <th><?php echo $cStrings->HEADING_SCRIPT; ?></th>
          <th><?php echo $cStrings->HEADING_SELECTOR; ?></th>
        </tr>
<?php
  foreach($options_array['front_scripts'] as $script => $selector) {
?>
        <tr class="dataTableRowAlt3">
          <td><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action','front_popup_remove')) . 'action=process_options&front_popup_remove=' . $script) . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', IMAGE_DELETE) . '</a>'; ?></td>
          <td><?php echo $script; ?></td>
          <td><?php echo $selector; ?></td>
        </tr>
<?php
  }
?>
        <tr class="dataTableRowAlt3">
          <td><?php echo $cStrings->TEXT_INSERT_FRONT_SCRIPT; ?></td>
          <td><?php echo tep_draw_pull_down_menu('script_entry', $front_dir); ?></td>
          <td><?php echo tep_draw_input_field('script_selector', ''); ?></td>
        </tr>
        <tr class="dataTableRowAlt3">
          <td colspan="2"><?php echo tep_draw_checkbox_field('front_all', 1, $front_all) . '&nbsp;' . $cStrings->TEXT_ATTACH_FRONT_ALL; ?></td>
          <td><?php echo tep_draw_input_field('front_common_selector', $front_common_selector); ?></td>
        </tr>
        <tr class="dataTableRowAlt3">
          <td colspan="3"><?php echo sprintf($cStrings->HEADING_FRONT_ASSIGNED, count($options_array['front_scripts'])); ?></td>
        </tr>
        <tr>
          <th colspan="3"><hr /></th>
        </tr>
        <tr class="dataTableHeadingRow">
          <th><?php echo (count($options_array['back_scripts'])?$cStrings->HEADING_REMOVE:'&nbsp;'); ?></th>
          <th><?php echo $cStrings->HEADING_SCRIPT; ?></th>
          <th><?php echo $cStrings->HEADING_SELECTOR; ?></th>
        </tr>
<?php
  foreach($options_array['back_scripts'] as $script => $selector) {
?>
        <tr class="dataTableRowAlt2">
          <td><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'front_popup_remove', 'back_popup_remove')) . 'action=process_options&back_popup_remove=' . $script) . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', IMAGE_DELETE) . '</a>'; ?></td>
          <td><?php echo $script; ?></td>
          <td><?php echo $selector; ?></td>
        </tr>
<?php
  }
?>
        <tr class="dataTableRowAlt2">
          <td><?php echo $cStrings->TEXT_INSERT_BACK_SCRIPT; ?></td>
          <td><?php echo tep_draw_pull_down_menu('admin_entry', $back_dir); ?></td>
          <td><?php echo tep_draw_input_field('admin_selector', ''); ?></td>
        </tr>
        <tr class="dataTableRowAlt2">
          <td colspan="2"><?php echo tep_draw_checkbox_field('back_all', 1, $back_all) . '&nbsp;' . $cStrings->TEXT_ATTACH_BACK_ALL; ?></td>
          <td><?php echo tep_draw_input_field('back_common_selector', $back_common_selector); ?></td>
        </tr>
        <tr class="dataTableRowAlt2">
          <td colspan="3"><?php echo sprintf($cStrings->HEADING_BACK_ASSIGNED, count($options_array['back_scripts'])); ?></td>
        </tr>
        <tr>
          <td colspan="3" class="formButtons"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'front_popup_remove', 'back_popup_remove'))) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM); ?></td>
        </tr>
      </table></form></div>
