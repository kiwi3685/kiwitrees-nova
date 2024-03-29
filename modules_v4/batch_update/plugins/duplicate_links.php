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

class duplicate_links_bu_plugin extends base_plugin
{
	public static function getName()
	{
		return KT_I18N::translate('Remove duplicate links');
	}

	public static function getDescription()
	{
		return KT_I18N::translate('A common error is to have multiple links to the same record, for example listing the same child more than once in a family record.');
	}

	// Default is to operate on INDI records
	public function getRecordTypesToUpdate()
	{
		return ['INDI', 'FAM', 'SOUR', 'REPO', 'NOTE', 'OBJE'];
	}

	public static function doesRecordNeedUpdate($xref, $gedrec)
	{
		return
			preg_match('/(\n1.*@.+@.*(?:(?:\n[2-9].*)*))(?:\n1.*(?:\n[2-9].*)*)*\1/', $gedrec)
			|| preg_match('/(\n2.*@.+@.*(?:(?:\n[3-9].*)*))(?:\n2.*(?:\n[3-9].*)*)*\1/', $gedrec)
			|| preg_match('/(\n3.*@.+@.*(?:(?:\n[4-9].*)*))(?:\n3.*(?:\n[4-9].*)*)*\1/', $gedrec);
	}

	public static function updateRecord($xref, $gedrec)
	{
		return preg_replace(
			[
				'/(\n1.*@.+@.*(?:(?:\n[2-9].*)*))((?:\n1.*(?:\n[2-9].*)*)*\1)/',
				'/(\n2.*@.+@.*(?:(?:\n[3-9].*)*))((?:\n2.*(?:\n[3-9].*)*)*\1)/',
				'/(\n3.*@.+@.*(?:(?:\n[4-9].*)*))((?:\n3.*(?:\n[4-9].*)*)*\1)/',
			],
			'$2',
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
