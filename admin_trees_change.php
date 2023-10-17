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

define('KT_SCRIPT_NAME', 'admin_trees_change.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';

global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Family tree changes'));


$statuses = array(
	''			=> KT_I18N::translate('All'),
	'accepted'	=> /* I18N: the status of an edit accepted/rejected/pending */ KT_I18N::translate('accepted'),
	'rejected'	=> /* I18N: the status of an edit accepted/rejected/pending */ KT_I18N::translate('rejected'),
	'pending' 	=> /* I18N: the status of an edit accepted/rejected/pending */ KT_I18N::translate('pending' ),
);

$earliest = KT_DB::prepare("SELECT DATE(MIN(change_time)) FROM `##change`")->execute(array())->fetchOne();
$latest   = KT_DB::prepare("SELECT DATE(MAX(change_time)) FROM `##change`")->execute(array())->fetchOne();

$action   = KT_Filter::get('action');
$from     = KT_Filter::get('from', '\d\d\d\d-\d\d-\d\d', $earliest);
$to       = KT_Filter::get('to',   '\d\d\d\d-\d\d-\d\d', $latest);
$type     = KT_Filter::get('type', 'accepted|rejected|pending');
$oldged   = KT_Filter::get('oldged');
$newged   = KT_Filter::get('newged');
$xref     = KT_Filter::get('xref', KT_REGEX_XREF);
$user     = KT_Filter::get('user');
if (KT_USER_IS_ADMIN) {
	// Administrators can see all logs
	$gedc = KT_Filter::get('gedc');
} else {
	// Managers can only see logs relating to this gedcom
	$gedc = KT_GEDCOM;
}

switch($action) {
	case 'delete':
		$DELETE=
			"DELETE `##change` FROM `##change`".
			" LEFT JOIN `##user`   USING (user_id)".   // user may be deleted
			" LEFT JOIN `##gedcom` USING (gedcom_id)". // gedcom may be deleted
			$WHERE;
		KT_DB::prepare($DELETE)->execute($args);
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
		echo json_encode( KT_DataTables_AdminChangeLog::changeLog(
			$from,
			$to,
			$type,
			$oldged,
			$newged,
			$xref,
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
		datatable_defaults("' . KT_SCRIPT_NAME . '?action=loadrows&from=' . $from . '&to=' . $to . '&type=' . $type . '&oldged=' . rawurlencode((string) $oldged) . '&newged=' . rawurlencode((string) $newged) . '&xref=' . rawurlencode((string) $xref) . '&user=' . rawurlencode((string) $user) . '&gedc=' . rawurlencode((string) $gedc) . '");

		jQuery("#change_list").dataTable( {
			sorting: [[ 0, "desc" ]],
			columns: [
				/* 0 - Timestamp   */ {},
				/* 1 - Status      */ {},
				/* 2 - Record      */ {},
				/* 3 - Old data    */ {class:"raw_gedcom"},
				/* 4 - New data    */ { visible:false },
				/* 5 - User        */ {},
				/* 6 - Family tree */ {}
			]
		});

		jQuery(".fdatepicker").fdatepicker({
			startDate: "' . $earliest . '",
			endDate: "' . $latest . '",
			language: "' . KT_LOCALE . '"
		});

	');

$users_array = array_combine(get_all_users(), get_all_users());
uksort($users_array, 'strnatcasecmp');

// Start page display
echo relatedPages($trees, KT_SCRIPT_NAME);

echo pageStart('admin_trees_change', $controller->getPageTitle()); ?>

	<form class="cell" name="changes" method="get" action="<?php echo KT_SCRIPT_NAME; ?>" data-abide novalidate>
		<div class="grid-x">
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
					<?php echo select_edit_control('type', $statuses, null, $type, ''); ?>
				</div>
				<div class="cell medium-2">
					<label class="h6"><?php echo KT_I18N::translate('Record'); ?></label>
					<input class="log-filter" type="text" name="xref" value="<?php echo htmlspecialchars((string) $xref); ?>">
				</div>
				<div class="cell medium-3 medium-offset-2">
					<label class="h6"><?php echo KT_I18N::translate('Old data'); ?></label>
					<input class="log-filter" type="text" name="oldged" value="<?php echo htmlspecialchars((string) $oldged); ?>">
				</div>
				<div class="cell medium-3">
					<label class="h6"><?php echo KT_I18N::translate('User'); ?></label>
					<?php echo select_edit_control('user', $users_array, '', $user, ''); ?>
				</div>
				<div class="cell medium-2">
					<label class="h6"><?php echo KT_I18N::translate('Family tree'); ?></label>
					<?php echo select_edit_control('gedc', KT_Tree::getNameList(), '', $gedc, KT_USER_IS_ADMIN ? '' : 'disabled'); ?>
				</div>
				<div class="cell medium-4">
					<button type="submit" class="button">
						<i class="<?php echo $iconStyle; ?> fa-magnifying-glass"></i>
						<?php echo KT_I18N::translate('Search'); ?>
					</button>
					<button type="submit" class="button" <?php echo 'onclick="if (confirm(\'' . htmlspecialchars(KT_I18N::translate('Permanently delete these records?')) . '\')) {document.logs.action.value=\'delete\';return true;} else {return false;}"' . ($action=='show' ? '' : 'disabled="disabled"');?> >
						<i class="<?php echo $iconStyle; ?> fa-trash-can"></i>
						<?php echo KT_I18N::translate('Delete'); ?>
					</button>
				</div>
			</div>
		</div>
	</form>
	<hr class="cell">
	<?php if ($action) { ?>
		<div class="cell">
			<table id="change_list">
				<thead>
					<tr>
						<th><?php echo KT_I18N::translate('Timestamp'); ?></th>
						<th><?php echo KT_I18N::translate('Status'); ?></th>
						<th><?php echo KT_I18N::translate('Record'); ?></th>
						<th class="text-center"><?php echo KT_I18N::translate('GEDCOM Data'); ?></th>
						<th></th>
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
