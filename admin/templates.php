<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Templates script
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

  $tID = (isset($_GET['tID']) ? (int)$_GET['tID'] : '');
  $gID = (isset($_GET['gID']) ? (int)$_GET['gID'] : TEMPLATE_SYSTEM_GROUP);
  $template_content = '';

  if( $tID > 0 ) {
    $check_query = $g_db->query("select count(*) as total from " . TABLE_TEMPLATES . " where template_id = '" . (int)$tID . "'");
    $check_array = $g_db->fetch_array($check_query);
    if( !$check_array['total'] ) {
      $tID = '';
    }
  }

  $s_sort_id = (isset($_GET['s_sort_id']) ? (int)$_GET['s_sort_id'] : '');

  switch( $action ) {
    case 'change_wp':
      $g_wp_ifc = (isset($_GET['wp']) && $_GET['wp'] == 1)?true:false;
      $messageStack->add_session(WARNING_WP_CHANGED, 'warning');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=new_template'));
      break;
    case 'delete_template_confirm':
      if( isset($_POST['template_id']) && !empty($_POST['template_id']) ) {
        $template_id = (int)$_POST['template_id'];
        $g_plugins->invoke($action, $tID);
        $g_db->query("delete from " . TABLE_TEMPLATES . " where template_id = '" . (int)$template_id . "'");
        $messageStack->add_session(WARNING_TEMPLATE_REMOVED, 'warning');
      }
      tep_redirect(tep_href_link($g_script));
      break;
    case 'insert_template_text':
    case 'update_template':
      if( empty($_POST['template_title']) ) {
        $messageStack->add(ERROR_TEMPLATE_TITLE_EMPTY);
        $action = 'new_template';
        break;
      }
      if( empty($_POST['template_description']) ) {
        $messageStack->add(ERROR_TEMPLATE_DESCRIPTION_EMPTY);
        $action = 'new_template';
        break;
      }

      if( empty($_POST['template_subject']) ) {
        $_POST['template_subject'] = $_POST['template_title'];
      }

      $group_id = $gID;
      if( isset($_POST['group_id']) && $_POST['group_id'] > 0 ) {
        $group_id = (int)$_POST['group_id'];
      }

      $sql_data_array = array(
        'group_id' => $group_id,
        'template_title' => $g_db->prepare_input($_POST['template_title']),
        'template_subject' => $g_db->prepare_input($_POST['template_subject']),
        'template_content' => $g_db->prepare_input($_POST['template_description']),
      );

      if( !empty($tID) ) {
        $template_id = $tID;
        $messageStack->add_session(SUCCESS_TEMPLATE_UPDATED, 'success');
      } elseif($action == 'insert_template_text') {
        $g_db->perform(TABLE_TEMPLATES, $sql_data_array);
        $template_id = $g_db->insert_id();
        $messageStack->add_session(SUCCESS_TEMPLATE_CREATED, 'success');
      } else {
        tep_redirect(tep_href_link($g_script));
      }

      if($action == 'insert_template_text') {
        $insert_sql_data = array(
          'template_id' => (int)$template_id,
        );
      } elseif ($action == 'update_template') {
        $g_db->perform(TABLE_TEMPLATES, $sql_data_array, 'update', "template_id = '" . (int)$template_id . "'");
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'tID', 'gID') . 'action=new_template&tID=' . $template_id . '&gID=' . $group_id));
      break;
    case 'copy_to_confirm':
      if( isset($_POST['template_id']) && tep_not_null($_POST['template_id']) ) {
        $template_id = $g_db->prepare_input($_POST['template_id']);
        if( $_POST['copy_as'] == 'duplicate') {
          $template_query = $g_db->query("select group_id, template_title, template_subject, template_content from " . TABLE_TEMPLATES . " where template_id = '" . (int)$template_id . "'");
          $template = $g_db->fetch_array($template_query);
          if( isset($_POST['template_title']) && !empty($_POST['template_title']) ) {
            $template['template_title'] = $g_db->filter($_POST['template_title']);
          }
          $sql_data_array = array(
            'group_id' => $template['group_id'],
            'template_title' => $template['template_title'],
            'template_subject' => $template['template_subject'],
            'template_content' => $template['template_content'],
          );

          $g_db->perform(TABLE_TEMPLATES, $sql_data_array);
          $template_id = $g_db->insert_id();
        }
      }

      tep_redirect(tep_href_link($g_script, 'tID=' . $template_id));
      break;
    case 'template_preview':
      break;
    case 'template_upload':
      $cFile = new upload('template_file');
      if( !$cFile->parse() || !tep_read_contents($cFile->tmp_filename, $template_content) ) {
        $messageStack->add_session(ERROR_TEMPLATE_FILE_READ);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=new_template'));
      }
      $action = 'new_template';
      break;
    case 'template_download':
      if( empty($tID) ) {
        $messageStack->add_session(ERROR_TEMPLATE_INVALID);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'tID') ));
      }
      $template_query = $g_db->query("select template_title, template_content from " . TABLE_TEMPLATES . " where template_id = '" . (int)$tID . "'");
      if( !$g_db->num_rows($template_query) ) {
        $messageStack->add_session(ERROR_TEMPLATE_INVALID);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'tID') ));
      }
      $template_array = $g_db->fetch_array($template_query);
      $filename = tep_create_safe_string(strtolower($template_array['template_title']), '-') . '.html';
      header('Content-type: application/x-octet-stream');
      header('Content-disposition: attachment; filename=' . $filename);
      echo $template_array['template_content'];
      $g_session->close();
      break;
    default:
      break;
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  if ($action == 'new_template') {
    $parameters = array(
      'template_id' => '',
      'group_id' => TEMPLATE_SYSTEM_GROUP,
      'template_title' => '',
      'template_subject' => '',
      'template_content' => $template_content,
    );
    $tInfo = new objectInfo($parameters);

    $groups_query = "select group_id as id, group_title as text from " . TABLE_TEMPLATES_GROUPS . " order by group_title";
    $groups_array = $g_db->query_to_array($groups_query);

    if( !empty($tID) ) {
      $template_query = $g_db->query("select template_id, group_id, template_title, template_subject, template_content from " . TABLE_TEMPLATES . " where template_id = '" . (int)$tID . "'");
      $template = $g_db->fetch_array($template_query);
      $tInfo->objectInfo($template, false);
      if( !empty($template_content) ) {
        $tInfo->template_content = $template_content;
      }
    }

    if( !empty($tID) ) {
      $form_action = 'tID=' . $tID . '&action=template_upload'; 
    } else {
      $form_action = 'action=template_upload'; 
    }
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=edit') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
            <div class="floater"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('form_template_text', $g_script, $form_action, 'post', 'enctype="multipart/form-data"'); ?><fieldset><legend><?php echo HEADING_TITLE_UPLOAD; ?></legend>
            <div class="bounder infile vmargin">
              <label class="floater"><?php echo TEXT_INFO_TEMPLATE_FILE; ?></label>
              <div class="floater lspacer"><?php echo tep_draw_file_field('template_file'); ?></div>
            </div>
            <div class="formButtons"><?php echo tep_image_submit('button_upload.gif', IMAGE_UPLOAD); ?></div>
          </fieldset></form></div>
<?php
    if( !empty($tID) ) {
      $form_action = 'tID=' . $tID . '&action=update_template'; 
    } else {
      $form_action = 'action=insert_template_text'; 
    }
?>
          <div class="formArea"><?php echo tep_draw_form('form_template_text', $g_script, $form_action, 'post', 'enctype="multipart/form-data"'); ?><fieldset><legend><?php echo HEADING_TEMPLATE_EDIT; ?></legend>
            <label for="template_title"><?php echo TEXT_TEMPLATE_NAME; ?></label>
            <div class="bspacer"><?php echo tep_draw_input_field('template_title', $tInfo->template_title, 'id="template_title" size="70"'); ?></div>
            <label for="template_subject"><?php echo TEXT_TEMPLATE_SUBJECT; ?></label>
            <div class="bspacer"><?php echo tep_draw_input_field('template_subject', $tInfo->template_subject, 'id="template_subject" size="70"'); ?></div>
            <label for="template_group"><?php echo TEXT_TEMPLATE_GROUP; ?></label>
            <div class="bspacer"><?php echo tep_draw_pull_down_menu('group_id', $groups_array, $tInfo->group_id, 'id="template_group"'); ?></div>

            <label class="floater"><?php echo TEXT_TEMPLATE_CONTENT; ?></label>
            <div class="floatend">
<?php 
    if( $g_wp_ifc ) {
      echo '<a class="dataTableContentRed" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=change_wp&wp=0') . '">' . TEXT_INFO_DISABLE_WP . '</a>';
    } else {
      echo '<a class="dataTableContentRed" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=change_wp&wp=1') . '">' . TEXT_INFO_ENABLE_WP . '</a>';
    }
