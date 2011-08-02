<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Generic Text Pages script
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

  $gtID = (isset($_GET['gtID']) ? (int)$_GET['gtID'] : '');

  if( $gtID > 0 ) {
    $check_query = $g_db->query("select count(*) as total from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtID . "'");
    $check_array = $g_db->fetch_array($check_query);
    if( !$check_array['total'] ) {
      $gtID = '';
    }
  }

  $filter_id = (isset($_GET['filter_id']) ? (int)$_GET['filter_id'] : 0);
  $s_sort_id = (isset($_GET['s_sort_id']) ? (int)$_GET['s_sort_id'] : '');

  $filter_array = array(
    array('id' => 0, 'text' => 'All Pages'),
    array('id' => 1, 'text' => 'Internal/Sub Pages'),
    array('id' => 2, 'text' => 'Complete Pages'),
    array('id' => 3, 'text' => 'Published Pages'),
    array('id' => 4, 'text' => 'Unpublished Pages'),
  );

  if( !empty($_POST) ) {
    $g_db->query("truncate table " . TABLE_SEO_CACHE);
  }
  $template_content = '';

  switch( $action ) {
    case 'change_wp':
      $g_wp_ifc = (isset($_GET['wp']) && $_GET['wp'] == 1)?true:false;
      $messageStack->add_session(WARNING_WP_CHANGED, 'warning');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=new_generic_text'));
      break;
    case 'setflag':
      tep_set_generic_text_status($gtID, $_GET['flag']);
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'flag')));
      break;
    case 'setsub':
      tep_set_generic_sub_status($gtID, $_GET['sub']);
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'sub')));
      break;
    case 'delete_generic_text_confirm':
      if( isset($_POST['gtext_id']) && !empty($_POST['gtext_id']) ) {
        $gtext_id = (int)$_POST['gtext_id'];
        $g_db->query("delete from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
        $g_db->query("delete from " . TABLE_GTEXT_TO_DISPLAY . " where gtext_id = '" . (int)$gtext_id . "'");
        $g_db->query("delete from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
        $g_db->query("delete from " . TABLE_META_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
        $messageStack->add_session(WARNING_TEXT_PAGE_REMOVED, 'warning');
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('gtID', 'action') ));
      break;
    case 'insert_generic_text':
    case 'update_generic_text':
      if( empty($_POST['gtext_title']) ) {
        $messageStack->add(ERROR_PAGE_TITLE_EMPTY);
        $action = 'new_generic_text';
        break;
      }
      if( empty($_POST['gtext_description']) ) {
        $messageStack->add(ERROR_PAGE_DESCRIPTION_EMPTY);
        $action = 'new_generic_text';
        break;
      }

      require_once(DIR_FS_CLASSES . 'seo_url.php');
      $cLink = new seoURL;

      $sql_data_array = array(
        'gtext_title' => $g_db->prepare_input($_POST['gtext_title']),
        'gtext_description' => $g_db->prepare_input($_POST['gtext_description']),
        'date_added' => 'now()',
        'sub' => (int)$_POST['sub'],
        'status' => (int)$_POST['status'],
      );

      if( !empty($gtID) ) {
        $gtext_id = $gtID;
        $messageStack->add_session(SUCCESS_TEXT_PAGE_UPDATED, 'success');
      } elseif($action == 'insert_generic_text') {
        $result = $g_db->perform(TABLE_GTEXT, $sql_data_array);
        $gtext_id = $g_db->insert_id();
        $messageStack->add_session(SUCCESS_TEXT_PAGE_CREATED, 'success');
      } else {
        tep_redirect(tep_href_link($g_script));
      }

      //-MS- SEO-G Added
      $seo_clear = false;
      if(tep_not_null($_POST['seo_name']) ) {
        $seo_name = $_POST['seo_name'];
      } else {
        $seo_name = $_POST['gtext_title'];
        $seo_clear = true;
      }
      $seo_name = $cLink->create_safe_string($seo_name);
      $seog_array = array('gtext_id' => (int)$gtext_id, 'seo_name' => $g_db->prepare_input($seo_name) );
      //-MS- SEO-G Added EOM

      //-MS- META-G Added
      $metag_title_array = $_POST['meta_title'];
      $metag_keywords_array = $_POST['meta_keywords'];
      $metag_text_array = $_POST['meta_text'];
      $metag_array = array(
        'meta_title' => (tep_not_null($metag_title_array) ? $g_db->prepare_input($metag_title_array) : $g_db->prepare_input($_POST['gtext_title'])),
        'meta_keywords' => (tep_not_null($metag_keywords_array) ? $g_db->prepare_input($metag_keywords_array) : $g_db->prepare_input($_POST['gtext_title'])),
        'meta_text' => (tep_not_null($metag_text_array) ? $g_db->prepare_input($metag_text_array) : $g_db->prepare_input($_POST['gtext_title'])),
      );
      //-MS- META-G Added EOM

      $date_added = $g_db->prepare_input($_POST['date_added']);
      if (tep_not_null($date_added)) {
        list($month, $day, $year) = explode('/', $date_added);
        $date_added = $year .
          ((strlen($month) == 1) ? '0' . $month : $month) .
          ((strlen($day) == 1) ? '0' . $day : $day);

        $sql_data_array['date_added'] = $g_db->prepare_input($date_added);
      }

      if($action == 'insert_generic_text') {
        if( !$seo_clear ) {
          //-MS- SEO-G Added
          $g_db->perform(TABLE_SEO_TO_GTEXT, $seog_array);
          //-MS- SEO-G Added EOM

          //-MS- META-G Added
          $insert_sql_data = array(
            'gtext_id' => (int)$gtext_id,
          );

          $metag_array = array_merge($metag_array, $insert_sql_data);
          $g_db->perform(TABLE_META_GTEXT, $metag_array);
          //-MS- META-G Added EOM
        } else {
          $g_db->query("delete from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
          $g_db->query("delete from " . TABLE_META_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
        }
      } elseif ($action == 'update_generic_text') {
        $g_db->perform(TABLE_GTEXT, $sql_data_array, 'update', "gtext_id = '" . (int)$gtext_id . "'");
        if( !$seo_clear ) {
          //-MS- SEO-G Added
          $check_query = $g_db->query("select gtext_id from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
          if( !$g_db->num_rows($check_query) ) {
            $g_db->perform(TABLE_SEO_TO_GTEXT, $seog_array);
          } else {
            unset($seog_array['gtext_id']);
            $g_db->perform(TABLE_SEO_TO_GTEXT, $seog_array, 'update', "gtext_id = '" . (int)$gtext_id . "'");
          }
          if( isset($_POST['seo_name_force']) ) {
            if( !$cLink->generate_text_link($gtext_id) ) {
              $messageStack->add_session(WARNING_SEO_FRIENDLY_FAILED, 'warning');
            }
          }
          //-MS- SEO-G Added EOM

          //-MS- META-G Added
          $check_query = $g_db->query("select gtext_id from " . TABLE_META_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
          if( !$g_db->num_rows($check_query) ) {
            $metag_array['gtext_id'] = (int)$gtext_id;
            $g_db->perform(TABLE_META_GTEXT, $metag_array);
          } else {
            $g_db->perform(TABLE_META_GTEXT, $metag_array, 'update', "gtext_id = '" . (int)$gtext_id . "'");
          }
          //-MS- META-G Added EOM
        } else {
          $g_db->query("delete from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
          $g_db->query("delete from " . TABLE_META_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
        }
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('gtID', 'action') . 'gtID=' . $gtext_id));
      break;
    case 'copy_to_confirm':
      if( isset($_POST['gtext_id']) && tep_not_null($_POST['gtext_id']) ) {
        $gtext_id = $g_db->prepare_input($_POST['gtext_id']);
        $generic_text_query = $g_db->query("select gtext_title, gtext_description from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
        $generic_text = $g_db->fetch_array($generic_text_query);
        if( isset($_POST['gtext_title']) && !empty($_POST['gtext_title']) ) {
          $generic_text['gtext_title'] = $g_db->filter($_POST['gtext_title']);
        }
        $sql_data_array = array(
          'gtext_title' => $generic_text['gtext_title'],
          'gtext_description' => $generic_text['gtext_description'],
          'status' => 0,
        );

        $g_db->perform(TABLE_GTEXT, $sql_data_array);
        $gtext_id = $g_db->insert_id();
      }

      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('gtID', 'action') . 'gtID=' . $gtext_id));
      break;

    case 'generic_text_preview':
      break;
    case 'template_upload':
      $cFile = new upload('template_file');
      if( !$cFile->parse() || !tep_read_contents($cFile->tmp_filename, $template_content) ) {
        $messageStack->add_session(ERROR_TEMPLATE_FILE_READ);
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') . 'action=new_generic_text'));
      }
      $messageStack->add(SUCCESS_TEXT_PAGE_TEMPLATE, 'success');
      $action = 'new_generic_text';
      extract(tep_load('defs'));
      $cDefs->action = 'new_generic_text';
      break;
    default:
      break;
  }

  if ($action == 'new_generic_text' || $action == 'update_generic_text') {
    $set_calendar = '1';
  }
?>
<?php require(DIR_FS_INCLUDES . 'objects/html_start_sub1.php'); ?>
<?php require(DIR_FS_INCLUDES . 'objects/html_start_sub2.php'); ?>
<?php
  $cPlug = $g_plugins->get();
  if ($action == 'new_generic_text') {
    $help_title = $cPlug->get_system_help_title('generic_text_edit');

    $parameters = array(
      'gtext_id' => '',
      'gtext_title' => '',
      'gtext_description' => $template_content,
      'date_added' => '',
      'sub' => '',
      'status' => '',
      'seo_name' => '',
    );

    $gtInfo = new objectInfo($parameters);

    if( !empty($gtID) ) {
      $generic_text_query = $g_db->query("select gtext_id, gtext_title, gtext_description, status, date_added, sub from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtID . "'");
      $generic_text = $g_db->fetch_array($generic_text_query);

      //-MS- SEO-G Added
      $seog_query = $g_db->query("select seo_name from " . TABLE_SEO_TO_GTEXT . " where gtext_id = '" . (int)$gtID . "'");
      if( !$g_db->num_rows($seog_query) ) {
        $seog_array = array('seo_name' => '');
      } else {
        $seog_array = $g_db->fetch_array($seog_query);
      }
      $generic_text = array_merge($generic_text, $seog_array);
      //-MS- SEO-G Added EOM

      //-MS- META-G Added
      $metag_query = $g_db->query("select meta_title, meta_keywords, meta_text from " . TABLE_META_GTEXT . " where gtext_id = '" . (int)$gtID . "'");
      if( !$g_db->num_rows($metag_query) ) {
        $metag_array = array('meta_title' => '', 'meta_keywords' => '', 'meta_text' => '');
      } else {
        $metag_array = $g_db->fetch_array($metag_query);
      }
      $generic_text = array_merge($generic_text, $metag_array);
      //-MS- META-G Added EOM

      $generic_text['gtext_description'] .= $template_content;
      $gtInfo->objectInfo($generic_text, false);

      // Navigation History
      $g_plugins->invoke('add_current_page', $gtInfo->gtext_title, tep_get_all_get_params());
    }

    if( empty($gtInfo->status)) $gtInfo->status = '1';
    switch ($gtInfo->status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }
    if( empty($gtInfo->sub) ) $gtInfo->sub = '0';
    switch ($gtInfo->sub) {
      case '0': $in_sub = false; $out_sub = true; break;
      case '1':
      default: $in_sub = true; $out_sub = false;
    }
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=generic_text_edit') . '" title="' . $help_title . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', $help_title) . '</a>'; ?></div>
            <div class="floater"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
<?php
    $form_action = $template_action = tep_get_all_get_params('action','gtID');
    if( !empty($gtID) ) {
      $form_action .= 'gtID=' . $gtID . '&action=update_generic_text'; 
      $template_action .= 'gtID=' . $gtID . '&action=template_upload';
    } else {
      $form_action .= 'action=insert_generic_text'; 
      $template_action .= 'action=template_upload';
    }
?>
          <div class="formArea"><?php echo tep_draw_form('form_template_text', $g_script, $template_action, 'post', 'enctype="multipart/form-data"'); ?><fieldset><legend><?php echo HEADING_TITLE_UPLOAD; ?></legend>
            <div class="bounder infile vmargin">
              <label class="floater"><?php echo TEXT_INFO_TEMPLATE_FILE; ?></label>
              <div class="floater lspacer"><?php echo tep_draw_file_field('template_file'); ?></div>
            </div>
            <div class="formButtons"><?php echo tep_image_submit('button_upload.gif', IMAGE_UPLOAD); ?></div>
          </fieldset></form></div>
          <div class="formArea"><?php echo tep_draw_form('form_generic_text', $g_script, $form_action, 'post', 'enctype="multipart/form-data"'); ?>
            <div class="mainInput"><fieldset><legend><?php echo HEADING_TEXT_EDIT; ?></legend>
              <div><label for="gtext_title"><?php echo TEXT_GENERIC_NAME; ?></label></div>
              <div class="rpad"><?php echo tep_draw_input_field('gtext_title', (isset($gtext_title)?$gtext_title:$gtInfo->gtext_title), 'id="gtext_title"'); ?></div>
              <div class="tmargin"><label class="floater"><?php echo TEXT_GENERIC_DESCRIPTION; ?></label></div>
              <div class="floatend">
<?php
    if( $g_session->register('g_wp_ifc') ) {
      echo '<a class="dataTableContentRed" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=change_wp&wp=0') . '">' . TEXT_INFO_DISABLE_WP . '</a>';
    } else {
      echo '<a class="dataTableContentRed" href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'wp') . 'action=change_wp&wp=1') . '">' . TEXT_INFO_ENABLE_WP . '</a>';
    }
?>
              </div>
              <div class="bounder"><?php echo tep_draw_textarea_field('gtext_description', $gtInfo->gtext_description, '', '15'); ?></div>
              <div class="formButtons inimg tmargin">
                <label class="floater"><?php echo TEXT_INFO_INSERT_IMAGES . ':'; ?></label>
                <div class="floater rspacer"><?php echo '<a href="#" id="image_selection">' . tep_image(DIR_WS_ICONS . 'icon_images_head.png', TEXT_INFO_INSERT_IMAGES) . '</a>'; ?></div>
                <label class="floater"><?php echo TEXT_INFO_UPLOAD_IMAGES . ':'; ?></label>
                <div class="floater"><?php echo '<a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a>'; ?></div>
<?php
    $templates_query_raw = "select template_id as id, template_title as text from " . TABLE_TEMPLATES . " where group_id = '" . TEMPLATE_CONTENT_GROUP . "' order by template_title";
    $templates_array = $g_db->query_to_array($templates_query_raw);
    if( count($templates_array) ) {
?>
                <div class="floatend">
<?php
      echo '<label for="template_list" class="floater">' . TEXT_INFO_TEMPLATES . '</label>';
      echo '<div class="floater hpad" style="margin-top: 4px;">' . tep_draw_pull_down_menu('template_list', $templates_array, '', 'id="template_list" style="width: auto"') . '</div>';
      echo '<div class="floater"><a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=template') . '" id="set_template">' . tep_image(DIR_WS_ICONS . 'icon_arrow_up.png', TEXT_INFO_INSERT_TEMPLATE) . '</a></div>';
      echo '<div class="floater rspacer"><a href="' . tep_href_link($g_script, tep_get_all_get_params('action') . 'action=template') . '" id="view_template" target="_blank" title="' . TEXT_INFO_VIEW_TEMPLATE . '">' . tep_image(DIR_WS_ICONS . 'icon_question.png', TEXT_INFO_VIEW_TEMPLATE) . '</a></div>';
?>
                </div>
<?php
    }
?>
              </div>
              <div class="cleaner tmargin"><label><?php echo TEXT_GENERIC_ZONES; ?></label></div>
              <div class="target_abstract_zones">
<?php
      $zones_query = $g_db->query("select az.abstract_zone_id, az.abstract_zone_name from " . TABLE_GTEXT_TO_DISPLAY . " g2d left join " . TABLE_ABSTRACT_ZONES . " az on (g2d.abstract_zone_id=az.abstract_zone_id) left join " . TABLE_ABSTRACT_TYPES . " at on (at.abstract_types_id = az.abstract_types_id) where at.abstract_types_class='generic_zones' and g2d.gtext_id = '" . (int)$gtID . "'");
      $zones_link_array = array();
      if( $g_db->num_rows($zones_query) ) {
      
        while($zones_array = $g_db->fetch_array($zones_query) ) {
          $zones_link_array[] = '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $zones_array['abstract_zone_id'] . '&action=list') . '" title="' . $zones_array['abstract_zone_name'] . '" class="list_abstract_zones" attr="' . $generic_text['gtext_id'] . '">' . $zones_array['abstract_zone_name'] . '</a>';
        }
      } else {
        $zones_link_array[] = '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES) . '" class="list_abstract_zones" attr="' . $gtInfo->gtext_id . '"><b style="color: #FF0000;">' . TEXT_INFO_ZONES_NOT_ASSIGNED . '</b></a>';
      }
      echo implode('<br />', $zones_link_array);
?>
              </div>
              <div class="formButtons tmargin">
<?php
    $buttons = array();
    if( !empty($gtID) ) {
      $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'gtID') . 'gtID=' . $gtID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
      $buttons[] = tep_image_submit('button_update.gif', IMAGE_UPDATE);
    } else {
      $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'gtID')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
      $buttons[] = tep_image_submit('button_insert.gif', IMAGE_INSERT);
    }
    echo implode('', $buttons);
?>
              </div>
            </fieldset></div>
            <div class="bounder sectionAlt"><fieldset><legend><?php echo HEADING_TEXT_FIELDS; ?></legend>
              <div><?php echo TEXT_GENERIC_STATUS; ?></div>
              <div>
                <?php echo tep_draw_radio_field('status', '0', $out_status, 'id="out_status"'); ?><label for="out_status"><?php echo TEXT_GENERIC_NOT_AVAILABLE; ?></label>
                <?php echo tep_draw_radio_field('status', '1', $in_status, 'id="in_status"'); ?><label for="in_status"><?php echo TEXT_GENERIC_AVAILABLE; ?></label>
              </div>
              <div class="tmargin"><?php echo TEXT_GENERIC_SUB; ?></div>
              <div>
                <?php echo tep_draw_radio_field('sub', '0', $out_sub, 'id="out_sub"'); ?><label for="out_sub"><?php echo TEXT_GENERIC_NOT_AVAILABLE; ?></label>
                <?php echo tep_draw_radio_field('sub', '1', $in_sub, 'id="in_sub"'); ?><label for="in_sub"><?php echo TEXT_GENERIC_AVAILABLE; ?></label>
              </div>
              <div class="tmargin"><?php echo TEXT_DATE_ADDED; ?></div>
              <label for="date_added_label"><?php echo ((isset($gtInfo->date_added) && tep_mysql_to_time_stamp($gtInfo->date_added) > 0 )?tep_date_short($gtInfo->date_added):''); ?></label>
<?php 
    echo tep_draw_input_field('date_added', (isset($gtInfo->date_added) && tep_mysql_to_time_stamp($gtInfo->date_added) > 0 )?tep_date_short($gtInfo->date_added):'', 'size="12" maxlength="12" class="date_added" id="date_added_label"'); ?>
<?php
/*
              <?php echo '<a href="#">' . tep_image(DIR_WS_ICONS . 'scheduled.gif', 'Date Entry Added', '','','id="start_trigger" style="vertical-align:middle;"') . '</a>'; ?>
*/
?>
            </fieldset></div>
            <div class="bounder sectionLight"><fieldset><legend><?php echo HEADING_SEO_FIELDS; ?></legend>
              <div><label for="seo_name"><?php echo TEXT_SEO_NAME; ?></label></div>
              <div class="rpad"><?php echo tep_draw_input_field('seo_name', $gtInfo->seo_name, 'class="wider" id="seo_name"'); ?></div>
              <div class="vlinepad"><?php echo tep_draw_checkbox_field('seo_name_force', 1, false, 'id="seo_name_force"'); ?><label for="seo_name_force" class="lspacer"><?php echo TEXT_SEO_NAME_FORCE; ?></label></div>
<?php
//-MS- META-G Added
      if( !isset($metag_title_array) ) {
        $metag_query = $g_db->query("select meta_title, meta_keywords, meta_text from " . TABLE_META_GTEXT . " where gtext_id = '" . (int)$gtInfo->gtext_id .  "'");
        if( $metag_array = $g_db->fetch_array($metag_query) ) {
          $metag_title = stripslashes($metag_array['meta_title']);
          $metag_keywords = stripslashes($metag_array['meta_keywords']);
          $metag_text = stripslashes($metag_array['meta_text']);
        } else {
          $metag_title = '';
          $metag_keywords = '';
          $metag_text = '';
        }
      } else {
        $metag_title = stripslashes($metag_title_array);
        $metag_keywords = stripslashes($metag_keywords_array);
        $metag_text = stripslashes($metag_keywords_array);
      }
?>
              <div class="tmargin"><label for="meta_title"><?php echo TEXT_META_TITLE; ?></label></div>
              <div class="rpad"><?php echo tep_draw_input_field('meta_title', $metag_title, 'class="wider" id="meta_title"'); ?></div>
              <div class="tmargin"><label for="meta_keywords"><?php echo TEXT_META_KEYWORDS; ?></label></div>
              <div class="rpad"><?php echo tep_draw_textarea_field('meta_keywords', $metag_keywords, '', '4', 'id="meta_keywords"'); ?></div>
              <div class="tmargin"><label for="meta_text"><?php echo TEXT_META_TEXT; ?></label></div>
              <div class="rpad"><?php echo tep_draw_textarea_field('meta_text', $metag_text, '', '4', 'id="meta_text"'); ?></div>
            </fieldset></div>
          </form></div>
        </div>
<?php
  } elseif( !empty($gtID) && $action == 'generic_text_preview' ) {
    $generic_text_query = $g_db->query("select gtext_id, gtext_title, gtext_description, status, sub from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtID . "'");
    $generic_text = $g_db->fetch_array($generic_text_query);
    $gtInfo = new objectInfo($generic_text, false);
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div><h1><?php  echo $gtInfo->gtext_title; ?></h1></div>
          </div>
          <div><?php echo $gtInfo->gtext_description; ?></div>
        </div>
<?php
  } else {
    $help_title = $cPlug->get_system_help_title('generic_text_list');

    $search = '';
    if( isset($_GET['search']) && !empty($_GET['search']) ) {
      $search = $g_db->prepare_input($_GET['search']);
    }
?>
        <div class="maincell">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=generic_text_list') . '" title="' . $help_title . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', $help_title) . '</a>'; ?></div>
            <div class="floater"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div class="floater textadj rspacer">
<?php
    echo tep_draw_form('search', $g_script, '', 'get', 'id="gtext_search"');
    echo '<label for="text_search">' . TEXT_TITLE_SEARCH . '</label>' . tep_draw_input_field('search', '', 'size="40" id="text_search"');
    $params_string = tep_get_all_get_params('action', 'search', 'page') . 'action=search';
    $params_array = tep_get_string_parameters($params_string);
    foreach($params_array as $key => $value ) {
      echo tep_draw_hidden_field($key, $value);
    }
    echo '</form>' . "\n";
?>
            </div>
            <div class="floater textadj">
<?php
    echo tep_draw_form("filter", $g_script, '', 'get'); 
    echo '<label for="text_filter">' . TEXT_TITLE_FILTER . '</label>' . tep_draw_pull_down_menu('filter_id', $filter_array, $filter_id, 'class="change_submit" id="text_filter"');

    $params_string = tep_get_all_get_params('action', 'search', 'filter_id', 'page');
    $params_array = tep_get_string_parameters($params_string);
    foreach($params_array as $key => $value ) {
      echo tep_draw_hidden_field($key, $value);
    }

    //if( !empty($search) ) {
    //  echo tep_draw_hidden_field('search', $search);
    //}
    echo '</form>' . "\n";
?>
            </div>
            <div class="floatend"><?php if (!isset($_GET['search'])) echo '<a href="' . tep_href_link($g_script, 'action=new_generic_text') . '">' . tep_image_button('button_new.gif', IMAGE_NEW_GENERIC_TEXT) . '</a>'; ?></div>
          </div>
          <div class="comboHeading">
            <div class="dataTableRowAlt3 colorblock floater"><?php echo TEXT_INFO_FRONT_WHOLE; ?></div>
            <div class="dataTableRowAlt4 colorblock floater"><?php echo TEXT_INFO_FRONT_INTERNAL; ?></div>
            <div class="dataTableRow colorblock floater"><?php echo TEXT_INFO_FRONT_DISABLED; ?></div>
            <div class="dataTableRowSelected colorblock floater"><?php echo TEXT_INFO_FRONT_SELECTED; ?></div>
          </div>
<?php
    $generic_count = 0;
    $rows = 0;
    $sub_flag = 0;

    $filter_string = '';
    switch( $filter_id) {
      case 1;
        $filter_string = "sub = '1'";
        break;
      case 2;
        $filter_string = "sub = '0'";
        break;
      case 3;
        $filter_string = "status = '1'";
        break;
      case 4;
        $filter_string = "status = '0'";
        break;
      default: 
        break;
    }

    $sort_by = '';
    $sortID = 2;
    $sortTitle = 3;
    switch( $s_sort_id) {
      case 1;
        $sort_by = "gtext_id";
        break;
      case 2;
        $sortID = 1;
        $sort_by = "gtext_id desc";
        break;
      case 3;
        $sortTitle = 4;
        $sort_by = "gtext_title asc";
        break;
      case 4;
        $sort_by = "gtext_title desc";
        break;
      default:
        $sort_by = "gtext_title, gtext_id desc";
        break;
    }

    if( !empty($search) ) {
      if( !empty($filter_string) ) {
        $filter_string = "and " . $filter_string;
      }
      $sort_by = "order by " . $sort_by;
      $generic_text_query_raw = "select gtext_id, gtext_title, gtext_description, status, sub from " . TABLE_GTEXT . " where (gtext_title like '%" . $g_db->input($search) . "%' or gtext_description like '%" . $g_db->input($search) . "%') " . $filter_string . " " . $sort_by . "";
    } else {
      if( !empty($filter_string) ) {
        $filter_string = "where " . $filter_string;
      }
      $sort_by = "order by " . $sort_by;
      $generic_text_query_raw = "select gtext_id, gtext_title, gtext_description, status, sub from " . TABLE_GTEXT . " " . $filter_string . " "  . $sort_by . "";
    }

    $types_query = $g_db->query("select abstract_types_id from " . TABLE_ABSTRACT_TYPES . " where abstract_types_class='generic_zones'");
    $types_array = $g_db->fetch_array($types_query);

    $generic_text_split = new splitPageResults($generic_text_query_raw, GTEXT_PAGE_SPLIT);
    $generic_text_query = $g_db->query($generic_text_split->sql_query);
?>
          <div class="comboHeading">
            <div class="floater"><?php echo $generic_text_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $generic_text_split->display_links(tep_get_all_get_params('page')); ?></div>
          </div>
          <div class="formArea"><table class="tabledata" id="gtext_table">
            <tr class="dataTableHeadingRow">
              <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 's_sort_id') . 's_sort_id=' . $sortID) . '">' . TABLE_HEADING_ID . '</a>'; ?></th>
              <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 's_sort_id') . 's_sort_id=' . $sortTitle) . '">' . TABLE_HEADING_TITLE . '</a>'; ?></th>
              <th><?php echo TABLE_HEADING_PAGE_GROUPS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_SUB; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
