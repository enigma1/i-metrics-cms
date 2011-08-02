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
// - Added Plugin General Calls 
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
?>
       <div>
<?php
  $cPlug->invoke('html_left_first');
  require(DIR_FS_BOXES . 'configuration.php');
  require(DIR_FS_BOXES . 'abstract_zones.php');
  require(DIR_FS_BOXES . 'languages.php');
  require(DIR_FS_BOXES . 'helpdesk.php');
  require(DIR_FS_BOXES . 'seo_g.php');
  require(DIR_FS_BOXES . 'meta_g.php');
  require(DIR_FS_BOXES . 'cache.php');
  require(DIR_FS_BOXES . 'plugins.php');
  require(DIR_FS_BOXES . 'tools.php');
  require(DIR_FS_BOXES . 'other.php');
  $cPlug->invoke('html_left_last');
?>
      </div>
