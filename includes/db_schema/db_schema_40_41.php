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
    self::exec("UPDATE `ktn_module_setting` SET `setting_name` = REPLACE(`setting_name`, 'FAQ_', 'HEADER_'");
} catch (PDOException $ex) {
    // Perhaps we have already deleted this data?
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
