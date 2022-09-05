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


/**
 * Print links to related admin pages
 *
 * //@param string $title name of page
 */
function relatedPages($links) {
	global $iconStyle;
	include KT_THEME_URL . 'templates/adminData.php';

	$html =  '
		<div class="grid-x relatedPages show-for-medium">
			<div class="cell text-right">
				<label>' .
					KT_I18N::translate('Related pages') . '
				</label>';

				foreach ($adminPagesList as $link => $title) {
					if (in_array($link, $links)) {
						$html .= '
							<a href="' . $link. '" target="_blank" class="button small large-down-expanded">
							' . $title . '
						</a>';
					}
				}

			$html .= '</div>
		</div>
	';

	return $html;

}
