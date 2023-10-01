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
class report_decorative_tree_KT_Module extends KT_Module implements KT_Module_Report {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ KT_I18N::translate('Family tree image');
	}
	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Research tasks” module */ KT_I18N::translate('A pictorial view of a basic family tree.');
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
	// Implement KT_Module_Report
	public function getReportMenus() {
		global $controller;
		$fam_xref = $controller->getSignificantFamily()->getXref();
		$menus	= array();
		$menu	= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $fam_xref . '&amp;ged=' . KT_GEDURL,
			'menu-report-' . $this->getName()
		);
		$menus[] = $menu;
		return $menus;
	}
	// Implement class KT_Module_Report
	public function show() {
		global $controller, $iconStyle, $GEDCOM;

//		$controller = new KT_Controller_Page();
		$controller = new KT_Controller_Family();
		$controller
			->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_favorites', KT_USER_ACCESS_LEVEL))
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('
				autocomplete();

				jQuery("#container").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			');


		//-- args
		$rootid 		= KT_Filter::get('rootid');
		$root_id		= KT_Filter::post('root_id');
		$rootid			= empty($root_id) ? $rootid : $root_id;
		$ged			= KT_Filter::post('ged') ? KT_Filter::post('ged') : $GEDCOM;
		$showsources	= KT_Filter::post('showsources') ? KT_Filter::post('showsources') : 0;
		$shownotes		= KT_Filter::post('shownotes') ? KT_Filter::post('shownotes') : 0;
		$missing		= KT_Filter::post('missing') ? KT_Filter::post('missing') : 0;
		$showmedia		= KT_Filter::post('showmedia') ? KT_Filter::post('showmedia') : 'main';
		$photos			= KT_Filter::post('photos') ? KT_Filter::post('photos') : 'highlighted';
		$exclude_tags	= array('CHAN','NAME','SEX','SOUR','NOTE','OBJE','RESN','FAMC','FAMS','TITL','CHIL','HUSB','WIFE','_UID','_KT_OBJE_SORT');
		$basic_tags		= array('BIRT','BAPM_CHR','DEAT','BURI_CREM');


$famid = 'F406';
		echo pageStart('family_tree', $this->getTitle(), 'y', $this->getDescription()); ?>

			<div id="container" class="cell" height: 50rem; width: 100%;">
				<?php print_gparents_simplified($famid); ?>
				<?php print_children($famid); ?>
			</div>

		<?php echo pageClose();

	}
}
