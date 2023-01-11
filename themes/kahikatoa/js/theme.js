/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * initialisation of CKEditor 5 with basic toolbar
 * 
 * 
*/
function ckeditorBasic() {
	ClassicEditor
	.create( document.querySelector(".html-edit"), {
		licenseKey: "",
		toolbar: [
			"heading", "|",
			"alignment:left", "alignment:right", "alignment:center", "|",
			"bold", "italic", "|",
			"undo", "redo"
		],
	})
	.then( editor => {
		window.editor = editor;
	});

}

/**
 * Initialisation of CKEditor 5 with standard toolbar
 * Ver 35.4.0
 * Build id: "u3ttwnnue5fa-mbpmrp5gjfnq"
*/
function ckeditorStandard() {
	ClassicEditor
	.create( document.querySelector(".html-edit"), {
		licenseKey: "",
		fontColor: {
			colors: [
				{label: "Red",			color: "#ab3334"},
				{label: "White",		color: "#faf5e8"},
				{label: "Green",		color: "#3adb76"},
				{label: "Yellow",		color: "#ffae00"},
				{label: "Light grey",	color: "#e0e0e0"},
				{label: "Medium grey",	color: "#cacaca"},
				{label: "Dark grey",	color: "#8a8a8a"},
				{label: "Black",		color: "#331919"},
				{label: "Blue",			color: "#0193b7"},
				{label: "Pink",			color: "#e09798"}
			]
		},
		fontBackgroundColor: {
			colors: [
				{label: "Red",			color: "#ab3334"},
				{label: "White",		color: "#faf5e8"},
				{label: "Green",		color: "#3adb76"},
				{label: "Yellow",		color: "#ffae00"},
				{label: "Light grey",	color: "#e0e0e0"},
				{label: "Medium grey",	color: "#cacaca"},
				{label: "Dark grey",	color: "#8a8a8a"},
				{label: "Black",		color: "#331919"},
				{label: "Blue",			color: "#0193b7"},
				{label: "Pink",			color: "#e09798"}
			]
		},
		fontSize: {
            options: [8, 10, 12, 14, "default", 18, 20, 22]
        },
        heading: {
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

//       toolbar: {
//		    removeItems: [ "fontColor", "fontBackgroundColor" ],
//		}
//	})
//	.then( editor => {
//        console.log( Array.from( editor.ui.componentFactory.names() ) );

    })
	.then( editor => {
		window.editor = editor;
	});

}
