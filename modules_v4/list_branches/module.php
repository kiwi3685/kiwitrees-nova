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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class list_branches_KT_Module extends KT_Module implements KT_Module_List {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Branches');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the branches list module */ KT_I18N::translate('A list of branches for a chosen surname');
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
		$menus = array();
		$menu  = new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;surname=' . rawurlencode($controller->getSignificantSurname()) . '&amp;ged=' . KT_GEDURL,
			'menu-list-branches'
		);
		$menus[] = $menu;
		return $menus;
	}

	// Display list
	public function show() {
		global $controller, $iconStyle;

		$controller = new KT_Controller_Branches();
		$controller
			->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_branches', KT_USER_ACCESS_LEVEL))
			->pageHeader()
			->addExternalJavascript(KT_JQUERY_TREEVIEW_JS_URL)
//			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('
//				autocomplete();
				jQuery("#branch-list").treeview({
					collapsed: true,
					animated: "slow",
					control:"#treecontrol"
				});
				jQuery("#branch-list").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			');
		?>

		<div id="branches-page" class="grid-x grid-padding-x">
			<div class="cell large-10 large-offset-1">
				<h3><?php echo $controller->getPageTitle(); ?></h3>
				<form name="surnlist" id="surnlist" method="get" action="?">
					<input type="hidden" name="mod" value="<?php echo $this->getName(); ?>">
					<input type="hidden" name="mod_action" value="show">
					<div class="grid-x grid-margin-x">
						<div class="cell medium-4">
							<label class="h5" for="autocompleteInput"><?php echo KT_Gedcom_Tag::getLabel('SURN'); ?></label>
							<div class="input-group autocomplete_container">
								<input data-autocomplete-type="SURN" type="text" id="autocompleteInput" value="<?php echo KT_Filter::escapeHtml($controller->surn); ?>">
								<span class="input-group-label">
									<button class="clearAutocomplete autocomplete_icon">
										<i class="<?php echo $iconStyle; ?> fa-times"></i>
									</button>
								</span>
							</div>
							<input type="hidden" id="selectedValue" name="surname" >
						</div>
						<div class="cell medium-4">
							<label class="h5"><?php echo KT_I18N::translate('Phonetic search'); ?></label>
								<div class="grid-x grid-margin-x">
									<label class="cell small-4 text-right" for="soundex_std"><?php echo KT_I18N::translate('Russell'); ?></label>
									<div class="switch">
										<input class="switch-input" id="soundex_std" type="checkbox" name="soundex_std" value="1" <?php echo $controller->soundex_std ? ' checked="checked"' : ''; ?>>
										<label class="switch-paddle" for="soundex_std">
											<span class="show-for-sr"><?php echo KT_I18N::translate('Russell'); ?></span>
										    <span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('on'); ?></span>
										    <span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('off'); ?></span>
										</label>
									</div>
									<label class="cell small-4 text-right" for="soundex_dm"><?php echo KT_I18N::translate('Daitch-Mokotoff'); ?></label>
									<div class="switch auto">
										<input class="switch-input" id="soundex_dm" type="checkbox" name="soundex_dm" value="1" <?php echo $controller->soundex_dm ? ' checked="checked"' : ''; ?>>
										<label class="switch-paddle" for="soundex_dm">
											<span class="show-for-sr"><?php echo KT_I18N::translate('Daitch-Mokotoff'); ?></span>
										    <span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('on'); ?></span>
										    <span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('off'); ?></span>
										</label>
									</div>
								</div>
						</div>
					</div>
					<button class="button" type="submit">
						<i class="<?php echo $iconStyle; ?> fa-eye"></i>
						<?php echo KT_I18N::translate('Show'); ?>
					</button>
				</form>
				<hr>
				<!-- end of form -->
				<?php
				//-- results
				if ($controller->surn) { ?>
					<h5 id="treecontrol">
						<a href="#"><?php echo KT_I18N::translate('Collapse all'); ?></a>
						 |
						<a href="#"><?php echo KT_I18N::translate('Expand all'); ?></a>
					</h5>
					<div class="cell align-center loading-image">
						<i class="<?php echo $iconStyle; ?> fa-spinner fa-spin fa-3x"></i>
						<span class="sr-only">Loading...</span></div>
						<ul id="branch-list" style="visibility: hidden;">
							<?php $controller->getBranchList(); ?>
						</ul>
					</div>
				<?php } ?>
		</div> <!--  close branches-page -->
	<?php }

}
