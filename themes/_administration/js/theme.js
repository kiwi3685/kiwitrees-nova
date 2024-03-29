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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>
 */
/**
 * On submit validation for the import/upload GEDCOM form
 *
 */
function checkGedcomImportForm (message)
{
	var old_file = jQuery("#gedcom_filename")
		.val();
	var method = jQuery("input[name=action]:checked")
		.val();
	var new_file = method === "replace_import" ? jQuery("#import-server-file")
		.val() : jQuery("#import-computer-file")
		.val();
	// Some browsers include c:\fakepath\ in the filename.
	new_file = new_file.replace(/.*[\/\\]/, '');
	if (new_file !== old_file && old_file !== '')
	{
		return confirm(message);
	}
	else
	{
		return true;
	}
}
/**
 * Hide / show and disable inputs for add & edit gallery plugin options
 *
 */
function hide_fields ()
{
	if (jQuery("input#kiwitrees-radio")
		.is(":checked"))
	{
		jQuery("div.kiwitreesInputGroup")
			.show();
		jQuery("div.flickrInputGroup")
			.hide();
		jQuery("div.uploadsInputGroup")
			.hide();
		jQuery("select#kiwitrees")
			.prop("disabled", false);
		jQuery("input#flickr")
			.prop("disabled", true);
		jQuery("input#uploads")
			.prop("disabled", true);
	}
	else if (jQuery("input#flickr-radio")
		.is(":checked"))
	{
		jQuery("div.kiwitreesInputGroup")
			.hide();
		jQuery("div.flickrInputGroup")
			.show();
		jQuery("div.uploadsInputGroup")
			.hide();
		jQuery("select#kiwitrees")
			.prop("disabled", true);
		jQuery("input#flickr")
			.prop("disabled", false);
		jQuery("input#uploads")
			.prop("disabled", true);
	}
	else if (jQuery("input#uploads-radio")
		.is(":checked"))
	{
		jQuery("div.kiwitreesInputGroup")
			.hide();
		jQuery("div.flickrInputGroup")
			.hide();
		jQuery("div.uploadsInputGroup")
			.show();
		jQuery("select#kiwitrees")
			.prop("disabled", true);
		jQuery("input#flickr")
			.prop("disabled", true);
		jQuery("input#uploads")
			.prop("disabled", false);
	}
};
/**
 * initialisation of CKEditor 5 with basic toolbar
 *
 *
 */
function ckeditorBasic ()
{
	ClassicEditor.create(document.querySelector(".html-edit"),
		{
			licenseKey: "",
			toolbar: [
			"heading", "|",
			"alignment:left", "alignment:right", "alignment:center", "|",
			"bold", "italic", "|",
			"undo", "redo"
		],
		})
		.then(editor =>
		{
			window.editor = editor;
		});
}
/**
 * Initialisation of CKEditor 5 with standard toolbar
 * Ver 35.4.0
 * Build id: "u3ttwnnue5fa-mbpmrp5gjfnq"
 */
function ckeditorStandard ()
{
	ClassicEditor.create(document.querySelector(".html-edit"),
		{
			licenseKey: "",
			fontColor:
			{
				colors: [
					{ label: "Red", color: "#ab3334" },
					{ label: "White", color: "#faf5e8" },
					{ label: "Green", color: "#3adb76" },
					{ label: "Yellow", color: "#ffae00" },
					{ label: "Light grey", color: "#e0e0e0" },
					{ label: "Medium grey", color: "#cacaca" },
					{ label: "Dark grey", color: "#8a8a8a" },
					{ label: "Black", color: "#331919" },
					{ label: "Blue", color: "#0193b7" },
					{ label: "Pink", color: "#e09798" }
			]
			},
			fontBackgroundColor:
			{
				colors: [
					{ label: "Red", color: "#ab3334" },
					{ label: "White", color: "#faf5e8" },
					{ label: "Green", color: "#3adb76" },
					{ label: "Yellow", color: "#ffae00" },
					{ label: "Light grey", color: "#e0e0e0" },
					{ label: "Medium grey", color: "#cacaca" },
					{ label: "Dark grey", color: "#8a8a8a" },
					{ label: "Black", color: "#331919" },
					{ label: "Blue", color: "#0193b7" },
					{ label: "Pink", color: "#e09798" }
			]
			},
			fontSize:
			{
				options: [8, 10, 12, 14, "default", 18, 20, 22]
			},
			heading:
			{
				options: [
					{ model: "paragraph", title: "Paragraph", class: "ck-heading_paragraph" },
					{ model: "heading1", view: "h1", title: "Heading 1", class: "ck-heading_heading1" },
					{ model: "heading2", view: "h2", title: "Heading 2", class: "ck-heading_heading2" },
					{ model: "heading3", view: "h3", title: "Heading 3", class: "ck-heading_heading3" },
					{ model: "heading4", view: "h4", title: "Heading 4", class: "ck-heading_heading4" },
					{ model: "heading5", view: "h5", title: "Heading 5", class: "ck-heading_heading5" },
					{ model: "heading6", view: "h6", title: "Heading 6", class: "ck-heading_heading6" }
			]
			},
			toolbar:
			{
				items: [
			'sourceEditing', '|',
			'undo', 'redo', '|',
			'heading', 'alignment', '|',
			'bold', 'underline', 'italic', 'strikethrough', 'subscript', 'superscript', '|',
			'fontBackgroundColor', 'fontColor', 'fontFamily', 'fontSize', '|',
			'-', // break point
			'bulletedList', 'numberedList', 'outdent', 'indent',
			'horizontalLine', 'link', /*'imageUpload', 'imageInsert',*/ 'code', 'highlight',
			'insertTable', 'mediaEmbed', 'pageBreak', 'htmlEmbed', 'removeFormat', 'specialCharacters',
			'findAndReplace'
		],
				shouldNotGroupWhenFull: true
			},
		})
		.then(editor =>
		{
			window.editor = editor;
		});
}
/**
 * Sort items in a table
 * Used in config page of FAQ, Gallery, Pages, and Stories modules
 *
 */
function tableSort ()
{
	jQuery("#reorderTable")
		.sortable({ items: ".sortme", forceHelperSize: true, forcePlaceholderSize: true, opacity: 0.7, cursor: "move", axis: "y" });
	//-- update the order numbers after drag-n-drop sorting is complete
	jQuery("#reorderTable")
		.bind("sortupdate", function (event, ui)
		{
			jQuery("#" + jQuery(this)
					.attr("id") + " input")
				.each(function (index, value)
				{
					value.value = index + 1;
				});
		});
}
/**
 * initialisation of icon-picker
 *
 */
