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

define('KT_SCRIPT_NAME', 'admin_site_access.php');
require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';

global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
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

	case 'loadrows1':
		Zend_Session::writeClose();

		$search    = KT_Filter::get('sSearch', '');
		$start     = KT_Filter::getInteger('iDisplayStart');
		$length    = KT_Filter::getInteger('iDisplayLength');
		$isort     = KT_Filter::getInteger('iSortingCols');
		$draw      = KT_Filter::getInteger('sEcho');
		$colsort   = [];
		$sortdir   = [];
		for ($i = 0; $i < $isort; ++$i) {
			$colsort[$i] = KT_Filter::getInteger('iSortCol_' . $i);
			$sortdir[$i] = KT_Filter::get('sSortDir_' . $i);
		}

		Zend_Session::writeClose();
		header('Content-type: application/json');
		echo json_encode( KT_DataTables_AdminAccess::accessOne(
			$search,
			$start,
			$length,
			$isort,
			$draw,
			$colsort,
			$sortdir
		));
		exit;

	case 'loadrows2':
		Zend_Session::writeClose();

		$search    = KT_Filter::get('sSearch', '');
		$start     = KT_Filter::getInteger('iDisplayStart');
		$length    = KT_Filter::getInteger('iDisplayLength');
		$isort     = KT_Filter::getInteger('iSortingCols');
		$draw      = KT_Filter::getInteger('sEcho');
		$colsort   = [];
		$sortdir   = [];
		for ($i = 0; $i < $isort; ++$i) {
			$colsort[$i] = KT_Filter::getInteger('iSortCol_' . $i);
			$sortdir[$i] = KT_Filter::get('sSortDir_' . $i);
		}

		Zend_Session::writeClose();
		header('Content-type: application/json');
		echo json_encode( KT_DataTables_AdminAccess::accessTwo(
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
	->addExternalJavascript(KT_JQUERY_JEDITABLE_URL)
	->addInlineJavascript('
		datables_defaults("' . KT_SCRIPT_NAME . '?action=loadrows1");

		jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
		jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};

		jQuery("#site_access_rules").dataTable({
			buttons: [{extend: "csv", exportOptions: {columns: [0,2,4,5,6] }}],
			sAjaxSource: "' . KT_SCRIPT_NAME . '?action=loadrows1",
			columns: [
				/* 0 ip_address_start        */ {dataSort: 1, class: "ip_address"},
				/* 1 ip_address_start (sort) */ {type: "numeric", visible: false},
				/* 2 ip_address_end          */ {dataSort: 3, class: "ip_address"},
				/* 3 ip_address_end (sort)   */ {type: "numeric", visible: false},
				/* 4 user_agent_pattern      */ {class: "ua_string"},
				/* 5 rule                    */ {class: "rule"},
				/* 6 comment                 */ {class: "comment"},
				/* 7 <delete>                */ {sortable: false, class: "text-center delete"}
			],
			"fnDrawCallback": function() {
				// Our JSON responses include Javascript as well as HTML.  This does not get
				// executed, So extract it, and execute it
				jQuery("#site_access_rules script").each(function() {
					eval(this.text);
				});
			}
		});

		datables_defaults("' . KT_SCRIPT_NAME . '?action=loadrows2");

		jQuery("#unknown_site_visitors").dataTable({
			buttons: [{extend: "csvHtml5", exportOptions: {columns: [0,2] }}],
			sAjaxSource: "' . KT_SCRIPT_NAME . '?action=loadrows2",
			columns: [
				/* 0 ip_address         */ {dataSort: 1, class: "ip_address"},
				/* 1 ip_address (sort)  */ {type: "numeric", "bVisible": false},
				/* 2 user_agent_pattern */ {class: "ua_string"},
				/* 3 <allowed>          */ {sortable: false, class: "text-center strong success"},
				/* 4 <banned>           */ {sortable: false, class: "text-center strong alert"},
				/* 5 <search-engine>    */ {sortable: false, class: "text-center strong warning"}
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

echo relatedPages($site_tools, KT_SCRIPT_NAME);

echo pageStart('site_access', $controller->getPageTitle()); ?>

	<?php echo faqLink('general-topics/site-access-rules/'); ?>
	<div class="callout info-help">
		<?php echo KT_I18N::translate('The following rules are used to decide whether a visitor is a human being (allow full access), a search-engine robot (allow restricted access) or an unwanted crawler (deny all access).'); ?>
	</div>
	<div class="cell">
		<table id="site_access_rules">
			<thead>
				<tr>
					<th class="text-center"><?php echo /* I18N: [...] of a range of addresses */ KT_I18N::translate('Start IP address'); ?></th>
					<th></th>
					<th class="text-center"><?php echo /* I18N: [...] of a range of addresses */ KT_I18N::translate('End IP address'); ?></th>
					<th></th>
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
			<i class="<?php echo $iconStyle; ?> fa-undo"></i>
			<?php echo KT_I18N::translate('Reset'); ?>
		</button>
		<hr>
	</div>
	<div class="callout info-help">
		<?php echo KT_I18N::translate('The following visitors were not recognised, and were assumed to be search engines.'); ?>
	</div>
	<div class="cell">
		<table id="unknown_site_visitors">
			<thead>
				<tr>
					<th class="text-center" rowspan="2"><?php /* I18N: http://en.wikipedia.org/wiki/IP_address */ echo KT_I18N::translate('IP address'); ?></th>
					<th rowspan="2"></th>
					<th class="text-center" rowspan="2"><?php echo KT_I18N::translate('User-agent string'); ?></th>
					<th class="text-center" colspan="3"><?php echo KT_I18N::translate('Create a new rule'); ?></th>
				</tr>
				<tr>
					<th class="text-center"><?php echo KT_I18N::translate('allow'); ?></th>
					<th class="text-center"><?php echo KT_I18N::translate('deny'); ?></th>
					<th class="text-center"><?php echo KT_I18N::translate('robot'); ?></th>
				</tr>
			</thead>
		</table>
	</div>
	<div class="cell">
		<button type="submit" class="button" <?php echo 'onclick="if (confirm(\''.htmlspecialchars(KT_I18N::translate('Are you sure you want to delete all visitors not recognised?')).'\')) { document.location=\''.KT_SCRIPT_NAME.'?action=purge\'; }"';?> >
			<i class="<?php echo $iconStyle; ?> fa-trash-can"></i>
			<?php echo KT_I18N::translate('Delete'); ?>
		</button>
	</div>

<?php echo pageClose();
