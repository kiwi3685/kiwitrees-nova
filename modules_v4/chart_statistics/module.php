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

class chart_statistics_KT_Module extends KT_Module implements KT_Module_Chart {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Statistics');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Statistics chart” module */ KT_I18N::translate('An individual\'s statistics chart');
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
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Chart
	public function getChartMenus() {
		global $controller;
		$indi_xref	= $controller->getSignificantIndividual()->getXref();
		$menus		= array();
		$menu		= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL . '#stats-indi',
			'menu-chart-statistics'
		);
		$menus[] = $menu;
		return $menus;
	}

	// Display list
	public function show() {
		global $controller, $GEDCOM, $iconStyle, $KT_STATS_CHART_COLOR1, $KT_STATS_CHART_COLOR2, $KT_STATS_CHART_COLOR3;
		$controller	= new KT_Controller_Page;
		$stats		= new KT_Stats($GEDCOM);
		$tab		= KT_Filter::get('tab', KT_REGEX_NOSCRIPT, 0);

		$controller
			->restrictAccess(KT_Module::isActiveChart(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addExternalJavascript(KT_D3_JS);

		include_once 'statistics.js.php'; ?>

		<!-- Start page layout  -->
		<?php echo pageStart('statistics', $controller->getPageTitle()); ?>
			<ul class="tabs" id="statistics-tabs" data-tabs data-deep-link="true">
				<li class="tabs-title is-active">
					<a href="#stats-indi" aria-selected="true">
						<span><?php echo KT_I18N::translate('Individuals'); ?></span>
					</a>
				</li>
				<li class="tabs-title">
					<a href="#stats-fam">
						<span><?php echo KT_I18N::translate('Families'); ?></span>
					</a>
				</li>
				<li class="tabs-title">
					<a href="#stats-other">
						<span><?php echo KT_I18N::translate('Other'); ?></span>
					</a>
				</li>
			</ul>
			<div class="tabs-content" data-tabs-content="statistics-tabs">
				<!-- INDIVIDUAL TAB -->
				<?php
				$controller->addInlineJavascript('
					pieChart("chartSex");
					pieChart("chartMortality");
					horizontalChart("chartCommonSurnames");
					horizontalChart("chartCommonGiven");
					barChart("chartStatsBirth");
					barChart("chartStatsDeath");
					groupChart("chartStatsAge");
				'); ?>

				<div class="tabs-panel is-active" id="stats-indi">
					<h5>
						<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis" target="_blank">
							<?php echo KT_I18N::translate('Total individuals: %s', $stats->totalIndividuals()); ?>
						</a>
					</h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<?php $stats->totalSexUnknown() > 0 ? $cells = 4 : $cells = 6; ?>
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell medium-<?php echo $cells; ?> text-center">
									<small>
										<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=male" target="_blank">
											<?php echo KT_I18N::translate('Total males') . ' ' . $stats->totalSexMales(); ?>
										</a>
										 (<?php echo $stats->totalSexMalesPercentage(); ?>)
									</small>
								</div>
								<div class="cell medium-<?php echo $cells; ?> text-center">
									<small>
										<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=female" target="_blank">
											<?php echo KT_I18N::translate('Total females') . ' ' . $stats->totalSexFemales(); ?>
										</a>
										 (<?php echo $stats->totalSexFemalesPercentage(); ?>)
									</small>
								</div>
								<?php if ($stats->totalSexUnknown() > 0) { ?>
									<div class="cell medium-<?php echo $cells; ?> text-center">
										<small>
											<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=unknown" target="_blank">
												<?php echo KT_I18N::translate('Total unknown') . ' ' . $stats->totalSexUnknown(); ?>
											</a>
											 (<?php echo $stats->totalSexUnknownPercentage(); ?>)
										 </small>
									</div>
								<?php } ?>
								<div class="cell text-center"><?php echo KT_I18N::translate('Individuals, by gender'); ?></div>
								<div class="cell" id="chartSex"></div>
							</div>
						</div>
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell medium-6 text-center">
									<small>
										<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=living" target="_blank">
											<?php echo KT_I18N::translate('Total living') . ' ' . $stats->totalLiving(); ?>
										</a>
										 (<?php echo $stats->totalLivingPercentage(); ?>)
									</small>
								</div>
								<div class="cell medium-6 text-center">
									<small>
										<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=deceased" target="_blank">
											<?php echo KT_I18N::translate('Total deceased') . ' ' . $stats->totalDeceased(); ?>
										</a>
										 (<?php echo $stats->totalDeceasedPercentage(); ?>)
									</small>
								</div>
								<div class="cell text-center"><?php echo KT_I18N::translate('Individuals, by living / deceased status'); ?></div>
								<div class="cell" id="chartMortality"></div>
							</div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Events'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total births') . '&nbsp;' . $stats->totalBirths(); ?></label>
							<div class="cell text-center"><?php echo KT_I18N::translate('Number of births in each century'); ?></div>
							<div class="cell" id="chartStatsBirth"></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total deaths') . '&nbsp;' . $stats->totalDeaths(); ?></label>
							<div class="cell text-center"><?php echo KT_I18N::translate('Number of deaths in each century'); ?></div>
							<div class="cell" id="chartStatsDeath"></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Earliest birth'); ?></label>
							<div><?php echo $stats->firstBirth(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Earliest death'); ?></label>
							<div><?php echo $stats->firstDeath(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Latest birth'); ?></label>
							<div><?php echo $stats->lastBirth(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Latest death'); ?></label>
							<div><?php echo $stats->lastDeath(); ?></div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Lifespan'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-4 text-center">
							<?php echo KT_I18N::translate('Average age at death') . ' ' . $stats->averageLifespan(true); ?>
						</div>
						<div class="cell medium-4 text-center">
							<?php echo KT_I18N::translate('Males') . ' ' . $stats->averageLifespanMale(true); ?>
						</div>
						<div class="cell medium-4 text-center">
							<?php echo KT_I18N::translate('Females') . ' ' . $stats->averageLifespanFemale(true); ?>
						</div>
						<div class="cell text-center"><?php echo KT_I18N::translate('Average age at death date, by century'); ?></div>
						<div class="cell" id="chartStatsAge"></div>
					</div>
					<h5><?php echo KT_I18N::translate('Greatest age at death'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Males'); ?></label>
							<div><?php echo $stats->topTenOldestMaleList(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Females'); ?></label>
							<div><?php echo $stats->topTenOldestFemaleList(); ?></div>
						</div>
					</div>
					<?php if (KT_USER_ID) { ?>
						<h5><?php echo KT_I18N::translate('Oldest living people'); ?></h5>
						<div class="grid-x grid-margin-x grid-margin-y statisticSection">
							<div class="cell medium-6">
								<label class="h6"><?php echo KT_I18N::translate('Males'); ?></label>
								<div><?php echo $stats->topTenOldestMaleListAlive(); ?></div>
							</div>
							<div class="cell medium-6">
								<label class="h6"><?php echo KT_I18N::translate('Females'); ?></label>
								<div><?php echo $stats->topTenOldestFemaleListAlive(); ?></div>
							</div>
						</div>
					<?php } ?>
					<h5><?php echo KT_I18N::translate('Names'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total surnames') . ' ' . $stats->totalSurnames(); ?></label>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total given names') . ' ' . $stats->totalGivennames(); ?></label>
						</div>
						<div class="cell medium-6">
							<div class="cell" id="chartCommonSurnames"></div>
							<div class="cell text-center"><?php echo KT_I18N::translate('Top surnames'); ?></div>
						</div>
						<div class="cell medium-6">
							<div class="cell" id="chartCommonGiven"></div>
							<div class="cell text-center"><?php echo KT_I18N::translate('Top given names'); ?></div>
						</div>
					</div>
				</div>
				<!-- FAMILY TAB -->
				<?php
				$controller->addInlineJavascript('
					barChart("chartMarr");
					barChart("chartDiv");
					groupChart("chartMarrAge");
					barChart("chartChild");
					barChart("chartNoChild");
				'); ?>
				<div class="tabs-panel" id="stats-fam">
					<h5><?php echo KT_I18N::translate('Total families: %s', $stats->totalFamilies()); ?></h5>
					<h5><?php echo KT_I18N::translate('Events'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total marriages') . '&nbsp;' . $stats->totalMarriages(); ?></label>
							<div class="cell text-center"><?php echo KT_I18N::translate('Number of marriages in each century'); ?></div>
							<div class="cell" id="chartMarr"></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total divorces') . '&nbsp;' . $stats->totalDivorces(); ?></label>
							<div class="cell text-center"><?php echo KT_I18N::translate('Number of divorces in each century'); ?></div>
							<div class="cell" id="chartDiv"></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Earliest marriage'); ?></label>
							<div><?php echo $stats->firstMarriage(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Earliest divorce'); ?></label>
							<div><?php echo $stats->firstDivorce(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Latest marriage'); ?></label>
							<div><?php echo $stats->lastMarriage(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Latest divorce'); ?></label>
							<div><?php echo $stats->lastDivorce(); ?></div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Length of marriage'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<?php echo KT_I18N::translate('Longest marriage'), ' ', $stats->topAgeOfMarriage(); ?>
						</div>
						<div class="cell medium-6">
							<?php echo KT_I18N::translate('Shortest marriage'), ' ', $stats->minAgeOfMarriage(); ?>
						</div>
						<div class="cell medium-6">
							<div><?php echo $stats->topAgeOfMarriageFamily(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo $stats->minAgeOfMarriageFamily(); ?></div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Age in year of marriage'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<?php echo KT_I18N::translate('Youngest male'), ' ', $stats->youngestMarriageMaleAge(true); ?>
							<div><?php echo $stats->youngestMarriageMale(); ?></div>
						</div>
						<div class="cell medium-6">
							<?php echo KT_I18N::translate('Youngest female'), ' ', $stats->youngestMarriageFemaleAge(true); ?>
							<div><?php echo $stats->youngestMarriageFemale(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Oldest male'), ' ', $stats->oldestMarriageMaleAge(true); ?></div>
							<div><?php echo $stats->oldestMarriageMale(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Oldest female'), ' ', $stats->oldestMarriageFemaleAge(true); ?></div>
							<div><?php echo $stats->oldestMarriageFemale(); ?></div>
						</div>
						<div class="cell">
							<div class="cell text-center"><?php echo KT_I18N::translate('Average age at marriage date, by century'); ?></div>
							<div class="cell" id="chartMarrAge"></div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Age at birth of child'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<?php echo KT_I18N::translate('Youngest father'), ' ', $stats->youngestFatherAge(true); ?>
							<div><?php echo $stats->youngestFather(); ?></div>
						</div>
						<div class="cell medium-6">
							<?php echo KT_I18N::translate('Youngest mother'), ' ', $stats->youngestMotherAge(true); ?>
							<div><?php echo $stats->youngestMarriageFemale(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Oldest male'), ' ', $stats->oldestMarriageMaleAge(true); ?></div>
							<div><?php echo $stats->youngestMother(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Oldest female'), ' ', $stats->oldestMarriageFemaleAge(true); ?></div>
							<div><?php echo $stats->oldestMarriageFemale(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Oldest father'), ' ', $stats->oldestFatherAge(true); ?></div>
							<div><?php echo $stats->oldestFather(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Oldest mother'), ' ', $stats->oldestMotherAge(true); ?></div>
							<div><?php echo $stats->oldestMother(); ?></div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Children in family'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Average number of children per family'), ' ', $stats->averageChildren(); ?></div>
							<div class="cell text-center"><?php echo KT_I18N::translate('Number of children per family, by century'); ?></div>
							<div class="cell" id="chartChild"></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Number of families without children'), ' ', $stats->noChildrenFamilies(); ?></div>
							<div class="cell text-center"><?php echo KT_I18N::translate('Number of families without children, by century'); ?></div>
							<div class="cell" id="chartNoChild"></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Largest families'), ' ', $stats->topTenLargestFamilyList(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Largest number of grandchildren'), ' ', $stats->topTenLargestGrandFamilyList(); ?></div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Age difference'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Between siblings'); ?></label>
							<div><?php echo $stats->topAgeBetweenSiblingsList(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Greatest age between siblings'); ?></label>
							<div><?php echo $stats->topAgeBetweenSiblingsFullName(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Between husband and wife, husband older'); ?></label>
							<div><?php echo $stats->ageBetweenSpousesMFList(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Between wife and husband, wife older'); ?></label>
							<div><?php echo $stats->ageBetweenSpousesFMList(); ?></div>
						</div>
					</div>
				</div>
				<!-- OTHER TAB -->
				<?php $controller->addInlineJavascript('
					barChart("chartMedia");
					pieChart("chartIndisWithSources");
					pieChart("chartFamsWithSources");
					mapChart("chartDistribution");
				'); ?>
				<div class="tabs-panel" id="stats-other">
					<h5><?php echo KT_I18N::translate('Total records: %s', $stats->totalRecords()); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-3">
							<a href="module.php?action=filter&search=yes&mod=list_media&mod_action=show&folder=&subdirs=on&sortby=title&form_type=&max=18&filter=&ged=<?php echo $GEDCOM; ?>" target="_blank">
								<?php echo KT_I18N::translate('Media objects: %s', $stats->totalMedia()); ?>
							</a>
						</div>
						<div class="cell medium-3">
							<a href="module.php?mod=list_sources&mod_action=show&ged=<?php echo $GEDCOM; ?>" target="_blank">
								<?php echo KT_I18N::translate('Sources: %s', $stats->totalSources()); ?>
							</a>
						</div>
						<div class="cell medium-3">
							<a href="module.php?mod=list_shared_notes&mod_action=show&ged=<?php echo $GEDCOM; ?>" target="_blank">
								<?php echo KT_I18N::translate('Shared notes: %s', $stats->totalNotes()); ?>
							</a>
						</div>
						<div class="cell medium-3">
							<a href="module.php?mod=list_repositories&mod_action=show&ged=<?php echo $GEDCOM; ?>" target="_blank">
								<?php echo KT_I18N::translate('Repositories: %s', $stats->totalRepositories()); ?>
							</a>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Total events: %s', $stats->totalEvents()); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('First event'); ?> - <?php echo $stats->firstEventType(); ?></label>
							<div><?php echo $stats->firstEvent(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Last event'); ?> - <?php echo $stats->lastEventType(); ?></label>
							<div><?php echo $stats->lastEvent(); ?></div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Media objects: %s', $stats->totalMedia()); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell">
							<div class="cell text-center"><?php echo KT_I18N::translate('Media objects by type'); ?></div>
							<div class="cell" id="chartMedia"></div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Sources: %s', $stats->totalSources()); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell text-center">
									<?php echo KT_I18N::translate('Individuals with sources'); ?>
									<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=male" target="_blank">
										<?php echo $stats->totalIndisWithSources(); ?>
									</a>
									 (<?php echo $stats->totalIndividualsPercentage(); ?>)
								 </div>
								<div class="cell" id="chartIndisWithSources"></div>
							</div>
						</div>
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell text-center">
									<?php echo KT_I18N::translate('Families with sources'); ?>
									<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=male" target="_blank">
										<?php echo $stats->totalFamsWithSources(); ?>
									</a>
									 (<?php echo $stats->totalFamiliesPercentage(); ?>)
								 </div>
								<div class="cell" id="chartFamsWithSources"></div>
							</div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Places: %s', $stats->totalPlaces()); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Birth places'); ?></label>
							<div><?php echo $stats->commonBirthPlacesList(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Death places'); ?></label>
							<div><?php echo $stats->commonDeathPlacesList(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Marriage places'); ?></label>
							<div><?php echo $stats->commonMarriagePlacesList(); ?></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Events in countries'); ?></label>
							<div><?php echo $stats->commonCountriesList(); ?></div>
						</div>
						<div class="cell">
							<div class="grid-x">
								<div class="cell text-center"><?php echo KT_I18N::translate('Individual distribution chart'); ?></div>
								<div class="cell" id="chartDistribution"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php echo pageClose();
	}
}
