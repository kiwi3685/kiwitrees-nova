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

// Use LONGTEXT for log files.
try {
	self::exec("ALTER TABLE `##log`            CHANGE log_message   log_message   LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##media`          CHANGE m_gedcom      m_gedcom      LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##module_setting` CHANGE setting_value setting_value LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##news`           CHANGE body          body          LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##other`          CHANGE o_gedcom      o_gedcom      LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##places`         CHANGE p_std_soundex p_std_soundex LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##places`         CHANGE p_dm_soundex  p_dm_soundex  LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##sources`        CHANGE s_gedcom      s_gedcom      LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##site_setting`   CHANGE setting_value setting_value varchar(2000) COLLATE utf8_unicode_ci NOT NULL");
} catch (PDOException $ex) {
	// Perhaps we have already deleted this data?
}

// Update some database field names
try {
    self::exec("DELETE FROM `##module_setting` WHERE `module_name` = 'gallery' AND `setting_name` = 'THEME_DIR'");
    self::exec("DELETE FROM `##block_setting` WHERE `setting_name` = 'gallery_folder_f' AND `setting_value` = '';");
    self::exec("DELETE FROM `##block_setting` WHERE `setting_name` = 'gallery_folder_w' AND `setting_value` = '';");
    self::exec("UPDATE `##module_setting` SET `setting_name` = REPLACE(`setting_name`, 'FAQ_', 'HEADER_')");
} catch (PDOException $ex) {
    // Perhaps we have already deleted this data?
}
try {
    self::exec("UPDATE `##block_setting`  SET `setting_name` = 'faq_title' WHERE `setting_name` = 'header'");
} catch (PDOException $ex) {
    // Perhaps we have already deleted this data?
}
try {
    self::exec("UPDATE `##block_setting`  SET `setting_name` = 'faq_content' WHERE `setting_name` = 'faqbody'");
} catch (PDOException $ex) {
    // Perhaps we have already deleted this data?
}
try {
    self::exec("UPDATE `##block_setting`  SET `setting_name` = 'gallery_plugin' WHERE `setting_name` = 'plugin'");
} catch (PDOException $ex) {
    // Perhaps we have already deleted this data?
}
try {
    self::exec("UPDATE `##block_setting`  SET `setting_name` = 'gallery_folder' WHERE `setting_name` = 'gallery_folder_f'");
} catch (PDOException $ex) {
    // Perhaps we have already deleted this data?
}
try {
    self::exec("UPDATE `##block_setting`  SET `setting_name` = 'gallery_folder' WHERE `setting_name` = 'gallery_folder_w'");
} catch (PDOException $ex) {
    // Perhaps we have already deleted this data?
}
try {
    self::exec("UPDATE `##block_setting`  SET `setting_name` = 'story_content' WHERE `setting_name` = 'story_body'");
} catch (PDOException $ex) {
    // Perhaps we have already deleted this data?try {
}
try {
    self::exec("UPDATE `##block_setting`  SET `setting_name` = 'story_title' WHERE `setting_name` = 'title'");
} catch (PDOException $ex) {
    // Perhaps we have already deleted this data?
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
