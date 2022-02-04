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

class tabi_families_KT_Module extends KT_Module implements KT_Module_IndiTab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Families');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Families” module */ KT_I18N::translate('A tab showing the close relatives of an individual.');
	}

	// Extend class KT_Module_IndiTab
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_IndiTab
	public function defaultTabOrder() {
		return 20;
	}

	// Implement KT_Module_IndiTab
	public function hasTabContent() {
		return true;
	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut() {
		return false;
	}

	// Implement KT_Module_IndiTab
	public function canLoadAjax() {
		return false; // Search engines cannot use AJAX
	}

	// Implement KT_Module_IndiTab
	public function getPreLoadContent() {
		return '';
	}

	// Implement KT_Module_IndiTab
	public function getTabContent() {
		global $GEDCOM, $ABBREVIATE_CHART_LABELS;
		global $iconStyle, $show_full, $personcount, $controller;

		$controller->addInlineJavascript('
			persistent_toggle("checkbox_elder", ".elderdate");
			persistent_toggle("checkbox_rela", ".fam_rela");
		');

		if (isset($show_full)) $saved_show_full = $show_full; // We always want to see full details here
		$show_full = 1;

		$saved_ABBREVIATE_CHART_LABELS = $ABBREVIATE_CHART_LABELS;
		$ABBREVIATE_CHART_LABELS = false; // Override GEDCOM configuration

		ob_start();
			$personcount	= 0;
			$families		= $controller->record->getChildFamilies(); ?>
			<div class="cell tabHeader">
				<div class="grid-x">
					<div class="cell shrink">
						<input id="checkbox_elder" type="checkbox" checked>
						<label for="checkbox_elder"><?php echo KT_I18N::translate('Show date differences'); ?></label>
					</div>
					<div class="cell shrink">
						<input id="checkbox_rela" type="checkbox" checked>
						<label for="checkbox_rela"><?php echo KT_I18N::translate('Show relationships'); ?></label>
					</div>
					<?php if (count($families) == 0 && $controller->record->canEdit()) { ?>
						<div class="cell auto">
							<a href="#" onclick="return addnewparent('<?php echo $controller->record->getXref(); ?>', 'HUSB');">
								<i class="<?php echo $iconStyle; ?> fa-mars"></i>
								<?php echo KT_I18N::translate('Add a father'); ?>
							</a>
							<a href="#" onclick="return addnewparent('<?php echo $controller->record->getXref(); ?>', 'WIFE');">
								<i class="<?php echo $iconStyle; ?> fa-venus"></i>
								<?php echo KT_I18N::translate('Add a mother'); ?>
							</a>
						</div>
					<?php } ?>
				</div>
			</div>

			<?php // parents
			foreach ($families as $family) {
				$people = $controller->buildFamilyList($family, "parents"); ?>
				<div class="cell indiFact">
					<div class="grid-x">
						<?php $this->printFamilyHeader($family, 'FAMC', $controller->record->getChildFamilyLabel($family), $people); ?>
						<?php $this->printParentsRows($family, $people, "parents"); ?>
					</div>
				</div>
			<?php }

			// step-parents
			foreach ($controller->record->getChildStepFamilies() as $family) {
				$people = $controller->buildFamilyList($family, "step-parents"); ?>
				<div class="cell indiFact">
					<div class="grid-x">
						<?php $this->printFamilyHeader($family, 'FAMC', $controller->record->getStepFamilyLabel($family), $people);
						$this->printParentsRows($family, $people, "parents");
						$this->printChildrenRows($family, $people, "parents"); ?>
					</div>
				</div>
			<?php }

			// spouses
			$families = $controller->record->getSpouseFamilies();
			foreach ($families as $family) {
				$people = $controller->buildFamilyList($family, "spouse"); ?>
				<div class="cell indiFact">
					<div class="grid-x">
						<?php $this->printFamilyHeader($family, 'FAMS', $controller->record->getSpouseFamilyLabel($family), $people);
						$this->printParentsRows($family, $people, "spouse");
						$this->printChildrenRows($family, $people, "spouse"); ?>
					</div>
				</div>
			<?php }

			// step-children
			foreach ($controller->record->getSpouseStepFamilies() as $family) {
				$people = $controller->buildFamilyList($family, "step-children"); ?>
				<div class="cell indiFact">
					<div class="grid-x">
						<?php $this->printFamilyHeader($family, 'FAMS', $family->getFullName(), $people);
						$this->printParentsRows($family, $people, "spouse");
						$this->printChildrenRows($family, $people, "spouse"); ?>
					</div>
				</div>
			<?php }

			$ABBREVIATE_CHART_LABELS = $saved_ABBREVIATE_CHART_LABELS; // Restore GEDCOM configuration
			unset($show_full);
			if (isset($saved_show_full)) $show_full = $saved_show_full;

		return '
			<div id="' . $this->getName() . '_content" class="grid-x grid-padding-y">' .
				ob_get_clean() . '
			</div>
		';
	}

	function printFamilyHeader(KT_Family $family, $type, $label, $people) {
		global $iconStyle; ?>
		<div class="cell subHeader">
			<span class="h5"><?php echo $label; ?></span>
			<a href="<?php echo $family->getHtmlUrl(); ?>">
				 <i class="<?php echo $iconStyle; ?> fa-users"></i>
				 <?php echo KT_USER_CAN_EDIT ? KT_I18N::translate('Edit family') : KT_I18N::translate('View family'); ?>
			 </a>
		 </div>
		<?php if (
			array_key_exists('chart_relationship', KT_Module::getActiveModules()) &&
				KT_USER_ID && (
				($type == 'FAMC' && get_gedcom_setting(KT_GED_ID, 'TAB_REL_OF_PARENTS') > 0) ||
				($type == 'FAMS' && get_gedcom_setting(KT_GED_ID, 'TAB_REL_TO_SPOUSE') > 0)
			)
		) { ?>
			<div class="cell fam_rela"><?php echo printFamilyRelationship($type, $people); ?></div>
		<?php }
	}

	/**
	* print parents informations
	* @param Family family
	* @param Array people
	* @param String family type
	*/
	function printParentsRows($family, $people, $type) {
		global $personcount, $SHOW_PEDIGREE_PLACES, $controller, $SEARCH_SPIDER;
		global $iconStyle;

		$elderdate = "";
		//-- new father/husband
		$styleadd = "";
		if (isset($people["newhusb"])) {
			$styleadd = "red";
			?>
			<div class="cell">
				<div class="grid-x">
					<div class="cell medium-2 facts_labelblue">
						<?php echo $people["newhusb"]->getLabel(); ?>
					</div>
					<div class="cell medium-9 <?php echo $controller->getPersonStyle($people["newhusb"]); ?>">
						<?php print_pedigree_person($people["newhusb"], 2, 0, $personcount++); ?>
					</div>
				</div>
			</div>
			<?php
			$elderdate = $people["newhusb"]->getBirthDate();
		}
		//-- father/husband
		if (isset($people["husb"])) { ?>
			<div class="cell">
				<div class="grid-x">
					<div class="cell medium-2 facts_label<?php echo $styleadd; ?>">
						<?php echo $people["husb"]->getLabel();
						if ($controller->record->equals($people["husb"])) { ?>
							<i class="<?php echo $iconStyle; ?> fa-check"></i>
							<?php echo reflexivePronoun($controller->record);
						} else {
							echo get_relationship_name(get_relationship($controller->record, $people["husb"], true, 3));
						} ?>
					</div>
					<div class="cell medium-9 <?php echo $controller->getPersonStyle($people["husb"]); ?>">
						<?php print_pedigree_person($people["husb"], 2, 0, $personcount++); ?>
					</div>
				</div>
			</div>
			<?php
			$elderdate = $people["husb"]->getBirthDate();
		}
		//-- missing father
		if ($type=="parents" && !isset($people["husb"]) && !isset($people["newhusb"])) {
			if ($controller->record->canEdit()) { ?>
				<div class="cell subHeader">
					<div class="grid-x">
						<div class="cell medium-2 facts_label">
							<?php echo KT_I18N::translate('Add a father'); ?>
						</div>
						<div class="cell medium-9 facts_value">
							<a href="#" onclick="return addnewparentfamily('<?php echo $controller->record->getXref(); ?>', 'HUSB', '<?php echo $family->getXref(); ?>');">
								<?php echo KT_I18N::translate('Add a father'); ?>
							</a>
						</div>
					</div>
				</div>
			<?php }
		}
		//-- missing husband
		if ($type=="spouse" && !isset($people["husb"]) && !isset($people["newhusb"])) {
			if ($controller->record->canEdit()) { ?>
				<div class="cell subHeader">
					<div class="grid-x">
						<div class="cell medium-2 facts_label">
							<?php echo KT_I18N::translate('Add husband'); ?>
						</div>
						<div class="cell medium-9 facts_value">
							<a href="#" onclick="return addnewspouse('<?php echo $controller->record->getXref(); ?>', '<?php echo $family->getXref(); ?>', 'HUSB');">
								<?php echo KT_I18N::translate('Add a husband to this family'); ?>
							</a>
						</div>
					</div>
				</div>
			<?php }
		}
		//-- new mother/wife
		$styleadd = "";
		if (isset($people["newwife"])) {
			$styleadd = "red";
			?>
			<div class="cell">
				<div class="grid-x">
					<div class="cell medium-2 facts_labelblue">
						<?php echo $people["newwife"]->getLabel($elderdate); ?>
					</div>
					<div class="cell medium-9 <?php echo $controller->getPersonStyle($people["newwife"]); ?>">
						<?php print_pedigree_person($people["newwife"], 2, 0, $personcount++); ?>
					</div>
				</div>
			</div>
			<?php
		}
		//-- mother/wife
		if (isset($people["wife"])) {
			?>
			<div class="cell">
				<div class="grid-x">
					<div class="cell medium-2<?php echo $styleadd; ?>">
						<?php echo $people["wife"]->getLabel($elderdate);
						if ($controller->record->equals($people["wife"])) { ?>
							<i class="<?php echo $iconStyle; ?> fa-check"></i>
							<?php echo reflexivePronoun($controller->record);
						} else {
							echo get_relationship_name(get_relationship($controller->record, $people["wife"], true, 3));
						} ?>
					</div>
					<div class="cell medium-9 <?php echo $controller->getPersonStyle($people["wife"]); ?>">
						<?php print_pedigree_person($people["wife"], 2, 0, $personcount++); ?>
					</div>
				</div>
			</div>
			<?php
		}
		//-- missing mother
		if ($type=="parents" && !isset($people["wife"]) && !isset($people["newwife"])) {
			if ($controller->record->canEdit()) { ?>
				<div class="cell subHeader">
					<div class="grid-x">
						<div class="cell medium-2 facts_label">
							<?php echo KT_I18N::translate('Add a mother'); ?>
						</div>
						<div class="cell medium-9 facts_value">
							<a href="#" onclick="return addnewparentfamily('<?php echo $controller->record->getXref(); ?>', 'WIFE', '<?php echo $family->getXref(); ?>');">
								<?php echo KT_I18N::translate('Add a mother'); ?>
							</a>
						</div>
					</div>
				</div>
			<?php }
		}
		//-- missing wife
		if ($type=="spouse" && !isset($people["wife"]) && !isset($people["newwife"])) {
			if ($controller->record->canEdit()) { ?>
				<div class="cell subHeader">
					<div class="grid-x">
						<div class="cell medium-2 facts_label">
							<?php echo KT_I18N::translate('Add wife'); ?>
						</div>
						<div class="cell medium-9 facts_value">
							<a href="#" onclick="return addnewspouse('<?php echo $controller->record->getXref(); ?>','<?php echo $family->getXref(); ?>', 'WIFE');">
								<?php echo KT_I18N::translate('Add a wife to this family'); ?>
							</a>
						</div>
					</div>
				</div>
			<?php }
		}
		//-- marriage row
		if ($family->getMarriageRecord()!="" || KT_USER_CAN_EDIT) {
			?>
			<div class="cell">
				<div class="grid-x">
					<div class="cell medium-2 <?php echo $styleadd; ?>">
					</div>
					<div class="cell medium-9">
						<?php $marr_type = strtoupper($family->getMarriageType());
						if ($marr_type=='CIVIL' || $marr_type=='PARTNERS' || $marr_type=='RELIGIOUS' || $marr_type=='COML' || $marr_type=='UNKNOWN') {
							$marr_fact = 'MARR_' . $marr_type;
						} else {
							$marr_fact = 'MARR';
						}
						$famid = $family->getXref();
						$place = $family->getMarriagePlace();
						$date = $family->getMarriageDate();
						if ($date && $date->isOK() || $place) {
							if ($date) {
								$details=$date->Display(false);
							}
							if ($place) {
								if ($details) {
									$details .= ' — ';
								}
								$tmp=new KT_Place($place, KT_GED_ID);
								$details .= $tmp->getShortName();
							}
							echo KT_Gedcom_Tag::getLabelValue($marr_fact, $details);
						} else if (get_sub_record(1, "1 _NMR", find_family_record($famid, KT_GED_ID))) {
							$husb = $family->getHusband();
							$wife = $family->getWife();
							if (empty($wife) && !empty($husb)) {
								echo KT_Gedcom_Tag::getLabel('_NMR', $husb);
							} elseif (empty($husb) && !empty($wife)) {
								echo KT_Gedcom_Tag::getLabel('_NMR', $wife);
							} else {
								echo KT_Gedcom_Tag::getLabel('_NMR');
							}
						} else if ($family->getMarriageRecord()=="" && $controller->record->canEdit()) {
							echo "<a href=\"#\" onclick=\"return add_new_record('".$famid."', 'MARR');\">".KT_I18N::translate('Add marriage details')."</a>";
						} else {
							echo KT_Gedcom_Tag::getLabelValue($marr_fact, KT_I18N::translate('yes'));
						}
						?>
					</div>
				</div>
			</div>
		<?php }
	}

	/**
	* print children informations
	* @param Family family
	* @param Array people
	* @param String family type
	*/
	function printChildrenRows($family, $people, $type) {
		global $personcount, $controller, $iconStyle;
		$elderdate	= $family->getMarriageDate();
		$key		= 0;
		foreach ($people["children"] as $child) {
			$label		= $child->getLabel();
			$styleadd	= '';
			?>
			<div class="cell">
				<div class="grid-x">
					<div class="cell medium-2 facts_label<?php echo $styleadd; ?>">
						<?php if ($styleadd=="red") {
							echo $child->getLabel();
						} else {
							echo $child->getLabel($elderdate, $key+1);
						}
						if ($controller->record->equals($child)) { ?>
							<i class="<?php echo $iconStyle; ?> fa-check"></i>
							<?php echo reflexivePronoun($controller->record);
						} else {
							echo get_relationship_name(get_relationship($controller->record, $child, true, 3));
						} ?>
					</div>
					<div class="cell medium-9 <?php echo $controller->getPersonStyle($child); ?>">
						<?php print_pedigree_person($child, 2, 0, $personcount++); ?>
					</div>
				</div>
			</div>
			<?php $elderdate = $child->getBirthDate();
			++ $key;
		}
		foreach ($people["newchildren"] as $child) {
			$label		= $child->getLabel();
			$styleadd	= "blue"; ?>
			<div class="cell">
				<div class="grid-x">
					<div class="cell medium-2 facts_label<?php echo $styleadd; ?>">
						<?php if ($styleadd=="red") {
							echo $child->getLabel();
						} else {
							echo $child->getLabel($elderdate, $key+1);
						} ?>
					</div>
					<div class="cell medium-9 <?php echo $controller->getPersonStyle($child); ?>">
						<?php print_pedigree_person($child, 2, 0, $personcount++); ?>
					</div>
				</div>
			</div>
			<?php $elderdate = $child->getBirthDate();
			++ $key;
		}
		foreach ($people["delchildren"] as $child) {
			$label = $child->getLabel();
			$styleadd = "red"; ?>
			<div class="cell">
				<div class="grid-x">
					<div class="cell medium-2 facts_label<?php echo $styleadd; ?>">
						<?php if ($styleadd=="red"){
							echo $child->getLabel();
						} else {
							echo $child->getLabel($elderdate, $key+1);
						} ?>
					</div>
					<div class="cell medium-9 <?php echo $controller->getPersonStyle($child); ?>">
						<?php print_pedigree_person($child, 2, 0, $personcount++); ?>
					</div>
				</div>
			</div>
		<?php }

		if (isset($family) && $controller->record->canEdit()) {
			if ($type == "spouse") {
				$child_u = KT_I18N::translate('Add a son or daughter');
				$child_m = KT_I18N::translate('son');
				$child_f = KT_I18N::translate('daughter');
			} else {
				$child_u = KT_I18N::translate('Add a brother or sister');
				$child_m = KT_I18N::translate('brother');
				$child_f = KT_I18N::translate('sister');
			} ?>
			<div class="cell subHeader">
				<div class="grid-x">
					<div class="cell medium-2 facts_label">
						<?php if (KT_USER_CAN_EDIT && isset($people["children"][1])) { ?>
							<a href="edit_interface.php?action=reorder_children
								&amp;pid=<?php echo $family->getXref(); ?>
								&amp;accesstime=<?php echo KT_TIMESTAMP; ?>
								&amp;ged=<?php echo KT_GEDCOM; ?>"
							 target="_blank">
								<i class="<?php echo $iconStyle; ?> fa-random"></i>
								<?php echo KT_I18N::translate('Re-order children'); ?>
							</a>
						<?php } ?>
					</div>
					<div class="cell medium-9">
						<a href="edit_interface.php?action=addchild
							&amp;gender=
							&amp;famid=<?php echo $family->getXref(); ?>
							&amp;accesstime=<?php echo KT_TIMESTAMP; ?>
							&amp;ged=<?php echo KT_GEDCOM; ?>"
						 target="_blank">
						 	<?php echo $child_u; ?>
						</a>
						<span style="white-space:nowrap;">
							<a href="edit_interface.php?action=addchild
								&amp;gender=M
								&amp;famid=<?php echo $family->getXref(); ?>
								&amp;accesstime=<?php echo KT_TIMESTAMP; ?>
								&amp;ged=<?php echo KT_GEDCOM; ?>"
							 target="_blank">
							 	<i class="<?php echo $iconStyle; ?> fa-male"></i>
							</a>
							<a href="edit_interface.php?action=addchild
								&amp;gender=F
								&amp;famid=<?php echo $family->getXref(); ?>
								&amp;accesstime=<?php echo KT_TIMESTAMP; ?>
								&amp;ged=<?php echo KT_GEDCOM; ?>"
							 target="_blank">
							 	<i class="<?php echo $iconStyle; ?> fa-female"></i>
							</a>
						</span>
					</div>
				</div>
			</div>
			<?php
		}
	}

}
