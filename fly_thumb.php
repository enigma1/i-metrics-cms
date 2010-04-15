<?php
/*
// This is "On the Fly Thumbnailer with Caching Option" by Pallieter Koopmans.
// Based on Marcello Colaruotolo (1.5.1) which builds upon Nathan Welch (1.5)
// and Roberto Ghizzi. With improvements by @Quest WebDesign, http://atQuest.nl/
//
// Scales product images dynamically, resulting in smaller file sizes, and keeps
// proper image ratio.
//----------------------------------------------------------------------------
// Modifications by Asymmetrics
// Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Modified file to add cache control and header validation
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  require('includes/configure.php');
//
// CONFIGURATION SETTINGS
//
// Use Resampling? Set the value below to true to generate resampled thumbnails
// resulting in smoother-looking images. Not supported in GD ver. < 2.01
//$use_resampling = true;
//
// Create True Color Thumbnails? Better quality overall but set to false if you
// have GD version < 2.01 or if creating transparent thumbnails.
$use_truecolor = true;
//
// Output GIFs as JPEGS? Set this option to true if you have GD version > 1.6
// and want to output GIF thumbnails as JPGs instead of GIFs or PNGs. Note that your
// GIF transparencies will not be retained in the thumbnail if you output them
// as JPGs. If you have GD Library < 1.6 with GIF create support, GIFs will
// be output as GIFs. Set the "matte" color below if setting this option to true.
$gif_as_jpeg = false;
//
// Cache Images? Set to true if you want to create cached images for each thumbnail.
// This will add to disk space but will save your processor from having to create
// the thumbnail for every visitor.
$tn_cache = true;
//
// Define RGB Color Value for background matte color if outputting GIFs as JPEGs
// Example: white is r=255, b=255, g=255; black is r=0, b=0, g=0; red is r=255, b=0, g=0;
$r = 255; // Red color value (0-255)
$g = 255; // Green color value (0-255)
$b = 255; // Blue color value (0-255)
//
// Allow the creation of thumbnail images that are larger than the original images:
$allow_larger = false; // The default is false.
// If allow_larger is set to false, you can opt to output the original image:
// Better leave it true if you want pixel_trans_* to work as expected
$show_original = true; // The default is true.

$pre_ext = '-fly-thumb-';
$pre_path = 'images/thumbs/';
//
// END CONFIGURATION SETTINGS

// Note: In order to manually debug this script, you might want to comment
// the three header() lines -- otherwise no output is shown.

  if( isset($_GET['no_cache']) ) {
    $tn_cache = false;
  }
  $local_image = DIR_FS_CATALOG . $_GET['img'];
  $tmp_array = explode('.',basename($_GET['img']));

  if( empty($_GET['w']) || empty($_GET['h']) || strpos($_GET['img'], 'images/') != 0 || !file_exists($local_image) || !is_array($tmp_array) || count($tmp_array) != 2 || strlen($tmp_array[0]) < 1 ) {
    header('Content-type: image/jpeg');
    $src = imagecreate(75, 150); // Create a blank image
    $bgc = imagecolorallocate($src, 255, 255, 255);
    $tc  = imagecolorallocate($src, 0, 0, 0);
    imagefilledrectangle($src, 0, 0, 75, 150, $bgc);
    imagestring($src, 1, 5, 5, 'Error', $tc);
    imagejpeg($src, '', 75);
    exit();
  }

  $base_image = $pre_path . $tmp_array[0];
  $image = @getimagesize($local_image);
// Check the input variables and decide what to do:
  if( empty($image) || (empty($allow_larger) && ($_GET['w'] > $image[0] || $_GET['h'] > $image[1]))) {
    if (empty($image) || empty($show_original)) {
      // Originally a simple return was given, now we show an error image:
      header('Content-type: image/jpeg');
      $src = imagecreate(75, 150); // Create a blank image
      $bgc = imagecolorallocate($src, 255, 255, 255);
      $tc  = imagecolorallocate($src, 0, 0, 0);
      imagefilledrectangle($src, 0, 0, 75, 150, $bgc);
      imagestring($src, 1, 5, 5, 'Error', $tc);
      imagejpeg($src, '', 75);
      exit();
    } else {
      // 2Do: Return the original image w/o making a copy (as that is what we currently do):
      $_GET['w'] = $image[0];
      $_GET['h'] = $image[1];
    }
  }

  $_GET['w'] = (int)$_GET['w'];
  $_GET['h'] = (int)$_GET['h'];
  if( !$_GET['w'] || !$_GET['h'] ) {
    header('Content-type: image/jpeg');
    $src = imagecreate(75, 150); // Create a blank image
    $bgc = imagecolorallocate($src, 255, 255, 255);
    $tc  = imagecolorallocate($src, 0, 0, 0);
    imagefilledrectangle($src, 0, 0, 75, 150, $bgc);
    imagestring($src, 1, 5, 5, 'Error', $tc);
    imagejpeg($src, '', 75);
    exit();
  }

  $image_time = filemtime($local_image);
  $filename = DIR_FS_CATALOG . $base_image . $pre_ext . $_GET['w'].'x'.$_GET['h'].'.jpg';
  if( file_exists($filename) ) {
    $thumb_time = filemtime($filename);
    if($thumb_time < $image_time) {
      unlink($filename);
    }
  }

  $oldtime = time() - 86400;

  if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);

    $expiry = strtotime($if_modified_since);
    if($expiry > $oldtime) {
      $expiry = gmdate('D, d M Y H:i:s', $expiry+86400).' GMT';
      header('Pragma: private');
      header("Expires: " . $expiry);
      header('Cache-Control: must-revalidate, max-age=86400, s-maxage=86400, private');
      header('HTTP/1.1 304 Not Modified');
      exit();
    }
  }

  $last_modified = gmdate('D, d M Y H:i:s').' GMT';
  $newtime = time() + 86400;
  $expiry = gmdate('D, d M Y H:i:s', $newtime).' GMT';
  header("Last-Modified: " . $last_modified);
  header("Expires: " . $expiry);
  header('Cache-Control: must-revalidate, max-age=86400, s-maxage=86400, private');

// Create appropriate image header:
  if ($image[2] == 2 || ($image[2] == 1 && $gif_as_jpeg)) {
    header('Content-type: image/jpeg');
    if ($tn_cache) $filename = $base_image . $pre_ext . $_GET['w'].'x'.$_GET['h'].'.jpg';
  } elseif ($image[2] == 1 && function_exists('imagegif')) {
    header('Content-type: image/gif');

    if ($tn_cache) $filename = $base_image . $pre_ext . $_GET['w'].'x'.$_GET['h'].'.gif';
  } elseif ($image[2] == 3 || $image[2] == 1) {
    header('Content-type: image/png');
    if ($tn_cache) $filename = $base_image . $pre_ext .$_GET['w'].'x'.$_GET['h'].'.png';
  }

// If you are required to set the full path for file_exists(), set this:
// $filename = '/your/path/to/catalog/'.$filename;

  $cached_time = time() - 2592000;

  if (file_exists($filename) && $tn_cache && filemtime($filename) > filemtime($local_image) && filemtime($filename) > $cached_time) {
    if( $image[2] == 2 || ($image[2] == 1 && $gif_as_jpeg) ) {
      $src = imagecreatefromjpeg($filename);
      imagejpeg($src, '', 100);
    } elseif ($image[2] == 1 && function_exists('imagegif')) {
      $src = imagecreatefromgif($filename);
      imagegif($src);
    } elseif ($image[2] == 3 || $image[2] == 1) {
      $src = imagecreatefrompng($filename);
      imagepng($src);
    } else {
        $src = imagecreate($_GET['w'], $_GET['h']); // Create a blank image
        $bgc = imagecolorallocate($src, 255, 255, 255);
        $tc  = imagecolorallocate($src, 0, 0, 0);
        imagefilledrectangle($src, 0, 0, $_GET['w'], $_GET['h'], $bgc);
        imagestring($src, 1, 5, 5, 'Error', $tc);
        imagejpeg($src, '', 75);
        exit();
    }
  } else {
    // Create a new, empty image based on settings:
    if (function_exists('imagecreatetruecolor') && $use_truecolor && ($image[2] == 2 || $image[2] == 3)) {
      $tmp_img = imagecreatetruecolor($_GET['w'],$_GET['h']);
    } else {
      $tmp_img = imagecreate($_GET['w'],$_GET['h']);
    }

    $th_bg_color = imagecolorallocate($tmp_img, $r, $g, $b);

    imagefill($tmp_img, 0, 0, $th_bg_color);
    imagecolortransparent($tmp_img, $th_bg_color);

    // Create the image to be scaled:
    if ($image[2] == 2 && function_exists('imagecreatefromjpeg')) {
      $src = imagecreatefromjpeg($local_image);
    } elseif ($image[2] == 1 && function_exists('imagecreatefromgif')) {
      $src = imagecreatefromgif($local_image);
    } elseif (($image[2] == 3 || $image[2] == 1) && function_exists('imagecreatefrompng')) {
      $src = imagecreatefrompng($local_image);
    } else {
      $src = imagecreate($_GET['w'], $_GET['h']); // Create a blank image.
      $bgc = imagecolorallocate($src, 255, 255, 255);
      $tc  = imagecolorallocate($src, 0, 0, 0);
      imagefilledrectangle($src, 0, 0, $_GET['w'], $_GET['h'], $bgc);
      imagestring($src, 1, 5, 5, 'Error', $tc);
      imagejpeg($src, '', 75);
      exit();
    }

    if( !function_exists('imagecopyresampled') || $image[2] == 1 ) {
      imagecopyresampledbicubic($tmp_img, $src, 0, 0, 0, 0, $_GET['w'], $_GET['h'], $image[0], $image[1]);
    } else {
      imagecopyresampled($tmp_img, $src, 0, 0, 0, 0, $_GET['w'], $_GET['h'], $image[0], $image[1]);
    }

    // Scale the image based on settings:
//    if (function_exists('imagecopyresampled') && $use_resampling) {
//      imagecopyresampled($tmp_img, $src, 0, 0, 0, 0, $_GET['w'], $_GET['h'], $image[0], $image[1]);
//    } else {
//      imagecopyresized($tmp_img, $src, 0, 0, 0, 0, $_GET['w'], $_GET['h'], $image[0], $image[1]);
//    }

    // Output the image:
    if ($image[2] == 2 || ($image[2] == 1 && $gif_as_jpeg)) {
      imagejpeg($tmp_img, '', 100);
      if ($tn_cache) imagejpeg($tmp_img,$base_image . $pre_ext . $_GET['w'].'x'.$_GET['h'].'.jpg', 100);
    } elseif ($image[2] == 1 && function_exists('imagegif')) {
      imagegif($tmp_img);
      if ($tn_cache) imagegif($tmp_img,$base_image . $pre_ext . $_GET['w'].'x'.$_GET['h'].'.gif');
    } elseif ($image[2] == 3 || $image[2] == 1) {
      imagepng($tmp_img);
      if ($tn_cache) imagepng($tmp_img,$base_image . $pre_ext . $_GET['w'].'x'.$_GET['h'].'.png');
    } else {
      $src = imagecreate($_GET['w'], $_GET['h']); // Create a blank image.
      $bgc = imagecolorallocate($src, 255, 255, 255);
      $tc  = imagecolorallocate($src, 0, 0, 0);
      imagefilledrectangle($src, 0, 0, $_GET['w'], $_GET['h'], $bgc);
      imagestring($src, 1, 5, 5, 'Error', $tc);
      imagejpeg($src, '', 75);
      exit();
    }
    // Clear the image from memory:
    imagedestroy($src);
    imagedestroy($tmp_img);
  }

//-MS- imagecopyresampledbicubic Came from http://www.php.net/manual/en/function.imagecopyresampled.php
// by matt1walsh DESPAMMER gmail dot com
  function imagecopyresampledbicubic(&$dst_image, &$src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)  {
    // we should first cut the piece we are interested in from the source
    $src_img = imagecreatetruecolor($src_w, $src_h);
    imagecopy($src_img, $src_image, 0, 0, $src_x, $src_y, $src_w, $src_h);

    // this one is used as temporary image
    $dst_img = imagecreatetruecolor($dst_w, $dst_h);

    imagepalettecopy($dst_img, $src_img);
    $rX = $src_w / $dst_w;
    $rY = $src_h / $dst_h;
    $w = 0;
    for ($y = 0; $y < $dst_h; $y++)  {
      $ow = $w; $w = round(($y + 1) * $rY);
      $t = 0;
      for ($x = 0; $x < $dst_w; $x++)  {
        $r = $g = $b = 0; $a = 0;
        $ot = $t; $t = round(($x + 1) * $rX);
        for ($u = 0; $u < ($w - $ow); $u++)  {
          for ($p = 0; $p < ($t - $ot); $p++)  {
            $c = imagecolorsforindex($src_img, imagecolorat($src_img, $ot + $p, $ow + $u));
            $r += $c['red'];
            $g += $c['green'];
            $b += $c['blue'];
            $a++;
          }
        }
        imagesetpixel($dst_img, $x, $y, imagecolorclosest($dst_img, $r / $a, $g / $a, $b / $a));
      }
    }

    // apply the temp image over the returned image and use the destination x,y coordinates
    imagecopy($dst_image, $dst_img, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h);
    // we should return true since imagecopyresampled/imagecopyresized do it
    return true;
  }
//-MS- imagecopyresampledbicubic EOM
?>