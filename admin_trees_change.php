<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_trees_change.php');
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Family tree changes'));

require KT_ROOT.'includes/functions/functions_edit.php';
require_once KT_ROOT.'library/php-diff/lib/Diff.php';
require_once KT_ROOT.'library/php-diff/lib/Diff/Renderer/Html/SideBySide.php';

$statuses = array(
	''			=> KT_I18N::translate('All'),
	'accepted'	=> /* I18N: the status of an edit accepted/rejected/pending */ KT_I18N::translate('accepted'),
	'rejected'	=> /* I18N: the status of an edit accepted/rejected/pending */ KT_I18N::translate('rejected'),
	'pending' 	=> /* I18N: the status of an edit accepted/rejected/pending */ KT_I18N::translate('pending' ),
);

$earliest	= KT_DB::prepare("SELECT DATE(MIN(change_time)) FROM `##change`")->execute(array())->fetchOne();
$latest		= KT_DB::prepare("SELECT DATE(MAX(change_time)) FROM `##change`")->execute(array())->fetchOne();

// Filtering
$action	= KT_Filter::get('action');
$from	= KT_Filter::get('from', '\d\d\d\d-\d\d-\d\d', $earliest);
$to		= KT_Filter::get('to',   '\d\d\d\d-\d\d-\d\d', $latest);
$type	= KT_Filter::get('type', 'accepted|rejected|pending');
$oldged	= KT_Filter::get('oldged');
$newged	= KT_Filter::get('newged');
$xref	= KT_Filter::get('xref', KT_REGEX_XREF);
$user	= KT_Filter::get('user');
if (KT_USER_IS_ADMIN) {
	// Administrators can see all logs
	$gedc = KT_Filter::get('gedc');
} else {
	// Managers can only see logs relating to this gedcom
	$gedc = KT_GEDCOM;
}

$query	= array();
$args	= array();
if ($from) {
	$query[]	= 'change_time>=?';
	$args[]		= $from;
}
if ($to) {
	$query[]	= 'change_time<TIMESTAMPADD(DAY, 1 , ?)'; // before end of the day
	$args []	= $to;
}
if ($type) {
	$query[]	= 'status=?';
	$args []	= $type;
}
if ($oldged) {
	$query[]	= "old_gedcom LIKE CONCAT('%', ?, '%')";
	$args []	= $oldged;
}
if ($newged) {
	$query[]	= "new_gedcom LIKE CONCAT('%', ?, '%')";
	$args []	= $newged;
}
if ($xref) {
	$query[]	= "xref = ?";
	$args []	= $xref;
}
if ($user) {
	$query[]	= "user_name LIKE CONCAT('%', ?, '%')";
	$args []	= $user;
}
if ($gedc) {
	$query[]	= "gedcom_name LIKE CONCAT('%', ?, '%')";
	$args []	= $gedc;
}

$SELECT1 =
	"SELECT SQL_CALC_FOUND_ROWS change_time, status, xref, old_gedcom, new_gedcom, IFNULL(user_name, '<none>') AS user_name, IFNULL(gedcom_name, '<none>') AS gedcom_name".
	" FROM `##change`".
	" LEFT JOIN `##user`   USING (user_id)".   // user may be deleted
	" LEFT JOIN `##gedcom` USING (gedcom_id)"; // gedcom may be deleted
$SELECT2 =
	"SELECT COUNT(*) FROM `##change`".
	" LEFT JOIN `##user`   USING (user_id)".   // user may be deleted
	" LEFT JOIN `##gedcom` USING (gedcom_id)"; // gedcom may be deleted
