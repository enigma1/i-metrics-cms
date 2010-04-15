<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
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

  $action = (isset($_GET['action']) ? $g_db->prepare_input($_GET['action']) : '');
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
    $g_db->query("truncate table " . TABLE_SEO_CACHE . "");
  }

  switch( $action ) {
    case 'setflag':
      tep_set_generic_text_status($gtID, $_GET['flag']);
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'flag'))));
      break;
    case 'setsub':
      tep_set_generic_sub_status($gtID, $_GET['sub']);
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action', 'sub'))));
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
      tep_redirect(tep_href_link($g_script));
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
        $g_db->perform(TABLE_GTEXT, $sql_data_array);
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
      require_once(DIR_WS_CLASSES . 'seo_zones.php');
      $cSEO = new seo_zones();
      $seo_name = $cSEO->create_safe_string($seo_name);
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
      tep_redirect(tep_href_link($g_script, 'gtID=' . $gtext_id));
      break;
    case 'copy_to_confirm':
      if( isset($_POST['gtext_id']) && tep_not_null($_POST['gtext_id']) ) {
        $gtext_id = $g_db->prepare_input($_POST['gtext_id']);
        if( $_POST['copy_as'] == 'duplicate') {
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
      }

      tep_redirect(tep_href_link($g_script, 'gtID=' . $gtext_id));
      break;

    case 'generic_text_preview':
      break;
    default:
      break;
  }

  $g_media[] = '<link rel="stylesheet" type="text/css" href="includes/javascript/jquery/themes/smoothness/ui.all.css">';
  $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery-1.3.2.js"></script>';
  $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.ajaxq.js"></script>';
  $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/jquery.form.js"></script>';
  $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jquery/ui/jquery-ui-1.7.2.custom.js"></script>';
  $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/zones_control.js"></script>';
  if ($action == 'new_generic_text' || $action == 'update_generic_text') {
    $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/tiny_mce.js"></script>';
    $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/config.js"></script>';
    $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/image_control.js"></script>';
    $g_media[] = '<link rel="stylesheet" type="text/css" href="includes/javascript/jscalendar/calendar-win2k-1.css">';
    $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jscalendar/calendar.js"></script>';
    $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jscalendar/lang/calendar-en.js"></script>';
    $g_media[] = '<script language="javascript" type="text/javascript" src="includes/javascript/jscalendar/calendar-setup.js"></script>';
  }
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php
  if ($action == 'new_generic_text' || $action == 'update_generic_text') {
    $set_calendar = '1';
?>
<script language="javascript" type="text/javascript">
$(document).ready(function(){
  var jqWrap = tinymce_ifc;
  // Initialize JS variables with PHP parameters to be passed to the js file
  jqWrap.TinyMCE = '<?php echo $g_relpath . DIR_WS_INCLUDES . 'javascript/tiny_mce/tiny_mce.js'; ?>';
  // Point the basefront relative to the admin server to have absolute paths where applicable
  jqWrap.baseFront = '<?php echo $g_server . DIR_WS_CATALOG; ?>';
  jqWrap.cssFront = '<?php echo $g_crelpath . 'stylesheet.css'; ?>';
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.areas = 'gtext_description';
  jqWrap.launch();

  var jqWrap = image_control;
  jqWrap.editObject = tinyMCE;
  jqWrap.baseFront = '<?php echo $g_server . DIR_WS_CATALOG; ?>';
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.launch();

  var jqWrap = zones_control;
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.launch();
});
</script>
<script language="javascript" type="text/javascript">
  function init_calendar() {
Calendar.setup( { inputField : "date_added", ifFormat : "%m/%d/%Y", button : "start_trigger" } );
  }
</script>
<?php
  } elseif( empty($action) ) {
?>
<script language="javascript" type="text/javascript">
$(document).ready(function(){
  var jqWrap = zones_control;
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.launch();
});
</script>
<?php
  }
