<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Multi-Lingual Support and Configurations Script
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

  $lID = isset($_GET['lID'])?(int)$_GET['lID']:'';

  switch($action) {
    case 'help':
      break;
    case 'save':
      if( empty($lID) ) {
        $messageStack->add_session(ERROR_LANGUAGE_INVALID);
        tep_redirect(tep_href_link($g_script));
      }
    case 'insert':
      $error_action = ($action == 'insert')?'new':'save';

      $language_name = $g_db->prepare_input($_POST['language_name']);
      $language_code = strtolower($g_db->prepare_input($_POST['language_code']));
      $language_path = strtolower($g_db->prepare_input($_POST['language_path']));
      $sort_id = (int)$_POST['sort_id'];
      $status_id = isset($_POST['status_id'])?1:0;

      if( empty($language_name) || empty($language_path) ) {
        $messageStack->add(ERROR_LANGUAGE_PARAMS);
        $action = $error_action;
        break;
      }

      if( !empty($language_code) && strlen($language_code) != 2 ) {
        $messageStack->add(ERROR_LANGUAGE_CODE);
        $action = $error_action;
        break;
      }

      $sql_data_array = array(
        'language_name' => $language_name,
        'language_code' => $language_code,
        'language_path' => $language_path,
        'sort_id' => $sort_id,
        'status_id' => $status_id,
      );

      if( $action == 'insert' ) {
        $check_query = $g_db->query("select count(*) as total from " . TABLE_LANGUAGES . " where language_code = '" . $g_db->input($language_code) . "' or language_path = '" . $g_db->input($language_path) . "'");
        $check_array = $g_db->fetch_array($check_query);
        if( $check_array['total'] ) {
          $messageStack->add(ERROR_LANGUAGE_PARAMS);
          $action = $error_action;
          break;
        }
        $g_db->perform(TABLE_LANGUAGES, $sql_data_array);
        $lID = $g_db->insert_id();
        $g_lng->create($lID);

      } elseif( $action == 'save' ) {
        $check_query = $g_db->query("select count(*) as total from " . TABLE_LANGUAGES . " where language_id != '" . (int)$lID . "' and (language_code = '" . $g_db->input($language_code) . "' or language_path = '" . $g_db->input($language_path) . "')");
        $check_array = $g_db->fetch_array($check_query);
        if( $check_array['total'] ) {
          $messageStack->add(ERROR_LANGUAGE_PARAMS);
          $action = $error_action;
          break;
        }

        if( empty($language_code) ) {
          $messageStack->add_session(WARNING_LANGUAGE_DEFAULT_UPDATE, 'warning');
        } else {
          $check_query = $g_db->query("select count(*) as total from " . TABLE_LANGUAGES . " where language_id = '" . (int)$lID . "' and language_code = '" . $g_db->input($language_code) . "'");
          $check_array = $g_db->fetch_array($check_query);
          if( !$check_array['total'] ) {
            $messageStack->add_session(WARNING_LANGUAGE_CODE_INVALID, 'warning');
          }
        }

        $g_db->perform(TABLE_LANGUAGES, $sql_data_array, 'update', "language_id = '" . (int)$lID . "'");
        //$g_lng->create($lID);
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'lID') . 'lID=' . $lID));
      break;
    case 'deleteconfirm':
      if( empty($lID) ) {
        $messageStack->add_session(ERROR_LANGUAGE_INVALID);
        tep_redirect(tep_href_link($g_script));
      }
      $result = $g_lng->delete($lID);
      if( !$result ) {
        $messageStack->add_session(ERROR_LANGUAGE_DELETE);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      $g_db->query("delete from " . TABLE_LANGUAGES . " where language_id = '" . (int)$lID . "'");
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'lID') ));
      break;
    case 'delete':
      $check_query = $g_db->query("select count(*) as total from " . TABLE_LANGUAGES . " where language_id = '" . (int)$lID . "'");
      $check_array = $g_db->fetch_array($check_query);
      if( !$check_array['total'] ) {
        $messageStack->add_session(ERROR_LANGUAGE_INVALID);
        tep_redirect(tep_href_link($g_script));
      }
      break;
    default:
      break;
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
          <div class="maincell">
            <div class="comboHeadingTop">
              <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', BOX_OTHER_QUICK_HELP) . '</a>'; ?></div>
              <div class="floater"><h1><?php echo HEADING_TITLE; ?></h1></div>
            </div>
            <div class="formArea"><table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_LANGUAGES; ?></th>
                <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
<?php
  $languages_query_raw = "select language_id, language_name, language_code, language_path, sort_id, status_id from " . TABLE_LANGUAGES . " order by status_id desc, sort_id";
  $languages_split = new splitPageResults($languages_query_raw);
  $languages_query = $g_db->query($languages_split->sql_query);
  $rows = 0;
  while ($languages = $g_db->fetch_array($languages_query)) {

    $rows++;
    $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

    if( (empty($lID) || $lID == $languages['language_id']) && !isset($lInfo) && (substr($action, 0, 3) != 'new')) {
      $lInfo = new objectInfo($languages);
    }

    if( (isset($lInfo) && is_object($lInfo)) && ($languages['language_id'] == $lInfo->language_id) ) {
      echo '              <tr class="dataTableRowSelected row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('action, lID') . 'lID=' . $languages['language_id'] . '&action=edit') . '">' . "\n";
    } else {
      echo '              <tr class="' . $row_class . ' row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'lID') . 'lID=' . $languages['language_id']) . '">';
    }
?>
                <td><?php echo $languages['language_name']; ?></td>
                <td class="tinysep calign">
<?php
    echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action, lID') . 'lID=' . $languages['language_id'] . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $languages['language_name']) . '</a>';
    echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action, lID') . 'lID=' . $languages['language_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $languages['language_name']) . '</a>';

    if(isset($lInfo) && is_object($lInfo) && ($languages['language_id'] == $lInfo->language_id) ) { 
      echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', TEXT_SELECTED); 
    } else { 
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'lID') . 'lID=' . $languages['language_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_ICON_INFO) . '</a>';
    } 
