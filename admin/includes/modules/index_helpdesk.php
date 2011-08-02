<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Index Content Level Module
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
  $entries_array = array();

  $entries_array[] = array(
    'id' => 'index_main',
    'sub' => 'index_main',
    'title' => TEXT_INFO_BACK, 
    'image' => 'root.png', 
    'href' => tep_href_link(),
  );

  $entries_array[] = array(
    'id' => 'helpdesk',
    'title' => TEXT_INFO_HELPDESK, 
    'image' => 'mail.png', 
    'href' => tep_href_link(FILENAME_HELPDESK, 'selected_box=helpdesk_box'),
  );

  $entries_array[] = array(
    'id' => 'download',
    'title' => TEXT_INFO_HELPDESK_DOWNLOAD, 
    'image' => 'download.png', 
    'href' => tep_href_link(FILENAME_HELPDESK_POP3, 'selected_box=helpdesk_box'),
  );

  $entries_array[] = array(
    'id' => 'email',
    'title' => TEXT_INFO_HELPDESK_EMAIL, 
    'image' => 'email.png', 
    'href' => tep_href_link(FILENAME_HELPDESK, 'action=view&subaction=new&selected_box=helpdesk_box'),
  );

  $entries_array[] = array(
    'id' => 'blender',
    'title' => TEXT_INFO_HELPDESK_DEPARTMENTS, 
    'image' => 'blender.png', 
    'href' => tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'selected_box=helpdesk_box'),
  );

  $entries_array[] = array(
    'id' => 'plugins',
    'title' => TEXT_INFO_HELPDESK_BOOK, 
    'image' => 'personal.png', 
    'href' => tep_href_link(FILENAME_HELPDESK_BOOK, 'selected_box=helpdesk_box'),
  );

  $entries_array[] = array(
    'id' => 'flags',
    'title' => TEXT_INFO_HELPDESK_PRIORITIES, 
    'image' => 'slabflag.png', 
    'href' => tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'selected_box=helpdesk_box'),
  );

  $entries_array[] = array(
    'id' => 'switching',
    'title' => TEXT_INFO_HELPDESK_STATUS, 
    'image' => 'switching.png', 
    'href' => tep_href_link(FILENAME_HELPDESK_STATUS, 'selected_box=helpdesk_box'),
  );

  $entries_array[] = array(
    'id' => 'configuration',
    'title' => TEXT_INFO_CFG_HELPDESK, 
    'image' => 'configuration.png', 
    'href' => tep_href_link(FILENAME_HELPDESK_CONFIG, 'selected_box=helpdesk_box'),
  );
  $system_start_count = count($entries_array);

  // Amend plugin options
  extract(tep_load('plugins_admin'));
  $plugin_contents = array();
  $args = array('entries_array' => &$plugin_contents);
  $cPlug->invoke('html_home_helpdesk', $args);
  $system_end_count = count($entries_array);
?>
            <div id="index_helpdesk">
<?php
  for($i=0, $j=count($entries_array); $i<$j; $i++) {
    $data_id = $attr = '';
    if( isset($entries_array[$i]['sub']) ) {
      $attr = 'class="sandbox" attr="' . $entries_array[$i]['sub'] . '"';
    }
    if( isset($entries_array[$i]['id']) ) {
      $data_id = ' data-id="' . $entries_array[$i]['id'] . '"';
    }

    if( $i >= $system_start_count && $i < $system_end_count) {
      $class = 'plugin colorblock floater calign';
    } else {
      $class = 'homeCell colorblock floater calign';
    }
?>
              <div class="<?php echo $class; ?>" style="width: 132px; height: 160px;"<?php echo $data_id; ?>><?php echo '<a href="' . $entries_array[$i]['href'] . '" title="' . $entries_array[$i]['title'] . '"' . $attr . '>' . tep_image(DIR_WS_IMAGES . 'categories/' . $entries_array[$i]['image'], $entries_array[$i]['title']) . '<br />' . $entries_array[$i]['title'] . '</a>'; ?></div>
<?php
  }
?>
            </div>
