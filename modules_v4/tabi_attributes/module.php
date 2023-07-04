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

class tabi_attributes_KT_Module extends KT_Module implements KT_Module_IndiTab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('Attributes');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Facts and events” module */ KT_I18N::translate('A tab showing all recorded attributes of an individual');
	}

	// Extend class KT_Module_IndiTab
	public function defaultAccessLevel() {
		return false;
	}

	// Implement KT_Module_IndiTab
	public function defaultTabOrder() {
		return 10;
	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut() {
		return false;
	}

	// Implement KT_Module_IndiTab
	public function hasTabContent() {
		return true;
	}

	// Implement KT_Module_IndiTab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_IndiTab
	public function getPreLoadContent() {
		return '';
	}

	// Implement KT_Module_IndiTab
	public function getTabContent() {
		global $controller,$SHOW_COUNTER, $SEARCH_SPIDER;
		require_once(KT_ROOT . 'includes/functions/functions_attributes.php');

		ob_start();
		$indifacts = $controller->getIndiFacts();

		$xrefData = array(
			'label' => KT_I18N::translate('Internal reference '),
			'detail'=> '<span>' . $controller->record->getXref() . '</span>',
		);
		if ($SHOW_COUNTER && (empty($SEARCH_SPIDER))) {
			require KT_ROOT . 'includes/hitcount.php';
			$hitData = array(
				'label' => KT_I18N::translate('Hit Count:'),
				'detail'=> '<span>' . $hitCount . '</span>',
			);
		}

		if (count($indifacts) == 0) { ?>
			<div class="callout alert">
				<?php echo KT_I18N::translate('There are no attributes for this individual.'); ?>
			</div>
		<?php } else { ?>
			<div class="cell tabHeader">
				&nbsp;
			</div>

			<!-- Xref id -->
			<div class="cell indiFact">
				<div class="grid-x">
					<div class="cell small-10 medium-3 small-order-1 medium-order-1 event">
						<span class="h6"><?php echo $xrefData['label']; ?></span>
					</div>
					<div class="cell <?php echo (KT_USER_CAN_EDIT ? 'small-10 medium-7' : 'auto'); ?> small-order-5 medium-order-4 detail">
						<?php echo $xrefData['detail']; ?>
					</div>
				</div>
			</div>
			<!-- Privacy status -->
			<div class="cell indiFact">
				<div class="grid-x">
					<div class="cell small-10 medium-3 small-order-1 medium-order-1 event">
						<span class="h6"><?php echo KT_I18N::translate('Privacy status'); ?></span>
					</div>
					<div class="cell <?php echo (KT_USER_CAN_EDIT ? 'small-10 medium-8' : 'auto'); ?> small-order-5 medium-order-4 detail">
						<?php echo privacyStatus($this->getName()); ?>
					</div>
				</div>
			</div>

			<!--  All GEDCOM attribute facts -->
			<?php foreach ($indifacts as $fact) {
				if (KT_Gedcom_Tag::isTagAttribute($fact->getTag())) {
					$styleadd = '';
					if ($fact->getIsNew()) {
						$styleadd = 'change_new';
					}
					if ($fact->getIsOld()) {
						$styleadd = 'change_old';
					} ?>
					<div class="cell indiFact <?php echo $styleadd; ?>">
						<div class="grid-x">
							<div class="cell small-12 medium-3 small-order-1 medium-order-1 event">
								<?php print_attributes_label($fact, $controller->record); ?>
							</div>
							<div class="cell <?php echo(KT_USER_CAN_EDIT ? 'small-10 medium-6' : 'auto'); ?> small-order-5 medium-order-4 detail">
								<?php print_attributes_detail($fact, $controller->record); ?>
							</div>
							<div class="cell small-12 medium-3 small-order-2 medium-order-5 edit">
								<?php print_attributes_edit($fact, $controller->record, $styleadd, ''); ?>
							</div>
						</div>
					</div>
				<?php }
			} ?>

			<!-- Hit count -->
			<div class="cell indiFact">
				<div class="grid-x">
					<div class="cell small-10 medium-3 small-order-1 medium-order-1 event">
						<span class="h6"><?php echo $hitData['label']; ?></span>
					</div>
					<div class="cell <?php echo (KT_USER_CAN_EDIT ? 'small-10 medium-8' : 'auto'); ?> small-order-5 medium-order-4 detail">
						<?php echo $hitData['detail']; ?>
					</div>
				</div>
			</div>
			<!-- New fact link -->
			<?php if ($controller->record->canEdit()) {
				print_add_new_fact($controller->record->getXref(), $indifacts, 'INDI_ATTRIB');
			}
		}

		return ob_get_clean();

	}

}
