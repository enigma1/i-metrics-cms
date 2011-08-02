<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Ajax Image Resizing module
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
  $img_alt = (isset($_POST['img_alt']) ? tep_sanitize_string($g_db->prepare_input($_POST['img_alt'])) : '');
  $img_desc = (isset($_POST['img_desc']) ? tep_sanitize_string($g_db->prepare_input($_POST['img_desc'])) : '');
  $img_popup = (isset($_POST['img_popup']) ? tep_sanitize_string($g_db->prepare_input($_POST['img_popup'])) : '');
  $img_group_name = (isset($_POST['img_group_name']) ? tep_sanitize_string($g_db->prepare_input($_POST['img_group_name'])) : '');
  $img_thumb = (isset($_POST['img_thumb']) ? true : false);

  $org_image = $image = (isset($_POST['image']) ? tep_sanitize_string($g_db->prepare_input($_POST['image'])) : '');
  $resize_image = '';


  $fs_dir = tep_front_physical_path(DIR_WS_CATALOG_IMAGES);

  $length = strlen(DIR_WS_CATALOG);
  $rel_path = substr(DIR_WS_CATALOG_IMAGES, $length);
  $image = substr($image, strlen($rel_path));

  $tmp_array = explode('.',$image);
  if( !is_array($tmp_array) || count($tmp_array) != 2 || strlen($tmp_array[0]) < 1 || !file_exists($fs_dir . $image) ) {
    $action = 'error';
  }

  switch ($action) {
    case 'error':
      break;
    default:
      $resize_flag = false;
        $width =  (isset($_POST['width']) ? (int)$_POST['width'] : '');
        $height =  (isset($_POST['height']) ? (int)$_POST['height'] : '');

        if( !$width && $height ) $width = $height;
        if( !$height && $width ) $height = $width;

      if( $img_thumb ) {
        $org_width = $org_height = 0;
        $test = tep_catalog_calculate_image($image, $org_width, $org_height);

        $resized_image = tep_catalog_calculate_image($image, $width, $height);

        if( empty($resized_image) ) {
          $resized_image = $g_cserver . DIR_WS_CATALOG_IMAGES . $image;
        } elseif($org_width != $width || $org_height != $height) {
          $resize_flag = true;
        }
      } elseif( isset($_POST['width']) && isset($_POST['height']) ) {
        $resized_image = $g_cserver . DIR_WS_CATALOG_IMAGES . $image;
      } else {
        $resized_image = $g_cserver . DIR_WS_CATALOG_IMAGES . $image;
      }
      break;
  }
?>
    <div id="resize_result">
      <div class="comboHeading" style="border: 1px solid #777;">
<?php
  if( $action != 'error' ) {
    if( !$resize_flag && !$width && !$height ) {
      $width = 240;
      $height = 240;
    }
?>
        <?php echo tep_draw_form("form_resize", $g_script, 'action=resize', 'post', 'id="core_resize_form" enctype="multipart/form-data"'); ?>
          <div><?php echo TEXT_IMAGE_RESIZE_OPTIONS . '&nbsp;'  . $image; ?></div>
          <div class="vlinepad"><?php echo TEXT_IMAGE_RESIZE_INFO; ?></div>
          <div class="cleaner">
            <div class="floater" style="padding-right: 20px;"><fieldset><legend><b><?php echo TEXT_IMAGE_PARAMETERS; ?></b></legend>
              <div class="pad_tb2"><?php echo tep_draw_checkbox_field('img_thumb', 'on', $img_thumb, 'title="' . TEXT_IMAGE_THUMB_INFO . '"') . TEXT_IMAGE_THUMB ; ?></div>
              <div><?php echo TEXT_IMAGE_WIDTH; ?></div>
              <div class="pad_b2"><?php echo tep_draw_input_field('width', '', 'size="3"'); ?></div>
              <div><?php echo TEXT_IMAGE_HEIGHT; ?></div>
              <div class="pad_b2"><?php echo tep_draw_input_field('height', '', 'size="3"'); ?></div>
              <div><?php echo TEXT_IMAGE_ALT; ?></div>
              <div class="pad_b2"><?php echo tep_draw_input_field('img_alt', '', 'title="' . TEXT_IMAGE_ALT_INFO . '"'); ?></div>
              <div><?php echo TEXT_IMAGE_DESC; ?></div>
              <div class="pad_b2"><?php echo tep_draw_input_field('img_desc', '', 'title="' . TEXT_IMAGE_DESC_INFO . '"'); ?></div>
              <div><?php echo TEXT_IMAGE_POPUP; ?></div>
              <div class="pad_b2"><?php echo tep_draw_input_field('img_popup', '', 'title="' . TEXT_IMAGE_POPUP_INFO . '"'); ?></div>
              <div><?php echo TEXT_IMAGE_GROUP_NAME; ?></div>
              <div><?php echo tep_draw_input_field('img_group_name', '', 'title="' . TEXT_IMAGE_GROUP_INFO . '"'); ?></div>
            </fieldset></div>
            <div><fieldset><legend><b><?php echo TEXT_IMAGE_PREVIEW; ?></b></legend>
              <div id="image_resize_complete" attr="<?php echo $resized_image; ?>" class="main" style="text-align: center; padding: 8px 0px 8px 0px;"><?php echo tep_catalog_image($image, $image, $width, $height); ?></div>
              <div style="display:none;">
<?php 
    echo tep_image_submit('button_insert.gif', IMAGE_INSERT, 'attr="' . $image . '" class="resize_button"'); 
    echo tep_draw_hidden_field('module','image_resize');
    echo tep_draw_hidden_field('image', $rel_path . $image);
    echo tep_draw_hidden_field('org_image', $g_crelpath . $org_image);
?>
              </div>
            </fieldset></div>
          </div>
        </form>
<?php
  } else {
?>
        <div><?php echo ERROR_INCOMPLETE_PARAMETERS; ?></div>
<?php
  }
?>
      </div>
    </div>