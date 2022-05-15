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

define('KT_SCRIPT_NAME', 'save.php');
require './includes/session.php';

Zend_Session::writeClose();

// The script must always end by calling one of these two functions.
function ok() {
	global $value;
	header('Content-type: text/html; charset=UTF-8');
	echo $value;
	exit;
}
function fail() {
	// Any 4xx code should work.  jeditable recommends 406
	header('HTTP/1.0 406 Not Acceptable');
	exit;
}

// Do we have a valid CSRF token?
if (!KT_Filter::checkCsrf()) {
	fail();
}

// The data item to updated must identified with a single "id" element.
// The id must be a valid CSS identifier, so it can be used in HTML.
// We use "[A-Za-z0-9_]+" separated by "-".

$id = KT_Filter::post('id', '[a-zA-Z0-9_-]+');
list($table, $id1, $id2, $id3) = explode('-', $id . '---');

// The replacement value.
$value = KT_Filter::post('value', KT_REGEX_UNSAFE);

// Every switch must have a default case, and every case must end in ok() or fail()

switch ($table) {
	case 'site_access_rule':
		//////////////////////////////////////////////////////////////////////////////
		// Table name: KT_SITE_ACCESS_RULE
		// ID format:  site_access_rule-{column_name}-{user_id}
		//////////////////////////////////////////////////////////////////////////////

		if (!KT_USER_IS_ADMIN) {
			fail();
		}
		switch ($id1) {
			case 'ip_address_start':
			case 'ip_address_end':
				KT_DB::prepare("UPDATE `##site_access_rule` SET {$id1}=INET_ATON(?) WHERE site_access_rule_id=?")
					->execute(array($value, $id2));
				$value=KT_DB::prepare(
					"SELECT INET_NTOA({$id1}) FROM `##site_access_rule` WHERE site_access_rule_id=?"
				)->execute(array($id2))->fetchOne();
				ok();
				break;
			case 'user_agent_pattern':
			case 'rule':
			case 'comment':
				KT_DB::prepare("UPDATE `##site_access_rule` SET {$id1}=? WHERE site_access_rule_id=?")
					->execute(array($value, $id2));
				ok();
		}
		fail();

	default:
		// An unrecognised table
		fail();
}
