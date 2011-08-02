<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Plugins main Initialization and Processing script
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
  require('includes/application_top.php');
  require_once(DIR_FS_FUNCTIONS . 'compression.php');
  require_once(DIR_FS_CLASSES . 'pkzip.php');
  require_once(DIR_FS_CLASSES . 'pkunzip.php');
  require_once(DIR_FS_CLASSES . 'plug_manager.php');

  $dir_array = array_filter(glob(DIR_FS_PLUGINS . '*'), 'is_dir');

  $last_id = 0;

  $plugins_array = array();
  $plugins_compressed_array = array();

  foreach($dir_array as $key => $value) {
    $value = preg_replace('/\s\s+/', ' ', trim($value));
    $value = preg_replace("/[^0-9a-z\-_\/\:]+/i", '_', strtolower($value));

    // Get rid of pssible delete errors due to dirs left open
    $install_file = $value . '/install.php';
    $name = basename($value);
    if( !is_dir($value) ) continue;
    if( !is_file($install_file) ) {
      $compressed_file = $value.'/'.$name.'.zip';
      if( is_file($compressed_file) ) {
        $plugins_compressed_array[$name] = array('file' => $compressed_file, 'path' => $value);
      }
      continue;
    }

    if( !is_file($install_file) ) continue;

    closedir(opendir($value));
    require($value . '/install.php');

    $class = PLUGINS_INSTALL_PREFIX . $name;
    if( !class_exists($class) ) continue;

    $plugins_array[$name] = new $class;
  }

  $plgID = (isset($_GET['plgID']) ? $g_db->prepare_input($_GET['plgID']) : '');
  $cplgID = (isset($_GET['cplgID']) ? $g_db->prepare_input($_GET['cplgID']) : '');

  if( !empty($plgID) && isset($plugins_array[$plgID]) ) {
    $plugin = $plugins_array[$plgID];
  } elseif( empty($cplgID) || !isset($plugins_compressed_array[$cplgID]) ) {
    $action = 'info';
  }

  switch($action) {
    case 'edit':
      if( !$plugin->is_installed() ) {
        //$messageStack->add(WARNING_PLUGIN_EDIT_NOT_INSTALLED, 'warning');
        $action = 'info';
      }
      break;
    case 'edit_confirm':
      $plugin->change($_POST['change_status'], $_POST['sort_id']);
      $messageStack->add_session(WARNING_PLUGIN_STATUS_CHANGE, 'warning');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    case 'copy_front':
      break;
    case 'copy_front_confirm':
      if( !$plugin->is_installed() ) {
        $messageStack->add_session(WARNING_PLUGIN_EDIT_NOT_INSTALLED, 'error');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action','plgID','cplgID') ));
      }
      $plugin->re_copy_front();
      $messageStack->add(sprintf(WARNING_PLUGIN_FILES_COPIED, $g_db->input($plugin->title)), 'warning');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action','cplgID') ));
      break;

    case 'revert_files':
      break;
    case 'revert_files_confirm':
      if( !$plugin->is_installed() ) {
        $messageStack->add_session(WARNING_PLUGIN_EDIT_NOT_INSTALLED, 'error');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action','plgID','cplgID') ));
      }
      $plugin->revert_files();
      //$messageStack->add(sprintf(WARNING_PLUGIN_FILES_REVERTED, $plugin->title), 'warning');
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action','cplgID') ));
      break;

    case 'remove':
      break;
    case 'remove_confirm':
      if( $plugin->is_installed() ) {
        $plugin->uninstall();
      }
      $plugin->remove_admin_plugin();
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action','plgID','cplgID') ));
      break;
    case 'install':
      if( $plugin->is_installed() ) {
        $messageStack->add(WARNING_PLUGIN_ALREADY_INSTALLED, 'warning');
      }
      break;
    case 'install_confirm':
      if( !$plugin->install_progress ) {
        $plugin->install();
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      } else {
        $action = 'install_display';
      }
      break;
    case 'uninstall':
      if( !$plugin->is_installed() ) {
        $messageStack->add(WARNING_PLUGIN_EDIT_NOT_INSTALLED, 'warning');
      }
      break;
    case 'uninstall_confirm':
      $plugin->uninstall();
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    case 'set_options':
      if( !$plugin->is_installed() || !method_exists($plugin, 'set_options') ) {
        $messageStack->add_session(WARNING_PLUGIN_NOT_CONFIGURABLE, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      break;
    case 'process_options':
      if( !$plugin->is_installed() || !method_exists($plugin, 'process_options') ) {
        $messageStack->add_session(WARNING_PLUGIN_NOT_CONFIGURABLE, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      $plugin->process_options();
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      break;
    case 'decompress_confirm':
      $result_array = tep_decompress($plugins_compressed_array[$cplgID]['file'], $plugins_compressed_array[$cplgID]['path']);
      for($i=0, $j=count($result_array['messages']); $i<$j; $i++) {
        $messageStack->add_session($result_array['messages'][$i]);
      }
      tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action', 'plgID', 'cplgID') . 'plgID=' . $cplgID));
      break;
    case 'archive_confirm':
      $pdir = DIR_FS_PLUGINS.tep_trail_path($plugin->key);
      $zip_file = $pdir . $plugin->key . '.zip';
      $cZip = new pkzip;

      if( isset($_POST['store'] ) ) {
        $cZip->addDir($pdir);
      } else {
        $cZip->addDir($pdir, '', array());
      }
      $contents = $cZip->file();

      if( isset($_POST['store'] ) ) {
        tep_write_contents($zip_file, $contents);
      }

      $archive = $plugin->key;
      $filename =  $plugin->key . '.zip';
      header('Content-type: application/x-octet-stream');
      header('Content-disposition: attachment; filename=' . $filename);
      echo $contents;
      $g_session->close();
      break;
    case 'info':
      break;
    default:
      break;
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php
  if( $last_id > 0 ) {
    echo '  <meta http-equiv="refresh" content="5">' . "\n";
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  $cPlug = $g_plugins->get();

  $link = '';
  $plugin_args = array(
    'key' => $plgID,
    'action' => $action,
    'link' => &$link
  );
  $g_plugins->invoke('help_link', $plugin_args);

  if( empty($plugin_args['link']) ) {
    $help_title = $cPlug->get_system_help_title('plugins_list');
    $plugin_args['link'] = '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" title="' . $help_title . '" class="heading_help" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', $help_title) . '</a>';
  }

  if( $action == 'set_options' ) {
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo $plugin_args['link']; ?></div>
            <div><h1><?php echo HEADING_CONFIGURE_TITLE . '&nbsp;[' . $plugin->title . ']'; ?></h1></div>
          </div>
<?php
    $result = $plugin->set_options();
    if( !empty($result) ) {
      echo $result;
    }
?>
        </div>
<?php
  } elseif($action == 'install_display') {
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo $plugin_args['link']; ?></div>
            <div><h1><?php echo HEADING_INSTALL_TITLE . '&nbsp;[' . $plugin->title . ']'; ?></h1></div>
          </div>
          <div class="scroller" style="height: 500px;">
<?php
    $plugin = $plugins_array[$plgID];
    $plugin->install();

?>
          </div>
        </div>
<?php
  } elseif($action == 'install') {
     $plugin = $plugins_array[$plgID];
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo $plugin_args['link']; ?></div>
            <div><h1><?php echo HEADING_INSTALL_TITLE . '&nbsp;[' . $plugin->title . ']'; ?></h1></div>
          </div>
          <div class="blockCell blockpad comboHeading">
            <div class="floater rspacer"><?php echo tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM); ?></div>
            <div><h2><?php echo TEXT_INFO_INSTALL_NOTICE . '&nbsp;<b>' . $plugin->title . '</b>'; ?></h2></div>
            <div><?php echo TEXT_INFO_INSTALL_WARN; ?></div>
          </div>
          <div class="formArea cleaner"><?php echo tep_draw_form('install', $g_script, tep_get_all_get_params('action', 'cplgID') . 'action=install_confirm', 'post'); ?>
<?php
    // Get plugin additional pre-install options
    $result = $plugin->pre_install();
    $buttons_array = array(
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'cplgID')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'
    );
    if( $result ) {
      if( count($plugin->files_array) || count($plugin->admin_files_array) || count($plugin->front_strings_array) ) {
?>
            <div class="linepad lineCell"><b><?php echo TEXT_INFO_NEW_FILES; ?></b></div>
            <table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_FILE; ?></th>
              </tr>
<?php
        $rows = 0;
        if( !empty($plugin->files_array) ) {
?>
              <tr class="dataTableRowAlt2">
                <td class="heavy"><?php echo TEXT_INFO_FRONT_FILES; ?></td>
              </tr>
<?php
          foreach($plugin->files_array as $key => $value ) {
            $rows++;
            $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
            echo '                      <tr class="' . $row_class . '">';
?>
                <td><?php echo $value; ?></td>
              </tr>
<?php
          }
        }
        if( !empty($plugin->front_strings_array) ) { 
?>
              <tr class="dataTableRowAlt2">
                <td class="heavy"><?php echo sprintf(TEXT_INFO_FRONT_STRINGS, $plugin->title); ?></td>
              </tr>
<?php
          foreach($plugin->front_strings_array as $key => $value ) {
            $tmp_array = $g_lng->get_string_file_path($plugin->key, $value);
            for( $i=0, $j=count($tmp_array); $i<$j; $i++) {
              $rows++;
              $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
              echo '                      <tr class="' . $row_class . '">';
?>
                <td><?php echo $tmp_array[$i]; ?></td>
              </tr>
<?php
            }
          }
        }
        if( !empty($plugin->admin_files_array) ) {
?>
              <tr class="dataTableRowAlt2">
                <td class="heavy"><?php echo TEXT_INFO_ADMIN_FILES; ?></td>
              </tr>
<?php
          foreach($plugin->admin_files_array as $key => $value ) {
            $rows++;
            $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
            echo '                      <tr class="' . $row_class . '">';
?>
                <td><?php echo $value; ?></td>
              </tr>
<?php
          }
        }
?>
            </table>
<?php
      }
      $buttons_array[] = tep_image_submit('button_confirm.gif', IMAGE_CONFIRM);
    } else {
      echo '<div style="color: #F00; font-weight: bold;">' . TEXT_INFO_PREINSTALL_FAILED . '</div>';
    }
?>
            <div class="formButtons"><?php echo implode('',$buttons_array); ?></div>
          </form></div>
        </div>
<?php
  } elseif($action == 'uninstall') {
    $plugin = $plugins_array[$plgID];
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo $plugin_args['link']; ?></div>
            <div><h1><?php echo HEADING_UNINSTALL_TITLE . '&nbsp;[' . $plugin->title . ']'; ?></h1></div>
          </div>
          <div class="blockCell2 blockpad comboHeading">
            <div class="floater rspacer"><?php echo tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM); ?></div>
            <div><h2><?php echo TEXT_INFO_UNINSTALL_NOTICE . '&nbsp;<b>' . $plugin->title . '</b>'; ?></h2></div>
            <div><?php echo TEXT_INFO_UNINSTALL_WARN; ?></div>
          </div>
          <div class="formArea cleaner"><?php echo tep_draw_form('uninstall', $g_script, tep_get_all_get_params('action', 'cplgID') . 'action=uninstall_confirm', 'post'); ?>
<?php
    $plugin->pre_uninstall();
    if( count($plugin->files_array) || count($plugin->admin_files_array) ) {
?>
            <div class="linepad lineCell"><b><?php echo TEXT_INFO_REMOVE_FILES; ?></b></div>
            <table class="tabledata">
              <tr class="dataTableHeadingRow">
                <th><?php echo TABLE_HEADING_FILE; ?></th>
              </tr>
<?php
      $rows = 0;
      if( !empty($plugin->files_array) ) {
?>
              <tr class="dataTableRowAlt2">
                <td class="heavy"><?php echo TEXT_INFO_FRONT_FILES; ?></td>
              </tr>
<?php
        foreach($plugin->files_array as $key => $value ) {
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          echo '                      <tr class="' . $row_class . '">';
?>
                <td><?php echo $value; ?></td>
              </tr>
<?php
        }
      }
      if( !empty($plugin->front_strings_array) ) { 
?>
              <tr class="dataTableRowAlt2">
                <td class="heavy"><?php echo sprintf(TEXT_INFO_FRONT_STRINGS, $plugin->title); ?></td>
              </tr>
<?php
        foreach($plugin->front_strings_array as $key => $value ) {
          $tmp_array = $g_lng->get_string_file_path($plugin->key, $value);
          for( $i=0, $j=count($tmp_array); $i<$j; $i++) {
            $rows++;
            $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
            echo '                      <tr class="' . $row_class . '">';
?>
                <td><?php echo $tmp_array[$i]; ?></td>
              </tr>
<?php
          }
        }
      }
      if( !empty($plugin->admin_files_array) ) {
?>
              <tr class="dataTableRowAlt2">
                <td class="heavy"><?php echo TEXT_INFO_ADMIN_FILES; ?></td>
              </tr>
<?php
        foreach($plugin->admin_files_array as $key => $value ) {
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          echo '                      <tr class="' . $row_class . '">';
?>
                <td><?php echo $value; ?></td>
              </tr>
<?php
        }
      }
?>
            </table>
<?php
    }
    $buttons_array = array(
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'cplgID')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
      tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
    );
?>
            <div class="linepad lineCell hideover">
              <div class="floater rspacer"><?php echo tep_draw_checkbox_field('database', 1, true); ?></div>
              <div class="floater textadj"><?php echo TEXT_INFO_DATABASE_BACKUP; ?></b></div>
            </div>
            <div class="linepad lineCell hideover">
              <div class="floater rspacer"><?php echo tep_draw_checkbox_field('zip', 1, true); ?></div>
              <div class="floater textadj"><?php echo TEXT_INFO_REMOVE_MAKE_ZIP; ?></b></div>
            </div>
            <div class="formButtons"><?php echo implode('',$buttons_array); ?></div>
          </form></div>
        </div>
<?php
  } elseif($action == 'revert_files') {
     $plugin = $plugins_array[$plgID];
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo $plugin_args['link']; ?></div>
            <div><h1><?php echo HEADING_REVERT_TITLE . '&nbsp;[' . $plugin->title . ']'; ?></h1></div>
          </div>
          <div class="blockCell blockpad comboHeading">
            <div class="floater rspacer"><?php echo tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM); ?></div>
            <div><h2><?php echo TEXT_INFO_REVERT_NOTICE . '&nbsp;<b>' . $plugin->title . '</b>'; ?></h2></div>
            <div><?php echo TEXT_INFO_REVERT_WARN; ?></div>
          </div>
          <div class="formArea cleaner"><?php echo tep_draw_form('revert_files', $g_script, tep_get_all_get_params('action', 'cplgID') . 'action=revert_files_confirm', 'post'); ?>
<?php
    // Get plugin additional pre-install options
    $result = $plugin->pre_revert();
    $buttons_array = array(
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'cplgID')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'
    );
    if( $result ) {
      $files_array = array_merge($plugin->admin_files_array, $plugin->files_array, $plugin->template_array);
      if( count($files_array) ) {
?>
          <div class="linepad lineCell"><b><?php echo TEXT_INFO_REVERT_FILES_OVER; ?></b></div>
          <div class="formArea"><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_FILE_FROM; ?></th>
              <th><?php echo TABLE_HEADING_FILE_INTO; ?></th>
            </tr>
<?php
        $rows = 0;
        $path = DIR_WS_PLUGINS . tep_trail_path($plugin->key);
        foreach($files_array as $key => $value ) {
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          echo '                      <tr class="' . $row_class . '">';
?>
              <td><?php echo $value; ?></td>
              <td><?php echo $path . $key; ?></td>
            </tr>
<?php
        }
        if( isset($plugin->options_array['strings']) && !empty($plugin->front_strings_array) ) { 
          foreach($plugin->front_strings_array as $value ) {
            $tmp_array = $g_lng->get_string_file_path($plugin->key, $value);
            $lng_array = array_values($g_lng->languages);
            for( $i=0, $j=count($tmp_array); $i<$j; $i++) {
              $rows++;
              $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
              echo '                      <tr class="' . $row_class . '">';
?>
              <td><?php echo $tmp_array[$i]; ?></td>
              <td><?php echo $path . tep_trail_path($plugin->options_array['strings']) . tep_trail_path($lng_array[$i]['language_path']) . $value; ?></td>
            </tr>
<?php
            }
          }
        }
?>
          </table></div>
          <div class="linepad lineCell hideover">
            <div class="floater"><?php echo tep_draw_checkbox_field('database', 1, false, 'id="database_file"'); ?></div>
            <label class="lpad" for="database_file"><?php echo TEXT_INFO_DATABASE_BACKUP; ?></label>
          </div>
          <div class="linepad lineCell hideover">
            <div class="floater"><?php echo tep_draw_checkbox_field('zip', 1, false, 'id="zip_file"'); ?></div>
            <label class="lpad" for="zip_file"><?php echo TEXT_INFO_CREATE_ZIP; ?></label>
          </div>
<?php
      }
      $buttons_array[] = tep_image_submit('button_confirm.gif', IMAGE_CONFIRM);
    } else {
      echo '<div style="color: #F00; font-weight: bold;">' . TEXT_INFO_PRECOPY_FAILED . '</div>';
    }
    echo '<div class="formButtons">' . implode('',$buttons_array) . '</div>';
