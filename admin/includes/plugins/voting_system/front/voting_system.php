<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
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
//
*/
  class voting_system extends plugins_base {
    // Force this plugin to operate only with the following scripts
    var $scripts_array = array(
      FILENAME_GENERIC_PAGES,
      FILENAME_IMAGE_PAGES,
    );
    // The form name
    var $form_box = 'process_voting_system_box';

    // Compatibility constructor
    function voting_system() {
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      // Load plugin configuration settings
      $this->options = $this->load_options();
      // Load the plugin specific strings
      $this->strings = tep_get_strings($this->web_template_path . 'web_strings.php');

      require_once($this->web_path . 'tables.php');
      $this->votes_form = $this->web_template_path . 'votes_form.tpl';
      if( !file_exists($this->votes_form) ) $this->change(false);
    }

    function plugin_form_process() {
      global $g_db, $g_script, $g_validator, $messageStack, $current_gtext_id, $current_abstract_id;
      $cStrings =& $this->strings;

      // self-check
      if( !tep_check_submit($this->form_box) ) {
        return false;
      }

      $result_array = $g_validator->post_validate(array(
        'rating'    => array('max' => $this->options['box_steps'], 'min' => 1, 'type' => 'range'),
      ));

      // Get the validated parameters only
      $params = $g_validator->convert_to_get();

      $type_id = 0;
      if( $current_gtext_id && $this->options['text_pages'] ) {
        $type_id=1;
        $id=$current_gtext_id;
      } elseif( $current_abstract_id ) {
        $result = $this->check_collection();
        if( $result !== false ) {
          $type_id=2;
          $id=$current_abstract_id;
        }
      }

      if( !$type_id ) {
        $messageStack->add_session($cStrings->ERROR_PLUGIN_INVALID_PAGE);
        tep_redirect(tep_href_link($g_script, $params));
      }

      if( !isset($_POST['rating']) || $_POST['rating'] > $this->options['box_steps'] ) {
        $messageStack->add_session($cStrings->ERROR_PLUGIN_INVALID_RATING);
        tep_redirect(tep_href_link($g_script, $params));
      }

      if( $this->check_reentry($id, $type_id) ) {
        $messageStack->add_session($cStrings->ERROR_PLUGIN_ALREADY_SUBMITTED);
        tep_redirect(tep_href_link($g_script, $params));
      }
      $ip_address = tep_get_ip_address();
      $sql_data_array = array(
                              'votes_id' => (int)$id,
                              'votes_type' => (int)$type_id,
                              'ip_address' => $g_db->prepare_input($ip_address),
                              'rating' => (int)$_POST['rating'],
                              'resolution' => (int)$this->options['box_steps'],
                              'date_added' => 'now()',
                             );
      $g_db->perform(TABLE_VOTES, $sql_data_array);

      $messageStack->add_session($cStrings->SUCCESS_PLUGIN_VOTE_ACCEPTED, 'success');
      tep_redirect(tep_href_link($g_script, $params));
      return false;
    }

    function html_left() {
      if( $this->options['display_col'] == 0 ) {
        return $this->display_common();
      }
      return false;
    }

    function html_right() {
      if( $this->options['display_col'] == 1 ) {
        return $this->display_common();
      }
      return false;
    }

    function display_common() {
      global $g_db, $current_gtext_id, $current_abstract_id, $box_array;

      if( !$this->options['display_box'] ) return false;

      if( $current_gtext_id && $this->options['text_pages'] ) {

        $id = $current_gtext_id;
        $type_id = 1;
        if( $this->check_reentry($id, $type_id) ) {
          return false;
        }

        $desc_query = $g_db->query("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$current_gtext_id . "'");
        $desc_array = $g_db->fetch_array($desc_query);
        $desc = $desc_array['gtext_title'];

        $link = tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $current_gtext_id . '&action=plugin_form_process');
      } else {
        $result = $this->check_collection();
        switch($result) {
          case 'image_zones':
            $link = tep_href_link(FILENAME_IMAGE_PAGES, 'abz_id=' . $current_abstract_id . '&action=plugin_form_process');
            break;
          case 'generic_zones':
            $link = tep_href_link(FILENAME_GENERIC_PAGES, 'abz_id=' . $current_abstract_id . '&action=plugin_form_process');
            break;
          default:
            return false;

        }

        $id = $current_abstract_id;
        $type_id = 2;
        if( $this->check_reentry($id, $type_id) ) {
          return false;
        }

        $desc_query = $g_db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$current_abstract_id . "'");
        $desc_array = $g_db->fetch_array($desc_query);
        $desc = $desc_array['abstract_zone_name'];
      }

      if( $this->check_reentry($id, $type_id) ) return false;
      $this->display_box($link, $desc);
      return true;
    }

    function check_reentry($id, $type_id) {
      global $g_db;

      $ip_address = tep_get_ip_address();
      $check_query = $g_db->query("select count(*) as total from " . TABLE_VOTES . " where ip_address = '" . $g_db->filter($ip_address) . "' and votes_id = '" . (int)$id . "' and votes_type = '" . (int)$type_id . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( $check_array['total'] ) {
        return true;
      }
      return false;
    }

    function check_collection() {
      global $current_abstract_id;

      $result = false;
      if( !$current_abstract_id ) return $result;
      $cAbstract = new abstract_front();
      $zone_class = $cAbstract->get_zone_class($current_abstract_id);
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
