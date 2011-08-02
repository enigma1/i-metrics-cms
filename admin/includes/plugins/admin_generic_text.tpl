<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin System JS: Generic Text script
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
  $base_domain = DEFAULT_BASE_DOMAIN;
  if( empty($base_domain) ) {
    $base_domain = $cDefs->server;
  }
?>
<div><script language="javascript" type="text/javascript">
<?php
  if ($cDefs->action == 'new_generic_text' || $cDefs->action == 'update_generic_text') {
    if( $cSessions->register('g_wp_ifc') ) {
?>
  var jqWrap = tinymce_ifc;
  // Initialize JS variables with PHP parameters to be passed to the js file
  jqWrap.TinyMCE = '<?php echo $cDefs->relpath . DIR_WS_INCLUDES . 'javascript/tiny_mce/tiny_mce.js'; ?>';
  // Point the basefront relative to the admin server to have absolute paths where applicable
  jqWrap.baseFront = '<?php echo $base_domain . DIR_WS_CATALOG; ?>';
  jqWrap.cssFront = '<?php echo $cDefs->crelpath . 'includes/template/stylesheet.css'; ?>';
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.areas = 'gtext_description';
  jqWrap.launch();
<?php
    }
?>
  var jqWrap = image_control;
  var wp = 'gtext_description';
<?php
    if( $cSessions->register('g_wp_ifc') ) {
?>
      wp = tinyMCE;
<?php
    }
?>
  jqWrap.editObject = wp;
  jqWrap.baseFront = '<?php echo $cDefs->server . DIR_WS_CATALOG; ?>';
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.launch();

  var jqWrap = zones_control;
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.launch();

  var jqWrap = templates;
  jqWrap.editObject = wp;
  jqWrap.baseTemplate = '<?php echo tep_href_link(FILENAME_TEMPLATES, 'action=new_template&tID='); ?>';
  jqWrap.launch();

  $('.date_added').datepicker({
    changeMonth: true,
    changeYear: true,
    dateFormat: 'mm/dd/yy'
  });

<?php
  } elseif( empty($cDefs->action) ) {
?>
  var jqWrap = zones_control;
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.launch();

  $('#gtext_search input[name="search"]').liveSearch({
    'method'        : 'GET',
    'url'           : '<?php echo $cDefs->script; ?>',
    'form_id'       : '#gtext_search'
  });

  $('#gtext_table').dragtable({
    maxMovingRows: 1,
    restoreState: '<?php echo tep_href_link($cDefs->script, 'action=columns'); ?>',
    persistState: '<?php echo tep_href_link($cDefs->script, 'action=set_columns'); ?>'
  });
<?php
  }
?>
</script></div>