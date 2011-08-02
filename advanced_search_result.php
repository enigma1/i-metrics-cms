<?php
/*
  $Id: advanced_search_result.php,v 1.72 2003/06/23 06:50:11 project3000 Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Search results Script
// Processes search criteria retrieves search results.
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - 07/05/2007: PHP5 Register Globals and Long Arrays Off support added
// - 07/08/2007: PHP5 Long Arrays Off support added
// - 07/12/2007: Moved HTML Header/Footer to a common section
// - 08/31/2007: HTML Body Common Sections Added
// - 09/21/2009: Ported code to use the gtext pages instead of products
// - 09/22/2009: Added POST handling instead of GET to hide searches
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  require('includes/application_top.php');

  // split page-results via post
  require(DIR_FS_CLASSES . 'post_page_results.php');
  $error_script = FILENAME_DEFAULT;

  $result_array = $g_validator->post_validate(array(
    'keywords' => array(
      'max' => 100,
      'min' => 2,
    )
  ));

  if( count($result_array['keywords']) ) {
    $messageStack->add_session(ERROR_AT_LEAST_ONE_INPUT, 'error', tep_get_script_name($error_script));
    tep_redirect(tep_href_link());
  }

  $keywords = $_POST['keywords'];
  $adv_array = array(
    'keywords' => $keywords,
  );
  $keywords_array = $keywords_exclude_array = array();

  if(!tep_parse_search_string($keywords, $keywords_array, $keywords_exclude_array)) {
    $messageStack->add_session(ERROR_INVALID_KEYWORDS, 'error', tep_get_script_name($error_script));
    tep_redirect(tep_href_link());
  }
  $g_breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT));
//  $g_breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT));
  $g_http->set_headers("Cache-Control: public");
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  $heading_row = true;
  require(DIR_FS_OBJECTS . 'html_body_header.php');
?>
        <div><h1><?php echo HEADING_TITLE_2; ?></h1></div>
<?php
  $select_str = "select gt.gtext_id, gt.gtext_title, gt.gtext_description, gt.date_added ";
  $from_str = "from " . TABLE_GTEXT . " gt ";
  $where_str = " where gt.status = '1' and gt.sub='0' ";

  $key_str = '';
  $score_array = array();

  if (isset($keywords_array) && (sizeof($keywords_array) > 0)) {
    //$where_str .= " and (";
    $key_str .= " and (";
    for ($i=0, $n=sizeof($keywords_array); $i<$n; $i++ ) {
      switch ($keywords_array[$i]) {
        //case '(':
        //case ')':
        //case 'and':
        //case 'or':
          //$where_str .= " " . $keywords_array[$i] . " ";
        //  $key_str .= " " . $keywords_array[$i] . " ";
        //  break;
        default:
          if( $i ) $key_str .= ' and ';
          $keyword = $g_db->prepare_input($keywords_array[$i]);
          $key_str .= "(gt.gtext_title like '" . $g_db->input($keyword) . "%' or gt.gtext_title like '% " . $g_db->input($keyword) . "%'";
          $key_str .= " or gt.gtext_description like '" . $g_db->input($keyword) . "%' or gt.gtext_description like '% " . $g_db->input($keyword) . "%'";
          $key_str .= " or gt.gtext_description like '%-" . $g_db->input($keyword) . "%' or gt.gtext_description like '%," . $g_db->input($keyword) . "%'";
          $key_str .= " or gt.gtext_description like '%." . $g_db->input($keyword) . "%' or gt.gtext_description like '%(" . $g_db->input($keyword) . "%'";

          $tmp_str = "(" . "(gt.gtext_title like '" . $g_db->input($keyword) . "%' or gt.gtext_title like '% " . $g_db->input($keyword) . "%'";
          $tmp_str .= " or gt.gtext_description like '" . $g_db->input($keyword) . "%' or gt.gtext_description like '% " . $g_db->input($keyword) . "%'";
          $tmp_str .= " or gt.gtext_description like '%-" . $g_db->input($keyword) . "%' or gt.gtext_description like '%," . $g_db->input($keyword) . "%'";
          $tmp_str .= " or gt.gtext_description like '%." . $g_db->input($keyword) . "%' or gt.gtext_description like '%(" . $g_db->input($keyword) . "%'";

          $key_str .= ')';
          $tmp_str .= '))';
          $listing_sql = $select_str . $from_str . $where_str . ' and ' . $tmp_str;
          $listing_split = new postPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'gt.gtext_id');
          if( $listing_split->number_of_rows > 0) {
            $score_array[md5($keywords_array[$i])] = $tmp_str;
          }
          break;
      }
    }
    //$where_str .= " )";
    $key_str .= " )";
  }

  $group_str = '';

  $order_str = ' order by gt.gtext_title';

  $listing_sql = $select_str . $from_str . $where_str . $key_str . $group_str . $order_str;
  $listing_split = new postPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'gt.gtext_id');


  if( !($listing_split->number_of_rows) && isset($keywords_array) && count($keywords_array) > 0 ) {
    $max_threshold = DEFAULT_SEARCH_SENSITIVITY;

    for($i=0, $n=sizeof($keywords_array); $i<$n; $i++ ) {
      $found_flag = false;
      switch($keywords_array[$i]) {
        default:
          if( isset($score_array[md5($keywords_array[$i])]) ) {
            continue;
          }

          $key_len = tep_string_length($keywords_array[$i]);
          if( $key_len <= $max_threshold ) {
            break;
          }

          unset($left_str, $left_count, $listing_left_split, $right_str, $right_count, $listing_right_split);
          for( $iterations = DEFAULT_SEARCH_ITERATIONS, $left_threshold = $key_len-1; $left_threshold > $max_threshold && $iterations > 0; $left_threshold--, $iterations-- ) {

            $key_str = " and (";
            //$keyword = $g_db->prepare_input($keywords_array[$i]);
            $keyword = $keywords_array[$i];
            $keyword = substr($keyword, 0, $left_threshold);
            $key_str .= "(gt.gtext_title like '%" . $g_db->input($keyword) . "%' or gt.gtext_description like '%" . $g_db->input($keyword) . "%'";

            $key_str .= ')';
            $key_str .= " )";
            $listing_sql = $select_str . $from_str . $where_str . $key_str . $group_str . $order_str;
            $listing_left_split = new postPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'gt.gtext_id');

            if ( $listing_left_split->number_of_rows > 0 ) {
              $left_key = $key_str;
              $left_count = $listing_left_split->number_of_rows;
              $left_str = $listing_sql;
              $found_flag = true;
              break;
            }
          }

          for( $iterations = DEFAULT_SEARCH_ITERATIONS, $right_threshold = $key_len-1; $right_threshold > $max_threshold && $iterations > 0; $right_threshold--, $iterations-- ) {
            $key_str = " and (";
            $keyword = $g_db->prepare_input($keywords_array[$i]);
            $keyword = substr($keyword, -$right_threshold);
            $key_str .= "(gt.gtext_title like '%" . $g_db->input($keyword) . "%' or gt.gtext_description like '%" . $g_db->input($keyword) . "%'";

            $key_str .= ')';
            $key_str .= " )";
            $listing_sql = $select_str . $from_str . $where_str . $key_str . $group_str . $order_str;
            $listing_right_split = new postPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'gt.gtext_id');

            // If the word was previously processed and results found skip the keyword
            if( $listing_right_split->number_of_rows > 0 ) {
              $right_key = $key_str;
              $right_count = $listing_right_split->number_of_rows;
              $right_str = $listing_sql;
              $found_flag = true;
              break;
            }
          }

          if($found_flag && isset($right_count) && isset($left_count) ) {
            // If both scans are successful, check percentages and promote left scan
            if( $left_threshold >= $right_threshold-1 || $right_threshold <= 1 ) {
              $listing_sql = $select_str . $from_str . $where_str . $left_key;
            } elseif($left_count < $right_count) {
              $listing_sql = $select_str . $from_str . $where_str . $left_key;
            } else {
              $listing_sql = $select_str . $from_str . $where_str . $right_key;
            }
          } elseif($found_flag && isset($right_count))  {
            $listing_sql = $select_str . $from_str . $where_str . $right_key;
          } elseif($found_flag && isset($left_count)) {
            $listing_sql = $select_str . $from_str . $where_str . $left_key;
          }

          if( $found_flag ) {
            $tmp_str = '';
            if( count($score_array) ) {
              $tmp_str = " and " . implode(" and ", $score_array);
            }
            $org_sql = $listing_sql;
            $listing_sql .= $tmp_str;
            $listing_sql .= $group_str . $order_str;
            $listing_split = new postPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'gt.gtext_id');
            if( !$listing_split->number_of_rows) {
              $listing_sql = $org_sql;
              $tmp_str = '';
              if( count($score_array) ) {
                $tmp_str = " and " . implode(" or ", $score_array);
              }
              $listing_sql .= $tmp_str;
              $listing_sql .= $group_str . $order_str;
              $listing_split = new postPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'gt.gtext_id');
            } else {
            }

            break 2;
          }
          break;
      }
    }
  }

  // Initialize vars
  $total_items = $g_db->query_to_array($listing_split->sql_query);
  $j=count($total_items);

  if( $j > 0 ) {
    if( $listing_split->number_of_rows > MAX_DISPLAY_SEARCH_RESULTS && (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3') ) {
?>
          <div class="splitLine">
            <div class="hideflow" style="width: 50%;"><?php echo '<span>' . TEXT_RESULT_PAGE . '</span>' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, $adv_array); ?></div>
            <div class="floatend"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
          </div>
<?php
    }
    for($i=0; $i<$j; $i++) {
      $short_description = strip_tags(tep_truncate_string($total_items[$i]['gtext_description']));
      $html_output = 
      '  <div class="splitColumn">' . "\n" . 
      '    <div class="floater"><h2><a href="' . tep_href_link(FILENAME_GENERIC_PAGES, 'gtext_id=' . $total_items[$i]['gtext_id']) . '" title="' . strip_tags($short_description) . '">' . $total_items[$i]['gtext_title'] . '</a></h2></div>' . "\n" .
      '    <div class="floatend">' . tep_date_short($total_items[$i]['date_added']) . '</div>' . "\n" . 
      '    <div class="cleaner">' . $short_description . '</div>' . "\n" . 
      '  </div>' . "\n";
      echo $html_output;
    }

    if( $listing_split->number_of_rows > MAX_DISPLAY_SEARCH_RESULTS && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3') ) {
?>
          <div class="splitLine">
            <div class="hideflow" style="width: 50%;"><?php echo '<span>' . TEXT_RESULT_PAGE . '</span>' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, $adv_array); ?></div>
            <div class="floatend"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
          </div>
<?php
    }
  } else {
?>
        <div><?php echo TEXT_NO_ENTRIES; ?></div>
<?php
  }
  $html_lines_array = array();
  $html_lines_array[] = '<div class="main"><a href="' . tep_href_link() . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a></div>' . "\n";
  require(DIR_FS_OBJECTS . 'html_content_bottom.php'); 
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
