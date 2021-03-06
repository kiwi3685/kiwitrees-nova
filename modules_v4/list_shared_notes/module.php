<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

class list_shared_notes_KT_Module extends KT_Module implements KT_Module_List {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Shared notes');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the shared notes list module */ KT_I18N::translate('A list of shared notes');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_List
	public function getListMenus() {
		global $controller, $SEARCH_SPIDER;
		if ($SEARCH_SPIDER) {
			return null;
		}
		// Do not show empty lists
		$row = KT_DB::prepare(
			"SELECT SQL_CACHE EXISTS(SELECT 1 FROM `##other` WHERE o_file=? AND o_type='NOTE')"
		)->execute(array(KT_GED_ID))->fetchOneRow();
		if ($row) {
			$menus = array();
			$menu  = new KT_Menu(
				$this->getTitle(),
				'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
				'menu-list-note'
			);
			$menus[] = $menu;
			return $menus;
		} else {
			return false;
		}
	}

	// Display list
	public function show() {
		global $controller;
		require_once KT_ROOT . 'includes/functions/functions_print_lists.php';

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_shared_notes', KT_USER_ACCESS_LEVEL))
			->setPageTitle(KT_I18N::translate('Shared notes'))
			->pageHeader();
		?>
		<div id="sourcelist-page" class="grid-x grid-padding-x">
			<div class="cell large-10 large-offset-1">
				<h3><?php echo $controller->getPageTitle(); ?></h3>
				<?php echo format_note_table(get_note_list(KT_GED_ID)); ?>
			</div>
		</div>
		<?php
	}

}