/*
<div class="cleaner"><?php echo tep_draw_textarea_field('template_content', (!empty($template_content)?$template_content:$tInfo->template_content), '', '15'); ?></div>
*/
?>
            </div>
            <div class="cleaner"><?php echo tep_draw_textarea_field('template_description', $tInfo->template_content, '', '15'); ?></div>
            <div class="bounder inimg vmargin">
              <label class="floater"><?php echo TEXT_INFO_INSERT_IMAGES . ':'; ?></label>
              <div class="floater rspacer"><?php echo '<a href="#" id="image_selection">' . tep_image(DIR_WS_ICONS . 'icon_images_head.png', TEXT_INFO_INSERT_IMAGES) . '</a>'; ?></div>
              <label class="floater"><?php echo TEXT_INFO_UPLOAD_IMAGES . ':'; ?></label>
              <div class="floater"><?php echo '<a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a>'; ?></div>
            </div>
            <div class="formButtons">
<?php
    $buttons = array();
    if( !empty($tID) ) {
      $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'tID') . 'tID=' . $tID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>';
      $buttons[] = tep_image_submit('button_update.gif', IMAGE_UPDATE);
    } else {
      $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'tID') ) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>';
      $buttons[] = tep_image_submit('button_insert.gif', IMAGE_INSERT);
    }
    echo implode('', $buttons);
