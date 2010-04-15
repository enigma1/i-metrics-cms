<?php
/*
  $Id: split_page_results.php,v 1.15 2003/06/09 22:35:34 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Split results into multiple pages
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - 07/05/2007: Renamed to post_page_results.php
// - 07/05/2007: PHP5 Register Globals and Long Arrays Off support added
// - 07/06/2007: PHP5 Long Arrays Off support added
// - 09/24/2009: Rewritten constructor to support SQL conbinations for 
//               ordering, grouping, sorting. Removed POST dependencies.
// - 09/25/2009: Rewritten display_links function to process POST forms
// - 09/26/2009: Added POST handling instead of GET to hide searches
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class postPageResults {
    var $sql_query, $number_of_rows, $current_page_number, $number_of_pages, $number_of_rows_per_page, $page_name;

    function postPageResults($query, $max_rows, $count_key = '*', $page_holder = 'page') {
      global $g_db;

      $this->sql_query = $query;
      $this->page_name = $page_holder;

      if (isset($_GET[$page_holder])) {
        $page = (int)$_GET[$page_holder];
      } else {
        $page = 1;
      }
      if( $page <= 0 ) $page = 1;

      $this->current_page_number = $page;
      $this->number_of_rows_per_page = $max_rows;

      $pos_to = strlen($this->sql_query);
      $pos_from = strpos($this->sql_query, ' from', 0);

      $pos_group_by = strpos($this->sql_query, ' group by', $pos_from);
      if (($pos_group_by < $pos_to) && ($pos_group_by !== false)) $pos_to = $pos_group_by;

      $pos_having = strpos($this->sql_query, ' having', $pos_from);
      if (($pos_having < $pos_to) && ($pos_having !== false)) $pos_to = $pos_having;

      $pos_order_by = strpos($this->sql_query, ' order by', $pos_from);
      if (($pos_order_by < $pos_to) && ($pos_order_by !== false)) $pos_to = $pos_order_by;

      if (strpos($this->sql_query, 'distinct') || strpos($this->sql_query, 'group by')) {
        $count_string = 'distinct ' . $g_db->input($count_key);
      } else {
        $count_string = $g_db->input($count_key);
      }

      $count_query = $g_db->query("select count(" . $count_string . ") as total " . substr($this->sql_query, $pos_from, ($pos_to - $pos_from)));
      $count = $g_db->fetch_array($count_query);
      $this->number_of_rows = $count['total'];

      $this->number_of_pages = ceil($this->number_of_rows / $this->number_of_rows_per_page);

      if ($this->current_page_number > $this->number_of_pages) {
        $this->current_page_number = $this->number_of_pages;
      }

      $offset = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

      $this->sql_query .= " limit " . max($offset, 0) . ", " . $this->number_of_rows_per_page;
    }

// display split-page-number-links
    function display_links($max_page_links, $post_array, $parameters = '') {
      global $PHP_SELF, $request_type;

      $display_links_string = '';

      $parameters = str_replace('&amp;', '&', $parameters);
      if (tep_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';

      if( !is_array($post_array) ) $post_array = array();

      $hidden_string = '';
      foreach($post_array as $key => $value) {
        $hidden_string .= tep_draw_hidden_field($key, $value);
      }

// previous button - not displayed on first page
      if( $this->current_page_number == 2 ) { 
        $display_links_string .= tep_draw_form('split_page_previous', tep_href_link(basename($PHP_SELF), $parameters, $request_type), '', 'class="floater"');
        $display_links_string .= $hidden_string;
        $display_links_string .= tep_text_submit('split_name_previous', TEXT_PREVIOUS);
        $display_links_string .= '</form>';
      } else {
        if ($this->current_page_number > 1) {
          $display_links_string .= tep_draw_form('split_page_p' . ($this->current_page_number - 1), tep_href_link(basename($PHP_SELF), $parameters . $this->page_name . '=' . ($this->current_page_number - 1), $request_type), '', 'class="floater"');
          $display_links_string .= $hidden_string;
          $display_links_string .= tep_text_submit('split_name_p' . ($this->current_page_number - 1), TEXT_PREVIOUS);
          $display_links_string .= '</form>';
        }
      }

// check if number_of_pages > $max_page_links
      $cur_window_num = intval($this->current_page_number / $max_page_links);
      if ($this->current_page_number % $max_page_links) $cur_window_num++;

      $max_window_num = intval($this->number_of_pages / $max_page_links);
      if ($this->number_of_pages % $max_page_links) $max_window_num++;

// previous window of pages
      if ($cur_window_num > 1) {
        $index = (($cur_window_num - 1) * $max_page_links);
        $display_links_string .= tep_draw_form('split_page_' . $index, tep_href_link(basename($PHP_SELF), $parameters . $this->page_name . '=' . $index, $request_type), '', 'class="floater"');
        $display_links_string .= $hidden_string;
        $display_links_string .= tep_text_submit('split_name_' . $index, '...');
        $display_links_string .= '</form>';
      }

// page nn button
      for ($jump_to_page = 1 + (($cur_window_num - 1) * $max_page_links); ($jump_to_page <= ($cur_window_num * $max_page_links)) && ($jump_to_page <= $this->number_of_pages); $jump_to_page++) {
        if( $jump_to_page == $this->current_page_number) {
          $display_links_string .= '<b>' . $jump_to_page . '</b>';
        } elseif( $jump_to_page == 1) {
          $display_links_string .= tep_draw_form('split_page_' . $jump_to_page, tep_href_link(basename($PHP_SELF), $parameters, $request_type), '', 'class="floater"');
          $display_links_string .= $hidden_string;
          $display_links_string .= tep_text_submit('split_name' . $jump_to_page, $jump_to_page);
          $display_links_string .= '</form>';
        } else {
          $display_links_string .= tep_draw_form('split_page_' . $jump_to_page, tep_href_link(basename($PHP_SELF), $parameters . $this->page_name . '=' . $jump_to_page, $request_type), '', 'class="floater"');
          $display_links_string .= $hidden_string;
          $display_links_string .= tep_text_submit('split_name' . $jump_to_page, $jump_to_page);
          $display_links_string .= '</form>';
        }
      }

// next window of pages
      if ($cur_window_num < $max_window_num) {
        $index = (($cur_window_num) * $max_page_links + 1);
        $display_links_string .= tep_draw_form('split_page_' . $index, tep_href_link(basename($PHP_SELF), $parameters . $this->page_name . '=' . $index, $request_type), '', 'class="floater"');
        $display_links_string .= $hidden_string;
        $display_links_string .= tep_text_submit('split_name_' . $index, '...');
        $display_links_string .= '</form>';
      }

// next button
      if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)) {
        $display_links_string .= tep_draw_form('split_page_n' . ($this->current_page_number + 1), tep_href_link(basename($PHP_SELF), $parameters . $this->page_name . '=' . ($this->current_page_number + 1), $request_type), '', 'class="floater"');
        $display_links_string .= $hidden_string;
        $display_links_string .= tep_text_submit('split_name_n' . ($this->current_page_number + 1), TEXT_NEXT);
        $display_links_string .= '</form>';
      }

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
