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

class tabi_notes_KT_Module extends KT_Module implements KT_Module_IndiTab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Notes');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Notes” module */ KT_I18N::translate('A tab showing the notes attached to an individual.');
	}

	// Implement KT_Module_IndiTab
	public function defaultTabOrder() {
		return 40;
	}

	protected $noteCount = null;

	// Implement KT_Module_IndiTab
	public function getTabContent() {
		global $iconStyle, $controller;

		$controller->addInlineJavascript('
			persistent_toggle("checkbox_note2", "div.row_note2");
		');
		?>

		<div id="tabi_notes_content" class="grid-x grid-padding-x grid-padding-y">
			<div class="cell tabHeader">
				<div class="grid-x">
					<div class="cell shrink">
						<input id="checkbox_note2" type="checkbox" checked>
						<label for="checkbox_note2"><?php echo KT_I18N::translate('Show all notes'); ?></label>
					</div>
					<?php if ($controller->record->canEdit()) { ?>
						<div class="cell shrink">
							<a href="#" onclick="add_new_record('<?php echo $controller->record->getXref(); ?>','NOTE'); return false;">
								<i class="<?php echo $iconStyle; ?> fa-note-sticky"></i>
								<?php echo KT_I18N::translate('Add a note'); ?>
							</a>
						</div>
						<div class="cell shrink">
							<a href="#" onclick="add_new_record('<?php echo $controller->record->getXref(); ?>','SHARED_NOTE'); return false;">
								<i class="<?php echo $iconStyle; ?> fa-notes-medical"></i>
								<?php echo KT_I18N::translate('Add a shared note'); ?>
							</a>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php
			if ($this->get_note_count() > 0) { ?>
					<?php
					$globalfacts = $controller->getGlobalFacts();
					foreach ($globalfacts as $event) {
						$fact = $event->getTag();
						if ($fact == 'NAME') {
							print_main_notes($event, 2);
						}
					}

					$otherfacts = $controller->getOtherFacts();
					foreach ($otherfacts as $event) {
						$fact = $event->getTag();
						if ($fact == 'NOTE') {
							print_main_notes($event, 1);
						}
					}
					// 2nd to 5th level notes/sources
					$controller->record->add_family_facts(false);
					foreach ($controller->getIndiFacts() as $factrec) {
						for ($i = 2; $i < 6; $i ++) {
							print_main_notes($factrec, $i);
						}
					} ?>
			<?php } else { ?>
				<div class="cell callout warning">
					<?php echo KT_I18N::translate('There are no notes for this individual.'); ?>
				</div>

			<?php } ?>

		</div>
		<?php

	}

	function get_note_count() {
		global $controller;

		if ($this->noteCount===null) {
			$ct = preg_match_all("/\d NOTE /", $controller->record->getGedcomRecord(), $match, PREG_SET_ORDER);
			foreach ($controller->record->getSpouseFamilies() as $sfam)
			$ct += preg_match("/\d NOTE /", $sfam->getGedcomRecord());
			$this->noteCount = $ct;
		}
		return $this->noteCount;
	}

	// Implement KT_Module_IndiTab
	public function hasTabContent() {
		return KT_USER_CAN_EDIT || $this->get_note_count() > 0;
	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut() {
		return $this->get_note_count()==0;
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

	// Implement KT_Module_IndiTab
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

}
