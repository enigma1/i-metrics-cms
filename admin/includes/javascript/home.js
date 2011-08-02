/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// jQuery: Home processing options Script
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
var home = {
  animationBusy: false,
  imageBoxDialog: false,
  baseURL: false,
  replacementElement: false,

  // Ajax Queue Interface
  ajaxRequest: function (options){
    var request = {
      url: options.url,
      type: options.type || 'POST',
      cache: options.cache || false,
      dataType: options.dataType || 'html',
      beforeSend: function (){
        //home.showAjaxMessage(options.beforeSendMsg || 'Loading, please wait...');
        //home.showAjaxLoader();
      },

      //dataType: 'json',
      async: options.async || false,
      contentType: options.contentType || 'application/x-www-form-urlencoded; charset=utf-8',
      data: options.data || false,
      success: options.success,
      error: options.error,
      complete: function(robj,result){
        if( document.ajaxq.q['scriptProcess'].length <= 0 ) {
          //home.hideAjaxLoader();
        }
      }
    };
    //$.ajax(request);
    $.ajaxq('scriptProcess', request);
    //html = result.responseText;
    //return html;
  },

  retrieve: function($sel, attr, msg) {

      if( home.animationBusy ) return;

      var options = {
        easing: 'easeInOutQuad',
        adjustHeight: false
      };

      home.animationBusy = true;

      var qsel = $sel.parent().parent();
      //$(msg).css('display: none');
      //var inner = $(msg).appendTo('#top_level');

      $('#top_level').append(msg);
      $(attr).css('display', 'none');
      //$(attr).css('visibility', 'hidden');
      //$(attr).hide();

      $(qsel).quicksand( 
        (attr+' div'), options, function() {
          //$(attr).hide();
          $('#top_level').html(msg);
          //$(attr).show();
          home.animationBusy = false;
	    // callback code
        }
      );
  },

  launch: function(source, destination) {

    home.animationBusy = false;

    $('a.sandbox').live("click", function(event) {
      event.preventDefault();
      if( home.animationBusy ) return;

      var $this = $(this);
      var attr = $this.attr('attr');

      var $url = home.baseURL+'?action='+attr;
      var $title = $this.attr('title');
      var $post_data = '';

      home.ajaxRequest({
        //data: $post_data,
        url: $url,
        type: 'GET',
        beforeSendMsg: $title,
        complete: function(msg){
        },
        success: function(msg) {
          home.retrieve($this, '#'+attr, msg);
        }
      });
    });


  }
}

