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

global $GM_API_KEY;

$GM_API_KEY = get_module_setting('googlemap', 'GM_API_KEY', ''); // Optional Google Map API key

if ($GM_API_KEY) {
	$key = '&key=' . $GM_API_KEY;
} else {
	$key = '';
}

define('KT_GM_SCRIPT', 'https://maps.google.com/maps/api/js?v=3&amp;language=' . KT_LOCALE . $key);

// http://www.google.com/permissions/guidelines.html
//
// "... an unregistered Google Brand Feature should be followed by
// the superscripted letters TM or SM ..."
//
// Hence, use "Google Maps™"
//
// "... Use the trademark only as an adjective"
//
// "... Use a generic term following the trademark, for example:
// GOOGLE search engine, Google search"
//
// Hence, use "Google Maps™ mapping service" where appropriate.

class googlemap_KT_Module extends KT_Module implements KT_Module_Config, KT_Module_IndiTab, KT_Module_Chart {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: The name of a module.  Google Maps™ is a trademark.  Do not translate it? http://en.wikipedia.org/wiki/Google_maps */ KT_I18N::translate('Google Maps™');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Google Maps™” module */ KT_I18N::translate('Show the location of places and events using the Google Maps™ mapping service.');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'pedigree_map':
			case 'admin_flags':
			case 'admin_places':
			case 'admin_places_edit':
			case 'admin_preferences':
			case 'admin_placecheck':
				require KT_ROOT . KT_MODULES_DIR . 'googlemap/googlemap.php';
				require KT_ROOT . KT_MODULES_DIR . 'googlemap/defaultconfig.php';
				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/' . $mod_action . '.php';
				break;
			default:
				header('HTTP/1.0 404 Not Found');
				break;
		}
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_preferences';
	}

	// Implement KT_Module_Chart
	public function getChartMobile() {
		// exclude this module from mobile displays
		return false;
	}

	// Implement KT_Module_Chart
	public function getChartMenus() {
		global $controller;
		$indi_xref = $controller->getSignificantIndividual()->getXref();
		$menus	= array();
		$menu	= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=pedigree_map&amp;rootid=' . $indi_xref . '&amp;ged=' . KT_GEDURL,
			'menu-chart-pedigree_map'
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement KT_Module_IndiTab
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_IndiTab
	public function defaultTabOrder() {
		return 60;
	}

	// Implement KT_Module_IndiTab
	public function getPreLoadContent() {
		ob_start();
		require_once KT_ROOT . KT_MODULES_DIR . 'googlemap/googlemap.php';
		require_once KT_ROOT . KT_MODULES_DIR . 'googlemap/defaultconfig.php';
		echo '<link type="text/css" href ="' . KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/css/googlemap.min.css" rel="stylesheet">';
		setup_map();
		return ob_get_clean();
	}

	// Implement KT_Module_IndiTab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_IndiTab
	public function getTabContent() {
		global $controller, $iconStyle;

		ob_start();
		require_once KT_ROOT . KT_MODULES_DIR . 'googlemap/googlemap.php';
		require_once KT_ROOT . KT_MODULES_DIR . 'googlemap/defaultconfig.php';
		?>

		<div class="grid-x grid-padding-y" id="<?php echo $this->getName(); ?>_content">
			<?php if ($this->checkMapData()) { ?>
				<div class="cell tabHeader">
					<div class="grid-x">
						<?php if (KT_USER_IS_ADMIN) { ?>
							<div class="cell small-4">
								<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_preferences">
									<i class="<?php echo $iconStyle; ?> fa-globe"></i>
									<?php echo KT_I18N::translate('Google Maps™ preferences'); ?>
								</a>
							</div>
							<div class="cell small-4 medium-3">
								<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_places">
									<i class="<?php echo $iconStyle; ?> fa-map-pin"></i>
									<?php echo KT_I18N::translate('Geographic data'); ?>
								</a>
							</div>
							<div class="cell small-4 shrink">
								<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_placecheck">
									<i class="<?php echo $iconStyle; ?> fa-location-crosshairs"></i>
									<?php echo KT_I18N::translate('Place Check'); ?>
								</a>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="cell indiFact">
					<div class="grid-x" id="gm_mapTab">
				    	<div id="map_pane" class="cell"></div>

						<?php
					    $famids = array();
					    $families = $controller->record->getSpouseFamilies();

					    foreach ($families as $family) {
					        $famids[] = $family->getXref();
					    }

					    $controller->record->add_family_facts(false);

					    build_indiv_map($controller->record->getIndiFacts(), $famids);
					    ?>

						<script>loadMap();</script>

					</div>
				</div>
			<?php } else { ?>
				<div class="cell tabHeader">
					<div class="grid-x">
						<?php if (KT_USER_IS_ADMIN) { ?>
							<div class="cell shrink">
								<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_preferences">
									<i class="icon-config_maps">&nbsp;</i>
									<?php echo KT_I18N::translate('Google Maps™ preferences'); ?>
								</a>
							</div>
						<?php } ?>
						<div class="cell text-center">
							<?php echo KT_I18N::translate('No map data for this person'); ?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>

		<?php return ob_get_clean() ;

	}

	// Implement KT_Module_IndiTab
	public function hasTabContent() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER && (array_key_exists('googlemap', KT_Module::getActiveModules()) || KT_USER_IS_ADMIN);

	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut() {
		return false;
	}

	private function checkMapData() {
		global $controller;

		$xrefs		= "'" . $controller->record->getXref() . "'";
		$families	= $controller->record->getSpouseFamilies();

		foreach ($families as $family) {
			$xrefs .= ", '" . $family->getXref() . "'";
		}

		$data = KT_DB::prepare("
			SELECT COUNT(*) AS tot
			FROM `##placelinks`
			WHERE pl_gid IN (" . $xrefs . ")
			AND pl_file=?
		")->execute(array(KT_GED_ID))->fetchOne();

		return $data;
	}

}
