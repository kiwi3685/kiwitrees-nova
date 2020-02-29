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

class KT_MenuBar {
	public static function getGedcomMenu() {
		if (count(KT_Tree::getAll()) === 1 || KT_Site::preference('ALLOW_CHANGE_GEDCOM') === '0') {
			$menu = new KT_Menu(KT_I18N::translate('Home'), 'index.php?ged=' . KT_GEDURL, 'menu-tree');
			$menu->addClass('', '', 'fa-home');
		} else {
			$menu = new KT_Menu(KT_I18N::translate('Home'), '#', 'menu-tree');
			$menu->addClass('', '', 'fa-home');
			foreach (KT_Tree::getAll() as $tree) {
				$submenu = new KT_Menu(
					$tree->tree_title_html,
					'index.php?ged=' . $tree->tree_name_url,
					'menu-tree-' . $tree->tree_id // Cannot use name - it must be a CSS identifier
				);
				$menu->addSubmenu($submenu);
			}
		}
		return $menu;
	}

	public static function getMyAccountMenu() {
		global $PEDIGREE_FULL_DETAILS, $PEDIGREE_LAYOUT;

		$showFull = ($PEDIGREE_FULL_DETAILS) ? 1 : 0;
		$showLayout = ($PEDIGREE_LAYOUT) ? 1 : 0;

		if (!KT_USER_ID) {
			return null;
		}

		//-- main menu
		$menu = new KT_Menu(getUserFullName(KT_USER_ID), '#', 'menu-mylogout');

		//-- edit account submenu
			$submenu = new KT_Menu(KT_I18N::translate('My account'), 'edituser.php', 'menu-myaccount');
			$menu->addSubmenu($submenu);
		if (KT_USER_GEDCOM_ID) {
			//-- my_pedigree submenu
			$submenu = new KT_Menu(
				KT_I18N::translate('My pedigree'),
				'pedigree.php?ged=' . KT_GEDURL . '&amp;rootid=' . KT_USER_GEDCOM_ID . "&amp;show_full=' . $showFull . '&amp;talloffset=' . '$showLayout . '",
				'menu-mypedigree'
			);
			$menu->addSubmenu($submenu);
			//-- my_indi submenu
			$submenu = new KT_Menu(KT_I18N::translate('My individual record'), 'individual.php?pid=' . KT_USER_GEDCOM_ID . '&amp;ged=' . KT_GEDURL, 'menu-myrecord');
			$menu->addSubmenu($submenu);
		}
		if (KT_USER_GEDCOM_ADMIN) {
			//-- admin submenu
			$submenu = new KT_Menu(KT_I18N::translate('Administration'), 'admin.php', 'menu-admin');
			$menu->addSubmenu($submenu);
			//-- change home page blocks submenu
			if (KT_SCRIPT_NAME === 'index.php') {
				$submenu = new KT_Menu(KT_I18N::translate('Change the home page blocks'), 'index_edit.php?gedcom_id=' . KT_GED_ID,'menu-change-blocks');
				$menu->addSubmenu($submenu);
			}
			//-- change footer blocks submenu
			if (KT_SCRIPT_NAME === 'index.php') {
				$submenu = new KT_Menu(KT_I18N::translate('Change the footer blocks'), 'footer_edit.php?gedcom_id=' . KT_GED_ID,'menu-change-blocks');
				$menu->addSubmenu($submenu);
			}

		}
		//-- logout
		$submenu = new KT_Menu(logout_link(false), '', 'menu-logout');
		$menu->addSubmenu($submenu);

		return $menu;
	}

	public static function getChartsMenu() {
		global $SEARCH_SPIDER, $controller;
		if ($SEARCH_SPIDER || !KT_GED_ID) {
			return null;
		}
		$active_charts = KT_Module::getActiveCharts();
		if ($active_charts) {
			$indi_xref = $controller->getSignificantIndividual()->getXref();
			$PEDIGREE_ROOT_ID = get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID');
			$menu = new KT_Menu(KT_I18N::translate('Charts'), '#', 'menu-chart');
			$menu->addClass('', '', 'fa-sitemap');

			uasort($active_charts, function ($x, $y) {
				return KT_I18N::strcasecmp((string)$x, (string)$y);
			});

			foreach ($active_charts as $chart) {
				foreach ($chart->getChartMenus() as $submenu) {
					$menu->addSubmenu($submenu);
				}
			}
			return $menu;
		}
	}

	public static function getListsMenu() {
		global $SEARCH_SPIDER, $controller;

		$active_lists = KT_Module::getActiveLists();

		if ($SEARCH_SPIDER || !$active_lists) {
			return null;
		}

		$menu = new KT_Menu(KT_I18N::translate('Lists'), '#', 'menu-list');
		$menu->addClass('', '', 'fa-list');

		uasort($active_lists, function ($x, $y) {
			return KT_I18N::strcasecmp($x->getTitle(), $y->getTitle());
		});

		foreach ($active_lists as $list) {
			foreach ($list->getListMenus() as $submenu) {
				$menu->addSubmenu($submenu);
			}
		}
		return $menu;
	}

