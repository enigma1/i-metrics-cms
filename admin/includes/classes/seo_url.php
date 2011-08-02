<?php
/*
//----------------------------------------------------------------------------
//---------------------- SEO-G by Asymmetrics --------------------------------
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: SEO-G Class
// Creates and manages friendly links for the front end
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
  class seoURL {

    function seoURL() {
      $ext_array = explode(',', SEO_DEFAULT_EXTENSION);
      if( !is_array($ext_array) || empty($ext_array) ) {
        $ext_array = array('');
      }
      $this->default_extension = $ext_array[0];
    }

    function create_safe_string($string, $separator=SEO_DEFAULT_WORDS_SEPARATOR, $flat=false) {
      if( $flat ) {
        $string = tep_create_safe_string(strtolower($string), $separator, "/[^0-9a-z]+/i");
      } else {
        $string = tep_create_safe_string(strtolower($string), $separator, "/[^0-9a-z\/]+/i");
      }
      return $string;
    }

    function create_safe_name($string, $separator=SEO_DEFAULT_WORDS_SEPARATOR) {
      extract(tep_load('database'));

      $string = $this->create_safe_string($string, $separator, true);
      $words_array = explode($separator, $string);

      // Apply META-G Inclusion Dictionary
      if( defined(META_USE_LEXICO) && META_USE_LEXICO == 'true' && SEO_METAG_INCLUSION == 'true' ) {
        if( is_array($words_array) && count($words_array) ) {
          $words_array = array_unique($words_array);
          $tmp_array = array();
          foreach($words_array as $key => $value) {
            $check_query = $db->query("select meta_lexico_text, sort_id from " . TABLE_META_LEXICO . " where meta_lexico_text like '%" . $db->input($db->prepare_input($value)) . "%' and meta_lexico_status='1' order by sort_id limit " . SEO_METAG_INCLUSION_LIMIT);
            if( !$db->num_rows($check_query) )
              continue;
            unset($words_array[$key]);
            while( $check_array = $db->fetch_array($check_query) ) {
              $tmp_array[$check_array['sort_id']] = $this->create_safe_string($check_array['meta_lexico_text'], $separator);
            }
          }
          $words_array = array_merge($tmp_array,$words_array);
        }
      }

      // Adapt META-G Exclusion list
      if( defined(META_USE_LEXICO) && META_USE_LEXICO == 'true' && SEO_METAG_EXCLUSION == 'true' ) {
        if( is_array($words_array) ) {
          $tmp_array = array();
          foreach($words_array as $key => $value) {
            $tmp_array[] = md5($value);
          }

          $check_query = $db->query("select meta_exclude_text from " . TABLE_META_EXCLUDE . " where meta_exclude_key in ('" . implode("', '", $tmp_array ) . "')");
          $words_array = array_flip($words_array);
          while( $check_array = $db->fetch_array($check_query) ) {
            unset($words_array[$check_array['meta_exclude_text']]);
          }
          if(count($words_array)) {
            $words_array = array_flip($words_array);
          }
        }
      }

      // Filter by Length of words
      if(SEO_DEFAULT_WORD_LENGTH > 1) {
        if( is_array($words_array) ) {
          foreach( $words_array as $key => $value ) {
            if(strlen($value) < SEO_DEFAULT_WORD_LENGTH) {
              unset($words_array[$key]);
            }
          }
        }
      }

      if( is_array($words_array) && count($words_array) ) {
        $string = implode($separator, $words_array);
      }
      return $string;
    }


    function generate_text_link($gtext_id) {
      extract(tep_load('database'));

      $result = false;
      $check_query = $db->query("select seo_name from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
      if( !$db->num_rows($check_query) ) {
        return $result;
      }
      $check_array = $db->fetch_array($check_query);
      $db->query("delete from " . TABLE_SEO_URL . " where seo_url_get like '%" . $db->input($check_array['seo_name']) . "%'");

      $osc_link = tep_catalog_href_link('generic_pages.php', 'gtext_id=' . (int)$gtext_id);
      $link = $this->get_naked_link($osc_link);

      $osc_md5 = md5($link);
      $db->query("delete from " . TABLE_SEO_URL . " where osc_url_key = '" . $db->input($osc_md5) . "'");

      $seo_link = $check_array['seo_name'] . $this->default_extension;
      $seo_md5 = md5($seo_link);
      $check_query = $db->query("select seo_url_key from " . TABLE_SEO_URL . " where seo_url_key = '" . $db->input($seo_md5) . "'");

      if( !$db->num_rows($check_query) ) {
        $sql_data_array = array(
          'seo_url_key' => $db->prepare_input($seo_md5),
          'seo_url_get' => $db->prepare_input($seo_link),
          'osc_url_key' => $db->prepare_input($osc_md5),
          'seo_url_org' => $db->prepare_input($link),
          'date_added' => 'now()',
          'last_modified' => 'now()'
        );
        $db->perform(TABLE_SEO_URL, $sql_data_array);
        $db->query("truncate table " . TABLE_SEO_CACHE);
        $result = true;
      }
      return $result;
    }

    function generate_collection_link($abstract_zone_id) {
      extract(tep_load('database'));

      $result = false;
      $check_query = $db->query("select seo_name from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$abstract_zone_id . "'");
      if( !$db->num_rows($check_query) ) {
        return $result;
      }

      $check_array = $db->fetch_array($check_query);
      $db->query("delete from " . TABLE_SEO_URL . " where seo_url_get like '%" . $db->input($check_array['seo_name']) . "%'");

      $cAbstract = new abstract_zones();
      $class_name = $cAbstract->get_zone_class($abstract_zone_id. false);

      $script = 'FILENAME_COLLECTIONS';
      $files_array = tep_get_file_array(tep_front_physical_path(DIR_WS_CATALOG_INCLUDES) . 'filenames.php');
      if( !isset($files_array[$script]) ) return $result;

      $script = $files_array[$script];

      $osc_link = tep_catalog_href_link($script, 'abz_id=' . (int)$abstract_zone_id);
      $link = $this->get_naked_link($osc_link);

      $osc_md5 = md5($link);
      $db->query("delete from " . TABLE_SEO_URL . " where osc_url_key = '" . $db->input($osc_md5) . "'");

      $seo_link = $check_array['seo_name'] . $this->default_extension;
      $seo_md5 = md5($seo_link);
      $check_query = $db->query("select seo_url_key from " . TABLE_SEO_URL . " where seo_url_key = '" . $db->input($seo_md5) . "'");

      if( !$db->num_rows($check_query) ) {
        $sql_data_array = array(
          'seo_url_key' => $db->prepare_input($seo_md5),
          'seo_url_get' => $db->prepare_input($seo_link),
          'osc_url_key' => $db->prepare_input($osc_md5),
          'seo_url_org' => $db->prepare_input($link),
          'date_added' => 'now()',
          'last_modified' => 'now()'
        );
        $db->perform(TABLE_SEO_URL, $sql_data_array);
        $db->query("truncate table " . TABLE_SEO_CACHE);
        $result = true;
      }
      return $result;
    }

    function get_naked_link($url) {
      extract(tep_load('defs'));

      $url = str_replace('&amp;', '&', $url);
      $naked = substr($url, strlen($cDefs->crelpath));
      return $naked;
    }

  }
?>
