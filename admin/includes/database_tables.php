<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Database Definition Tables
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
  //define('TABLE_VOTES', 'votes');

//-MS- Abstract Zones Support Added
  define('TABLE_ABSTRACT_ZONES', 'abstract_zones');
  define('TABLE_ABSTRACT_TYPES', 'abstract_types');

  define('TABLE_GTEXT', 'gtext');
  define('TABLE_GTEXT_TO_DISPLAY', 'gtext_to_display');

  define('TABLE_SUPER_ZONES', 'super_zones');
  define('TABLE_IMAGE_ZONES', 'image_zones');
//-MS- Abstract Zones Support Added EOM

//-MS- SEO-G Added
  define('TABLE_SEO_URL', 'seo_url');
  define('TABLE_SEO_CACHE', 'seo_cache');
  define('TABLE_SEO_REDIRECT', 'seo_redirect');
  define('TABLE_SEO_EXCLUDE', 'seo_exclude');
  define('TABLE_SEO_FREQUENCY', 'seo_frequency');
  define('TABLE_SEO_TYPES', 'seo_types');
  define('TABLE_SEO_TO_GTEXT','seo_to_gtext');
  define('TABLE_SEO_TO_ABSTRACT','seo_to_abstract');
  define('TABLE_SEO_TO_FILTERS','seo_to_filters');
  define('TABLE_SEO_TO_SCRIPTS','seo_to_scripts');
//-MS- SEO-G Added EOM

//-MS- META-G Added
  define('TABLE_META_SCRIPTS', 'meta_scripts');
  define('TABLE_META_LEXICO', 'meta_lexico');
  define('TABLE_META_EXCLUDE', 'meta_exclude');
  define('TABLE_META_TYPES', 'meta_types');
  define('TABLE_META_GTEXT', 'meta_gtext');
  define('TABLE_META_ABSTRACT', 'meta_abstract');
  define('TABLE_META_FILTERS', 'meta_filters');
//-MS- META-G Added EOM

//-MS- Cache Support added
  define('TABLE_CACHE_HTML', 'cache_html');
  define('TABLE_CACHE_HTML_REPORTS', 'cache_html_reports');
//-MS- Cache Support added


//-MS- Help Desk Added
  define('TABLE_HELPDESK_ENTRIES', 'helpdesk_entries');
  define('TABLE_HELPDESK_DEPARTMENTS', 'helpdesk_departments');
  define('TABLE_HELPDESK_PRIORITIES', 'helpdesk_priorities');
  define('TABLE_HELPDESK_STATUSES', 'helpdesk_statuses');
  define('TABLE_HELPDESK_TEMPLATES', 'helpdesk_templates');
  define('TABLE_HELPDESK_TICKETS', 'helpdesk_tickets');
  define('TABLE_HELPDESK_ATTACHMENTS', 'helpdesk_attachments');
//-MS- Help Desk Added EOM

//-MS- Plugins
  define('TABLE_PLUGINS', 'plugins');
//-MS- Plugins EOM

  define('TABLE_SESSIONS_ADMIN', 'sessions_admin');

//-MS- Original osCommerce MS2.2 Tables
  define('TABLE_CONFIGURATION', 'configuration');
  define('TABLE_CONFIGURATION_GROUP', 'configuration_group');
  define('TABLE_SESSIONS', 'sessions');
  define('TABLE_WHOS_ONLINE', 'whos_online');
//-MS- Original osCommerce MS2.2 Tables EOM

  define('TABLE_FORM_FIELDS', 'form_fields');
  define('TABLE_FORM_OPTIONS', 'form_options');
  define('TABLE_FORM_VALUES', 'form_values');
?>
