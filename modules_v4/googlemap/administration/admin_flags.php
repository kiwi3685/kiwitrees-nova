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

require KT_ROOT . KT_MODULES_DIR . 'googlemap/defaultconfig.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_I18N::translate('Select flag'))
	->pageHeader();

$stats				= new KT_Stats(KT_GEDCOM);
$countries			= $stats->get_all_countries();
$action				= safe_REQUEST($_REQUEST, 'action');
$countrySelected	= KT_Filter::get('countrySelected', null, 'Countries');
$stateSelected		= KT_Filter::get('stateSelected',   null, 'States');

$country = array();
if (is_dir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/flags')) {
	$rep = opendir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/flags');
	while ($file = readdir($rep)) {
		if (stristr($file, '.png')) {
			$country[] = substr($file, 0, strlen($file) - 4);
		}
	}
	closedir($rep);
	sort($country);
}

if ($countrySelected == 'Countries') {
	$flags = $country;
} else {
	$flags = array();
	if (is_dir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags')) {
		$rep = opendir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags');
		while ($file = readdir($rep)) {
			if (stristr($file, '.png')) {
				$flags[] = substr($file, 0, strlen($file) - 4);
			}
		}
		closedir($rep);
		sort($flags);
	}
}
// Sort flags into alpha list after transaltion
$flag_list = array();
foreach ($flags as $flag) {
	if (array_key_exists($flag, $countries)) {
		$flag_list[$flag] = $countries[$flag];
	} else {
		$flag_list[$flag] = $flag;
	}
}
uasort($flag_list, "utf8_strcasecmp");

$flags_s = array();
if ($stateSelected != 'States' && is_dir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/' . $stateSelected)) {
	$rep = opendir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/' . $stateSelected);
	while ($file = readdir($rep)) {
		if (stristr($file, '.png')) {
			$flags_s[] = substr($file, 0, strlen($file)-4);
		}
	}
	closedir($rep);
	sort($flags_s);
}

if ($action == 'ChangeFlag' && KT_Filter::post('FLAGS')) {
?>
	<script>
<?php if (KT_Filter::post('selcountry') == 'Countries') { ?>
			window.opener.document.editplaces.icon.value = 'places/flags/<?php echo KT_Filter::post('FLAGS'); ?>.png';
			window.opener.document.getElementById('flagsDiv').innerHTML = "<img src=\"<?php echo KT_STATIC_URL . KT_MODULES_DIR; ?>googlemap/places/flags/<?php echo KT_Filter::post('FLAGS'); ?>.png\">&nbsp;&nbsp;<a href=\"#\" onclick=\"change_icon();return false;\"><?php echo KT_I18N::translate('Change flag'); ?></a>&nbsp;&nbsp;<a href=\"#\" onclick=\"remove_icon();return false;\"><?php echo KT_I18N::translate('Remove flag'); ?></a>";
<?php } elseif (KT_Filter::post('selstate') != "States"){ ?>
			window.opener.document.editplaces.icon.value = 'places/<?php echo $countrySelected, '/flags/', $_POST['selstate'], '/', $flags_s[$_POST['FLAGS']]; ?>.png';
			window.opener.document.getElementById('flagsDiv').innerHTML = "<img src=\"<?php echo KT_STATIC_URL . KT_MODULES_DIR; ?>googlemap/places/<?php echo $countrySelected, "/flags/", $_POST['selstate'], "/", $flags_s[$_POST['FLAGS']]; ?>.png\">&nbsp;&nbsp;<a href=\"#\" onclick=\"change_icon();return false;\"><?php echo KT_I18N::translate('Change flag'); ?></a>&nbsp;&nbsp;<a href=\"#\" onclick=\"remove_icon();return false;\"><?php echo KT_I18N::translate('Remove flag'); ?></a>";
<?php } else { ?>
			window.opener.document.editplaces.icon.value = "places/<?php echo $countrySelected, "/flags/", KT_Filter::post('FLAGS'); ?>.png";
			window.opener.document.getElementById('flagsDiv').innerHTML = "<img src=\"<?php echo KT_STATIC_URL, KT_MODULES_DIR; ?>googlemap/places/<?php echo $countrySelected, "/flags/", KT_Filter::post('FLAGS'); ?>.png\">&nbsp;&nbsp;<a href=\"#\" onclick=\"change_icon();return false;\"><?php echo KT_I18N::translate('Change flag'); ?></a>&nbsp;&nbsp;<a href=\"#\" onclick=\"remove_icon();return false;\"><?php echo KT_I18N::translate('Remove flag'); ?></a>";
<?php } ?>
			window.opener.updateMap();
			window.close();
	</script>
<?php
	exit;
} else {
?>
<script>
	function selectCountry() {
		if (document.flags.COUNTRYSELECT.value == 'Countries') {
			window.location="module.php?mod=googlemap&mod_action=admin_flags";
		} else if (document.flags.STATESELECT.value != 'States') {
			window.location="module.php?mod=googlemap&mod_action=admin_flags&countrySelected=" + document.flags.COUNTRYSELECT.value + "&stateSelected=" + document.flags.STATESELECT.value;
		} else {
			window.location="module.php?mod=googlemap&mod_action=admin_flags&countrySelected=" + document.flags.COUNTRYSELECT.value;
		}
	}
</script>
<?php
}
$countryList = array();
$placesDir = scandir(KT_MODULES_DIR.'googlemap/places/');
for ($i = 0; $i < count($country); $i++) {
	if (count(preg_grep('/' . $country[$i] . '/', $placesDir)) != 0) {
		$rep = opendir(KT_MODULES_DIR.'googlemap/places/'.$country[$i].'/');
		while ($file = readdir($rep)) {
			if (stristr($file, 'flags')) {
				if (isset($countries[$country[$i]])) {
					$countryList[$country[$i]] = $countries[$country[$i]];
				} else {
					$countryList[$country[$i]] = $country[$i];
				}
			}
		}
		closedir($rep);
	}
}
asort($countryList);

