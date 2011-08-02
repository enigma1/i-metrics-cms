/*!
 * dragtable
 *
 * Copyright (c) 2010, Andres Koetter akottr@gmail.com
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * Inspired by the the dragtable from Dan Vanderkam (danvk.org/dragtable/)
 * Thanks to the jquery and jqueryui comitters
 * 
 * Any comment, bug report, feature-request is welcome
 * Feel free to contact me.
 * !!! see you at jsconf.eu 2010 !!!
 */

/* TOKNOW:
 * For IE7 you need this css rule:
 * table {
 *   border-collapse: collapse;
 * }
 * Or take a clean reset.css (see http://meyerweb.com/eric/tools/css/reset/)
 */

/* TODO: investigate
 * Does not work properly with css rule:
 * html {
 *      overflow: -moz-scrollbars-vertical;
 *  }
 * Workaround:
 * Fixing Firefox issues by scrolling down the page
 * http://stackoverflow.com/questions/2451528/jquery-ui-sortable-scroll-helper-element-offset-firefox-issue
 *
 * var start = $.noop;
 * var beforeStop = $.noop;
 * if($.browser.mozilla) {
 * var start = function (event, ui) {
 *               if( ui.helper !== undefined )
 *                 ui.helper.css('position','absolute').css('margin-top', $(window).scrollTop() );
 *               }
 * var beforeStop = function (event, ui) {
 *              if( ui.offset !== undefined )
 *                ui.helper.css('margin-top', 0);
 *              }
 * }
 *
 * and pass this as start and stop function to the sortable initialisation
 * start: start,
 * beforeStop: beforeStop
 */

/* TODO: fix it
 * jqueryui sortable Ticket #4482
 * Hotfixed it, but not very nice (deprecated api)
 * if(!p.height() || (jQuery.browser.msie && jQuery.browser.version.match('^7|^6'))) { p.height(self.currentItem.innerHeight() - parseInt(self.currentItem.css('paddingTop')||0, 10) - parseInt(self.currentItem.css('paddingBottom')||0, 10)); };
 * if(!p.width() || (jQuery.browser.msie && jQuery.browser.version.match('^7|^6'))) { p.width(self.currentItem.innerWidth() - parseInt(self.currentItem.css('paddingLeft')||0, 10) - parseInt(self.currentItem.css('paddingRight')||0, 10)); };
 */

/* TODO: support colgroups
 */

/*
// Modifications by Asymmetrics
//----------------------------------------------------------------------------
// Copyright (c) 2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: common columns control for HTML tables
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// - Replaced URL processing code for persist and restore state
// - Added code to bypass event propagation to support drag selector children
// - Added code to rearrange columns for persist and restore functions.
// - Removed browser conditionals
// - Removed copying of table attributes into the temp table.
// - Replaced height calculations based on the row of the headers.
// - Removed CSS styles that are page dependent.
------------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
//
*/

