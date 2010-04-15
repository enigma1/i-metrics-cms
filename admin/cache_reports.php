<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Cache HTML Reports
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
  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  switch($action) {
    case 'truncate_html':
      $g_db->query("truncate table " . TABLE_CACHE_HTML_REPORTS . "");
      tep_redirect(tep_href_link(FILENAME_CACHE_REPORTS, tep_get_all_get_params(array('action')) ));
      break;
    default:
      break;
  }

  $modes_array = array(
                        array('id' => '1', 'text' => 'Cache'),
                        array('id' => '2', 'text' => 'Flush'),
                        array('id' => '3', 'text' => 'Parametric')
                      );
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php require('includes/objects/html_start_sub2.php'); ?>
        <div class="maincell" style="width: 100%;">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
<?php
  if( $action == 'report_mysql' ) {
?>
<?php
  } else {
// Get Scripts info from the database
    $rows = 0;
    $cache_html_query_raw = "select cr.*, c.cache_html_type from " . TABLE_CACHE_HTML_REPORTS . " cr left join " . TABLE_CACHE_HTML . " c on (c.cache_html_key = cr.cache_html_key) order by cr.cache_html_script";
    $cache_html_split = new splitPageResults($cache_html_query_raw, MAX_DISPLAY_HTML_CACHE_SCRIPTS, '', 'cr.cache_html_key');
    if( $cache_html_split->number_of_rows > 0 ) {
?>
          <div class="listArea"><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_FILENAME; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_HITS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_MISSES; ?></th>
              <th class="ralign"><?php echo TABLE_HEADING_EFFICIENCY; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_SPIDER_HITS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_SPIDER_MISSES; ?></th>
              <th class="ralign"><?php echo TABLE_HEADING_SPIDER_EFFICIENCY; ?></th>
            </tr>
<?php
      $cache_html_query = $g_db->query($cache_html_split->sql_query);
      $bCheck = false;
      while ($cache_html = $g_db->fetch_array($cache_html_query)) {
        $rows++;
        if( $cache_html['cache_html_type'] == 3 ) {
          $row_class = 'dataTableRowHigh';
        } elseif($cache_html['cache_html_type'] == 2) {
          $row_class = 'dataTableRowImpact';
        } else {
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowSelected';
        }
        echo '                      <tr class="' . $row_class . '">';
?>
              <td><?php echo $cache_html['cache_html_script']; ?></td>
              <td class="calign"><?php echo $cache_html['cache_hits']; ?></td>
              <td class="calign"><?php echo $cache_html['cache_misses']; ?></td>
              <td class="ralign">
<?php 
        $total_access = $cache_html['cache_misses']+$cache_html['cache_hits'];

        if( $total_access <= 0 )
          $total_access = 1;

        $efficiency = tep_round( ($cache_html['cache_hits']*100)/$total_access, 2);
        echo $efficiency . '%';
?>
              </td>
              <td class="calign"><?php echo $cache_html['cache_spider_hits']; ?></td>
              <td class="calign"><?php echo $cache_html['cache_spider_misses']; ?></td>
              <td class="ralign">
<?php 
        $total_access = $cache_html['cache_spider_misses']+$cache_html['cache_spider_hits'];
        if( $total_access <= 0 )
          $total_access = 1;

        $efficiency = tep_round( ($cache_html['cache_spider_hits']*100)/$total_access, 2);
        echo $efficiency . '%';
?>
              </td>
            </tr>
<?php
      }
?>
          </table></div>
          <div class="formButtons"><?php echo '<a href="' . tep_href_link(FILENAME_CACHE_REPORTS, tep_get_all_get_params(array('action')) . 'action=truncate_html' ) . '">' . tep_image_button('button_delete.gif', 'Truncate Cache HTML Reports') . '</a>'; ?></div>
          <div class="splitLine">
            <div style="float: left;"><?php echo $cache_html_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $cache_html_split->display_links(tep_get_all_get_params(array('action', 'page'))); ?></div>
          </div>
<?php 
    }
  }
?>
        </div>
<?php require('includes/objects/html_end.php'); ?>
