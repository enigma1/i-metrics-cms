<?php
  $copyright_string='
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: MultiSite Configuration script for Web-Front
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
';
  require('includes/application_top.php');
  $multi_filter = "/[^0-9a-z\-_]+/i";
  $multi_prefix = 'multi_';

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (isset($_POST['delete_multi_x']) || isset($_POST['delete_multi_y'])) $action='delete_multi';

  switch ($action) {
    case 'restart':
    case 'restart_confirm':
      $site = (isset($_GET['site']) ? strtolower(tep_create_safe_string($_GET['site'],'_', $multi_filter)) : '');
      $filename = DIR_WS_MODULES . $multi_prefix . $site . '.php';
      if( empty($site) || !file_exists($filename) ) {
        $messageStack->add_session(ERROR_SITE_CONFIG_INVALID);
        tep_redirect(tep_href_link($g_script));
      }
      if( $action == 'restart' ) break;

      require($filename);
      $contents = '<?php' . $copyright_string . "\n" . 
                  '  define(\'HTTP_CATALOG_SERVER\', \'' . $http_server . '\');' . "\n" .
                  '  define(\'HTTPS_CATALOG_SERVER\', \'' . $https_server . '\');' . "\n" .
                  '  define(\'ENABLE_SSL_CATALOG\', \'' . $site_ssl . '\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG\', \'' . $ws_path . '\');' . "\n" .
                  '  define(\'DIR_FS_CATALOG\', \'' . $fs_path . '\');' . "\n\n" .

                  '  define(\'DIR_WS_CATALOG_IMAGES\', DIR_WS_CATALOG . \'images/\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG_ICONS\', DIR_WS_CATALOG_IMAGES . \'images/\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG_BANNERS\', DIR_WS_CATALOG_IMAGES . \'images/\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG_STRINGS\', DIR_WS_CATALOG . \'includes/strings/\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG_MODULES\', DIR_WS_CATALOG . \'includes/modules/\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG_PLUGINS\', DIR_WS_CATALOG . \'includes/plugins/\');' . "\n" .
                  '  define(\'DIR_WS_CATALOG_TEMPLATE\', DIR_WS_CATALOG . \'includes/template/\');' . "\n\n" .

                  '  define(\'DB_SERVER\', \'' . $db_server . '\');' . "\n" .
                  '  define(\'DB_SERVER_USERNAME\', \'' . $db_username . '\');' . "\n" .
                  '  define(\'DB_SERVER_PASSWORD\', \'' . $db_password . '\');' . "\n" .
                  '  define(\'DB_DATABASE\', \'' . $db_database . '\');' . "\n" .
                  '  define(\'USE_PCONNECT\', \'false\');' . "\n" .
                  '  define(\'STORE_SESSIONS\', \'mysql\');' . "\n" . 
                  '?>' . "\n";

      $site_file = DIR_WS_INCLUDES . 'configure_site.php';
      if( !tep_write_contents(DIR_WS_INCLUDES . 'configure_site.php', $contents) ) {
        $messageStack->add_session (sprintf(ERROR_SITE_CONFIG_WRITE, DIR_FS_ADMIN . $site_file) );
        tep_redirect(tep_href_link($g_script));
      }
      $g_session->destroy();
      $g_db->query("truncate table " . TABLE_SESSIONS_ADMIN);
      header("HTTP/1.1 301");
      header('P3P: CP="NOI ADM DEV PSAi COM NAV STP IND"');
      header('Location: ' . $g_relpath);
      exit();
      break;
    case 'add':
      $config_name = (isset($_POST['config_name']) ? strtolower(tep_create_safe_string($_POST['config_name'], '_', $multi_filter)) : '');
      $http_server = (isset($_POST['http_server']) ? $g_db->prepare_input($_POST['http_server']) : '');
      $https_server = (isset($_POST['https_server']) ? $g_db->prepare_input($_POST['https_server']) : '');
      $site_ssl = (isset($_POST['site_ssl']) ? 'true':'false');
      $ws_path = (isset($_POST['ws_path']) ? $g_db->prepare_input($_POST['ws_path']) : '');
      $fs_path = (isset($_POST['fs_path']) ? $g_db->prepare_input($_POST['fs_path']) : '');

      $db_server = (isset($_POST['db_server']) ? $g_db->prepare_input($_POST['db_server']) : '');
      $db_username = (isset($_POST['db_username']) ? $g_db->prepare_input($_POST['db_username']) : '');
      $db_password = (isset($_POST['db_password']) ? $g_db->prepare_input($_POST['db_password']) : '');
      $db_database = (isset($_POST['db_database']) ? $g_db->prepare_input($_POST['db_database']) : '');

      $error = false;
      if( empty($config_name) ) {
        $messageStack->add_session(ERROR_EMPTY_CONFIG_NAME);
        $error = true;
      }

      if( empty($http_server) ) {
        $messageStack->add_session(ERROR_EMPTY_HTTP_SERVER);
        $error = true;
      }

      //if( empty($https_server) ) {
      //  $messageStack->add_session(ERROR_EMPTY_HTTPS_SERVER);
      //  $error = true;
      //}

      if( empty($ws_path) ) {
        $messageStack->add_session(ERROR_EMPTY_WS_PATH);
        $error = true;
      }
      if( empty($fs_path) ) {
        $messageStack->add_session(ERROR_EMPTY_FS_PATH);
        $error = true;
      }
      if( empty($db_server) ) {
        $messageStack->add_session(ERROR_EMPTY_DB_SERVER);
        $error = true;
      }
      if( empty($db_username) ) {
        $messageStack->add_session(ERROR_EMPTY_DB_USERNAME);
        $error = true;
      }
      if( empty($db_password) ) {
        $messageStack->add_session(ERROR_EMPTY_DB_PASSWORD);
        $error = true;
      }
      if( empty($db_database) ) {
        $messageStack->add_session(ERROR_EMPTY_DB_DATABASE);
        $error = true;
      }

      if( $error ) {
        tep_redirect(tep_href_link($g_script));
      }

      $config_name = strtolower($config_name);
      $contents = '<?php' . $copyright_string . "\n" . 
                  '  $http_server = \'' . $http_server . '\';' . "\n" .
                  '  $https_server = \'' . $https_server . '\';' . "\n" .
                  '  $site_ssl = \'' . $site_ssl . '\';' . "\n" .
                  '  $ws_path = \'' . $ws_path . '\';' . "\n" .
                  '  $fs_path = \'' . $fs_path . '\';' . "\n" .
                  '  $db_server = \'' . $db_server . '\';' . "\n" .
                  '  $db_username = \'' . $db_username . '\';' . "\n" .
                  '  $db_password = \'' . $db_password . '\';' . "\n" .
                  '  $db_database = \'' . $db_database . '\';' . "\n" .
                  '?>' . "\n";

      $config_name = DIR_WS_MODULES . $multi_prefix . $config_name . '.php';
      @unlink($config_name);

      if( !tep_write_contents($config_name, $contents) ) {
        $messageStack->add_session( sprintf(ERROR_SITE_CONFIG_WRITE, DIR_FS_ADMIN . $config_name) );
        tep_redirect(tep_href_link($g_script));
      }

      $messageStack->add_session(SUCCESS_ENTRY_CREATE, 'success');
      tep_redirect(tep_href_link($g_script));
      break;
    case 'delete':
    case 'delete_confirm':
      $site = (isset($_GET['site']) ? strtolower(tep_create_safe_string($_GET['site'], '_', $multi_filter)) : '');
      $filename = DIR_WS_MODULES . $multi_prefix . $site . '.php';
      if( empty($site) || !file_exists($filename) ) {
        $messageStack->add_session(ERROR_SITE_CONFIG_INVALID);
        tep_redirect(tep_href_link($g_script));
      }
      if( $action == 'delete' ) break;
      @unlink($filename);
      $messageStack->add_session(WARNING_SITE_CONFIG_DELETED, 'warning');
      tep_redirect(tep_href_link($g_script));
      break;
    case 'delete_multi':
    case 'delete_multi_confirm':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) ));
      }
      if( $action == 'delete_multi' ) break;

      $result = false;
      foreach ($_POST['mark'] as $key => $val) {
        $site = strtolower(tep_create_safe_string($key, '_', $multi_filter));
        $filename = DIR_WS_MODULES . $multi_prefix . $site . '.php';

        if( empty($site) || !file_exists($filename) ) {
          $messageStack->add_session(sprintf(WARNING_SITE_CONFIG_INVALID, DIR_FS_ADMIN . $config_name) );
          continue;
        }
        $result = true;
        @unlink($filename);
      }
      if( $result ) {
        $messageStack->add_session(WARNING_SITE_CONFIG_DELETED, 'warning');
      }
      tep_redirect(tep_href_link($g_script));
      break;
    case 'update':
      if( !isset($_POST['mark']) || !is_array($_POST['mark']) || !count($_POST['mark']) ) {
        $messageStack->add_session(WARNING_NOTHING_SELECTED, 'warning');
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params(array('action')) ));
      }
      $result = false;
      foreach ($_POST['mark'] as $key => $val) {
        $config_name = (isset($_POST['config_name'][$key]) ? strtolower(tep_create_safe_string($_POST['config_name'][$key], '_', $multi_filter)) : '');

        if( empty($config_name) ) {
          continue;
        }

        $http_server = (isset($_POST['http_server'][$key]) ? $g_db->prepare_input($_POST['http_server'][$key]) : '');
        $https_server = (isset($_POST['https_server'][$key]) ? $g_db->prepare_input($_POST['https_server'][$key]) : '');
        $site_ssl = (isset($_POST['site_ssl'][$key]) ? 'true':'false');
        $ws_path = (isset($_POST['ws_path'][$key]) ? $g_db->prepare_input($_POST['ws_path'][$key]) : '');
        $fs_path = (isset($_POST['fs_path'][$key]) ? $g_db->prepare_input($_POST['fs_path'][$key]) : '');

        $db_server = (isset($_POST['db_server'][$key]) ? $g_db->prepare_input($_POST['db_server'][$key]) : '');
        $db_username = (isset($_POST['db_username'][$key]) ? $g_db->prepare_input($_POST['db_username'][$key]) : '');
        $db_password = (isset($_POST['db_password'][$key]) ? $g_db->prepare_input($_POST['db_password'][$key]) : '');
        $db_database = (isset($_POST['db_database'][$key]) ? $g_db->prepare_input($_POST['db_database'][$key]) : '');

        $contents = '<?php' . $copyright_string . "\n" . 
                    '  $http_server = \'' . $http_server . '\';' . "\n" .
                    '  $https_server = \'' . $https_server . '\';' . "\n" .
                    '  $site_ssl = \'' . $site_ssl . '\';' . "\n" .
                    '  $ws_path = \'' . $ws_path . '\';' . "\n" .
                    '  $fs_path = \'' . $fs_path . '\';' . "\n" .
                    '  $db_server = \'' . $db_server . '\';' . "\n" .
                    '  $db_username = \'' . $db_username . '\';' . "\n" .
                    '  $db_password = \'' . $db_password . '\';' . "\n" .
                    '  $db_database = \'' . $db_database . '\';' . "\n" .
                    '?>' . "\n";
        $config_name = DIR_WS_MODULES . $multi_prefix . $config_name . '.php';
        if( !tep_write_contents($config_name, $contents) ) {
          $messageStack->add_session( sprintf(ERROR_SITE_CONFIG_WRITE, DIR_FS_ADMIN . $config_name) );
          continue;
        }
        $result = true;
      }
      if( $result ) {
        $messageStack->add_session(SUCCESS_ENTRY_UPDATED, 'success');
      }
      tep_redirect(tep_href_link($g_script));
      break;
    default:
      $config_name = strtolower(tep_create_safe_string(STORE_NAME, '_', $multi_filter));
      $http_server = HTTP_CATALOG_SERVER;
      $https_server = HTTPS_CATALOG_SERVER;
      $site_ssl = ENABLE_SSL_CATALOG;
      $ws_path = DIR_WS_CATALOG;
      $fs_path = DIR_FS_CATALOG;
      $db_server = DB_SERVER;
      $db_username = DB_SERVER_USERNAME;
      $db_password = DB_SERVER_PASSWORD;
      $db_database = DB_DATABASE;
      break;
  }
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php
  $set_focus = true;
  require('includes/objects/html_start_sub2.php'); 
