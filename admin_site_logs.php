<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_site_logs.php');
require './includes/session.php';

global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('%s logs', KT_KIWITREES));

require KT_ROOT.'includes/functions/functions_edit.php';

$earliest	= KT_DB::prepare("SELECT DATE(MIN(log_time)) FROM `##log`")->execute(array())->fetchOne();
$latest		= KT_DB::prepare("SELECT DATE(MAX(log_time)) FROM `##log`")->execute(array())->fetchOne();

// Filtering
$action	= KT_Filter::get('action');
$from	= KT_Filter::get('from', '\d\d\d\d-\d\d-\d\d', $earliest);
$to		= KT_Filter::get('to',   '\d\d\d\d-\d\d-\d\d', $latest);
$type	= KT_Filter::get('type', 'auth|change|config|debug|edit|error|media|search|spam');
$text	= KT_Filter::get('text');
$ip		= KT_Filter::get('ip');
$user	= KT_Filter::get('user');
$search = KT_Filter::get('search');
$search = isset($search['value']) ? $search['value'] : null;

if (KT_USER_IS_ADMIN) {
	// Administrators can see all logs
	$gedc = KT_Filter::get('gedc');
} else {
	// Managers can only see logs relating to this gedcom
	$gedc = KT_GEDCOM;
}

$query=array();
$args =array();
if ($from) {
	$query[]='log_time>=?';
	$args []=$from;
}
if ($to) {
	$query[]='log_time<TIMESTAMPADD(DAY, 1 , ?)'; // before end of the day
	$args []=$to;
}
if ($type) {
	$query[]='log_type=?';
	$args []=$type;
}
if ($text) {
	$query[]="log_message LIKE CONCAT('%', ?, '%')";
	$args []=$text;
}
if ($ip) {
	$query[]="ip_address LIKE CONCAT('%', ?, '%')";
	$args []=$ip;
}
if ($user) {
	$query[]="user_name LIKE CONCAT('%', ?, '%')";
	$args []=$user;
}
if ($gedc) {
	$query[]="gedcom_name LIKE CONCAT('%', ?, '%')";
	$args []=$gedc;
}

$SELECT1=
	"SELECT SQL_CACHE SQL_CALC_FOUND_ROWS log_time, log_type, log_message, ip_address, IFNULL(user_name, '<none>') AS user_name, IFNULL(gedcom_name, '<none>') AS gedcom_name".
	" FROM `##log`".
	" LEFT JOIN `##user`   USING (user_id)".   // user may be deleted
	" LEFT JOIN `##gedcom` USING (gedcom_id)"; // gedcom may be deleted
$SELECT2=
	"SELECT COUNT(*) FROM `##log`".
	" LEFT JOIN `##user`   USING (user_id)".   // user may be deleted
	" LEFT JOIN `##gedcom` USING (gedcom_id)"; // gedcom may be deleted
if ($query) {
	$WHERE=" WHERE " . implode(' AND ', $query);
} else {
	$WHERE='';
}

