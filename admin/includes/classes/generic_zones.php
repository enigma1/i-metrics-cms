<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Generic Text Zones class
//----------------------------------------------------------------------------
// This is a Bridge for Abstract Zones Class
// Groups text pages enables sequence of pages
// Featuring:
// - Multi-Text Entries instant selection/insertion
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

  class generic_zones extends abstract_zones {
    var $m_spage;
// class constructor
    function generic_zones() {}

    function initialize() {
      $this->m_spage = isset($_GET['spage'])?$_GET['spage']:'';
      parent::initialize();
      if (isset($_POST['delete_multizone_x']) || isset($_POST['delete_multizone_y'])) $this->m_action='delete_multizone';
      if (isset($_POST['update_multizone_x']) || isset($_POST['update_multizone_y'])) $this->m_action='update_multizone';
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
      extract(tep_load('defs', 'database'));

      foreach ($_POST['gt_id'] as $gtext_id=>$val) {
        $sql_data_array = array(
          'gtext_alt_title' => $db->prepare_input($_POST['alt_title'][$gtext_id]),
          'sequence_order' => (int)$_POST['seq'][$gtext_id],
        );
        $db->perform(TABLE_GTEXT_TO_DISPLAY, $sql_data_array, 'update', "abstract_zone_id = '" . (int)$this->m_zID . "' and gtext_id = '" . (int)$gtext_id . "'");
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
    }


    function insert_multi_entries() {
      extract(tep_load('defs', 'database'));

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');
      switch( $multi_form ) {
        case 'insert_multi_entries':
          foreach($_POST['gt_id'] as $gtext_id=>$val) {
            $check_query = $db->query("select gtext_id from " . TABLE_GTEXT_TO_DISPLAY . " where gtext_id = '" . (int)$gtext_id . "' and abstract_zone_id = '" . (int)$this->m_zID . "'");
            if( $db->num_rows($check_query) )
                continue;

            $check_query = $db->query("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
            if( !$db->num_rows($check_query) )
              continue;

            $seq = (isset($_POST['seq']) && isset($_POST['seq'][$gtext_id]))?$_POST['seq'][$gtext_id]:1;

            $check_array = $db->fetch_array($check_query);
            $sql_data_array = array(
              'abstract_zone_id' => (int)$this->m_zID,
              'gtext_id' => (int)$gtext_id,
              'gtext_alt_title' => $db->prepare_input($check_array['gtext_title']),
              'sequence_order' => (int)$seq,
            );
            $db->perform(TABLE_GTEXT_TO_DISPLAY, $sql_data_array);
          }
          tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action', 'sID') . 'action=list&sID=' . $gtext_id));
          break;

        default:
          break;
      }
    }

    function deleteconfirm_multizone() {
      extract(tep_load('defs', 'database'));

      foreach($_POST['gt_id'] as $gtext_id => $val) {
        $db->query("delete from " . TABLE_GTEXT_TO_DISPLAY . " where abstract_zone_id = '" . (int)$this->m_zID . "' and gtext_id = '" . (int)$gtext_id . "'");
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
    }

    function deleteconfirm_zone() {
      extract(tep_load('defs', 'database'));

      $db->query("delete from " . TABLE_GTEXT_TO_DISPLAY . " where abstract_zone_id = '" . (int)$this->m_zID . "'");
      parent::deleteconfirm_zone();
      tep_redirect(tep_href_link($cDefs->script));
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
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $rows = 0;
      $zones_query_raw = "select gtext_id, gtext_alt_title, sequence_order from " . TABLE_GTEXT_TO_DISPLAY . " where abstract_zone_id = '" . (int)$this->m_zID . "' order by sequence_order, gtext_alt_title";
      $zones_split = new splitPageResults($zones_query_raw, ABSTRACT_PAGE_SPLIT, '', 'spage');

      $html_string .= 
      '        <div class="comboHeading">' . "\n" .
      '          <div class="dataTableRowAlt3 spacer floater"><a class="blockbox" href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=multi_entries') . '">' . TEXT_INFO_ASSIGN_TEXT . '</a></div>'. "\n" . 
      '          <div class="spacer">' . TEXT_INFO_ASSIGN_TEXT_HELP . '</div>' . "\n" . 
      '        </div>'. "\n";

      if( $zones_split->number_of_rows > 0 ) {
        $buttons = array();
        if(empty($this->m_saction)) {
          $buttons= array(
            '<a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>',
            tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'class="dflt" name="update_multizone"') . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'class="dflt" name="delete_multizone"'),
          ); 
        }

        $html_string .= 
        '        <div class="formArea">' . tep_draw_form('rl', $cDefs->script, 'action=delete_multizone&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage . '&spage=' . $this->m_spage, 'post') . "\n";

        //if(empty($this->m_saction)) {
        //  $html_string .= '<div class="formButtons"><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'class="dflt" name="update_multizone"') . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'class="dflt" name="delete_multizone"') . '</div>';
        //}

        $html_string .= 
        '        <table class="tabledata" id="abstract_table">' . "\n" . 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <th class="calign"><a href="#gt_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
        '            <th>' . TABLE_HEADING_ENTRIES . '</th>' . "\n" . 
        '            <th class="halfer">' . TABLE_HEADING_ALT_TITLE . '</th>' . "\n" . 
        '            <th class="calign">' . TABLE_HEADING_SEQUENCE_ORDER . '</th>' . "\n" . 
        '          </tr>' . "\n";
        $zones_query = $db->query($zones_split->sql_query);
        $bCheck = false;
        while( $zones_array = $db->fetch_array($zones_query) ) {
          $extra_query = $db->query("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$zones_array['gtext_id'] . "'");
          if( $db->num_rows($extra_query) ) {
            $extra_array = $db->fetch_array($extra_query);
            $final_name = '<a href="' . tep_href_link(FILENAME_GENERIC_TEXT, 'gtID=' . $zones_array['gtext_id'] . '&action=new_generic_text') . '" title="' . $zones_array['gtext_alt_title'] . '">' . $extra_array['gtext_title'] . '</a>';
          } else {
            $final_name = '<font color="FF0000">' . TEXT_INFO_NA . '&nbsp;[' . $zones_array['gtext_alt_title'] . ']' . '</font>';
          }
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';


          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="calign">' . tep_draw_checkbox_field('gt_id[' . $zones_array['gtext_id'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '            <td>' . $final_name . '</td>' . "\n" . 
          '            <td><div class="rpad">' . tep_draw_input_field('alt_title[' . $zones_array['gtext_id'] . ']', $zones_array['gtext_alt_title'], 'maxlength="255"') . '</div></td>' . "\n" . 
          '            <td class="calign">' . tep_draw_input_field('seq[' . $zones_array['gtext_id'] . ']', $zones_array['sequence_order'], 'size="3" maxlength="3"') . '</td>' . "\n" . 
          '          </tr>'  . "\n";
        }
        $html_string .= 
        '          </table><div class="formButtons">' . implode('', $buttons) . '</div></form></div>' . "\n"; 
        $html_string .= 
        '          <div class="listArea splitLine">' . "\n" . 
        '            <div class="floater">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
        '            <div class="floatend">' . $zones_split->display_links(tep_get_all_get_params('action', 'spage') . 'action=list') . '</div>' . "\n" . 
        '          </div>' . "\n";
      }
      return $html_string;
    }


    function display_multi_entries() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $html_string .=
      '        <div class="comboHeading">' . TEXT_SELECT_MULTIENTRIES . '</div>' . "\n" . 
      '        <div class="formArea">' . tep_draw_form('mc', $cDefs->script, tep_get_all_get_params('action') . 'action=insert_multi_entries', 'post') . '<table class="tabledata">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <th class="calign"><a href="#gt_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
      '              <th>' . TABLE_HEADING_ENTRIES . tep_draw_hidden_field('multi_form', 'insert_multi_entries') . '</th>' . "\n" . 
      '            </tr>' . "\n";

      $buttons = array(
        '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'mcpage') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_insert.gif', IMAGE_INSERT),
      );
 
      $rows = 0;
      $zones_query_raw = "select gt.gtext_id, gt.gtext_title from " . TABLE_GTEXT . " gt order by gt.gtext_title";
      $zones_split = new splitPageResults($zones_query_raw, ABSTRACT_PAGE_SPLIT, '', 'mcpage');
      $zones_query = $db->query($zones_split->sql_query);
      $bCheck = false;
      while( $zones_array = $db->fetch_array($zones_query) ) {
        $check_query = $db->query("select gz2d.abstract_zone_id, gz2d.gtext_id from " . TABLE_GTEXT_TO_DISPLAY . " gz2d where gz2d.abstract_zone_id = '" . (int)$this->m_zID . "' and gz2d.gtext_id = '" . (int)$zones_array['gtext_id'] . "'");
        $bCheck  = $db->num_rows($check_query)?true:false;

        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if($bCheck) {
          $row_class = 'dataTableRowGreen';
        }
        $html_string .=
        '            <tr class="' . $row_class . '">' . "\n" . 
        '              <td class="calign">' . ($bCheck?'Included':tep_draw_checkbox_field('gt_id[' . $zones_array['gtext_id'] . ']')) . '</td>' . "\n" . 
        '              <td>' . $zones_array['gtext_title'] . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .=
      '            </table><div class="formButtons">' . implode('', $buttons) . '</div></form></div>' . "\n" . 
      '            <div class="listArea splitLine">' . "\n" . 
      '              <div class="floater">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
      '              <div class="floatend">' . $zones_split->display_links(tep_get_all_get_params('action', 'mcpage') . 'action=multi_entries') . '</div>' . "\n" . 
      '            </div>' . "\n";
      return $html_string;
    }

    function display_delete_multizone() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $zones_query = $db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$this->m_zID . "'");
      $zones_array = $db->fetch_array($zones_query);
      $html_string .= 
      '          <div class="comboHeading">' . sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zones_array['abstract_zone_name']) . '</div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', $cDefs->script, tep_get_all_get_params('action') . 'action=deleteconfirm_multizone', 'post') . '<table class="tabledata">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <th>' . TABLE_HEADING_ENTRIES . '</th>' . "\n" . 
      '            </tr>' . "\n";

      $buttons = array(
        '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
      );

      $rows = 0;
      foreach ($_POST['gt_id'] as $gtext_id=>$val) {
        $delete_query = $db->query("select gz2d.abstract_zone_id, gz2d.gtext_id, gt.gtext_title from " . TABLE_GTEXT . " gt left join " . TABLE_GTEXT_TO_DISPLAY . " gz2d on (gz2d.gtext_id = gt.gtext_id) where gz2d.gtext_id = '" . (int)$gtext_id . "' and gz2d.abstract_zone_id = '" . (int)$this->m_zID . "' order by gt.gtext_title");
        if( $delete_array = $db->fetch_array($delete_query) ) {
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td>' . tep_draw_hidden_field('gt_id[' . $delete_array['gtext_id'] . ']', $delete_array['gtext_id']) . $delete_array['gtext_title'] . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
      }
      $html_string .= 
      '            </table><div class="formButtons">' . implode('', $buttons) . '</div></form></div>' . "\n";
      return $html_string;
    }

    function display_assignment() {

    }
  }
?>