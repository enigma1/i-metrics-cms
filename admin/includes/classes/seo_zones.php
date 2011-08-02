<?php
/*
//----------------------------------------------------------------------------
//-------------- SEO-G by Asymmetrics (Renegade Edition) ---------------------
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO-G Zones root class for osCommerce Admin
// Controls relationships among products, categories, customers etc.
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
  class seo_zones {
   var $m_zID, $m_zpage, $m_saction, $m_action, $m_zInfo, $m_sID, $m_spage;
    // class constructor
    function seo_zones() {
      extract(tep_load('defs', 'database'));

      $this->m_action = $cDefs->action;
      $this->m_zID = isset($_GET['zID'])?(int)$_GET['zID']:'';
      $this->m_zpage = isset($_GET['zpage'])?(int)$_GET['zpage']:'';
      $this->m_saction = isset($_GET['saction'])?$db->prepare_input($_GET['saction']):'';
      $this->m_sID = isset($_GET['sID'])?(int)$_GET['sID']:'';
      $this->m_spage = isset($_GET['spage'])?(int)$_GET['spage']:'';

      $ext_array = explode(',', SEO_DEFAULT_EXTENSION);
      if( !is_array($ext_array) || empty($ext_array) ) {
        $ext_array = array('');
      }
      $this->default_extension = $ext_array[0];
    }

    function is_top_level() {
      extract(tep_load('defs'));

      if( empty($cDefs->action) ) {
        return true;
      }
      return false;
    }

    function validate_array_selection($entity, $action='list') {
      extract(tep_load('defs', 'message_stack'));
      if( !isset($_POST[$entity]) || !is_array($_POST[$entity]) || !count($_POST[$entity]) ) {
        $msg->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($cDefs->script, tep_get_all_get_params('action') . 'action=' . $action));
      }
    }

    function create_safe_string($string, $separator=SEO_DEFAULT_WORDS_SEPARATOR) {
      $string = tep_create_safe_string(strtolower($string), $separator, "/[^0-9a-z\-_]+/i");

      if(SEO_DEFAULT_WORD_LENGTH > 1) {
        $words_array = explode($separator, $string);
        if( is_array($words_array) ) {
          for($i=0, $j=count($words_array); $i<$j; $i++) {
            if(strlen($words_array[$i]) < SEO_DEFAULT_WORD_LENGTH) {
              unset($words_array[$i]);
            }
          }
          if(count($words_array))
            $string = implode($separator, $words_array);
        }
      }
      return $string;
    }

    function adapt_lexico($string, $separator=SEO_DEFAULT_WORDS_SEPARATOR) {
      extract(tep_load('database'));

      $words_array = explode($separator, $string);
      if( !defined(META_USE_LEXICO) || !is_array($words_array) ) {
        return $string;
      }

      // Apply META-G Inclusion Dictionary
      if( META_USE_LEXICO == 'true' && SEO_METAG_INCLUSION == 'true' ) {
        if( is_array($words_array) && count($words_array) ) {
          $words_array = array_unique($words_array);
          $tmp_array = array();
          foreach($words_array as $key => $value) {
            $check_query = $db->query("select meta_lexico_text, sort_id from " . TABLE_META_LEXICO . " where meta_lexico_text like '%" . $db->filter($value) . "%' and meta_lexico_status='1' order by sort_id limit " . SEO_METAG_INCLUSION_LIMIT);
            if( !$db->num_rows($check_query) ) continue;

            unset($words_array[$key]);
            while( $check_array = $db->fetch_array($check_query) ) {
              $tmp_array[$check_array['sort_id']] = $this->create_safe_string($check_array['meta_lexico_text'], $separator);
            }
          }
          $words_array = array_merge($tmp_array,$words_array);
        }
      }

      // Apply META-G Exclusion Dictionary
      if( META_USE_LEXICO == 'true' && SEO_METAG_EXCLUSION == 'true' ) {
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

    function process_action() {
      switch( $this->m_action ) {
        default:
          break;
      }
    }

    function process_saction() {
      switch( $this->m_saction ) {
        default:
          break;
      }
    }

    function display_html() {
      $html_string = '';

      if (!$this->m_action) {
        $html_string = $this->display_default();
      }
      return $html_string;
    }

    function display_default() {
      extract(tep_load('defs','database'));

      $html_string = '';
      $html_string .= 
      '          <div class="listArea"><table class="tabledata">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <th>' . TABLE_HEADING_SEO_ZONES . '</th>' . "\n" . 
      '              <th class="calign">' . TABLE_HEADING_ACTION . '</th>' . "\n" . 
      '            </tr>' . "\n";

      $rows = 0;
      $zones_query_raw = "select at.seo_types_id, at.seo_types_name, at.seo_types_class, at.seo_types_prefix, at.seo_types_handler, at.seo_types_subfix from " . TABLE_SEO_TYPES . " at where seo_types_status='1' order by at.sort_order";
      $zones_split = new splitPageResults($zones_query_raw, SEO_PAGE_SPLIT, '', 'zpage');
      $zones_query = $db->query($zones_split->sql_query);
      while( $zones = $db->fetch_array($zones_query) ) {
        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

        if( (!tep_not_null($this->m_zID) || (tep_not_null($this->m_zID) && ($this->m_zID == $zones['seo_types_id']))) && !isset($this->m_zInfo) && (substr($this->m_action, 0, 3) != 'new')) {
          $this->m_zInfo = new objectInfo($zones);
          $this->m_zID = $this->m_zInfo->seo_types_id;
        }
        if (isset($this->m_zInfo) && is_object($this->m_zInfo) && ($zones['seo_types_id'] == $this->m_zInfo->seo_types_id)) {
          $html_string .= 
          '          <tr class="dataTableRowSelected row_link" href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&spage=1' . '&zID=' . $this->m_zInfo->seo_types_id . '&action=list') . '">' . "\n";
        } else {
          $html_string .= 
          '          <tr class="' . $row_class . ' row_link" href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $zones['seo_types_id']) . '">' . "\n";
        }
        $html_string .= 
        '              <td><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $zones['seo_types_id'] . '&action=list') . '">' . tep_image(DIR_WS_ICONS . 'icon_folder.png', ICON_FOLDER) . '</a>&nbsp;' . $zones['seo_types_name'] . '</td>' . "\n" .
        '              <td class="calign">';
        $html_string .= '<a href="' . tep_href_link($cDefs->script, tep_get_all_get_params('zpage', 'zID', 'action') . 'zpage=' . $this->m_zpage . '&zID=' . $zones['seo_types_id'] . '&action=validate') . '">' . tep_image(DIR_WS_ICONS . 'icon_validate.png', TEXT_VALIDATE . ' ' . $zones['seo_types_name']) . '</a>&nbsp;';
        if( isset($this->m_zInfo) && is_object($this->m_zInfo) && ($zones['seo_types_id'] == $this->m_zInfo->seo_types_id) && tep_not_null($this->m_zID) ) {
          $html_string .= tep_image(DIR_WS_ICONS . 'icon_arrow_right.png'); 
        } else { 
          $html_string .= '<a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&zID=' . $zones['seo_types_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
        } 
        $html_string .= '</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '          </table></div>' . "\n" . 
      '          <div class="listArea splitLine">' . "\n" . 
      '            <div class="floater">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES) . '</div>' . "\n" . 
      '            <div class="floatend">' . $zones_split->display_links(tep_get_all_get_params('zpage') ) . '</div>' . "\n" . 
      '          </div>' . "\n";
      return $html_string;
    }

    function display_right_box() {
      extract(tep_load('defs'));

      $html_string = '';

      if( !$this->is_top_level() ) {
        return $html_string;
      }
      $heading = array();
      $contents = array();

      switch( $this->m_action ) {
        case 'list':
          break;
        default:
          if (isset($this->m_zInfo) && is_object($this->m_zInfo) && tep_not_null($this->m_zID) ) {
            $heading[] = array('text' => '<b>' . $this->m_zInfo->seo_types_name . '</b>');

            $contents[] = array('class' => 'infoBoxSection', 'section' => '<div>');
            $contents[] = array('text' => TEXT_INFO_ZONE_TYPE . '<br /><b>' . $this->m_zInfo->seo_types_name . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_CLASS . '<br /><b>' . $this->m_zInfo->seo_types_class . '.php</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_PREFIX . '<br /><b>' . $this->m_zInfo->seo_types_prefix . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_HANDLER . '<br /><b>' . $this->m_zInfo->seo_types_handler . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_SUBFIX . '<br /><b>' . $this->m_zInfo->seo_types_subfix . '</b>');
            $contents[] = array('section' => '</div>');
            $contents[] = array('class' => 'calign', 'text' => '<a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&spage=1' . '&zID=' . $this->m_zInfo->seo_types_id . '&action=validate') . '">' . tep_image_button('button_validate.gif', 'Validate Entries for this type') . '</a><a href="' . tep_href_link($cDefs->script, 'zpage=' . $this->m_zpage . '&spage=1' . '&zID=' . $this->m_zInfo->seo_types_id . '&action=list') . '">' . tep_image_button('button_details.gif', IMAGE_DETAILS) . '</a>');
          } else { // create generic_text dummy info
            $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
            $contents[] = array('text' => TEXT_NO_GENERIC);
          }
          break;
      }
      if( !empty($heading) && !empty($contents) ) {
        $html_string .= '            <div class="rightcell">' . "\n";
        $box = new box;
        $html_string .= $box->infoBox($heading, $contents);
        $html_string .= '            </div>' . "\n";
      }
      return $html_string;
    }

    function display_bottom() {
       return '';
    }
  }
?>