?>
          </form></div>
        </div>
<?php
  } elseif($action == 'copy_front') {
     $plugin = $plugins_array[$plgID];
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo $plugin_args['link']; ?></div>
            <div><h1><?php echo HEADING_COPY_TITLE . '&nbsp;[' . $plugin->title . ']'; ?></h1></div>
          </div>
          <div class="blockCell2 blockpad comboHeading">
            <div class="floater rspacer"><?php echo tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM); ?></div>
            <div><h2><?php echo TEXT_INFO_COPY_NOTICE . '&nbsp;<b>' . $plugin->title . '</b>'; ?></h2></div>
            <div><?php echo TEXT_INFO_COPY_WARN; ?></div>
          </div>
          <div class="formArea cleaner"><?php echo tep_draw_form('copy_front', $g_script, tep_get_all_get_params('action', 'cplgID') . 'action=copy_front_confirm', 'post'); ?>
<?php
    // Get plugin additional pre-install options
    $result = $plugin->pre_copy_front();
    $buttons_array = array(
      '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'cplgID')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'
    );
    if( $result ) {
      $files_array = array_merge($plugin->admin_files_array, $plugin->files_array, $plugin->template_array);
      if( count($files_array) ) {
?>
          <div class="linepad lineCell"><b><?php echo TEXT_INFO_COPY_FILES_OVER; ?></b></div>
          <table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_FILE_FROM; ?></th>
              <th><?php echo TABLE_HEADING_FILE_INTO; ?></th>
            </tr>
<?php
        $rows = 0;
        $path = DIR_WS_PLUGINS.$plugin->key.'/';
        foreach($files_array as $key => $value ) {
          $rows++;
          $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
          echo '                      <tr class="' . $row_class . '">';
?>
              <td><?php echo $path.$key; ?></td>
              <td><?php echo $value; ?></td>
            </tr>
<?php
        }
        if( !empty($plugin->front_strings_array) ) { 
          foreach($plugin->front_strings_array as $value ) {
            $tmp_array = $g_lng->get_string_file_path($plugin->key, $value);
            $lng_array = array_values($g_lng->languages);
            for( $i=0, $j=count($tmp_array); $i<$j; $i++) {
              $rows++;
              $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
              echo '                      <tr class="' . $row_class . '">';
?>
              <td><?php echo $path . tep_trail_path($plugin->options_array['strings']) . tep_trail_path($lng_array[$i]['language_path']) . $value; ?></td>
              <td><?php echo $tmp_array[$i]; ?></td>
            </tr>
<?php
            }
          }
        }
?>
          </table>
<?php
      }
      $buttons_array[] = tep_image_submit('button_confirm.gif', IMAGE_CONFIRM);
    } else {
      echo '<div style="color: #F00; font-weight: bold;">' . TEXT_INFO_PRECOPY_FAILED . '</div>';
    }
    echo '<div class="formButtons">' . implode('', $buttons_array) . '</div>';
