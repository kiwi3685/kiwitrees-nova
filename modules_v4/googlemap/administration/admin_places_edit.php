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

require KT_ROOT . 'includes/functions/functions_edit.php';

$action = safe_REQUEST($_REQUEST, 'action');
if (isset($_REQUEST['placeid'])) $placeid = $_REQUEST['placeid'];
if (isset($_REQUEST['placeid'])) $placeid = $_REQUEST['placeid'];
if ($placeid) {
	$place_image = KT_DB::prepare("SELECT pl_image FROM `##placelocation` WHERE pl_id = ?")->execute([$placeid]);
} else {
	$place_image = '';
}

$controller = new KT_Controller_Page();
$controller
		->restrictAccess(KT_USER_IS_ADMIN)
		->setPageTitle(KT_I18N::translate('Geographic data'))
		->pageHeader();

$where_am_i = place_id_to_hierarchy($placeid);
$level		= count($where_am_i);
$link 		= 'module.php?mod=googlemap&amp;mod_action=admin_places&amp;parent=' . $placeid;

if ($action == 'addrecord' && KT_USER_IS_ADMIN) {
	$statement=
		KT_DB::prepare("INSERT INTO `##placelocation` (pl_id, pl_parent_id, pl_level, pl_place, pl_long, pl_lati, pl_zoom, pl_icon) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

	if (($_POST['LONG_CONTROL'] == '') || ($_POST['NEW_PLACE_LONG'] == '') || ($_POST['NEW_PLACE_LATI'] == '')) {
		$statement->execute(array(getHighestIndex()+1, $placeid, $level, $_POST['NEW_PLACE_NAME'], null, null, (int) $_POST['NEW_ZOOM_FACTOR'], $_POST['icon']));
	} else {
		$statement->execute(array(getHighestIndex()+1, $placeid, $level, $_POST['NEW_PLACE_NAME'], $_POST['LONG_CONTROL'][3].$_POST['NEW_PLACE_LONG'], $_POST['LATI_CONTROL'][3].$_POST['NEW_PLACE_LATI'], $_POST['NEW_ZOOM_FACTOR'], $_POST['icon']));
	}

	// autoclose window when update successful unless debug on
	if (!KT_DEBUG) {
		$controller->addInlineJavaScript('closePopupAndReloadParent();');
	}
	echo "<div class=\"center\"><button onclick=\"closePopupAndReloadParent();return false;\">", KT_I18N::translate('close'), "</button></div>";
	exit;
}

if ($action == 'updaterecord' && KT_USER_IS_ADMIN) {
	$statement=
		KT_DB::prepare("UPDATE `##placelocation` SET pl_place=?, pl_lati=?, pl_long=?, pl_zoom=?, pl_icon=? WHERE pl_id=?");

	if (($_POST['LONG_CONTROL'] == '') || ($_POST['NEW_PLACE_LONG'] == '') || ($_POST['NEW_PLACE_LATI'] == '')) {
		$statement->execute(array($_POST['NEW_PLACE_NAME'], null, null, $_POST['NEW_ZOOM_FACTOR'], $_POST['icon'], $placeid));
	} else {
		$statement->execute(array($_POST['NEW_PLACE_NAME'], $_POST['LATI_CONTROL'][3].$_POST['NEW_PLACE_LATI'], $_POST['LONG_CONTROL'][3].$_POST['NEW_PLACE_LONG'], $_POST['NEW_ZOOM_FACTOR'], $_POST['icon'], $placeid));
	}

	// autoclose window when update successful unless debug on
	if (!KT_DEBUG) {
		$controller->addInlineJavaScript('closePopupAndReloadParent();');
	}
	echo "<div class=\"center\"><button onclick=\"closePopupAndReloadParent();return false;\">", KT_I18N::translate('close'), "</button></div>";
	exit;
}

if ($action == 'update') {
	// --- find the place in the file
	$row=
		KT_DB::prepare("SELECT pl_place, pl_lati, pl_long, pl_icon, pl_parent_id, pl_level, pl_zoom, pl_image FROM `##placelocation` WHERE pl_id=?")
		->execute(array($placeid))
		->fetchOneRow();
	$place_name  = $row->pl_place;
	$place_icon  = $row->pl_icon;
	$place_image = $row->pl_image;
	$selected_country = explode("/", (string)$place_icon);
	if (isset($selected_country[1]) && $selected_country[1]!="flags"){
		$selected_country = $selected_country[1];
	} else {
		$selected_country = "Countries";
	}
	$parent_id = $row->pl_parent_id;
	$level = $row->pl_level;
	$zoomfactor = $row->pl_zoom;
	$parent_lati = "0.0";
	$parent_long = "0.0";
	if ($row->pl_lati !== null && $row->pl_long !== null) {
		$place_lati = (float)(str_replace(array('N', 'S', ','), array('', '-', '.') , $row->pl_lati));
		$place_long = (float)(str_replace(array('E', 'W', ','), array('', '-', '.') , $row->pl_long));
		$show_marker = true;
	} else {
		$place_lati = null;
		$place_long = null;
		$zoomfactor = 1;
		$show_marker = false;
	}

	do {
		$row=
			KT_DB::prepare("SELECT pl_lati, pl_long, pl_parent_id, pl_zoom FROM `##placelocation` WHERE pl_id=?")
			->execute(array($parent_id))
			->fetchOneRow();
		if (!$row) {
			break;
		}
		if ($row->pl_lati!==null && $row->pl_long!==null) {
			$parent_lati = (float)(str_replace(array('N', 'S', ','), array('', '-', '.') , $row->pl_lati));
			$parent_long = (float)(str_replace(array('E', 'W', ','), array('', '-', '.') , $row->pl_long));
			if ($zoomfactor == 1) {
				$zoomfactor = $row->pl_zoom;
			}
		}
		$parent_id = $row->pl_parent_id;
	}
	while ($row->pl_parent_id!=0 && $row->pl_lati===null && $row->pl_long===null);

	$success = false; ?>

	<div id="editplaces-page" class="cell">
		<h4>
			<?php echo htmlspecialchars(str_replace('Unknown', KT_I18N::translate('unknown'), implode(KT_I18N::$list_separator, array_reverse($where_am_i, true)))); ?>
		</h4>

	<?php
}

if ($action == 'add') {
	// --- find the parent place in the file
	if ($placeid != 0) {
		if (!isset($place_name)) $place_name  = '';
			$place_lati  = null;
			$place_long  = null;
			$zoomfactor  = 1;
			$parent_lati = '0.0';
			$parent_long = '0.0';
			$place_icon  = '';
			$place_image = '';
			$parent_id   =$placeid;
		do {
			$row =
				KT_DB::prepare("SELECT  pl_lati, pl_long, pl_parent_id, pl_zoom, pl_level FROM `##placelocation` WHERE pl_id=?")
				->execute(array($parent_id))
				->fetchOneRow();
			if ($row->pl_lati !== null && $row->pl_long !== null) {
				$parent_lati = str_replace(array('N', 'S', ','), array('', '-', '.') , $row->pl_lati);
				$parent_long = str_replace(array('E', 'W', ','), array('', '-', '.') , $row->pl_long);
				$zoomfactor	 = $row->pl_zoom;
				if ($zoomfactor > $GOOGLEMAP_MAX_ZOOM) {
					$zoomfactor = $GOOGLEMAP_MAX_ZOOM;
				}
				$level = $row->pl_level+1;
			}
			$parent_id = $row->pl_parent_id;
		} while ($row->pl_parent_id != 0 && $row->pl_lati === null && $row->pl_long === null);
	}
	else {
		if (!isset($place_name)) {
			$place_name  = '';
			$place_lati  = null;
			$place_long  = null;
			$parent_lati = "0.0";
			$parent_long = "0.0";
			$place_icon  = '';
			$place_image = '';
			$parent_id   = 0;
			$level       = 0;
			$zoomfactor  = $GOOGLEMAP_MIN_ZOOM;
		}
	}
	$selected_country = 'Countries';
	$show_marker = false;
	$success = false;

	if (!isset($place_name) || $place_name=="") {
		echo '<h3>', KT_I18N::translate('unknown');
	} else {
		echo '<h3>', $place_name;
		if (count( $where_am_i ) >0 ) {
			echo ', ', htmlspecialchars(str_replace('Unknown', KT_I18N::translate('unknown'), implode(KT_I18N::$list_separator, array_reverse($where_am_i, true)))), '</b><br>';
		}
	}
	echo '</h3>';

	if ($place_name == null || $place_name == "") { ?>
		<div id="editplaces-page" class="cell">
			<h4>
				<?php echo KT_I18N::translate('unknown'); ?>
			</h4>
	<?php } else { ?>
		<div id="editplaces-page" class="cell">
			<h4><?php echo $place_name; ?>
				<?php if (count( $where_am_i ) > 0 ) { ?>
					<?php echo ', ' . htmlspecialchars(str_replace('Unknown', KT_I18N::translate('unknown'), implode(KT_I18N::$list_separator, array_reverse($where_am_i, true)))) . '</b><br>'; ?>
				<?php } ?>
			</h4>
	<?php }

}

include_once KT_MODULES_DIR . 'googlemap/places_edit.js.php';
require KT_ROOT . 'includes/functions/functions_media.php';
require KT_ROOT . 'includes/functions/functions_edit_addsimpletags.php';
global $iconStyle; ?>

	<div id="editplaces-page" class="cell">
		<div class="grid-x">
			<form class="cell medium-10 medium-offset-1" method="post" id="editplaces" name="editplaces" action="module.php?mod=googlemap&amp;mod_action=admin_places_edit">
				<input type="hidden" name="action" 		value="<?php echo $action; ?>record">
				<input type="hidden" name="placeid" 	value="<?php echo $placeid; ?>">
				<input type="hidden" name="level" 		value="<?php echo $level; ?>">
				<input type="hidden" name="icon" 		value="<?php echo $place_icon; ?>">
				<input type="hidden" name="parent_id" 	value="<?php echo $parent_id; ?>">
				<input type="hidden" name="place_long" 	value="<?php echo $place_long; ?>">
				<input type="hidden" name="place_lati" 	value="<?php echo $place_lati; ?>">
				<input type="hidden" name="parent_long" value="<?php echo $parent_long; ?>">
				<input type="hidden" name="parent_lati" value="<?php echo $parent_lati; ?>">

				<div class="grid-x grid-margin-x">

					<div class="cell" id="map_pane"></div>

					<div class="cell medium-2">
						<label for="place_name" class="middle">
							<?php echo KT_Gedcom_Tag::getLabel('PLAC'); ?>
						</label>
					</div>
					<div class="cell medium-6 input-group">
						<input
							id="place_name"auto
							class="input-group-field"
							type="text" id="new_pl_name"
							name="NEW_PLACE_NAME"
							value="<?php echo htmlspecialchars((string) $place_name); ?>"
						>
						<span class="input-group-label">
							<?php echo print_specialchar_link('new_pl_name'); ?>
						</span>
					</div>
					<div class="cell medium-2 text-right">
						<a class="button hollow" id="new_pl_name1" href="#" onclick="showLocation_all(document.getElementById('new_pl_name1').value); return false">
							<?php echo KT_I18N::translate('Search globally'); ?>
						</a>
					</div>
					<div class="cell medium-2 text-right">
						<a class="button hollow" id="new_pl_name2" href="#" onclick="showLocation_level(document.getElementById('new_pl_name2').value); return false">
							<?php echo KT_I18N::translate('Search locally'); ?>
						</a>
					</div>
					<div class="cell medium-2">
						<label for="" class="middle">
							<?php echo KT_I18N::translate('Precision'); ?>
						</label>
					</div>
					<div class="cell medium-10 precision">
						<?php
							$exp = explode(".", $place_lati);
							if (isset($exp[1])) {
								$precision1 = strlen($exp[1]);
							} else {
								$precision1 = -1;
							}
							$exp = explode(".", $place_long);
							if (isset($exp[1])) {
								$precision2 = strlen($exp[1]);
							} else {
								$precision2 = -1;
							}
							($precision1 > $precision2) ? ($precision = $precision1) : ($precision = $precision2);
							if ($precision == -1 ) ($level > 3) ? ($precision = 3) : ($precision = $level);
							elseif ($precision > 5) {
								$precision = 5;
							}
						?>
						<span>
							<input
								type="radio"
								id="new_prec_0"
								name="NEW_PRECISION"
								onchange="updateMap();"
								<?php if ($precision == $GOOGLEMAP_PRECISION_0) {echo 'checked="checked"';} ?>
								value="<?php echo $GOOGLEMAP_PRECISION_0; ?>"
							>
							<label for="new_prec_0" class="middle">
								<?php echo KT_I18N::translate('Country'); ?>
							</label>
						</span>
						<span>
							<input
								type="radio"
								id="new_prec_1"
								name="NEW_PRECISION"
								onchange="updateMap();"
								<?php if ($precision == $GOOGLEMAP_PRECISION_1) echo 'checked="checked"'; ?>
								value="<?php echo $GOOGLEMAP_PRECISION_1; ?>"
							>
							<label for="new_prec_1" class="middle">
								<?php echo KT_I18N::translate('State'); ?>
							</label>
						</span>
						<span>
							<input
								type="radio"
								id="new_prec_2"
								name="NEW_PRECISION"
								onchange="updateMap();"
								<?php if ($precision == $GOOGLEMAP_PRECISION_2) echo 'checked="checked"'; ?>
								value="<?php echo $GOOGLEMAP_PRECISION_2; ?>"
							>
							<label for="new_prec_2" class="middle">
								<?php echo KT_I18N::translate('City'); ?>
							</label>
						</span>
						<span>
							<input
								type="radio"
								id="new_prec_3"
								name="NEW_PRECISION"
								onchange="updateMap();"
								<?php if ($precision == $GOOGLEMAP_PRECISION_3) echo 'checked="checked"'; ?>
								value="<?php echo $GOOGLEMAP_PRECISION_3; ?>"
							>
							<label for="new_prec_3" class="middle">
								<?php echo KT_I18N::translate('Neighborhood'); ?>
							</label>
						</span>
						<span>
							<input
								type="radio"
								id="new_prec_4"
								name="NEW_PRECISION"
								onchange="updateMap();"
								<?php if ($precision == $GOOGLEMAP_PRECISION_4) echo 'checked="checked"'; ?>
								value="<?php echo $GOOGLEMAP_PRECISION_4; ?>"
							>
							<label for="new_prec_4" class="middle">
								<?php echo KT_I18N::translate('House'); ?>
							</label>
						</span>
						<span>
							<input
								type="radio"
								id="new_prec_5"
								name="NEW_PRECISION"
								onchange="updateMap();"
								<?php if ($precision>$GOOGLEMAP_PRECISION_5) echo 'checked="checked"'; ?>
								value="<?php echo $GOOGLEMAP_PRECISION_5; ?>"
							>
							<label for="new_prec_5" class="middle">
								<?php echo KT_I18N::translate('Max'); ?>
							</label>
						</span>
					</div>
					<div class="cell callout medium-10 medium-offset-2 warning helpcontent">
						<?php echo KT_I18N::translate('This setting determines the number of digits that will be used in latitude and longitude .'); ?>
					</div>
					<div class="cell medium-2">
						<label for="NEW_PLACE_LATI" class="middle">
							<?php echo KT_Gedcom_Tag::getLabel('LATI'); ?>
						</label>
					</div>
					<div class="cell medium-6 input-group">
						<input
							type="text"
							id="NEW_PLACE_LATI"
							name="NEW_PLACE_LATI"
							placeholder="<?php echo /* I18N: Measure of latitude/longitude */ KT_I18N::translate('degrees') ?>"
							value="<?php if ($place_lati != null) echo abs($place_lati); ?>"
							onchange="updateMap();"
						>
						<div class="input-group-button">
							<select name="LATI_CONTROL" onchange="updateMap();">
								<option value="PL_N"
									<?php if ($place_lati > 0) {
										echo ' selected="selected"';
									} ?>
								>
									<?php echo KT_I18N::translate('North'); ?>
								</option>
								<option value="PL_S"
									<?php if ($place_lati < 0) {
										echo " selected=\"selected\"";
									} ?>
								>
									<?php echo KT_I18N::translate('South'); ?>
								</option>
							</select>
					    </div>
					</div>
					<div class="cell medium-4"></div>
					<div class="cell medium-2">
						<label for="NEW_PLACE_LONG" class="middle">
							<?php echo KT_Gedcom_Tag::getLabel('LONG'); ?>
						</label>
					</div>
					<div class="cell medium-6 input-group">
						<input
							type="text"
							id="NEW_PLACE_LONG"
							name="NEW_PLACE_LONG"
							placeholder="<?php echo KT_I18N::translate('degrees') ?>"
							value="<?php if ($place_long != null) echo abs($place_long); ?>"
							onchange="updateMap();"
						>
						<div class="input-group-button">
							<select name="LONG_CONTROL" onchange="updateMap();">
								<option value="PL_E"
									<?php if ($place_long > 0) {
										echo ' selected="selected"';
									} ?>
								>
									<?php echo KT_I18N::translate('East'); ?>
								</option>
								<option value="PL_W"
									<?php if ($place_long < 0) {
										echo ' selected="selected"';
									} ?>
								>
									<?php echo KT_I18N::translate('West'); ?>
								</option>
							</select>
						</div>
					</div>
					<div class="cell medium-4"></div>
					<div class="cell medium-2">
						<label for="NEW_ZOOM_FACTOR" class="middle">
							<?php echo KT_I18N::translate('Zoom factor'); ?>
						</label>
					</div>
					<div class="cell medium-6">
						<input
							type="text"
							id="NEW_ZOOM_FACTOR"
							name="NEW_ZOOM_FACTOR"
							value="<?php echo $zoomfactor; ?>"
							onchange="updateMap();"
						>
					</div>
					<div class="cell callout medium-10 medium-offset-2 warning helpcontent">
						<?php echo KT_I18N::translate('This value will be used as the minimal value when displaying this geographic location on a map.'); ?>
					</div>
					<div class="cell medium-2">
						<label class="middle">
							<?php echo KT_I18N::translate('Flag'); ?>
						</label>
					</div>
					<div class="cell medium-6" id="flagsDiv">
						<?php if ($place_icon == null || $place_icon == "") { ?>
							<a class="button hollow" href="#" onclick="change_icon();return false;"><?php echo KT_I18N::translate('Change flag'); ?></a>
						<?php } else { ?>
							<img
								alt="<?php echo /* I18N: The emblem of a country or region */ KT_I18N::translate('Flag'); ?>"
								src="<?php echo KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/' . $place_icon; ?>"
							>
							<a class="button hollow" href="#" onclick="change_icon();return false;"><?php echo KT_I18N::translate('Change flag'); ?></a>
							<a class="button hollow" href="#" onclick="remove_icon();return false;"><?php echo KT_I18N::translate('Remove flag'); ?></a>
						<?php } ?>
					</div>
					<div class="cell callout medium-10 medium-offset-2 warning helpcontent">
						<?php echo KT_I18N::translate('When this geographic location is shown, this flag will be displayed.'); ?>
					</div>
					<div class="cell medium-2">
						<label class="middle">
							<?php echo KT_I18N::translate('Add new or existing place image'); ?>
						</label>
					</div>
					<div class="cell medium-10">
						<div class="grid-x" id="imageDiv">
							<?php if ($place_image == null || $place_image == '') { ?>
								<div class="input-group autocomplete_container">
									<span class="input-group-label addnew">
									<a href="#" onclick="pastefield=document.getElementById('OBJE1694228369071759'); window.open('addmedia.php?action=showmediaform&amp;type=event', '_blank', ''); return false;" title="">
										<?php echo hintElement("span", "", "", KT_I18N::translate('Upload a new place image'), "<i class=\"' . $iconStyle . ' fa-square-plus\"></i>"); ?>
									</a>
									</span>
									<input id="autocompleteInput-OBJE1694228369071759" data-autocomplete-type="OBJE" type="text" value="" class="ui-autocomplete-input" autocomplete="off">
									<input type="hidden" name="text[]" id="selectedValue-OBJE1694228369071759" value="">
									<span class="input-group-label">
										<button id="OBJE1694228369071759" class="clearAutocomplete autocomplete_icon" data-position="top" data-alignment="center">
											<i class="fa-solid fa-xmark"></i>
										</button>
									</span>
								</div>
							<?php } else { ?>
								<?php $media = KT_Media::getInstance($place_image); ?>
								<div class="cell">
									<?php echo $media->displayImage(); ?>
								</div>
								<div class="cell">
									<?php echo media_object_info($media, false, true, true, [1,2,3], false) ?>
								</div>
							<?php } ?>
						</div>
						<div class="cell callout warning helpcontent">
							<?php echo KT_I18N::translate('Place image help text.....'); ?>
						</div>
					</div>
					<button class="button primary" type="submit">
						<i class="<?php echo $iconStyle; ?> fa-save"></i>
						<?php echo KT_I18N::translate('Save'); ?>
					</button>
					<button class="button hollow" type="button" onclick="window.close();">
						<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
						<?php echo KT_I18N::translate('Close'); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php

/** Take a place id and find its place in the hierarchy
 * Input: place ID
 * Output: ordered array of id=>name values, starting with the Top Level
 * e.g. array(0=>"Top Level", 16=>"England", 19=>"London", 217=>"Westminster");
 * NB This function exists in both places.php and admin_places_edit.php
 */
function place_id_to_hierarchy($id) {
	$statement=
		KT_DB::prepare("SELECT pl_parent_id, pl_place FROM `##placelocation` WHERE pl_id=?");
	$arr = array();
	while ($id != 0) {
		$row = $statement->execute(array($id))->fetchOneRow();
		$arr = array($id=>$row->pl_place)+$arr;
		$id = $row->pl_parent_id;
	}
	return $arr;
}

// NB This function exists in both admin_places.php and admin_places_edit.php
function getHighestIndex() {
	return (int)KT_DB::prepare("SELECT MAX(pl_id) FROM `##placelocation`")->fetchOne();
}
