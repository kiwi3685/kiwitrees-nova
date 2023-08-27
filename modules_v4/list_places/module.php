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

class list_places_KT_Module extends KT_Module implements KT_Module_List {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Places');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the places list module */ KT_I18N::translate('A list or hierarchy of place names');
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
			"SELECT EXISTS(SELECT 1 FROM `##other` WHERE o_file=? AND o_type='NOTE')"
		)->execute(array(KT_GED_ID))->fetchOneRow();
		if ($row) {
			$menus = array();
			$menu  = new KT_Menu(
				$this->getTitle(),
				'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
				'menu-list-plac'
			);
			$menus[] = $menu;
			return $menus;
		} else {
			return false;
		}
	}

	public function show() {
		require_once KT_ROOT.'includes/functions/functions_print_lists.php';

		global $controller, $iconStyle;

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_places', KT_USER_ACCESS_LEVEL))
			->setPageTitle(KT_I18N::translate('Places'))
			->pageHeader();

		$display = KT_Filter::get('display', '');
		$parent  = KT_Filter::getArray('parent');
		if (!is_array($parent)) {
			$parent = array();
		}
		$level = count($parent);

		// Find this place and its ID
		$place			= new KT_Place(implode(', ', array_reverse($parent)), KT_GED_ID);
		$place_id		= $place->getPlaceId();
		$child_places	= $place->getChildPlaces();

		$use_googlemap  = array_key_exists('googlemap', KT_Module::getActiveModules()) && get_module_setting('googlemap', 'GM_PLACE_HIERARCHY');
		$title = '';
		if ($use_googlemap && $level > 0) {
			$title = '<span>' . htmlspecialchars(end($parent)) . '</span>';
			if ($place_id) {
				$parent_place = $place->getParentPlace();
				while ($parent_place->getPlaceId()) {
					$title .= ' - ' . '<a href="' . $parent_place->getURL() . '">' .
						$parent_place->getPlaceName() . '
					</a>';
					$parent_place = $parent_place->getParentPlace();
				}
				$title .= ' - ' . '<a href="module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL . '">' .
					KT_I18N::translate('Top Level') . '
				</a>';
			}
		}

		//calculate column requirements, based on 2-cols out of 12 grid per item
		$numfound		= count($child_places);

		if ($child_places) {
			$colItems	= ceil(count($child_places) / 5); // max number places displayed per page column
			$columns	= array_chunk($child_places, $colItems); // arrays for each column
		} else {
			$columns	= array();
		}
		$numColumns		= count($columns);
		$offset			= (12 - ($numColumns * 2)) / 2;

		$linklevels  = '';
		$placelevels = '';
		$place_names = array();
		for ($j = 0; $j < $level; $j ++) {
			$linklevels .= '&amp;parent[' . $j . ']=' . rawurlencode((string) $parent[$j]);
			if ($parent[$j] == '') {
				$placelevels = ', ' . KT_I18N::translate('unknown') . $placelevels;
			} else {
				$placelevels = ', ' . $parent[$j] . $placelevels;
			}
		}

		$gm_place_id = '';
		$place_image = '';

		$controller->addInlineJavascript('
			jQuery("#place_tabs").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
		');


		echo pageStart('placelist', $controller->getPageTitle());

			if ($display == 'list') {
				echo KT_Mapping_PlaceList::PlaceListAll ($controller, $title);
			}
			if (!$use_googlemap) {
				if ($level == 0 && $display = '') {
					echo KT_Mapping_PlaceList::PlaceListNoMap (
						$place_id,
						$child_places,
						$place,
						$numColumns,
						$columns,
						$numfound,
						$level,
						$parent,
						$linklevels,
						$place_names,
						$placelevels
					);
				}
				if ($level > 0) {
					echo KT_Mapping_PlaceList::PlaceListMap (
						$use_googlemap,
						$place_id,
						$child_places,
						$place,
						$numColumns,
						$columns,
						$numfound,
						$level,
						$parent,
						$linklevels,
						$place_names,
						$placelevels
					);
				}
			} else {
				if ($level == 0) {
					echo KT_Mapping_PlaceList::PlaceListMap (
						$title,
						$place_id,
						$child_places,
						$place,
						$numColumns,
						$columns,
						$numfound,
						$level,
						$parent,
						$linklevels,
						$place_names,
						$placelevels
					);
				} else { ?>
					<div class="cell text-center loading-image"  style="display:block;">
						<i class="<?php echo $iconStyle; ?> fa-sync fa-spin fa-3x"></i>
						<span class="sr-only">Loading...</span>
					</div>

					<ul id="place_tabs" class="cell accordion" data-responsive-accordion-tabs="accordion small-accordion medium-tabs" style="visibility:hidden;">
						<li class="accordion-item is-active" data-accordion-item>
							<a href="#map" aria-selected="true"><?php echo KT_I18N::translate('Map'); ?></a>
							<div class="accordion-content" data-tab-content>
								<?php echo KT_Mapping_PlaceList::PlaceListMap (
									$title,
									$place_id,
									$child_places,
									$place,
									$numColumns,
									$columns,
									$numfound,
									$level,
									$parent,
									$linklevels,
									$place_names,
									$placelevels
								); ?>
							</div>
						</li>

						<li class="accordion-item " data-accordion-item>
							<a href="#records"><?php echo KT_I18N::translate('Linked records'); ?></a>
							<div class="accordion-content" data-tab-content>
								<?php echo KT_Mapping_PlaceList::PlaceListRecords ($title, $place_id); ?>
							</div>
						</li>

						<li class="accordion-item" data-accordion-item>
							<a href="#image"><?php echo KT_I18N::translate('Place details'); ?></a>
							<div class="accordion-content" data-tab-content>
								<?php echo KT_Mapping_PlaceList::PlaceListDetails ($title, $placelevels); ?>
							</div>
						</li>

						<li class="accordion-item" data-accordion-item>
							<a href="#list"><?php echo KT_I18N::translate('Full place list'); ?></a>
							<div class="accordion-content" data-tab-content>
								<?php echo KT_Mapping_PlaceList::PlaceListAll ($controller, $title); ?>
							</div>
						</li>
					</ul>
				<?php }
			}

	 	echo pageClose();

	}

}
