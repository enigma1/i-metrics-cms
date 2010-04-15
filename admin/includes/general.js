function SetFocus() {
  if (document.forms.length > 0) {
    var field = document.forms[0];
    for (i=0; i<field.length; i++) {
      if ( (field.elements[i].type != "image") &&
           (field.elements[i].type != "hidden") &&
           (field.elements[i].type != "reset") &&
           (field.elements[i].type != "submit") ) {

        document.forms[0].elements[i].focus();

        if ( (field.elements[i].type == "text") ||
             (field.elements[i].type == "password") )
          document.forms[0].elements[i].select();

        break;
      }
    }
  }
}

function rowOverEffect(object) {
  if (object.className == 'dataTableRow') object.className = 'dataTableRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'dataTableRowOver') object.className = 'dataTableRow';
}



var g_checkbox2 = 0;
  function copy_checkboxes(form, array_name) {
    for (var i = 0; i < form.elements.length; i++) {
      if( form.elements[i].type == "checkbox" ) {
        check_name = form.elements[i].name;
        if( array_name == check_name.substring(0, array_name.length) ) {
          form.elements[i].checked = g_checkbox2?"":"on";
        }
      }
    }
    g_checkbox2 ^= 1;
  }

  function copy_inputs(form, array_name) {
    var hit = 0;
    for (var i = 0; i < form.elements.length; i++) {
      if( form.elements[i].type == "text" ) {
        check_name = form.elements[i].name;
        if( array_name == check_name.substring(0, array_name.length) ) {
          if( hit == 0 ) {
            input_value = form.elements[i].value;
          }
          form.elements[i].value = input_value;
          hit++;
        }
      }
    }
  }

  function copy_combos(form, array_name) {
    var hit = 0;
    for (var i = 0; i < form.elements.length; i++) {
      if( form.elements[i].type == "select-one" ) {
        check_name = form.elements[i].name;
        if( array_name == check_name.substring(0, array_name.length) ) {
          if( hit == 0 ) {
            input_value = form.elements[i].value;
          }
          form.elements[i].value = input_value;
          hit++;
        }
      }
    }
  }


  function copy_radios(form, array_name) {
    var hit = 0;
    for (var i = 0; i < form.elements.length; i++) {
      if( form.elements[i].type == "radio" ) {
        check_name = form.elements[i].name;
        if( array_name == check_name.substring(0, array_name.length) ) {
          if( hit == 0 ) {
            input_value = form.elements[i].value;
            input_checked = form.elements[i].checked;
          }

          if( form.elements[i].value == input_value ) {
            form.elements[i].checked = input_checked;
          } else {
            form.elements[i].checked = !input_checked;
          }

          hit++;
        }
      }
    }
  }

/*
  function copy_radios(form, array_name, start) {

    count_name = array_name + '[' + start + ']';
    input_name = document.getElementsByName(count_name); 
    input_value = input_name;

    for( var checked_index=0; checked_index<input_value.length; checked_index++ ) {
      if(input_value[checked_index].checked) {
        break;
      }
    }

    for (var i=start;; i++) {
      count_name = array_name + '[' + (i+1) + ']';
      input_name = document.getElementsByName(count_name); 
      if( input_name && input_name[0] ) {
        for( var j=0; j<input_value.length; j++ ) {
          input_name[j].checked = 0;
        }
        input_name[checked_index].checked = 1;
      } else {
        break;
      }
    }
  }
*/

  function copy_checkboxes_horizontal(form, src_name, start) {
    var destination_count = copy_horizontal.arguments.length;
    count_name = src_name + '[' + start + '][1]';
    input_name = document.getElementsByName(count_name); 
    input_value = input_name[0].value;

    for (var j=3; j<destination_count; j++) {
      tmp_name = copy_horizontal.arguments[j];
      copy_name = tmp_name + '[' + start + '][1]';
      next_name = document.getElementsByName(copy_name); 
      next_name[0].value = input_value;
    }
  }

  var g_checkbox = 0;
  function check_boxes(form) {
    for (var i = 0; i < form.elements.length; i++) {
      if( form.elements[i].type == "checkbox" ) {
        form.elements[i].checked = g_checkbox?"":"on";
      }
    }
    g_checkbox ^= 1;
  }

  function comboToIndex(link, object) 
  {
    for (var i = 0; i < object.options.length; i++) {
      if (object.options[i].selected) {
        return link+"&sID="+object.options[i].value;
      }
    }
    return false;
  }
