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

class tabi_sources_KT_Module extends KT_Module implements KT_Module_IndiTab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Sources');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Sources” module */ KT_I18N::translate('A tab showing the sources linked to an individual.');
	}

	// Implement KT_Module_IndiTab
	public function defaultTabOrder() {
		return 30;
	}

	// Implement KT_Module_IndiTab
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	protected $sourceCount = null;

	// Implement KT_Module_IndiTab
	public function getTabContent() {
		global $iconStyle, $controller;

		$controller->addInlineJavascript('
			persistent_toggle("checkbox_sour2", "div.row_sour2");
		');
		?>

		<div id="tabi_sources_content" class="grid-x grid-padding-x grid-padding-y">
			<div class="cell tabHeader">
				<div class="grid-x">
					<div class="cell shrink">
						<input id="checkbox_sour2" type="checkbox" checked>
						<label for="checkbox_sour2"><?php echo KT_I18N::translate('Show all sources'); ?></label>
					</div>
					<?php if ($controller->record->canEdit()) { ?>
						<div class="cell shrink">
							<a href="#" onclick="add_new_record('<?php echo $controller->record->getXref(); ?>','SOUR'); return false;">
								<i class="<?php echo $iconStyle; ?> fa-book-medical"></i>
								<?php echo KT_I18N::translate('Add a source citation'); ?>
							</a>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php
			if ($this->get_source_count() > 0) {
				$otheritems = $controller->getOtherFacts();
					foreach ($otheritems as $event) {
						if ($event->getTag()=='SOUR') {
							print_main_sources($event, 1);
						}
				}
				// 2nd level sources [ 1712181 ]
				$controller->record->add_family_facts(false);
				foreach ($controller->getIndiFacts() as $event) {
					print_main_sources($event, 2);
				}

			} else { ?>
				<div class="cell callout warning">
					<?php echo KT_I18N::translate('There are no source citations for this individual.'); ?>
				</div>

			<?php } ?>
		</div>
		<?php

	}

	function get_source_count() {
		global $controller;

		if ($this->sourceCount===null) {
			$ct = preg_match_all("/\d SOUR @(.*)@/", $controller->record->getGedcomRecord(), $match, PREG_SET_ORDER);
			foreach ($controller->record->getSpouseFamilies() as $sfam)
				$ct += preg_match("/\d SOUR /", $sfam->getGedcomRecord());
			$this->sourceCount = $ct;
		}
		return $this->sourceCount;
	}

	// Implement KT_Module_IndiTab
	public function hasTabContent() {
		return KT_USER_CAN_EDIT || $this->get_source_count() > 0;
	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut() {
		return $this->get_source_count() == 0;
	}

	// Implement KT_Module_IndiTab
	public function canLoadAjax() {
		//		global $SEARCH_SPIDER;

		//		return !$SEARCH_SPIDER; // Search engines cannot use AJAX
		return false;
	}

	// Implement KT_Module_IndiTab
	public function getPreLoadContent() {
		return '';
	}

}