?>
<?php
  $set_focus = true;
  require('includes/objects/html_start_sub2.php'); 

  if ($action == 'new_generic_text') {
    $parameters = array(
      'gtext_id' => '',
      'gtext_title' => '',
      'gtext_description' => '',
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
          <div class="comboHeading">
            <div class="pageHeading" style="float: left;"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
<?php
    if( !empty($gtID) ) {
      $form_action = 'gtID=' . $gtID . '&action=update_generic_text'; 
    } else {
      $form_action = 'action=insert_generic_text'; 
    }
?>
          <div class="formArea"><?php echo tep_draw_form('form_generic_text', $g_script, $form_action, 'post', 'enctype="multipart/form-data"'); ?><table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td><div class="listArea" style="padding-bottom: 8px;"><table border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <th colspan="4"><?php echo TEXT_GENERIC_STATUS; ?></th>
                </tr>
                <tr>
                  <td><?php echo tep_draw_radio_field('status', '0', $out_status); ?></td>
                  <td><?php echo TEXT_GENERIC_NOT_AVAILABLE; ?></td>
                  <td><?php echo tep_draw_radio_field('status', '1', $in_status); ?></td>
                  <td><?php echo TEXT_GENERIC_AVAILABLE; ?></td>
                </tr>
                <tr>
                  <th colspan="4"><?php echo TEXT_GENERIC_SUB; ?></th>
                </tr>
                <tr>
                  <td><?php echo tep_draw_radio_field('sub', '0', $out_sub); ?></td>
                  <td><?php echo TEXT_GENERIC_NOT_AVAILABLE; ?></td>
                  <td><?php echo tep_draw_radio_field('sub', '1', $in_sub); ?></td>
                  <td><?php echo TEXT_GENERIC_AVAILABLE; ?></td>
                </tr>
              </table></div></td>
            </tr>
            <tr>
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <td><?php echo TEXT_DATE_ADDED . '&nbsp;' . ((isset($gtInfo->date_added) && tep_mysql_to_time_stamp($gtInfo->date_added) > 0 )?tep_date_short($gtInfo->date_added):''); ?></td>
                  <td><?php echo tep_draw_input_field('date_added', (isset($gtInfo->date_added) && tep_mysql_to_time_stamp($gtInfo->date_added) > 0 )?tep_date_short($gtInfo->date_added):'', 'size="12" maxlength="12" id="date_added"'); ?></td>
                  <td><?php echo '<a href="#">' . tep_image(DIR_WS_ICONS . 'scheduled.gif', 'Date Entry Added', '','','id="start_trigger"') . '</a>'; ?></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                  <th><?php echo TEXT_GENERIC_NAME; ?></th>
                </tr>
                <tr>
                  <td class="smallText"><?php echo tep_draw_input_field('gtext_title', (isset($gtext_title)?$gtext_title:$gtInfo->gtext_title), 'size="70"'); ?></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <th><?php echo TEXT_GENERIC_DESCRIPTION; ?></th>
                </tr>
                <tr>
                  <td><?php echo tep_draw_textarea_field('gtext_description', 'soft', '70', '15', (isset($gtext_description)?$gtext_description:$gtInfo->gtext_description)); ?></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <td><b><?php echo TEXT_INFO_INSERT_IMAGES . ':'; ?></b></td>
                  <td><?php echo '<a href="#" id="image_selection">' . tep_image(DIR_WS_ICONS . 'icon_images_head.png', TEXT_INFO_INSERT_IMAGES) . '</a>'; ?></td>
                  <td><?php echo tep_draw_separator('pixel_trans.gif', '30', '1'); ?></td>
                  <td><b><?php echo TEXT_INFO_UPLOAD_IMAGES . ':'; ?></b></td>
                  <td><?php echo '<a href="#" id="image_upload">' . tep_image(DIR_WS_ICONS . 'icon_upload_head.png', TEXT_INFO_UPLOAD_IMAGES) . '</a>'; ?></td>
                </tr>
              </table></td>
            </tr>
<?php
    if( !empty($gtID) ) {
?>
            <tr class="dataTableRowGreenLite">
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <th><?php echo TEXT_GENERIC_ZONES; ?></td>
                </tr>
                <tr>
                  <td><div class="target_abstract_zones">
<?php
      $zones_query = $g_db->query("select az.abstract_zone_id, az.abstract_zone_name from " . TABLE_GTEXT_TO_DISPLAY . " g2d left join " . TABLE_ABSTRACT_ZONES . " az on (g2d.abstract_zone_id=az.abstract_zone_id) left join " . TABLE_ABSTRACT_TYPES . " at on (at.abstract_types_id = az.abstract_types_id) where at.abstract_types_class='generic_zones' and g2d.gtext_id = '" . (int)$gtID . "'");
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
                </tr>
              </table></td>
            </tr>
<?php
    }
//-MS- SEO-G Added
?>
            <tr>
              <td><div class="listArea"><table width="100%" border="0" cellspacing="0" cellpadding="2">
                <tr bgcolor="#ffffeb">
                  <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
                    <tr>
                      <td class="smallText"><?php echo '<b>' . TEXT_SEO_SECTION . '</b>'; ?></td>
                    </tr>
                    <tr>
                      <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '4'); ?></td>
                    </tr>
                    <tr>
                      <td class="smallText"><?php echo TEXT_SEO_NAME; ?></td>
                    </tr>
                    <tr>
                      <td class="smallText"><?php echo tep_draw_input_field('seo_name', $gtInfo->seo_name); ?></td>
                    </tr>
                    <tr>
                      <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '4'); ?></td>
                    </tr>
