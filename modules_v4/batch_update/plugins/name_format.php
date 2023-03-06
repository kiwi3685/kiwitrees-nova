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

class name_format_bu_plugin extends base_plugin
{
	public static function getName()
	{
		return KT_I18N::translate('Fix name slashes and spaces');
	}

	public static function getDescription()
	{
		return KT_I18N::translate('Correct NAME records of the form \'John/DOE/\' or \'John /DOE\', as produced by older genealogy programs.');
	}

	public static function doesRecordNeedUpdate($xref, $gedrec)
	{
		return
			preg_match('/^(?:1 NAME|2 (?:FONE|ROMN|_MARNM|_AKA|_HEB)) [^\/\n]*\/[^\/\n]*$/m', $gedrec)
			|| preg_match('/^(?:1 NAME|2 (?:FONE|ROMN|_MARNM|_AKA|_HEB)) [^\/\n]*[^\/ ]\//m', $gedrec);
	}

	public static function updateRecord($xref, $gedrec)
	{
		return preg_replace(
			[
				'/^((?:1 NAME|2 _MARNM|2 _AKA) [^\/\n]*\/[^\/\n]*)$/m',
				'/^((?:1 NAME|2 _MARNM|2 _AKA) [^\/\n]*[^\/ ])(\/)/m',
			],
			[
				'$1/',
				'$1 $2',
			],
			$gedrec
		);
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
