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

	$pagesList = array(
		 'admin_users.php'					=> KT_I18N::translate('Manage users'),
		 'admin_users.php?action=edit'		=> KT_I18N::translate('Add a new user'),
		 'admin_users.php?action=messaging'	=> KT_I18N::translate('Broadcast messages'),
		 'admin_users.php?action=cleanup'	=> KT_I18N::translate('Delete inactive users'),

		 'admin_trees_manage.php'			=> KT_I18N::translate('Manage all family trees'),
		 'admin_trees_config.php'			=> KT_I18N::translate('Configure each family tree'),
		 'admin_trees_check.php'			=> KT_I18N::translate('Check for GEDCOM errors'),
		 'admin_trees_change.php'			=> KT_I18N::translate('Changes log'),
		 'admin_trees_addunlinked.php'		=> KT_I18N::translate('Add unlinked records'),
		 'admin_trees_places.php'			=> KT_I18N::translate('Place name editing'),
		 'admin_trees_merge.php'			=> KT_I18N::translate('Merge records'),
		 'admin_trees_renumber.php'			=> KT_I18N::translate('Renumber family tree'),
		 'admin_trees_append.php'			=> KT_I18N::translate('Append family tree'),
		 'admin_trees_duplicates.php'		=> KT_I18N::translate('Find duplicate individuals'),
		 'admin_trees_findunlinked.php'		=> KT_I18N::translate('Find unlinked records'),
		 'admin_trees_sanity.php'			=> KT_I18N::translate('Sanity check'),
		 'admin_trees_source.php'			=> KT_I18N::translate('Sources - review'),
		 'admin_trees_sourcecite.php'		=> KT_I18N::translate('Sources - review citations'),
		 'admin_trees_missing.php'			=> KT_I18N::translate('Missing fact or event details'),
	);
	asort($pagesList);

	$html =  '
		<div class="grid-x relatedPages show-for-medium">
			<div class="cell text-right">
				<label>' .
					KT_I18N::translate('Related pages') . '
				</label>';

				foreach ($pagesList as $link => $title) {
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
