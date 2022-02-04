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

 // Onsubmit validation for the import/upload GEDCOM form
 function checkGedcomImportForm(message) {
 	var old_file = jQuery("#gedcom_filename").val();
 	var method   = jQuery("input[name=action]:checked").val();
 	var new_file = method === "replace_import" ? jQuery("#import-server-file").val() : jQuery("#import-computer-file").val();

 	// Some browsers include c:\fakepath\ in the filename.
 	new_file = new_file.replace(/.*[\/\\]/, '');
 	if (new_file !== old_file && old_file !== '') {
 		return confirm(message);
 	} else {
 		return true;
 	}
 }
