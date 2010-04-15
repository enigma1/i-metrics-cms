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
        tep_redirect(tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('action')) . 'action=' . $action));
      }
    }

    function create_safe_string($string, $separator=SEO_DEFAULT_WORDS_SEPARATOR) {
      $string = preg_replace('/\s\s+/', ' ', trim($string));
	  $string = preg_replace("/[^0-9a-z\-_]+/i", $separator, strtolower($string));
      $string = trim($string, $separator);
      $string = str_replace($separator . $separator . $separator, $separator, $string);
      $string = str_replace($separator . $separator, $separator, $string);
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
      global $g_db;

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
            $check_query = $g_db->query("select meta_lexico_text, sort_id from " . TABLE_META_LEXICO . " where meta_lexico_text like '%" . $g_db->filter($value) . "%' and meta_lexico_status='1' order by sort_id limit " . SEO_METAG_INCLUSION_LIMIT);
            if( !$g_db->num_rows($check_query) )
              continue;
            unset($words_array[$key]);
            while( $check_array = $g_db->fetch_array($check_query) ) {
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

          $check_query = $g_db->query("select meta_exclude_text from " . TABLE_META_EXCLUDE . " where meta_exclude_key in ('" . implode("', '", $tmp_array ) . "')");
          $words_array = array_flip($words_array);
          while( $check_array = $g_db->fetch_array($check_query) ) {
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
      global $g_db;

      $html_string = '';
      $html_string .= 
      '          <div class="listArea"><table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_SEO_ZONES . '</td>' . "\n" . 
      '              <td class="dataTableHeadingContent" align="center">' . TABLE_HEADING_ACTION . '</td>' . "\n" . 
      '            </tr>' . "\n";
      $zones_query_raw = "select at.seo_types_id, at.seo_types_name, at.seo_types_class, at.seo_types_prefix, at.seo_types_handler, at.seo_types_subfix from " . TABLE_SEO_TYPES . " at where seo_types_status='1' order by at.sort_order";
      $zones_split = new splitPageResults($zones_query_raw, SEO_PAGE_SPLIT, '', 'zpage');
      $zones_query = $g_db->query($zones_split->sql_query);
      while( $zones = $g_db->fetch_array($zones_query) ) {

        if( (!tep_not_null($this->m_zID) || (tep_not_null($this->m_zID) && ($this->m_zID == $zones['seo_types_id']))) && !isset($this->m_zInfo) && (substr($this->m_action, 0, 3) != 'new')) {
          $this->m_zInfo = new objectInfo($zones);
          $this->m_zID = $this->m_zInfo->seo_types_id;
        }
        if (isset($this->m_zInfo) && is_object($this->m_zInfo) && ($zones['seo_types_id'] == $this->m_zInfo->seo_types_id)) {
          $html_string .= 
          '          <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . tep_href_link(FILENAME_SEO_ZONES, 'zpage=' . $this->m_zpage . '&spage=1' . '&zID=' . $this->m_zInfo->seo_types_id . '&action=list') . '\'">' . "\n";
        } else {
          $html_string .= 
          '          <tr class="dataTableRow" onclick="document.location.href=\'' . tep_href_link(FILENAME_SEO_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $zones['seo_types_id']) . '\'">' . "\n";
        }
        $html_string .= 
        '              <td class="dataTableContent"><a href="' . tep_href_link(FILENAME_SEO_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $zones['seo_types_id'] . '&action=list') . '">' . tep_image(DIR_WS_ICONS . 'icon_folder.png', ICON_FOLDER) . '</a>&nbsp;' . $zones['seo_types_name'] . '</td>' . "\n" .
        '              <td class="dataTableContent" align="center">';
        $html_string .= '<a href="' . tep_href_link(FILENAME_SEO_ZONES, tep_get_all_get_params(array('zpage', 'zID', 'action')) . 'zpage=' . $this->m_zpage . '&zID=' . $zones['seo_types_id'] . '&action=validate') . '">' . tep_image(DIR_WS_ICONS . 'icon_validate.png', TEXT_VALIDATE . ' ' . $zones['seo_types_name']) . '</a>&nbsp;';
        if( isset($this->m_zInfo) && is_object($this->m_zInfo) && ($zones['seo_types_id'] == $this->m_zInfo->seo_types_id) && tep_not_null($this->m_zID) ) {
          $html_string .= tep_image(DIR_WS_ICONS . 'icon_arrow_right.png'); 
        } else { 
          $html_string .= '<a href="' . tep_href_link(FILENAME_SEO_ZONES, 'zpage=' . $this->m_zpage . '&zID=' . $zones['seo_types_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
        } 
        $html_string .= '&nbsp;</td>' . "\n" . 
        '            </tr>' . "\n";
      }
      $html_string .= 
      '              <tr>' . "\n" . 
      '                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n" . 
      '                  <tr>' . "\n" . 
      '                    <td class="smallText">' . $zones_split->display_count(TEXT_DISPLAY_NUMBER_OF_SEO_ZONES) . '</td>' . "\n" . 
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
            $heading[] = array('text' => '<b>' . $this->m_zInfo->seo_types_name . '</b>');

            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_SEO_ZONES, 'zpage=' . $this->m_zpage . '&spage=1' . '&zID=' . $this->m_zInfo->seo_types_id . '&action=validate') . '">' . tep_image_button('button_validate.gif', 'Validate Entries for this type') . '</a> <a href="' . tep_href_link(FILENAME_SEO_ZONES, 'zpage=' . $this->m_zpage . '&spage=1' . '&zID=' . $this->m_zInfo->seo_types_id . '&action=list') . '">' . tep_image_button('button_details.gif', IMAGE_DETAILS) . '</a>');
            $contents[] = array('text' => TEXT_INFO_ZONE_TYPE . '<br /><b>' . $this->m_zInfo->seo_types_name . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_CLASS . '<br /><b>' . $this->m_zInfo->seo_types_class . '.php</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_PREFIX . '<br /><b>' . $this->m_zInfo->seo_types_prefix . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_HANDLER . '<br /><b>' . $this->m_zInfo->seo_types_handler . '</b>');
            $contents[] = array('text' => TEXT_INFO_ZONE_SUBFIX . '<br /><b>' . $this->m_zInfo->seo_types_subfix . '</b>');
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
