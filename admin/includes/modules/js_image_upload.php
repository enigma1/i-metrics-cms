<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Ajax Image Upload module
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
------------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
//
*/
  $action = (isset($_GET['action']) ? tep_sanitize_string($g_db->prepare_input($_GET['action'])) : '');
  $sub_folder = (isset($_POST['sub_path']) ? tep_sanitize_string($g_db->prepare_input($_POST['sub_path'])) : '');
  if( substr($sub_folder, 0, 1) == '.' ) {
    $sub_folder = '';
  }

  if( !empty($sub_folder) ) {
    $sub_folder = rtrim($sub_folder, ' /');
    $sub_folder .= '/';
  }

  $images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
  $switch_folder = $images_path . $sub_folder;
  $current_dir = getcwd();
  $dir = dir($switch_folder);
  chdir($switch_folder);
  $subdirs_array = array();

  if( !empty($sub_folder) ) {
    $subdirs_array[] = '';
  }

  while(false !== ($script = $dir->read()) ) {
    if( substr($script, 0, 1) != '.' && is_dir($script) ) {
      $subdirs_array[] = $switch_folder . $script;
    }
  }
  chdir($current_dir);
  sort($subdirs_array, SORT_STRING);

  switch ($action) {
    case 'insert':
      $cImage = new upload('image', $switch_folder);
      if( $cImage->c_result ) {
        echo '<div class="messageStackSuccess" style="padding: 2px;">' . sprintf(SUCCESS_IMAGE_UPLOAD, $cImage->filename,$switch_folder) . '</div>';
      } else {
        echo '<div class="messageStackError" style="padding: 2px;">' . ERROR_IMAGE_UPLOAD . '</div>';
      }
      break;
    default:
      break;
  }
?>
<div id="upload_result"></div>
<?php
  if( empty($action) ) {
?>
    <div class="comboHeading" style="border: 1px solid #777;">
      <?php echo tep_draw_form("insert", $g_script, 'action=insert', 'post', 'id="core_upload_form" enctype="multipart/form-data"'); ?>
        <div><?php echo TEXT_FILE_UPLOAD_FOLDER . '&nbsp;'  . basename(DIR_WS_CATALOG_IMAGES) . '/' . $sub_folder; ?></div>
        <div style="padding: 8px 0px 8px 0px;">
<?php 
    echo TEXT_FILE_UPLOAD . '&nbsp;' . tep_draw_file_field('image');
    echo tep_draw_hidden_field('module','image_upload');
    if( !empty($sub_folder) ) {
      echo tep_draw_hidden_field('sub_path', $sub_folder);
    }
?>
        </div>
        <div style="display: none;"><?php echo tep_image_submit('button_insert.gif', IMAGE_INSERT, 'class="upload_button"'); ?></div>
      </form>
      <div style="clear: both; padding: 8px 0px 4px 0px;"><hr /></div>
      <div id="#image_list">
<?php
    $j = count($subdirs_array);
    if( $j ) {
?>
        <div style="clear: both;">
          <div><?php echo TEXT_FILE_FOLDER . '&nbsp;'  . basename(DIR_WS_CATALOG_IMAGES) . '/' . $sub_folder; ?></div>
          
<?php
      for($i=0; $i<$j; $i++) {
?>
          <div style="float:left; margin: 4px; padding: 8px; text-align: center; border: 1px solid #FFF;">
            <div>
<?php
        if( empty($subdirs_array[$i]) ) {
          $tmp_array = explode('/', $sub_folder);
          array_pop($tmp_array);
          if( count($tmp_array) ) {
            array_pop($tmp_array);
          }
          $attr = implode('/', $tmp_array);
          $subdirs_array[$i] = 'Up One Level';
          $folder_image = 'folder_up.png';
        } else {
          $attr = trim(basename($subdirs_array[$i]), ' /');
          $folder_image = 'folder_image.png';

          if( !empty($sub_folder) ) {
            $attr = $sub_folder . $attr;
          }
        }
        echo '<a class="folder_upload_list" href="#" attr="' . $attr . '">' . tep_image(DIR_WS_IMAGES . $folder_image, basename($subdirs_array[$i])) . '</a>'; 
?>
            </div>
            <div class="calign"><?php echo basename($subdirs_array[$i]); ?></div>
          </div>
<?php
      }
?>
        </div>
<?php
    }
?>
      </div>
    </div>
<?php
  }
?>