<?php
//-MS- SEO-G Added EOM
?>
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
                    <tr>
                      <td class="smallText"><?php echo TEXT_META_TITLE; ?></td>
                    </tr>
                    <tr>
                      <td class="smallText"><?php echo tep_draw_input_field('meta_title', $metag_title, 'size="62"'); ?></td>
                    </tr>
                    <tr>
                      <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '4'); ?></td>
                    </tr>
                    <tr>
                      <td class="smallText"><?php echo TEXT_META_KEYWORDS; ?></td>
                    </tr>
                    <tr>
                      <td class="smallText"><?php echo tep_draw_textarea_field('meta_keywords', 'soft', '70', '2', $metag_keywords); ?></td>
                    </tr>
                    <tr>
                      <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '4'); ?></td>
                    </tr>
                    <tr>
                      <td class="smallText"><?php echo TEXT_META_TEXT; ?></td>
                    </tr>
                    <tr>
                      <td class="smallText"><?php echo tep_draw_textarea_field('meta_text', 'soft', '70', '2', $metag_text); ?></td>
                    </tr>
                  </table></td>
                </tr>
              </table></div></td>
            </tr>
<?php
//-MS- META-G Added EOM
?>
            <tr>
              <td class="formButtons">
<?php 
    if( !empty($gtID) ) {
      $submit = tep_image_submit('button_update.gif', IMAGE_UPDATE);
      $cancel = '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'gtID')) . 'gtID=' . $gtID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
    } else {
      $submit = tep_image_submit('button_insert.gif', IMAGE_INSERT);
      $cancel = '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'gtID'))) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
    }
    echo $cancel . '&nbsp;' . $submit;
?>
              </td>
            </tr>
          </table></form></div>
        </div>
