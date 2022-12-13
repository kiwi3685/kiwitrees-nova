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

class footer_contacts_KT_Module extends KT_Module implements KT_Module_Footer {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Footer contacts');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Footer contacts” module */ KT_I18N::translate('Add contact links in a footer block');
	}

	// Implement KT_Module_Sidebar
	public function defaultFooterOrder() {
		return 10;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement class KT_Module_Footer
	public function getFooter($footer_id) {
		global $iconStyle;

		$id			= $this->getName();
		$class		= $this->getName();
		$title		= $this->getTitle();

		$content = '
			<div class="card-divider">
				<h5>' . KT_I18N::translate('Contact Information') . '</h5>
			</div>
			<div class="card-section">';
				if (array_key_exists('contact', KT_Module::getActiveModules()) && KT_USER_ID) {
					$content .= '
						<p>
							<a
								href="message.php?url=<?php echo KT_SERVER_NAME . KT_SCRIPT_PATH . addslashes(rawurlencode(get_query_url())); ?>"
								rel="noopener noreferrer"
								title="' . KT_I18N::translate('Send Message') . '"
							>' .
								getUserFullName(KT_USER_ID) . '
								<i class="' . $iconStyle . ' fa-envelope"></i>
							</a>
						</p>
					';
				} elseif (contact_links()) {
					$content .= contact_links();
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
