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

class extra_menus_KT_Module extends KT_Module implements KT_Module_Menu, KT_Module_Block, KT_Module_Config {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module. */ KT_I18N::translate('Extra menus');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Extra menus” module */ KT_I18N::translate('Provides links to custom defined pages.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 60;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_NONE;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'admin_add':
			case 'admin_config':
			case 'admin_edit':
				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/' . $mod_action . '.php';
				break;
			case 'admin_delete':
				$this->delete();
				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/admin_config.php';
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return false;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
	}

	public function getMenuTitle() {
		$default_title = KT_I18N::translate('Menu');
		$HEADER_TITLE  = KT_I18N::translate(get_module_setting($this->getName(), 'MENU_TITLE', $default_title));
		return $HEADER_TITLE;
	}

	public function getMenuIcon() {
		$default_icon = 'fa-signature';
		$HEADER_ICON  = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_ICON', $default_icon));
		return $HEADER_ICON;
	}

	// Return the list of Menus
	private function getItemList() {
		$sql = "
			SELECT block_id, block_order,
			bs1.setting_value AS menu_title,
			bs2.setting_value AS menu_access,
			bs3.setting_value AS menu_address
			FROM `##block` b
			JOIN `##block_setting` bs1 USING (block_id)
			JOIN `##block_setting` bs2 USING (block_id)
			JOIN `##block_setting` bs3 USING (block_id)
			WHERE module_name = ?
			AND bs1.setting_name = 'menu_title'
			AND bs2.setting_name = 'menu_access'
			AND bs3.setting_name = 'menu_address'
			AND (gedcom_id IS NULL OR gedcom_id = ?)
			ORDER BY block_order
		";

		$items = KT_DB::prepare($sql)->execute(array($this->getName(), KT_GED_ID))->fetchAll();

		$itemList = [];

		// Filter for valid language and access
		foreach ($items as $item) {
			$languages   = get_block_setting($item->block_id, 'languages');
			$item_access = get_block_setting($item->block_id, 'menu_access');
			if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $item_access >= KT_USER_ACCESS_LEVEL) {
				$itemList[] = $item;
			}
		}

		return $itemList;

	}

	// Implement KT_Module_Menu
	public function getMenu()
	{
		global $controller, $SEARCH_SPIDER;

		if ($SEARCH_SPIDER) {
			return null;
		}

		$items = $this->getItemList();
		$minBlockId = $items ? min(array_column($items, 'block_id')) : '';

		if (count($items) == 0) {
			$main_menu_address = '#';
		} else {
			$main_menu_address = KT_DB::prepare(
				"SELECT setting_value FROM `##block_setting` WHERE block_id=? AND setting_name=?"
			)->execute(array($minBlockId, 'menu_address'))->fetchOne();
		}

		$main_menu_target = KT_DB::prepare(
			"SELECT setting_value FROM `##block_setting` WHERE block_id=? AND setting_name=?"
		)->execute(array($minBlockId, 'new_tab'))->fetchOne();

		// -- main Extra_menus menu item
		$menu = new KT_Menu(
			'<span>' . $this->getMenuTitle() . '</span>',
			$main_menu_address,
			'menu-my_menu',
			'down'
		);
		$menu->addClass('', '', $this->getMenuIcon());
		if ($main_menu_target == 1) {
			$menu->addTarget('_blank');
		}

		foreach ($items as $item) {
			$submenu = new KT_Menu(
				KT_I18N::translate($item->menu_title),
				$item->menu_address,
				'menu-my_menu-' . $item->block_id
			);
			$target = get_block_setting($item->block_id, 'new_tab', 0);
			if ($target == 1) {
				$submenu->addTarget('_blank');
			}
			$menu->addSubmenu($submenu);
		}

		if (KT_USER_IS_ADMIN) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit menus'), $this->getConfigLink(), 'menu-my_menu-edit');
			$menu->addSubmenu($submenu);
		}

		return $menu;
	}

	private function delete() {
		$block_id = safe_GET('block_id');

		KT_DB::prepare(
			"DELETE FROM `##block_setting` WHERE block_id=?"
		)->execute(array($block_id));

		KT_DB::prepare(
			"DELETE FROM `##block` WHERE block_id=?"
		)->execute(array($block_id));
	}

}
