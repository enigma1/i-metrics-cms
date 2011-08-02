<?php
/*
//----------------------------------------------------------------------------
//-------------- SEO-G by Asymmetrics (Renegade Edition) ---------------------
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: URL processing Front-End class
//----------------------------------------------------------------------------
// Processes SEO tables and urls, generates SEO links
// Mods/Features:
// - SEO URLs Generator
// - Table driven and Auto Redirection methods
// - Auto Builder for SEO URLs.
// - Multi-Layer processing facility for products, categories, etc., segments
// - Priority processing facility for products, categories, etc., segments
// - Multi separators for SEO URL segments.
// - Multi URLs Extensions Decoding.
// - Support for Generic Text
// - Support for Abstract Zones
// - Support for Page splitter
// - META-G adaptive methods to include/exclude link components added
// - Proximity Redirection added
// - Privacy header on redirecs added
// - Periodic URL refresh added
// - Subfixes for secondary handlers added
// - Index for osc urls added
// - Cascade Path level added
// - I-Metrics CMS integration
// - Generates Independent host urls
// - Capability for Extensionless URLs
// - Decoder for multiple extensions - combinations of extensions
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class seoURL {
    // compatibility constructor
    function seoURL() {
      extract(tep_load('defs'));
      $this->path = $this->query = '';
      $this->params_array = array();
      $this->query_array = array();
      $this->osc_keys_array = array();
      $this->error_level = 0;
      $this->handler_flag = false;
      $this->osc_key = '';

      $ext_array = explode(',', SEO_DEFAULT_EXTENSION);
      if( !is_array($ext_array) || empty($ext_array) ) {
        $ext_array = array('');
      }
      $this->default_extension = $ext_array[0];

//      if( $cDefs->script == 'root.php' ) {
        $query = substr($cDefs->server . $_SERVER['REQUEST_URI'], strlen($cDefs->relpath));
        $this->check_redirection(0, $query);
//      }
    }

    function create_safe_string($string, $separator=SEO_DEFAULT_WORDS_SEPARATOR, $flat=false) {
      $string = preg_replace('/\s\s+/', ' ', trim($string));
      if( $flat ) {
        $string = preg_replace("/[^0-9a-z]+/i", $separator, strtolower($string));
	    //$string = preg_replace("/[^0-9a-z\-_]+/i", $separator, strtolower($string));
      } else {
	    $string = preg_replace("/[^0-9a-z\/]+/i", $separator, strtolower($string));
	    //$string = preg_replace("/[^0-9a-z\-_\/]+/i", $separator, strtolower($string));
      }
      if( !empty($separator) ) {
        $string = trim($string, $separator);
        $string = preg_replace("/\$separator\$separator+/", $separator, trim($string));
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

    function get_script($script) {
      extract(tep_load('database'));

      $check_query = $db->query("select seo_name from " . TABLE_SEO_TO_SCRIPTS . " where script = '" . $db->filter($script) . "'");
      if( $check_array = $db->fetch_array($check_query) ) {
        $result = $check_array['seo_name'];
      } else {
        $result = $this->create_safe_string($script, SEO_DEFAULT_WORDS_SEPARATOR);
      }
      return $result;
    }

    // Get osc url from a passed seo url
    function get_osc_url($seo_url, &$url, &$url_params, &$url_parse) {
      extract(tep_load('defs', 'database'));

      // Validate REQUEST_URI in case we got a redirect from a server script. May needed with some servers
      $this->validate_uri($seo_url);

      $url = $url_params = $url_parse = $result = false;
      $seo_left = explode('?', $seo_url);

      $seo_script = basename($seo_left[0]);
      $seo_script = str_replace('.', '_', $seo_script);
      if( isset($_GET[$seo_script]) ) {
        unset($_GET[$seo_script]);
      }

      $seo_left[0] = substr($seo_left[0], strlen($cDefs->relpath));
      $key = md5($seo_left[0]);

      if( !isset($seo_left[1]) ) {
        $seo_left[1] = '';
      }

      $this->check_redirection($key, $seo_left[1]);

      $check_query = $db->query("select seo_url_get, seo_url_org from " . TABLE_SEO_URL . " where seo_url_key = '" . $db->input($key) . "'");

      if( $db->num_rows($check_query) ) {
        $seo_array = $db->fetch_array($check_query);

        $url = $seo_array['seo_url_org'];
        $url_parse = parse_url($url);

        if( !isset($url_parse['query']) ) {
          $url_query = '';
        } else {
          $url_query = $url_parse['query'];
        }

        $url_params = array();
        if( !empty($url_query) ) {
          $url_params = explode('&', $url_query);
        }
        //$db->query("update " . TABLE_SEO_URL . " set seo_url_hits = seo_url_hits+1, last_modified=now() where seo_url_key = '" . $db->input($key) . "'");
        $this->osc_key = $key;
        $result = true;
      } else {
        $url_parse = parse_url($seo_url);
      }
      return $result;
    }

    // Convert osc url to an html url. Do not pass the session name/id to this function
    function get_seo_url($url, &$separator, $store=true) {
      extract(tep_load('defs', 'database'));

      $org_url = $url;

      if( SEO_DEFAULT_ENABLE == 'false' ) {
        return $org_url;
      }
      $seg_array = parse_url($url);

      if( empty($seg_array)) return $org_url;
      if( !isset($seg_array['path']) ) $seg_array['path'] = '';
      if( !isset($seg_array['query']) ) $seg_array['query'] = '';
      if( empty($seg_array['path']) && empty($seg_array['query'])) return $org_url;
      if( strpos($seg_array['path'], '.php') === false && empty($seg_array['query'])) return $org_url;

      if( !empty($seg_array['query']) ) $seg_array['path'] .= '?';
      $tmp_array = explode('/', $seg_array['path'] . $seg_array['query']);

      $url = $tmp_array[count($tmp_array)-1];
      $key_osc = md5($url);
      if( isset($this->osc_keys_array[$key_osc]) ) {
        $separator = $this->osc_keys_array[$key_osc]['sep'];
        return $this->osc_keys_array[$key_osc]['url'];
      }

      $force_update = false;
      // Check if the url already recorded, if so skip processing
      if( SEO_CONTINUOUS_CHECK == 'false' ) {
        $check_query = $db->query("select seo_url_key, seo_url_get, unix_timestamp(last_modified) as last_time from " . TABLE_SEO_URL . " where osc_url_key = '" . $db->filter($key_osc) . "'");
        if( $db->num_rows($check_query) ) {
          $check_array = $db->fetch_array($check_query);

          $separator = '?';
          $this->osc_keys_array[$key_osc] = array('url' => $check_array['seo_url_get'], 'sep' => $separator);
          return $cDefs->relpath . $check_array['seo_url_get'];

          $diff_time = time() - $check_array['last_time'];
          if( $diff_time < SEO_PERIODIC_REFRESH ) {
            $separator = '?';
            $this->osc_keys_array[$key_osc] = array('url' => $check_array['seo_url_get'], 'sep' => $separator);
            return $cDefs->relpath . $check_array['seo_url_get'];
          }
          $force_update = true;
          $old_key = $check_array['seo_url_key'];
        }
      }

      if( $store !== true ) {
        return $org_url;
      }

      $seo_url = '';
      $result = $this->parse_params($url, $seo_url);

      if( !$result ) {
        $this->osc_keys_array[$key_osc] = array('url' => $org_url, 'sep' => $separator);
        return $org_url;
      }

      $key = md5($seo_url);

      // Redirection double-check. Do not build url if a redirect exists but keep the redirect record.
      if( $this->check_redirection($key, '', true) ) {
        $this->osc_keys_array[$key_osc] = array('url' => $url, 'sep' => $separator);
        return $url;
      }

      $check_query = $db->query("select seo_url_get, seo_url_org, osc_url_key from " . TABLE_SEO_URL . " where seo_url_key = '" . $db->input($key) . "' or osc_url_key = '" . $db->input($key_osc) . "'");
      $key_rows = $db->num_rows($check_query);
      if( $key_rows > 1 ) {
        $force_update = true;
      }
      if( $key_rows ) {
        $seo_array = $db->fetch_array($check_query);
/*
        // Note: SEO_CONTINUOUS_CHECK switch = true, should be used for short periods of time as it significantly increases latency.
        //if( $force_update || ($seo_array['seo_url_org'] != $url && SEO_CONTINUOUS_CHECK == 'true') ) {
        if( $force_update || ($seo_array['osc_url_key'] != $key_osc || SEO_CONTINUOUS_CHECK == 'true') ) {
          if( $force_update ) {
            if( $old_key != $key || $seo_array['osc_url_key'] != $key_osc) {
              $db->query("delete from " . TABLE_SEO_URL . " where seo_url_key = '" . $db->input($old_key) . "'");
              $db->query("delete from " . TABLE_SEO_URL . " where osc_url_key = '" . $db->input($key_osc) . "'");
              $db->query("delete from " . TABLE_SEO_URL . " where seo_url_key = '" . $db->input($key) . "'");
              $this->insert_record($key, $seo_url, $key_osc, $url);
            } else {
              $sql_data_array = array(
                'seo_url_org' => $db->prepare_input($url),
                'last_modified' => 'now()'
              );
              $db->perform(TABLE_SEO_URL, $sql_data_array, 'update', "seo_url_key = '" . $db->input($key) . "'");
            }
          } else {
            $db->query("delete from " . TABLE_SEO_URL . " where osc_url_key = '" . $db->input($key_osc) . "'");
            $db->query("delete from " . TABLE_SEO_URL . " where seo_url_key = '" . $db->input($key) . "'");
            $this->insert_record($key, $seo_url, $key_osc, $url);
          }
        }
*/
      } else {
        $this->insert_record($key, $seo_url, $key_osc, $url);
      }
      $separator = '?';
      $this->osc_keys_array[$key_osc] = array('url' => $seo_url, 'sep' => $separator);
      return $cDefs->relpath . $seo_url;
      //return $seo_url;
    }


    function insert_record($key, $seo_url, $key_osc, $url) {
      extract(tep_load('database'));

      $check_query = $db->query("select seo_url_key from " . TABLE_SEO_URL . " where seo_url_key = '" . $db->input($key) . "'");
      if( !$db->num_rows($check_query) ) {
        $sql_data_array = array(
          'seo_url_key' => $db->prepare_input($key),
          'seo_url_get' => $db->prepare_input($seo_url),
          'osc_url_key' => $db->prepare_input($key_osc),
          'seo_url_org' => $db->prepare_input($url),
          'date_added' => 'now()',
          'last_modified' => 'now()'
        );
        $db->perform(TABLE_SEO_URL, $sql_data_array);
      } else {
/*
        $sql_data_array = array(
          'seo_url_get' => $db->prepare_input($seo_url),
          'osc_url_key' => $db->prepare_input($key_osc),
          'seo_url_org' => $db->prepare_input($url),
          'last_modified' => 'now()'
        );
        $db->perform(TABLE_SEO_URL, $sql_data_array, 'update', "seo_url_key = '" . $db->input($key) . "'");
*/
      }
    }

    function parse_params(&$url, &$seo_url) {
      $this->error_level = 0;
      $result = false;
      $seo_url = '';
      $url = trim($url, '&');
      $seo_array = parse_url($url);
      // Validate result
      if( !is_array($seo_array) || !isset($seo_array['path']) ) {
        return $result;
      }

      $this->path = basename($seo_array['path']);

      // Process the query part.
      $query = isset($seo_array['query'])?$seo_array['query']:'';

      if( tep_not_null($query) ) {
        $query = htmlspecialchars(urldecode($query));
        $query = str_replace('&amp;', '&', $query);
      }
      $this->query = $query;

      // Check exclusion list scripts and parameters
      if( $this->exclude_script() ) {
        return $result;
      }

      // Store original query
      $osc_query = $query;
      $osc_path = $path = $seo_array['path'];

      if( tep_not_null($query) ) {
        if( count($this->params_array) ) {
          $other = false;
          $result = $this->translate_params($other, $query);
          // Check if safe mode is on and unknown parameters were detected, in which case abort.
          if( $other && SEO_DEFAULT_SAFE_MODE == 'true') {
            return false;
          }
          if($result == 2) {
            $this->error_level = 2;
            return false;
          }
        }
        $query = $this->create_safe_string($query, SEO_DEFAULT_PARTS_SEPARATOR);
      }

      if( tep_not_null($path) ) {
        if( tep_not_null($query) ) {
          if($result == 1) {
            $tmp_array = explode('/', $path);
            $count = is_array($tmp_array)?count($tmp_array):0;
            if( $count ) {
              unset($tmp_array[$count-1]);
              $path = implode('/', $tmp_array);
            } else {
              $path = '';
            }
            //$path .= '/';
          } else {
            $path = str_replace('.php', SEO_DEFAULT_INNER_SEPARATOR, $path);
          }
        } else {
          $path = str_replace('.php', '', $path);
        }
      }

      if( tep_not_null($osc_query) ) {
        $this->eliminate_session();
        if( count($this->params_array) ) {
          $osc_query = '?' . implode('&', $this->params_array);
        } else {
          $osc_query = '';
        }
      }

      $url = $osc_path . $osc_query;
      $seo_url = $path . $query . $this->default_extension;
      $seo_url = str_replace('___', '-', $seo_url);
      return true;
    }

    // Convert supported url parameters
    function translate_params(&$other, &$query) {
      extract(tep_load('sessions'));

      $this->handler_flag = $other = false;
      $result = 0;
      $flags_array = array('other' => false);
      $seo_params_array = array();
      $params_array = array();
      $array_and = $this->params_array;
      foreach ($array_and as $key => $value) {
        $inner = explode('=', $value);
        if( !is_array($inner) || count($inner) != 2) {
          if( SEO_STRICT_VALIDATION == 'false' ) {
            $this->assign_default($params_array, $value);
          }
          $flags_array['other'] = true;
          continue;
        }
        // No Sessions should ever passed to this class and this is going to be enforced.
        if( $inner[0] == $cSessions->name ) {
          continue;
        }

        switch($inner[0]) {
          case 'gtext_id':
            if( isset($flags_array['gtext_id']) || !tep_not_null($inner[1]) || $inner[1] == '0' ) break;
            if( !is_numeric($inner[1]) ) {
              return 2;
            }
            $this->auto_builder($inner[0], $inner[1]);
            $params_query_raw = "select s2g.seo_name, st.sort_order, st.seo_types_linkage, st.seo_types_prefix, st.seo_types_handler, st.seo_types_subfix from " . TABLE_SEO_TO_GTEXT . " s2g, " . TABLE_SEO_TYPES . " st where st.seo_types_class='seo_gtext' and st.seo_types_status='1' and s2g.gtext_id = '" . (int)$inner[1] . "'";
            if( !$this->set_id($params_query_raw, $seo_params_array) ) {
              $this->assign_default($params_array, $value);
            }
            $flags_array['gtext_id'] = $inner[1];
            break;
          case 'abz_id':
            if( isset($flags_array['abz_id']) || !tep_not_null($inner[1]) || $inner[1] == '0') break;
            if( !is_numeric($inner[1]) ) {
              return 2;
            }
            $this->auto_builder($inner[0], $inner[1]);
            $params_query_raw = "select s2az.seo_name, st.sort_order, st.seo_types_linkage, st.seo_types_prefix, st.seo_types_handler, st.seo_types_subfix from " . TABLE_SEO_TO_ABSTRACT . " s2az, " . TABLE_SEO_TYPES . " st where st.seo_types_class='seo_abstract' and st.seo_types_status='1' and s2az.abstract_zone_id = '" . (int)$inner[1] . "'";
            if( !$this->set_id($params_query_raw, $seo_params_array) ) {
              $this->assign_default($params_array, $value);
            }
            $flags_array['abz_id'] = $inner[1];
            break;
          case 'page':
            if( isset($flags_array['page']) || !tep_not_null($inner[1]) || $inner[1] == '0') break;
            if( !is_numeric($inner[1]) ) {
              return 2;
            }
            $handler = '';
            if( !$this->handler_flag && count($flags_array) == 1 ) {
              $tmp_string = str_replace('.php', '', $this->path);
              $tmp_string = $this->get_script($tmp_string);
              $handler = $tmp_string . SEO_DEFAULT_INNER_SEPARATOR;

              //$handler = str_replace('.php', SEO_DEFAULT_INNER_SEPARATOR, $this->path);
              $this->handler_flag = true;
            }
            $seo_params_array[$handler . 'p' . $inner[1]] = '99' . '_' . '-1';
            $flags_array['page'] = $inner[1];
            break;

          default:
            $process_flag = false;
            // Custom Parameter handling add it here
            if( !$process_flag ) {
              $this->assign_default($params_array, $value);
              $flags_array['other'] = true;
            }
            break;
        }
      }
      if( count($seo_params_array) ) {
        $this->resolve_linkage($seo_params_array);
        asort($seo_params_array, SORT_NUMERIC);
        $seo_params_array = array_keys($seo_params_array);
        $params_array = array_merge($seo_params_array, $params_array);
        $result = 1;
      }
      $query = implode('&', $params_array);
      $other = $flags_array['other'];
      return $result;
    }

    function resolve_linkage(&$seo_params_array) {
      $tmp_array = array();
      foreach($seo_params_array as $key => $value) {
        list($sort, $link) = preg_split("/_/", $value, 2);
        $seo_params_array[$key] = $sort;
        $tmp_array[$key] = $link;
      }
      asort($tmp_array, SORT_NUMERIC);
      foreach($tmp_array as $key => $value) {
        if( $value < 0 )
          continue;

        if( !isset($reduce) ) {
          $reduce = $value;
          continue;
        }
        if($reduce != $value) {
          unset($seo_params_array[$key]);
        }
      }
    }

    function auto_builder($entity, $id) {
      extract(tep_load('database'));

      if( SEO_AUTO_BUILDER == 'false' )
        return;

      switch($entity) {
        case 'gtext_id':
          $check_query = $db->query("select gtext_id from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$id . "'");
          if( $db->num_rows($check_query) ) return;

          $name_query = $db->query("select gtext_title as name from " . TABLE_GTEXT . " where gtext_id = '" . (int)$id . "'");
          if( $db->num_rows($name_query) ) {
            $names_array = $db->fetch_array($name_query);
            $types_query = $db->query("select seo_types_id from " . TABLE_SEO_TYPES . " where seo_types_class = 'seo_gtext' and seo_types_status='1'");
            if( $db->num_rows($types_query) ) {
              $types_array = $db->fetch_array($types_query);
              if( function_exists('translate_to_ascii') ) {
                $names_array['name'] = translate_to_ascii($names_array['name']);
              }
              $seo_name = $this->create_safe_name($names_array['name']);
              $sql_data_array = array(
                'gtext_id' => (int)$id,
                'seo_name' => $db->prepare_input($seo_name),
              );
              $db->perform(TABLE_SEO_TO_GTEXT, $sql_data_array, 'insert');
            }
          }
          break;
        case 'abz_id':
          $check_query = $db->query("select abstract_zone_id from " . TABLE_SEO_TO_ABSTRACT . " where abstract_zone_id = '" . (int)$id . "'");
          if( $db->num_rows($check_query) ) return;

          $name_query = $db->query("select abstract_zone_name as name from " . TABLE_ABSTRACT_ZONES . " where abstract_zone_id = '" . (int)$id . "'");
          if( $db->num_rows($name_query) ) {
            $names_array = $db->fetch_array($name_query);
            $types_query = $db->query("select seo_types_id from " . TABLE_SEO_TYPES . " where seo_types_class = 'seo_abstract' and seo_types_status='1'");
            if( $db->num_rows($types_query) ) {
              $types_array = $db->fetch_array($types_query);
              if( function_exists('translate_to_ascii') ) {
                $names_array['name'] = translate_to_ascii($names_array['name']);
              }
              $seo_name = $this->create_safe_name($names_array['name']);
              $sql_data_array = array(
                'abstract_zone_id' => (int)$id,
                'seo_name' => $db->prepare_input($seo_name),
              );
              $db->perform(TABLE_SEO_TO_ABSTRACT, $sql_data_array, 'insert');
            }
          }
          break;
        default:
          break;
      }
    }

    function set_id($query_raw, &$seo_params_array) {
      extract(tep_load('database'));

      $result = $handler = false;
      $params_query = $db->query($query_raw);
      if( $entry = $db->fetch_array($params_query) ) {
        if( tep_not_null($entry['seo_types_subfix']) ) {
          $handler_array = explode(',', $entry['seo_types_handler']);
          $subfix_array = explode(',', $entry['seo_types_subfix']);
          foreach($handler_array as $key => $value ) {
            $value = trim($value);
            if( $this->path == $value ) {
              if( isset($subfix_array[$key]) ) {
                $handler = $subfix_array[$key];
              } else {
                $handler = $value;
                $handler = str_replace('.php', SEO_DEFAULT_INNER_SEPARATOR, $handler);
              }
              break;
            }
          }
        }
        if( $handler && !$this->handler_flag) {
          //$handler = str_replace('.php', SEO_DEFAULT_INNER_SEPARATOR, $handler);
          $seo_params_array[$entry['seo_name'] . SEO_DEFAULT_INNER_SEPARATOR . $handler] = $entry['sort_order'] . '_' . $entry['seo_types_linkage'];
          $this->handler_flag = true;
        } else {
          $seo_params_array[$entry['seo_types_prefix'] . $entry['seo_name']] = $entry['sort_order'] . '_' . $entry['seo_types_linkage'];
        }
        $result = true;
      }
      return $result;
    }

    function set_path($query_raw, &$tmp_array, &$depth, &$sort_order) {
      extract(tep_load('database'));

      $result = $handler = false;
      $params_query = $db->query($query_raw);
      if( $entry = $db->fetch_array($params_query) ) {
        if( !$depth ) {
          if( tep_not_null($entry['seo_types_subfix']) ) {
            $handler_array = explode(',', $entry['seo_types_handler']);
            $subfix_array = explode(',', $entry['seo_types_subfix']);
            foreach($handler_array as $key => $value) {
              if( $this->path == $value ) {
                if( isset($subfix_array[$key]) ) {
                  $handler = $subfix_array[$key];
                } else {
                  $handler = $value;
                  $handler = str_replace('.php', SEO_DEFAULT_INNER_SEPARATOR, $handler);
                }
              }
            }
          }
          if( $handler && !$this->handler_flag ) {
            //$handler = str_replace('.php', SEO_DEFAULT_INNER_SEPARATOR, $handler);
            $tmp_array[] = $handler . $entry['seo_name'];
            $tmp_array[] = $entry['seo_name'] . SEO_DEFAULT_INNER_SEPARATOR . $handler;
            $this->handler_flag = true;
          } else {
            $tmp_array[] = $entry['seo_types_prefix'] . $entry['seo_name'];
          }
          $sort_order = $entry['sort_order'] . '_' . $entry['seo_types_linkage'];
        } else {
          $tmp_array[] = $entry['seo_name'];
        }
        $depth++;
        $result = true;
      }
      return $result;
    }

    function assign_default(&$params_array, $value) {
      $value = $this->create_safe_string($value);
      $params_array[$value] = $value;
    }

    function exclude_script() {
      extract(tep_load('database'));
      // Make sure this is a php script otherwise exclude it.
      if( strlen($this->path) < 5 || substr($this->path, -4, 4) != '.php') {
        return true;
      }
      $result = false;
      $key = md5($this->path);

      $check_query = $db->query("select seo_exclude_key from " . TABLE_SEO_EXCLUDE . " where seo_exclude_key = '" . $db->input($key) . "'");
      if( $db->num_rows($check_query) ) {
         return true;
      }
      $this->params_array = explode('&', $this->query );
      return $result;
    }

    // Validate REQUEST_URI in case we got a redirect from a server script. May needed with some servers
    function validate_uri(&$seo_url) {
      extract(tep_load('defs'));

      $request_uri = explode('?', $_SERVER['REQUEST_URI']);
      $self = basename($_SERVER['PHP_SELF']);
      $self_count = strlen($self);
      if( is_array($request_uri) && isset($request_uri[1]) && strlen($request_uri[0]) > $self_count && $self == substr($request_uri[0], -$self_count, $self_count) ) {
        $this->params_array = explode('&', $request_uri[1]);
        if( is_array($this->params_array) ) {
          $seo_url = $_SERVER['REQUEST_URI'] = $this->params_array[0];
          unset($this->params_array[0]);
          $query_string = implode('&',$this->params_array);
          if( $query_string != '' ) {
            $seo_url .= '?' . $query_string;
            $_SERVER['REQUEST_URI'] = $seo_url;
          }
          // Rectify seo url
          $seo_url = $cDefs->relpath . $_SERVER['REQUEST_URI'];
        }
      }
    }

    // Scan redirection table for matches against incoming urls.
    function check_redirection($key, $seo_right, $check_only=false) {
      extract(tep_load('database'));

      if( SEO_DEFAULT_ENABLE == 'false' || SEO_REDIRECT_TABLE == 'false' || !empty($_POST) ) {
        return false;
      }

      $update = true;
      if( !empty($key) ) {
        $check_query = $db->query("select seo_url_org, seo_redirect from " . TABLE_SEO_REDIRECT . " where seo_url_key = '" . $db->input($key) . "'");
        if( $db->num_rows($check_query) ) {
          $seo_array = $db->fetch_array($check_query);

          if( $check_only ) 
            return true;

          $separator = '';
          $url = $seo_array['seo_url_org'];
          $url_parse = parse_url($url);
          if( !isset($url_parse['query']) ) {
            if( $seo_right != '' ) {
              $separator = '?';
            }
            $url_query = '';
          } else {
            if( $seo_right != '' ) {
              $separator = '&';
            }
            $url_query = '?' . $url_parse['query'];
          }

          // Abort on duplicates
          $double_query = $db->query("select seo_url_key from " . TABLE_SEO_URL . " where seo_url_key = '" . $db->input($key) . "'");
          if($db->num_rows($double_query)) return false;

        } else {
          return false;
        }
      } else {

        if( empty($seo_right) ) return false;
        $key = md5($seo_right);

        $check_query = $db->query("select seo_url_key, seo_url_get from " . TABLE_SEO_URL . " where osc_url_key = '" . $db->input($key) . "'");
        if( $db->num_rows($check_query) ) {

          if( $check_only ) return true;
          if( SEO_FORCE_OSC_REDIRECT == 'false' ) return false;

          $seo_array = $db->fetch_array($check_query);
          $update = false;
          $seo_array['seo_redirect'] = SEO_DEFAULT_ERROR_HEADER;
          $key = $seo_array['seo_url_key'];
          $url = $url_query = $separator = '';
          $seo_right = $seo_array['seo_url_get'];

        } else {
          $check_query = $db->query("select seo_url_key, seo_url_org, seo_redirect from " . TABLE_SEO_REDIRECT . " where seo_url_get = '" . $db->input($seo_right) . "'");

          $check_query = $db->query("select seo_url_key, seo_url_org, seo_redirect from " . TABLE_SEO_REDIRECT . " where seo_url_key = '" . $db->input($key) . "'");
          if( $db->num_rows($check_query) ) {
            $seo_array = $db->fetch_array($check_query);

            if( $check_only ) 
              return true;

            $key = $seo_array['seo_url_key'];
            $url = $url_query = $separator = '';
            $seo_right = $seo_array['seo_url_org'];
          } else {
            $this->check_url_proximity($seo_right);
            return false;
          }

        }

      }

      if($update) {
        //$db->query("update " . TABLE_SEO_REDIRECT . " set seo_url_hits = seo_url_hits+1, last_modified=now() where seo_url_key = '" . $db->input($key) . "'");
      }
      $url_redirect = $url . $url_query . $separator . $seo_right;
      $this->issue_redirect($seo_array['seo_redirect'], $url_redirect);
      return false;
    }

    function eliminate_session($remove_name=false) {
      extract(tep_load('sessions'));

      if( !$remove_name ) {
        $remove_name = $cSessions->name;
      }
      if( is_array($this->params_array) ) {
        for($i=0, $j=count($this->params_array); $i<$j; $i++ ) {
          if(strpos($this->params_array[$i], $remove_name) !== false ) {
            unset($this->params_array[$i]);
          }
        }
      }
    }

    // Proximity redirect
    function check_url_proximity($seo_right) {
      extract(tep_load('defs', 'database'));

      $result = false;

      $key = md5($seo_right);
      $check_query = $db->query("select seo_url_key, seo_url_org from " . TABLE_SEO_URL . " where seo_url_key = '" . $db->input($key) . "'");
      if( !$db->num_rows($check_query) && SEO_PROXIMITY_CLEANUP == 'true' ) {
        $url_parse = parse_url($seo_right);

        $ext_array = explode(',', SEO_DEFAULT_EXTENSION);
        if( !is_array($ext_array) || empty($ext_array) ) {
          $ext_array = array('');
        }
        $valid_extension = -1;
        for( $i=0, $j=count($ext_array); $i<$j; $i++) {
          if( !empty($ext_array[$i]) && substr($url_parse['path'], -strlen($ext_array[$i]), strlen($ext_array[$i])) != $ext_array[$i] ) continue;
          $valid_extension = strlen($ext_array[$i]);
          break;
        }

        if( !empty($url_parse['query']) || $valid_extension < 0 ) {
          return $result;
        }

        if( $valid_extension ) {
          $seo_right = substr($seo_right, 0, -$valid_extension );
        }

        if( strlen(basename($seo_right)) > SEO_PROXIMITY_THRESHOLD ) {

          $match = $seo_right = $this->create_safe_string($seo_right, SEO_DEFAULT_PARTS_SEPARATOR, true);

          do {
            $check_query = $db->query("select seo_url_key, seo_url_get from " . TABLE_SEO_URL . " where seo_url_get like '" . $db->input($match) . "%' order by seo_url_hits desc limit 1");
            if( $db->num_rows($check_query) ) {
              $seo_array = $db->fetch_array($check_query);
              $seo_array['seo_redirect'] = SEO_DEFAULT_ERROR_HEADER;
              $key = $seo_array['seo_url_key'];
              $seo_right = $seo_array['seo_url_get'];
              $result = true;
              break;
            } else {
              $match = substr($match, 0, -1);
            }
          } while( strlen($match) > SEO_PROXIMITY_THRESHOLD);

          $match = $seo_right;

          if(!$result) do {
            $check_query = $db->query("select seo_url_key, seo_url_get from " . TABLE_SEO_URL . " where seo_url_get like '" . $db->input($match) . "%' order by seo_url_hits desc limit 1");
            if( $db->num_rows($check_query) ) {
              $seo_array = $db->fetch_array($check_query);
              $seo_array['seo_redirect'] = SEO_DEFAULT_ERROR_HEADER;
              $key = $seo_array['seo_url_key'];
              $seo_right = $seo_array['seo_url_get'];
              $result = true;
              break;
            } else {
              $match = substr($match, 1);
            }
          } while( strlen($match) > SEO_PROXIMITY_THRESHOLD);

        }
      }
      if( $result ) {
        $url_redirect = $cDefs->relpath . $seo_right;
        $this->issue_redirect($seo_array['seo_redirect'], $url_redirect);
      }
      return $result;
    }

    function issue_redirect($type_redirect, $url_redirect) {
      // Issue Redirect
      header("HTTP/1.1 " . $type_redirect);
      header('P3P: CP="NOI ADM DEV PSAi COM NAV STP IND"');
      header('Location: ' . $url_redirect);
      exit();
    }

    function cache_urls() {
      extract(tep_load('database'));

      if( SEO_CACHE_ENABLE == 'false' || $this->osc_key == '') {
        return;
      }
      $check_query = $db->query("select osc_url_key from " . TABLE_SEO_CACHE . " where osc_url_key = '" . $db->filter($this->osc_key) . "'");
      if( !$db->num_rows($check_query) ) {
        $keys_array = array_keys($this->osc_keys_array);
        $keys_string = implode(',', $keys_array);
        unset($keys_array);
        $url_array = array();
        $sep_array = array();
        foreach($this->osc_keys_array as $key => $value ) {
          $url_array[] = $value['url'];
          $sep_array[] = $value['sep'];
        }
        $url_string = implode(',', $url_array);
        $sep_string = implode(',', $sep_array);
        unset($url_array, $sep_array);

        $keys_zip = base64_encode(gzdeflate($keys_string, 1));
        $url_zip = base64_encode(gzdeflate($url_string, 1));
        $sql_data_array = array(
          'osc_url_key' => $db->prepare_input($this->osc_key),
          'seo_cache_keys' => $db->prepare_input($keys_zip),
          'seo_cache_urls' => $db->prepare_input($url_zip),
          'seo_cache_separators' => $db->prepare_input($sep_string),
          'date_added' => 'now()'
        );
        $db->perform(TABLE_SEO_CACHE, $sql_data_array);
      }
    }

    function cache_init($key) {
      extract(tep_load('database'));

      if( SEO_CACHE_ENABLE == 'false' || empty($key) ) {
        return;
      }

      $check_query = $db->query("select seo_cache_keys, seo_cache_urls, seo_cache_separators from " . TABLE_SEO_CACHE . " where osc_url_key = '" . $db->input($key) . "'");
      if( $db->num_rows($check_query) ) {
        $check_array = $db->fetch_array($check_query);
        $keys_array = explode(',', gzinflate(base64_decode($check_array['seo_cache_keys'])));
        //$keys_array = explode(',', $check_array['seo_cache_keys']);
        $url_array = explode(',', gzinflate(base64_decode($check_array['seo_cache_urls'])));
        $sep_array = explode(',', $check_array['seo_cache_separators']);
        for($i=0, $j=count($keys_array); $i<$j; $i++ ) {
          $this->osc_keys_array[$keys_array[$i]] = array('url' => $url_array[$i], 'sep' => $sep_array[$i]);
        }
        unset($keys_array, $url_array, $sep_array);
        $past_time = strtotime(SEO_UPDATE_TIMEOUT);
        $new_time = time()-86400;
        if( $new_time > $past_time) {
          $db->query("delete from " . TABLE_SEO_CACHE . " where (unix_timestamp(now()) - unix_timestamp(date_added)) > " . SEO_CACHE_REFRESH);
          $db->query("update " . TABLE_CONFIGURATION . " set configuration_value=now() where configuration_key = 'SEO_UPDATE_TIMOUT'");
          $db->query("alter table " . TABLE_SEO_CACHE . " ENGINE = InnoDB");
          $db->query("alter table " . TABLE_SEO_URL . " ENGINE = InnoDB");
        }
      }
    }
  }
?>
