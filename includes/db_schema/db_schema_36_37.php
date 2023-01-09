<?php
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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Add new site setting
try {
	self::exec(
		"INSERT IGNORE INTO `##site_setting` (setting_name, setting_value) VALUES ".
		"('BLOCKED_EMAIL_ADDRESS_LIST', 'youremail@gmail.com')"
	);
} catch (PDOException $ex) {
	// Perhaps we have already added this data?
}

// add widgets to module table
try {
	self::exec("ALTER TABLE `##module` ADD COLUMN footer_order INTEGER NULL");
} catch (PDOException $ex) {
	// If this fails, it has probably already been done.
}

self::exec("ALTER TABLE `##block` CHANGE location location ENUM('main', 'side', 'footer')");

// Add the initial default footer block settings
self::exec("INSERT IGNORE INTO `##block` (gedcom_id, location, block_order, module_name) VALUES (-1, 'footer', 1, 'footer_contacts'), (-1, 'footer', 2, 'footer_html'), (-1, 'footer', 3, 'footer_logo')");

// Rename tab to tabi and add tabf
try {
	self::exec("ALTER TABLE `##module` CHANGE `tab_order` `tabi_order` INTEGER NULL;");
} catch (PDOException $ex) {
	// If this fails, it has probably already been done.
}
try {
	self::exec("ALTER TABLE `##module` ADD COLUMN `tabf_order` INTEGER NULL;");
} catch (PDOException $ex) {
	// If this fails, it has probably already been done.
}
try {
	self::exec("DELETE FROM `##module_privacy` WHERE `component` = 'tab';");
} catch (PDOException $ex) {
	// If this fails, it has probably already been done.
}
try {
	self::exec("ALTER TABLE `##module_privacy` CHANGE component component ENUM('block', 'chart', 'footer', 'list', 'menu', 'report', 'sidebar', 'tabi', 'widget', 'tabf')");
} catch (PDOException $ex) {
	// If this fails, it has probably already been done.
}



// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