?>
          </form></div>
        </div>
<?php
  } elseif($action == 'remove') {
     $plugin = $plugins_array[$plgID];
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo $plugin_args['link']; ?></div>
            <div><h1 style="color: #F00;"><?php echo HEADING_REMOVE_TITLE . '&nbsp;[' . $plugin->title . ']'; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div class="floater rspacer"><?php echo tep_image(DIR_WS_IMAGES . 'critical_notice.png', IMAGE_CONFIRM); ?></div>
            <div><h2><?php echo TEXT_INFO_REMOVE_NOTICE . '&nbsp;<b>' . $plugin->title . '</b>'; ?></h2></div>
            <div><?php echo TEXT_INFO_REMOVE_WARN; ?></div>
          </div>
          <div class="formArea cleaner vlinepad"><?php echo tep_draw_form('remove', $g_script, tep_get_all_get_params('action') . 'action=remove_confirm', 'post'); ?>
<?php
     echo '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
     echo tep_image_submit('button_confirm.gif', IMAGE_CONFIRM);
?>
          </form></div>
        </div>
<?php
  } else {
?>
        <div class="maincell">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo $plugin_args['link']; ?></div>
            <div>
<?php
    if( isset($plugins_compressed_array[$cplgID]) ) {
      $ptitle = TEXT_INFO_CODE_CLASS . '&nbsp;' . $cplgID;
    } elseif ( isset($plugins_array[$plgID]) ) {
      $ptitle = $plugins_array[$plgID]->title;
    } else {
      $ptitle = '';
    }

    if( empty($ptitle) ) {
      $ptitle = '<h1>' . HEADING_TITLE . '</h1>';
    } else {
      $ptitle = '<h1>' . HEADING_TITLE . '&nbsp;[' . $ptitle . ']</h1>';
    }
    echo $ptitle; 
?>
            </div>
          </div>
          <div class="comboHeading">
            <div class="dataTableRowAlt4 colorblock floater"><?php echo TEXT_INFO_BOTH_USE; ?></div>
            <div class="dataTableRowAlt3 colorblock floater"><?php echo TEXT_INFO_FRONT_USE; ?></div>
            <div class="dataTableRowAlt2 colorblock floater"><?php echo TEXT_INFO_ADMIN_USE; ?></div>
            <div class="dataTableRow colorblock floater"><?php echo TEXT_INFO_COMPRESS_USE; ?></div>
            <div class="dataTableRowSelected colorblock floater"><?php echo TEXT_INFO_SELECTED; ?></div>
          </div>
          <div class="formArea"><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_NAME; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_STATUS; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_VERSION; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_FRAMEWORK; ?></th>
              <th><?php echo TABLE_HEADING_AUTHOR; ?></th>
              <th class="calign"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>

<?php
    $rows = 0;
    foreach($plugins_array as $key => $plugin) {
      $rows++;
      $row_class = 'dataTableRowHigh';

      if( $plugin->front && $plugin->back ) {
        $row_class = 'dataTableRowAlt4';
      } elseif($plugin->front) {
        $row_class = 'dataTableRowAlt3';
      } elseif($plugin->back) {
        $row_class = 'dataTableRowAlt2';
      }

      if( $plgID == $key ) {
        echo '              <tr class="dataTableRowSelected">' . "\n";
      } else {
        echo '              <tr class="' . $row_class . ' row_link"  href="' . tep_href_link($g_script, 'plgID=' . $key . '&action=info' . $configuration['configuration_id']) . '">';
      }
?>
              <td><?php echo $plugin->title; ?></td>
<?php
      if( $plugin->is_installed() ) {
        if( $plugin->is_enabled() ){
          echo '                  <td class="calign" style="background:#2FCE2F; color: #000;"><a href="' . tep_href_link($g_script, 'plgID=' . $plugin->key . '&action=edit') . '">' . TEXT_ENABLED . '</a></td>' . "\n";
        } else {
          echo '                  <td class="calign" style="background:#FF9F9F; color: #000;"><a href="' . tep_href_link($g_script, 'plgID=' . $plugin->key . '&action=edit') . '">' . TEXT_DISABLED . '</a></td>' . "\n";
        }
      } else {
        echo '                    <td class="calign">' . TEXT_INFO_NA . '</td>' . "\n";
      }
        
?>
              <td class="transtwenties calign"><?php echo $plugin->version; ?></td>
              <td class="calign"><?php echo $plugin->framework; ?></td>
              <td class="transtwenties"><?php echo $plugin->author; ?></td>
              <td class="tinysep ralign">
<?php
      if( $plugin->is_installed() ) {
        echo '<a href="' . tep_href_link($g_script, 'plgID=' . $key . '&action=uninstall') . '">' . tep_image(DIR_WS_ICONS . 'icon_minus.png', TEXT_INFO_UNINSTALL . ' ' . $plugin->title) . '</a>';
        if( method_exists($plugin, 'set_options') ) {
          echo '<a href="' . tep_href_link($g_script, 'plgID=' . $key . '&action=set_options') . '">' . tep_image(DIR_WS_ICONS . 'icon_configure.png', TEXT_INFO_CONFIGURE . ' ' . $plugin->title) . '</a>';
        }
        echo '<a href="' . tep_href_link($g_script, 'plgID=' . $key . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'icon_edit.png', TEXT_INFO_EDIT . ' ' . $plugin->title) . '</a>';
        echo '<a href="' . tep_href_link($g_script, 'plgID=' . $key . '&action=copy_front') . '">' . tep_image(DIR_WS_ICONS . 'icon_copy.png', TEXT_INFO_COPY_FILES . ' ' . $plugin->title) . '</a>';
        echo '<a href="' . tep_href_link($g_script, 'plgID=' . $key . '&action=revert_files') . '">' . tep_image(DIR_WS_ICONS . 'icon_revert.png', TEXT_INFO_REVERT_FILES . ' ' . $plugin->title) . '</a>';
      } else {
        echo '<a href="' . tep_href_link($g_script, 'plgID=' . $key . '&action=install') . '">' . tep_image(DIR_WS_ICONS . 'icon_plus.png', TEXT_INFO_INSTALL . ' ' . $plugin->title) . '</a>';
      }
      //echo '<a href="' . tep_href_link($g_script, 'plgID=' . $key . '&action=remove') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_INFO_REMOVE . ' ' . $plugin->title) . '</a>';
      if( $plgID == $key ) {
        echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', $plugin->title . ' ' . TEXT_SELECTED); 
      } else {
        echo '<a href="' . tep_href_link($g_script, 'plgID=' . $key . '&action=info') . '">' . tep_image(DIR_WS_ICONS . 'icon_info.png', TEXT_INFO . ' ' . $plugin->title) . '</a>';
      }
?>
              </td>
            </tr>
<?php
    }

    if( count($plugins_compressed_array) ) {
?>
            <tr class="dataTableHeadingRow">
              <th colspan="6"><?php echo TABLE_HEADING_COMPRESSED; ?></th>
            </tr>
<?php
      foreach($plugins_compressed_array as $key => $zip_info) {
        $inf_link = tep_href_link($g_script, 'cplgID=' . $key . '&action=decompress');
        if( $cplgID == $key ) {
          echo '              <tr class="dataTableRowSelected">' . "\n";
        } else {
          echo '              <tr class="dataTableRow row_link" href="' . $inf_link . '">' . "\n";
        }
?>
              <td><?php echo $key; ?></td>
              <td colspan="4" class="transtwenties"><?php echo DIR_WS_PLUGINS . tep_trail_path($key) . basename($zip_info['file']); ?></td>
              <td class="ralign">
<?php
        if( $cplgID == $key ) {
          echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.png', $key . ' ' . TEXT_SELECTED); 
        } else {
          echo '<a href="' . $inf_link . '">' . tep_image(DIR_WS_ICONS . 'icon_decompress.png', sprintf(TEXT_INFO_DECOMPRESS, basename($zip_info['file']), DIR_WS_PLUGINS . $key)) . '</a>'; 
        }
?>
              </td>
            </tr>
<?php
      }
    }
?>
          </table></div>
          <div class="listArea splitLine">
            <div><?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_ENTRIES, min(1, $rows), $rows, $rows); ?></div>
          </div>
        </div>