<?php
    while( $generic_text = $g_db->fetch_array($generic_text_query) ) {
      $row_class = 'dataTableRow';
      if( $generic_text['sub'] == '1' && $generic_text['status'] == '1' ) {
        $row_class = 'dataTableRowAlt4';
      } elseif($generic_text['status'] == '1') {
        $row_class = 'dataTableRowAlt3';
      }

      if( !empty($gtID) && $gtID == $generic_text['gtext_id'] ) {
        $gtInfo = new objectInfo($generic_text);
      }
      $generic_count++;
      $rows++;

      $sel_link = tep_href_link($g_script, tep_get_all_get_params('gtID', 'action') . 'gtID=' . $generic_text['gtext_id'] . '&action=new_generic_text');
      $inf_link = tep_href_link($g_script, tep_get_all_get_params('gtID', 'action') . 'gtID=' . $generic_text['gtext_id']);

      if( isset($gtInfo) && is_object($gtInfo) && ($generic_text['gtext_id'] == $gtInfo->gtext_id) ) {
        echo '              <tr class="dataTableRowSelected row_link" href="' . $sel_link . '">' . "\n";
      } else {
        echo '              <tr class="' . $row_class . ' row_link" href="' . $inf_link . '">' . "\n";
      }
?>
              <td><?php echo $generic_text['gtext_id']; ?></td>
              <td class="transtwenties"><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'gtID') . 'gtID=' . $generic_text['gtext_id'] . '&action=new_generic_text') . '">' . $generic_text['gtext_title'] . '</a>'; ?></td>
              <td><div class="target_abstract_zones">
