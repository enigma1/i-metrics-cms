/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// jQuery: Direct Management Selection Popup
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
var dm_popup = {
  baseURL: false,

  launch: function() {

    $('#dm_popup').live('click', function(e) {
      e.preventDefault();

      var url = $(this).attr('href')+'&action=direct_management_select';
      var title = $(this).text();

      $.fancybox({
        'type'          : 'ajax',
        'href'          : url,
        'autoScale'     : true,
        'transitionIn'  : 'elastic',
        'transitionOut' : 'elastic',
        'title'         : title
      });
    });
  }
}
