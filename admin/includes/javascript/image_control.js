/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// jQuery: Image Collection/Resize/Control Script
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
var image_control = {
  imageBoxOpenFlag: false,
  imageBoxDialog: false,
  editObject: false,
  baseFront: false,
  baseURL: false,

  // Ajax Queue Interface
  ajaxRequest: function (options){
    var request = {
      url: options.url,
      type: options.type || 'POST',
      cache: options.cache || false,
      dataType: options.dataType || 'html',
      beforeSend: function (){
        image_control.showAjaxMessage(options.beforeSendMsg || 'Loading, please wait...');
        image_control.showAjaxLoader();
      },

      //dataType: 'json',
      async: options.async || true,
      contentType: options.contentType || 'application/x-www-form-urlencoded; charset=utf-8',
      data: options.data || false,
      success: options.success,
      error: options.error,
      complete: function(robj,result){
        if( document.ajaxq.q['imageProcess'].length <= 0 ) {
          image_control.hideAjaxLoader();
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


    if( image_control.imageBoxDialog && image_control.imageBoxOpenFlag ) {

      image_control.imageBoxDialog.html(msg);

      $dialog = image_control.imageBoxDialog.dialog();
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
      //image_control.imageBoxDialog.dialog('open');
      //image_control.imageBoxDialog.html(msg);
      return;
    }

    //image_control.imageBoxDialog = $('#modalBox').clone().show().appendTo(document.body).dialog({
    image_control.imageBoxDialog = $('#modalBox').show().dialog({
      resizable:  options.resizable || true,
      modal:      options.modal || true,
      shadow:     options.shadow || false,
      width:      options.width || 640,
      // Do not specify height use style instead
      height:     options.height || 480,
      minWidth:   options.minWidth || 200,
      minHeight:  options.minHeight || 200,
      buttons:    cbuttons,

      close: function (){
        image_control.imageBoxOpenFlag = false;
        image_control.imageBoxDialog.dialog('destroy');
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
        this.style.overflow = 'scroll';

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
        image_control.imageBoxOpenFlag = true;
      }

    });
    return false;
  },

  uploadImages: function(sub_path, action) {
    var before_msg = 'Upload Images';
    var post_data = '';
    var action = '';
    post_data += 'module=image_upload';

    if( sub_path.length > 0 ) {
      post_data += '&sub_path=' + sub_path;
    }

    if( action.length > 0 ) {
      action = '?action=' + action;
    }

    image_control.ajaxRequest({
      data: post_data,
      url: image_control.baseURL+action,
      beforeSendMsg: 'Loading Image Uploader',
      complete: function(msg){
      },
      success: function(msg) {

        var options = {};
        var styles = {};
        var callbacks = {
          'Insert': function() {
            var $this = $(this);
            $form = $this.find('#core_upload_form');
            $target = $this.find('#upload_result');

            var options = {
              target:        $target,   // target element(s) to be updated with server response
              beforeSubmit:  image_control.showRequest,  // pre-submit callback
              success:       image_control.showResponse  // post-submit callback
            };      
            $form.ajaxSubmit(options);
          }
        }
        image_control.showDialog(msg, options, callbacks, styles);
      }
    });
  },

  popupImages: function(sub_path) {
    var before_msg = 'Root Images';
    var post_data = '';
    post_data += 'module=image_list';
    if( sub_path.length > 0 ) {
      post_data += '&sub_path=' + sub_path;
      before_msg = sub_path;
    }

    image_control.ajaxRequest({
      data: post_data,
      url: image_control.baseURL,
      beforeSendMsg: 'Scanning Folder: ' + before_msg,
      complete: function(msg){
      },
      success: function(msg) {
        var callbacks = {};
        var options = {
          title: 'Image Folders and Selection',
          resizable:  true
        };
        var styles = {};

        var styles = {
          height: '480px',
          margin: 'auto',
          overflow: 'auto'
        };

        image_control.showDialog(msg, options, callbacks, styles);
      }
    });
  },

  resizeImages: function(action, post_input) {
    var before_msg = 'Resize Selection';
    var action = '';
    var post_data = 'module=image_resize';
    if( post_input.length > 0 ) {
      post_data += '&'+post_input;
    }

    if( action.length > 0 ) {
      action = '?action=' + action;
    }

    image_control.ajaxRequest({
      data: post_data,
      url: image_control.baseURL+action,
      beforeSendMsg: 'Resize Selection',
      complete: function(msg){
      },
      success: function(msg) {
        var callbacks = {
          'Insert': function() {
            var $this = $(this);

            var $rel = '';
            var $result = $this.find('#image_resize_complete');
            var $org_image = $this.find(':input[name="org_image"]');
            var $title = $this.find(':input[name="img_alt"]');
            var $group_name = $this.find(':input[name="img_group_name"]');
            var $desc = $this.find(':input[name="img_desc"]');
            var $width = $this.find(':input[name="width"]');
            var $height = $this.find(':input[name="height"]');
            var $popup = $this.find(':input[name="img_popup"]');
            var $thumb = $this.find(':input[name="img_thumb"]');

            var $item = $result.attr('attr');

            if( !$item || $item == 'undefined' ) {
              $this.dialog('close');
              alert('Invalid Image Request');
              image_control.popupImages('');
              return;
            }

            if( $thumb.attr('checked') ) {
              $item = $item.replace('&amp;','&');
              $item = $item.replace('no_cache=1&','');
              $entry = '<img border="0" src="' + $item + '" alt="' + $title.val() + '" title="' + $desc.val() + '"';
            } else {
              $entry = '<img border="0" src="' + $org_image.val() + '" alt="' + $title.val() + '" title="' + $desc.val() + '" width="' + $width.val() +'" height="' + $height.val() + '"';
            }
            $entry += ' />';

            if( $group_name.val().length > 0 ) {
              $rel = ' rel="' + $group_name.val() + '"';
            }
            if( $popup.val().length > 0 ) {
              $entry = '<a' + $rel + ' href="' + $org_image.val() + '" title="' + $desc.val() + '" class="' + $popup.val() + '" target="_blank" class="imetrics_popup">' + $entry + '</a>';
            }
            $this.dialog('close');
            if( typeof(image_control.editObject) == 'string' ) {
              var $doc = $('body');
              var $area = $doc.find(':input[name='+image_control.editObject+']');
              $area.val($area.val()+$entry);
            } else {
              image_control.editObject.editors[0].execCommand('mceInsertContent', false, $entry);
            }
          },

          'Preview': function() {
            var $this = $(this);
            $form = $this.find('#core_resize_form');
            $target = $this.find('#resize_result');

            var options = {
              target:        $target,   // target element(s) to be updated with server response
              beforeSubmit:  image_control.showRequest,  // pre-submit callback
              success:       image_control.showResponse  // post-submit callback
            };
            $form.ajaxSubmit(options);
          },

          'Back': function() {
            var $this = $(this);
            image_control.popupImages('');
          }
        };
        var options = {
          title: before_msg,
          resizable:  false
        };
        var styles = {
          height: '100%',
          overflow: 'hidden'
        };
        // Reopen the dialog to control styles and options correctly
        image_control.imageBoxDialog.dialog('close');
        image_control.showDialog(msg, options, callbacks, styles);
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

    //alert('status: ' + statusText + '\n\nresponseText: \n' + responseText +
    //    '\n\nThe output div should have already been updated with the responseText.');
  },

  launch: function(selector, update) {
    if( !image_control.baseFront ) {
      alert('Site Path not specified - aborting');
      return;
    }
    if( !image_control.baseURL ) {
      alert('AJAX Base URL not specified - aborting');
      return;
    }

    var wrapper = '';
    var $modal = $('#modalBox');
    if( !$modal.length ) {
      $('body').append(
        wrapper = $('<div id="modalBox" title="Image Selection" style="display:none; overflow: hidden;">Loading...Please Wait</div>')
      );
      var inner = $('<div id="ajaxLoader" title="Image Manager" style="display:none;"><img src="includes/javascript/jquery/themes/smoothness/images/ajax_load.gif"><p id="ajaxMsg" class="main">Updating, please wait...</p><hr /></div>').appendTo(wrapper);
    }

    image_control.imageBoxOpenFlag = false;

    $('#image_upload').click(function() {
      image_control.uploadImages('', '');
      return false;
    });

    $('a.folder_upload_list').live("click", function(event) {
      event.preventDefault();
      $item = $(this).attr('attr');
      sub_path = '';
      if( $item.length > 0 ) {
        sub_path += $item;
      }
      image_control.uploadImages(sub_path, '');
    });


    $('#image_selection').click(function() {
      image_control.popupImages('');
      return false;
    });

    $('a.folder_list').live("click", function(event) {
      event.preventDefault();
      $item = $(this).attr('attr');
      sub_path = '';
      if( $item.length > 0 ) {
        sub_path += $item;
      }
      image_control.popupImages(sub_path);
    });

    $('a.file_list').live("click", function(event) {
      event.preventDefault();
      var $item = $(this).attr('attr');
      var $post_data = 'image='+$item;
      image_control.resizeImages('', $post_data);
    });
  }
}

