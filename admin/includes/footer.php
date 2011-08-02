<?php
/*
  $Id: footer.php,v 1.12 2003/02/17 16:54:12 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Catalog: Create Account page
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Modifications:
// - 07/05/2007: PHP5 Register Globals and Long Arrays Off support added
// - 07/08/2007: PHP5 Long Arrays Off support added
// - 10/08/2010: CSS Implementation Added I-Metrics CMS copyright
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
?>
   <div id="footer">
    <div class="calign"><?php echo '<a href="http://demos.asymmetrics.com" title="I-Metrics CMS by Asymmetrics" target="_blank"><b style="color: #CFC">I-Metrics CMS</b></a> &copy; 2009 - ' . date('Y') . ' <a href="http://www.asymmetrics.com" target="_blank" title="Asymmetric Software - Innovation &amp; Excellence"><b>Asymmetric Software</b></a>'; ?></div>
    <div class="calign">
<?php
/*
  The following copyright announcement is in compliance
  to section 2c of the GNU General Public License, and
  thus can not be removed, or can only be modified
  appropriately.

  For more information please read the osCommerce
  Copyright Policy at:

  http://www.oscommerce.com/about/copyright

  Please leave this comment intact together with the
  following copyright announcement.
*/
?>
E-Commerce Engine Copyright &copy; 2003 <a href="http://www.oscommerce.com" target="_blank"><b>osCommerce</b></a> (MS2.2)<br />
osCommerce provides no warranty and is redistributable under the <a href="http://www.fsf.org/licenses/gpl.txt" target="_blank"><b>GNU General Public License</b></a>
    </div>
    <div class="calign"><?php echo 'Copyright &copy; ' . date('Y') . '&nbsp;<a href="' . tep_catalog_href_link() . '" target="_blank"><b>' . STORE_NAME . '</b></a> - All rights reserved.'; ?></div>
  </div>
