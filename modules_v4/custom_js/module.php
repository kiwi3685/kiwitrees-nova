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

class custom_js_KT_Module extends KT_Module implements KT_Module_Config, KT_Module_Menu {
	// Extend KT_Module
	public function getTitle() {
		return KT_I18N::translate('Custom JavaScript');
	}

	// Extend KT_Module
	public function getDescription() {
		return KT_I18N::translate('Allows you to easily add Custom JavaScript to your kiwitrees site.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 999;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return false;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'other';
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		global $iconStyle;

		switch($mod_action) {
			case 'admin_config':
				$controller = new KT_Controller_Page;
				$controller
					->restrictAccess(KT_USER_IS_ADMIN)
					->setPageTitle($this->getTitle())
					->pageHeader()
					->addInlineJavascript('
					    function clearFields() {
						    document.getElementById("new_js").value=""
						}
					');

				$action = KT_Filter::post("action");

				if ($action == 'update') {
					set_module_setting('custom_js', 'CJS_FOOTER',  $_POST['NEW_CJS_FOOTER']);
					AddToLog($this->getTitle().' config updated', 'config');
				}

				$CJS_FOOTER = get_module_setting('custom_js', 'CJS_FOOTER'); ?>
				<div class="cell">
					<h4 class="inline"><?php echo KT_I18N::translate('Custom Javascript for Footer'); ?></h4>
					<a class="current faq_link" href="<?php echo KT_KIWITREES_URL; ?>/faqs/modules/add-code-for-google-analytics-social-media-piwik-etc/" target="_blank" rel="noopener noreferrer" title="<?php echo KT_I18N::translate('View FAQ for this page.'); ?>"><?php echo KT_I18N::translate('View FAQ for this page.'); ?><i class="<?php echo $iconStyle; ?> fa-comments"></i></a>
					<form style="width:98%;" method="post" name="configform" action="<?php echo $this->getConfigLink(); ?>">
						<input type="hidden" name="action" value="update">
						<textarea id="new_js" style="width:100%;" name="NEW_CJS_FOOTER"><?php echo $CJS_FOOTER; ?></textarea>
						<button class="button" type="submit">
						    <i class="<?php echo $iconStyle; ?> fa-save"></i>
						    <?php echo KT_I18N::translate('Save'); ?>
						</button>
						<button class="button" type="reset">
						    <i class="<?php echo $iconStyle; ?> fa-sync"></i>
						    <?php echo KT_I18N::translate('Reset'); ?>
						</button>
						<button class="button" type="button" onclick="clearFields()">
						    <i class="<?php echo $iconStyle; ?> fa-trash-alt"></i>
						    <?php echo KT_I18N::translate('clear'); ?>
						</button>
					</form>
				</div>
				<?php
				break;
            default:
                header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		// We don't actually have a menu - this is just a convenient "hook" to execute
		// code at the right time during page execution
		global $controller;

		$cjs_footer = get_module_setting('custom_js', 'CJS_FOOTER', '');
		if (strpos($cjs_footer, '#') !== false) {
			# parse for embedded keywords
			$stats = new KT_Stats(KT_GEDCOM);
			$cjs_footer = $stats->embedTags($cjs_footer);
		}
		$controller->addInlineJavaScript($cjs_footer, KT_Controller_Base::JS_PRIORITY_LOW);

		return null;
	}

}
