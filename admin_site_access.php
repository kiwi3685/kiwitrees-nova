<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_site_access.php');

global $iconStyle;

require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->addExternalJavascript(KT_JQUERY_JEDITABLE_URL)
	->setPageTitle(KT_I18N::translate('Site access rules'));

$action = KT_Filter::get('action');
switch ($action) {
case 'reset':
	KT_DB::exec("DELETE FROM `##site_access_rule` WHERE rule<>'unknown'");
	KT_DB::exec(
		"INSERT IGNORE INTO `##site_access_rule` (user_agent_pattern, rule, comment) VALUES".
		" ('Mozilla/5.0 (%) Gecko/% %/%', 'allow', 'Gecko-based browsers'),".
		" ('Mozilla/5.0 (%) AppleWebKit/% (KHTML, like Gecko)%', 'allow', 'WebKit-based browsers'),".
		" ('Opera/% (%) Presto/% Version/%', 'allow', 'Presto-based browsers'),".
		" ('Mozilla/% (compatible; MSIE %', 'allow', 'Trident-based browsers'),".
		" ('Mozilla/% (Windows%; Trident%; rv:%) like Gecko', 'allow', 'Modern Internet Explorer'),".
		" ('Mozilla/5.0 (compatible; Konqueror/%', 'allow', 'Konqueror browser')"
	);
	break;
case 'purge':
	KT_DB::exec("DELETE FROM `##site_access_rule` WHERE rule='unknown'");
	break;
case 'delete':
	$user_access_rule_id=KT_Filter::get('site_access_rule_id');
	KT_DB::prepare("DELETE FROM `##site_access_rule` WHERE site_access_rule_id=?")->execute(array($user_access_rule_id));
	break;
case 'allow':
case 'deny':
case 'robot':
	$user_access_rule_id = KT_Filter::get('site_access_rule_id');
	KT_DB::prepare("UPDATE `##site_access_rule` SET rule=? WHERE site_access_rule_id=?")->execute(array($action, $user_access_rule_id));
	break;
case 'load_rules':
	Zend_Session::writeClose();
	// AJAX callback for datatables
	$sql=
		"SELECT SQL_CALC_FOUND_ROWS".
		" INET_NTOA(ip_address_start), ip_address_start, INET_NTOA(ip_address_end), ip_address_end, user_agent_pattern, rule, comment, site_access_rule_id".
		" FROM `##site_access_rule`".
		" WHERE rule<>'unknown'";
	$args = array();

	$sSearch = KT_Filter::get('sSearch');
	if ($sSearch) {
		$sql .=
			" AND (INET_ATON(?) BETWEEN ip_address_start AND ip_address_end".
			" OR INET_NTOA(ip_address_start) LIKE CONCAT('%', ?, '%')".
			" OR INET_NTOA(ip_address_end) LIKE CONCAT('%', ?, '%')".
			" OR user_agent_pattern LIKE CONCAT('%', ?, '%')".
			" OR comment LIKE CONCAT('%', ?, '%'))";
		$args[] = $sSearch;
		$args[] = $sSearch;
		$args[] = $sSearch;
		$args[] = $sSearch;
		$args[] = $sSearch;
	}

	$iSortingCols = KT_Filter::get('iSortingCols');
	if ($iSortingCols) {
		$sql .=" ORDER BY ";
		for ($i = 0; $i < $iSortingCols; ++$i) {
			// Datatables numbers columns 0, 1, 2, ...
			// MySQL numbers columns 1, 2, 3, ...
			switch (KT_Filter::get('sSortDir_' . $i)) {
			case 'asc':
				$sql.=(1+(int)KT_Filter::get('iSortCol_' . $i)).' ASC ';
				break;
			case 'desc':
				$sql.=(1+(int)KT_Filter::get('iSortCol_' . $i)).' DESC ';
				break;
			}
			if ($i < $iSortingCols - 1) {
				$sql .= ',';
			}
		}
	} else {
		$sql .= " ORDER BY updated DESC";
	}

	$iDisplayStart  = (int)KT_Filter::get('iDisplayStart');
	$iDisplayLength = (int)KT_Filter::get('iDisplayLength');
	if ($iDisplayLength > 0) {
		$sql .= " LIMIT " . $iDisplayStart . ',' . $iDisplayLength;
	}

	// This becomes a JSON list, not a JSON array, so we need numeric keys.
	$aaData = KT_DB::prepare($sql)->execute($args)->fetchAll(PDO::FETCH_NUM);
	// Reformat the data for display
	foreach ($aaData as &$row) {
		$site_access_rule_id = $row[7];
		$user_agent = $row[4];
		$row[0] = edit_field_inline('site_access_rule-ip_address_start-' . $site_access_rule_id, $row[0]);
		$row[2] = edit_field_inline('site_access_rule-ip_address_end-' . $site_access_rule_id, $row[2]);
		$row[4] = edit_field_inline('site_access_rule-user_agent_pattern-' . $site_access_rule_id, $row[4]);
		$row[5] = select_edit_control_inline('site_access_rule-rule-' . $site_access_rule_id, array(
			'allow'=>/* I18N: An access rule - allow access to the site */ KT_I18N::translate('allow'),
			'deny' =>/* I18N: An access rule - deny access to the site */  KT_I18N::translate('deny'),
			'robot'=>/* I18N: http://en.wikipedia.org/wiki/Web_crawler */  KT_I18N::translate('robot'),
		), null, $row[5]);
		$row[6] = edit_field_inline('site_access_rule-comment-'.$site_access_rule_id, $row[6]);
		$row[7] = '<i class="icon-delete" onclick="if (confirm(\'' . htmlspecialchars(KT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($user_agent))).'\')) { document.location=\''.KT_SCRIPT_NAME.'?action=delete&amp;site_access_rule_id='.$site_access_rule_id.'\'; }"></i>';
	}

	// Total filtered rows
	$iTotalDisplayRecords = KT_DB::prepare("SELECT FOUND_ROWS()")->fetchColumn();
	// Total unfiltered rows
	$iTotalRecords = KT_DB::prepare("SELECT COUNT(*) FROM `##site_access_rule` WHERE rule <> 'unknown'")->fetchColumn();

	header('Content-type: application/json');
	echo json_encode(array( // See http://www.datatables.net/usage/server-side
		'sEcho'                => (int)KT_Filter::get('sEcho'),
		'iTotalRecords'        => $iTotalRecords,
		'iTotalDisplayRecords' => $iTotalDisplayRecords,
		'aaData'               => $aaData
	));
	exit;
