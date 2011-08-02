<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Language and Country support code
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
// look in your $PATH_LOCALE/locale directory for available locales
// or type locale -a on the server.
// Examples:
// on RedHat try 'en_US'
// on FreeBSD try 'en_US.ISO_8859-1'
// on Windows try 'en', or 'English'
// @setlocale(LC_TIME, 'en_US.ISO_8859-1');
@setlocale(LC_TIME, 'en_US.UTF-8');

////
// Return date in raw format
// $date should be in format mm/dd/yyyy
// raw date is in format YYYYMMDD, or DDMMYYYY
function tep_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . substr($date, 0, 2) . substr($date, 3, 2);
  }
}

// This is the current language translation table that can be used by various modules 
// to convert strings into ascii. 
// For example the integrated SEO friendly URLs generator uses it 
// as it strips language specific characters by default
function translate_to_ascii($string) {
  // Using a trnaslation table
  $translation_table = array(
    chr(0) => '0', chr(1) => '1', chr(2) => '2', chr(3) => '3', chr(4) => '4', chr(5) => '5', chr(6) => '3', chr(7) => '7',
    chr(8) => '8', chr(9) => ' ', chr(10)=> ' ', chr(11) =>' ', chr(12)=> ' ', chr(13)=> ' ', chr(14)=> ' ', chr(15)=> ' ',
    chr(16)=> '8', chr(17)=> ' ', chr(18)=> ' ', chr(19) =>' ', chr(20)=> ' ', chr(21)=> ' ', chr(22)=> ' ', chr(23)=> ' ',
    chr(24)=> '8', chr(25)=> ' ', chr(26)=> ' ', chr(27) =>' ', chr(28)=> ' ', chr(29)=> ' ', chr(30)=> ' ', chr(31)=> ' ',
    chr(128)=>'C', chr(129)=>'u', chr(130)=>'e', chr(131)=>'a', chr(132)=>'a', chr(133)=>'a', chr(134)=>'a', chr(135)=>'c', 
    chr(136)=>'e', chr(137)=>'e', chr(138)=>'e', chr(139)=>'i', chr(140)=>'i', chr(141)=>'i', chr(142)=>'A', chr(143)=>'A', 
    chr(144)=>'A', chr(145)=>'E', chr(146)=>'e', chr(147)=>'E', chr(148)=>'E', chr(149)=>'E', chr(150)=>'E', chr(151)=>'E',
    chr(152)=>'A', chr(153)=>'E', chr(154)=>'e', chr(155)=>'E', chr(156)=>'E', chr(157)=>'E', chr(158)=>'E', chr(159)=>'E',
    chr(160)=>'E', chr(161)=>'E', chr(162)=>'A', chr(163)=>'L', chr(164)=>'E', chr(165)=>'L', chr(166)=>'S', chr(167)=>'E', 
    chr(168)=>'E', chr(169)=>'S', chr(170)=>'S', chr(171)=>'T', chr(172)=>'Z', chr(173)=>'E', chr(174)=>'Z', chr(175)=>'Z',
    chr(176)=>'E', chr(177)=>'a', chr(178)=>'E', chr(179)=>'l', chr(180)=>'E', chr(181)=>'l', chr(182)=>'s', chr(183)=>'E', 
    chr(184)=>'E', chr(185)=>'s', chr(186)=>'s', chr(187)=>'t', chr(188)=>'z', chr(189)=>'E', chr(190)=>'z', chr(191)=>'z',
    chr(192)=>'R', chr(193)=>'A', chr(194)=>'A', chr(195)=>'A', chr(196)=>'A', chr(197)=>'L', chr(198)=>'C', chr(199)=>'C', 
    chr(200)=>'C', chr(201)=>'E', chr(202)=>'E', chr(203)=>'E', chr(204)=>'E', chr(205)=>'I', chr(206)=>'I', chr(207)=>'D', 
    chr(208)=>'D', chr(209)=>'N', chr(210)=>'N', chr(211)=>'O', chr(212)=>'O', chr(213)=>'O', chr(214)=>'O', chr(215)=>'A',
    chr(216)=>'R', chr(217)=>'U', chr(218)=>'U', chr(219)=>'U', chr(220)=>'U', chr(221)=>'Y', chr(222)=>'T', chr(223)=>'s', 
    chr(224)=>'r', chr(225)=>'a', chr(226)=>'a', chr(227)=>'a', chr(228)=>'a', chr(229)=>'l', chr(230)=>'c', chr(231)=>'c', 
    chr(232)=>'c', chr(233)=>'e', chr(234)=>'e', chr(235)=>'e', chr(236)=>'e', chr(237)=>'i', chr(238)=>'i', chr(239)=>'d', 
    chr(240)=>'d', chr(241)=>'n', chr(242)=>'n', chr(243)=>'o', chr(244)=>'o', chr(245)=>'o', chr(246)=>'o', chr(247)=>'A',
    chr(248)=>'r', chr(249)=>'u', chr(250)=>'u', chr(251)=>'u', chr(252)=>'u', chr(253)=>'y', chr(254)=>'t', chr(255)=>'A',
  );
  return strtr($string, $translation_table);

  // Using function eg: utf8_decode
  //return utf8_decode($string);
} 
?>