<?php
  } elseif( !empty($gtID) && $action == 'generic_text_preview' ) {
    $generic_text_query = $g_db->query("select gtext_id, gtext_title, gtext_description, status, sub from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtID . "'");
    $generic_text = $g_db->fetch_array($generic_text_query);
    $gtInfo = new objectInfo($generic_text, false);
?>
        <div class="maincell wider">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php  echo $gtInfo->gtext_title; ?></h1></div>
          </div>
          <div><?php echo $gtInfo->gtext_description; ?></div>
        </div>
<?php
  } else {
    $search = '';
    if( isset($_GET['search']) && !empty($_GET['search']) ) {
      $search = $g_db->prepare_input($_GET['search']);
    }
?>
        <div class="maincell">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div class="smallText" style="float: left;">
<?php
    echo tep_draw_form('search', $g_script, '', 'get');
    echo TEXT_TITLE_SEARCH . '&nbsp;' . tep_draw_input_field('search');
    $params_string = tep_get_all_get_params(array('action', 'search', 'page'));
    $params_array = tep_get_string_parameters($params_string);
    foreach($params_array as $key => $value ) {
      echo tep_draw_hidden_field($key, $value);
    }
    echo '</form>' . "\n";
?>
            </div>
            <div class="smallText" style="float: left; padding-left: 20px;">
<?php
    echo tep_draw_form("filter", $g_script, '', 'get'); 
    echo TEXT_TITLE_FILTER . '&nbsp;' . tep_draw_pull_down_menu('filter_id', $filter_array, $filter_id, 'onchange="this.form.submit()"');

    $params_string = tep_get_all_get_params(array('action', 'search', 'filter_id', 'page'));
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
            <div style="float: right;"><?php if (!isset($_GET['search'])) echo '<a href="' . tep_href_link($g_script, 'action=new_generic_text') . '">' . tep_image_button('button_new.gif', IMAGE_NEW_GENERIC_TEXT) . '</a>'; ?></div>
          </div>
          <div class="bounder">
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
          <div class="splitLine">
            <div style="float: left;"><?php echo $generic_text_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $generic_text_split->display_links(tep_get_all_get_params(array('page'))); ?></div>
          </div>
          <div class="listArea"><table class="tabledata" cellspacing="1">
              <tr class="dataTableHeadingRow">
                <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 's_sort_id')) . 's_sort_id=' . $sortID) . '">' . TABLE_HEADING_ID . '</a>'; ?></th>
                <th><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 's_sort_id')) . 's_sort_id=' . $sortTitle) . '">' . TABLE_HEADING_TITLE . '</a>'; ?></th>
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

      if( isset($gtInfo) && is_object($gtInfo) && ($generic_text['gtext_id'] == $gtInfo->gtext_id) ) {
        //echo '              <tr class="dataTableRowSelected" onclick="document.location.href=\'' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'gtID')) . 'gtID=' . $generic_text['gtext_id'] . '&action=new_generic_text') . '\'">' . "\n";
        echo '              <tr class="dataTableRowSelected">' . "\n";
      } else {
        //echo '              <tr class="dataTableRow" onclick="document.location.href=\'' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'gtID')) . 'gtID=' . $generic_text['gtext_id']) . '\'">' . "\n";
        echo '              <tr class="' . $row_class . '">' . "\n";
      }
?>
                <td><?php echo $generic_text['gtext_id']; ?></td>
                <td><?php echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params(array('action', 'gtID')) . 'gtID=' . $generic_text['gtext_id'] . '&action=new_generic_text') . '">' . $generic_text['gtext_title'] . '</a>'; ?></td>
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
                <td class="calign">
