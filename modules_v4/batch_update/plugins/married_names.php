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

class married_names_bu_plugin extends base_plugin
{
	public $surname; // User option: add or replace husband's surname

	public static function getName()
	{
		return KT_I18N::translate('Add missing married names');
	}

	public static function getDescription()
	{
		return KT_I18N::translate('You can make it easier to search for married women by recording their married name.<br />However not all women take their husband\'s surname, so beware of introducing incorrect information into your database.');
	}

	public function doesRecordNeedUpdate($xref, $gedrec)
	{
		return preg_match('/^1 SEX F/m', $gedrec) && preg_match('/^1 NAME /m', $gedrec) && self::_surnames_to_add($xref, $gedrec);
	}

	public function updateRecord($xref, $gedrec)
	{
		$SURNAME_TRADITION = get_gedcom_setting(KT_GED_ID, 'SURNAME_TRADITION');

		preg_match('/^1 NAME (.*)/m', $gedrec, $match);
		$wife_name = $match[1];
		$married_names = [];

		foreach (self::_surnames_to_add($xref, $gedrec) as $surname) {
			switch ($this->surname) {
				case 'add':
					$married_names[] = "\n2 _MARNM " . str_replace('/', '', $wife_name) . ' /' . $surname . '/';

					break;

				case 'replace':
					if ('polish' == $SURNAME_TRADITION) {
						$surname = preg_replace(['/ski$/', '/cki$/', '/dzki$/'], ['ska', 'cka', 'dzka'], $surname);
					}
					$married_names[] = "\n2 _MARNM " . preg_replace('!/.*/!', '/' . $surname . '/', $wife_name);

					break;
			}
		}

		return preg_replace('/(^1 NAME .*([\r\n]+[2-9].*)*)/m', '\\1' . implode('', $married_names), $gedrec, 1);
	}

	public static function _surnames_to_add($xref, $gedrec)
	{
		$wife_surnames = self::_surnames($xref, $gedrec);
		$husb_surnames = [];
		$missing_surnames = [];
		preg_match_all('/^1 FAMS @(.+)@/m', $gedrec, $fmatch);
		foreach ($fmatch[1] as $famid) {
			$famrec = batch_update::getLatestRecord($famid, 'FAM');
			if (preg_match('/^1 ' . KT_EVENTS_MARR . '/m', $famrec) && preg_match('/^1 HUSB @(.+)@/m', $famrec, $hmatch)) {
				$husbrec = batch_update::getLatestRecord($hmatch[1], 'INDI');
				$husb_surnames = array_unique(array_merge($husb_surnames, self::_surnames($hmatch[1], $husbrec)));
			}
		}
		foreach ($husb_surnames as $husb_surname) {
			if (!in_array($husb_surname, $wife_surnames)) {
				$missing_surnames[] = $husb_surname;
			}
		}

		return $missing_surnames;
	}

	public static function _surnames($xref, $gedrec)
	{
		if (preg_match_all('/^(?:1 NAME|2 _MARNM) .*\/(.+)\//m', $gedrec, $match)) {
			return $match[1];
		}

		return [];
	}

	// Add an option for different surname styles
	public function getOptions()
	{
		parent::getOptions();
		$this->surname = safe_GET('surname', ['add', 'replace'], 'replace');
	}

	public function getOptionsForm()
	{
		global $iconStyle;

		echo parent::getOptionsForm(); ?>

		<div class="cell medium-2">
			<label>
				<?php echo KT_I18N::translate('Surname Option'); ?>
			</label>
		</div>
		<div class="cell medium-4">
			<select name="surname" onchange="reset_reload();">
					<option value="replace"
						<?php echo 'replace' == $this->surname ? ' selected="selected"' : ''; ?>
					>
						<?php echo KT_I18N::translate('Wife\'s surname replaced by husband\'s surname'); ?>
					</option>
					<option value="add"
						<?php echo 'add' == $this->surname ? ' selected="selected"' : ''; ?>
					>
						<?php echo KT_I18N::translate('Wife\'s maiden surname becomes new given name'); ?>
					</option>
			</select>
		</div>
		<div class="cell medium-6"></div>

		<button class="button" onchange="this.form.submit();" name="start" value="start">
			<i class="<?php echo $iconStyle; ?> fa-play-circle"></i>
			<?php echo KT_I18N::translate('Start'); ?>
		</button>

		<hr class="cell">

	<?php }

}