switch($action) {
	case 'delete':
		$DELETE =
			"DELETE `##log` FROM `##log`".
			" LEFT JOIN `##user`   USING (user_id)".   // user may be deleted
			" LEFT JOIN `##gedcom` USING (gedcom_id)". // gedcom may be deleted
			$WHERE;
		KT_DB::prepare($DELETE)->execute($args);
		break;

	case 'load_json':
		Zend_Session::writeClose();
		$iDisplayStart	= (int)safe_GET('iDisplayStart');
		$iDisplayLength	= (int)safe_GET('iDisplayLength');
		set_user_setting(KT_USER_ID, 'admin_site_log_page_size', $iDisplayLength);
		if ($iDisplayLength>0) {
			$LIMIT = " LIMIT " . $iDisplayStart . ',' . $iDisplayLength;
		} else {
			$LIMIT = "";
		}
		$iSortingCols=safe_GET('iSortingCols');
		if ($iSortingCols) {
			$ORDER_BY = ' ORDER BY ';
			for ($i = 0; $i < $iSortingCols; ++$i) {
				// Datatables numbers columns 0, 1, 2, ...
				// MySQL numbers columns 1, 2, 3, ...
				switch (safe_GET('sSortDir_' . $i)) {
				case 'asc':
					if ((int)safe_GET('iSortCol_' . $i) == 0) {
						$ORDER_BY .= 'log_id ASC '; // column 0 is "timestamp", using log_id gives the correct order for events in the same second
					} else {
						$ORDER_BY .= (1 + (int)safe_GET('iSortCol_' . $i)) . ' ASC ';
					}
					break;
				case 'desc':
					if ((int)safe_GET('iSortCol_'.$i)==0) {
						$ORDER_BY .= 'log_id DESC ';
					} else {
						$ORDER_BY .= ( 1 + (int)safe_GET('iSortCol_' . $i)) . ' DESC ';
					}
					break;
				}
				if ($i<$iSortingCols-1) {
					$ORDER_BY.=',';
				}
			}
		} else {
			$ORDER_BY='1 DESC';
		}

		// This becomes a JSON list, not array, so need to fetch with numeric keys.
		$data = KT_DB::prepare($SELECT1 . $WHERE . $ORDER_BY . $LIMIT)->execute($args)->fetchAll(PDO::FETCH_NUM);
		foreach ($data as &$row) {
			$row[2] = htmlspecialchars($row[2]);
		}

		// Total filtered/unfiltered rows
		$iTotalDisplayRecords	= KT_DB::prepare("SELECT FOUND_ROWS()")->fetchColumn();
		$iTotalRecords			= KT_DB::prepare($SELECT2 . $WHERE)->execute($args)->fetchColumn();

		header('Content-type: application/json');
		echo json_encode(array( // See http://www.datatables.net/usage/server-side
			'sEcho'               =>(int)safe_GET('sEcho'),
			'iTotalRecords'       =>$iTotalRecords,
			'iTotalDisplayRecords'=>$iTotalDisplayRecords,
			'data'              =>$data
		));
	exit;
}

