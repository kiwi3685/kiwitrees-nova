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

define('KT_SCRIPT_NAME', 'admin_trees_check.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Check for errors'))
	->pageHeader();

$gedID 	= KT_Filter::post('gedID') ? KT_Filter::post('gedID') : KT_GED_ID;

echo relatedPages($trees, KT_SCRIPT_NAME);

echo pageStart('check_for_errors', $controller->getPageTitle(), 'y', '', 'kb/user-guide/check-for-errors/'); ?>

	<div class="cell callout info-help ">
		<p class="h6"><?php echo KT_I18N::translate('Types of error'); ?></p>
		<p class="alert"><span><?php echo KT_I18N::translate('These items may cause a problem for kiwitrees.'); ?></span></p>
		<p class="warning"><span><?php echo KT_I18N::translate('These items may cause a problem for other applications.'); ?></span></p>
	</div>

	<form class="cell" method="get" action="<?php echo KT_SCRIPT_NAME; ?>">
		<input type="hidden" name="go" value="1">
		<div class="grid-x">
			<div class="cell medium-2">
				<label for="gedID"><?php echo KT_I18N::translate('Family tree'); ?></label>
			</div>
			<div class="cell medium-4">
				<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM); ?>
			</div>

			<?php echo singleButton('Show'); ?>

		</div>
	 </form>

	<?php $errors = false;

	if (KT_Filter::get('go')) {
		// We need to work with raw GEDCOM data, as we are looking for errors
		// which may prevent the KT_GedcomRecord objects from working...

		$rows = KT_DB::prepare('
			SELECT i_id AS xref, "INDI" AS type, i_gedcom AS gedrec FROM `##individuals` WHERE i_file=?
			UNION 
			SELECT f_id AS xref, "FAM"  AS type, f_gedcom AS gedrec FROM `##families`    WHERE f_file=?
			UNION 
			SELECT s_id AS xref, "SOUR" AS type, s_gedcom AS gedrec FROM `##sources`     WHERE s_file=?
			UNION 
			SELECT m_id AS xref, "OBJE" AS type, m_gedcom AS gedrec FROM `##media`       WHERE m_file=?
			UNION 
			SELECT o_id AS xref, o_type AS type, o_gedcom AS gedrec FROM `##other`       WHERE o_file=? AND o_type NOT IN ("HEAD", "TRLR")
		')->execute(array(KT_GED_ID, KT_GED_ID, KT_GED_ID, KT_GED_ID, KT_GED_ID))->fetchAll();

		$records = array();

		foreach ($rows as $row) {
			$records[$row->xref] = $row;
		}

		// Need to merge pending new/changed/deleted records

		$rows = KT_DB::prepare('
			SELECT xref, SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(CASE WHEN old_gedcom="" THEN new_gedcom ELSE old_gedcom END, "\n", 1), " ", 3), " ", -1) AS type, new_gedcom AS gedrec
			FROM (
			SELECT MAX(change_id) AS change_id
			FROM `##change`
			WHERE gedcom_id=? AND status="pending"
			GROUP BY xref
			) AS t1
			JOIN `##change` t2 USING (change_id)
		')->execute(array(KT_GED_ID))->fetchAll();

		foreach ($rows as $row) {
			if ($row->gedrec) {
				// new/updated record
				$records[$row->xref] = $row;
			} else {
				// deleted record
				unset($records[$row->xref]);
			}
		}

		// Keep a list of upper case XREFs, to detect mismatches.
		$ukeys = array();
		foreach (array_keys($records) as $key) {
			$ukeys[strtoupper($key)] = $key;
		}

		// LOOK FOR BROKEN LINKS
		$XREF_LINKS = array(
			'NOTE'          => 'NOTE',
			'SOUR'          => 'SOUR',
			'REPO'          => 'REPO',
			'OBJE'          => 'OBJE',
			'SUBM'          => 'SUBM',
			'FAMC'          => 'FAM',
			'FAMS'          => 'FAM',
			//'ADOP'		=> 'FAM', // Need to handle this case specially.  We may have both ADOP and FAMC links to the same FAM, but only store one.
			'HUSB'          => 'INDI',
			'WIFE'          => 'INDI',
			'CHIL'          => 'INDI',
			'ASSO'          => 'INDI',
			'_ASSO'         => 'INDI', // A kiwitrees extension
			'ALIA'          => 'INDI',
			'AUTH'          => 'INDI', // A kiwitrees extension
			'ANCI'          => 'SUBM',
			'DESI'          => 'SUBM',
			'_KT_OBJE_SORT' => 'OBJE',
		);

		$RECORD_LINKS = array(
			'INDI' => array('NOTE', 'OBJE', 'SOUR', 'SUBM', 'ASSO', '_ASSO', 'FAMC', 'FAMS', 'ALIA', '_KT_OBJE_SORT', '_LOC'),
			'FAM'  => array('NOTE', 'OBJE', 'SOUR', 'SUBM', 'ASSO', '_ASSO', 'HUSB', 'WIFE', 'CHIL', '_LOC'),
			'SOUR' => array('NOTE', 'OBJE', 'REPO', 'AUTH'),
			'REPO' => array('NOTE'),
			'OBJE' => array('NOTE'), // The spec also allows SOUR, but we treat this as a warning
			'NOTE' => array(), // The spec also allows SOUR, but we treat this as a warning
			'SUBM' => array('NOTE', 'OBJE'),
			'SUBN' => array('SUBM'),
			'_LOC' => array('SOUR', 'OBJE', '_LOC'),
		);

		// Generate lists of all links
		$all_links		= array();
		$upper_links	= array();
		foreach ($records as $record) {
			$all_links[$record->xref] = array();
			$upper_links[strtoupper($record->xref)] = $record->xref;
			preg_match_all('/\n\d (' . KT_REGEX_TAG . ') @([^#@\n][^\n@]*)@/', $record->gedrec, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$all_links[$record->xref][$match[2]] = $match[1];
			}
		} ?>

		<div class="cell results">

			<?php foreach ($all_links as $xref1 => $links) {
				// PHP converts array keys to integers.
	            $xref1 = (string) $xref1;

				$type1 = $records[$xref1]->type;
				foreach ($links as $xref2 => $type2) {
					// PHP converts array keys to integers.
	                $xref2 = (string) $xref2;

					$type3 = isset($records[$xref2]) ? $records[$xref2]->type : '';
					if (!array_key_exists($xref2, $all_links)) {
						if (array_key_exists(strtoupper($xref2), $upper_links)) {
							echo warning(
								link_message($type1, $xref1, $type2, $xref2) .' '.
								/* I18N: placeholders are GEDCOM IDs, such as R123 */ KT_I18N::translate('%1$s does not exist.  Did you mean %2$s?', format_link($xref2), format_link($upper_links[strtoupper($xref2)]))
							);
						} else {
							echo error(
								link_message(
									$type1, $xref1, $type2, $xref2) .' '.
									/* I18N: placeholders are GEDCOM IDs, such as R123 */ KT_I18N::translate('%1$s does not exist.', format_link($xref2))
							);
						}
					} elseif ($type2 == 'SOUR' && $type1 == 'NOTE') {
						//echo warning(KT_I18N::translate('The note %1$s has a source %2$s. Notes are intended to add explanations and comments to other records.  They should not have their own sources.'), format_link($xref1), format_link($xref2));
					} elseif ($type2 == 'SOUR' && $type1 == 'OBJE') {
						//echo warning(KT_I18N::translate('The media object %1$s has a source %2$s. Media objects are intended to illustrate other records, facts, and source/citations.  They should not have their own sources.', format_link($xref1), format_link($xref2)));
					} elseif ($type2 == 'OBJE' && $type1 == 'REPO') {
						echo warning(
							link_message($type1, $xref1, $type2, $xref2) . ' ' .  KT_I18N::translate('This type of link is not allowed here.')
						);
					} elseif (!array_key_exists($type1, $RECORD_LINKS) || !in_array($type2, $RECORD_LINKS[$type1]) || !array_key_exists($type2, $XREF_LINKS)) {
						echo error(
							link_message($type1, $xref1, $type2, $xref2) .' '.
							KT_I18N::translate('This type of link is not allowed here.')
						);
					} elseif ($XREF_LINKS[$type2] != $type3) {
						// Target XREF does exist - but is invalid
						echo error(
							link_message($type1, $xref1, $type2, $xref2) .' '.
							/* I18N: %1$s is an internal ID number such as R123.  %2$s and %3$s are record types, such as INDI or SOUR */ KT_I18N::translate('%1$s is a %2$s but a %3$s is expected.', format_link($xref2), format_type($type3), format_type($type2))
						);
					} elseif (
						$type2 == 'FAMC' && (!array_key_exists($xref1, $all_links[$xref2]) || $all_links[$xref2][$xref1] != 'CHIL') ||
						$type2 == 'FAMS' && (!array_key_exists($xref1, $all_links[$xref2]) || $all_links[$xref2][$xref1] != 'HUSB' && $all_links[$xref2][$xref1] != 'WIFE') ||
						$type2 == 'CHIL' && (!array_key_exists($xref1, $all_links[$xref2]) || $all_links[$xref2][$xref1] != 'FAMC') ||
						$type2 == 'HUSB' && (!array_key_exists($xref1, $all_links[$xref2]) || $all_links[$xref2][$xref1] != 'FAMS') ||
						$type2 == 'WIFE' && (!array_key_exists($xref1, $all_links[$xref2]) || $all_links[$xref2][$xref1] != 'FAMS')
					) {
						echo error(
							link_message($type1, $xref1, $type2, $xref2) .' '.
							/* I18N: %1$s and %2$s are internal ID numbers such as R123 */ KT_I18N::translate('%1$s does not have a link back to %2$s.', format_link($xref2), format_link($xref1))
						);
					}
				}
			}

			if (!$errors) { ?>
				<div class="cell callout warning">
					<?php echo KT_I18N::translate('No errors were found.'); ?>
				</div>
			<?php } ?>

		</div>

	<?php }

echo pageClose();

function link_message($type1, $xref1, $type2, $xref2) {
	return
		/* I18N: The placeholders are GEDCOM identifiers and tags.  e.g. “INDI I123 contains a FAMC link to F234.” */ KT_I18N::translate(
			'%1$s %2$s has a %3$s link to %4$s.',
			format_type($type1),
			format_link($xref1),
			format_type($type2),
			format_link($xref2)
		);
}

function format_link($xref) {
	return '<span><a href="gedrecord.php?pid=' . $xref . '">' . $xref . '</a></b>';
}

function format_type($type) {
	return '<span title="' . strip_tags(KT_Gedcom_Tag::getLabel($type)) . '">' . $type . '</b>';
}

function error($message) {
	global $errors;
	$errors = true;
	return '<p class="alert">' . $message . '</p>';
}

function warning($message) {
	global $errors;
	$errors = true;
	return '<p class="warning">' . $message . '</p>';
}
