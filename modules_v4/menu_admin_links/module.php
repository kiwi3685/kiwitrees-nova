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
class menu_admin_links_KT_Module extends KT_Module implements KT_Module_Menu, KT_Module_Config {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/menu */ KT_I18N::translate('Administration link options');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the administration links module */
			KT_I18N::translate('
				Select your preferred location for a link or links to the site adminstration area
			');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 220;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_NONE;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		$menuType = get_module_setting($this->getName(), 'ADMIN_LOCATION');
		if ($menuType) {
			switch ($menuType) {
				case 'main':
					return 'main';
					break;
				case 'other':
					return 'other';
					break;
				case 'user':
				default:
					return false;
			}
		}
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		$menu = KT_MenuBar::getAdminMenu();
		return $menu;
	}

	// Extend KT_Module
	public function modAction($mod_action)
	{
		switch ($mod_action) {
			case 'admin_config':
				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/' . $mod_action . '.php';
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Config
	public function getConfigLink()
	{
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

}
