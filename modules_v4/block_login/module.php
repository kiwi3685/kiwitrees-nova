<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

class block_login_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Login');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Login” module */ KT_I18N::translate('An alternative way to login and logout.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $controller, $iconStyle;

		$id		= $this->getName() . $block_id;
		$class	= $this->getName();
		$config = false;
		$url	= KT_LOGIN_URL . '?url=' . rawurlencode(get_query_url());

		if (KT_USER_ID) {
			$title		= KT_I18N::translate('Logout');
			$content	= '<div id="block_logout_' . $block_id . '" class="grid-x grid-padding-x grid-padding-y">
				<div class="cell text-center">
					<form method="post" action="index.php?logout=1" name="logoutform" onsubmit="return true;">
						<div class="callout secondary small h6">
							<a href="edituser.php">' . KT_I18N::translate('Logged in as ') . ' (' . KT_USER_NAME . ')</a>
						</div>
						<button class="button expanded" type="submit">
							<i class="' . $iconStyle . ' fa-lock-open"></i>' .
							KT_I18N::translate('Logout') . '
						</button>
					</form>
				</div>
			</div>';
		} else {
			$title		= (KT_Site::preference('USE_REGISTRATION_MODULE') ? KT_I18N::translate('Login or Register') : KT_I18N::translate('Login'));
			$content	= '<div id="login-box" class="grid-x">
				<div class="cell">
					<form class="login-form" name="login-form" method="post" action="' . $url . '" onsubmit="t = new Date(); this.usertime.value=t.getFullYear()+\'-\'+(t.getMonth()+1)+\'-\'+t.getDate()+\' \'+t.getHours()+\':\'+t.getMinutes()+\':\'+t.getSeconds();return true;">
						<input type="hidden" name="action" value="login">
						<input type="hidden" name="ged" value="'; if (isset($ged)) $content .= htmlspecialchars($ged); else $content .= htmlentities(KT_GEDCOM); $content .= '">
						<input type="hidden" name="pid" value="'; if (isset($pid)) $content .= htmlspecialchars($pid); $content .= '">
						<input type="hidden" name="usertime" value="">
						<label for="username">' . KT_I18N::translate('Username') . '</label>
						<input type="text" name="username" class="formField">
						<label for="password">' . KT_I18N::translate('Password') . '</label>
						<input type="password" name="password" class="formField">
						<button class="button expanded" type="submit" >
							<i class="' . $iconStyle . ' fa-sign-in-alt"></i>' .
							KT_I18N::translate('Login') . '
						</button>
						<p>
							<a href="' . KT_LOGIN_URL . '?action=requestpw">' . KT_I18N::translate('Request new password').'</a>
						</p>';
						if (KT_Site::preference('USE_REGISTRATION_MODULE')) {
							$content .= '
								<p>
									<a href="' . KT_LOGIN_URL . '?action=register">'. KT_I18N::translate('Request new user account') .  '</a>
								</p>';
						}
					$content .= '</form>
				</div>
			</div>';
		}

		if ($template) {
			if (get_block_location($block_id) === 'side') {
				require KT_THEME_DIR . 'templates/block_small_temp.php';
			} else {
				require KT_THEME_DIR . 'templates/block_main_temp.php';
			}
		} else {
			return $content;
		}
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
	}
}
