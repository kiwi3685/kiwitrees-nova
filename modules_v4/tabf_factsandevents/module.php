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

class tabf_factsandevents_KT_Module extends KT_Module implements KT_Module_FamTab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('Facts and events');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Facts and events” module */ KT_I18N::translate('A tab showing the facts and events of the individual.');
	}

	// Extend class KT_Module_FamTab
	public function defaultAccessLevel() {
		return false;
	}

	// Implement KT_Module_FamTab
	public function defaultTabOrder() {
		return 5;
	}

	// Implement KT_Module_FamTab
	public function isGrayedOut() {
		return false;
	}

	// Implement KT_Module_FamTab
	public function hasTabContent() {
		return true;
	}

	// Implement KT_Module_FamTab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_FamTab
	public function getPreLoadContent() {
		return '';
	}

	// Implement KT_Module_FamTab
	public function getTabContent() {
		global $controller;

		ob_start();
		?>
		<div class="cell tabHeader">
			<div class="grid-x">
				<div class="cell">
					<h5><?php echo KT_I18N::translate('Family group information'); ?></h5>
				</div>
			</div>
		</div>
		<?php if ($controller->record->canDisplayDetails()) { ?>
			<?php echo $controller->printFamilyFacts(); ?>
		<?php } else { ?>
			<div class="callout secondary">
				<?php echo KT_I18N::translate('The details of this family are private.'); ?>
			</div>
		<?php }

		return '
			<div class="grid-x grid-margin-y">' .
				ob_get_clean() . '
			</div>
		';
	}
}
