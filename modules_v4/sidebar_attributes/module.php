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

class sidebar_attributes_KT_Module extends KT_Module implements KT_Module_Sidebar {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/sidebar */ KT_I18N::translate('Attributes');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Extra information” module */ KT_I18N::translate('A sidebar showing an individuals attrubutes.');
	}

	// Implement KT_Module_Sidebar
	public function defaultSidebarOrder() {
		return 10;
	}

	// Implement KT_Module_Sidebar
	public function hasSidebarContent() {
		return true;
	}

	// Implement KT_Module_Sidebar
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Sidebar
	public function getSidebarContent() {
		global $SHOW_COUNTER, $controller;
		require_once(KT_ROOT . 'includes/functions/functions_attributes.php');

		$indifacts = array();
		// The individual’s own facts
		foreach ($controller->record->getIndiFacts() as $fact) {
				$indifacts[] = $fact;
		}
		ob_start();
		$indifacts = $controller->getIndiFacts();

		if ($SHOW_COUNTER && (empty($SEARCH_SPIDER))) {
			require KT_ROOT . 'includes/hitcount.php';
			$hitData = array(
				'label' => KT_I18N::translate('Hit Count:'),
				'detail'=> $hitCount,
			);
		}
		if (count($indifacts) == 0) { ?>
			<div class="callout alert">
				<?php echo KT_I18N::translate('There are no attributes for this individual.'); ?>
			</div>
		<?php } else { ?>

			<div class="cell" id="sb_content_sidebar_attributes">
				<!-- Xref id -->
				<div class="grid-x attribute">
					<label class="cell small-6 middle">
						<?php echo KT_I18N::translate('Internal reference '); ?>
					</label>
					<div class="cell small-6">
						<?php echo $controller->record->getXref(); ?>
					</div>
				</div>

				<!-- Privacy status -->
				<div class="grid-x attribute">
					<label class="cell small-6 middle">
						<?php echo KT_I18N::translate('Privacy status'); ?>
					</label>
					<div class="cell small-6">
						<?php echo privacyStatus($this->getName()); ?>
					</div>
				</div>

				<!-- All GEDCOM attribute facts -->
				<?php foreach ($indifacts as $fact) {
					if (KT_Gedcom_Tag::isTagAttribute($fact->getTag())) {
						$styleadd = "";
						if ($fact->getIsNew()) {
							$styleadd = "change_new";
						}
						if ($fact->getIsOld()) {
							$styleadd = "change_old";
						} ?>
						<div class="grid-x attribute <?php echo $styleadd; ?>">
							<label class="cell small-6 order-1 event">
								<?php print_attributes_label($fact, $controller->record); ?>
							</label>
							<div class="cell small-6 order-2 edit">
								<?php print_attributes_edit($fact, $controller->record, '', 'sidebar'); ?>
							</div>
							<div class="cell order-3 detail">
								<?php print_attributes_detail($fact, $controller->record); ?>
							</div>
						</div>
					<?php }
				} ?>

				<!-- Hit count -->
				<div class="grid-x attribute">
					<label class="cell small-6 middle">
						<?php echo $hitData['label']; ?>
					</label>
					<div class="cell small-6">
						<?php echo $hitData['detail']; ?>
					</div>
				</div>
			<?php
			//-- new fact link
			if ($controller->record->canEdit()) {
				print_add_new_fact($controller->record->getXref(), $indifacts, 'SB_ATTRIB');
			} ?>
		</div>

		<?php }

		return ob_get_clean();
	}

	// Implement KT_Module_Sidebar
	public function getSidebarAjaxContent() {
		return '';
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'none';
	}

}
