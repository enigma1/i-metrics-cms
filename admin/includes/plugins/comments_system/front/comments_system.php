<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Comments System Runtime processing script
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
  class comments_system extends plugins_base {
    // Force this plugin to operate only with the following scripts
    var $scripts_array = array(
      FILENAME_GENERIC_PAGES,
      FILENAME_IMAGE_PAGES,
    );
    // The form name
    var $form_box = 'process_comments_system';
    var $storage = array();
    var $max_buttons = 50;

    // Compatibility constructor
    function comments_system() {
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      // Load plugin configuration settings
      $this->options = $this->load_options();
      // Load the plugin specific strings
      $this->strings = tep_get_strings($this->web_template_path . 'web_strings.php');
      // Load the plugin definitions files/tables
      require_once($this->web_path . 'files.php');
      require_once($this->web_path . 'tables.php');
      // Prepare and validate the comments templates, disable plugin on errors
      $this->comments_form = $this->web_template_path . 'comments_form.tpl';
      $this->comments_posted = $this->web_template_path . 'comments_posted.tpl';
      if( !file_exists($this->comments_form) ) $this->change(false);
      if( !file_exists($this->comments_posted) ) $this->change(false);
    }

    function plugin_form_process() {
      global $g_db, $g_script, $g_session, $g_validator, $messageStack, $current_gtext_id, $current_abstract_id;
      $cStrings =& $this->strings;
      $this->storage =& $g_session->register($this->key);

      $buttons_array = array();
      if( $this->options['anti_bot'] ) {
        $buttons_array = $this->storage['css_buttons'];
        unset($buttons_array['visible_button']);
        $buttons_array = array_values($buttons_array);
      }

      // anti-bot verification check
      if( !$this->storage['process'] || !tep_check_submit($this->storage['visible_button'], $buttons_array) ) {
        return false;
      }

      if( $this->options['display_rating'] ) {
        $min_rating = 1;
      } else {
        $_POST['rating'] = 0;
        $min_rating = 0;
      }

      $result_array = $g_validator->post_validate(array(
        'email'     => array('max' => 100, 'min' => 7),
        'rating'    => array('max' => $this->options['rating_steps'], 'min' => $min_rating, 'type' => 'range'),
        'name'      => array('max' => 64, 'min' => 3),
        'comment'   => array('max' => 10000, 'min' => 6),
        'url'       => array('max' => 250, 'min' => 0),
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

      $error = false;
      if( !empty($result_array['rating']) ) {
        $messageStack->add($cStrings->ERROR_PLUGIN_INVALID_RATING);
        $error = true;
      }
      if( !empty($result_array['name']) ) {
        $messageStack->add($cStrings->ERROR_PLUGIN_INVALID_NAME);
        $error = true;
      }
      if( !empty($result_array['comment']) ) {
        $messageStack->add($cStrings->ERROR_PLUGIN_INVALID_COMMENT);
        $error = true;
      }
      if( !empty($result_array['url']) ) {
        $messageStack->add($cStrings->ERROR_PLUGIN_INVALID_URL);
        $error = true;
      }
      if( !empty($result_array['email']) ) {
        $messageStack->add($cStrings->ERROR_PLUGIN_INVALID_EMAIL);
        $error = true;
      }
      if( $error ) {
        return true;
      }

      $error = false;
      $body = $g_db->prepare_input($_POST['comment'], false);
      $body_key = md5($body);
      if( $this->check_reentry($body_key) ) {
        $messageStack->add_session($cStrings->ERROR_PLUGIN_ALREADY_SUBMITTED);
        tep_redirect(tep_href_link($g_script, $params));
      }

      if( !tep_validate_email($_POST['email']) ) {
        $messageStack->add($cStrings->ERROR_PLUGIN_INVALID_EMAIL);
        $error = true;
      }
      if( $error ) {
        return true;
      }

      $url = $g_db->prepare_input($_POST['url']);
      if( !tep_validate_url($url) ) {
        $url = '';
      }
      if( !empty($url) && substr($url, 0, 7) != 'http://' ) {
        $url = 'http://' . $url;
      }

      $body = $g_db->prepare_input($_POST['comment'], false);
      $sql_data_array = array(
        'comments_id'     => (int)$id,
        'content_type'    => (int)$type_id,
        'comments_author' => $g_db->prepare_input($_POST['name']),
        'comments_email'  => $g_db->prepare_input($_POST['email']),
        'comments_url'    => $url,
        'comments_body'   => $body,
        'comments_key'    => $body_key,
        'ip_address'      => $g_db->prepare_input(tep_get_ip_address()),
        'comments_rating' => (int)$_POST['rating'],
        'resolution'      => (int)$this->options['rating_steps'],
        'date_added'      => 'now()',
        'status_id'       => (int)$this->options['auto_display'],
      );
      $g_db->perform(TABLE_COMMENTS, $sql_data_array);

      $messageStack->add_session($cStrings->SUCCESS_PLUGIN_COMMENT_ACCEPTED, 'success');
      $g_session->unregister($this->key);
      tep_redirect(tep_href_link($g_script, $params));
      return false;
    }

    function html_start() {
      global $g_session, $g_media;

      // Disable the anti-bot on spider presence
      if( !$g_session->has_started() ) {
        $this->options['anti_bot'] = false;
      }

      $this->storage =& $g_session->register($this->key);
      if( !$g_session->is_registered($this->key) ) {
        $this->storage = array(
          'css_buttons' => '',
          'visible_button' => '',
          'process' => false
        );
      }

      if( $this->options['anti_bot'] ) {
        $g_media[] = '<link rel="stylesheet" type="text/css" href="' . $this->web_path . 'cscss.css" media="screen" />';
        if( empty($this->storage['css_buttons']) || $this->options['anti_bot_strict'] || $this->storage['visible_button'] == $this->form_box ) {
          $this->storage['css_buttons'] = tep_random_buttons_css($this->storage['visible_button'], '#cscss_buttons', $this->max_buttons);
        }
        $g_media[] = '<link rel="stylesheet" type="text/css" href="' . tep_href_link(FILENAME_COMMENTS_SYSTEM_CSS) . '" media="screen" />';
      } else  {
        $this->storage['visible_button'] = $this->form_box;
      }
      return true;
    }

    function html_main_content_end() {
      global $g_db, $g_session, $g_script, $current_gtext_id, $current_abstract_id;

      $this->storage['process'] = false;
      if( $current_gtext_id && $this->options['text_pages'] ) {
        $id = $current_gtext_id;
        $type_id = 1;

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

        $desc_query = $g_db->query("select abstract_zone_name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$current_abstract_id . "'");
        $desc_array = $g_db->fetch_array($desc_query);
        $desc = $desc_array['abstract_zone_name'];
      }
      if( !$this->check_entry($id, $type_id) ) return false;

      $this->display_posts($id, $type_id, $desc);
      $this->display_form($link, $desc);
      $this->storage =& $g_session->register($this->key);
      $this->storage['process'] = true;
      return true;
    }

    function check_reentry($key) {
      global $g_db;

      $ip_address = tep_get_ip_address();
      $check_query = $g_db->query("select count(*) as total from " . TABLE_COMMENTS . " where comments_key = '" . $g_db->filter($key) . "'");
      $check_array = $g_db->fetch_array($check_query);
      return ($check_array['total'] > 0);
    }

    function check_entry($id, $type_id) {
      global $g_db;

      $check_query = $g_db->query("select count(*) as total from " . TABLE_COMMENTS_TO_CONTENT . " where comments_id = '" . (int)$id . "' and content_type= '" . (int)$type_id . "'");
      $check_array = $g_db->fetch_array($check_query);

      $mode = 1;
      if( $type_id == 1 && $this->options['text_include'] ) {
        $mode = 0;
      }
      if( $type_id == 2 && $this->options['collection_include'] ) {
        $mode = 0;
      }
      return (($check_array['total']^$mode) > 0);
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

    function display_form($link, $desc) {
      $cStrings =& $this->strings;
      require($this->comments_form);
    }

    function display_posts($id, $type_id, $comments_title) {
      $cStrings =& $this->strings;
      $comments_array = $this->get_posted_comments($id, $type_id);
      $rating_array = $this->get_rating($id, $type_id);
      require($this->comments_posted);
    }

    function get_posted_comments($id, $type_id) {
      global $g_db;
      $comments_query_raw = "select comments_author, comments_body, comments_url, date_added, comments_rating, resolution from " . TABLE_COMMENTS . " where comments_id = '" . (int)$id . "' and content_type= '" . (int)$type_id . "' and status_id='1' order by auto_id desc";
      return $g_db->query_to_array($comments_query_raw);
    }

    function get_rating($id, $type_id) {
      global $g_db;
      $rating_query = $g_db->query("select if(sum(comments_rating), sum(comments_rating), 0) as total_rating, if(sum(resolution), sum(resolution), 0) as total_resolution from " . TABLE_COMMENTS . " where comments_id = '" . (int)$id . "' and content_type= '" . (int)$type_id . "' and status_id='1'");
      $rating_array = $g_db->fetch_array($rating_query);
      return $rating_array;
    }
  }
?>
