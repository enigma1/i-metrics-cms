<?php
/*
  $Id: column_left.php,v 1.15 2002/01/11 05:03:25 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

//----------------------------------------------------------------------------
// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2007-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Reviews page
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - Added CMS and SEO components
// - Removed Products related boxes
// - Changed HTML removed tables, added DIVs
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
?>
         <div>
<?php
  require(DIR_WS_BOXES . 'configuration.php');
  require(DIR_WS_BOXES . 'abstract_zones.php');
  require(DIR_WS_BOXES . 'helpdesk.php');
  require(DIR_WS_BOXES . 'seo_g.php');
  require(DIR_WS_BOXES . 'meta_g.php');
  require(DIR_WS_BOXES . 'cache.php');
  require(DIR_WS_BOXES . 'plugins.php');
  require(DIR_WS_BOXES . 'tools.php');
  require(DIR_WS_BOXES . 'other.php');
  //require(DIR_WS_BOXES . 'history.php');
  $g_plugins->invoke('html_left_last');
?>
        </div>
