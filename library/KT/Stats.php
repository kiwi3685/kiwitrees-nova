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

require_once KT_ROOT . 'includes/functions/functions_print_lists.php';

class KT_Stats
{
	private $_gedcom;
	private $_gedcom_url;
	private $_ged_id;

	// Methods not allowed to be used as embedded statistics
	private static $_not_allowed = ['stats', 'getTags', 'embedTags', 'iso3166', 'get_all_countries'];
	private static $_media_types = ['audio', 'book', 'card', 'certificate', 'coat', 'document', 'electronic', 'magazine', 'manuscript', 'map', 'fiche', 'film', 'newspaper', 'painting', 'photo', 'tombstone', 'video', 'other'];

	public function __construct($gedcom)
	{
		$this->_gedcom = $gedcom;
		$this->_ged_id = get_id_from_gedcom($gedcom);
		$this->_gedcom_url = rawurlencode((string) $gedcom);
	}

	/**
	 * Return a string of all supported tags and an example of its output in table row form.
	 */
	public function getAllTagsTable()
	{
		$examples = [];
		foreach (get_class_methods($this) as $method) {
			if (in_array($method, self::$_not_allowed) || '_' == $method[0] || 'getAllTagsTable' == $method || 'getAllTagsText' == $method) {
				continue;
			}
			$examples[$method] = $this->{$method}();
			if (stristr($method, 'highlight')) {
				$examples[$method] = str_replace([' align="left"', ' align="right"'], '', $examples[$method]);
			}
		}
		ksort($examples);

		$html = '';
		foreach ($examples as $tag => $value) {
			$html .= '<tr>';
			$html .= '<td class="list_value_wrap">' . $tag . '</td>';
			$html .= '<td class="list_value_wrap">' . $value . '</td>';
			$html .= '</tr>';
		}

		return
			'<table id="keywords"><thead>' .
			'<tr>' .
			'<th class="list_label_wrap">' . KT_I18N::translate('Embedded variable') . '</th>' .
			'<th class="list_label_wrap">' . KT_I18N::translate('Resulting value') . '</th>' .
			'</tr>' .
			'</thead><tbody>' .
			$html .
			'</tbody></table>';
	}

	/**
	 * Return a string of all supported tags in plain text.
	 */
	public function getAllTagsText()
	{
		$examples = [];
		foreach (get_class_methods($this) as $method) {
			if (in_array($method, self::$_not_allowed) || '_' == $method[0] || 'getAllTagsTable' == $method || 'getAllTagsText' == $method) {
				continue;
			}
			$examples[$method] = $method;
		}
		ksort($examples);

		return implode('<br>', $examples);
	}

