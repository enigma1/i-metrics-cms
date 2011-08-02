/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// jQuery Front: Admin Support Function for editing content
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
var admin = {
  baseTemplate: false,
  title: false,
  titleTag: false,
  contentTag: false,
  glue_key: ':',
  glue_value: '*',

  launch: function() {
    $('.dm_popup_editor').live('click', function(e) {
      e.preventDefault();

      var sep = '?';
      var id = $(this).attr('id');
      var href = $(location).attr('href');

      var tmp = href.split(sep);
      if( tmp.length > 1 ) {
        sep = '&';
      }
      href += sep+'action=edit_content';

      $.fancybox({
        //'ajax'          : {
		//  'type'		: 'POST',
        //  'data'        : 'post1=1'
        //},
        'type'          : 'ajax',
        'href'          : href,
        'autoScale'     : false,
        'transitionIn'  : 'elastic',
        'transitionOut' : 'elastic',
        'title'         : admin.title
      });

    });


    $('#dm_button_update').live('click', function(e) {
      e.preventDefault();

      var $parent = $(this).closest("form");
      var href = $parent.attr('action');
      var parameters = $(':input', $parent).serialize();

      $.ajax({
        type: 'POST',
        url: href,
        data: parameters,
        dataType: 'html',
        complete: function(msg){
        },
        success: function(msg) {
          var msg_array = msg.split(admin.glue_value);
          var i, j, content, text;
          for(i=0, j=msg_array.length; i<j; i++) {
            content = msg_array[i].split(admin.glue_key);
            if( typeof content[1] == 'undefined' ) continue;

            text = $.base64Decode(content[1]);
            if( content[0] == 'content_title' ) {
              admin.titleTag.html(text);
            } else if(content[0] == 'content_description') {
              admin.contentTag.html(text);
            }
          }
          $.fancybox.close();
        }
      });
    });

    var tag = '<div class="floater tpad rpad"><a href="#top" class="dm_popup_editor"><img src="'+admin.baseTemplate+'dm_edit.png" alt="popup editor" /></a></div>';

    admin.titleTag = $('#maindriver .pageHeader h1');
    admin.title = admin.titleTag.text();
    admin.contentTag = $('#maindriver .pageContent');

    var heading = $('#maindriver .pageHeader');
    heading.prepend(tag);
  }
}
