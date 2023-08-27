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
 class KT_Datatables_AdminSiteLog {
	 /**
	  *
	  * @return data array
	  * Converted to json on display page
	  */
	 public static function siteLog($from, $to, $type, $text, $ip, $user, $gedc, $search, $start, $length, $isort, $draw, $colsort, $sortdir) {

		$QUERY = [];
		$ARGS = [];
		if ($from) {
			$QUERY[] = 'log_time>=?';
			$ARGS [] = $from;
		}
		if ($to) {
			$QUERY[] = 'log_time<TIMESTAMPADD(DAY, 1 , ?)'; // before end of the day
			$ARGS[] = $to;
		}
		if ($type) {
			$QUERY[] = 'log_type=?';
			$ARGS[] = $type;
		}
		if ($text) {
			$QUERY[] = "log_message LIKE CONCAT('%', ?, '%')";
			$ARGS[] = $text;
		}
		if ($ip) {
			$QUERY[] = "ip_address LIKE CONCAT('%', ?, '%')";
			$ARGS[] = $ip;
		}
		if ($user) {
			$QUERY[] = "user_name LIKE CONCAT('%', ?, '%')";
			$ARGS[] = $user;
		}
		if ($gedc) {
			$QUERY[] = "gedcom_name LIKE CONCAT('%', ?, '%')";
			$ARGS[] = $gedc;
		}

		if ($QUERY) {
			$WHERE = " WHERE ".implode(' AND ', $QUERY);
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
 							$ORDER_BY .= 'log_id ASC '; // column 0 is "timestamp", using change_id gives the correct order for events in the same second
						} else {
 							$ORDER_BY .= (1 + ($colsort[$i])) . ' ASC ';
						}
						break;
					case 'desc':
						if ( ($colsort[$i]) == 0) {
							$ORDER_BY .= 'log_id DESC ';
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
				log_time,
				log_type,
				log_message,
				ip_address,
				IFNULL(user_name, '<none>') AS user_name,
				IFNULL(gedcom_name, '<none>') AS gedcom_name
			FROM `##log`
			LEFT JOIN `##user`   USING (user_id)
			LEFT JOIN `##gedcom` USING (gedcom_id)
		";

		 $SELECT2 = "
			SELECT COUNT(*) FROM `##log`
			LEFT JOIN `##user` USING (user_id)
			LEFT JOIN `##gedcom` USING (gedcom_id)
		";

		// This becomes a JSON list, not array, so need to fetch with numeric keys.
		$aaData = KT_DB::prepare($SELECT1 . $WHERE . $ORDER_BY . $LIMIT)->execute($ARGS)->fetchAll(PDO::FETCH_NUM);

		// Reformat various columns for display
		foreach ($aaData as &$aData) {
			$aData[2] = htmlspecialchars((string) $aData[2]);
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

	/**
	  *
	  * @return data array
	  * Converted to json on display page
	  */
	 public static function deleteLog($from, $to, $type, $text, $ip, $user, $gedc) {

		$QUERY = [];
		$ARGS = [];
		if ($from) {
			$QUERY[] = 'log_time>=?';
			$ARGS [] = $from;
		}
		if ($to) {
			$QUERY[] = 'log_time<TIMESTAMPADD(DAY, 1 , ?)'; // before end of the day
			$ARGS[] = $to;
		}
		if ($type) {
			$QUERY[] = 'log_type=?';
			$ARGS[] = $type;
		}
		if ($text) {
			$QUERY[] = "log_message LIKE CONCAT('%', ?, '%')";
			$ARGS[] = $text;
		}
		if ($ip) {
			$QUERY[] = "ip_address LIKE CONCAT('%', ?, '%')";
			$ARGS[] = $ip;
		}
		if ($user) {
			$QUERY[] = "user_name LIKE CONCAT('%', ?, '%')";
			$ARGS[] = $user;
		}
		if ($gedc) {
			$QUERY[] = "gedcom_name LIKE CONCAT('%', ?, '%')";
			$ARGS[] = $gedc;
		}

		if ($QUERY) {
			$WHERE = " WHERE ".implode(' AND ', $QUERY);
		} else {
			$WHERE = '';
		}

		$DELETE = "
			DELETE `##log`
			FROM `##log`
			LEFT JOIN `##user`   USING (user_id)
			LEFT JOIN `##gedcom` USING (gedcom_id)
		";

		$delete = KT_DB::prepare($DELETE . $WHERE);
		$delete->execute($ARGS);

		return $delete->rowCount();

	}

 }
