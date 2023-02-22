<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net.
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

class death_y_bu_plugin extends base_plugin
{
    public static function getName()
    {
        return KT_I18N::translate('Add missing death records');
    }

    public static function getDescription()
    {
        return KT_I18N::translate('You can speed up the privacy calculations by adding a death record to individuals whose death can be inferred from other dates, but who do not have a record of death, burial, cremation, etc.');
    }

    public static function doesRecordNeedUpdate($xref, $gedrec)
    {
        return !preg_match('/\n1 (' . KT_EVENTS_DEAT . ')/', $gedrec) && KT_Person::getInstance($xref)->isDead();
    }

    public static function updateRecord($xref, $gedrec)
    {
        return $gedrec . "\n1 DEAT Y";
    }

    public function getOptionsForm()
    {
        global $iconStyle;

        echo parent::getOptionsForm(); ?>

        <button class="button" onchange="this.form.submit();" name="start" value="start">
            <i class="<?php echo $iconStyle; ?> fa-play-circle"></i>
            <?php echo KT_I18N::translate('Start'); ?>
        </button>

        <hr class="cell">

    <?php }
}
