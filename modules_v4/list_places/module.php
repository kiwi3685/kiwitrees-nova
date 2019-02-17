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

class list_places_KT_Module extends KT_Module implements KT_Module_List {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Place hierarchy');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the places list module */ KT_I18N::translate('A hierarchy of place names');
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
			"SELECT SQL_CACHE EXISTS(SELECT 1 FROM `##other` WHERE o_file=? AND o_type='NOTE')"
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
		global $controller;
		require_once KT_ROOT.'includes/functions/functions_print_lists.php';

		$controller = new KT_Controller_Page();

		$action		= KT_Filter::get('action', 'find|view', 'find');
		$display	= KT_Filter::get('display', 'hierarchy|list', 'hierarchy');
		$parent		= KT_Filter::getArray('parent');
		$level = count($parent);

		if ($display == 'hierarchy') {
			if ($level) {
				$controller->setPageTitle(KT_I18N::translate('Place hierarchy') . ' - <span>' . htmlspecialchars(end($parent)) . '</span>');
			} else {
				$controller->setPageTitle(KT_I18N::translate('Place hierarchy'));
			}
		} else {
			$controller->setPageTitle(KT_I18N::translate('Place List'));
		}

		$controller
			->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_places', KT_USER_ACCESS_LEVEL))
			->pageHeader();
		?>

