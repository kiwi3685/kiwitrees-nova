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
 class KT_Datatables_AdminAccess {
	 /**
	  *
	  * @return data array
	  * Converted to json on display page
	  */
	 public static function accessOne($search, $start, $length, $isort, $draw, $colsort, $sortdir) {

		global $iconStyle;

		Zend_Session::writeClose();

		$ARGS	= [];

		$WHERE = " WHERE rule<>'unknown' ";

		if ($search) {
			$WHERE .= "
				AND (INET_ATON(?) BETWEEN ip_address_start AND ip_address_end
				OR INET_NTOA(ip_address_start) LIKE CONCAT('%', ?, '%')
				OR INET_NTOA(ip_address_end) LIKE CONCAT('%', ?, '%')
				OR user_agent_pattern LIKE CONCAT('%', ?, '%')
				OR comment LIKE CONCAT('%', ?, '%'))
			";
			$ARGS[] = $sSearch;
			$ARGS[] = $sSearch;
			$ARGS[] = $sSearch;
			$ARGS[] = $sSearch;
			$ARGS[] = $sSearch;
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
						$ORDER_BY .= (1 + ($colsort[$i])) . ' ASC ';
						break;
					case 'desc':
						$ORDER_BY .= (1 + ($colsort[$i])) . ' DESC ';
						break;
				}
				if ($i < $isort - 1) {
					$ORDER_BY .= ',';
				}
			}
		} else {
			$ORDER_BY = ' ORDER BY updated DESC';
		}

		$SELECT = "
			SELECT
				SQL_CALC_FOUND_ROWS
				INET_NTOA(ip_address_start),
				ip_address_start,
				INET_NTOA(ip_address_end),
				ip_address_end,
				user_agent_pattern,
				rule,
				comment,
				site_access_rule_id
			FROM `##site_access_rule`
		";

		// This becomes a JSON list, not a JSON array, so we need numeric keys.
		$aaData = KT_DB::prepare($SELECT . $WHERE . $ORDER_BY . $LIMIT)->execute($ARGS)->fetchAll(PDO::FETCH_NUM);

		// Reformat the data for display
		foreach ($aaData as &$adata) {
			$site_access_rule_id = $adata[7];
			$user_agent          = $adata[4];

			$adata[0] = edit_field_inline('site_access_rule-ip_address_start-' . $site_access_rule_id, $adata[0]);
			$adata[2] = edit_field_inline('site_access_rule-ip_address_end-' . $site_access_rule_id, $adata[2]);
			$adata[4] = edit_field_inline('site_access_rule-user_agent_pattern-' . $site_access_rule_id, $adata[4]);
			$adata[5] = select_edit_control_inline('site_access_rule-rule-' . $site_access_rule_id, array(
				'allow'=>/* I18N: An access rule - allow access to the site */ KT_I18N::translate('allow'),
				'deny' =>/* I18N: An access rule - deny access to the site */  KT_I18N::translate('deny'),
				'robot'=>/* I18N: http://en.wikipedia.org/wiki/Web_crawler */  KT_I18N::translate('robot'),
			), null, $adata[5]);
			$adata[6] = edit_field_inline('site_access_rule-comment-'.$site_access_rule_id, $adata[6]);
			$adata[7] = '<i class="' . $iconStyle . ' fa-trash-can" onclick="if (confirm(\'' . htmlspecialchars(KT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($user_agent))).'\')) { document.location=\''.KT_SCRIPT_NAME.'?action=delete&amp;site_access_rule_id='.$site_access_rule_id.'\'; }"></i>';
		}


		// Total filtered/unfiltered rows
		$iTotalDisplayRecords = KT_DB::prepare("SELECT FOUND_ROWS()")->fetchOne();
		$iTotalRecords        = KT_DB::prepare("SELECT COUNT(*) FROM `##site_access_rule` WHERE rule <> 'unknown'")->fetchOne();

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
	 public static function accessTwo($search, $start, $length, $isort, $draw, $colsort, $sortdir) {

		global $iconStyle;

		Zend_Session::writeClose();

		$ARGS	= [];

		$WHERE = " WHERE rule='unknown' ";

		if ($search) {
			 $WHERE .= "
				AND (INET_ATON(ip_address_start) LIKE CONCAT('%', ?, '%')
				OR user_agent_pattern LIKE CONCAT('%', ?, '%'))
			 ";
			 $ARGS[] = $sSearch;
			 $ARGS[] = $sSearch;
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
						 $ORDER_BY .= (1 + ($colsort[$i])) . ' ASC ';
						 break;
					 case 'desc':
						 $ORDER_BY .= (1 + ($colsort[$i])) . ' DESC ';
						 break;
				 }
				 if ($i < $isort - 1) {
					 $ORDER_BY .= ',';
				 }
			 }
		} else {
			 $ORDER_BY = ' ORDER BY updated DESC';
		}

		$SELECT = "
			SELECT
				SQL_CALC_FOUND_ROWS
				INET_NTOA(ip_address_start),
				ip_address_start,
				user_agent_pattern,
				site_access_rule_id
			FROM `##site_access_rule`
		";

		// This becomes a JSON list, not a JSON array, so we need numeric keys.
		$aaData = KT_DB::prepare($SELECT . $WHERE . $ORDER_BY . $LIMIT)->execute($ARGS)->fetchAll(PDO::FETCH_NUM);

		// Reformat the data for display
		foreach ($aaData as &$adata) {
			$site_access_rule_id = $adata[3];

			$adata[3] = '<i class="text-center ' . $iconStyle . ' fa-check" onclick="document.location=\'' . KT_SCRIPT_NAME.'?action=allow&amp;site_access_rule_id=' . $site_access_rule_id . '\';"></i>';
			$adata[4] = '<i class="text-center ' . $iconStyle . ' fa-check" onclick="document.location=\'' . KT_SCRIPT_NAME.'?action=deny&amp;site_access_rule_id=' . $site_access_rule_id . '\';"></i>';
			$adata[5] = '<i class="text-center ' . $iconStyle . ' fa-check" onclick="document.location=\'' . KT_SCRIPT_NAME.'?action=robot&amp;site_access_rule_id=' . $site_access_rule_id . '\';"></i>';
		}


		// Total filtered/unfiltered rows
		$iTotalDisplayRecords = KT_DB::prepare("SELECT FOUND_ROWS()")->fetchOne();
		$iTotalRecords        = KT_DB::prepare("SELECT COUNT(*) FROM `##site_access_rule` WHERE rule = 'unknown'")->fetchOne();

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
