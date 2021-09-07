<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

class tabf_census_KT_Module extends KT_Module implements KT_Module_FamTab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('Census summary');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Facts and events” module */ KT_I18N::translate('A tab showing a summary of census records for a family.');
	}

	// Extend class KT_Module_FamTab
	public function defaultAccessLevel() {
		return false;
	}

	// Implement KT_Module_FamTab
	public function defaultTabOrder() {
		return 10;
	}

	// Implement KT_Module_FamTab
	public function isGrayedOut() {
		return false;
	}

	// Implement KT_Module_FamTab
	public function hasTabContent() {
		return true;
	}

	// Implement KT_Module_FamTab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_FamTab
	public function getPreLoadContent() {
		return '';
	}

	// Implement KT_Module_FamTab
	public function getTabContent() {
		global $controller, $iconStyle, $MAX_ALIVE_AGE;
		// $icon styles
		$nothing	= '<i class="fa-xs '   . $iconStyle . ' fa-minus"></i>';
		$correct	= '<i class="success ' . $iconStyle . ' fa-check"></i>';
		$missing	= '<i class="alert '   . $iconStyle . ' fa-times"></i>';
		$unknown	= '<i class="warning ' . $iconStyle . ' fa-question"></i>';

		ob_start();
		?>
		<div class="cell tabHeader">
			<div class="grid-x">
				<div class="cell">
					<h5><?php echo KT_I18N::translate('Family group census summary for %s', $this->getCountry($controller->record->getXref())); ?></h5>
				</div>
			</div>
		</div>
		<div class="cell FamFact">
			<div class="grid-x grid-padding-x">
				<table>
					<thead>
						<tr>
							<th><?php echo KT_I18N::translate('Family members'); ?></th>
							<?php foreach (KT_Census_Census::allCensusPlaces() as $censusPlace) {
								if ($censusPlace->censusPlace() === $this->getCountry($controller->record->getXref())) {
									foreach ($censusPlace->allCensusDates() as $census) { ?>
										<th>
											<?php echo substr($census->censusDate(), -4); ?>
										</th>
									<?php }
								}
							} ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->familyCensus($controller->record->getXref()) as $details ) {
							$person	= KT_Person::getInstance($details['xref']); ?>
							<tr>
								<td>
									<a href="individual.php?pid=<?php echo $details['xref']; ?>&amp;ged=<?php echo KT_GEDURL; ?>#tab_i_census"><?php echo $details['name']; ?></a>
								</td>
								<?php foreach ($censusPlace->allCensusDates() as $census) {
									$year	= substr($census->censusDate(), -4);
									$date	= new KT_Date($census->censusDate()); ?>
									<td>
										<?php if (in_array($year, $details['cens'])) {
											echo $correct;
										} elseif (($person->getBirthDate()->JD() > 0 && $person->getBirthDate()->JD() > $date->JD()) || ($person->getDeathDate()->JD() > 0 && $person->getDeathDate()->JD() < $date->JD())) {
											echo $nothing;
										} elseif (($person->getBirthDate()->JD() == 0 && $person->getEstimatedBirthDate()->JD() > $date->JD()) || ($person->getDeathDate()->JD() == 0 && $person->getEstimatedBirthDate()->JD() < $date->JD())) {
											echo $unknown;
										} else {
											echo $missing;
										}?>
									</td>
								<?php } ?>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<div class="cell medium-1 h6"><?php echo KT_I18N::translate('Key to summary'); ?></div>
				<div class="cell medium-2"><?php echo $correct . KT_I18N::translate('Census entry found'); ?></div>
				<div class="cell medium-2"><?php echo $nothing . KT_I18N::translate('No census entry expected'); ?></div>
				<div class="cell medium-4"><?php echo $unknown . KT_I18N::translate('Birth or death date missing, but census is within expected lifetime'); ?></div>
				<div class="cell medium-3"><?php echo $missing . KT_I18N::translate('Census entry missing'); ?></div>
			</div>
		</div>

		<?php
		return '
			<div class="grid-x grid-margin-y">' .
				ob_get_clean() . '
			</div>
		';
	}

	/**
	 * Try to get census country from marriage place of Parents first,
	 * then try birth place of $husband,
	 * then try birth place of wife.
	 * When place found, select the country level only
	 */
	private function getCountry($famid) {
		$family = KT_Family::getInstance($famid);

		$family ? $place = $family->getMarriagePlace() : $place = '';

		if (!$place) {
			$husb = $family->getHusband();
			if ($husb) {
				$husb	= KT_Person::getInstance($husb->getXref());
				$place	= $husb->getBirthPlace();
			} else {
				$wife = $family->getWife();
				if ($wife) {
					$wife	= KT_Person::getInstance($wife->getXref());
					$place	= $wife->getBirthPlace();
				}
			}
		}

		$levels 		= explode(', ', $place);
		$levels			= array_reverse($levels);
		$censusCountry	= $levels[0];

		return $censusCountry;

	}

	/**
	 * Find all census records from all family members
	 * @param string $famid XREF for this family (Fxxx)
	 */
	private function familyCensus($famid) {
		$family = KT_Family::getInstance($famid);
		if (!$family) return;

		$famList = array_merge($family->getSpouses(), $family->getChildren());

		foreach ($famList as $famMember) {
			$indifacts		= $famMember->getIndiFacts();
			$censusFacts	= array();
			foreach ($indifacts as $fact) {
				if ($fact->getTag() === 'CENS') {
					$censusFacts[] = substr(strip_tags(format_fact_date($fact, $famMember, false, false, false)), -4);
				}
			}
			$famCensus[] = ['name' => $famMember->getLifespanName(), 'cens' => $censusFacts, 'xref' => $famMember->getXref()];
		}

		return $famCensus;
	}

}
