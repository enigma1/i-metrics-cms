var tinymce_ifc = {
  // Pre-initialize the defaults to relative paths, they will be overriden
  baseTinyMCE:  'includes/javascript/tiny_mce/tiny_mce.js',
  cssFront: 'stylesheet.css',
  baseFront: '../',
  baseURL: '#',

  launch: function() {
    tinyMCE.init({
      remove_linebreaks : false,
      language: "en",
      entity_encoding : "raw",
      skin : "o2k7",
      mode : "exact",
      elements : tinymce_ifc.areas,
      baseURL: tinymce_ifc.baseURL,
      // Location of TinyMCE script
      //script_url : tinymce_ifc.TinyMCE, //'../js/tinymce/jscripts/tiny_mce/tiny_mce.js',
      // General options
      theme : "advanced",
      plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,preview,media,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
      // Theme options
      theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
      theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,preview,|,forecolor,backcolor",
      theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
      theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
      theme_advanced_toolbar_location : "top",
      theme_advanced_toolbar_align : "left",
      theme_advanced_statusbar_location : "bottom",
      //theme_advanced_resizing : true,
      // Example content CSS (should be your site CSS)
      content_css : tinymce_ifc.cssFront, //"css/content.css",
      document_base_url : tinymce_ifc.baseFront,
      // Drop lists for link/image/media/template dialogs
      template_external_list_url : "lists/template_list.js",
      external_link_list_url : "lists/link_list.js",
      external_image_list_url : "lists/image_list.js",
      width : "100%", 
      height : "480",
      media_external_list_url : "lists/media_list.js",
      // Replace values for the template plugin
      template_replace_values : {
        username : "Some User",
        staffid : "991234"
      }
    });
  }
}