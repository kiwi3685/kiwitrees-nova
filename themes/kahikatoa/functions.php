<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

/**
 * print start of all pages
 *
 * @param string $title name of page
 */
function pageStart($title, $pageTitle = '', $includeTitle = 'y') {
	$pageTitle ? $pageTitle = $pageTitle : $pageTitle = $title;

	if ($includeTitle = 'n') {
		$pageTitle = '';
	} else {
		$pageTitle = '<h3>' . $pageTitle . '</h3>';
	}

	return '
		<div id="' . strtolower($title) . '-page" class="grid-x grid-padding-x">
			<div class="cell large-10 large-offset-1">' .
				$pageTitle;
		// function pageClose() must be added after content to close this div element
}

/**
 * print end of all pages
 *
 */
function pageClose() {
	echo '
		</div>
			<div>
	';
}
