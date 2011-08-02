<?php
/*
File came from catalog\admin\includes\classes\split_page_results.php

  $Id: split_page_results.php,v 1.13 2003/05/05 17:56:50 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: General database page splitter via the GET array
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
// - Added page loose groups and page view limit for large page sets
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class splitPageResults {
    // compatibility constructor
    function splitPageResults($query, $max_rows = MAX_DISPLAY_SEARCH_RESULTS, $count_key = '*', $page_holder = 'page') {
      extract(tep_load('database'));

      $this->sql_query = $query;
      $this->page_name = $page_holder;

      $page = (isset($_GET[$page_holder]) && (int)$_GET[$page_holder] > 0)?(int)$_GET[$page_holder]:1;
      if( empty($count_key) ) $count_key = '*';

      $this->current_page_number = (int)$page;
      $this->number_of_rows_per_page = max((int)$max_rows, 1);

      $pos_to = strlen($this->sql_query);
      $pos_from = strpos($this->sql_query, ' from', 0);

      $pos_group_by = strpos($this->sql_query, ' group by', $pos_from);
      if (($pos_group_by < $pos_to) && ($pos_group_by !== false)) $pos_to = $pos_group_by;

      $pos_having = strpos($this->sql_query, ' having', $pos_from);
      if (($pos_having < $pos_to) && ($pos_having !== false)) $pos_to = $pos_having;

      $pos_order_by = strpos($this->sql_query, ' order by', $pos_from);
      if (($pos_order_by < $pos_to) && ($pos_order_by !== false)) $pos_to = $pos_order_by;

      //if (strpos($this->sql_query, 'distinct') || strpos($this->sql_query, 'group by')) {
      if (strpos($this->sql_query, 'distinct') ) {
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

    // display split-page-number-links
    function display_links($parameters = '') {
      extract(tep_load('defs'));

      $display_links_string = $form_links_string = '';
      if( $this->number_of_pages > 1 ) {
        $view_max = 20;
        $range_gap = 5;
        $range_step = (int)($this->number_of_pages/$view_max);
        $range_step = max($range_step, 1);

        $param_array = tep_get_string_parameters($parameters);
        $form_links_string = tep_draw_form('form_split_' . $this->page_name, $cDefs->script, '', 'get');

        for ($i=1; $i <= $this->number_of_pages; $i++) {
          if( $this->number_of_pages > $view_max  && $i > $range_gap && $i < ($this->number_of_pages-$range_gap) ) {
            if( ($i+$range_gap) < ($this->current_page_number-$range_gap) || ($i-$range_gap) > ($this->current_page_number+$range_gap) ) {
              $i = (int)min($i+$range_step, $this->number_of_pages);
            }
          }
          $pages_array[] = array('id' => $i, 'text' => $i);
        }

        $form_links_string .= sprintf(TEXT_RESULT_PAGE, tep_draw_pull_down_menu($this->page_name, $pages_array, $this->current_page_number, 'class="change_submit"'), $this->number_of_pages);
        foreach($param_array as $key => $value ) {
          $form_links_string .= tep_draw_hidden_field($key, $value);
        }
        $form_links_string .= '</form>';
      }

      // previous button - not displayed on first page
      if( $this->current_page_number > 1 ) { 
        if (tep_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';
        $display_links_string .= '<a href="' . tep_href_link($cDefs->script, $parameters . $this->page_name . '=' . ($this->current_page_number - 1)) . '" class="pageResults rpad" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' ">' . PREVNEXT_BUTTON_PREV . '</a>';
      }
      $display_links_string .= $form_links_string;

      // next button
      if( $this->current_page_number < $this->number_of_pages && $this->number_of_pages > 1 ) {
        if (tep_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';
        $display_links_string .= '<a href="' . tep_href_link($cDefs->script, $parameters . $this->page_name . '=' . ($this->current_page_number + 1)) . '" class="pageResults lpad" title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' ">' . PREVNEXT_BUTTON_NEXT . '</a>';
      }
      return $display_links_string;
    }

    // display number of total products found
    function display_count($text_output) {
      $to_num = $this->number_of_rows_per_page * $this->current_page_number;
      if ($to_num > $this->number_of_rows) $to_num = $this->number_of_rows;

      $from_num = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

      if( !$to_num ) {
        $from_num = 0;
      } else {
        $from_num++;
      }
      return sprintf($text_output, $from_num, $to_num, $this->number_of_rows);
    }
  }
?>