		<div id="placelist-page" class="grid-x">
			<div class="cell large-10 large-offset-1">
				<?php
				switch ($display) {
					case 'list':
						$listPlaceNames = array();
						$placeName		= array();
						$maxParts		= 0;
						$list_places = KT_Place::allPlaces(KT_GED_ID);

						foreach ($list_places as $n=>$list_place) {
							$placeName	= explode(', ', $list_place->getReverseName());
							$countParts	= count($placeName);
							if ($countParts > $maxParts) {
								$maxParts = $countParts;
							}
							$listPlaceNames[]	= $placeName;
						}

						$controller->addExternalJavascript(KT_DATATABLES_JS);
						if (KT_USER_CAN_EDIT) {
							$buttons = 'B';
						} else {
							$buttons = '';
						}
						$controller->addInlineJavascript('
							jQuery("#placeListTable").dataTable({
								dom: \'<"top"' . $buttons . 'lp<"clear">irf>t<"bottom"pl>\',
								' . KT_I18N::datatablesI18N() . ',
								buttons: [{extend: "csv"}],
								jQueryUI: true,
								autoWidth: false,
								displayLength: 20,
								pagingType: "full_numbers",
								stateSave: true,
								stateDuration: -1
							});
							jQuery("#placeListContainer").css("visibility", "visible");
							jQuery(".loading-image").css("display", "none");
						'); ?>

						<div id="PlaceListContainer-page" class="grid-x grid-padding-x">
							<div class="cell large-10 large-offset-1">
								<h3><?php echo $controller->getPageTitle(); ?></h3>
								<h5 class="text-center">
									<a href="module.php?mod=<?php echo $this->getName(); ?>&mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>&amp;display=hierarchy">
										<?php echo KT_I18N::translate('Switch to Place hierarchy'); ?>
									</a>
								</h5>
								<table id="placeListTable">
									<thead>
										<tr>
											<?php for ($i = 0; $i < $maxParts; $i++) { ?>
												<th><?php echo KT_I18N::translate('Places'); ?>&nbsp;<?php echo $i + 1; ?></th>
											<?php } ?>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($list_places as $n=>$list_place) {
											$placeName	= explode(', ', $list_place->getReverseName());?>
											<tr>
												<?php for ($i = 0; $i < $maxParts; $i++) { ?>
													<td>
														<?php if ($i < count($placeName)) { ?>
															<a href="<?php echo $list_place->getURL(); ?>"><?php echo $placeName[$i]; ?></a>
														<?php } else { ?>
															&nbsp;
														<?php }?>
													</td>
												<?php } ?>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php break;
					case 'hierarchy':
						$use_googlemap = array_key_exists('googlemap', KT_Module::getActiveModules()) && get_module_setting('googlemap', 'GM_PLACE_HIERARCHY');
						if ($use_googlemap) {
							require KT_ROOT . KT_MODULES_DIR . 'googlemap/placehierarchy.php';
						}

						// Find this place and its ID
						$place			= new KT_Place(implode(', ', array_reverse($parent)), KT_GED_ID);
						$place_id		= $place->getPlaceId();
						$child_places	= $place->getChildPlaces();

						//calculate column requirements, based on 2-cols out of 12 grid per item
						$numfound		= count($child_places);
						if ($child_places) {
							$colItems	= ceil(count($child_places)/6); // max number places displayed per page column
							$columns	= array_chunk($child_places, $colItems); // arrays for each column
						} else {
							$columns	= array();
						}
						$numColumns		= count($columns);
						$offset			= max(1, (12 - ($numColumns * 2)) / 2);
						if ($offset % 2 == 1) $offset ++; // If odd, add one to make even

						//-- if the number of places found is 0 then automatically redirect to search page
						if ($numfound == 0) {
							$action='view';
						}
						?>

						<h3><?php echo $controller->getPageTitle(); ?>
							<?php if ($place_id) {
								$parent_place = $place->getParentPlace();
								while ($parent_place->getPlaceId()) { ?>
									<a href="<?php echo $parent_place->getURL(); ?>">
										,
										<?php echo $parent_place->getPlaceName(); ?>
									</a>
									<?php $parent_place = $parent_place->getParentPlace();
								} ?>
								<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>">
									,
									<?php echo KT_I18N::translate('Top Level'); ?>
								</a>
							<?php } ?>
						</h3>
						<h5 class="text-center">
							<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>&amp;display=list">
								<?php echo KT_I18N::translate('Switch to list view'); ?>
							</a>
						</h5>

						<?php if ($use_googlemap) {
							$linklevels		= '';
							$placelevels	= '';
							$place_names = array();
							for ($j = 0; $j < $level; $j ++) {
								$linklevels .= '&amp;parent[' . $j . ']=' . rawurlencode($parent[$j]);
								if ($parent[$j] == '') {
									$placelevels = ', ' . KT_I18N::translate('unknown') . $placelevels;
								} else {
									$placelevels = ', ' . $parent[$j] . $placelevels;
								}
							}
							create_map($placelevels);
						} ?>

						<div id="place-list" class="grid-x">
							<?php if ($place_id) { ?>
								<h5 class="cell text-center">
									<?php echo /* I18N: %s is a country or region */ KT_I18N::translate('Places in %s', $place->getPlaceName()); ?>
								</h5>
							<?php } ?>
							<div class="cell large-8 large-offset-2">
								<div class="places grid-x grid-margin-y grid-padding-x medium-up-<?php echo $numColumns; ?>">
									<?php foreach ($columns as $child_places) { ?>
									    <div class="cell">
											<ul>
											    <?php foreach($child_places as $n => $child_place) { ?>
													<li>
														<a href="<?php echo $child_place->getURL(); ?>" class="list_item">
															<?php echo $child_place->getPlaceName(); ?>
														</a>
													</li>
													<?php if ($use_googlemap) {
														$place_names[$n] = $child_place->getPlaceName();
													}
											    } ?>
									    	</ul>
										</div>
									<?php } ?>
								</div>
								<?php if ($child_places) {
									if ($action == 'find' && $place_id) {
										$this_place = '
											<a href="' . $place->getURL() . '&amp;action=view"
												class="formField"
												data-tooltip
												aria-haspopup="true"
												class="has-tip top"
												data-disable-hover="false"
												title="' . KT_I18N::translate('Click to see a list of all individuals and families that have events occurring in this place.') . '"
											>' .
												$place->getPlaceName() . '
											</a>
										'; ?>
										<div class="grid-x">
											<h5 class="cell large-8 large-offset-2 text-center">
													<?php echo KT_I18N::translate('View all records found in %s', $this_place); ?>
											</h5>
										</div>
									<?php }
								} ?>
							</div>
						</div>

						<?php if ($place_id && $action == 'view') {
							// -- array of names
							$myindilist	= array();
							$myfamlist	= array();
							$positions	=
								KT_DB::prepare("SELECT DISTINCT pl_gid FROM `##placelinks` WHERE pl_p_id=? AND pl_file=?")
								->execute(array($place_id, KT_GED_ID))
								->fetchOneColumn();

							foreach ($positions as $position) {
								$record = KT_GedcomRecord::getInstance($position);
								if ($record && $record->canDisplayDetails()) {
									switch ($record->getType()) {
									case 'INDI':
										$myindilist[]	= $record;
										break;
									case 'FAM':
										$myfamlist[]	= $record;
										break;
									}
								}
							}

							//-- display results
							$controller
								->addInlineJavascript('jQuery("#places-tabs").css("visibility", "visible");')
								->addInlineJavascript('jQuery(".loading-image").css("display", "none");');
							?>
							<div class="loading-image">&nbsp;</div>
							<div class="grid-x">
								<ul class="tabs" data-tabs id="places-tabs">
									<?php if ($myindilist) { ?>
										<li class="tabs-title is-active" aria-selected="true">
											<a href="#places-indi"><span><?php echo KT_I18N::translate('Individuals'); ?></span></a>
										</li>
									<?php }
									if ($myfamlist) { ?>
										<li class="tabs-title">
											<a href="#places-fam"><span><?php echo KT_I18N::translate('Families'); ?></span></a>
										</li>
									<?php } ?>
								</ul>
								<div class="cell tabs-content" data-tabs-content="places-tabs">
									<?php if ($myindilist) { ?>
										<div id="places-indi" class="tabs-panel is-active">
											<?php echo format_indi_table($myindilist); ?>
										</div>
									<?php }
									if ($myfamlist) { ?>
										<div id="places-fam" class="tabs-panel">
											<?php echo format_fam_table($myfamlist); ?>
										</div>
									<?php }
									if (!$myindilist && !$myfamlist) { ?>
										<div id="places-indi" class="tabs-panel">
											<?php echo format_indi_table(array()); ?>
										</div>
									<?php } ?>
								</div>
							</div>
						<?php }
						if ($use_googlemap) { ?>
							<link type="text/css" href="<?php echo KT_STATIC_URL . KT_MODULES_DIR; ?>googlemap/css/googlemap.css" rel="stylesheet">
							<?php map_scripts($numfound, $level, $parent, $linklevels, $placelevels, $place_names);
						}
					break;
				} ?>
		</div></div>
	<?php }
}