<?php
      $zones_query = $g_db->query("select az.abstract_zone_id, az.abstract_zone_name from " . TABLE_GTEXT_TO_DISPLAY . " g2d left join " . TABLE_ABSTRACT_ZONES . " az on (g2d.abstract_zone_id=az.abstract_zone_id) where az.abstract_types_id = '" . (int)$types_array['abstract_types_id'] . "' and g2d.gtext_id = '" . (int)$generic_text['gtext_id'] . "'");
      $zones_link_array = array();
      if( $g_db->num_rows($zones_query) ) {
      
        while($zones_array = $g_db->fetch_array($zones_query) ) {
          $zones_link_array[] = '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $zones_array['abstract_zone_id'] . '&action=list') . '" title="' . $zones_array['abstract_zone_name'] . '" class="list_abstract_zones" attr="' . $generic_text['gtext_id'] . '">' . $zones_array['abstract_zone_name'] . '</a>';
        }
      } else {
        $zones_link_array[] = '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES) . '" class="list_abstract_zones" attr="' . $generic_text['gtext_id'] . '"><b style="color: #FF0000;">' . TEXT_INFO_ZONES_NOT_ASSIGNED . '</b></a>';
      }
      echo implode('<br />', $zones_link_array);
?>
              </div></td>
              <td class="transtwenties medsep calign">
