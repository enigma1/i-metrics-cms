<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front Plugin: Comments System template comments list
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
  $rstring = $cStrings->TEXT_TOTAL_RATING_EMPTY;
  if( $rating_array['total_resolution'] > 1 ) {
    $r = (100*ceil($rating_array['total_rating'])/($rating_array['total_resolution']-1)) . '%';
    $rstring = sprintf($cStrings->TEXT_TOTAL_RATING, $r);
  }
  $j = count($comments_array);
  $string = $j?sprintf($cStrings->TEXT_TOTAL_COMMENTS, $j, $comments_title, $rstring):sprintf($cStrings->TEXT_TOTAL_NO_COMMENTS, $comments_title);
?>
          <div class="cleaner vspacer">
            <div class="lcharsep" style="background: #E2E6BF; margin: 12px 0px; border: 1px solid #777;"><h2><i><?php echo $string; ?></i></h2></div>
          </div>
<?php
  for($i=0; $i<$j; $i++ ) {
    $entry = $comments_array[$i];

    $post_author = $entry['comments_author'];
    $post_date = tep_date_short($entry['date_added']);
    if( !empty($entry['comments_url']) ) {
      $post_author = '<a href="' . $entry['comments_url'] . '" rel="nofollow">' . $post_author . '</a>' . "\n";
    } else {
      $post_author = '<span style="color: #000077">' . $post_author . '</span>' . "\n";
    }
?>
          <div class="splitColumn">
            <div class="floater"><?php echo $cStrings->TEXT_FROM . '&nbsp;' . $post_author; ?></div>
            <div class="floatend"><?php echo $post_date; ?></div>
            <div class="desc cleaner"><?php echo $entry['comments_body']; ?></div>
          </div>
<?php
  }
  if( !$this->form_show ) {
?>
          <div class="cleaner vspacer">
            <div class="lcharsep" style="background: #FFF; margin: 12px 0px; border: 1px solid #777;"><h2><?php echo $cStrings->TEXT_LOCKED_COMMENTS; ?></i></div>
          </div>
<?php
  }
?>
