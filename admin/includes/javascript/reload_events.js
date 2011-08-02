/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// jQuery: Events using ajax but cannot be invoked as live methods
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
var reload_events = {

  launch: function() {

    $('input').click(function(e) {
      e.stopPropagation();
    });

    // Page splitter support with GET parameters
    $('select.change_submit').change(function() {
      var url = $(location).attr('href');
      var $parent = $(this).closest("form");
      var parameters = $(':input', $parent).serialize();
      var tmp_array = url.split('?');
      url = tmp_array[0] + '?' + parameters;

      $.fancybox({
        'type'          : 'ajax',
        'href'          : url,
        'autoScale'     : false,
        'transitionIn'  : 'elastic',
        'transitionOut' : 'elastic',
      });
      return false;
    });

/*
    // Page splitter support with GET parameters
    $('form.split_pages select').each(function(index) {
      $(this).unbind('change').bind('change',function(){
        var url = $(location).attr('href');
        var parameters = $(':input', ($(this).parent()) ).serialize();
        var tmp_array = url.split('?');
        url = tmp_array[0] + '?' + parameters;

        $.fancybox({
          'type'          : 'ajax',
          'href'          : url,
          'autoScale'     : false,
          'transitionIn'  : 'elastic',
          'transitionOut' : 'elastic',
        });
        return false;
      });
    });
*/
  }
}
