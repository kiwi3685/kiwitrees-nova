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

class tabi_cousins_KT_Module extends KT_Module implements KT_Module_IndiTab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('Cousins');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the "Facts and events" module */ KT_I18N::translate('A tab showing cousins of an individual.');
	}

	// Implement KT_Module_Tab
	public function defaultTabOrder() {
		return 80;
	}

	// Implement KT_Module_Tab
	public function isGrayedOut() {
		return false;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement KT_Module_Tab
	public function hasTabContent() {
		return true;
	}

	// Implement KT_Module_Tab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	// Implement KT_Module_Tab
	public function getTabContent() {
		global $controller, $iconStyle;

		$person			= $controller->getSignificantIndividual();
		$fullname		= $controller->record->getFullName();
		$xref			= $controller->record->getXref();
		$parentFamily	= '';
		$cousins		= KT_Filter::post('cousins');

		if ($person->getPrimaryChildFamily()) {
			$parentFamily = $person->getPrimaryChildFamily();
		}
		if ($parentFamily && $parentFamily->getHusband()) {
			$grandparentFamilyHusb = $parentFamily->getHusband()->getPrimaryChildFamily();
		} else {
			$grandparentFamilyHusb = '';
		}
		if ($parentFamily && $parentFamily->getWife()) {
			$grandparentFamilyWife = $parentFamily->getWife()->getPrimaryChildFamily();
		} else {
			$grandparentFamilyWife = '';
		}

		ob_start();
			if (!$parentFamily) { ?>
				<div class="callout alert">
					<?php echo KT_I18N::translate('No family available'); ?>
				</div>
			<?php } else { ?>
				<div class="cell tabHeader">
					<div class="grid-x">
						<div class="cell medium-2">
							<form name="cousinsForm" id="cousinsForm" method="post" action="">
								<input type="hidden" name="cousins" value="<?php echo $cousins == 'second' ? 'first' : 'second'; ?>">
								<button class="button clear" type="submit">
									<i class="<?php echo $iconStyle; ?> fa-eye"></i>
									<?php echo $cousins == 'second' ? KT_I18N::translate('Show first cousins') : KT_I18N::translate('Show second cousins'); ?>
								</button>
							</form>
						</div>
					</div>
				</div>
				<div class="cell">
					<div class="grid-x">
						<?php if ($cousins <> 'second') { ?>
							<?php
								$firstCousinsF	= $grandparentFamilyHusb ? $this->getFirstCousins($parentFamily, $grandparentFamilyHusb, 'husb') : array('',0,0,'');
								$list 			= $firstCousinsF[3]; // list of cousins used by next function to assess possible duplicates due to siblings marry siblings links.
								$firstCousinsM	= $grandparentFamilyWife ? $this->getFirstCousins($parentFamily, $grandparentFamilyWife, 'wife', $list) : array('',0,0);
								$countCousinsF	= $firstCousinsF[1];
								$countCousinsM	= $firstCousinsM[1];
								$totalCousins	= $countCousinsF + $countCousinsM;
								$duplicatesF	= $firstCousinsF[2];
								$duplicatesM	= $firstCousinsM[2];
								$duplicates		= $duplicatesF + $duplicatesM;
							?>
							<div class="cell subHeader">
								<span class="h5">
									<?php echo KT_I18N::plural('%2$s has %1$d first cousin recorded', '%2$s has %1$d first cousins recorded', $totalCousins, $totalCousins, $fullname); ?>
								</span>
								<?php if ($duplicates > 0) { ?>
									<span class="h6"><?php echo /* I18N: a reference to cousins of siblings married to siblings */ KT_I18N::plural('%1$d is on both sides of the family', '%1$d are on both sides of the family', $duplicates, $duplicates); ?></span>
								<?php } ?>
							 </div>
							<div class="cell medium-6 cousins_f">
								<span class="h5">
									<?php echo KT_I18N::translate('Father\'s family (%s)', $countCousinsF); ?>
								</span>
								<?php echo $firstCousinsF[0]; ?>
							</div>
							<div class="cell medium-6 cousins_m">
								<span class="h5">
									<?php echo KT_I18N::translate('Mother\'s family (%s)', $countCousinsM); ?>
								</span>
								<?php echo $firstCousinsM[0]; ?>
							</div>
						<?php } ?>

						<?php if ($cousins == 'second') { ?>
							<?php
								$secondCousinsF = $grandparentFamilyHusb ? $this->getSecondCousins($grandparentFamilyHusb) : array('',0);
								$secondCousinsM = $grandparentFamilyWife ? $this->getSecondCousins($grandparentFamilyWife) : array('',0);
								$countCousinsF	= $secondCousinsF[1];
								$countCousinsM	= $secondCousinsM[1];
								$totalCousins	= $countCousinsF + $countCousinsM;
							?>
							<div class="cell subHeader">
								<span class="h5">
									<?php echo KT_I18N::plural('%2$s has %1$d second cousin recorded', '%2$s has %1$d second cousins recorded', $totalCousins, $totalCousins, $fullname); ?>
								</span>
							</div>
							<div class="cell medium-6 cousins_f">
								<span class="h5">
									<?php echo KT_I18N::translate('Second cousins on father\'s side (%s)', $countCousinsF); ?>
								</span>
								<?php echo $secondCousinsF[0]; ?>
							</div>
							<div class="cell medium-6 cousins_m">
								<span class="h5">
									<?php echo KT_I18N::translate('Second cousins on mother\'s side (%s)', $countCousinsM); ?>
								</span>
								<?php echo $secondCousinsM[0]; ?>
							</div>
						<?php } ?>
					</div>
				</div>
			<?php }

			return '
				<div id="' . $this->getName() . '_content" class="grid-x grid-padding-y">' .
					ob_get_clean() . '
				</div>
			';

	}

	function getFirstCousins($parentFamily, $grandparentFamily, $type, $list = array()) {
		$html				= '';
		$count_1cousins		= 0;
		$prev_fam_id		= -1;
		$family				= '';
		$count_duplicates	= 0;
		$list ? $list : $list = array();

		if ($type == 'husb') {
			$myParent = $parentFamily->getHusband()->getXref();
		} elseif ($type == 'wife') {
			$myParent = $parentFamily->getWife()->getXref();
		}

		foreach ($grandparentFamily->getChildren() as $key => $child) {
			if ($child->getSpouseFamilies() && $child->getXref() <> $myParent) {
				foreach ($child->getSpouseFamilies() as $family) {
					if (!is_null($family)) {
						$i = 0;
						$children = $family->getChildren();
						foreach ($children as $key => $child2) {
							if ($child2->canDisplayName()) {
								$i ++;
								if (in_array($child2->getXref(), $list)) {$count_duplicates++;} // this adjusts the count for cousins of siblings married to siblings
								$list[] = $child2->getXref();
								$record = KT_Person::getInstance($child2->getXref());
								$cousinParentFamily = substr($record->getPrimaryChildFamily(), 0, strpos($record->getPrimaryChildFamily(), '@'));
					 			if ( $cousinParentFamily == $parentFamily->getXref() )
									continue; // cannot be cousin to self
								$tmp = array('M'=>'', 'F'=>'F', 'U'=>'NN');
								$isF = $tmp[$child2->getSex()];
								$label = '';
								$famcrec = get_sub_record(1, '1 FAMC @'.$cousinParentFamily.'@', $record->getGedcomRecord());
								$pedi = get_gedcom_value('PEDI', 2, $famcrec, '', false);
								if ($pedi) {
									$label = KT_Gedcom_Code_Pedi::getValue($pedi, $record);
								}
								$cousinParentFamily = substr($child2->getPrimaryChildFamily(), 0, strpos($child2->getPrimaryChildFamily(), '@'));
								$family2 = KT_Family::getInstance($cousinParentFamily);
								if ($cousinParentFamily != $prev_fam_id) {
									$prev_fam_id = $cousinParentFamily;
									$html .= '<div class="lead">' . KT_I18N::translate('Parents');
										if (!is_null($family2)) {
											$html .= '
												<a target="_blank" rel="noopener noreferrer" href="' . $family2->getHtmlUrl() . '">
													&nbsp;' . $family2->getFullName() . '
												</a>
											';
										}
									$html .= '</div>';
									$i = 1;
								}
								$html .= '
									<div class="person_box' . $isF . '">
										<span class="cousins_counter">' . $i . '</span>
										<span class="cousins_name">
											<a target="_blank" rel="noopener noreferrer" href="' . $child2->getHtmlUrl() . '">' . $child2->getFullName() . '</a>
										</span>
										<span class="cousins_lifespan">' . $child2->getLifeSpan() . '</span>
										<span class="cousins_pedi">' . $label . '</span>
									</div>
								';
								$count_1cousins ++;
							}
						}
					}
				}
			}
		}

		return array($html, $count_1cousins, $count_duplicates, $list);
	}

	function getSecondCousins($grandparentFamily) {
		$html			= '';
		$count_2cousins	= 0;
		$prev_fam_id	= -1;

		for ($x = 1; $x < 3; $x ++) {
			$x == 1 ? $myGrandParent = $grandparentFamily->getHusband() : $myGrandParent = $grandparentFamily->getWife();
			if ($myGrandParent->getPrimaryChildFamily()) {
				foreach ($myGrandParent->getPrimaryChildFamily()->getChildren() as $key => $child) {
					if ($child->getSpouseFamilies() && $child->getXref() <> $myGrandParent->getXref()) {
						foreach ($child->getSpouseFamilies() as $family) {
							if (!is_null($family)) {
								$i = 0;
								$children = $family->getChildren();
								foreach ($children as $key => $child2) {
									foreach ($child2->getSpouseFamilies() as $family2) {
										if (!is_null($family2)) {
											$children2 = $family2->getChildren();
											foreach ($children2 as $key => $child3) {
												if ($child->canDisplayName()) {
													$i ++;
													$tmp				= array('M'=>'', 'F'=>'F', 'U'=>'NN');
													$isF				= $tmp[$child3->getSex()];
													$cousinParentFamily = substr($child3->getPrimaryChildFamily(), 0, strpos($child3->getPrimaryChildFamily(), '@'));
													$family3			= KT_Family::getInstance($cousinParentFamily);
													if ($cousinParentFamily != $prev_fam_id) {
										 				$prev_fam_id = $cousinParentFamily;
														$html .= '<div class="lead">' . KT_I18N::translate('Parents');
															if (!is_null($family3)) {
																$html .= '
																	<a target="_blank" rel="noopener noreferrer" href="' . $family3->getHtmlUrl() . '">
																		&nbsp;' . $family3->getFullName() . '
																	</a>
																';
															}
														$html .= '</div>';
														$i = 1;
													}
													$html .= '
														<div class="person_box' . $isF . '">
															<span class="cousins_counter">' . $i . '</span>
															<span class="cousins_name">
																<a target="_blank" rel="noopener noreferrer" href="' . $child3->getHtmlUrl() . '">' . $child3->getFullName() . '</a>
															</span>
															<span class="cousins_lifespan">' . $child3->getLifeSpan() . '</span>
														</div>
													';
													$count_2cousins ++;
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return array($html, $count_2cousins);
	}

}
