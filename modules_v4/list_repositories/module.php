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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class list_repositories_KT_Module extends KT_Module implements KT_Module_List {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Repositories');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the repositories list module */ KT_I18N::translate('A list of repositories');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_List
	public function getListMenus() {
		global $controller, $SEARCH_SPIDER;
		if ($SEARCH_SPIDER) {
			return null;
		}
		// Do not show empty lists
		$row = KT_DB::prepare(
			"SELECT EXISTS(SELECT 1 FROM `##other` WHERE o_file=? AND o_type='REPO')"
		)->execute(array(KT_GED_ID))->fetchOneRow();
		if ($row) {
			$menus = array();
			$menu  = new KT_Menu(
				$this->getTitle(),
				'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
				'menu-list-repo'
			);
			$menus[] = $menu;
			return $menus;
		} else {
			return false;
		}
	}

	// Display list
	public function show() {

		global $iconStyle, $SHOW_LAST_CHANGE, $controller;

		switch (KT_Filter::get('action')) {
			case 'loadrows':
				Zend_Session::writeClose();

				$search  = KT_Filter::get('sSearch', '');
				$start   = KT_Filter::getInteger('iDisplayStart');
				$length  = KT_Filter::getInteger('iDisplayLength');
				$isort   = KT_Filter::getInteger('iSortingCols');
				$draw    = KT_Filter::getInteger('sEcho');
				$colsort = [];
				$sortdir = [];
				for ($i = 0; $i < $isort; ++$i) {
					$colsort[$i] = KT_Filter::getInteger('iSortCol_' . $i);
					$sortdir[$i] = KT_Filter::get('sSortDir_' . $i);
				}

				header('Content-type: application/json');
				echo json_encode(KT_DataTables_ListRepositories::repoList($search, $start, $length, $isort, $draw, $colsort, $sortdir));

				exit;
		}

		// Access default datatables settings
		include_once KT_ROOT . 'library/KT/DataTables/KTdatatables.js.php';

		$controller = new KT_Controller_Page();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_repositories', KT_USER_ACCESS_LEVEL))
			->addExternalJavascript(KT_DATATABLES_KT_JS)
			->addInlineJavascript('
				datables_defaults("module.php?mod=' . $this->getName() . '&mod_action=show&action=loadrows");

				jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};

				jQuery("#repoTable").dataTable({
					sorting: [[2,"asc"]],
					columns: [
						/* 0 xref         */ { type: "num", visible: false },
						/* 1 title        */ { orderData: 2 , type: "unicode" },
						/* 2 TITL (sort)  */ { type: "unicode", visible: false },
						/* 3 #sour        */ { orderData: 4, class: "text-center" },
						/* 4 #SOUR (sort) */ { type: "num", visible: false },
						/* 5 CHAN         */ { orderData: 6, visible: ' . ($SHOW_LAST_CHANGE ? 'true' : 'false') . ', class: "text-center" },
						/* 6 CHAN (sort)  */ { visible: false },
						/* 7 DELETE       */ { sortable: false, class: "text-center" }
					]
				});
			')
		;

		echo pageStart('sourcelist', $controller->getPageTitle()); ?>

			<div class="cell callout info-help">
				<?php echo KT_I18N::translate('
					A list of all repository records for this family tree, limited only by privacy settings.
					An addional column shows the number of sources each repository is linked to.
				'); ?>
			</div>

			<div class="cell">
				<table id="repoTable" class="shadow scroll" >
					<thead>
						<tr>
							<th></th>
							<th><?php echo KT_I18N::translate('Repository name');  ?></th>
							<th></th>
							<th><?php echo KT_I18N::translate('Sources');  ?></th>
							<th></th>
							<th><?php echo KT_Gedcom_Tag::getLabel('CHAN');  ?></th>
							<th></th>
							<th class="delete_src" style="<?php echo (KT_USER_GEDCOM_ADMIN ? '' : 'display: none;'); ?>">
								<input type="button" class="button tiny" value="<?php echo KT_I18N::translate('Delete'); ?>" onclick="if (confirm('<?php echo htmlspecialchars(KT_I18N::translate('Permanently delete these records?')); ?>')) {return checkbox_delete('sources');} else {return false;}">
								<input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
							</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>

		<?php echo pageClose();

	}

}
