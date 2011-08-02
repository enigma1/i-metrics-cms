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
  $restore = $persist = null;
  $cAbstract = new abstract_zones;
  if( $cAbstract->is_top_level() ) {
    $restore = tep_href_link($cDefs->script, 'action=columns');
    $persist = tep_href_link($cDefs->script, 'action=set_columns');
  }
?>
<div><script language="javascript" type="text/javascript">
  $('#abstract_table').dragtable({
    maxMovingRows:1,
    restoreState: '<?php echo $restore; ?>',
    persistState: '<?php echo $persist; ?>'
  });
</script></div>