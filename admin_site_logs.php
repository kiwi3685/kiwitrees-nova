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
define('KT_SCRIPT_NAME', 'admin_site_logs.php');

require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';

global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('%s logs', KT_KIWITREES))
;


$earliest = KT_DB::prepare('SELECT DATE(MIN(log_time)) FROM `##log`')->execute([])->fetchOne();
$latest   = KT_DB::prepare('SELECT DATE(MAX(log_time)) FROM `##log`')->execute([])->fetchOne();

// Filtering
$action   = KT_Filter::get('action');
$from     = KT_Filter::get('from', '\d\d\d\d-\d\d-\d\d', $earliest);
$to       = KT_Filter::get('to', '\d\d\d\d-\d\d-\d\d', $latest);
$type     = KT_Filter::get('type', 'auth|change|config|debug|edit|error|media|search|spam');
$text     = KT_Filter::get('text');
$ip       = KT_Filter::get('ip');
$user     = KT_Filter::get('user');
if (KT_USER_IS_ADMIN) {
	// Administrators can see all logs
	$gedc = KT_Filter::get('gedc');
} else {
	// Managers can only see logs relating to this gedcom
	$gedc = KT_GEDCOM;
}

switch ($action) {
	case 'delete':
		$return =  KT_DataTables_AdminSiteLog::deleteLog($from, $to, $type, $text, $ip, $user, $gedc);

		if ($return > 0) {
			KT_FlashMessages::addMessage(KT_I18N::translate('The selected entries are deleted'));
		}
		break;

	case 'loadrows':

		$search    = KT_Filter::post('sSearch', '');
		$start     = KT_Filter::postInteger('iDisplayStart');
		$length    = KT_Filter::postInteger('iDisplayLength');
		$isort     = KT_Filter::postInteger('iSortingCols');
		$draw      = KT_Filter::postInteger('sEcho');
		$colsort   = [];
		$sortdir   = [];
		for ($i = 0; $i < $isort; ++$i) {
			$colsort[$i] = KT_Filter::postInteger('iSortCol_' . $i);
			$sortdir[$i] = KT_Filter::post('sSortDir_' . $i);
		}

		Zend_Session::writeClose();
		header('Content-type: application/json');
		echo json_encode( KT_DataTables_AdminSiteLog::siteLog(
			$from,
			$to,
			$type,
			$text,
			$ip,
			$user,
			$gedc,
			$search,
			$start,
			$length,
			$isort,
			$draw,
			$colsort,
			$sortdir
		));
		exit;
}

// Access default datatables settings
include_once KT_ROOT . 'library/KT/DataTables/KTdatatables.js.php';

$controller
	->pageHeader()
	->addExternalJavascript(KT_DATATABLES_KT_JS)
	->addExternalJavascript(KT_DATEPICKER_JS)
	->addExternalJavascript(KT_DATEPICKER_JS_LOCALE)
	->addInlineJavascript('
		datables_defaults("' . KT_SCRIPT_NAME . '?action=loadrows&from=' . $from . '&to=' . $to . '&type=' . $type . '&text=' . rawurlencode((string) $text) . '&ip=' . rawurlencode((string) $ip) . '&user=' . rawurlencode((string) $user) . '&gedc=' . rawurlencode((string) $gedc) . '");

		jQuery("#log_list").dataTable({
			dom: \'<"top"pB<"clear">irl>t<"bottom"pl>\',
			sorting: [[ 0, "desc" ]],
			columns: [
				/* 0 - Timestamp   */ { },
				/* 1 - Type        */ { },
				/* 2 - message     */ {class: "message_col" },
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
	')
;

$users_array = array_combine(get_all_users(), get_all_users());
uksort($users_array, 'strnatcasecmp');

echo relatedPages($site_tools, KT_SCRIPT_NAME);

echo pageStart('site_logs', $controller->getPageTitle()); ?>

	<form class="cell" name="logs" method="get" action="<?php echo KT_SCRIPT_NAME; ?>" data-abide novalidate>
		<input type="hidden" name="action" value="show">
		<div class="grid-x grid-margin-x">
			<div class="cell medium-3 medium-offset-1">
				<label class="h6"><?php echo KT_I18N::translate('From'); ?></label>
				<div class="date fdatepicker" id="from" data-date-format="yyyy-mm-dd">
					<div class="input-group">
						<input class="input-group-field" type="text" name="from" value="<?php echo htmlspecialchars((string) $from); ?>">
						<span class="postfix input-group-label"><i class="<?php echo $iconStyle; ?> fa-calendar-days fa-lg"></i></span>
					</div>
				</div>
			</div>
			<div class="cell medium-3">
				<label class="h6"><?php echo KT_I18N::translate('To'); ?></label>
				<div class="date fdatepicker" id="to" data-date-format="yyyy-mm-dd">
					<div class="input-group">
						<input class="input-group-field" type="text" name="to" value="<?php echo htmlspecialchars((string) $to); ?>">
						<span class="postfix input-group-label"><i class="<?php echo $iconStyle; ?> fa-calendar-days fa-lg"></i></span>
					</div>
				</div>
			</div>
			<div class="cell medium-2">
				<label class="h6"><?php echo KT_I18N::translate('Type'); ?></label>
				<?php echo select_edit_control('type', ['' => '', 'auth' => 'auth', 'config' => 'config', 'debug' => 'debug', 'edit' => 'edit', 'error' => 'error', 'media' => 'media', 'search' => 'search', 'spam' => 'spam'], null, $type, ''); ?>
			</div>
			<div class="cell medium-2">
				<label class="h6"><?php echo KT_I18N::translate('IP address'); ?></label>
				<input class="log-filter" type="text" name="ip" value="<?php echo htmlspecialchars((string) $ip); ?>">
			</div>
			<div class="cell medium-4 medium-offset-1">
				<label class="h6"><?php echo KT_I18N::translate('Message'); ?></label>
				<input class="log-filter" type="text" name="text" value="<?php echo htmlspecialchars((string) $text); ?>">
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
					<i class="<?php echo $iconStyle; ?> fa-magnifying-glass"></i>
					<?php echo KT_I18N::translate('Search'); ?>
				</button>
				<button type="submit" class="button" <?php echo 'onclick="if (confirm(\'' . htmlspecialchars(KT_I18N::translate('Permanently delete these records?')) . '\')) {document.logs.action.value=\'delete\';return true;} else {return false;}"' . ('show' == $action ? '' : 'disabled="disabled"'); ?> >
					<i class="<?php echo $iconStyle; ?> fa-trash-can"></i>
					<?php echo KT_I18N::translate('Delete results'); ?>
				</button>
			</div>
		</div>
	</form>
	<hr class="cell">
	<?php if ($action == 'show') { ?>
		<div class="cell grid-x grid-margin-x">
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
	<?php }

	echo pageClose();
