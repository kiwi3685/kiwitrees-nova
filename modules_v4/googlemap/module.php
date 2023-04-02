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

global $GM_API_KEY;

$GM_API_KEY = get_module_setting('googlemap', 'GM_API_KEY', ''); // Required Google Map API key

if ($GM_API_KEY) {
	$key = '?key=' . $GM_API_KEY;
} else {
	$key = '';
}

define('KT_GM_SCRIPT','https://maps.googleapis.com/maps/api/js' . $key . '&v=3&language=\'' . KT_LOCALE . '\'&callback=Function.prototype');

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
		return
			/* I18N: The name of a module.  Google Maps™ is a trademark.
			Do not translate it? http://en.wikipedia.org/wiki/Google_maps */
			KT_I18N::translate('Google Maps™')
		;
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Google Maps™” module */ KT_I18N::translate('Show the location of places and events using the Google Maps™ mapping service.');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'pedigree_map':
				$this->pedigree_map();
				break;
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
	public function getConfigLink($option = '') {
		if ($option) {
			return 'module.php?mod='.$this->getName() . '&amp;mod_action=' . $option;
		} else {
			return 'module.php?mod='.$this->getName() . '&amp;mod_action=admin_preferences';
		}
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

		ob_start();	 ?>	
		<div class="grid-x grid-padding-y" id="<?php echo $this->getName(); ?>_content">
			<?php if ($this->checkMapData()) {
				require_once KT_ROOT . KT_MODULES_DIR . 'googlemap/googlemap.php';
				require_once KT_ROOT . KT_MODULES_DIR . 'googlemap/defaultconfig.php';
 				?>
				<div class="cell tabHeader">
					<?php if (KT_USER_IS_ADMIN) { ?>
						<?php echo googlemap_links(); ?>
					<?php } ?>
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

	private function pedigree_map() {
		global $controller, $PEDIGREE_GENERATIONS, $MAX_PEDIGREE_GENERATIONS, $iconStyle;

		require KT_ROOT . KT_MODULES_DIR . 'googlemap/defaultconfig.php';
		require_once KT_ROOT . KT_MODULES_DIR . 'googlemap/googlemap.php';

		$controller = new KT_Controller_Pedigree();

		// Start of internal configuration variables
		// Limit this to match available number of icons.
		// 8 generations equals up to 255 individuals
		$MAX_PEDIGREE_GENERATIONS = min($MAX_PEDIGREE_GENERATIONS, 8);

		// End of internal configuration variables
		$controller
		    ->setPageTitle(/* I18N: %s is an individual’s name */ KT_I18N::translate('Pedigree map of %s', $controller->getPersonName()))
		    ->pageHeader()
		    ->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
		    ->addInlineJavascript('autocomplete();');

		    $xref	= $controller->root->getXref();
		    $person	= KT_Person::getInstance($xref);
		?>

		<div id="pedigreemap-page" class="grid-x grid-padding-x">
		    <div class="cell large-10 large-offset-1">
		        <h3><?php echo $controller->getPageTitle(); ?></h3>
		        <form name="people" method="get" action="module.php?ged=<?php echo KT_GEDURL; ?>&amp;mod=googlemap&amp;mod_action=pedigree_map">
		            <input type="hidden" name="mod" value="googlemap">
		            <input type="hidden" name="mod_action" value="pedigree_map">
		            <div class="grid-x grid-margin-x">
		                <div class="cell medium-4">
		                    <label class="h5" for="autocompleteInput">
		                        <?php echo KT_I18N::translate('Individual'); ?>
		                    </label>
							<?php echo autocompleteHtml(
								'googlemap', // id
								'INDI', // TYPE
								'', // autocomplete-ged
								strip_tags($person->getLifespanName()), // input value
								'', // placeholder
								'rootid', // hidden input name
								$controller->rootid // hidden input value
							); ?>
		                </div>
		                <div class="cell medium-4">
		                    <label for="pedigree_generations" class="h5">
		                        <?php echo KT_I18N::translate('Generations'); ?>
		                    </label>
		                    <select name="PEDIGREE_GENERATIONS" id="pedigree_generations">
		                    <?php
		                        for ($p=3; $p<=$MAX_PEDIGREE_GENERATIONS; $p++) {
		                            echo '<option value="', $p, '" ';
		                            if ($p == $controller->PEDIGREE_GENERATIONS) {
		                                echo 'selected="selected"';
		                            }
		                            echo '>', $p, '</option>';
		                        }
		                    ?>
		                    </select>
		                </div>
		            </div>
		            <button class="button" type="submit">
		                <i class="<?php echo $iconStyle; ?> fa-eye"></i>
		                <?php echo KT_I18N::translate('Show'); ?>
		            </button>
		        </form>
		        <hr>
		    </div>
		    <!-- end of form -->


		    <!-- count records by type -->
		    <?php
		    $curgen=1;
		    $priv=0;
		    $count=0;
		    $miscount=0;
		    $missing = '';

		    for ($i=0; $i<($controller->treesize); $i++) {
		        // -- check to see if we have moved to the next generation
		        if ($i+1 >= pow(2, $curgen)) {$curgen++;}
		        $person = KT_Person::getInstance($controller->treeid[$i]);
		        if (!empty($person)) {
		            $name = $person->getFullName();
		            if ($name == KT_I18N::translate('Private')) $priv++;
		            $place = $person->getBirthPlace();
		            if (empty($place)) {
		                $latlongval[$i] = NULL;
		            } else {
		                $latlongval[$i] = get_lati_long_placelocation($person->getBirthPlace());
		                if ($latlongval[$i] != NULL && $latlongval[$i]['lati'] == '0' && $latlongval[$i]['long'] == '0') {
		                    $latlongval[$i] = NULL;
		                }
		            }
		            if ($latlongval[$i] != NULL) {
		                $lat[$i] = str_replace(array('N', 'S', ','), array('', '-', '.'), $latlongval[$i]['lati']);
		                $lon[$i] = str_replace(array('E', 'W', ','), array('', '-', '.'), $latlongval[$i]['long']);
		                if (($lat[$i] != NULL) && ($lon[$i] != NULL)) {
		                    $count++;
		                } else { // The place is in the table but has empty values
		                    if ($name) {
		                        if ($missing) {
		                            $missing .= ', ';
		                        }
		                        $missing .= '<a href="' . $person->getHtmlUrl() . '">' . $name . '</a>';
		                        $miscount++;
		                    }
		                }
		            } else { // There was no place, or not listed in the map table
		                if ($name) {
		                    if ($missing) {
		                        $missing .= ', ';
		                    }
		                    $missing .= '<a href="' . $person->getHtmlUrl() . '">' . $name . '</a>';
		                    $miscount++;
		                }
		            }
		        }
		    }
		    // end of count records by type

		    // start of map display
		    ?>
		    <div id="pedigreemap_chart" class="cell large-10 large-offset-1">
		        <div class="grid-x grid-margin-x grid-margin-y">
		            <div class="cell medium-9">
		                <div class="shadow" id="pm_map"></div>
			            <?php if (KT_USER_IS_ADMIN) { ?>
			            	<div class="cell medium-9">
			            		<?php echo googlemap_links(); ?>
			            	</div>
			            <?php } ?>
		            </div>
		            <div class="cell medium-3">
		                <div class="shadow" id="side_bar"></div>
		            </div>
		        </div>
		        <?php // display info under map ?>
		        <hr>
		        <?php // print summary statistics ?>
		        <?php if (isset($curgen)) { ?>
		            <div class="cell">
		                <?php
		                    $total  = pow(2, $curgen) - 1;
		                    $miss   = $total - $count - $priv;
		                    echo KT_I18N::plural(
		                        '%1$d individual displayed, out of the normal total of %2$d, from %3$d generations.',
		                        '%1$d individuals displayed, out of the normal total of %2$d, from %3$d generations.',
		                        $count,
		                        $count, $total, $curgen
		                    )
		                ?>
		            </div>
		            <?php if ($priv) { ?>
		                <div class="cell">
		                    <?php echo KT_I18N::plural('%s individual is private.', '%s individuals are private.', $priv, $priv); ?>
		                </div>
		            <?php } ?>
		            <?php if ($count + $priv != $total) { ?>
		                <div class="cell">
		                    <?php if ($miscount == 0) {
		                        echo KT_I18N::translate('No ancestors in the database.');
		                    } else {
		                        echo /* I18N: %1$d is a count of individuals, %2$s is a list of their names */ KT_I18N::plural(
		                            '%1$d individual is missing birthplace map coordinates: %2$s.',
		                            '%1$d individuals are missing birthplace map coordinates: %2$s.',
		                            $miscount, $miscount, $missing);
		                    } ?>
		                </div>
		            <?php }
		        } ?>
		    </div>
		</div>
		<!-- end of map display -->

		<!-- Start of map scripts -->
		<script src="<?php echo KT_GM_SCRIPT; ?>"></script>
		<?php $controller->addInlineJavascript($this->pedigree_map_js());

	}


	private function pedigree_map_js() {
		global $controller, $SHOW_HIGHLIGHT_IMAGES, $PEDIGREE_GENERATIONS;
		// The HomeControl returns the map to the original position and style
		$js='function HomeControl(controlDiv, pm_map) {'.
			// Set CSS styles for the DIV containing the control
			// Setting padding to 5 px will offset the control from the edge of the map
			'controlDiv.style.paddingTop = "5px";
			controlDiv.style.paddingRight = "0px";'.
			// Set CSS for the control border
			'var controlUI = document.createElement("DIV");
			controlUI.style.backgroundColor = "white";
			controlUI.style.color = "black";
			controlUI.style.borderColor = "black";
			controlUI.style.borderColor = "black";
			controlUI.style.borderStyle = "solid";
			controlUI.style.borderWidth = "2px";
			controlUI.style.cursor = "pointer";
			controlUI.style.textAlign = "center";
			controlUI.title = "";
			controlDiv.appendChild(controlUI);'.
			// Set CSS for the control interior
			'var controlText = document.createElement("DIV");
			controlText.style.fontFamily = "Arial,sans-serif";
			controlText.style.fontSize = "12px";
			controlText.style.paddingLeft = "15px";
			controlText.style.paddingRight = "15px";
			controlText.innerHTML = "<b>' . KT_I18N::translate('Redraw map') . '<\/b>";
			controlUI.appendChild(controlText);'.
			// Setup the click event listeners: simply set the map to original LatLng
			'controlUI.addEventListener( "click", function() {
				pm_map.setMapTypeId(google.maps.MapTypeId.TERRAIN),
				pm_map.fitBounds(bounds),
				pm_map.setCenter(bounds.getCenter()),
				infowindow.close()
				if (document.getElementById(lastlinkid) != null) {
					document.getElementById(lastlinkid).className = "person_box:target";
				}
			});
		}'.
		// This function picks up the click and opens the corresponding info window
		'function myclick(i) {
			if (document.getElementById(lastlinkid) != null) {
				document.getElementById(lastlinkid).className = "person_box:target";
			}
			google.maps.event.trigger(gmarkers[i], "click");
		}'.
		// this variable will collect the html which will eventually be placed in the side_bar
		'var side_bar_html = "";'.
		// arrays to hold copies of the markers and html used by the side_bar
		// because the function closure trick doesnt work there
		'var gmarkers = [];
		var i = 0;
		var lastlinkid;
		var infowindow = new google.maps.InfoWindow({});'.
		// === Create an associative array of GIcons()
		'var gicons = [];
		gicons["1"]        = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon1.png")
		gicons["1"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["2"]         = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon2.png")
		gicons["2"].shadow  = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["2L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon2L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["2L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["2R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon2R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["2R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["2Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon2Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["2Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon2Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["3"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon3.png")
		gicons["3"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["3L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon3L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["3L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["3R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon3R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["3R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["3Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon3Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["3Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon3Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["4"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon4.png")
		gicons["4"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["4L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon4L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["4L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["4R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon4R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["4R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["4Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon4Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["4Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon4Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["5"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon5.png")
		gicons["5"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["5L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon5L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["5L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["5R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon5R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["5R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["5Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon5Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["5Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon5Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["6"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon6.png")
		gicons["6"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["6L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon6L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["6L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["6R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon6R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["6R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["6Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon6Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["6Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon6Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["7"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon7.png")
		gicons["7"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["7L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon7L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["7L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["7R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon7R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["7R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["7Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon7Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["7Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon7Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["8"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon8.png")
		gicons["8"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["8L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon8L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["8L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["8R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon8R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["8R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["8Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon8Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["8Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon8Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);'.
		// / A function to create the marker and set up the event window
		'function createMarker(point, name, html, mhtml, icontype) {
			// alert(i+". "+name+", "+icontype);
			var contentString = "<div id=\'iwcontent_edit\'>"+mhtml+"<\/div>";'.
			//create a marker with the requested icon
			'var marker = new google.maps.Marker({
				icon:     gicons[icontype],
				shadow:   gicons[icontype].shadow,
				map:      pm_map,
				position: point,
				zIndex:   0
			});
			var linkid = "link"+i;
			google.maps.event.addListener(marker, "click", function() {
				infowindow.close();
				infowindow.setContent(contentString);
				infowindow.open(pm_map, marker);
				document.getElementById(linkid).className = "person_box";
				if (document.getElementById(lastlinkid) != null) {
					document.getElementById(lastlinkid).className = "person_box:target";
				}
				lastlinkid=linkid;
			});'.
			// save the info we need to use later for the side_bar
			'gmarkers[i] = marker;'.
			// add a line to the side_bar html
			'side_bar_html += "<div id=\'"+linkid+"\' onclick=\'myclick(" + i + ")\'>" + html +"<br></div>";
			i++;
			return marker;
		};'.
		// create the map
		'var myOptions = {
			zoom: 6,
			center: new google.maps.LatLng(0, 0),
			mapTypeId: google.maps.MapTypeId.TERRAIN,  // ROADMAP, SATELLITE, HYBRID, TERRAIN
			mapTypeControlOptions: {
				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU  // DEFAULT, DROPDOWN_MENU, HORIZONTAL_BAR
			},
			navigationControlOptions: {
				position: google.maps.ControlPosition.TOP_RIGHT,  // BOTTOM, BOTTOM_LEFT, LEFT, TOP, etc
				style: google.maps.NavigationControlStyle.SMALL   // ANDROID, DEFAULT, SMALL, ZOOM_PAN
			},
			streetViewControl: false,  // Show Pegman or not
			scrollwheel: true
		};
		var pm_map = new google.maps.Map(document.getElementById("pm_map"), myOptions);
		google.maps.event.addListener(pm_map, "maptypechanged", function() {
			map_type.refresh();
		});
		google.maps.event.addListener(pm_map, "click", function() {
			if (document.getElementById(lastlinkid) != null) {
				document.getElementById(lastlinkid).className = "person_box:target";
			}
		infowindow.close();
		});'.
		// Create the DIV to hold the control and call HomeControl() passing in this DIV. --
		'var homeControlDiv = document.createElement("DIV");
		var homeControl = new HomeControl(homeControlDiv, pm_map);
		homeControlDiv.index = 1;
		pm_map.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);'.
		// create the map bounds
		'var bounds = new google.maps.LatLngBounds();';
		// add the points
		$curgen=1;
		$priv=0;
		$count=0;
		$event = '<img src="' . KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/images/sq1.png">'.
			'<strong>&nbsp;' . KT_I18N::translate('Root') . ':&nbsp;</strong>';
		$colored_line = array('1'=>'#FF0000','2'=>'#0000FF','3'=>'#00FF00',
						'4'=>'#FFFF00','5'=>'#00FFFF','6'=>'#FF00FF',
						'7'=>'#C0C0FF','8'=>'#808000');

		for ($i=0; $i<($controller->treesize); $i++) {
			// moved up to grab the sex of the individuals
			$person = KT_Person::getInstance($controller->treeid[$i]);
			if ($person) {
				$name = $person->getFullName();

				// -- check to see if we have moved to the next generation
				if ($i+1 >= pow(2, $curgen)) {
					$curgen++;
				}
				$relationship=get_relationship_name(get_relationship($controller->root, $person, false, 0));
				if (empty($relationship)) $relationship = KT_I18N::translate('self');
				$event = '<img src=\"' . KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/images/sq' . $curgen . '.png\">'.
					'<span class=\"relationship\">' . $relationship . '</span>';
				// add thumbnail image
				if ($SHOW_HIGHLIGHT_IMAGES) {
					$image = $person->displayImage();
				} else {
					$image = '';
				}
				// end of add image

				$dataleft  = addslashes($image) . $event . addslashes($name);
				$datamid   = "<a href='".$person->getHtmlUrl()."' id='alturl' title='" . KT_I18N::translate('Individual information') . "'>";
				$datamid .= '<br>' . KT_I18N::translate('View Person') . '<br>';
				$datamid  .= '</a>';
				$dataright = '<span class=\"event\">' . KT_I18N::translate('Birth') . ' </span>' .
						addslashes($person->getBirthDate()->Display(false)) . '<br>' . $person->getBirthPlace();

				$latlongval[$i] = get_lati_long_placelocation($person->getBirthPlace());
				if ($latlongval[$i] != NULL) {
					$lat[$i] = (double)str_replace(array('N', 'S', ','), array('', '-', '.'), $latlongval[$i]['lati']);
					$lon[$i] = (double)str_replace(array('E', 'W', ','), array('', '-', '.'), $latlongval[$i]['long']);
					if ($lat[$i] || $lon[$i]) {
						if (($latlongval[$i]['icon'] != NULL)) {
							$flags[$i] = $latlongval[$i]['icon'];
							$ffile = strrchr($latlongval[$i]['icon'], '/');
							$ffile = substr($ffile,1, strpos($ffile, '.')-1);
							if (empty($flags[$ffile])) {
								$flags[$ffile] = $i; // Only generate the flag once
								$js .= 'var point = new google.maps.LatLng(' . $lat[$i] . ',' . $lon[$i]. ');';
								$js .= 'var Marker1_0_flag = new google.maps.MarkerImage();';
								$js .= 'Marker1_0_flag.image = "' . KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/'.$flags[$i].'";';
								$js .= 'Marker1_0_flag.shadow = "' . KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/images/flag_shadow.png";';
								$js .= 'Marker1_0_flag.iconSize = new google.maps.Size(25, 15);';
								$js .= 'Marker1_0_flag.shadowSize = new google.maps.Size(35, 45);';
								$js .= 'Marker1_0_flag.iconAnchor = new google.maps.Point(12, 15);';
								$js .= 'var Marker1_0 = new google.maps.LatLng(point, {icon:Marker1_0_flag});';
							}
						}
						$marker_number = $curgen;
						$dups=0;
						for ($k=0; $k<$i; $k++) {
							if ($latlongval[$i] == $latlongval[$k]) {
								$dups++;
								switch($dups) {
									case 1: $marker_number = $curgen . 'L'; break;
									case 2: $marker_number = $curgen . 'R'; break;
									case 3: $marker_number = $curgen . 'Ls'; break;
									case 4: $marker_number = $curgen . 'Rs'; break;
									case 5: //adjust position where markers have same coodinates
									default: $marker_number = $curgen;
										$lon[$i] = $lon[$i]+0.0025;
										$lat[$i] = $lat[$i]+0.0025;
										break;
								}
							}
						}
						$js .= 'var point = new google.maps.LatLng('.$lat[$i].','.$lon[$i].');';
						$js .= "var marker = createMarker(point, \"".addslashes($name)."\",\n\t\"<div>".$dataleft.$datamid.$dataright."</div>\", \"";
						$js .= "<div class='iwstyle'>";
						$js .= "<a href='module.php?ged=" . KT_GEDURL."&amp;mod=googlemap&amp;mod_action=pedigree_map&amp;rootid=" . $person->getXref() . "&amp;PEDIGREE_GENERATIONS={$PEDIGREE_GENERATIONS}";
						$js .= "' title='" . KT_I18N::translate('Pedigree map')."'>".$dataleft."</a>".$datamid.$dataright."</div>\", \"".$marker_number."\");";
						// Construct the polygon lines
						$to_child = (intval(($i-1)/2)); // Draw a line from parent to child
						if (array_key_exists($to_child, $lat) && $lat[$to_child]!=0 && $lon[$to_child]!=0) {
							$js .='
							var linecolor;
							var plines;
							var lines = [new google.maps.LatLng('.$lat[$i].','.$lon[$i].'),
								new google.maps.LatLng('.$lat[$to_child].','.$lon[$to_child].')];
							linecolor = "'.$colored_line[$curgen].'";
							plines = new google.maps.Polygon({
								paths: lines,
								strokeColor: linecolor,
								strokeOpacity: 0.8,
								strokeWeight: 3,
								fillColor: "#FF0000",
								fillOpacity: 0.1
							});
							plines.setMap(pm_map);';
						}
					// Extend and fit marker bounds
					$js .='bounds.extend(point);';
					$js .='pm_map.fitBounds(bounds);';
					$count++;
					}
				}
			} else {
				$latlongval[$i] = NULL;
			}
		}
		$js .='pm_map.setCenter(bounds.getCenter());'.
		// Close the sidebar highlight when the infowindow is closed
		'google.maps.event.addListener(infowindow, "closeclick", function() {
			document.getElementById(lastlinkid).className = "person_box:target";
		});'.
		// put the assembled side_bar_html contents into the side_bar div
		'document.getElementById("side_bar").innerHTML = side_bar_html;'.
		// create the context menu div
		'var contextmenu = document.createElement("div");
			contextmenu.style.visibility="hidden";
			contextmenu.innerHTML = "<a href=\'#\' onclick=\'zoomIn()\'><div class=\'optionbox\'>&nbsp;&nbsp;' . KT_I18N::translate('Zoom in') . '&nbsp;&nbsp;</div></a>"
								+ "<a href=\'#\' onclick=\'zoomOut()\'><div class=\'optionbox\'>&nbsp;&nbsp;' . KT_I18N::translate('Zoom out') . '&nbsp;&nbsp;</div></a>"
								+ "<a href=\'#\' onclick=\'zoomInHere()\'><div class=\'optionbox\'>&nbsp;&nbsp;' . KT_I18N::translate('Zoom in here') . '</div></a>"
								+ "<a href=\'#\' onclick=\'zoomOutHere()\'><div class=\'optionbox\'>&nbsp;&nbsp;' . KT_I18N::translate('Zoom out here') . '&nbsp;&nbsp;</div></a>"
								+ "<a href=\'#\' onclick=\'centreMapHere()\'><div class=\'optionbox\'>&nbsp;&nbsp;' . KT_I18N::translate('Center map here') . '&nbsp;&nbsp;</div></a>";'.
		// listen for singlerightclick
		'google.maps.event.addListener(pm_map,"singlerightclick", function(pixel,tile) {'.
			// store the "pixel" info in case we need it later
			// adjust the context menu location if near an egde
			// create a GControlPosition
			// apply it to the context menu, and make the context menu visible
			'clickedPixel = pixel;
			var x=pixel.x;
			var y=pixel.y;
			if (x > pm_map.getSize().width - 120) { x = pm_map.getSize().width - 120 }
			if (y > pm_map.getSize().height - 100) { y = pm_map.getSize().height - 100 }
			var pos = new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(x,y));
			pos.apply(contextmenu);
			contextmenu.style.visibility = "visible";
		});
		'.
		// functions that perform the context menu options
		'function zoomIn() {'.
			// perform the requested operation
			'pm_map.zoomIn();'.
			// hide the context menu now that it has been used
			'contextmenu.style.visibility="hidden";
		}
		function zoomOut() {'.
			// perform the requested operation
			'pm_map.zoomOut();'.
			// hide the context menu now that it has been used
			'contextmenu.style.visibility="hidden";
		}
		function zoomInHere() {'.
			// perform the requested operation
			'var point = pm_map.fromContainerPixelToLatLng(clickedPixel)
			pm_map.zoomIn(point,true);'.
			// hide the context menu now that it has been used
			'contextmenu.style.visibility="hidden";
		}
		function zoomOutHere() {'.
			// perform the requested operation
			'var point = pm_map.fromContainerPixelToLatLng(clickedPixel)
			pm_map.setCenter(point,pm_map.getZoom()-1);'.
			// There is no pm_map.zoomOut() equivalent
			// hide the context menu now that it has been used
			'contextmenu.style.visibility="hidden";
		}
		function centreMapHere() {'.
			// perform the requested operation
			'var point = pm_map.fromContainerPixelToLatLng(clickedPixel)
			pm_map.setCenter(point);'.
			// hide the context menu now that it has been used
			'contextmenu.style.visibility="hidden";
		}'.
		// If the user clicks on the map, close the context menu
		'google.maps.event.addListener(pm_map, "click", function() {
			contextmenu.style.visibility="hidden";
		});';
		return $js;
	}


}
