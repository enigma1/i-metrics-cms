<?php
/*
  $Id: backup.php,v 1.60 2003/06/29 22:50:51 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

// Modifications by asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Database Backup main script
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - Compatibility added for PHP 4,5
// - Restructured backup/restore code and moved into a separate class
// - Added zip compression support
// - Added segmented sql files for compression to avoid server dependencies
// - Added the common template support
// - Added filtering for delete operations
// - Added truncate of relevant tables on restore
// - Fix for escape characters to backup/restore database
// - Ported for the I-Metrics CMS
// - Removed configuration entries
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  require('includes/application_top.php');

  require_once(DIR_FS_CLASSES . 'pkzip.php');
  require_once(DIR_FS_CLASSES . 'pkunzip.php');
  require_once(DIR_FS_FUNCTIONS . 'compression.php');

  switch ($action) {
    case 'backupnow':
      extract(tep_load('database_backup'));
      $plain_name = 'db_' . DB_DATABASE . '-' . date('YmdHis');
      $backup_file = $plain_name . '.sql';
      $tables_array = $g_db->get_tables();
      $database_backup->save_tables(DIR_FS_BACKUP . $backup_file, $tables_array);

      if( isset($_POST['compress']) ) {
        $cZip = new pkzip;
        $result = $cZip->splitFile(DIR_FS_BACKUP . $backup_file, DIR_FS_BACKUP . $plain_name . '.zip');
        if( !$result ) {
          $msg->add_session(sprintf(ERROR_INVALID_FILE, $input_file));
        } else {
          @unlink(DIR_FS_BACKUP . $backup_file);
          $backup_file = DIR_FS_BACKUP . $plain_name . '.zip';
        }
      }
      if( isset($_POST['download']) && is_file($backup_file) ) {
        header('Content-type: application/x-octet-stream');
        header('Content-disposition: attachment; filename=' . $backup_file);
        $fp = fopen($backup_file, 'rb');
        while( !feof($fp) ) {
          $buffer = fread($fp, 10485760);
          echo $buffer;
        }
        fclose($fp);
      }
      $messageStack->add_session(SUCCESS_DATABASE_SAVED, 'success');
      tep_redirect(tep_href_link($g_script));
      break;
    case 'restorenow':
      $file = basename($_GET['file']);
      $extension = substr($file, -3);

      if( $extension != 'zip' && $extension != 'sql' ) {
        $messageStack->add_session(ERROR_CANNOT_OPEN_FILE);
        tep_redirect(tep_href_link($g_script));
      }

      if( !is_file(DIR_FS_BACKUP . $file) ) {
        $messageStack->add_session(ERROR_CANNOT_OPEN_FILE);
        tep_redirect(tep_href_link($g_script));
      }

      $files_array = array($file);
      if( $extension == 'zip' ) {
        $result_array = tep_decompress(DIR_FS_BACKUP . $file, DIR_FS_BACKUP);
        if( count($result_array['messages']) ) {
          for($i=0, $j=count($result_array['messages']); $i<$j; $i++) {
            $messageStack->add_session($result_array['messages'][$i]);
          }
          tep_redirect(tep_href_link($g_script));
        } else {
          $files_array = $result_array['files'];
        }

        $file = 'tmp_zip_' . basename($file, '.zip') . '.sql';
        $fp = fopen(DIR_FS_BACKUP . $file, 'w');
        if( !$fp ) {
          $messageStack->add_session(ERROR_CANNOT_CREATE_FILE);
          tep_redirect(tep_href_link($g_script));
        }

        for($i=0, $j=count($files_array); $i<$j; $i++) {
          $contents = '';
          tep_read_contents(DIR_FS_BACKUP . $files_array[$i], $contents);
          fwrite($fp, $contents);
          @unlink(DIR_FS_BACKUP . $files_array[$i]);
        }
        fclose($fp);
        $_GET['file'] = $file;
      }
      break;
    case 'restorelocalnow':
      break;
/*
    case 'restorenow':
    case 'restorelocalnow':
      tep_set_time_limit(0);

      if ($action == 'restorenow') {
        $read_from = $_GET['file'];

        if (file_exists(DIR_FS_BACKUP . $_GET['file'])) {
          $restore_file = DIR_FS_BACKUP . $_GET['file'];
          $extension = substr($_GET['file'], -3);

          if ( ($extension == 'sql') || ($extension == '.gz') || ($extension == 'zip') ) {
            switch ($extension) {
              case 'sql':
                $restore_from = $restore_file;
                $remove_raw = false;
                break;
              case '.gz':
                $restore_from = substr($restore_file, 0, -3);
                exec(LOCAL_EXE_GUNZIP . ' ' . $restore_file . ' -c > ' . $restore_from);
                $remove_raw = true;
                break;
              case 'zip':
                $restore_from = substr($restore_file, 0, -4);
                exec(LOCAL_EXE_UNZIP . ' ' . $restore_file . ' -d ' . DIR_FS_BACKUP);
                $remove_raw = true;
            }

            if (isset($restore_from) && file_exists($restore_from) && (filesize($restore_from) > 15000)) {
              $fd = fopen($restore_from, 'rb');
              $restore_query = fread($fd, filesize($restore_from));
              fclose($fd);
            }
          }
        }
      } elseif ($action == 'restorelocalnow') {
        $sql_file = new upload('sql_file');

        if ($sql_file->parse() == true) {
          $restore_query = fread(fopen($sql_file->tmp_filename, 'r'), filesize($sql_file->tmp_filename));
          $read_from = $sql_file->filename;
        }
      }

      if (isset($restore_query)) {
        $sql_array = array();
        $drop_table_names = array();
        $sql_length = strlen($restore_query);
        $pos = strpos($restore_query, ';');
        for ($i=$pos; $i<$sql_length; $i++) {
          if ($restore_query[0] == '#') {
            $restore_query = ltrim(substr($restore_query, strpos($restore_query, "\n")));
            $sql_length = strlen($restore_query);
            $i = strpos($restore_query, ';')-1;
            continue;
          }
          if ($restore_query[($i+1)] == "\n") {
            for ($j=($i+2); $j<$sql_length; $j++) {
              if (trim($restore_query[$j]) != '') {
                $next = substr($restore_query, $j, 6);
                if ($next[0] == '#') {
      // find out where the break position is so we can remove this line (#comment line)
                  for ($k=$j; $k<$sql_length; $k++) {
                    if ($restore_query[$k] == "\n") break;
                  }
                  $query = substr($restore_query, 0, $i+1);
                  $restore_query = substr($restore_query, $k);
     // join the query before the comment appeared, with the rest of the dump
                  $restore_query = $query . $restore_query;
                  $sql_length = strlen($restore_query);
                  $i = strpos($restore_query, ';')-1;
                  continue 2;
                }
                break;
              }
            }
            if ($next == '') { // get the last insert query
              $next = 'insert';
            }
            if ( (preg_match('/create/i', $next)) || (preg_match('/insert/i', $next)) || (preg_match('/drop t/i', $next)) ) {
              $query = substr($restore_query, 0, $i);

              $next = '';
              $sql_array[] = $query;
              $restore_query = ltrim(substr($restore_query, $i+1));
              $sql_length = strlen($restore_query);
              $i = strpos($restore_query, ';')-1;

              if (preg_match('/^create*\/i', $query)) {
                $table_name = trim(substr($query, stripos($query, 'table ')+6));
                $table_name = substr($table_name, 0, strpos($table_name, ' '));

                $drop_table_names[] = $table_name;
              }
            }
          }
        }

        $g_db->query('drop table if exists ' . implode(', ', $drop_table_names));

        for ($i=0, $n=sizeof($sql_array); $i<$n; $i++) {
          $g_db->query($sql_array[$i]);
        }

        $g_db->query("delete from " . TABLE_WHOS_ONLINE);
        $g_db->query("delete from " . TABLE_SESSIONS);
        $g_db->query("delete from " . TABLE_SESSIONS_ADMIN);

        //$g_db->query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'DB_LAST_RESTORE'");
        //$g_db->query("insert into " . TABLE_CONFIGURATION . " values (null, 'Last Database Restore', 'DB_LAST_RESTORE', '" . $read_from . "', 'Last database restore file', '6', '0', null, now(), '', '')");

        if( isset($remove_raw) && ($remove_raw == true) ) {
          unlink($restore_from);
        }
        $messageStack->add_session(SUCCESS_DATABASE_RESTORED, 'success');
      }

      tep_redirect(tep_href_link(FILENAME_BACKUP));
      break;
*/
    case 'download':
      $file = basename($_GET['file']);
      $extension = substr($file, -3);

      if( !is_file(DIR_FS_BACKUP . $file) ) {
        $messageStack->add_session(ERROR_CANNOT_OPEN_FILE);
        tep_redirect(tep_href_link($g_script));
      }

      if( $extension != 'zip' && $extension != 'sql' ) {
        $messageStack->add_session(ERROR_CANNOT_OPEN_FILE);
        tep_redirect(tep_href_link($g_script));
      }

      header('Content-type: application/x-octet-stream');
      header('Content-disposition: attachment; filename=' . $file);
      $result = tep_read_file(DIR_FS_BACKUP . $file);
      if( !$result ) {
        echo ERROR_CANNOT_OPEN_FILE;
      }
      $g_session->close();
      break;
    case 'deleteconfirm':
      $file = basename($_GET['file']);
      $extension = substr($file, -3);

      if( $extension != 'zip' && $extension != 'sql' ) {
        $messageStack->add_session(ERROR_CANNOT_OPEN_FILE);
        tep_redirect(tep_href_link($g_script));
      }

      if( is_file(DIR_FS_BACKUP . $file) ) {
        $messageStack->add_session(SUCCESS_BACKUP_DELETED, 'success');
        @unlink(DIR_FS_BACKUP . $file);
      } else {
        $messageStack->add_session(ERROR_CANNOT_OPEN_FILE);
      }
      tep_redirect(tep_href_link($g_script));
      break;
    default:
      break;
  }

