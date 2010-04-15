<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// Common html header-lower section
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
</head>
<!-- body //-->
<?php
  $params = $focus = $calendar = '';
  if( isset($set_focus) ) {
    $focus = 'SetFocus();';
  } 
  if(isset($set_calendar) ) {
    $calendar = 'init_calendar();';
  }
  $tmp_params = $focus . $calendar;
  if(strlen($tmp_params) ) {
    $params = ' onload="' . $tmp_params . '"';
  }
  echo '<body' . $params . '>' . "\n";
?>
  <div id="wrapper">
<?php
  if( basename($PHP_SELF) != FILENAME_DEFAULT ) {
?>
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <div class="cleaner">
      <div id="leftpane"><?php require(DIR_WS_INCLUDES . 'column_left.php'); ?></div>
      <div id="mainpane">
<?php
  } else {
?>
    <div class="main" style="width: 860px; overflow: hidden; margin: auto;">
<?php
  }
  // Display Script specific notices
  $messageStack->output();
/*
  if( defined('HELP_SHOT') ) {
?>
<script language="javascript" type="text/javascript">
  $("div#help_image_group a").fancybox({
    'titleShow'     : false,
    'transitionIn'  : 'elastic',
    'transitionOut' : 'elastic'
  });
</script>
<?php
  }
*/
?>