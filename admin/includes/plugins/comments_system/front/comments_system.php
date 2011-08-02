<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
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
*/
  class comments_system extends plugins_base {
    // Compatibility constructor
    function comments_system() {
      // Force this plugin to operate only with the following scripts
      $this->scripts_array = array(
        FILENAME_GENERIC_PAGES,
        FILENAME_COLLECTIONS,
      );
      $this->form_box = 'process_comments_system';
      $this->storage =  array(
        'css_buttons' => array(),
        'visible_button' => '',
        'process' => false
      );
      $this->entry_result = array();
      $this->max_buttons = 50;
      $this->form_show = false;
      // Call the parent to set operation path and activation conditions
      parent::plugins_base();
      // Load plugin configuration settings
      $this->options = $this->load_options();
      // Load the plugin specific strings
      $strings_array = array('web_strings.php');
      $this->strings = $this->load_strings($strings_array);

      // Load the plugin definitions files/tables
      tep_define_vars($this->fs_path . 'defs.php');
      // Prepare and validate the comments templates, disable plugin on errors
      $this->comments_form = $this->fs_template_path . 'comments_form.tpl';
      $this->comments_posted = $this->fs_template_path . 'comments_posted.tpl';

      if( !is_file($this->comments_form) || !is_file($this->comments_posted) ) $this->change(false);
    }

    function plugin_form_process() {
      extract(tep_load('defs', 'http_validator', 'database', 'sessions', 'validator', 'message_stack'));

      if( empty($this->entry_result) ) return false;
      $cStrings =& $this->strings;
      $this->storage =& $cSessions->register($this->key, $this->storage);

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

      $result_array = $cValidator->post_validate(array(
        'email'     => array('max' => 100, 'min' => 7),
        'rating'    => array('max' => $this->options['rating_steps'], 'min' => $min_rating, 'type' => 'range'),
        'name'      => array('max' => 64, 'min' => 3),
        'comment'   => array('max' => 10000, 'min' => 6),
        'url'       => array('max' => 250, 'min' => 0),
      ));

      // Get the validated parameters only
      $params = $cValidator->convert_to_get();

      $idx_array = $this->get_content_indices();

      if( empty($idx_array) ) {
        $msg->add_session($cStrings->ERROR_PLUGIN_INVALID_PAGE);
        tep_redirect(tep_href_link($cDefs->script, $params));
      }

      $error = false;
      if( !empty($result_array['rating']) ) {
        $msg->add($cStrings->ERROR_PLUGIN_INVALID_RATING);
        $error = true;
      }
      if( !empty($result_array['name']) ) {
        $msg->add($cStrings->ERROR_PLUGIN_INVALID_NAME);
        $error = true;
      }
      if( !empty($result_array['comment']) ) {
        $msg->add($cStrings->ERROR_PLUGIN_INVALID_COMMENT);
        $error = true;
      }
      if( !empty($result_array['url']) ) {
        $msg->add($cStrings->ERROR_PLUGIN_INVALID_URL);
        $error = true;
      }
      if( !empty($result_array['email']) ) {
        $msg->add($cStrings->ERROR_PLUGIN_INVALID_EMAIL);
        $error = true;
      }
      if( $error ) {
        return true;
      }

      $error = false;
      $body = $db->prepare_input($_POST['comment'], false);
      $body_key = md5($body);
      if( $this->check_reentry($body_key) ) {
        $msg->add_session($cStrings->ERROR_PLUGIN_ALREADY_SUBMITTED);
        tep_redirect(tep_href_link($cDefs->script, $params));
      }

      if( !tep_validate_email($_POST['email']) ) {
        $msg->add($cStrings->ERROR_PLUGIN_INVALID_EMAIL);
        $error = true;
      }

      $url = $db->prepare_input($_POST['url']);
      if( !tep_validate_url($url) ) {
        if( !empty($_POST['url']) ) {
          $msg->add($cStrings->ERROR_PLUGIN_INVALID_URL);
          $error = true;
        }
        $url = '';
      }
      if( !empty($url) && substr($url, 0, 7) != 'http://' ) {
        $url = 'http://' . $url;
      }

      if( $error ) {
        return true;
      }

      $body = $db->prepare_input($_POST['comment'], false);
      $sql_data_array = array(
        'comments_id'     => (int)$idx_array['id'],
        'content_type'    => (int)$idx_array['type_id'],
        'comments_author' => $db->prepare_input($_POST['name']),
        'comments_email'  => $db->prepare_input($_POST['email']),
        'comments_url'    => $url,
        'comments_body'   => $body,
        'comments_key'    => $body_key,
        'ip_address'      => $db->prepare_input($http->ip_string),
        'comments_rating' => (int)$_POST['rating'],
        'resolution'      => (int)$this->options['rating_steps'],
        'date_added'      => 'now()',
        'status_id'       => (int)$this->options['auto_display'],
      );
      $db->perform(TABLE_COMMENTS, $sql_data_array);

      $msg->add_session($cStrings->SUCCESS_PLUGIN_COMMENT_ACCEPTED, 'success');
      $cSessions->unregister($this->key);
      tep_redirect(tep_href_link($cDefs->script, $params));
      return false;
    }

    function init_post() {
      $result_array = $this->get_entry_details();
      if( empty($result_array) ) return $result_array;

      if( $this->check_entry($result_array['id'], $result_array['type_id']) ) {
        $this->form_show = true;
      }
      $this->entry_result = $result_array;
      return true;
    }

    function html_start() {
      extract(tep_load('defs', 'sessions'));

      // Disable the anti-bot on spider presence
      if( !$cSessions->has_started() ) {
        $this->options['anti_bot'] = false;
      }

      $cStrings =& $this->strings;
      $this->storage =& $cSessions->register($this->key, $this->storage);
      if( $this->options['anti_bot'] ) {
        $cDefs->media[] = '<link rel="stylesheet" type="text/css" href="' . $this->web_path . 'cscss.css" media="screen" />';
        if( empty($this->storage['css_buttons']) || $this->options['anti_bot_strict'] || $this->storage['visible_button'] == $this->form_box ) {
          $this->storage['css_buttons'] = tep_random_buttons_css($this->storage['visible_button'], '#cscss_buttons', $this->max_buttons);
        }
        $cDefs->media[] = '<link rel="stylesheet" type="text/css" href="' . tep_href_link(FILENAME_COMMENTS_SYSTEM_CSS) . '" media="screen" />';
      } else  {
        $this->storage['visible_button'] = $this->form_box;
      }

      if( $this->options['rss'] && $this->get_comments_count() ) {
        $rss_array = $this->get_entry_details();
        $cDefs->media[] = '<link rel="alternate" type="application/rss+xml" title="' . sprintf($cStrings->TEXT_RSS_TITLE, $rss_array['title']) . '" href="' . tep_href_link(FILENAME_COMMENTS_SYSTEM_RSS, 'comments_id=' . $rss_array['id'] . '&type_id=' . $rss_array['type_id']) . '" />';
      }
/*
<link rel="alternate" type="application/rss+xml" title="Ben Maynard&#039;s blog about anything &raquo; OpenCart Secured Comments Feed" href="http://blog.visionsource.org/2010/02/14/opencart-secured/feed/" />
*/
      return true;
    }

    function html_main_content_end() {
      extract(tep_load('defs', 'sessions'));

      $this->storage['process'] = false;

      $result_array = $this->entry_result;
      if( empty($result_array) ) {
        return false;
      }

      if( $result_array['type_id'] == 1 ) {
        $result_array['link'] = tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $cDefs->gtext_id . '&action=plugin_form_process');
      } else {
        switch($result_array['class']) {
          case 'super_zones':
          case 'image_zones':
          case 'generic_zones':
            $result_array['link'] = tep_href_link(FILENAME_COLLECTIONS, 'abz_id=' . $cDefs->abstract_id . '&action=plugin_form_process');
            break;
          default:
            return false;
        }
      }

      $this->display_posts($result_array['id'], $result_array['type_id'], $result_array['title']);

      if( $this->form_show ) {
        $this->display_form($result_array['link'], $result_array['title']);
        $this->storage =& $cSessions->register($this->key);
        $this->storage['process'] = true;
      }
      return true;
    }

    function check_reentry($key) {
      extract(tep_load('database'));

      $check_query = $db->query("select count(*) as total from " . TABLE_COMMENTS . " where comments_key = '" . $db->filter($key) . "'");
      $check_array = $db->fetch_array($check_query);
      return ($check_array['total'] > 0);
    }

    function check_entry($id, $type_id) {
      extract(tep_load('database'));

      $check_query = $db->query("select count(*) as total from " . TABLE_COMMENTS_TO_CONTENT . " where comments_id = '" . (int)$id . "' and content_type= '" . (int)$type_id . "'");
      $check_array = $db->fetch_array($check_query);

      $mode = 1;
      if( $type_id == 1 && $this->options['text_include'] ) {
        $mode = 0;
      }
      if( $type_id == 2 && $this->options['collection_include'] ) {
        $mode = 0;
      }
      return (($check_array['total']^$mode) > 0);
    }

    function check_posted_comments() {
      extract(tep_load('database'));

      $result_array = $this->get_content_indices();
      $check_query = $db->query("select count(*) as total from " . TABLE_COMMENTS_TO_CONTENT . " where comments_id = '" . (int)$result_array['id'] . "' and content_type= '" . (int)$result_array['type_id'] . "'");
      $check_array = $db->fetch_array($check_query);
      return $check_array['total'];   
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
        case 'super_zones':
          if( !$this->options['mixed_collections']) return $result;
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
      if( empty($comments_array) && !$this->form_show ) return;

      $rating_array = $this->get_rating($id, $type_id);
      require($this->comments_posted);
    }

    function get_posted_comments($id, $type_id) {
      extract(tep_load('database'));

      $comments_query_raw = "select comments_author, comments_body, comments_url, date_added, comments_rating, resolution from " . TABLE_COMMENTS . " where comments_id = '" . (int)$id . "' and content_type= '" . (int)$type_id . "' and status_id='1' order by auto_id desc";
      return $db->query_to_array($comments_query_raw);
    }

    function get_rating($id, $type_id) {
      extract(tep_load('database'));

      $rating_query = $db->query("select if(sum(comments_rating), sum(comments_rating), 0) as total_rating, if(sum(resolution), sum(resolution), 0) as total_resolution from " . TABLE_COMMENTS . " where comments_id = '" . (int)$id . "' and content_type= '" . (int)$type_id . "' and status_id='1'");
      $rating_array = $db->fetch_array($rating_query);
      return $rating_array;
    }

    function get_comments_count() {
      extract(tep_load('database'));

      $result_array = $this->get_content_indices();
      if( empty($result_array) ) return 0;

      $check_query = $db->query("select count(*) as total from " . TABLE_COMMENTS . " where comments_id = '" . (int)$result_array['id'] . "' and content_type= '" . (int)$result_array['type_id'] . "' and status_id='1'");
      $check_array = $db->fetch_array($check_query);
      return $check_array['total'];
    }

    function get_entry_details() {
      extract(tep_load('defs', 'database'));

      $result_array = $this->get_content_indices();
      if( empty($result_array)) return $result_array;

      if( $result_array['type_id'] == 1 ) {
        $tmp_query = $db->query("select gtext_title as title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$cDefs->gtext_id . "'");
        $tmp_array = $db->fetch_array($tmp_query);
        $result_array = array_merge($result_array, $tmp_array);
      } elseif( $result_array['type_id'] == 2 ) {
        $tmp_query = $db->query("select abstract_zone_name as title from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$cDefs->abstract_id . "'");
        $tmp_array = $db->fetch_array($tmp_query);
        $result_array = array_merge($result_array, $tmp_array);
      }
      return $result_array;
    }

    function get_content_indices() {
      extract(tep_load('defs'));

      $result_array = array();
      if( $cDefs->gtext_id ) {
        $result_array['type_id'] = 1;
        $result_array['id'] = $cDefs->gtext_id;;
        $result_array['class'] = '';
      } elseif( $cDefs->abstract_id ) {
        $result = $this->check_collection();
        if( $result !== false ) {
          $result_array['type_id'] = 2;
          $result_array['id'] = $cDefs->abstract_id;
          $result_array['class'] = $result;
        }
      }
      return $result_array;
    }
  }
?>