?>
            </div>
          </fieldset></form></div>
        </div>
<?php
  } elseif( !empty($tID) && $action == 'template_preview' ) {
    $template_query = $g_db->query("select template_id, template_title, template_content from " . TABLE_TEMPLATES . " where template_id = '" . (int)$tID . "'");
    $template = $g_db->fetch_array($template_query);
    $tInfo = new objectInfo($template, false);
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="floater"><h1><?php  echo $tInfo->template_title; ?></h1></div>
          </div>
          <div><?php echo $tInfo->template_content; ?></div>
        </div>
<?php
  } else {
    $search = isset($_GET['search'])?$g_db->prepare_input($_GET['search']):'';
?>
        <div class="maincell">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_TITLE . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_TITLE) . '</a>'; ?></div>
            <div class="floater"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div class="floater">
<?php
    echo tep_draw_form('search', $g_script, '', 'get', 'id="template_search"');
    echo TEXT_TITLE_SEARCH . '&nbsp;' . tep_draw_input_field('search', '', 'size="50"');
    $params_string = tep_get_all_get_params('action', 'search', 'page') . 'action=search';
    $params_array = tep_get_string_parameters($params_string);
    foreach($params_array as $key => $value ) {
      echo tep_draw_hidden_field($key, $value);
    }
    echo '</form>' . "\n";

    $groups_query = "select group_id, group_title from " . TABLE_TEMPLATES_GROUPS;
    $groups_array = $g_db->query_to_array($groups_query, 'group_id');
?>
            </div>
            <div class="floatend"><?php echo '<a href="' . tep_href_link($g_script, 'action=new_template') . '">' . tep_image_button('button_new.gif', IMAGE_NEW_TEMPLATE) . '</a>'; ?></div>
          </div>
          <div class="comboHeading">
