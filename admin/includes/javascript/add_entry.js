/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// jQuery: Add an extra HTML element
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with the following:
//
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//
// jQuery JavaScript Library
// http://jquery.com
// Copyright (c) 2009 John Resig
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
var add_entry = {
  add_button: '#add_button',
  add_field: '#add_field',

  launch: function() {
    $(add_entry.add_button).click(function() {
      var extra = $(add_entry.add_field).html();
      extra = extra.replace(new RegExp( "\\*", "g" ), '');
      $(add_entry.add_field).append(extra);
      return false;
    });
  }
}