case 'load_unknown':
	Zend_Session::writeClose();
	// AJAX callback for datatables
	$sql =
		"SELECT SQL_CALC_FOUND_ROWS".
		" INET_NTOA(ip_address_start), ip_address_start, user_agent_pattern, site_access_rule_id".
		" FROM `##site_access_rule`".
		" WHERE rule='unknown'";
	$args = array();

	$sSearch = KT_Filter::get('sSearch');
	if ($sSearch) {
		$sql .=
			" AND (INET_ATON(ip_address_start) LIKE CONCAT('%', ?, '%')".
			" OR user_agent_pattern LIKE CONCAT('%', ?, '%'))";
		$args[]=$sSearch;
		$args[]=$sSearch;
	}

	$iSortingCols = KT_Filter::get('iSortingCols');
	if ($iSortingCols) {
		$sql.=" ORDER BY ";
		for ($i=0; $i<$iSortingCols; ++$i) {
			// Datatables numbers columns 0, 1, 2, ...
			// MySQL numbers columns 1, 2, 3, ...
			switch (KT_Filter::get('sSortDir_'.$i)) {
			case 'asc':
				$sql .= (1+(int)KT_Filter::get('iSortCol_'.$i)).' ASC ';
				break;
			case 'desc':
				$sql .= (1+(int)KT_Filter::get('iSortCol_'.$i)).' DESC ';
				break;
			}
			if ($i < $iSortingCols-1) {
				$sql .= ',';
			}
		}
	} else {
		$sql .= " ORDER BY updated DESC";
	}

	$iDisplayStart  = (int)KT_Filter::get('iDisplayStart');
	$iDisplayLength = (int)KT_Filter::get('iDisplayLength');
	if ($iDisplayLength > 0) {
		$sql .= " LIMIT " . $iDisplayStart . ',' . $iDisplayLength;
	}

	// This becomes a JSON list, not a JSON array, so we need numeric keys.
	$aaData = KT_DB::prepare($sql)->execute($args)->fetchAll(PDO::FETCH_NUM);
	// Reformat the data for display
	foreach ($aaData as &$row) {
		$site_access_rule_id = $row[3];
		$row[3] = '<i class="' . $iconStyle . ' fa-check" onclick="document.location=\'' . KT_SCRIPT_NAME.'?action=allow&amp;site_access_rule_id=' . $site_access_rule_id . '\';"></i>';
		$row[4] = '<i class="' . $iconStyle . ' fa-check" onclick="document.location=\'' . KT_SCRIPT_NAME.'?action=deny&amp;site_access_rule_id=' . $site_access_rule_id . '\';"></i>';
		$row[5] = '<i class="' . $iconStyle . ' fa-check" onclick="document.location=\'' . KT_SCRIPT_NAME.'?action=robot&amp;site_access_rule_id=' . $site_access_rule_id . '\';"></i>';
	}

	// Total filtered rows
	$iTotalDisplayRecords = KT_DB::prepare("SELECT FOUND_ROWS()")->fetchColumn();
	// Total unfiltered rows
	$iTotalRecords = KT_DB::prepare("SELECT COUNT(*) FROM `##site_access_rule` WHERE rule = 'unknown'")->fetchColumn();

	header('Content-type: application/json');
	echo json_encode(array( // See http://www.datatables.net/usage/server-side
		'sEcho'                => (int)KT_Filter::get('sEcho'),
		'iTotalRecords'        => $iTotalRecords,
		'iTotalDisplayRecords' => $iTotalDisplayRecords,
		'aaData'               => $aaData
	));
	exit;
}

