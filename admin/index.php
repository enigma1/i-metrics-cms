<?php
/*
  $Id: index.php,v 1.19 2003/06/27 09:38:31 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $cat = array(
               array('title' => BOX_ABSTRACT_GENERIC_TEXT_NEW,
                     'image' => 'zones.png',
                     'href' => tep_href_link(FILENAME_GENERIC_TEXT, 'action=new_generic_text&selected_box=abstract_config'),
                     'children' => array(
                                         array('title' => BOX_ABSTRACT_GENERIC_TEXT, 'link' => tep_href_link(FILENAME_GENERIC_TEXT, 'selected_box=abstract_config')),
                                         array('title' => BOX_TITLE_GROUP_PAGES, 'link' => tep_href_link(FILENAME_ABSTRACT_ZONES, 'selected_box=abstract_config')),
                                         array('title' => BOX_ABSTRACT_CONFIG, 'link' => tep_href_link(FILENAME_ABSTRACT_ZONES_CONFIG, 'selected_box=abstract_config')),
                                   )
               ),

               array('title' => BOX_HEADING_HELPDESK,
                     'image' => 'helpdesk.png',
                     'href' => tep_href_link(FILENAME_HELPDESK, 'selected_box=helpdesk'),
                     'children' => array(array('title' => BOX_HELPDESK_POP3, 'link' => tep_href_link(FILENAME_HELPDESK_POP3, 'selected_box=helpdesk')),
                                         array('title' => BOX_TOOLS_MAIL, 'link' => tep_href_link(FILENAME_MAIL, 'selected_box=helpdesk')),
                                         array('title' => BOX_HELPDESK_DEPARTMENTS, 'link' => tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'selected_box=helpdesk')),
                                   ) 
               ),

               array('title' => BOX_HEADING_CONFIGURATION,
                     'image' => 'configuration.png',
                     'href' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=1'),
                     'children' => array(array('title' => BOX_CONFIGURATION_MYSTORE, 'link' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=1')),
                                         array('title' => BOX_CONFIGURATION_LOGGING, 'link' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=10')),
                                   )
               ),

               array('title' => BOX_HEADING_MARKETING,
                     'image' => 'marketing.png',
                     'href' => tep_href_link(FILENAME_SEO_REPORTS, 'selected_box=seo_config'),
                     'children' => array(array('title' => BOX_SEO_ZONES, 'link' => tep_href_link(FILENAME_SEO_ZONES, 'selected_box=seo_config')),
                                         array('title' => BOX_META_ZONES, 'link' => tep_href_link(FILENAME_META_ZONES, 'selected_box=meta_config')))),


               array('title' => BOX_CACHE_REPORTS,
                     'image' => 'cache.png',
                     'href' => tep_href_link(FILENAME_CACHE_REPORTS, 'selected_box=cache'),
                     'children' => array(array('title' => BOX_CACHE_CONFIG, 'link' => tep_href_link(FILENAME_CACHE_CONFIG, 'selected_box=cache')),
                                         array('title' => BOX_CACHE_HTML, 'link' => tep_href_link(FILENAME_CACHE_HTML, 'selected_box=cache')),
                                   )
               ),

               array('title' => BOX_HEADING_TOOLS,
                     'image' => 'tools.png',
                     'href' => tep_href_link(FILENAME_WHOS_ONLINE, 'selected_box=tools'),
                     'children' => array(
                                         array('title' => BOX_TOOLS_PLUGINS, 'link' => tep_href_link(FILENAME_PLUGINS, 'selected_box=tools')),
                                         array('title' => BOX_TOOLS_BACKUP, 'link' => tep_href_link(FILENAME_BACKUP, 'selected_box=tools')),
                                         array('title' => BOX_TOOLS_MULTI_SITES, 'link' => tep_href_link(FILENAME_MULTI_SITES, 'selected_box=tools')),
                                         array('title' => TOOLS_WHOS_ONLINE, 'link' => tep_href_link(FILENAME_WHOS_ONLINE, 'selected_box=tools')),
                                         array('title' => BOX_TOOLS_TOTAL_CONFIGURATION, 'link' => tep_href_link(FILENAME_TOTAL_CONFIGURATION, 'selected_box=tools')),
                                   )
               ),
         );
?>
<?php require('includes/objects/html_start_sub1.php'); ?>
<?php require('includes/objects/html_start_sub2.php'); ?>
      <div id="header">
        <div class="logo" style="height: 38px;">
          <div style="float: left; padding: 12px 0px 0px 20px;"><?php echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_IMAGES . 'design/logo.png', STORE_NAME) . '</a>'; ?></div>
          <div style="float: right; padding: 10px 10px 0px 0px;"><h1><?php echo HEADING_MANAGE_SITE; ?></h1></div>
        </div>
      </div>
<?php
  $messageStack->output('header');
?>
      <div id="leftpane">
        <div style="padding: 8px;">
<?php
  if( DEFAULT_WARNING_PASSWORD_PROTECT_REMIND == 'true' ) {
    $contents = array();
    $cfq_query = $g_db->query("select configuration_id from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_WARNING_PASSWORD_PROTECT_REMIND'");
    $cfg_array = $g_db->fetch_array($cfq_query);
    $warning_string = '<a href="' . tep_href_link(FILENAME_CONFIGURATION, 'action=edit&cID=' . $cfg_array['configuration_id']) . '"><b style="color: #FF0000">' . WARNING_PASSWORD_PROTECT_REMIND . '</b></a>';

    $contents[] = array(
                        'text' => tep_image(DIR_WS_ICONS . 'icon_restrict.png', ICON_UNLOCKED, '', '', 'align="right"') . $warning_string
                       );

    echo '<div class="vspacer"></div>' . "\n";
    $box = new box;
    $box->common_data_parameters = 'class="altBoxContent"';
    echo $box->commonBlock($contents);
    echo '<div class="vspacer"></div>' . "\n";
  }

  $heading = array();
  $contents = array();

  $heading[] = array(
                     'text'  => BOX_HEADING_TOP,
                     'link'  => tep_href_link()
                    );

  $contents[] = array('text'  => '<a href="http://demos.asymmetrics.com" target="_blank">' . BOX_ENTRY_SUPPORT_SITE . '</a>');
  $contents[] = array('text'  => '<a href="http://demos.asymmetrics.com" target="_blank">' . BOX_ENTRY_DOCUMENTATION . '</a>');
  $contents[] = array('text'  => '<a href="http://demos.asymmetrics.com" target="_blank">' . BOX_ENTRY_FORUMS . '</a>');
  $contents[] = array('text'  => '<a href="http://demos.asymmetrics.com" target="_blank">' . BOX_ENTRY_MODULES . '</a>');


  $box = new box;
  echo $box->menuBox($heading, $contents, 'class="altBoxHeading"', 'class="altBoxContent"');

  echo '<div class="vspacer"></div>' . "\n";

  $contents = array();

  if (getenv('HTTPS') == 'on') {
    $size = ((getenv('SSL_CIPHER_ALGKEYSIZE')) ? getenv('SSL_CIPHER_ALGKEYSIZE') . '-bit' : '<i>' . BOX_CONNECTION_UNKNOWN . '</i>');
    $contents[] = array(
                        'text' => tep_image(DIR_WS_ICONS . 'locked.gif', ICON_LOCKED, '', '', 'align="right"') . sprintf(BOX_CONNECTION_PROTECTED, $size)
                       );
  } else {
    $contents[] = array(
                        'text' => tep_image(DIR_WS_ICONS . 'unlocked.gif', ICON_UNLOCKED, '', '', 'align="right"') . BOX_CONNECTION_UNPROTECTED
                       );
  }

  $box = new box;
  $box->common_data_parameters = 'class="altBoxContent"';
  echo $box->commonBlock($contents);
?>
        </div>
      </div>
      <div id="mainpane">
        <div class="maincell" style="width: 100%;">
          <div style="padding: 8px;">
<?php
  $col = 2;
  for ($i = 0, $n = sizeof($cat); $i < $n; $i++) {
    if( !($i%$col) ) {
      echo '                  <div class="cleaner">' . "\n";
    }

    $children = '';
    for ($j = 0, $k = sizeof($cat[$i]['children']); $j < $k; $j++) {
      $children .= '<a href="' . $cat[$i]['children'][$j]['link'] . '" class="pageSub">' . $cat[$i]['children'][$j]['title'] . '</a>, ';
    }
    $children = substr($children, 0, -2);
?>
    <div style="float: left; width: 50%; height: 134px;">
      <div style="float: left; padding-right: 4px;"><?php echo '<a href="' . $cat[$i]['href'] . '">' . tep_image(DIR_WS_IMAGES . 'categories/' . $cat[$i]['image'], $cat[$i]['title']) . '</a>'; ?></div>
      <div style="margin-top: 30px;"><?php echo '<a href="' . $cat[$i]['href'] . '" title="' . $cat[$i]['title'] . '" class="pageHeading">' . $cat[$i]['title'] . '</a><br />' . $children; ?></div>
    </div>
<?php
    if( !(($i+1)%$col) ) {
      echo '                  </div>' . "\n";
    }
  }

  if( !(($i+1)%$col) ) {
    echo '                  </div>' . "\n";
  }
?>
          </div>
        </div>
      </div>
<?php require('includes/objects/html_end.php'); ?>
