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

class report_census_KT_Module extends KT_Module implements KT_Module_Report {

	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Census check');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “UK Census check” module */ KT_I18N::translate('A list of missing census data');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement KT_Module_Report
	public function getReportMenus() {
		$menus	= array();
		$menu	= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&mod_action=show',
			'menu-report-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class KT_Module_Report
	public function show() {
		global $controller, $GEDCOM, $iconStyle;

		//-- args
		$surn	= KT_Filter::post('surn', '[^<>&%{};]*');
		$plac	= KT_Filter::post('plac', '[^<>&%{};]*') ? KT_Filter::post('plac', '[^<>&%{};]*') : '';
		$dat	= KT_Filter::post('dat', '[^<>&%{};]*');
		$ged	= empty($ged) ? $GEDCOM : KT_Filter::post('ged');
		$go 	= KT_Filter::post('go') ? KT_Filter::post('go') : "0";

		if (KT_Filter::post('reset')) {
			$surn	= '';
			$plac	= '';
			$dat	= '';
			$ged	= '';
			$go 	= '0';
		}

		foreach (KT_Census_Census::allCensusPlaces() as $census_place) {
			//List of Places
			$census_places[] = $census_place->censusPlace();
			//List of Dates
			foreach ($census_place->allCensusDates() as $census) {
				$census_dates[]	= $census->censusDate();
				$date			= new KT_Date($census->censusDate());
				$jd				= $date->JD();
				$data_sources[]	= array('event'=>'CENS', 'date'=>$census->censusDate(), 'place'=>$census_place->censusPlace(), 'jd'=>$jd);
			}
		}

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_Module::isActiveReport(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
			->setPageTitle(KT_I18N::translate(KT_I18N::translate('Missing Census Data')))
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('
				autocomplete();
			'); ?>

			<!-- Start page layout  -->
			<?php echo pageStart('report_census', KT_I18N::translate('Individuals with missing census data', '', $this->getDescription())); ?>
				<form class="hide-for-print" name="surnlist" id="surnlist" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show">
					<div class="grid-x grid-margin-x">
						<div class="cell callout info-help">
							<?php echo KT_I18N::translate('
								Enter a surname, then select any combination of Census place and Census date.
								<br>
								Under Surname, you can also enter \'All\' for everyone, or leave it blank for all of your own ancestors.
							'); ?>
						</div>
						<div class="cell medium-4">
							<label class="h5" for="autocompleteInput"><?php echo KT_Gedcom_Tag::getLabel('SURN'); ?></label>
							<?php echo autocompleteHtml(
								'surname', // id
								'SURN', // TYPE
								'', // autocomplete-ged
								$surn, // input value
								'', // placeholder
								'surn', // hidden input name
								$surn // hidden input value
							); ?>
						</div>
						<div class="cell medium-4">
							<label class="h5" for="cens_plac"><?php echo KT_I18N::translate('Census Place'); ?></label>
							<select name="plac" id="cens_plac" onchange="this.form.submit();">
								<?php echo '<option value="' . KT_I18N::translate('All') . '"';
									if ($dat == KT_I18N::translate('All')) {
										echo ' selected = "selected"';
									}
									echo '>' . KT_I18N::translate('All') . '
								</option>';
								foreach ($census_places as $census_place) {
									echo '<option value="' . $census_place. '"';
										if ($census_place == $plac) {
											echo ' selected = "selected"';
										}
										echo '>' . $census_place. '
									</option>';
								}
								?>
							</select>
						</div>
						<div class="cell medium-4">
							<label class="h5" for "cens_dat"><?php echo KT_I18N::translate('Census date'); ?></label>
							<select name="dat"  id="cens_dat">
								<?php  if ($plac == '') {
									echo '<option value="' . KT_I18N::translate('All') . '"';
										if ($dat == KT_I18N::translate('All')) {
											echo ' selected = "selected"';
										}
									echo '>
										' . KT_I18N::translate('All') . '
									</option>';
									foreach (KT_Census_Census::allCensusPlaces() as $census_place) {
										echo '<optgroup id="' . str_replace(" ", "", $census_place->censusPlace()) . '" label="' . $census_place->censusPlace() . '">';
											foreach ($census_place->allCensusDates() as $census) {
												echo '<option value="' . $census->censusDate() . '"';
													if ($dat == $census->censusDate()) {
														echo ' selected="selected"';
													}
													echo '>' . $census->censusDate() . '
												</option>';
											}
										echo '</optgroup>';
									}
								}
								foreach (KT_Census_Census::allCensusPlaces() as $census_place) {
									if ($plac && $plac == $census_place->censusPlace()) {
										echo '<optgroup id="' . str_replace(" ", "", $census_place->censusPlace()) . '" label="' . $census_place->censusPlace() . '">';
											echo '<option value="' . KT_I18N::translate('All') . '"';
												if ($dat == KT_I18N::translate('All')) {
													echo ' selected = "selected"';
												}
											echo '>
												' . KT_I18N::translate('All') . '
											</option>';
											foreach ($census_place->allCensusDates() as $census) {
												echo '<option value="' . $census->censusDate() . '"';
													if ($dat == $census->censusDate()) {
														echo ' selected="selected"';
													}
													echo '>' . $census->censusDate() . '
												</option>';
											}
										echo '</optgroup>';
									}
								} ?>
							</select>
						</div>
					</div>

					<?php echo resetButtons(); ?>

				</form>
				<hr style="clear:both;">
				<!-- end of form -->

				<?php
				function life_sort($a, $b) {
					if ($a->getDate()->minJD() < $b->getDate()->minJD()) return -1;
					if ($a->getDate()->minJD() > $b->getDate()->minJD()) return 1;
					return 0;
				}

				function add_parents(&$array, $indi) {
					if ($indi) {
						$array[] = $indi;
						foreach ($indi->getChildFamilies() as $parents) {
							add_parents($array, $parents->getHusband());
							add_parents($array, $parents->getWife());
						}
					}
				}

				if ($surn == KT_I18N::translate('All') || $surn == KT_I18N::translate('All')) {
					$indis = array_unique(KT_Query_Name::individuals('', '', '', false, false, KT_GED_ID));
				} elseif ($surn) {
					$indis = array_unique(KT_Query_Name::individuals($surn, '', '', false, false, KT_GED_ID));
				} else {
					$id = KT_Tree::get(KT_GED_ID)->userPreference(KT_USER_ID, 'gedcomid');
					if (!$id) {
						$id = KT_Tree::get(KT_GED_ID)->userPreference(KT_USER_ID, 'rootid');
					}
					if (!$id) {
						$id = 'I1';
					}
					$indis = array();
					add_parents($indis, KT_Person::getInstance($id));
				}


				// Show sources to user
				if ($go == 1) {
					// Notes about the register
					if (in_array($plac, array('England', 'Wales')) && ($dat === '29 SEP 1939' || $dat === KT_I18N::translate('All'))) { ?>
						<div class="callout secondary">
							<h5 style="margin: 0 auto;"><?php echo KT_I18N::translate('Notes:'); ?></h5>
							<ol id="register_notes">
								<li><strong>1939 Register (England & Wales)</strong>
									<ol>
										<li><?php echo /* I18N: Note about UK 1939 Register check */ KT_I18N::translate('This list assumes entries relating to the 1939 Register are recorded using the <b>census</b> GEDCOM tag (CENS))'); ?></li>
										<li><?php echo /* I18N: Note about UK 1939 Register check */ KT_I18N::translate('In general anyone who died after 1991, or is still alive, will be redacted (hidden) on the Register. They are listed here, but with a note indicating they are likely to be redacted. However, the Register is incomplete in this regard, so many people who died <u>before</u> 1991 are still redacted.'); ?></li>
										<li><?php echo /* I18N: Note about UK 1939 Register check */ KT_I18N::translate('Anyone serving in the military on 29 September 1939 is excluded from the Register. They are included in these lists but with a note that they may be in the military. This list assumes their military service is recorded with either the _MILI or _MILT GEDCOM tags'); ?></li>
									</ol>
								</li>
							</ol>
						</div>
					<?php } ?>

					<div class="cell" id="nocensus_result">
						<div class="grid-container">
							<div class="grid-x grid-margin-x medium-up-3 large-up-4">
								<?php // Check each INDI against each SOUR
								$n = 0;
								foreach ($indis as $id=>$indi) {
									// Build up a list of significant life events for this individual
									$life = array();
									// Get a birth/death date for this indi
									// Make sure we have a BIRTH, whether it has a place or not
									$birt_jd	= $indi->getEstimatedBirthDate()->JD();
									$birt_plac	= $indi->getBirthPlace();
									$deat_jd	= $indi->getEstimatedDeathDate()->JD();
									$deat_plac	= $indi->getDeathPlace();

									// Create an array of events with dates
									foreach ($indi->getFacts() as $event) {
										if ($event->getTag() != 'CHAN' && $event->getDate()->isOK()) {
											$life[] = $event;
										}
									}
									uasort($life, 'life_sort');
									// Now check for missing sources
									$missing_text = '';
									foreach ($data_sources as $data_source) {
									$check1 = $data_source['place'];
									$check2 = $data_source['date'];
										if($check1 == $plac || $plac == KT_I18N::translate('All')) {
											if($check2 == $dat || $dat == KT_I18N::translate('All')) {
												// Person not alive - skip
												if ($data_source['jd'] < $birt_jd || $data_source['jd'] > $deat_jd)
													continue;
												// Find where the person was immediately before/after
												$bef_plac	= $birt_plac;
												$aft_plac	= $deat_plac;
												$bef_fact	= 'BIRT';
												$bef_jd		= $birt_jd;
												$aft_jd		= $deat_jd;
												$aft_fact	= 'DEAT';
												foreach ($life as $event) {
													if ($event->getDate()->MinJD() <= $data_source['jd'] && $event->getDate()->MinJD() > $bef_jd) {
														$bef_jd		= $event->getDate()->MinJD();
														$bef_plac	= $event->getPlace();
														$bef_fact	= $event->getTag();
													}
													if ($event->getDate()->MinJD() >= $data_source['jd'] && $event->getDate()->MinJD() < $aft_jd) {
														$aft_jd		=$event->getDate()->MinJD();
														$aft_plac	=$event->getPlace();
														$aft_fact	=$event->getTag();
													}
												}
												// If we already have this event - skip
												if ($bef_jd == $data_source['jd'] && $bef_fact == $data_source['event'])
													continue;
												// If we were in the right place before/after the missing event, show it
												if (stripos($bef_plac, $data_source['place']) !== false || stripos($aft_plac, $data_source['place']) !== false) {
													$age_at_census = substr($data_source['date'],7,4) - $indi->getBirthDate()->gregorianYear();
													$desc_event = KT_Gedcom_Tag::getLabel($data_source['event']);
													$missing_text .= '
														<span><strong>' . $data_source['place'] . '&nbsp;' . $desc_event . ' for ' . $data_source['date'] . '</strong></span>
														<span><i>' . KT_I18N::translate('Age') . ' ' . $age_at_census . '</i></span>
													';
													if (substr($check2,7,4) === '1939') {
														// Person died after 1991 - make note
														if ($indi->getEstimatedDeathDate()->gregorianYear() > '1991') {
															$missing_text .= '<span><i>' . KT_I18N::translate('Probably redacted - living or died after 1991') . '</i></span>';
														}
														// Check if person in military
														if ($bef_fact == '_MILI' || $bef_fact == '_MILT') {
															$missing_text .= '<span><i>' . KT_I18N::translate('Probably excluded - military service') . '</i></span>';
														}
													}
												}
											}
										}
									}
								if ($missing_text) {
									$birth_year = $indi->getBirthDate()->gregorianYear();
									if ($birth_year == 0) {
										$birth_year='????';
									}
									$death_year = $indi->getDeathDate()->gregorianYear();
									if ($death_year == 0) {
										$death_year='????';
									} ?>

									<div class="cell">
										<div class="card">
										  <div class="card-divider">
											  <a target="_blank" rel="noopener noreferrer" href="<?php echo$indi->getHtmlUrl(); ?>">
		  										<?php echo$indi->getFullName(); ?>
		  										<span> (<?php echo$birth_year . '-' . $death_year; ?>)</span>
		  									</a>
										  </div>
										  <div class="card-section">
											  <?php echo $missing_text; ?>
										  </div>
										</div>
									</div>

									<?php
									++$n;
									}
								} ?>
							</div>
							<?php if ($n == 0 && $surn) { ?>
								<div class="cell callout alert "><?php echo KT_I18N::translate('No missing records found'); ?></div>
							<?php } else { ?>
								<div class="cell callout success"><?php echo KT_I18N::plural('%s record found', '%s records found', $n, $n); ?></div>
							<?php } ?>
						</div>
					</div>
				<?php }

		echo pageClose();
	}

}