(function($) {
  $.fn.dragtable = function(options) {
    var defaults = {
      revert:true,                 // smooth revert
      dragHandle:'.table-handle',  // handle for moving cols, if not exists the whole 'th' is the handle
      maxMovingRows:40,            // 1 -> only header. 40 row should be enough, the rest is usually not in the viewport
      onlyHeaderThreshold:100,     // TODO: not implemented yet, switch automatically between entire col moving / only header moving
      dragaccept:null,             // draggable cols -> default all
      persistState: '',            // url to store columns
      restoreState: '',            // url to restore columns
      beforeStart:$.noop,
      beforeMoving:$.noop,
      beforeReorganize:$.noop,
      beforeStop:$.noop
    };
    var opts = $.extend(defaults, options);

    // here comes the logic. Why var-name _D? My laziness is the culprit!
    var _D = {
      // this is the underlying -original- table
      originalTable:{
        el:$(),
        selectedHandle:$(),
        sortOrder:{},
        startIndex:0,
        endIndex:0
      },

      // this the sortable table on the layer above the original table
      sortableTable:{
        el:$(),
        selectedHandle:$(),
        movingRow:$()
      },

      swapNodes: function(a, b) {
        var aparent= a.parentNode;
        var asibling= a.nextSibling===b? a : a.nextSibling;
        b.parentNode.insertBefore(a, b);
        aparent.insertBefore(b, asibling);
      },

      exchangeNodes: function(a, b) {
        if( b ) {
          b.parentNode.appendChild(a);
        }
      },

      // send ids=index as req-param to server
      persistState: function() {
        _D.originalTable.el.find('th').each(function(i) {

          if(this.id != '') {
            _D.originalTable.sortOrder[this.id]=i;
          }
        });

        var post_data = '';

        for(var n in _D.originalTable.sortOrder) {
          post_data += 'columns['+n+']='+ _D.originalTable.sortOrder[n]+'&';
        }
        post_data = post_data.substr(0, post_data.length-1);

        $.ajax({
          type: 'POST',
          url: opts.persistState,
          data: post_data,
          complete: function(msg){
          },
          success: function(msg) {

          }
        });
      },

      restoreState: function() {
        $.ajax({
          type: 'POST',
          url: opts.restoreState,
          data: '',
          complete: function(msg){
          },
          success: function(msg) {

            var tmp_array = [];
		    var parts = msg.split("&");
            var i = 0;
            for(n in parts) {
		      var values = parts[n].split("=");
              _D.originalTable.sortOrder[values[0]] = parseInt(values[1]);
              i++;
            }
            _D.swapColumns();
            _D.originalTable.startIndex = _D.originalTable.endIndex = 0;

          }
        });
      },

      // bubble the moved col left or right
      bubbleCols: function() {
        var from = _D.originalTable.startIndex;
        var to = _D.originalTable.endIndex;

        if( from == to ) return false;

        var sign = start = end = 0;
        if( from < to ) {
          start = from-1;
          end = to-1;
        } else {
          start = to-1;
          end = from-1;
          sign = 1;
        }

        for(n in _D.originalTable.sortOrder) {
          var r = s = _D.originalTable.sortOrder[n];
          if( s == from-1) {
            r = to-1;
          } else if( s >= start && s <= end ) {
            if( s != from-1 && sign ) {
              r++;
            } else if(s != from-1 && !sign)  {
              r--;
            }
          }
          _D.originalTable.sortOrder[n] = r;
        }

        if(from < to) {
          for(var i = from; i < to; i++) {
            var row1 = _D.originalTable.el.find('tr > td:nth-child('+i+')')
                                          .add(_D.originalTable.el.find('tr > th:nth-child('+i+')'));
            var row2 = _D.originalTable.el.find('tr > td:nth-child('+(i+1)+')')
                                          .add(_D.originalTable.el.find('tr > th:nth-child('+(i+1)+')'));

            for(var j = 0; j < row1.length; j++) {
              _D.swapNodes(row1[j],row2[j]);
            }
          }
        } else {

          for(var i = from; i > to; i--) {
            var row1 = _D.originalTable.el.find('tr > td:nth-child('+i+')')
                                          .add(_D.originalTable.el.find('tr > th:nth-child('+i+')'));
            var row2 = _D.originalTable.el.find('tr > td:nth-child('+(i-1)+')')
                                          .add(_D.originalTable.el.find('tr > th:nth-child('+(i-1)+')'));

            for(var j = 0; j < row1.length; j++) {
              _D.swapNodes(row1[j],row2[j]);
            }
          }
        }
        return true;
      },

      swapColumns: function() {

        var tmp_array = [];
        for(n in _D.originalTable.sortOrder) {
          tmp_array[_D.originalTable.sortOrder[n]] = n;
        }

        var s,t,from, to;
        for(s=0, t=tmp_array.length; s<t; s++) {
          from = parseInt(tmp_array[s])+1;
          to = t;

          var row1 = _D.originalTable.el.find('tr > td:nth-child('+from+')').add(_D.originalTable.el.find('tr > th:nth-child('+from+')'));
          var row2 = _D.originalTable.el.find('tr > td:nth-child('+to+')').add(_D.originalTable.el.find('tr > th:nth-child('+to+')'));
          for(var j = 0; j < row1.length; j++) {
            _D.exchangeNodes(row1[j],row2[j]);
          }
          var diff = s+1;
          for(s2=diff, t2=tmp_array.length; s2 < t2; s2++ ) {
            if( tmp_array[s2] >= from ) {
              tmp_array[s2]--;
            }
          }
        }
      },

      rearrangeTableBackroundProcessing: function() {
        return function() {
          var result = _D.bubbleCols();
          opts.beforeStop(_D.originalTable);
          _D.sortableTable.el.remove();
          // persist state if necessary
          if( result && opts.persistState.length ) { 
            $.isFunction(opts.persistState) ? opts.persistState(_D.originalTable) : _D.persistState();
          }
        };
      },

      rearrangeTable: function() {
        // remove handler-class -> handler is now finished
        _D.originalTable.selectedHandle.removeClass('dragtable-handle-selected');
        // add disabled class -> reorgorganisation starts soon
        _D.sortableTable.el.sortable("disable");
        _D.sortableTable.el.addClass('dragtable-disabled');
        opts.beforeReorganize(_D.originalTable,_D.sortableTable);
        // do reorganisation asynchronous
        // for chrome a little bit more than 1 ms because we want to force a rerender
        _D.originalTable.endIndex = _D.sortableTable.movingRow.prevAll().size() + 1;
        setTimeout(_D.rearrangeTableBackroundProcessing(),50);
      },

      /*
       * Disrupts the table. The original table stays the same.
       * But on a layer above the original table we are constructing a list (ul > li)
       * each li with a separate table representig a single col of the original table.
       */
      generateSortable:function(e) {

        // compute width, no special handling for ie needed :-)
        var widthArr = [];
        var max_height = 0;

        _D.originalTable.el.find('tr > th').each(function(i,v) {
          widthArr.push($(this).width());
          //var height = $(this).height();
          //if(height > max_height) max_height = height;
        });

        _D.originalTable.el.find('tr.dataTableHeadingRow').each(function(i,v) {
          var height = $(this).height();
          if(height > max_height) max_height = height;
        });

        var sortableHtml = '<div class="dragtable-sortable" style="position:absolute;">'+"\n";
        // assemble the needed html
        _D.originalTable.el.find('tr.dataTableHeadingRow th').each(function(i,v) {
          sortableHtml += '<div>';
          sortableHtml += '<table class="ui-tabledata">';
          var row = _D.originalTable.el.find('tr > th:nth-child('+(i+1)+')');

          if(opts.maxMovingRows > 1) {
            row = row.add(_D.originalTable.el.find('tr > td:nth-child('+(i+1)+')').slice(0,opts.maxMovingRows-1));
          }
          row.each(function(j) {
            // TODO: May cause duplicate style-Attribute
            sortableHtml += '<tr>';
            sortableHtml += $(this).clone().wrap('<div style="height:'+ max_height +'px"></div>'+"\n").parent().html();
            sortableHtml += '</tr>';
          });
          sortableHtml += '</table>';
          sortableHtml += '</div>' + "\n";

        });
        sortableHtml += '</div>';
        _D.sortableTable.el = _D.originalTable.el.before(sortableHtml).prev();
        // set width if necessary
        _D.sortableTable.el.find('th').each(function(i,v) {
           var _this = $(this);
           if(widthArr[i] > _this.width()) {
             _this.css('width',widthArr[i]);
             _this.css('height', max_height);
           }
        });

        // assign _D.sortableTable.selectedHandle
        _D.sortableTable.selectedHandle = _D.sortableTable.el.find('th')
                                                          .find('.dragtable-handle-selected');

        var items = !opts.dragaccept ? 'div' : 'div:has(' + opts.dragaccept + ')';
        _D.sortableTable.el.sortable({
           stop:_D.rearrangeTable,
           items:items,
           revert:opts.revert,
           distance: 0
           }).disableSelection();

        // assign start index
        _D.originalTable.startIndex = $(e.target).closest('th').prevAll().size() + 1;

        opts.beforeMoving(_D.originalTable, _D.sortableTable);
        // Start moving by delegating the original event to the new sortable table
        _D.sortableTable.movingRow = _D.sortableTable.el.find('div:nth-child('+_D.originalTable.startIndex+')');

          var delegateEvt = $.extend(true, {}, e);
          _D.sortableTable.movingRow.trigger(delegateEvt);
          // clone
          var moveEvt = $.extend(true, {}, e);

          moveEvt = $.extend(true, moveEvt, {
            type:'mousemove',
            pageX:e.pageX,
            pageY:e.pageY
          });
          _D.sortableTable.movingRow.trigger(moveEvt);
      }

    };

    return this.each(function(){

      _D.originalTable.el = $(this);
      // bind draggable to 'th' by default
      var bindTo = _D.originalTable.el.find('th');

      // filter only the cols that are accepted
      if(opts.dragaccept) { bindTo = bindTo.filter(opts.dragaccept); }
      // bind draggable to handle if exists
      if(bindTo.find(opts.dragHandle).size() > 0) { bindTo = bindTo.find(opts.dragHandle);}
      // restore state if necessary
      if( opts.restoreState.length) { 
        _D.restoreState();
      }

      bindTo.each(function(i) {
        _D.originalTable.sortOrder[i]=i;
      });
      
      bindTo.unbind('mousedown').bind('mousedown',function(evt) {
        if( evt.target != this ) {
          return;
        }
        //evt.preventDefault();
        _D.originalTable.selectedHandle = $(this);
        _D.originalTable.selectedHandle.addClass('dragtable-handle-selected');
        opts.beforeStart(_D.originalTable);
        _D.generateSortable(evt);
      });

    });
  };
})(jQuery);