<?php
    foreach($groups_array as $key => $value ) {
?>
            <div class="dataTableRowAlt3 colorblock floater"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'gID', 'tID') . 'gID=' . $key) . '">' . sprintf($value['group_title'], TEXT_INFO_TEMPLATES) . '</a>'; ?></div>
<?php
    }
?>
          </div>
<?php
    $rows = 0;
    $sort_by = '';
    $sortID = 2;
    $sortTitle = 3;
    $sortSubject = 5;
    switch( $s_sort_id) {
      case 1;
        $sort_by = "template_id";
        break;
      case 2;
        $sortID = 1;
        $sort_by = "template_id desc";
        break;
      case 3;
        $sortTitle = 4;
        $sort_by = "template_title asc";
        break;
      case 4;
        $sort_by = "template_title desc";
        break;
      case 5;
        $sortSubject = 6;
        $sort_by = "template_subject asc";
        break;
      case 6;
        $sort_by = "template_subject desc";
        break;
      default:
        $sort_by = "template_title, template_id desc";
        break;
    }

    $sort_by = " order by " . $sort_by;
    if( !empty($search) ) {
      $template_query_raw = "select template_id, group_id, template_title, template_subject, template_content from " . TABLE_TEMPLATES . " where (template_title like '%" . $g_db->input($search) . "%' or template_content like '%" . $g_db->input($search) . "%')" . $sort_by . "";
    } else {
      if( empty($gID) ) $gID = TEMPLATE_SYSTEM_GROUP;
      $template_query_raw = "select template_id, group_id, template_title, template_subject, template_content from " . TABLE_TEMPLATES . " where group_id = '" . (int)$gID . "'" . $sort_by . "";
    }

    $template_split = new splitPageResults($template_query_raw, 50);
    $template_query = $g_db->query($template_split->sql_query);
?>
          <div class="comboHeading">
            <div class="floater"><?php echo $template_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $template_split->display_links(tep_get_all_get_params('page')); ?></div>
          </div>
          <div class="listArea"><table class="tabledata" id="templates_table">
            <tr class="dataTableHeadingRow">
              <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 's_sort_id') . 's_sort_id=' . $sortID) . '">' . TABLE_HEADING_ID . '</a>'; ?></th>
              <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 's_sort_id') . 's_sort_id=' . $sortTitle) . '">' . TABLE_HEADING_TITLE . '</a>'; ?></th>
              <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 's_sort_id') . 's_sort_id=' . $sortSubject) . '">' . TABLE_HEADING_SUBJECT . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_GROUP; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
<?php
    while( $template = $g_db->fetch_array($template_query) ) {
      $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';

      if( !empty($tID) && $tID == $template['template_id'] ) {
        $tInfo = new objectInfo($template);
      }

      $rows++;
      $template['group_title'] = $groups_array[$template['group_id']]['group_title'];
      if( isset($tInfo) && is_object($tInfo) && ($template['template_id'] == $tInfo->template_id) ) {
        //echo '              <tr class="dataTableRowSelected row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'tID') . 'tID=' . $template['template_id'] . '&action=new_template') . '">' . "\n";
        echo '              <tr class="dataTableRowSelected">' . "\n";
      } else {
        echo '              <tr class="' . $row_class . ' row_link" href="' . tep_href_link($g_script, tep_get_all_get_params('tID') . 'tID=' . $template['template_id']) . '">' . "\n";
      }
?>
              <td><?php echo $template['template_id']; ?></td>
              <td><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'tID') . 'tID=' . $template['template_id'] . '&action=new_template') . '">' . $template['template_title'] . '</a>'; ?></td>
              <td><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'tID') . 'tID=' . $template['template_id'] . '&action=new_template') . '">' . $template['template_subject'] . '</a>'; ?></td>
              <td><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'tID') . 'tID=' . $template['template_id'] . '&action=new_template') . '">' . $template['group_title'] . '</a>'; ?></td>
              <td class="tinysep calign">
