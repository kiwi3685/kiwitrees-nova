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
?>

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
