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

class sidebar_family_nav_KT_Module extends KT_Module implements KT_Module_Sidebar {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/sidebar */ KT_I18N::translate('Family navigator');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Family navigator” module */ KT_I18N::translate('A sidebar showing an individual’s close families and relatives.');
	}

	// Implement KT_Module_Sidebar
	public function defaultSidebarOrder() {
		return 40;
	}

	// Implement KT_Module_Sidebar
	public function hasSidebarContent() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER;
	}

	// Implement KT_Module_Sidebar
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Sidebar
	public function getSidebarContent() {
		global $controller, $spouselinks, $parentlinks;
		$controller->addInlineJavascript('
			jQuery("#sb_family_nav_content")
				.on("click", ".flyout a", function() {
					return false;
				})
				.on("click", ".flyout3", function() {
					window.location.href = jQuery(this).data("href");
					return false;
				});
		');

		$person = KT_Person::getInstance($controller->record->getXref());

		ob_start();
		?>

		<div id="sb_family_nav_content">
			<table class="nav_content">
				<?php
				//-- parent families -------------------------------------------------------------
				foreach ($controller->record->getChildFamilies() as $family) {
					$this->drawFamily($family, $person->getChildFamilyLabel($family));
				}
				//-- step parents ----------------------------------------------------------------
				foreach ($controller->record->getChildStepFamilies() as $family) {
					$this->drawFamily($family, $person->getStepFamilyLabel($family));
				}
				//-- spouse and children --------------------------------------------------
				foreach ($controller->record->getSpouseFamilies() as $family) {
					$this->drawFamily($family, $person->getSpouseFamilyLabel($family, $controller->record));
				}
				//-- step children ----------------------------------------------------------------
				foreach ($controller->record->getSpouseStepFamilies() as $family) {
					$this->drawFamily($family, $family->getFullName());
				}
				?>
			</table>
		</div>
		<?php
		return ob_get_clean();
	}

	// Implement KT_Module_Sidebar
	public function getSidebarAjaxContent() {
		return '';
	}

	/**
	 * Format a family.
	 *
	 * @param Family $family
	 * @param string $title
	 */
	private function drawFamily(KT_Family $family, $title) {
		global $controller;
		?>
		<tr class="famnavTitleContainer">
			<td class="text-center" colspan="2">
				<a class="famnav_title" href="<?php echo $family->getHtmlUrl(); ?>">
					<?php echo $title; ?>
				</a>
			</td>
		</tr>
		<?php
		foreach ($family->getSpouses() as $spouse) {
			$menu = new KT_Menu(getCloseRelationshipName($controller->record, $spouse));
			$menu->addClass('', 'submenu');
			$menu->addSubmenu(new KT_Menu($this->getParents($spouse)));
			?>
			<tr>
				<td class="facts_label">
					<?php echo $menu->getMenu(); ?>
				</td>
				<td class="<?php echo $controller->getPersonStyle($spouse); ?>">
					<?php if ($spouse->canDisplayName()): ?>
						<a class="famnav_link" href="<?php echo $spouse->getHtmlUrl(); ?>">
							<?php echo $spouse->getFullName(); ?>
						</a>
						<div class="lifeSpan">
							<?php echo $spouse->getLifeSpan(); ?>
						</div>
					<?php else: ?>
						<?php echo $spouse->getFullName(); ?>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		}
		foreach ($family->getChildren() as $child) {
			$menu = new KT_Menu(getCloseRelationshipName($controller->record, $child));
			$menu->addClass('', 'submenu');
			$menu->addSubmenu(new KT_Menu($this->getFamily($child)));
			?>
			<tr>
				<td class="facts_label">
					<?php echo $menu->getMenu(); ?>
				</td>
				<td class="<?php echo $controller->getPersonStyle($child); ?>">
					<?php if ($child->canDisplayName()): ?>
					<a class="famnav_link" href="<?php echo $child->getHtmlUrl(); ?>">
						<?php echo $child->getFullName(); ?>
					</a>
					<div class="lifeSpan">
						<?php echo $child->getLifeSpan(); ?>
					</div>
					<?php else: ?>
						<?php echo $child->getFullName(); ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php
		}
	}

	/**
	 * Format the parents of an individual.
	 *
	 * @param KT_Person $person
	 *
	 * @return string
	 */
	private function getParents(KT_Person $person) {
		$father = null;
		$mother = null;
		$html   = '<div class="flyout2">' . KT_I18N::translate('Parents') . '</div>';
		$family = $person->getPrimaryChildFamily();
		if ($person->canDisplayName() && $family !== null) {
			$father = $family->getHusband();
			$mother = $family->getWife();
			$html .= $this->getHTML($father) .
					 $this->getHTML($mother);

			// Can only have a step parent if one & only one parent found at this point
			if ($father instanceof KT_Person xor $mother instanceof KT_Person) {
				$stepParents = '';
				foreach ($person->getChildStepFamilies() as $family) {
					if (!$father instanceof KT_Person) {
						$stepParents .= $this->getHTML($family->getHusband());
					} else {
						$stepParents .= $this->getHTML($family->getWife());
					}
				}
				if ($stepParents) {
					$relationship = $father instanceof KT_Person ?
						KT_I18N::translate_c("father’s wife", "step-mother") : KT_I18N::translate_c("mother’s husband", "step-father");
					$html .= '<div class="flyout2">' . $relationship . '</div>' . $stepParents;
				}
			}
		}
		if (!($father instanceof KT_Person || $mother instanceof KT_Person)) {
			$html .= '<div class="flyout4">(' . KT_I18N::translate_c('unknown family', 'unknown') . ')</div>';
		}
		return $html;
	}

	/**
	 * Format a family.
	 *
	 * @param Individual $person
	 *
	 * @return string
	 */
	private function getFamily(KT_Person $person) {
		$html = '';
		if ($person->canDisplayName()) {
			foreach ($person->getSpouseFamilies() as $family) {
				$spouse = $family->getSpouse($person);
				$html .= $this->getHTML($spouse, true);
				$children = $family->getChildren();
				if (count($children) > 0) {
					$html .= "<ul class='clist'>";
					foreach ($children as $child) {
						$html .= '<li>' . $this->getHTML($child) . '</li>';
					}
					$html .= '</ul>';
				}
			}
		}
		if (!$html) {
			$html = '<div class="flyout4">(' . KT_I18N::translate('none') . ')</div>';;
		}

		return '<div class="flyout2">' . KT_I18N::translate('Family') . '</div>' . $html;

	}

	/**
	 * Format an individual.
	 *
	 * @param      $person
	 * @param bool $showUnknown
	 *
	 * @return string
	 */
	private function getHTML($person, $showUnknown = false) {
		if ($person instanceof KT_Person) {
			return '<div class="flyout3" data-href="' . $person->getHtmlUrl() . '">' . $person->getFullName() . '</div>';
		} elseif ($showUnknown) {
			return '<div class="flyout4">(' . KT_I18N::translate('unknown') . ')</div>';
		} else {
			return '';
		}
	}

	function print_pedigree_person_nav($person) {
		global $SEARCH_SPIDER, $spouselinks, $parentlinks, $step_parentlinks;

		$persons 		  = false;
		$person_step 	  = false;
		$person_parent 	  = false;
		$natdad 		  = false;
		$natmom 		  = false;
		$spouselinks      = '';
		$parentlinks      = '';
		$step_parentlinks = '';

		if ($person->canDisplayName() && !$SEARCH_SPIDER) {
			//-- draw a box for the family
			$parentlinks      .= '<div class="flyout4">' . KT_I18N::translate('Parents') . '</div>';
			$step_parentlinks .= '<div class="flyout4">' . KT_I18N::translate('Parents') . '</div>';
			$spouselinks      .= '<div class="flyout4">' . KT_I18N::translate('Family' ) . '</div>';

			//-- parent families --------------------------------------
			$fams = $person->getChildFamilies();
			foreach ($fams as $family) {

				if (!is_null($family)) {
					$husb = $family->getHusband($person);
					$wife = $family->getWife($person);
					$children = $family->getChildren();

					// Husband ------------------------------
					if ($husb || $children) {
						if ($husb) {
							$person_parent = true;
							$parentlinks .= '<div class="flyout3" data-href="' . $husb->getHtmlUrl() . '">' . $husb->getFullName() . '</div>';
							$natdad = true;
						}
					}

					// Wife ------------------------------
					if ($wife || $children) {
						if ($wife) {
							$person_parent = true;
							$parentlinks .= '<div class="flyout3" data-href="' . $wife->getHtmlUrl() . '">' . $wife->getFullName() . '</div>';
							$natmom = true;
						}
					}
				}
			}

			//-- step families -----------------------------------------
			$fams = $person->getChildStepFamilies();
			foreach ($fams as $family) {
				if (!is_null($family)) {
					$husb = $family->getHusband($person);
					$wife = $family->getWife($person);
					$children = $family->getChildren();

					if (!$natdad) {
						// Husband -----------------------
						if ($husb || $children) {
							if ($husb) {
								$person_step = true;
								$parentlinks .= '<div class="flyout3" data-href="' . $husb->getHtmlUrl() . '">' . $husb->getFullName() . '</div>';
							}
						}
					}

					if (!$natmom) {
						// Wife ----------------------------
						if ($wife || $children) {
							if ($wife) {
								$person_step = true;
								$parentlinks .= '<div class="flyout3" data-href="' . $wife->getHtmlUrl() . '">' . $wife->getFullName() . '</div>';
							}
						}
					}
				}
			}

			// Spouse Families -------------------------------------- @var $family Family
			foreach ($person->getSpouseFamilies() as $family) {
				$spouse = $family->getSpouse($person);
				$children = $family->getChildren();

				// Spouse ------------------------------
				if ($spouse || $children) {
					if ($spouse) {
						$spouselinks .= '<div class="flyout3" data-href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</div>';
						$persons = true;
					}
				}

				// Children ------------------------------   @var $child Person
				foreach ($children as $child) {
					$persons = true;
					$spouselinks .= '
						<ul class="clist">
							<li class="flyout3" data-href="' . $child->getHtmlUrl() . '">' . $child->getFullName() . '</li>
						</ul>';
				}
			}
			if (!$persons) {
				$spouselinks .= '(' . KT_I18N::translate('none') . ')';
			}
			if (!$person_parent) {
				$parentlinks .= '(' . KT_I18N::translate_c('unknown family', 'unknown') . ')';
			}
			if (!$person_step) {
				$step_parentlinks .= '(' . KT_I18N::translate_c('unknown family', 'unknown') . ')';
			}
		}
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'none';
	}
}

