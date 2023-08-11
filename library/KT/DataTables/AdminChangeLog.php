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
 class KT_Datatables_AdminChangeLog {
	 /**
	  *
	  * @return data array
	  * Converted to json on display page
	  */
	 public static function changeLog($from, $to, $type, $oldged, $newged, $xref, $user, $gedc, $search, $start, $length, $isort, $draw, $colsort, $sortdir) {

		require_once KT_ROOT.'library/php-diff/lib/Diff.php';
		require_once KT_ROOT.'library/php-diff/lib/Diff/Renderer/Html/SideBySide.php';

		$QUERY	= [];
		$ARGS	= [];
		if ($from) {
			$QUERY[]	= 'change_time>=?';
			$ARGS[]		= $from;
		}
		if ($to) {
			$QUERY[]	= 'change_time<TIMESTAMPADD(DAY, 1 , ?)'; // before end of the day
			$ARGS []	= $to;
		}
		if ($type) {
			$QUERY[]	= 'status=?';
			$ARGS []	= $type;
		}
		if ($oldged) {
			$QUERY[]	= "old_gedcom LIKE CONCAT('%', ?, '%')";
			$ARGS []	= $oldged;
		}
		if ($newged) {
			$QUERY[]	= "new_gedcom LIKE CONCAT('%', ?, '%')";
			$ARGS []	= $newged;
		}
		if ($xref) {
			$QUERY[]	= "xref = ?";
			$ARGS []	= $xref;
		}
		if ($user) {
			$QUERY[]	= "user_name LIKE CONCAT('%', ?, '%')";
			$ARGS []	= $user;
		}
		if ($gedc) {
			$QUERY[]	= "gedcom_name LIKE CONCAT('%', ?, '%')";
			$ARGS []	= $gedc;
		}

		if ($QUERY) {
			$WHERE = " WHERE " . implode(' AND ', $QUERY);
		} else {
			$WHERE = '';
		}

		if ($length > 0) {
			$LIMIT = " LIMIT " . $start . ',' . $length;
		} else {
			$LIMIT = "";
		}

		if ($isort) {
			$ORDER_BY = ' ORDER BY ';
			for ($i = 0; $i < $isort; ++$i) {
				// Datatables numbers columns 0, 1, 2, ...
				// MySQL numbers columns 1, 2, 3, ...
				switch ($sortdir[$i]) {
					case 'asc':
						if ( ($colsort[$i]) == 0) {
 							$ORDER_BY .= 'change_id ASC '; // column 0 is "timestamp", using change_id gives the correct order for events in the same second
						} else {
 							$ORDER_BY .= (1 + ($colsort[$i])) . ' ASC ';
						}
						break;
					case 'desc':
						if ( ($colsort[$i]) == 0) {
							$ORDER_BY .= 'change_id DESC ';
						} else {
							$ORDER_BY .= (1 + ($colsort[$i])) . ' DESC ';
						}
						break;
				}
				if ($i < $isort - 1) {
					$ORDER_BY .= ',';
				}
			}
	 	} else {
		 	$ORDER_BY = '';
	 	}

		$SELECT1 = "
			SELECT
				SQL_CALC_FOUND_ROWS
				change_time,
				status,
				xref,
				old_gedcom,
				new_gedcom,
				IFNULL(user_name, '<none>') AS user_name,
				IFNULL(gedcom_name, '<none>') AS gedcom_name
			FROM `##change`
			LEFT JOIN `##user` USING (user_id)
			LEFT JOIN `##gedcom` USING (gedcom_id)
		";

		$SELECT2 = "
			SELECT COUNT(*) FROM `##change`
			LEFT JOIN `##user` USING (user_id)
			LEFT JOIN `##gedcom` USING (gedcom_id)
		";

		// This becomes a JSON list, not array, so need to fetch with numeric keys.
		$aaData = KT_DB::prepare($SELECT1 . $WHERE . $ORDER_BY . $LIMIT)->execute($ARGS)->fetchAll(PDO::FETCH_NUM);

		// Reformat various columns for display
		foreach ($aaData as &$aData) {

			$a = explode("\n", htmlspecialchars((string) $aData[3]));
			$b = explode("\n", htmlspecialchars((string) $aData[4]));

			// Generate a side by side diff
			$renderer = new Diff_Renderer_Html_SideBySide;

			// Options for generating the diff
			$options = array(
				//'ignoreWhitespace' => true,
				//'ignoreCase' => true,
			);

			// Initialize the diff class
			$diff = new Diff($a, $b, $options);

			$aData[1] = KT_I18N::translate($aData[1]);
			$aData[2] = '<a href="gedrecord.php?pid=' . $aData[2] . '&ged=' . $aData[6] . '" target="_blank" rel="noopener noreferrer">' . $aData[2] . '</a>';
			$aData[3] = $diff->Render($renderer);
			$aData[4] = '';

		}

		// Total filtered/unfiltered rows
		$iTotalDisplayRecords = KT_DB::prepare("SELECT FOUND_ROWS()")->fetchOne();
		$iTotalRecords        = KT_DB::prepare($SELECT2 . $WHERE)->execute($ARGS)->fetchOne();

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
