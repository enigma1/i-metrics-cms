<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Super Zones for the Abstract Zones
// Controls relationships among zones
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  class super_zones extends abstract_zones {
    var $m_sID, $m_ssID, $m_spage, $m_apage;
// class constructor
    function super_zones() {
      global $g_db;

      $this->m_spage = isset($_GET['spage'])?$_GET['spage']:'';
      $this->m_ssID = isset($_GET['ssID'])?$_GET['ssID']:'';
      $this->m_sID = isset($_GET['sID'])?$_GET['sID']:'';
      $this->m_apage = isset($_GET['apage'])?$_GET['apage']:'';
      parent::abstract_zones();

      if (isset($_POST['delete_multizone_x']) || isset($_POST['delete_multizone_y'])) $this->m_action='delete_multizone';
      if (isset($_POST['update_multizone_x']) || isset($_POST['update_multizone_y'])) $this->m_action='update_multizone';

      if( !tep_not_null($this->m_sID) ) {
        $zones_query = $g_db->query("select subzone_id from " . TABLE_SUPER_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "'");
        if( $zones_array = $g_db->fetch_array($zones_query) ) {
          $this->m_sID = $zones_array['subzone_id'];
        }
      }
    }

    function process_action() {
      switch( $this->m_action ) {
        case 'update_multizone':
          $result = parent::validate_array_selection('gt_id'); 
          return $this->update_multizone();
        case 'insert_multi_entries':
          $result = parent::validate_array_selection('gt_id', 'multi_entries');
          return $this->insert_multi_entries();
        case 'deleteconfirm_multizone':
          $result = parent::validate_array_selection('gt_id'); 
          return $this->deleteconfirm_multizone();
        case 'deleteconfirm_zone':
          return $this->deleteconfirm_zone();
        case 'delete_multizone':
          $result = parent::validate_array_selection('gt_id'); 
          break;
        default:
          return parent::process_action(); 
          break;
      }
    }

    function update_multizone() {
      global $g_db;

      foreach ($_POST['gt_id'] as $subzone_id=>$val) {
        $sql_data_array = array(
                                'sub_alt_title' => $g_db->prepare_input($_POST['alt_title'][$subzone_id]),
                                'sequence_order' => (int)$_POST['seq'][$subzone_id],
                               );

        $g_db->perform(TABLE_SUPER_ZONES, $sql_data_array, 'update', "abstract_zone_id = '" . (int)$this->m_zID . "' and subzone_id = '" . (int)$subzone_id . "'");
      }
      tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
    }


    function insert_multi_entries() {
      global $g_db;

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');
      switch( $multi_form ) {
        case 'insert_multi_entries':
          foreach($_POST['gt_id'] as $subzone_id=>$val) {
            $check_query = $g_db->query("select subzone_id from " . TABLE_SUPER_ZONES . " where subzone_id = '" . (int)$subzone_id . "' and abstract_zone_id = '" . (int)$this->m_zID . "'");
            if( $g_db->num_rows($check_query) )
                continue;

            $check_query = $g_db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$subzone_id . "'");
            if( !$g_db->num_rows($check_query) ) {
              continue;
            }

            $seq = (isset($_POST['seq']) && isset($_POST['seq'][$subzone_id]))?$_POST['seq'][$subzone_id]:1;
            $check_array = $g_db->fetch_array($check_query);
            $sql_data_array = array(
                                    'abstract_zone_id' => (int)$this->m_zID,
                                    'subzone_id' => (int)$subzone_id,
                                    'sub_alt_title' => $g_db->prepare_input($check_array['abstract_zone_name']),
                                    'sequence_order' => (int)$seq,
                                   );
            $g_db->perform(TABLE_SUPER_ZONES, $sql_data_array);
          }
          tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action', 'sID')) . 'action=list&sID=' . $subzone_id));
          break;

        default:
          break;
      }
    }

    function deleteconfirm_multizone() {
      global $g_db;

      foreach($_POST['gt_id'] as $subzone_id => $val) {
        $g_db->query("delete from " . TABLE_SUPER_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "' and subzone_id = '" . (int)$subzone_id . "'");
      }
      tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
    }

    function deleteconfirm_zone() {
      // Let our parent take care of us
      parent::deleteconfirm_zone();
      tep_redirect(tep_href_link(FILENAME_ABSTRACT_ZONES));
    }

    function display_html() {
      switch( $this->m_action ) {
        case 'list':
          $result = $this->display_list();
          break;
        case 'multi_entries':
          $result = $this->display_multi_entries();
          break;
        case 'delete_multizone':
          $result = $this->display_delete_multizone();
          break;
        case 'apply_multizone':
          $result = $this->display_apply_multizone();
          break;
        default:
          $result = $this->display_default();
          $result .= $this->display_bottom();
          break;
      }
      return $result;
    }