<?php
      echo '<a href="' . tep_href_link($g_script, 'tID=' . $template['template_id'] . '&action=delete_template') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $template['template_title']) . '</a>';
      echo '<a href="' . tep_href_link($g_script, 'tID=' . $template['template_id'] . '&action=new_template') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $template['template_title']) . '</a>';
      echo '<a href="' . tep_href_link($g_script, 'tID=' . $template['template_id'] . '&action=template_preview') . '">' . tep_image(DIR_WS_ICONS . 'icon_preview.png', ICON_PREVIEW) . '</a>';
      echo '<a href="' . tep_href_link($g_script, 'tID=' . $template['template_id'] . '&action=template_download') . '">' . tep_image(DIR_WS_ICONS . 'icon_download.png', ICON_FILE_DOWNLOAD . ' ' . $template['template_title']) . '</a>';
      if (isset($tInfo) && is_object($tInfo) && ($template['template_id'] == $tInfo->template_id)) { 
        echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', $template['template_title'] . ' ' . TEXT_SELECTED);
      } else { 
        echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('tID') . 'tID=' . $template['template_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_SELECT . ' ' . $template['template_title']) . '</a>';
      }
?>
              </td>
            </tr>
<?php
    }
?>
          </table></div>
          <div class="listArea splitLine">
            <div class="floater"><?php echo $template_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $template_split->display_links(tep_get_all_get_params('page')); ?></div>
          </div>
        </div>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'new_group':
        break;
      case 'delete_template':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_TEMPLATE . '</b>');
        $contents[] = array('form' => tep_draw_form('template', $g_script, 'action=delete_template_confirm') . tep_draw_hidden_field('template_id', $tInfo->template_id));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
        $contents[] = array('text' => TEXT_DELETE_TEMPLATE_INTRO);
        $contents[] = array('text' => '<b>' . $tInfo->template_title . '</b>');

        $buttons = array(
          '<a href="' . tep_href_link($g_script, 'tID=' . $tInfo->template_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          tep_image_submit('button_delete.gif', IMAGE_DELETE)
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons)
        );
        break;
      case 'copy_to':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_COPY_TO . '</b>');
        $contents[] = array('form' => tep_draw_form('copy_to', $g_script, 'action=copy_to_confirm') . tep_draw_hidden_field('template_id', $tInfo->template_id));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'copy_entry.png', IMAGE_COPY) );
        $contents[] = array('text' => TEXT_INFO_COPY_TO_INTRO);
        $contents[] = array('class' => 'rpad', 'text' => tep_draw_input_field('template_title', $tInfo->template_title));
        $contents[] = array('text' => tep_draw_hidden_field('copy_as', 'duplicate'));
        $buttons = array(
          '<a href="' . tep_href_link($g_script, 'tID=' . $tInfo->template_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          tep_image_submit('button_copy.gif', IMAGE_COPY)
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons)
        );
        break;
      default:
        if( $rows > 0 && isset($tInfo) && is_object($tInfo) ) {
          // Navigation History
          $g_plugins->invoke('add_current_page', $tInfo->template_title, tep_get_all_get_params());

          $heading[] = array('text' => '<b>' . $tInfo->template_title . '</b>');
          $buttons = array(
            '<a href="' . tep_href_link($g_script, 'tID=' . $tInfo->template_id . '&action=new_template') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>',
            '<a href="' . tep_href_link($g_script, 'tID=' . $tInfo->template_id . '&action=delete_template') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>',
            '<a href="' . tep_href_link($g_script, 'tID=' . $tInfo->template_id . '&action=copy_to') . '">' . tep_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a>',
          );
          $contents[] = array(
            'class' => 'calign', 
            'text' => implode('', $buttons),
          );
          $contents[] = array('text' => TEXT_INFO_SUBJECT);
          $contents[] = array('text' => $tInfo->template_subject);
        } else { // create template dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('class' => 'calign', 'text' => '<a href="' . tep_href_link($g_script, 'action=new_template') . '">' . tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW_TEMPLATE) . '</a>');
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
