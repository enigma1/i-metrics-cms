<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Voting System Runtime processing script
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
*/
  class voting_system extends plugins_base {
    // Compatibility constructor
    function voting_system() {
      // Force this plugin to operate only with the following scripts
      $this->scripts_array = array(
        FILENAME_GENERIC_PAGES,
        FILENAME_COLLECTIONS,
      );
      // The form name
      $this->form_box = 'process_voting_system_box';
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      // Load plugin configuration settings
      $this->options = $this->load_options();
      // Load the plugin specific strings
      $strings_array = array('web_strings.php');
      $this->strings = $this->load_strings($strings_array);

      tep_define_vars($this->fs_path . 'defs.php');
      $this->votes_form = $this->fs_template_path . 'votes_form.tpl';
      if( !is_file($this->votes_form) ) $this->change(false);
    }

    function plugin_form_process() {
      extract(tep_load('defs', 'http_validator', 'database', 'validator', 'message_stack'));

      $cStrings =& $this->strings;

      // self-check
      if( !tep_check_submit($this->form_box) ) {
        return false;
      }

      $result_array = $cValidator->post_validate(array(
        'rating'    => array('max' => $this->options['box_steps'], 'min' => 1, 'type' => 'range'),
      ));

      // Get the validated parameters only
      $params = $cValidator->convert_to_get();

      $type_id = 0;
      if( $cDefs->gtext_id && $this->options['text_pages'] ) {
        $type_id=1;
        $id = $cDefs->gtext_id;
      } elseif( $cDefs->abstract_id ) {
        $result = $this->check_collection();
        if( $result !== false ) {
          $type_id = 2;
          $id = $cDefs->abstract_id;
        }
      }

      if( !$type_id ) {
        $msg->add_session($cStrings->ERROR_PLUGIN_INVALID_PAGE);
        tep_redirect(tep_href_link($cDefs->script, $params));
      }

      if( !isset($_POST['rating']) || $_POST['rating'] > $this->options['box_steps'] ) {
        $msg->add_session($cStrings->ERROR_PLUGIN_INVALID_RATING);
        tep_redirect(tep_href_link($cDefs->script, $params));
      }

      if( $this->check_reentry($id, $type_id) ) {
        $msg->add_session($cStrings->ERROR_PLUGIN_ALREADY_SUBMITTED);
        tep_redirect(tep_href_link($cDefs->script, $params));
      }

      $sql_data_array = array(
        'votes_id' => (int)$id,
        'votes_type' => (int)$type_id,
        'ip_address' => $db->prepare_input($http->ip_string),
        'rating' => (int)$_POST['rating'],
        'resolution' => (int)$this->options['box_steps'],
        'date_added' => 'now()',
      );
      $db->perform(TABLE_VOTES, $sql_data_array);

      $msg->add_session($cStrings->SUCCESS_PLUGIN_VOTE_ACCEPTED, 'success');
      tep_redirect(tep_href_link($cDefs->script, $params));
      return false;
    }

    function html_left() {
      extract(tep_ref('box_array'), EXTR_OVERWRITE|EXTR_REFS);
      if( $this->options['display_col'] == 0 ) {
        return $this->display_common($box_array);
      }
      return false;
    }

    function html_right() {
      extract(tep_ref('box_array'), EXTR_OVERWRITE|EXTR_REFS);
      if( $this->options['display_col'] == 1 ) {
        return $this->display_common($box_array);
      }
      return false;
    }

    function display_common(&$box_array) {
      extract(tep_load('defs', 'database', 'validator', 'message_stack'));

      if( !$this->options['display_box'] ) return false;

      if( $cDefs->gtext_id && $this->options['text_pages'] ) {

        $id = $cDefs->gtext_id;
        $type_id = 1;
        if( $this->check_reentry($id, $type_id) ) {
          return false;
        }

        $desc_query = $db->query("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$cDefs->gtext_id . "'");
        $desc_array = $db->fetch_array($desc_query);
        $desc = $desc_array['gtext_title'];

        $link = tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $cDefs->gtext_id . '&action=plugin_form_process');
      } else {
        $result = $this->check_collection();
        switch($result) {
          case 'image_zones':
          case 'generic_zones':
            $link = tep_href_link(FILENAME_COLLECTIONS, 'abz_id=' . $cDefs->abstract_id . '&action=plugin_form_process');
            break;
          default:
            return false;

        }

        $id = $cDefs->abstract_id;
        $type_id = 2;
        if( $this->check_reentry($id, $type_id) ) {
          return false;
        }

        $desc_query = $db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$cDefs->abstract_id . "'");
        $desc_array = $db->fetch_array($desc_query);
        $desc = $desc_array['abstract_zone_name'];
      }

      if( $this->check_reentry($id, $type_id) ) return false;
      $this->display_box($link, $desc);
      return true;
    }

    function check_reentry($id, $type_id) {
      extract(tep_load('database', 'http_validator'));

      $check_query = $db->query("select count(*) as total from " . TABLE_VOTES . " where ip_address = '" . $db->filter($http->ip_string) . "' and votes_id = '" . (int)$id . "' and votes_type = '" . (int)$type_id . "'");
      $check_array = $db->fetch_array($check_query);
      if( $check_array['total'] ) {
        return true;
      }
      return false;
    }

    function check_collection() {
      extract(tep_load('defs'));

      $result = false;
      if( !$cDefs->abstract_id ) return $result;
      $cAbstract = new abstract_front();
      $zone_class = $cAbstract->get_zone_class($cDefs->abstract_id);
      switch($zone_class) {
        case 'generic_zones':
          if( !$this->options['text_collections']) return $result;
          $result = $zone_class;
          break;           
        case 'image_zones':
          if( !$this->options['image_collections']) return $result;
          $result = $zone_class;
          break;
        default:
          break;
      }
      return $result;
    }

    function display_box($link, $desc) {
      $cStrings =& $this->strings;
      require($this->votes_form);
    }
  }
?>