$controller
	->pageHeader()
	->addExternalJavascript(KT_DATATABLES_JS)
	->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
	->addExternalJavascript(KT_DATATABLES_BUTTONS)
	->addExternalJavascript(KT_DATATABLES_HTML5)
	->addExternalJavascript(KT_DATEPICKER_JS)
	->addExternalJavascript(KT_DATEPICKER_JS_LOCALE)
	->addInlineJavascript('
		jQuery("#log_list").dataTable({
			dom: \'<"top"pBf<"clear">irl>t<"bottom"pl>\',
			' . KT_I18N::datatablesI18N(array(10,20,50,100,500,1000,-1)) . ',
			buttons: [{extend: "csvHtml5"}],
			autoWidth: false,
			processing: true,
			displayLength: ' . get_user_setting(KT_USER_ID, 'admin_site_log_page_size', 10) . ',
			serverSide: true,
			pagingType: "full_numbers",
			"sAjaxSource": "admin_site_logs.php?action=load_json&from='.$from.'&to='.$to.'&type='.$type.'&text='.rawurlencode($text).'&ip='.rawurlencode($ip).'&user='.rawurlencode($user).'&gedc='.rawurlencode($gedc).'",
			sorting: [[ 0, "desc" ]],
			columns: [
				/* 0 - Timestamp   */ { },
				/* 1 - Type        */ { },
				/* 2 - message     */ { },
				/* 3 - IP address  */ { },
				/* 4 - User        */ { },
				/* 5 - Family tree */ { }
			]
		});

		jQuery(".fdatepicker").fdatepicker({
			startDate: "' . $earliest . '",
			endDate: "' . $latest . '",
			language: "' . KT_LOCALE . '"
		});
	');

$users_array=array_combine(get_all_users(), get_all_users());
uksort($users_array, 'strnatcasecmp');
?>

<div id="site_logs-page" class="cell">
	<h4><?php echo $controller->getPageTitle(); ?></h4>
	<div class="grid-x grid-margin-y">
		<form name="logs" method="get" action="<?php echo KT_SCRIPT_NAME; ?>" data-abide novalidate>
			<input type="hidden" name="action" value="show">
			<div class="grid-x grid-margin-x">
				<div class="cell medium-3 medium-offset-1">
					<label class="h6"><?php echo KT_I18N::translate('From'); ?></label>
					<div class="date fdatepicker" id="from" data-date-format="yyyy-mm-dd">
						<div class="input-group">
							<input class="input-group-field" type="text" name="from" value="<?php echo htmlspecialchars($from); ?>">
							<span class="postfix input-group-label"><i class="<?php echo $iconStyle; ?> fa-calendar-alt fa-lg"></i></span>
						</div>
					</div>
				</div>
				<div class="cell medium-3">
					<label class="h6"><?php echo KT_I18N::translate('To'); ?></label>
					<div class="date fdatepicker" id="to" data-date-format="yyyy-mm-dd">
						<div class="input-group">
							<input class="input-group-field" type="text" name="to" value="<?php echo htmlspecialchars($to); ?>">
							<span class="postfix input-group-label"><i class="<?php echo $iconStyle; ?> fa-calendar-alt fa-lg"></i></span>
						</div>
					</div>
				</div>
				<div class="cell medium-2">
					<label class="h6"><?php echo KT_I18N::translate('Type'); ?></label>
					<?php echo select_edit_control('type', array(''=>'', 'auth'=>'auth','config'=>'config','debug'=>'debug','edit'=>'edit','error'=>'error','media'=>'media','search'=>'search', 'spam'=>'spam'), null, $type, ''); ?>
				</div>
				<div class="cell medium-2">
					<label class="h6"><?php echo KT_I18N::translate('IP address'); ?></label>
					<input class="log-filter" type="text" name="ip" value="<?php echo htmlspecialchars($ip); ?>">
				</div>
				<div class="cell medium-4 medium-offset-1">
					<label class="h6"><?php echo KT_I18N::translate('Message'); ?></label>
					<input class="log-filter" type="text" name="text" value="<?php echo htmlspecialchars($text); ?>">
				</div>
				<div class="cell medium-3">
					<label class="h6"><?php echo KT_I18N::translate('User'); ?></label>
					<?php echo select_edit_control('user', $users_array, '', $user, ''); ?>
				</div>
				<div class="cell medium-3">
					<label class="h6"><?php echo KT_I18N::translate('Family tree'); ?></label>
					<?php echo select_edit_control('gedc', KT_Tree::getNameList(), '', $gedc, KT_USER_IS_ADMIN ? '' : 'disabled'); ?>
				</div>
				<div class="cell medium-4">
					<button type="submit" class="button">
						<i class="<?php echo $iconStyle; ?> fa-search"></i>
						<?php echo KT_I18N::translate('Search'); ?>
					</button>
					<button type="submit" class="button" <?php echo 'onclick="if (confirm(\'' . htmlspecialchars(KT_I18N::translate('Permanently delete these records?')) . '\')) {document.logs.action.value=\'delete\';return true;} else {return false;}"' . ($action=='show' ? '' : 'disabled="disabled"');?> >
						<i class="<?php echo $iconStyle; ?> fa-trash-alt"></i>
						<?php echo KT_I18N::translate('Delete'); ?>
					</button>
				</div>
			</div>
		</form>
	</div>
	<hr>
	<?php if ($action) { ?>
		<div class="grid-x grid-margin-x">
			<div class="cell">
				<table id="log_list">
					<thead>
						<tr>
							<th><?php echo KT_I18N::translate('Timestamp'); ?></th>
							<th><?php echo KT_I18N::translate('Type'); ?></th>
							<th><?php echo KT_I18N::translate('Message'); ?></th>
							<th><?php echo KT_I18N::translate('IP address'); ?></th>
							<th><?php echo KT_I18N::translate('User'); ?></th>
							<th><?php echo KT_I18N::translate('Family tree'); ?></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	<?php } ?>
</div>