	/**
	 * Embed tags in text.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function embedTags($text)
	{
		if (false !== strpos($text, '#')) {
			[$new_tags, $new_values] = $this->getTags($text);
			$text = str_replace($new_tags, $new_values, $text);
		}

		return $text;
	}

// /////////////////////////////////////////////////////////////////////////////
// RELATIONSHIPS                                                             //
// /////////////////////////////////////////////////////////////////////////////

	/**
	 * Embed a relationship statement in text in the format "xxx is your yyy"
	 * between a logged in user and any individual added in parameter.
	 *
	 * @param string str 'I1234'
	 * @param mixed $params
	 *
	 * @return string str without html tags
	 */
	public function RelaToMe($params = [])
	{
		if (!empty($params[0]) && array_key_exists('chart_relationship', KT_Module::getActiveModules()) && KT_USER_ID && get_gedcom_setting(KT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI') > 0) {
			require_once KT_ROOT . 'includes/functions/functions_print_relations.php';
			$person1 = KT_Person::getInstance(KT_USER_GEDCOM_ID);
			$person2 = KT_Person::getInstance($params[0]);

			return printSlcasBetween($person1, $person2, 7, 99, 1, 'INDI', 'html');
		}
		$person = KT_Person::getInstance($params[0]);

		return KT_I18N::translate('Your are not closely related to %1$s .', $person->getFullName());
	}

// GEDCOM                                                                    //
// /////////////////////////////////////////////////////////////////////////////

	public function gedcomFilename()
	{
		return get_gedcom_from_id($this->_ged_id);
	}

	public function gedcomID()
	{
		return $this->_ged_id;
	}

	public function gedcomTitle()
	{
		$trees = KT_Tree::getAll();

		return $trees[$this->_ged_id]->tree_title_html;
	}

	public function _gedcomHead()
	{
		$title = '';
		$version = '';
		$source = '';
		static $cache = null;
		if (is_array($cache)) {
			return $cache;
		}
		$head = find_other_record('HEAD', $this->_ged_id);
		$ct = preg_match('/1 SOUR (.*)/', $head, $match);
		if ($ct > 0) {
			$softrec = get_sub_record(1, '1 SOUR', $head);
			$tt = preg_match('/2 NAME (.*)/', $softrec, $tmatch);
			if ($tt > 0) {
				$title = trim($tmatch[1]);
			} else {
				$title = trim($match[1]);
			}
			if (!empty($title)) {
				$tt = preg_match('/2 VERS (.*)/', $softrec, $tmatch);
				if ($tt > 0) {
					$version = trim($tmatch[1]);
				} else {
					$version = '';
				}
			} else {
				$version = '';
			}
			$tt = preg_match('/1 SOUR (.*)/', $softrec, $tmatch);
			if ($tt > 0) {
				$source = trim($tmatch[1]);
			} else {
				$source = trim($match[1]);
			}
		}
		$cache = [$title, $version, $source];

		return $cache;
	}

	public function gedcomCreatedSoftware()
	{
		$head = self::_gedcomHead();

		return $head[0];
	}

	public function gedcomCreatedVersion()
	{
		$head = self::_gedcomHead();
		// fix broken version string in Family Tree Maker
		if (strstr($head[1], 'Family Tree Maker ')) {
			$p = strpos($head[1], '(') + 1;
			$p2 = strpos($head[1], ')');
			$head[1] = substr($head[1], $p, $p2 - $p);
		}
		// Fix EasyTree version
		if ('EasyTree' == $head[2]) {
			$head[1] = substr($head[1], 1);
		}

		return $head[1];
	}

	public function gedcomDate()
	{
		global $DATE_FORMAT;

		$head = find_other_record('HEAD', $this->_ged_id);
		if (preg_match('/1 DATE (.+)/', $head, $match)) {
			$date = new KT_Date($match[1]);

			return $date->Display(false, $DATE_FORMAT); // Override $PUBLIC_DATE_FORMAT
		}

		return '';
	}

	public function gedcomUpdated()
	{
		$row =
			KT_DB::prepare('SELECT d_year, d_month, d_day FROM `##dates` WHERE d_julianday1 = ( SELECT max( d_julianday1 ) FROM `##dates` WHERE d_file =? AND d_fact=? ) LIMIT 1')
				->execute([$this->_ged_id, 'CHAN'])
				->fetchOneRow()
			;
		if ($row) {
			$date = new KT_Date("{$row->d_day} {$row->d_month} {$row->d_year}");

			return $date->Display(false);
		}

		return self::gedcomDate();
	}

	public function gedcomRootID()
	{
		$root = KT_Person::getInstance(get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID'));

		return substr($root, 0, stripos($root, '@'));
	}

	public function gedcomRootIDname()
	{
		$root = KT_Person::getInstance(get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID'));

		return $root->getFullName();
	}

// /////////////////////////////////////////////////////////////////////////////
// Totals                                                                    //
// /////////////////////////////////////////////////////////////////////////////

	public function _getPercentage($total, $type)
	{
		switch ($type) {
			case 'individual':
				$type = $this->_totalIndividuals();

				break;

			case 'family':
				$type = $this->_totalFamilies();

				break;

			case 'source':
				$type = $this->_totalSources();

				break;

			case 'note':
				$type = $this->_totalNotes();

				break;

			case 'all':
			default:
				$type = $this->_totalIndividuals() + $this->_totalFamilies() + $this->_totalSources();
		}
		if (0 == $type) {
			return KT_I18N::percentage(0, 1);
		}

		return KT_I18N::percentage($total / $type, 1);
	}

	public function totalRecords()
	{
		return KT_I18N::number($this->_totalIndividuals() + $this->_totalFamilies() + $this->_totalSources());
	}

	public function _totalIndividuals()
	{
		return
			KT_DB::prepare('SELECT COUNT(*) FROM `##individuals` WHERE i_file=?')
				->execute([$this->_ged_id])
				->fetchOne()
			;
	}

	public function totalIndividuals()
	{
		return KT_I18N::number($this->_totalIndividuals());
	}

	public function _totalIndisWithSources()
	{
		$rows = self::_runSQL('SELECT COUNT(DISTINCT i_id) AS tot FROM `##link`, `##individuals` WHERE i_id=l_from AND i_file=l_file AND l_file=' . $this->_ged_id . " AND l_type='SOUR'");

		return $rows[0]['tot'];
	}

	public function totalIndisWithSources()
	{
		return KT_I18N::number(self::_totalIndisWithSources());
	}

	public function totalIndisWithSourcesPercentage()
	{
		return KT_I18N::percentage(round(self::_totalIndisWithSources() / self::_totalIndividuals(), 1));
	}

	public function _totalIndisWithoutSources()
	{
		return self::_totalIndividuals() - self::_totalIndisWithSources();
	}

	public function totalIndisWithoutSources()
	{
		return KT_I18N::number(self::_totalIndisWithoutSources());
	}

	public function totalIndisWithoutSourcesPercentage()
	{
		return KT_I18N::percentage(round(self::_totalIndisWithoutSources() / self::_totalIndividuals(), 1));
	}

	public function chartIndisWithSources()
	{
		$tot = $this->_totalIndividuals();

		if (0 == $tot) {
			return '';
		}
		$data = [
			[
				'category' => KT_I18N::translate('With sources'),
				'count' => $this->_totalIndisWithSources(),
				'percent' => $this->totalIndisWithSourcesPercentage(),
				'color' => 'l',
			],
			[
				'category' => KT_I18N::translate('Without sources'),
				'count' => $this->_totalIndisWithoutSources(),
				'percent' => $this->totalIndisWithoutSourcesPercentage(),
				'color' => 'd',
			],
		];

		return json_encode($data);
	}

	public function totalIndividualsPercentage()
	{
		return $this->_getPercentage($this->_totalIndividuals(), 'all');
	}

	public function _totalFamilies()
	{
		return
			KT_DB::prepare('SELECT COUNT(*) FROM `##families` WHERE f_file=?')
				->execute([$this->_ged_id])
				->fetchOne()
			;
	}

	public function totalFamilies()
	{
		return KT_I18N::number($this->_totalFamilies());
	}

	public function _totalFamsWithSources()
	{
		$rows = self::_runSQL('SELECT COUNT(DISTINCT f_id) AS tot FROM `##link`, `##families` WHERE f_id=l_from AND f_file=l_file AND l_file=' . $this->_ged_id . " AND l_type='SOUR'");

		return $rows[0]['tot'];
	}

	public function totalFamsWithSources()
	{
		return KT_I18N::number(self::_totalFamsWithSources());
	}

	public function totalFamsWithSourcesPercentage()
	{
		return KT_I18N::percentage(round(self::_totalFamsWithSources() / self::_totalFamilies(), 1));
	}

	public function _totalFamsWithoutSources()
	{
		return self::_totalFamilies() - self::_totalFamsWithSources();
	}

	public function totalFamsWithoutSources()
	{
		return KT_I18N::number(self::_totalFamsWithoutSources());
	}

	public function totalFamsWithoutSourcesPercentage()
	{
		return KT_I18N::percentage(round(self::_totalFamsWithoutSources() / self::_totalFamilies(), 1));
	}

	public function chartFamsWithSources()
	{
		$tot = $this->_totalFamilies();

		if (0 == $tot) {
			return '';
		}
		$data = [
			[
				'category' => KT_I18N::translate('With sources'),
				'count' => $this->_totalFamsWithSources(),
				'percent' => $this->totalFamsWithSourcesPercentage(),
				'color' => 'l',
			],
			[
				'category' => KT_I18N::translate('Without sources'),
				'count' => $this->_totalFamsWithoutSources(),
				'percent' => $this->totalFamsWithoutSourcesPercentage(),
				'color' => 'd',
			],
		];

		return json_encode($data);
	}

	public function totalFamiliesPercentage()
	{
		return $this->_getPercentage($this->_totalFamilies(), 'all');
	}

	public function _totalSources()
	{
		return
			KT_DB::prepare('SELECT COUNT(*) FROM `##sources` WHERE s_file=?')
				->execute([$this->_ged_id])
				->fetchOne()
			;
	}

	public function totalSources()
	{
		return KT_I18N::number($this->_totalSources());
	}

	public function totalSourcesPercentage()
	{
		return $this->_getPercentage($this->_totalSources(), 'all');
	}

	public function _totalNotes()
	{
		return
			KT_DB::prepare("SELECT COUNT(*) FROM `##other` WHERE o_type='NOTE' AND o_file=?")
				->execute([$this->_ged_id])
				->fetchOne()
			;
	}

	public function totalNotes()
	{
		return KT_I18N::number($this->_totalNotes());
	}

	public function totalNotesPercentage()
	{
		return $this->_getPercentage($this->_totalNotes(), 'all');
	}

	public function _totalRepositories()
	{
		return
			KT_DB::prepare("SELECT COUNT(*) FROM `##other` WHERE o_type='REPO' AND o_file=?")
				->execute([$this->_ged_id])
				->fetchOne()
			;
	}

	public function totalRepositories()
	{
		return KT_I18N::number($this->_totalRepositories());
	}

	public function totalRepositoriesPercentage()
	{
		return $this->_getPercentage($this->_totalRepositories(), 'all');
	}

	/**
	 * Count the surnames.
	 *
	 * @param string[] $params
	 *
	 * @return string
	 */
	public function _totalSurnames($params = [])
	{
		if ($params) {
			$opt = 'IN (' . implode(',', array_fill(0, count($params), '?')) . ')';
			$distinct = '';
		} else {
			$opt = 'IS NOT NULL';
			$distinct = 'DISTINCT';
		}
		$params[] = $this->_ged_id;

		return KT_DB::prepare(
				"SELECT COUNT({$distinct} n_surn COLLATE '" . KT_I18N::$collation . "')" .
				' FROM `##name`' .
				" WHERE n_surn COLLATE '" . KT_I18N::$collation . "' {$opt} AND n_file=?"
			)->execute(
				$params
			)->fetchOne();
	}

	public function totalSurnames()
	{
		return KT_I18N::number($this->_totalSurnames());
	}

	public function _totalGivennames($params = [])
	{
		if ($params) {
			$qs = implode(',', array_fill(0, count($params), '?'));
			$params[] = $this->_ged_id;
			$total =
				KT_DB::prepare("SELECT COUNT( n_givn) FROM `##name` WHERE n_givn IN ({$qs}) AND n_file=?")
					->execute($params)
					->fetchOne()
				;
		} else {
			$total =
				KT_DB::prepare('SELECT COUNT(DISTINCT n_givn) FROM `##name` WHERE n_givn IS NOT NULL AND n_file=?')
					->execute([$this->_ged_id])
					->fetchOne()
				;
		}

		return $total;
	}

	public function totalGivennames()
	{
		return KT_I18N::number($this->_totalGivennames());
	}

	public function totalEvents($params = [], $list = false)
	{
		$vars = [$this->_ged_id];

		$no_types = ['HEAD', 'CHAN'];
		$list ? $sql = 'SELECT d_gid AS xref' : $sql = 'SELECT COUNT(*) AS tot FROM `##dates` WHERE d_file=?';
		if ($params) {
			$types = [];
			foreach ($params as $type) {
				if ('!' == substr($type, 0, 1)) {
					$no_types[] = substr($type, 1);
				} else {
					$types[] = $type;
				}
			}
			if ($types) {
				$sql .= ' AND d_fact IN (' . implode(', ', array_fill(0, count($types), '?')) . ')';
				$vars = array_merge($vars, $types);
			}
		}
		$sql .= ' AND d_fact NOT IN (' . implode(', ', array_fill(0, count($no_types), '?')) . ')';
		$vars = array_merge($vars, $no_types);
		if ($list) {
			$rows = KT_DB::prepare($sql)->execute($vars)->fetchAll(PDO::FETCH_ASSOC);
			$list = [];
			foreach ($rows as $row) {
				$family = KT_Family::getInstance($row['xref']);
				$list[] = clone $family;
			}

			return $list;
		}

		return KT_I18N::number(KT_DB::prepare($sql)->execute($vars)->fetchOne());
	}

	public function totalBirths()
	{
		$list = [];

		$rows = KT_DB::prepare("
            SELECT DISTINCT i_id
            FROM `##individuals`
            WHERE `i_file`=?
			AND `i_gedcom` REGEXP '\n1 BIRT'
            ORDER BY i_id
        ")
			->execute([$this->_ged_id])
			->fetchAll()
		;

		foreach ($rows as $row) {
			$list[] = $row->i_id;
		}

		if (null != $list) {
			return ['list' => $list, 'count' => count($list)];
		}

		return ['list' => KT_I18N::translate('None recorded'), 'count' => 0];
	}

	public function totalDatedBirths()
	{
		$list = [];

		$rows = KT_DB::prepare("
            SELECT DISTINCT d_gid
            FROM `##dates`
            WHERE `d_file` = ?
            AND `d_year` > 0
            AND `d_fact` = 'BIRT'
            AND `d_type` IN ('@#DGREGORIAN@', '@#DJULIAN@')
            ORDER BY `d_gid`
		")
			->execute([$this->_ged_id])
			->fetchAll()
		;

		foreach ($rows as $row) {
			$list[] = $row->d_gid;
		}

		if (null != $list) {
			return ['list' => $list, 'count' => count($list)];
		}

		return ['list' => KT_I18N::translate('None recorded'), 'count' => 0];
	}

	public function totalUndatedBirths()
	{
		$total = $this->totalBirths();
		$dated = $this->totalDatedBirths();

		if (null != $total && null != $dated && !is_string($total['list']) && !is_string($dated['list'])) {
			$undated = array_diff($total['list'], $dated['list']);

			return ['list' => $undated, 'count' => count($undated)];
		}

		return ['list' => KT_I18N::translate('None recorded'), 'count' => 0];
	}

	public function noBirthRecorded()
	{
		$list = [];

		$rows = KT_DB::prepare("
			SELECT *
			FROM `##individuals`
			WHERE `i_file` = ?
			AND `i_gedcom` NOT REGEXP '\n1 BIRT'
			ORDER BY `i_id`
		")
			->execute([$this->_ged_id])
			->fetchAll()
		;

		foreach ($rows as $row) {
			$list[] = $row->i_id;
		}

		if (null != $list) {
			return ['list' => $list, 'count' => count($list)];
		}

		return ['list' => KT_I18N::translate('None recorded'), 'count' => 0];
	}

	public function totalDeaths()
	{
		$list = [];

		$rows = KT_DB::prepare("
            SELECT DISTINCT i_id
            FROM `##individuals`
            WHERE `i_file`=?
			AND `i_gedcom` REGEXP '\n1 DEAT'
            ORDER BY i_id
        ")
			->execute([$this->_ged_id])
			->fetchAll()
		;

		foreach ($rows as $row) {
			$list[] = $row->i_id;
		}

		if (null != $list) {
			return ['list' => $list, 'count' => count($list)];
		}

		return ['list' => KT_I18N::translate('None recorded'), 'count' => 0];
	}

	public function totalDatedDeaths()
	{
		$list = [];

		$rows = KT_DB::prepare("
            SELECT DISTINCT d_gid
            FROM `##dates`
            WHERE d_file=?
            AND d_year > 0
            AND d_fact='DEAT'
            AND d_type IN ('@#DGREGORIAN@', '@#DJULIAN@')
            ORDER BY d_gid
		")->execute([$this->_ged_id])
			->fetchAll()
		;

		foreach ($rows as $row) {
			$list[] = $row->d_gid;
		}

		if (null != $list) {
			return ['list' => $list, 'count' => count($list)];
		}

		return ['list' => KT_I18N::translate('None recorded'), 'count' => 0];
	}

	public function totalUndatedDeaths()
	{
		$total = $this->totalDeaths();
		$dated = $this->totalDatedDeaths();

		if (null != $total && null != $dated && !is_string($total['list']) && !is_string($dated['list'])) {
			$undated = array_diff($total['list'], $dated['list']);

			return ['list' => $undated, 'count' => count($undated)];
		}

		return ['list' => KT_I18N::translate('None recorded'), 'count' => 0];
	}

	public function totalEventsBirth()
	{
		return $this->totalEvents(explode('|', KT_EVENTS_BIRT));
	}

	public function totalEventsDeath()
	{
		return $this->totalEvents(explode('|', KT_EVENTS_DEAT));
	}

	public function totalEventsMarriage()
	{
		return $this->totalEvents(explode('|', KT_EVENTS_MARR));
	}

	public function totalMarriages()
	{
		return $this->totalEvents(['MARR']);
	}

	public function totalEventsDivorce()
	{
		return $this->totalEvents(explode('|', KT_EVENTS_DIV));
	}

	public function totalDivorces()
	{
		return $this->totalEvents(['DIV']);
	}

	public function totalEventsOther()
	{
		$facts = array_merge(explode('|', KT_EVENTS_BIRT . '|' . KT_EVENTS_MARR . '|' . KT_EVENTS_DIV . '|' . KT_EVENTS_DEAT));
		$no_facts = [];
		foreach ($facts as $fact) {
			$fact = '!' . str_replace('\'', '', $fact);
			$no_facts[] = $fact;
		}

		return $this->totalEvents($no_facts);
	}

	public function _totalSexMales()
	{
		return
			KT_DB::prepare('SELECT COUNT(*) FROM `##individuals` WHERE i_file=? AND i_sex=?')
				->execute([$this->_ged_id, 'M'])
				->fetchOne()
			;
	}

	public function totalSexMales()
	{
		return KT_I18N::number($this->_totalSexMales());
	}

	public function totalSexMalesPercentage()
	{
		return $this->_getPercentage($this->_totalSexMales(), 'individual');
	}

	public function _totalSexFemales()
	{
		return
			KT_DB::prepare('SELECT COUNT(*) FROM `##individuals` WHERE i_file=? AND i_sex=?')
				->execute([$this->_ged_id, 'F'])
				->fetchOne()
			;
	}

	public function totalSexFemales()
	{
		return KT_I18N::number($this->_totalSexFemales());
	}

	public function totalSexFemalesPercentage()
	{
		return $this->_getPercentage($this->_totalSexFemales(), 'individual');
	}

	public function _totalSexUnknown()
	{
		return
			KT_DB::prepare('SELECT COUNT(*) FROM `##individuals` WHERE i_file=? AND i_sex=?')
				->execute([$this->_ged_id, 'U'])
				->fetchOne()
			;
	}

	public function totalSexUnknown()
	{
		return KT_I18N::number($this->_totalSexUnknown());
	}

	public function totalSexUnknownPercentage()
	{
		return $this->_getPercentage($this->_totalSexUnknown(), 'individual');
	}

	public function chartSex()
	{
		$tot = $this->_totalSexFemales() + $this->_totalSexMales() + $this->_totalSexUnknown();

		if (0 == $tot) {
			return '';
		}
		$data = [
			[
				'category' => KT_I18N::translate('Females'),
				'count' => $this->_totalSexFemales(),
				'percent' => KT_I18N::number($this->_totalSexFemales(), 0),
				'color' => 'f',
			],
			[
				'category' => KT_I18N::translate('Males'),
				'count' => $this->_totalSexMales(),
				'percent' => KT_I18N::number($this->_totalSexMales(), 0),
				'color' => 'm',
			],
			[
				'category' => $this->_totalSexUnknown() > 0 ? KT_I18N::translate_c('unknown people', 'Unknown') : '',
				'count' => $this->_totalSexUnknown() > 0 ? $this->_totalSexUnknown() : '',
				'percent' => $this->_totalSexUnknown() > 0 ? KT_I18N::number($this->_totalSexUnknown(), 0) : '',
				'color' => 'u',
			],
		];

		return json_encode($data);
	}

	// The totalLiving/totalDeceased queries assume that every dead person will
	// have a DEAT record.  It will not include individuals who were born more
	// than MAX_ALIVE_AGE years ago, and who have no DEAT record.
	// A good reason to run the “Add missing DEAT records” batch-update!
	// However, SQL cannot provide the same logic used by Person::isDead().
	public function _totalLiving()
	{
		return
			KT_DB::prepare("
				SELECT COUNT(*)
				FROM `##individuals`
				WHERE i_file=?
				AND i_gedcom NOT REGEXP '\\n1 (" . KT_EVENTS_DEAT . ")'
			")->execute([$this->_ged_id])->fetchOne();
	}

	public function totalLiving()
	{
		return KT_I18N::number($this->_totalLiving());
	}

	public function totalLivingPercentage()
	{
		return $this->_getPercentage($this->_totalLiving(), 'individual');
	}

	public function _totalDeceased()
	{
		return
			KT_DB::prepare("SELECT COUNT(*) FROM `##individuals` WHERE i_file=? AND i_gedcom REGEXP '\\n1 (" . KT_EVENTS_DEAT . ")'")
				->execute([$this->_ged_id])
				->fetchOne()
			;
	}

	public function totalDeceased()
	{
		return KT_I18N::number($this->_totalDeceased());
	}

	public function totalDeceasedPercentage()
	{
		return $this->_getPercentage($this->_totalDeceased(), 'individual');
	}

	public function chartMortality()
	{
		$tot = $this->_totalLiving() + $this->_totalDeceased();

		if (0 == $tot) {
			return '';
		}
		$data = [
			[
				'category' => KT_I18N::translate('Living'),
				'count' => $this->_totalLiving(),
				'percent' => KT_I18N::number($this->_totalLiving(), 0),
				'color' => 'l',
			],
			[
				'category' => KT_I18N::translate('Dead'),
				'count' => $this->_totalDeceased(),
				'percent' => KT_I18N::number($this->_totalDeceased(), 0),
				'color' => 'd',
			],
		];

		return json_encode($data);
	}

	public static function totalUsers($params = [])
	{
		if (!empty($params[0])) {
			$total = get_user_count() + (int) $params[0];
		} else {
			$total = get_user_count();
		}

		return KT_I18N::number($total);
	}

	public static function totalAdmins()
	{
		return KT_I18N::number(get_admin_user_count());
	}

	public static function totalNonAdmins()
	{
		return KT_I18N::number(get_non_admin_user_count());
	}

	public function _totalMediaType($type = 'all')
	{
		if (!in_array($type, self::$_media_types) && 'all' != $type && 'unknown' != $type) {
			return 0;
		}

		$sql = 'SELECT COUNT(*) AS tot FROM `##media` WHERE m_file=?';
		$vars = [$this->_ged_id];

		if ('all' != $type) {
			if ('unknown' == $type) {
				// There has to be a better way then this :(
				foreach (self::$_media_types as $t) {
					$sql .= ' AND (m_gedcom NOT LIKE ? AND m_gedcom NOT LIKE ?)';
					$vars[] = "%3 TYPE {$t}%";
					$vars[] = "%1 _TYPE {$t}%";
				}
			} else {
				$sql .= ' AND (m_gedcom LIKE ? OR m_gedcom LIKE ?)';
				$vars[] = "%3 TYPE {$type}%";
				$vars[] = "%1 _TYPE {$type}%";
			}
		}

		return KT_DB::prepare($sql)->execute($vars)->fetchOne();
	}

	public function totalMedia()
	{
		return KT_I18N::number($this->_totalMediaType('all'));
	}

	public function totalMediaAudio()
	{
		return KT_I18N::number($this->_totalMediaType('audio'));
	}

	public function totalMediaBook()
	{
		return KT_I18N::number($this->_totalMediaType('book'));
	}

	public function totalMediaCard()
	{
		return KT_I18N::number($this->_totalMediaType('card'));
	}

	public function totalMediaCertificate()
	{
		return KT_I18N::number($this->_totalMediaType('certificate'));
	}

	public function totalMediaCoatOfArms()
	{
		return KT_I18N::number($this->_totalMediaType('coat'));
	}

	public function totalMediaDocument()
	{
		return KT_I18N::number($this->_totalMediaType('document'));
	}

	public function totalMediaElectronic()
	{
		return KT_I18N::number($this->_totalMediaType('electronic'));
	}

	public function totalMediaMagazine()
	{
		return KT_I18N::number($this->_totalMediaType('magazine'));
	}

	public function totalMediaManuscript()
	{
		return KT_I18N::number($this->_totalMediaType('manuscript'));
	}

	public function totalMediaMap()
	{
		return KT_I18N::number($this->_totalMediaType('map'));
	}

	public function totalMediaFiche()
	{
		return KT_I18N::number($this->_totalMediaType('fiche'));
	}

	public function totalMediaFilm()
	{
		return KT_I18N::number($this->_totalMediaType('film'));
	}

	public function totalMediaNewspaper()
	{
		return KT_I18N::number($this->_totalMediaType('newspaper'));
	}

	public function totalMediaPainting()
	{
		return KT_I18N::number($this->_totalMediaType('painting'));
	}

	public function totalMediaPhoto()
	{
		return KT_I18N::number($this->_totalMediaType('photo'));
	}

	public function totalMediaTombstone()
	{
		return KT_I18N::number($this->_totalMediaType('tombstone'));
	}

	public function totalMediaVideo()
	{
		return KT_I18N::number($this->_totalMediaType('video'));
	}

	public function totalMediaOther()
	{
		return KT_I18N::number($this->_totalMediaType('other'));
	}

	public function totalMediaUnknown()
	{
		return KT_I18N::number($this->_totalMediaType('unknown'));
	}

	public function chartMedia($minimum = 0)
	{
		$tot = $this->_totalMediaType('all');
		// Beware divide by zero
		if (0 == $tot) {
			return KT_I18N::translate('None');
		}
		// Build a table listing only the media types actually present in the GEDCOM
		$mediaCounts = [];
		$mediaTypes = '';
		$chart_title = '';
		$c = 0;
		$max = 0;
		$media = [];
		foreach (self::$_media_types as $type) {
			$count = $this->_totalMediaType($type);
			if ($count > $minimum) {
				$media[$type] = $count;
				if ($count > $max) {
					$max = $count;
				}
				$c += $count;
			}
		}
		$count = $this->_totalMediaType('unknown');
		if ($count > $minimum) {
			$media['unknown'] = $tot - $c;
			if ($tot - $c > $max) {
				$max = $count;
			}
		}
		if (($max / $tot) > 0.6 && count($media) > $minimum) {
			arsort($media);
			$media = array_slice($media, 0, $minimum);
			$c = $tot;
			foreach ($media as $cm) {
				$c -= $cm;
			}
			if (isset($media['other'])) {
				$media['other'] += $c;
			} else {
				$media['other'] = $c;
			}
		}
		asort($media);

		foreach ($media as $type => $count) {
			$data[] = [
				'category' => KT_Gedcom_Tag::getFileFormTypeValue($type),
				'count' => $count,
				'percent' => KT_I18N::number($count) . ' (' . KT_I18N::number(100 * $count / $tot, 1) . '%)',
				'color' => 'd',
				'type' => $type,
			];
		}

		return json_encode($data);
	}

// /////////////////////////////////////////////////////////////////////////////
// Birth & Death                                                             //
// /////////////////////////////////////////////////////////////////////////////

	public function _mortalityQuery($type = 'full', $life_dir = 'ASC', $birth_death = 'BIRT')
	{
		global $listDir;
		if ('MARR' == $birth_death) {
			$query_field = "'MARR'";
		} elseif ('DIV' == $birth_death) {
			$query_field = "'DIV'";
		} elseif ('BIRT' == $birth_death) {
			$query_field = "'BIRT'";
		} else {
			$birth_death = 'DEAT';
			$query_field = "'DEAT'";
		}
		if ('ASC' == $life_dir) {
			$dmod = 'MIN';
		} else {
			$dmod = 'MAX';
			$life_dir = 'DESC';
		}
		$rows = self::_runSQL('
			SELECT d_year, d_type, d_fact, d_gid
			 FROM `##dates`
			 WHERE d_file=' . $this->_ged_id . ' AND d_fact IN (' . $query_field . ') AND d_julianday1=(
			 	SELECT ' . $dmod . '( d_julianday1 )
				 FROM `##dates`
				 WHERE d_file=' . $this->_ged_id . ' AND d_fact IN (' . $query_field . ') AND d_julianday1<>0 )
				 LIMIT 1
		');
		if (!isset($rows[0])) {
			return '';
		}
		$row = $rows[0];
		$record = KT_GedcomRecord::getInstance($row['d_gid']);

		switch ($type) {
			default:
			case 'full':
				if ($record->canDisplayDetails()) {
					$result = $record->format_list('span', false, $record->getFullName());
				} else {
					$result = KT_I18N::translate('This information is private and cannot be shown.');
				}

				break;

			case 'year':
				$date = new KT_Date($row['d_type'] . ' ' . $row['d_year']);
				$result = $date->Display(true);

				break;

			case 'name':
				$result = '<a href="' . $record->getHtmlUrl() . '">' . $record->getFullName() . '</a>';

				break;

			case 'place':
				$fact = KT_GedcomRecord::getInstance($row['d_gid'])->getFactByType($row['d_fact']);
				if ($fact) {
					$result = format_fact_place($fact, true, true, true);
				} else {
					$result = KT_I18N::translate('Private');
				}

				break;
		}

		return $result;
	}

	public function _statsPlaces($what = 'ALL', $fact = false, $parent = 0, $country = false)
	{
		if ($fact) {
			if ('INDI' == $what) {
				$rows =
					KT_DB::prepare('SELECT i_gedcom AS ged FROM `##individuals` WHERE i_file=?')
						->execute([$this->_ged_id])
						->fetchAll()
					;
			} elseif ('FAM' == $what) {
				$rows =
					KT_DB::prepare('SELECT f_gedcom AS ged FROM `##families` WHERE f_file=?')
						->execute([$this->_ged_id])
						->fetchAll()
					;
			}
			$placelist = [];
			foreach ($rows as $row) {
				$factrec = trim(get_sub_record(1, "1 {$fact}", $row->ged, 1));
				if (!empty($factrec) && preg_match('/2 PLAC (.+)/', $factrec, $match)) {
					if ($country) {
						$tmp = explode(KT_Place::GEDCOM_SEPARATOR, $match[1]);
						$place = end($tmp);
					} else {
						$place = $match[1];
					}
					if (!isset($placelist[$place])) {
						$placelist[$place] = 1;
					} else {
						$placelist[$place]++;
					}
				}
			}

			return $placelist;
		}
		// used by placehierarchy googlemap module
		if ($parent > 0) {
			if ('INDI' == $what) {
				$join = ' JOIN `##individuals` ON pl_file = i_file AND pl_gid = i_id';
			} elseif ('FAM' == $what) {
				$join = ' JOIN `##families` ON pl_file = f_file AND pl_gid = f_id';
			} else {
				$join = '';
			}
			$rows = self::_runSQL(
				' SELECT' .
				' p_place AS place,' .
				' COUNT(*) AS tot' .
				' FROM' .
				' `##places`' .
				' JOIN `##placelinks` ON pl_file=p_file AND p_id=pl_p_id' .
				$join .
				' WHERE' .
				" p_id={$parent} AND" .
				" p_file={$this->_ged_id}" .
				' GROUP BY place'
			);
			if (!isset($rows[0])) {
				return '';
			}

			return $rows;
		}

		if ('INDI' == $what) {
			$join = ' JOIN `##individuals` ON pl_file = i_file AND pl_gid = i_id';
		} elseif ('FAM' == $what) {
			$join = ' JOIN `##families` ON pl_file = f_file AND pl_gid = f_id';
		} else {
			$join = '';
		}
		$rows = self::_runSQL(
			' SELECT' .
			' p_place AS country,' .
			' COUNT(*) AS tot' .
			' FROM' .
			' `##places`' .
			' JOIN `##placelinks` ON pl_file=p_file AND p_id=pl_p_id' .
			$join .
			' WHERE' .
			" p_file={$this->_ged_id}" .
			" AND p_parent_id='0'" .
			' GROUP BY country ORDER BY tot DESC, country ASC'
		);
		if (!isset($rows[0])) {
			return '';
		}

		return $rows;
	}

	public function _totalPlaces()
	{
		return
			KT_DB::prepare('SELECT COUNT(*) FROM `##places` WHERE p_file=?')
				->execute([$this->_ged_id])
				->fetchOne()
			;
	}

	public function totalPlaces()
	{
		return KT_I18n::number($this->_totalPlaces());
	}

	public function chartDistribution($params = [])
	{
		global $KT_STATS_CHART_COLOR1, $KT_STATS_CHART_COLOR2, $KT_STATS_CHART_COLOR3, $KT_STATS_MAP_X, $KT_STATS_MAP_Y;
		if (null !== $params && isset($params[0])) {
			$chart_shows = $params[0];
		} else {
			$chart_shows = 'world';
		}
		if (null !== $params && isset($params[1])) {
			$chart_type = $params[1];
		} else {
			$chart_type = '';
		}
		if (null !== $params && isset($params[2])) {
			$surname = $params[2];
		} else {
			$surname = '';
		}

		if (0 == $this->_totalPlaces()) {
			return '';
		}
		// Get the country names for each language
		$country_to_iso3166 = [];
		foreach (KT_I18N::installed_languages() as $code => $lang) {
			KT_I18N::init($code);
			$countries = self::get_all_countries();
			foreach (self::iso3166() as $three => $two) {
				$country_to_iso3166[$three] = $two;
				$country_to_iso3166[$countries[$three]] = $two;
			}
		}
		KT_I18N::init(KT_LOCALE);

		switch ($chart_type) {
			case 'surname_distribution_chart':
				if ('' == $surname) {
					$surname = $this->getCommonSurname();
				}
				$chart_title = KT_I18N::translate('Surname distribution chart') . ': ' . $surname;
				// Count how many people are events in each country
				$surn_countries = [];
				$indis = KT_Query_Name::individuals(utf8_strtoupper($surname), '', '', false, false, KT_GED_ID);
				foreach ($indis as $person) {
					if (preg_match_all('/^2 PLAC (.*, *)*(.*)/m', $person->getGedcomRecord(), $matches)) {
						// kiwitrees uses 3 letter country codes and localised country names, but google uses 2 letter codes.
						foreach ($matches[1] as $country) {
							$country = trim($country);
							if (array_key_exists($country, $country_to_iso3166)) {
								if (!isset($surn_countries[$country_to_iso3166[$country]])) {
									$surn_countries[$country_to_iso3166[$country]] = $place['tot'];
								} else {
									$surn_countries[$country_to_iso3166[$country]] += $place['tot'];
								}
							}
						}
					}
				}

				break;

			case 'birth_distribution_chart':
				$chart_title = KT_I18N::translate('Birth by country');
				// Count how many people were born in each country
				$surn_countries = [];
				$b_countries = $this->_statsPlaces('INDI', 'BIRT', 0, true);
				foreach ($b_countries as $place => $count) {
					$country = trim($place['country']);
					if (array_key_exists($country, $country_to_iso3166)) {
						if (!isset($surn_countries[$country_to_iso3166[$country]])) {
							$surn_countries[$country_to_iso3166[$country]] = $place['tot'];
						} else {
							$surn_countries[$country_to_iso3166[$country]] += $place['tot'];
						}
					}
				}

				break;

			case 'death_distribution_chart':
				$chart_title = KT_I18N::translate('Death by country');
				// Count how many people were death in each country
				$surn_countries = [];
				$d_countries = $this->_statsPlaces('INDI', 'DEAT', 0, true);
				foreach ($d_countries as $place => $count) {
					$country = trim($place['country']);
					if (array_key_exists($country, $countries)) {
						if (!isset($surn_countries[$country])) {
							$surn_countries[$country_to_iso3166[$country]] = $place['tot'];
						} else {
							$surn_countries[$country_to_iso3166[$country]] += $place['tot'];
						}
					}
					if (!isset($surn_countries[$country])) {
						$surn_countries[$country] = $place['tot'];
					} else {
						$surn_countries[$country] += $place['tot'];
					}
				}

				break;

			case 'marriage_distribution_chart':
				$chart_title = KT_I18N::translate('Marriage by country');
				// Count how many families got marriage in each country
				$surn_countries = [];
				$m_countries = $this->_statsPlaces('FAM');
				// kiwitrees uses 3 letter country codes and localised country names, but google uses 2 letter codes.
				foreach ($m_countries as $place) {
					$country = trim($place['country']);
					if (array_key_exists($country, $country_to_iso3166)) {
						if (!isset($surn_countries[$country_to_iso3166[$country]])) {
							$surn_countries[$country_to_iso3166[$country]] = $place['tot'];
						} else {
							$surn_countries[$country_to_iso3166[$country]] += $place['tot'];
						}
					}
				}

				break;

			case 'indi_distribution_chart':
			default:
				$surn_countries = [];
				$a_countries = $this->_statsPlaces('INDI');
				if ($a_countries) {
					// kiwitrees uses 3 letter country codes and localised country names, but google uses 2 letter codes.
					foreach ($a_countries as $place) {
						$country = trim($place['country']);
						if (array_key_exists($country, $country_to_iso3166)) {
							if (!isset($surn_countries[$country_to_iso3166[$country]])) {
								$surn_countries[$country_to_iso3166[$country]] = $place['tot'];
							} else {
								$surn_countries[$country_to_iso3166[$country]] += $place['tot'];
							}
						}
					}
				}

				break;
		}
		if ($surn_countries) {
			foreach ($surn_countries as $country => $count) {
				$data[] = [
					'country' => $country,
					'count' => $count,
				];
			}

			return json_encode($data);
		}

		return false;
	}

	public function commonCountriesList($params = [])
	{
		if (null === $params) {
			$params = [];
		}
		if (isset($params[0]) && '' != $params[0]) {
			$max = $params[0];
		} else {
			$max = 10;
		}
		$countries = $this->_statsPlaces();
		if (!is_array($countries)) {
			return '';
		}
		$top10 = [];
		$i = 1;
		// Get the country names for each language
		$country_names = [];
		foreach (KT_I18N::installed_languages() as $code => $lang) {
			KT_I18N::init($code);
			$all_countries = self::get_all_countries();
			foreach ($all_countries as $country_code => $country_name) {
				$country_names[$country_name] = $country_code;
			}
		}
		KT_I18N::init(KT_LOCALE);
		$all_db_countries = [];
		foreach ($countries as $place) {
			$country = trim($place['country']);
			if (array_key_exists($country, $country_names)) {
				if (!isset($all_db_countries[$country_names[$country]][$country])) {
					$all_db_countries[$country_names[$country]][$country] = $place['tot'];
				} else {
					$all_db_countries[$country_names[$country]][$country] += $place['tot'];
				}
			}
		}
		// get all the user’s countries names
		$all_countries = self::get_all_countries();
		foreach ($all_db_countries as $country_code => $country) {
			$top10[] = '<li>';
			foreach ($country as $country_name => $tot) {
				$tot >= 5000 ? $totClass = 'jsConfirm' : $totClass = 'notJsConfirm';
				$tmp = new KT_Place($country_name, $this->_ged_id);
				$place = '<a class="' . $totClass . '" href="' . $tmp->getURL() . '" class="list_item">' . $all_countries[$country_code] . '</a>';
				$top10[] .= $place . ' - ' . KT_I18N::number($tot);
			}
			$top10[] .= '</li>';
			if ($i++ == $max) {
				break;
			}
		}
		$top10 = join('', $top10);

		return '<ul>' . $top10 . '</ul>';
	}

	public function statsChartPlacesList()
	{
		$data = array_values(json_decode($this->chartDistribution(), true));
		$total = 0;
		foreach ($data as $result) {
			$total += $result['count'];
		}
		$htmlHigh = '<ul class="htmlHigh"><label class="h6">' . KT_I18N::translate('High population') . '</label>';
		$htmlMedm = '<ul class="htmlMedm"><label class="h6">' . KT_I18N::translate('Low population') . '</label>';

		foreach ($data as $result) {
			$percent = $result['count'] / $total;
			foreach ($this->iso3166() as $key => $value) {
				if ($value == $result['country']) {
					$countries = $this->get_all_countries();
					if (array_key_exists($key, $countries)) {
						$countryName = $countries[$key];
						$tmp = new KT_Place($countryName, $this->_ged_id);
						$place = '<a href="' . $tmp->getURL() . '" class="list_item">' . $countries[$key] . '</a>';
					}
				}
			}

			if ($percent >= 0.80) {
				$htmlHigh .= '<li>' . $place . '&nbsp;<small>(' . KT_I18N::number($result['count']) . ')&nbsp;' . KT_I18N::percentage($percent, 1) . '</small></li>';
			} elseif ($percent < 0.80 && $percent > 0) {
				$htmlMedm .= '<li>' . $place . '&nbsp;<small>(' . KT_I18N::number($result['count']) . ')&nbsp;' . KT_I18N::percentage($percent, 1) . '</small></li>';
			}
		}
		$htmlHigh .= '</ul>';
		$htmlMedm .= '</ul>';

		return $htmlHigh . $htmlMedm;
	}

	public function commonBirthPlacesList($params = [])
	{
		if (null === $params) {
			$params = [];
		}
		if (isset($params[0]) && '' != $params[0]) {
			$max = $params[0];
		} else {
			$max = 10;
		}
		$places = $this->_statsPlaces('INDI', 'BIRT');
		$top10 = [];
		$i = 1;
		arsort($places);
		foreach ($places as $place => $count) {
			$tmp = new KT_Place($place, $this->_ged_id);
			$place = '<a href="' . $tmp->getURL() . '" class="notJsConfirm list_item">' . $tmp->getFullName() . '</a>';
			$top10[] = '<li>' . $place . ' - ' . KT_I18N::number($count) . '</li>';
			if ($i++ == $max) {
				break;
			}
		}
		$top10 = join('', $top10);

		return '<ul>' . $top10 . '</ul>';
	}

	public function commonDeathPlacesList($params = [])
	{
		if (null === $params) {
			$params = [];
		}
		if (isset($params[0]) && '' != $params[0]) {
			$max = $params[0];
		} else {
			$max = 10;
		}
		$places = $this->_statsPlaces('INDI', 'DEAT');
		$top10 = [];
		$i = 1;
		arsort($places);
		foreach ($places as $place => $count) {
			$tmp = new KT_Place($place, $this->_ged_id);
			$place = '<a href="' . $tmp->getURL() . '" class="notJsConfirm list_item">' . $tmp->getFullName() . '</a>';
			$top10[] = '<li>' . $place . ' - ' . KT_I18N::number($count) . '</li>';
			if ($i++ == $max) {
				break;
			}
		}
		$top10 = join('', $top10);

		return '<ul>' . $top10 . '</ul>';
	}

	public function commonMarriagePlacesList($params = [])
	{
		if (null === $params) {
			$params = [];
		}
		if (isset($params[0]) && '' != $params[0]) {
			$max = $params[0];
		} else {
			$max = 10;
		}
		$places = $this->_statsPlaces('FAM', 'MARR');
		$top10 = [];
		$i = 1;
		arsort($places);
		foreach ($places as $place => $count) {
			$tmp = new KT_Place($place, $this->_ged_id);
			$place = '<a href="' . $tmp->getURL() . '" class="notJsConfirm list_item">' . $tmp->getFullName() . '</a>';
			$top10[] = '<li>' . $place . ' - ' . KT_I18N::number($count) . '</li>';
			if ($i++ == $max) {
				break;
			}
		}
		$top10 = join('', $top10);

		return '<ul>' . $top10 . '</ul>';
	}

	public function _statsBirth($simple = true, $sex = false, $year1 = -1, $year2 = -1, $params = [])
	{
		if ($simple) {
			KT_DB::exec(
				"CREATE TEMPORARY TABLE tempdates1
                SELECT d_file, d_year, d_fact, d_type, d_gid FROM `##dates`
                WHERE
                d_file={$this->_ged_id} AND
                d_year<>0 AND
                d_fact='BIRT' AND
                d_type IN ('@#DGREGORIAN@', '@#DJULIAN@')
                GROUP BY d_file, d_year, d_fact, d_type, d_gid"
			);
			$sql = 'SELECT FLOOR(d_year/100+1) AS century, COUNT(*) AS total FROM `tempdates1` ';
		} elseif ($sex) {
			$sql =
				'SELECT d_gid, d_month, i_sex, COUNT(*) AS total FROM `##dates` ' .
				'JOIN `##individuals` ON d_file = i_file AND d_gid = i_id ' .
				'WHERE ' .
				"d_file={$this->_ged_id} AND " .
				"d_fact='BIRT' AND " .
				"d_type IN ('@#DGREGORIAN@', '@#DJULIAN@')";
		} else {
			$sql =
				'SELECT d_gid, d_month, COUNT(*) AS total FROM `##dates` ' .
				'WHERE ' .
				"d_file={$this->_ged_id} AND " .
				"d_fact='BIRT' AND " .
				"d_type IN ('@#DGREGORIAN@', '@#DJULIAN@')";
		}
		if ($year1 >= 0 && $year2 >= 0) {
			$sql .= " AND d_year BETWEEN '{$year1}' AND '{$year2}'";
		}
		if ($simple) {
			$sql .= ' GROUP BY century ORDER BY century';
		} else {
			$sql .= ' GROUP BY d_month';
			if ($sex) {
				$sql .= ', i_sex';
			}
		}
		$rows = self::_runSQL($sql);
		if ($simple) {
			$tot = 0;
			foreach ($rows as $values) {
				$tot += $values['total'];
			}
			// Beware divide by zero
			if (0 == $tot) {
				return '';
			}
			foreach ($rows as $values) {
				$data[] = [
					'category' => self::_centuryName($values['century']),
					'count' => $values['total'],
					'percent' => KT_I18N::number($values['total']) . ' (' . KT_I18N::number(round(100 * $values['total'] / $tot, 0)) . '%)',
					'color' => 'd',
					'type' => $values['century'],
				];
			}

			return json_encode($data);
		}

		if (!isset($rows)) {
			return 0;
		}

		return $rows;
	}

	public function _statsDeath($simple = true, $sex = false, $year1 = -1, $year2 = -1)
	{
		if ($simple) {
			KT_DB::exec(
				"CREATE TEMPORARY TABLE tempdates2
                SELECT d_file, d_year, d_fact, d_type, d_gid FROM `##dates`
                WHERE
                d_file={$this->_ged_id} AND
                d_year<>0 AND
                d_fact='DEAT' AND
                d_type IN ('@#DGREGORIAN@', '@#DJULIAN@')
                GROUP BY d_file, d_year, d_fact, d_type, d_gid"
			);
			$sql = 'SELECT FLOOR(d_year/100+1) AS century, COUNT(*) AS total FROM `tempdates2` ';
		} elseif ($sex) {
			$sql =
				'SELECT d_gid, d_month, i_sex, COUNT(*) AS total FROM `##dates` ' .
				'JOIN `##individuals` ON d_file = i_file AND d_gid = i_id ' .
				'WHERE ' .
				"d_file={$this->_ged_id} AND " .
				"d_fact='DEAT' AND " .
				"d_type IN ('@#DGREGORIAN@', '@#DJULIAN@')";
		} else {
			$sql =
				'SELECT d_gid, d_month, COUNT(*) AS total FROM `##dates` ' .
				'WHERE ' .
				"d_file={$this->_ged_id} AND " .
				"d_fact='DEAT' AND " .
				"d_type IN ('@#DGREGORIAN@', '@#DJULIAN@')";
		}
		if ($year1 >= 0 && $year2 >= 0) {
			$sql .= " AND d_year BETWEEN '{$year1}' AND '{$year2}'";
		}
		if ($simple) {
			$sql .= ' GROUP BY century ORDER BY century';
		} else {
			$sql .= ' GROUP BY d_month';
			if ($sex) {
				$sql .= ', i_sex';
			}
		}
		$rows = self::_runSQL($sql);
		if ($simple) {
			$tot = 0;
			foreach ($rows as $values) {
				$tot += $values['total'];
			}
			// Beware divide by zero
			if (0 == $tot) {
				return '';
			}
			foreach ($rows as $values) {
				$data[] = [
					'category' => self::_centuryName($values['century']),
					'count' => $values['total'],
					'percent' => KT_I18N::number($values['total']) . ' (' . KT_I18N::number(round(100 * $values['total'] / $tot, 0)) . '%)',
					'color' => 'd',
					'type' => $values['century'],
				];
			}

			return json_encode($data);
		}

		if (!isset($rows)) {
			return 0;
		}

		return $rows;
	}

	//
	// Birth
	//

	public function firstBirth()
	{
		return $this->_mortalityQuery('full', 'ASC', 'BIRT');
	}

	public function firstBirthYear()
	{
		return $this->_mortalityQuery('year', 'ASC', 'BIRT');
	}

	public function firstBirthName()
	{
		return $this->_mortalityQuery('name', 'ASC', 'BIRT');
	}

	public function firstBirthPlace()
	{
		return $this->_mortalityQuery('place', 'ASC', 'BIRT');
	}

	public function lastBirth()
	{
		return $this->_mortalityQuery('full', 'DESC', 'BIRT');
	}

	public function lastBirthYear()
	{
		return $this->_mortalityQuery('year', 'DESC', 'BIRT');
	}

	public function lastBirthName()
	{
		return $this->_mortalityQuery('name', 'DESC', 'BIRT');
	}

	public function lastBirthPlace()
	{
		return $this->_mortalityQuery('place', 'DESC', 'BIRT');
	}

	public function statsBirth($params = [])
	{
		return $this->_statsBirth(true, false, -1, -1, $params);
	}
	//
	// Death
	//

	public function firstDeath()
	{
		return $this->_mortalityQuery('full', 'ASC', 'DEAT');
	}

	public function firstDeathYear()
	{
		return $this->_mortalityQuery('year', 'ASC', 'DEAT');
	}

	public function firstDeathName()
	{
		return $this->_mortalityQuery('name', 'ASC', 'DEAT');
	}

	public function firstDeathPlace()
	{
		return $this->_mortalityQuery('place', 'ASC', 'DEAT');
	}

	public function lastDeath()
	{
		return $this->_mortalityQuery('full', 'DESC', 'DEAT');
	}

	public function lastDeathYear()
	{
		return $this->_mortalityQuery('year', 'DESC', 'DEAT');
	}

	public function lastDeathName()
	{
		return $this->_mortalityQuery('name', 'DESC', 'DEAT');
	}

	public function lastDeathPlace()
	{
		return $this->_mortalityQuery('place', 'DESC', 'DEAT');
	}

	public function statsDeath($params = [])
	{
		return $this->_statsDeath(true, false, -1, -1, $params);
	}

// /////////////////////////////////////////////////////////////////////////////
// Lifespan                                                                  //
// /////////////////////////////////////////////////////////////////////////////

	public function _longlifeQuery($type = 'full', $sex = 'F')
	{
		global $listDir;

		$sex_search = ' 1=1';
		if ('F' == $sex) {
			$sex_search = " i_sex='F'";
		} elseif ('M' == $sex) {
			$sex_search = " i_sex='M'";
		}

		$rows = self::_runSQL(
			' SELECT' .
			' death.d_gid AS id,' .
			' death.d_julianday2-birth.d_julianday1 AS age' .
			' FROM' .
			' `##dates` AS death,' .
			' `##dates` AS birth,' .
			' `##individuals` AS indi' .
			' WHERE' .
			' indi.i_id=birth.d_gid AND' .
			' birth.d_gid=death.d_gid AND' .
			" death.d_file={$this->_ged_id} AND" .
			' birth.d_file=death.d_file AND' .
			' birth.d_file=indi.i_file AND' .
			" birth.d_fact='BIRT' AND" .
			" death.d_fact='DEAT' AND" .
			' birth.d_julianday1<>0 AND' .
			' death.d_julianday1>birth.d_julianday2 AND' .
			$sex_search .
			' ORDER BY' .
			' age DESC LIMIT 1'
		);
		if (!isset($rows[0])) {
			return '';
		}
		$row = $rows[0];
		$person = KT_Person::getInstance($row['id']);

		switch ($type) {
			default:
			case 'full':
				if ($person->canDisplayName()) {
					$result = $person->format_list('span', false, $person->getFullName());
				} else {
					$result = KT_I18N::translate('This information is private and cannot be shown.');
				}

				break;

			case 'age':
				$result = KT_I18N::number((int) ($row['age'] / 365.25));

				break;

			case 'name':
				$result = '<a href="' . $person->getHtmlUrl() . '">' . $person->getFullName() . '</a>';

				break;
		}

		return $result;
	}

	public function _topTenOldest($type = 'list', $sex = 'BOTH', $params = [])
	{
		global $TEXT_DIRECTION;

		if ('F' == $sex) {
			$sex_search = " AND i_sex='F' ";
		} elseif ('M' == $sex) {
			$sex_search = " AND i_sex='M' ";
		} else {
			$sex_search = '';
		}
		if (null !== $params && isset($params[0])) {
			$total = $params[0];
		} else {
			$total = 10;
		}
		$total = (int) $total;
		$rows = self::_runSQL(
			'SELECT ' .
			' MAX(death.d_julianday2-birth.d_julianday1) AS age, ' .
			' death.d_gid AS deathdate ' .
			'FROM ' .
			' `##dates` AS death, ' .
			' `##dates` AS birth, ' .
			' `##individuals` AS indi ' .
			'WHERE ' .
			' indi.i_id=birth.d_gid AND ' .
			' birth.d_gid=death.d_gid AND ' .
			" death.d_file={$this->_ged_id} AND " .
			' birth.d_file=death.d_file AND ' .
			' birth.d_file=indi.i_file AND ' .
			" birth.d_fact='BIRT' AND " .
			" death.d_fact='DEAT' AND " .
			' birth.d_julianday1<>0 AND ' .
			' death.d_julianday1>birth.d_julianday2 ' .
			$sex_search .
			'GROUP BY deathdate ' .
			'ORDER BY age DESC ' .
			'LIMIT ' . $total
		);
		if (!isset($rows[0])) {
			return '';
		}
		$top10 = [];
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row['deathdate']);
			$age = $row['age'];
			if ((int) ($age / 365.25) > 0) {
				$age = (int) ($age / 365.25) . 'y';
			} elseif ((int) ($age / 30.4375) > 0) {
				$age = (int) ($age / 30.4375) . 'm';
			} else {
				$age = $age . 'd';
			}
			$age = get_age_at_event($age, true);
			if ($person->canDisplayDetails()) {
				if ('list' == $type) {
					$top10[] = '<li><a href="' . $person->getHtmlUrl() . '">' . $person->getFullName() . '</a> (' . $age . ')</li>';
				} else {
					$top10[] = '<a href="' . $person->getHtmlUrl() . '">' . $person->getFullName() . '</a> (' . $age . ')';
				}
			}
		}
		if ('list' == $type) {
			$top10 = join('', $top10);
		} else {
			$top10 = join(' ', $top10);
		}
		if ('rtl' == $TEXT_DIRECTION) {
			$top10 = str_replace(['[', ']', '(', ')', '+'], ['&rlm;[', '&rlm;]', '&rlm;(', '&rlm;)', '&rlm;+'], $top10);
		}
		if ('list' == $type) {
			return '<ul>' . $top10 . '</ul>';
		}

		return $top10;
	}

	public function _topTenOldestAlive($type = 'list', $sex = 'BOTH', $params = [])
	{
		global $TEXT_DIRECTION;

		if (!KT_USER_CAN_ACCESS) {
			return KT_I18N::translate('This information is private and cannot be shown.');
		}
		if ('F' == $sex) {
			$sex_search = " AND i_sex='F'";
		} elseif ('M' == $sex) {
			$sex_search = " AND i_sex='M'";
		} else {
			$sex_search = '';
		}
		if (null !== $params && isset($params[0])) {
			$total = $params[0];
		} else {
			$total = 10;
		}
		$total = (int) $total;
		$rows = self::_runSQL(
			'SELECT' .
			' birth.d_gid AS id,' .
			' MIN(birth.d_julianday1) AS age' .
			' FROM' .
			' `##dates` AS birth,' .
			' `##individuals` AS indi' .
			' WHERE' .
			' indi.i_id=birth.d_gid AND' .
			" indi.i_gedcom NOT REGEXP '\\n1 (" . KT_EVENTS_DEAT . ")' AND" .
			" birth.d_file={$this->_ged_id} AND" .
			" birth.d_fact='BIRT' AND" .
			' birth.d_file=indi.i_file AND' .
			' birth.d_julianday1<>0' .
			$sex_search .
			' GROUP BY id' .
			' ORDER BY age' .
			' ASC LIMIT ' . $total
		);
		if (!isset($rows)) {
			return 0;
		}
		$top10 = [];
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row['id']);
			$age = (KT_CLIENT_JD - $row['age']);
			if ((int) ($age / 365.25) > 0) {
				$age = (int) ($age / 365.25) . 'y';
			} elseif ((int) ($age / 30.4375) > 0) {
				$age = (int) ($age / 30.4375) . 'm';
			} else {
				$age = $age . 'd';
			}
			$age = get_age_at_event($age, true);
			if ('list' == $type) {
				$top10[] = '<li><a href="' . $person->getHtmlUrl() . '">' . $person->getFullName() . '</a> (' . $age . ')</li>';
			} else {
				$top10[] = '<a href="' . $person->getHtmlUrl() . '">' . $person->getFullName() . '</a> (' . $age . ')';
			}
		}
		if ('list' == $type) {
			$top10 = join('', $top10);
		} else {
			$top10 = join(';&nbsp; ', $top10);
		}
		if ('rtl' == $TEXT_DIRECTION) {
			$top10 = str_replace(['[', ']', '(', ')', '+'], ['&rlm;[', '&rlm;]', '&rlm;(', '&rlm;)', '&rlm;+'], $top10);
		}
		if ('list' == $type) {
			return '<ul>' . $top10 . '</ul>';
		}

		return $top10;
	}

	public function _averageLifespanQuery($sex = 'BOTH', $show_years = false)
	{
		if ('F' == $sex) {
			$sex_search = " AND i_sex='F' ";
		} elseif ('M' == $sex) {
			$sex_search = " AND i_sex='M' ";
		} else {
			$sex_search = '';
		}
		$rows = self::_runSQL(
			'SELECT ' .
			' AVG(death.d_julianday2-birth.d_julianday1) AS age ' .
			'FROM ' .
			' `##dates` AS death, ' .
			' `##dates` AS birth, ' .
			' `##individuals` AS indi ' .
			'WHERE ' .
			' indi.i_id=birth.d_gid AND ' .
			' birth.d_gid=death.d_gid AND ' .
			' death.d_file=' . $this->_ged_id . ' AND ' .
			' birth.d_file=death.d_file AND ' .
			' birth.d_file=indi.i_file AND ' .
			" birth.d_fact='BIRT' AND " .
			" death.d_fact='DEAT' AND " .
			' birth.d_julianday1<>0 AND ' .
			' death.d_julianday1>birth.d_julianday2 ' .
			$sex_search
		);
		if (!isset($rows[0])) {
			return '';
		}
		$row = $rows[0];
		$age = $row['age'];
		if ($show_years) {
			if ((int) ($age / 365.25) > 0) {
				$age = (int) ($age / 365.25) . 'y';
			} elseif ((int) ($age / 30.4375) > 0) {
				$age = (int) ($age / 30.4375) . 'm';
			} elseif (!empty($age)) {
				$age = $age . 'd';
			}

			return get_age_at_event($age, true);
		}

		return KT_I18N::number($age / 365.25);
	}

	public function _statsAge($simple = true, $related = 'BIRT', $sex = 'BOTH', $year1 = -1, $year2 = -1, $params = [])
	{
		if ($simple) {
			if (isset($params[0]) && '' != $params[0]) {
				$size = strtolower($params[0]);
			} else {
				$size = '230x250';
			}
			$sizes = explode('x', $size);
			$rows = self::_runSQL(
				'SELECT' .
				' ROUND(AVG(death.d_julianday2-birth.d_julianday1)/365.25,1) AS age,' .
				' FLOOR(death.d_year/100+1) AS century,' .
				' i_sex AS sex' .
				' FROM' .
				' `##dates` AS death,' .
				' `##dates` AS birth,' .
				' `##individuals` AS indi' .
				' WHERE' .
				' indi.i_id=birth.d_gid AND' .
				' birth.d_gid=death.d_gid AND' .
				" death.d_file={$this->_ged_id} AND" .
				' birth.d_file=death.d_file AND' .
				' birth.d_file=indi.i_file AND' .
				" birth.d_fact='BIRT' AND" .
				" death.d_fact='DEAT' AND" .
				' birth.d_julianday1<>0 AND' .
				" birth.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND" .
				" death.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND" .
				' death.d_julianday1>birth.d_julianday2' .
				' GROUP BY century, sex ORDER BY century, sex'
			);
			if (empty($rows)) {
				return '';
			}
			$countsm = '';
			$countsf = '';
			$countsa = '';
			foreach ($rows as $values) {
				$out[$values['century']][$values['sex']] = $values['age'];
			}

			foreach ($out as $century => $values) {
				$female_age = $values['F'] ?? 0;
				$male_age = $values['M'] ?? 0;
				$average_age = round(($female_age + $male_age) / 2.0, 1);

				$data[] = [
					'century' => self::_centuryName($century),
					KT_I18N::translate('Males') => $male_age,
					KT_I18N::translate('Females') => $female_age,
					KT_I18N::translate('Average') => $average_age,
				];
			}

			return json_encode($data);
		}
		$sex_search = '';
		$years = '';
		if ('F' == $sex) {
			$sex_search = " AND i_sex='F'";
		} elseif ('M' == $sex) {
			$sex_search = " AND i_sex='M'";
		}
		if ($year1 >= 0 && $year2 >= 0) {
			if ('BIRT' == $related) {
				$years = " AND birth.d_year BETWEEN '{$year1}' AND '{$year2}'";
			} elseif ('DEAT' == $related) {
				$years = " AND death.d_year BETWEEN '{$year1}' AND '{$year2}'";
			}
		}
		$rows = self::_runSQL(
			'SELECT' .
			' death.d_julianday2-birth.d_julianday1 AS age' .
			' FROM' .
			' `##dates` AS death,' .
			' `##dates` AS birth,' .
			' `##individuals` AS indi' .
			' WHERE' .
			' indi.i_id=birth.d_gid AND' .
			' birth.d_gid=death.d_gid AND' .
			" death.d_file={$this->_ged_id} AND" .
			' birth.d_file=death.d_file AND' .
			' birth.d_file=indi.i_file AND' .
			" birth.d_fact='BIRT' AND" .
			" death.d_fact='DEAT' AND" .
			' birth.d_julianday1<>0 AND' .
			" birth.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND" .
			" death.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND" .
			' death.d_julianday1>birth.d_julianday2' .
			$years .
			$sex_search .
			' ORDER BY age DESC'
		);
		if (!isset($rows)) {
			return 0;
		}

		return $rows;
	}

	// Both Sexes
	public function statsAge($params = [])
	{
		return $this->_statsAge(true, 'BIRT', 'BOTH', -1, -1, $params);
	}

	public function longestLife()
	{
		return $this->_longlifeQuery('full', 'BOTH');
	}

	public function longestLifeAge()
	{
		return $this->_longlifeQuery('age', 'BOTH');
	}

	public function longestLifeName()
	{
		return $this->_longlifeQuery('name', 'BOTH');
	}

	public function topTenOldest($params = [])
	{
		return $this->_topTenOldest('nolist', 'BOTH', $params);
	}

	public function topTenOldestList($params = [])
	{
		return $this->_topTenOldest('list', 'BOTH', $params);
	}

	public function topTenOldestAlive($params = [])
	{
		return $this->_topTenOldestAlive('nolist', 'BOTH', $params);
	}

	public function topTenOldestListAlive($params = [])
	{
		return $this->_topTenOldestAlive('list', 'BOTH', $params);
	}

	public function averageLifespan($show_years = false)
	{
		return $this->_averageLifespanQuery('BOTH', $show_years);
	}

	// Female Only

	public function longestLifeFemale()
	{
		return $this->_longlifeQuery('full', 'F');
	}

	public function longestLifeFemaleAge()
	{
		return $this->_longlifeQuery('age', 'F');
	}

	public function longestLifeFemaleName()
	{
		return $this->_longlifeQuery('name', 'F');
	}

	public function topTenOldestFemale($params = [])
	{
		return $this->_topTenOldest('nolist', 'F', $params);
	}

	public function topTenOldestFemaleList($params = [])
	{
		return $this->_topTenOldest('list', 'F', $params);
	}

	public function topTenOldestFemaleAlive($params = [])
	{
		return $this->_topTenOldestAlive('nolist', 'F', $params);
	}

	public function topTenOldestFemaleListAlive($params = [])
	{
		return $this->_topTenOldestAlive('list', 'F', $params);
	}

	public function averageLifespanFemale($show_years = false)
	{
		return $this->_averageLifespanQuery('F', $show_years);
	}

	// Male Only

	public function longestLifeMale()
	{
		return $this->_longlifeQuery('full', 'M');
	}

	public function longestLifeMaleAge()
	{
		return $this->_longlifeQuery('age', 'M');
	}

	public function longestLifeMaleName()
	{
		return $this->_longlifeQuery('name', 'M');
	}

	public function topTenOldestMale($params = [])
	{
		return $this->_topTenOldest('nolist', 'M', $params);
	}

	public function topTenOldestMaleList($params = [])
	{
		return $this->_topTenOldest('list', 'M', $params);
	}

	public function topTenOldestMaleAlive($params = [])
	{
		return $this->_topTenOldestAlive('nolist', 'M', $params);
	}

	public function topTenOldestMaleListAlive($params = [])
	{
		return $this->_topTenOldestAlive('list', 'M', $params);
	}

	public function averageLifespanMale($show_years = false)
	{
		return $this->_averageLifespanQuery('M', $show_years);
	}

// /////////////////////////////////////////////////////////////////////////////
// Events                                                                    //
// /////////////////////////////////////////////////////////////////////////////

	public function _eventQuery($type, $direction, $facts)
	{
		global $listDir;
		$eventTypes = [
			'BIRT' => KT_I18N::translate('birth'),
			'DEAT' => KT_I18N::translate('death'),
			'MARR' => KT_I18N::translate('marriage'),
			'ADOP' => KT_I18N::translate('adoption'),
			'BURI' => KT_I18N::translate('burial'),
			'CENS' => KT_I18N::translate('census added'),
		];

		$fact_query = "IN ('" . str_replace('|', "','", $facts) . "')";

		if ('ASC' != $direction) {
			$direction = 'DESC';
		}
		$rows = self::_runSQL(
			''
			. ' SELECT'
				. ' d_gid AS id,'
				. ' d_year AS year,'
				. ' d_fact AS fact,'
				. ' d_type AS type'
			. ' FROM'
				. ' `##dates`'
			. ' WHERE'
				. " d_file={$this->_ged_id} AND"
				. " d_gid<>'HEAD' AND"
				. " d_fact {$fact_query} AND"
				. ' d_julianday1<>0'
			. ' ORDER BY'
				. " d_julianday1 {$direction}, d_type LIMIT 1"
		);
		if (!isset($rows[0])) {
			return '';
		}
		$row = $rows[0];
		$record = KT_GedcomRecord::getInstance($row['id']);

		switch ($type) {
			default:
			case 'full':
				if ($record->canDisplayDetails()) {
					$result = $record->format_list('span', false, $record->getFullName());
				} else {
					$result = KT_I18N::translate('This information is private and cannot be shown.');
				}

				break;

			case 'year':
				$date = new KT_Date($row['type'] . ' ' . $row['year']);
				$result = $date->Display(true);

				break;

			case 'type':
				if (isset($eventTypes[$row['fact']])) {
					$result = $eventTypes[$row['fact']];
				} else {
					$result = KT_Gedcom_Tag::getLabel($row['fact']);
				}

				break;

			case 'name':
				$result = '<a href="' . $record->getHtmlUrl() . '">' . $record->getFullName() . '</a>';

				break;

			case 'place':
				$fact = $record->getFactByType($row['fact']);
				if ($fact) {
					$result = format_fact_place($fact, true, true, true);
				} else {
					$result = KT_I18N::translate('Private');
				}

				break;
		}

		return $result;
	}

	public function firstEvent()
	{
		return $this->_eventQuery('full', 'ASC', KT_EVENTS_BIRT . '|' . KT_EVENTS_MARR . '|' . KT_EVENTS_DIV . '|' . KT_EVENTS_DEAT);
	}

	public function firstEventYear()
	{
		return $this->_eventQuery('year', 'ASC', KT_EVENTS_BIRT . '|' . KT_EVENTS_MARR . '|' . KT_EVENTS_DIV . '|' . KT_EVENTS_DEAT);
	}

	public function firstEventType()
	{
		return $this->_eventQuery('type', 'ASC', KT_EVENTS_BIRT . '|' . KT_EVENTS_MARR . '|' . KT_EVENTS_DIV . '|' . KT_EVENTS_DEAT);
	}

	public function firstEventName()
	{
		return $this->_eventQuery('name', 'ASC', KT_EVENTS_BIRT . '|' . KT_EVENTS_MARR . '|' . KT_EVENTS_DIV . '|' . KT_EVENTS_DEAT);
	}

	public function firstEventPlace()
	{
		return $this->_eventQuery('place', 'ASC', KT_EVENTS_BIRT . '|' . KT_EVENTS_MARR . '|' . KT_EVENTS_DIV . '|' . KT_EVENTS_DEAT);
	}

	public function lastEvent()
	{
		return $this->_eventQuery('full', 'DESC', KT_EVENTS_BIRT . '|' . KT_EVENTS_MARR . '|' . KT_EVENTS_DIV . '|' . KT_EVENTS_DEAT);
	}

	public function lastEventYear()
	{
		return $this->_eventQuery('year', 'DESC', KT_EVENTS_BIRT . '|' . KT_EVENTS_MARR . '|' . KT_EVENTS_DIV . '|' . KT_EVENTS_DEAT);
	}

	public function lastEventType()
	{
		return $this->_eventQuery('type', 'DESC', KT_EVENTS_BIRT . '|' . KT_EVENTS_MARR . '|' . KT_EVENTS_DIV . '|' . KT_EVENTS_DEAT);
	}

	public function lastEventName()
	{
		return $this->_eventQuery('name', 'DESC', KT_EVENTS_BIRT . '|' . KT_EVENTS_MARR . '|' . KT_EVENTS_DIV . '|' . KT_EVENTS_DEAT);
	}

	public function lastEventPlace()
	{
		return $this->_eventQuery('place', 'DESC', KT_EVENTS_BIRT . '|' . KT_EVENTS_MARR . '|' . KT_EVENTS_DIV . '|' . KT_EVENTS_DEAT);
	}

// /////////////////////////////////////////////////////////////////////////////
// Marriage                                                                  //
// /////////////////////////////////////////////////////////////////////////////

	// Query the KT_DB for marriage tags.
	public function _marriageQuery($type = 'full', $age_dir = 'ASC', $sex = 'F', $show_years = false)
	{
		if ('F' == $sex) {
			$sex_field = 'f_wife';
		} else {
			$sex_field = 'f_husb';
		}
		if ('ASC' != $age_dir) {
			$age_dir = 'DESC';
		}
		$rows = self::_runSQL(
			" SELECT fam.f_id AS famid, fam.{$sex_field}, married.d_julianday2-birth.d_julianday1 AS age, indi.i_id AS i_id" .
			' FROM `##families` AS fam' .
			" LEFT JOIN `##dates` AS birth ON birth.d_file = {$this->_ged_id}" .
			" LEFT JOIN `##dates` AS married ON married.d_file = {$this->_ged_id}" .
			" LEFT JOIN `##individuals` AS indi ON indi.i_file = {$this->_ged_id}" .
			' WHERE' .
			' birth.d_gid = indi.i_id AND' .
			' married.d_gid = fam.f_id AND' .
			" indi.i_id = fam.{$sex_field} AND" .
			" fam.f_file = {$this->_ged_id} AND" .
			" birth.d_fact = 'BIRT' AND" .
			" married.d_fact = 'MARR' AND" .
			' birth.d_julianday1 <> 0 AND' .
			' married.d_julianday2 > birth.d_julianday1 AND' .
			" i_sex='{$sex}'" .
			' ORDER BY' .
			" married.d_julianday2-birth.d_julianday1 {$age_dir} LIMIT 1"
		);
		if (!isset($rows[0])) {
			return '';
		}
		$row = $rows[0];
		if (isset($row['famid'])) {
			$family = KT_Family::getInstance($row['famid']);
		}
		if (isset($row['i_id'])) {
			$person = KT_Person::getInstance($row['i_id']);
		}

		switch ($type) {
			default:
			case 'full':
				if ($family->canDisplayDetails()) {
					$result = $family->format_list('span', false, $person->getFullName());
				} else {
					$result = KT_I18N::translate('This information is private and cannot be shown.');
				}

				break;

			case 'name':
				$result = '<a href="' . $family->getHtmlUrl() . '">' . $person->getFullName() . '</a>';

				break;

			case 'age':
				$age = $row['age'];
				if ($show_years) {
					if ((int) ($age / 365.25) > 0) {
						$age = (int) ($age / 365.25) . 'y';
					} elseif ((int) ($age / 30.4375) > 0) {
						$age = (int) ($age / 30.4375) . 'm';
					} else {
						$age = $age . 'd';
					}
					$result = get_age_at_event($age, true);
				} else {
					$result = (int) ($age / 365.25);
				}

				break;
		}

		return $result;
	}

	public function _ageOfMarriageQuery($type = 'list', $age_dir = 'ASC', $params = [])
	{
		global $TEXT_DIRECTION;
		if (null !== $params && isset($params[0])) {
			$total = $params[0];
		} else {
			$total = 10;
		}
		if ('ASC' != $age_dir) {
			$age_dir = 'DESC';
		}
		$hrows = self::_runSQL(
			' SELECT DISTINCT fam.f_id AS family, MIN(husbdeath.d_julianday2-married.d_julianday1) AS age' .
			' FROM `##families` AS fam' .
			" LEFT JOIN `##dates` AS married ON married.d_file = {$this->_ged_id}" .
			" LEFT JOIN `##dates` AS husbdeath ON husbdeath.d_file = {$this->_ged_id}" .
			' WHERE' .
			" fam.f_file = {$this->_ged_id} AND" .
			' husbdeath.d_gid = fam.f_husb AND' .
			" husbdeath.d_fact = 'DEAT' AND" .
			' married.d_gid = fam.f_id AND' .
			" married.d_fact = 'MARR' AND" .
			' married.d_julianday1 < husbdeath.d_julianday2 AND' .
			' married.d_julianday1 <> 0' .
			' GROUP BY family' .
			" ORDER BY age {$age_dir}"
		);
		$wrows = self::_runSQL(
			' SELECT DISTINCT fam.f_id AS family, MIN(wifedeath.d_julianday2-married.d_julianday1) AS age' .
			' FROM `##families` AS fam' .
			" LEFT JOIN `##dates` AS married ON married.d_file = {$this->_ged_id}" .
			" LEFT JOIN `##dates` AS wifedeath ON wifedeath.d_file = {$this->_ged_id}" .
			' WHERE' .
			" fam.f_file = {$this->_ged_id} AND" .
			' wifedeath.d_gid = fam.f_wife AND' .
			" wifedeath.d_fact = 'DEAT' AND" .
			' married.d_gid = fam.f_id AND' .
			" married.d_fact = 'MARR' AND" .
			' married.d_julianday1 < wifedeath.d_julianday2 AND' .
			' married.d_julianday1 <> 0' .
			' GROUP BY family' .
			" ORDER BY age {$age_dir}"
		);
		$drows = self::_runSQL(
			' SELECT DISTINCT fam.f_id AS family, MIN(divorced.d_julianday2-married.d_julianday1) AS age' .
			' FROM `##families` AS fam' .
			" LEFT JOIN `##dates` AS married ON married.d_file = {$this->_ged_id}" .
			" LEFT JOIN `##dates` AS divorced ON divorced.d_file = {$this->_ged_id}" .
			' WHERE' .
			" fam.f_file = {$this->_ged_id} AND" .
			' married.d_gid = fam.f_id AND' .
			" married.d_fact = 'MARR' AND" .
			' divorced.d_gid = fam.f_id AND' .
			" divorced.d_fact IN ('DIV', 'ANUL', '_SEPR', '_DETS') AND" .
			' married.d_julianday1 < divorced.d_julianday2 AND' .
			' married.d_julianday1 <> 0' .
			' GROUP BY family' .
			" ORDER BY age {$age_dir}"
		);
		if (!isset($hrows) && !isset($wrows) && !isset($drows)) {
			return 0;
		}
		$rows = [];
		foreach ($drows as $family) {
			$rows[$family['family']] = $family['age'];
		}
		foreach ($hrows as $family) {
			if (!isset($rows[$family['family']])) {
				$rows[$family['family']] = $family['age'];
			}
		}
		foreach ($wrows as $family) {
			if (!isset($rows[$family['family']])) {
				$rows[$family['family']] = $family['age'];
			} elseif ($rows[$family['family']] > $family['age']) {
				$rows[$family['family']] = $family['age'];
			}
		}
		if ('DESC' == $age_dir) {
			arsort($rows);
		} else {
			asort($rows);
		}
		$top10 = [];
		$i = 0;
		foreach ($rows as $fam => $age) {
			$family = KT_Family::getInstance($fam);
			if ('name' == $type) {
				return $family->format_list('span', false, $family->getFullName());
			}
			if ((int) ($age / 365.25) > 0) {
				$age = (int) ($age / 365.25) . 'y';
			} elseif ((int) ($age / 30.4375) > 0) {
				$age = (int) ($age / 30.4375) . 'm';
			} else {
				$age = $age . 'd';
			}
			$age = get_age_at_event($age, true);
			if ('age' == $type) {
				return $age;
			}
			$husb = $family->getHusband();
			$wife = $family->getWife();
			if (($husb->getAllDeathDates() && $wife->getAllDeathDates()) || !$husb->isDead() || !$wife->isDead()) {
				if ($family->canDisplayDetails()) {
					if ('list' == $type) {
						$top10[] = '<li><a href="' . $family->getHtmlUrl() . '">' . $family->getFullName() . '</a> (' . $age . ')</li>';
					} else {
						$top10[] = '<a href="' . $family->getHtmlUrl() . '">' . $family->getFullName() . '</a> (' . $age . ')';
					}
				}
				if (++$i == $total) {
					break;
				}
			}
		}
		if ('list' == $type) {
			$top10 = join('', $top10);
		} else {
			$top10 = join(';&nbsp; ', $top10);
		}
		if ('rtl' == $TEXT_DIRECTION) {
			$top10 = str_replace(['[', ']', '(', ')', '+'], ['&rlm;[', '&rlm;]', '&rlm;(', '&rlm;)', '&rlm;+'], $top10);
		}
		if ('list' == $type) {
			return '<ul>' . $top10 . '</ul>';
		}

		return $top10;
	}

	public function _ageBetweenSpousesQuery($type = 'list', $age_dir = 'DESC', $params = [])
	{
		global $TEXT_DIRECTION;
		if (null !== $params && isset($params[0])) {
			$total = $params[0];
		} else {
			$total = 10;
		}
		if ('DESC' == $age_dir) {
			$query1 = ' MIN(wifebirth.d_julianday2-husbbirth.d_julianday1) AS age';
			$query2 = ' wifebirth.d_julianday2 >= husbbirth.d_julianday1 AND husbbirth.d_julianday1 <> 0';
		} else {
			$query1 = ' MIN(husbbirth.d_julianday2-wifebirth.d_julianday1) AS age';
			$query2 = ' wifebirth.d_julianday1 < husbbirth.d_julianday2 AND wifebirth.d_julianday1 <> 0';
		}
		$total = (int) $total;
		$rows = self::_runSQL(
			' SELECT fam.f_id AS family,' . $query1 .
			' FROM `##families` AS fam' .
			" LEFT JOIN `##dates` AS wifebirth ON wifebirth.d_file = {$this->_ged_id}" .
			" LEFT JOIN `##dates` AS husbbirth ON husbbirth.d_file = {$this->_ged_id}" .
			' WHERE' .
			" fam.f_file = {$this->_ged_id} AND" .
			' husbbirth.d_gid = fam.f_husb AND' .
			" husbbirth.d_fact = 'BIRT' AND" .
			' wifebirth.d_gid = fam.f_wife AND' .
			" wifebirth.d_fact = 'BIRT' AND" .
			$query2 .
			' GROUP BY family' .
			' ORDER BY age DESC LIMIT ' . $total
		);
		if (!isset($rows[0])) {
			return '';
		}
		$top10 = [];
		foreach ($rows as $fam) {
			$family = KT_Family::getInstance($fam['family']);
			if ($fam['age'] < 0) {
				break;
			}
			$age = $fam['age'];
			if ((int) ($age / 365.25) > 0) {
				$age = (int) ($age / 365.25) . 'y';
			} elseif ((int) ($age / 30.4375) > 0) {
				$age = (int) ($age / 30.4375) . 'm';
			} else {
				$age = $age . 'd';
			}
			$age = get_age_at_event($age, true);
			if ($family->canDisplayDetails()) {
				if ('list' == $type) {
					$top10[] = '<li><a href="' . $family->getHtmlUrl() . '">' . $family->getFullName() . '</a> (' . $age . ')</li>';
				} else {
					$top10[] = '<a href="' . $family->getHtmlUrl() . '">' . $family->getFullName() . '</a> (' . $age . ')';
				}
			}
		}
		if ('list' == $type) {
			$top10 = join('', $top10);
		} else {
			$top10 = join(';&nbsp; ', $top10);
		}
		if ('rtl' == $TEXT_DIRECTION) {
			$top10 = str_replace(['[', ']', '(', ')', '+'], ['&rlm;[', '&rlm;]', '&rlm;(', '&rlm;)', '&rlm;+'], $top10);
		}
		if ('list' == $type) {
			return '<ul>' . $top10 . '</ul>';
		}

		return $top10;
	}

	public function _parentsQuery($type = 'full', $age_dir = 'ASC', $sex = 'F', $show_years = false)
	{
		if ('F' == $sex) {
			$sex_field = 'WIFE';
		} else {
			$sex_field = 'HUSB';
		}
		if ('ASC' != $age_dir) {
			$age_dir = 'DESC';
		}

		$rows = self::_runSQL("
			SELECT
			parentfamily.l_to AS id,
			childbirth.d_julianday2-birth.d_julianday1 AS age
			FROM `##link` AS parentfamily
				JOIN `##link` AS childfamily ON childfamily.l_file = {$this->_ged_id}
				JOIN `##dates` AS birth ON birth.d_file = {$this->_ged_id}
				JOIN `##dates` AS childbirth ON childbirth.d_file = {$this->_ged_id}
			WHERE
				birth.d_gid = parentfamily.l_to AND
				childfamily.l_to = childbirth.d_gid AND
				childfamily.l_type = 'CHIL' AND
				parentfamily.l_type = '{$sex_field}' AND
				childfamily.l_from = parentfamily.l_from AND
				parentfamily.l_file = {$this->_ged_id} AND
				birth.d_fact = 'BIRT' AND
				childbirth.d_fact = 'BIRT' AND
				birth.d_julianday1 <> 0 AND
				childbirth.d_julianday2 > birth.d_julianday1
			ORDER BY age {$age_dir}
			LIMIT 1
		");

		if (!isset($rows[0])) {
			return '';
		}

		$row = $rows[0];
		if (isset($row['id'])) {
			$person = KT_Person::getInstance($row['id']);
		}

		switch ($type) {
			default:
			case 'full':
				if ($person->canDisplayDetails()) {
					$result = $person->format_list('span', false, $person->getFullName());
				} else {
					$result = KT_I18N::translate('This information is private and cannot be shown.');
				}

				break;

			case 'name':
				$result = '<a href="' . $person->getHtmlUrl() . '">' . $person->getFullName() . '</a>';

				break;

			case 'age':
				$age = $row['age'];
				if ($show_years) {
					if ((int) ($age / 365.25) > 0) {
						$age = (int) ($age / 365.25) . 'y';
					} elseif ((int) ($age / 30.4375) > 0) {
						$age = (int) ($age / 30.4375) . 'm';
					} else {
						$age = $age . 'd';
					}
					$result = get_age_at_event($age, true);
				} else {
					$result = (int) ($age / 365.25);
				}

				break;
		}

		return $result;
	}

	public function _statsMarr($simple = true, $first = false, $year1 = -1, $year2 = -1, $params = [])
	{
		if ($simple) {
			$sql = '
				SELECT FLOOR(d_year/100+1) AS century, COUNT(*) AS total
					FROM (
						SELECT d_file, d_year, d_fact, d_type, d_gid FROM `##dates`
						 WHERE d_file=' . $this->_ged_id . " AND d_year<>0 AND d_fact = 'MARR' AND d_type IN ('@#DGREGORIAN@', '@#DJULIAN@')
						 GROUP BY d_file, d_year, d_fact, d_type, d_gid";
			if ($year1 >= 0 && $year2 >= 0) {
				$sql .= " AND d_year BETWEEN '" . $year1 . "' AND '" . $year2 . "'";
			}
			$sql .= ') AS t1
				 GROUP BY century ORDER BY century
			';
		} elseif ($first) {
			$years = '';
			if ($year1 >= 0 && $year2 >= 0) {
				$years = " married.d_year BETWEEN '{$year1}' AND '{$year2}' AND";
			}
			$sql =
			' SELECT fam.f_id AS fams, fam.f_husb, fam.f_wife, married.d_julianday2 AS age, married.d_month AS month, indi.i_id AS indi' .
			' FROM `##families` AS fam' .
			" LEFT JOIN `##dates` AS married ON married.d_file = {$this->_ged_id}" .
			" LEFT JOIN `##individuals` AS indi ON indi.i_file = {$this->_ged_id}" .
			' WHERE' .
			' married.d_gid = fam.f_id AND' .
			" fam.f_file = {$this->_ged_id} AND" .
			" married.d_fact = 'MARR' AND" .
			' married.d_julianday2 <> 0 AND' .
			$years .
			' (indi.i_id = fam.f_husb OR indi.i_id = fam.f_wife)' .
			' ORDER BY fams, indi, age ASC';
		} else {
			$sql =
				'SELECT d_month, COUNT(*) AS total FROM `##dates` ' .
				"WHERE d_file={$this->_ged_id} AND d_fact = 'MARR'";
			if ($year1 >= 0 && $year2 >= 0) {
				$sql .= " AND d_year BETWEEN '{$year1}' AND '{$year2}'";
			}
			$sql .= ' GROUP BY d_month';
		}
		$rows = self::_runSQL($sql);
		if ($simple) {
			$tot = 0;
			foreach ($rows as $values) {
				$tot += $values['total'];
			}
			// Beware divide by zero
			if (0 == $tot) {
				return '';
			}
			foreach ($rows as $values) {
				$data[] = [
					'category' => self::_centuryName($values['century']),
					'count' => $values['total'],
					'percent' => KT_I18N::number($values['total']) . ' (' . KT_I18N::number(round(100 * $values['total'] / $tot, 0)) . '%)',
					'color' => 'd',
					'type' => $values['century'],
				];
			}

			return json_encode($data);
		}

		if (!isset($rows)) {
			return 0;
		}

		return $rows;
	}

	public function _statsDiv($simple = true, $first = false, $year1 = -1, $year2 = -1, $params = [])
	{
		if ($simple) {
			$sql = '
				SELECT FLOOR(d_year/100+1) AS century, COUNT(*) AS total
					FROM (
						SELECT d_file, d_year, d_fact, d_type, d_gid FROM `##dates`
						 WHERE d_file=' . $this->_ged_id . " AND d_year<>0 AND d_fact = 'DIV' AND d_type IN ('@#DGREGORIAN@', '@#DJULIAN@')
						 GROUP BY d_file, d_year, d_fact, d_type, d_gid";
			if ($year1 >= 0 && $year2 >= 0) {
				$sql .= " AND d_year BETWEEN '" . $year1 . "' AND '" . $year2 . "'";
			}
			$sql .= ') AS t1
				 GROUP BY century ORDER BY century
			';
		} elseif ($first) {
			$years = '';
			if ($year1 >= 0 && $year2 >= 0) {
				$years = " divorced.d_year BETWEEN '{$year1}' AND '{$year2}' AND";
			}
			$sql =
			' SELECT fam.f_id AS fams, fam.f_husb, fam.f_wife, divorced.d_julianday2 AS age, divorced.d_month AS month, indi.i_id AS indi' .
			' FROM `##families` AS fam' .
			" LEFT JOIN `##dates` AS divorced ON divorced.d_file = {$this->_ged_id}" .
			" LEFT JOIN `##individuals` AS indi ON indi.i_file = {$this->_ged_id}" .
			' WHERE' .
			' divorced.d_gid = fam.f_id AND' .
			" fam.f_file = {$this->_ged_id} AND" .
			" divorced.d_fact = 'DIV' AND" .
			' divorced.d_julianday2 <> 0 AND' .
			$years .
			' (indi.i_id = fam.f_husb OR indi.i_id = fam.f_wife)' .
			' ORDER BY fams, indi, age ASC';
		} else {
			$sql =
				'SELECT d_month, COUNT(*) AS total FROM `##dates` ' .
				"WHERE d_file={$this->_ged_id} AND d_fact = 'DIV'";
			if ($year1 >= 0 && $year2 >= 0) {
				$sql .= " AND d_year BETWEEN '{$year1}' AND '{$year2}'";
			}
			$sql .= ' GROUP BY d_month';
		}
		$rows = self::_runSQL($sql);
		if ($simple) {
			$tot = 0;
			foreach ($rows as $values) {
				$tot += $values['total'];
			}
			// Beware divide by zero
			if (0 == $tot) {
				return '';
			}
			foreach ($rows as $values) {
				$data[] = [
					'category' => self::_centuryName($values['century']),
					'count' => $values['total'],
					'percent' => KT_I18N::number($values['total']) . ' (' . KT_I18N::number(round(100 * $values['total'] / $tot, 0)) . '%)',
					'color' => 'd',
					'type' => $values['century'],
				];
			}

			return json_encode($data);
		}

		if (!isset($rows)) {
			return 0;
		}

		return $rows;
	}

	//
	// Marriage
	//
	public function firstMarriage()
	{
		return $this->_mortalityQuery('full', 'ASC', 'MARR');
	}

	public function firstMarriageYear()
	{
		return $this->_mortalityQuery('year', 'ASC', 'MARR');
	}

	public function firstMarriageName()
	{
		return $this->_mortalityQuery('name', 'ASC', 'MARR');
	}

	public function firstMarriagePlace()
	{
		return $this->_mortalityQuery('place', 'ASC', 'MARR');
	}

	public function lastMarriage()
	{
		return $this->_mortalityQuery('full', 'DESC', 'MARR');
	}

	public function lastMarriageYear()
	{
		return $this->_mortalityQuery('year', 'DESC', 'MARR');
	}

	public function lastMarriageName()
	{
		return $this->_mortalityQuery('name', 'DESC', 'MARR');
	}

	public function lastMarriagePlace()
	{
		return $this->_mortalityQuery('place', 'DESC', 'MARR');
	}

	public function statsMarr($params = [])
	{
		return $this->_statsMarr(true, false, -1, -1, $params);
	}

	//
	// Divorce
	//
	public function firstDivorce()
	{
		return $this->_mortalityQuery('full', 'ASC', 'DIV');
	}

	public function firstDivorceYear()
	{
		return $this->_mortalityQuery('year', 'ASC', 'DIV');
	}

	public function firstDivorceName()
	{
		return $this->_mortalityQuery('name', 'ASC', 'DIV');
	}

	public function firstDivorcePlace()
	{
		return $this->_mortalityQuery('place', 'ASC', 'DIV');
	}

	public function lastDivorce()
	{
		return $this->_mortalityQuery('full', 'DESC', 'DIV');
	}

	public function lastDivorceYear()
	{
		return $this->_mortalityQuery('year', 'DESC', 'DIV');
	}

	public function lastDivorceName()
	{
		return $this->_mortalityQuery('name', 'DESC', 'DIV');
	}

	public function lastDivorcePlace()
	{
		return $this->_mortalityQuery('place', 'DESC', 'DIV');
	}

	public function statsDiv($params = [])
	{
		return $this->_statsDiv(true, false, -1, -1, $params);
	}

	public function _statsMarrAge($simple = true, $sex = 'M', $year1 = -1, $year2 = -1, $params = [])
	{
		if ($simple) {
			if (isset($params[0]) && '' != $params[0]) {
				$size = strtolower($params[0]);
			} else {
				$size = '200x250';
			}
			$sizes = explode('x', $size);
			$rows = self::_runSQL(
				'SELECT ' .
				' ROUND(AVG(married.d_julianday2-birth.d_julianday1-182.5)/365.25,1) AS age, ' .
				' FLOOR(married.d_year/100+1) AS century, ' .
				" 'M' AS sex " .
				'FROM `##dates` AS married ' .
				'JOIN `##families` AS fam ON (married.d_gid=fam.f_id AND married.d_file=fam.f_file) ' .
				'JOIN `##dates` AS birth ON (birth.d_gid=fam.f_husb AND birth.d_file=fam.f_file) ' .
				'WHERE ' .
				" '{$sex}' IN ('M', 'BOTH') AND " .
				" married.d_file={$this->_ged_id} AND married.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND married.d_fact='MARR' AND " .
				" birth.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND birth.d_fact='BIRT' AND " .
				' married.d_julianday1>birth.d_julianday1 AND birth.d_julianday1<>0 ' .
				'GROUP BY century, sex ' .
				'UNION ALL ' .
				'SELECT ' .
				' ROUND(AVG(married.d_julianday2-birth.d_julianday1-182.5)/365.25,1) AS age, ' .
				' FLOOR(married.d_year/100+1) AS century, ' .
				" 'F' AS sex " .
				'FROM `##dates` AS married ' .
				'JOIN `##families` AS fam ON (married.d_gid=fam.f_id AND married.d_file=fam.f_file) ' .
				'JOIN `##dates` AS birth ON (birth.d_gid=fam.f_wife AND birth.d_file=fam.f_file) ' .
				'WHERE ' .
				" '{$sex}' IN ('F', 'BOTH') AND " .
				" married.d_file={$this->_ged_id} AND married.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND married.d_fact='MARR' AND " .
				" birth.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND birth.d_fact='BIRT' AND " .
				' married.d_julianday1>birth.d_julianday1 AND birth.d_julianday1<>0 ' .
				' GROUP BY century, sex ORDER BY century'
			);
			if (empty($rows)) {
				return '';
			}
			$max = 0;
			foreach ($rows as $values) {
				if ($max < $values['age']) {
					$max = $values['age'];
				}
			}
			$i = 0;
			$countsm = '';
			$countsf = '';
			$countsa = '';
			foreach ($rows as $values) {
				$out[$values['century']][$values['sex']] = $values['age'];
			}
			foreach ($out as $century => $values) {
				$female_age = $values['F'] ?? 0;
				$male_age = $values['M'] ?? 0;
				$average_age = round(($female_age + $male_age) / 2.0, 1);

				$data[] = [
					'century' => self::_centuryName($century),
					KT_I18N::translate('Males') => $male_age,
					KT_I18N::translate('Females') => $female_age,
					KT_I18N::translate('Average') => $average_age,
				];
			}

			return json_encode($data);
		}
		if ($year1 >= 0 && $year2 >= 0) {
			$years = " married.d_year BETWEEN {$year1} AND {$year2} AND ";
		} else {
			$years = '';
		}

		return self::_runSQL(
			'SELECT ' .
			' fam.f_id, ' .
			' birth.d_gid, ' .
			' married.d_julianday2-birth.d_julianday1 AS age ' .
			'FROM `##dates` AS married ' .
			'JOIN `##families` AS fam ON (married.d_gid=fam.f_id AND married.d_file=fam.f_file) ' .
			'JOIN `##dates` AS birth ON (birth.d_gid=fam.f_husb AND birth.d_file=fam.f_file) ' .
			'WHERE ' .
			" '{$sex}' IN ('M', 'BOTH') AND {$years} " .
			" married.d_file={$this->_ged_id} AND married.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND married.d_fact='MARR' AND " .
			" birth.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND birth.d_fact='BIRT' AND " .
			' married.d_julianday1>birth.d_julianday1 AND birth.d_julianday1<>0 ' .
			'UNION ALL ' .
			'SELECT ' .
			' fam.f_id, ' .
			' birth.d_gid, ' .
			' married.d_julianday2-birth.d_julianday1 AS age ' .
			'FROM `##dates` AS married ' .
			'JOIN `##families` AS fam ON (married.d_gid=fam.f_id AND married.d_file=fam.f_file) ' .
			'JOIN `##dates` AS birth ON (birth.d_gid=fam.f_wife AND birth.d_file=fam.f_file) ' .
			'WHERE ' .
			" '{$sex}' IN ('F', 'BOTH') AND {$years} " .
			" married.d_file={$this->_ged_id} AND married.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND married.d_fact='MARR' AND " .
			" birth.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND birth.d_fact='BIRT' AND " .
			' married.d_julianday1>birth.d_julianday1 AND birth.d_julianday1<>0 '
		);
	}

	//
	// Female only
	//
	public function youngestMarriageFemale()
	{
		return $this->_marriageQuery('full', 'ASC', 'F');
	}

	public function youngestMarriageFemaleName()
	{
		return $this->_marriageQuery('name', 'ASC', 'F');
	}

	public function youngestMarriageFemaleAge($show_years = false)
	{
		return $this->_marriageQuery('age', 'ASC', 'F', $show_years);
	}

	public function oldestMarriageFemale()
	{
		return $this->_marriageQuery('full', 'DESC', 'F');
	}

	public function oldestMarriageFemaleName()
	{
		return $this->_marriageQuery('name', 'DESC', 'F');
	}

	public function oldestMarriageFemaleAge($show_years = false)
	{
		return $this->_marriageQuery('age', 'DESC', 'F', $show_years);
	}

	//
	// Male only
	//
	public function youngestMarriageMale()
	{
		return $this->_marriageQuery('full', 'ASC', 'M');
	}

	public function youngestMarriageMaleName()
	{
		return $this->_marriageQuery('name', 'ASC', 'M');
	}

	public function youngestMarriageMaleAge($show_years = false)
	{
		return $this->_marriageQuery('age', 'ASC', 'M', $show_years);
	}

	public function oldestMarriageMale()
	{
		return $this->_marriageQuery('full', 'DESC', 'M');
	}

	public function oldestMarriageMaleName()
	{
		return $this->_marriageQuery('name', 'DESC', 'M');
	}

	public function oldestMarriageMaleAge($show_years = false)
	{
		return $this->_marriageQuery('age', 'DESC', 'M', $show_years);
	}

	public function statsMarrAge($params = [])
	{
		return $this->_statsMarrAge(true, 'BOTH', -1, -1, $params);
	}

	public function ageBetweenSpousesMF($params = [])
	{
		return $this->_ageBetweenSpousesQuery($type = 'nolist', $age_dir = 'DESC', $params = []);
	}

	public function ageBetweenSpousesMFList($params = [])
	{
		return $this->_ageBetweenSpousesQuery($type = 'list', $age_dir = 'DESC', $params = []);
	}

	public function ageBetweenSpousesFM($params = [])
	{
		return $this->_ageBetweenSpousesQuery($type = 'nolist', $age_dir = 'ASC', $params = []);
	}

	public function ageBetweenSpousesFMList($params = [])
	{
		return $this->_ageBetweenSpousesQuery($type = 'list', $age_dir = 'ASC', $params = []);
	}

	public function topAgeOfMarriageFamily()
	{
		return $this->_ageOfMarriageQuery('name', 'DESC', ['1']);
	}

	public function topAgeOfMarriage()
	{
		return $this->_ageOfMarriageQuery('age', 'DESC', ['1']);
	}

	public function topAgeOfMarriageFamilies($params = [])
	{
		return $this->_ageOfMarriageQuery('nolist', 'DESC', $params);
	}

	public function topAgeOfMarriageFamiliesList($params = [])
	{
		return $this->_ageOfMarriageQuery('list', 'DESC', $params);
	}

	public function minAgeOfMarriageFamily()
	{
		return $this->_ageOfMarriageQuery('name', 'ASC', ['1']);
	}

	public function minAgeOfMarriage()
	{
		return $this->_ageOfMarriageQuery('age', 'ASC', ['1']);
	}

	public function minAgeOfMarriageFamilies($params = [])
	{
		return $this->_ageOfMarriageQuery('nolist', 'ASC', $params);
	}

	public function minAgeOfMarriageFamiliesList($params = [])
	{
		return $this->_ageOfMarriageQuery('list', 'ASC', $params);
	}

	//
	// Mother only
	//
	public function youngestMother()
	{
		return $this->_parentsQuery('full', 'ASC', 'F');
	}

	public function youngestMotherName()
	{
		return $this->_parentsQuery('name', 'ASC', 'F');
	}

	public function youngestMotherAge($show_years = false)
	{
		return $this->_parentsQuery('age', 'ASC', 'F', $show_years);
	}

	public function oldestMother()
	{
		return $this->_parentsQuery('full', 'DESC', 'F');
	}

	public function oldestMotherName()
	{
		return $this->_parentsQuery('name', 'DESC', 'F');
	}

	public function oldestMotherAge($show_years = false)
	{
		return $this->_parentsQuery('age', 'DESC', 'F', $show_years);
	}

	//
	// Father only
	//
	public function youngestFather()
	{
		return $this->_parentsQuery('full', 'ASC', 'M');
	}

	public function youngestFatherName()
	{
		return $this->_parentsQuery('name', 'ASC', 'M');
	}

	public function youngestFatherAge($show_years = false)
	{
		return $this->_parentsQuery('age', 'ASC', 'M', $show_years);
	}

	public function oldestFather()
	{
		return $this->_parentsQuery('full', 'DESC', 'M');
	}

	public function oldestFatherName()
	{
		return $this->_parentsQuery('name', 'DESC', 'M');
	}

	public function oldestFatherAge($show_years = false)
	{
		return $this->_parentsQuery('age', 'DESC', 'M', $show_years);
	}

	public function totalMarriedMales()
	{
		$n = KT_DB::prepare("SELECT COUNT(DISTINCT f_husb) FROM `##families` WHERE f_file=? AND f_gedcom LIKE '%\\n1 MARR%'")
			->execute([$this->_ged_id])
			->fetchOne()
		;

		return KT_I18N::number($n);
	}

	public function totalMarriedFemales()
	{
		$n = KT_DB::prepare("SELECT COUNT(DISTINCT f_wife) FROM `##families` WHERE f_file=? AND f_gedcom LIKE '%\\n1 MARR%'")
			->execute([$this->_ged_id])
			->fetchOne()
		;

		return KT_I18N::number($n);
	}

// /////////////////////////////////////////////////////////////////////////////
// Family Size                                                               //
// /////////////////////////////////////////////////////////////////////////////

	public function _familyQuery($type = 'full')
	{
		$rows = self::_runSQL(
			' SELECT f_numchil AS tot, f_id AS id' .
			' FROM `##families`' .
			' WHERE' .
			" f_file={$this->_ged_id}" .
			' AND f_numchil = (' .
			'  SELECT max( f_numchil )' .
			'  FROM `##families`' .
			"  WHERE f_file ={$this->_ged_id}" .
			' )' .
			' LIMIT 1'
		);
		if (!isset($rows[0])) {
			return '';
		}
		$row = $rows[0];
		$family = KT_Family::getInstance($row['id']);

		switch ($type) {
			default:
			case 'full':
				if ($family->canDisplayDetails()) {
					$result = $family->format_list('span', false, $family->getFullName());
				} else {
					$result = KT_I18N::translate('This information is private and cannot be shown.');
				}

				break;

			case 'size':
				$result = KT_I18N::number($row['tot']);

				break;

			case 'name':
				$result = '<a href="' . $family->getHtmlUrl() . '">' . $family->getFullName() . '</a>';

				break;
		}

		return $result;
	}

	public function _topTenFamilyQuery($type = 'list', $params = [])
	{
		global $TEXT_DIRECTION;
		if (null !== $params && isset($params[0])) {
			$total = $params[0];
		} else {
			$total = 10;
		}
		$total = (int) $total;
		$rows = self::_runSQL(
			'SELECT f_numchil AS tot, f_id AS id' .
			' FROM `##families`' .
			' WHERE' .
			" f_file={$this->_ged_id}" .
			' ORDER BY tot DESC' .
			' LIMIT ' . $total
		);
		if (!isset($rows[0])) {
			return '';
		}
		if (count($rows) < $total) {
			$total = count($rows);
		}
		$top10 = [];
		for ($c = 0; $c < $total; $c++) {
			$family = KT_Family::getInstance($rows[$c]['id']);
			if ($family->canDisplayDetails()) {
				if ('list' == $type) {
					$top10[] =
						'<li><a href="' . $family->getHtmlUrl() . '">' . $family->getFullName() . '</a> - ' .
						KT_I18N::plural('%s child', '%s children', $rows[$c]['tot'], KT_I18N::number($rows[$c]['tot']));
				} else {
					$top10[] =
						'<a href="' . $family->getHtmlUrl() . '">' . $family->getFullName() . '</a> - ' .
						KT_I18N::plural('%s child', '%s children', $rows[$c]['tot'], KT_I18N::number($rows[$c]['tot']));
				}
			}
		}
		if ('list' == $type) {
			$top10 = join('', $top10);
		} else {
			$top10 = join(';&nbsp; ', $top10);
		}
		if ('rtl' == $TEXT_DIRECTION) {
			$top10 = str_replace(['[', ']', '(', ')', '+'], ['&rlm;[', '&rlm;]', '&rlm;(', '&rlm;)', '&rlm;+'], $top10);
		}
		if ('list' == $type) {
			return '<ul>' . $top10 . '</ul>';
		}

		return $top10;
	}

	public function _ageBetweenSiblingsQuery($type = 'list', $params = [])
	{
		global $TEXT_DIRECTION;
		if (null === $params) {
			$params = [];
		}
		if (isset($params[0])) {
			$total = $params[0];
		} else {
			$total = 10;
		}
		if (isset($params[1])) {
			$one = $params[1];
		} else {
			$one = false;
		} // each family only once if true
		$total = (int) $total;
		$rows = self::_runSQL(
			' SELECT DISTINCT' .
			' link1.l_from AS family,' .
			' link1.l_to AS ch1,' .
			' link2.l_to AS ch2,' .
			' child1.d_julianday2-child2.d_julianday2 AS age' .
			' FROM `##link` AS link1' .
			" LEFT JOIN `##dates` AS child1 ON child1.d_file = {$this->_ged_id}" .
			" LEFT JOIN `##dates` AS child2 ON child2.d_file = {$this->_ged_id}" .
			" LEFT JOIN `##link` AS link2 ON link2.l_file = {$this->_ged_id}" .
			' WHERE' .
			" link1.l_file = {$this->_ged_id} AND" .
			' link1.l_from = link2.l_from AND' .
			" link1.l_type = 'CHIL' AND" .
			' child1.d_gid = link1.l_to AND' .
			" child1.d_fact = 'BIRT' AND" .
			" link2.l_type = 'CHIL' AND" .
			' child2.d_gid = link2.l_to AND' .
			" child2.d_fact = 'BIRT' AND" .
			' child1.d_julianday2 > child2.d_julianday2 AND' .
			' child2.d_julianday2 <> 0 AND' .
			' child1.d_gid <> child2.d_gid' .
			' ORDER BY age DESC' .
			' LIMIT ' . $total
		);
		if (!isset($rows[0])) {
			return '';
		}
		$top10 = [];
		if ($one) {
			$dist = [];
		}
		foreach ($rows as $fam) {
			$family = KT_Family::getInstance($fam['family']);
			$child1 = KT_Person::getInstance($fam['ch1']);
			$child2 = KT_Person::getInstance($fam['ch2']);
			if ('name' == $type) {
				if ($child1->canDisplayDetails() && $child2->canDisplayDetails()) {
					$return = '<a href="' . $child2->getHtmlUrl() . '">' . $child2->getFullName() . '</a> ';
					$return .= KT_I18N::translate('and') . ' ';
					$return .= '<a href="' . $child1->getHtmlUrl() . '">' . $child1->getFullName() . '</a>';
					$return .= ' <a href="' . $family->getHtmlUrl() . '">[' . KT_USER_CAN_EDIT ? KT_I18N::translate('Edit family') : KT_I18N::translate('View family') . ']</a>';
				} else {
					$return = KT_I18N::translate('This information is private and cannot be shown.');
				}

				return $return;
			}
			$age = $fam['age'];
			if ((int) ($age / 365.25) > 0) {
				$age = (int) ($age / 365.25) . 'y';
			} elseif ((int) ($age / 30.4375) > 0) {
				$age = (int) ($age / 30.4375) . 'm';
			} else {
				$age = $age . 'd';
			}
			$age = get_age_at_event($age, true);
			if ('age' == $type) {
				return $age;
			}
			if ('list' == $type) {
				if ($one && !in_array($fam['family'], $dist)) {
					if ($child1->canDisplayDetails() && $child2->canDisplayDetails()) {
						$return = '<li>';
						$return .= '<a href="' . $child2->getHtmlUrl() . '">' . $child2->getFullName() . '</a> ';
						$return .= KT_I18N::translate('and') . ' ';
						$return .= '<a href="' . $child1->getHtmlUrl() . '">' . $child1->getFullName() . '</a>';
						$return .= ' (' . $age . ') ';
						$return .= '<a href="' . $family->getHtmlUrl() . '"><small>' . (KT_USER_CAN_EDIT ? KT_I18N::translate('Edit family') : KT_I18N::translate('View family')) . '</small></a>';
						$return .= '</li>';
						$top10[] = $return;
						$dist[] = $fam['family'];
					}
				} elseif (!$one && $child1->canDisplayDetails() && $child2->canDisplayDetails()) {
					$return = '<li>';
					$return .= '<a href="' . $child2->getHtmlUrl() . '">' . $child2->getFullName() . '</a> ';
					$return .= KT_I18N::translate('and') . ' ';
					$return .= '<a href="' . $child1->getHtmlUrl() . '">' . $child1->getFullName() . '</a>';
					$return .= ' (' . $age . ') ';
					$return .= '<a href="' . $family->getHtmlUrl() . '"><small>' . (KT_USER_CAN_EDIT ? KT_I18N::translate('Edit family') : KT_I18N::translate('View family')) . '</small></a>';
					$return .= '</li>';
					$top10[] = $return;
				}
			} else {
				if ($child1->canDisplayDetails() && $child2->canDisplayDetails()) {
					$return = $child2->format_list('span', false, $child2->getFullName());
					$return .= '<br>' . KT_I18N::translate('and') . '<br>';
					$return .= $child1->format_list('span', false, $child1->getFullName());
					$return .= '<br><a href="' . $family->getHtmlUrl() . '"><small>' . (KT_USER_CAN_EDIT ? KT_I18N::translate('Edit family') : KT_I18N::translate('View family')) . '</small></a>';

					return $return;
				}

				return KT_I18N::translate('This information is private and cannot be shown.');
			}
		}
		if ('list' == $type) {
			$top10 = join('', $top10);
		}
		if ('rtl' == $TEXT_DIRECTION) {
			$top10 = str_replace(['[', ']', '(', ')', '+'], ['&rlm;[', '&rlm;]', '&rlm;(', '&rlm;)', '&rlm;+'], $top10);
		}
		if ('list' == $type) {
			return '<ul>' . $top10 . '</ul>';
		}

		return $top10;
	}

	public function _monthFirstChildQuery($simple = true, $sex = false, $year1 = -1, $year2 = -1, $params = [])
	{
		global $KT_STATS_S_CHART_X, $KT_STATS_S_CHART_Y, $KT_STATS_CHART_COLOR1, $KT_STATS_CHART_COLOR2;
		if (null === $params) {
			$params = [];
		}
		if (isset($params[0])) {
			$total = $params[0];
		} else {
			$total = 10;
		}
		if (isset($params[1])) {
			$one = $params[1];
		} else {
			$one = false;
		} // each family only once if true
		$total = (int) $total;
		if ($year1 >= 0 && $year2 >= 0) {
			$sql_years = " AND (d_year BETWEEN '{$year1}' AND '{$year2}')";
		} else {
			$sql_years = '';
		}
		if ($sex) {
			$sql_sex1 = ', i_sex';
			$sql_sex2 = ' JOIN `##individuals` AS child ON child1.d_file = i_file AND child1.d_gid = child.i_id ';
		} else {
			$sql_sex1 = '';
			$sql_sex2 = '';
		}
		$sql =
			"SELECT d_month{$sql_sex1}, COUNT(*) AS total " .
			'FROM (' .
			" SELECT family{$sql_sex1}, MIN(date) AS d_date, d_month" .
			' FROM (' .
			'  SELECT' .
			'  link1.l_from AS family,' .
			'  link1.l_to AS child,' .
			'  child1.d_julianday2 as date,' .
			'  child1.d_month as d_month' .
			$sql_sex1 .
			'  FROM `##link` AS link1' .
			"  LEFT JOIN `##dates` AS child1 ON child1.d_file = {$this->_ged_id}" .
			$sql_sex2 .
			'  WHERE' .
			"  link1.l_file = {$this->_ged_id} AND" .
			"  link1.l_type = 'CHIL' AND" .
			'  child1.d_gid = link1.l_to AND' .
			"  child1.d_fact = 'BIRT' AND" .
			"  d_type IN ('@#DGREGORIAN@', '@#DJULIAN@') AND" .
			"  child1.d_month <> ''" .
			$sql_years .
			'  ORDER BY date' .
			' ) AS children' .
			' GROUP BY family' .
			') AS first_child ' .
			'GROUP BY d_month';
		if ($sex) {
			$sql .= ', i_sex';
		}
		$rows = self::_runSQL($sql);
		if ($simple) {
			if (isset($params[0]) && '' != $params[0]) {
				$size = strtolower($params[0]);
			} else {
				$size = $KT_STATS_S_CHART_X . 'x' . $KT_STATS_S_CHART_Y;
			}
			if (isset($params[1]) && '' != $params[1]) {
				$color_from = strtolower($params[1]);
			} else {
				$color_from = $KT_STATS_CHART_COLOR1;
			}
			if (isset($params[2]) && '' != $params[2]) {
				$color_to = strtolower($params[2]);
			} else {
				$color_to = $KT_STATS_CHART_COLOR2;
			}
			$sizes = explode('x', $size);
			$tot = 0;
			foreach ($rows as $values) {
				$tot += $values['total'];
			}
			// Beware divide by zero
			if (0 == $tot) {
				return '';
			}
			$text = '';
			foreach ($rows as $values) {
				$counts[] = round(100 * $values['total'] / $tot, 0);

				switch ($values['d_month']) {
					default:
					case 'JAN':
						$values['d_month'] = 1;

						break;

					case 'FEB':
						$values['d_month'] = 2;

						break;

					case 'MAR':
						$values['d_month'] = 3;

						break;

					case 'APR':
						$values['d_month'] = 4;

						break;

					case 'MAY':
						$values['d_month'] = 5;

						break;

					case 'JUN':
						$values['d_month'] = 6;

						break;

					case 'JUL':
						$values['d_month'] = 7;

						break;

					case 'AUG':
						$values['d_month'] = 8;

						break;

					case 'SEP':
						$values['d_month'] = 9;

						break;

					case 'OCT':
						$values['d_month'] = 10;

						break;

					case 'NOV':
						$values['d_month'] = 11;

						break;

					case 'DEC':
						$values['d_month'] = 12;

						break;
				}
				$text .= KT_I18N::translate(ucfirst(strtolower($values['d_month']))) . ' - ' . $values['total'] . '|';
			}
			$chd = self::_array_to_extended_encoding($counts);
			$chl = substr($text, 0, -1);
			$img = '<img src="https://chart.googleapis.com/chart?cht=p3&amp;chd=e:' . $chd . '&amp;chs=' . $size . '&amp;chco=' . $color_from . ',' . $color_to . '&amp;chf=bg,s,ffffff00&amp;chl=' . $chl . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . KT_I18N::translate('Month of birth of first child in a relation') . '" title="' . KT_I18N::translate('Month of birth of first child in a relation') . '" />';

			return str_replace('|', '%7C', $img);
		}
		if (!isset($rows)) {
			return 0;
		}

		return $rows;
	}

	public function largestFamily()
	{
		return $this->_familyQuery('full');
	}

	public function largestFamilySize()
	{
		return $this->_familyQuery('size');
	}

	public function largestFamilyName()
	{
		return $this->_familyQuery('name');
	}

	public function topTenLargestFamily($params = [])
	{
		return $this->_topTenFamilyQuery('nolist', $params);
	}

	public function topTenLargestFamilyList($params = [])
	{
		return $this->_topTenFamilyQuery('list', $params);
	}

	public function chartLargestFamilies($params = [])
	{
		global $KT_STATS_CHART_COLOR1, $KT_STATS_CHART_COLOR2, $KT_STATS_L_CHART_X, $KT_STATS_S_CHART_Y;
		if (null === $params) {
			$params = [];
		}
		if (isset($params[0]) && '' != $params[0]) {
			$size = strtolower($params[0]);
		} else {
			$size = $KT_STATS_L_CHART_X . 'x' . $KT_STATS_S_CHART_Y;
		}
		if (isset($params[1]) && '' != $params[1]) {
			$color_from = strtolower($params[1]);
		} else {
			$color_from = $KT_STATS_CHART_COLOR1;
		}
		if (isset($params[2]) && '' != $params[2]) {
			$color_to = strtolower($params[2]);
		} else {
			$color_to = $KT_STATS_CHART_COLOR2;
		}
		if (isset($params[3]) && '' != $params[3]) {
			$total = strtolower($params[3]);
		} else {
			$total = 10;
		}
		$sizes = explode('x', $size);
		$total = (int) $total;
		$rows = self::_runSQL(
			' SELECT f_numchil AS tot, f_id AS id' .
			' FROM `##families`' .
			" WHERE f_file={$this->_ged_id}" .
			' ORDER BY tot DESC' .
			' LIMIT ' . $total
		);
		if (!isset($rows[0])) {
			return '';
		}
		$tot = 0;
		foreach ($rows as $row) {
			$tot += $row['tot'];
		}
		$chd = '';
		$chl = [];
		foreach ($rows as $row) {
			$family = KT_Family::getInstance($row['id']);
			if ($family->canDisplayDetails()) {
				if (0 == $tot) {
					$per = 0;
				} else {
					$per = round(100 * $row['tot'] / $tot, 0);
				}
				$chd .= self::_array_to_extended_encoding([$per]);
				$chl[] = htmlspecialchars_decode(strip_tags($family->getFullName())) . ' - ' . KT_I18N::number($row['tot']);
			}
		}
		$chl = rawurlencode(join('|', $chl));

		$img = "<img src=\"https://chart.googleapis.com/chart?cht=p3&amp;chd=e:{$chd}&amp;chs={$size}&amp;chco={$color_from},{$color_to}&amp;chf=bg,s,ffffff00&amp;chl={$chl}\" width=\"{$sizes[0]}\" height=\"{$sizes[1]}\" alt=\"" . KT_I18N::translate('Largest families') . '" title="' . KT_I18N::translate('Largest families') . '" />';

		return str_replace('|', '%7C', $img);
	}

	public function totalChildren()
	{
		$rows = self::_runSQL("SELECT SUM(f_numchil) AS tot FROM `##families` WHERE f_file={$this->_ged_id}");
		$row = $rows[0];

		return KT_I18N::number($row['tot']);
	}

	public function averageChildren()
	{
		$rows = self::_runSQL("SELECT AVG(f_numchil) AS tot FROM `##families` WHERE f_file={$this->_ged_id}");
		$row = $rows[0];

		return KT_I18N::number($row['tot'], 2);
	}

	public function _statsChildren($simple = true, $sex = 'BOTH', $year1 = -1, $year2 = -1, $params = [])
	{
		if ($simple) {
			if (isset($params[0]) && '' != $params[0]) {
				$size = strtolower($params[0]);
			} else {
				$size = '220x200';
			}
			$sizes = explode('x', $size);
			$max = 0;
			$rows = self::_runSQL("
				SELECT ROUND(AVG(f_numchil),1) AS num, FLOOR(married.d_year / 100 + 1) AS century
				FROM `##families` AS fam
				JOIN `##dates` AS married ON (married.d_file = fam.f_file AND married.d_gid = fam.f_id)
				WHERE
					f_numchil > 0  AND
					fam.f_file = {$this->_ged_id} AND
					married.d_fact IN ('MARR', '_NMR') AND
					married.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@')
				GROUP BY century
				ORDER BY century;
			");

			if (empty($rows)) {
				return '';
			}

			foreach ($rows as $values) {
				if ($max < $values['num']) {
					$max = $values['num'];
				}
			}

			// Beware divide by zero
			if (0 == $max) {
				return '';
			}
			foreach ($rows as $values) {
				$data[] = [
					'category' => self::_centuryName($values['century']),
					'count' => $values['num'],
					'percent' => KT_I18N::number($values['num'], 1),
					'color' => 'd',
					'type' => $values['century'],
				];
			}

			return json_encode($data);
		}
		if ('M' == $sex) {
			$sql =
				'SELECT num, COUNT(*) AS total FROM ' .
				'(SELECT count(i_sex) AS num FROM `##link` ' .
				'LEFT OUTER JOIN `##individuals` ' .
				"ON l_from=i_id AND l_file=i_file AND i_sex='M' AND l_type='FAMC' " .
				"JOIN `##families` ON f_file=l_file AND f_id=l_to WHERE f_file={$this->_ged_id} GROUP BY l_to" .
				') boys' .
				' GROUP BY num' .
				' ORDER BY num';
		} elseif ('F' == $sex) {
			$sql =
				'SELECT num, COUNT(*) AS total FROM ' .
				'(SELECT count(i_sex) AS num FROM `##link` ' .
				'LEFT OUTER JOIN `##individuals` ' .
				"ON l_from=i_id AND l_file=i_file AND i_sex='F' AND l_type='FAMC' " .
				"JOIN `##families` ON f_file=l_file AND f_id=l_to WHERE f_file={$this->_ged_id} GROUP BY l_to" .
				') girls' .
				' GROUP BY num' .
				' ORDER BY num';
		} else {
			$sql = 'SELECT f_numchil, COUNT(*) AS total FROM `##families` ';
			if ($year1 >= 0 && $year2 >= 0) {
				$sql .=
					"AS fam LEFT JOIN `##dates` AS married ON married.d_file = {$this->_ged_id}"
					. ' WHERE'
					. ' married.d_gid = fam.f_id AND'
					. " fam.f_file = {$this->_ged_id} AND"
					. " married.d_fact = 'MARR' AND"
					. " married.d_year BETWEEN '{$year1}' AND '{$year2}'";
			} else {
				$sql .= "WHERE f_file={$this->_ged_id}";
			}
			$sql .= ' GROUP BY f_numchil';
		}
		$rows = self::_runSQL($sql);
		if (!isset($rows)) {
			return 0;
		}

		return $rows;
	}

	public function statsChildren($params = [])
	{
		return $this->_statsChildren($simple = true, $sex = 'BOTH', $year1 = -1, $year2 = -1, $params = []);
	}

	public function topAgeBetweenSiblingsName($params = [])
	{
		return $this->_ageBetweenSiblingsQuery($type = 'name', $params = []);
	}

	public function topAgeBetweenSiblings($params = [])
	{
		return $this->_ageBetweenSiblingsQuery($type = 'age', $params = []);
	}

	public function topAgeBetweenSiblingsFullName($params = [])
	{
		return $this->_ageBetweenSiblingsQuery($type = 'nolist', $params = []);
	}

	public function topAgeBetweenSiblingsList($params = [])
	{
		return $this->_ageBetweenSiblingsQuery($type = 'list', $params = []);
	}

	public function noChildrenFamilies()
	{
		$rows = self::_runSQL("
			SELECT COUNT(*) AS tot
			FROM `##families` AS fam
			WHERE
				f_numchil = 0 AND
				fam.f_file = {$this->_ged_id}
		");
		$row = $rows[0];

		return $row['tot'];
	}

	public function noChildrenFamiliesList($params = [])
	{
		global $TEXT_DIRECTION;
		if (isset($params[0]) && '' != $params[0]) {
			$type = strtolower($params[0]);
		} else {
			$type = 'list';
		}
		$rows = self::_runSQL(
			' SELECT f_id AS family' .
			' FROM `##families` AS fam' .
			" WHERE f_numchil = 0 AND fam.f_file = {$this->_ged_id}"
		);
		if (!isset($rows[0])) {
			return '';
		}
		$top10 = [];
		foreach ($rows as $row) {
			$family = KT_Family::getInstance($row['family']);
			if ($family->canDisplayDetails()) {
				if ('list' == $type) {
					$top10[] = '<li><a href="' . $family->getHtmlUrl() . '">' . $family->getFullName() . '</a></li>';
				} else {
					$top10[] = '<a href="' . $family->getHtmlUrl() . '">' . $family->getFullName() . '</a>';
				}
			}
		}
		if ('list' == $type) {
			$top10 = join('', $top10);
		} else {
			$top10 = join(';&nbsp; ', $top10);
		}
		if ('rtl' == $TEXT_DIRECTION) {
			$top10 = str_replace(['[', ']', '(', ')', '+'], ['&rlm;[', '&rlm;]', '&rlm;(', '&rlm;)', '&rlm;+'], $top10);
		}
		if ('list' == $type) {
			return '<ul>' . $top10 . '</ul>';
		}

		return $top10;
	}

	public function chartNoChildrenFamilies($params = [])
	{
		if (isset($params[0]) && '' != $params[0]) {
			$size = strtolower($params[0]);
		} else {
			$size = '220x200';
		}
		if (isset($params[1]) && '' != $params[1]) {
			$year1 = $params[1];
		} else {
			$year1 = -1;
		}
		if (isset($params[2]) && '' != $params[2]) {
			$year2 = $params[2];
		} else {
			$year2 = -1;
		}
		if ($year1 >= 0 && $year2 >= 0) {
			$years = " married.d_year BETWEEN '{$year1}' AND '{$year2}' AND";
		} else {
			$years = '';
		}
		$max = 0;
		$tot = 0;
		KT_DB::exec("
			CREATE TEMPORARY TABLE family_dates
			SELECT fam.*, FLOOR(married.d_year/100+1) AS century
			FROM `##families` AS fam
			JOIN `##dates` AS married ON (married.d_file = fam.f_file AND married.d_gid = fam.f_id)
			WHERE
				f_numchil = 0 AND
				fam.f_file = {$this->_ged_id} AND
				{$years}
				married.d_fact = 'MARR' AND
				married.d_type IN ('@#DGREGORIAN@', '@#DJULIAN@')
			GROUP BY fam.f_id, married.d_year ;
		");
		$rows = self::_runSQL('
			SELECT count(*) AS count, century
			FROM family_dates
			GROUP BY century;
		');

		if (empty($rows)) {
			return '';
		}

		foreach ($rows as $values) {
			$tot += $values['count'];
		}

		// Beware divide by zero
		if (0 == $tot) {
			return '';
		}
		foreach ($rows as $values) {
			$data[] = [
				'category' => self::_centuryName($values['century']),
				'count' => $values['count'],
				'percent' => KT_I18N::number($values['count']),
				'color' => 'd',
				'type' => $values['century'],
			];
		}

		return json_encode($data);
	}

	public function totalChildrenTable()
	{
		$rows = self::_runSQL("
			Select *
			FROM `##families` AS fam
			WHERE
				f_numchil > 0 AND
				fam.f_file = {$this->_ged_id};
		");

		if (empty($rows)) {
			return '';
		}

		foreach ($rows as $row) {
			$family = KT_Family::getInstance($row['f_id']);
			$list[] = clone $family;
		}

		return $list;
	}

	public function totalNoChildrenTable()
	{
		$rows = self::_runSQL("
			Select *
			FROM `##families` AS fam
			WHERE
				f_numchil = 0 AND
				fam.f_file = {$this->_ged_id};
		");

		if (empty($rows)) {
			return '';
		}

		foreach ($rows as $row) {
			$family = KT_Family::getInstance($row['f_id']);
			$list[] = clone $family;
		}

		return $list;
	}

	public function _topTenGrandFamilyQuery($type = 'list', $params = [])
	{
		global $TEXT_DIRECTION;
		if (null !== $params && isset($params[0])) {
			$total = $params[0];
		} else {
			$total = 10;
		}
		$total = (int) $total;
		$rows = self::_runSQL(
			'SELECT COUNT(*) AS tot, f_id AS id' .
			' FROM `##families`' .
			" JOIN `##link` AS children ON children.l_file = {$this->_ged_id}" .
			" JOIN `##link` AS mchildren ON mchildren.l_file = {$this->_ged_id}" .
			" JOIN `##link` AS gchildren ON gchildren.l_file = {$this->_ged_id}" .
			' WHERE' .
			" f_file={$this->_ged_id} AND" .
			' children.l_from=f_id AND' .
			" children.l_type='CHIL' AND" .
			' children.l_to=mchildren.l_from AND' .
			" mchildren.l_type='FAMS' AND" .
			' mchildren.l_to=gchildren.l_from AND' .
			" gchildren.l_type='CHIL'" .
			' GROUP BY id' .
			' ORDER BY tot DESC' .
			' LIMIT ' . $total
		);
		if (!isset($rows[0])) {
			return '';
		}
		$top10 = [];
		foreach ($rows as $row) {
			$family = KT_Family::getInstance($row['id']);
			if ($family->canDisplayDetails()) {
				if ('list' == $type) {
					$top10[] =
						'<li><a href="' . $family->getHtmlUrl() . '">' . $family->getFullName() . '</a> - ' .
						KT_I18N::plural('%s grandchild', '%s grandchildren', $row['tot'], KT_I18N::number($row['tot']));
				} else {
					$top10[] =
						'<a href="' . $family->getHtmlUrl() . '">' . $family->getFullName() . '</a> - ' .
						KT_I18N::plural('%s grandchild', '%s grandchildren', $row['tot'], KT_I18N::number($row['tot']));
				}
			}
		}
		if ('list' == $type) {
			$top10 = join('', $top10);
		} else {
			$top10 = join(';&nbsp; ', $top10);
		}
		if ('rtl' == $TEXT_DIRECTION) {
			$top10 = str_replace(['[', ']', '(', ')', '+'], ['&rlm;[', '&rlm;]', '&rlm;(', '&rlm;)', '&rlm;+'], $top10);
		}
		if ('list' == $type) {
			return '<ul>' . $top10 . '</ul>';
		}

		return $top10;
	}

	public function topTenLargestGrandFamily($params = [])
	{
		return $this->_topTenGrandFamilyQuery('nolist', $params);
	}

	public function topTenLargestGrandFamilyList($params = [])
	{
		return $this->_topTenGrandFamilyQuery('list', $params);
	}

// /////////////////////////////////////////////////////////////////////////////
// Surnames                                                                  //
// /////////////////////////////////////////////////////////////////////////////

	public static function _commonSurnamesQuery($type = 'list', $show_tot = false, $params = [])
	{
		global $SURNAME_LIST_STYLE, $GEDCOM;

		$ged_id = get_id_from_gedcom($GEDCOM);
		if (is_array($params) && isset($params[0]) && '' != $params[0]) {
			$threshold = strtolower($params[0]);
		} else {
			$threshold = get_gedcom_setting($ged_id, 'COMMON_NAMES_THRESHOLD');
		}
		if (is_array($params) && isset($params[1]) && '' != $params[1] && $params[1] >= 0) {
			$maxtoshow = strtolower($params[1]);
		} else {
			$maxtoshow = false;
		}
		if (is_array($params) && isset($params[2]) && '' != $params[2]) {
			$sorting = strtolower($params[2]);
		} else {
			$sorting = 'alpha';
		}
		$surname_list = get_common_surnames($threshold);
		if (0 == count($surname_list)) {
			return '';
		}
		uasort($surname_list, ['KT_Stats', '_name_total_rsort']);
		if ($maxtoshow > 0) {
			$surname_list = array_slice($surname_list, 0, $maxtoshow);
		}

		switch ($sorting) {
			default:
			case 'alpha':
				uksort($surname_list, 'utf8_strcasecmp');

				break;

			case 'count':
				uasort($surname_list, ['KT_Stats', '_name_total_sort']);

				break;

			case 'rcount':
				uasort($surname_list, ['KT_Stats', '_name_total_rsort']);

				break;
		}

		// Note that we count/display SPFX SURN, but sort/group under just SURN
		$surnames = [];
		foreach (array_keys($surname_list) as $surname) {
			$surnames = array_merge($surnames, KT_Query_Name::surnames($surname, '', false, false, KT_GED_ID));
		}

		return format_surname_list($surnames, 'list' == $type ? 1 : 2, $show_tot, 'indilist.php');
	}

	public function getCommonSurname()
	{
		$surnames = array_keys(get_top_surnames($this->_ged_id, 1, 1));

		return array_shift($surnames);
	}

	public static function commonSurnames($params = ['', '', 'alpha'])
	{
		return self::_commonSurnamesQuery('nolist', false, $params);
	}

	public static function commonSurnamesTotals($params = ['', '', 'rcount'])
	{
		return self::_commonSurnamesQuery('nolist', true, $params);
	}

	public static function commonSurnamesList($params = ['', '', 'alpha'])
	{
		return self::_commonSurnamesQuery('list', false, $params);
	}

	public static function commonSurnamesListTotals($params = ['', '', 'rcount'])
	{
		return self::_commonSurnamesQuery('list', true, $params);
	}

	public function chartCommonSurnames($params = [])
	{
		// parameter example: chartCommonSurnames(array(25,7))
		if (isset($params[0]) && '' != $params[0]) {
			$threshold = strtolower($params[0]);
		} else {
			$threshold = get_gedcom_setting($this->_ged_id, 'COMMON_NAMES_THRESHOLD');
		}
		if (isset($params[1]) && '' != $params[1]) {
			$maxtoshow = strtolower($params[1]);
		} else {
			$maxtoshow = 7;
		}
		$tot_indi = $this->_totalIndividuals();
		$surnames = get_common_surnames($threshold);
		if (count($surnames) <= 0) {
			return '';
		}
		$SURNAME_TRADITION = get_gedcom_setting(KT_GED_ID, 'SURNAME_TRADITION');
		uasort($surnames, ['KT_Stats', '_name_total_rsort']);
		$surnames = array_slice($surnames, 0, $maxtoshow);
		$all_surnames = [];
		foreach (array_keys($surnames) as $n => $surname) {
			if ($n >= $maxtoshow) {
				break;
			}
			$all_surnames = array_merge($all_surnames, KT_Query_Name::surnames(utf8_strtoupper($surname), '', false, false, KT_GED_ID));
		}

		$tot_indi = $this->_totalIndividuals();
		$tot = 0;
		$per = 0;
		foreach ($surnames as $indexval => $surname) {
			$tot += $surname['match'];
		}

		foreach ($all_surnames as $surn => $surns) {
			$count_per = 0;
			$max_name = 0;
			foreach ($surns as $spfxsurn => $indis) {
				$per = count($indis);
				$count_per += $per;
				// select most common surname from all variants
				if ($per > $max_name) {
					$max_name = $per;
					$top_name = $spfxsurn;
				}
			}

			switch ($SURNAME_TRADITION) {
				case 'polish':
					// most common surname should be in male variant (Kowalski, not Kowalska)
					$top_name = preg_replace(['/ska$/', '/cka$/', '/dzka$/', '/żka$/'], ['ski', 'cki', 'dzki', 'żki'], $top_name);
			}
			$data[] = [
				'category' => $top_name,
				'count' => $count_per,
				'percent' => KT_I18N::number($count_per) . ' (' . KT_I18N::number(100 * $count_per / $tot_indi, 1) . '%)',
				'color' => 'd',
				'type' => $top_name,
			];
		}

		return json_encode($data);
	}

// /////////////////////////////////////////////////////////////////////////////
// Given Names                                                               //
// /////////////////////////////////////////////////////////////////////////////

	/*
	* Most Common Given Names Block
	* Original block created by kiwi
	*/
	public static function _commonGivenQuery($sex = 'B', $type = 'list', $show_tot = false, $params = [])
	{
		global $TEXT_DIRECTION, $GEDCOM;
		static $sort_types = ['count' => 'asort', 'rcount' => 'arsort', 'alpha' => 'ksort', 'ralpha' => 'krsort'];
		static $sort_flags = ['count' => SORT_NUMERIC, 'rcount' => SORT_NUMERIC, 'alpha' => SORT_STRING, 'ralpha' => SORT_STRING];

		if (is_array($params) && isset($params[0]) && '' != $params[0] && $params[0] >= 0) {
			$threshold = strtolower($params[0]);
		} else {
			$threshold = 1;
		}
		if (is_array($params) && isset($params[1]) && '' != $params[1] && $params[1] >= 0) {
			$maxtoshow = strtolower($params[1]);
		} else {
			$maxtoshow = 10;
		}
		if (is_array($params) && isset($params[2]) && '' != $params[2] && isset($sort_types[strtolower($params[2])])) {
			$sorting = strtolower($params[2]);
		} else {
			$sorting = 'rcount';
		}

		switch ($sex) {
			case 'M':
				$sex_sql = "i_sex='M'";

				break;

			case 'F':
				$sex_sql = "i_sex='F'";

				break;

			case 'U':
				$sex_sql = "i_sex='U'";

				break;

			case 'B':
				$sex_sql = "i_sex<>'U'";

				break;
		}
		$ged_id = get_id_from_gedcom($GEDCOM);

		$rows = KT_DB::prepare("
			SELECT n_givn, COUNT(DISTINCT n_id) AS num
			FROM `##name`
			JOIN `##individuals` ON (n_id = i_id AND n_file = i_file)
			WHERE n_file = {$ged_id}
			AND n_type NOT IN ('_MARNM', '_AKA')
			AND n_givn NOT IN ('@P.N.', '')
			AND LENGTH(n_givn) > 1
			AND {$sex_sql}
			GROUP BY n_givn
		")->fetchAll();

		$nameList = [];

		foreach ($rows as $row) {
			// Split “John Thomas” into “John” and “Thomas” and count against both totals
			foreach (explode(' ', $row->n_givn) as $given) {
				// Exclude initials and particles.
				if (!preg_match('/^([A-Z]|[a-z]{1,3})$/', $given)) {
					if (array_key_exists($given, $nameList)) {
						$nameList[$given] += $row->num;
					} else {
						$nameList[$given] = $row->num;
					}
				}
			}
		}
		arsort($nameList, SORT_NUMERIC);
		$nameList = array_slice($nameList, 0, $maxtoshow);
		if (0 == count($nameList)) {
			return '';
		}
		if ('chart' == $type) {
			return $nameList;
		}
		$common = [];
		foreach ($nameList as $given => $total) {
			if (-1 !== $maxtoshow) {
				if ($maxtoshow-- <= 0) {
					break;
				}
			}
			if ($total < $threshold) {
				break;
			}
			if ($show_tot) {
				$tot = '&nbsp;(' . KT_I18N::number($total) . ')';
			} else {
				$tot = '';
			}

			switch ($type) {
				case 'table':
					$common[] = '<tr><td>' . $given . '</td><td>' . KT_I18N::number($total) . '</td><td>' . $total . '</td></tr>';

					break;

				case 'list':
					$common[] = '<li><span dir="auto">' . $given . '</span>' . $tot . '</li>';

					break;

				case 'nolist':
					$common[] = '<span dir="auto">' . $given . '</span>' . $tot;

					break;
			}
		}
		if ($common) {
			switch ($type) {
				case 'table':
					global $controller;
					$table_id = 'ID' . (int) (microtime(true) * 1000000); // lists requires a unique ID in case there are multiple lists per page
					$controller
						->addExternalJavascript(KT_DATATABLES_JS)
						->addInlineJavascript('
					jQuery("#' . $table_id . '").dataTable({
						"sDom": \'t\',
						"bAutoWidth":false,
						"bPaginate": false,
						"bLengthChange": false,
						"bFilter": false,
						"bInfo": false,
						"bJQueryUI": true,
						"aaSorting": [[1,"desc"]],
						"aoColumns": [
							/* 0-name */ {},
							/* 1-count */ { sClass:"center", iDataSort:2},
							/* 2-COUNT */ { bVisible:false}
						]
					});
					jQuery("#' . $table_id . '").css("visibility", "visible");
				')
					;
					$lookup = ['M' => KT_I18N::translate('Male'), 'F' => KT_I18N::translate('Female'), 'U' => KT_I18N::translate_c('unknown gender', 'Unknown'), 'B' => KT_I18N::translate('All')];

					return '<table id="' . $table_id . '" class="givn-list"><thead><tr><th class="ui-state-default" colspan="3">' . $lookup[$sex] . '</th></tr><tr><th>' . KT_I18N::translate('Name') . '</th><th>count</th><th>COUNT</th></tr></thead><tbody>' . join('', $common) . '</tbody></table>';

				case 'list':
					return '<ul>' . join('', $common) . '</ul>';

				case 'nolist':
					return join(KT_I18N::$list_separator, $common);
			}
		} else {
			return '';
		}
	}

	public static function commonGiven($params = [1, 10, 'alpha'])
	{
		return self::_commonGivenQuery('B', 'nolist', false, $params);
	}

	public static function commonGivenTotals($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('B', 'nolist', true, $params);
	}

	public static function commonGivenList($params = [1, 10, 'alpha'])
	{
		return self::_commonGivenQuery('B', 'list', false, $params);
	}

	public static function commonGivenListTotals($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('B', 'list', true, $params);
	}

	public static function commonGivenTable($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('B', 'table', false, $params);
	}

	public static function commonGivenFemale($params = [1, 10, 'alpha'])
	{
		return self::_commonGivenQuery('F', 'nolist', false, $params);
	}

	public static function commonGivenFemaleTotals($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('F', 'nolist', true, $params);
	}

	public static function commonGivenFemaleList($params = [1, 10, 'alpha'])
	{
		return self::_commonGivenQuery('F', 'list', false, $params);
	}

	public static function commonGivenFemaleListTotals($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('F', 'list', true, $params);
	}

	public static function commonGivenFemaleTable($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('F', 'table', false, $params);
	}

	public static function commonGivenMale($params = [1, 10, 'alpha'])
	{
		return self::_commonGivenQuery('M', 'nolist', false, $params);
	}

	public static function commonGivenMaleTotals($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('M', 'nolist', true, $params);
	}

	public static function commonGivenMaleList($params = [1, 10, 'alpha'])
	{
		return self::_commonGivenQuery('M', 'list', false, $params);
	}

	public static function commonGivenMaleListTotals($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('M', 'list', true, $params);
	}

	public static function commonGivenMaleTable($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('M', 'table', false, $params);
	}

	public static function commonGivenUnknown($params = [1, 10, 'alpha'])
	{
		return self::_commonGivenQuery('U', 'nolist', false, $params);
	}

	public static function commonGivenUnknownTotals($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('U', 'nolist', true, $params);
	}

	public static function commonGivenUnknownList($params = [1, 10, 'alpha'])
	{
		return self::_commonGivenQuery('U', 'list', false, $params);
	}

	public static function commonGivenUnknownListTotals($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('U', 'list', true, $params);
	}

	public static function commonGivenUnknownTable($params = [1, 10, 'rcount'])
	{
		return self::_commonGivenQuery('U', 'table', false, $params);
	}

	public function chartCommonGiven($params = [])
	{
		// parameter example: chartCommonSurnames(array(25,7))
		if (isset($params[0]) && '' != $params[0]) {
			$threshold = strtolower($params[0]);
		} else {
			$threshold = get_gedcom_setting($this->_ged_id, 'COMMON_NAMES_THRESHOLD');
		}
		if (isset($params[1]) && '' != $params[1]) {
			$maxtoshow = strtolower($params[1]);
		} else {
			$maxtoshow = 7;
		}
		$tot_indi = $this->_totalIndividuals();
		$given = self::_commonGivenQuery('B', 'chart');
		if (!is_array($given)) {
			return '';
		}
		$given = array_slice($given, 0, $maxtoshow);
		if (count($given) <= 0) {
			return '';
		}
		foreach ($given as $givn => $count) {
			$data[] = [
				'category' => $givn,
				'count' => $count,
				'percent' => KT_I18N::number($count) . ' (' . KT_I18N::number(100 * $count / $tot_indi, 1) . '%)',
				'color' => 'd',
				'type' => $givn,
			];
		}

		return json_encode($data);
	}

// /////////////////////////////////////////////////////////////////////////////
// Users                                                                     //
// /////////////////////////////////////////////////////////////////////////////

	public static function _usersLoggedIn($type = 'nolist')
	{
		$content = '';
		// List active users
		$NumAnonymous = 0;
		$loggedusers = [];
		$x = get_logged_in_users();
		foreach ($x as $user_id => $user_name) {
			if (KT_USER_IS_ADMIN || get_user_setting($user_id, 'visibleonline')) {
				$loggedusers[$user_id] = $user_name;
			} else {
				$NumAnonymous++;
			}
		}
		$LoginUsers = count($loggedusers);
		if ((0 == $LoginUsers) && (0 == $NumAnonymous)) {
			return KT_I18N::translate('No logged-in and no anonymous users');
		}
		if ($NumAnonymous > 0) {
			$content .= '<b>' . KT_I18N::plural('%d anonymous logged-in user', '%d anonymous logged-in users', $NumAnonymous, $NumAnonymous) . '</b>';
		}
		if ($LoginUsers > 0) {
			if ($NumAnonymous) {
				if ('list' == $type) {
					$content .= '<br><br>';
				} else {
					$content .= ' ' . KT_I18N::translate('and') . ' ';
				}
			}
			$content .= '<b>' . KT_I18N::plural('%d logged-in user', '%d logged-in users', $LoginUsers, $LoginUsers) . '</b>';
			if ('list' == $type) {
				$content .= '<ul>';
			} else {
				$content .= ': ';
			}
		}
		if (KT_USER_ID) {
			foreach ($loggedusers as $user_id => $user_name) {
				if ('list' == $type) {
					$content .= '<li>';
				}
				$content .= htmlspecialchars(getUserFullName($user_id)) . " - {$user_name}";
				if (KT_USER_ID != $user_id && 'none' != get_user_setting($user_id, 'contactmethod')) {
					if ('list' == $type) {
						$content .= '<br>';
					}
					$content .= '<a class="fa-envelope-o" href="message.php?to=' . $user_id . '&amp;url=' . addslashes(urlencode(get_query_url())) . '"  title="' . KT_I18N::translate('Send Message') . '"></a>';
				}
				if ('list' == $type) {
					$content .= '</li>';
				}
			}
		}
		if ('list' == $type) {
			$content .= '</ul>';
		}

		return $content;
	}

	public static function _usersLoggedInTotal($type = 'all')
	{
		$anon = 0;
		$visible = 0;
		$x = get_logged_in_users();
		foreach ($x as $user_id => $user_name) {
			if (KT_USER_IS_ADMIN || get_user_setting($user_id, 'visibleonline')) {
				$visible++;
			} else {
				$anon++;
			}
		}
		if ('anon' == $type) {
			return $anon;
		}
		if ('visible' == $type) {
			return $visible;
		}

		return $visible + $anon;
	}

	public static function usersLoggedIn()
	{
		return self::_usersLoggedIn('nolist');
	}

	public static function usersLoggedInList()
	{
		return self::_usersLoggedIn('list');
	}

	public static function usersLoggedInTotal()
	{
		return self::_usersLoggedInTotal('all');
	}

	public static function usersLoggedInTotalAnon()
	{
		return self::_usersLoggedInTotal('anon');
	}

	public static function usersLoggedInTotalVisible()
	{
		return self::_usersLoggedInTotal('visible');
	}

	public static function userID()
	{
		return getUserId();
	}

	public static function userName($params = [])
	{
		if (getUserId()) {
			return getUserName();
		}
		if (is_array($params) && isset($params[0]) && '' != $params[0]) {
			// if #username:visitor# was specified, then "visitor" will be returned when the user is not logged in
			return $params[0];
		}

		return null;
	}

	public static function userFullName()
	{
		return getUserFullName(getUserId());
	}

	public static function _getLatestUserData($type = 'userid', $params = [])
	{
		global $DATE_FORMAT, $TIME_FORMAT;
		static $user_id = null;

		if (null === $user_id) {
			$user_id = get_newest_registered_user();
		}

		switch ($type) {
			default:
			case 'userid':
				return $user_id;

			case 'username':
				return get_user_name($user_id);

			case 'fullname':
				return getUserFullName($user_id);

			case 'regdate':
				if (is_array($params) && isset($params[0]) && '' != $params[0]) {
					$datestamp = $params[0];
				} else {
					$datestamp = $DATE_FORMAT;
				}

				return timestamp_to_gedcom_date(get_user_setting($user_id, 'reg_timestamp'))->Display(false, $datestamp);

			case 'regtime':
				if (is_array($params) && isset($params[0]) && '' != $params[0]) {
					$datestamp = $params[0];
				} else {
					$datestamp = str_replace('%', '', $TIME_FORMAT);
				}

				return date($datestamp, get_user_setting($user_id, 'reg_timestamp'));

			case 'loggedin':
				if (is_array($params) && isset($params[0]) && '' != $params[0]) {
					$yes = $params[0];
				} else {
					$yes = KT_I18N::translate('Yes');
				}
				if (is_array($params) && isset($params[1]) && '' != $params[1]) {
					$no = $params[1];
				} else {
					$no = KT_I18N::translate('No');
				}

				return KT_DB::prepare('SELECT SQL_NO_CACHE 1 FROM `##session` WHERE user_id=? LIMIT 1')->execute([$user_id])->fetchOne() ? $yes : $no;
		}
	}

	public static function latestUserId()
	{
		return self::_getLatestUserData('userid');
	}

	public static function latestUserName()
	{
		return self::_getLatestUserData('username');
	}

	public static function latestUserFullName()
	{
		return self::_getLatestUserData('fullname');
	}

	public static function latestUserRegDate($params = [])
	{
		return self::_getLatestUserData('regdate', $params);
	}

	public static function latestUserRegTime($params = [])
	{
		return self::_getLatestUserData('regtime', $params);
	}

	public static function latestUserLoggedin($params = [])
	{
		return self::_getLatestUserData('loggedin', $params);
	}

// /////////////////////////////////////////////////////////////////////////////
// Contact                                                                   //
// /////////////////////////////////////////////////////////////////////////////

	public function contactWebmaster()
	{
		return user_contact_link(get_gedcom_setting($this->_ged_id, 'WEBMASTER_USER_ID'));
	}

	public function contactGedcom()
	{
		return user_contact_link(get_gedcom_setting($this->_ged_id, 'CONTACT_USER_ID'));
	}

// /////////////////////////////////////////////////////////////////////////////
// Date & Time                                                               //
// /////////////////////////////////////////////////////////////////////////////

	public static function serverDate()
	{
		return timestamp_to_gedcom_date(KT_TIMESTAMP)->Display(false);
	}

	public static function serverTime()
	{
		return date('g:i a');
	}

	public static function serverTime24()
	{
		return date('G:i');
	}

	public static function serverTimezone()
	{
		return date('T');
	}

	public static function browserDate()
	{
		return timestamp_to_gedcom_date(KT_CLIENT_TIMESTAMP)->Display(false);
	}

	public static function browserTime()
	{
		return date('g:i a', KT_CLIENT_TIMESTAMP);
	}

	public static function browserTime24()
	{
		return date('G:i', KT_CLIENT_TIMESTAMP);
	}

	public static function browserTimezone()
	{
		return date('T', KT_CLIENT_TIMESTAMP);
	}

// /////////////////////////////////////////////////////////////////////////////
// Tools                                                                     //
// /////////////////////////////////////////////////////////////////////////////

	// Older versions allowed access to all constants and globals.
	// Newer version just allow access to these values:
	public static function KT_VERSION()
	{
		return KT_VERSION;
	}

	public static function KT_VERSION_TEXT()
	{
		return KT_VERSION_TEXT;
	}

	public static function hitCount($params = [])
	{
		return self::_getHitCount(null, $params);
	}

	public static function hitCountUser($params = [])
	{
		return self::_getHitCount('index.php', $params);
	}

	public static function hitCountIndi($params = [])
	{
		return self::_getHitCount('individual.php', $params);
	}

	public static function hitCountFam($params = [])
	{
		return self::_getHitCount('family.php', $params);
	}

	public static function hitCountSour($params = [])
	{
		return self::_getHitCount('source.php', $params);
	}

	public static function hitCountRepo($params = [])
	{
		return self::_getHitCount('repo.php', $params);
	}

	public static function hitCountNote($params = [])
	{
		return self::_getHitCount('note.php', $params);
	}

	public static function hitCountObje($params = [])
	{
		return self::_getHitCount('mediaviewer.php', $params);
	}

	// Leave for backwards compatibility? Anybody using this?
	public static function _getEventType($type)
	{
		$eventTypes = [
			'BIRT' => KT_I18N::translate('birth'),
			'DEAT' => KT_I18N::translate('death'),
			'MARR' => KT_I18N::translate('marriage'),
			'ADOP' => KT_I18N::translate('adoption'),
			'BURI' => KT_I18N::translate('burial'),
			'CENS' => KT_I18N::translate('census added'),
		];
		if (isset($eventTypes[$type])) {
			return $eventTypes[$type];
		}

		return false;
	}

	// http://bendodson.com/news/google-extended-encoding-made-easy/
	public static function _array_to_extended_encoding($a)
	{
		$xencoding = KT_GOOGLE_CHART_ENCODING;

		if (!is_array($a)) {
			$a = [$a];
		}
		$encoding = '';
		foreach ($a as $value) {
			if ($value < 0) {
				$value = 0;
			}
			$first = (int) ($value / 64);
			$second = $value % 64;
			$encoding .= $xencoding[(int) $first] . $xencoding[(int) $second];
		}

		return $encoding;
	}

	public static function _name_total_sort($a, $b)
	{
		return $a['match'] - $b['match'];
	}

	public static function _name_total_rsort($a, $b)
	{
		return $b['match'] - $a['match'];
	}

	public static function _runSQL($sql)
	{
		static $cache = [];
		$id = md5($sql);
		if (isset($cache[$id])) {
			return $cache[$id];
		}
		$rows = KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
		$cache[$id] = $rows;

		return $rows;
	}

	/**
	 * Find the favorites for the tree.
	 *
	 * @return string
	 */
	public function gedcomFavorites()
	{
		if (KT_Module::getModuleByName('gedcom_favorites')) {
			$block = new gedcom_favorites_KT_Module();

			return $block->getBlock(0, false);
		}

		return '';
	}

	/**
	 * Find the favorites for the user.
	 *
	 * @return string
	 */
	public function userFavorites()
	{
		if (KT_USER_ID && KT_Module::getModuleByName('user_favorites')) {
			$block = new widget_favorites_KT_Module();

			return $block->getBlock(0, false);
		}

		return '';
	}

	/**
	 * Find the number of favorites for the tree.
	 *
	 * @return int
	 */
	public function totalGedcomFavorites()
	{
		if (KT_Module::getModuleByName('gedcom_favorites')) {
			return count(gedcom_favorites_KT_Module::getFavorites($this->tree->getTreeId()));
		}

		return 0;
	}

	/**
	 * Find the number of favorites for the user.
	 *
	 * @return int
	 */
	public function totalUserFavorites()
	{
		if (KT_Module::getModuleByName('user_favorites')) {
			return count(widget_favorites_KT_Module::getFavorites(Auth::id()));
		}

		return 0;
	}

	// /////////////////////////////////////////////////////////////////////////////
	// Other blocks                                                              //
	// example of use: #callBlock:block_name#                                    //
	// /////////////////////////////////////////////////////////////////////////////

	public static function callBlock($params = [])
	{
		if (null === $params) {
			return '';
		}
		if (isset($params[0]) && '' != $params[0]) {
			$block = $params[0];
		} else {
			return '';
		}
		$all_blocks = [];
		foreach (KT_Module::getActiveBlocks() as $name => $active_block) {
			if ($active_block->isGedcomBlock()) {
				$all_blocks[$name] = $active_block;
			}
		}
		if (!array_key_exists($block, $all_blocks) || 'html' == $block) {
			return '';
		}
		$class_name = $block . '_KT_Module';

		// Build the config array
		array_shift($params);
		$cfg = [];
		foreach ($params as $config) {
			$bits = explode('=', $config);
			if (count($bits) < 2) {
				continue;
			}
			$v = array_shift($bits);
			$cfg[$v] = join('=', $bits);
		}
		$block = new $class_name();
		$block_id = KT_Filter::get('block_id');

		return $block->getBlock($block_id, false, $cfg);
	}

	public function totalUserMessages()
	{
		return KT_I18N::number(count(getUserMessages(KT_USER_NAME)));
	}

	public function totalUserJournal()
	{
		return KT_I18N::number(count(getUserNews(KT_USER_ID)));
	}

	public function totalGedcomNews()
	{
		return KT_I18N::number(count(getUserNews(KT_GEDCOM)));
	}

	// ////////////////////////////////////////////////////////////////////////////
	// Country lookup data
	// ////////////////////////////////////////////////////////////////////////////

	// ISO3166 3 letter codes, with their 2 letter equivalent.
	// NOTE: this is not 1:1.  ENG/SCO/WAL/NIR => GB
	// NOTE: this also includes champman codes and others.  Should it?
	public static function iso3166()
	{
		return [
			'WLS' => 'GB', 'SCT' => 'GB', 'NIR' => 'GB', 'GBR' => 'GB', 'ENG' => 'GB', 'ABW' => 'AW', 'AFG' => 'AF',
			'AGO' => 'AO', 'AIA' => 'AI', 'ALA' => 'AX', 'ALB' => 'AL', 'AND' => 'AD', 'ANT' => 'AN', 'ARE' => 'AE', 
			'ARG' => 'AR', 'ARM' => 'AM', 'ASM' => 'AS', 'ATA' => 'AQ', 'ATF' => 'TF', 'ATG' => 'AG', 'AUS' => 'AU', 
			'AUT' => 'AT', 'AZE' => 'AZ', 'BDI' => 'BI', 'BEL' => 'BE', 'BEN' => 'BJ', 'BFA' => 'BF', 'BGD' => 'BD', 
			'BGR' => 'BG', 'BHR' => 'BH', 'BHS' => 'BS', 'BIH' => 'BA', 'BLR' => 'BY', 'BLZ' => 'BZ', 'BMU' => 'BM', 
			'BOL' => 'BO', 'BRA' => 'BR', 'BRB' => 'BB', 'BRN' => 'BN', 'BTN' => 'BT', 'BVT' => 'BV', 'BWA' => 'BW', 
			'CAF' => 'CF', 'CAN' => 'CA', 'CCK' => 'CC', 'CHE' => 'CH', 'CHL' => 'CL', 'CHN' => 'CN', 'CHI' => 'JE', 
			'CIV' => 'CI', 'CMR' => 'CM', 'COD' => 'CD', 'COG' => 'CG', 'COK' => 'CK', 'COL' => 'CO', 'COM' => 'KM', 
			'CPV' => 'CV', 'CRI' => 'CR', 'CUB' => 'CU', 'CXR' => 'CX', 'CYM' => 'KY', 'CYP' => 'CY', 'CZE' => 'CZ', 
			'DEU' => 'DE', 'DJI' => 'DJ', 'DMA' => 'DM', 'DNK' => 'DK', 'DOM' => 'DO', 'DZA' => 'DZ', 'ECU' => 'EC', 
			'EGY' => 'EG', 'ERI' => 'ER', 'ESH' => 'EH', 'ESP' => 'ES', 'EST' => 'EE', 'ETH' => 'ET', 'FIN' => 'FI', 
			'FJI' => 'FJ', 'FLK' => 'FK', 'FRA' => 'FR', 'FRO' => 'FO', 'FSM' => 'FM', 'GAB' => 'GA', 'GEO' => 'GE', 
			'GHA' => 'GH', 'GIB' => 'GI', 'GIN' => 'GN', 'GLP' => 'GP', 'GMB' => 'GM', 'GNB' => 'GW', 'GNQ' => 'GQ', 
			'GRC' => 'GR', 'GRD' => 'GD', 'GRL' => 'GL', 'GTM' => 'GT', 'GUF' => 'GF', 'GUM' => 'GU', 'GUY' => 'GY', 
			'HKG' => 'HK', 'HMD' => 'HM', 'HND' => 'HN', 'HRV' => 'HR', 'HTI' => 'HT', 'HUN' => 'HU', 'IDN' => 'ID', 
			'IND' => 'IN', 'IOT' => 'IO', 'IRL' => 'IE', 'IRN' => 'IR', 'IRQ' => 'IQ', 'ISL' => 'IS', 'ISR' => 'IL', 
			'ITA' => 'IT', 'JAM' => 'JM', 'JOR' => 'JO', 'JPN' => 'JA', 'KAZ' => 'KZ', 'KEN' => 'KE', 'KGZ' => 'KG', 
			'KHM' => 'KH', 'KIR' => 'KI', 'KNA' => 'KN', 'KOR' => 'KO', 'KWT' => 'KW', 'LAO' => 'LA', 'LBN' => 'LB', 
			'LBR' => 'LR', 'LBY' => 'LY', 'LCA' => 'LC', 'LIE' => 'LI', 'LKA' => 'LK', 'LSO' => 'LS', 'LTU' => 'LT', 
			'LUX' => 'LU', 'LVA' => 'LV', 'MAC' => 'MO', 'MAR' => 'MA', 'MCO' => 'MC', 'MDA' => 'MD', 'MDG' => 'MG', 
			'MDV' => 'MV', 'MEX' => 'MX', 'MHL' => 'MH', 'MKD' => 'MK', 'MLI' => 'ML', 'MLT' => 'MT', 'MMR' => 'MM', 
			'MNG' => 'MN', 'MNP' => 'MP', 'MNT' => 'ME', 'MOZ' => 'MZ', 'MRT' => 'MR', 'MSR' => 'MS', 'MTQ' => 'MQ', 
			'MUS' => 'MU', 'MWI' => 'MW', 'MYS' => 'MY', 'MYT' => 'YT', 'NAM' => 'NA', 'NCL' => 'NC', 'NER' => 'NE', 
			'NFK' => 'NF', 'NGA' => 'NG', 'NIC' => 'NI', 'NIU' => 'NU', 'NLD' => 'NL', 'NOR' => 'NO', 'NPL' => 'NP', 
			'NRU' => 'NR', 'NZL' => 'NZ', 'OMN' => 'OM', 'PAK' => 'PK', 'PAN' => 'PA', 'PCN' => 'PN', 'PER' => 'PE', 
			'PHL' => 'PH', 'PLW' => 'PW', 'PNG' => 'PG', 'POL' => 'PL', 'PRI' => 'PR', 'PRK' => 'KP', 'PRT' => 'PO', 
			'PRY' => 'PY', 'PSE' => 'PS', 'PYF' => 'PF', 'QAT' => 'QA', 'REU' => 'RE', 'RHO' => 'RH', 'ROM' => 'RO', 
			'RUS' => 'RU', 'RWA' => 'RW', 'SAU' => 'SA', 'SDN' => 'SD', 'SEN' => 'SN', 'SER' => 'RS', 'SGP' => 'SG', 
			'SHN' => 'SH', 'SIC' => 'IT', 'SJM' => 'SJ', 'SLB' => 'SB', 'SLE' => 'SL', 'SLV' => 'SV', 'SMR' => 'SM', 
			'SOM' => 'SO', 'SPM' => 'PM', 'STP' => 'ST', 'SUN' => 'RU', 'SUR' => 'SR', 'SVK' => 'SK', 'SVN' => 'SI', 
			'SWE' => 'SE', 'SWZ' => 'SZ', 'SYC' => 'SC', 'SYR' => 'SY', 'TCA' => 'TC', 'TCD' => 'TD', 'TGO' => 'TG', 
			'THA' => 'TH', 'TJK' => 'TJ', 'TKL' => 'TK', 'TKM' => 'TM', 'TLS' => 'TL', 'TON' => 'TO', 'TTO' => 'TT', 
			'TUN' => 'TN', 'TUR' => 'TR', 'TUV' => 'TV', 'TWN' => 'TW', 'TZA' => 'TZ', 'UGA' => 'UG', 'UKR' => 'UA', 
			'UMI' => 'UM', 'URY' => 'UY', 'USA' => 'US', 'UZB' => 'UZ', 'VAT' => 'VA', 'VCT' => 'VC', 'VEN' => 'VE', 
			'VGB' => 'VG', 'VIR' => 'VI', 'VNM' => 'VN', 'VUT' => 'VU', 'WLF' => 'WF', 'WSM' => 'WS', 'YEM' => 'YE', 
			'ZAF' => 'ZA', 'ZMB' => 'ZM', 'ZWE' => 'ZW',
		];
	}

	public static function get_all_countries()
	{
		return [
			'???' => KT_I18N::translate('Unknown'),
			'ABW' => KT_I18N::translate('Aruba'),
			'ACA' => KT_I18N::translate('Acadia'),
			'AFG' => KT_I18N::translate('Afghanistan'),
			'AGO' => KT_I18N::translate('Angola'),
			'AIA' => KT_I18N::translate('Anguilla'),
			'ALA' => KT_I18N::translate('Aland Islands'),
			'ALB' => KT_I18N::translate('Albania'),
			'AND' => KT_I18N::translate('Andorra'),
			'ANT' => KT_I18N::translate('Netherlands Antilles'),
			'ARE' => KT_I18N::translate('United Arab Emirates'),
			'ARG' => KT_I18N::translate('Argentina'),
			'ARM' => KT_I18N::translate('Armenia'),
			'ASM' => KT_I18N::translate('American Samoa'),
			'ATA' => KT_I18N::translate('Antarctica'),
			'ATF' => KT_I18N::translate('French Southern Territories'),
			'ATG' => KT_I18N::translate('Antigua and Barbuda'),
			'AUS' => KT_I18N::translate('Australia'),
			'AUT' => KT_I18N::translate('Austria'),
			'AZE' => KT_I18N::translate('Azerbaijan'),
			'AZR' => KT_I18N::translate('Azores'),
			'BDI' => KT_I18N::translate('Burundi'),
			'BEL' => KT_I18N::translate('Belgium'),
			'BEN' => KT_I18N::translate('Benin'),
			'BFA' => KT_I18N::translate('Burkina Faso'),
			'BGD' => KT_I18N::translate('Bangladesh'),
			'BGR' => KT_I18N::translate('Bulgaria'),
			'BHR' => KT_I18N::translate('Bahrain'),
			'BHS' => KT_I18N::translate('Bahamas'),
			'BIH' => KT_I18N::translate('Bosnia and Herzegovina'),
			'BLR' => KT_I18N::translate('Belarus'),
			'BLZ' => KT_I18N::translate('Belize'),
			'BMU' => KT_I18N::translate('Bermuda'),
			'BOL' => KT_I18N::translate('Bolivia'),
			'BRA' => KT_I18N::translate('Brazil'),
			'BRB' => KT_I18N::translate('Barbados'),
			'BRN' => KT_I18N::translate('Brunei Darussalam'),
			'BTN' => KT_I18N::translate('Bhutan'),
			'BVT' => KT_I18N::translate('Bouvet Island'),
			'BWA' => KT_I18N::translate('Botswana'),
			'BWI' => KT_I18N::translate('British West Indies'),
			'CAF' => KT_I18N::translate('Central African Republic'),
			'CAN' => KT_I18N::translate('Canada'),
			'CAP' => KT_I18N::translate('Cape Colony'),
			'CAT' => KT_I18N::translate('Catalonia'),
			'CCK' => KT_I18N::translate('Cocos (Keeling) Islands'),
			'CHE' => KT_I18N::translate('Switzerland'),
			'CHI' => KT_I18N::translate('Channel Islands'),
			'CHL' => KT_I18N::translate('Chile'),
			'CHN' => KT_I18N::translate('China'),
			'CIV' => KT_I18N::translate('Cote d\'Ivoire'),
			'CMR' => KT_I18N::translate('Cameroon'),
			'COD' => KT_I18N::translate('Congo (Kinshasa)'),
			'COG' => KT_I18N::translate('Congo (Brazzaville)'),
			'COK' => KT_I18N::translate('Cook Islands'),
			'COL' => KT_I18N::translate('Colombia'),
			'COM' => KT_I18N::translate('Comoros'),
			'CPV' => KT_I18N::translate('Cape Verde'),
			'CRI' => KT_I18N::translate('Costa Rica'),
			'CSK' => KT_I18N::translate('Czechoslovakia'),
			'CUB' => KT_I18N::translate('Cuba'),
			'CXR' => KT_I18N::translate('Christmas Island'),
			'CYM' => KT_I18N::translate('Cayman Islands'),
			'CYP' => KT_I18N::translate('Cyprus'),
			'CZE' => KT_I18N::translate('Czech Republic'),
			'DEU' => KT_I18N::translate('Germany'),
			'DJI' => KT_I18N::translate('Djibouti'),
			'DMA' => KT_I18N::translate('Dominica'),
			'DNK' => KT_I18N::translate('Denmark'),
			'DOM' => KT_I18N::translate('Dominican Republic'),
			'DZA' => KT_I18N::translate('Algeria'),
			'ECU' => KT_I18N::translate('Ecuador'),
			'EGY' => KT_I18N::translate('Egypt'),
			'EIR' => KT_I18N::translate('Eire'),
			'ENG' => KT_I18N::translate('England'),
			'ERI' => KT_I18N::translate('Eritrea'),
			'ESH' => KT_I18N::translate('Western Sahara'),
			'ESP' => KT_I18N::translate('Spain'),
			'EST' => KT_I18N::translate('Estonia'),
			'ETH' => KT_I18N::translate('Ethiopia'),
			'FIN' => KT_I18N::translate('Finland'),
			'FJI' => KT_I18N::translate('Fiji'),
			'FLD' => KT_I18N::translate('Flanders'),
			'FLK' => KT_I18N::translate('Falkland Islands'),
			'FRA' => KT_I18N::translate('France'),
			'FRO' => KT_I18N::translate('Faeroe Islands'),
			'FSM' => KT_I18N::translate('Micronesia'),
			'GAB' => KT_I18N::translate('Gabon'),
			'GBR' => KT_I18N::translate('United Kingdom'),
			'GEO' => KT_I18N::translate('Georgia'),
			'GGY' => KT_I18N::translate('Guernsey'),
			'GHA' => KT_I18N::translate('Ghana'),
			'GIB' => KT_I18N::translate('Gibraltar'),
			'GIN' => KT_I18N::translate('Guinea'),
			'GLP' => KT_I18N::translate('Guadeloupe'),
			'GMB' => KT_I18N::translate('Gambia'),
			'GNB' => KT_I18N::translate('Guinea-Bissau'),
			'GNQ' => KT_I18N::translate('Equatorial Guinea'),
			'GRC' => KT_I18N::translate('Greece'),
			'GRD' => KT_I18N::translate('Grenada'),
			'GRL' => KT_I18N::translate('Greenland'),
			'GTM' => KT_I18N::translate('Guatemala'),
			'GUF' => KT_I18N::translate('French Guiana'),
			'GUM' => KT_I18N::translate('Guam'),
			'GUY' => KT_I18N::translate('Guyana'),
			'HKG' => KT_I18N::translate('Hong Kong'),
			'HMD' => KT_I18N::translate('Heard Island and McDonald Islands'),
			'HND' => KT_I18N::translate('Honduras'),
			'HRV' => KT_I18N::translate('Croatia'),
			'HTI' => KT_I18N::translate('Haiti'),
			'HUN' => KT_I18N::translate('Hungary'),
			'IDN' => KT_I18N::translate('Indonesia'),
			'IND' => KT_I18N::translate('India'),
			'IOM' => KT_I18N::translate('Isle of Man'),
			'IOT' => KT_I18N::translate('British Indian Ocean Territory'),
			'IRL' => KT_I18N::translate('Ireland'),
			'IRN' => KT_I18N::translate('Iran'),
			'IRQ' => KT_I18N::translate('Iraq'),
			'ISL' => KT_I18N::translate('Iceland'),
			'ISR' => KT_I18N::translate('Israel'),
			'ITA' => KT_I18N::translate('Italy'),
			'JAM' => KT_I18N::translate('Jamaica'),
			'JOR' => KT_I18N::translate('Jordan'),
			'JPN' => KT_I18N::translate('Japan'),
			'KAZ' => KT_I18N::translate('Kazakhstan'),
			'KEN' => KT_I18N::translate('Kenya'),
			'KGZ' => KT_I18N::translate('Kyrgyzstan'),
			'KHM' => KT_I18N::translate('Cambodia'),
			'KIR' => KT_I18N::translate('Kiribati'),
			'KNA' => KT_I18N::translate('Saint Kitts and Nevis'),
			'KOR' => KT_I18N::translate('Korea'),
			'KWT' => KT_I18N::translate('Kuwait'),
			'LAO' => KT_I18N::translate('Laos'),
			'LBN' => KT_I18N::translate('Lebanon'),
			'LBR' => KT_I18N::translate('Liberia'),
			'LBY' => KT_I18N::translate('Libya'),
			'LCA' => KT_I18N::translate('Saint Lucia'),
			'LIE' => KT_I18N::translate('Liechtenstein'),
			'LKA' => KT_I18N::translate('Sri Lanka'),
			'LSO' => KT_I18N::translate('Lesotho'),
			'LTU' => KT_I18N::translate('Lithuania'),
			'LUX' => KT_I18N::translate('Luxembourg'),
			'LVA' => KT_I18N::translate('Latvia'),
			'MAC' => KT_I18N::translate('Macau'),
			'MAR' => KT_I18N::translate('Morocco'),
			'MCO' => KT_I18N::translate('Monaco'),
			'MDA' => KT_I18N::translate('Moldova'),
			'MDG' => KT_I18N::translate('Madagascar'),
			'MDV' => KT_I18N::translate('Maldives'),
			'MEX' => KT_I18N::translate('Mexico'),
			'MHL' => KT_I18N::translate('Marshall Islands'),
			'MKD' => KT_I18N::translate('Macedonia'),
			'MLI' => KT_I18N::translate('Mali'),
			'MLT' => KT_I18N::translate('Malta'),
			'MMR' => KT_I18N::translate('Myanmar'),
			'MNG' => KT_I18N::translate('Mongolia'),
			'MNP' => KT_I18N::translate('Northern Mariana Islands'),
			'MNT' => KT_I18N::translate('Montenegro'),
			'MOZ' => KT_I18N::translate('Mozambique'),
			'MRT' => KT_I18N::translate('Mauritania'),
			'MSR' => KT_I18N::translate('Montserrat'),
			'MTQ' => KT_I18N::translate('Martinique'),
			'MUS' => KT_I18N::translate('Mauritius'),
			'MWI' => KT_I18N::translate('Malawi'),
			'MYS' => KT_I18N::translate('Malaysia'),
			'MYT' => KT_I18N::translate('Mayotte'),
			'NAM' => KT_I18N::translate('Namibia'),
			'NCL' => KT_I18N::translate('New Caledonia'),
			'NER' => KT_I18N::translate('Niger'),
			'NFK' => KT_I18N::translate('Norfolk Island'),
			'NGA' => KT_I18N::translate('Nigeria'),
			'NIC' => KT_I18N::translate('Nicaragua'),
			'NIR' => KT_I18N::translate('Northern Ireland'),
			'NIU' => KT_I18N::translate('Niue'),
			'NLD' => KT_I18N::translate('Netherlands'),
			'NOR' => KT_I18N::translate('Norway'),
			'NPL' => KT_I18N::translate('Nepal'),
			'NRU' => KT_I18N::translate('Nauru'),
			'NTZ' => KT_I18N::translate('Neutral Zone'),
			'NZL' => KT_I18N::translate('New Zealand'),
			'OMN' => KT_I18N::translate('Oman'),
			'PAK' => KT_I18N::translate('Pakistan'),
			'PAN' => KT_I18N::translate('Panama'),
			'PCN' => KT_I18N::translate('Pitcairn'),
			'PER' => KT_I18N::translate('Peru'),
			'PHL' => KT_I18N::translate('Philippines'),
			'PLW' => KT_I18N::translate('Palau'),
			'PNG' => KT_I18N::translate('Papua New Guinea'),
			'POL' => KT_I18N::translate('Poland'),
			'PRI' => KT_I18N::translate('Puerto Rico'),
			'PRK' => KT_I18N::translate('North Korea'),
			'PRT' => KT_I18N::translate('Portugal'),
			'PRY' => KT_I18N::translate('Paraguay'),
			'PSE' => KT_I18N::translate('Occupied Palestinian Territory'),
			'PYF' => KT_I18N::translate('French Polynesia'),
			'QAT' => KT_I18N::translate('Qatar'),
			'REU' => KT_I18N::translate('Reunion'),
			'RHO' => KT_I18N::translate('Rhodesia'),
			'ROM' => KT_I18N::translate('Romania'),
			'RUS' => KT_I18N::translate('Russia'),
			'RWA' => KT_I18N::translate('Rwanda'),
			'SAU' => KT_I18N::translate('Saudi Arabia'),
			'SCG' => KT_I18N::translate('Serbia and Montenegro'),
			'SCT' => KT_I18N::translate('Scotland'),
			'SDN' => KT_I18N::translate('Sudan'),
			'SEA' => KT_I18N::translate('At Sea'),
			'SEN' => KT_I18N::translate('Senegal'),
			'SER' => KT_I18N::translate('Serbia'),
			'SGP' => KT_I18N::translate('Singapore'),
			'SHN' => KT_I18N::translate('Saint Helena'),
			'SIC' => KT_I18N::translate('Sicily'),
			'SJM' => KT_I18N::translate('Svalbard and Jan Mayen Islands'),
			'SLB' => KT_I18N::translate('Solomon Islands'),
			'SLE' => KT_I18N::translate('Sierra Leone'),
			'SLV' => KT_I18N::translate('El Salvador'),
			'SMR' => KT_I18N::translate('San Marino'),
			'SOM' => KT_I18N::translate('Somalia'),
			'SPM' => KT_I18N::translate('Saint Pierre and Miquelon'),
			'SSD' => KT_I18N::translate('South Sudan'),
			'STP' => KT_I18N::translate('Sao Tome and Principe'),
			'SUN' => KT_I18N::translate('USSR'),
			'SUR' => KT_I18N::translate('Suriname'),
			'SVK' => KT_I18N::translate('Slovakia'),
			'SVN' => KT_I18N::translate('Slovenia'),
			'SWE' => KT_I18N::translate('Sweden'),
			'SWZ' => KT_I18N::translate('Swaziland'),
			'SYC' => KT_I18N::translate('Seychelles'),
			'SYR' => KT_I18N::translate('Syrian Arab Republic'),
			'TCA' => KT_I18N::translate('Turks and Caicos Islands'),
			'TCD' => KT_I18N::translate('Chad'),
			'TGO' => KT_I18N::translate('Togo'),
			'THA' => KT_I18N::translate('Thailand'),
			'TJK' => KT_I18N::translate('Tajikistan'),
			'TKL' => KT_I18N::translate('Tokelau'),
			'TKM' => KT_I18N::translate('Turkmenistan'),
			'TLS' => KT_I18N::translate('Timor-Leste'),
			'TON' => KT_I18N::translate('Tonga'),
			'TRN' => KT_I18N::translate('Transylvania'),
			'TTO' => KT_I18N::translate('Trinidad and Tobago'),
			'TUN' => KT_I18N::translate('Tunisia'),
			'TUR' => KT_I18N::translate('Turkey'),
			'TUV' => KT_I18N::translate('Tuvalu'),
			'TWN' => KT_I18N::translate('Taiwan'),
			'TZA' => KT_I18N::translate('Tanzania'),
			'UGA' => KT_I18N::translate('Uganda'),
			'UKR' => KT_I18N::translate('Ukraine'),
			'UMI' => KT_I18N::translate('US Minor Outlying Islands'),
			'URY' => KT_I18N::translate('Uruguay'),
			'USA' => KT_I18N::translate('USA'),
			'UZB' => KT_I18N::translate('Uzbekistan'),
			'VAT' => KT_I18N::translate('Vatican City'),
			'VCT' => KT_I18N::translate('Saint Vincent and the Grenadines'),
			'VEN' => KT_I18N::translate('Venezuela'),
			'VGB' => KT_I18N::translate('British Virgin Islands'),
			'VIR' => KT_I18N::translate('US Virgin Islands'),
			'VNM' => KT_I18N::translate('Viet Nam'),
			'VUT' => KT_I18N::translate('Vanuatu'),
			'WAF' => KT_I18N::translate('West Africa'),
			'WLF' => KT_I18N::translate('Wallis and Futuna Islands'),
			'WLS' => KT_I18N::translate('Wales'),
			'WSM' => KT_I18N::translate('Samoa'),
			'YEM' => KT_I18N::translate('Yemen'),
			'YUG' => KT_I18N::translate('Yugoslavia'),
			'ZAF' => KT_I18N::translate('South Africa'),
			'ZAR' => KT_I18N::translate('Zaire'),
			'ZMB' => KT_I18N::translate('Zambia'),
			'ZWE' => KT_I18N::translate('Zimbabwe'),
		];
	}

	// century name, English => 21st, Polish => XXI, etc.
	public static function _centuryName($century)
	{
		if ($century < 0) {
			return str_replace(-$century, KT_Stats::_centuryName(-$century), /* I18N: BCE=Before the Common Era, for Julian years < 0.  See http://en.wikipedia.org/wiki/Common_Era */ KT_I18N::translate('%s BCE', KT_I18N::number(-$century)));
		}

		switch ($century) {
			case 21: return strip_tags(KT_I18N::translate_c('CENTURY', '21st'));

			case 20: return strip_tags(KT_I18N::translate_c('CENTURY', '20th'));

			case 19: return strip_tags(KT_I18N::translate_c('CENTURY', '19th'));

			case 18: return strip_tags(KT_I18N::translate_c('CENTURY', '18th'));

			case 17: return strip_tags(KT_I18N::translate_c('CENTURY', '17th'));

			case 16: return strip_tags(KT_I18N::translate_c('CENTURY', '16th'));

			case 15: return strip_tags(KT_I18N::translate_c('CENTURY', '15th'));

			case 14: return strip_tags(KT_I18N::translate_c('CENTURY', '14th'));

			case 13: return strip_tags(KT_I18N::translate_c('CENTURY', '13th'));

			case 12: return strip_tags(KT_I18N::translate_c('CENTURY', '12th'));

			case 11: return strip_tags(KT_I18N::translate_c('CENTURY', '11th'));

			case 10: return strip_tags(KT_I18N::translate_c('CENTURY', '10th'));

			case 9: return strip_tags(KT_I18N::translate_c('CENTURY', '9th'));

			case 8: return strip_tags(KT_I18N::translate_c('CENTURY', '8th'));

			case 7: return strip_tags(KT_I18N::translate_c('CENTURY', '7th'));

			case 6: return strip_tags(KT_I18N::translate_c('CENTURY', '6th'));

			case 5: return strip_tags(KT_I18N::translate_c('CENTURY', '5th'));

			case 4: return strip_tags(KT_I18N::translate_c('CENTURY', '4th'));

			case 3: return strip_tags(KT_I18N::translate_c('CENTURY', '3rd'));

			case 2: return strip_tags(KT_I18N::translate_c('CENTURY', '2nd'));

			case 1: return strip_tags(KT_I18N::translate_c('CENTURY', '1st'));

			default: return ($century - 1) . '01-' . $century . '00';
		}
	}

	// /////////////////////////////////////////////////////////////////////////////
	// LISTS                                                                     //
	// /////////////////////////////////////////////////////////////////////////////

	public static function individualsList($ged_id, $option = '')
	{
		$sql = "SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec FROM `##individuals` WHERE i_file=" . $ged_id;
		if ($option) {
			switch ($option) {
				case 'male':
					$sql .= " AND i_gedcom LIKE '%1 SEX M%'";

					break;

				case 'female':
					$sql .= " AND i_gedcom LIKE '%1 SEX F%'";

					break;

				case 'unknown':
					$sql .= " AND i_gedcom NOT LIKE '%1 SEX M%' AND i_gedcom NOT LIKE '%1 SEX F%'";

					break;

				case 'living':
					$sql .= " AND i_gedcom NOT REGEXP '\\n1 (" . KT_EVENTS_DEAT . ")'";

					break;

				case 'deceased':
					$sql .= " AND i_gedcom REGEXP '\\n1 (" . KT_EVENTS_DEAT . ")'";

					break;

				case 'withsour':
					$sql = '
						SELECT i_id AS xref, i_file AS ged_id
						FROM `##individuals`, `##link`
						WHERE i_id = l_from AND i_file = l_file
						AND l_file = ' . $ged_id . "
						AND l_type='SOUR'
						GROUP BY i_id, i_file
					";

					break;

				case 'withoutsour':
					$sql = '
						SELECT i_id AS xref, i_file AS ged_id
						FROM `##individuals`
						WHERE i_file=1
						AND i_id NOT IN (
						    SELECT DISTINCT i_id
						    FROM `##individuals`, `##link`
						    WHERE i_id = l_from AND i_file = l_file
						    AND l_file=' . $ged_id . "
						    AND l_type = 'SOUR'
						);
					";

					break;
			}
		}
		$rows = KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
		$list = [];
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row['xref']);
			$list[] = clone $person;
		}

		return $list;
	}

	public static function famsList($ged_id, $option = false)
	{
		switch ($option) {
			case 'withsour':
				$sql = '
					SELECT f_id AS xref, f_file AS ged_id
					FROM `##families`, `##link`
					WHERE f_id = l_from AND f_file = l_file
					AND l_file = ' . $ged_id . "
					AND l_type='SOUR'
					GROUP BY f_id, f_file
				";

				break;

			case 'withoutsour':
				$sql = '
					SELECT f_id AS xref, f_file AS ged_id
					FROM `##families`
					WHERE f_file=' . $ged_id . '
					AND f_id NOT IN (
					    SELECT DISTINCT f_id
					    FROM `##families`, `##link`
					    WHERE f_id = l_from AND f_file = l_file
					    AND l_file=' . $ged_id . "
					    AND l_type = 'SOUR'
					);
				";

				break;

			default:
				$sql = '
					SELECT f_id AS xref, f_file AS ged_id
					FROM `##families`
					WHERE f_file=' . $ged_id . '
				';

				break;
		}
		$rows = KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
		$list = [];
		foreach ($rows as $row) {
			$family = KT_Family::getInstance($row['xref']);
			$list[] = clone $family;
		}

		return $list;
	}

	/**
	 * Get tags and their parsed results.
	 *
	 * @param string $text
	 *
	 * @return string[][]
	 */
	private function getTags($text)
	{
		static $funcs;

		// Retrive all class methods
		isset($funcs) or $funcs = get_class_methods($this);

		// Extract all tags from the provided text
		preg_match_all('/#([^#]+)(?=#)/', (string) $text, $match);
		$tags = $match[1];
		$c = count($tags);
		$new_tags = []; // tag to replace
		$new_values = []; // value to replace it with

		// Parse block tags.
		for ($i = 0; $i < $c; $i++) {
			$full_tag = $tags[$i];
			// Added for new parameter support
			$params = explode(':', $tags[$i]);
			if (count($params) > 1) {
				$tags[$i] = array_shift($params);
			} else {
				$params = [];
			}

			// Generate the replacement value for the tag
			if (method_exists($this, $tags[$i])) {
				$new_tags[] = '#' . $full_tag . '#';
				$new_values[] = call_user_func_array([$this, $tags[$i]], [$params]);
			}
		}

		return [$new_tags, $new_values];
	}

	// These functions provide access to hitcounter
	// for use in the HTML block.

	private static function _getHitCount($page_name, $params)
	{
		if (is_array($params) && isset($params[0]) && '' != $params[0]) {
			$page_parameter = $params[0];
		} else {
			$page_parameter = '';
		}

		if (null === $page_name) {
			// index.php?ctype=gedcom
			$page_name = 'index.php';
			$page_parameter = 'gedcom:' . get_id_from_gedcom($page_parameter ? $page_parameter : KT_GEDCOM);
		} elseif ('index.php' == $page_name) {
			// index.php?ctype=user
			$page_parameter = 'user:' . ($page_parameter ? get_user_id($page_parameter) : KT_USER_ID);
		}
		// indi/fam/sour/etc.

		$count = KT_DB::prepare(
			'SELECT page_count FROM `##hit_counter`' .
			' WHERE gedcom_id=? AND page_name=? AND page_parameter=?'
		)->execute([KT_GED_ID, $page_name, $page_parameter])->fetchOne();

		return '<span class="hit-counter">' . KT_I18N::number($count) . '</span>';
	}
}
