/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
// jQuery: General Support Function for the I-Metrics CMS
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
var general = {

  launch: function() {
/*
    $('input').click(function(e) {
      e.stopPropagation();
    });
*/
    $('select.change_submit').change(function() {
      var $parent = $(this).closest("form");
      $parent.submit();
    });

    // checkboxes multi-select
    //$('form a.page_select').live("click", function(e) {
    $('form a.page_select').click(function(e) {
      e.preventDefault();

      var id = $(this).attr('href').substr(1);
      var status = -1;
      var $parent = $(this).closest("form");
      var checkboxes_array = $parent.find('input:checkbox');

      for(var i=0; i < checkboxes_array.length; i++) {
        var check = checkboxes_array[i].name.split('[');
        if( check[0] != id ) continue;

        if( status < 0 ) {
          status = $(checkboxes_array[i]).is(':checked');
          status = status?false:true;
        }
        $(checkboxes_array[i]).attr('checked', status);        
      }
    });

    // inputs multi-select
    $('form a.input_select').click(function(e) {
      e.preventDefault();

      var id = $(this).attr('href').substr(1);
      var text = -1;
      var $parent = $(this).closest("form");
      var inputs_array = $parent.find('input:not(input[type=hidden])');

      for(var i=0; i<inputs_array.length; i++) {

        var check = inputs_array[i].name.split('[');
        if( check[0] != id ) continue;
        if( text < 0 ) {
          text = $(inputs_array[i]).val();
        }
        $(inputs_array[i]).val(text);
      }
    });

    // combos multi-select
    $('form a.combo_select').click(function(e) {
      e.preventDefault();

      var id = $(this).attr('href').substr(1);
      var index = -1;
      var $parent = $(this).closest("form");
      var selects_array = $parent.find('select');

      for(var i=0; i<selects_array.length; i++) {

        var check = selects_array[i].name.split('[');
        if( check[0] != id ) continue;
        if( index < 0 ) {
          index = $(selects_array[i]).val();
        }
        $(selects_array[i]).val(index);
      }
    });

    $('form input[type=checkbox]').live("click", function(e) {
    //$('form input[type=checkbox]').click(function(e) {

      if( !e.shiftKey && !e.ctrlKey ) {
        return;
      }

      var base = this.name.split('[');
      base = base[0];

      var status = false;
      var $parent = $(this).closest("form");
      var checkboxes_array = $parent.find('input[type=checkbox]');
      if( checkboxes_array.length < 8 ) return;

      if( $(this).is(':checked') ) {
        status = true;
      }

      if( e.shiftKey ) {
        for(var i=0; i<checkboxes_array.length; i++) {
          var tmp = checkboxes_array[i].name.split('[');
          if( tmp[0] != base ) continue;

          if( checkboxes_array[i].name == this.name ) {
            break;
          }
        }
        for(; i<checkboxes_array.length; i++) {
          var tmp = checkboxes_array[i].name.split('[');
          if( tmp[0] != base ) continue;

          $(checkboxes_array[i]).attr('checked', status);
        }
      } else {
        for(var i=checkboxes_array.length-1; i>=0; i--) {
          var tmp = checkboxes_array[i].name.split('[');
          if( tmp[0] != base ) continue;

          if( checkboxes_array[i].name == this.name ) {
            break;
          }
        }
        for(; i>=0; i--) {
          var tmp = checkboxes_array[i].name.split('[');
          if( tmp[0] != base ) continue;

          $(checkboxes_array[i]).attr('checked', status);
        }
      }
    });

    $('.add_button').click(function() {
      var $parent = $(this).closest('.add_field_section');
      if( !$parent.length ) return true;

      var $field = $parent.find('.add_field').eq(0);
      var content = $field.html().replace(new RegExp( "\\*", "g" ), '');
      $field.append(content);
      return false;
    });

    $('.row_link').click(function(e) {
      if(e.target.nodeName != 'TD'){
        return true;
      }
      $(location).attr('href',$(this).attr('href'));
    });
  }
}
