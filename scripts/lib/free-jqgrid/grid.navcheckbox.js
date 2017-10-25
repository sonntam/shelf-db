/**
 * jqGrid extension for checkboxes in navbar
 * Copyright (c) 2008-2014, Tony Tomov, tony@trirand.com, http://trirand.com/blog/
 * Copyright (c) 2014-2017, Oleg Kiriljuk, oleg.kiriljuk@ok-soft-gmbh.com
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl-2.0.html
**/

/*jshint eqeqeq:false, eqnull:true, devel:true */
/*jslint browser: true, eqeq: true, plusplus: true, unparam: true, vars: true, nomen: true, continue: true, white: true, todo: true */
/*global jQuery, define, exports, module, require */
(function (global, factory) {
	"use strict";
	if (typeof define === "function" && define.amd) {
		// AMD. Register as an anonymous module.
		//console.log("grid.formedit AMD");
		define([
			"jquery",
			"./ui.jqgrid"
		], function ($) {
			//console.log("grid.formedit AMD: define callback");
			return factory($, global, global.document);
		});
	} else if (typeof module === "object" && module.exports) {
		// Node/CommonJS
		//console.log("grid.formedit CommonJS, typeof define=" + typeof define + ", define=" + define);
		module.exports = function (root, $) {
			//console.log("grid.formedit CommonJS: in module.exports");
			if (!root) {
				root = window;
			}
			//console.log("grid.formedit CommonJS: before require('jquery')");
			if ($ === undefined) {
				// require("jquery") returns a factory that requires window to
				// build a jQuery instance, we normalize how we use modules
				// that require this pattern but the window provided is a noop
				// if it's defined (how jquery works)
				$ = typeof window !== "undefined" ?
						require("jquery") :
						require("jquery")(root);
			}
			require("./ui.jqgrid");
			factory($, root, root.document);
			return $;
		};
	} else {
		// Browser globals
		//console.log("grid.formedit Browser: before factory");
		factory(jQuery, global, global.document);
	}
}(typeof window !== "undefined" ? window : this, function ($, window, document) {
	"use strict";
	var jgrid = $.jgrid, jqID = jgrid.jqID, base = $.fn.jqGrid, getGuiStyles = base.getGuiStyles,
		mergeCssClasses = jgrid.mergeCssClasses, hasOneFromClasses = jgrid.hasOneFromClasses;

	// begin module grid.navcheckbox
	var getGuiStateStyles = function (path) {
			return getGuiStyles.call(this, "states." + path);
		};
	jgrid.extend({
    navCheckboxAdd: function (elem, oMuligrid) {
	    if (typeof elem === "object") {
	      oMuligrid = elem;
	      elem = undefined;
	    }

	    return this.each(function () {
	      var $t = this, p = $t.p;
	      if (!$t.grid) { return; }
	      var o = $.extend(
	          {
	            caption: "newCheckbox",
	            title: "",
	            onChange: null,
	            position: "last",
	          },
	          base.getGridRes.call($($t), "nav"),
	          jgrid.nav || {},
	          p.navOptions || {},
	          oMuligrid || {}
	        ),
	        id = o.id,
	        hoverClasses = getGuiStateStyles.call($t, "hover"),
	        disabledClass = getGuiStateStyles.call($t, "disabled"),
	        navButtonClass = getGuiStyles.call($t, "navButton", "ui-pg-button");
	      if (elem === undefined) {
	        if (p.pager) {
	          base.navButtonAdd.call($($t), p.pager, o);
	          if (p.toppager) {
	            elem = p.toppager;
	            if (id) {
	              id += "_top";
	            }
	          } else {
	            return;
	          }
	        } else if (p.toppager) {
	          elem = p.toppager;
	        }
	      }
	      if (typeof elem === "string" && elem.indexOf("#") !== 0) { elem = "#" + jqID(elem); }
	      var findnav = $(".navtable", elem), commonIconClass = o.commonIconClass;
	      if (findnav.length > 0) {
	        if (id && findnav.find("#" + jqID(id)).length > 0) { return; }
	        var tbd = $("<div tabindex='0' role='checkbox'></div>");
	        $(tbd).addClass(navButtonClass).append("<div class='ui-pg-div'>" +
	          "<span class='" + mergeCssClasses(commonIconClass) +
	          "'><input type='checkbox' id='"+ id +"'></span>" +
	          (o.caption ? "<span class='ui-pg-button-text'><label for='"+id+"'>" + o.caption + "</label></span>" : "") +
	          "</div>");
	        if (id) { $(tbd).attr("id", id + "_super"); }
	        if (o.position === "first" && findnav.children("div.ui-pg-button").length > 0) {
	          findnav.children("div.ui-pg-button").first().before(tbd);
	        } else {
	          findnav.append(tbd);
	        }
	        $("[type=checkbox]", tbd, findnav)
	          .attr("title", o.title || "")
	          .change(function (e) {
	            if (!hasOneFromClasses(this, disabledClass)) {
	              if ($.isFunction(o.onChange)) { o.onChange.call(this, o, e); }
	            }
	            return false;
	          })
	          .hover(
	            function () {
	              if (!hasOneFromClasses(this, disabledClass)) {
	                $(this).addClass(hoverClasses);
	              }
	            },
	            function () { $(this).removeClass(hoverClasses); }
	          );
	        $($t).triggerHandler("jqGridResetFrozenHeights");
	      }
	    });
	  }
	});
	// end module grid.navcheckbox
}));
