<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front Plugin: Right Column System template for collections
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
  for( $i=0, $j=count($total_array); $i<$j; $i++ ) {
    $id = $total_array[$i]['id'];
    $name = $total_array[$i]['name'];
    $href = $total_array[$i]['href'];
    $text = $total_array[$i]['text'];
    $class_contents = ($i%2)?'infoBoxContents':'infoBoxContents infoBoxContentsAlt';
?>
            <div class="infoBox">
              <div class="infoBoxHeading"><?php echo '<h2><a href="' . $href . '" title="' . $name . '">' . $name . '</a></h2>'; ?></div>
              <div class="<?php echo $class_contents; ?>"><?php echo $text; ?></div>
              <div class="<?php echo $class_contents; ?>"><?php echo '<a href="' . $href . '" title="' . $name . '">' . sprintf($cStrings->TEXT_READ_MORE, $name) . '</a>'; ?></div>
            </div>
<?php
  }
?>