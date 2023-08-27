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

function getGmPlaceId($place) {
	$par		= explode (",", strip_tags($place));
	$par		= array_reverse($par);
	$place_id	= 0;
	for ($i = 0; $i < count($par); $i ++) {
		$par[$i] = trim($par[$i]);
		if (empty($par[$i])) $par[$i] = "unknown";
		$placelist = create_possible_place_names($par[$i], $i+1);
		foreach ($placelist as $key => $placename) {
			$pl_id =
				KT_DB::prepare("
					SELECT pl_id
					FROM `##placelocation`
					WHERE pl_level=?
					AND pl_parent_id=?
					AND pl_place LIKE ?
					ORDER BY pl_place
				")
				->execute(array($i, $place_id, $placename))
				->fetchOne();
			if (!empty($pl_id)) break;
		}
		if (empty($pl_id)) break;
		$place_id = $pl_id;
	}
	return $place_id;
}

// functions copied from print_fact_place
function print_fact_place_map($factrec) {
	$ct = preg_match("/2 PLAC (.*)/", $factrec, $match);
	if ($ct > 0) {
		$retStr	= ' ';
		$levels	= explode(',', $match[1]);
		$place	= trim($match[1]);
		// reverse the array so that we get the top level first
		$levels	= array_reverse($levels);
		$retStr	.= '<a href="module.php?mod=list_places&mod_action=show';
		foreach ($levels as $pindex=>$ppart) {
			// routine for replacing ampersands
			$ppart	= preg_replace("/amp\%3B/", "", trim($ppart));
			$retStr	.= "&amp;parent[$pindex]=" . $ppart;
		}
		$retStr .= '"> ' . htmlspecialchars((string) $place) . '</a>';
		return $retStr;
	}
	return '';
}

function abbreviate($text) {
	if (utf8_strlen($text)>13) {
		if (trim(utf8_substr($text, 10, 1))!='') {
			$desc = utf8_substr($text, 0, 11).'.';
		} else {
			$desc = trim(utf8_substr($text, 0, 11));
		}
	}
	else $desc = $text;
	return $desc;
}

function get_lati_long_placelocation ($place) {
	$parent = explode (',', $place);
	$parent = array_reverse($parent);
	$place_id = 0;

	for ($i=0; $i<count($parent); $i++) {
		$parent[$i] = trim($parent[$i]);
		if (empty($parent[$i])) $parent[$i]='unknown';// GoogleMap module uses "unknown" while GEDCOM uses , ,
		$placelist = create_possible_place_names($parent[$i], $i+1);
		foreach ($placelist as $key => $placename) {
			$pl_id=
				KT_DB::prepare("SELECT pl_id FROM `##placelocation` WHERE pl_level=? AND pl_parent_id=? AND pl_place LIKE ? ORDER BY pl_place")
				->execute(array($i, $place_id, $placename))
				->fetchOne();
			if (!empty($pl_id)) break;
		}
		if (empty($pl_id)) break;
		$place_id = $pl_id;
	}

	$row = KT_DB::prepare("SELECT pl_lati, pl_long, pl_zoom, pl_icon, pl_level FROM `##placelocation` WHERE pl_id=? ORDER BY pl_place")
		->execute(array($place_id))
		->fetchOneRow();
	if ($row) {
		return array('lati'=>$row->pl_lati, 'long'=>$row->pl_long, 'zoom'=>$row->pl_zoom, 'icon'=>$row->pl_icon, 'level'=>$row->pl_level);
	} else {
		return array();
	}
}

function create_possible_place_names ($placename, $level) {
	global $GM_PREFIX, $GM_POSTFIX;

	$retlist = array();
	if ($level<=9) {
		$retlist = rem_prefix_postfix_from_placename($GM_PREFIX[$level], $GM_POSTFIX[$level], $placename, $retlist); // Remove both
		$retlist = rem_prefix_from_placename($GM_PREFIX[$level], $placename, $retlist); // Remove prefix
		$retlist = rem_postfix_from_placename($GM_POSTFIX[$level], $placename, $retlist); // Remove suffix
	}
	$retlist[]=$placename; // Exact

	return $retlist;
}

function rem_prefix_from_placename($prefix_list, $place, $placelist) {
	if ($prefix_list) {
		foreach (explode(';', $prefix_list) as $prefix) {
			if ($prefix && substr($place, 0, strlen($prefix)+1)==$prefix.' ') {
				$placelist[] = substr($place, strlen($prefix)+1);
			}
		}
	}
	return $placelist;
}

function rem_postfix_from_placename($postfix_list, $place, $placelist) {
	if ($postfix_list) {
		foreach (explode (';', $postfix_list) as $postfix) {
			if ($postfix && substr($place, -strlen($postfix)-1)==' '.$postfix) {
				$placelist[] = substr($place, 0, strlen($place)-strlen($postfix)-1);
			}
		}
	}
	return $placelist;
}

function rem_prefix_postfix_from_placename($prefix_list, $postfix_list, $place, $placelist) {
	if ($prefix_list && $postfix_list) {
		foreach (explode (";", $prefix_list) as $prefix) {
			foreach (explode (";", $postfix_list) as $postfix) {
				if ($prefix && $postfix && substr($place, 0, strlen($prefix)+1)==$prefix.' ' && substr($place, -strlen($postfix)-1)==' '.$postfix) {
					$placelist[] = substr($place, strlen($prefix)+1, strlen($place)-strlen($prefix)-strlen($postfix)-2);
				}
			}
		}
	}
	return $placelist;
}

