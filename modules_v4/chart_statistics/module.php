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
		$controller		= new KT_Controller_Page;
		$stats			= new KT_Stats($GEDCOM);
		$tab			= KT_Filter::get('tab', KT_REGEX_NOSCRIPT, 0);
		$memory_limit	= (int)(int_from_bytestring(ini_get('memory_limit')) / (1024 * 1024));

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
				memory = ' . $memory_limit . ';
				jQuery("a.jsConfirm").confirm({
					title: "' . KT_I18N::translate('Caution - server overload possible') . '",
					content: "' . KT_I18N::translate('Generating lists of large numbers may be slow or not work at all if your server has insufficient resources (i.e. far more than most normal servers). Do you want to continue?') . '",
					// Before the modal is displayed.
					onOpenBefore: function () {
						var jc = this;
						// Set button text for locale
						jQuery(".btnCancel").html("' . KT_I18N::translate('Cancel') . '");
						jQuery(".btnConfirm").html("' . KT_I18N::translate('Continue') . '");

						// Only display warning for long lists where server processing slows too far
						num = parseInt(this.$target.html().replace(/\W/g,""));
						if (isNaN(num) || typeof num !== "number" || (num <= 5000 && memory >= 256)) {
							jc.close();
							url = this.$target.attr("href");
							window.open(url, "_blank");
						}
					}
				});

			');
		?>
		<!-- Start page layout  -->
		<?php echo pageStart('statistics', $controller->getPageTitle()); ?>

			<?php include_once 'statistics.js.php'; ?>

			<div class="callout alert small" data-closable>
				<div class="grid-x">
					<div class="cell">
						<?php echo KT_I18N::translate('Click on highlighted links to see more details for each statistic.'); ?>
					</div>
					<button class="close-button" aria-label="Dismiss alert" type="button" data-close>
						<span aria-hidden="true"><i class="<?php echo $iconStyle; ?> fa-xmark"></i></span>
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

				<?php include 'pages/individuals.php'; ?>

				<!-- FAMILY TAB -->
				<?php
				$controller->addInlineJavascript('
					barChart("chartMarr");
					barChart("chartDiv");
					groupChart("chartMarrAge");
					barChart("chartChild");
					barChart("chartNoChild");
				'); ?>

				<?php include 'pages/families.php'; ?>

				<!-- OTHER TAB -->
				<?php $controller->addInlineJavascript('
					barChart("chartMedia");
					pieChart("chartIndisWithSources");
					pieChart("chartFamsWithSources");
					mapChart("chartDistribution");
				'); ?>

				<?php include 'pages/other.php'; ?>
			</div>

		<?php echo pageClose();
	}
}
