<?php
/*
  $Id: split_page_results.php,v 1.15 2003/06/09 22:35:34 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2007-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Split results into multiple pages
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - Restructured for the I-Metrics CMS
// - Modified constructor to properly count the total on "distinct" and "group by"
// as it is used by the SEO-G framwework. Stock osc does not use the class with
// distinct but other contributions do. To avoid confusion this separate class
// is used with SEO-G.
// - Removed globals
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  class splitPageResults {
    // compatibility constructor
    function splitPageResults($query, $max_rows, $count_key = '*', $page_holder = 'page') {
      extract(tep_load('database'));

      $this->sql_query = $query;
      $this->page_name = $page_holder;

      if( isset($_GET[$page_holder]) ) {
        $page = $_GET[$page_holder];
      } else {
        $page = '';
      }

      if( empty($page) || $page < 1 ) $page = 1;
      $this->current_page_number = (int)$page;

      $this->number_of_rows_per_page = $max_rows;

      $pos_to = strlen($this->sql_query);
      $pos_from = strpos($this->sql_query, ' from', 0);

      $pos_group_by = strpos($this->sql_query, ' group by', $pos_from);
      if (($pos_group_by < $pos_to) && ($pos_group_by !== false)) $pos_to = $pos_group_by;

      $pos_having = strpos($this->sql_query, ' having', $pos_from);
      if (($pos_having < $pos_to) && ($pos_having !== false)) $pos_to = $pos_having;

      $pos_order_by = strpos($this->sql_query, ' order by', $pos_from);
      if (($pos_order_by < $pos_to) && ($pos_order_by !== false)) $pos_to = $pos_order_by;

      //if (strpos($this->sql_query, 'distinct') || strpos($this->sql_query, 'group by')) {
      if( strpos($this->sql_query, 'distinct') ) {
        $count_string = 'distinct ' . $db->input($count_key);
      } else {
        $count_string = $db->input($count_key);
      }

      $count_query = $db->fly("select count(" . $count_string . ") as total " . substr($this->sql_query, $pos_from, ($pos_to - $pos_from)) );
      $count = $db->fetch_array($count_query);
      $this->number_of_rows = $count['total'];

      $this->number_of_pages = ceil($this->number_of_rows / $this->number_of_rows_per_page);

      if ($this->current_page_number > $this->number_of_pages) {
        $this->current_page_number = $this->number_of_pages;
      }
      if( $this->number_of_pages > 1 ) {
        $offset = ($this->number_of_rows_per_page * ($this->current_page_number - 1));
        $this->sql_query .= " limit " . max($offset, 0) . ", " . $this->number_of_rows_per_page;
      }
    }

/* class functions */

// display split-page-number-links
    function display_links($max_page_links, $parameters = '', $style_class= 'pageResults' ) {
      extract(tep_load('defs'));

      $display_links_string = '';

      if (tep_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';

// previous button - not displayed on first page
      if( $this->current_page_number == 2 ) { 
        if ($this->current_page_number > 1) $display_links_string .= '<a href="' . tep_href_link($cDefs->script, $parameters, $cDefs->request_type) . '" class="' . $style_class . '" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' ">' . PREVNEXT_BUTTON_PREV . '</a>';
      } else {
        if ($this->current_page_number > 1) $display_links_string .= '<a href="' . tep_href_link($cDefs->script, $parameters . $this->page_name . '=' . ($this->current_page_number - 1), $cDefs->request_type) . '" class="' . $style_class . '" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' ">' . PREVNEXT_BUTTON_PREV . '</a>';
      }

// check if number_of_pages > $max_page_links
      $cur_window_num = intval($this->current_page_number / $max_page_links);
      if ($this->current_page_number % $max_page_links) $cur_window_num++;

      $max_window_num = intval($this->number_of_pages / $max_page_links);
      if ($this->number_of_pages % $max_page_links) $max_window_num++;

// previous window of pages
      if ($cur_window_num > 1) $display_links_string .= '<a href="' . tep_href_link($cDefs->script, $parameters . $this->page_name . '=' . (($cur_window_num - 1) * $max_page_links), $cDefs->request_type) . '" class="' . $style_class . '" title=" ' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a>';

// page nn button
      for ($jump_to_page = 1 + (($cur_window_num - 1) * $max_page_links); ($jump_to_page <= ($cur_window_num * $max_page_links)) && ($jump_to_page <= $this->number_of_pages); $jump_to_page++) {
        if ($jump_to_page == $this->current_page_number) {
          if( $this->number_of_pages > 1 ) {
            $display_links_string .= '<b class="' . $style_class . '">' . $jump_to_page . '</b>';
          } else {
            $display_links_string .= '<b>' . $jump_to_page . '</b>';
          }
        } elseif( $jump_to_page == 1 ) {
          $display_links_string .= '<a href="' . tep_href_link($cDefs->script, $parameters, $cDefs->request_type) . '" class="' . $style_class . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '</a>';
        } else {
          $display_links_string .= '<a href="' . tep_href_link($cDefs->script, $parameters . $this->page_name . '=' . $jump_to_page, $cDefs->request_type) . '" class="' . $style_class . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '</a>';
        }
      }

// next window of pages
      if ($cur_window_num < $max_window_num) $display_links_string .= '<a href="' . tep_href_link($cDefs->script, $parameters . $this->page_name . '=' . (($cur_window_num) * $max_page_links + 1), $cDefs->request_type) . '" class="' . $style_class . '" title=" ' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a>';

// next button
      if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)) $display_links_string .= '<a href="' . tep_href_link($cDefs->script, $parameters . $this->page_name . '=' . ($this->current_page_number + 1), $cDefs->request_type) . '" class="' . $style_class . '" title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' ">' . PREVNEXT_BUTTON_NEXT . '</a>';

      return $display_links_string;
    }

// display number of total products found
    function display_count($text_output) {
      $to_num = ($this->number_of_rows_per_page * $this->current_page_number);
      if ($to_num > $this->number_of_rows) $to_num = $this->number_of_rows;

      $from_num = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

      if ($to_num == 0) {
        $from_num = 0;
      } else {
        $from_num++;
      }

      return sprintf($text_output, $from_num, $to_num, $this->number_of_rows);
    }
  }
?>