?>
                </td>
              </tr>
<?php
  }
  $buttons = array();
  if( empty($action) ) {
    $buttons = array(
     '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>',
    );
  }
?>
            </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></div>
            <div class="listArea splitLine">
              <div class="floater"><?php echo $languages_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
              <div class="floatend"><?php echo $languages_split->display_links(tep_get_all_get_params('action', 'page') ); ?></div>
            </div>
          </div>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
    case 'new':
      $heading[] = array('class' => 'heavy', 'text' => TEXT_INFO_HEADING_NEW_LANGUAGE);

      $contents[] = array('form' => tep_draw_form('languages', $g_script, tep_get_all_get_params('action', 'lID') . 'action=insert'));
      $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'new_entry.png', IMAGE_NEW) );
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('class' => 'rpad', 'section' => '<div>');
      $contents[] = array('text' => TEXT_INFO_LANGUAGE_NAME . '<br />' . tep_draw_input_field('language_name'));
      $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . '<br />' . tep_draw_input_field('language_code'));
      $contents[] = array('text' => TEXT_INFO_LANGUAGE_PATH . '<br />' . tep_draw_input_field('language_path'));
      $contents[] = array('section' => '</div>');
      $contents[] = array('text' => TEXT_INFO_SORT_ORDER . '<br />' . tep_draw_input_field('sort_id'));
      $contents[] = array('text' => tep_draw_checkbox_field('status_id') . ' ' . TEXT_INFO_ENABLED);

      $buttons = array(
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_insert.gif', IMAGE_INSERT),
      );
      $contents[] = array(
        'class' => 'calign', 
        'text' => implode('', $buttons),
      );
      break;
    case 'edit':
      $heading[] = array('class' => 'heavy', 'text' => TEXT_INFO_HEADING_EDIT_LANGUAGE);

      $contents[] = array('form' => tep_draw_form('languages', $g_script, tep_get_all_get_params('action', 'lID') . 'lID=' . $lInfo->language_id . '&action=save'));
      $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'update_entry.png', IMAGE_EDIT) );
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('class' => 'rpad', 'section' => '<div>');
      $contents[] = array('text' => TEXT_INFO_LANGUAGE_NAME . '<br />' . tep_draw_input_field('language_name', $lInfo->language_name));
      $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . '<br />' . tep_draw_input_field('language_code', $lInfo->language_code));
      $contents[] = array('text' => TEXT_INFO_LANGUAGE_PATH . '<br />' . tep_draw_input_field('language_path', $lInfo->language_path));
      $contents[] = array('section' => '</div>');
      $contents[] = array('text' => TEXT_INFO_SORT_ORDER . '<br />' . tep_draw_input_field('sort_id', $lInfo->sort_id));

      $contents[] = array('text' => tep_draw_checkbox_field('status_id', 1, $lInfo->status_id?true:false) . ' ' .  TEXT_INFO_ENABLED);

      $buttons = array(
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_update.gif', IMAGE_UPDATE)
      );
      $contents[] = array(
        'class' => 'calign', 
        'text' => implode('', $buttons),
      );
      break;

    case 'delete':
      $heading[] = array('class' => 'heavy', 'text' => TEXT_INFO_HEADING_DELETE_LANGUAGE);

      if( empty($lInfo->language_code) ) {
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'critical_notice.png', IMAGE_CONFIRM) );
        $contents[] = array('class' => 'calign heavy', 'text' => $lInfo->language_name);
        $contents[] = array('text' => sprintf(TEXT_INFO_CANNOT_DELETE_INTRO, $lInfo->language_name));
        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'lID') . 'lID=' . $lInfo->language_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>',
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons),
        );
        break;
      }

      $contents[] = array('form' => tep_draw_form('languages', $g_script, tep_get_all_get_params('action', 'lID') . 'lID=' . $lInfo->language_id  . '&action=deleteconfirm'));
      $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
      $contents[] = array('class' => 'calign heavy', 'text' => $lInfo->language_name);
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);

      $buttons = array(
        '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action') ) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
        tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
      );
      $contents[] = array(
        'class' => 'calign', 
        'text' => implode('', $buttons),
      );
      break;

    default:
      if( isset($lInfo) && is_object($lInfo)) {

        $heading[] = array('class' => 'heavy', 'text' => $lInfo->language_name);
        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'lID') . 'lID=' . $lInfo->language_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>',
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'lID') . 'lID=' . $lInfo->language_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>'
        );

        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons),
        );
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_NAME . '<br />' . $lInfo->language_name);
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . '<br />' . $lInfo->language_code);
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_PATH . '<br />' . $lInfo->language_path);
        $contents[] = array('text' => TEXT_INFO_SORT_ORDER . '<br />' . $lInfo->sort_id);

        if( $lInfo->status_id) $contents[] = array('class' => 'heavy', 'text' => TEXT_INFO_ENABLED);
      } else { // create generic_text dummy info
        $heading[] = array('class' => 'heavy', 'text' => EMPTY_GENERIC);
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
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>
