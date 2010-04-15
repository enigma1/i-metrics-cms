<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// META-G Meta-Tags class
// Processes Tag tables, generates keywords, descriptions, titles
// Featuring:
// - Abstract Zones tags processing
// - GText Module tags processing
// - Dual Dictionary processing to include/exclude keywords
// - Multi-layer support by the keywords generator/mixer
// - Priority support for passed arguments and executed scripts
// - Auto builder for keywords/phrases
// - SEO-G assist with keywords density/emphasis
// - Added Google verify header (10/10/2007)
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
  class metaG {
    var $path, $query, $params_array, $error_level, $handler_flag, $script;

    function metaG() {
      global $PHP_SELF;
      $this->script = basename($PHP_SELF);
      $this->path = $this->query = '';
      $this->params_array = array();
      $this->query_array = array();
      $this->error_level = 0;
    }

    function create_safe_string($string, $separator=META_DEFAULT_WORDS_SEPARATOR, $word_length=META_DEFAULT_WORD_LENGTH) {
      if( empty($separator) ) {
        $separator = ' ';
      }

      $string = trim(strip_tags($string));
      $string = trim(preg_replace('#[^\p{L}\p{N}]+#u', ' ', $string));
      $string = preg_replace('/\s\s+/', ' ', $string);

      if( $word_length > 1 ) {
        $words_array = explode($separator, $string);
        if( is_array($words_array) ) {
          for($i=0, $j=count($words_array); $i<$j; $i++) {
            $char_size = tep_utf8_size($words_array[$i]);
            $word_size = $word_length*$char_size;

            if( strlen($words_array[$i]) < $word_size) {
              unset($words_array[$i]);
            }
          }
          if(count($words_array)) {
            $string = implode($separator, $words_array);
          }
        }
      }
      return $string;
    }

    function create_keywords_array($string, $separator=META_DEFAULT_KEYWORDS_SEPARATOR) {
      if( !tep_not_null($separator) ) {
        $separator = ' ';
      }
      $string = str_replace(META_DEFAULT_WORDS_SEPARATOR, $separator, $string);
      $string = $this->create_safe_string($string, $separator);
      $keywords_array = explode(META_DEFAULT_KEYWORDS_SEPARATOR, $string, META_MAX_KEYWORDS);
      return $keywords_array;
    }

    function create_keywords_string($string, $separator=META_DEFAULT_KEYWORDS_SEPARATOR) {
      if( empty($separator) ) {
        $separator = ' ';
      }
      $string = trim(strip_tags($string));
      $keywords_array = $this->create_keywords_array($string, $separator);
      $string = implode(',',$keywords_array);
      return $string;
    }

    function create_keywords_lexico($string, $separator=META_DEFAULT_KEYWORDS_SEPARATOR) {
      global $g_db;
      if( empty($separator) ) {
        $separator = ' ';
      }

      //$string = tep_sanitize_string($string, ' ');
      $string = $this->create_safe_string($string);
      $phrases_array = explode($separator, $string);
      $keywords_array = array();
      $index = 0;
      foreach($phrases_array as $key => $value) {
        if( $index > META_MAX_KEYWORDS) break;
        if( is_numeric($value) ) continue;
        if( strlen($value) <= META_DEFAULT_WORD_LENGTH) continue;
        $check_query = $g_db->query("select meta_exclude_key from " . TABLE_META_EXCLUDE . " where meta_exclude_text like '%" . $g_db->input($value) . "%' and meta_exclude_status='1'");
        if( $g_db->num_rows($check_query) ) continue;

        $process = false;
        if( META_USE_LEXICO == 'true' ) {
          $check_query = $g_db->query("select meta_lexico_key as id, meta_lexico_text as text from " . TABLE_META_LEXICO . " where meta_lexico_text like '%" . $g_db->input($g_db->prepare_input($value)) . "%' and meta_lexico_status='1' order by sort_id");
          if( $check_array = $g_db->fetch_array($check_query) ) {
            $tmp_string = $this->create_safe_string($check_array['text']);
            $md5_key = md5($tmp_string);
            $keywords_array[$check_array['id']] = $tmp_string;
            $process = true;
            $index++;
          }
        }
        if( !$process && META_USE_GENERATOR == 'true') {
          $tmp_string = $this->create_safe_string($value);
          $md5_key = md5($tmp_string);
          $keywords_array[$md5_key] = $tmp_string;
          $index++;
        }
      }
      $string = implode(',',$keywords_array);
      return $string;
    }

    function get_script_tags(&$results_array) {
      global $g_db;

      $result = false;
      $check_query = $g_db->query("select sort_order, meta_types_linkage from " . TABLE_META_TYPES . " where meta_types_class='meta_scripts' and meta_types_status='1'");
      if( $check_array = $g_db->fetch_array($check_query) ) {
        $index_key = $check_array['sort_order'] . '_' . $check_array['meta_types_linkage'];
      } else {
        return $result;
      }

      $md5_key = md5($this->script);

      if( !is_array($results_array) ) {
        $results_array = array();
      }

      $scripts_query = $g_db->query("select meta_title, meta_keywords, meta_text from " . TABLE_META_SCRIPTS . " where meta_scripts_key = '" . $g_db->input($g_db->prepare_input($md5_key)) . "'");
      if( $scripts_array = $g_db->fetch_array($scripts_query) ) {
        $results_array[$index_key] = array(
                                 'type' => 'meta_scripts',
                                 'title' => $scripts_array['meta_title'],
                                 'keywords' => $scripts_array['meta_keywords'],
                                 'text' => $scripts_array['meta_text']
                                );
        $result = true;
      } else {
        //$this->auto_builder($inner[0], $inner[1]);
      }
      return $result;
    }

    function get_meta_tags($params_array) {
      $tmp_array = array();
      if( tep_not_null(META_GOOGLE_VERIFY) && $this->script == FILENAME_DEFAULT ) {
        $tmp_array['google_verify'] = '<meta name="verify-v1" content="' . META_GOOGLE_VERIFY . '" />';
      }

      $tags_array = $this->get_tags_info($params_array);

      if( !is_array($tags_array) || !count($tags_array) ) {
        $string = substr($this->script, 0, -4);
        $title = $this->create_safe_string($string, META_DEFAULT_WORDS_SEPARATOR, 0);
        $tmp_array['title'] = '<title>' . STORE_NAME . ' ' . $title . '</title>';
        $tmp_array['author'] = '<meta name="author" content="' . STORE_NAME . '" />';
        $meta_string = implode("\n", $tmp_array) . "\n";
        return $meta_string;
      }

      $tmp_array['keywords'] = '';
      $tmp_array['text'] = '';
      $tmp_array['title'] = '<title>';
      foreach($tags_array as $key => $value) {
//        if( !isset($tmp_array['title']) && tep_not_null($value['title']) ) {
//          $tmp_array['title'] = '<title>' . ucwords($value['title']) . '</title>';
//        }
        $tmp_array['title'] .= ucwords($value['title']) . '-';
        $tmp_array['keywords'] .= $value['keywords'] . ',';
        $tmp_array['text'] .= $value['text'] . '.';
      }
      $tmp_array['title'] = substr($tmp_array['title'], 0, -1);
      $tmp_array['title'] .= '</title>';

      if( strlen($tmp_array['keywords']) ) {
        $tmp_array['keywords'] = substr($tmp_array['keywords'], 0, -1);
      }
      if( strlen($tmp_array['text']) ) {
        $tmp_array['text'] = substr($tmp_array['text'], 0, -1);
      }

      $tmp_array['keywords'] = '<meta name="keywords" content="' . $tmp_array['keywords'] . '" />';
      $tmp_array['text'] = '<meta name="description" content="' . $tmp_array['text'] . '" />';
      $tmp_array['author'] = '<meta name="author" content="' . STORE_NAME . '" />';
      $tmp_array['generator'] = '<meta name="generator" content="META-G in I-Metrics CMS" />';
      $meta_string = implode("\n", $tmp_array) . "\n";
      return $meta_string;
    }

    // Get tags for parameters/scripts
    function get_tags_info($params_array) {
      global $g_db;

      $results_array = array();
      $flags_array = array('other' => false);
      $result = $this->get_script_tags($results_array);
      if( !$result ) {
        return $results_array;
      }

      if( !is_array($params_array) ) {
        return $results_array;
      }

      foreach ($params_array as $key => $value) {
        switch($key) {
          case 'abz_id':
            if( isset($flags_array['abz_id']) || !tep_not_null($value) || $value == 0) break;
            if( !is_numeric($value) ) {
              die('hack attempt');
            }

            $check_query = $g_db->query("select sort_order, meta_types_linkage from " . TABLE_META_TYPES . " where meta_types_class='meta_abstract' and meta_types_status='1'");
            if( $check_array = $g_db->fetch_array($check_query) ) {
              $index_key = $check_array['sort_order'] . '_' . $check_array['meta_types_linkage'];
            } else {
              break;
            }

            $this->auto_builder($key, $value);
            $tags_query = $g_db->query("select meta_title, meta_keywords, meta_text from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$value . "'");
            if( $g_db->num_rows($tags_query) ) {
              $tags = $g_db->fetch_array($tags_query);
              $results_array[$index_key] = array(
                                       'type' => 'meta_abstract',
                                       'title' => $tags['meta_title'],
                                       'keywords' => $tags['meta_keywords'],
                                       'text' => $tags['meta_text']
                                      );
            }
            $flags_array[$key] = $value;
            break;
          case 'gtext_id':
            if( isset($flags_array['gtext_id']) || !tep_not_null($value) || $value == 0) break;
            if( !is_numeric($value) ) {
              die('hack attempt');
            }

            $check_query = $g_db->query("select sort_order, meta_types_linkage from " . TABLE_META_TYPES . " where meta_types_class='meta_gtext' and meta_types_status='1'");
            if( $check_array = $g_db->fetch_array($check_query) ) {
              $index_key = $check_array['sort_order'] . '_' . $check_array['meta_types_linkage'];
            } else {
              break;
            }

            $this->auto_builder($key, $value);
            $tags_query = $g_db->query("select meta_title, meta_keywords, meta_text from " . TABLE_META_GTEXT . " where gtext_id = '" . (int)$value . "'");
            if( $g_db->num_rows($tags_query) ) {
              $tags = $g_db->fetch_array($tags_query);
              $results_array[$index_key] = array(
                                       'type' => 'meta_gtext',
                                       'title' => $tags['meta_title'],
                                       'keywords' => $tags['meta_keywords'],
                                       'text' => $tags['meta_text']
                                      );
            }
            $flags_array[$key] = $value;
            break;
          case 'page': 
            if( isset($flags_array['page']) || !tep_not_null($value) || $value == 0) break;
            if( !is_numeric($value) ) {
              die('hack attempt');
            }
            $index_key = '99' . '_' . '-1';

            $results_array[$index_key] = array(
                                       'type' => 'page',
                                       'title' => 'Page-' . $value,
                                       'keywords' => '',
                                       'text' => ''
                                      );

            $flags_array[$key] = $value;
            break;
          default:
            $process_flag = false;
            if( !$process_flag ) {
              $flags_array['other'] = true;
            }
            break;
        }
      }

      if( count($results_array) ) {
        $this->resolve_linkage($results_array);

        //asort($results_array, SORT_NUMERIC);
        //$results_array = array_keys($results_array);
        //$params_array = array_merge($results_array, $params_array);
        //$result = 1;
      }
      $other = $flags_array['other'];
      return $results_array;

    }

    function resolve_linkage(&$results_array) {
      $keys_array = $link_array = array();
      foreach($results_array as $key => $value) {
        list($sort, $link) = split("_", $key, 2);
        $keys_array[$sort] = $value;
        $link_array[$sort] = $link;
      }

      ksort($keys_array, SORT_NUMERIC);
      ksort($link_array, SORT_NUMERIC);
      $count = count($link_array);

      foreach($link_array as $key => $value) {
        if($value < 0 && $count > 1 ) {
          unset($keys_array[$key]);
        }

        if( $value < 0 ) {
          continue;
        }

        if( !isset($reduce) && $value > 0 ) {
          $reduce = $value;
          continue;
        }
        if($reduce != $value && $value > 0) {
          unset($keys_array[$key]);
        }
      }
      $results_array = $keys_array;
    }


    function auto_builder($entity, $id, $extra = 'none') {
      global $g_db;

      if( META_BUILDER == 'false' )
        return;

      switch($entity) {
        case 'abz_id':
          $check_query = $g_db->query("select abstract_zone_id from " . TABLE_META_ABSTRACT . " where abstract_zone_id = '" . (int)$id . "'");
          if( $g_db->num_rows($check_query) ) 
            return;

          $abstract_query = $g_db->query("select azt.abstract_types_class, azt.abstract_types_table, abstract_zone_name, abstract_zone_desc from " . TABLE_ABSTRACT_ZONES . " az left join " . TABLE_ABSTRACT_TYPES . " azt on (az.abstract_types_id=azt.abstract_types_id) where azt.abstract_types_status='1' and az.abstract_zone_id = '" . (int)$id . "'");
          if( !$g_db->num_rows($abstract_query) ) return;
          $abstract_array = $g_db->fetch_array($abstract_query);
          $meta_name = $this->create_safe_string($abstract_array['abstract_zone_name'], META_DEFAULT_WORDS_SEPARATOR, 0 );
          $meta_text = tep_truncate_string($abstract_array['abstract_zone_desc'], META_MAX_DESCRIPTION);
          $keywords_array = array();
          $meta_keywords = '';

          switch($abstract_array['abstract_types_class']) {
            case 'generic_zones':
              $keywords_array = $this->get_group_text($id, $abstract_array['abstract_types_table']);
              break;
            default:
              break;

          }

          if( count($keywords_array) ) {
            $meta_keywords = implode(',',$keywords_array);
          } else {
            $meta_keywords = $meta_name;
          }

          if( !strlen($meta_text) ) {
            $meta_text = $meta_name;
          }

          $sql_data_array = array(
                                  'abstract_zone_id' => (int)$id,
                                  'meta_title' => $g_db->prepare_input($meta_name),
                                  'meta_keywords' => $g_db->prepare_input($meta_keywords),
                                  'meta_text' => $g_db->prepare_input($meta_text)
                                 );
          $g_db->perform(TABLE_META_ABSTRACT, $sql_data_array, 'insert');
          break;
        case 'gtext_id':
          $check_query = $g_db->query("select gtext_id from " . TABLE_META_GTEXT . " where gtext_id = '" . (int)$id . "'");
          if( $g_db->num_rows($check_query) ) return;

          $tags_query = $g_db->query("select gtext_title, gtext_description from " . TABLE_GTEXT . " where gtext_id = '" . (int)$id . "'");

          if( $g_db->num_rows($tags_query) ) {
            $tags_array = $g_db->fetch_array($tags_query);
            $meta_name = $this->create_safe_string($tags_array['gtext_title'], META_DEFAULT_WORDS_SEPARATOR, 0 );
            $meta_keywords = $this->create_keywords_lexico($tags_array['gtext_description']);
            $meta_text = tep_truncate_string($tags_array['gtext_description'], META_MAX_DESCRIPTION);
            $sql_data_array = array(
                                    'gtext_id' => (int)$id,
                                    'meta_title' => $g_db->prepare_input($meta_name),
                                    'meta_keywords' => $g_db->prepare_input($meta_keywords),
                                    'meta_text' => $g_db->prepare_input($meta_text)
                                    );
            $g_db->perform(TABLE_META_GTEXT, $sql_data_array, 'insert');
          }
          break;
        default:
          break;
      }
    }

    function get_group_text($zone_id, $table) {
      global $g_db;

      $products_array = array();
      $text_array = array();

      $tables_array = explode(',', $table);
      if( !is_array($tables_array) ) {
        return $text_array;
      }

      $text_query = $g_db->query("select gtd.gtext_id, gt.gtext_title from " . TABLE_GTEXT . " gt left join " . $g_db->prepare_input($g_db->input($tables_array[0])) . " gtd on (gtd.gtext_id=gt.gtext_id) where gtd.abstract_zone_id = '" . (int)$zone_id . "' and gt.status='1' order by gtd.sequence_order");
      while($text = $g_db->fetch_array($text_query) ) { 
        $text_array['txt_' . $text['gtext_id']] = $this->create_safe_string($text['gtext_title']);
        if( count($text_array) > META_MAX_KEYWORDS ) {
          break;
        }
      }
      return $text_array;
    }
  }
?>