// check if the backup directory exists
  $backup_directory = false;
  if( !is_dir(DIR_FS_BACKUP) ) {
    $messageStack->add(ERROR_BACKUP_DIRECTORY_DOES_NOT_EXIST, 'error');
  } elseif( !is_writeable(DIR_FS_BACKUP) ) {
    $messageStack->add(ERROR_BACKUP_DIRECTORY_NOT_WRITEABLE, 'error');
  } else {
    $backup_directory = true;
  }
?>
<?php require(DIR_FS_INCLUDES . 'objects/html_start_sub1.php'); ?>
<?php require(DIR_FS_INCLUDES . 'objects/html_start_sub2.php'); ?>
          <div class="maincell">
            <div class="comboHeadingTop">
              <div><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
<?php
  if( $action == 'restorenow' ) {
    $file = basename($_GET['file']);
?>
            <div class="bounder">
              <div class="blockpad dataTableRowYellow"><?php echo TEXT_INFO_RESTORE; ?></div>
            </div>
            <div class="formArea"><fieldset><legend><?php echo sprintf(TEXT_INFO_LABEL_RESTORE, $file); ?></legend>
              <div class="scroller" style="height: 400px;"><?php echo $g_db->file_exec(DIR_FS_BACKUP . $file, true); ?></div>
            </fieldset></div>
<?php
    $pos = strpos($file, 'tmp_zip_');
    if( $pos !== false && !$pos ) {
      @unlink($file);
    }
    $g_plugins->invoke('db_restore');
    $g_db->query("truncate table " . TABLE_SEO_CACHE);
    $g_db->query("truncate table " . TABLE_CACHE_HTML_REPORTS);
    $g_db->query("truncate table " . TABLE_WHOS_ONLINE);
    $g_db->query("truncate table " . TABLE_SESSIONS);
    $g_db->query("truncate table " . TABLE_SESSIONS_ADMIN);
  }