$controller
	->pageHeader()
	->addExternalJavascript(KT_DATATABLES_JS)
	->addInlineJavascript('
		jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
		jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
		jQuery("#site_access_rules").dataTable({
			dom: \'<"top"Blp<"clear">irf>t<"bottom"pl>\',
			' . KT_I18N::datatablesI18N() . ',
			buttons: [{extend: "csv", exportOptions: {columns: [0,1,4,6,9,11,12,15,17] }}],
			autoWidth: false,
			processing: true,
			retrieve: true,
			displayLength: 20,
			pagingType: "full_numbers",
			stateSave: true,
			stateSaveParams: function (settings, data) {
				data.columns.forEach(function(column) {
					delete column.search;
				});
			},
			stateDuration: -1,
			columns: [
				/* 0 ip_address_start        */ {dataSort: 1, class: "ip_address"},
				/* 1 ip_address_start (sort) */ {type: "numeric", visible: false},
				/* 2 ip_address_end          */ {dataSort: 3, class: "ip_address"},
				/* 3 ip_address_end (sort)   */ {type: "numeric", visible: false},
				/* 4 user_agent_pattern      */ {class: "ua_string"},
				/* 5 rule                    */ {class: "rule"},
				/* 6 comment                 */ {class: "comment"},
				/* 7 <delete>                */ {sortable: false, class: "center delete"}
			],
			"fnDrawCallback": function() {
				// Our JSON responses include Javascript as well as HTML.  This does not get
				// executed, So extract it, and execute it
				jQuery("#site_access_rules script").each(function() {
					eval(this.text);
				});
			}
		});
		jQuery("#unknown_site_visitors").dataTable({
			dom: \'<"top"Blp<"clear">irf>t<"bottom"pl>\',
			' . KT_I18N::datatablesI18N() . ',
			buttons: [{extend: "csv", exportOptions: {columns: [0,1,4,6,9,11,12,15,17] }}],
			autoWidth: false,
			processing: true,
			retrieve: true,
			displayLength: 20,
			pagingType: "full_numbers",
			stateSave: true,
			stateSaveParams: function (settings, data) {
				data.columns.forEach(function(column) {
					delete column.search;
				});
			},
			stateDuration: -1,
			columns: [
				/* 0 ip_address         */ {dataSort: 1, class: "ip_address"},
				/* 0 ip_address (sort)  */ {type: "numeric", "bVisible": false},
				/* 1 user_agent_pattern */ {class: "ua_string"},
				/* 2 <allowed>          */ {sortable: false, class: "center"},
				/* 3 <banned>           */ {sortable: false, class: "center"},
				/* 4 <search-engine>    */ {sortable: false, class: "center"}
			]
		});
	');

// Delete any "unknown" visitors that are now "known".
// This could happen every time we create/update a rule.
KT_DB::exec(
	"DELETE unknown".
	" FROM `##site_access_rule` AS unknown".
	" JOIN `##site_access_rule` AS known ON (unknown.user_agent_pattern LIKE known.user_agent_pattern)".
	" WHERE unknown.rule='unknown' AND known.rule<>'unknown'".
	" AND unknown.ip_address_start BETWEEN known.ip_address_start AND known.ip_address_end"
);

?>
<div id="site_access-page" class="cell">
	<div class="grid-x grid-margin-y">
		<div class="cell">
			<h4 class="inline"><?php echo /* I18N: http://en.wikipedia.org/wiki/User_agent */ KT_I18N::translate('Restrict access to the site, using IP addresses and user-agent strings'); ?></h4>
			<?php echo faqLink('general/site-access-rules/'); ?>
		</div>
		<div class="cell callout primary">
			<?php echo KT_I18N::translate('The following rules are used to decide whether a visitor is a human being (allow full access), a search-engine robot (allow restricted access) or an unwanted crawler (deny all access).'); ?>
		</div>
		<div class="cell">
			<table id="site_access_rules">
				<thead>
					<tr>
						<th><?php echo /* I18N: [...] of a range of addresses */ KT_I18N::translate('Start IP address'); ?></th>
						<th>-</th>
						<th><?php echo /* I18N: [...] of a range of addresses */ KT_I18N::translate('End IP address'); ?></th>
						<th>-</th>
						<th><?php echo /* I18N: http://en.wikipedia.org/wiki/User_agent_string */ KT_I18N::translate('User-agent string'); ?></th>
						<th><?php echo /* I18N: noun */ KT_I18N::translate('Rule'); ?></th>
						<th><?php echo KT_I18N::translate('Comment'); ?></th>
						<th><?php echo KT_I18N::translate('Delete'); ?></th>
					</tr>
				</thead>
			</table>
		</div>
		<div class="cell">
			<button type="submit" class="button" <?php echo 'onclick="if (confirm(\''.htmlspecialchars(KT_I18N::translate('This will delete all your access rules and replace with basic kiwitrees defaults. Are you sure?')).'\')) { document.location=\''.KT_SCRIPT_NAME.'?action=reset\'; }"';?> >
				<i class="far fa-undo"></i>
				<?php echo KT_I18N::translate('Reset'); ?>
			</button>
			<hr>
		</div>
		<div class="cell callout primary">
			<?php echo KT_I18N::translate('The following visitors were not recognised, and were assumed to be search engines.'); ?>
		</div>
		<div class="cell">
			<table id="unknown_site_visitors">
		<thead>
			<tr>
				<th rowspan="2"><?php /* I18N: http://en.wikipedia.org/wiki/IP_address */ echo KT_I18N::translate('IP address'); ?></th>
				<th rowspan="2">-</th>
				<th rowspan="2"><?php echo KT_I18N::translate('User-agent string'); ?></th>
				<th colspan="3"><?php echo KT_I18N::translate('Create a new rule'); ?></th>
			</tr>
			<tr>
				<th><?php echo KT_I18N::translate('allow'); ?></th>
				<th><?php echo KT_I18N::translate('deny'); ?></th>
				<th><?php echo KT_I18N::translate('robot'); ?></th>
			</tr>
		</thead>
	</table>
		</div>
		<div class="cell">
			<button type="submit" class="button" <?php echo 'onclick="if (confirm(\''.htmlspecialchars(KT_I18N::translate('Are you sure you want to delete all visitors not recognised?')).'\')) { document.location=\''.KT_SCRIPT_NAME.'?action=purge\'; }"';?> >
				<i class="far fa-trash-alt"></i>
				<?php echo KT_I18N::translate('Delete'); ?>
			</button>
		</div>
	</div>
</div>
