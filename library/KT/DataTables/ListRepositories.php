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
 class KT_Datatables_ListRepositories {
	/**
	 *
	 * @return data array
	 * Converted to json on display page
	 */
	public static function repoList($search, $start, $length, $isort, $draw, $colsort, $sortdir) {

		global $iconStyle, $SHOW_LAST_CHANGE;

		$WHERE = " WHERE o_file=" . KT_GED_ID . " AND o_type='REPO'";

		if ($search) {
			$QUERY = " AND o_gedcom LIKE CONCAT('%', ?, '%')";
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
			$ORDER_BY = 'ORDER BY 2 ASC';
		}

		if ($length > 0) {
			$LIMIT = ' LIMIT ' . $start . ', ' . $length;
		} else {
			$LIMIT = '';
		}

		$SELECT1 = "
			SELECT
				`o_id` AS xref,
				'',
				'',
				'',
				(SELECT COUNT(*) FROM `##sources` JOIN `##link` ON l_from = s_id AND l_file = s_file WHERE l_type = 'NOTE' AND s_file = o_file AND l_to = xref GROUP BY l_to) as sour,
				'',
				'',
				''
			FROM `##other`
		";

		$SELECT2 = "SELECT COUNT(*) FROM `##other`";

		// Total filtered/unfiltered rows
		$iTotalDisplayRecords = KT_DB::prepare($SELECT2 . $WHERE . $QUERY . $LIMIT)->execute($ARGS)->fetchOne();
		$iTotalRecords        = KT_DB::prepare($SELECT2 . $WHERE)->execute()->fetchOne();

		// This becomes a JSON list, not array, so need to fetch with numeric keys.
		$aaData = KT_DB::prepare($SELECT1 . $WHERE . $QUERY . $ORDER_BY . $LIMIT)->execute($ARGS)->fetchAll(PDO::FETCH_NUM);

		$aData = [];
		$n     = 0;
		foreach ($aaData as &$aData) {
			$xref   = $aData[0];
			$sour   = $aData[4] ? $aData[4]: 0;

			$repo = KT_Note::getInstance($xref);

			if (!$repo || !$repo->canDisplayDetails()) {
				continue;
			}

			// xref
			$aData[0] =  $xref;

			//-- Repo name
			$aData[1] = '<a href="' . $repo->getHtmlUrl() . '">' . $repo->getFullName() . '</a>';

			// Sortable title
			$aData[2] =  $repo->getFullName();

			// -- Linked SOURces
			$aData[3] = KT_I18N::number($sour);
			$aData[4] = $repo;

			// -- Last change
			if ($SHOW_LAST_CHANGE) {
				$aData[5] = $repo->LastChangeTimestamp();
			} else {
				$aData[5] = '';
			}

			// -- Last change hidden sort column
			if ($SHOW_LAST_CHANGE) {
				$aData[6] = $repo->LastChangeTimestamp(true);
			} else {
				$aData[6] = '';
			}

			// -- Select & delete
			$aData[7] = '<div class="delete_src"><input type="checkbox" name="del_places[]" class="check" value="' . $repo->getXref() . '" title="' . KT_I18N::translate('Delete') . '"></div>';
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