// List of entries
    function display_list() {
      global $g_db;

      $html_string = '';
      $rows = 0;
      $zones_query_raw = "select subzone_id, sub_alt_title, sequence_order from " . TABLE_SUPER_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "' order by sequence_order, sub_alt_title";
      $zones_split = new splitPageResults($zones_query_raw, ABSTRACT_PAGE_SPLIT, '', 'spage');

      if( $zones_split->number_of_rows > 0 ) {
        $html_string .= 
        '        <div class="formArea">' . tep_draw_form('rl', FILENAME_ABSTRACT_ZONES, 'action=delete_multizone&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage . '&spage=' . $this->m_spage, 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n";

        if(empty($this->m_saction)) {

          $html_string .= 
          '          <tr>' . "\n" . 
          '            <td colspan="5" class="formButtons"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'name="update_multizone"') . ' ' . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'name="delete_multizone"') . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
        $html_string .= 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <td class="dataTableHeadingContent"><a href="javascript:void(0)" onclick="check_boxes(document.rl)" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</span></a></td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_ENTRIES . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_ALT_TITLE . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_SEQUENCE_ORDER . '</td>' . "\n" . 
        '          </tr>' . "\n";
        $zones_query = $g_db->query($zones_split->sql_query);
        $bCheck = false;
        while( $zones_array = $g_db->fetch_array($zones_query) ) {
          $extra_query = $g_db->query("select abstract_zone_name, status_id from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id != '" . (int)$this->m_zID . "' and abstract_zone_id = '" . (int)$zones_array['subzone_id'] . "'");
          if( $g_db->num_rows($extra_query) ) {
            $extra_array = $g_db->fetch_array($extra_query);
            $final_name = '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action', 'spage', 'zID')) . 'zID=' . $zones_array['subzone_id'] . '&action=list') . '" title="' . $zones_array['sub_alt_title'] . '">' . $extra_array['abstract_zone_name'] . '</a>';
            $status_id = $extra_array['status_id'];
          } else {
            $final_name = '<font color="FF0000">' . TEXT_INFO_NA . '&nbsp;[' . $zones_array['sub_alt_title'] . ']' . '</font>';
            $status_id = 1;
          }
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

          if(!$status_id) {
            $row_class = 'dataTableRowImpact';
          }

          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_checkbox_field('gt_id[' . $zones_array['subzone_id'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . $final_name . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_input_field('alt_title[' . $zones_array['subzone_id'] . ']', $zones_array['sub_alt_title'], 'size="24" maxlength="255"') . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_input_field('seq[' . $zones_array['subzone_id'] . ']', $zones_array['sequence_order'], 'size="3" maxlength="3"') . '</td>' . "\n" . 
          '          </tr>'  . "\n";
        }
        if(empty($this->m_saction)) {
          $html_string .= 
          '          <tr>' . "\n" . 
          '            <td colspan="5" class="formButtons"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'name="update_multizone"') . ' ' . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'name="delete_multizone"') . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
        $html_string .= 
        '          </table></form></div>' . "\n" . 
        '          <div class="splitLine">' . "\n" . 
        '            <div style="float: left;">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
        '            <div style="float: right;">' . $zones_split->display_links(tep_get_all_get_params(array('action', 'spage')) . 'action=list') . '</div>' . "\n" . 
        '          </div>' . "\n";

      } else {
        $html_string .= 
        '        <div class="comboHeading">' . "\n" . 
        '          <div class="smallText">' . TEXT_INFO_NO_ENTRIES . '</div>' . "\n" . 
        '        </div>' . "\n";
      }
      if (empty($this->m_saction)) {
        $html_string .= 
        '        <div class="formButtons"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=multi_entries') . '">' . tep_image_button('button_entries.gif', 'Switch to Generic Entries Mode') . '</a></div>' . "\n";
      }
      return $html_string;
    }


    function display_multi_entries() {
      global $g_db;
      $html_string = '';
      $html_string .=
      '        <div class="comboHeading">' . "\n" . 
      '          <div class="smallText">' . TEXT_SELECT_MULTIENTRIES . '</div>' . "\n" . 
      '        </div>' . "\n" . 
      '        <div class="formArea">' . tep_draw_form('mc', FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=insert_multi_entries', 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent"><a href="javascript:void(0)" onclick="check_boxes(document.mc)" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></td>' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_ENTRIES . '</td>' . "\n" . 
      '            </tr>' . "\n"; 
      $rows = 0;
      $zones_query_raw = "select abstract_zone_id, abstract_zone_name, status_id from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id != '" . (int)$this->m_zID . "' order by abstract_zone_name";
      $zones_split = new splitPageResults($zones_query_raw, ABSTRACT_PAGE_SPLIT, '', 'mcpage');
      $zones_query = $g_db->query($zones_split->sql_query);
      $bCheck = false;
      while( $zones_array = $g_db->fetch_array($zones_query) ) {
        $check_query = $g_db->query("select count(*) as total from " . TABLE_SUPER_ZONES . " gz2d where gz2d.abstract_zone_id = '" . (int)$this->m_zID . "' and gz2d.subzone_id = '" . (int)$zones_array['abstract_zone_id'] . "'");
        $check_array = $g_db->fetch_array($check_query);
        $bCheck  = $check_array['total']?true:false;

        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if($bCheck) {
          $row_class = 'dataTableRowGreen';
        }
        if(!$zones_array['status_id']) {
          $row_class = 'dataTableRowImpact';
        }

        $html_string .=
        '            <tr class="' . $row_class . '">' . "\n" . 
        '              <td class="dataTableContent">' . ($bCheck?'Included':tep_draw_checkbox_field('gt_id[' . $zones_array['abstract_zone_id'] . ']')) . '</td>' . "\n" . 
        '              <td class="dataTableContent">' . '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action', 'spage', 'zID')) . 'zID=' . $zones_array['abstract_zone_id'] . '&action=list') . '" title="' . $zones_array['abstract_zone_name'] . '">' . $zones_array['abstract_zone_name'] . '</a></td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .=
      '              <tr>' . "\n" . 
      '                <td colspan="3" class="formButtons"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action', 'mcpage')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_draw_hidden_field('multi_form', 'insert_multi_entries') . '&nbsp;' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '</td>' . "\n" . 
      '              </tr>' . "\n" . 
      '            </table></form></div>' . "\n" . 
      '            <div class="splitLine">' . "\n" . 
      '              <div style="float: left;">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
      '              <div style="float: right;">' . $zones_split->display_links(tep_get_all_get_params(array('action', 'mcpage')) . 'action=multi_entries') . '</div>' . "\n" . 
      '            </div>' . "\n";
      return $html_string;
    }

    function display_delete_multizone() {
      global $g_db;

      $html_string = '';
      $zones_query = $g_db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_types_id != '" . (int)$this->m_type . "' and abstract_zone_id = '" . (int)$this->m_zID . "'");
      $zones_array = $g_db->fetch_array($zones_query);
      $html_string .= 
      '          <div class="comboHeading">' . "\n" . 
      '            <div class="smallText">' . sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zones_array['abstract_zone_name']) . '</div>' . "\n" . 
      '          </div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=deleteconfirm_multizone', 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_ENTRIES . '</td>' . "\n" . 
      '            </tr>' . "\n";
      $rows = 0;
      foreach ($_POST['gt_id'] as $subzone_id=>$val) {
        $delete_query = $g_db->query("select gz2d.abstract_zone_id, gz2d.subzone_id, gt.abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " gt left join " . TABLE_SUPER_ZONES . " gz2d on (gz2d.subzone_id = gt.abstract_zone_id) where gt.abstract_types_id != '" . (int)$this->m_type . "' and gz2d.subzone_id = '" . (int)$subzone_id . "' and gz2d.abstract_zone_id = '" . (int)$this->m_zID . "' order by gt.abstract_zone_name");
        if( $delete_array = $g_db->fetch_array($delete_query) ) {
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_hidden_field('gt_id[' . $delete_array['subzone_id'] . ']', $delete_array['subzone_id']) . $delete_array['abstract_zone_name'] . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
      }
      if( count($_POST['gt_id']) ) {
        $html_string .= 
        '            <tr>' . "\n" . 
        '              <td colspan="4" class="formButtons"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, tep_get_all_get_params(array('action')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '            </table></form></div>' . "\n";
      return $html_string;
    }
  }
?>