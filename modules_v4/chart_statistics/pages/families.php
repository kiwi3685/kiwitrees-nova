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
?>

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
