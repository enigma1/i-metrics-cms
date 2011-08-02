<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Newsletter System jscripts
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
?>
<div><script language="javascript" type="text/javascript">
<?php
  if( $cDefs->script == FILENAME_NEWSLETTERS && ($cDefs->action == 'edit' || $cDefs->action == 'template_upload') ) {

    $base_domain = DEFAULT_BASE_DOMAIN;
    $base_front = $base_domain . DIR_WS_CATALOG;
    $css_front = $cDefs->crelpath . 'includes/template/stylesheet.css';

    if($cSessions->register('g_wp_ifc')) {
?>
  var jqWrap = tinymce_ifc;
  // Initialize JS variables with PHP parameters to be passed to the js file
  jqWrap.TinyMCE = '<?php echo $cDefs->relpath . DIR_WS_JS . 'tiny_mce/tiny_mce.js'; ?>';
  // Point the basefront relative to the admin server to have absolute paths where applicable
  jqWrap.baseFront = '<?php echo $base_front; ?>';
  jqWrap.cssFront = '<?php echo $css_front; ?>';
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.areas = 'template_description';
  jqWrap.launch();
<?php
    }
?>
  var jqWrap = image_control;
  var wp = 'template_content_description';
<?php
    if($cSessions->register('g_wp_ifc')) {
?>
      wp = tinyMCE;
<?php
    }
?>
  jqWrap.editObject = wp;
  jqWrap.baseFront = '<?php echo $cDefs->server . DIR_WS_CATALOG; ?>';
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  //jqWrap.templateSelector = 'template_select';
  jqWrap.launch();
<?php
  } elseif( empty($cDefs->action) || $cDefs->action == 'search' ) {
?>
  $('#newsletter_search input[name="search"]').liveSearch({
    'method'        : 'GET',
    'url'           : '<?php echo $cDefs->script; ?>',
    'form_id'       : '#template_search'
  });

  $('#newsletter_table').dragtable({
    maxMovingRows: 1,
    restoreState: '<?php echo tep_href_link($cDefs->script, 'action=columns'); ?>',
    persistState: '<?php echo tep_href_link($cDefs->script, 'action=set_columns'); ?>'
  });

<?php
  }
?>
</script></div>