<?php
    $heading = array();
    $contents = array();
    switch($action) {
      case 'archive':
        $plugin = $plugins_array[$plgID];
        $heading[] = array('text' => TEXT_INFO_ARCHIVE_TITLE . ' <b>' . $plugin->title . '</b>');
        $contents[] = array('form' => tep_draw_form('archive_plugin', $g_script, 'plgID=' . $plugin->key . '&action=archive_confirm'));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'archive.png', IMAGE_CONFIRM) );
        $contents[] = array('text' => sprintf(TEXT_INFO_ARCHIVE_INTRO, '<b>' . $plugin->title . '</b>') );
        $contents[] = array('text' => tep_draw_checkbox_field('store', 1, false, 'id="archive_store"') . '<label class="charsep" for="archive_store">' . TEXT_INFO_ARCHIVE_STORE . '</label>');
        $contents[] = array(
          'class' => 'calign', 
          'text' => tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'
        );
        break;

      case 'decompress':
        $zip_array = $plugins_compressed_array[$cplgID];
        $heading[] = array('text' => TEXT_INFO_DECOMPRESS_TITLE . ' <b>' . $cplgID . '</b>');
        $contents[] = array('form' => tep_draw_form('decompress_plugin', $g_script, 'cplgID=' . $cplgID . '&action=decompress_confirm'));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'decompress_notice.png', IMAGE_CONFIRM) );
        $contents[] = array('text' => TEXT_INFO_DECOMPRESS_INTRO);
        $contents[] = array('text' => '<b>' . $zip_array['file'] . '</b>');
        $contents[] = array('text' => TEXT_INFO_INTO_FOLDER);
        $contents[] = array('text' => '<b>' . DIR_WS_PLUGINS . $cplgID . '</b>');
        $contents[] = array(
          'class' => 'calign', 
          'text' => tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'
        );
        break;
      case 'edit':
        $plugin = $plugins_array[$plgID];
        $options_array = array(
          array('id' => 0, 'text' => TEXT_DISABLED),
          array('id' => 1, 'text' => TEXT_ENABLED),
        );

        $data_array = $plugin->load_options();

        $heading[] = array('text' => '<b>' . $plugin->title . '</b>');
        $contents[] = array('form' => tep_draw_form('edit_plugin', $g_script, 'plgID=' . $plugin->key . '&action=edit_confirm'));
        $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'final_notice.png', IMAGE_CONFIRM) );
        $contents[] = array('text' => TEXT_INFO_EDIT_PLUGIN_INTRO);
        $contents[] = array('text' => '<b>' . TEXT_INFO_CURRENT_STATUS . '</b><br />' . tep_draw_pull_down_menu('change_status', $options_array, ($plugin->is_enabled())?1:0 ));

        if( $plugin->front && isset($data_array['fscripts']) && !empty($data_array['fscripts']) ) {
          $fscripts = '<div class="lpad">' . implode('</div><div class="lpad">', $data_array['fscripts']) . '</div>';
          $contents[] = array('class' => 'heavy', 'text' => TEXT_INFO_FRONT_SCRIPTS . $fscripts);
        }

        if( $plugin->back && isset($data_array['ascripts']) && !empty($data_array['ascripts']) ) {
          $ascripts =  '<div class="lpad">' . implode('</div><div class="lpad">', $data_array['ascripts']) . '</div>';
          $contents[] = array('class' => 'heavy', 'text' => TEXT_INFO_ADMIN_SCRIPTS . $ascripts);
        }

        $contents[] = array('text' => '<b>' . TEXT_INFO_SORT_ORDER . '</b><br />' . tep_draw_input_field('sort_id', $data_array['sort_id'], 'size="2"'));

        $buttons = array(
          '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'plgID') . 'plgID=' . $plugin->key) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>',
          tep_image_submit('button_confirm.gif', IMAGE_CONFIRM),
        );
        $contents[] = array(
          'class' => 'calign', 
          'text' => implode('', $buttons)
        );
        break;

      default:
        if( $rows > 0 && isset($plugins_array[$plgID]) ) {
          $plugin_text = '';
          $plugin = $plugins_array[$plgID];
          $heading[] = array('text' => '<b>' . $plugin->title . '</b>');

          if( isset($plugin->icon) && !empty($plugin->icon) && is_file(DIR_FS_PLUGINS . $plugin->key . '/' . $plugin->icon) ) {
            $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_PLUGINS . $plugin->key . '/' . $plugin->icon, $plugin->title) );
          }

          $buttons = array();
          if( $plugin->is_installed() && method_exists($plugin, 'set_options') ) {
            $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'plgID') . 'plgID=' . $plugin->key . '&action=set_options') . '">' . tep_image_button('button_options.gif', TEXT_INFO_OPTIONS) . '</a>';
          }
          if( $plugin->is_installed() ) {
            $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'plgID') . 'plgID=' . $plugin->key . '&action=edit') . '">' . tep_image_button('button_edit.gif', TEXT_INFO_BASIC_SETTINGS) . '</a>';
            $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'plgID') . 'plgID=' . $plugin->key . '&action=copy_front') . '">' . tep_image_button('button_copy.gif', TEXT_INFO_COPY_FILES) . '</a>';
          } else {
            $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'plgID') . 'plgID=' . $plugin->key . '&action=install') . '">' . tep_image_button('button_install.gif', TEXT_INFO_INSTALL) . '</a>';
          }
          $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'plgID') . 'plgID=' . $plugin->key . '&action=archive') . '">' . tep_image_button('button_archive.gif', TEXT_INFO_ARCHIVE) . '</a>';
          $buttons[] = '<a href="' . tep_href_link($g_script, tep_get_all_get_params('action', 'plgID') . 'plgID=' . $plugin->key . '&action=remove') . '">' . tep_image_button('button_remove.gif', TEXT_INFO_REMOVE) . '</a>';

          $contents[] = array(
            'class' => 'calign', 
            'text' => implode('', $buttons)
          );

          $contents[] = array('text' => TEXT_INFO_KEY . '<br /><b>' . DIR_WS_ADMIN . DIR_WS_PLUGINS . $plugin->key . '/</b>');

          $tmp_array = array();
          if( $plugin->front ) $tmp_array[] = TEXT_INFO_WEB_FRONT;
          if( $plugin->back ) $tmp_array[] = TEXT_INFO_ADMIN;
          $plugin_text = TEXT_INFO_SIDE . '<br /><b>' . implode('<br />', $tmp_array) . '</b>';
          $contents[] = array('text' => $plugin_text);
          $contents[] = array('text' => TEXT_INFO_CURRENT_STATUS . '<br /><b>' . ($plugin->is_installed()?TEXT_ENABLED:TEXT_DISABLED) . '</b>');
          $contents[] = array('text' => TEXT_INFO_CODE_CLASS . '<br /><b>' . $plugin->key . '</b>');

          $plugin_text = $plugin->help;
          if( empty($plugin_text) ) {
            $plugin_text = TEXT_INFO_NO_HELP;
          } else {
            $plugin_text = '<b>' . TEXT_INFO_SYNOPSIS . '</b><br />' . $plugin_text;
          }
          $contents[] = array('class' => 'infoBoxSection', 'section' => '<div>');
          $contents[] = array('text' => $plugin_text);
          $contents[] = array('section' => '</div>');
        } else { // create generic_text dummy info
          $heading[] = array('text' => '<b>' . EMPTY_GENERIC . '</b>');
          $contents[] = array('class' => 'calign', 'text' => tep_image(DIR_WS_IMAGES . 'invalid_entry.png', TEXT_INFO_EMPTY));
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
