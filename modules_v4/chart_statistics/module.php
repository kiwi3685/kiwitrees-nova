<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
			'menu-chart-statistics'
		);
		$menus[] = $menu;
		return $menus;
	}

	// Display list
	public function show() {
		global $controller, $GEDCOM, $iconStyle;

		$controller = new KT_Controller_Page;
		$controller
			->restrictAccess(KT_Module::isActiveChart(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addExternalJavascript(KT_D3_JS)
			->addInlineJavascript('
				pieChart("chartSex");
				pieChart("chartMortality");
				pieChart("chartIndisWithSources");
				pieChart("chartFamsWithSources");
				barChart("chartStatsBirth");
				barChart("chartStatsDeath");
				barChart("chartMarr");
				barChart("chartDiv");
				barChart("chartChild");
				barChart("chartNoChild");
				barChart("chartMedia");
				horizontalChart("chartCommonSurnames");
				horizontalChart("chartCommonGiven");
				groupChart("chartStatsAge");
				groupChart("chartMarrAge");
				mapChart("chartDistribution");
			');

		$stats	= new KT_Stats($GEDCOM);
		include_once 'statistics.js.php';
		?>

		<!-- Start page layout  -->
		<?php echo pageStart($controller->getPageTitle()); ?>
			<ul class="tabs" data-tabs id="statistics-tabs">
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
				<div class="tabs-panel is-active" id="stats-indi">
					<h5><?php echo KT_I18N::translate('Total individuals: %s', $stats->totalIndividuals()); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<?php $stats->totalSexUnknown() > 0 ? $cells = 4 : $cells = 6; ?>
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell medium-<?php echo $cells; ?> text-center">
									<small><?php echo KT_I18N::translate('Total males') . ' ' . $stats->totalSexMales(); ?></small>
								</div>
								<div class="cell medium-<?php echo $cells; ?> text-center">
									<small><?php echo KT_I18N::translate('Total females') . ' ' . $stats->totalSexFemales(); ?></small>
								</div>
								<?php if ($stats->totalSexUnknown() > 0) { ?>
									<div class="cell medium-<?php echo $cells; ?> text-center">
										<small><?php echo KT_I18N::translate('Total unknown') . ' ' . $stats->totalSexUnknown(); ?></small>
									</div>
								<?php } ?>
								<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Individuals by gender'); ?></div>
								<div id="chartSex"></div>
							</div>
						</div>
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell medium-6 text-center">
									<small><?php echo KT_I18N::translate('Total living') . ' ' . $stats->totalLiving(); ?></small>
								</div>
								<div class="cell medium-6 text-center">
									<small><?php echo KT_I18N::translate('Total deceased') . ' ' . $stats->totalDeceased(); ?></small>
								</div>
								<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Individuals by mortality'); ?></div>
								<div id="chartMortality"></div>
							</div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Events'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total births') . '&nbsp;' . $stats->totalBirths(); ?></label>
							<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Births by century'); ?></div>
							<div id="chartStatsBirth"></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total deaths') . '&nbsp;' . $stats->totalDeaths(); ?></label>
							<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Deaths by century'); ?></div>
							<div id="chartStatsDeath"></div>
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
						<div class="cell">
							<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Average age at death, by century'); ?></div>
							<div id="chartStatsAge"></div>
						</div>
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
							<div id="chartCommonSurnames"></div>
							<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Top surnames'); ?></div>
						</div>
						<div class="cell medium-6">
							<div id="chartCommonGiven"></div>
							<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Top given names'); ?></div>
						</div>
					</div>
				</div>
				<!-- FAMILY TAB -->
				<div class="tabs-panel" id="stats-fam">
					<h5><?php echo KT_I18N::translate('Total families: %s', $stats->totalFamilies()); ?></h5>
					<h5><?php echo KT_I18N::translate('Events'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total marriages') . '&nbsp;' . $stats->totalMarriages(); ?></label>
							<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Marriages by century'); ?></div>
							<div id="chartMarr"></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total divorces') . '&nbsp;' . $stats->totalDivorces(); ?></label>
							<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Divorces by century'); ?></div>
							<div id="chartDiv"></div>
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
							<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Average age at marriage, by century'); ?></div>
							<div id="chartMarrAge"></div>
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
							<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Children per family, by century'); ?></div>
							<div id="chartChild"></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Number of families without children'), ' ', $stats->noChildrenFamilies(); ?></div>
							<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Families without children, by century'); ?></div>
							<div id="chartNoChild"></div>
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
				<div class="tabs-panel" id="stats-other">
					<h5><?php echo KT_I18N::translate('Total records: %s', $stats->totalRecords()); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-3">
							<?php echo KT_I18N::translate('Media objects: %s', $stats->totalMedia()); ?>
						</div>
						<div class="cell medium-3">
							<?php echo KT_I18N::translate('Sources: %s', $stats->totalSources()); ?>
						</div>
						<div class="cell medium-3">
							<?php echo KT_I18N::translate('Notes: %s', $stats->totalNotes()); ?>
						</div>
						<div class="cell medium-3">
							<?php echo KT_I18N::translate('Repositories: %s', $stats->totalRepositories()); ?>
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
							<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Media objects by type'); ?></div>
							<div id="chartMedia"></div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Sources: %s', $stats->totalSources()); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell text-center chartTitle">
									<?php echo KT_I18N::translate('Individuals with sources'); ?>
									<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=male" target="_blank">
										<?php echo $stats->totalIndisWithSources(); ?>
									</a>
									 (<?php echo $stats->totalIndividualsPercentage(); ?>)
								 </div>
								<div id="chartIndisWithSources"></div>
							</div>
						</div>
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell text-center chartTitle">
									<?php echo KT_I18N::translate('Families with sources'); ?>
									<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=male" target="_blank">
										<?php echo $stats->totalFamsWithSources(); ?>
									</a>
									 (<?php echo $stats->totalFamiliesPercentage(); ?>)
								 </div>
								<div id="chartFamsWithSources"></div>
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
								<div class="cell text-center chartTitle"><?php echo KT_I18N::translate('Individual distribution chart'); ?></div>
								<div id="chartDistribution"></div>
							</div>"
						</div>
					</div>
				</div>
			</div>
		<?php echo pageClose();
	}

}