$stateList = array();
if ($countrySelected != 'Countries') {
	$placesDir = scandir(KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/');
	for ($i = 0; $i < count($flags); $i++) {
		if (in_array($flags[$i], $placesDir)) {
			$rep = opendir(KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/' . $flags[$i] . '/');
			while ($file = readdir($rep)) {
				$stateList[$flags[$i]] = $flags[$i];
			}
			closedir($rep);
		}
	}
	asort($stateList);
}
?>

<!-- START OF DISPLAY PAGE -->
<div id="changeflags-page" class="cell">
	<h4><?php echo KT_I18N::translate('Change flag'); ?></h4>
	<div class="grid-x">
		<form class="cell" method="post" id="flags" name="flags" action="module.php?mod=googlemap&amp;mod_action=admin_flags&amp;countrySelected=<?php echo $countrySelected; ?>&amp;stateSelected=<?php echo $stateSelected; ?>">
			<input type="hidden" name="action" value="ChangeFlag">
			<input type="hidden" name="selcountry" value="<?php echo $countrySelected; ?>">
			<input type="hidden" name="selstate" value="<?php echo $stateSelected; ?>">

			<div class="grid-x">
				<div class="cell callout info-help ">
					<?php echo KT_I18N::translate('
						Some countries have state or county flags. Using the pull down menu it is possible to select a country,
						and display it\'s flags for selection.
						If no flags are shown, then there no flags hare provided for this country.
					'); ?>
				</div>
				<div class="cell medium-2">
					<label for"selectCountry">
						<?php echo KT_I18N::translate('Select country'); ?>
					</label>
				</div>
				<div class="cell medium-6">
					<select name="COUNTRYSELECT" dir="ltr" onchange="selectCountry()">
						<option value="Countries">
							<?php echo KT_I18N::translate('Countries'); ?>
						</option>
						<?php foreach ($countryList as $country_key=>$country_name) { ?>
							<option value="<?php echo $country_key; ?>"
								<?php if ($countrySelected == $country_key) {
									echo ' selected="selected" ';
								} ?>
							>
								<?php echo $country_name; ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="cell medium-4"></div>
				<div class="cell">
					<?php if ($countrySelected == 'Countries' || count($stateList) == 0) {
						if (count($flag_list) > 50) { // Add second set of save/close buttons ?>
							<div class="cell">
								<button class="button primary" type="submit">
									<i class="<?php echo $iconStyle; ?> fa-save"></i>
									<?php echo KT_I18N::translate('Save'); ?>
								</button>
								<button class="button hollow" type="button" onclick="window.close();">
									<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
									<?php echo KT_I18N::translate('Close'); ?>
								</button>
							</div>
						<?php }
					} ?>
					<div class="clearfloat"></div>
					<hr class="cell">
					<?php
					foreach ($flag_list as $iso => $name) {
						if ($countrySelected == 'Countries') {
							echo '<div class="flags_item">
								<span>
									<input type="radio" dir="ltr" name="FLAGS" value="' . $iso . '">
									<img src="' . KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/places/flags/' . $iso . '.png" alt="' . $name . '"  title="' . $iso . '">
								</span>
								<label>' . $name . '</label>
							</div>';
						} else {
							echo '<div class="flags_item">
								<span>
									<input type="radio" dir="ltr" name="FLAGS" value="' . $iso . '">
									<img src="' . KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/' . $iso . '.png">
								</span>
								<label>' . $iso . '</label>
							</div>';
						}
					} ?>
				</div>

				<div class="cell callout info-help "<?php echo ($countrySelected == 'Countries' || count($stateList) == 0)  ? ' style=" display: none"' : ''; ?>>
					<?php echo KT_I18N::translate('
						Some countries have a further level of flags. Using the pull down menu it is possible to select a subdivision,
						and display it\'s flags for selection.
						If no flags are shown, then there no flags hare provided for this country.
					'); ?>
				</div>
				<div class="cell medium-2">
					<label for"selectCountry">
						<?php echo KT_I18N::translate('Select country'); ?>
					</label>
				</div>
				<div class="cell medium-6">
					<select name="STATESELECT" dir="ltr" onchange="selectCountry()">
						<option value="States"><?php echo /* I18N: Part of a country, state/region/county */ KT_I18N::translate('Subdivision'); ?></option>
						<?php foreach ($stateList as $state_key=>$state_name) {
							echo '<option value="', $state_key, '"';
							if ($stateSelected == $state_key) echo ' selected="selected"';
							echo '>', $state_name, '</option>';
						} ?>
					</select>
					<hr class="cell">
				</div>
				<div class="flags_wrapper">
					<?php
					$j=1;
					for ($i=0; $i<count($flags_s); $i++) {
						if ($stateSelected != 'States') {
							echo '
								<div class="flags_item">
									<span>
										<input type="radio" dir="ltr" name="FLAGS" value="', $i, '">
										<img src="' . KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/' . $stateSelected . '/' . $flags_s[$i], '.png">
									</span>
									<label>', $flags_s[$i], '</label>
								</div>
							';
						}
						$j++;
					} ?>
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
		</form>
	</div>
</div>
