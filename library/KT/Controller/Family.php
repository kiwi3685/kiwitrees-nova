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

require_once KT_ROOT.'includes/functions/functions_print_facts.php';
require_once KT_ROOT.'includes/functions/functions_import.php';

class KT_Controller_Family extends KT_Controller_GedcomRecord {
	var $diff_record;
	var $record = null;
	var $user = null;
	var $display = false;
	var $famrec = '';
	var $title = '';
	public $tabs;

	public function __construct() {
		global $Dbwidth, $bwidth, $pbwidth, $pbheight, $bheight;
		$bwidth = $Dbwidth;
		$pbwidth = $bwidth + 12;
		$pbheight = $bheight + 14;

		$xref = safe_GET_xref('famid');

		$gedrec = find_family_record($xref, KT_GED_ID);

		if (empty($gedrec)) {
			$gedrec = "0 @".$xref."@ FAM\n";
		}

		if (find_family_record($xref, KT_GED_ID) || find_updated_record($xref, KT_GED_ID)!==null) {
			$this->record = new KT_Family($gedrec);
			$this->record->ged_id=KT_GED_ID; // This record is from a file
		} else if (!$this->record) {
			parent::__construct();
			return;
		}

		$xref = $this->record->getXref(); // Correct upper/lower case mismatch

		//-- if the user can edit and there are changes then get the new changes
		if (KT_USER_CAN_EDIT) {
			$newrec = find_updated_record($xref, KT_GED_ID);
			if (!empty($newrec)) {
				$this->diff_record = new KT_Family($newrec);
				$this->diff_record->setChanged(true);
				$this->record->diffMerge($this->diff_record);
			}
		}

		$this->tabs = KT_Module::getActiveFamTabs();

		parent::__construct();
	}

	// Get significant information from this page, to allow other pages such as
	// charts and reports to initialise with the same records
	public function getSignificantIndividual() {
		if ($this->record) {
			foreach ($this->record->getSpouses() as $individual) {
				return $individual;
			}
			foreach ($this->record->getChildren() as $individual) {
				return $individual;
			}
		}
		return parent::getSignificantIndividual();
	}
	public function getSignificantFamily() {
		if ($this->record) {
			return $this->record;
		}
		return parent::getSignificantFamily();
	}

	// $tags is an array of HUSB/WIFE/CHIL
	function getTimelineIndis($tags) {
		preg_match_all('/\n1 (?:'.implode('|', $tags).') @('.KT_REGEX_XREF.')@/', $this->record->getGedcomRecord(), $matches);
		foreach ($matches[1] as &$match) {
			$match='pids[]='.$match;
		}
		return implode('&amp;', $matches[1]);
	}

	/**
	* get edit menu
	*/
	function getEditMenu() {
		$SHOW_GEDCOM_RECORD=get_gedcom_setting(KT_GED_ID, 'SHOW_GEDCOM_RECORD');

		if (!$this->record || $this->record->isMarkedDeleted()) {
			return null;
		}

		// edit menu
		$menu = new KT_Menu('<span>' . KT_I18N::translate('Edit') . '</span>', '#', 'menu-fam');
		$menu->addLabel($menu->label, 'down');
		$menu->addClass('', '', 'fa-pen-to-square');

		if (KT_USER_CAN_EDIT) {
			// edit_fam / members
			$submenu = new KT_Menu(KT_I18N::translate('Change Family Members'), '#', 'menu-fam-change');
			$submenu->addOnclick("return change_family_members('".$this->record->getXref()."');");
			$menu->addSubmenu($submenu);

			// edit_fam / add child
			$submenu = new KT_Menu(KT_I18N::translate('Add a child to this family'), '#', 'menu-fam-addchil');
			$submenu->addOnclick("return addnewchild('".$this->record->getXref()."');");
			$menu->addSubmenu($submenu);

			// edit_fam / reorder_children
			if ($this->record->getNumberOfChildren() > 1) {
				$submenu = new KT_Menu(KT_I18N::translate('Re-order children'), '#', 'menu-fam-orderchil');
				$submenu->addOnclick("return reorder_children('".$this->record->getXref()."');");
				$menu->addSubmenu($submenu);
			}
		}

		// edit/view raw gedcom
		if (KT_USER_IS_ADMIN || $SHOW_GEDCOM_RECORD) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit raw GEDCOM record'), '#', 'menu-fam-editraw');
			$submenu->addOnclick("return edit_raw('".$this->record->getXref()."');");
			$menu->addSubmenu($submenu);
		} elseif ($SHOW_GEDCOM_RECORD) {
			$submenu = new KT_Menu(KT_I18N::translate('View GEDCOM Record'), '#', 'menu-fam-viewraw');
			if (KT_USER_CAN_EDIT) {
				$submenu->addOnclick("return show_gedcom_record('new');");
			} else {
				$submenu->addOnclick("return show_gedcom_record();");
			}
			$menu->addSubmenu($submenu);
		}