if ($query) {
	$WHERE = " WHERE ".implode(' AND ', $query);
} else {
	$WHERE = '';
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
case 'export':
	Zend_Session::writeClose();
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="kiwitrees-changes.csv"');
	$rows=KT_DB::prepare($SELECT1.$WHERE.' ORDER BY change_id')->execute($args)->fetchAll();
	foreach ($rows as $row) {
		$row->old_gedcom = str_replace('"', '""', $row->old_gedcom);
		$row->old_gedcom = str_replace("\n", '""', $row->old_gedcom);
		$row->new_gedcom = str_replace('"', '""', $row->new_gedcom);
		$row->new_gedcom = str_replace("\n", '""', $row->new_gedcom);
		echo
			'"', $row->change_time, '",',
			'"', $row->status, '",',
			'"', $row->xref, '",',
			'"', $row->old_gedcom, '",',
			'"', $row->new_gedcom, '",',
			'"', str_replace('"', '""', $row->user_name), '",',
			'"', str_replace('"', '""', $row->gedcom_name), '"',
			"\n";
	}
	exit;
case 'load_json':
	Zend_Session::writeClose();
	$iDisplayStart	= (int) KT_Filter::get('iDisplayStart');
	$iDisplayLength	= (int) KT_Filter::get('iDisplayLength');
	set_user_setting(KT_USER_ID, 'admin_site_change_page_size', $iDisplayLength);
	if ($iDisplayLength>0) {
		$LIMIT = " LIMIT " . $iDisplayStart . ',' . $iDisplayLength;
	} else {
		$LIMIT = "";
	}
	$iSortingCols = KT_Filter::get('iSortingCols');
	if ($iSortingCols) {
		$ORDER_BY = ' ORDER BY ';
		for ($i = 0; $i < $iSortingCols; ++$i) {
			// Datatables numbers columns 0, 1, 2, ...
			// MySQL numbers columns 1, 2, 3, ...
			switch ( KT_Filter::get('sSortDir_' . $i)) {
			case 'asc':
				if ((int) KT_Filter::get('iSortCol_' . $i) == 0) {
					$ORDER_BY .= 'change_id ASC '; // column 0 is "timestamp", using change_id gives the correct order for events in the same second
				} else {
					$ORDER_BY .= (1 + (int) KT_Filter::get('iSortCol_' . $i)) . ' ASC ';
				}
				break;
			case 'desc':
				if ((int) KT_Filter::get('iSortCol_' . $i) == 0) {
					$ORDER_BY .= 'change_id DESC ';
				} else {
					$ORDER_BY .= (1 + (int) KT_Filter::get('iSortCol_' . $i)) . ' DESC ';
				}
				break;
			}
			if ($i < $iSortingCols - 1) {
				$ORDER_BY .= ',';
			}
		}
	} else {
		$ORDER_BY = '1 DESC';
	}

	// This becomes a JSON list, not array, so need to fetch with numeric keys.
	$aaData = KT_DB::prepare($SELECT1.$WHERE.$ORDER_BY.$LIMIT)->execute($args)->fetchAll(PDO::FETCH_NUM);
	foreach ($aaData as &$row) {

		$a = explode("\n", htmlspecialchars($row[3]));
		$b = explode("\n", htmlspecialchars($row[4]));

		// Generate a side by side diff
		$renderer = new Diff_Renderer_Html_SideBySide;

		// Options for generating the diff
		$options = array(
			//'ignoreWhitespace' => true,
			//'ignoreCase' => true,
		);

		// Initialize the diff class
		$diff = new Diff($a, $b, $options);

		$row[1] = KT_I18N::translate($row[1]);
		$row[2] = '<a href="gedrecord.php?pid=' . $row[2] . '&ged=' . $row[6] . '" target="_blank" rel="noopener noreferrer">' . $row[2] . '</a>';
		$row[3] = $diff->Render($renderer);
		$row[4] = '';
	}

	// Total filtered/unfiltered rows
	$iTotalDisplayRecords	= KT_DB::prepare("SELECT FOUND_ROWS()")->fetchColumn();
	$iTotalRecords			= KT_DB::prepare($SELECT2.$WHERE)->execute($args)->fetchColumn();

	header('Content-type: application/json');
	echo json_encode(array( // See http://www.datatables.net/usage/server-side
		'sEcho'					=> (int) KT_Filter::get('sEcho'),
		'iTotalRecords'			=> $iTotalRecords,
		'iTotalDisplayRecords'	=> $iTotalDisplayRecords,
		'aaData'				=> $aaData
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
		var oTable=jQuery("#change_list").dataTable( {
			dom: \'<"top"pBf<"clear">irl>t<"bottom"pl>\',
			buttons: [{extend: "csvHtml5"}],
			autoWidth: false,
			processing: true,
			displayLength: ' . get_user_setting(KT_USER_ID, 'admin_site_log_page_size', 10) . ',
			serverSide: true,
			pagingType: "full_numbers",
			"sAjaxSource": "' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '?action=load_json&from='.$from.'&to='.$to.'&type='.$type.'&oldged='.rawurlencode($oldged).'&newged='.rawurlencode($newged).'&xref='.rawurlencode($xref).'&user='.rawurlencode($user).'&gedc='.rawurlencode($gedc).'",
			' . KT_I18N::datatablesI18N(array(10,20,50,100,500,1000,-1)) . ',
			"aaSorting": [[ 0, "desc" ]],
			"aoColumns": [
				/* 0 - Timestamp   */ {},
				/* 1 - Status      */ {},
				/* 2 - Record      */ {},
				/* 3 - Old data    */ {"sClass":"raw_gedcom"},
				/* 4 - New data    */ { bVisible:false },
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

$url =
	KT_SCRIPT_NAME.'?from='.rawurlencode($from).
	'&amp;to='.rawurlencode($to).
	'&amp;type='.rawurlencode($type).
	'&amp;oldged='.rawurlencode($oldged).
	'&amp;newged='.rawurlencode($newged).
	'&amp;xref='.rawurlencode($xref).
	'&amp;user='.rawurlencode($user).
	'&amp;gedc='.rawurlencode($gedc);

$users_array=array_combine(get_all_users(), get_all_users());
uksort($users_array, 'strnatcasecmp');
?>
<div id="tree_changes-page" class="cell">
	<h4><?php echo $controller->getPageTitle(); ?></h4>
	<div class="grid-x grid-margin-y">
		<form class="cell" name="changes" method="get" action="<?php echo KT_SCRIPT_NAME; ?>" data-abide novalidate>
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
					<?php echo select_edit_control('type', $statuses, null, $type, ''); ?>
				</div>
				<div class="cell medium-2">
					<label class="h6"><?php echo KT_I18N::translate('Record'); ?></label>
					<input class="log-filter" type="text" name="xref" value="<?php echo htmlspecialchars($xref); ?>">
				</div>
				<div class="cell medium-3 medium-offset-2">
					<label class="h6"><?php echo KT_I18N::translate('Old data'); ?></label>
					<input class="log-filter" type="text" name="oldged" value="<?php echo htmlspecialchars($oldged); ?>">
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
		</form>
	</div>
	<hr>
	<?php if ($action) { ?>
		<div class="grid-x grid-margin-x">
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
		</div>
	<?php } ?>
</div>
<?php