function iconPicker (elementID = '#menuIcon')
{
	jQuery(elementID)
		.iconpicker(
		{
			placement: "bottomRight",
			hideOnSelect: true,
			templates:
			{
				search: '<div></div>',
			},
			icons: [
			/*
			{
				title: "fas fa-0",
				searchTerms: ["0"]
			}, {
				title: "fas fa-1",
				searchTerms: ["1"]
			}, {
				title: "fas fa-2",
				searchTerms: ["2"]
			}, {
				title: "fas fa-3",
				searchTerms: ["3"]
			}, {
				title: "fas fa-4",
				searchTerms: ["4"]
			}, {
				title: "fas fa-5",
				searchTerms: ["5"]
			}, {
				title: "fas fa-6",
				searchTerms: ["6"]
			}, {
				title: "fas fa-7",
				searchTerms: ["7"]
			}, {
				title: "fas fa-8",
				searchTerms: ["8"]
			}, {
				title: "fas fa-9",
				searchTerms: ["9"]
			}, {
				title: "fas fa-a",
				searchTerms: ["a"]
			},*/
				{
					title: "fas fa-address-book",
					searchTerms: ["address", "book"]
			},
				{
					title: "fas fa-address-card",
					searchTerms: ["address", "card"]
			},
				{
					title: "fas fa-align-center",
					searchTerms: ["align-center"]
			},
				{
					title: "fas fa-align-justify",
					searchTerms: ["align-justify"]
			},
				{
					title: "fas fa-align-left",
					searchTerms: ["align-left"]
			},
				{
					title: "fas fa-align-right",
					searchTerms: ["align-right"]
			},
				{
					title: "fas fa-anchor",
					searchTerms: ["anchor"]
			},
				{
					title: "fas fa-anchor-circle-check",
					searchTerms: ["anchor", "circle-check"]
			},
				{
					title: "fas fa-anchor-circle-exclamation",
					searchTerms: ["anchor", "circle-exclamation"]
			},
				{
					title: "fas fa-anchor-circle-xmark",
					searchTerms: ["anchor", "circle-xmark"]
			},
				{
					title: "fas fa-anchor-lock",
					searchTerms: ["anchor", "lock"]
			},
				{
					title: "fas fa-angle-down",
					searchTerms: ["angle-down"]
			},
				{
					title: "fas fa-angle-left",
					searchTerms: ["angle-left"]
			},
				{
					title: "fas fa-angle-right",
					searchTerms: ["angle-right"]
			},
				{
					title: "fas fa-angle-up",
					searchTerms: ["angle-up"]
			},
				{
					title: "fas fa-angles-down",
					searchTerms: ["angles", "down"]
			},
				{
					title: "fas fa-angles-left",
					searchTerms: ["angles", "left"]
			},
				{
					title: "fas fa-angles-right",
					searchTerms: ["angles", "right"]
			},
				{
					title: "fas fa-angles-up",
					searchTerms: ["angles", "up"]
			},
				{
					title: "fas fa-ankh",
					searchTerms: ["ankh"]
			},
				{
					title: "fas fa-apple-whole",
					searchTerms: ["apple", "whole"]
			},
				{
					title: "fas fa-archway",
					searchTerms: ["archway"]
			},
				{
					title: "fas fa-arrow-down",
					searchTerms: ["arrow", "down"]
			},
				{
					title: "fas fa-arrow-down-1-9",
					searchTerms: ["arrow", "down", "1", "9"]
			},
				{
					title: "fas fa-arrow-down-9-1",
					searchTerms: ["arrow", "down", "9", "1"]
			},
				{
					title: "fas fa-arrow-down-a-z",
					searchTerms: ["arrow", "down", "a", "z"]
			},
				{
					title: "fas fa-arrow-down-long",
					searchTerms: ["arrow", "down", "long"]
			},
				{
					title: "fas fa-arrow-down-short-wide",
					searchTerms: ["arrow", "down", "short", "wide"]
			},
				{
					title: "fas fa-arrow-down-up-across-line",
					searchTerms: ["arrow", "down-up-across-line"]
			},
				{
					title: "fas fa-arrow-down-up-lock",
					searchTerms: ["arrow", "down-up-lock"]
			},
				{
					title: "fas fa-arrow-down-wide-short",
					searchTerms: ["arrow", "down", "wide", "short"]
			},
				{
					title: "fas fa-arrow-down-z-a",
					searchTerms: ["arrow", "down", "z", "a"]
			},
				{
					title: "fas fa-arrow-left",
					searchTerms: ["arrow-left"]
			},
				{
					title: "fas fa-arrow-left-long",
					searchTerms: ["arrow", "left", "long"]
			},
				{
					title: "fas fa-arrow-pointer",
					searchTerms: ["arrow", "pointer"]
			},
				{
					title: "fas fa-arrow-right",
					searchTerms: ["arrow", "right"]
			},
				{
					title: "fas fa-arrow-right-arrow-left",
					searchTerms: ["arrow", "right", "arrow", "left"]
			},
				{
					title: "fas fa-arrow-right-from-bracket",
					searchTerms: ["arrow", "right", "from", "bracket"]
			},
				{
					title: "fas fa-arrow-right-long",
					searchTerms: ["arrow", "right", "long"]
			},
				{
					title: "fas fa-arrow-right-to-bracket",
					searchTerms: ["arrow", "right", "to", "bracket"]
			},
				{
					title: "fas fa-arrow-right-to-city",
					searchTerms: ["arrow", "right-to-city"]
			},
				{
					title: "fas fa-arrow-rotate-left",
					searchTerms: ["arrow", "rotate", "left"]
			},
				{
					title: "fas fa-arrow-rotate-right",
					searchTerms: ["arrow", "rotate", "right"]
			},
				{
					title: "fas fa-arrow-trend-down",
					searchTerms: ["arrow", "trend", "down"]
			},
				{
					title: "fas fa-arrow-trend-up",
					searchTerms: ["arrow", "trend", "up"]
			},
				{
					title: "fas fa-arrow-turn-down",
					searchTerms: ["arrow", "turn", "down"]
			},
				{
					title: "fas fa-arrow-turn-up",
					searchTerms: ["arrow", "turn", "up"]
			},
				{
					title: "fas fa-arrow-up",
					searchTerms: ["arrow", "up"]
			},
				{
					title: "fas fa-arrow-up-1-9",
					searchTerms: ["arrow", "up", "1", "9"]
			},
				{
					title: "fas fa-arrow-up-9-1",
					searchTerms: ["arrow", "up", "9", "1"]
			},
				{
					title: "fas fa-arrow-up-a-z",
					searchTerms: ["arrow", "up", "a", "z"]
			},
				{
					title: "fas fa-arrow-up-from-bracket",
					searchTerms: ["arrow", "up", "from", "bracket"]
			},
				{
					title: "fas fa-arrow-up-from-ground-water",
					searchTerms: ["arrow", "up-from-ground-water"]
			},
				{
					title: "fas fa-arrow-up-from-water-pump",
					searchTerms: ["arrow", "up-from-water-pump"]
			},
				{
					title: "fas fa-arrow-up-long",
					searchTerms: ["arrow", "up", "long"]
			},
				{
					title: "fas fa-arrow-up-right-dots",
					searchTerms: ["arrow", "up-right-dots"]
			},
				{
					title: "fas fa-arrow-up-right-from-square",
					searchTerms: ["arrow", "up", "right", "from", "square"]
			},
				{
					title: "fas fa-arrow-up-short-wide",
					searchTerms: ["arrow", "up", "short", "wide"]
			},
				{
					title: "fas fa-arrow-up-wide-short",
					searchTerms: ["arrow", "up", "wide", "short"]
			},
				{
					title: "fas fa-arrow-up-z-a",
					searchTerms: ["arrow", "up", "z", "a"]
			},
				{
					title: "fas fa-arrows-down-to-line",
					searchTerms: ["arrows", "down-to-line"]
			},
				{
					title: "fas fa-arrows-down-to-people",
					searchTerms: ["arrows", "down-to-people"]
			},
				{
					title: "fas fa-arrows-left-right",
					searchTerms: ["arrows", "left", "right"]
			},
				{
					title: "fas fa-arrows-left-right-to-line",
					searchTerms: ["arrows", "left-right-to-line"]
			},
				{
					title: "fas fa-arrows-rotate",
					searchTerms: ["arrows", "rotate"]
			},
				{
					title: "fas fa-arrows-spin",
					searchTerms: ["arrows", "spin"]
			},
				{
					title: "fas fa-arrows-split-up-and-left",
					searchTerms: ["arrows", "split-up-and-left"]
			},
				{
					title: "fas fa-arrows-to-circle",
					searchTerms: ["arrows", "to-circle"]
			},
				{
					title: "fas fa-arrows-to-dot",
					searchTerms: ["arrows", "to-dot"]
			},
				{
					title: "fas fa-arrows-to-eye",
					searchTerms: ["arrows", "to-eye"]
			},
				{
					title: "fas fa-arrows-turn-right",
					searchTerms: ["arrows", "turn-right"]
			},
				{
					title: "fas fa-arrows-turn-to-dots",
					searchTerms: ["arrows", "turn-to-dots"]
			},
				{
					title: "fas fa-arrows-up-down",
					searchTerms: ["arrows", "up", "down"]
			},
				{
					title: "fas fa-arrows-up-down-left-right",
					searchTerms: ["arrows", "up", "down", "left", "right"]
			},
				{
					title: "fas fa-arrows-up-to-line",
					searchTerms: ["arrows", "up-to-line"]
			},
				{
					title: "fas fa-asterisk",
					searchTerms: ["asterisk"]
			},
				{
					title: "fas fa-at",
					searchTerms: ["at"]
			},
				{
					title: "fas fa-atom",
					searchTerms: ["atom"]
			},
				{
					title: "fas fa-audio-description",
					searchTerms: ["rectangle", "audio", "description"]
			},
				{
					title: "fas fa-austral-sign",
					searchTerms: ["austral", "sign"]
			},
				{
					title: "fas fa-award",
					searchTerms: ["award"]
			},
				{
					title: "fas fa-b",
					searchTerms: ["b"]
			},
				{
					title: "fas fa-baby",
					searchTerms: ["baby"]
			},
				{
					title: "fas fa-baby-carriage",
					searchTerms: ["baby", "carriage"]
			},
				{
					title: "fas fa-backward",
					searchTerms: ["backward"]
			},
				{
					title: "fas fa-backward-fast",
					searchTerms: ["backward", "fast"]
			},
				{
					title: "fas fa-backward-step",
					searchTerms: ["backward", "step"]
			},
				{
					title: "fas fa-bacon",
					searchTerms: ["bacon"]
			},
				{
					title: "fas fa-bacteria",
					searchTerms: ["bacteria"]
			},
				{
					title: "fas fa-bacterium",
					searchTerms: ["bacterium"]
			},
				{
					title: "fas fa-bag-shopping",
					searchTerms: ["bag", "shopping"]
			},
				{
					title: "fas fa-bahai",
					searchTerms: ["bahá'í"]
			},
				{
					title: "fas fa-baht-sign",
					searchTerms: ["baht", "sign"]
			},
				{
					title: "fas fa-ban",
					searchTerms: ["ban"]
			},
				{
					title: "fas fa-ban-smoking",
					searchTerms: ["ban", "smoking"]
			},
				{
					title: "fas fa-bandage",
					searchTerms: ["bandage"]
			},
				{
					title: "fas fa-bangladeshi-taka-sign",
					searchTerms: ["bangladeshi", "taka", "sign"]
			},
				{
					title: "fas fa-barcode",
					searchTerms: ["barcode"]
			},
				{
					title: "fas fa-bars",
					searchTerms: ["bars"]
			},
				{
					title: "fas fa-bars-progress",
					searchTerms: ["bars", "progress"]
			},
				{
					title: "fas fa-bars-staggered",
					searchTerms: ["bars", "staggered"]
			},
				{
					title: "fas fa-baseball",
					searchTerms: ["baseball", "ball"]
			},
				{
					title: "fas fa-baseball-bat-ball",
					searchTerms: ["baseball", "bat", "ball"]
			},
				{
					title: "fas fa-basket-shopping",
					searchTerms: ["basket", "shopping"]
			},
				{
					title: "fas fa-basketball",
					searchTerms: ["basketball", "ball"]
			},
				{
					title: "fas fa-bath",
					searchTerms: ["bath"]
			},
				{
					title: "fas fa-battery-empty",
					searchTerms: ["battery", "empty"]
			},
				{
					title: "fas fa-battery-full",
					searchTerms: ["battery", "full"]
			},
				{
					title: "fas fa-battery-half",
					searchTerms: ["battery", "1/2", "full"]
			},
				{
					title: "fas fa-battery-quarter",
					searchTerms: ["battery", "1/4", "full"]
			},
				{
					title: "fas fa-battery-three-quarters",
					searchTerms: ["battery", "3/4", "full"]
			},
				{
					title: "fas fa-bed",
					searchTerms: ["bed"]
			},
				{
					title: "fas fa-bed-pulse",
					searchTerms: ["bed", "pulse"]
			},
				{
					title: "fas fa-beer-mug-empty",
					searchTerms: ["beer", "mug", "empty"]
			},
				{
					title: "fas fa-bell",
					searchTerms: ["bell"]
			},
				{
					title: "fas fa-bell-concierge",
					searchTerms: ["bell", "concierge"]
			},
				{
					title: "fas fa-bell-slash",
					searchTerms: ["bell", "slash"]
			},
				{
					title: "fas fa-bezier-curve",
					searchTerms: ["bezier", "curve"]
			},
				{
					title: "fas fa-bicycle",
					searchTerms: ["bicycle"]
			},
				{
					title: "fas fa-binoculars",
					searchTerms: ["binoculars"]
			},
				{
					title: "fas fa-biohazard",
					searchTerms: ["biohazard"]
			},
				{
					title: "fas fa-bitcoin-sign",
					searchTerms: ["bitcoin", "sign"]
			},
				{
					title: "fas fa-blender",
					searchTerms: ["blender"]
			},
				{
					title: "fas fa-blender-phone",
					searchTerms: ["blender", "phone"]
			},
				{
					title: "fas fa-blog",
					searchTerms: ["blog"]
			},
				{
					title: "fas fa-bold",
					searchTerms: ["bold"]
			},
				{
					title: "fas fa-bolt",
					searchTerms: ["bolt"]
			},
				{
					title: "fas fa-bolt-lightning",
					searchTerms: ["lightning", "bolt"]
			},
				{
					title: "fas fa-bomb",
					searchTerms: ["bomb"]
			},
				{
					title: "fas fa-bone",
					searchTerms: ["bone"]
			},
				{
					title: "fas fa-bong",
					searchTerms: ["bong"]
			},
				{
					title: "fas fa-book",
					searchTerms: ["book"]
			},
				{
					title: "fas fa-book-atlas",
					searchTerms: ["book", "atlas"]
			},
				{
					title: "fas fa-book-bible",
					searchTerms: ["book", "bible"]
			},
				{
					title: "fas fa-book-bookmark",
					searchTerms: ["book", "bookmark"]
			},
				{
					title: "fas fa-book-journal-whills",
					searchTerms: ["book", "journal", "whills"]
			},
				{
					title: "fas fa-book-medical",
					searchTerms: ["medical", "book"]
			},
				{
					title: "fas fa-book-open",
					searchTerms: ["book", "open"]
			},
				{
					title: "fas fa-book-open-reader",
					searchTerms: ["book", "open", "reader"]
			},
				{
					title: "fas fa-book-quran",
					searchTerms: ["book", "quran"]
			},
				{
					title: "fas fa-book-skull",
					searchTerms: ["book", "skull"]
			},
				{
					title: "fas fa-book-tanakh",
					searchTerms: ["book", "tanakh"]
			},
				{
					title: "fas fa-bookmark",
					searchTerms: ["bookmark"]
			},
				{
					title: "fas fa-border-all",
					searchTerms: ["border", "all"]
			},
				{
					title: "fas fa-border-none",
					searchTerms: ["border", "none"]
			},
				{
					title: "fas fa-border-top-left",
					searchTerms: ["border", "top", "left"]
			},
				{
					title: "fas fa-bore-hole",
					searchTerms: ["bore", "hole"]
			},
				{
					title: "fas fa-bottle-droplet",
					searchTerms: ["bottle", "droplet"]
			},
				{
					title: "fas fa-bottle-water",
					searchTerms: ["bottle", "water"]
			},
				{
					title: "fas fa-bowl-food",
					searchTerms: ["bowl", "food"]
			},
				{
					title: "fas fa-bowl-rice",
					searchTerms: ["bowl", "rice"]
			},
				{
					title: "fas fa-bowling-ball",
					searchTerms: ["bowling", "ball"]
			},
				{
					title: "fas fa-box",
					searchTerms: ["box"]
			},
				{
					title: "fas fa-box-archive",
					searchTerms: ["box", "archive"]
			},
				{
					title: "fas fa-box-open",
					searchTerms: ["box", "open"]
			},
				{
					title: "fas fa-box-tissue",
					searchTerms: ["tissue", "box"]
			},
				{
					title: "fas fa-boxes-packing",
					searchTerms: ["boxes", "packing"]
			},
				{
					title: "fas fa-boxes-stacked",
					searchTerms: ["boxes", "stacked"]
			},
				{
					title: "fas fa-braille",
					searchTerms: ["braille"]
			},
				{
					title: "fas fa-brain",
					searchTerms: ["brain"]
			},
				{
					title: "fas fa-brazilian-real-sign",
					searchTerms: ["brazilian", "real", "sign"]
			},
				{
					title: "fas fa-bread-slice",
					searchTerms: ["bread", "slice"]
			},
				{
					title: "fas fa-bridge",
					searchTerms: ["bridge"]
			},
				{
					title: "fas fa-bridge-circle-check",
					searchTerms: ["bridge", "circle-check"]
			},
				{
					title: "fas fa-bridge-circle-exclamation",
					searchTerms: ["bridge", "circle-exclamation"]
			},
				{
					title: "fas fa-bridge-circle-xmark",
					searchTerms: ["bridge", "circle-xmark"]
			},
				{
					title: "fas fa-bridge-lock",
					searchTerms: ["bridge", "lock"]
			},
				{
					title: "fas fa-bridge-water",
					searchTerms: ["bridge", "water"]
			},
				{
					title: "fas fa-briefcase",
					searchTerms: ["briefcase"]
			},
				{
					title: "fas fa-briefcase-medical",
					searchTerms: ["medical", "briefcase"]
			},
				{
					title: "fas fa-broom",
					searchTerms: ["broom"]
			},
				{
					title: "fas fa-broom-ball",
					searchTerms: ["broom", "and", "ball"]
			},
				{
					title: "fas fa-brush",
					searchTerms: ["brush"]
			},
				{
					title: "fas fa-bucket",
					searchTerms: ["bucket"]
			},
				{
					title: "fas fa-bug",
					searchTerms: ["bug"]
			},
				{
					title: "fas fa-bug-slash",
					searchTerms: ["bug", "slash"]
			},
				{
					title: "fas fa-bugs",
					searchTerms: ["bugs"]
			},
				{
					title: "fas fa-building",
					searchTerms: ["building"]
			},
				{
					title: "fas fa-building-circle-arrow-right",
					searchTerms: ["building", "circle-arrow-right"]
			},
				{
					title: "fas fa-building-circle-check",
					searchTerms: ["building", "circle-check"]
			},
				{
					title: "fas fa-building-circle-exclamation",
					searchTerms: ["building", "circle-exclamation"]
			},
				{
					title: "fas fa-building-circle-xmark",
					searchTerms: ["building", "circle-xmark"]
			},
				{
					title: "fas fa-building-columns",
					searchTerms: ["building", "with", "columns"]
			},
				{
					title: "fas fa-building-flag",
					searchTerms: ["building", "flag"]
			},
				{
					title: "fas fa-building-lock",
					searchTerms: ["building", "lock"]
			},
				{
					title: "fas fa-building-ngo",
					searchTerms: ["building", "ngo"]
			},
				{
					title: "fas fa-building-shield",
					searchTerms: ["building", "shield"]
			},
				{
					title: "fas fa-building-un",
					searchTerms: ["building", "un"]
			},
				{
					title: "fas fa-building-user",
					searchTerms: ["building", "user"]
			},
				{
					title: "fas fa-building-wheat",
					searchTerms: ["building", "wheat"]
			},
				{
					title: "fas fa-bullhorn",
					searchTerms: ["bullhorn"]
			},
				{
					title: "fas fa-bullseye",
					searchTerms: ["bullseye"]
			},
				{
					title: "fas fa-burger",
					searchTerms: ["burger"]
			},
				{
					title: "fas fa-burst",
					searchTerms: ["burst"]
			},
				{
					title: "fas fa-bus",
					searchTerms: ["bus"]
			},
				{
					title: "fas fa-bus-simple",
					searchTerms: ["bus", "simple"]
			},
				{
					title: "fas fa-business-time",
					searchTerms: ["briefcase", "clock"]
			},
				{
					title: "fas fa-c",
					searchTerms: ["c"]
			},
				{
					title: "fas fa-cable-car",
					searchTerms: ["cable", "car"]
			},
				{
					title: "fas fa-cake-candles",
					searchTerms: ["cake", "candles"]
			},
				{
					title: "fas fa-calculator",
					searchTerms: ["calculator"]
			},
				{
					title: "fas fa-calendar",
					searchTerms: ["calendar"]
			},
				{
					title: "fas fa-calendar-check",
					searchTerms: ["calendar", "check"]
			},
				{
					title: "fas fa-calendar-day",
					searchTerms: ["calendar", "with", "day", "focus"]
			},
				{
					title: "fas fa-calendar-days",
					searchTerms: ["calendar", "days"]
			},
				{
					title: "fas fa-calendar-minus",
					searchTerms: ["calendar", "minus"]
			},
				{
					title: "fas fa-calendar-plus",
					searchTerms: ["calendar", "plus"]
			},
				{
					title: "fas fa-calendar-week",
					searchTerms: ["calendar", "with", "week", "focus"]
			},
				{
					title: "fas fa-calendar-xmark",
					searchTerms: ["calendar", "x", "mark"]
			},
				{
					title: "fas fa-camera",
					searchTerms: ["camera"]
			},
				{
					title: "fas fa-camera-retro",
					searchTerms: ["retro", "camera"]
			},
				{
					title: "fas fa-camera-rotate",
					searchTerms: ["camera", "rotate"]
			},
				{
					title: "fas fa-campground",
					searchTerms: ["campground"]
			},
				{
					title: "fas fa-candy-cane",
					searchTerms: ["candy", "cane"]
			},
				{
					title: "fas fa-cannabis",
					searchTerms: ["cannabis"]
			},
				{
					title: "fas fa-capsules",
					searchTerms: ["capsules"]
			},
				{
					title: "fas fa-car",
					searchTerms: ["car"]
			},
				{
					title: "fas fa-car-battery",
					searchTerms: ["car", "battery"]
			},
				{
					title: "fas fa-car-burst",
					searchTerms: ["car", "crash"]
			},
				{
					title: "fas fa-car-on",
					searchTerms: ["car", "on"]
			},
				{
					title: "fas fa-car-rear",
					searchTerms: ["car", "rear"]
			},
				{
					title: "fas fa-car-side",
					searchTerms: ["car", "side"]
			},
				{
					title: "fas fa-car-tunnel",
					searchTerms: ["car", "tunnel"]
			},
				{
					title: "fas fa-caravan",
					searchTerms: ["caravan"]
			},
				{
					title: "fas fa-caret-down",
					searchTerms: ["caret", "down"]
			},
				{
					title: "fas fa-caret-left",
					searchTerms: ["caret", "left"]
			},
				{
					title: "fas fa-caret-right",
					searchTerms: ["caret", "right"]
			},
				{
					title: "fas fa-caret-up",
					searchTerms: ["caret", "up"]
			},
				{
					title: "fas fa-carrot",
					searchTerms: ["carrot"]
			},
				{
					title: "fas fa-cart-arrow-down",
					searchTerms: ["cart", "arrow", "down"]
			},
				{
					title: "fas fa-cart-flatbed",
					searchTerms: ["cart", "flatbed"]
			},
				{
					title: "fas fa-cart-flatbed-suitcase",
					searchTerms: ["cart", "flatbed", "suitcase"]
			},
				{
					title: "fas fa-cart-plus",
					searchTerms: ["cart", "plus"]
			},
				{
					title: "fas fa-cart-shopping",
					searchTerms: ["cart", "shopping"]
			},
				{
					title: "fas fa-cash-register",
					searchTerms: ["cash", "register"]
			},
				{
					title: "fas fa-cat",
					searchTerms: ["cat"]
			},
				{
					title: "fas fa-cedi-sign",
					searchTerms: ["cedi", "sign"]
			},
				{
					title: "fas fa-cent-sign",
					searchTerms: ["cent", "sign"]
			},
				{
					title: "fas fa-certificate",
					searchTerms: ["certificate"]
			},
				{
					title: "fas fa-chair",
					searchTerms: ["chair"]
			},
				{
					title: "fas fa-chalkboard",
					searchTerms: ["chalkboard"]
			},
				{
					title: "fas fa-chalkboard-user",
					searchTerms: ["chalkboard", "user"]
			},
				{
					title: "fas fa-champagne-glasses",
					searchTerms: ["champagne", "glasses"]
			},
				{
					title: "fas fa-charging-station",
					searchTerms: ["charging", "station"]
			},
				{
					title: "fas fa-chart-area",
					searchTerms: ["area", "chart"]
			},
				{
					title: "fas fa-chart-bar",
					searchTerms: ["bar", "chart"]
			},
				{
					title: "fas fa-chart-column",
					searchTerms: ["chart", "column"]
			},
				{
					title: "fas fa-chart-gantt",
					searchTerms: ["chart", "gantt"]
			},
				{
					title: "fas fa-chart-line",
					searchTerms: ["line", "chart"]
			},
				{
					title: "fas fa-chart-pie",
					searchTerms: ["pie", "chart"]
			},
				{
					title: "fas fa-chart-simple",
					searchTerms: ["chart", "simple"]
			},
				{
					title: "fas fa-check",
					searchTerms: ["check"]
			},
				{
					title: "fas fa-check-double",
					searchTerms: ["double", "check"]
			},
				{
					title: "fas fa-check-to-slot",
					searchTerms: ["check", "to", "slot"]
			},
				{
					title: "fas fa-cheese",
					searchTerms: ["cheese"]
			},
				{
					title: "fas fa-chess",
					searchTerms: ["chess"]
			},
				{
					title: "fas fa-chess-bishop",
					searchTerms: ["chess", "bishop"]
			},
				{
					title: "fas fa-chess-board",
					searchTerms: ["chess", "board"]
			},
				{
					title: "fas fa-chess-king",
					searchTerms: ["chess", "king"]
			},
				{
					title: "fas fa-chess-knight",
					searchTerms: ["chess", "knight"]
			},
				{
					title: "fas fa-chess-pawn",
					searchTerms: ["chess", "pawn"]
			},
				{
					title: "fas fa-chess-queen",
					searchTerms: ["chess", "queen"]
			},
				{
					title: "fas fa-chess-rook",
					searchTerms: ["chess", "rook"]
			},
				{
					title: "fas fa-chevron-down",
					searchTerms: ["chevron-down"]
			},
				{
					title: "fas fa-chevron-left",
					searchTerms: ["chevron-left"]
			},
				{
					title: "fas fa-chevron-right",
					searchTerms: ["chevron-right"]
			},
				{
					title: "fas fa-chevron-up",
					searchTerms: ["chevron-up"]
			},
				{
					title: "fas fa-child",
					searchTerms: ["child"]
			},
				{
					title: "fas fa-child-combatant",
					searchTerms: ["child", "combatant"]
			},
				{
					title: "fas fa-child-dress",
					searchTerms: ["child", "dress"]
			},
				{
					title: "fas fa-child-reaching",
					searchTerms: ["child", "reaching"]
			},
				{
					title: "fas fa-children",
					searchTerms: ["children"]
			},
				{
					title: "fas fa-church",
					searchTerms: ["church"]
			},
				{
					title: "fas fa-circle",
					searchTerms: ["circle"]
			},
				{
					title: "fas fa-circle-arrow-down",
					searchTerms: ["circle", "arrow", "down"]
			},
				{
					title: "fas fa-circle-arrow-left",
					searchTerms: ["circle", "arrow", "left"]
			},
				{
					title: "fas fa-circle-arrow-right",
					searchTerms: ["circle", "arrow", "right"]
			},
				{
					title: "fas fa-circle-arrow-up",
					searchTerms: ["circle", "arrow", "up"]
			},
				{
					title: "fas fa-circle-check",
					searchTerms: ["circle", "check"]
			},
				{
					title: "fas fa-circle-chevron-down",
					searchTerms: ["circle", "chevron", "down"]
			},
				{
					title: "fas fa-circle-chevron-left",
					searchTerms: ["circle", "chevron", "left"]
			},
				{
					title: "fas fa-circle-chevron-right",
					searchTerms: ["circle", "chevron", "right"]
			},
				{
					title: "fas fa-circle-chevron-up",
					searchTerms: ["circle", "chevron", "up"]
			},
				{
					title: "fas fa-circle-dollar-to-slot",
					searchTerms: ["circle", "dollar", "to", "slot"]
			},
				{
					title: "fas fa-circle-dot",
					searchTerms: ["circle", "dot"]
			},
				{
					title: "fas fa-circle-down",
					searchTerms: ["circle", "down"]
			},
				{
					title: "fas fa-circle-exclamation",
					searchTerms: ["circle", "exclamation"]
			},
				{
					title: "fas fa-circle-h",
					searchTerms: ["circle", "h"]
			},
				{
					title: "fas fa-circle-half-stroke",
					searchTerms: ["circle", "half", "stroke"]
			},
				{
					title: "fas fa-circle-info",
					searchTerms: ["circle", "info"]
			},
				{
					title: "fas fa-circle-left",
					searchTerms: ["circle", "left"]
			},
				{
					title: "fas fa-circle-minus",
					searchTerms: ["circle", "minus"]
			},
				{
					title: "fas fa-circle-nodes",
					searchTerms: ["circle", "nodes"]
			},
				{
					title: "fas fa-circle-notch",
					searchTerms: ["circle", "notched"]
			},
				{
					title: "fas fa-circle-pause",
					searchTerms: ["circle", "pause"]
			},
				{
					title: "fas fa-circle-play",
					searchTerms: ["circle", "play"]
			},
				{
					title: "fas fa-circle-plus",
					searchTerms: ["circle", "plus"]
			},
				{
					title: "fas fa-circle-question",
					searchTerms: ["circle", "question"]
			},
				{
					title: "fas fa-circle-radiation",
					searchTerms: ["circle", "radiation"]
			},
				{
					title: "fas fa-circle-right",
					searchTerms: ["circle", "right"]
			},
				{
					title: "fas fa-circle-stop",
					searchTerms: ["circle", "stop"]
			},
				{
					title: "fas fa-circle-up",
					searchTerms: ["circle", "up"]
			},
				{
					title: "fas fa-circle-user",
					searchTerms: ["circle", "user"]
			},
				{
					title: "fas fa-circle-xmark",
					searchTerms: ["circle", "x", "mark"]
			},
				{
					title: "fas fa-city",
					searchTerms: ["city"]
			},
				{
					title: "fas fa-clapperboard",
					searchTerms: ["clapperboard"]
			},
				{
					title: "fas fa-clipboard",
					searchTerms: ["clipboard"]
			},
				{
					title: "fas fa-clipboard-check",
					searchTerms: ["clipboard", "check"]
			},
				{
					title: "fas fa-clipboard-list",
					searchTerms: ["clipboard", "list"]
			},
				{
					title: "fas fa-clipboard-question",
					searchTerms: ["clipboard", "question"]
			},
				{
					title: "fas fa-clipboard-user",
					searchTerms: ["clipboard", "user"]
			},
				{
					title: "fas fa-clock",
					searchTerms: ["clock"]
			},
				{
					title: "fas fa-clock-rotate-left",
					searchTerms: ["clock", "rotate", "left"]
			},
				{
					title: "fas fa-clone",
					searchTerms: ["clone"]
			},
				{
					title: "fas fa-closed-captioning",
					searchTerms: ["closed", "captioning"]
			},
				{
					title: "fas fa-cloud",
					searchTerms: ["cloud"]
			},
				{
					title: "fas fa-cloud-arrow-down",
					searchTerms: ["cloud", "arrow", "down"]
			},
				{
					title: "fas fa-cloud-arrow-up",
					searchTerms: ["cloud", "arrow", "up"]
			},
				{
					title: "fas fa-cloud-bolt",
					searchTerms: ["cloud", "bolt"]
			},
				{
					title: "fas fa-cloud-meatball",
					searchTerms: ["cloud", "with", "(a", "chance", "of)", "meatball"]
			},
				{
					title: "fas fa-cloud-moon",
					searchTerms: ["cloud", "with", "moon"]
			},
				{
					title: "fas fa-cloud-moon-rain",
					searchTerms: ["cloud", "with", "moon", "and", "rain"]
			},
				{
					title: "fas fa-cloud-rain",
					searchTerms: ["cloud", "with", "rain"]
			},
				{
					title: "fas fa-cloud-showers-heavy",
					searchTerms: ["cloud", "with", "heavy", "showers"]
			},
				{
					title: "fas fa-cloud-showers-water",
					searchTerms: ["cloud", "showers-water"]
			},
				{
					title: "fas fa-cloud-sun",
					searchTerms: ["cloud", "with", "sun"]
			},
				{
					title: "fas fa-cloud-sun-rain",
					searchTerms: ["cloud", "with", "sun", "and", "rain"]
			},
				{
					title: "fas fa-clover",
					searchTerms: ["clover"]
			},
				{
					title: "fas fa-code",
					searchTerms: ["code"]
			},
				{
					title: "fas fa-code-branch",
					searchTerms: ["code", "branch"]
			},
				{
					title: "fas fa-code-commit",
					searchTerms: ["code", "commit"]
			},
				{
					title: "fas fa-code-compare",
					searchTerms: ["code", "compare"]
			},
				{
					title: "fas fa-code-fork",
					searchTerms: ["code", "fork"]
			},
				{
					title: "fas fa-code-merge",
					searchTerms: ["code", "merge"]
			},
				{
					title: "fas fa-code-pull-request",
					searchTerms: ["code", "pull", "request"]
			},
				{
					title: "fas fa-coins",
					searchTerms: ["coins"]
			},
				{
					title: "fas fa-colon-sign",
					searchTerms: ["colon", "sign"]
			},
				{
					title: "fas fa-comment",
					searchTerms: ["comment"]
			},
				{
					title: "fas fa-comment-dollar",
					searchTerms: ["comment", "dollar"]
			},
				{
					title: "fas fa-comment-dots",
					searchTerms: ["comment", "dots"]
			},
				{
					title: "fas fa-comment-medical",
					searchTerms: ["alternate", "medical", "chat"]
			},
				{
					title: "fas fa-comment-slash",
					searchTerms: ["comment", "slash"]
			},
				{
					title: "fas fa-comment-sms",
					searchTerms: ["comment", "sms"]
			},
				{
					title: "fas fa-comments",
					searchTerms: ["comments"]
			},
				{
					title: "fas fa-comments-dollar",
					searchTerms: ["comments", "dollar"]
			},
				{
					title: "fas fa-compact-disc",
					searchTerms: ["compact", "disc"]
			},
				{
					title: "fas fa-compass",
					searchTerms: ["compass"]
			},
				{
					title: "fas fa-compass-drafting",
					searchTerms: ["compass", "drafting"]
			},
				{
					title: "fas fa-compress",
					searchTerms: ["compress"]
			},
				{
					title: "fas fa-computer",
					searchTerms: ["computer"]
			},
				{
					title: "fas fa-computer-mouse",
					searchTerms: ["computer", "mouse"]
			},
				{
					title: "fas fa-cookie",
					searchTerms: ["cookie"]
			},
				{
					title: "fas fa-cookie-bite",
					searchTerms: ["cookie", "bite"]
			},
				{
					title: "fas fa-copy",
					searchTerms: ["copy"]
			},
				{
					title: "fas fa-copyright",
					searchTerms: ["copyright"]
			},
				{
					title: "fas fa-couch",
					searchTerms: ["couch"]
			},
				{
					title: "fas fa-cow",
					searchTerms: ["cow"]
			},
				{
					title: "fas fa-credit-card",
					searchTerms: ["credit", "card"]
			},
				{
					title: "fas fa-crop",
					searchTerms: ["crop"]
			},
				{
					title: "fas fa-crop-simple",
					searchTerms: ["crop", "simple"]
			},
				{
					title: "fas fa-cross",
					searchTerms: ["cross"]
			},
				{
					title: "fas fa-crosshairs",
					searchTerms: ["crosshairs"]
			},
				{
					title: "fas fa-crow",
					searchTerms: ["crow"]
			},
				{
					title: "fas fa-crown",
					searchTerms: ["crown"]
			},
				{
					title: "fas fa-crutch",
					searchTerms: ["crutch"]
			},
				{
					title: "fas fa-cruzeiro-sign",
					searchTerms: ["cruzeiro", "sign"]
			},
				{
					title: "fas fa-cube",
					searchTerms: ["cube"]
			},
				{
					title: "fas fa-cubes",
					searchTerms: ["cubes"]
			},
				{
					title: "fas fa-cubes-stacked",
					searchTerms: ["cubes", "stacked"]
			},
				{
					title: "fas fa-d",
					searchTerms: ["d"]
			},
				{
					title: "fas fa-database",
					searchTerms: ["database"]
			},
				{
					title: "fas fa-delete-left",
					searchTerms: ["delete", "left"]
			},
				{
					title: "fas fa-democrat",
					searchTerms: ["democrat"]
			},
				{
					title: "fas fa-desktop",
					searchTerms: ["desktop"]
			},
				{
					title: "fas fa-dharmachakra",
					searchTerms: ["dharmachakra"]
			},
				{
					title: "fas fa-diagram-next",
					searchTerms: ["diagram", "next"]
			},
				{
					title: "fas fa-diagram-predecessor",
					searchTerms: ["diagram", "predecessor"]
			},
				{
					title: "fas fa-diagram-project",
					searchTerms: ["project", "diagram"]
			},
				{
					title: "fas fa-diagram-successor",
					searchTerms: ["diagram", "successor"]
			},
				{
					title: "fas fa-diamond",
					searchTerms: ["diamond"]
			},
				{
					title: "fas fa-diamond-turn-right",
					searchTerms: ["diamond", "turn", "right"]
			},
				{
					title: "fas fa-dice",
					searchTerms: ["dice"]
			},
				{
					title: "fas fa-dice-d20",
					searchTerms: ["dice", "d20"]
			},
				{
					title: "fas fa-dice-d6",
					searchTerms: ["dice", "d6"]
			},
				{
					title: "fas fa-dice-five",
					searchTerms: ["dice", "five"]
			},
				{
					title: "fas fa-dice-four",
					searchTerms: ["dice", "four"]
			},
				{
					title: "fas fa-dice-one",
					searchTerms: ["dice", "one"]
			},
				{
					title: "fas fa-dice-six",
					searchTerms: ["dice", "six"]
			},
				{
					title: "fas fa-dice-three",
					searchTerms: ["dice", "three"]
			},
				{
					title: "fas fa-dice-two",
					searchTerms: ["dice", "two"]
			},
				{
					title: "fas fa-disease",
					searchTerms: ["disease"]
			},
				{
					title: "fas fa-display",
					searchTerms: ["display"]
			},
				{
					title: "fas fa-divide",
					searchTerms: ["divide"]
			},
				{
					title: "fas fa-dna",
					searchTerms: ["dna"]
			},
				{
					title: "fas fa-dog",
					searchTerms: ["dog"]
			},
				{
					title: "fas fa-dollar-sign",
					searchTerms: ["dollar", "sign"]
			},
				{
					title: "fas fa-dolly",
					searchTerms: ["dolly"]
			},
				{
					title: "fas fa-dong-sign",
					searchTerms: ["dong", "sign"]
			},
				{
					title: "fas fa-door-closed",
					searchTerms: ["door", "closed"]
			},
				{
					title: "fas fa-door-open",
					searchTerms: ["door", "open"]
			},
				{
					title: "fas fa-dove",
					searchTerms: ["dove"]
			},
				{
					title: "fas fa-down-left-and-up-right-to-center",
					searchTerms: ["down", "left", "and", "up", "right", "to", "center"]
			},
				{
					title: "fas fa-down-long",
					searchTerms: ["down", "long"]
			},
				{
					title: "fas fa-download",
					searchTerms: ["download"]
			},
				{
					title: "fas fa-dragon",
					searchTerms: ["dragon"]
			},
				{
					title: "fas fa-draw-polygon",
					searchTerms: ["draw", "polygon"]
			},
				{
					title: "fas fa-droplet",
					searchTerms: ["droplet"]
			},
				{
					title: "fas fa-droplet-slash",
					searchTerms: ["droplet", "slash"]
			},
				{
					title: "fas fa-drum",
					searchTerms: ["drum"]
			},
				{
					title: "fas fa-drum-steelpan",
					searchTerms: ["drum", "steelpan"]
			},
				{
					title: "fas fa-drumstick-bite",
					searchTerms: ["drumstick", "with", "bite", "taken", "out"]
			},
				{
					title: "fas fa-dumbbell",
					searchTerms: ["dumbbell"]
			},
				{
					title: "fas fa-dumpster",
					searchTerms: ["dumpster"]
			},
				{
					title: "fas fa-dumpster-fire",
					searchTerms: ["dumpster", "fire"]
			},
				{
					title: "fas fa-dungeon",
					searchTerms: ["dungeon"]
			},
				{
					title: "fas fa-e",
					searchTerms: ["e"]
			},
				{
					title: "fas fa-ear-deaf",
					searchTerms: ["ear", "deaf"]
			},
				{
					title: "fas fa-ear-listen",
					searchTerms: ["ear", "listen"]
			},
				{
					title: "fas fa-earth-africa",
					searchTerms: ["earth", "africa"]
			},
				{
					title: "fas fa-earth-americas",
					searchTerms: ["earth", "americas"]
			},
				{
					title: "fas fa-earth-asia",
					searchTerms: ["earth", "asia"]
			},
				{
					title: "fas fa-earth-europe",
					searchTerms: ["earth", "europe"]
			},
				{
					title: "fas fa-earth-oceania",
					searchTerms: ["earth", "oceania"]
			},
				{
					title: "fas fa-egg",
					searchTerms: ["egg"]
			},
				{
					title: "fas fa-eject",
					searchTerms: ["eject"]
			},
				{
					title: "fas fa-elevator",
					searchTerms: ["elevator"]
			},
				{
					title: "fas fa-ellipsis",
					searchTerms: ["ellipsis"]
			},
				{
					title: "fas fa-ellipsis-vertical",
					searchTerms: ["ellipsis", "vertical"]
			},
				{
					title: "fas fa-envelope",
					searchTerms: ["envelope"]
			},
				{
					title: "fas fa-envelope-circle-check",
					searchTerms: ["envelope", "circle", "check"]
			},
				{
					title: "fas fa-envelope-open",
					searchTerms: ["envelope", "open"]
			},
				{
					title: "fas fa-envelope-open-text",
					searchTerms: ["envelope", "open", "text"]
			},
				{
					title: "fas fa-envelopes-bulk",
					searchTerms: ["envelopes", "bulk"]
			},
				{
					title: "fas fa-equals",
					searchTerms: ["equals"]
			},
				{
					title: "fas fa-eraser",
					searchTerms: ["eraser"]
			},
				{
					title: "fas fa-ethernet",
					searchTerms: ["ethernet"]
			},
				{
					title: "fas fa-euro-sign",
					searchTerms: ["euro", "sign"]
			},
				{
					title: "fas fa-exclamation",
					searchTerms: ["exclamation"]
			},
				{
					title: "fas fa-expand",
					searchTerms: ["expand"]
			},
				{
					title: "fas fa-explosion",
					searchTerms: ["explosion"]
			},
				{
					title: "fas fa-eye",
					searchTerms: ["eye"]
			},
				{
					title: "fas fa-eye-dropper",
					searchTerms: ["eye", "dropper"]
			},
				{
					title: "fas fa-eye-low-vision",
					searchTerms: ["eye", "low", "vision"]
			},
				{
					title: "fas fa-eye-slash",
					searchTerms: ["eye", "slash"]
			},
				{
					title: "fas fa-f",
					searchTerms: ["f"]
			},
				{
					title: "fas fa-face-angry",
					searchTerms: ["face", "angry"]
			},
				{
					title: "fas fa-face-dizzy",
					searchTerms: ["face", "dizzy"]
			},
				{
					title: "fas fa-face-flushed",
					searchTerms: ["face", "flushed"]
			},
				{
					title: "fas fa-face-frown",
					searchTerms: ["face", "frown"]
			},
				{
					title: "fas fa-face-frown-open",
					searchTerms: ["face", "frown", "open"]
			},
				{
					title: "fas fa-face-grimace",
					searchTerms: ["face", "grimace"]
			},
				{
					title: "fas fa-face-grin",
					searchTerms: ["face", "grin"]
			},
				{
					title: "fas fa-face-grin-beam",
					searchTerms: ["face", "grin", "beam"]
			},
				{
					title: "fas fa-face-grin-beam-sweat",
					searchTerms: ["face", "grin", "beam", "sweat"]
			},
				{
					title: "fas fa-face-grin-hearts",
					searchTerms: ["face", "grin", "hearts"]
			},
				{
					title: "fas fa-face-grin-squint",
					searchTerms: ["face", "grin", "squint"]
			},
				{
					title: "fas fa-face-grin-squint-tears",
					searchTerms: ["face", "grin", "squint", "tears"]
			},
				{
					title: "fas fa-face-grin-stars",
					searchTerms: ["face", "grin", "stars"]
			},
				{
					title: "fas fa-face-grin-tears",
					searchTerms: ["face", "grin", "tears"]
			},
				{
					title: "fas fa-face-grin-tongue",
					searchTerms: ["face", "grin", "tongue"]
			},
				{
					title: "fas fa-face-grin-tongue-squint",
					searchTerms: ["face", "grin", "tongue", "squint"]
			},
				{
					title: "fas fa-face-grin-tongue-wink",
					searchTerms: ["face", "grin", "tongue", "wink"]
			},
				{
					title: "fas fa-face-grin-wide",
					searchTerms: ["face", "grin", "wide"]
			},
				{
					title: "fas fa-face-grin-wink",
					searchTerms: ["face", "grin", "wink"]
			},
				{
					title: "fas fa-face-kiss",
					searchTerms: ["face", "kiss"]
			},
				{
					title: "fas fa-face-kiss-beam",
					searchTerms: ["face", "kiss", "beam"]
			},
				{
					title: "fas fa-face-kiss-wink-heart",
					searchTerms: ["face", "kiss", "wink", "heart"]
			},
				{
					title: "fas fa-face-laugh",
					searchTerms: ["face", "laugh"]
			},
				{
					title: "fas fa-face-laugh-beam",
					searchTerms: ["face", "laugh", "beam"]
			},
				{
					title: "fas fa-face-laugh-squint",
					searchTerms: ["face", "laugh", "squint"]
			},
				{
					title: "fas fa-face-laugh-wink",
					searchTerms: ["face", "laugh", "wink"]
			},
				{
					title: "fas fa-face-meh",
					searchTerms: ["face", "meh"]
			},
				{
					title: "fas fa-face-meh-blank",
					searchTerms: ["face", "meh", "blank"]
			},
				{
					title: "fas fa-face-rolling-eyes",
					searchTerms: ["face", "rolling", "eyes"]
			},
				{
					title: "fas fa-face-sad-cry",
					searchTerms: ["face", "sad", "cry"]
			},
				{
					title: "fas fa-face-sad-tear",
					searchTerms: ["face", "sad", "tear"]
			},
				{
					title: "fas fa-face-smile",
					searchTerms: ["face", "smile"]
			},
				{
					title: "fas fa-face-smile-beam",
					searchTerms: ["face", "smile", "beam"]
			},
				{
					title: "fas fa-face-smile-wink",
					searchTerms: ["face", "smile", "wink"]
			},
				{
					title: "fas fa-face-surprise",
					searchTerms: ["face", "surprise"]
			},
				{
					title: "fas fa-face-tired",
					searchTerms: ["face", "tired"]
			},
				{
					title: "fas fa-fan",
					searchTerms: ["fan"]
			},
				{
					title: "fas fa-faucet",
					searchTerms: ["faucet"]
			},
				{
					title: "fas fa-faucet-drip",
					searchTerms: ["faucet", "drip"]
			},
				{
					title: "fas fa-fax",
					searchTerms: ["fax"]
			},
				{
					title: "fas fa-feather",
					searchTerms: ["feather"]
			},
				{
					title: "fas fa-feather-pointed",
					searchTerms: ["feather", "pointed"]
			},
				{
					title: "fas fa-ferry",
					searchTerms: ["ferry"]
			},
				{
					title: "fas fa-file",
					searchTerms: ["file"]
			},
				{
					title: "fas fa-file-arrow-down",
					searchTerms: ["file", "arrow", "down"]
			},
				{
					title: "fas fa-file-arrow-up",
					searchTerms: ["file", "arrow", "up"]
			},
				{
					title: "fas fa-file-audio",
					searchTerms: ["audio", "file"]
			},
				{
					title: "fas fa-file-circle-check",
					searchTerms: ["file", "circle-check"]
			},
				{
					title: "fas fa-file-circle-exclamation",
					searchTerms: ["file", "circle-exclamation"]
			},
				{
					title: "fas fa-file-circle-minus",
					searchTerms: ["file", "circle-minus"]
			},
				{
					title: "fas fa-file-circle-plus",
					searchTerms: ["file", "circle-plus"]
			},
				{
					title: "fas fa-file-circle-question",
					searchTerms: ["file", "circle-question"]
			},
				{
					title: "fas fa-file-circle-xmark",
					searchTerms: ["file", "circle-xmark"]
			},
				{
					title: "fas fa-file-code",
					searchTerms: ["code", "file"]
			},
				{
					title: "fas fa-file-contract",
					searchTerms: ["file", "contract"]
			},
				{
					title: "fas fa-file-csv",
					searchTerms: ["file", "csv"]
			},
				{
					title: "fas fa-file-excel",
					searchTerms: ["excel", "file"]
			},
				{
					title: "fas fa-file-export",
					searchTerms: ["file", "export"]
			},
				{
					title: "fas fa-file-image",
					searchTerms: ["image", "file"]
			},
				{
					title: "fas fa-file-import",
					searchTerms: ["file", "import"]
			},
				{
					title: "fas fa-file-invoice",
					searchTerms: ["file", "invoice"]
			},
				{
					title: "fas fa-file-invoice-dollar",
					searchTerms: ["file", "invoice", "with", "us", "dollar"]
			},
				{
					title: "fas fa-file-lines",
					searchTerms: ["file", "lines"]
			},
				{
					title: "fas fa-file-medical",
					searchTerms: ["medical", "file"]
			},
				{
					title: "fas fa-file-pdf",
					searchTerms: ["pdf", "file"]
			},
				{
					title: "fas fa-file-pen",
					searchTerms: ["file", "pen"]
			},
				{
					title: "fas fa-file-powerpoint",
					searchTerms: ["powerpoint", "file"]
			},
				{
					title: "fas fa-file-prescription",
					searchTerms: ["file", "prescription"]
			},
				{
					title: "fas fa-file-shield",
					searchTerms: ["file", "shield"]
			},
				{
					title: "fas fa-file-signature",
					searchTerms: ["file", "signature"]
			},
				{
					title: "fas fa-file-video",
					searchTerms: ["video", "file"]
			},
				{
					title: "fas fa-file-waveform",
					searchTerms: ["file", "waveform"]
			},
				{
					title: "fas fa-file-word",
					searchTerms: ["word", "file"]
			},
				{
					title: "fas fa-file-zipper",
					searchTerms: ["file", "zipper"]
			},
				{
					title: "fas fa-fill",
					searchTerms: ["fill"]
			},
				{
					title: "fas fa-fill-drip",
					searchTerms: ["fill", "drip"]
			},
				{
					title: "fas fa-film",
					searchTerms: ["film"]
			},
				{
					title: "fas fa-filter",
					searchTerms: ["filter"]
			},
				{
					title: "fas fa-filter-circle-dollar",
					searchTerms: ["filter", "circle", "dollar"]
			},
				{
					title: "fas fa-filter-circle-xmark",
					searchTerms: ["filter", "circle", "x", "mark"]
			},
				{
					title: "fas fa-fingerprint",
					searchTerms: ["fingerprint"]
			},
				{
					title: "fas fa-fire",
					searchTerms: ["fire"]
			},
				{
					title: "fas fa-fire-burner",
					searchTerms: ["fire", "burner"]
			},
				{
					title: "fas fa-fire-extinguisher",
					searchTerms: ["fire-extinguisher"]
			},
				{
					title: "fas fa-fire-flame-curved",
					searchTerms: ["fire", "flame", "curved"]
			},
				{
					title: "fas fa-fire-flame-simple",
					searchTerms: ["fire", "flame", "simple"]
			},
				{
					title: "fas fa-fish",
					searchTerms: ["fish"]
			},
				{
					title: "fas fa-fish-fins",
					searchTerms: ["fish", "fins"]
			},
				{
					title: "fas fa-flag",
					searchTerms: ["flag"]
			},
				{
					title: "fas fa-flag-checkered",
					searchTerms: ["flag", "checkered"]
			},
				{
					title: "fas fa-flag-usa",
					searchTerms: ["flag", "usa"]
			},
				{
					title: "fas fa-flask",
					searchTerms: ["flask"]
			},
				{
					title: "fas fa-flask-vial",
					searchTerms: ["flask", "and", "vial"]
			},
				{
					title: "fas fa-floppy-disk",
					searchTerms: ["floppy", "disk"]
			},
				{
					title: "fas fa-florin-sign",
					searchTerms: ["florin", "sign"]
			},
				{
					title: "fas fa-folder",
					searchTerms: ["folder"]
			},
				{
					title: "fas fa-folder-closed",
					searchTerms: ["folder", "closed"]
			},
				{
					title: "fas fa-folder-minus",
					searchTerms: ["folder", "minus"]
			},
				{
					title: "fas fa-folder-open",
					searchTerms: ["folder", "open"]
			},
				{
					title: "fas fa-folder-plus",
					searchTerms: ["folder", "plus"]
			},
				{
					title: "fas fa-folder-tree",
					searchTerms: ["folder", "tree"]
			},
				{
					title: "fas fa-font",
					searchTerms: ["font"]
			},
				{
					title: "fas fa-font-awesome",
					searchTerms: ["font", "awesome"]
			},
				{
					title: "fas fa-football",
					searchTerms: ["football", "ball"]
			},
				{
					title: "fas fa-forward",
					searchTerms: ["forward"]
			},
				{
					title: "fas fa-forward-fast",
					searchTerms: ["forward", "fast"]
			},
				{
					title: "fas fa-forward-step",
					searchTerms: ["forward", "step"]
			},
				{
					title: "fas fa-franc-sign",
					searchTerms: ["franc", "sign"]
			},
				{
					title: "fas fa-frog",
					searchTerms: ["frog"]
			},
				{
					title: "fas fa-futbol",
					searchTerms: ["futbol", "ball"]
			},
				{
					title: "fas fa-g",
					searchTerms: ["g"]
			},
				{
					title: "fas fa-gamepad",
					searchTerms: ["gamepad"]
			},
				{
					title: "fas fa-gas-pump",
					searchTerms: ["gas", "pump"]
			},
				{
					title: "fas fa-gauge",
					searchTerms: ["gauge", "med"]
			},
				{
					title: "fas fa-gauge-high",
					searchTerms: ["gauge"]
			},
				{
					title: "fas fa-gauge-simple",
					searchTerms: ["gauge", "simple", "med"]
			},
				{
					title: "fas fa-gauge-simple-high",
					searchTerms: ["gauge", "simple"]
			},
				{
					title: "fas fa-gavel",
					searchTerms: ["gavel"]
			},
				{
					title: "fas fa-gear",
					searchTerms: ["gear"]
			},
				{
					title: "fas fa-gears",
					searchTerms: ["gears"]
			},
				{
					title: "fas fa-gem",
					searchTerms: ["gem"]
			},
				{
					title: "fas fa-genderless",
					searchTerms: ["genderless"]
			},
				{
					title: "fas fa-ghost",
					searchTerms: ["ghost"]
			},
				{
					title: "fas fa-gift",
					searchTerms: ["gift"]
			},
				{
					title: "fas fa-gifts",
					searchTerms: ["gifts"]
			},
				{
					title: "fas fa-glass-water",
					searchTerms: ["glass", "water"]
			},
				{
					title: "fas fa-glass-water-droplet",
					searchTerms: ["glass", "water-droplet"]
			},
				{
					title: "fas fa-glasses",
					searchTerms: ["glasses"]
			},
				{
					title: "fas fa-globe",
					searchTerms: ["globe"]
			},
				{
					title: "fas fa-golf-ball-tee",
					searchTerms: ["golf", "ball", "tee"]
			},
				{
					title: "fas fa-gopuram",
					searchTerms: ["gopuram"]
			},
				{
					title: "fas fa-graduation-cap",
					searchTerms: ["graduation", "cap"]
			},
				{
					title: "fas fa-greater-than",
					searchTerms: ["greater", "than"]
			},
				{
					title: "fas fa-greater-than-equal",
					searchTerms: ["greater", "than", "equal", "to"]
			},
				{
					title: "fas fa-grip",
					searchTerms: ["grip"]
			},
				{
					title: "fas fa-grip-lines",
					searchTerms: ["grip", "lines"]
			},
				{
					title: "fas fa-grip-lines-vertical",
					searchTerms: ["grip", "lines", "vertical"]
			},
				{
					title: "fas fa-grip-vertical",
					searchTerms: ["grip", "vertical"]
			},
				{
					title: "fas fa-group-arrows-rotate",
					searchTerms: ["group", "arrows-rotate"]
			},
				{
					title: "fas fa-guarani-sign",
					searchTerms: ["guarani", "sign"]
			},
				{
					title: "fas fa-guitar",
					searchTerms: ["guitar"]
			},
				{
					title: "fas fa-gun",
					searchTerms: ["gun"]
			},
				{
					title: "fas fa-h",
					searchTerms: ["h"]
			},
				{
					title: "fas fa-hammer",
					searchTerms: ["hammer"]
			},
				{
					title: "fas fa-hamsa",
					searchTerms: ["hamsa"]
			},
				{
					title: "fas fa-hand",
					searchTerms: ["paper", "(hand)"]
			},
				{
					title: "fas fa-hand-back-fist",
					searchTerms: ["rock", "(hand)"]
			},
				{
					title: "fas fa-hand-dots",
					searchTerms: ["hand", "dots"]
			},
				{
					title: "fas fa-hand-fist",
					searchTerms: ["raised", "fist"]
			},
				{
					title: "fas fa-hand-holding",
					searchTerms: ["hand", "holding"]
			},
				{
					title: "fas fa-hand-holding-dollar",
					searchTerms: ["hand", "holding", "dollar"]
			},
				{
					title: "fas fa-hand-holding-droplet",
					searchTerms: ["hand", "holding", "droplet"]
			},
				{
					title: "fas fa-hand-holding-hand",
					searchTerms: ["hand", "holding-hand"]
			},
				{
					title: "fas fa-hand-holding-heart",
					searchTerms: ["hand", "holding", "heart"]
			},
				{
					title: "fas fa-hand-holding-medical",
					searchTerms: ["hand", "holding", "medical", "cross"]
			},
				{
					title: "fas fa-hand-lizard",
					searchTerms: ["lizard", "(hand)"]
			},
				{
					title: "fas fa-hand-middle-finger",
					searchTerms: ["hand", "with", "middle", "finger", "raised"]
			},
				{
					title: "fas fa-hand-peace",
					searchTerms: ["peace", "(hand)"]
			},
				{
					title: "fas fa-hand-point-down",
					searchTerms: ["hand", "pointing", "down"]
			},
				{
					title: "fas fa-hand-point-left",
					searchTerms: ["hand", "pointing", "left"]
			},
				{
					title: "fas fa-hand-point-right",
					searchTerms: ["hand", "pointing", "right"]
			},
				{
					title: "fas fa-hand-point-up",
					searchTerms: ["hand", "pointing", "up"]
			},
				{
					title: "fas fa-hand-pointer",
					searchTerms: ["pointer", "(hand)"]
			},
				{
					title: "fas fa-hand-scissors",
					searchTerms: ["scissors", "(hand)"]
			},
				{
					title: "fas fa-hand-sparkles",
					searchTerms: ["hand", "sparkles"]
			},
				{
					title: "fas fa-hand-spock",
					searchTerms: ["spock", "(hand)"]
			},
				{
					title: "fas fa-handcuffs",
					searchTerms: ["handcuffs"]
			},
				{
					title: "fas fa-hands",
					searchTerms: ["hands"]
			},
				{
					title: "fas fa-hands-asl-interpreting",
					searchTerms: ["hands", "american", "sign", "language", "interpreting"]
			},
				{
					title: "fas fa-hands-bound",
					searchTerms: ["hands", "bound"]
			},
				{
					title: "fas fa-hands-bubbles",
					searchTerms: ["hands", "bubbles"]
			},
				{
					title: "fas fa-hands-clapping",
					searchTerms: ["hands", "clapping"]
			},
				{
					title: "fas fa-hands-holding",
					searchTerms: ["hands", "holding"]
			},
				{
					title: "fas fa-hands-holding-child",
					searchTerms: ["hands", "holding-child"]
			},
				{
					title: "fas fa-hands-holding-circle",
					searchTerms: ["hands", "holding-circle"]
			},
				{
					title: "fas fa-hands-praying",
					searchTerms: ["hands", "praying"]
			},
				{
					title: "fas fa-handshake",
					searchTerms: ["handshake"]
			},
				{
					title: "fas fa-handshake-angle",
					searchTerms: ["handshake", "angle"]
			},
				{
					title: "fas fa-handshake-simple",
					searchTerms: ["handshake", "simple"]
			},
				{
					title: "fas fa-handshake-simple-slash",
					searchTerms: ["handshake", "simple", "slash"]
			},
				{
					title: "fas fa-handshake-slash",
					searchTerms: ["handshake", "slash"]
			},
				{
					title: "fas fa-hanukiah",
					searchTerms: ["hanukiah"]
			},
				{
					title: "fas fa-hard-drive",
					searchTerms: ["hard", "drive"]
			},
				{
					title: "fas fa-hashtag",
					searchTerms: ["hashtag"]
			},
				{
					title: "fas fa-hat-cowboy",
					searchTerms: ["cowboy", "hat"]
			},
				{
					title: "fas fa-hat-cowboy-side",
					searchTerms: ["cowboy", "hat", "side"]
			},
				{
					title: "fas fa-hat-wizard",
					searchTerms: ["wizard's", "hat"]
			},
				{
					title: "fas fa-head-side-cough",
					searchTerms: ["head", "side", "cough"]
			},
				{
					title: "fas fa-head-side-cough-slash",
					searchTerms: ["head", "side-cough-slash"]
			},
				{
					title: "fas fa-head-side-mask",
					searchTerms: ["head", "side", "mask"]
			},
				{
					title: "fas fa-head-side-virus",
					searchTerms: ["head", "side", "virus"]
			},
				{
					title: "fas fa-heading",
					searchTerms: ["heading"]
			},
				{
					title: "fas fa-headphones",
					searchTerms: ["headphones"]
			},
				{
					title: "fas fa-headphones-simple",
					searchTerms: ["headphones", "simple"]
			},
				{
					title: "fas fa-headset",
					searchTerms: ["headset"]
			},
				{
					title: "fas fa-heart",
					searchTerms: ["heart"]
			},
				{
					title: "fas fa-heart-circle-bolt",
					searchTerms: ["heart", "circle-bolt"]
			},
				{
					title: "fas fa-heart-circle-check",
					searchTerms: ["heart", "circle-check"]
			},
				{
					title: "fas fa-heart-circle-exclamation",
					searchTerms: ["heart", "circle-exclamation"]
			},
				{
					title: "fas fa-heart-circle-minus",
					searchTerms: ["heart", "circle-minus"]
			},
				{
					title: "fas fa-heart-circle-plus",
					searchTerms: ["heart", "circle-plus"]
			},
				{
					title: "fas fa-heart-circle-xmark",
					searchTerms: ["heart", "circle-xmark"]
			},
				{
					title: "fas fa-heart-crack",
					searchTerms: ["heart", "crack"]
			},
				{
					title: "fas fa-heart-pulse",
					searchTerms: ["heart", "pulse"]
			},
				{
					title: "fas fa-helicopter",
					searchTerms: ["helicopter"]
			},
				{
					title: "fas fa-helicopter-symbol",
					searchTerms: ["helicopter", "symbol"]
			},
				{
					title: "fas fa-helmet-safety",
					searchTerms: ["helmet", "safety"]
			},
				{
					title: "fas fa-helmet-un",
					searchTerms: ["helmet", "un"]
			},
				{
					title: "fas fa-highlighter",
					searchTerms: ["highlighter"]
			},
				{
					title: "fas fa-hill-avalanche",
					searchTerms: ["hill", "avalanche"]
			},
				{
					title: "fas fa-hill-rockslide",
					searchTerms: ["hill", "rockslide"]
			},
				{
					title: "fas fa-hippo",
					searchTerms: ["hippo"]
			},
				{
					title: "fas fa-hockey-puck",
					searchTerms: ["hockey", "puck"]
			},
				{
					title: "fas fa-holly-berry",
					searchTerms: ["holly", "berry"]
			},
				{
					title: "fas fa-horse",
					searchTerms: ["horse"]
			},
				{
					title: "fas fa-horse-head",
					searchTerms: ["horse", "head"]
			},
				{
					title: "fas fa-hospital",
					searchTerms: ["hospital"]
			},
				{
					title: "fas fa-hospital-user",
					searchTerms: ["hospital", "with", "user"]
			},
				{
					title: "fas fa-hot-tub-person",
					searchTerms: ["hot", "tub", "person"]
			},
				{
					title: "fas fa-hotdog",
					searchTerms: ["hot", "dog"]
			},
				{
					title: "fas fa-hotel",
					searchTerms: ["hotel"]
			},
				{
					title: "fas fa-hourglass",
					searchTerms: ["hourglass"]
			},
				{
					title: "fas fa-hourglass-end",
					searchTerms: ["hourglass", "end"]
			},
				{
					title: "fas fa-hourglass-half",
					searchTerms: ["hourglass", "half"]
			},
				{
					title: "fas fa-hourglass-start",
					searchTerms: ["hourglass", "start"]
			},
				{
					title: "fas fa-house",
					searchTerms: ["house"]
			},
				{
					title: "fas fa-house-chimney",
					searchTerms: ["house", "chimney"]
			},
				{
					title: "fas fa-house-chimney-crack",
					searchTerms: ["house", "crack"]
			},
				{
					title: "fas fa-house-chimney-medical",
					searchTerms: ["house", "medical"]
			},
				{
					title: "fas fa-house-chimney-user",
					searchTerms: ["house", "user"]
			},
				{
					title: "fas fa-house-chimney-window",
					searchTerms: ["house", "with", "window", "+", "chimney"]
			},
				{
					title: "fas fa-house-circle-check",
					searchTerms: ["house", "circle-check"]
			},
				{
					title: "fas fa-house-circle-exclamation",
					searchTerms: ["house", "circle-exclamation"]
			},
				{
					title: "fas fa-house-circle-xmark",
					searchTerms: ["house", "circle-xmark"]
			},
				{
					title: "fas fa-house-crack",
					searchTerms: ["house", "simple", "crack"]
			},
				{
					title: "fas fa-house-fire",
					searchTerms: ["house", "fire"]
			},
				{
					title: "fas fa-house-flag",
					searchTerms: ["house", "flag"]
			},
				{
					title: "fas fa-house-flood-water",
					searchTerms: ["house", "flood"]
			},
				{
					title: "fas fa-house-flood-water-circle-arrow-right",
					searchTerms: ["house", "flood-circle-arrow-right"]
			},
				{
					title: "fas fa-house-laptop",
					searchTerms: ["house", "laptop"]
			},
				{
					title: "fas fa-house-lock",
					searchTerms: ["house", "lock"]
			},
				{
					title: "fas fa-house-medical",
					searchTerms: ["house", "simple", "medical"]
			},
				{
					title: "fas fa-house-medical-circle-check",
					searchTerms: ["house", "medical-circle-check"]
			},
				{
					title: "fas fa-house-medical-circle-exclamation",
					searchTerms: ["house", "medical-circle-exclamation"]
			},
				{
					title: "fas fa-house-medical-circle-xmark",
					searchTerms: ["house", "medical-circle-xmark"]
			},
				{
					title: "fas fa-house-medical-flag",
					searchTerms: ["house", "medical-flag"]
			},
				{
					title: "fas fa-house-signal",
					searchTerms: ["house", "signal"]
			},
				{
					title: "fas fa-house-tsunami",
					searchTerms: ["house", "tsunami"]
			},
				{
					title: "fas fa-house-user",
					searchTerms: ["home", "user"]
			},
				{
					title: "fas fa-hryvnia-sign",
					searchTerms: ["hryvnia", "sign"]
			},
				{
					title: "fas fa-hurricane",
					searchTerms: ["hurricane"]
			},
				{
					title: "fas fa-i",
					searchTerms: ["i"]
			},
				{
					title: "fas fa-i-cursor",
					searchTerms: ["i", "beam", "cursor"]
			},
				{
					title: "fas fa-ice-cream",
					searchTerms: ["ice", "cream"]
			},
				{
					title: "fas fa-icicles",
					searchTerms: ["icicles"]
			},
				{
					title: "fas fa-icons",
					searchTerms: ["icons"]
			},
				{
					title: "fas fa-id-badge",
					searchTerms: ["identification", "badge"]
			},
				{
					title: "fas fa-id-card",
					searchTerms: ["identification", "card"]
			},
				{
					title: "fas fa-id-card-clip",
					searchTerms: ["id", "card", "clip"]
			},
				{
					title: "fas fa-igloo",
					searchTerms: ["igloo"]
			},
				{
					title: "fas fa-image",
					searchTerms: ["image"]
			},
				{
					title: "fas fa-image-portrait",
					searchTerms: ["image", "portrait"]
			},
				{
					title: "fas fa-images",
					searchTerms: ["images"]
			},
				{
					title: "fas fa-inbox",
					searchTerms: ["inbox"]
			},
				{
					title: "fas fa-indent",
					searchTerms: ["indent"]
			},
				{
					title: "fas fa-indian-rupee-sign",
					searchTerms: ["indian", "rupee-sign"]
			},
				{
					title: "fas fa-industry",
					searchTerms: ["industry"]
			},
				{
					title: "fas fa-infinity",
					searchTerms: ["infinity"]
			},
				{
					title: "fas fa-info",
					searchTerms: ["info"]
			},
				{
					title: "fas fa-italic",
					searchTerms: ["italic"]
			},
				{
					title: "fas fa-j",
					searchTerms: ["j"]
			},
				{
					title: "fas fa-jar",
					searchTerms: ["jar"]
			},
				{
					title: "fas fa-jar-wheat",
					searchTerms: ["jar", "wheat"]
			},
				{
					title: "fas fa-jedi",
					searchTerms: ["jedi"]
			},
				{
					title: "fas fa-jet-fighter",
					searchTerms: ["jet", "fighter"]
			},
				{
					title: "fas fa-jet-fighter-up",
					searchTerms: ["jet", "fighter", "up"]
			},
				{
					title: "fas fa-joint",
					searchTerms: ["joint"]
			},
				{
					title: "fas fa-jug-detergent",
					searchTerms: ["jug", "detergent"]
			},
				{
					title: "fas fa-k",
					searchTerms: ["k"]
			},
				{
					title: "fas fa-kaaba",
					searchTerms: ["kaaba"]
			},
				{
					title: "fas fa-key",
					searchTerms: ["key"]
			},
				{
					title: "fas fa-keyboard",
					searchTerms: ["keyboard"]
			},
				{
					title: "fas fa-khanda",
					searchTerms: ["khanda"]
			},
				{
					title: "fas fa-kip-sign",
					searchTerms: ["kip", "sign"]
			},
				{
					title: "fas fa-kit-medical",
					searchTerms: ["kit", "medical"]
			},
				{
					title: "fas fa-kitchen-set",
					searchTerms: ["kitchen", "set"]
			},
				{
					title: "fas fa-kiwi-bird",
					searchTerms: ["kiwi", "bird"]
			},
				{
					title: "fas fa-l",
					searchTerms: ["l"]
			},
				{
					title: "fas fa-land-mine-on",
					searchTerms: ["land", "mine-on"]
			},
				{
					title: "fas fa-landmark",
					searchTerms: ["landmark"]
			},
				{
					title: "fas fa-landmark-dome",
					searchTerms: ["landmark", "dome"]
			},
				{
					title: "fas fa-landmark-flag",
					searchTerms: ["landmark", "flag"]
			},
				{
					title: "fas fa-language",
					searchTerms: ["language"]
			},
				{
					title: "fas fa-laptop",
					searchTerms: ["laptop"]
			},
				{
					title: "fas fa-laptop-code",
					searchTerms: ["laptop", "code"]
			},
				{
					title: "fas fa-laptop-file",
					searchTerms: ["laptop", "file"]
			},
				{
					title: "fas fa-laptop-medical",
					searchTerms: ["laptop", "medical"]
			},
				{
					title: "fas fa-lari-sign",
					searchTerms: ["lari", "sign"]
			},
				{
					title: "fas fa-layer-group",
					searchTerms: ["layer", "group"]
			},
				{
					title: "fas fa-leaf",
					searchTerms: ["leaf"]
			},
				{
					title: "fas fa-left-long",
					searchTerms: ["left", "long"]
			},
				{
					title: "fas fa-left-right",
					searchTerms: ["left", "right"]
			},
				{
					title: "fas fa-lemon",
					searchTerms: ["lemon"]
			},
				{
					title: "fas fa-less-than",
					searchTerms: ["less", "than"]
			},
				{
					title: "fas fa-less-than-equal",
					searchTerms: ["less", "than", "equal", "to"]
			},
				{
					title: "fas fa-life-ring",
					searchTerms: ["life", "ring"]
			},
				{
					title: "fas fa-lightbulb",
					searchTerms: ["lightbulb"]
			},
				{
					title: "fas fa-lines-leaning",
					searchTerms: ["lines", "leaning"]
			},
				{
					title: "fas fa-link",
					searchTerms: ["link"]
			},
				{
					title: "fas fa-link-slash",
					searchTerms: ["link", "slash"]
			},
				{
					title: "fas fa-lira-sign",
					searchTerms: ["lira", "sign"]
			},
				{
					title: "fas fa-list",
					searchTerms: ["list"]
			},
				{
					title: "fas fa-list-check",
					searchTerms: ["list", "check"]
			},
				{
					title: "fas fa-list-ol",
					searchTerms: ["list-ol"]
			},
				{
					title: "fas fa-list-ul",
					searchTerms: ["list-ul"]
			},
				{
					title: "fas fa-litecoin-sign",
					searchTerms: ["litecoin", "sign"]
			},
				{
					title: "fas fa-location-arrow",
					searchTerms: ["location-arrow"]
			},
				{
					title: "fas fa-location-crosshairs",
					searchTerms: ["location", "crosshairs"]
			},
				{
					title: "fas fa-location-dot",
					searchTerms: ["location", "dot"]
			},
				{
					title: "fas fa-location-pin",
					searchTerms: ["location"]
			},
				{
					title: "fas fa-location-pin-lock",
					searchTerms: ["location", "pin-lock"]
			},
				{
					title: "fas fa-lock",
					searchTerms: ["lock"]
			},
				{
					title: "fas fa-lock-open",
					searchTerms: ["lock", "open"]
			},
				{
					title: "fas fa-locust",
					searchTerms: ["locust"]
			},
				{
					title: "fas fa-lungs",
					searchTerms: ["lungs"]
			},
				{
					title: "fas fa-lungs-virus",
					searchTerms: ["lungs", "virus"]
			},
				{
					title: "fas fa-m",
					searchTerms: ["m"]
			},
				{
					title: "fas fa-magnet",
					searchTerms: ["magnet"]
			},
				{
					title: "fas fa-magnifying-glass",
					searchTerms: ["magnifying", "glass"]
			},
				{
					title: "fas fa-magnifying-glass-arrow-right",
					searchTerms: ["magnifying", "glass-arrow-right"]
			},
				{
					title: "fas fa-magnifying-glass-chart",
					searchTerms: ["magnifying", "glass-chart"]
			},
				{
					title: "fas fa-magnifying-glass-dollar",
					searchTerms: ["magnifying", "glass", "dollar"]
			},
				{
					title: "fas fa-magnifying-glass-location",
					searchTerms: ["magnifying", "glass", "location"]
			},
				{
					title: "fas fa-magnifying-glass-minus",
					searchTerms: ["magnifying", "glass", "minus"]
			},
				{
					title: "fas fa-magnifying-glass-plus",
					searchTerms: ["magnifying", "glass", "plus"]
			},
				{
					title: "fas fa-manat-sign",
					searchTerms: ["manat", "sign"]
			},
				{
					title: "fas fa-map",
					searchTerms: ["map"]
			},
				{
					title: "fas fa-map-location",
					searchTerms: ["map", "location"]
			},
				{
					title: "fas fa-map-location-dot",
					searchTerms: ["map", "location", "dot"]
			},
				{
					title: "fas fa-map-pin",
					searchTerms: ["map", "pin"]
			},
				{
					title: "fas fa-marker",
					searchTerms: ["marker"]
			},
				{
					title: "fas fa-mars",
					searchTerms: ["mars"]
			},
				{
					title: "fas fa-mars-and-venus",
					searchTerms: ["mars", "and", "venus"]
			},
				{
					title: "fas fa-mars-and-venus-burst",
					searchTerms: ["mars", "and", "venus", "burst"]
			},
				{
					title: "fas fa-mars-double",
					searchTerms: ["mars", "double"]
			},
				{
					title: "fas fa-mars-stroke",
					searchTerms: ["mars", "stroke"]
			},
				{
					title: "fas fa-mars-stroke-right",
					searchTerms: ["mars", "stroke", "right"]
			},
				{
					title: "fas fa-mars-stroke-up",
					searchTerms: ["mars", "stroke", "up"]
			},
				{
					title: "fas fa-martini-glass",
					searchTerms: ["martini", "glass"]
			},
				{
					title: "fas fa-martini-glass-citrus",
					searchTerms: ["martini", "glass", "citrus"]
			},
				{
					title: "fas fa-martini-glass-empty",
					searchTerms: ["martini", "glass", "empty"]
			},
				{
					title: "fas fa-mask",
					searchTerms: ["mask"]
			},
				{
					title: "fas fa-mask-face",
					searchTerms: ["face", "mask"]
			},
				{
					title: "fas fa-mask-ventilator",
					searchTerms: ["mask", "ventilator"]
			},
				{
					title: "fas fa-masks-theater",
					searchTerms: ["masks", "theater"]
			},
				{
					title: "fas fa-mattress-pillow",
					searchTerms: ["mattress", "pillow"]
			},
				{
					title: "fas fa-maximize",
					searchTerms: ["maximize"]
			},
				{
					title: "fas fa-medal",
					searchTerms: ["medal"]
			},
				{
					title: "fas fa-memory",
					searchTerms: ["memory"]
			},
				{
					title: "fas fa-menorah",
					searchTerms: ["menorah"]
			},
				{
					title: "fas fa-mercury",
					searchTerms: ["mercury"]
			},
				{
					title: "fas fa-message",
					searchTerms: ["message"]
			},
				{
					title: "fas fa-meteor",
					searchTerms: ["meteor"]
			},
				{
					title: "fas fa-microchip",
					searchTerms: ["microchip"]
			},
				{
					title: "fas fa-microphone",
					searchTerms: ["microphone"]
			},
				{
					title: "fas fa-microphone-lines",
					searchTerms: ["microphone", "lines"]
			},
				{
					title: "fas fa-microphone-lines-slash",
					searchTerms: ["microphone", "lines", "slash"]
			},
				{
					title: "fas fa-microphone-slash",
					searchTerms: ["microphone", "slash"]
			},
				{
					title: "fas fa-microscope",
					searchTerms: ["microscope"]
			},
				{
					title: "fas fa-mill-sign",
					searchTerms: ["mill", "sign"]
			},
				{
					title: "fas fa-minimize",
					searchTerms: ["minimize"]
			},
				{
					title: "fas fa-minus",
					searchTerms: ["minus"]
			},
				{
					title: "fas fa-mitten",
					searchTerms: ["mitten"]
			},
				{
					title: "fas fa-mobile",
					searchTerms: ["mobile"]
			},
				{
					title: "fas fa-mobile-button",
					searchTerms: ["mobile", "button"]
			},
				{
					title: "fas fa-mobile-retro",
					searchTerms: ["mobile", "retro"]
			},
				{
					title: "fas fa-mobile-screen",
					searchTerms: ["mobile", "screen"]
			},
				{
					title: "fas fa-mobile-screen-button",
					searchTerms: ["mobile", "screen", "button"]
			},
				{
					title: "fas fa-money-bill",
					searchTerms: ["money", "bill"]
			},
				{
					title: "fas fa-money-bill-1",
					searchTerms: ["money", "bill", "1"]
			},
				{
					title: "fas fa-money-bill-1-wave",
					searchTerms: ["money", "bill", "1", "wave"]
			},
				{
					title: "fas fa-money-bill-transfer",
					searchTerms: ["money", "bill-transfer"]
			},
				{
					title: "fas fa-money-bill-trend-up",
					searchTerms: ["money", "bill-trend-up"]
			},
				{
					title: "fas fa-money-bill-wave",
					searchTerms: ["wavy", "money", "bill"]
			},
				{
					title: "fas fa-money-bill-wheat",
					searchTerms: ["money", "bill-wheat"]
			},
				{
					title: "fas fa-money-bills",
					searchTerms: ["money", "bills"]
			},
				{
					title: "fas fa-money-check",
					searchTerms: ["money", "check"]
			},
				{
					title: "fas fa-money-check-dollar",
					searchTerms: ["money", "check", "dollar"]
			},
				{
					title: "fas fa-monument",
					searchTerms: ["monument"]
			},
				{
					title: "fas fa-moon",
					searchTerms: ["moon"]
			},
				{
					title: "fas fa-mortar-pestle",
					searchTerms: ["mortar", "pestle"]
			},
				{
					title: "fas fa-mosque",
					searchTerms: ["mosque"]
			},
				{
					title: "fas fa-mosquito",
					searchTerms: ["mosquito"]
			},
				{
					title: "fas fa-mosquito-net",
					searchTerms: ["mosquito", "net"]
			},
				{
					title: "fas fa-motorcycle",
					searchTerms: ["motorcycle"]
			},
				{
					title: "fas fa-mound",
					searchTerms: ["mound"]
			},
				{
					title: "fas fa-mountain",
					searchTerms: ["mountain"]
			},
				{
					title: "fas fa-mountain-city",
					searchTerms: ["mountain", "city"]
			},
				{
					title: "fas fa-mountain-sun",
					searchTerms: ["mountain", "sun"]
			},
				{
					title: "fas fa-mug-hot",
					searchTerms: ["mug", "hot"]
			},
				{
					title: "fas fa-mug-saucer",
					searchTerms: ["mug", "saucer"]
			},
				{
					title: "fas fa-music",
					searchTerms: ["music"]
			},
				{
					title: "fas fa-n",
					searchTerms: ["n"]
			},
				{
					title: "fas fa-naira-sign",
					searchTerms: ["naira", "sign"]
			},
				{
					title: "fas fa-network-wired",
					searchTerms: ["wired", "network"]
			},
				{
					title: "fas fa-neuter",
					searchTerms: ["neuter"]
			},
				{
					title: "fas fa-newspaper",
					searchTerms: ["newspaper"]
			},
				{
					title: "fas fa-not-equal",
					searchTerms: ["not", "equal"]
			},
				{
					title: "fas fa-notdef",
					searchTerms: ["notdef"]
			},
				{
					title: "fas fa-note-sticky",
					searchTerms: ["note", "sticky"]
			},
				{
					title: "fas fa-notes-medical",
					searchTerms: ["notes", "medical"]
			},
				{
					title: "fas fa-o",
					searchTerms: ["o"]
			},
				{
					title: "fas fa-object-group",
					searchTerms: ["object", "group"]
			},
				{
					title: "fas fa-object-ungroup",
					searchTerms: ["object", "ungroup"]
			},
				{
					title: "fas fa-oil-can",
					searchTerms: ["oil", "can"]
			},
				{
					title: "fas fa-oil-well",
					searchTerms: ["oil", "well"]
			},
				{
					title: "fas fa-om",
					searchTerms: ["om"]
			},
				{
					title: "fas fa-otter",
					searchTerms: ["otter"]
			},
				{
					title: "fas fa-outdent",
					searchTerms: ["outdent"]
			},
				{
					title: "fas fa-p",
					searchTerms: ["p"]
			},
				{
					title: "fas fa-pager",
					searchTerms: ["pager"]
			},
				{
					title: "fas fa-paint-roller",
					searchTerms: ["paint", "roller"]
			},
				{
					title: "fas fa-paintbrush",
					searchTerms: ["paint", "brush"]
			},
				{
					title: "fas fa-palette",
					searchTerms: ["palette"]
			},
				{
					title: "fas fa-pallet",
					searchTerms: ["pallet"]
			},
				{
					title: "fas fa-panorama",
					searchTerms: ["panorama"]
			},
				{
					title: "fas fa-paper-plane",
					searchTerms: ["paper", "plane"]
			},
				{
					title: "fas fa-paperclip",
					searchTerms: ["paperclip"]
			},
				{
					title: "fas fa-parachute-box",
					searchTerms: ["parachute", "box"]
			},
				{
					title: "fas fa-paragraph",
					searchTerms: ["paragraph"]
			},
				{
					title: "fas fa-passport",
					searchTerms: ["passport"]
			},
				{
					title: "fas fa-paste",
					searchTerms: ["paste"]
			},
				{
					title: "fas fa-pause",
					searchTerms: ["pause"]
			},
				{
					title: "fas fa-paw",
					searchTerms: ["paw"]
			},
				{
					title: "fas fa-peace",
					searchTerms: ["peace"]
			},
				{
					title: "fas fa-pen",
					searchTerms: ["pen"]
			},
				{
					title: "fas fa-pen-clip",
					searchTerms: ["pen", "clip"]
			},
				{
					title: "fas fa-pen-fancy",
					searchTerms: ["pen", "fancy"]
			},
				{
					title: "fas fa-pen-nib",
					searchTerms: ["pen", "nib"]
			},
				{
					title: "fas fa-pen-ruler",
					searchTerms: ["pen", "ruler"]
			},
				{
					title: "fas fa-pen-to-square",
					searchTerms: ["pen", "to", "square"]
			},
				{
					title: "fas fa-pencil",
					searchTerms: ["pencil"]
			},
				{
					title: "fas fa-people-arrows",
					searchTerms: ["people", "arrows", "left", "right"]
			},
				{
					title: "fas fa-people-carry-box",
					searchTerms: ["people", "carry", "box"]
			},
				{
					title: "fas fa-people-group",
					searchTerms: ["people", "group"]
			},
				{
					title: "fas fa-people-line",
					searchTerms: ["people", "line"]
			},
				{
					title: "fas fa-people-pulling",
					searchTerms: ["people", "pulling"]
			},
				{
					title: "fas fa-people-robbery",
					searchTerms: ["people", "robbery"]
			},
				{
					title: "fas fa-people-roof",
					searchTerms: ["people", "roof"]
			},
				{
					title: "fas fa-pepper-hot",
					searchTerms: ["hot", "pepper"]
			},
				{
					title: "fas fa-percent",
					searchTerms: ["percent"]
			},
				{
					title: "fas fa-person",
					searchTerms: ["person"]
			},
				{
					title: "fas fa-person-arrow-down-to-line",
					searchTerms: ["person", "arrow-down-to-line"]
			},
				{
					title: "fas fa-person-arrow-up-from-line",
					searchTerms: ["person", "arrow-up-from-line"]
			},
				{
					title: "fas fa-person-biking",
					searchTerms: ["person", "biking"]
			},
				{
					title: "fas fa-person-booth",
					searchTerms: ["person", "entering", "booth"]
			},
				{
					title: "fas fa-person-breastfeeding",
					searchTerms: ["person", "breastfeeding"]
			},
				{
					title: "fas fa-person-burst",
					searchTerms: ["person", "burst"]
			},
				{
					title: "fas fa-person-cane",
					searchTerms: ["person", "cane"]
			},
				{
					title: "fas fa-person-chalkboard",
					searchTerms: ["person", "chalkboard"]
			},
				{
					title: "fas fa-person-circle-check",
					searchTerms: ["person", "circle-check"]
			},
				{
					title: "fas fa-person-circle-exclamation",
					searchTerms: ["person", "circle-exclamation"]
			},
				{
					title: "fas fa-person-circle-minus",
					searchTerms: ["person", "circle-minus"]
			},
				{
					title: "fas fa-person-circle-plus",
					searchTerms: ["person", "circle-plus"]
			},
				{
					title: "fas fa-person-circle-question",
					searchTerms: ["person", "circle-question"]
			},
				{
					title: "fas fa-person-circle-xmark",
					searchTerms: ["person", "circle-xmark"]
			},
				{
					title: "fas fa-person-digging",
					searchTerms: ["person", "digging"]
			},
				{
					title: "fas fa-person-dots-from-line",
					searchTerms: ["person", "dots", "from", "line"]
			},
				{
					title: "fas fa-person-dress",
					searchTerms: ["person", "dress"]
			},
				{
					title: "fas fa-person-dress-burst",
					searchTerms: ["person", "dress", "burst"]
			},
				{
					title: "fas fa-person-drowning",
					searchTerms: ["person", "drowning"]
			},
				{
					title: "fas fa-person-falling",
					searchTerms: ["person", "falling"]
			},
				{
					title: "fas fa-person-falling-burst",
					searchTerms: ["person", "falling", "burst"]
			},
				{
					title: "fas fa-person-half-dress",
					searchTerms: ["person", "half-dress"]
			},
				{
					title: "fas fa-person-harassing",
					searchTerms: ["person", "harassing"]
			},
				{
					title: "fas fa-person-hiking",
					searchTerms: ["person", "hiking"]
			},
				{
					title: "fas fa-person-military-pointing",
					searchTerms: ["person", "military-pointing"]
			},
				{
					title: "fas fa-person-military-rifle",
					searchTerms: ["person", "military-rifle"]
			},
				{
					title: "fas fa-person-military-to-person",
					searchTerms: ["person", "military-to-person"]
			},
				{
					title: "fas fa-person-praying",
					searchTerms: ["person", "praying"]
			},
				{
					title: "fas fa-person-pregnant",
					searchTerms: ["person", "pregnant"]
			},
				{
					title: "fas fa-person-rays",
					searchTerms: ["person", "rays"]
			},
				{
					title: "fas fa-person-rifle",
					searchTerms: ["person", "rifle"]
			},
				{
					title: "fas fa-person-running",
					searchTerms: ["person", "running"]
			},
				{
					title: "fas fa-person-shelter",
					searchTerms: ["person", "shelter"]
			},
				{
					title: "fas fa-person-skating",
					searchTerms: ["person", "skating"]
			},
				{
					title: "fas fa-person-skiing",
					searchTerms: ["person", "skiing"]
			},
				{
					title: "fas fa-person-skiing-nordic",
					searchTerms: ["person", "skiing", "nordic"]
			},
				{
					title: "fas fa-person-snowboarding",
					searchTerms: ["person", "snowboarding"]
			},
				{
					title: "fas fa-person-swimming",
					searchTerms: ["person", "swimming"]
			},
				{
					title: "fas fa-person-through-window",
					searchTerms: ["person", "through-window"]
			},
				{
					title: "fas fa-person-walking",
					searchTerms: ["person", "walking"]
			},
				{
					title: "fas fa-person-walking-arrow-loop-left",
					searchTerms: ["person", "walking-arrow-loop-left"]
			},
				{
					title: "fas fa-person-walking-arrow-right",
					searchTerms: ["person", "walking-arrow-right"]
			},
				{
					title: "fas fa-person-walking-dashed-line-arrow-right",
					searchTerms: ["person", "walking-dashed-line-arrow-right"]
			},
				{
					title: "fas fa-person-walking-luggage",
					searchTerms: ["person", "walking-luggage"]
			},
				{
					title: "fas fa-person-walking-with-cane",
					searchTerms: ["person", "walking", "with", "cane"]
			},
				{
					title: "fas fa-peseta-sign",
					searchTerms: ["peseta", "sign"]
			},
				{
					title: "fas fa-peso-sign",
					searchTerms: ["peso", "sign"]
			},
				{
					title: "fas fa-phone",
					searchTerms: ["phone"]
			},
				{
					title: "fas fa-phone-flip",
					searchTerms: ["phone", "flip"]
			},
				{
					title: "fas fa-phone-slash",
					searchTerms: ["phone", "slash"]
			},
				{
					title: "fas fa-phone-volume",
					searchTerms: ["phone", "volume"]
			},
				{
					title: "fas fa-photo-film",
					searchTerms: ["photo", "film"]
			},
				{
					title: "fas fa-piggy-bank",
					searchTerms: ["piggy", "bank"]
			},
				{
					title: "fas fa-pills",
					searchTerms: ["pills"]
			},
				{
					title: "fas fa-pizza-slice",
					searchTerms: ["pizza", "slice"]
			},
				{
					title: "fas fa-place-of-worship",
					searchTerms: ["place", "of", "worship"]
			},
				{
					title: "fas fa-plane",
					searchTerms: ["plane"]
			},
				{
					title: "fas fa-plane-arrival",
					searchTerms: ["plane", "arrival"]
			},
				{
					title: "fas fa-plane-circle-check",
					searchTerms: ["plane", "circle-check"]
			},
				{
					title: "fas fa-plane-circle-exclamation",
					searchTerms: ["plane", "circle-exclamation"]
			},
				{
					title: "fas fa-plane-circle-xmark",
					searchTerms: ["plane", "circle-xmark"]
			},
				{
					title: "fas fa-plane-departure",
					searchTerms: ["plane", "departure"]
			},
				{
					title: "fas fa-plane-lock",
					searchTerms: ["plane", "lock"]
			},
				{
					title: "fas fa-plane-slash",
					searchTerms: ["plane", "slash"]
			},
				{
					title: "fas fa-plane-up",
					searchTerms: ["plane", "up"]
			},
				{
					title: "fas fa-plant-wilt",
					searchTerms: ["plant", "wilt"]
			},
				{
					title: "fas fa-plate-wheat",
					searchTerms: ["plate", "wheat"]
			},
				{
					title: "fas fa-play",
					searchTerms: ["play"]
			},
				{
					title: "fas fa-plug",
					searchTerms: ["plug"]
			},
				{
					title: "fas fa-plug-circle-bolt",
					searchTerms: ["plug", "circle-bolt"]
			},
				{
					title: "fas fa-plug-circle-check",
					searchTerms: ["plug", "circle-check"]
			},
				{
					title: "fas fa-plug-circle-exclamation",
					searchTerms: ["plug", "circle-exclamation"]
			},
				{
					title: "fas fa-plug-circle-minus",
					searchTerms: ["plug", "circle-minus"]
			},
				{
					title: "fas fa-plug-circle-plus",
					searchTerms: ["plug", "circle-plus"]
			},
				{
					title: "fas fa-plug-circle-xmark",
					searchTerms: ["plug", "circle-xmark"]
			},
				{
					title: "fas fa-plus",
					searchTerms: ["plus"]
			},
				{
					title: "fas fa-plus-minus",
					searchTerms: ["plus", "minus"]
			},
				{
					title: "fas fa-podcast",
					searchTerms: ["podcast"]
			},
				{
					title: "fas fa-poo",
					searchTerms: ["poo"]
			},
				{
					title: "fas fa-poo-storm",
					searchTerms: ["poo", "bolt"]
			},
				{
					title: "fas fa-poop",
					searchTerms: ["poop"]
			},
				{
					title: "fas fa-power-off",
					searchTerms: ["power", "off"]
			},
				{
					title: "fas fa-prescription",
					searchTerms: ["prescription"]
			},
				{
					title: "fas fa-prescription-bottle",
					searchTerms: ["prescription", "bottle"]
			},
				{
					title: "fas fa-prescription-bottle-medical",
					searchTerms: ["prescription", "bottle", "medical"]
			},
				{
					title: "fas fa-print",
					searchTerms: ["print"]
			},
				{
					title: "fas fa-pump-medical",
					searchTerms: ["pump", "medical"]
			},
				{
					title: "fas fa-pump-soap",
					searchTerms: ["pump", "soap"]
			},
				{
					title: "fas fa-puzzle-piece",
					searchTerms: ["puzzle", "piece"]
			},
				{
					title: "fas fa-q",
					searchTerms: ["q"]
			},
				{
					title: "fas fa-qrcode",
					searchTerms: ["qrcode"]
			},
				{
					title: "fas fa-question",
					searchTerms: ["question"]
			},
				{
					title: "fas fa-quote-left",
					searchTerms: ["quote-left"]
			},
				{
					title: "fas fa-quote-right",
					searchTerms: ["quote-right"]
			},
				{
					title: "fas fa-r",
					searchTerms: ["r"]
			},
				{
					title: "fas fa-radiation",
					searchTerms: ["radiation"]
			},
				{
					title: "fas fa-radio",
					searchTerms: ["radio"]
			},
				{
					title: "fas fa-rainbow",
					searchTerms: ["rainbow"]
			},
				{
					title: "fas fa-ranking-star",
					searchTerms: ["ranking", "star"]
			},
				{
					title: "fas fa-receipt",
					searchTerms: ["receipt"]
			},
				{
					title: "fas fa-record-vinyl",
					searchTerms: ["record", "vinyl"]
			},
				{
					title: "fas fa-rectangle-ad",
					searchTerms: ["rectangle", "ad"]
			},
				{
					title: "fas fa-rectangle-list",
					searchTerms: ["rectangle", "list"]
			},
				{
					title: "fas fa-rectangle-xmark",
					searchTerms: ["rectangle", "x", "mark"]
			},
				{
					title: "fas fa-recycle",
					searchTerms: ["recycle"]
			},
				{
					title: "fas fa-registered",
					searchTerms: ["registered", "trademark"]
			},
				{
					title: "fas fa-repeat",
					searchTerms: ["repeat"]
			},
				{
					title: "fas fa-reply",
					searchTerms: ["reply"]
			},
				{
					title: "fas fa-reply-all",
					searchTerms: ["reply", "all"]
			},
				{
					title: "fas fa-republican",
					searchTerms: ["republican"]
			},
				{
					title: "fas fa-restroom",
					searchTerms: ["restroom"]
			},
				{
					title: "fas fa-retweet",
					searchTerms: ["retweet"]
			},
				{
					title: "fas fa-ribbon",
					searchTerms: ["ribbon"]
			},
				{
					title: "fas fa-right-from-bracket",
					searchTerms: ["right", "from", "bracket"]
			},
				{
					title: "fas fa-right-left",
					searchTerms: ["right", "left"]
			},
				{
					title: "fas fa-right-long",
					searchTerms: ["right", "long"]
			},
				{
					title: "fas fa-right-to-bracket",
					searchTerms: ["right", "to", "bracket"]
			},
				{
					title: "fas fa-ring",
					searchTerms: ["ring"]
			},
				{
					title: "fas fa-road",
					searchTerms: ["road"]
			},
				{
					title: "fas fa-road-barrier",
					searchTerms: ["road", "barrier"]
			},
				{
					title: "fas fa-road-bridge",
					searchTerms: ["road", "bridge"]
			},
				{
					title: "fas fa-road-circle-check",
					searchTerms: ["road", "circle-check"]
			},
				{
					title: "fas fa-road-circle-exclamation",
					searchTerms: ["road", "circle-exclamation"]
			},
				{
					title: "fas fa-road-circle-xmark",
					searchTerms: ["road", "circle-xmark"]
			},
				{
					title: "fas fa-road-lock",
					searchTerms: ["road", "lock"]
			},
				{
					title: "fas fa-road-spikes",
					searchTerms: ["road", "spikes"]
			},
				{
					title: "fas fa-robot",
					searchTerms: ["robot"]
			},
				{
					title: "fas fa-rocket",
					searchTerms: ["rocket"]
			},
				{
					title: "fas fa-rotate",
					searchTerms: ["rotate"]
			},
				{
					title: "fas fa-rotate-left",
					searchTerms: ["rotate", "left"]
			},
				{
					title: "fas fa-rotate-right",
					searchTerms: ["rotate", "right"]
			},
				{
					title: "fas fa-route",
					searchTerms: ["route"]
			},
				{
					title: "fas fa-rss",
					searchTerms: ["rss"]
			},
				{
					title: "fas fa-ruble-sign",
					searchTerms: ["ruble", "sign"]
			},
				{
					title: "fas fa-rug",
					searchTerms: ["rug"]
			},
				{
					title: "fas fa-ruler",
					searchTerms: ["ruler"]
			},
				{
					title: "fas fa-ruler-combined",
					searchTerms: ["ruler", "combined"]
			},
				{
					title: "fas fa-ruler-horizontal",
					searchTerms: ["ruler", "horizontal"]
			},
				{
					title: "fas fa-ruler-vertical",
					searchTerms: ["ruler", "vertical"]
			},
				{
					title: "fas fa-rupee-sign",
					searchTerms: ["indian", "rupee", "sign"]
			},
				{
					title: "fas fa-rupiah-sign",
					searchTerms: ["rupiah", "sign"]
			},
				{
					title: "fas fa-s",
					searchTerms: ["s"]
			},
				{
					title: "fas fa-sack-dollar",
					searchTerms: ["sack", "of", "money"]
			},
				{
					title: "fas fa-sack-xmark",
					searchTerms: ["sack", "xmark"]
			},
				{
					title: "fas fa-sailboat",
					searchTerms: ["sailboat"]
			},
				{
					title: "fas fa-satellite",
					searchTerms: ["satellite"]
			},
				{
					title: "fas fa-satellite-dish",
					searchTerms: ["satellite", "dish"]
			},
				{
					title: "fas fa-scale-balanced",
					searchTerms: ["scale", "balanced"]
			},
				{
					title: "fas fa-scale-unbalanced",
					searchTerms: ["scale", "unbalanced"]
			},
				{
					title: "fas fa-scale-unbalanced-flip",
					searchTerms: ["scale", "unbalanced", "flip"]
			},
				{
					title: "fas fa-school",
					searchTerms: ["school"]
			},
				{
					title: "fas fa-school-circle-check",
					searchTerms: ["school", "circle", "check"]
			},
				{
					title: "fas fa-school-circle-exclamation",
					searchTerms: ["school", "circle", "exclamation"]
			},
				{
					title: "fas fa-school-circle-xmark",
					searchTerms: ["school", "circle", "xmark"]
			},
				{
					title: "fas fa-school-flag",
					searchTerms: ["school", "flag"]
			},
				{
					title: "fas fa-school-lock",
					searchTerms: ["school", "lock"]
			},
				{
					title: "fas fa-scissors",
					searchTerms: ["scissors"]
			},
				{
					title: "fas fa-screwdriver",
					searchTerms: ["screwdriver"]
			},
				{
					title: "fas fa-screwdriver-wrench",
					searchTerms: ["screwdriver", "wrench"]
			},
				{
					title: "fas fa-scroll",
					searchTerms: ["scroll"]
			},
				{
					title: "fas fa-scroll-torah",
					searchTerms: ["scroll", "torah"]
			},
				{
					title: "fas fa-sd-card",
					searchTerms: ["sd", "card"]
			},
				{
					title: "fas fa-section",
					searchTerms: ["section"]
			},
				{
					title: "fas fa-seedling",
					searchTerms: ["seedling"]
			},
				{
					title: "fas fa-server",
					searchTerms: ["server"]
			},
				{
					title: "fas fa-shapes",
					searchTerms: ["shapes"]
			},
				{
					title: "fas fa-share",
					searchTerms: ["share"]
			},
				{
					title: "fas fa-share-from-square",
					searchTerms: ["share", "from", "square"]
			},
				{
					title: "fas fa-share-nodes",
					searchTerms: ["share", "nodes"]
			},
				{
					title: "fas fa-sheet-plastic",
					searchTerms: ["sheet", "plastic"]
			},
				{
					title: "fas fa-shekel-sign",
					searchTerms: ["shekel", "sign"]
			},
				{
					title: "fas fa-shield",
					searchTerms: ["shield"]
			},
				{
					title: "fas fa-shield-cat",
					searchTerms: ["shield", "cat"]
			},
				{
					title: "fas fa-shield-dog",
					searchTerms: ["shield", "dog"]
			},
				{
					title: "fas fa-shield-halved",
					searchTerms: ["shield", "halved"]
			},
				{
					title: "fas fa-shield-heart",
					searchTerms: ["shield", "heart"]
			},
				{
					title: "fas fa-shield-virus",
					searchTerms: ["shield", "virus"]
			},
				{
					title: "fas fa-ship",
					searchTerms: ["ship"]
			},
				{
					title: "fas fa-shirt",
					searchTerms: ["t-shirt"]
			},
				{
					title: "fas fa-shoe-prints",
					searchTerms: ["shoe", "prints"]
			},
				{
					title: "fas fa-shop",
					searchTerms: ["shop"]
			},
				{
					title: "fas fa-shop-lock",
					searchTerms: ["shop", "lock"]
			},
				{
					title: "fas fa-shop-slash",
					searchTerms: ["shop", "slash"]
			},
				{
					title: "fas fa-shower",
					searchTerms: ["shower"]
			},
				{
					title: "fas fa-shrimp",
					searchTerms: ["shrimp"]
			},
				{
					title: "fas fa-shuffle",
					searchTerms: ["shuffle"]
			},
				{
					title: "fas fa-shuttle-space",
					searchTerms: ["shuttle", "space"]
			},
				{
					title: "fas fa-sign-hanging",
					searchTerms: ["sign", "hanging"]
			},
				{
					title: "fas fa-signal",
					searchTerms: ["signal"]
			},
				{
					title: "fas fa-signature",
					searchTerms: ["signature"]
			},
				{
					title: "fas fa-signs-post",
					searchTerms: ["signs", "post"]
			},
				{
					title: "fas fa-sim-card",
					searchTerms: ["sim", "card"]
			},
				{
					title: "fas fa-sink",
					searchTerms: ["sink"]
			},
				{
					title: "fas fa-sitemap",
					searchTerms: ["sitemap"]
			},
				{
					title: "fas fa-skull",
					searchTerms: ["skull"]
			},
				{
					title: "fas fa-skull-crossbones",
					searchTerms: ["skull", "&", "crossbones"]
			},
				{
					title: "fas fa-slash",
					searchTerms: ["slash"]
			},
				{
					title: "fas fa-sleigh",
					searchTerms: ["sleigh"]
			},
				{
					title: "fas fa-sliders",
					searchTerms: ["sliders"]
			},
				{
					title: "fas fa-smog",
					searchTerms: ["smog"]
			},
				{
					title: "fas fa-smoking",
					searchTerms: ["smoking"]
			},
				{
					title: "fas fa-snowflake",
					searchTerms: ["snowflake"]
			},
				{
					title: "fas fa-snowman",
					searchTerms: ["snowman"]
			},
				{
					title: "fas fa-snowplow",
					searchTerms: ["snowplow"]
			},
				{
					title: "fas fa-soap",
					searchTerms: ["soap"]
			},
				{
					title: "fas fa-socks",
					searchTerms: ["socks"]
			},
				{
					title: "fas fa-solar-panel",
					searchTerms: ["solar", "panel"]
			},
				{
					title: "fas fa-sort",
					searchTerms: ["sort"]
			},
				{
					title: "fas fa-sort-down",
					searchTerms: ["sort", "down", "(descending)"]
			},
				{
					title: "fas fa-sort-up",
					searchTerms: ["sort", "up", "(ascending)"]
			},
				{
					title: "fas fa-spa",
					searchTerms: ["spa"]
			},
				{
					title: "fas fa-spaghetti-monster-flying",
					searchTerms: ["spaghetti", "monster", "flying"]
			},
				{
					title: "fas fa-spell-check",
					searchTerms: ["spell", "check"]
			},
				{
					title: "fas fa-spider",
					searchTerms: ["spider"]
			},
				{
					title: "fas fa-spinner",
					searchTerms: ["spinner"]
			},
				{
					title: "fas fa-splotch",
					searchTerms: ["splotch"]
			},
				{
					title: "fas fa-spoon",
					searchTerms: ["spoon"]
			},
				{
					title: "fas fa-spray-can",
					searchTerms: ["spray", "can"]
			},
				{
					title: "fas fa-spray-can-sparkles",
					searchTerms: ["spray", "can", "sparkles"]
			},
				{
					title: "fas fa-square",
					searchTerms: ["square"]
			},
				{
					title: "fas fa-square-arrow-up-right",
					searchTerms: ["square", "arrow", "up", "right"]
			},
				{
					title: "fas fa-square-caret-down",
					searchTerms: ["square", "caret", "down"]
			},
				{
					title: "fas fa-square-caret-left",
					searchTerms: ["square", "caret", "left"]
			},
				{
					title: "fas fa-square-caret-right",
					searchTerms: ["square", "caret", "right"]
			},
				{
					title: "fas fa-square-caret-up",
					searchTerms: ["square", "caret", "up"]
			},
				{
					title: "fas fa-square-check",
					searchTerms: ["square", "check"]
			},
				{
					title: "fas fa-square-envelope",
					searchTerms: ["square", "envelope"]
			},
				{
					title: "fas fa-square-full",
					searchTerms: ["square", "full"]
			},
				{
					title: "fas fa-square-h",
					searchTerms: ["square", "h"]
			},
				{
					title: "fas fa-square-minus",
					searchTerms: ["square", "minus"]
			},
				{
					title: "fas fa-square-nfi",
					searchTerms: ["square", "nfi"]
			},
				{
					title: "fas fa-square-parking",
					searchTerms: ["square", "parking"]
			},
				{
					title: "fas fa-square-pen",
					searchTerms: ["square", "pen"]
			},
				{
					title: "fas fa-square-person-confined",
					searchTerms: ["square", "person-confined"]
			},
				{
					title: "fas fa-square-phone",
					searchTerms: ["square", "phone"]
			},
				{
					title: "fas fa-square-phone-flip",
					searchTerms: ["square", "phone", "flip"]
			},
				{
					title: "fas fa-square-plus",
					searchTerms: ["square", "plus"]
			},
				{
					title: "fas fa-square-poll-horizontal",
					searchTerms: ["square", "poll", "horizontal"]
			},
				{
					title: "fas fa-square-poll-vertical",
					searchTerms: ["square", "poll", "vertical"]
			},
				{
					title: "fas fa-square-root-variable",
					searchTerms: ["square", "root", "variable"]
			},
				{
					title: "fas fa-square-rss",
					searchTerms: ["square", "rss"]
			},
				{
					title: "fas fa-square-share-nodes",
					searchTerms: ["square", "share", "nodes"]
			},
				{
					title: "fas fa-square-up-right",
					searchTerms: ["square", "up", "right"]
			},
				{
					title: "fas fa-square-virus",
					searchTerms: ["square", "virus"]
			},
				{
					title: "fas fa-square-xmark",
					searchTerms: ["square", "x", "mark"]
			},
				{
					title: "fas fa-staff-snake",
					searchTerms: ["staff", "aesculapius"]
			},
				{
					title: "fas fa-stairs",
					searchTerms: ["stairs"]
			},
				{
					title: "fas fa-stamp",
					searchTerms: ["stamp"]
			},
				{
					title: "fas fa-stapler",
					searchTerms: ["stapler"]
			},
				{
					title: "fas fa-star",
					searchTerms: ["star"]
			},
				{
					title: "fas fa-star-and-crescent",
					searchTerms: ["star", "and", "crescent"]
			},
				{
					title: "fas fa-star-half",
					searchTerms: ["star-half"]
			},
				{
					title: "fas fa-star-half-stroke",
					searchTerms: ["star", "half", "stroke"]
			},
				{
					title: "fas fa-star-of-david",
					searchTerms: ["star", "of", "david"]
			},
				{
					title: "fas fa-star-of-life",
					searchTerms: ["star", "of", "life"]
			},
				{
					title: "fas fa-sterling-sign",
					searchTerms: ["sterling", "sign"]
			},
				{
					title: "fas fa-stethoscope",
					searchTerms: ["stethoscope"]
			},
				{
					title: "fas fa-stop",
					searchTerms: ["stop"]
			},
				{
					title: "fas fa-stopwatch",
					searchTerms: ["stopwatch"]
			},
				{
					title: "fas fa-stopwatch-20",
					searchTerms: ["stopwatch", "20"]
			},
				{
					title: "fas fa-store",
					searchTerms: ["store"]
			},
				{
					title: "fas fa-store-slash",
					searchTerms: ["store", "slash"]
			},
				{
					title: "fas fa-street-view",
					searchTerms: ["street", "view"]
			},
				{
					title: "fas fa-strikethrough",
					searchTerms: ["strikethrough"]
			},
				{
					title: "fas fa-stroopwafel",
					searchTerms: ["stroopwafel"]
			},
				{
					title: "fas fa-subscript",
					searchTerms: ["subscript"]
			},
				{
					title: "fas fa-suitcase",
					searchTerms: ["suitcase"]
			},
				{
					title: "fas fa-suitcase-medical",
					searchTerms: ["suitcase", "medical"]
			},
				{
					title: "fas fa-suitcase-rolling",
					searchTerms: ["suitcase", "rolling"]
			},
				{
					title: "fas fa-sun",
					searchTerms: ["sun"]
			},
				{
					title: "fas fa-sun-plant-wilt",
					searchTerms: ["sun", "plant-wilt"]
			},
				{
					title: "fas fa-superscript",
					searchTerms: ["superscript"]
			},
				{
					title: "fas fa-swatchbook",
					searchTerms: ["swatchbook"]
			},
				{
					title: "fas fa-synagogue",
					searchTerms: ["synagogue"]
			},
				{
					title: "fas fa-syringe",
					searchTerms: ["syringe"]
			},
				{
					title: "fas fa-t",
					searchTerms: ["t"]
			},
				{
					title: "fas fa-table",
					searchTerms: ["table"]
			},
				{
					title: "fas fa-table-cells",
					searchTerms: ["table", "cells"]
			},
				{
					title: "fas fa-table-cells-large",
					searchTerms: ["table", "cells", "large"]
			},
				{
					title: "fas fa-table-columns",
					searchTerms: ["table", "columns"]
			},
				{
					title: "fas fa-table-list",
					searchTerms: ["table", "list"]
			},
				{
					title: "fas fa-table-tennis-paddle-ball",
					searchTerms: ["table", "tennis", "paddle", "ball"]
			},
				{
					title: "fas fa-tablet",
					searchTerms: ["tablet"]
			},
				{
					title: "fas fa-tablet-button",
					searchTerms: ["tablet", "button"]
			},
				{
					title: "fas fa-tablet-screen-button",
					searchTerms: ["tablet", "screen", "button"]
			},
				{
					title: "fas fa-tablets",
					searchTerms: ["tablets"]
			},
				{
					title: "fas fa-tachograph-digital",
					searchTerms: ["tachograph", "digital"]
			},
				{
					title: "fas fa-tag",
					searchTerms: ["tag"]
			},
				{
					title: "fas fa-tags",
					searchTerms: ["tags"]
			},
				{
					title: "fas fa-tape",
					searchTerms: ["tape"]
			},
				{
					title: "fas fa-tarp",
					searchTerms: ["tarp"]
			},
				{
					title: "fas fa-tarp-droplet",
					searchTerms: ["tarp", "droplet"]
			},
				{
					title: "fas fa-taxi",
					searchTerms: ["taxi"]
			},
				{
					title: "fas fa-teeth",
					searchTerms: ["teeth"]
			},
				{
					title: "fas fa-teeth-open",
					searchTerms: ["teeth", "open"]
			},
				{
					title: "fas fa-temperature-arrow-down",
					searchTerms: ["temperature", "arrow", "down"]
			},
				{
					title: "fas fa-temperature-arrow-up",
					searchTerms: ["temperature", "arrow", "up"]
			},
				{
					title: "fas fa-temperature-empty",
					searchTerms: ["temperature", "empty"]
			},
				{
					title: "fas fa-temperature-full",
					searchTerms: ["temperature", "full"]
			},
				{
					title: "fas fa-temperature-half",
					searchTerms: ["temperature", "half"]
			},
				{
					title: "fas fa-temperature-high",
					searchTerms: ["high", "temperature"]
			},
				{
					title: "fas fa-temperature-low",
					searchTerms: ["low", "temperature"]
			},
				{
					title: "fas fa-temperature-quarter",
					searchTerms: ["temperature", "quarter"]
			},
				{
					title: "fas fa-temperature-three-quarters",
					searchTerms: ["temperature", "three", "quarters"]
			},
				{
					title: "fas fa-tenge-sign",
					searchTerms: ["tenge", "sign"]
			},
				{
					title: "fas fa-tent",
					searchTerms: ["tent"]
			},
				{
					title: "fas fa-tent-arrow-down-to-line",
					searchTerms: ["tent", "arrow-down-to-line"]
			},
				{
					title: "fas fa-tent-arrow-left-right",
					searchTerms: ["tent", "arrow-left-right"]
			},
				{
					title: "fas fa-tent-arrow-turn-left",
					searchTerms: ["tent", "arrow-turn-left"]
			},
				{
					title: "fas fa-tent-arrows-down",
					searchTerms: ["tent", "arrows-down"]
			},
				{
					title: "fas fa-tents",
					searchTerms: ["tents"]
			},
				{
					title: "fas fa-terminal",
					searchTerms: ["terminal"]
			},
				{
					title: "fas fa-text-height",
					searchTerms: ["text-height"]
			},
				{
					title: "fas fa-text-slash",
					searchTerms: ["text", "slash"]
			},
				{
					title: "fas fa-text-width",
					searchTerms: ["text", "width"]
			},
				{
					title: "fas fa-thermometer",
					searchTerms: ["thermometer"]
			},
				{
					title: "fas fa-thumbs-down",
					searchTerms: ["thumbs-down"]
			},
				{
					title: "fas fa-thumbs-up",
					searchTerms: ["thumbs-up"]
			},
				{
					title: "fas fa-thumbtack",
					searchTerms: ["thumbtack"]
			},
				{
					title: "fas fa-ticket",
					searchTerms: ["ticket"]
			},
				{
					title: "fas fa-ticket-simple",
					searchTerms: ["ticket", "simple"]
			},
				{
					title: "fas fa-timeline",
					searchTerms: ["timeline"]
			},
				{
					title: "fas fa-toggle-off",
					searchTerms: ["toggle", "off"]
			},
				{
					title: "fas fa-toggle-on",
					searchTerms: ["toggle", "on"]
			},
				{
					title: "fas fa-toilet",
					searchTerms: ["toilet"]
			},
				{
					title: "fas fa-toilet-paper",
					searchTerms: ["toilet", "paper"]
			},
				{
					title: "fas fa-toilet-paper-slash",
					searchTerms: ["toilet", "paper", "slash"]
			},
				{
					title: "fas fa-toilet-portable",
					searchTerms: ["toilet", "portable"]
			},
				{
					title: "fas fa-toilets-portable",
					searchTerms: ["toilets", "portable"]
			},
				{
					title: "fas fa-toolbox",
					searchTerms: ["toolbox"]
			},
				{
					title: "fas fa-tooth",
					searchTerms: ["tooth"]
			},
				{
					title: "fas fa-torii-gate",
					searchTerms: ["torii", "gate"]
			},
				{
					title: "fas fa-tornado",
					searchTerms: ["tornado"]
			},
				{
					title: "fas fa-tower-broadcast",
					searchTerms: ["tower", "broadcast"]
			},
				{
					title: "fas fa-tower-cell",
					searchTerms: ["tower", "cell"]
			},
				{
					title: "fas fa-tower-observation",
					searchTerms: ["tower", "observation"]
			},
				{
					title: "fas fa-tractor",
					searchTerms: ["tractor"]
			},
				{
					title: "fas fa-trademark",
					searchTerms: ["trademark"]
			},
				{
					title: "fas fa-traffic-light",
					searchTerms: ["traffic", "light"]
			},
				{
					title: "fas fa-trailer",
					searchTerms: ["trailer"]
			},
				{
					title: "fas fa-train",
					searchTerms: ["train"]
			},
				{
					title: "fas fa-train-subway",
					searchTerms: ["train", "subway"]
			},
				{
					title: "fas fa-train-tram",
					searchTerms: ["train", "tram"]
			},
				{
					title: "fas fa-transgender",
					searchTerms: ["transgender"]
			},
				{
					title: "fas fa-trash",
					searchTerms: ["trash"]
			},
				{
					title: "fas fa-trash-arrow-up",
					searchTerms: ["trash", "arrow", "up"]
			},
				{
					title: "fas fa-trash-can",
					searchTerms: ["trash", "can"]
			},
				{
					title: "fas fa-trash-can-arrow-up",
					searchTerms: ["trash", "can", "arrow", "up"]
			},
				{
					title: "fas fa-tree",
					searchTerms: ["tree"]
			},
				{
					title: "fas fa-tree-city",
					searchTerms: ["tree", "city"]
			},
				{
					title: "fas fa-triangle-exclamation",
					searchTerms: ["triangle", "exclamation"]
			},
				{
					title: "fas fa-trophy",
					searchTerms: ["trophy"]
			},
				{
					title: "fas fa-trowel",
					searchTerms: ["trowel"]
			},
				{
					title: "fas fa-trowel-bricks",
					searchTerms: ["trowel", "bricks"]
			},
				{
					title: "fas fa-truck",
					searchTerms: ["truck"]
			},
				{
					title: "fas fa-truck-arrow-right",
					searchTerms: ["truck", "arrow-right"]
			},
				{
					title: "fas fa-truck-droplet",
					searchTerms: ["truck", "droplet"]
			},
				{
					title: "fas fa-truck-fast",
					searchTerms: ["truck", "fast"]
			},
				{
					title: "fas fa-truck-field",
					searchTerms: ["truck", "field"]
			},
				{
					title: "fas fa-truck-field-un",
					searchTerms: ["truck", "field-un"]
			},
				{
					title: "fas fa-truck-front",
					searchTerms: ["truck", "front"]
			},
				{
					title: "fas fa-truck-medical",
					searchTerms: ["truck", "medical"]
			},
				{
					title: "fas fa-truck-monster",
					searchTerms: ["truck", "monster"]
			},
				{
					title: "fas fa-truck-moving",
					searchTerms: ["truck", "moving"]
			},
				{
					title: "fas fa-truck-pickup",
					searchTerms: ["truck", "side"]
			},
				{
					title: "fas fa-truck-plane",
					searchTerms: ["truck", "plane"]
			},
				{
					title: "fas fa-truck-ramp-box",
					searchTerms: ["truck", "ramp", "box"]
			},
				{
					title: "fas fa-tty",
					searchTerms: ["tty"]
			},
				{
					title: "fas fa-turkish-lira-sign",
					searchTerms: ["turkish", "lira-sign"]
			},
				{
					title: "fas fa-turn-down",
					searchTerms: ["turn", "down"]
			},
				{
					title: "fas fa-turn-up",
					searchTerms: ["turn", "up"]
			},
				{
					title: "fas fa-tv",
					searchTerms: ["television"]
			},
				{
					title: "fas fa-u",
					searchTerms: ["u"]
			},
				{
					title: "fas fa-umbrella",
					searchTerms: ["umbrella"]
			},
				{
					title: "fas fa-umbrella-beach",
					searchTerms: ["umbrella", "beach"]
			},
				{
					title: "fas fa-underline",
					searchTerms: ["underline"]
			},
				{
					title: "fas fa-universal-access",
					searchTerms: ["universal", "access"]
			},
				{
					title: "fas fa-unlock",
					searchTerms: ["unlock"]
			},
				{
					title: "fas fa-unlock-keyhole",
					searchTerms: ["unlock", "keyhole"]
			},
				{
					title: "fas fa-up-down",
					searchTerms: ["up", "down"]
			},
				{
					title: "fas fa-up-down-left-right",
					searchTerms: ["up", "down", "left", "right"]
			},
				{
					title: "fas fa-up-long",
					searchTerms: ["up", "long"]
			},
				{
					title: "fas fa-up-right-and-down-left-from-center",
					searchTerms: ["up", "right", "and", "down", "left", "from", "center"]
			},
				{
					title: "fas fa-up-right-from-square",
					searchTerms: ["up", "right", "from", "square"]
			},
				{
					title: "fas fa-upload",
					searchTerms: ["upload"]
			},
				{
					title: "fas fa-user",
					searchTerms: ["user"]
			},
				{
					title: "fas fa-user-astronaut",
					searchTerms: ["user", "astronaut"]
			},
				{
					title: "fas fa-user-check",
					searchTerms: ["user", "check"]
			},
				{
					title: "fas fa-user-clock",
					searchTerms: ["user", "clock"]
			},
				{
					title: "fas fa-user-doctor",
					searchTerms: ["user", "doctor"]
			},
				{
					title: "fas fa-user-gear",
					searchTerms: ["user", "gear"]
			},
				{
					title: "fas fa-user-graduate",
					searchTerms: ["user", "graduate"]
			},
				{
					title: "fas fa-user-group",
					searchTerms: ["user", "group"]
			},
				{
					title: "fas fa-user-injured",
					searchTerms: ["user", "injured"]
			},
				{
					title: "fas fa-user-large",
					searchTerms: ["user", "large"]
			},
				{
					title: "fas fa-user-large-slash",
					searchTerms: ["user", "large", "slash"]
			},
				{
					title: "fas fa-user-lock",
					searchTerms: ["user", "lock"]
			},
				{
					title: "fas fa-user-minus",
					searchTerms: ["user", "minus"]
			},
				{
					title: "fas fa-user-ninja",
					searchTerms: ["user", "ninja"]
			},
				{
					title: "fas fa-user-nurse",
					searchTerms: ["nurse"]
			},
				{
					title: "fas fa-user-pen",
					searchTerms: ["user", "pen"]
			},
				{
					title: "fas fa-user-plus",
					searchTerms: ["user", "plus"]
			},
				{
					title: "fas fa-user-secret",
					searchTerms: ["user", "secret"]
			},
				{
					title: "fas fa-user-shield",
					searchTerms: ["user", "shield"]
			},
				{
					title: "fas fa-user-slash",
					searchTerms: ["user", "slash"]
			},
				{
					title: "fas fa-user-tag",
					searchTerms: ["user", "tag"]
			},
				{
					title: "fas fa-user-tie",
					searchTerms: ["user", "tie"]
			},
				{
					title: "fas fa-user-xmark",
					searchTerms: ["user", "x", "mark"]
			},
				{
					title: "fas fa-users",
					searchTerms: ["users"]
			},
				{
					title: "fas fa-users-between-lines",
					searchTerms: ["users", "between-lines"]
			},
				{
					title: "fas fa-users-gear",
					searchTerms: ["users", "gear"]
			},
				{
					title: "fas fa-users-line",
					searchTerms: ["users", "line"]
			},
				{
					title: "fas fa-users-rays",
					searchTerms: ["users", "rays"]
			},
				{
					title: "fas fa-users-rectangle",
					searchTerms: ["users", "rectangle"]
			},
				{
					title: "fas fa-users-slash",
					searchTerms: ["users", "slash"]
			},
				{
					title: "fas fa-users-viewfinder",
					searchTerms: ["users", "viewfinder"]
			},
				{
					title: "fas fa-utensils",
					searchTerms: ["utensils"]
			},
				{
					title: "fas fa-v",
					searchTerms: ["v"]
			},
				{
					title: "fas fa-van-shuttle",
					searchTerms: ["van", "shuttle"]
			},
				{
					title: "fas fa-vault",
					searchTerms: ["vault"]
			},
				{
					title: "fas fa-vector-square",
					searchTerms: ["vector", "square"]
			},
				{
					title: "fas fa-venus",
					searchTerms: ["venus"]
			},
				{
					title: "fas fa-venus-double",
					searchTerms: ["venus", "double"]
			},
				{
					title: "fas fa-venus-mars",
					searchTerms: ["venus", "mars"]
			},
				{
					title: "fas fa-vest",
					searchTerms: ["vest"]
			},
				{
					title: "fas fa-vest-patches",
					searchTerms: ["vest-patches"]
			},
				{
					title: "fas fa-vial",
					searchTerms: ["vial"]
			},
				{
					title: "fas fa-vial-circle-check",
					searchTerms: ["vial", "circle-check"]
			},
				{
					title: "fas fa-vial-virus",
					searchTerms: ["vial", "virus"]
			},
				{
					title: "fas fa-vials",
					searchTerms: ["vials"]
			},
				{
					title: "fas fa-video",
					searchTerms: ["video"]
			},
				{
					title: "fas fa-video-slash",
					searchTerms: ["video", "slash"]
			},
				{
					title: "fas fa-vihara",
					searchTerms: ["vihara"]
			},
				{
					title: "fas fa-virus",
					searchTerms: ["virus"]
			},
				{
					title: "fas fa-virus-covid",
					searchTerms: ["virus", "covid"]
			},
				{
					title: "fas fa-virus-covid-slash",
					searchTerms: ["virus", "covid-slash"]
			},
				{
					title: "fas fa-virus-slash",
					searchTerms: ["virus", "slash"]
			},
				{
					title: "fas fa-viruses",
					searchTerms: ["viruses"]
			},
				{
					title: "fas fa-voicemail",
					searchTerms: ["voicemail"]
			},
				{
					title: "fas fa-volcano",
					searchTerms: ["volcano"]
			},
				{
					title: "fas fa-volleyball",
					searchTerms: ["volleyball", "ball"]
			},
				{
					title: "fas fa-volume-high",
					searchTerms: ["volume", "high"]
			},
				{
					title: "fas fa-volume-low",
					searchTerms: ["volume", "low"]
			},
				{
					title: "fas fa-volume-off",
					searchTerms: ["volume", "off"]
			},
				{
					title: "fas fa-volume-xmark",
					searchTerms: ["volume", "x", "mark"]
			},
				{
					title: "fas fa-vr-cardboard",
					searchTerms: ["cardboard", "vr"]
			},
				{
					title: "fas fa-w",
					searchTerms: ["w"]
			},
				{
					title: "fas fa-walkie-talkie",
					searchTerms: ["walkie", "talkie"]
			},
				{
					title: "fas fa-wallet",
					searchTerms: ["wallet"]
			},
				{
					title: "fas fa-wand-magic",
					searchTerms: ["wand", "magic"]
			},
				{
					title: "fas fa-wand-magic-sparkles",
					searchTerms: ["wand", "magic", "sparkles"]
			},
				{
					title: "fas fa-wand-sparkles",
					searchTerms: ["wand", "sparkles"]
			},
				{
					title: "fas fa-warehouse",
					searchTerms: ["warehouse"]
			},
				{
					title: "fas fa-water",
					searchTerms: ["water"]
			},
				{
					title: "fas fa-water-ladder",
					searchTerms: ["water", "ladder"]
			},
				{
					title: "fas fa-wave-square",
					searchTerms: ["square", "wave"]
			},
				{
					title: "fas fa-weight-hanging",
					searchTerms: ["hanging", "weight"]
			},
				{
					title: "fas fa-weight-scale",
					searchTerms: ["weight", "scale"]
			},
				{
					title: "fas fa-wheat-awn",
					searchTerms: ["wheat", "awn"]
			},
				{
					title: "fas fa-wheat-awn-circle-exclamation",
					searchTerms: ["wheat", "awn-circle-exclamation"]
			},
				{
					title: "fas fa-wheelchair",
					searchTerms: ["wheelchair"]
			},
				{
					title: "fas fa-wheelchair-move",
					searchTerms: ["wheelchair", "move"]
			},
				{
					title: "fas fa-whiskey-glass",
					searchTerms: ["whiskey", "glass"]
			},
				{
					title: "fas fa-wifi",
					searchTerms: ["wifi"]
			},
				{
					title: "fas fa-wind",
					searchTerms: ["wind"]
			},
				{
					title: "fas fa-window-maximize",
					searchTerms: ["window", "maximize"]
			},
				{
					title: "fas fa-window-minimize",
					searchTerms: ["window", "minimize"]
			},
				{
					title: "fas fa-window-restore",
					searchTerms: ["window", "restore"]
			},
				{
					title: "fas fa-wine-bottle",
					searchTerms: ["wine", "bottle"]
			},
				{
					title: "fas fa-wine-glass",
					searchTerms: ["wine", "glass"]
			},
				{
					title: "fas fa-wine-glass-empty",
					searchTerms: ["wine", "glass", "empty"]
			},
				{
					title: "fas fa-won-sign",
					searchTerms: ["won", "sign"]
			},
				{
					title: "fas fa-worm",
					searchTerms: ["worm"]
			},
				{
					title: "fas fa-wrench",
					searchTerms: ["wrench"]
			},
				{
					title: "fas fa-x",
					searchTerms: ["x"]
			},
				{
					title: "fas fa-x-ray",
					searchTerms: ["x-ray"]
			},
				{
					title: "fas fa-xmark",
					searchTerms: ["x", "mark"]
			},
				{
					title: "fas fa-xmarks-lines",
					searchTerms: ["xmarks", "lines"]
			},
				{
					title: "fas fa-y",
					searchTerms: ["y"]
			},
				{
					title: "fas fa-yen-sign",
					searchTerms: ["yen", "sign"]
			},
				{
					title: "fas fa-yin-yang",
					searchTerms: ["yin", "yang"]
			},
				{
					title: "fas fa-z",
					searchTerms: ["z"]
			},
		]
		});
}