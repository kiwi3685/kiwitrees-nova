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

class tabi_census_KT_Module extends KT_Module implements KT_Module_IndiTab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('Census summary');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the "Facts and events" module */ KT_I18N::translate('A tab summarising census events for an individual.');
	}

	// Implement KT_Module_IndiTab
	public function defaultTabOrder() {
		return 100;
	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut() {
		return $this->getCensFacts() == null;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return false;
	}

	// Implement KT_Module_IndiTab
	public function getTabContent() {
		global $EXPAND_SOURCES, $EXPAND_NOTES, $controller, $iconStyle;
		$person		= $controller->getSignificantIndividual();
		$xref		= $controller->record->getXref();
		$facts		= $this->getCensFacts();

		ob_start();
		?>
			<div class="cell tabHeader">
				<div class="grid-x">
					<?php if (KT_USER_CAN_EDIT) { ?>
						<div class="cell small-2">
							<a href="edit_interface.php?action=add&pid=<?php echo $xref; ?>&fact=CENS&accesstime=<?php echo KT_TIMESTAMP; ?>&ged=<?php echo KT_GEDCOM; ?>" target="_blank">
								<i class="<?php echo $iconStyle; ?> fa-plus"></i>
								<?php echo KT_I18N::translate('Add census'); ?>
							</a>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="cell indiFact">
				<div class="grid-x">
					<?php if ($person && $person->canDisplayDetails() && $facts) { ?>
						<table>
							<thead>
								<tr>
									<th><?php echo KT_I18N::translate('Date'); ?></th>
									<th><?php echo KT_I18N::translate('Place'); ?></th>
									<th><?php echo KT_I18N::translate('Address'); ?></th>
									<th><?php echo KT_I18N::translate('Notes'); ?></th>
									<th><?php echo KT_I18N::translate('Sources'); ?></th>
									<th><?php echo KT_I18N::translate('Media'); ?></th>
									<?php if (KT_USER_CAN_EDIT) { ?>
										<th><?php echo KT_I18N::translate('Edit'); ?></th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($facts as $fact) {
									$styleadd = "";
									if ($fact->getIsNew()) $styleadd = "change_new";
									if ($fact->getIsOld()) $styleadd = "change_old";
									?>
									<tr>
										<td class="date nowrap"><?php echo $fact->getDate()->JD() != 0 ?  format_fact_date($fact, $person, false, false, true) : ""; ?></td>
										<td class="nowrap"><?php echo format_fact_place($fact, true); ?></td>
										<td class="nowrap"><?php echo print_address_structure($fact->getGedcomRecord(), 2, 'inline'); ?></td>
										<td><?php echo print_fact_notes($fact->getGedcomRecord(), 2); ?></td>
										<td><?php echo print_fact_sources($fact->getGedcomRecord(), 2, true, true); ?></td>
										<td><?php echo print_media_links($fact->getGedcomRecord(), 2, $xref); ?></td>
										<?php if (KT_USER_CAN_EDIT && $styleadd!='change_old' && $fact->getLineNumber()>0 && $fact->canEdit()) { ?>
											<td>
												<div class="editfacts button-group stacked">
													<a class="button clear" onclick="return edit_record('<?php echo $xref; ?>', <?php echo $fact->getLineNumber(); ?>);" title="<?php echo KT_I18N::translate('Edit'); ?>">
														<i class="<?php echo $iconStyle; ?> fa-edit"></i>
														<span class="link_text" tabindex="1">
															<?php echo KT_I18N::translate('Edit'); ?>
														</span>
													</a>
													<a class="button clear" onclick="jQuery.post('action.php',{action:'copy-fact', type:'<?php echo $fact->getParentObject()->getType(); ?>',factgedcom:'<?php echo rawurlencode($fact->getGedcomRecord()); ?>'},function(){location.reload();})" title="<?php echo  KT_I18N::translate('Copy'); ?>">
														<i class="<?php echo $iconStyle; ?> fa-copy"></i>
														<span class="link_text" tabindex="2">
															<?php echo KT_I18N::translate('Copy'); ?>
														</span>
													</a>
													<a class="button clear" onclick="return delete_fact('<?php echo $xref; ?>', <?php echo $fact->getLineNumber(); ?>, '', '<?php echo KT_I18N::translate('Are you sure you want to delete this fact?'); ?>');" title="<?php echo KT_I18N::translate('Delete'); ?>">
														<i class="<?php echo $iconStyle; ?> fa-trash-can"></i>
														<span class="link_text" tabindex="3">
															<?php echo KT_I18N::translate('Delete'); ?>
														</span>
													</a>
												</div>
											</td>
										<?php } ?>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php } ?>

				</div>
			</div>
		<?php return '
			<div id="' . $this->getName() . '_content" class="grid-x grid-padding-y">' .
				ob_get_clean() . '
			</div>
		';
	}

	// Implement KT_Module_IndiTab
	public function hasTabContent() {
		return KT_USER_CAN_EDIT || $this->getCensFacts();
	}

	// Implement KT_Module_IndiTab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_IndiTab
	public function getPreLoadContent() {
		return '';
	}

	private function getCensFacts() {
		global $controller;
		$person			= $controller->getSignificantIndividual();
		$fullname		= $controller->record->getFullName();
		$xref			= $controller->record->getXref();
		$indifacts		= $person->getIndiFacts();
		$censusFacts	= array();

		foreach ($indifacts as $fact) {
			if ($fact->getTag() === 'CENS') {
				$censusFacts[] = $fact;
			}
		}
		sort_facts($censusFacts);

		return $censusFacts;
	}

}
