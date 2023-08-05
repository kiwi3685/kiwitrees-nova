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
 class KT_Datatables_AdminUsers {
	 /**
	  *
	  * @return data array
	  * Converted to json on display page
	  */
	 public static function userList($search, $start, $length, $isort, $draw, array $colsort, array $sortdir) {

		global $iconStyle;

		Zend_Session::writeClose();

		// convert days to seconds
		$days = (KT_Site::preference('VERIFY_DAYS') ? KT_Site::preference('VERIFY_DAYS') : 7);
		$time = $days * 60 * 60 * 24;

		// Generate an AJAX/JSON response for datatables to load a block of rows
		$WHERE		= " WHERE u.user_id>0 ";

		$ARGS		= [];

		if ($search) {
			$WHERE .= "
				AND (
					user_name LIKE CONCAT('%', ?, '%') OR
					real_name LIKE CONCAT('%', ?, '%') OR
					email     LIKE CONCAT('%', ?, '%')
				)
			";
			$ARGS = [$search, $search, $search];
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
		 	$ORDER_BY = '';
	 	}

	 	$SELECT = "
			SELECT SQL_CALC_FOUND_ROWS
			'',
			u.user_id,
			user_name,
			real_name,
			email,
			us1.setting_value,
			us2.setting_value,
			us2.setting_value,
			us3.setting_value,
			us3.setting_value,
			us4.setting_value,
			us5.setting_value,
			'',
			''
			FROM `##user` u
			LEFT JOIN `##user_setting` us1 ON (u.user_id=us1.user_id AND us1.setting_name='language')
			LEFT JOIN `##user_setting` us2 ON (u.user_id=us2.user_id AND us2.setting_name='reg_timestamp')
			LEFT JOIN `##user_setting` us3 ON (u.user_id=us3.user_id AND us3.setting_name='sessiontime')
			LEFT JOIN `##user_setting` us4 ON (u.user_id=us4.user_id AND us4.setting_name='verified')
			LEFT JOIN `##user_setting` us5 ON (u.user_id=us5.user_id AND us5.setting_name='verified_by_admin')
		";

		// This becomes a JSON list, not array, so need to fetch with numeric keys.
		$aaData = KT_DB::prepare($SELECT .  $WHERE . $ORDER_BY . $LIMIT)->execute($ARGS)->fetchAll(PDO::FETCH_NUM);

		$installed_languages = array();
		foreach (KT_I18N::used_languages() as $code=>$name) {
		$installed_languages[$code] = KT_I18N::translate($name);
		}

		// Reformat various columns for display
		foreach ($aaData as &$aData) {
		 	$user_id            = $aData[1];
		 	$user_name          = $aData[2];
		 	$user_realname      = $aData[3];
		 	$user_email         = $aData[4];
		 	$user_lang          = $aData[5];
		 	$user_regtime       = $aData[6];
		 	$user_regtime       = $aData[7];
		 	$user_emailverified = $aData[8];
		 	$user_emailverified = $aData[9];
		 	$user_verified      = $aData[10];
		 	$user_adminverified = $aData[11];
		 	$user_delete        = $aData[12];
		 	$user_masquerade    = $aData[13];

		 	// Edit user icon
		 	$aData[0] = '<a href="?action=edit&amp;user_id=' . $user_id . '" title="'. KT_I18N::translate('Edit user').'"><i class="' . $iconStyle . ' fa-pen-to-square"></i></a>';

		 	// User ID (not displayed)
		 	$aData[1] = $aData[1];

		 	// User name
		 	$aData[2] = '<a href="?action=edit&amp;user_id=' . $user_id . '" title="'. KT_I18N::translate('Edit user').'"><span dir="auto">' . KT_Filter::escapeHtml($user_name) . '</span></a>';

		 	// Real name
		 	$aData[3] = '<a href="?action=edit&amp;user_id=' . $user_id . '" title="'. KT_I18N::translate('Edit user') . '"><span dir="auto">' . KT_Filter::escapeHtml($user_realname) . '</span></a>';

		 	// Email address
		 	if ($user_id != KT_USER_ID) {
			 	$url = KT_SERVER_NAME . KT_SCRIPT_PATH . 'admin_users.php';
			 	$aData[4] = '<a href="message.php?to=' . $user_name . '&amp;url=' . $url . '"  title="' . KT_I18N::translate('Send Message') . '">' . KT_Filter::escapeHtml($user_email) . '&nbsp;<i class="fa-envelope-o"></i></a>';
		 	}

		 	// Language
		 	if (array_key_exists($aData[5], $installed_languages)) {
			 	$aData[5] = $installed_languages[$user_lang];
		 	}

		 	// User registration time (not displayed)
		 	$aData[6] = $aData[6];

		 	//Displayed user registration time
		 	$aData[7] = $aData[7] ? format_timestamp($user_regtime) : '';
		 	if (date("U") - $aData[6] > $time && !$aData[10]) {
			 	$aData[7] = '<span class="red">' . $user_regtime . '</span>'; // display in red if user does not verify within the set number of days (converted to secs)
		 	}

		 	// Sortable last-login timestamp (not displayed)
		 	$aData[8] = $aData[8];

		 	// Displayed last-login timestamp
		 	if ($aData[8]) {
			 	$aData[9] = format_timestamp($aData[8]) . '<br>' . KT_I18N::time_ago(KT_TIMESTAMP - $aData[8]);
		 	} else {
			 	$aData[9] = KT_I18N::translate('Never');
			}

		// Has user verified email address
			$aData[10] = $aData[10] ? KT_I18N::translate('Yes') : KT_I18N::translate('No');

			// Has admin approved user details
			$aData[11] = $aData[11] ? KT_I18N::translate('Yes') : KT_I18N::translate('No');

			// Add extra columns for "delete" & "masquerade" actions
			if ($aData[1] != KT_USER_ID) {
					$aData[12] = '<div class="' . $iconStyle . ' fa-trash-can" onclick="if (confirm(\''.htmlspecialchars(KT_I18N::translate('Are you sure you want to delete “%s”?', $user_name)).'\')) { document.location=\'' . KT_SCRIPT_NAME . '?action=deleteuser&username='.htmlspecialchars((string) $user_name).'\'; }"></div>';
					$aData[13] = '<div class="' . $iconStyle . ' fa-mask" onclick="if (confirm(\''.htmlspecialchars(KT_I18N::translate('Are you sure you want to masquerade as “%s”?',$user_name)).'\')) { document.location=\''.KT_SCRIPT_NAME.'?action=masquerade_user&username='.htmlspecialchars((string) $user_name).'\'; }"></div>';
			} else {
				// Do not delete ourself!
				$aData[12] = '';
				// Do not masquerade as ourself!
				$aData[13] = '';
			}
		}

		// Total filtered/unfiltered rows
		$iTotalDisplayRecords = KT_DB::prepare("SELECT FOUND_ROWS()")->fetchOne();
		$iTotalRecords        = KT_DB::prepare("SELECT COUNT(*) FROM `##user` WHERE user_id>0")->fetchOne();

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