?>
<?php
  if( $action == 'restart' ) {
?>
        <div class="maincell wider">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_RESTART; ?></h1></div>
          </div>
          <div class="textInfo"><?php echo TEXT_INFO_RESTART; ?></div>
<?php 
    $site = strtolower(tep_create_safe_string($_GET['site'], '_', $multi_filter));
    $filename = DIR_WS_MODULES . $multi_prefix . $site . '.php';
?>
          <div class="formArea">
            <div class="textInfo"><?php echo '<b style="color: #FF0000">' . $filename . '</b>'; ?></div>
          </div>
          <div class="formButtons">
<?php
    echo '<a href="' . tep_href_link($g_script) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
    echo '<a href="' . tep_href_link($g_script, 'action=restart_confirm&site=' . $site) . '">' . tep_image_button('button_confirm.gif', IMAGE_CONFIRM) . '</a>';
?>
          </div>
        </div>
<?php
  } elseif( $action == 'delete' ) {
?>
        <div class="maincell wider">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_DELETE; ?></h1></div>
          </div>
          <div class="textInfo"><?php echo TEXT_INFO_DELETE; ?></div>
<?php 
    $site = strtolower(tep_create_safe_string($_GET['site'], '_', $multi_filter));
    $filename = DIR_WS_MODULES . $multi_prefix . $site . '.php';
?>
          <div class="formArea">
            <div class="textInfo"><?php echo '<b style="color: #FF0000">' . $filename . '</b>'; ?></div>
          </div>
          <div class="formButtons">
<?php
    echo '<a href="' . tep_href_link($g_script) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
    echo '<a href="' . tep_href_link($g_script, 'action=delete_confirm&site=' . $site) . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>';
?>
          </div>
        </div>
<?php
  } elseif( $action =='delete_multi' ) {
?>
        <div class="maincell wider">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_MULTI_DELETE; ?></h1></div>
          </div>
          <div class="textInfo"><?php echo TEXT_INFO_MULTI_DELETE; ?></div>
          <div><?php echo tep_draw_form('multi_delete', $g_script, 'action=delete_multi_confirm', 'post'); ?>
<?php
      foreach( $_POST['mark'] as $key => $val) {
        $site = strtolower(tep_create_safe_string($key, '_', $multi_filter));
        $filename = DIR_WS_MODULES . $multi_prefix . $site . '.php';

        if( !empty($site) && file_exists($filename) ) {
          echo '<div class="textInfo"><b style="color: #FF0000">' . $filename . '</b>' . tep_draw_hidden_field('mark[' . $site . ']', $site) . '</div>' . "\n";
        }
      }
?>
            <div class="formButtons">
<?php
    echo '<a href="' . tep_href_link($g_script) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
    echo tep_image_submit('button_confirm.gif', IMAGE_CONFIRM);
?>
            </div>
          </form></div>
        </div>
<?php
  } else {
?>
        <div class="maincell wider">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_MULTI_SITES_ADD; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div class="smallText"><?php echo TEXT_INFO_INSERT; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form("add_field", $g_script, 'action=add', 'post'); ?><table class="tabledata" cellspacing="1">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_MULTI_NAME; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_HTTP_SERVER; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_HTTPS_SERVER; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_SSL; ?></th>
            </tr>
            <tr>
              <td><?php echo tep_draw_input_field('config_name', '', 'style="width:99%"'); ?></td>
              <td><?php echo tep_draw_input_field('http_server', '', 'style="width:99%"'); ?></td>
              <td><?php echo tep_draw_input_field('https_server', '', 'style="width:99%"'); ?></td>
              <td><?php echo tep_draw_checkbox_field('ssl'); ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_MULTI_WS_PATH; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_FS_PATH; ?></th>
              <th>&nbsp;</th>
              <th>&nbsp;</th>
            </tr>
            <tr>
              <td><?php echo tep_draw_input_field('ws_path', '', 'style="width:99%"'); ?></td>
              <td><?php echo tep_draw_input_field('fs_path', '', 'style="width:99%"'); ?></td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_MULTI_DB_SERVER; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_DB_USERNAME; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_DB_PASSWORD; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_DB_DATABASE; ?></th>
            </tr>
            <tr>
              <td><?php echo tep_draw_input_field('db_server', '', 'style="width:99%"'); ?></td>
              <td><?php echo tep_draw_input_field('db_username', '', 'style="width:99%"'); ?></td>
              <td><?php echo tep_draw_input_field('db_password', '', 'style="width:99%"'); ?></td>
              <td><?php echo tep_draw_input_field('db_database', '', 'style="width:99%"'); ?></td>
            </tr>
            <tr>
              <td colspan="4" class="formButtons"><?php echo tep_image_submit('button_insert.gif', IMAGE_INSERT); ?></td>
            </tr>
          </table></form></div>
        </div>
<?php
    $sites_array = glob(DIR_WS_MODULES . $multi_prefix . '*.php');
    if( count($sites_array) ) {
?>
        <div class="maincell wider">
          <div class="comboHeading">
            <div class="pageHeading"><h1><?php echo HEADING_MULTI_SITES_UPDATE; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div class="smallText"><?php echo TEXT_INFO_UPDATE; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('seo_types', $g_script,'action=update', 'post'); ?><table border="0" width="100%" cellspacing="1" cellpadding="3">
<?php
      $count = 0;
      foreach($sites_array as $filename) {
        $name = substr(basename($filename), strlen($multi_prefix), -4);
        $name = strtolower(tep_create_safe_string($name, '_', $multi_filter));
        require($filename);
        $count++;
?>
            <tr style="background: #000066;">
              <td><table border="0" cellspacing="1" cellpadding="3">
                <tr>
                  <td><?php echo tep_draw_checkbox_field('mark['.$name.']', 1, false, 'title="' . sprintf(TEXT_INFO_MARK, $name) . '"') ?></td>
                  <td><h2 style="color: #FFF;"><?php echo $count . '. ' . TEXT_SITE . ' ' . $name; ?></h2></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><table class="tabledata" cellspacing="1">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MULTI_NAME; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MULTI_HTTP_SERVER; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MULTI_HTTPS_SERVER; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MULTI_SSL; ?></td>
                </tr>
                <tr>
                  <td class="dataTableContent"><?php echo tep_draw_input_field('config_name[' . $name . ']', $name, 'style="width:99%"'); ?></td>
                  <td class="dataTableContent"><?php echo tep_draw_input_field('http_server[' . $name . ']', $http_server, 'style="width:99%"'); ?></td>
                  <td class="dataTableContent"><?php echo tep_draw_input_field('https_server[' . $name . ']', $https_server, 'style="width:99%"'); ?></td>
                  <td class="dataTableContent"><?php echo tep_draw_checkbox_field('site_ssl[' . $name . ']', 'on', ($site_ssl=='true')); ?></td>
                </tr>
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MULTI_WS_PATH; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MULTI_FS_PATH; ?></td>
                  <td class="dataTableHeadingContent">&nbsp;</td>
                  <td class="dataTableHeadingContent">&nbsp;</td>
                </tr>
                <tr>
                  <td class="dataTableContent"><?php echo tep_draw_input_field('ws_path[' . $name . ']', $ws_path, 'style="width:99%"'); ?></td>
                  <td class="dataTableContent"><?php echo tep_draw_input_field('fs_path[' . $name . ']', $fs_path, 'style="width:99%"'); ?></td>
                  <td class="dataTableContent">&nbsp;</td>
                  <td class="dataTableContent">&nbsp;</td>
                </tr>
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MULTI_DB_SERVER; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MULTI_DB_USERNAME; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MULTI_DB_PASSWORD; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MULTI_DB_DATABASE; ?></td>
                </tr>
                <tr>
                  <td class="dataTableContent"><?php echo tep_draw_input_field('db_server[' . $name . ']', $db_server, 'style="width:99%"'); ?></td>
                  <td class="dataTableContent"><?php echo tep_draw_input_field('db_username[' . $name . ']', $db_username, 'style="width:99%"'); ?></td>
                  <td class="dataTableContent"><?php echo tep_draw_input_field('db_password[' . $name . ']', $db_password, 'style="width:99%"'); ?></td>
                  <td class="dataTableContent"><?php echo tep_draw_input_field('db_database[' . $name . ']', $db_database, 'style="width:99%"'); ?></td>
                </tr>
              </table></td>
            </tr>
            <tr style="background: #660000;">
              <td><table border="0" cellspacing="1" cellpadding="3">
                <tr>
                  <td><h2 style="color: #FFF;"><?php echo TEXT_RESTART . '&nbsp;&raquo;&nbsp;' . $name; ?></h2></td>
                  <td class="dataTableContent" align="center"><?php echo '<a href="' . tep_href_link($g_script, 'site=' . $name . '&action=restart') . '">' . tep_image(DIR_WS_ICONS . 'icon_restart.png', TEXT_RESTART_USING . ' ' . basename($filename)) . '</a>'; ?></td>
                  <td class="dataTableContent" align="center"><?php echo '<a href="' . tep_href_link($g_script, 'site=' . $name . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE_CONFIG . ' ' . basename($filename)) . '</a>'; ?></td>
                </tr>
              </table></td>
            </tr>
<?php
      }
?>
            <tr>
              <td class="formButtons"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE, 'name="update"') . '&nbsp;' . tep_image_submit('button_delete.gif', IMAGE_DELETE, 'name="delete_multi"') ?></td>
            </tr>
          </table></form></div>
        </div>
<?php
    }
  }
?>
<?php require('includes/objects/html_end.php'); ?>