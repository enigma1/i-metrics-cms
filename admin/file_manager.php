<?php
/*
  $Id: file_manager.php,v 1.42 2003/06/29 22:50:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: File Manager
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - Rewritten file processing to improve security
// - Added common sections
// - Fixed File Editor Bugs
// - Use of the CMS additional functions
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  require('includes/application_top.php');

  $current_path =& $g_session->register('current_path');
  $current_path = isset($_GET['goto'])?$g_db->prepare_input($_GET['goto']):$current_path;

  $current_file = isset($_GET['info'])?$g_db->prepare_input(basename($_GET['info'])):'';

  $cleanup_array = array('/\\\\/', '/\/{2,}/');
  $filter = "/[^0-9a-z\-_\/\.]+/i";
  $current_path = rtrim(preg_replace($cleanup_array, '/', $current_path), '/');
  if( !empty($current_path) ) {
    $current_path .= '/';
  }

  if( !empty($current_path) ) {
    $pos = strpos($current_path, DIR_FS_CATALOG);

    if( strstr($current_path, '..') || !is_dir($current_path) || $pos != 0 ) {
      $messageStack->add(sprintf(ERROR_INVALID_PATH_NAME, $org_path));
      $current_path = DIR_FS_CATALOG;
    }
  } else {
    $current_path = DIR_FS_CATALOG;
  }

  $current_path_file = $current_path;

  if( strstr($current_file, '..') ) {
    $current_file = '';
  }
  $current_path_file .= $current_file;

  $file_writeable = true;
  $directory_writeable = true;

  if( !is_writeable($current_path) ) {
    $file_writeable = false;
    $directory_writeable = false;
    $messageStack->add(sprintf(ERROR_FILE_NOT_WRITEABLE, $current_path));
  }

  if( !empty($current_file) && $directory_writeable && file_exists($current_path_file) ) {
    if( !is_writable($current_path_file) ) {
      $file_writeable = false;
      $messageStack->add(sprintf(ERROR_FILE_NOT_WRITEABLE, $current_path_file));
    }
  }

  switch ($action) {
    case 'reset':
      $g_session->unregister('current_path');
      tep_redirect(tep_href_link($g_script));
      break;
    case 'deleteconfirm':
      if( empty($current_file) ) {
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'info') ));
      }
      @unlink($current_file);
      $messageStack->add_session(sprintf(WARNING_FILE_REMOVED, $current_path_file), 'warning');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'info') ));
      break;
    case 'insert':
      $folder = $g_db->prepare_input($_POST['folder_name']);
      $new_file_path = $current_path . $folder;
      if( is_dir($current_file_path) ) {
        $messageStack->add_session(sprintf(ERROR_CREATE_DIR_EXISTS, $current_file_path));
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'info', 'goto') . 'goto=' . $new_file_path));
      }
      $result = tep_mkdir($sub_dir);
      if( !$result ) {
        $messageStack->add_session(sprintf(ERROR_CREATE_DIR, $current_file_path));
      } else {
        $messageStack->add_session(sprintf(SUCCESS_DIR_CREATED, $new_file_path), 'success');
      }
      break;
    case 'save':
      if( empty($current_file) ) {
        $messageStack->add_session(ERROR_FILE_INVALID);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }

      $result = tep_write_contents($current_path_file, $g_db->prepare_input($_POST['file_contents']));
      if( !$result ) {
        $messageStack->add_session(WARNING_FILE_LENGTH, 'warning');
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    case 'processuploads':
      for ($i=1; $i<6; $i++) {
        if (isset($GLOBALS['file_' . $i]) && tep_not_null($GLOBALS['file_' . $i])) {
          new upload('file_' . $i, $current_path);
        }
      }

      tep_redirect(tep_href_link($g_script));
      break;
    case 'download':
      $filename = tep_create_safe_string(basename($_GET['filename']), '', "/[^0-9a-z_\-\.]+/i");
      if( !empty($filename) && is_file($current_path . '/' . $filename) ) {
        header('Content-type: application/x-octet-stream');
        header('Content-disposition: attachment; filename=' . $filename);
        readfile($current_path . '/' . $filename);
      }
      $g_session->close();
      break;
    case 'upload':
    case 'new_folder':
    case 'new_file':
      break;
    case 'edit':
      break;
    case 'delete':
      break;
  }

  $goto_array = array();

  $dir_array = array_filter(glob($current_path . '*'), 'is_dir');
  foreach($dir_array as $key => $value ) {
    $goto_array[] = array(
      'id' => $key,
      'text' => basename($value),
    );
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  $plugin = $g_plugins->get();
  $tmp_path = $path_string = rtrim(DIR_FS_CATALOG, '/');
  $path_array = explode('/', rtrim($current_path, '/'));
  $path_array = array_slice($path_array, count(explode('/',$path_string)));
  $path_string = '<a href="' . tep_href_link($g_script, 'goto=' . $tmp_path) . '" style="color: #EE0000;">' . $tmp_path . '</a>/';
  for($i=0, $j=count($path_array); $i<$j; $i++) {
    $tmp_path .= '/' . $path_array[$i];
    $path_string .= '<a href="' . tep_href_link($g_script, 'goto=' . $tmp_path) . '" style="color: #000099;">' . $path_array[$i] . '</a>';
    $path_string .= '/';
  }
?>
<?php
  if( ($action == 'new_file' && $directory_writeable == true) || ($action == 'edit') ) {
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
            <div class="floater"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div><h2><?php echo TEXT_INFO_IN_FOLDER . '&nbsp;' . $path_string; ?></h2></div>
          </div>
<?php
    $file_contents = '';
    if ($action == 'new_file') {
      $filename_input_field = TEXT_INFO_NEW_FILE;
    } elseif ($action == 'edit') {
      tep_read_contents($current_path_file, $file_contents);
      $filename_input_field = TEXT_INFO_EDIT_FILE . '&nbsp;<span class="required">' . $current_file . '</span>';
    }
?>
          <div class="formArea hideover"><?php echo tep_draw_form('new_file', $g_script, tep_get_all_get_params('action') . 'action=save'); ?><fieldset><legend><?php echo $filename_input_field; ?></legend>
<?php
    if( $action == 'new_file') {
?>
            <label for="file_title"><?php echo TEXT_INFO_FILENAME; ?></label>
            <div class="bspacer"><?php echo tep_draw_input_field('filename', '', 'id="file_title"'); ?></div>
<?php
    }
?>
            <label for="file_contents"><?php echo TEXT_FILE_CONTENTS; ?></label>
            <div class="rpad"><?php echo tep_draw_textarea_field('file_contents', $file_contents, '', '50', 'id="file_contents" class="codeFont"' . (($file_writeable) ? '' : ' readonly'), false); ?></div>
            <div class="formButtons">
<?php 
    if ($file_writeable == true) {
      echo tep_image_submit('button_save.gif', IMAGE_SAVE); 
    }
    echo '<a href="' . tep_href_link($g_script, (isset($_GET['info']) ? 'info=' . $_GET['info'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; 
?>
            </div>
          </fieldset></form></div>
        </div>
<?php
  } else {
?>
        <div class="maincell">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
            <div class="floater"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div><h2><?php echo TEXT_INFO_BROWSING . '&nbsp;' . $path_string; ?></h2></div>
          </div>
<?php
    $user = $group = array('name' => '');
    if( function_exists('posix_getpwuid') ) {
      $os = 0;
    } else {
      $os = 1;
    }

    $contents = array();
    $dir = dir($current_path);
    while ($file = $dir->read()) {
      if( ($file != '.') && ($file != 'CVS') && ( ($file != '..') || ($current_path != DIR_FS_CATALOG) ) ) {
        $path_file = $current_path . $file;
        $file_size = number_format(filesize($path_file)) . ' bytes';

        $permissions = $plugin->get_file_permissions_string(fileperms($path_file));
        if(!$os) {
          $user = @posix_getpwuid(fileowner($path_file));
          $group = @posix_getgrgid(filegroup($path_file));
        } else {
          $user['name'] = getenv('USERNAME');
        }

        $contents[] = array(
          'name' => $file,
          'is_dir' => is_dir($path_file),
          'last_modified' => strftime(DATE_TIME_FORMAT, filemtime($path_file)),
          'size' => $file_size,
          'permissions' => $permissions,
          'user' => $user['name'],
          'group' => $group['name']
        );
      }
    }

    function tep_cmp($a, $b) {
      return strcmp( ($a['is_dir'] ? 'D' : 'F') . $a['name'], ($b['is_dir'] ? 'D' : 'F') . $b['name']);
    }
    usort($contents, 'tep_cmp');
?>
          <div class="formArea"><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th></th>
              <th><?php echo TABLE_HEADING_FILENAME; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_SIZE; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_PERMISSIONS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_USER; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_GROUP; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_LAST_MODIFIED; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
<?php
  $rows = 0;
  for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
    $rows++;

    if((!isset($_GET['info']) || (isset($_GET['info']) && ($_GET['info'] == $contents[$i]['name']))) && !isset($fInfo) && ($action != 'upload') && ($action != 'new_folder')) {
      $fInfo = new objectInfo($contents[$i]);
    }

    if ($contents[$i]['name'] == '..') {
      $tmp_array = explode('/', rtrim($current_path, '/'));
      array_pop($tmp_array);
      $goto_link = implode('/', $tmp_array);
      //$goto_link = substr($current_path, 0, strrpos($current_path, '/'));
    } else {
      $goto_link = $current_path . $contents[$i]['name'];
    }

    $row_class = 'dataTableRow';
    if (isset($fInfo) && is_object($fInfo) && ($contents[$i]['name'] == $fInfo->name)) {
      $row_class = 'dataTableRowSelected';
      if( $contents[$i]['is_dir'] ) {
        $href = tep_href_link($g_script, 'goto=' . $goto_link);
      } else {
        $href = tep_href_link($g_script, 'info=' . $contents[$i]['name'] . '&action=edit');
      }
    } else {
      if( $contents[$i]['is_dir'] ) {
        $row_class = 'dataTableRowAlt4';
        $href = tep_href_link($g_script, 'goto=' . $goto_link);
      } else {
        $row_class = 'dataTableRowAlt3';
        $href = tep_href_link($g_script, 'info=' . $contents[$i]['name']);
      }
    }

    if ($contents[$i]['is_dir']) {
      if( $contents[$i]['name'] == '..' ) {
        $icon = tep_image(DIR_WS_ICONS . 'icon_previous_level.png', ICON_PREVIOUS_LEVEL);
        $contents[$i]['name'] = '<b>' . ICON_PREVIOUS_LEVEL . '</b>';
      } else {
        $icon = tep_image(DIR_WS_ICONS . 'icon_folder.png', ICON_FOLDER);
        //$icon = (isset($fInfo) && is_object($fInfo) && ($contents[$i]['name'] == $fInfo->name) ? tep_image(DIR_WS_ICONS . 'current_folder.gif', ICON_CURRENT_FOLDER) : tep_image(DIR_WS_ICONS . 'icon_folder.png', ICON_FOLDER));
      }
      $link = tep_href_link($g_script, 'goto=' . $goto_link);
    } else {
      $icon = tep_image(DIR_WS_ICONS . 'icon_download.png', ICON_FILE_DOWNLOAD . ' ' . $contents[$i]['name']);
      $link = tep_href_link($g_script, 'action=download&filename=' . $contents[$i]['name']);
    }

    echo '              <tr class="' . $row_class . ' row_link" href="' . $href . '">' . "\n";
?>
              <td class="calign"><?php echo '<a href="' . $link . '">' . $icon . '</a>'; ?></td>
              <td><?php echo $contents[$i]['name']; ?></td>
              <td class="ralign"><?php echo ($contents[$i]['is_dir'] ? '&nbsp;' : $contents[$i]['size']); ?></td>
              <td class="calign"><?php echo $contents[$i]['permissions']; ?></td>
              <td><?php echo $contents[$i]['user']; ?></td>
              <td><?php echo $contents[$i]['group']; ?></td>
              <td class="calign"><?php echo $contents[$i]['last_modified']; ?></td>
              <td class="calign tinysep">
<?php 
  if ($contents[$i]['name'] != '..') {
    echo '<a href="' . tep_href_link($g_script, 'info=' . $contents[$i]['name'] . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', ICON_DELETE) . '</a>';
  }

  if( !$contents[$i]['is_dir'] ) {
    echo '<a href="' . tep_href_link($g_script, 'info=' . $contents[$i]['name'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', IMAGE_EDIT) . '</a>';
  }

  if (isset($fInfo) && is_object($fInfo) && ($fInfo->name == $contents[$i]['name']) ) { 
    echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png'); 
  } else {
    if ($contents[$i]['is_dir']) {
      echo '<a href="' . tep_href_link($g_script, 'goto=' . $goto_link) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
    } else {
      echo '<a href="' . tep_href_link($g_script, 'info=' . $contents[$i]['name']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>'; 
    }
  } 
?>
              </td>
            </tr>
<?php
  }

  $info = '';
  if( !empty($current_file) ) {
    $info = 'info=' . $current_file . '&';
  }

  $buttons = array(
    '<a href="' . tep_href_link($g_script, 'action=reset') . '">' . tep_image_button('button_reset.gif', IMAGE_RESET) . '</a>',
    '<a href="' . tep_href_link($g_script, $info . 'action=upload') . '">' . tep_image_button('button_upload.gif', IMAGE_UPLOAD) . '</a>',
    '<a href="' . tep_href_link($g_script, $info . 'action=new_file') . '">' . tep_image_button('button_new_file.gif', TEXT_INFO_NEW_FILE) . '</a>',
    '<a href="' . tep_href_link($g_script, $info . 'action=new_folder') . '">' . tep_image_button('button_new_folder.gif', TEXT_INFO_NEW_FOLDER) . '</a>',
  );
?>
          </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
          <div class="listArea splitLine">
            <div class="floater"><?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, min(1, $rows), $rows, $rows); ?></div>
          </div>
        </div>
<?php
    $heading = array();
    $contents = array();

    switch ($action) {
      case 'delete':
        $heading[] = array('text' => '<b>' . $fInfo->name . '</b>');

        $contents[] = array('form' => tep_draw_form('file', $g_script, 'info=' . $fInfo->name . '&action=deleteconfirm'));
        $contents[] = array('text' => TEXT_DELETE_INTRO);
        $contents[] = array('text' => '<br /><b>' . $fInfo->name . '</b>');
        $contents[] = array('class' => 'calign', 'text' => '<br />' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link($g_script, (tep_not_null($fInfo->name) ? 'info=' . $fInfo->name : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'new_folder':
        $heading[] = array('text' => '<b>' . TEXT_NEW_FOLDER . '</b>');

        $contents[] = array('form' => tep_draw_form('folder', $g_script, 'action=insert'));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
        $contents[] = array('text' => TEXT_NEW_FOLDER_INTRO);
        $contents[] = array('text' => '<br />' . TEXT_INFO_FILENAME . '<br />' . tep_draw_input_field('folder_name'));
        $contents[] = array('class' => 'calign', 'text' => '<br />' . (($directory_writeable == true) ? tep_image_submit('button_save.gif', IMAGE_SAVE) : '') . ' <a href="' . tep_href_link($g_script, (isset($_GET['info']) ? 'info=' . $_GET['info'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'upload':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_UPLOAD . '</b>');

        $contents[] = array('form' => tep_draw_form('file', $g_script, 'action=processuploads', 'post', 'enctype="multipart/form-data"'));
        $contents[] = array('text' => TEXT_UPLOAD_INTRO);

        $file_upload = '';
        for ($i=1; $i<6; $i++) $file_upload .= tep_draw_file_field('file_' . $i) . '<br />';

        $contents[] = array('text' => '<br />' . $file_upload);
        $contents[] = array('class' => 'calign', 'text' => '<br />' . (($directory_writeable == true) ? tep_image_submit('button_upload.gif', IMAGE_UPLOAD) : '') . ' <a href="' . tep_href_link($g_script, (isset($_GET['info']) ? 'info=' . $_GET['info'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if (isset($fInfo) && is_object($fInfo)) {
          $heading[] = array('text' => '<b>' . $fInfo->name . '</b>');

          if( !$fInfo->is_dir ) {
            $contents[] = array(
             'class' => 'calign', 
             'text' => '<a href="' . tep_href_link($g_script, 'info=' . $fInfo->name . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>'
            );
          }
          $contents[] = array('text' => TEXT_INFO_FILENAME . '<br/><b>' . $fInfo->name . '</b>');
          if( !$fInfo->is_dir) {
            $contents[] = array('text' => TEXT_FILE_SIZE . '<br/><b>' . $fInfo->size . '</b>');
          }
          $contents[] = array('text' => TEXT_LAST_MODIFIED . '<br/>' . $fInfo->last_modified);
        } else { // create generic_text dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('class' => 'calign', 'text' => '<a href="' . tep_href_link($g_script, 'action=new_generic_text') . '">' . tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW) . '</a>');
          $contents[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
    }

    if( !empty($heading) && !empty($contents) ) {
      echo '             <div class="rightcell">';
      $box = new box;
      echo $box->infoBox($heading, $contents);
      echo '             </div>';
    }
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>