/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http:/**
 */
/**
 * Specifications for various types of popup edit window
 * Choose positions to center in the smallest (1000x800) target screen
 */
var find_window_specs = 'width=550,height=600,left=250,top=150,resizable=1,scrollbars=1'; // special characters
/**
 * Add help texts to page
 */
function display_help (title)
{
	jQuery(".help-text")
		.each(function ()
		{
			var helpID = jQuery(this)
				.attr("id");
			jQuery("#" + helpID)
				.load("help_text.php?help=" + helpID + "title=" + title);
		});
}
/**
 *  TODO: This function loads help_text.php twice.  It should only load it once.
 */
function helpDialog (which, mod)
{
	var url = 'help_text.php?help=' + which + '&mod=' + mod;
	jQuery('<div style="max-height:375px; overflow-y:auto"><div><div class="loading-image"></div></div></div>')
		.dialog(
		{
			modal: true,
			width: 500,
			height: 'auto',
		})
		.load(url + ' .help_content', function ()
		{
			jQuery(this)
				.dialog("option", "position",
				{
					my: "center top",
					at: "center top",
					of: "#content"
				});
		});
	jQuery(".ui-widget-overlay")
		.on("click", function ()
		{
			jQuery("div:ui-dialog:visible")
				.dialog("close");
		});
	jQuery('.ui-dialog-title')
		.load(url + ' .helpheader');
	return false;
}
/**
 *  Create a modal dialog, fetching the contents from a URL
 */
function modalDialog (url, title)
{
	jQuery('<div style="max-height:800px; overflow-y:auto"><div title="' + title + '"><div class="loading-image"></div><div></div>')
		.dialog(
		{
			title: title,
			modal: false,
			width: 'auto',
			height: 'auto',
			modal: true,
			closeText: "",
			close: function (event, ui)
			{
				jQuery(this)
					.remove();
				jQuery('.ui-widget-overlay')
					.remove();
			}
		})
		.load(url, function ()
		{
			jQuery(this)
				.dialog("option", "position",
				{
					my: "left top",
					at: "left+10% top+10%",
					of: window
				});
		});
	// Close the window when we click outside it.
	jQuery(".ui-widget-overlay")
		.on("click", function ()
		{
			jQuery("div:ui-dialog:visible")
				.dialog("close");
			jQuery(this)
				.remove();
		});
	return false;
}
/**
 *  Create a modal dialog to display notes
 */
function modalNotes (content, title)
{
	dialog = jQuery('<div title="' + title + '"></div>')
		.html(content)
		.dialog(
		{
			modal: true,
			width: 500,
			closeText: "",
			close: function (event, ui)
			{
				jQuery(this)
					.remove();
			}
		});
	// Close the window when we click outside it.
	jQuery(".ui-widget-overlay")
		.on("click", function ()
		{
			jQuery("div:ui-dialog:visible")
				.dialog("close");
		});
	return false;
}
/**
 *  For a dialog containing a form, submit the form via AJAX
 * (to save the data), then reload the page (to display it)
 */
function modalDialogSubmitAjax (form)
{
	jQuery.ajax(
	{
		type: 'POST',
		url: jQuery(form)
			.attr('action'),
		data: jQuery(form)
			.serialize(),
		success: function (response)
		{
			window.location.reload();
		}
	});
	return false;
}

function closePopupAndReloadParent (url)
{
	if (parent.opener)
	{
		if (!url)
		{
			parent.opener.location.reload();
		}
		else
		{
			parent.opener.location = url;
		}
	}
	window.close();
}
/**
 *  variables to hold mouse x-y pos.s
 */
var msX = 0;
var msY = 0;
/**
 *   The following javascript function is for the positioning and hide/show of
 *   DIV layers used in the display of the pedigree chart.
 */
function MM_showHideLayers ()
{ //v6.0
	var i, p, v, obj, args = MM_showHideLayers.arguments;
	for (i = 0; i < (args.length - 3); i += 4)
	{
		if ((obj = document.getElementById(args[i])) !== null)
		{
			if (obj.style)
			{
				div = obj; // unused?
				obj = obj.style;
			}
			v = args[i + 2];
			if (v == 'toggle')
			{
				if (obj.visibility.indexOf('hid') != -1) v = 'show';
				else v = 'hide';
			}
			v = (v == 'show') ? 'visible' : (v == 'hide') ? 'hidden' : v;
			obj.visibility = v;
			if (args[i + 1] == 'followmouse')
			{
				var pobj = document.getElementById(args[i + 3]);
				if (pobj !== null)
				{
					if (pobj.style.top != "auto" && args[i + 3] != "relatives")
					{
						obj.top = 5 + msY - parseInt(pobj.style.top) + 'px';
						if (textDirection == "ltr") obj.left = 5 + msX - parseInt(pobj.style.left) + 'px';
						if (textDirection == "rtl") obj.right = 5 + msX - parseInt(pobj.style.right) + 'px';
					}
					else
					{
						obj.top = "auto";
						var pagewidth = document.documentElement.offsetWidth + document.documentElement.scrollLeft;
						if (textDirection == "rtl") pagewidth -= document.documentElement.scrollLeft;
						if (msX > pagewidth - 160) msX = msX - 150 - pobj.offsetLeft;
						var contentdiv = document.getElementById("content");
						msX = msX - contentdiv.offsetLeft;
						if (textDirection == "ltr") obj.left = (5 + msX) + 'px';
						obj.zIndex = 1000;
					}
				}
				else
				{
					//obj.top="auto";
					if (SCRIPT_NAME.indexOf("fanchart") > 0)
					{
						obj.top = (msY - 20) + 'px';
						obj.left = (msX - 20) + 'px';
					}
					else if (SCRIPT_NAME.indexOf("index.php") == -1)
					{
						Xadjust = document.getElementById('content')
							.offsetLeft;
						obj.left = (5 + (msX - Xadjust)) + 'px';
						obj.top = "auto";
					}
					else
					{
						Xadjust = document.getElementById('content')
							.offsetLeft;
						obj.top = (msY - 50) + 'px';
						obj.left = (10 + (msX - Xadjust)) + 'px';
					}
					obj.zIndex = 1000;
				}
			}
		}
	}
}
var show = false;

function togglechildrenbox (pid)
{
	if (!pid) pid = '';
	else pid = '.' + pid;
	if (show)
	{
		MM_showHideLayers('childbox' + pid, ' ', 'hide', ' ');
		show = false;
	}
	else
	{
		MM_showHideLayers('childbox' + pid, ' ', 'show', ' ');
		show = true;
	}
	return false;
}