	/**
	* get the reports menu
	* @return KT_Menu the menu item
	*/
	public static function getReportsMenu($pid='', $famid='') {
		global $SEARCH_SPIDER;

		$active_reports = KT_Module::getActiveReports();

		if ($SEARCH_SPIDER || !$active_reports) {
			return null;
		}

		$menu = new KT_Menu(KT_I18N::translate('Reports'), '#', 'menu-report');
		$menu->addClass('', '', 'fa-file-alt');

		foreach ($active_reports as $report) {
			foreach ($report->getReportMenus() as $submenu) {
				$menu->addSubmenu($submenu);
			}
		}
		return $menu;
	}

	public static function getSearchMenu() {
		global $SEARCH_SPIDER;

		if ($SEARCH_SPIDER) {
			return null;
		}

		$menu = new KT_Menu(KT_I18N::translate('Search'), 'search.php?ged=' . KT_GEDURL, 'menu-search');
		$menu->addClass('', '', 'fa-search');

		return $menu;
	}

	public static function getLanguageMenu() {
		global $SEARCH_SPIDER;
		$languages = KT_I18N::used_languages();

		if ($SEARCH_SPIDER) {
			return null;
		} else {
			$menu = new KT_Menu(KT_I18N::translate('Language'), '#', 'menu-language');
			$menu->addClass('', '', 'fa-globe');

			foreach ($languages as $lang=>$name) {
				$submenu = new KT_Menu(KT_I18N::translate($name), get_query_url(array('lang' => $lang), '&amp;'), 'menu-language-' . $lang);
				if (KT_LOCALE == $lang) {
					$submenu->addClass('','','lang-active');
				}
				$menu->addSubMenu($submenu);
			}
			if (count($menu->submenus)>1) {
				return $menu;
			} else {
				return null;
			}
		}
	}

	public static function getLoginDropdownMenu() {
		global $SEARCH_SPIDER;

		if ($SEARCH_SPIDER) {
			return null;
		} else {
			$dropdown	= menu_login_dropdown_KT_Module::login_dropdown();
			$label		= KT_Site::preference('USE_REGISTRATION_MODULE') ? KT_I18N::translate('Login or Register') : KT_I18N::translate('Login');

			$menu		= new KT_Menu($label,'#', 'menu-login');
			$submenu	= new KT_Menu($dropdown, null, 'menu-login_popup');
			$menu->addClass('', '', 'fa-lock');
			$menu->addSubMenu($submenu);

			return $menu;
		}
	}

	public static function getFavoritesMenu() {
		global $controller, $SEARCH_SPIDER;
		$iconStyle = '';

		$show_user_favs = KT_USER_ID && array_key_exists('widget_favorites', KT_Module::getActiveModules());
		$show_gedc_favs = array_key_exists('block_favorites', KT_Module::getActiveModules());

		if ($show_user_favs && !$SEARCH_SPIDER) {
			if ($show_gedc_favs && !$SEARCH_SPIDER) {
				$favorites = array_merge(
					block_favorites_KT_Module::getFavorites(KT_GED_ID),
					widget_favorites_KT_Module::getFavorites(KT_USER_ID)
				);
			} else {
				$favorites = widget_favorites_KT_Module::getFavorites(KT_USER_ID);
			}
		} else {
			if ($show_gedc_favs && !$SEARCH_SPIDER) {
				$favorites = block_favorites_KT_Module::getFavorites(KT_GED_ID);
			} else {
				return null;
			}
		}
		// Sort $favorites alphabetically?

		$menu = new KT_Menu(KT_I18N::translate('Favorites'), '#', 'menu-favorites');

		foreach ($favorites as $favorite) {
			switch($favorite['type']) {
				case 'URL':
					$submenu = new KT_Menu($favorite['title'], $favorite['url']);
					$menu->addSubMenu($submenu);
				break;
				case 'INDI':
				case 'FAM':
				case 'SOUR':
				case 'OBJE':
				case 'NOTE':
					$obj = KT_GedcomRecord::getInstance($favorite['gid']);
					if ($obj && $obj->canDisplayName()) {
						$submenu = new KT_Menu($obj->getFullName(), $obj->getHtmlUrl());
						$menu->addSubMenu($submenu);
					}
				break;
			}
		}

		if ($show_user_favs) {
			if (isset($controller->record) && $controller->record instanceof KT_GedcomRecord) {
				$submenu = new KT_Menu(KT_I18N::translate('Add to favorites'), '#');
				$submenu->addOnclick("jQuery.post('module.php?mod=widget_favorites&amp;mod_action=menu-add-favorite',{xref:'".$controller->record->getXref()."'},function(){location.reload();})");
				$menu->addSubMenu($submenu);
			}
		}
		return $menu;
	}

	public static function getMainMenus() {
		$menus = array();
		foreach (KT_Module::getActiveMenus() as $module) {
			if ($module->MenuType() == 'main' || !$module->MenuType()) {
				$menu = $module->getMenu();
				if ($menu) {
					$menus[] = $menu;
				}
			}
		}
		return $menus;
	}

	public static function getOtherMenus() {
		$menus = array();
		foreach (KT_Module::getActiveMenus() as $module) {
			if ($module->MenuType() == 'other') {
				$menu = $module->getMenu();
				if ($menu) {
					$menus[] = $menu;
				}
			}
		}
		return $menus;
	}

}