?>
<?php
  if( $backup_directory == true) {
?>
            <div class="bounder">
              <div class="blockpad dataTableRowYellow"><h3><?php echo TEXT_BACKUP_DIRECTORY . ' ' . DIR_FS_BACKUP;; ?></h3></div>
            </div>
            <div class="formArea"><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_TITLE; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_FILE_DATE; ?></th>
                <th class="ralign"><?php echo TABLE_HEADING_FILE_SIZE; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
              </tr>
<?php
    $contents = array_filter(glob(DIR_FS_BACKUP . '*'), 'is_file');
    sort($contents);

    for ($i=0, $rows=count($contents); $i<$rows; $i++) {
      $entry = $contents[$i];

      if( strlen(basename($entry)) < 5 ) continue;

      $extension = substr($entry, -3);
      if( $extension != 'zip' && $extension != 'sql') continue;

      $row_class = ($i%2)?'dataTableRow':'dataTableRowAlt';

      if( (!isset($_GET['file']) || (isset($_GET['file']) && ($_GET['file'] == basename($entry) ))) && !isset($buInfo) && ($action != 'backup') && ($action != 'restorelocal')) {
        $file_array['file'] = basename($entry);
        $file_array['date'] = date(PHP_DATE_TIME_FORMAT, filemtime($entry));
        $file_array['size'] = number_format(filesize($entry)) . ' bytes';
        $file_array['base'] = basename($entry);
        $buInfo = new objectInfo($file_array);
      }

      if (isset($buInfo) && is_object($buInfo) && ( basename($entry) == $buInfo->file)) {
        $onclick_link = 'file=' . $buInfo->base . '&action=restore';
        echo '              <tr class="dataTableRowSelected row_link" href="' . tep_href_link($g_script, $onclick_link) . '">' . "\n";
      } else {
        $onclick_link = 'file=' . basename($entry);
        echo '              <tr class="' . $row_class . ' row_link" href="' . tep_href_link($g_script, $onclick_link) . '">' . "\n";
      }
?>
                <td class="heavy"><?php echo basename($entry); ?></td>
                <td class="calign"><?php echo date(PHP_DATE_TIME_FORMAT, filemtime($entry)); ?></td>
                <td class="ralign"><?php echo number_format(filesize($entry)); ?> bytes</td>
                <td class="tinysep calign">
<?php 
      echo '<a href="' . tep_href_link($g_script, 'action=download&file=' . basename($entry)) . '">' . tep_image(DIR_WS_ICONS . 'icon_download.png', ICON_FILE_DOWNLOAD) . '</a>';
      if (isset($buInfo) && is_object($buInfo) && ($entry == $buInfo->file)) {
        echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', '');
      } else { 
        echo '<a href="' . tep_href_link($g_script, 'file=' . basename($entry)) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>';
      } 
?>
                </td>
              </tr>
<?php
    }
    $buttons = array();
    if( $action != 'backup' ) {
      $buttons[] = '<a href="' . tep_href_link($g_script, 'action=backup') . '">' . tep_image_button('button_backup.gif', IMAGE_BACKUP) . '</a>';
    }
    //if( $action != 'restorelocal') {
    //  $buttons[] = '<a href="' . tep_href_link($g_script, 'action=restorelocal') . '">' . tep_image_button('button_restore.gif', IMAGE_RESTORE) . '</a>';
    //}
?>
            </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></div>
            <div class="listArea splitLine">
              <div class="floater"><?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, min(1, $rows), $rows, $rows); ?></div>
            </div>
