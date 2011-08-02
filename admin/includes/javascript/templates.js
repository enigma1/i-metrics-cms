/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// jQuery: Preview and Insert a template into a textarea from a selection
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
var templates = {
  baseTemplate: false,
  edit_object: false,
  add_template: '#set_template',
  list: '#template_list',

  launch: function() {
    $(templates.add_template).click(function() {

      var id = $(templates.list).val();
      var url = $(this).attr('href')+'&template_id='+id;
      $.get( url, function(data) {
        if( typeof(templates.editObject) == 'string' ) {
          var $area = $('body').find(':input[name='+templates.editObject+']');
          $area.val($area.val()+data);
        } else {
          templates.editObject.editors[0].execCommand('mceInsertContent', false, data);
        }
      });
      return false;

    });

    $('#view_template').click(function() {
      var id = $(templates.list).val();
      var url = $(this).attr('href')+'&template_id='+id;
      var title = '<a href="'+templates.baseTemplate+id+'" style="color: #FFF;">'+$(this).attr('title')+' - '+$(templates.list).text() + '</a>';

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
  }
}
