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
class contact_links_KT_Module extends KT_Module implements KT_Module_Menu, KT_Module_Footer, KT_Module_Widget, KT_Module_Config {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/menu */ KT_I18N::translate('Contact link options');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the administration links module */
			KT_I18N::translate('Select your preferred location for a link or links to a contact page');
	}

	// Implement class KT_Module_Footer
	public function loadAjax() {
		return false;
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

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 220;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		$menuTypeMain  = get_module_setting($this->getName(), 'CONTACT_MAIN', '');
		$menuTypeOther = get_module_setting($this->getName(), 'CONTACT_OTHER', '');

		if ($menuTypeMain && $menuTypeOther) {
			return 'both';
		} else if ($menuTypeMain && !$menuTypeOther) {
			return 'main';
		} else if (!$menuTypeMain && $menuTypeOther)
		if ($menuTypeMain && $menuTypeOther) {
			return 'other';
		} else {
			return false;
		}
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		$menu = KT_MenuBar::getContactMenu();
		return $menu;
	}

	// Implement KT_Module_Sidebar
	public function defaultFooterOrder() {
		return 10;
	}

	// Implement class KT_Module_Footer
	public function getFooter($footer_id) {
		global $iconStyle;

		$id			= $this->getName();
		$class		= $this->getName();
		$title		= KT_I18N::translate('Contacts');

		$content = '
			<div class="card-divider">
				<h5>' . KT_I18N::translate('Contact') . '</h5>
			</div>
			<div class="card-section">';
				if (
					array_key_exists('contact_links', KT_Module::getActiveModules()) &&
					get_module_setting($this->getName(), 'CONTACT_FOOTER') == 'footer' &&
					KT_USER_ID
				 ) {
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
	public function configureBlock($footer_id) {
		return false;
	}

	// Implement KT_Module_Sidebar
	public function defaultWidgetOrder() {
		return 15;
	}

	// Implement class KT_Module_Widget
	public function getWidget($widget_id, $template=true, $cfg=null) {
		global $iconStyle;

		$id			= $this->getName();
		$class		= $this->getName();
		$title		= KT_I18N::translate('User contacts');
		$order		= get_widget_order($this->getName());
		$content	= '';

		//list all users for inter-user communication, only when logged in, and there is more than one user -->
		if (KT_USER_ID) {
			$content .= '<div id="contact_page">
				<form name="messageform" action="message.php">
					<input type="hidden" name="url" value="' . KT_Filter::escapeHtml(KT_SERVER_NAME . KT_SCRIPT_PATH . get_query_url()) . '">
					<div class="contact_form">
						<div class="option">
							<label for="to_name">' . KT_I18N::translate('To') . '</label>
							<!-- list all users for inter-user communication, only when logged in, and there is more than one user -->
							<select name="to">';
								if (get_user_count() > 1) {
									$content .= '<option value="">' . KT_I18N::translate('Select') . '</option>';
								}
								foreach (get_all_users() as $user_id => $user_name) {
								// don't list yourself; unverified users; or users with contact method = none //
									if ($user_id != KT_USER_ID && get_user_setting($user_id, 'verified_by_admin') && get_user_setting($user_id, 'contactmethod') != 'none') {
										$content .= '<option value="' . $user_name . '">
											<span dir="auto">' . htmlspecialchars(getUserFullName($user_id)) . '</span> - <span dir="auto">' . $user_name . '</span>
										</option>';
									}
								}
							$content .= '</select>
						</div>
						<p id="save-cancel">
							<button class="btn btn-primary" type="submit">
								<i class="' . $iconStyle . ' fa-pen-to-square"></i>' .
								KT_I18N::translate('Write message') . '
							</button>
						</p>
					</div>
				</form>
			</div>';
		} else {
			$content .= KT_I18N::translate('This feature is for registered members only.');
		}

		if ($template) {
			require KT_THEME_DIR . 'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Extend KT_Module_Footer
	static function show() {
		global $iconStyle ;

		$float_icon = get_module_setting('contact_links', 'FLOAT_ICON', 'fa-comment-dots'); ?>

		<div id="floating_contact">
			<a
				href="message.php?url=<?php echo KT_SERVER_NAME . KT_SCRIPT_PATH . addslashes(rawurlencode(get_query_url())); ?>"
				rel="noopener noreferrer"
				title="<?php echo KT_I18N::translate('Send Message'); ?>"
			>
				<i class="<?php echo $iconStyle; ?> <?php echo $float_icon; ?>"></i>
			</a>
		</div>

	<?php }

}
