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

class footer_logo_KT_Module extends KT_Module implements KT_Module_Footer {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Footer logo');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Footer contacts” module */ KT_I18N::translate('Add the kiwitrees logo to the footer');
	}

	// Implement KT_Module_Sidebar
	public function defaultFooterOrder() {
		return 30;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement class KT_Module_Footer
	public function getFooter($footer_id) {
		global $SHOW_COUNTER, $hitCount;

		$id			= $this->getName();
		$class		= $this->getName();
		$title		= $this->getTitle();

		//list all users for inter-user communication, only when logged in, and there is more than one user -->
		$content = '
			<div class="card-divider">
				<h5 class="kiwitrees_logo">&nbsp;</h5>
			</div>
			<div class="card-section">
				<p>
					<a href="' . KT_KIWITREES_URL . '" target="_blank" rel="noopener noreferrer" title="' . KT_KIWITREES_URL . '">' .
						/*I18N: kiwitrees logo on page footer */ KT_I18N::translate('Powered by %s', KT_KIWITREES) . '<span>&trade;</span>
					</a>
				</p>';
				if ($SHOW_COUNTER) {
					$content .= '<p>' . KT_I18N::translate('Hit Count:') . '&nbsp;' . $hitCount . '</p>';
				}
			$content .= '</div>
		';

		return $content;
	}

	// Implement class KT_Module_Footer
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Footer
	public function configureBlock($footer_id) {
		return false;
	}

}
