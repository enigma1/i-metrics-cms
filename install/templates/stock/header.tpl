<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Image Zones class
//----------------------------------------------------------------------------
// Front: Main Header Section
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
?>
  <div class="totalsize" id="header">
    <div class="leftsize logo floater">
      <div class="leftlogo floater"><?php echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_TEMPLATE . 'design/logo.png', STORE_NAME) . '</a>'; ?></div>
      <div class="floater" style="color: #FFC; font-size: 10px; padding-left: 2px; padding-top: 18px; font-weight:bold;"><?php echo 'Version 1.11'; ?></div>
      <div class="quicksearch floatend"><?php echo tep_draw_form('quick_find', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'post'); ?>
<?php 
  echo tep_draw_input_field('keywords', 'Search this site', 'class="search" size="50" maxlength="100" style="width: 160px"') . tep_image_submit(DIR_WS_TEMPLATE . 'design/search.png', IMAGE_BUTTON_SEARCH, 'style="margin: 0px 0px -4px 8px;"', true);
?>
      </form></div>
      <div class="cleaner">
        <div class="breadcrumb lcharsep">
<?php
  $string = $breadcrumb->trail();
  if( empty($string) ) {
    $breadcrumb->add(HEADER_TITLE_CATALOG, tep_href_link());
    $string = $breadcrumb->trail();
  }
  echo $string; 
?>
        </div>
<?php
  if($g_script == FILENAME_DEFAULT || $current_gtext_id) {
?>
        <div class="calign" style="margin:0px -1px;"><table class="colorinvert wider" style="background:#C7CED6;" cellspacing="1">
          <tr style="background: #7A808C;">
            <td>Open Source Code</td>
            <td>Create Text Pages</td>
            <td>Text Collections</td>
            <td>Image Collections</td>
          </tr>
          <tr style="background: #8C7A80;">
            <td>Advanced Administration</td>
            <td>Plugins-Manager</td>
            <td>Multi-Sites Manager</td>
            <td>Marketing Tools</td>
          </tr>
          <tr style="background: #7A808C;">
            <td colspan="2" style="background:#7A303C;"><a href="http://sourceforge.net/projects/i-metrics-cms/forums" rel="nofollow" title="I-Metrics CMS Forums">Forums</a></td>
            <td colspan="2" style="background:#3A503C;"><a href="http://sourceforge.net/projects/i-metrics-cms/" rel="nofollow" title="I-Metrics CMS Download at sourceforge.net">Download</a></td>
          </tr>
<?php
/*
            <td>Send/Receive E-Mails</td>
            <td>Multi-Sites Manager</td>
        //<div class="mainlogo calign">
  //echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_TEMPLATE . 'banner1.png', STORE_NAME) . '</a>'; 
*/
?>
        </table></div>
<?php
  } else {
?>
        <div class="mainlogo calign"><?php echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_TEMPLATE . 'design/banner1.png', STORE_NAME) . '</a>'; ?></div>
<?php
  }
?>
      </div>

    </div>
    <div class="floatend" id="rightheader">
      <div class="rightheading"><a style="color: #EEE; font-size: 13px;" href="http://sourceforge.net/project/showfiles.php?group_id=31957&amp;package_id=74386&amp;release_id=440294" target="_blank">osCommerce MS2.2 Engine</a></div>
      <div class="rightlogo calign floater" style="background: #999999; width: 199px;"><?php echo tep_image(DIR_WS_TEMPLATE . 'design/logo-news.png', STORE_NAME); ?></div>
      <div class="rightlogo calign floater" style="background: #333333; width: 65px;"><?php echo '<a href="' . tep_href_link() . '">' . tep_image(DIR_WS_TEMPLATE . 'design/i-metrics-cms-v.png', STORE_NAME) . '</a>'; ?></div>
    </div>
  </div>
