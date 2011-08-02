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
  $subaction = (isset($_GET['subaction']) ? $_GET['subaction'] : '');
  if( $cDefs->action == 'view' && ($subaction == 'edit' || $subaction == 'reply' || $subaction == 'new') ) {
    if($cSessions->register('g_wp_ifc')) {
?>
  var jqWrap = tinymce_ifc;
  // Initialize JS variables with PHP parameters to be passed to the js file
  jqWrap.TinyMCE = '<?php echo $cDefs->relpath . DIR_WS_JS . 'tiny_mce/tiny_mce.js'; ?>';
  // Point the basefront relative to the admin server to have absolute paths where applicable
  jqWrap.baseFront = '<?php echo $base_domain . DIR_WS_CATALOG; ?>';
  jqWrap.cssFront = '<?php echo $cDefs->crelpath . 'includes/template/stylesheet.css'; ?>';
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.areas = 'body';
  jqWrap.launch();
<?php
    }
?>
/*
  $('#reply_to_section input[name="to_name"]').liveSearch({
    'id'            : 'jquery-live-search',
    'method'        : 'POST',
    'url'           : '<?php echo $cDefs->script; ?>?action=search_book_names',
    'form_id'       : '#reply_form'
  });

  $('#reply_to_email_label input[name="to_email_address"]').liveSearch({
    'id'            : 'jquery-live-search2',
    'method'        : 'POST',
    'url'           : '<?php echo $cDefs->script; ?>?action=search_book_emails',
    'form_id'       : '#reply_form'
  });
*/
  var jqWrap = image_control;
  var wp = 'body';
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
  jqWrap.launch();

  var jqWrap = zones_control;
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_JS_MODULES); ?>';
  jqWrap.launch();

  var jqWrap = templates;
  jqWrap.editObject = wp;
  jqWrap.baseTemplate = '<?php echo tep_href_link(FILENAME_TEMPLATES, 'action=new_template&tID='); ?>';
  jqWrap.launch();

  var jqWrap = helpdesk;
  jqWrap.baseURL = '<?php echo tep_href_link(FILENAME_HELPDESK, 'action=reply_to_label'); ?>';
  jqWrap.fromURL = '<?php echo tep_href_link(FILENAME_HELPDESK, 'action=reply_from_email'); ?>';
  jqWrap.launch();

  $('#date_added').datepicker({
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


  $('#gtext_table').dragtable({
    maxMovingRows: 1,
    restoreState: '<?php echo tep_href_link($cDefs->script, 'action=columns'); ?>',
    persistState: '<?php echo tep_href_link($cDefs->script, 'action=set_columns'); ?>'
  });
<?php
  }
?>
</script></div>