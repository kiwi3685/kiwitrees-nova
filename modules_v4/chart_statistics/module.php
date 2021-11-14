<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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
	public function getChartMobile() {
		// exclude this module from mobile displays
		return false;
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
		global $controller, $GEDCOM, $iconStyle, $KT_STATS_CHART_COLOR1, $KT_STATS_CHART_COLOR2, $KT_STATS_CHART_COLOR3, $iconStyle;
		$controller	= new KT_Controller_Page;
		$stats		= new KT_Stats($GEDCOM);
		$tab		= KT_Filter::get('tab', KT_REGEX_NOSCRIPT, 0);

		$controller
			->restrictAccess(KT_Module::isActiveChart(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addExternalJavascript(KT_D3_JS)
			->addExternalJavascript(KT_CONFIRM_JS)
			->addInlineJavascript('
				// force all links except the tab switchers to open in new window
				jQuery("#statistics-page a:not(.tabs)[href]").attr("target", "_blank");

				// Add the jquery_confirm defaults
				jquery_confirm_defaults();

				// Add page specific settings
				jQuery("a.jsConfirm").confirm({
					// Before the modal is displayed.
					onOpenBefore: function () {
						// Set button text for locale
						jQuery(".btnCancel").html("' . KT_I18N::translate('Cancel') . '");
						jQuery(".btnConfirm").html("' . KT_I18N::translate('Continue') . '");

						// Only display warning for long lists where server processing slows too far
						num1 = parseInt(this.$target.html().replace(/\W/g,""));
						if (isNaN(num) || typeof num !== "number" || num <= 5000) {
							url = this.$target.attr("href");
							window.open(url, "_blank");
						}
					},
					title: "' . KT_I18N::translate('Caution - server overload possible') . '",
					content: "' . KT_I18N::translate('Generating lists of large numbers may be slow or not work at all if your server has insufficient resources (i.e. far more than most normal servers). Do you want to continue?') . '",
				});

			');

		include_once 'statistics.js.php'; ?>

		<!-- Start page layout  -->
		<?php echo pageStart('statistics', $controller->getPageTitle()); ?>
			<div class="callout alert small"  data-closable>
				<div class="grid-x">
					<div class="cell">
						<?php echo KT_I18N::translate('Click on links to see more details for each statistic.'); ?>
					</div>
					<button class="close-button" aria-label="Dismiss alert" type="button" data-close>
						<span aria-hidden="true"><i class="<?php echo $iconStyle; ?> fa-times"></i></span>
					</button>
				</div>
			</div>
			<ul class="tabs " id="statistics-tabs" data-tabs data-deep-link="true">
				<li class="tabs-title is-active">
					<a href="#stats-indi" class="tabs" aria-selected="true">
						<span><?php echo KT_I18N::translate('Individuals'); ?></span>
					</a>
				</li>
				<li class="tabs-title">
					<a href="#stats-fam" class="tabs">
						<span><?php echo KT_I18N::translate('Families'); ?></span>
					</a>
				</li>
				<li class="tabs-title">
					<a href="#stats-other" class="tabs">
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
						<?php echo KT_I18N::translate('Total individuals'); ?>
						<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis">
							<?php echo $stats->totalIndividuals(); ?>
						</a>
					</h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<?php $stats->totalSexUnknown() > 0 ? $cells = 4 : $cells = 6; ?>
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell medium-<?php echo $cells; ?> chartKeys text-center">
									<small>
										<i class="<?php echo $iconStyle; ?> fa-circle fa-2x male"></i>
										<?php echo KT_I18N::translate('Males'); ?>&nbsp;
										<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=male">
	                                    	<?php echo $stats->totalSexMales(); ?>
										</a>
										 (<?php echo $stats->totalSexMalesPercentage(); ?>)
									</small>
								</div>
								<div class="cell medium-<?php echo $cells; ?> chartKeys text-center">
									<small>
										<i class="<?php echo $iconStyle; ?> fa-circle fa-2x female"></i>
										<?php echo KT_I18N::translate('Females'); ?>&nbsp;
										<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=female">
                                        	<?php echo $stats->totalSexFemales(); ?>
										</a>
										 (<?php echo $stats->totalSexFemalesPercentage(); ?>)
									</small>
								</div>
								<?php if ($stats->totalSexUnknown() > 0) { ?>
									<div class="cell medium-<?php echo $cells; ?> chartKeys text-center">
										<small>
											<i class="<?php echo $iconStyle; ?> fa-circle fa-2x unknown"></i>
											<?php echo KT_I18N::translate('Unknown'); ?>&nbsp;
											<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=unknown">
                                        		<?php echo $stats->totalSexUnknown(); ?>
											</a>
											 (<?php echo $stats->totalSexUnknownPercentage(); ?>)
										 </small>
									</div>
								<?php } ?>
								<div class="cell text-center pie-chart"><?php echo KT_I18N::translate('Individuals, by gender'); ?></div>
								<div class="cell" id="chartSex"></div>
							</div>
						</div>
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell medium-6 chartKeys text-center">
									<small>
										<i class="<?php echo $iconStyle; ?> fa-circle fa-2x female"></i>
										<?php echo KT_I18N::translate('Total living'); ?>&nbsp;
										<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=living">
											<?php echo $stats->totalLiving(); ?>
										</a>
										 (<?php echo $stats->totalLivingPercentage(); ?>)
									</small>
								</div>
								<div class="cell medium-6 chartKeys text-center">
									<small>
										<i class="<?php echo $iconStyle; ?> fa-circle fa-2x male"></i>
										<?php echo KT_I18N::translate('Total deceased'); ?>&nbsp;
										<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=deceased">
											<?php echo $stats->totalDeceased(); ?>
										</a>
										 (<?php echo $stats->totalDeceasedPercentage(); ?>)
									</small>
								</div>
								<div class="cell text-center pie-chart"><?php echo KT_I18N::translate('Individuals, by living / deceased status'); ?></div>
								<div class="cell" id="chartMortality"></div>
							</div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Individual events'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total births'); ?>
								<?php
	                            $totals      = $stats->totalBirths();
	                            $dated       = $stats->totalDatedBirths();
	                            $undated     = $stats->totalUndatedBirths();
	                            $totalsLink  = '<a class="jsConfirm" href="statisticsTables.php?ged=' . $GEDCOM . '&amp;table=totalBirths">' .
	                                KT_I18n::number($totals['count']) . '
	                            </a>';
	                            $datedLink   = '<a class="jsConfirm" href="statisticsTables.php?ged=' . $GEDCOM . '&amp;table=datedBirths">' .
	                                KT_I18n::number($dated['count']) . '
	                            </a>';
	                            $undatedLink = '<a class="jsConfirm" href="statisticsTables.php?ged=' . $GEDCOM . '&amp;table=undatedBirths">' .
	                                KT_I18n::number($undated['count']) . '
	                            </a>'; ?>
	                            <?php echo KT_I18N::translate('%1s (%2s with a birth date and %3s without)', $totalsLink, $datedLink, $undatedLink); ?>
							</label>
							<div class="cell text-center"><?php echo KT_I18N::translate('Number of births in each century'); ?></div>
							<div class="cell" id="chartStatsBirth"></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total deaths'); ?>
								<?php
                                $totals      = $stats->totalDeaths();
                                $dated       = $stats->totalDatedDeaths();
                                $undated     = $stats->totalUndatedDeaths();
                                $totalsLink  = '<a class="jsConfirm" href="statisticsTables.php?ged=' . $GEDCOM . '&amp;table=totalDeaths">' .
                                    KT_I18n::number($totals['count']) . '
                                </a>';
                                $datedLink   = '<a class="jsConfirm" href="statisticsTables.php?ged=' . $GEDCOM . '&amp;table=datedDeaths">' .
                                    KT_I18n::number($dated['count']) . '
                                </a>';
                                $undatedLink = '<a class="jsConfirm" href="statisticsTables.php?ged=' . $GEDCOM . '&amp;table=undatedDeaths">' .
                                    KT_I18n::number($undated['count']) . '
                                </a>'; ?>
                                <?php echo KT_I18N::translate('%1s (%2s with a death date and %3s without)', $totalsLink, $datedLink, $undatedLink); ?>
							</label>
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
							<?php echo KT_I18N::translate('Average age at death'); ?>&nbsp;<?php echo $stats->averageLifespan(true); ?>
						</div>
						<div class="cell medium-4 text-center">
							<?php echo KT_I18N::translate('Males'); ?>&nbsp;<?php echo $stats->averageLifespanMale(true); ?>
						</div>
						<div class="cell medium-4 text-center">
							<?php echo KT_I18N::translate('Females'); ?>&nbsp;<?php echo $stats->averageLifespanFemale(true); ?>
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
							<label class="h6"><?php echo KT_I18N::translate('Total surnames'); ?>&nbsp;
								<?php echo $stats->totalSurnames(); ?>
							</label>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total given names'); ?>&nbsp;
								<?php echo $stats->totalGivennames(); ?>
							</label>
						</div>
						<div class="cell medium-6">
							<div class="cell" id="chartCommonSurnames"></div>
							<div class="cell text-center"><?php echo KT_I18N::translate('Top 10 surnames'); ?></div>
							<?php
								$proportionSurnames = KT_I18N::number($stats->_totalSurnames() / $stats->_totalIndividuals() * 100, 0) . '%';
							?>
							<div class="cell text-center">
								<small><?php echo KT_I18N::translate('Representing %s of all individuals', $proportionSurnames); ?></small>
							</div>
						</div>
						<div class="cell medium-6">
							<div class="cell" id="chartCommonGiven"></div>
							<div class="cell text-center"><?php echo KT_I18N::translate('Top 10 given names'); ?></div>
							<?php
								$proportionSurnames = KT_I18N::number($stats->_totalGivennames() / $stats->_totalIndividuals() * 100, 0) . '%';
							?>
							<div class="cell text-center">
								<small><?php echo KT_I18N::translate('Representing %s of all individuals', $proportionSurnames); ?></small>
							</div>
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
					<h5>
						<?php echo KT_I18N::translate('Total families'); ?>
						<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalFams">
							<?php echo $stats->totalFamilies(); ?>
						</a>
					</h5>
					<h5><?php echo KT_I18N::translate('Family events'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total marriages'); ?>&nbsp;<?php echo $stats->totalMarriages(); ?></label>
							<div class="cell text-center"><?php echo KT_I18N::translate('Number of marriages in each century'); ?></div>
							<div class="cell" id="chartMarr"></div>
						</div>
						<div class="cell medium-6">
							<label class="h6"><?php echo KT_I18N::translate('Total divorces'); ?>&nbsp;<?php echo $stats->totalDivorces(); ?></label>
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
							<?php echo KT_I18N::translate('Longest marriage'); ?>&nbsp;<?php echo $stats->topAgeOfMarriage(); ?>
						</div>
						<div class="cell medium-6">
							<?php echo KT_I18N::translate('Shortest marriage'); ?>&nbsp;<?php echo $stats->minAgeOfMarriage(); ?>
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
							<?php echo KT_I18N::translate('Youngest male'); ?>&nbsp;<?php echo $stats->youngestMarriageMaleAge(true); ?>
							<div><?php echo $stats->youngestMarriageMale(); ?></div>
						</div>
						<div class="cell medium-6">
							<?php echo KT_I18N::translate('Youngest female'); ?>&nbsp;<?php echo $stats->youngestMarriageFemaleAge(true); ?>
							<div><?php echo $stats->youngestMarriageFemale(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Oldest male'); ?>&nbsp;<?php echo $stats->oldestMarriageMaleAge(true); ?></div>
							<div><?php echo $stats->oldestMarriageMale(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Oldest female'); ?>&nbsp;<?php echo $stats->oldestMarriageFemaleAge(true); ?></div>
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
							<?php echo KT_I18N::translate('Youngest father'); ?>&nbsp;<?php echo $stats->youngestFatherAge(true); ?>
							<div><?php echo $stats->youngestFather(); ?></div>
						</div>
						<div class="cell medium-6">
							<?php echo KT_I18N::translate('Youngest mother'); ?>&nbsp;<?php echo $stats->youngestMotherAge(true); ?>
							<div><?php echo $stats->youngestMother(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Oldest father'); ?>&nbsp;<?php echo $stats->oldestFatherAge(true); ?></div>
							<div><?php echo $stats->oldestFather(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Oldest mother'); ?>&nbsp;<?php echo $stats->oldestMotherAge(true); ?></div>
							<div><?php echo $stats->oldestMother(); ?></div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Children in family'); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<div class="strong"><?php echo KT_I18N::translate('Average number of children per family'); ?>
								<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalFams&amp;tag=withchildren">
									<?php echo $stats->averageChildren(); ?></div>
								</a>
							<hr>
							<div class="cell text-center"><?php echo KT_I18N::translate('Number of children per family, by century'); ?></div>
							<div class="cell" id="chartChild"></div>
						</div>
						<div class="cell medium-6">
							<div class="strong">
								<?php echo KT_I18N::translate('Number of families without children'); ?>
								<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalFams&amp;tag=nochildren">
									<?php echo $stats->noChildrenFamilies(); ?>
								</a>
								<span data-tooltip class="strong top" data-click-open="false" data-alignment="center" title="<?php echo KT_I18N::translate('Total families with no children may not match Totals by Century. The latter can only include those with recorded date of marriage.'); ?>">
									<i class="<?php echo $iconStyle; ?> fa-exclamation-circle"></i>
								</span>
							</div>
							<hr>
							<div class="cell text-center"><?php echo KT_I18N::translate('Number of families without children, by century'); ?></div>
							<div class="cell" id="chartNoChild"></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Largest families'); ?>&nbsp;<?php echo $stats->topTenLargestFamilyList(); ?></div>
						</div>
						<div class="cell medium-6">
							<div><?php echo KT_I18N::translate('Largest number of grandchildren'); ?>&nbsp;<?php echo $stats->topTenLargestGrandFamilyList(); ?></div>
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
					<h5><?php echo KT_I18N::translate('Total records %s', $stats->totalRecords()); ?></h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-3">
							<?php echo KT_I18N::translate('Media objects'); ?>
							<a class="jsConfirm" href="module.php?action=filter&search=yes&mod=list_media&mod_action=show&folder=&subdirs=on&sortby=title&form_type=&max=18&filter=&ged=<?php echo $GEDCOM; ?>">
								<?php echo $stats->totalMedia(); ?>
							</a>
						</div>
						<div class="cell medium-3">
							<?php echo KT_I18N::translate('Sources'); ?>
							<a class="jsConfirm" href="module.php?mod=list_sources&mod_action=show&ged=<?php echo $GEDCOM; ?>">
								<?php echo $stats->totalSources(); ?>
							</a>
						</div>
						<div class="cell medium-3">
							<?php echo KT_I18N::translate('Shared notes'); ?>
							<a class="jsConfirm" href="module.php?mod=list_shared_notes&mod_action=show&ged=<?php echo $GEDCOM; ?>">
								<?php echo $stats->totalNotes(); ?>
							</a>
						</div>
						<div class="cell medium-3">
							<?php echo KT_I18N::translate('Repositories'); ?>
							<a class="jsConfirm" href="module.php?mod=list_repositories&mod_action=show&ged=<?php echo $GEDCOM; ?>">
								<?php echo $stats->totalRepositories(); ?>
							</a>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Total events %s', $stats->totalEvents()); ?></h5>
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
					<h5>
						<?php echo KT_I18N::translate('Media objects'); ?>
						<a class="jsConfirm" href="module.php?action=filter&search=yes&mod=list_media&mod_action=show&folder=&subdirs=on&sortby=title&form_type=&max=18&filter=&ged=<?php echo $GEDCOM; ?>">
							<?php echo $stats->totalMedia(); ?>
						</a>
					</h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell">
							<div class="cell text-center"><?php echo KT_I18N::translate('Media objects by type'); ?></div>
							<div class="cell" id="chartMedia"></div>
						</div>
					</div>
					<h5>
						<?php echo KT_I18N::translate('Sources'); ?>
						<a class="jsConfirm" href="module.php?mod=list_sources&amp;mod_action=show&amp;ged=<?php echo $GEDCOM; ?>">
							<?php echo $stats->totalSources(); ?>
						</a>
					</h5>
					<div class="grid-x grid-margin-x grid-margin-y statisticSection">
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell text-center"><?php echo KT_I18N::translate('Individuals'); ?></div>
								<div class="cell medium-6 chartKeys text-center">
									<small>
										<i class="<?php echo $iconStyle; ?> fa-circle fa-2x female"></i>
										<?php echo KT_I18N::translate('With sources') . '&nbsp'; ?>
										<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=withsour">
											<?php echo $stats->totalIndisWithSources(); ?>
										</a>
										 (<?php echo $stats->totalIndisWithSourcesPercentage(); ?>)
									 </small>
								 </div>
								 <div class="cell medium-6 chartKeys text-center">
									 <small>
 										<i class="<?php echo $iconStyle; ?> fa-circle fa-2x male"></i>
										<?php echo KT_I18N::translate('Without sources') . '&nbsp;'; ?>
											<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=withoutsour">
												<?php echo $stats->totalIndisWithoutSources(); ?>
											</a>
 										 (<?php echo $stats->totalIndisWithoutSourcesPercentage(); ?>)
 									 </small>
								 </div>
								<div class="cell" id="chartIndisWithSources"></div>
							</div>
						</div>
						<div class="cell medium-6">
							<div class="grid-x">
								<div class="cell text-center"><?php echo KT_I18N::translate('Families'); ?></div>
								<div class="cell medium-6 chartKeys text-center">
									<small>
										<i class="<?php echo $iconStyle; ?> fa-circle fa-2x female"></i>
										<?php echo KT_I18N::translate('With sources') . '&nbsp;'; ?>
										<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalFams&amp;tag=withsour">
											<?php echo $stats->totalFamsWithSources(); ?>
										</a>
										 (<?php echo $stats->totalFamsWithSourcesPercentage(); ?>)
									 </small>
								 </div>
								 <div class="cell medium-6 chartKeys text-center">
									 <small>
 										<i class="<?php echo $iconStyle; ?> fa-circle fa-2x male"></i>
										<?php echo KT_I18N::translate('Without sources') . '&nbsp;'; ?>
 										<a class="jsConfirm" href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalFams&amp;tag=withoutsour">
											<?php echo $stats->totalFamsWithoutSources(); ?>
 										</a>
 										 (<?php echo $stats->totalFamsWithoutSourcesPercentage(); ?>)
 									 </small>
								 </div>
								<div class="cell" id="chartFamsWithSources"></div>
							</div>
						</div>
					</div>
					<h5><?php echo KT_I18N::translate('Places %s', $stats->totalPlaces()); ?></h5>
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
							<label class="h6">
								<?php echo KT_I18N::translate('Events in countries'); ?>
								<span data-tooltip class="strong top" data-click-open="false" data-alignment="center" title="<?php echo KT_I18N::translate('Any events in the country, including multiple events per individual.'); ?>">
									<i class="<?php echo $iconStyle; ?> fa-exclamation-circle"></i>
								</span>
							</label>
							<div>
								<?php echo $stats->commonCountriesList(); ?>
							</div>
						</div>
						<div class="cell">
							<div class="grid-x">
								<div class="cell text-center h5">
									<?php echo KT_I18N::translate('Individual distribution chart'); ?>
									<span
										data-tooltip class="strong top"
										data-alignment="center"
										title="
											<?php echo KT_I18N::translate('Number of individuals with one or more events in the country.'); ?>
											<?php echo KT_I18N::translate('High populations are each 20 percent or more of the total.'); ?>
										">
										<i class="<?php echo $iconStyle; ?> fa-exclamation-circle"></i>
									</span>
								</div>
								<div class="cell medium-9" id="chartDistribution"></div>
								<div class="cell medium-3 topCountries">
									<label class="h5"><?php echo KT_I18N::translate('Top countries'); ?></label>
									<div class="scrollBlock">
										<?php echo $stats->statsChartPlacesList(); ?>
									</div>
									<div><small><?php echo KT_I18N::translate('Scroll for more...'); ?></small></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php echo pageClose();
	}
}
