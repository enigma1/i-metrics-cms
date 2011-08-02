/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// jQuery: Helpdesk support
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
var helpdesk = {
  baseURL: false,

  launch: function() {

    $('.reply_to_label').click(function() {
      var url = helpdesk.baseURL;
      //var url = helpdesk.baseURL+'?action='+$(this).attr('for');
      //var title = $(this).attr('title');
      var title = this.innerHTML;

      $.fancybox({
        'type'          : 'ajax',
        'href'          : url,
        'autoScale'     : false,
        'transitionIn'  : 'elastic',
        'transitionOut' : 'elastic',
        'title'         : title
      });
      return false;

    });

    $('.book_data').live('click', function(e){
      var email = $(this).attr('email');
      var name = $(this).attr('name');

      $(':input[name=to_email_address]').val(email);
      $(':input[name=to_name]').val(name);
      e.preventDefault();
    });

    $('#reply_from_email').change(function() {
      var index = $(this).val();
      var url = helpdesk.fromURL + '&dID=' + index;

      $.ajax({
        type: 'GET',
        url: url,
        dataType: 'html',
        complete: function(msg){
        },
        success: function(msg) {
          $(':input[name=from_name]').val(msg);
        }
      });

      return false;
    });
  }
}
