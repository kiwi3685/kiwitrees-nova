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

class menu_login_dropdown_KT_Module extends KT_Module implements KT_Module_Menu {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/menu */ KT_I18N::translate('Login dropdown');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the languages module */ KT_I18N::translate('The Login menu item as a dropdown (other menus)');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 200;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'other';
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		if (KT_USER_ID) {
			$menu = KT_MenuBar::getMyAccountMenu();
		} else {
			$menu = KT_MenuBar::getLoginDropdownMenu();
		}
		return $menu;
	}

	// Function to create a login dropdown menu item
	public static function login_dropdown() {
		global $iconStyle;
		$url	= KT_LOGIN_URL . '?url=' . rawurlencode(get_query_url());

		$html	= '
		<div id="login-popup" class="grid-x grid-padding-x grid-padding-y">
			<form class="cell login-form" name="login-form" method="post" action="' . $url . '" onsubmit="t = new Date(); this.usertime.value=t.getFullYear()+\'-\'+(t.getMonth()+1)+\'-\'+t.getDate()+\' \'+t.getHours()+\':\'+t.getMinutes()+\':\'+t.getSeconds();return true;">
				<input type="hidden" name="action" value="login">
				<input type="hidden" name="ged" value="'; (isset($ged) ? $html .= htmlspecialchars((string) $ged) : $html .= htmlentities(KT_GEDCOM)); $html .= '">
				<input type="hidden" name="pid" value="'; (isset($pid) ? $html .= htmlspecialchars((string) $pid) : ''); $html .= '">
				<input type="hidden" name="usertime" value="">
				<label for="username">' . KT_I18N::translate('Username') . '</label>
				<input type="text" name="username" id="username">
				<label for="password">' . KT_I18N::translate('Password') . '</label>
				<div class="input-group">
					<input class="input-group-field password" type="password" id="password" name="password">
					<span class="input-group-label unmask" title="' . KT_I18N::translate('Show/Hide password to check content') . '">
						<i class="' . $iconStyle . ' fa-eye"></i>
					</span>
				</div>
				<button class="button expanded" type="submit" >
					<i class="' . $iconStyle . ' fa-sign-in-alt"></i>' .
					KT_I18N::translate('Login') . '
				</button>
				<p class="text-center">
					<a href="' . KT_LOGIN_URL . '?action=requestpw">' . KT_I18N::translate('Request new password') . '</a>
				</p>';
				if (KT_Site::preference('USE_REGISTRATION_MODULE')) {
					$html .= '
						<p class="text-center">
							<a href="' . KT_LOGIN_URL . '?action=register">' . KT_I18N::translate('Request new user account') .  '</a>
						</p>
					';
				}
			$html .= '</form>
		</div>
		';

		return $html;

	}
}