function show_family_box (boxid, pboxid)
{
	var lastfamilybox = "";
	var popupopen = 0;
	popupopen = 1;
	lastfamilybox = boxid;
	if (pboxid == 'relatives') MM_showHideLayers('I' + boxid + 'links', 'followmouse', 'show', '' + pboxid);
	else
	{
		famlinks = document.getElementById("I" + boxid + "links");
		divbox = document.getElementById("out-" + boxid);
		parentbox = document.getElementById("box" + boxid);
		//alert(famlinks+" "+divbox+" "+parentbox);
		if (famlinks && divbox && parentbox)
		{
			famlinks.style.top = "0px";
			if (textDirection == "ltr") famleft = parseInt(divbox.style.width) + 15;
			else famleft = 0;
			if (isNaN(famleft))
			{
				famleft = 0;
				famlinks.style.top = parentbox.offsetTop + "px";
			}
			pagewidth = document.documentElement.offsetWidth + document.documentElement.scrollLeft;
			if (textDirection == "rtl") pagewidth -= document.documentElement.scrollLeft;
			if (famleft + parseInt(parentbox.style.left) > pagewidth - 100) famleft = 25;
			famlinks.style.left = famleft + "px";
			if (SCRIPT_NAME.indexOf("index.php") != -1) famlinks.style.left = "100%";
			MM_showHideLayers('I' + boxid + 'links', ' ', 'show', '' + pboxid);
			return;
		}
		MM_showHideLayers('I' + boxid + 'links', 'followmouse', 'show', '' + pboxid);
	}
}

function toggle_family_box (boxid, pboxid)
{
	if (popupopen == 1)
	{
		MM_showHideLayers('I' + lastfamilybox + 'links', ' ', 'hide', '' + pboxid);
		popupopen = 0;
	}
	if (boxid == lastfamilybox)
	{
		lastfamilybox = "";
		return;
	}
	popupopen = 1;
	lastfamilybox = boxid;
	if (pboxid == 'relatives') MM_showHideLayers('I' + boxid + 'links', 'followmouse', 'show', '' + pboxid);
	else
	{
		famlinks = document.getElementById("I" + boxid + "links");
		divbox = document.getElementById("out-" + boxid);
		parentbox = document.getElementById("box" + boxid);
		if (!parentbox) parentbox = document.getElementById(pboxid + ".0");
		if (famlinks && divbox && parentbox)
		{
			divWidth = parseInt(divbox.style.width);
			linkWidth = parseInt(famlinks.style.width);
			parentWidth = parseInt(parentbox.style.width);
			famlinks.style.top = "3px";
			famleft = divWidth + 8;
			if (textDirection == "rtl")
			{
				famleft -= (divWidth + linkWidth + 5);
				if (browserType != "mozilla") famleft -= 11;
			}
			pagewidth = document.documentElement.offsetWidth + document.documentElement.scrollLeft;
			if (famleft + parseInt(parentbox.style.left) > pagewidth - 100) famleft = 25;
			famlinks.style.left = famleft + "px";
			if (SCRIPT_NAME.indexOf("index.php") != -1) famlinks.style.left = "100%";
			MM_showHideLayers('I' + boxid + 'links', ' ', 'show', '' + pboxid);
		}
		else MM_showHideLayers('I' + boxid + 'links', 'followmouse', 'show', '' + pboxid);
	}
}

function hide_family_box (boxid)
{
	MM_showHideLayers('I' + boxid + 'links', '', 'hide', '');
	popupopen = 0;
	lastfamilybox = "";
}
var timeouts = [];

function family_box_timeout (boxid)
{
	timeouts[boxid] = setTimeout("hide_family_box('" + boxid + "')", 2500);
}

function clear_family_box_timeout (boxid)
{
	clearTimeout(timeouts[boxid]);
}

function expand_layer (sid)
{
	if (jQuery("#" + sid + "_img")
		.hasClass("fa-minimize"))
	{
		jQuery('#' + sid)
			.slideDown("slow");
		jQuery('#' + sid + "_img")
			.removeClass("fa-minimize")
			.addClass("fa-maximize");
	}
	else
	{
		jQuery('#' + sid)
			.slideUp("slow");
		jQuery('#' + sid + "_img")
			.removeClass("fa-maximize")
			.addClass("fa-minimize");
	}
	return false;
}
/**
 * @param params
 *        Object containing URL parameters.
 * @param {optional} windowspecs
 *        Window features to use.  Defaults to edit_window_specs.
 * @param {optional} pastefield
 *        Field to paste a result into.
 */
function edit_interface (params, windowspecs, pastefield)
{
	//    var features = windowspecs || edit_window_specs;
	var features = '';
	window.pastefield = pastefield;
	var url = 'edit_interface.php?' + jQuery.param(params) + '&accesstime=' + accesstime + '&ged=' + KT_GEDCOM;
	window.open(url, '_blank', features);
	return false;
}

function edit_record (pid, linenum)
{
	return edit_interface(
	{
		"action": "edit",
		"pid": pid,
		"linenum": linenum
	});
}

function edit_raw (pid)
{
	return edit_interface(
	{
		"action": "editraw",
		"pid": pid
	});
}

function edit_note (pid)
{
	return edit_interface(
	{
		"action": "editnote",
		"pid": pid,
		"linenum": 1
	});
}

function edit_source (pid)
{
	return edit_interface(
	{
		"action": "editsource",
		"pid": pid,
		"linenum": 1
	});
}

function add_record (pid, fact_field)
{
	var fact = jQuery('#' + fact_field)
		.val();
	if (fact)
	{
		if (fact === "OBJE")
		{
			window.open('addmedia.php?action=showmediaform&linkid=' + encodeURIComponent(xref) + '&ged=' + encodeURIComponent(KT_GEDCOM), '_blank', edit_window_specs);
		}
		else
		{
			return edit_interface(
			{
				"action": "add",
				"pid": pid,
				"fact": fact
			});
		}
	}
	return false;
}

function addClipboardRecord (pid, fact)
{
	var factfield = document.getElementById(fact);
	if (factfield)
	{
		var factvalue = factfield.options[factfield.selectedIndex].value;
		edit_interface(
		{
			"action": "paste",
			"pid": pid,
			"fact": factvalue.substr(10)
		});
	}
	return false;
}

function reorder_media (xref)
{
	return edit_interface(
	{
		"action": "reorder_media",
		"pid": xref
	}, mord_window_specs);
}

function add_new_record (pid, fact)
{
	return edit_interface(
	{
		"action": "add",
		"pid": pid,
		"fact": fact
	});
}

function addnewchild (famid, gender)
{
	return edit_interface(
	{
		"action": "addchild",
		"gender": gender,
		"famid": famid
	});
}

function addnewspouse (pid, famid, famtag)
{
	return edit_interface(
	{
		"action": "addspouse",
		"pid": pid,
		"famid": famid,
		"famtag": famtag
	});
}

function addopfchild (pid, gender)
{
	return edit_interface(
	{
		"action": "addopfchild",
		"pid": pid,
		"gender": gender
	});
}

function addspouse (pid, famtag)
{
	return edit_interface(
	{
		"action": "addspouse",
		"pid": pid,
		"famtag": famtag,
		"famid": "new"
	});
}

function linkspouse (pid, famtag)
{
	return edit_interface(
	{
		"action": "linkspouse",
		"pid": pid,
		"famtag": famtag,
		"famid": "new"
	});
}

function add_famc (pid)
{
	return edit_interface(
	{
		"action": "addfamlink",
		"pid": pid,
		"famtag": "CHIL"
	});
}

function add_fams (pid, famtag)
{
	return edit_interface(
	{
		"action": "addfamlink",
		"pid": pid,
		"famtag": famtag
	});
}

function edit_name (pid, linenum)
{
	return edit_interface(
	{
		"action": "editname",
		"pid": pid,
		"linenum": linenum
	});
}

function add_name (pid)
{
	return edit_interface(
	{
		"action": "addname",
		"pid": pid
	});
}

