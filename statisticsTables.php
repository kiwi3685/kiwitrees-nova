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

define('KT_SCRIPT_NAME', 'statisticsTables.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_print_lists.php';

$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_I18N::translate('Statistics tables'))
	->pageHeader();

global $GEDCOM;

$ged_id		= get_id_from_gedcom($GEDCOM);
$stats		= new KT_Stats($GEDCOM);
$table		= KT_Filter::get('table');
$option		= KT_Filter::get('option');
$tag		= KT_Filter::get('tag');
$subtitle 	= '';
$list	= array();

switch ($table) {
	case 'totalIndis':
		if ($option == NULL) {
			$title 		= KT_I18N::translate('Total individuals');
			$content	= simple_indi_table($stats->individualsList($ged_id));
		} else {
			switch ($option){
				case 'male':
					$title 		= KT_I18N::translate('Total males');
					$content	= simple_indi_table($stats->individualsList($ged_id, 'male'));
				break;
				case 'female':
					$title 		= KT_I18N::translate('Total females');
					$content	= simple_indi_table($stats->individualsList($ged_id, 'female'));
				break;
				case 'unknown':
					$title 		= KT_I18N::translate('Total unknown gender');
					$content	= simple_indi_table($stats->individualsList($ged_id, 'unknown'));
				break;
				case 'living':
					$title 		= KT_I18N::translate('Total living');
					$content	= simple_indi_table($stats->individualsList($ged_id, 'living'));
				break;
				case 'deceased':
					$title 		= KT_I18N::translate('Total deceased');
					$content	= simple_indi_table($stats->individualsList($ged_id, 'deceased'));
				break;
				case 'withsour':
					$title 		= KT_I18N::translate('Individuals with sources');
					$content	= simple_indi_table($stats->individualsList($ged_id, 'withsour'));
				break;
				case 'withoutsour':
					$title 		= KT_I18N::translate('Individuals without sources');
					$content	= simple_indi_table($stats->individualsList($ged_id, 'withoutsour'));
				break;
			}
		}
	break;
	case 'century': {
		switch ($tag) {
			case 'birt':
				$gTag	= 'BIRT';
				$label	= 'births';
			break;
			case 'deat':
				$gTag	= 'DEAT';
				$label	= 'deaths';
			break;
			case 'marr':
				$gTag	= 'MARR';
				$label	= 'marriages';
			break;
			case 'div':
				$gTag	= 'DIV';
				$label	= 'divorces';
			break;
		}
		$year = $option * 100 - 100;
		$rows = KT_DB::prepare("
			SELECT DISTINCT `d_gid` FROM `##dates`
				WHERE `d_file`=? AND
				`d_year` >= ? AND
				`d_year` < ? AND
				`d_fact`='" . $gTag . "' AND
				`d_type` IN ('@#DGREGORIAN@', '@#DJULIAN@')
		")->execute(array($ged_id, $year, $year + 100))->fetchAll(PDO::FETCH_ASSOC);
		switch ($tag){
			case 'birt':
			case 'deat':
				foreach ($rows as $row) {
					$person = KT_Person::getInstance($row['d_gid']);
						$list[] = clone $person;
				}
				$title 		= KT_I18N::translate('Number of %s in the %s century', $label, $stats->_centuryName($option));
				$content	= simple_indi_table($list);
			break;
			case 'marr':
			case 'div':
				foreach ($rows as $row) {
					$family = KT_Family::getInstance($row['d_gid']);
						$list[] = clone $family;
				}
				$title 		= KT_I18N::translate('Number of %s in the %s century', $label, $stats->_centuryName($option));
				$content	= format_fam_table($list);
			break;
		}
	}
	break;
	case 'totalFams' :
		$title 		= KT_I18N::translate('Total families');
		$content	= format_fam_table($stats->famsList($ged_id));
		switch ($tag){
			case 'marr' :
				$title 		= KT_I18N::translate('Total marriages');
				$content	= format_fam_table($stats->totalEvents(array('MARR'), true));
			break;
			case 'div' :
				$title 		= KT_I18N::translate('Total divorces');
				$content	= format_fam_table($stats->totalEvents(array('DIV'), true));
			break;
			case 'withchildren' :
				$list       = $stats->totalChildrenTable();
				$title 		= KT_I18N::translate('All families with children recorded');
				$content	= format_fam_table($list, 'sort_children');
			break;
			case 'nochildren' :
				$list       = $stats->totalNoChildrenTable();
				$title 		= KT_I18N::translate('All families with no children recorded');
				$content	= format_fam_table($list);
			break;
			case 'withchildrenbycentury' :
				$sumChildren = 0;
				$rows = KT_DB::prepare("
					SELECT fam.*
					FROM `##families` AS fam
					JOIN `##dates` AS married ON (married.d_file = fam.f_file AND married.d_gid = fam.f_id)
					WHERE
						f_numchil > 0 AND
					    fam.f_file = ? AND
						married.d_fact IN ('MARR', '_NMR') AND
					    married.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND
					    FLOOR(married.d_year/100+1) = ?
					GROUP BY fam.f_id, FLOOR(married.d_year/100+1)
				")->execute(array($ged_id, $option))->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows as $row) {
					$family = KT_Family::getInstance($row['f_id']);
					$list[] = clone $family;
					$sumChildren += $row['f_numchil'];
				}
				$title 		= KT_I18N::translate('Families with children in the %s century', $stats->_centuryName($option));
				$subtitle	= KT_I18N::translate('Average = %s per family', KT_I18N::number($sumChildren / count($rows), 1));
				$content	= format_fam_table($list, 'sort_children');
			break;
			case 'nochildrenbycentury' :
				$rows = KT_DB::prepare("
					SELECT fam.* FROM `##families` AS fam
					JOIN `##dates` AS married ON (married.d_file = fam.f_file AND married.d_gid = fam.f_id)
					WHERE
						f_numchil = 0 AND
					    fam.f_file = ? AND
						married.d_fact IN ('MARR', '_NMR') AND
					    married.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND
					    FLOOR(married.d_year/100+1) = ?
					GROUP BY fam.f_id, married.d_year
				")->execute(array($ged_id, $option))->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows as $row) {
					$family = KT_Family::getInstance($row['f_id']);
						$list[] = clone $family;
				}
				$title 		= KT_I18N::translate('Families with no children in the %s century', $stats->_centuryName($option));
				$content	= format_fam_table($list);
			break;
			case 'withsour':
				$title 		= KT_I18N::translate('Families with sources');
				$content	= format_fam_table($stats->famsList($ged_id, 'withsour'));
			break;
			case 'withoutsour':
				$title 		= KT_I18N::translate('Families without sources');
				$content	= format_fam_table($stats->famsList($ged_id, 'withoutsour'));
			break;
		}
	break;
    case 'totalBirths' :
        $list       = $stats->totalBirths();
        $title 		= KT_I18N::translate('Total births');
        $content	= simple_indi_table($list['list']);
    break;
    case 'datedBirths' :
        $list       = $stats->totaldatedBirths();
        $title 		= KT_I18N::translate('Total dated births');
        $content	= simple_indi_table($list['list']);
    break;
    case 'undatedBirths' :
        $list       = $stats->totalUndatedBirths();
        $title 		= KT_I18N::translate('Total undated births');
        $content	= simple_indi_table($list['list']);
    break;
	case 'noRecordBirths' :
		$list       = $stats->noBirthRecorded();
		$title 		= KT_I18N::translate('Individuals with no birth record');
		$subtitle	= KT_I18N::translate('(Baptism or christening dates may be displayed instead of birth dates if available)');
		$content	= simple_indi_table($list['list']);
	break;
    case 'totalDeaths' :
        $list       = $stats->totalDeaths();
        $title 		= KT_I18N::translate('Total deaths');
        $content	= simple_indi_table($list['list']);
    break;
    case 'datedDeaths' :
        $list       = $stats->totaldatedDeaths();
        $title 		= KT_I18N::translate('Total dated deaths');
        $content	= simple_indi_table($list['list']);
    break;
    case 'undatedDeaths' :
        $list       = $stats->totalUndatedDeaths();
        $title 		= KT_I18N::translate('Total undated deaths');
        $content	= simple_indi_table($list['list']);
    break;
	default:
		$title 		= '';
		$content	= KT_I18N::translate('No table selected');
	break;
	case 'commonNames':
		switch ($option){
			case 'surn':
			$count = 0;
				$surns = KT_Query_Name::surnames($tag, '', false, false, KT_GED_ID);
				foreach ($surns as $surnames) {
					$legend = implode('/', array_keys($surnames));
					foreach ($surnames as $xrefList) {
						foreach ($xrefList as $xref => $num) {
							$person = KT_Person::getInstance($xref);
							$list[] = clone $person;
						}
					}
				}
				$title 		= KT_I18N::translate('All individuals with the surname "%s"', $legend);
				$content	= simple_indi_table($list);
			break;
			case 'givn':
				$rows = KT_DB::prepare("
					SELECT DISTINCT n_id
					FROM `##name`
					JOIN `##individuals` ON (n_id = i_id AND n_file = i_file)
					WHERE `n_file` = ?
						AND `n_type` NOT IN ('_MARNM','_AKA')
						AND n_givn NOT IN ('@P.N.', '')
						AND LENGTH(n_givn) > 1
						AND i_sex<>'U'
						AND `n_givn` REGEXP CONCAT('[[:<:]]', ?, '[[:>:]]')
					GROUP BY n_id
				")->execute(array($ged_id, $tag))->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows as $row) {
					$person = KT_Person::getInstance($row['n_id']);
						$list[] = clone $person;
				}
				$title 		= KT_I18N::translate('All individuals with the given name "%s"', $tag);
				$subtitle	= KT_I18N::translate('(Number may differ from chart where individuals have multiple names recorded)');
				$content	= simple_indi_table($list);
			break;
		}
	break;
}

	echo pageStart('statistics_tables', $controller->getPageTitle()); ?>
		<?php if (!KT_USER_ID) { ?>
		<div class="callout alert small"  data-closable>
			<div class="grid-x">
				<button class="close-button" aria-label="Dismiss alert" type="button" data-close>
					<span aria-hidden="true"><i class="<?php echo $iconStyle; ?> fa-xmark"></i></span>
				</button>
				<div class="cell">
					<?php echo KT_I18N::translate('Due to privacy settings the number of items in this list may be less than the number on the statistics chart'); ?>
				</div>
			</div>
		</div>
	<?php } ?>
	<h5 class="text-center"><?php echo $title; ?></h5>
	<h6 class="text-center"><?php echo $subtitle; ?></h6>
	<?php echo $content;
	pageClose();
