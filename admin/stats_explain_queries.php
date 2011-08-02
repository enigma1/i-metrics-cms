<?php
/*-----------------------------------------------------------------------------*\
#################################################################################
#   Script name: admin/stats_explain_queries.php
#   Version: v1.0
#
#   Copyright (C) 2005 Bobby Easland
#   Internet moniker: Chemo
#   Contact: chemo@mesoimpact.com
#
#   This script is free software; you can redistribute it and/or
#   modify it under the terms of the GNU General Public License
#   as published by the Free Software Foundation; either version 2
#   of the License, or (at your option) any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   Script is intended to be used with:
#   osCommerce, Open Source E-Commerce Solutions
#   http://www.oscommerce.com
#   Copyright (c) 2003 osCommerce
#
#   Version: 1.1
#   Modified by Mark Samios
#   - Modified explain_queries table columns to fix problem with dbase backup
#
################################################################################
\*-----------------------------------------------------------------------------*/

/*
// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Explain Queries Debug Script
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - Ported for I-Metrics CMS
// - Changed layout to use a single table
// - Using the core CSV functions
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/

  require('includes/application_top.php');

  $saction = isset($_GET['saction'])?$g_db->prepare_input($_GET['saction']):'';
  $selected_script = isset($_POST['selected_script'])?$g_db->prepare_input($_POST['selected_script']):'';

  $queries_limit = (isset($_POST['queries_limit']) && $_POST['queries_limit']>0)?(int)$_POST['queries_limit']:400;

  $queries_offset = isset($_POST['queries_offset'])?(int)$_POST['queries_offset']:0;
  $queries_script = isset($_POST['queries_script'])?$g_db->prepare_input($_POST['queries_script']):'';

  $select_string = "select explain_md5query, explain_query, explain_time, explain_script, explain_request_string, explain_table, explain_type, explain_possible_keys, explain_key, explain_key_len, explain_ref, explain_rows, explain_extra, explain_comment, avg(explain_time) as average, count(explain_md5query) as num_records, min(explain_time) as min, max(explain_time) as max";
  switch($saction) {
    case 'script':
      $explain_query_raw = $select_string . " from " . TABLE_EXPLAIN_QUERIES . " where explain_script='" . $g_db->input($queries_script) . "' group by explain_md5query order by average desc limit " . (int)$queries_offset . ", " . (int)$queries_limit;
      $explain_array = $g_db->query_to_array($explain_query_raw);
      break;
    case 'query':
      $explain_query_raw = $select_string . " from " . TABLE_EXPLAIN_QUERIES . " where explain_md5query='" . $g_db->input($queries_query) . "' group by explain_script order by average desc limit " . (int)$queries_offset . ", " . (int)$queries_limit;
      $explain_array = $g_db->query_to_array($explain_query_raw);
      break;
    default: 
      $saction = 'query';
      $explain_array = array();
      break;
  }

  switch($action) {
    case 'truncate':
      $g_db->query("truncate table " . TABLE_EXPLAIN_QUERIES);
      tep_redirect(tep_href_link($g_script));
      break;
    case 'analyze':
      $g_db->query("analyze table " . TABLE_EXPLAIN_QUERIES);
      break;
    case 'export':
      if( empty($_GET['qs']) ) {
        $messageStack->add_session(ERROR_INVALID_SCRIPT);
        tep_redirect(tep_href_link($g_script));
      }

      $header_cols = array(
        'explain_time',
        'explain_table',
        'explain_request_string',
        'explain_query',
        'explain_rows',
        'explain_type',
        'explain_possible_keys',
        'explain_key',
        'explain_key_len',
        'explain_ref',
        'explain_extra',
        'explain_comment',
      );

      $select_string = "select " . implode(',',$header_cols);
      $explain_query_raw = $select_string . " from " . TABLE_EXPLAIN_QUERIES . " where explain_script='" . $g_db->filter($_GET['qs']) . "' order by explain_time desc";
      $explain_array = $g_db->query_to_array($explain_query_raw);

      if( empty($explain_array) ) {
        $messageStack->add_session(ERROR_EMPTY_QUERY);
        tep_redirect(tep_href_link($g_script));
      }

      require(DIR_FS_CLASSES . 'csv_core.php');
      $csv = new csv_core(',', '"', "\r\n");
      $csv->reset_buffer();

      $csv->write_header($header_cols);
      //$header_cols = array_flip($header_cols);
      for($i=0, $j=count($explain_array); $i<$j; $i++) {
        //$explain_array[$i] = array_intersect_assoc($header_cols, $explain_array[$i]);
        $csv->write_data($explain_array[$i]);
        $csv->insert_line(1);
      }

      $filename = $g_db->prepare_input(tep_get_script_name($_GET['qs']), true) . '-' .  gmdate("m-d-y");
      $csv->output($filename);
      $g_session->close();
      exit();
      break;
    default:
      break;
  }
  header("Cache-Control: public");
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
            <div><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div class="dataTableRowAlt3 spacer floater"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=truncate') . '" class="blockbox">' . TEXT_INFO_BUTTON_TRUNCATE . '</a>'; ?></div>
            <div class="dataTableRowAlt4 spacer floater"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=analyze') . '" class="blockbox">' . TEXT_INFO_BUTTON_ANALYZE . '</a>'; ?></div>
            <div class="dataTableRow spacer floater"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'qs', 'qo', 'ql') . 'action=export&qs=' . $queries_script . '&qo=' . $queries_offset . '&ql=' . $queries_limit) . '" class="blockbox">' . TEXT_INFO_BUTTON_EXPORT . '</a>'; ?></div>
          </div>
<?php

  // Query for total scripts, number of unique queries, and total queries stored for the script
  $pages_query_raw = "select explain_script as id, concat(explain_script, '(', count(distinct explain_md5query), '/', count(*), ')') as text, count(distinct explain_md5query) as count, count(*) as total from " . TABLE_EXPLAIN_QUERIES . " group by explain_script";
  $pages_array = $g_db->query_to_array($pages_query_raw);
  if( count($pages_array) ) {
?>
          <div class="formArea"><?php echo tep_draw_form('expkain_queries', $g_script, tep_get_all_get_params('action', 'saction') . 'saction=script'); ?><fieldset><legend><?php echo HEADING_TEXT_SELECT_SCRIPT; ?></legend>
            <div class="bounder infile vmargin">
              <label class="floater" for="queries_script"><?php echo TEXT_INFO_SCRIPT; ?></label><div class="floater rspacer"><?php echo tep_draw_pull_down_menu('queries_script', $pages_array, $selected_script, 'id="queries_script"'); ?></div>
              <label class="floater" for="queries_limit"><?php echo TEXT_INFO_LIMIT; ?></label><div class="floater rspacer"><?php echo tep_draw_input_field('queries_limit', '', 'size="3" maxlength="3" id="queries_limit"'); ?></div>
              <label class="floater" for="queries_offset"><?php echo TEXT_INFO_OFFSET; ?></label><div class="floater rspacer"><?php echo tep_draw_input_field('queries_offset', '', 'size="3" maxlength="3" id="queries_offset"'); ?></div>
            </div>
            <div class="formButtons"><?php echo tep_image_submit('button_submit.gif', IMAGE_CONFIRM); ?></div>
          </fieldset></form></div>
<?php
  }
  $j = count($explain_array);
  if($j) {
?>
          <div class="formArea"><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th class="calign"><?php echo TABLE_HEADING_TIME; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ROWS; ?></th>
              <th class="halfer"><?php echo TABLE_HEADING_QUERY; ?></th>
              <th><?php echo TABLE_HEADING_REQUEST; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_TYPE; ?></th>
              <th><?php echo TABLE_HEADING_KEY_USED; ?></th>
              <th><?php echo TABLE_HEADING_KEY_LENGTH; ?></th>
              <th><?php echo TABLE_HEADING_REF; ?></th>
              <th><?php echo TABLE_HEADING_EXTRA; ?></th>
              <th><?php echo TABLE_HEADING_COMMENT; ?></th>
            </tr>
<?php
    $time_total = array(
      'min' => 0, 
      'average' => 0, 
      'max' => 0
    );

    $total_rows = $unique_rows = 0;

    for($i=0; $i<$j; $i++) {
      $data = $explain_array[$i];
      $time_total['min'] += $data['min'];
      $time_total['average'] += $data['average'];
      $time_total['max'] += $data['max'];
      $row_class = $i%2?'dataTableRow':'dataTableRowAlt';
      if( $data['min'] != $data['max'] ) {
        $row_class = 'dataTableRowAlt4';
      }
      $unique_rows += $data['explain_rows'];
      $total_rows +=  $data['num_records'];

      echo '              <tr class="' . $row_class . '">' . "\n";
?>
              <td class="ralign">
<?php
      if( $data['min'] != $data['max'] ) {
        echo '<span class="successText heavy">' . number_format($data['min'], 3) .'</span><br/><b>'.number_format($data['average'], 3).'</b><br /><span class="errorText heavy">'.number_format($data['max'], 3) . '</span>'; 
      } else {
        echo number_format($data['min'], 3) .'<br/><b>'.number_format($data['average'], 3).'</b><br />'.number_format($data['max'], 3);
      }
?>
              </td>
              <td class="transtwenties calign"><?php echo $data['explain_rows'] . '<br />' . $data['num_records']; ?></td>
              <td><div class="rpad"><?php echo '<label for="ls' . $i . '">' . $data['explain_table'] . '</label><br />' . tep_draw_textarea_field('q' . $i, $data['explain_query'], 50, '', 'class="wider" id="ls' . $i . '"'); ?></div></td>
              <td class="transtwenties">
<?php 
      echo $data['explain_script'];
      if( !empty($data['explain_request_string']) ) {
        echo '?' . $data['explain_request_string'];
      }
?>
              </td>
              <td class="calign"><?php echo $data['explain_type']; ?></td>
              <td class="transtwenties"><?php echo $data['explain_possible_keys'] . ' / ' . $data['explain_key']; ?></td>
              <td><?php echo $data['explain_key_len']; ?></td>
              <td><?php echo $data['explain_ref']; ?></td>
              <td><?php echo $data['explain_extra']; ?></td>
              <td><?php echo $data['explain_comment']; ?></td>
            </tr>
<?php
    }
    $total_time = number_format($time_total['min'], 3) .' - <b>'.number_format($time_total['average'], 3).'</b> - '.number_format($time_total['max'], 3);
?>
          </table></div>
          <div class="listArea splitLine">
            <div class="floater"><?php echo sprintf(TEXT_INFO_TIME_TOTAL, $total_time); ?></div>
            <div class="floatend"><?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, min(1, $i), $i, $total_rows); ?></div>
          </div>
<?php
  }
?>
        </div>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>