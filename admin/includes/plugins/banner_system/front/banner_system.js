/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// jQuery: Banner System clicks and impressions monitoring
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
var banner_system = {
  baseURL: false,
  atag: false,

  launch: function() {

    $(banner_system.atag).click(function() {
      var url = banner_system.baseURL+'?action=click';
      var post_data = 'attr='+$(this).attr('attr')+'&banner_system=click';

      $.ajax({
        type: 'POST',
        url: url,
        data: post_data,
        dataType: 'html',
        complete: function(msg){
        },
        success: function(msg) {
        }
      });
      return true;

    });

    $(banner_system.atag).each(function(index) {
      var post_data = 'attr='+$(this).attr('attr')+'&banner_system=impression';

      $.ajax({
        type: 'POST',
        url: banner_system.baseURL+'?action=impression',
        data: post_data,
        dataType: 'html',
        complete: function(msg){
        },
        success: function(msg) {
        }
      });

    });

  }
}
