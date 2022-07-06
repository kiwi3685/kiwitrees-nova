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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class list_sources_KT_Module extends KT_Module implements KT_Module_List {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Sources');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the sources list module */ KT_I18N::translate('A list of sources');
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
			"SELECT EXISTS(SELECT 1 FROM `##sources` WHERE s_file=? )"
		)->execute(array(KT_GED_ID))->fetchOneRow();

		if ($row) {
			$menus = array();
			$menu  = new KT_Menu(
				$this->getTitle(),
				'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
				'menu-list-sour'
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

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_sources', KT_USER_ACCESS_LEVEL))
			->setPageTitle(KT_I18N::translate('Sources'))
			->pageHeader();

		switch (KT_Filter::post('action')) {
		    // Generate an AJAX/JSON response for datatables to load a block of rows
		    case 'loadrows':
		        Zend_Session::writeClose();
		        $sSearch        = KT_Filter::get('sSearch');
		        $iDisplayStart  = KT_Filter::getInteger('iDisplayStart');
		        $iDisplayLength = KT_Filter::getInteger('iDisplayLength');

		        $sql = "
		            SELECT 'SOUR' AS type, s_id AS xref, s_file AS ged_id, s_gedcom AS gedrec
		            FROM `##sources`
		            WHERE s_file=?
		        ";

		        // This becomes a JSON list, not array, so need to fetch with numeric keys.
		        $aaData = KT_DB::prepare($sql)->execute(array(KT_GED_ID))->fetchAll(PDO::FETCH_ASSOC);

		        $installed_languages = array();
		        foreach (KT_I18N::used_languages() as $code=>$name) {
		            $installed_languages[$code] = KT_I18N::translate($name);
		        }

		        // Reformat various columns for display
		        foreach ($aaData as $aData) {
		            $sour_id	= $aData[1];
		            $ged_id		= $aData[2];
		            $gedrec		= $aData[3];

		            if (!$sour_id || !$sour_id->canDisplayDetails()) {
		                continue;
		            }

		            foreach ($source->getAllNames() as $n=>$name) {
		                if ($n) {
		                    $html .= '<br>';
		                }
		                if ($n==$source->getPrimaryName()) {
		                    $html .= '<a class="name2" href="'. $source->getHtmlUrl(). '">'. highlight_search_hits($name['full']). '</a>';
		                } else {
		                    $html .= '<a href="'. $source->getHtmlUrl(). '">'. highlight_search_hits($name['full']). '</a>';
		                }
		            }

		            $aData[0]	= '<td><a class="name2" href="source.php?sid=' . $sour_id . '&amp;ged=' . $ged_id . '"><span dir="auto">Birth record</span></a></td>';
		            $aData[1]	= '';
		            $aData[2]	= '';
		            $aData[3]	= '';
		            $aData[4]	= '';
		            $aData[5]	= '';
		            $aData[6]	= '';
		            $aData[7]	= '';
		            $aData[8]	= '';
		            $aData[9]	= '';
		            $aData[10]	= '';
		            $aData[11]	= '';
		            $aData[12]	= '';
		            $aData[13]	= '';
		        }


		        // Total filtered/unfiltered rows
		        $iTotalDisplayRecords	= KT_DB::prepare("SELECT FOUND_ROWS()")->fetchOne();
		        $iTotalRecords			= KT_DB::prepare("SELECT COUNT(*) FROM `##user` WHERE user_id>0")->fetchOne();

		        Zend_Session::writeClose();
		        header('Content-type: application/json');
		        echo json_encode(array( // See http://www.datatables.net/usage/server-side
		            'sEcho'               => KT_Filter::getInteger('sEcho'),
		            'iTotalRecords'       => $iTotalRecords,
		            'iTotalDisplayRecords'=> $iTotalDisplayRecords,
		            'aaData'              => $aaData
		        ));
		    break;

		    default:

		        if (KT_SCRIPT_NAME == 'search.php') {
		            $table_id = 'ID' . (int)(microtime(true)*1000000); // lists requires a unique ID in case there are multiple lists per page
		        } else {
		            $table_id = 'sourTable';
		        }

		        $controller
		            ->addExternalJavascript(KT_DATATABLES_JS)
		            ->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
		        ;

		        if (KT_USER_CAN_EDIT) {
		            $controller
		                ->addExternalJavascript(KT_DATATABLES_BUTTONS)
		                ->addExternalJavascript(KT_DATATABLES_HTML5);
		            $buttons = 'B';
		        } else {
		            $buttons = '';
		        }

		        $html = '';

		        $controller
		            ->addInlineJavascript('
		                jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
		                jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
		                jQuery("#' . $table_id . '").dataTable({
		                    dom: \'<"top"' . $buttons . 'lp<"clear">irf>t<"bottom"pl>\',
		                    ' . KT_I18N::datatablesI18N() . ',
		                    buttons: [{extend: "csv", exportOptions: {columns: [0,2,3,5,7,9] }}],
		                    autoWidth: false,
		                    processing: true,
		                    serverSide: true,
		                    ajax: "module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;action=loadrows",
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
		                        /*  0 title     */ { dataSort: 1 },
		                        /*  1 TITL      */ { type: "unicode", visible: false },
		                        /*  2 author    */ { type: "unicode" },
		                        /*  3 #indi     */ { dataSort: 4, class: "text-center show-for-medium" },
		                        /*  4 #INDI     */ { type: "num", visible: false },
		                        /*  5 #fam      */ { dataSort: 6, class: "text-center show-for-medium" },
		                        /*  6 #FAM      */ { type: "num", visible: false },
		                        /*  7 #obje     */ { dataSort: 8, class: "text-center show-for-medium" },
		                        /*  8 #OBJE     */ { type: "num", visible: false },
		                        /*  9 #note     */ { dataSort: 10, class: "text-center show-for-medium" },
		                        /* 10 #NOTE     */ { type: "num", visible: false },
		                        /* 11 CHAN      */ { dataSort: 12, visible: ' . ($SHOW_LAST_CHANGE ? 'true' : 'false') . ', class: "text-center show-for-medium" },
		                        /* 12 CHAN_sort */ { visible: false },
		                        /* 13 DELETE    */ { sortable: false, class: "text-center show-for-medium" }
		                    ]
		               });
		               jQuery("#' . $table_id . '").css("visibility", "visible");
		               jQuery(".loading-image").css("display", "none");
		            '); ?>


		            <div id="sourcelist-page" class="grid-x grid-padding-x">
		                <div class="cell large-10 large-offset-1">
		                    <h3><?php echo $controller->getPageTitle(); ?></h3>

		                    <div class="cell text-center loading-image">
		                        <i class="<?php echo $iconStyle; ?> fa-spinner fa-spin fa-3x"></i>
		                        <span class="sr-only">Loading...</span>
		                    </div>
		                    <div class="sour-list">
		                        <table id="<?php echo $table_id; ?>" style="visibility: hidden;">
		                            <thead>
		                                <tr>
		                                    <th><?php echo KT_Gedcom_Tag::getLabel('TITL'); ?></th>
		                                    <th>TITL</th>
		                                    <th><?php echo KT_Gedcom_Tag::getLabel('AUTH'); ?></th>
		                                    <th><?php echo KT_I18N::translate('Individuals'); ?></th>
		                                    <th>#INDI</th>
		                                    <th><?php echo KT_I18N::translate('Families'); ?></th>
		                                    <th>#FAM</th>
		                                    <th><?php echo KT_I18N::translate('Media objects'); ?></th>
		                                    <th>#OBJE</th>
		                                    <th><?php echo KT_I18N::translate('Shared notes'); ?></th>
		                                    <th>#NOTE</th>
		                                    <th><?php echo KT_Gedcom_Tag::getLabel('CHAN'); ?></th>
		                                    <th>CHAN</th>
		                                    <th class="delete_src" style="<?php echo (KT_USER_GEDCOM_ADMIN ? '' : 'display: none;'); ?>">
		                                        <input
		                                            type="button"
		                                            value = "<?php echo KT_I18N::translate('Delete'); ?>"
		                                            class="button tiny"
		                                            onclick="if (confirm(\'<?php echo htmlspecialchars(KT_I18N::translate('Permanently delete these records?')); ?>\')) {return checkbox_delete(\'sources\');} else {return false;}"
		                                            >
		                                        <input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
		                                    </th>
		                                </tr>
		                            </thead>
		                            <tbody>
		                            </tbody>
		                        </table>
		                    </div>

		                </div>
		            </div>
		        <?php
		    break;
		}

	}

}
