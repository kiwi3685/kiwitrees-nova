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

/**
 * Compare various event datess with date of birth
 *
 * @param array $tag_array - an array of vaild GEDCOM tags for comparison
 * @param string $tag2 - a single secondary tag where $tag_array is FAMS, FAMC, etc (e.g. array('FAMS'), 'CHIL');)
 */
function birth_comparisons($gedID, $tag_array, $tag2 = '') {
	$html		= '';
	$count		= 0;
	$tag_count	= count($tag_array);
	$start		= microtime(true);
	$sql		= "
					SELECT i_id AS xref
					FROM `##individuals`
					WHERE `i_file` = ?
					AND `i_gedcom` LIKE CONCAT('%1 ', ?, '%')
					AND `i_gedcom` NOT LIKE CONCAT('%1 ', ?, ' Y%')
				  ";

	for ($i = 0; $i < $tag_count; $i ++) {
		$rows = KT_DB::prepare($sql)->execute(array($gedID, $tag_array[$i], $tag_array[$i]))->fetchAll();

		foreach ($rows as $row) {
			$person		= KT_Person::getInstance($row->xref);
			$birth_date = $person->getBirthDate();

			switch ($tag_array[$i]) {
				case ('FAMS'):
					switch ($tag2) {
						case 'MARR':
							foreach ($person->getSpouseFamilies() as $family) {
								$event_date	= $family->getMarriageDate();
								$age_diff	= KT_Date::Compare($event_date, $birth_date);
								if ($event_date->MinJD() && $birth_date->MinJD() && ($age_diff < 0)) {
									$html .= '
										<tr>
											<td><a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a></td>
											<td>' . $birth_date->Display() . '</td>
											<td>' . $event_date->Display() . '</td>
										</tr>';
									$count ++;
								}
							}
						break;
						case 'CHIL':
							foreach ($person->getSpouseFamilies() as $family) {
								$children = $family->getChildren();
								foreach ($children as $child) {
									$event_date	= $child->getBirthDate();
									$age_diff	= KT_Date::Compare($event_date, $birth_date);
									if ($event_date->MinJD() && $birth_date->MinJD() && ($age_diff < 0)) {
										$html .= '
											<tr>
												<td><a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a></td>
												<td>' . $birth_date->Display() . '</td>
												<td><a href="' . $child->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $child->getFullName() . '</a>' . '</td>
												<td>' . $event_date->Display() . '</td>
											</tr>';
										$count ++;
									}
								}
							}
						break;
						case 'CHIL_AGES':
							$families		= array();
							$dates			= array();
							$differences	= array();
							foreach ($person->getSpouseFamilies() as $family) {
								if (!in_array($family->getXref(), $families)) {
									$families[]	= $family->getXref();
									$children	= $family->getChildren();
									if (count($children) > 1) {
										foreach ($children as $child) {
											$dates[$child->getXref()] = $child->getBirthDate()->MinJD();
										}
									}
									if ($dates) {
										asort($dates);
										foreach ($dates as $xref => $day) {
											$xrefs[] = $xref;
											foreach ($dates as $xref2 => $day2) {
												if ($xref <> $xref2 && !in_array($xref2, $xrefs)) {
													$diff = $day2 - $day;
													if ($diff > 1 && $diff < (365 / 12 * 9)) { // more than 1 daye & less than 9 months
														$months		= round($diff / (365 / 12), 0);
														$person1	= KT_Person::getInstance($xref);
														$person2	= KT_Person::getInstance($xref2);
														$html .= '
															<tr>
																<td><a href="' . $person1->getHtmlUrl(). '#relatives" target="_blank" rel="noopener noreferrer">' . $person1->getFullName() . '</a></td>
																<td><a href="' . $person2->getHtmlUrl(). '#relatives" target="_blank" rel="noopener noreferrer">' . $person2->getFullName() . '</a></td>
																<td>' . KT_I18N::plural('%s month', '%s months', $months, $months) . '</td>
															</tr>';
														$count ++;
													}
												}
											}
										}
									}
								}
							}
						break;
					}
				break;
				case 'FAMC':
				break;
				default:
					$event = $person->getFactByType($tag_array[$i]);
					if ($event) {
						$event_date = $person->getFactByType($tag_array[$i])->getDate();
						$age_diff	= KT_Date::Compare($event_date, $birth_date);
						if ($event_date->MinJD() && $birth_date->MinJD() && ($age_diff < 0)) {
							$html .= '
								<tr>
									<td><a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a></td>
									<td>' . $birth_date->Display() . '</td>
									<td>' . $event_date->Display() . '</td>
								</tr>';
							$count ++;
						}
					}
				break;
			}
		}
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);

	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function death_comparisons($gedID, $tag_array) {
	$html		= '';
	$count		= 0;
	$tag_count	= count($tag_array);
	$start		= microtime(true);
	$sql		= "
					SELECT i_id AS xref
					FROM `##individuals`
					WHERE `i_file` = ?
					AND `i_gedcom` LIKE CONCAT('%1 ', ?, '%')
					AND `i_gedcom` NOT LIKE CONCAT('%1 ', ?, ' Y%')
				  ";

	for ($i = 0; $i < $tag_count; $i ++) {
		$rows = KT_DB::prepare($sql)->execute(array($gedID, $tag_array[$i], $tag_array[$i]))->fetchAll();

		foreach ($rows as $row) {
			$person		= KT_Person::getInstance($row->xref);
			$death_date = $person->getDeathDate();
			$event		= $person->getFactByType($tag_array[$i]);
			if ($event) {
				$event_date = $event->getDate();
				$age_diff	= KT_Date::Compare($event_date, $death_date);
				if ($event_date->MinJD() && $death_date->MinJD() && ($age_diff < 0)) {
					$html .= '
						<tr>
							<td><a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a></td>
							<td>' . $event_date->Display() . '</td>
							<td>' . $death_date->Display() . '</td>
						</tr>';
					$count ++;
				}
			}
		}
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);

	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function query_age($gedID, $tag_array, $age) {
	$html		= '';
	$count		= 0;
	$tag_count	= count($tag_array);
	$start		= microtime(true);

	for ($i = 0; $i < $tag_count; $i ++) {
		switch ($tag_array[$i]) {
			case ('DEAT'):
				$sql = "
					SELECT
					 birth.d_gid AS xref,
					 YEAR(NOW()) - birth.d_year AS age,
					 birth.d_year AS birthyear
					FROM
					 `##dates` AS birth,
					 `##individuals` AS indi
					WHERE
					 indi.i_id = birth.d_gid AND
					 indi.i_gedcom NOT REGEXP '\\n1 (" . KT_EVENTS_DEAT . ")' AND
					 birth.d_file = ? AND
					 birth.d_fact = 'BIRT' AND
					 birth.d_file = indi.i_file AND
					 birth.d_julianday1 <> 0 AND
					 YEAR(NOW()) - birth.d_year > ?
					GROUP BY xref, birthyear
					ORDER BY age DESC
				";
				$rows		= KT_DB::prepare($sql)->execute(array($gedID, $age))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
			case ('MARR'):
				$sql = "
					SELECT
					 birth.d_gid AS xref,
					 married.d_year AS marryear,
					 married.d_year - birth.d_year AS age
					 FROM `##families` AS fam
					 INNER JOIN `##dates` AS birth ON birth.d_file = ?
					 INNER JOIN `##dates` AS married ON married.d_file = ?
					 WHERE
						fam.f_file = ? AND
						married.d_gid = fam.f_id AND
						(birth.d_gid = fam.f_wife OR birth.d_gid = fam.f_HUSB) AND
						birth.d_fact = 'BIRT' AND
						married.d_fact = 'MARR' AND
						birth.d_julianday1 <> 0 AND
						married.d_julianday2 > birth.d_julianday1 AND
						married.d_year - birth.d_year < ?
					GROUP BY xref, marryear, birth.d_year
					ORDER BY age DESC
				";
				$rows		= KT_DB::prepare($sql)->execute(array($gedID, $gedID, $gedID, $age))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
			case ('FAMS'):
				$sql = "
					SELECT
					 fam.f_id AS xref,
					 MIN(wifebirth.d_year-husbbirth.d_year) AS age
					 FROM `##families` AS fam
					 LEFT JOIN `##dates` AS wifebirth ON wifebirth.d_file = ?
					 LEFT JOIN `##dates` AS husbbirth ON husbbirth.d_file = ?
					 WHERE
						fam.f_file = ? AND
						husbbirth.d_gid = fam.f_husb AND
						husbbirth.d_fact = 'BIRT' AND
						wifebirth.d_gid = fam.f_wife AND
						wifebirth.d_fact = 'BIRT' AND
						husbbirth.d_julianday1 <> 0 AND
						wifebirth.d_year-husbbirth.d_year > ?
					 GROUP BY xref
					 ORDER BY age DESC
				";
				$rows		= KT_DB::prepare($sql)->execute(array($gedID, $gedID, $gedID, $age))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
			case ('CHIL_1'):
				$sql = "
					SELECT
					 parentfamily.l_to AS xref,
					 childfamily.l_to AS xref2,
					 MIN(childbirth.d_julianday2)-MIN(birth.d_julianday1) AS age,
					 MIN(birth.d_year) as dob
					 FROM `##link` AS parentfamily
					 JOIN `##link` AS childfamily ON childfamily.l_file = ?
					 JOIN `##dates` AS birth ON birth.d_file = ?
					 JOIN `##dates` AS childbirth ON childbirth.d_file = ?
					 WHERE
						birth.d_gid = parentfamily.l_to AND
						childfamily.l_to = childbirth.d_gid AND
						childfamily.l_type = 'CHIL' AND
						parentfamily.l_type = 'WIFE' AND
						childfamily.l_from = parentfamily.l_from AND
						parentfamily.l_file = ? AND
						birth.d_fact = 'BIRT' AND
						childbirth.d_fact = 'BIRT' AND
						birth.d_julianday1 <> 0 AND
						childbirth.d_julianday2-birth.d_julianday1 < ?
					GROUP BY xref, xref2
					ORDER BY age ASC
				";
				$rows		= KT_DB::prepare($sql)->execute(array($gedID, $gedID, $gedID, $gedID, ($age * 365.25)))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
			case ('CHIL_2'):
				$sql = "
					SELECT
					 parentfamily.l_to AS xref,
					 childfamily.l_to AS xref2,
					 MIN(childbirth.d_julianday2)-MIN(birth.d_julianday1) AS age,
					 MIN(birth.d_year) as dob
					 FROM `##link` AS parentfamily
					 JOIN `##link` AS childfamily ON childfamily.l_file = ?
					 JOIN `##dates` AS birth ON birth.d_file = ?
					 JOIN `##dates` AS childbirth ON childbirth.d_file = ?
					 WHERE
						birth.d_gid = parentfamily.l_to AND
						childfamily.l_to = childbirth.d_gid AND
						childfamily.l_type = 'CHIL' AND
						parentfamily.l_type = 'WIFE' AND
						childfamily.l_from = parentfamily.l_from AND
						parentfamily.l_file = ? AND
						birth.d_fact = 'BIRT' AND
						childbirth.d_fact = 'BIRT' AND
						birth.d_julianday1 <> 0 AND
						childbirth.d_julianday2-birth.d_julianday1 > ?
					GROUP BY xref, xref2
					ORDER BY age ASC
				";
				$rows		= KT_DB::prepare($sql)->execute(array($gedID, $gedID, $gedID, $gedID, ($age * 365.25)))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
			default:
				$sql = "
					SELECT
					 tag.d_gid AS xref,
					 birth.d_year AS birtyear,
					 tag.d_year - birth.d_year AS age
					 FROM
						 `##dates` AS tag,
						 `##dates` AS birth
					 WHERE
						 birth.d_gid = tag.d_gid AND
						 tag.d_file = ? AND
						 birth.d_file = tag.d_file AND
						 birth.d_fact = 'BIRT' AND
						 tag.d_fact = ? AND
						 birth.d_julianday1 <> 0 AND
						 tag.d_julianday1 > birth.d_julianday2 AND
						 tag.d_year-birth.d_year > ?
					 GROUP BY xref, birtyear, tag.d_year
					 ORDER BY age DESC
				";
				$rows		= KT_DB::prepare($sql)->execute(array($gedID, $tag_array[$i], $age))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
		}
		$link_url = $link_name = $result = false;

		foreach ($rows as $row) {
			switch ($result_tag) {
				case 'DEAT';
					$person = KT_Person::getInstance($row->xref);
					if ($person && !$person->getAllDeathDates()) {
						$link_url	= $person->getHtmlUrl();
						$link_name	= $person->getFullName();
						$result1	= $row->birthyear;
						$result2	= $row->age;
						$result3	= false;
		}
					break;
				case 'MARR';
					$person = KT_Person::getInstance($row->xref);
					if ($person) {
						$link_url	= $person->getHtmlUrl();
						$link_name	= $person->getFullName();
						$result1	= $row->marryear;
						$result2	= $row->age;
						$result3	= false;
					}
					break;
				case 'FAMS';
					$family = KT_Family::getInstance($row->xref);
					if ($family) {
						$link_url	= $family->getHtmlUrl();
						$link_name	= $family->getFullName();
						$result1	= $row->age;
						$result2	= false;
						$result3	= false;
					}
					break;
				case 'CHIL_1';
					$person = KT_Person::getInstance($row->xref);
					$person2 = KT_Person::getInstance($row->xref2);
					if ($person && $person2) {
						$link_url	= $person->getHtmlUrl();
						$link_url2	= $person2->getHtmlUrl();
						$link_name	= $person->getFullName();
						$link_name2	= $person2->getFullName();
						$child		= '<a href="' . $link_url2. '" target="_blank" rel="noopener noreferrer">' . $link_name2 . '</a>';
						$result1	= (int)($row->age / 365.25);
						$result2	= $child;
						$result3	= $row->dob;
					}
					break;
				case 'CHIL_2';
					$person = KT_Person::getInstance($row->xref);
					$person2 = KT_Person::getInstance($row->xref2);
					if ($person && $person2) {
						$link_url	= $person->getHtmlUrl();
						$link_url2	= $person2->getHtmlUrl();
						$link_name	= $person->getFullName();
						$link_name2	= $person2->getFullName();
						$child		= '<a href="' . $link_url2. '" target="_blank" rel="noopener noreferrer">' . $link_name2 . '</a>';
						$result1	= (int)($row->age / 365.25);
						$result2	= $child;
						$result3	= $row->dob;
					}
					break;
				case 'BAPM';
					$person = KT_Person::getInstance($row->xref);
					if ($person) {
						$link_url	= $person->getHtmlUrl();
						$link_name	= $person->getFullName();
						$result1	= $row->birtyear;
						$result2	= $row->age;
						$result3	= false;
					}
					break;
				case 'CHR';
					$person = KT_Person::getInstance($row->xref);
					if ($person) {
						$link_url	= $person->getHtmlUrl();
						$link_name	= $person->getFullName();
						$result1	= $row->birtyear;
						$result2	= $row->age;
						$result3	= false;
					}
					break;
			}
			if ($link_url && $link_name) {
				$html .= '
					<tr>
						<td><a href="' . $link_url. '" target="_blank" rel="noopener noreferrer">' . $link_name. '</a></td>
						<td>' . $result1 . '</td>';
						$result2 ? $html .= '<td>' . $result2 . '</td>' : '<td></td>';
						$result3 ? $html .= '<td>' . $result3 . '</td>' : '<td></td>';
					$html .= '</tr>';
				$count ++;
			}
		}

		$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	}
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function duplicate_tag($gedID, $tag) {
	$html	= '';
	$count	= 0;
	$start	= microtime(true);
	$sql1	= "
				SELECT i_id AS xref
				FROM `##individuals`
				WHERE `i_file`= ?
				AND (
					(`i_gedcom` LIKE BINARY CONCAT('%1 ', 'BAPM','%1 ', 'BAPM', '%')) OR
					(`i_gedcom` LIKE BINARY CONCAT('%1 ', 'BAPM','%1 ', 'CHR', '%')) OR
					(`i_gedcom` LIKE BINARY CONCAT('%1 ', 'CHR','%1 ', 'CHR', '%')) OR
					(`i_gedcom` LIKE BINARY CONCAT('%1 ', 'CHR','%1 ', 'BAPM', '%'))
				)
			 ";
	 $sql2	= "
 				SELECT i_id AS xref
				FROM `##individuals`
				WHERE `i_file`= ?
				AND `i_gedcom` LIKE BINARY CONCAT('%1 ', ?,'%1 ', ?, '%')
 			 ";

	switch ($tag) {
		case 'BAPM' :
		case 'CHR' :
			$rows = KT_DB::prepare($sql1)->execute(array($gedID))->fetchAll();
		break;
		default :
			$rows = KT_DB::prepare($sql2)->execute(array($gedID, $tag, $tag))->fetchAll();
	}

	foreach ($rows as $row) {
		$person	= KT_Person::getInstance($row->xref);
		$html	.= '
			<tr>
				<td>
					<a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a>
				</td>
			</tr>
		';
		$count	++;
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);

	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function identical_name($gedID) {
	$html	= '';
	$count	= 0;
	$start	= microtime(true);
	$sql 	= "
				SELECT n_id AS xref, COUNT(*) as count
				FROM `##name`
				WHERE `n_file`= ?
				AND `n_type`= 'NAME'
				GROUP BY `n_id`, `n_sort`
				HAVING COUNT(*) > 1
			  ";
	$rows	= KT_DB::prepare($sql)->execute(array($gedID))->fetchAll();

	foreach ($rows as $row) {
		$person	= KT_Person::getInstance($row->xref);
		$html	.= '
			<tr>
				<td>
					<a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a>
				</td>
			</tr>
		';
		$count	++;
	}

	$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function duplicate_famtag($gedID, $tag) {
	$html	= '';
	$count	= 0;
	$start	= microtime(true);
	$sql 	= "
				SELECT f_id AS xref
				FROM `##families`
				WHERE `f_file`= ?
				AND `f_gedcom` LIKE BINARY CONCAT('%1 ', ?,'%1 ', ?, '%')
			  ";

	$rows	= KT_DB::prepare($sql)->execute(array($gedID, $tag, $tag))->fetchAll();

	foreach ($rows as $row) {
		$family	= KT_Family::getInstance($row->xref);
		$html	.= '
			<tr>
				<td>
					<a href="' . $family->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $family->getFullName() . '</a>
				</td>
			</tr>
		';
		$count	++;
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);

	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function duplicate_child($gedID) {
	$html	= '';
	$count	= 0;
	$start	= microtime(true);
	$sql 	= "
				SELECT f_id AS xref
				FROM `##families`
				WHERE `f_file`= ?
				AND ROUND((LENGTH(`f_gedcom`) - LENGTH(REPLACE(`f_gedcom`, '1 CHIL @', '')))/LENGTH('1 CHIL @')) > 1
			  ";

	$rows	= KT_DB::prepare($sql)->execute(array($gedID))->fetchAll();

	foreach ($rows as $row) {
		$names = array();
		$new_children = array();
		$family	= KT_Family::getInstance($row->xref);
		$children = $family->getChildren();
		foreach ($children as $child) {
			$names[]							= $child->getFullName();
			$new_children[$child->getXref()]	= $child->getFullName();
		}
		asort($new_children);
		if (count(array_unique($names)) < count($names)) {
			$single_names = array_diff($names, array_diff_assoc($names, array_unique($names)));
			$html .= '
				<tr>
					<td>
						<a href="' . $family->getHtmlUrl() . '" target="_blank" rel="noopener noreferrer">' . $family->getFullName() . '</a>
					</td>
					<td>';
						foreach ($new_children as $xref => $name) {
							if (!in_array($name, $single_names)) {
								$person	= KT_Person::getInstance($xref);
								$html	.= '<p>' . $person->getSexImage('small') . ' - ' . $person->getLifespanName() . '</p>';
							}
						}
					$html .= '</td>
				</tr>
			';
			$count	++;
		}
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);

	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function missing_vital($gedID, $tag, $DateTag, $PlacTag, $SourTag) {
	$html		= '';
	$count		= 0;
	$start		= microtime(true);
	$gedrec 	= '';
	$subTags	= trim($DateTag . '|' . $PlacTag . '|' . $SourTag, "|");
	$subTags	= str_replace("||", "|", $subTags);
	$results 	= array();
	$sql1 		= "
					SELECT i_id AS xref, i_gedcom AS gedrec
					FROM `##individuals`
					WHERE `i_file` = ?
					AND `i_gedcom` NOT REGEXP '\n1 " . $tag . "'
				  ";
	$sql2 		= "
					SELECT i_id AS xref, i_gedcom AS gedrec
					FROM `##individuals`
					WHERE `i_file` = ?
					AND `i_gedcom` REGEXP '\n1 " . $tag . " Y\n1'
				  ";
	$sql3 		= "
					SELECT i_id AS xref, i_gedcom AS gedrec
					FROM `##individuals`
					WHERE `i_file` = ?
					AND `i_gedcom` REGEXP '\n1 " . $tag . "'
				  ";

	// no <<$tag>> record at all
	$rows1 = KT_DB::prepare($sql1)->execute(array($gedID))->fetchAll();

	foreach ($rows1 as $row) {
		$person = KT_Person::getInstance($row->xref);
		if ($tag != 'DEAT' || ($tag == 'DEAT' && $person->isDead())) {
			$results[] = array(
				'HtmlUrl'	=> $person->getHtmlUrl(),
				'FullName'	=> $person->getFullName(),
				'gedrec'	=> KT_I18N::translate('No %s data', KT_Gedcom_Tag::getLabel($tag))
			);
		}
	}

	// <<$tag>> record with only <<Y>>
	$rows2 = KT_DB::prepare($sql2)->execute(array($gedID))->fetchAll();

	foreach ($rows2 as $row) {
		$person = KT_Person::getInstance($row->xref);
		if ($tag != 'DEAT' || ($tag == 'DEAT' && $person->isDead())) {
			$results[] = array(
				'HtmlUrl'	=> $person->getHtmlUrl(),
				'FullName'	=> $person->getFullName(),
				'gedrec'	=> '1 ' . $tag . ' Y'
			);
		}
	}

	// <<$tag>> record with or without <<Y>> but without any of the sub-tags specified
	$rows3 = KT_DB::prepare($sql3)->execute(array($gedID))->fetchAll();

	foreach ($rows3 as $row) {
		preg_match('/\n(1 ' . $tag . '.*\n([2-9] (?!' . $subTags . ').*\n)+)1/i', $row->gedrec, $match2);
		if ($match2) {
			$person = KT_Person::getInstance($row->xref);
			if ($tag != 'DEAT' || ($tag == 'DEAT' && $person->isDead())) {
				$results[] = array(
					'HtmlUrl'	=> $person->getHtmlUrl(),
					'FullName'	=> $person->getFullName(),
					'gedrec'	=> $match2[1]
				);
			}
		}
	}

	asort($results);

	foreach ($results as $result) {
		$html 	.= '
			<tr>
				<td><a href="' . $result['HtmlUrl'] . '" target="_blank" rel="noopener noreferrer">' . $result['FullName'] . '</a></td>
				<td><pre>' . $result['gedrec'] . '</pre></td>
			</tr>
		';
		$count	++;
	}

	$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function missing_tag($gedID, $tag) {
	$html	= '';
	$count	= 0;
	$start	= microtime(true);
	$sql 	= "
				SELECT i_id AS xref, i_gedcom AS gedrec
				FROM `##individuals`
				WHERE `i_file` = ?
				AND `i_gedcom`
				NOT REGEXP CONCAT('\n[0-9] ' , ?)
			  ";

	$rows	= KT_DB::prepare($sql)->execute(array($gedID, $tag))->fetchAll();

	foreach ($rows as $row) {
		$person = KT_Person::getInstance($row->xref);
		$html 	.= '
			<tr>
				<td>
					<a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a>
				</td>
			</tr';
		$count	++;
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);

	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function invalid_age($gedID) {
	$html		= '';
	$count		= 0;
	$start		= microtime(true);
	$sql1 	= "
				SELECT i_id AS xref, i_gedcom AS gedrec
				FROM `##individuals`
				WHERE `i_file` = ?
				AND `i_gedcom` REGEXP CONCAT('[0-9] ', 'AGE') COLLATE utf8_bin
				AND `i_gedcom` NOT REGEXP CONCAT('[0-9] ', 'AGE', ' [0-9]{1,3}[a-z]{1}') COLLATE utf8_bin
			  ";
	$sql2 	= "
				SELECT f_id AS xref, f_gedcom AS gedrec
				FROM `##families`
				WHERE `f_file` = ?
				AND BINARY `f_gedcom` REGEXP CONCAT('\n[0-9] ', 'AGE') COLLATE utf8_bin
				AND BINARY `f_gedcom` NOT REGEXP CONCAT('[0-9] ', 'AGE', ' [0-9]{1,3}[a-z]{1}') COLLATE utf8_bin
			  ";

	// Individuals
	$rows	= KT_DB::prepare($sql1)->execute(array($gedID))->fetchAll();

	foreach ($rows as $row) {
		$gedrec	= '';
		$person = KT_Person::getInstance($row->xref);
		if (preg_match('/\n\d AGE.*\n/i', $row->gedrec, $match)) {
			$gedrec = $match[0];
		}
		$html 	.= '
			<tr>
				<td>
					<a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a>
				</td>
				<td>
					' . $gedrec . '
				</td>
			</tr>';
		$count	++;
	}
	// Families (HUSB, WIFE)
 	$rows	= KT_DB::prepare($sql2)->execute(array($gedID))->fetchAll();

	foreach ($rows as $row) {
		$gedrec	= '';
		$family = KT_Family::getInstance($row->xref);
		if (preg_match_all('/\n\d AGE.*/', $row->gedrec, $match)) {
			foreach ($match[0] as $value) {
				$gedrec .= '<p>' . $value . '</p>';
			}
		}
		$html 	.= '
			<tr>
				<td>
					<a href="' . $family->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $family->getFullName() . '</a>
				</td>
				<td>
					' . $gedrec . '
				</td>
			</tr>';
		$count	++;
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);

	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function empty_tag($gedID) {
	global $emptyfacts;

	$html			= '<ul>';
	$count			= 0;
	$start			= microtime(true);
	$person_list	= array();
	$sql 			= "
						SELECT i_id AS xref
						FROM `##individuals`
						WHERE `i_file` = ?
					  ";

	$rows			= KT_DB::prepare($sql)->execute(array($gedID))->fetchAll();

	foreach ($rows as $row) {
		$person		= KT_Person::getInstance($row->xref);
		$indifacts	= $person->getIndiFacts();
		$tag_list	= array();
		foreach ($indifacts as $key=>$value) {
			$fact	= $value->getDetail();
			$tag	= $value->getTag();
			if (!in_array($tag, $emptyfacts) && $fact == '') {
				$tag_list[] = $tag;
				$tag_count = array_count_values($tag_list)[$tag];
				if (!in_array($person->getXref(), $person_list)) {
					$count	++;
					$person_list[] = $person->getXref();
					$html .= '
						</tr>
							<td>
								<a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a>
							</td>
					';
				}
				$html .= '<td>';
					if ($tag_count == 1) {
						$html .= '<span>' . KT_I18N::translate('One or more empty %s tags ', $tag) . '</span>';
					}
				$html .= '</td>';
				$html .= '</tr>';

			}
		}
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);

	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function child_order($gedID) {
	$html	= '';
	$count	= 0;
	$start	= microtime(true);
	$dates	= array();
	$sql 	= "
				SELECT f_id AS xref, f_gedcom AS gedrec
				FROM `##families`
				WHERE `f_file` = ?
				AND `f_numchil` > 1
			  ";

	// Families
 	$rows	= KT_DB::prepare($sql)->execute(array($gedID))->fetchAll();

	foreach ($rows as $row) {
		$family			= KT_Family::getInstance($row->xref);
		$children		= $family->getChildren();
		$dates_original	= array();
		$dates_sorted	= array();
		$gedName		= KT_Tree::getNameFromId($gedID);

		foreach ($children as $child) {
			$bdate = $child->getBirthDate();
			if ($bdate->isOK()) {
				$date = $bdate->MinJD();
			} else {
				$date = 1e8; // birth date missing => sort last
			}
			$dates_original[]	= $date;
			$dates_sorted[]		= $date;
		}
		asort($dates_sorted);

		if ($dates_original !== $dates_sorted) {
			$html .= '
				<tr>
					<td>
						<a href="' . $family->getHtmlUrl() . '" target="_blank" rel="noopener noreferrer">' . $family->getFullName() . '</a>
					</td>
					<td>
						<a href="edit_interface.php?action=reorder_children&pid=' . $family->getXref() . '&amp;ged=' . $gedName . '" target="_blank">' . KT_I18N::translate('Click to update order') . '</a>
					</td>
				</tr>
			';
			$count ++;
		}
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);

	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function fam_order($gedID) {
	$html	= '';
	$count	= 0;
	$start	= microtime(true);
	$dates	= array();
	$sql 	= "
				SELECT i_id AS xref, i_gedcom AS gedrec
				FROM `##individuals`
				WHERE `i_file` = ?
				AND `i_gedcom` LIKE '%1 FAMS @%'
			  ";

	// Individuals with FAMS records
	$rows	= KT_DB::prepare($sql)->execute(array($gedID))->fetchAll();

	foreach ($rows as $row) {
		$person = KT_Person::getInstance($row->xref);

		if (count($person->getSpouseFamilies()) > 1) {
			$dates_original	= array();
			$dates_sorted	= array();

			foreach ($person->getSpouseFamilies() as $family) {
				$mdate	= $family->getMarriageDate();
				if ($mdate->isOK()) {
					$date = $mdate->MinJD();
				} else {
					$date = 1e8; // birth date missing => sort last
				}
				$dates_original[]	= $date;
				$dates_sorted[]		= $date;
			}
			sort($dates_sorted);

			if ($dates_original !== $dates_sorted) {
				$html .= '
					<tr>
						<td>
							<a href="individual.php?pid=' . $person->getXref(). '&amp;ged=' . KT_GEDCOM . '#tabi_families" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a>
						</td>
						<td>
							<a href="edit_interface.php?action=reorder_fams&pid=' . $person->getXref() . '&amp;ged=' . KT_GEDCOM . '" target="_blank">' . KT_I18N::translate('Click to update order') . '</a>
						</td>
					</tr>
				';
				$count ++;
			}
		}
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);

	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}
