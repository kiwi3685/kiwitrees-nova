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

/**
  *
  */
 class KT_Datatables_ListSources {
	/**
	 *
	 * @return data array
	 * Converted to json on display page
	 */
	public static function sourceList($search, $start, $length, $isort, $draw, $colsort, $sortdir) {

		global $iconStyle, $SHOW_LAST_CHANGE;

		$WHERE = " WHERE s_file=" . KT_GED_ID;

		if ($search) {
			$QUERY = " AND s_name LIKE CONCAT('%', ?, '%')";
			$ARGS  = [$search];
		} else {
			$QUERY = "";
			$ARGS  = [];
		}

		if ($isort) {
			$ORDER_BY = ' ORDER BY ';
			for ($i = 0; $i < $isort; ++$i) {
				// Datatables numbers columns 0, 1, 2, ...
				// MySQL numbers columns 1, 2, 3, ...
				switch ($sortdir[$i]) {
					case 'asc':
						$ORDER_BY .= (1 + $colsort[$i]) . ' ASC ';
						break;
					case 'desc':
						$ORDER_BY .= (1 + $colsort[$i]) . ' DESC ';
						break;
				}
				if ($i < $isort - 1) {
					$ORDER_BY .= ',';
				}
			}
		} else {
			$ORDER_BY = 'ORDER BY 1 ASC';
		}

		if ($length > 0) {
			$LIMIT = ' LIMIT ' . $start . ', ' . $length;
		} else {
			$LIMIT = '';
		}

		$SELECT1 = "
			SELECT
				s_id AS xref,
				s_name AS name,
				'',
				IF(`s_gedcom` LIKE '%1 AUTH %', SUBSTRING_INDEX(SUBSTRING_INDEX(`s_gedcom`, '1 AUTH ', -1),'\n',1), '') AS auth,
				'',
				(SELECT COUNT(*) FROM `##individuals` JOIN `##link` ON l_from = i_id AND l_file = i_file WHERE l_type = 'SOUR' AND i_file = s_file AND l_to = xref GROUP BY l_to) as indi,
				'',
				(SELECT COUNT(*) FROM `##families` JOIN `##link` ON l_from = f_id AND l_file = f_file WHERE l_type = 'SOUR' AND f_file = s_file AND l_to = xref GROUP BY l_to) as fam,
				'',
				(SELECT COUNT(*) FROM `##media` JOIN `##link` ON l_from = m_id AND l_file = m_file WHERE l_type = 'SOUR' AND m_file = s_file AND l_to = xref GROUP BY l_to) as obj,
				'',
				(SELECT COUNT(*) FROM `##other` JOIN `##link` ON l_from = o_id AND l_file = o_file WHERE l_type = 'SOUR' AND o_file = s_file AND l_to = xref GROUP BY l_to) as note,
				'',
				'',
				'',
				''
			FROM `##sources`
		";

		$SELECT2 = "SELECT COUNT(*) FROM `##sources`";

		// Total filtered/unfiltered rows
		$iTotalDisplayRecords = KT_DB::prepare($SELECT2 . $WHERE . $QUERY . $LIMIT)->execute($ARGS)->fetchOne();
		$iTotalRecords        = KT_DB::prepare($SELECT2 . $WHERE)->execute()->fetchOne();

		// This becomes a JSON list, not array, so need to fetch with numeric keys.
		$aaData = KT_DB::prepare($SELECT1 . $WHERE . $QUERY . $ORDER_BY . $LIMIT)->execute($ARGS)->fetchAll(PDO::FETCH_NUM);

		$aData = [];
		$n     = 0;
		foreach ($aaData as &$aData) {
			$xref   = $aData[0];
			$name   = $aData[1];
			$auth   = $aData[3];
			$indi   = $aData[5] ? $aData[5]: 0;
			$fam    = $aData[7] ? $aData[7]: 0;
			$obj    = $aData[9] ? $aData[9]: 0;
			$note   = $aData[11] ? $aData[11]: 0;

			$source = KT_Source::getInstance($xref);

			if (!$source || !$source->canDisplayDetails()) {
				continue;
			}

			// xref
			$aData[0] =  $xref;

			// Sortable title
			$aData[1] =  $name;

			//-- Source title(s)
			$aData[2] = '<a href="' . $source->getHtmlUrl() . '">' . $source->getFullName() . '</a>';

			// -- Sortable author
			$aData[3] =  $auth;

			// -- Author
			$aData[4] =  htmlspecialchars((string) $source->getAuth());

			// -- Linked INDIs
			$aData[5] = $indi;
			$aData[6] = KT_I18N::number($indi);

			// -- Linked FAMs
			$aData[7]  = $fam;
			$aData[8]  = KT_I18N::number($fam);

			// -- Linked OBJEcts
			$aData[9] = $obj;
			$aData[10] = KT_I18N::number($obj);

			// -- Linked NOTEs
			$aData[11] = $note;
			$aData[12] = KT_I18N::number($note);

			// -- Last change hidden sort column
			if ($SHOW_LAST_CHANGE) {
				$aData[13] = $source->LastChangeTimestamp(true);
			} else {
				$aData[13] = '';
			}

			// -- Last change
			if ($SHOW_LAST_CHANGE) {
				$aData[14] = $source->LastChangeTimestamp();
			} else {
				$aData[14] = '';
			}

			// -- Select & delete
			$aData[15] = '<div class="delete_src"><input type="checkbox" name="del_places[]" class="check" value="' . $source->getXref() . '" title="' . KT_I18N::translate('Delete') . '"></div>';
		}

		// See http://www.datatables.net/usage/server-side
		$data = [
			'sEcho'                => $draw,
			'iTotalDisplayRecords' => $iTotalDisplayRecords,
			'iTotalRecords'        => $iTotalRecords,
			'aaData'               => $aaData
		];

		return $data;

	}
 }
