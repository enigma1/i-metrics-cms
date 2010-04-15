<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// META-G Abstract Zones class for Admin
// This is a Bridge for META-G
// Processes Abstract Zones generates meta-tag segments.
// Featuring:
// - Multi-Abstract Zones Listings with Meta-Tags
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

  class meta_abstract extends meta_zones {
    var $error_array;

// class constructor
    function meta_abstract() {
      $this->m_ssID = isset($_GET['ssID'])?$_GET['ssID']:'';
      $this->m_mcpage = isset($_GET['mcpage'])?$_GET['mcpage']:'';
      $this->m_mppage = isset($_GET['mppage'])?$_GET['mppage']:'';
      parent::meta_zones();
    }

    function generate_name($abstract_zone_id) {
      global $g_db;
      $name = '';
      $name_query = $g_db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      if( $names_array = $g_db->fetch_array($name_query) ) {
        $name = $names_array['abstract_zone_name'];
        $name =  $this->create_safe_string($name);
      }
      return $name;
    }

    function generate_lexico($index=0) {
      global $g_db;

      $abstract_query = $g_db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . "");
      while( $abstract_array = $g_db->fetch_array($abstract_query) ) {

        $phrase = $this->create_safe_string($abstract_array['abstract_zone_name']);
        $md5_key = md5($phrase);
        $check_query = $g_db->query("select count(*) as total from " . TABLE_META_LEXICO . " where meta_lexico_key = '" . $g_db->filter($md5_key) . "'");
        $check_array = $g_db->fetch_array($check_query);
        if( !$check_array['total'] ) {
          $sql_data_array = array(
                                  'meta_lexico_key' => $g_db->prepare_input($md5_key),
                                  'meta_lexico_text' => $g_db->prepare_input($phrase)
                                 );
          $g_db->perform(TABLE_META_LEXICO, $sql_data_array);
        }
      }
    }

    function get_zone_names($zone_id) {
      global $g_db;

      $keywords_array = array();

      $types_query = $g_db->query("select abstract_types_class, abstract_types_table from " . TABLE_ABSTRACT_TYPES . " azt left join " . TABLE_ABSTRACT_ZONES . " az on (az.abstract_types_id=azt.abstract_types_id) where az.abstract_zone_id = '" . (int)$zone_id . "'");
      if( !$g_db->num_rows($types_query) )
        return $keywords_array;
      $types_array = $g_db->fetch_array($types_query);

      switch($types_array['abstract_types_class']) {
        case 'generic_zones':
          $keywords_array = $this->get_group_text($zone_id, $types_array['abstract_types_table']);
          break;
        default:
          return $keywords_array;

      }
      return $keywords_array;
    }

    function get_group_text($zone_id, $table) {
      global $g_db;


      $text_array = array();

      $tables_array = explode(',', $table);
      if( !is_array($tables_array) ) {
        return $text_array;
      }

      $text_query = $g_db->query("select gtd.gtext_id, gt.gtext_title from " . TABLE_GTEXT . " gt left join " . $g_db->filter($tables_array[0]) . " gtd on (gtd.gtext_id=gt.gtext_id) where gtd.abstract_zone_id = '" . (int)$zone_id . "' and gt.status='1' order by gtd.sequence_order");
      while($text = $g_db->fetch_array($text_query) ) { 
        $text_array['txt_' . $text['gtext_id']] = $this->create_safe_string($text['gtext_title']);
        if( count($text_array) > META_MAX_KEYWORDS ) {
          break;
        }
      }
      return $text_array;
    }


    function process_action() {
      switch( $this->m_action ) {
        case 'validate':
          return $this->validate();
        case 'validate_confirm':
          return $this->validate_confirm();
        case 'update_multizone':
          $result = parent::validate_array_selection('pc_id'); 
          return $this->update_multizone();
        case 'insert_multientries':
          $result = parent::validate_array_selection('pc_id', 'multi_entries'); 
          return $this->insert_multientries();
        case 'deleteconfirm_multizone':
          $result = parent::validate_array_selection('pc_id'); 
          return $this->deleteconfirm_multizone();
        case 'delete_multizone':
          $result = parent::validate_array_selection('pc_id'); 
          break;
        default:
          return parent::process_action(); 
          break;
      }
    }

    function validate() {
      global $g_db;

      $this->error_array = array();
      // First pass 
      $check_query = $g_db->query("select pd.abstract_zone_id, pd.abstract_zone_name as name, if(s2p.abstract_zone_id, s2p.abstract_zone_id, 0) as missing_id from " . TABLE_ABSTRACT_ZONES . " pd left join " . TABLE_META_ABSTRACT . " s2p on ((s2p.abstract_zone_id = if(s2p.abstract_zone_id,pd.abstract_zone_id,0))) order by pd.abstract_zone_id desc");
      while( $check_array = $g_db->fetch_array($check_query) ) {
        if( !$check_array['missing_id'] ) {
          $this->error_array[] = $check_array;
        }
        if( count($this->error_array) >= META_PAGE_SPLIT )
          break;
      }
      // Second pass check for redundant entries
      $check_query = $g_db->query("select s2p.abstract_zone_id, if(pd.abstract_zone_id, pd.abstract_zone_name, s2p.meta_name) as name, if(pd.abstract_zone_id, pd.abstract_zone_id, -1) as missing_id from " . TABLE_META_ABSTRACT . " s2p left join " . TABLE_ABSTRACT_ZONES . " pd on (s2p.abstract_zone_id = if(pd.abstract_zone_id,pd.abstract_zone_id,0)) order by s2p.abstract_zone_id desc");
      while( $check_array = $g_db->fetch_array($check_query) ) {
        if( $check_array['missing_id'] == -1 ) {
          $this->error_array[] = $check_array;
        }
        if( count($this->error_array) >= META_PAGE_SPLIT )
          break;
      }
      return $this->error_array;
    }

    function validate_confirm() {
      global $g_db;

      foreach($_POST['pc_id'] as $abstract_zone_id => $val) {
        if( $_POST['missing'][$abstract_zone_id] == -1 ) {
          $g_db->query("delete from " . TABLE_META_ABSTRACT . " where meta_types_id = '" . (int)$this->m_zID . "' and abstract_zone_id = '" . (int)$abstract_zone_id . "'");
        } elseif( $_POST['missing'][$abstract_zone_id] == 0 ) {
          $meta_name = $this->generate_name($abstract_zone_id);
          $sql_data_array = array(
                                  'meta_types_id' => (int)$this->m_zID,
                                  'abstract_zone_id' => (int)$abstract_zone_id,
                                  'meta_name' => $g_db->prepare_input($meta_name),
                                  );
          $g_db->perform(TABLE_META_ABSTRACT, $sql_data_array, 'insert');
        }
      }
      tep_redirect(tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=validate'));
    }

    function update_multizone() {
      global $g_db;

      foreach($_POST['pc_id'] as $abstract_zone_id => $val) {

        $meta_title = $_POST['title'][$abstract_zone_id];
        $meta_keywords = $_POST['keywords'][$abstract_zone_id];
        $meta_text = $_POST['text'][$abstract_zone_id];

        $sql_data_array = array(
                                'meta_title' => $g_db->prepare_input($meta_title),
                                'meta_keywords' => $g_db->prepare_input($meta_keywords),
                                'meta_text' => $g_db->prepare_input($meta_text)
                               );

        $g_db->perform(TABLE_META_ABSTRACT, $sql_data_array, 'update', "abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      }
      tep_redirect(tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
    }


    function insert_multientries() {
      global $g_db, $messageStack;

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');

      switch( $multi_form ) {
        case 'multi_entries':
          $tmp_array = array();
          foreach ($_POST['pc_id'] as $abstract_id=>$val) {
            $multi_query = $g_db->query("select abstract_zone_id, abstract_zone_name, abstract_zone_desc from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$abstract_id . "'");
            while( $multi = $g_db->fetch_array($multi_query) ) {
              $check_query = $g_db->query("select abstract_zone_id from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$multi['abstract_zone_id'] . "'");
              if( $g_db->num_rows($check_query) )
                continue;

              $meta_name = $this->create_safe_string($multi['abstract_zone_name']);
              $keywords_array = $this->get_zone_names($multi['abstract_zone_id']);
              
              $meta_text = '';
              if( strlen($meta_text) < META_MAX_DESCRIPTION ) {
                $meta_text .= $this->create_safe_description($multi['abstract_zone_desc']) . ' ';
              }

              if( count($keywords_array) ) {
                $meta_keywords = implode(',',$keywords_array);
              } else {
                $meta_keywords = $meta_name;
              }
              if( strlen($meta_text) ) {
                $meta_text = substr($meta_text, 0, -1);
              } else {
                $meta_text = $meta_name;
              }

              $sql_data_array = array(
                                      'abstract_zone_id' => (int)$abstract_id,
                                      'meta_title' => $g_db->prepare_input($meta_name),
                                      'meta_keywords' => $g_db->prepare_input($meta_keywords),
                                      'meta_text' => $g_db->prepare_input($meta_text)
                                     );
              $g_db->perform(TABLE_META_ABSTRACT, $sql_data_array, 'insert');

            }
          }
          $messageStack->add_session(SUCCESS_SELECTED_ADDED, 'success');
          tep_redirect(tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
          break;
        default:
          break;
      }
    }

    function deleteconfirm_multizone() {
      global $g_db;

      for($i=0, $j=count($_POST['pc_id']); $i<$j; $i++ ) {
        $abstract_zone_id = $_POST['pc_id'][$i];
        $g_db->query("delete from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      }
      tep_redirect(tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=list'));
    }


    function display_html() {
      switch( $this->m_action ) {
        case 'validate':
          $result = $this->display_validation();
          break;
        case 'list':
          $result = $this->display_list();
          break;
        case 'multi_entries':
          $result = $this->display_multi_entries();
          break;
        case 'delete_multizone':
          $result = $this->display_delete_multizone();
          break;
        default:
          $result = $this->display_default();
          $result .= $this->display_bottom();
          break;
      }
      return $result;
    }

    function display_validation() {
      $html_string = '';
      $html_string .= 
      '      <tr>' . "\n" . 
      '        <td><hr /></td>' . "\n" . 
      '      </tr>' . "\n";
      if( count($this->error_array) ) {
        $html_string .= 
        '      <tr>' . "\n" . 
        '        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n" . 
        '          <tr>' . "\n" . 
        '            <td class="dataTableRowHighBorder" width="16">&nbsp;</td>' . "\n" . 
        '            <td class="smallText"><b>&nbsp;-&nbsp;Zone present in the abstract zones table but not present in the META-G table</b></td>' . "\n" . 
        '          </tr>' . "\n" . 
        '          <tr>' . "\n" . 
        '            <td colspan="2">' . tep_draw_separator('pixel_trans.gif', '100%', '1') . '</td>' . "\n" . 
        '          </tr>' . "\n" . 
        '          <tr>' . "\n" . 
        '            <td class="dataTableRowImpactBorder" width="16">&nbsp;</td>' . "\n" . 
        '            <td class="smallText"><b>&nbsp;-&nbsp;Zone present in the META-G table but it is not present in the Abstract Zones table</b></td>' . "\n" . 
        '          </tr>' . "\n" . 
        '        </table></td>' . "\n" . 
        '      </tr>' . "\n" .
        '      <tr>' . "\n" . 
        '        <td>' . tep_draw_separator('pixel_trans.gif', '100%', '10') . '</td>' . "\n" . 
        '      </tr>' . "\n";
        $html_string .= 
        '      <tr>' . "\n" . 
        '        <td valign="top">' . tep_draw_form('rl', FILENAME_META_ZONES, 'action=validate_confirm&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage, 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <td class="dataTableHeadingContent" width="40"><a href="javascript:void(0)" onClick="copy_checkboxes(document.rl, \'pc_id\')" title="Page Select On/Off" class="menuBoxHeadingLink"><span class="dataTableHeadingContent">' . TABLE_HEADING_SELECT . '</span></a></td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_ID . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_NAME . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_COMMENT . '</td>' . "\n" . 
        '          </tr>' . "\n";
        for($i=0, $j=count($this->error_array); $i<$j; $i++ ) {
          $row_class = ($this->error_array[$i]['missing_id'])?'dataTableRowImpact':'dataTableRowHigh';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_checkbox_field('pc_id[' . $this->error_array[$i]['abstract_zone_id'] . ']', 'on', false ) . tep_draw_hidden_field('missing[' . $this->error_array[$i]['abstract_zone_id'] . ']', $this->error_array[$i]['missing_id']) . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . $this->error_array[$i]['abstract_zone_id'] . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . $this->error_array[$i]['name'] . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . (($this->error_array[$i]['missing_id'])?'Missing from Abstract Table':'Missing from META-G') . '</td>' . "\n" . 
          '          </tr>'  . "\n";
        }
        $html_string .= 
        '          <tr>' . "\n" . 
        '            <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n" . 
        '              <tr>' . "\n" . 
        '                <td><a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_fix_errors.gif', 'Fix Listed Errors') . '</td>' . "\n" . 
        '              </tr>' . "\n" . 
        '            </table></td>' . "\n" . 
        '          </tr>' . "\n" .
        '        </table></form></td>' . "\n" . 
        '      </tr>' . "\n";
      } else {
        $html_string .= 
        '      <tr>' . "\n" . 
        '        <td class="smallText">' . 'No Errors Found' . '</td>' . "\n" . 
        '      </tr>' . "\n" . 
        '      <tr>' . "\n" . 
        '        <td colspan="2">' . tep_draw_separator('pixel_trans.gif', '100%', '4') . '</td>' . "\n" . 
        '      </tr>' . "\n" . 
        '      <tr>' . "\n" . 
        '        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">' . "\n" . 
        '          <tr>' . "\n" . 
        '            <td><a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a></td>' . "\n" . 
        '          </tr>' . "\n" . 
        '        </table></td>' . "\n" . 
        '      </tr>' . "\n";
      }
      return $html_string;
    }

// Default List
    function display_list() {
      global $g_db;

      $html_string = '';
      $rows = 0;
      $zones_query_raw = "select * from " . TABLE_META_ABSTRACT . " order by meta_title, abstract_zone_id";
      $zones_split = new splitPageResults($zones_query_raw, META_PAGE_SPLIT, '', 'spage');

      if( $zones_split->number_of_rows > 0 ) {
        $html_string .= 
        '        <div class="formArea">' . tep_draw_form('rl', FILENAME_META_ZONES, 'action=delete_multizone&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage . '&spage=' . $this->m_spage, 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n";

        if(empty($this->saction)) {
          $html_string .= 
          '          <tr>' . "\n" . 
          '            <td colspan="5" class="formButtons"><a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=update_multizone') . '\'' . '"') . ' ' . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=delete_multizone') . '\'' . '"') . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
        $html_string .= 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <td class="dataTableHeadingContent"><a href="javascript:void(0)" onclick="copy_checkboxes(document.rl, \'pc_id\')" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_TITLE . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_KEYWORDS . '</td>' . "\n" . 
        '            <td class="dataTableHeadingContent">' . TABLE_HEADING_DESCRIPTION . '</td>' . "\n" . 
        '          </tr>' . "\n";
        $zones_query = $g_db->query($zones_split->sql_query);
        $bCheck = false;
        while( $zones_array = $g_db->fetch_array($zones_query) ) {

          $extra_query = $g_db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$zones_array['abstract_zone_id'] . "'");
          if( $g_db->num_rows($extra_query) ) {
            $extra_array = $g_db->fetch_array($extra_query);
            $final_name = '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $zones_array['abstract_zone_id'] . '&action=edit_zone') . '">' . $extra_array['abstract_zone_name'] . '</a>';
          } else {
            $final_name = '<font color="#FF0000"><b>' . TEXT_INFO_NA . '&nbsp;[' . $zones_array['meta_title'] . ']' . '</b></font>';
          }

          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_checkbox_field('pc_id[' . $zones_array['abstract_zone_id'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . $final_name . '<br />' . tep_draw_input_field('title[' . $zones_array['abstract_zone_id'] . ']', $zones_array['meta_title'], 'style="width: 300px"') . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_textarea_field('keywords[' . $zones_array['abstract_zone_id'] . ']', 'soft', '40','2', $zones_array['meta_keywords']) . '</td>' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_textarea_field('text[' . $zones_array['abstract_zone_id'] . ']', 'soft', '40','2', $zones_array['meta_text']) . '</td>' . "\n" . 
          '          </tr>'  . "\n";
        }
        if(empty($this->saction)) {
          $html_string .= 
          '          <tr>' . "\n" . 
          '            <td colspan="5" class="formButtons"><a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=update_multizone') . '\'' . '"') . ' ' . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'onclick="this.form.action=' . '\'' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=delete_multizone') . '\'' . '"') . '</td>' . "\n" . 
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
      if (empty($this->saction)) {
        $html_string .= 
        '          <div class="formButtons"><a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> <a href="' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=multi_entries') . '">' . tep_image_button('button_zones.gif', TEXT_SWITCH_ZONES) . '</a></div>' . "\n";
      }
      return $html_string;
    }


    function display_multi_entries() {
      global $g_db;

      $html_string = '';
      $html_string .=
      '        <div class="comboHeading">' . "\n" . 
      '          <div class="smallText">' . TEXT_SELECT_MULTIZONES . '</div>' . "\n" . 
      '        </div>' . "\n" . 
      '        <div class="formArea">' . tep_draw_form('mc', FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=insert_multientries', 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '          <tr class="dataTableHeadingRow">' . "\n" . 
      '            <td class="dataTableHeadingContent"><a href="javascript:void(0)" onclick="copy_checkboxes(document.mc, \'pc_id\')" title="' . TEXT_PAGE_SELECT . '" class="menuBoxHeadingLink">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></td>' . "\n" . 
      '            <td class="dataTableHeadingContent">' . TABLE_HEADING_ZONES . '</td>' . "\n" . 
      '            <td class="dataTableHeadingContent">' . TABLE_HEADING_TYPES . '</td>' . "\n" . 
      '          </tr>' . "\n"; 
      $rows = 0;
      //$zones_query_raw = "select abstract_zone_id, abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " order by abstract_zone_name";
      $zones_query_raw = "select az.abstract_zone_id, az.abstract_zone_name, azt.abstract_types_name, if(mm.abstract_zone_id, '1', '0') as checkbox from " . TABLE_ABSTRACT_ZONES . " az left join " . TABLE_ABSTRACT_TYPES . " azt on (az.abstract_types_id=azt.abstract_types_id) left join " . TABLE_META_ABSTRACT . " mm on ((mm.abstract_zone_id = if(mm.abstract_zone_id, az.abstract_zone_id,0))) order by az.abstract_zone_id, az.abstract_zone_name";
      $zones_split = new splitPageResults($zones_query_raw, META_PAGE_SPLIT, '', 'mcpage');
      $zones_query = $g_db->query($zones_split->sql_query);
      $bCheck = false;
      while( $zones_array = $g_db->fetch_array($zones_query) ) {
        $bCheck = ($zones_array['checkbox'] == '1')?true:false;
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if($bCheck)
          $row_class = 'dataTableRowGreen';

        $html_string .=
        '            <tr class="' . $row_class . '">' . "\n" . 
        '              <td class="dataTableContent">' . ($bCheck?'Included':tep_draw_checkbox_field('pc_id[' . $zones_array['abstract_zone_id'] . ']')) . '</td>' . "\n" . 
        '              <td class="dataTableContent"><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $zones_array['abstract_zone_id'] . '&action=list') . '"><b>' . $zones_array['abstract_zone_name'] . '</b></a></td>' . "\n" . 
        '              <td class="dataTableContent">' . $zones_array['abstract_types_name'] . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .=
      '              <tr>' . "\n" . 
      '                <td colspan="3" class="formButtons"><a href="' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action', 'mcpage')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_draw_hidden_field('multi_form', 'multi_entries') . '&nbsp;' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '</td>' . "\n" . 
      '              </tr>' . "\n" . 
      '            </table></form></div>' . "\n" . 
      '            <div class="splitLine">' . "\n" . 
      '              <div style="float: left;">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
      '              <div style="float: right;">' . $zones_split->display_links($this->m_mcpage, tep_get_all_get_params(array('action', 'mcpage')) . 'action=multi_entries') . '</div>' . "\n" . 
      '            </div>' . "\n";

      return $html_string;
    }

    function display_delete_multizone() {
      global $g_db;

      if( !isset($_POST['pc_id']) || !is_array($_POST['pc_id']) ) {
        return '';
      }

      $html_string = '';
      $zones_query = $g_db->query("select meta_types_name from " . TABLE_META_TYPES . " where meta_types_id = '" . (int)$this->m_zID . "'");
      $zones_array = $g_db->fetch_array($zones_query);
      $html_string .= 
      '          <div class="comboHeading">' . "\n" . 
      '            <div class="smallText">' . sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zones_array['meta_types_name']) . '</div>' . "\n" . 
      '          </div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=deleteconfirm_multizone', 'post') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TEXT_DELETE_MULTIZONE_CONFIRM . '</td>' . "\n" . 
      '            </tr>' . "\n";
      $rows = 0;

      foreach($_POST['pc_id'] as $key => $value) {
        $abstract_zone_id = $key;
        $delete_query = $g_db->query("select abstract_zone_id, meta_title from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$key . "' order by meta_title, abstract_zone_id");
        if( $delete_array = $g_db->fetch_array($delete_query) ) {
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="dataTableContent">' . tep_draw_hidden_field('pc_id[]', $delete_array['abstract_zone_id']) . $delete_array['meta_title'] . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
      }
      if( count($_POST['pc_id']) ) {
        $html_string .= 
        '            <tr>' . "\n" . 
        '              <td colspan="3" class="formButtons"><a href="' . tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '            </table></form></div>' . "\n";
      return $html_string;
    }
  }
?>