function addnewparent (pid, famtag)
{
	return edit_interface(
	{
		"action": "addnewparent",
		"pid": pid,
		"famtag": famtag,
		"famid": "new"
	});
}

function addnewparentfamily (pid, famtag, famid)
{
	return edit_interface(
	{
		"action": "addnewparent",
		"pid": pid,
		"famtag": famtag,
		"famid": famid
	});
}

function delete_fact (pid, linenum, mediaid, message)
{
	if (confirm(message))
	{
		return edit_interface(
		{
			"action": "delete",
			"pid": pid,
			"linenum": linenum,
			"mediaid": mediaid
		});
	}
}

function reorder_children (famid)
{
	return edit_interface(
	{
		"action": "reorder_children",
		"pid": famid
	});
}

function reorder_families (pid)
{
	return edit_interface(
	{
		"action": "reorder_fams",
		"pid": pid
	});
}

function reply (username, subject)
{
	window.open('message.php?to=' + username + '&subject=' + subject + '&ged=' + KT_GEDCOM, '_blank', mesg_window_specs);
	return false;
}

function delete_message (id)
{
	window.open('message.php?action=delete&id=' + id, '_blank' + '&ged=' + KT_GEDCOM, mesg_window_specs);
	return false;
}

function change_family_members (famid)
{
	return edit_interface(
	{
		"action": "changefamily",
		"famid": famid
	});
}

function addnewsource (field)
{
	return edit_interface(
	{
		"action": "addnewsource",
		"pid": "newsour"
	}, null, field);
}

function addnewrepository (field)
{
	return edit_interface(
	{
		"action": "addnewrepository",
		"pid": "newrepo"
	}, null, field);
}

function addnewnote (field)
{
	return edit_interface(
	{
		"action": "addnewnote",
		"noteid": "newnote"
	}, null, field);
}

function addnewnote_assisted (field, xref, census)
{
	return edit_interface(
	{
		"action": "addnewnote_assisted",
		"noteid": "newnote",
		"xref": xref,
		"census": census
	}, null, field);
}

function addmedia_links (field, iid, iname)
{
	pastefield = field;
	insertRowToTable(iid, iname);
	return false;
}