<?php
  } else {
?>

<?php
  }
?>
          </div>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'backup':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_BACKUP . '</b>');

      $contents[] = array('form' => tep_draw_form('backup', $g_script, 'action=backupnow'));
      $contents[] = array('text' => TEXT_INFO_NEW_BACKUP);
      $contents[] = array('text' => tep_draw_checkbox_field('compress', 'on', true) . ' ' . TEXT_INFO_USE_COMPRESSION);
      $contents[] = array('text' => tep_draw_checkbox_field('download', 'on') . ' ' . TEXT_INFO_DOWNLOAD_ONLY . '*<br /><br />*' . TEXT_INFO_BEST_THROUGH_HTTPS);
      $contents[] = array('class' => 'calign', 'text' => '<br />' . tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '<a href="' . tep_href_link($g_script) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'restore':
      $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

      //$contents[] = array('text' => tep_break_string(sprintf(TEXT_INFO_RESTORE, DIR_FS_BACKUP . (($buInfo->compression != TEXT_NO_EXTENSION) ? substr($buInfo->file, 0, strrpos($buInfo->file, '.')) : $buInfo->file), ($buInfo->compression != TEXT_NO_EXTENSION) ? TEXT_INFO_UNPACK : ''), 35, ' '));
      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL . '<br /><br />' . TEXT_INFO_BEST_THROUGH_HTTPS);
      $contents[] = array('class' => 'calign', 'text' => '<br /><a href="' . tep_href_link($g_script, 'file=' . $buInfo->file . '&action=restorenow') . '">' . tep_image_button('button_confirm.gif', IMAGE_CONFIRM) . '</a><a href="' . tep_href_link($g_script, 'file=' . $buInfo->file) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'restorelocal':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_RESTORE_LOCAL . '</b>');

      $contents[] = array('form' => tep_draw_form('restore', $g_script, 'action=restorelocalnow', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL . '<br /><br />' . TEXT_INFO_BEST_THROUGH_HTTPS);
      $contents[] = array('text' => '<br />' . tep_draw_file_field('sql_file'));
      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL_RAW_FILE);
      $contents[] = array('class' => 'calign', 'text' => tep_image_submit('button_restore.gif', IMAGE_RESTORE) . '<a href="' . tep_href_link($g_script) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

      $contents[] = array('form' => tep_draw_form('delete', $g_script, 'file=' . $buInfo->file . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br /><b>' . $buInfo->file . '</b>');
      $contents[] = array('class' => 'calign', 'text' => '<br />' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link($g_script, 'file=' . $buInfo->file) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($buInfo) && is_object($buInfo)) {
        $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

        $contents[] = array('class' => 'calign', 'text' => '<a href="' . tep_href_link($g_script, 'file=' . $buInfo->file . '&action=restore') . '">' . tep_image_button('button_restore.gif', IMAGE_RESTORE) . '</a><a href="' . tep_href_link($g_script, 'file=' . $buInfo->file . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE . ' ' . $buInfo->date);
        $contents[] = array('text' => TEXT_INFO_SIZE . ' ' . $buInfo->size);
      } else { // create generic_text dummy info
        $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', EMPTY_GENERIC) );
        $contents[] = array('text' => TEXT_INFO_NO_BACKUP);
      }
      break;
  }

  if( !empty($heading) && !empty($contents) ) {
    echo '             <div class="rightcell">';
    $box = new box;
    echo $box->infoBox($heading, $contents);
    echo '             </div>';
  }
?>
<?php require(DIR_FS_INCLUDES . 'objects/html_end.php'); ?>