<?php
      if( $generic_text['status'] == '1' ) {
        echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '&nbsp;&nbsp;<a href="' . tep_href_link($g_script, 'action=setflag&flag=0&gtID=' . $generic_text['gtext_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
      } else {
        echo '<a href="' . tep_href_link($g_script, 'action=setflag&flag=1&gtID=' . $generic_text['gtext_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
      }
?>
                </td>
                <td class="calign">
<?php
      if( $generic_text['sub'] == '1' ) {
        echo tep_image(DIR_WS_ICONS . 'icon_status_green.png', IMAGE_ICON_STATUS_GREEN) . '&nbsp;&nbsp;<a href="' . tep_href_link($g_script, 'action=setsub&sub=0&gtID=' . $generic_text['gtext_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_red_light.png', IMAGE_ICON_STATUS_RED_LIGHT) . '</a>';
      } else {
        echo '<a href="' . tep_href_link($g_script, 'action=setsub&sub=1&gtID=' . $generic_text['gtext_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_status_green_light.png', IMAGE_ICON_STATUS_GREEN_LIGHT) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_ICONS . 'icon_status_red.png', IMAGE_ICON_STATUS_RED);
      }
?>
                </td>
                <td class="tinysep calign">
<?php
      echo '<a href="' . tep_href_link($g_script, 'gtID=' . $generic_text['gtext_id'] . '&action=delete_generic_text') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE . ' ' . $generic_text['gtext_title']) . '</a>';
      echo '<a href="' . tep_href_link($g_script, 'gtID=' . $generic_text['gtext_id'] . '&action=new_generic_text') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_EDIT . ' ' . $generic_text['gtext_title']) . '</a>';
      echo '<a href="' . tep_href_link($g_script, 'gtID=' . $generic_text['gtext_id'] . '&action=generic_text_preview&read=only') . '">' . tep_image(DIR_WS_ICONS . 'icon_preview.png', ICON_PREVIEW) . '</a>';
      if (isset($gtInfo) && is_object($gtInfo) && ($generic_text['gtext_id'] == $gtInfo->gtext_id)) { 
        echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', $generic_text['gtext_title'] . ' ' . TEXT_SELECTED);
      } else { 
        echo '<a href="' . tep_href_link($g_script, 'gtID=' . $generic_text['gtext_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', IMAGE_SELECT . ' ' . $generic_text['gtext_title']) . '</a>';
      }
?>
                </td>
              </tr>
<?php
    }
?>
          </table></div>
          <div class="splitLine">
            <div style="float: left;"><?php echo $generic_text_split->display_count(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></div>
            <div style="float: right;"><?php echo $generic_text_split->display_links(tep_get_all_get_params(array('page'))); ?></div>
          </div>
        </div>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'delete_generic_text':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_GENERIC . '</b>');

        $contents[] = array('form' => tep_draw_form('generic_text', $g_script, 'action=delete_generic_text_confirm') . tep_draw_hidden_field('gtext_id', $gtInfo->gtext_id));
        $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
        $contents[] = array('text' => TEXT_DELETE_GENERIC_INTRO);
        $contents[] = array('text' => '<b>' . $gtInfo->gtext_title . '</b>');
        $contents[] = array('params' => 'text-align: center', 'text' => tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link($g_script, 'gtID=' . $gtInfo->gtext_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'copy_to':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_COPY_TO . '</b>');
        $contents[] = array('form' => tep_draw_form('copy_to', $g_script, 'action=copy_to_confirm') . tep_draw_hidden_field('gtext_id', $gtInfo->gtext_id));
        $contents[] = array('params' => 'text-align: center', 'text' => tep_image(DIR_WS_IMAGES . 'copy_entry.png', IMAGE_COPY) );
        $contents[] = array('text' => TEXT_INFO_COPY_TO_INTRO);
        $contents[] = array('text' => tep_draw_input_field('gtext_title', $gtInfo->gtext_title));
        $contents[] = array('text' => tep_draw_hidden_field('copy_as', 'duplicate'));
        $contents[] = array('params' => 'text-align: center', 'text' => tep_image_submit('button_copy.gif', IMAGE_COPY) . ' <a href="' . tep_href_link($g_script, 'gtID=' . $gtInfo->gtext_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;

      default:
        if( $rows > 0 && isset($gtInfo) && is_object($gtInfo) ) {
          // Navigation History
          $g_plugins->invoke('add_current_page', $gtInfo->gtext_title, tep_get_all_get_params());

          $heading[] = array('text' => '<b>' . $gtInfo->gtext_title . '</b>');
          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link($g_script, 'gtID=' . $gtInfo->gtext_id . '&action=new_generic_text') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a><a href="' . tep_href_link($g_script, 'gtID=' . $gtInfo->gtext_id . '&action=delete_generic_text') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a><a href="' . tep_href_link($g_script, 'gtID=' . $gtInfo->gtext_id . '&action=copy_to') . '">' . tep_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a>');
          $contents[] = array('text' => tep_truncate_string($gtInfo->gtext_description));
        } else { // create generic_text dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('params' => 'text-align: center', 'text' => '<a href="' . tep_href_link($g_script, 'action=new_generic_text') . '">' . tep_image(DIR_WS_IMAGES . 'invalid_entry.png', IMAGE_NEW_GENERIC_TEXT) . '</a>');
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
          <div id="modalBox" title="Image Selection" style="display:none;">Loading...Please Wait</div>
          <div id="ajaxLoader" title="Image Manager" style="display:none;"><img src="includes/javascript/jquery/themes/smoothness/images/ajax_load.gif"><p id="ajaxMsg" class="main">Updating, please wait...</p><hr /></div>

<?php require('includes/objects/html_end.php'); ?>
