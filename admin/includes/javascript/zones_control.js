/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// jQuery: Abstract Zones Control Script
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
var zones_control = {
  imageBoxOpenFlag: false,
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
        zones_control.showAjaxMessage(options.beforeSendMsg || 'Loading, please wait...');
        zones_control.showAjaxLoader();
      },

      //dataType: 'json',
      async: options.async || true,
      contentType: options.contentType || 'application/x-www-form-urlencoded; charset=utf-8',
      data: options.data || false,
      success: options.success,
      error: options.error,
      complete: function(robj,result){
        if( document.ajaxq.q['imageProcess'].length <= 0 ) {
          zones_control.hideAjaxLoader();
        }
      }
    };
    //$.ajax(request);
    $.ajaxq('imageProcess', request);
    //html = result.responseText;
    //return html;
  },
  showAjaxLoader: function (){
    $('#ajaxLoader').dialog({ 
      autoOpen: true,
      resizable: false,
      modal: true
    });
    $('#ajaxLoader').dialog('open');
  },
  hideAjaxLoader: function (){
    $('#ajaxLoader').dialog('close');
  },
  showAjaxMessage: function (message){
    $('#ajaxMsg').html(message);
  },

  showDialog: function(msg, options, callbacks, styles) {
    var cbuttons = {};

    for( var func in callbacks ) {
      cbuttons[func] = callbacks[func];
    }

    cbuttons['Cancel'] = function() {
        var $this = $(this);
        $this.dialog('close');
    };


    if( zones_control.imageBoxDialog && zones_control.imageBoxOpenFlag ) {

      zones_control.imageBoxDialog.html(msg);

      $dialog = zones_control.imageBoxDialog.dialog();
      $dialog.data("buttons.dialog", cbuttons);

      // override dialog options on dialog data update
      for( var entry in options ) {
        $dialog.data(entry+".dialog", options[entry]);
      }

      style_string = '';
      for( var tag in styles ) {
        style_string += tag+':'+styles[tag]+';';
      }
      if( style_string.length > 0 ) {
        $dialog.attr('style', style_string);
      }
      //$dialog.data("resizable.dialog", false);
      //zones_control.imageBoxDialog.dialog('open');
      //zones_control.imageBoxDialog.html(msg);
      return;
    }

    //zones_control.imageBoxDialog = $('#modalBox').clone().show().appendTo(document.body).dialog({
    zones_control.imageBoxDialog = $('#modalBox').show().dialog({
      resizable:  options.resizable || true,
      modal:      options.modal || true,
      shadow:     options.shadow || false,
      width:      options.width || 640,
      // Do not specify height use style instead
      //height:     options.height || 480,
      minWidth:   options.minWidth || 200,
      minHeight:  options.minHeight || 200,
      buttons:    cbuttons,

      close: function (){
        zones_control.imageBoxOpenFlag = false;
        zones_control.imageBoxDialog.dialog('destroy');
      },

      open: function() {
        var $dialog = $(this);

        // Do not handle the enter on inputs
        $("input:text", $dialog).live("keypress", function(e) {
          if (e.keyCode == 13) {
            return false;
          }
        });

        $dialog.html(msg);

        for( var entry in options ) {
          $dialog.data(entry+".dialog", options[entry]);
        }

        style_string = '';
        for( var tag in styles ) {
          style_string += tag+':'+styles[tag]+';';
        }
        if( style_string.length > 0 ) {
          $dialog.attr('style', style_string);
        }
        zones_control.imageBoxOpenFlag = true;
      }

    });
    return false;
  },


  listTextZones: function($action, $gtext_id, $post_data) {
    var before_msg = 'Content List';

    if( $post_data.length > 0 ) {
      $post_data += '&';
    }

    $post_data += 'module=abstract_zones';
    $post_data += '&gtext_id=' + $gtext_id;

    if( $action.length > 0 ) {
      $action = '?action=' + $action;
    }

    zones_control.ajaxRequest({
      data: $post_data,
      url: zones_control.baseURL,
      beforeSendMsg: 'Loading Text Collections',
      complete: function(msg){
      },
      success: function(msg) {
        var callbacks = {
          'Apply Changes': function() {
            var $this = $(this);

            $form = $this.find('#core_assign_form');
            $target = $this.find('#abstract_list');
            var options = {
              target:        $target,   // target element(s) to be updated with server response
              beforeSubmit:  zones_control.showRequest,  // pre-submit callback
              success:       zones_control.showResponse  // post-submit callback
            };
            $form.ajaxSubmit(options);
            //alert($replace.html());
          }
        };
        var options = {
          title: 'Website Text Collections',
          resizable:  true
        };
        var styles = {
        };
        zones_control.showDialog(msg, options, callbacks, styles);
      }
    });
  },

  // pre-submit callback
  showRequest: function(formData, jqForm, options) {
    // formData is an array; here we use $.param to convert it to a string to display it
    // but the form plugin does this for you automatically when it submits the data
    var queryString = $.param(formData);

    // jqForm is a jQuery object encapsulating the form element.  To access the
    // DOM element for the form do this:
    // var formElement = jqForm[0];

    //alert('About to submit: \n\n' + queryString);

    // here we could return false to prevent the form from being submitted;
    // returning anything other than false will allow the form submit to continue
    return true;
  },

  // post-submit callback
  showResponse: function(responseText, statusText)  {
    // for normal html responses, the first argument to the success callback
    // is the XMLHttpRequest object's responseText property

    // if the ajaxForm method was passed an Options Object with the dataType
    // property set to 'xml' then the first argument to the success callback
    // is the XMLHttpRequest object's responseXML property

    // if the ajaxForm method was passed an Options Object with the dataType
    // property set to 'json' then the first argument to the success callback
    // is the json data object returned by the server

    $replace = $(this).find('#target_abstract_zones');
    $replacement = $replace.html();
    zones_control.replacementElement.html($replacement);

    //alert('status: ' + statusText + '\n\nresponseText: \n' + responseText +
    //    '\n\nThe output div should have already been updated with the responseText.');
  },

  launch: function(selector, update) {

    var wrapper = '';
    var $modal = $('#modalBox');
    if( !$modal.length ) {
      $('body').append(
        wrapper = $('<div id="modalBox" title="Collections" style="display:none; overflow: hidden;">Loading...Please Wait</div>')
      );
      var inner = $('<div id="ajaxLoader" title="Collection Assignment" style="display:none;"><img src="includes/javascript/jquery/themes/smoothness/images/ajax_load.gif"><p id="ajaxMsg" class="main">Loading, please wait...</p><hr /></div>').appendTo(wrapper);
    }

    zones_control.imageBoxOpenFlag = false;

    $('a.list_abstract_zones').live("click", function(e) {
      e.stopPropagation();
      e.preventDefault();
      $gtext_id = $(this).attr('attr');
      zones_control.replacementElement = $(this).parent();
      zones_control.listTextZones('', $gtext_id, '');
    });
  }
}