		// delete
		if (KT_USER_CAN_EDIT) {
			$submenu = new KT_Menu(KT_I18N::translate('Delete'), '#', 'menu-fam-del');
			$submenu->addOnclick("if (confirm('".KT_I18N::translate('Deleting the family will unlink all of the individuals from each other but will leave the individuals in place.  Are you sure you want to delete this family?')."')) jQuery.post('action.php',{action:'delete-family',xref:'".$this->record->getXref()."'},function(){location.reload();})");
			$menu->addSubmenu($submenu);
		}

		// add to favorites
		if (array_key_exists('widget_favorites', KT_Module::getActiveModules())) {
			$submenu = new KT_Menu(
				/* I18N: Menu option.  Add [the current page] to the list of favorites */ KT_I18N::translate('Add to favorites'),
				'#',
				'menu-fam-addfav'
			);
			$submenu->addOnclick("jQuery.post('module.php?mod=widget_favorites&amp;mod_action=menu-add-favorite',{xref:'".$this->record->getXref()."'},function(){location.reload();})");
			$menu->addSubmenu($submenu);
		}

		//-- get the link for the first submenu and set it as the link for the main menu
		if (isset($menu->submenus[0])) {
			$link = $menu->submenus[0]->onclick;
			$menu->addOnclick($link);
		}
		return $menu;
	}

	// Get significant information from this page, to allow other pages such as
	// charts and reports to initialise with the same records
	public function getSignificantSurname() {
		if ($this->record && $this->record->getHusband()) {
			list($surn) = explode(',', $this->record->getHusband()->getSortname());
			return $surn;
		} else {
			return '';
		}
	}

	// Print the facts
	public function printFamilyFacts() {
		global $linkToID;

		$linkToID = $this->record->getXref(); // -- Tell addmedia.php what to link to

		/* Set width */
		if (KT_USER_CAN_EDIT) {
			$fam_width1 = 'medium-8';
			$fam_width2 = 'medium-4';
		} else {
			$fam_width1 = '';
			$fam_width2 = '';
		}

		$famFacts = $this->record->getFacts();
		if ($famFacts) { ?>
			<div class="cell <?php echo $fam_width1; ?>">
				<div class="grid-x">
						<?php
						sort_facts($famFacts);
						foreach ($famFacts as $fact) {
							print_fact($fact, $this->record);
						}
						print_main_media($this->record->getXref());
						?>
				</div>
			</div>
		<?php } else { ?>
			<div class="callout warning">
				<?php echo KT_I18N::translate('No facts for this family.'); ?>
			</div>
		<?php }

		if (KT_USER_CAN_EDIT) { ?>
			<div class="cell <?php echo $fam_width2; ?>">
				<div class="grid-x">
					<div class="cell">
						<h6 class="text-center"><?php echo KT_I18N::translate('Add new family information'); ?></h6>
					</div>
					<?php echo print_add_new_fact($this->record->getXref(), $famFacts, 'FAM'); ?>
					<div class="cell">
						<a href="#" onclick="return add_new_record('<?php echo $this->record->getXref(); ?>','NOTE');">
							<?php echo KT_I18N::translate('Add a note'); ?>
						</a>
						<?php echo help_link('add_note'); ?>
					</div>
					<div class="cell">
						<a href="#" onclick="return add_new_record('<?php echo $this->record->getXref(); ?>','SHARED_NOTE');">
							<?php echo KT_I18N::translate('Add a shared note'); ?>
						</a>
						<?php echo help_link('add_shared_note'); ?>
					</div>
					<?php if (get_gedcom_setting(KT_GED_ID, 'MEDIA_UPLOAD') >= KT_USER_ACCESS_LEVEL) { ?>
						<div class="cell">
							<a href="#" onclick="window.open('addmedia.php?action=showmediaform&amp;linktoid=<?php echo $this->record->getXref(); ?>', '_blank', edit_window_specs); return false;">
								<?php echo KT_I18N::translate('Add a media object'); ?>
							</a>
							<?php echo help_link('OBJE'); ?>
						</div>
						<div class="cell">
							<a href="#" onclick="window.open('inverselink.php?linktoid=<?php echo $this->record->getXref(); ?>&amp;linkto=family', '_blank', find_window_specs); return false;">
								<?php echo KT_I18N::translate('Link to an existing media object'); ?>
							</a>
						</div>
					<?php } ?>
					<div class="cell famFact">
						<a href="#" onclick="return add_new_record('<?php echo $this->record->getXref(); ?>','SOUR');">
							<?php echo KT_I18N::translate('Add a source citation'); ?>
						</a>
						<?php echo help_link('add_source'); ?>
					</div>
				</div>
			</div>
		<?php }
	}
}
