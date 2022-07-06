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
				</div>
				<div class="cell show-for-medium indiFactHeader">
					<div class="grid-x">
						<div class="cell medium-3 event">
							<label><?php echo KT_I18N::translate('Attribute'); ?></label>
						</div>
						<div class="cell <?php echo (KT_USER_CAN_EDIT ? 'medium-8' : 'auto'); ?> detail">
							<label><?php echo KT_I18N::translate('Details'); ?></label>
						</div>
						<?php if (KT_USER_CAN_EDIT) { ?>
							<div class="cell medium-1 edit">
								<label><?php echo KT_I18N::translate('Edit'); ?></label>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="cell indiFact">
					<div class="grid-x">
						<div class="cell small-10 medium-3 small-order-1 medium-order-1 event">
							<span class="h6"><?php echo $xrefData['label']; ?></span>
						</div>
						<div class="cell <?php echo (KT_USER_CAN_EDIT ? 'small-10 medium-8' : 'auto'); ?> small-order-5 medium-order-4 detail">
							<?php echo $xrefData['detail']; ?>
						</div>
					</div>
				</div>
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

				<?php foreach ($indifacts as $fact) {
					if (KT_Gedcom_Tag::isTagAttribute($fact->getTag())) {
						$data = print_attributes($fact, $controller->record); ?>

						<div class="cell indiFact">
							<div class="grid-x">
								<div class="cell small-10 medium-3 small-order-1 medium-order-1 event">
									<span class="h6"><?php echo $data['label']; ?></span>
								</div>
								<div class="cell <?php echo (KT_USER_CAN_EDIT ? 'small-10 medium-8' : 'auto'); ?> small-order-5 medium-order-4 detail">
									<?php echo $data['detail']; ?>
								</div>
							</div>
						</div>

					<?php }
				}

				//-- new fact link
				if ($controller->record->canEdit()) {
					print_add_new_fact($controller->record->getXref(), $indifacts, 'INDI_ATTRIB');
				}
			}
			return '
				<div id="' . $this->getName() . '_content" class="grid-x grid-padding-y">' .
					ob_get_clean() . '
				</div>
			';
		}

}
