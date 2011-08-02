<?php
  $copyright_string='
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
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

  if (isset($_POST['delete_multi_x']) || isset($_POST['delete_multi_y'])) $action='delete_multi';

  switch ($action) {
    case 'restart':
    case 'restart_confirm':
      $site = (isset($_GET['site']) ? strtolower(tep_create_safe_string($_GET['site'],'_', $multi_filter)) : '');
      $filename = DIR_FS_MODULES . $multi_prefix . $site . '.php';
      if( empty($site) || !is_file($filename) ) {
        $messageStack->add_session(ERROR_SITE_CONFIG_INVALID);
        tep_redirect(tep_href_link($g_script));
      }
      if( $action == 'restart' ) break;

      require($filename);
      $contents = 
        '<?php' . $copyright_string . "\n" . 
        '  define(\'HTTP_CATALOG_SERVER\', \'' . $http_server . '\');' . "\n" .
        '  define(\'HTTPS_CATALOG_SERVER\', \'' . $https_server . '\');' . "\n" .
        '  define(\'ENABLE_SSL_CATALOG\', \'' . $site_ssl . '\');' . "\n" .
        '  define(\'DIR_WS_CATALOG\', \'' . $ws_path . '\');' . "\n" .
        '  define(\'DIR_FS_CATALOG\', \'' . $fs_path . '\');' . "\n\n" .

        '  define(\'DIR_WS_CATALOG_INCLUDES\', DIR_WS_CATALOG . \'includes/\');' . "\n" .
        '  define(\'DIR_WS_CATALOG_IMAGES\', DIR_WS_CATALOG . \'images/\');' . "\n" .
        '  define(\'DIR_WS_CATALOG_ICONS\', DIR_WS_CATALOG_IMAGES . \'icons/\');' . "\n" .
        '  define(\'DIR_WS_CATALOG_STRINGS\', DIR_WS_CATALOG_INCLUDES . \'strings/\');' . "\n" .
        '  define(\'DIR_WS_CATALOG_MODULES\', DIR_WS_CATALOG_INCLUDES . \'modules/\');' . "\n" .
        '  define(\'DIR_WS_CATALOG_PLUGINS\', DIR_WS_CATALOG_INCLUDES . \'plugins/\');' . "\n" .
        '  define(\'DIR_WS_CATALOG_TEMPLATE\', DIR_WS_CATALOG_INCLUDES . \'template/\');' . "\n\n" .

        '  define(\'DB_SERVER\', \'' . $db_server . '\');' . "\n" .
        '  define(\'DB_SERVER_USERNAME\', \'' . $db_username . '\');' . "\n" .
        '  define(\'DB_SERVER_PASSWORD\', \'' . $db_password . '\');' . "\n" .
        '  define(\'DB_DATABASE\', \'' . $db_database . '\');' . "\n" .
        '  define(\'USE_PCONNECT\', \'false\');' . "\n" .
        '?>' . "\n";

      $site_file = DIR_FS_INCLUDES . 'configure_site.php';
      if( !tep_write_contents(DIR_FS_INCLUDES . 'configure_site.php', $contents) ) {
        $messageStack->add_session (sprintf(ERROR_SITE_CONFIG_WRITE, DIR_FS_ADMIN . $site_file) );
        tep_redirect(tep_href_link($g_script));
      }
      $g_session->destroy();
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
      $contents = 
        '<?php' . $copyright_string . "\n" . 
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

      $config_name = DIR_FS_MODULES . $multi_prefix . $config_name . '.php';
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
      $filename = DIR_FS_MODULES . $multi_prefix . $site . '.php';
      if( empty($site) || !is_file($filename) ) {
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
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
      }
      if( $action == 'delete_multi' ) break;

      $result = false;
      foreach ($_POST['mark'] as $key => $val) {
        $site = strtolower(tep_create_safe_string($key, '_', $multi_filter));
        $filename = DIR_FS_MODULES . $multi_prefix . $site . '.php';

        if( empty($site) || !is_file($filename) ) {
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
        tep_redirect(tep_href_link($g_script, tep_get_all_get_params('action') ));
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

        $contents = 
          '<?php' . $copyright_string . "\n" . 
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
        $config_name = DIR_FS_MODULES . $multi_prefix . $config_name . '.php';
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
<?php require(DIR_FS_OBJECTS . 'html_start_sub1.php'); ?>
<?php require(DIR_FS_OBJECTS . 'html_start_sub2.php'); ?>
<?php
  if( $action == 'restart' ) {
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div><h1><?php echo HEADING_RESTART; ?></h1></div>
          </div>
          <div class="textInfo"><?php echo TEXT_INFO_RESTART; ?></div>
<?php 
    $site = strtolower(tep_create_safe_string($_GET['site'], '_', $multi_filter));
    $filename = DIR_FS_MODULES . $multi_prefix . $site . '.php';
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
          <div class="comboHeadingTop">
            <div><h1><?php echo HEADING_DELETE; ?></h1></div>
          </div>
          <div class="textInfo"><?php echo TEXT_INFO_DELETE; ?></div>
<?php 
    $site = strtolower(tep_create_safe_string($_GET['site'], '_', $multi_filter));
    $filename = DIR_FS_MODULES . $multi_prefix . $site . '.php';
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
          <div class="comboHeadingTop">
            <div><h1><?php echo HEADING_MULTI_DELETE; ?></h1></div>
          </div>
          <div class="textInfo"><?php echo TEXT_INFO_MULTI_DELETE; ?></div>
          <div><?php echo tep_draw_form('multi_delete', $g_script, 'action=delete_multi_confirm', 'post'); ?>
<?php
      foreach( $_POST['mark'] as $key => $val) {
        $site = strtolower(tep_create_safe_string($key, '_', $multi_filter));
        $filename = DIR_FS_MODULES . $multi_prefix . $site . '.php';

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
    $buttons = array(
      tep_image_submit('button_insert.gif', IMAGE_INSERT)
    );
?>
        <div class="maincell wider">
          <div class="comboHeadingTop">
            <div class="rspacer floater help_page"><?php echo '<a href="' . tep_href_link($g_script, 'action=help&ajax=list') . '" class="heading_help" title="' . HEADING_MULTI_SITES_ADD . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'icon_help_32.png', HEADING_MULTI_SITES_ADD) . '</a>'; ?></div>
            <div><h1><?php echo HEADING_MULTI_SITES_ADD; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div class="smallText"><?php echo TEXT_INFO_INSERT; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form("add_field", $g_script, 'action=add', 'post'); ?><fieldset><legend><?php echo TEXT_INFO_ADD_NEW_SITE; ?></legend><table class="tabledata">
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_MULTI_NAME; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_HTTP_SERVER; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_HTTPS_SERVER; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_SSL; ?></th>
            </tr>
            <tr>
              <td><div class="rpad"><?php echo tep_draw_input_field('config_name'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('http_server'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('https_server'); ?></div></td>
              <td><?php echo tep_draw_checkbox_field('ssl'); ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_MULTI_WS_PATH; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_FS_PATH; ?></th>
              <th></th>
              <th></th>
            </tr>
            <tr>
              <td><div class="rpad"><?php echo tep_draw_input_field('ws_path'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('fs_path'); ?></div></td>
              <td></td>
              <td></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <th><?php echo TABLE_HEADING_MULTI_DB_SERVER; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_DB_USERNAME; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_DB_PASSWORD; ?></th>
              <th><?php echo TABLE_HEADING_MULTI_DB_DATABASE; ?></th>
            </tr>
            <tr>
              <td><div class="rpad"><?php echo tep_draw_input_field('db_server'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('db_username'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('db_password'); ?></div></td>
              <td><div class="rpad"><?php echo tep_draw_input_field('db_database'); ?></div></td>
            </tr>
          </table></fieldset><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
<?php
    $sites_array = glob(DIR_FS_MODULES . $multi_prefix . '*.php');
    if( count($sites_array) ) {
?>
          <div class="comboHeadingTop">
            <div><h1><?php echo HEADING_MULTI_SITES_UPDATE; ?></h1></div>
          </div>
          <div class="comboHeading">
            <div><?php echo TEXT_INFO_UPDATE; ?></div>
          </div>
          <div class="formArea"><?php echo tep_draw_form('seo_types', $g_script, 'action=update', 'post'); ?><table width="100%" cellspacing="0" cellpadding="0">
<?php
      $count = 0;
      foreach($sites_array as $filename) {
        $name = substr(basename($filename), strlen($multi_prefix), -4);
        $name = strtolower(tep_create_safe_string($name, '_', $multi_filter));
        require($filename);
        $count++;

        $site_string = tep_draw_checkbox_field('mark['.$name.']', 1, false, 'id="label_site_' . $count . '" title="' . sprintf(TEXT_INFO_MARK, $name) . '"');
        $site_string .= '<label style="font-size: 14px;" class="lpad" for="label_site_' . $count . '">' . $count . '. ' . TEXT_SITE . ' ' . $name . '</label>';
        $buttons = array(
          '<a href="' . tep_href_link($g_script, 'site=' . $name . '&action=restart') . '">' . tep_image(DIR_WS_ICONS . 'icon_restart.png', TEXT_RESTART_USING . ' ' . basename($filename)) . '</a>',
          '<a href="' . tep_href_link($g_script, 'site=' . $name . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'icon_delete.png', TEXT_DELETE_CONFIG . ' ' . basename($filename)) . '</a>'
        );
?>
            <tr class="dataTableRow">
              <td><fieldset><legend><?php echo $site_string; ?></legend><table class="tabledata">
                <tr class="dataTableHeadingRow">
                  <th><?php echo TABLE_HEADING_MULTI_NAME; ?></th>
                  <th><?php echo TABLE_HEADING_MULTI_HTTP_SERVER; ?></th>
                  <th><?php echo TABLE_HEADING_MULTI_HTTPS_SERVER; ?></th>
                  <th><?php echo TABLE_HEADING_MULTI_SSL; ?></th>
                </tr>
                <tr>
                  <td><div class="rpad"><?php echo tep_draw_input_field('config_name[' . $name . ']', $name); ?></div></td>
                  <td><div class="rpad"><?php echo tep_draw_input_field('http_server[' . $name . ']', $http_server); ?></div></td>
                  <td><div class="rpad"><?php echo tep_draw_input_field('https_server[' . $name . ']', $https_server); ?></div></td>
                  <td><?php echo tep_draw_checkbox_field('site_ssl[' . $name . ']', 'on', ($site_ssl=='true')); ?></td>
                </tr>
                <tr class="dataTableHeadingRow">
                  <th><?php echo TABLE_HEADING_MULTI_WS_PATH; ?></th>
                  <th><?php echo TABLE_HEADING_MULTI_FS_PATH; ?></th>
                  <th></th>
                  <th></th>
                </tr>
                <tr>
                  <td><div class="rpad"><?php echo tep_draw_input_field('ws_path[' . $name . ']', $ws_path); ?></div></td>
                  <td><div class="rpad"><?php echo tep_draw_input_field('fs_path[' . $name . ']', $fs_path); ?></div></td>
                  <td></td>
                  <td></td>
                </tr>
                <tr class="dataTableHeadingRow">
                  <th><?php echo TABLE_HEADING_MULTI_DB_SERVER; ?></th>
                  <th><?php echo TABLE_HEADING_MULTI_DB_USERNAME; ?></th>
                  <th><?php echo TABLE_HEADING_MULTI_DB_PASSWORD; ?></th>
                  <th><?php echo TABLE_HEADING_MULTI_DB_DATABASE; ?></th>
                </tr>
                <tr>
                  <td><div class="rpad"><?php echo tep_draw_input_field('db_server[' . $name . ']', $db_server); ?></div></td>
                  <td><div class="rpad"><?php echo tep_draw_input_field('db_username[' . $name . ']', $db_username); ?></div></td>
                  <td><div class="rpad"><?php echo tep_draw_input_field('db_password[' . $name . ']', $db_password); ?></div></td>
                  <td><div class="rpad"><?php echo tep_draw_input_field('db_database[' . $name . ']', $db_database); ?></div></td>
                </tr>
              </table><div class="formButtons tinysep"><?php echo implode('', $buttons); ?></div></fieldset></td>
            </tr>
<?php
      }
      $buttons = array(
        tep_image_submit('button_update.gif', IMAGE_UPDATE, 'name="update"'),
        tep_image_submit('button_delete.gif', IMAGE_DELETE, 'name="delete_multi"')
      );
?>
          </table><div class="formButtons"><?php echo implode('', $buttons); ?></div></form></div>
<?php
    }
?>
        </div>
<?php
  }
?>
<?php require(DIR_FS_OBJECTS . 'html_end.php'); ?>