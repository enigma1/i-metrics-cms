<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: META-G Zones root class
// Controls relationships among general pages, abstract zones, scripts, etc.
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
  class meta_zones {
   var $m_zID, $m_zpage, $m_saction, $m_action, $m_zInfo, $m_sID, $m_spage;
// class constructor
    function meta_zones() {
      $this->m_zID = isset($_GET['zID'])?$_GET['zID']:'';
      $this->m_zpage = isset($_GET['zpage'])?$_GET['zpage']:'';
      $this->m_saction = isset($_GET['saction'])?$_GET['saction']:'';
      $this->m_action = isset($_GET['action'])?$_GET['action']:'';
      $this->m_sID = isset($_GET['sID'])?$_GET['sID']:'';
      $this->m_spage = isset($_GET['spage'])?$_GET['spage']:'';
    }

    function is_top_level() {
      if( !isset($_GET['action']) ) {
        return true;
      }
      return false;
    }

    function validate_array_selection($entity, $action='list') {
      global $messageStack;
      if( !isset($_POST[$entity]) || !is_array($_POST[$entity]) || !count($_POST[$entity]) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link(FILENAME_META_ZONES, tep_get_all_get_params(array('action')) . 'action=' . $action));
      }
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
      $keywords_array = explode($separator, $string, META_MAX_KEYWORDS);
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
      if( !tep_not_null($separator) ) {
        $separator = ' ';
      }

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
          $check_query = $g_db->query("select meta_lexico_key as id, meta_lexico_text as text from " . TABLE_META_LEXICO . " where meta_lexico_text like '%" . $g_db->filter($value) . "%' and meta_lexico_status='1' order by sort_id");
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

    function create_safe_description($string, $max_length = META_MAX_DESCRIPTION, $open_tag = '<p>', $close_tag = '</p>') {

      $string = trim(strip_tags($string, $open_tag));

      $open_pos = strpos($string, $open_tag);
      $close_pos = strpos($string, $close_tag);
      if( $open_pos !== false && $close_pos !== false && $close_pos > $open_pos ) {
        $open_pos += strlen($open_tag);
        //$close_pos -= strlen($close_tag);
        $final_string = substr($string, $open_pos, $close_pos-$open_pos);
      } else {
        $final_string = strip_tags($string);
      }

      $char_size = tep_utf8_size($final_string);
      $max_length *= $char_size;
      
      if( strlen($final_string) > $max_length ) {
        $final_string = substr($final_string, 0, $max_length);
      }
      return $final_string;
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
      global $g_db;

      $html_string = '';
      $html_string .= 
      '          <div class="listArea"><table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_META_ZONES . '</td>' . "\n" . 
      '              <td class="dataTableHeadingContent" align="center">' . TABLE_HEADING_ACTION . '</td>' . "\n" . 
      '            </tr>' . "\n";
      $zones_query_raw = "select at.meta_types_id, at.meta_types_name, at.meta_types_class from " . TABLE_META_TYPES . " at where meta_types_status='1' order by at.sort_order";
      $zones_split = new splitPageResults($zones_query_raw, META_PAGE_SPLIT, '', 'zpage');
      $zones_query = $g_db->query($zones_split->sql_query);
      while( $zones = $g_db->fetch_array($zones_query) ) {

        if( (!tep_not_null($this->m_zID) || (tep_not_null($this->m_zID) && ($this->m_zID == $zones['meta_types_id']))) && !isset($this->m_zInfo) && (substr($this->m_action, 0, 3) != 'new')) {
          $this->m_zInfo = new objectInfo($zones);
          $this->m_zID = $this->m_zInfo->meta_types_id;
        }
        if (isset($this->m_zInfo) && is_object($this->m_zInfo) && ($zones['meta_types_id'] == $this->m_zInfo->meta_types_id)) {
          $html_string .= 
          '          <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&spage=1' . '&zID=' . $this->m_zInfo->meta_types_id . '&action=list') . '\'">' . "\n";
        } else {
          $html_string .= 
          '          <tr class="dataTableRow" onclick="document.location.href=\'' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $zones['meta_types_id']) . '\'">' . "\n";
        }
        $html_string .= 
        '              <td class="dataTableContent"><a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $zones['meta_types_id'] . '&action=list') . '">' . tep_image(DIR_WS_ICONS . 'icon_folder.png', ICON_FOLDER) . '</a>&nbsp;' . $zones['meta_types_name'] . '</td>' . "\n" . 
        '              <td class="dataTableContent" align="center">';
        if (isset($this->m_zInfo) && is_object($this->m_zInfo) && ($zones['meta_types_id'] == $this->m_zInfo->meta_types_id) && tep_not_null($this->m_zID) ) { 
          $html_string .= tep_image(DIR_WS_ICONS . 'icon_arrow_right.png'); 
        } else { 
          $html_string .= '<a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $zones['meta_types_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
        } 
        $html_string .= '&nbsp;</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '              <tr>' . "\n" . 
      '                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n" . 
      '                  <tr>' . "\n" . 
      '                    <td class="smallText">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_META_ZONES) . '</td>' . "\n" . 
      '                    <td class="smallText" align="right">' . $zones_split->display_links(tep_get_all_get_params(array('zpage'))) . '</td>' . "\n" . 
      '                  </tr>' . "\n" . 
      '                </table></td>' . "\n" . 
      '              </tr>' . "\n" . 
      '            </table></div>' . "\n";
      return $html_string;
    }

    function display_right_box() {
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
            $heading[] = array('text' => '<b>' . $this->m_zInfo->meta_types_name . '</b>');

            //$contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&spage=1' . '&zID=' . $this->m_zInfo->meta_types_id . '&action=validate') . '">' . tep_image_button('button_validate.gif', 'Validate Entries for this type') . '</a> <a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&spage=1' . '&zID=' . $this->m_zInfo->meta_types_id . '&action=list') . '">' . tep_image_button('button_details.gif', IMAGE_DETAILS) . '</a>');
            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_META_ZONES, 'zpage=' . $this->m_zpage . '&spage=1' . '&zID=' . $this->m_zInfo->meta_types_id . '&action=list') . '">' . tep_image_button('button_details.gif', IMAGE_DETAILS) . '</a>');
            $contents[] = array('text' => TEXT_INFO_ZONE_TYPE . '<br /><b>' . $this->m_zInfo->meta_types_name . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_CLASS . '<br /><b>' . $this->m_zInfo->meta_types_class . '.php</b>');
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