<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Direct Management runtime script
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
------------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
//
*/
  class admin_direct_management extends plugins_base {

    // Compatibility constructor
    function admin_direct_management() {
      $this->box = 'abstract_box';
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      $this->options = $this->load_options();

      $this->strings = tep_get_strings($this->admin_path . 'back/admin_strings.php');
    }

    function init_sessions() {
      extract(tep_load('defs', 'database', 'sessions', 'http_headers'));

      if( $cDefs->action != 'direct_management') return false;
      if( !isset($_POST['dm_select']) || $_POST['dm_select'] != 'confirm') return false;

      $db->query("delete from " . TABLE_SESSIONS . " where sesskey = '" . $db->filter($cSessions->id) . "'");

      $sql_data_array = array(
        'sesskey' => $cSessions->id,
        'ip_long' => $http->ip_string,
        'value' => base64_encode(serialize(array())),
        'expiry' => $cSessions->get_life(),
      );
      $db->perform(TABLE_SESSIONS, $sql_data_array);
      tep_redirect(tep_catalog_href_link('', $this->options['admin_key'] . '=' . $cSessions->id, 'SSL'));
      return true;
    }

    function abstract_box() {
      extract(tep_ref('contents'), EXTR_OVERWRITE|EXTR_REFS);
      extract(tep_load('defs'));

      $cStrings =& $this->strings;
      $contents[] = array('text' => '<a href="' . tep_href_link($cDefs->script, 'direct_management=enable&selected_box=' . $this->box) . '" id="dm_popup">' . $cStrings->BOX_DIRECT_MANAGEMENT . '</a>');
      return true;
    }

    function ajax_start() {
      extract(tep_load('defs', 'sessions'));
      $result = false;

      if( $cDefs->action != 'direct_management_select') {
        return $result;
      }
      $cStrings =& $this->strings;

      $contents = '<div class="bounder"><div class="formArea"><div class="blockpad">' . $cStrings->TEXT_INFO_DIRECT_MANAGEMENT_POPUP . '</div>' .  tep_draw_form('dm_confirm', $cDefs->script, 'action=direct_management', 'POST', 'target="_blank"') . tep_draw_hidden_field('dm_select', 'confirm') . '<div class="formButtons">' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '</div></form></div></div>';
      echo $contents;

      $cSessions->close();
      $result = true;
      return $result;
    }

    function html_start() {
      extract(tep_load('defs'));
      tep_set_lightbox();
      $cDefs->media[] = '<script language="javascript" type="text/javascript" src="' . $this->admin_web_path . 'back/dm.js"></script>';
      return true;
    }

    function html_end() {
      extract(tep_load('defs', 'sessions'));

      ob_start();
      $popup_file = $this->admin_path . 'back/dm_popup.tpl';
      require($popup_file);
      $contents = ob_get_contents();
      ob_end_clean();
      $cDefs->media[] = $contents;
      return true;
    }

    function html_home_plugins() {
      extract(tep_ref('entries_array'), EXTR_OVERWRITE|EXTR_REFS);
      return $this->common_dm($entries_array);
    }
    function html_home_collections() {
      extract(tep_ref('entries_array'), EXTR_OVERWRITE|EXTR_REFS);
      return $this->common_dm($entries_array);
    }

    function common_dm(&$entries_array) {
      extract(tep_load('defs'));

      $cStrings =& $this->strings;

      $entries_array[] = array(
        'id' => $this->key,
        'title' => $cStrings->TEXT_INFO_DIRECT_MANAGEMENT2,
        'image' => tep_image($this->admin_web_path . 'direct_management.png', $cStrings->TEXT_INFO_DIRECT_MANAGEMENT),
        //'href' => tep_href_link(FILENAME_PLUGINS, 'action=info&plgID=' . $this->key),
        'href' => tep_href_link($cDefs->script, 'direct_management=enable&selected_box=' . $this->box),
        'href_id' => 'dm_popup'
      );
      return true;
    }
  }
?>
