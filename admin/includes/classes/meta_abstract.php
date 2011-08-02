<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
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

    // Compatibility Constructor
    function meta_abstract() {
      $this->error_array = array();
      $this->m_ssID = isset($_GET['ssID'])?(int)$_GET['ssID']:'';
      $this->m_mcpage = isset($_GET['mcpage'])?(int)$_GET['mcpage']:'';
      $this->m_mppage = isset($_GET['mppage'])?(int)$_GET['mppage']:'';
      parent::meta_zones();
    }

    function generate_name($abstract_zone_id) {
      extract(tep_load('database'));

      $name = '';
      $name_query = $db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      if( $names_array = $db->fetch_array($name_query) ) {
        $name = $names_array['abstract_zone_name'];
        $name =  $this->create_safe_string($name);
      }
      return $name;
    }

    function generate_lexico($index=0) {
      extract(tep_load('database'));

      $abstract_query = $db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . "");
      while( $abstract_array = $db->fetch_array($abstract_query) ) {

        $phrase = $this->create_safe_string($abstract_array['abstract_zone_name']);
        $md5_key = md5($phrase);
        $check_query = $db->query("select count(*) as total from " . TABLE_META_LEXICO . " where meta_lexico_key = '" . $db->filter($md5_key) . "'");
        $check_array = $db->fetch_array($check_query);
        if( !$check_array['total'] ) {
          $sql_data_array = array(
            'meta_lexico_key' => $db->prepare_input($md5_key),
            'meta_lexico_text' => $db->prepare_input($phrase)
          );
          $db->perform(TABLE_META_LEXICO, $sql_data_array);
        }
      }
    }

    function get_zone_names($zone_id) {
      extract(tep_load('database'));

      $keywords_array = array();

      $types_query = $db->query("select abstract_types_class, abstract_types_table from " . TABLE_ABSTRACT_TYPES . " azt left join " . TABLE_ABSTRACT_ZONES . " az on (az.abstract_types_id=azt.abstract_types_id) where az.abstract_zone_id = '" . (int)$zone_id . "'");
      if( !$db->num_rows($types_query) )
        return $keywords_array;
      $types_array = $db->fetch_array($types_query);

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
      extract(tep_load('database'));

      $text_array = array();

      $tables_array = explode(',', $table);
      if( !is_array($tables_array) ) {
        return $text_array;
      }

      $text_query = $db->query("select gtd.gtext_id, gt.gtext_title from " . TABLE_GTEXT . " gt left join " . $db->filter($tables_array[0]) . " gtd on (gtd.gtext_id=gt.gtext_id) where gtd.abstract_zone_id = '" . (int)$zone_id . "' and gt.status='1' order by gtd.sequence_order");
      while($text = $db->fetch_array($text_query) ) { 
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
      extract(tep_load('database'));

      $this->error_array = array();
      // First pass 
      $check_query = $db->query("select pd.abstract_zone_id, pd.abstract_zone_name as name, if(s2p.abstract_zone_id, s2p.abstract_zone_id, 0) as missing_id from " . TABLE_ABSTRACT_ZONES . " pd left join " . TABLE_META_ABSTRACT . " s2p on ((s2p.abstract_zone_id = if(s2p.abstract_zone_id,pd.abstract_zone_id,0))) order by pd.abstract_zone_id desc");
      while( $check_array = $db->fetch_array($check_query) ) {
        if( !$check_array['missing_id'] ) {
          $this->error_array[] = $check_array;
        }
        if( count($this->error_array) >= META_PAGE_SPLIT )
          break;
      }
      // Second pass check for redundant entries
      $check_query = $db->query("select s2p.abstract_zone_id, if(pd.abstract_zone_id, pd.abstract_zone_name, s2p.meta_name) as name, if(pd.abstract_zone_id, pd.abstract_zone_id, -1) as missing_id from " . TABLE_META_ABSTRACT . " s2p left join " . TABLE_ABSTRACT_ZONES . " pd on (s2p.abstract_zone_id = if(pd.abstract_zone_id,pd.abstract_zone_id,0)) order by s2p.abstract_zone_id desc");
      while( $check_array = $db->fetch_array($check_query) ) {
        if( $check_array['missing_id'] == -1 ) {
          $this->error_array[] = $check_array;
        }
        if( count($this->error_array) >= META_PAGE_SPLIT )
          break;
      }
      return $this->error_array;
    }

    function validate_confirm() {
      extract(tep_load('defs', 'database'));

      foreach($_POST['pc_id'] as $abstract_zone_id => $val) {
        if( $_POST['missing'][$abstract_zone_id] == -1 ) {
          $db->query("delete from " . TABLE_META_ABSTRACT . " where meta_types_id = '" . (int)$this->m_zID . "' and abstract_zone_id = '" . (int)$abstract_zone_id . "'");
        } elseif( $_POST['missing'][$abstract_zone_id] == 0 ) {
          $meta_name = $this->generate_name($abstract_zone_id);
          $sql_data_array = array(
            'meta_types_id' => (int)$this->m_zID,
            'abstract_zone_id' => (int)$abstract_zone_id,
            'meta_name' => $db->prepare_input($meta_name),
          );
          $db->perform(TABLE_META_ABSTRACT, $sql_data_array, 'insert');
        }
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=validate'));
    }

    function update_multizone() {
      extract(tep_load('defs', 'database'));

      foreach($_POST['pc_id'] as $abstract_zone_id => $val) {

        $meta_title = $_POST['title'][$abstract_zone_id];
        $meta_keywords = $_POST['keywords'][$abstract_zone_id];
        $meta_text = $_POST['text'][$abstract_zone_id];

        $sql_data_array = array(
          'meta_title' => $db->prepare_input($meta_title),
          'meta_keywords' => $db->prepare_input($meta_keywords),
          'meta_text' => $db->prepare_input($meta_text)
        );
        $this->insert_update($abstract_id, $sql_data_array);
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
    }


    function insert_multientries() {
      extract(tep_load('defs', 'database', 'message_stack'));

      $multi_form = (isset($_POST['multi_form']) ? $_POST['multi_form'] : '');

      switch( $multi_form ) {
        case 'multi_entries':
          $tmp_array = array();
          foreach ($_POST['pc_id'] as $abstract_id=>$val) {
            $multi_query = $db->query("select abstract_zone_id, abstract_zone_name, abstract_zone_desc from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$abstract_id . "'");
            while( $multi = $db->fetch_array($multi_query) ) {
              $check_query = $db->query("select abstract_zone_id from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$multi['abstract_zone_id'] . "'");
              if( $db->num_rows($check_query) ) continue;

              $meta_name = $this->create_safe_string($multi['abstract_zone_name']);
              $keywords_array = $this->get_zone_names($multi['abstract_zone_id']);

              $meta_text = '';
              if( strlen($multi['abstract_zone_desc']) < META_MAX_DESCRIPTION ) {
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
                'meta_title' => $db->prepare_input($meta_name),
                'meta_keywords' => $db->prepare_input($meta_keywords),
                'meta_text' => $db->prepare_input($meta_text)
              );

              $this->insert_update($abstract_id, $sql_data_array, 'insert');

              $db->perform(TABLE_META_ABSTRACT, $sql_data_array);
            }
          }
          $msg->add_session(SUCCESS_SELECTED_ADDED, 'success');
          tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
          break;
        default:
          break;
      }
    }

    function insert_update($abstract_id, $metag_array, $op='') {
      extract(tep_load('database'));

      if( empty($metag_array) ) {
        $metag_query = $db->query("select abstract_zone_name as meta_title, abstract_zone_desc as meta_text from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$abstract_id . "'");
        if( !$db->num_rows($metag_query) ) return false;
        $metag_array = $db->fetch_array($metag_query);
      }

      if( empty($abstract_id) || !isset($metag_array['meta_title']) || empty($metag_array['meta_title'])) return false;

      $metag_array['meta_title'] = $this->create_safe_string($metag_array['meta_title']);
      if(empty($metag_array['meta_keywords'])) $metag_array['meta_keywords'] = $metag_array['meta_title'];
      if(empty($metag_array['meta_text'])) $metag_array['meta_text'] = $metag_array['meta_title'];
      if( empty($op) ) {
        $op = 'update';
      } elseif($op == 'check_insert' ) {
        $check_query = $db->query("select count(*) as total from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$abstract_id . "'");
        $check_array = $db->fetch_array($check_query);
        if( $check_array['total'] ) {
          $op = 'update';
        } else {
          $op = 'insert';
        }
      }

      $sql_data_array = array(
        'meta_title' => strip_tags($metag_array['meta_title']),
        'meta_keywords' => strip_tags($metag_array['meta_keywords']),
        'meta_text' => strip_tags($metag_array['meta_text']),
      );

      if($op == 'insert') {
        $sql_data_array['abstract_zone_id'] = (int)$abstract_id;
        $db->perform(TABLE_META_ABSTRACT, $sql_data_array);
      } else {
        $db->perform(TABLE_META_ABSTRACT, $sql_data_array, 'update', "abstract_zone_id = '" . (int)$abstract_id . "'");
      }
      return true;
    }

    function deleteconfirm_multizone() {
      extract(tep_load('defs', 'database'));

      for($i=0, $j=count($_POST['pc_id']); $i<$j; $i++ ) {
        $abstract_zone_id = $_POST['pc_id'][$i];
        $db->query("delete from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      }
      tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list'));
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
      extract(tep_load('defs'));

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
        '        <td valign="top">' . tep_draw_form('rl', $cDefs->script, 'action=validate_confirm&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage, 'post') . '<table class="tabledata">' . "\n" . 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <th class="calign"><a href="#pc_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
        '            <th>' . TABLE_HEADING_ID . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_NAME . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_COMMENT . '</th>' . "\n" . 
        '          </tr>' . "\n";
        for($i=0, $j=count($this->error_array); $i<$j; $i++ ) {
          $row_class = ($this->error_array[$i]['missing_id'])?'dataTableRowImpact':'dataTableRowHigh';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="calign">' . tep_draw_checkbox_field('pc_id[' . $this->error_array[$i]['abstract_zone_id'] . ']', 'on', false ) . tep_draw_hidden_field('missing[' . $this->error_array[$i]['abstract_zone_id'] . ']', $this->error_array[$i]['missing_id']) . '</td>' . "\n" . 
          '            <td>' . $this->error_array[$i]['abstract_zone_id'] . '</td>' . "\n" . 
          '            <td>' . $this->error_array[$i]['name'] . '</td>' . "\n" . 
          '            <td>' . (($this->error_array[$i]['missing_id'])?'Missing from Abstract Table':'Missing from META-G') . '</td>' . "\n" . 
          '          </tr>'  . "\n";
        }
        $html_string .= 
        '          <tr>' . "\n" . 
        '            <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n" . 
        '              <tr>' . "\n" . 
        '                <td><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . tep_image_submit('button_fix_errors.gif', 'Fix Listed Errors') . '</td>' . "\n" . 
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
        '            <td><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a></td>' . "\n" . 
        '          </tr>' . "\n" . 
        '        </table></td>' . "\n" . 
        '      </tr>' . "\n";
      }
      return $html_string;
    }

// Default List
    function display_list() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $rows = 0;
      $zones_query_raw = "select * from " . TABLE_META_ABSTRACT . " order by meta_title, abstract_zone_id";
      $zones_split = new splitPageResults($zones_query_raw, META_PAGE_SPLIT, '', 'spage');

      if( $zones_split->number_of_rows > 0 ) {
        $html_string .= 
        '        <div class="formArea">' . tep_draw_form('rl', $cDefs->script, 'action=delete_multizone&zID=' . $this->m_zID . '&zpage=' . $this->m_zpage . '&spage=' . $this->m_spage, 'post') . '<table class="tabledata">' . "\n";

        $html_string .= 
        '          <tr class="dataTableHeadingRow">' . "\n" . 
        '            <th class="calign"><a href="#pc_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
        '            <th>' . TABLE_HEADING_TITLE . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_KEYWORDS . '</th>' . "\n" . 
        '            <th>' . TABLE_HEADING_DESCRIPTION . '</th>' . "\n" . 
        '          </tr>' . "\n";
        $zones_query = $db->query($zones_split->sql_query);
        $bCheck = false;
        while( $zones_array = $db->fetch_array($zones_query) ) {

          $extra_query = $db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$zones_array['abstract_zone_id'] . "'");
          if( $db->num_rows($extra_query) ) {
            $extra_array = $db->fetch_array($extra_query);
            $final_name = '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $zones_array['abstract_zone_id'] . '&action=edit_zone') . '">' . $extra_array['abstract_zone_name'] . '</a>';
          } else {
            $final_name = '<font color="#FF0000"><b>' . TEXT_INFO_NA . '&nbsp;[' . $zones_array['meta_title'] . ']' . '</b></font>';
          }

          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td class="calign">' . tep_draw_checkbox_field('pc_id[' . $zones_array['abstract_zone_id'] . ']', ($bCheck?'on':''), $bCheck ) . '</td>' . "\n" . 
          '            <td><div class="rpad">' . $final_name . '<br />' . tep_draw_input_field('title[' . $zones_array['abstract_zone_id'] . ']', $zones_array['meta_title']) . '</div></td>' . "\n" . 
          '            <td><div class="rpad">' . tep_draw_textarea_field('keywords[' . $zones_array['abstract_zone_id'] . ']', $zones_array['meta_keywords'], '40','2') . '</div></td>' . "\n" . 
          '            <td><div class="rpad">' . tep_draw_textarea_field('text[' . $zones_array['abstract_zone_id'] . ']', $zones_array['meta_text'], '40','2') . '</div></td>' . "\n" . 
          '          </tr>'  . "\n";
        }

        $html_string .= 
        '      </table>' . "\n";

        if(empty($this->saction)) {
          $html_string .= 
          '            <div class="formButtons"><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>' . tep_image_submit('button_update.gif', TEXT_UPDATE_MULTIZONE, 'class="dflt" onclick="this.form.action=' . '\'' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=update_multizone') . '\'' . '"') . tep_image_submit('button_delete.gif', TEXT_DELETE_MULTIZONE, 'class="dflt" onclick="this.form.action=' . '\'' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=delete_multizone') . '\'' . '"') . '</div>' . "\n";
        }
        $html_string .= 
        '          </form></div>' . "\n" . 
        '          <div class="listArea splitLine">' . "\n" . 
        '            <div class="floater">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
        '            <div class="floatend">' . $zones_split->display_links(tep_get_all_get_params('action', 'spage') . 'action=list') . '</div>' . "\n" . 
        '          </div>' . "\n";
      } else {
        $html_string .= 
        '        <div class="comboHeading">' . TEXT_INFO_NO_ENTRIES . '</div>' . "\n";
      }
      if (empty($this->saction)) {
        $html_string .= 
        '          <div class="formButtons"><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $this->m_zID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=multi_entries') . '">' . tep_image_button('button_zones.gif', TEXT_SWITCH_ZONES) . '</a></div>' . "\n";
      }
      return $html_string;
    }

    function display_multi_entries() {
      extract(tep_load('defs', 'database'));

      $html_string = '';
      $html_string .=
      '        <div class="comboHeading">' . TEXT_SELECT_MULTIZONES . '</div>' . "\n" . 
      '        <div class="formArea">' . tep_draw_form('mc', $cDefs->script, tep_get_all_get_params('action') . 'action=insert_multientries', 'post') . '<table class="tabledata">' . "\n" . 
      '          <tr class="dataTableHeadingRow">' . "\n" . 
      '            <th class="calign"><a href="#pc_id" class="page_select" title="' . TEXT_PAGE_SELECT . '">' . tep_image(DIR_WS_ICONS . 'icon_tick.png', TEXT_PAGE_SELECT) . '</a></th>' . "\n" . 
      '            <th>' . TABLE_HEADING_ZONES . '</th>' . "\n" . 
      '            <th>' . TABLE_HEADING_TYPES . '</th>' . "\n" . 
      '          </tr>' . "\n"; 
      $rows = 0;
      //$zones_query_raw = "select abstract_zone_id, abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " order by abstract_zone_name";
      $zones_query_raw = "select az.abstract_zone_id, az.abstract_zone_name, azt.abstract_types_name, if(mm.abstract_zone_id, '1', '0') as checkbox from " . TABLE_ABSTRACT_ZONES . " az left join " . TABLE_ABSTRACT_TYPES . " azt on (az.abstract_types_id=azt.abstract_types_id) left join " . TABLE_META_ABSTRACT . " mm on ((mm.abstract_zone_id = if(mm.abstract_zone_id, az.abstract_zone_id,0))) order by az.abstract_zone_id, az.abstract_zone_name";
      $zones_split = new splitPageResults($zones_query_raw, META_PAGE_SPLIT, '', 'mcpage');
      $zones_query = $db->query($zones_split->sql_query);
      $bCheck = false;
      while( $zones_array = $db->fetch_array($zones_query) ) {
        $bCheck = ($zones_array['checkbox'] == '1')?true:false;
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if($bCheck)
          $row_class = 'dataTableRowGreen';

        $html_string .=
        '            <tr class="' . $row_class . '">' . "\n" . 
        '              <td class="calign">' . ($bCheck?'Included':tep_draw_checkbox_field('pc_id[' . $zones_array['abstract_zone_id'] . ']')) . '</td>' . "\n" . 
        '              <td><a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $zones_array['abstract_zone_id'] . '&action=list') . '"><b>' . $zones_array['abstract_zone_name'] . '</b></a></td>' . "\n" . 
        '              <td>' . $zones_array['abstract_types_name'] . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .=
      '              <tr>' . "\n" . 
      '                <td colspan="3" class="formButtons"><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action', 'mcpage') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_draw_hidden_field('multi_form', 'multi_entries') . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '</td>' . "\n" . 
      '              </tr>' . "\n" . 
      '            </table></form></div>' . "\n" . 
      '            <div class="splitLine">' . "\n" . 
      '              <div class="floater">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
      '              <div class="floatend">' . $zones_split->display_links($this->m_mcpage, tep_get_all_get_params('action', 'mcpage') . 'action=multi_entries') . '</div>' . "\n" . 
      '            </div>' . "\n";

      return $html_string;
    }

    function display_delete_multizone() {
      extract(tep_load('defs', 'database'));

      if( !isset($_POST['pc_id']) || !is_array($_POST['pc_id']) ) {
        return '';
      }

      $html_string = '';
      $zones_query = $db->query("select meta_types_name from " . TABLE_META_TYPES . " where meta_types_id = '" . (int)$this->m_zID . "'");
      $zones_array = $db->fetch_array($zones_query);
      $html_string .= 
      '          <div class="comboHeading">' . sprintf(TEXT_DELETE_MULTIZONE_CONFIRM, $zones_array['meta_types_name']) . '</div>' . "\n" . 
      '          <div class="formArea">' . tep_draw_form('rl_confirm', $cDefs->script, tep_get_all_get_params('action') . 'action=deleteconfirm_multizone', 'post') . '<table class="tabledata">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <th>' . TEXT_DELETE_MULTIZONE_CONFIRM . '</th>' . "\n" . 
      '            </tr>' . "\n";
      $rows = 0;

      foreach($_POST['pc_id'] as $key => $value) {
        $abstract_zone_id = $key;
        $delete_query = $db->query("select abstract_zone_id, meta_title from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$key . "' order by meta_title, abstract_zone_id");
        if( $delete_array = $db->fetch_array($delete_query) ) {
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          $html_string .= 
          '          <tr class="' . $row_class . '">' . "\n" . 
          '            <td>' . tep_draw_hidden_field('pc_id[]', $delete_array['abstract_zone_id']) . $delete_array['meta_title'] . '</td>' . "\n" . 
          '          </tr>' . "\n";
        }
      }
      if( count($_POST['pc_id']) ) {
        $html_string .= 
        '            <tr>' . "\n" . 
        '              <td class="formButtons"><a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=list') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '            </table></form></div>' . "\n";
      return $html_string;
    }
  }
?>