function valid_date (datefield)
{
	var months = new Array("JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC");
	var datestr = datefield.value;
	// if a date has a date phrase marked by () this has to be excluded from altering
	var datearr = datestr.split("(");
	var datephrase = "";
	if (datearr.length > 1)
	{
		datestr = datearr[0];
		datephrase = datearr[1];
	}
	// Gedcom dates are upper case
	datestr = datestr.toUpperCase();
	// Gedcom dates have no leading/trailing/repeated whitespace
	datestr = datestr.replace(/\s+/, " ");
	datestr = datestr.replace(/(^\s)|(\s$)/, "");
	// Gedcom dates have spaces between letters and digits, e.g. "01JAN2000" => "01 JAN 2000"
	datestr = datestr.replace(/(\d)([A-Z])/, "$1 $2");
	datestr = datestr.replace(/([A-Z])(\d)/, "$1 $2");
	// Shortcut for quarter format, "Q1 1900" => "BET JAN 1900 AND MAR 1900".  See [ 1509083 ]
	if (datestr.match(/^Q ([1-4]) (\d\d\d\d)$/))
	{
		datestr = "BET " + months[RegExp.$1 * 3 - 3] + " " + RegExp.$2 + " AND " + months[RegExp.$1 * 3 - 1] + " " + RegExp.$2;
	}
	// Shortcut for @#Dxxxxx@ 01 01 1400, etc.
	if (datestr.match(/^(@#DHIJRI@|HIJRI)( \d?\d )(\d?\d)( \d?\d?\d?\d)$/))
	{
		datestr = "@#DHIJRI@" + RegExp.$2 + hijri_months[parseInt(RegExp.$3, 10) - 1] + RegExp.$4;
	}
	if (datestr.match(/^(@#DJALALI@|JALALI)( \d?\d )(\d?\d)( \d?\d?\d?\d)$/))
	{
		datestr = "@#DJALALI@" + RegExp.$2 + jalali_months[parseInt(RegExp.$3, 10) - 1] + RegExp.$4;
	}
	if (datestr.match(/^(@#DHEBREW@|HEBREW)( \d?\d )(\d?\d)( \d?\d?\d?\d)$/))
	{
		datestr = "@#DHEBREW@" + RegExp.$2 + hebrew_months[parseInt(RegExp.$3, 10) - 1] + RegExp.$4;
	}
	if (datestr.match(/^(@#DFRENCH R@|FRENCH)( \d?\d )(\d?\d)( \d?\d?\d?\d)$/))
	{
		datestr = "@#DFRENCH R@" + RegExp.$2 + french_months[parseInt(RegExp.$3, 10) - 1] + RegExp.$4;
	}
	// e.g. 17.11.1860, 03/04/2005 or 1999-12-31.  Use locale settings where DMY order is ambiguous.
	var qsearch = /^([^\d]*)(\d+)[^\d](\d+)[^\d](\d+)$/i;
	if (qsearch.exec(datestr))
	{
		var f0 = RegExp.$1;
		var f1 = parseInt(RegExp.$2, 10);
		var f2 = parseInt(RegExp.$3, 10);
		var f3 = parseInt(RegExp.$4, 10);
		var f4 = RegExp.$5;
		var dmy = 'DMY';
		if (typeof (locale_date_format) != 'undefined')
			if (locale_date_format == 'MDY' || locale_date_format == 'YMD') dmy = locale_date_format;
		var yyyy = new Date()
			.getFullYear();
		var yy = yyyy % 100;
		var cc = yyyy - yy;
		if (dmy == 'DMY' && f1 <= 31 && f2 <= 12 || f1 > 13 && f1 <= 31 && f2 <= 12 && f3 > 31) datestr = f0 + f1 + " " + months[f2 - 1] + " " + (f3 >= 100 ? f3 : (f3 <= yy ? f3 + cc : f3 + cc - 100));
		else if (dmy == 'MDY' && f1 <= 12 && f2 <= 31 || f2 > 13 && f2 <= 31 && f1 <= 12 && f3 > 31) datestr = f0 + f2 + " " + months[f1 - 1] + " " + (f3 >= 100 ? f3 : (f3 <= yy ? f3 + cc : f3 + cc - 100));
		else if (dmy == 'YMD' && f2 <= 12 && f3 <= 31 || f3 > 13 && f3 <= 31 && f2 <= 12 && f1 > 31) datestr = f0 + f3 + " " + months[f2 - 1] + " " + (f1 >= 100 ? f1 : (f1 <= yy ? f1 + cc : f1 + cc - 100));
	}
	// Shortcuts for date ranges
	datestr = datestr.replace(/^[>]([\w ]+)$/, "AFT $1");
	datestr = datestr.replace(/^[<]([\w ]+)$/, "BEF $1");
	datestr = datestr.replace(/^([\w ]+)[-]$/, "FROM $1");
	datestr = datestr.replace(/^[-]([\w ]+)$/, "TO $1");
	datestr = datestr.replace(/^[~]([\w ]+)$/, "ABT $1");
	datestr = datestr.replace(/^[*]([\w ]+)$/, "EST $1");
	datestr = datestr.replace(/^[#]([\w ]+)$/, "CAL $1");
	datestr = datestr.replace(/^([\w ]+) ?- ?([\w ]+)$/, "BET $1 AND $2");
	datestr = datestr.replace(/^([\w ]+) ?~ ?([\w ]+)$/, "FROM $1 TO $2");
	if (datestr.match(/^=([\d ()\/+*-]+)$/)) datestr = eval(RegExp.$1);
	// Convert full months to short months
	// TODO: also convert long/short months in other languages
	datestr = datestr.replace(/(JANUARY)/, "JAN");
	datestr = datestr.replace(/(FEBRUARY)/, "FEB");
	datestr = datestr.replace(/(MARCH)/, "MAR");
	datestr = datestr.replace(/(APRIL)/, "APR");
	datestr = datestr.replace(/(MAY)/, "MAY");
	datestr = datestr.replace(/(JUNE)/, "JUN");
	datestr = datestr.replace(/(JULY)/, "JUL");
	datestr = datestr.replace(/(AUGUST)/, "AUG");
	datestr = datestr.replace(/(SEPTEMBER)/, "SEP");
	datestr = datestr.replace(/(OCTOBER)/, "OCT");
	datestr = datestr.replace(/(DECEMBER)/, "DEC");
	// Americans frequently enter dates as SEP 20, 1999
	// No need to internationalise this, as this is an english-language issue
	datestr = datestr.replace(/(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\.? (\d\d?)[, ]+(\d\d\d\d)/, "$2 $1 $3")
	// Apply leading zero to day numbers
	datestr = datestr.replace(/(^| )(\d [A-Z]{3,5} \d{4})/, "$10$2");
	if (datephrase)
	{
		datestr = datestr + " (" + datephrase;
	}
	// Only update it if is has been corrected - otherwise input focus
	// moves to the end of the field unnecessarily
	if (datefield.value !== datestr)
	{
		datefield.value = datestr;
	}
}
var oldheight = 0;
var oldwidth = 0;
var oldz = 0;
var oldleft = 0;
var big = 0;
var oldboxid = "";
var oldimgw = 0;
var oldimgh = 0;
var oldimgw1 = 0;
var oldimgh1 = 0;
var diff = 0;
var oldfont = 0;
var oldname = 0;
var oldthumbdisp = 0;
var repositioned = 0;
var oldiconsdislpay = 0;
var rv = null;

function expandbox (boxid, bstyle)
{
	if (big == 1)
	{
		if (clength > 0)
		{ // True only if compact chart
			fontdef.style.display = 'none';
		}
		restorebox(oldboxid, bstyle);
		if (boxid == oldboxid) return true;
	}
	jQuery(document)
		.ready(function ()
		{
			clength = jQuery(".compact_view")
				.length;
		});
	var url = window.location.toString();
	divbox = document.getElementById("out-" + boxid);
	inbox = document.getElementById("inout-" + boxid);
	inbox2 = document.getElementById("inout2-" + boxid);
	parentbox = document.getElementById("box" + boxid);
	if (!parentbox)
	{
		parentbox = divbox;
		//    if (bstyle!=2) divbox.style.position="absolute";
	}
	gender = document.getElementById("box-" + boxid + "-gender");
	thumb1 = document.getElementById("box-" + boxid + "-thumb");
	famlinks = document.getElementById("I" + boxid + "links");
	icons = document.getElementById("icons-" + boxid);
	iconz = document.getElementById("iconz-" + boxid); // This is the Zoom icon
	if (divbox)
	{
		if (icons)
		{
			oldiconsdislpay = icons.style.display;
			icons.style.display = "block";
		}
		if (jQuery(iconz)
			.hasClass("icon-zoomin"))
		{
			jQuery(iconz)
				.removeClass("icon-zoomin")
				.addClass("icon-zoomout");
		}
		else
		{
			jQuery(iconz)
				.removeClass("icon-zoomout")
				.addClass("icon-zoomin");
		}
		oldboxid = boxid;
		big = 1;
		oldheight = divbox.style.height;
		oldwidth = divbox.style.width;
		oldz = parentbox.style.zIndex;
		if (url.indexOf("descendancy.php") == -1) parentbox.style.zIndex = '100';
		if (bstyle != 2)
		{
			divbox.style.width = '300px';
			diff = 300 - parseInt(oldwidth);
			if (famlinks)
			{
				famleft = parseInt(famlinks.style.left);
				famlinks.style.left = (famleft + diff) + "px";
			}
			//parentbox.style.width = parseInt(parentbox.style.width)+diff;
		}
		divleft = parseInt(parentbox.style.left);
		if (textDirection == "rtl") divleft = parseInt(parentbox.style.right);
		oldleft = divleft;
		divleft = divleft - diff;
		repositioned = 0;
		if (divleft < 0)
		{
			repositioned = 1;
			divleft = 0;
		}
		divbox.style.height = 'auto';
		if (inbox)
		{
			inbox.style.display = 'block';
			if (inbox.innerHTML.indexOf("LOADING") > 0)
			{
				//-- load data from expand_view.php
				var pid = boxid.split(".")[0];
				var oXmlHttp = createXMLHttp();
				oXmlHttp.open("get", "expand_view.php?pid=" + pid, true);
				oXmlHttp.onreadystatechange = function ()
				{
					if (oXmlHttp.readyState == 4)
					{
						inbox.innerHTML = oXmlHttp.responseText;
					}
				};
				oXmlHttp.send(null);
			}
		}
		else
		{
			inbox.style.display = 'none';
		}
		if (inbox2) inbox2.style.display = 'none';
		fontdef = document.getElementById("fontdef-" + boxid);
		if (fontdef)
		{
			oldfont = fontdef.className;
			fontdef.className = 'detailsZoom';
			fontdef.style.display = 'block';
		}
		namedef = document.getElementById("namedef-" + boxid);
		if (namedef)
		{
			oldname = namedef.className;
			namedef.className = 'nameZoom';
		}
		addnamedef = document.getElementById("addnamedef-" + boxid);
		if (addnamedef)
		{
			oldaddname = addnamedef.className;
			addnamedef.className = 'nameZoom';
		}
		if (thumb1)
		{
			oldthumbdisp = thumb1.style.display;
			thumb1.style.display = 'block';
			oldimgw = thumb1.offsetWidth;
			oldimgh = thumb1.offsetHeight;
			if (oldimgw) thumb1.style.width = (oldimgw * 2) + "px";
			if (oldimgh) thumb1.style.height = (oldimgh * 2) + "px";
		}
		if (gender)
		{
			oldimgw1 = gender.offsetWidth;
			oldimgh1 = gender.offsetHeight;
			if (oldimgw1) gender.style.width = "15px";
			if (oldimgh1) gender.style.height = "15px";
		}
	}
	return true;
}

function createXMLHttp ()
{
	if (typeof XMLHttpRequest != "undefined")
	{
		return new XMLHttpRequest();
	}
	else if (window.ActiveXObject)
	{
		var ARR_XMLHTTP_VERS = ["MSXML2.XmlHttp.5.0", "MSXML2.XmlHttp.4.0",
			"MSXML2.XmlHttp.3.0", "MSXML2.XmlHttp", "Microsoft.XmlHttp"
		];
		for (var i = 0; i < ARR_XMLHTTP_VERS.length; i++)
		{
			try
			{
				var oXmlHttp = new ActiveXObject(ARR_XMLHTTP_VERS[i]);
				return oXmlHttp;
			}
			catch (oError)
			{
				;
			}
		}
	}
	throw new Error("XMLHttp object could not be created.");
}

function restorebox (boxid, bstyle)
{
	divbox = document.getElementById("out-" + boxid);
	inbox = document.getElementById("inout-" + boxid);
	inbox2 = document.getElementById("inout2-" + boxid);
	parentbox = document.getElementById("box" + boxid);
	if (!parentbox)
	{
		parentbox = divbox;
	}
	thumb1 = document.getElementById("box-" + boxid + "-thumb");
	icons = document.getElementById("icons-" + boxid);
	iconz = document.getElementById("iconz-" + boxid); // This is the Zoom icon
	if (divbox)
	{
		if (icons) icons.style.display = oldiconsdislpay;
		if (jQuery(iconz)
			.hasClass("icon-zoomin"))
		{
			jQuery(iconz)
				.removeClass("icon-zoomin")
				.addClass("icon-zoomout");
		}
		else
		{
			jQuery(iconz)
				.removeClass("icon-zoomout")
				.addClass("icon-zoomin");
		}
		big = 0;
		if (gender)
		{
			oldimgw1 = oldimgw1 + "px";
			oldimgh1 = oldimgh1 + "px";
			gender.style.width = oldimgw1;
			gender.style.height = oldimgh1;
		}
		if (thumb1)
		{
			oldimgw = oldimgw + "px";
			oldimgh = oldimgh + "px";
			thumb1.style.width = oldimgw;
			thumb1.style.height = oldimgh;
			thumb1.style.display = oldthumbdisp;
		}
		divbox.style.height = oldheight;
		divbox.style.width = oldwidth;
		if (parentbox)
		{
			//if (parentbox!=divbox) parentbox.style.width = parseInt(parentbox.style.width)-diff;
			//alert("here");
			parentbox.style.zIndex = oldz;
		}
		if (inbox) inbox.style.display = 'none';
		if (inbox2) inbox2.style.display = 'block';
		fontdef = document.getElementById("fontdef-" + boxid);
		if (fontdef) fontdef.className = oldfont;
		namedef = document.getElementById("namedef-" + boxid);
		if (namedef) namedef.className = oldname;
		addnamedef = document.getElementById("addnamedef-" + boxid);
		if (addnamedef) addnamedef.className = oldaddname;
	}
	return true;
}
var menutimeouts = [];
/**
 * Shows a submenu
 *
 * @author John Finlay
 * @param string elementid the id for the dom element you want to show
 */
function show_submenu (elementid, parentid, dir)
{
	var pagewidth = document.body.scrollWidth + document.documentElement.scrollLeft;
	var element = document.getElementById(elementid);
	if (element && element.style)
	{
		if (document.all)
		{
			pagewidth = document.body.offsetWidth;
			//if (textDirection=="rtl") element.style.left = (element.offsetLeft-70)+'px';
		}
		else
		{
			pagewidth = document.body.scrollWidth + document.documentElement.scrollLeft - 55;
			if (textDirection == "rtl")
			{
				boxright = element.offsetLeft + element.offsetWidth + 10;
			}
		}
		//-- make sure the submenu is the size of the largest child
		var maxwidth = 0;
		var count = element.childNodes.length;
		for (var i = 0; i < count; i++)
		{
			var child = element.childNodes[i];
			if (child.offsetWidth > maxwidth + 5) maxwidth = child.offsetWidth;
		}
		if (element.offsetWidth < maxwidth)
		{
			element.style.width = maxwidth + "px";
		}
		var pelement, boxright
		if (dir == "down")
		{
			pelement = document.getElementById(parentid);
			if (pelement)
			{
				element.style.left = pelement.style.left;
				boxright = element.offsetLeft + element.offsetWidth + 10;
				if (boxright > pagewidth)
				{
					var menuleft = pagewidth - element.offsetWidth;
					element.style.left = menuleft + "px";
				}
			}
		}
		else if (dir == "right")
		{
			pelement = document.getElementById(parentid);
			if (pelement)
			{
				if (textDirection == "ltr")
				{
					var boxleft = pelement.offsetLeft + pelement.offsetWidth - 40;
					boxright = boxleft + element.offsetWidth + 10;
					if (boxright > pagewidth)
					{
						element.style.right = pelement.offsetLeft + "px";
					}
					else
					{
						element.style.left = boxleft + "px";
					}
				}
				else
				{
					//                    element.style.right = pelement.offsetLeft+"px";
					element.style.left = (pelement.offsetLeft - element.offsetWidth) + "px";
				}
				element.style.top = pelement.offsetTop + "px";
			}
		}
		if (element.offsetLeft < 0) element.style.left = "0px";
		//-- put scrollbars on really long menus
		if (element.offsetHeight > 500)
		{
			element.style.height = '400px';
			element.style.overflow = 'auto';
		}
		element.style.visibility = 'visible';
	}
	clearTimeout(menutimeouts[elementid]);
	menutimeouts[elementid] = null;
}
/**
 * Hides a submenu
 *
 * @author John Finlay
 * @param string elementid the id for the dom element you want to hide
 */
function hide_submenu (elementid)
{
	if (typeof menutimeouts[elementid] !== "number")
	{
		return;
	}
	var element = document.getElementById(elementid);
	if (element && element.style)
	{
		element.style.visibility = 'hidden';
	}
	clearTimeout(menutimeouts[elementid]);
	menutimeouts[elementid] = null;
}
/**
 * Sets a timeout to hide a submenu
 *
 * @author John Finlay
 * @param string elementid the id for the dom element you want to hide
 */
function timeout_submenu (elementid)
{
	if (typeof menutimeouts[elementid] !== "number")
	{
		menutimeouts[elementid] = setTimeout("hide_submenu('" + elementid + "')", 100);
	}
}

function statusDisable (sel)
{
	var cbox = document.getElementById(sel);
	cbox.checked = false;
	cbox.disabled = true;
}

function statusEnable (sel)
{
	var cbox = document.getElementById(sel);
	cbox.disabled = false;
}

function statusChecked (sel)
{
	var cbox = document.getElementById(sel);
	cbox.checked = true;
}
var monthLabels = [];
monthLabels[1] = "January";
monthLabels[2] = "February";
monthLabels[3] = "March";
monthLabels[4] = "April";
monthLabels[5] = "May";
monthLabels[6] = "June";
monthLabels[7] = "July";
monthLabels[8] = "August";
monthLabels[9] = "September";
monthLabels[10] = "October";
monthLabels[11] = "November";
monthLabels[12] = "December";
var monthShort = [];
monthShort[1] = "JAN";
monthShort[2] = "FEB";
monthShort[3] = "MAR";
monthShort[4] = "APR";
monthShort[5] = "MAY";
monthShort[6] = "JUN";
monthShort[7] = "JUL";
monthShort[8] = "AUG";
monthShort[9] = "SEP";
monthShort[10] = "OCT";
monthShort[11] = "NOV";
monthShort[12] = "DEC";
var daysOfWeek = [];
daysOfWeek[0] = "S";
daysOfWeek[1] = "M";
daysOfWeek[2] = "T";
daysOfWeek[3] = "W";
daysOfWeek[4] = "T";
daysOfWeek[5] = "F";
daysOfWeek[6] = "S";
var weekStart = 0;

function cal_setMonthNames (jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec)
{
	monthLabels[1] = jan;
	monthLabels[2] = feb;
	monthLabels[3] = mar;
	monthLabels[4] = apr;
	monthLabels[5] = may;
	monthLabels[6] = jun;
	monthLabels[7] = jul;
	monthLabels[8] = aug;
	monthLabels[9] = sep;
	monthLabels[10] = oct;
	monthLabels[11] = nov;
	monthLabels[12] = dec;
}

function cal_setDayHeaders (sun, mon, tue, wed, thu, fri, sat)
{
	daysOfWeek[0] = sun;
	daysOfWeek[1] = mon;
	daysOfWeek[2] = tue;
	daysOfWeek[3] = wed;
	daysOfWeek[4] = thu;
	daysOfWeek[5] = fri;
	daysOfWeek[6] = sat;
}

function cal_setWeekStart (day)
{
	if (day >= 0 && day < 7) weekStart = day;
}

function cal_toggleDate (dateDivId, dateFieldId)
{
	var dateDiv = document.getElementById(dateDivId);
	if (!dateDiv) return false;
	if (dateDiv.style.visibility == 'visible')
	{
		dateDiv.style.visibility = 'hidden';
		return false;
	}
	if (dateDiv.style.visibility == 'show')
	{
		dateDiv.style.visibility = 'hide';
		return false;
	}
	var dateField = document.getElementById(dateFieldId);
	if (!dateField) return false;
	/* Javascript calendar functions only work with precise gregorian dates "D M Y" or "Y" */
	var greg_regex = /((\d+ (JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC) )?\d+)/;
	var date;
	if (greg_regex.exec(dateField.value))
	{
		date = new Date(RegExp.$1);
	}
	else
	{
		date = new Date();
	}
	dateDiv.innerHTML = cal_generateSelectorContent(dateFieldId, dateDivId, date);
	if (dateDiv.style.visibility == 'hidden')
	{
		dateDiv.style.visibility = 'visible';
		return false;
	}
	if (dateDiv.style.visibility == 'hide')
	{
		dateDiv.style.visibility = 'show';
		return false;
	}
	return false;
}

function cal_generateSelectorContent (dateFieldId, dateDivId, date)
{
	var content = '<table border="1"><thead><tr>';
	content += '<th><select name="' + dateFieldId + '_daySelect" id="' + dateFieldId + '_daySelect" onchange="return cal_updateCalendar(\'' + dateFieldId + '\', \'' + dateDivId + '\');">';
	for (i = 1; i < 32; i++)
	{
		content += '<option value="' + i + '"';
		if (date.getDate() == i) content += ' selected="selected"';
		content += '>' + i + '</option>';
	}
	content += '</select></th>';
	content += '<th><select name="' + dateFieldId + '_monSelect" id="' + dateFieldId + '_monSelect" onchange="return cal_updateCalendar(\'' + dateFieldId + '\', \'' + dateDivId + '\');">';
	for (i = 1; i < 13; i++)
	{
		content += '<option value="' + i + '"';
		if (date.getMonth() + 1 == i) content += ' selected="selected"';
		content += '>' + monthLabels[i] + '</option>';
	}
	content += '</select></th>';
	content += '</tr><thead><tbody><tr>';
	content += '<th colspan="2"><input type="text" name="' + dateFieldId + '_yearInput" id="' + dateFieldId + '_yearInput" size="5" value="' + date.getFullYear() + '" onchange="return cal_updateCalendar(\'' + dateFieldId + '\', \'' + dateDivId + '\');" /></th></tr>';
	content += '<tr><td colspan="2">';
	content += '<table width="100%">';
	content += '<tr>';
	j = weekStart;
	for (i = 0; i < 7; i++)
	{
		content += '<th>';
		content += daysOfWeek[j];
		content += '</th>';
		j++;
		if (j > 6) j = 0;
	}
	content += '</tr>';
	var tdate = new Date(date.getFullYear(), date.getMonth(), 1);
	var day = tdate.getDay();
	day = day - weekStart;
	var daymilli = (1000 * 60 * 60 * 24);
	tdate = tdate.getTime() - (day * daymilli) + (daymilli / 2);
	tdate = new Date(tdate);
	for (j = 0; j < 6; j++)
	{
		content += '<tr>';
		for (i = 0; i < 7; i++)
		{
			content += '<td ';
			if (tdate.getMonth() == date.getMonth())
			{
				if (tdate.getDate() == date.getDate()) content += '';
				else content += 'class="thisMth"';
			}
			else content += '';
			content += '><a href="#" onclick="return cal_dateClicked(\'' + dateFieldId + '\', \'' + dateDivId + '\', ' + tdate.getFullYear() + ', ' + tdate.getMonth() + ', ' + tdate.getDate() + ');">';
			content += tdate.getDate();
			content += '</a></td>';
			var datemilli = tdate.getTime() + daymilli;
			tdate = new Date(datemilli);
		}
		content += '</tr>';
	}
	content += '</table>';
	content += '</td></tr><tbody>';
	content += '</table>';
	return content;
}

function cal_setDateField (dateFieldId, year, month, day)
{
	var dateField = document.getElementById(dateFieldId);
	if (!dateField) return false;
	if (day < 10) day = "0" + day;
	dateField.value = day + ' ' + monthShort[month + 1] + ' ' + year;
	return false;
}

function cal_updateCalendar (dateFieldId, dateDivId)
{
	var dateSel = document.getElementById(dateFieldId + '_daySelect');
	if (!dateSel) return false;
	var monthSel = document.getElementById(dateFieldId + '_monSelect');
	if (!monthSel) return false;
	var yearInput = document.getElementById(dateFieldId + '_yearInput');
	if (!yearInput) return false;
	var month = parseInt(monthSel.options[monthSel.selectedIndex].value);
	month = month - 1;
	var date = new Date(yearInput.value, month, dateSel.options[dateSel.selectedIndex].value);
	if (!date) alert('Date error ' + date);
	cal_setDateField(dateFieldId, date.getFullYear(), date.getMonth(), date.getDate());
	var dateDiv = document.getElementById(dateDivId);
	if (!dateDiv)
	{
		alert('no dateDiv ' + dateDivId);
		return false;
	}
	dateDiv.innerHTML = cal_generateSelectorContent(dateFieldId, dateDivId, date);
	return false;
}

function cal_dateClicked (dateFieldId, dateDivId, year, month, day)
{
	cal_setDateField(dateFieldId, year, month, day);
	cal_toggleDate(dateDivId, dateFieldId);
	return false;
}

function findWindow (ged, type, pastefield, queryParams)
{
	queryParams = queryParams || {};
	queryParams.type = type;
	queryParams.ged = typeof ged === 'undefined' ? KT_GEDCOM : ged;
	window.pastefield = pastefield;
	window.open('find.php?' + jQuery.param(queryParams), '_blank', find_window_specs);
	return false;
}

function findSpecialChar (field)
{
	return findWindow(undefined, "specialchar", field);
}

function findFact (field, ged)
{
	return findWindow(ged, "facts", field,
	{
		"tags": field.value
	});
}

function openerpasteid (id)
{
	if (window.opener.paste_id)
	{
		window.opener.paste_id(id);
	}
	window.close();
}

function paste_id (value)
{
	pastefield.value = value;
}

function pastename (name)
{
	if (nameElement)
	{
		nameElement.innerHTML = name;
	}
	if (remElement)
	{
		remElement.style.display = "block";
	}
}

function paste_char (value)
{
	if (document.selection)
	{
		// IE
		pastefield.focus();
		sel = document.selection.createRange();
		sel.text = value;
	}
	else if (pastefield.selectionStart || pastefield.selectionStart == 0)
	{
		// Mozilla/Chrome/Safari
		pastefield.value = pastefield.value.substring(0, pastefield.selectionStart) + value + pastefield.value.substring(pastefield.selectionEnd, pastefield.value.length);
		pastefield.selectionStart = pastefield.selectionEnd = pastefield.selectionStart + value.length;
	}
	else
	{
		// Fallback? - just append
		pastefield.value += value;
	}
	if (pastefield.id == "NPFX" || pastefield.id == "GIVN" || pastefield.id == "SPFX" || pastefield.id == "SURN" || pastefield.id == "NSFX")
	{
		updatewholename();
	}
}

function message (username, method, url, subject)
{
	window.open('message.php?to=' + username + '&method=' + method + '&url=' + url + '&subject=' + subject, '_blank', mesg_window_specs);
	return false;
}
/**
 * Load a CSS file from the body of a document
 *
 * CSS files are normally loaded through a <link rel="stylesheet" type="text/css" href="something" />
 * statement.  This statement is only allowed in the <head> section of the document.
 *
 * See : http://www.phpied.com/javascript-include-ready-onload/
 *
 */
function include_css (css_file)
{
	var html_doc = document.getElementsByTagName('head')[0];
	var css = document.createElement('link');
	css.setAttribute('rel', 'stylesheet');
	css.setAttribute('type', 'text/css');
	css.setAttribute('href', css_file);
	html_doc.appendChild(css);
}

function include_js (file)
{
	var html_doc = document.getElementsByTagName('head')[0];
	var js = document.createElement('script');
	js.setAttribute('type', 'text/javascript');
	js.setAttribute('src', file);
	html_doc.appendChild(js);
}

function findPosX (obj)
{
	var curleft = 0;
	if (obj.offsetParent)
		while (1)
		{
			curleft += obj.offsetLeft;
			if (!obj.offsetParent) break;
			obj = obj.offsetParent;
		}
	else if (obj.x) curleft += obj.x;
	return curleft;
}

function findPosY (obj)
{
	var curtop = 0;
	if (obj.offsetParent)
		while (1)
		{
			if (obj.style.position == "relative") break;
			curtop += obj.offsetTop;
			if (!obj.offsetParent) break;
			obj = obj.offsetParent;
		}
	else if (obj.y) curtop += obj.y;
	return curtop;
}
/**
 *  function to create notes display on colorbox
 */
function notes (obj)
{
	var note = obj.data("obje-note");
	jQuery(".cboxPhoto")
		.each(function ()
		{
			jQuery(this)
				.attr("title", note);
			jQuery(this)
				.tooltip(
				{
					tooltipClass: "cboxTooltip",
					position:
					{
						my: "center",
						at: "bottom-60"
					},
					hide:
					{
						duration: 3000
					}
				})
				.mouseenter();
		});
}
/**
 *  This is the default way to show image galleries.
 *  Custom themes may use a different viewer.
 */
function activate_colorbox (config)
{
	jQuery.extend(jQuery.colorbox.settings,
	{
		current: '',
		// Don't scroll window with document
		fixed: true,
	});
	if (config)
	{
		jQuery.extend(jQuery.colorbox.settings, config);
	}
	// Trigger an event when we click on an (any) image
	jQuery('body')
		.on('click', 'a.gallery', function (event)
		{
			// Remove colorbox from hidden media (e.g. on other tabs)
			// (not needed unless we add :visible to our selectors - which may not
			// work on all browsers?)
			//jQuery.colorbox.remove();
			// Enable colorbox for images
			jQuery('a[type^=image].gallery')
				.colorbox(
				{
					rel: 'gallery', // Turn all images on the page into a slideshow
					photo: true,
					fixed: false,
					maxWidth: '95%',
					maxHeight: '95%',
					minWidth: "320",
					slideshowSpeed: 5000,
					slideshow: true,
					slideshowAuto: false,
					speed: 2000,
					title: function ()
					{
						var title = jQuery(this)
							.data("title");
						return title;
					},
					onComplete: function ()
					{
						// Display notes
						notes(jQuery(this));
						// Add wheelzoom to the displayed image
						jQuery('.cboxPhoto')
							.wheelzoom();
						// Drag events cause the slideshow to advance.  Prevent this.
						// TODO - only when the click was the end of a drag..
						jQuery('.cboxPhoto img')
							.on('click', function (e)
							{
								e.preventDefault();
							});
					}
				});
			// Add colorbox to pdf-files
			jQuery("a[type^=application].gallery")
				.colorbox(
				{
					innerWidth: "60%",
					innerHeight: "90%",
					iframe: true
				});
			// Enable colorbox for video using <video></video>, where supported
			jQuery('a[type^=video].gallery')
				.colorbox(
				{
					innerWidth: "50%",
					innerHeight: "80%",
					iframe: true,
					rel: 'nofollow' // Slideshows are just for images
				});
			// Enable colorbox for video using <audio></audio>, where supported
			//    jQuery('html.audio a[type^=audio].gallery').colorbox({
			//        rel:         'nofollow', // Slideshows are just for images
			//    });
			// Allow all other media types remain as download links
		});
}
/**
 *  Initialize autocomplete elements
 */
function autocomplete (selector)
{
	if (typeof (selector) === "undefined")
	{
		selector = "input[data-autocomplete-type]";
	}
	jQuery(selector)
		.each(function ()
		{
			var type = $(this)
				.data("autocomplete-type"); // What type of field
			// Warn of data type missing
			if (typeof (type) === "undefined")
			{
				alert("Missing data-autocomplete-type attribute");
			}
			var ged = jQuery(this)
				.data("autocomplete-ged"); // Which family tree
			// Default to the current tree
			if (typeof (ged) === "undefined")
			{
				jQuery(this)
					.data("autocomplete-ged", KT_GEDCOM);
			}
			var self = jQuery(this);
			self.autocomplete(
			{
				// Cannot use a simple URL, as the data-autocomplete-xxxx parameters may change.
				source: function (request, response)
				{
					// Some autocomplete fields require the current value of an earlier field
					var extra = null;
					if (self.data("autocomplete-extra"))
					{
						extra = jQuery("#selectedValue-" + self.data("autocomplete-extra"))
							.val();
					}
					jQuery.getJSON("autocomplete.php",
					{
						field: self.data("autocomplete-type"),
						ged: self.data("autocomplete-ged"),
						extra: extra,
						term: request.term
					}, response);
				},
				select: function (event, ui)
				{
					var item_html = jQuery("<p>" + ui.item.label + "</p>")
						.text();
					jQuery(self)
						.val(item_html);
					/**
					 * Display label in input field
					 */
					jQuery(self)
						.nextAll("input[id^=selectedValue]")
						.val(ui.item.value); //Saving the selected id in hidden field
					return false;
				},
				html: true
			});
		});
}
/**
 *  Clear autocomplete input field.
 */
jQuery(".clearAutocomplete")
	.click(function ()
	{
		var clickElement = jQuery(this)
			.attr("id");
		jQuery("input[id=selectedValue-" + clickElement + "]")
			.val("");
		jQuery("input[id=autocompleteInput-" + clickElement + "]")
			.val("")
			.focus();
		return false;
	});
/**
 * Add LTR/RTL support for jQueryUI Accordions
 * jQuery.extend($.ui.accordion.prototype.options, {
	icons: {
		header: textDirection === "rtl" ? "ui-icon-triangle-1-w" : "ui-icon-triangle-1-e",
		activeHeader: "ui-icon-triangle-1-s"
	}
});
*/
/**
 *  Optionally apply all CAPS to surnames
 */
function all_caps ()
{
	jQuery(".NAME .SURN")
		.css("text-transform", "uppercase");
}

function widget_bar ()
{
	jQuery("#widget-button")
		.click(function ()
		{
			jQuery("#widget-bar")
				.toggle();
		});
	jQuery("#widget-button")
		.click(function ()
		{
			jQuery("#widget-button")
				.toggleClass("fa-bars fa-xmark");
		});
}
/**
 *  Select all / no records in a list
 */
function toggle_select (source)
{
	checkboxes = document.getElementsByClassName("check");
	for (var i = 0, n = checkboxes.length; i < n; i++)
	{
		checkboxes[i].checked = source.checked;
	}
}
/**
 *  Select all / no records in a list
 */
jQuery(".parent")
	.each(function (index)
	{
		var group = jQuery(this)
			.data("group");
		var parent = jQuery(this);
		parent.change(function ()
		{ //"select all" change
			jQuery(group)
				.prop('checked', parent.prop("checked"));
		});
		jQuery(group)
			.change(function ()
			{
				parent.prop('checked', false);
				if (jQuery(group + ':checked')
					.length == jQuery(group)
					.length)
				{
					parent.prop('checked', true);
				}
			});
	});
/**
 *  Delete multiple list items
 */
function checkbox_delete (type)
{
	var i = 0,
		counter = 0,
		delete_list = [];
	input_obj = document.getElementsByClassName("check");
	for (i = 0; i < input_obj.length; i++)
	{
		if (input_obj[i].checked === true)
		{
			counter++;
			delete_list.push(input_obj[i].value);
		}
	}
	for (i = 0; i < counter; i++)
	{
		switch (type)
		{
			case "sources":
				jQuery.post("action.php",
				{
					action: "delete-source",
					xref: delete_list[i]
				}, function ()
				{
					location.reload();
				});
				break;
			case "notes":
				jQuery.post("action.php",
				{
					action: "delete-note",
					xref: delete_list[i]
				}, function ()
				{
					location.reload();
				});
				break;
			case "repos":
				jQuery.post("action.php",
				{
					action: "delete-repository",
					xref: delete_list[i]
				}, function ()
				{
					location.reload();
				});
				break;
			case "places":
				window.location = location.pathname + '?mod=googlemap&mod_action=admin_places&action=DeleteRecord&deleteRecord=' + delete_list;
				break;
			case "repos":
				jQuery.post("action.php",
				{
					action: "delete-repository",
					xref: delete_list[i]
				}, function ()
				{
					location.reload();
				});
				break;
			case "dna":
				jQuery.post("action.php",
				{
					action: "delete-dna",
					dna_id: delete_list[i]
				}, function ()
				{
					location.reload();
				});
				break;
			case "resn":
				jQuery.post("action.php",
				{
					action: "delete-resn",
					default_resn_id: delete_list[i]
				}, function ()
				{
					location.reload();
				});
				break;
		}
	}
}
/**
 *  common script for help text drop-down display
 */
jQuery(".help_content")
	.on("click", ".more", function (e)
	{
		e.preventDefault();
		jQuery(this)
			.next(".hidden")
			.slideToggle("slow");
		jQuery(this)
			.parent()
			.siblings()
			.find(".hidden")
			.slideUp();
	});
/**
 * Persistant checkbox options to hide/show extra data.

 * @param checkbox_id
 * @param data_selector
 */
function persistent_toggle (checkbox_id, data_selector)
{
	var checkbox = document.getElementById(checkbox_id);
	var elements = document.querySelectorAll(data_selector);
	var display = localStorage.getItem(checkbox_id);
	if (!checkbox)
	{
		return;
	}
	if (display !== "")
	{
		display = "none";
	}
	checkbox.checked = (display === "");
	for (var i = 0; i < elements.length; ++i)
	{
		elements[i].style.display = display;
	}
	checkbox.addEventListener("click", function ()
	{
		console.log(display);
		display = (display === "" ? "none" : "");
		localStorage.setItem(checkbox_id, display);
		for (var i = 0; i < elements.length; ++i)
		{
			elements[i].style.display = display;
		}
	});
}
/**
 *  Reveal passwords
 **/
jQuery('.unmask')
	.on('click', function ()
	{
		if (jQuery(this)
			.prev('input')
			.attr('type') == 'password')
		{
			changeType(jQuery(this)
				.prev('input'), 'text');
			jQuery(".close-eye")
				.attr("data-icon", "eye-slash");
		}
		else
		{
			changeType(jQuery(this)
				.prev('input'), 'password');
			jQuery(".close-eye")
				.attr("data-icon", "eye");
		}
		return false;
	});

function changeType (x, type)
{
	if (x.prop('type') == type)
	{
		return x; //That was easy.
	}
	try
	{
		return x.prop('type', type); //Stupid IE security will not allow this
	}
	catch (e)
	{
		//Try re-creating the element (yep... this sucks)
		//jQuery has no html() method for the element, so we have to put into a div first
		var html = jQuery("<div>")
			.append(x.clone())
			.html();
		var regex = /type=(\")?([^\"\s]+)(\")?/; //matches type=text or type="text"
		//If no match, we add the type attribute to the end; otherwise, we replace
		var tmp = jQuery(html.match(regex) == null ? html.replace(">", ' type="' + type + '">') : html.replace(regex, 'type="' + type + '"'));
		//Copy data from old element
		tmp.data('type', x.data('type'));
		var events = x.data('events');
		var cb = function (events)
		{
			return function ()
			{
				//Bind all prior events
				for (i in events)
				{
					var y = events[i];
					for (j in y) tmp.bind(i, y[j].handler);
				}
			}
		}(events);
		x.replaceWith(tmp);
		setTimeout(cb, 10); //Wait a bit to call function
		return tmp;
	}
}
/*
 * Initialise jquery=confirm
 *  PARAMS : /** -
 *
 */
function jquery_confirm_defaults ()
{
	jconfirm.defaults = {
		bgOpacity: "50%",
		boxWidth: "100%",
		useBootstrap: false,
		draggable: false,
		icon: "fas fa-exclamation-triangle",
		theme: "kiwitrees",
		animation: "rotate",
		animateFromElement: false,
		defaultButtons:
		{
			ok:
			{
				isHidden: true,
				isDisabled: true,
			},
			close:
			{
				isHidden: true,
				isDisabled: true,
			},
		},
		autoClose: "close|5000",
		buttons:
		{
			cancel:
			{
				text: "Cancel",
				btnClass: "btnCancel button hollow small",
				action: function () {},
			},
			confirm:
			{
				text: "Continue",
				btnClass: "btnConfirm button primary small",
				action: function ()
				{
					url = this.$target.attr("href");
					window.open(url, "_blank");
				}
			}
		}
	}
}