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
?>

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