<?php
      if( $generic_text['status'] == '1' ) {
        echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '<a href="' . tep_href_link($g_script, 'action=setflag&flag=0&gtID=' . $generic_text['gtext_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
      } else {
        echo '<a href="' . tep_href_link($g_script, 'action=setflag&flag=1&gtID=' . $generic_text['gtext_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
      }
?>
              </td>
              <td class="medsep calign">
<?php
      if( $generic_text['sub'] == '1' ) {
        echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '<a href="' . tep_href_link($g_script, 'action=setsub&sub=0&gtID=' . $generic_text['gtext_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
      } else {
        echo '<a href="' . tep_href_link($g_script, 'action=setsub&sub=1&gtID=' . $generic_text['gtext_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
      }
?>
              </td>
              <td class="transtwenties tinysep calign">
<?php
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('gtID', 'action') . 'gtID=' . $generic_text['gtext_id'] . '&action=delete_generic_text') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $generic_text['gtext_title']) . '</a>';
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('gtID', 'action') . 'gtID=' . $generic_text['gtext_id'] . '&action=new_generic_text') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $generic_text['gtext_title']) . '</a>';
      echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('gtID', 'action') . 'gtID=' . $generic_text['gtext_id'] . '&action=generic_text_preview') . '">' . tep_image(DIR_WS_ICONS . 'icon_preview.png', ICON_PREVIEW) . '</a>';
      if (isset($gtInfo) && is_object($gtInfo) && ($generic_text['gtext_id'] == $gtInfo->gtext_id)) { 
        echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', $generic_text['gtext_title'] . ' ' . TEXT_SELECTED);
      } else { 
        echo '<a href="' . $inf_link . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_SELECT . ' ' . $generic_text['gtext_title']) . '</a>';
      }
?>
              </td>
            </tr>
<?php
    }
?>
          </table></div>
          <div class="listArea splitLine">
            <div class="floater"><?php echo $generic_text_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div class="floatend"><?php echo $generic_text_split->display_links(tep_get_all_get_params('page')); ?></div>
          </div>
        </div>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'delete_generic_text':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_GENERIC . '</b>');

        $contents[] = array('form' => tep_draw_form('generic_text', $g_script, 'action=delete_generic_text_confirm') . tep_draw_hidden_field('gtext_id', $gtInfo->gtext_id));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
        $contents[] = array('text' => TEXT_DELETE_GENERIC_INTRO);
        $contents[] = array('text' => '<b>' . $gtInfo->gtext_title . '</b>');
        $contents[] = array('class' => 'calign', 'text' => tep_image_submit('button_delete.gif', IMAGE_DELETE) . '<a href="' . tep_href_link($g_script, 'gtID=' . $gtInfo->gtext_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'copy_to':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_COPY_TO . '</b>');
        $contents[] = array('form' => tep_draw_form('copy_to', $g_script, 'action=copy_to_confirm') . tep_draw_hidden_field('gtext_id', $gtInfo->gtext_id));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'copy_entry.png', IMAGE_COPY) );
        $contents[] = array('text' => TEXT_INFO_COPY_TO_INTRO);
        $contents[] = array('class' => 'rpad', 'text' => tep_draw_input_field('gtext_title', $gtInfo->gtext_title, 'class="wider"'));
        $contents[] = array('class' => 'calign', 'text' => tep_image_submit('button_copy.gif', IMAGE_COPY) . '<a href="' . tep_href_link($g_script, 'gtID=' . $gtInfo->gtext_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;

      default:
        if( $rows > 0 && isset($gtInfo) && is_object($gtInfo) ) {
          // Navigation History
          $g_plugins->invoke('add_current_page', $gtInfo->gtext_title, tep_get_all_get_params());

          $heading[] = array('text' => '<b>' . $gtInfo->gtext_title . '</b>');
          $buttons = array(
            '<a href="' . tep_href_link($g_script, 'gtID=' . $gtInfo->gtext_id . '&action=new_generic_text') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>',
            '<a href="' . tep_href_link($g_script, 'gtID=' . $gtInfo->gtext_id . '&action=delete_generic_text') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>',
            '<a href="' . tep_href_link($g_script, 'gtID=' . $gtInfo->gtext_id . '&action=copy_to') . '">' . tep_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a>',
          );
          $contents[] = array(
            'class' => 'calign', 
            'text' => implode('', $buttons),
          );
          $contents[] = array('text' => tep_truncate_string($gtInfo->gtext_description));
        } else { // create generic_text dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('class' => 'calign', 'text' => '<a href="' . tep_href_link($g_script, 'action=new_generic_text') . '">' . tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW_GENERIC_TEXT) . '</a>');
          $contents[] = array('text' => TEXT_NO_GENERIC);
        }
        break;
    }

    if( !empty($heading) && !empty($contents) ) {
      echo '             <div class="rightcell">';
      //extract(tep_load('box'));
      //echo $cBox->infoBox($heading, $contents);
      $box = new box;
      echo $box->infoBox($heading, $contents);
      echo '             </div>';
    }
  }
?>
<?php require(DIR_FS_INCLUDES . 'objects/html_end.php'); ?>
