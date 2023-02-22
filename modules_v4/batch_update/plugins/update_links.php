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

class update_links_bu_plugin extends base_plugin
{
	public static function getName()
	{
		return KT_I18N::translate('Update missing links');
	}

	public static function getDescription()
	{
		return KT_I18N::translate('Occasionally the table of links between records needs to be synchronised with the GEDCOM data. This tools checks for missing links and inserts them into the table.');
	}

	// Default is to operate on INDI records
	public function getRecordTypesToUpdate()
	{
		return ['INDI', 'FAM', 'SOUR', 'REPO', 'NOTE', 'OBJE'];
	}

	public static function doesRecordNeedUpdate($xref, $gedrec)
	{
		preg_match_all('/^\d+ (' . KT_REGEX_TAG . ') @(' . KT_REGEX_XREF . ')@/m', $gedrec, $matches, PREG_SET_ORDER);
		// Try fast check first - no links in table at all
		$record = KT_DB::prepare('SELECT l_to FROM `##link` WHERE l_from = ? AND l_file = ?')->execute([$xref, KT_GED_ID])->fetchAll();
		if ($matches && !$record) {
			return $matches;
		}
	}

	public static function updateRecord($xref, $gedrec)
	{
		// extract all the links from the given record and insert them into the database
		// copy of function in functions_import
		static $sql_insert_link = null;
		if (!$sql_insert_link) {
			$sql_insert_link = KT_DB::prepare('INSERT IGNORE INTO `##link` (l_from,l_to,l_type,l_file) VALUES (?,?,?,?)');
		}

		if (preg_match_all('/^\d+ (' . KT_REGEX_TAG . ') @(' . KT_REGEX_XREF . ')@/m', $gedrec, $matches, PREG_SET_ORDER)) {
			$data = [];
			foreach ($matches as $match) {
				// Include each link once only.
				if (!in_array($match[1] . $match[2], $data)) {
					$data[] = $match[1] . $match[2];
					// Ignore any errors, which may be caused by "duplicates" that differ on case/collation, e.g. "S1" and "s1"
					try {
						$sql_insert_link->execute([$xref, $match[2], $match[1], KT_GED_ID]);
					} catch (PDOException $e) {
						// We could display a warning here....
					}
				}
			}
		}

		return $gedrec;
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
