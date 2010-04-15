<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Ajax Image Browsing and Selection module
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
  $sub_folder = (isset($_POST['sub_path']) ? tep_sanitize_string($g_db->prepare_input($_POST['sub_path'])) : '');
  if( substr($sub_folder, 0, 1) == '.' ) {
    $sub_folder = '';
  }

  if( !empty($sub_folder) ) {
    $sub_folder .= '/';
  }

  $fs_images_path = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);
  $switch_folder = $fs_images_path . $sub_folder;
  $current_dir = getcwd();
  $dir = dir($switch_folder);
  chdir($switch_folder);
  $files_array = array();
  $subdirs_array = array();

  if( !empty($sub_folder) ) {
    $subdirs_array[] = '';
  }

  while(false !== ($script = $dir->read()) ) {
    if( substr($script, 0, 1) != '.' && is_dir($script) ) {
      $subdirs_array[] = $switch_folder . $script;
    } elseif( substr($script, 0, 1) != '.' && !is_dir($script) ) {
      $files_array[] = $sub_folder . $script;
    }
  }
  chdir($current_dir);
  sort($subdirs_array, SORT_STRING);
  sort($files_array, SORT_STRING);
?>
       <div id="#image_list">
<?php
  $j = count($subdirs_array);
  if( $j ) {
?>
        <div style="clear: both;">
          <div class="pageHeading"><?php echo 'Folder: '  . basename(DIR_WS_CATALOG_IMAGES) . '/' . $sub_folder; ?></div>
          
<?php
    for($i=0; $i<$j; $i++) {
?>
          <div style="float:left; margin: 4px; padding: 8px; text-align: center; border: 1px solid #DDD;">
<?php
/*
            <div><?php echo '<a class="folder_list" href="#" attr="' . tep_href_link(FILENAME_JS_MODULES, (!empty($sub_folder))?'sub_path =' . $sub_folder:'' ) . '">' . tep_image(DIR_WS_IMAGES . 'folder_image.png', basename($subdirs_array[$i])) . '</a>'; ?></div>
*/
?>
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
      echo '<a class="folder_list" href="#" attr="' . $attr . '">' . tep_image(DIR_WS_IMAGES . $folder_image, basename($subdirs_array[$i])) . '</a>'; 
?>
            </div>
            <div class="smallText" style="text-align: center"><?php echo basename($subdirs_array[$i]); ?></div>
          </div>
<?php
    }
?>
        </div>
        <div style="clear: both; padding: 8px 0px 4px 0px;"><hr /></div>
<?php
  }
  $j = count($files_array);
  if( $j ) {
?>
        <div style="clear: both;">
          <div class="pageHeading">File List:</div>
<?php
    for($i=0; $i<$j; $i++) {
?>
          <div style="float:left; text-align: center; ; margin: 8px; padding: 4px; width: <?php echo HEADING_IMAGE_WIDTH+16; ?>px;">
            <div style="border: 1px solid #DDD; padding: 8px; width: <?php echo HEADING_IMAGE_WIDTH; ?>px; height: <?php echo HEADING_IMAGE_HEIGHT; ?>px;"><?php echo '<a class="file_list" href="#" attr="' . basename(DIR_WS_CATALOG_IMAGES) . '/' . $files_array[$i] . '">' . tep_catalog_image($files_array[$i], $switch_folder . $files_array[$i], HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT) . '</a>'; ?></div>
            <div class="smallText" style="overflow: hidden; text-align: center;  height: <?php echo 36; ?>px;"><?php echo basename($files_array[$i]); ?></div>
          </div>
<?php
    }
?>
        </div>
<?php
  }
?>
      </div>