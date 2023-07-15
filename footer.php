<?php
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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

global $SHOW_COUNTER, $hitCount, $iconStyle;

if (KT_USER_ID && KT_SCRIPT_NAME != 'index.php') {
   $show_widgetbar = true;
   $this->addInlineJavascript ('widget_bar();');
} else {
   $show_widgetbar = false;
}

$blocks			= get_gedcom_footers(KT_GED_ID);
$active_blocks	= KT_Module::getActiveFooters();

// Remove empty blocks_pending
if (!exists_pending_change()) {
	foreach($blocks as $key => $value) {
		if($value == 'block_pending') {
			unset($blocks[$key]);
		}
	}
}

if (KT_DEBUG_SQL) {
echo KT_DB::getQueryLog();
}

count($active_blocks) ? $ct_footer_blocks = min(count($active_blocks), 5) : $ct_footer_blocks = '1';

// use the footer layout or customised version of it for the currently active theme
if (file_exists(KT_THEME_URL . 'myfooter_template.php')) {
	include KT_THEME_DIR . 'myfooter_template.php';
} else {
	include KT_THEME_DIR . 'templates/footer_template.php';
}
