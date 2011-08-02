<?php
/*
  $Id: server_info.php,v 1.6 2003/06/30 13:13:49 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2007-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Server Information script
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - PHP5 Register Globals off and Long Arrays Off support added
// - HTML Outer tables replaced with CSS driven divs
// - Added common HTML sections
// - Added SQL Info
// - Added PHP Extensions and PHP functions
// - Added parameters for info selection
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  require('includes/application_top.php');
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  $info = isset($_GET['info'])?$g_db->prepare_input($_GET['info']):'';
?>
          <div class="maincell wider">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
              <div><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
            <div class="comboHeading">
              <div class="dataTableRowAlt spacer floater"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('info') . 'info=php_server') . '" class="blockbox">' . TEXT_INFO_PHP_SERVER . '</a>'; ?></div>
              <div class="dataTableRowAlt4 spacer floater"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('info') . 'info=sql_server') . '" class="blockbox">' . TEXT_INFO_SQL_SERVER . '</a>'; ?></div>
              <div class="dataTableRowAlt3 spacer floater"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('info') . 'info=php_extensions') . '" class="blockbox">' . TEXT_INFO_PHP_EXTENSIONS . '</a>'; ?></div>
            </div>
<?php
  if($info == 'sql_server') {
?>
            <div class="listArea"></div>
            <div class="comboHeading dataTableRowGreenLite"><h2><?php echo HEADING_SQL_INFO; ?></h2></div>
            <div class="formArea"><fieldset><legend><?php echo TEXT_INFO_SQL_PROCESSES; ?></legend><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TEXT_INFO_ID; ?></th>
                <th><?php echo TEXT_INFO_HOST; ?></th>
                <th><?php echo TEXT_INFO_DB; ?></th>
                <th><?php echo TEXT_INFO_CMD; ?></th>
                <th><?php echo TEXT_INFO_TIME; ?></th>
              </tr>
<?php
    $processes_query = $g_db->processes();
    $rows = 0;
    while( $process = $g_db->fetch_array($processes_query) ) {
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
      echo '                  <tr class="' . $row_class . '">' . "\n";
?>
                <td><?php echo $process['Id']; ?></td>
                <td><?php echo $process['Host']; ?></td>
                <td><?php echo $process['db']; ?></td>
                <td><?php echo $process['Command']; ?></td>
                <td><?php echo $process['Time']; ?></td>
              </tr>
<?php
    }
?>
            </table></fieldset></div>
            <div class="formArea"><fieldset><legend><?php echo TEXT_INFO_SQL_STATS; ?></legend><table class="tabledata">
<?php
    $sql_array = $g_db->query_to_array("show status", false, false);
    $headings_array = array();
    for($i=0, $j=count($sql_array); $i<$j; $i++) {
      $row_class = ($i%2)?'dataTableRow':'dataTableRowAlt';
      if( !$i ) {
        echo '                  <tr class="dataTableHeadingRow">' . "\n";
        $headings_array = array_keys($sql_array[$i]);
        for($i2=0, $j2=count($headings_array); $i2<$j2; $i2++) {
          echo '                    <th>' . $headings_array[$i2] . '</th>' . "\n";
        }
        echo '                  </tr>' . "\n";
      }
      echo '                  <tr class="' . $row_class . '">' . "\n";
      for($i2=0, $j2=count($headings_array); $i2<$j2; $i2++) {
        if( !$i2 ) {
          echo '                   <td class="transtwenties heavy">';
        } else {
          echo '                   <td>';
        }
        echo $sql_array[$i][$headings_array[$i2]] . '</td>' . "\n";
      }
      echo '                  </tr>' . "\n";
    }
?>
            </table></fieldset></div>
            <div class="formArea"><fieldset><legend><?php echo TEXT_INFO_SQL_VARS; ?></legend><table class="tabledata">
<?php
    $sql_array = $g_db->query_to_array("show variables", false, false);
    $headings_array = array();
    for($i=0, $j=count($sql_array); $i<$j; $i++) {
      $row_class = ($i%2)?'dataTableRow':'dataTableRowAlt';
      if( !$i ) {
        echo '                  <tr class="dataTableHeadingRow">' . "\n";
        $headings_array = array_keys($sql_array[$i]);
        for($i2=0, $j2=count($headings_array); $i2<$j2; $i2++) {
          echo '                    <th>' . $headings_array[$i2] . '</th>' . "\n";
        }
        echo '                  </tr>' . "\n";
      }
      echo '                  <tr class="' . $row_class . '">' . "\n";
      for($i2=0, $j2=count($headings_array); $i2<$j2; $i2++) {
        if( !$i2 ) {
          echo '                   <td class="transtwenties heavy">';
        } else {
          echo '                   <td>';
        }
        echo $sql_array[$i][$headings_array[$i2]] . '</td>' . "\n";
      }
      echo '                  </tr>' . "\n";
    }
?>
            </table></fieldset></div>
<?php
  } elseif($info == 'php_extensions') {
?>
            <div class="listArea"></div>
            <div class="comboHeading dataTableRowGreenLite"><h2><?php echo HEADING_PHP_EXT; ?></h2></div>
<?php
    $extensions_array = get_loaded_extensions();
    $cells = 3;
    $php_base_link = 'http://www.php.net/manual/en/function.';
    foreach($extensions_array as $extension) {
      $funcs_array = get_extension_funcs($extension);
      if( empty($funcs_array) ) continue;
?>
            <div class="formArea"><fieldset><legend><?php echo $extension . ' [' . count($funcs_array) . ']'; ?></legend><table class="tabledata">
<?php
      sort($funcs_array);
      $rows = $index = 0;
      foreach($funcs_array as $function) {
        if( !($index%$cells) ) {
          $row_class = ($rows%2)?'dataTableRowBlueLite':'dataTableRowAlt';
          echo '<tr class="' . $row_class . '">';
        }
        echo '<td><a class="heavy blocker" href="' . ($php_base_link . str_replace('_', '-', $function) . '.php') . '" title="' . sprintf(TEXT_INFO_PHP_FUNCTION, $function) . '" target="_blank">' . $function . '</a></td>';
        if( !(($index+1)%$cells) ) {
          echo '</tr>';
          $rows++;
        }
        $index++;
      }
      if( $index%$cells ) {
        while( $index%$cells ) {
          echo '<td></td>';
          $index++;
        }
        echo '</tr>';
      }
?>
            </table></fieldset></div>
<?php
    }
  } else {
    $system = tep_get_system_information();
?>
            <div class="comboHeading dataTableRowGreenLite"><h2><?php echo HEADING_PHP_INFO; ?></h2></div>
            <div class="formArea"><table class="tabledata">
              <tr class="dataTableRow">
                <td class="heavy"><?php echo TITLE_SERVER_HOST; ?></td>
                <td><?php echo $system['host'] . ' (' . $system['ip'] . ')'; ?></td>
                <td class="heavy"><?php echo TITLE_DATABASE_HOST; ?></td>
                <td><?php echo $system['db_server'] . ' (' . $system['db_ip'] . ')'; ?></td>
              </tr>
              <tr class="dataTableRowAlt">
                <td class="heavy"><?php echo TITLE_SERVER_OS; ?></td>
                <td><?php echo $system['system'] . ' ' . $system['kernel']; ?></td>
                <td class="heavy"><?php echo TITLE_DATABASE; ?></td>
                <td><?php echo $system['db_version']; ?></td>
              </tr>
              <tr class="dataTableRow">
                <td><b><?php echo TITLE_SERVER_DATE; ?></b></td>
                <td><?php echo $system['date']; ?></td>
                <td class="heavy"><?php echo TITLE_DATABASE_DATE; ?></td>
                <td><?php echo $system['db_date']; ?></td>
              </tr>
            </table></div>
            <div class="formArea"><table class="tabledata">
              <tr class="dataTableRowAlt">
                <td class="heavy"><?php echo TITLE_SERVER_UP_TIME; ?></td>
                <td><?php echo $system['uptime']; ?></td>
              </tr>
              <tr class="dataTableRow">
                <td class="heavy"><?php echo TITLE_HTTP_SERVER; ?></td>
                <td><?php echo $system['http_server']; ?></td>
              </tr>
              <tr class="dataTableRowAlt">
                <td class="heavy"><?php echo TITLE_PHP_VERSION; ?></td>
                <td colspan="3"><?php echo $system['php'] . ' (' . TITLE_ZEND_VERSION . ' ' . $system['zend'] . ')'; ?></td>
              </tr>
            </table></div>
            <div class="listArea" style="padding: 8px 10px 0px 10px;">
<?php
    ob_start();
    phpinfo();
    preg_match ('%<style type="text/css">(.*?)</style>.*?<body>(.*?)</body>%s', ob_get_clean(), $matches);
    // $matches [1]; Style information
    // $matches [2]; Body information
    echo 
      '<div class="phpinfodisplay" style="font-size: 16px;"><style type="text/css">' . "\n",
      implode("\n", array_map(
        create_function('$i', 'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );'),
        preg_split( '/\n/', $matches[1])
      )),
      "</style>\n",
      $matches[2],
      "\n</div>\n";
?>
            </div>
<?php
  }
?>
          </div>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
