<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net.
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

class tabi_events_KT_Module extends KT_Module implements KT_Module_IndiTab
{
	// Extend KT_Module
	public function getTitle()
	{
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('Events');
	}

	// Extend KT_Module
	public function getDescription()
	{
		return /* I18N: Description of the “Facts and events” module */ KT_I18N::translate('A tab showing all events in an individuals life in date order');
	}

	// Extend class KT_Module_IndiTab
	public function defaultAccessLevel()
	{
		return false;
	}

	// Implement KT_Module_IndiTab
	public function defaultTabOrder()
	{
		return 10;
	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut()
	{
		return false;
	}

	// Implement KT_Module_IndiTab
	public function hasTabContent()
	{
		return true;
	}

	// Implement KT_Module_IndiTab
	public function canLoadAjax()
	{
		return false;
	}

	// Implement KT_Module_IndiTab
	public function getPreLoadContent()
	{
		return '';
	}

	// Implement KT_Module_IndiTab
	public function getTabContent()
	{
		global $SHOW_RELATIVES_EVENTS, $controller;

		if ($SHOW_RELATIVES_EVENTS) {
			$controller->record->add_family_facts();
		}

		$controller->addInlineJavascript('
			persistent_toggle("checkbox_rela_facts", "div.rela");
			persistent_toggle("checkbox_histo", "div.histo");
		');

		ob_start();
		$eventFacts = $controller->getIndiFacts();
		if (0 == count($eventFacts)) { ?>
			<div class="callout alert">
				<?php echo KT_I18N::translate('There are no events for this individual.'); ?>
			</div>
		<?php } else { ?>
			<div class="cell tabHeader">
				<?php if ($SHOW_RELATIVES_EVENTS || file_exists(KT_Site::preference('INDEX_DIRECTORY') . 'histo.' . KT_LOCALE . '.php')) { ?>
					<div class="grid-x">
						<?php if ($SHOW_RELATIVES_EVENTS) { ?>
							<div class="cell shrink">
								<input id="checkbox_rela_facts" type="checkbox">
								<label for="checkbox_rela_facts"><?php echo KT_I18N::translate('Events of close relatives'); ?></label>
							</div>
						<?php }
						if (file_exists(KT_Site::preference('INDEX_DIRECTORY') . 'histo.' . KT_LOCALE . '.php')) { ?>
							<div class="cell auto">
								<input id="checkbox_histo" type="checkbox">
								<label for="checkbox_histo"><?php echo KT_I18N::translate('Historical events'); ?></label>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
			<div class="cell show-for-medium indiFactHeader">
				<div class="grid-x grid-padding-x">
					<div class="cell medium-3 medium-offset-1 event">
						<label><?php echo KT_I18N::translate('Event'); ?></label>
					</div>
					<div class="cell medium-3 place">
						<label><?php echo KT_I18N::translate('Place'); ?></label>
					</div>
					<div class="cell <?php echo KT_USER_CAN_EDIT ? 'medium-4' : 'auto'; ?> detail">
						<label><?php echo KT_I18N::translate('Details'); ?></label>
					</div>
					<?php if (KT_USER_CAN_EDIT) { ?>
						<div class="cell medium-1 edit">
							<label><?php echo KT_I18N::translate('Edit'); ?></label>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php
			foreach ($eventFacts as $fact) {
				if ($fact->getParentObject() instanceof KT_Family) {
					// Print all family facts
					print_timeline($fact, $controller->record);
				} else {
					print_timeline($fact, $controller->record);
				}
			}
			//-- new fact link
			if ($controller->record->canEdit()) {
				print_add_new_fact($controller->record->getXref(), $eventFacts, 'INDI');
			}
		}

		return ob_get_clean();
	}
}
