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

class chart_fanchart_KT_Module extends KT_Module implements KT_Module_Chart {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Fanchart');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Fanchart” module */ KT_I18N::translate('An individual\'s fanchart');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch ($mod_action) {
		case 'update':
			Zend_Session::writeClose();
			$rootId	= KT_Filter::get('rootid', KT_REGEX_XREF);
			$person	= KT_Person::getInstance($rootId, KT_GED_ID);
			$controller	= new KT_Controller_Fanchart();

			header('Content-Type: application/json;charset=UTF-8');

			echo json_encode($controller->buildJsonTree($person));
			break;
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

	// Implement KT_Module_Chart
	public function getChartMobile() {
		// exclude this module from mobile displays
		return false;
	}

	private function show(){
		global $controller, $iconStyle;
		$controller = new KT_Controller_Fanchart();

        $chartParams = json_encode(
	        array(
				'fanDegree'     => $controller->fanDegree,
	            'generations'   => $controller->generations,
	            'defaultColor'  => $controller->getColor(),
	            'fontScale'     => $controller->fontScale,
	            'fontColor'     => $controller->getChartFontColor(),
	            'updateUrl'     => $controller->getUpdateUrl(),
	            'individualUrl' => $controller->getIndividualUrl(),
	            'data'          => $controller->buildJsonTree($controller->root),
            )
	    );

		$controller
			->restrictAccess(KT_Module::isActiveChart(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addExternalJavascript(KT_D3_JS)
			->addExternalJavascript(KT_STATIC_URL . KT_MODULES_DIR . $this->getName() . '/js/ancestral-fan-chart.js')
			->addInlineJavascript('
				autocomplete();

				// Init widget
				if (typeof jQuery().ancestralFanChart === "function") {
				    jQuery("#fan_chart").ancestralFanChart(' . $chartParams . ');
				}
		    ');

		require KT_ROOT . 'includes/functions/functions_edit.php';
		$xref	= $controller->root->getXref();
		$person	= KT_Person::getInstance($xref);

		echo pageStart('fanchart', $controller->getPageTitle()); ?>
			<form name="people" id="people" method="get" action="?">
				<input type="hidden" name="mod" value="<?php echo $this->getName(); ?>">
				<input type="hidden" name="mod_action" value="show">
				<input type="hidden" name="ged" value="<?php echo KT_GEDURL; ?>">
				<div class="grid-x grid-margin-x">
					<div class="cell medium-4">
						<label class="h5" for="autocompleteInput"><?php echo KT_I18N::translate('Individual'); ?></label>
						<?php echo autocompleteHtml(
							'fan', // id
							'INDI', // TYPE
							'', // autocomplete-ged
							strip_tags($person->getLifespanName()), // input value
							'', // placeholder
							'rootid', // hidden input name
							'' // hidden input value
						); ?>
					</div>
					<div class="cell medium-4">
						<label class="h5" for="generations"><?php echo KT_I18N::translate('Generations'); ?></label>
						<div class="grid-x grid-padding-x">
							<div class="cell small-9">
							  <div class="slider" data-slider data-start="2" data-step="1" data-end="10" data-initial-start="<?php echo $controller->generations; ?>">
							    <span class="slider-handle"  data-slider-handle role="slider" tabindex="1" aria-controls="generations"></span>
							    <span class="slider-fill" data-slider-fill></span>
							  </div>
							</div>
							<div class="cell small-3">
							  <input type="number" id="generations" name="generations">
							</div>
						</div>
					</div>
					<div class="cell medium-4">
						<label class="h5" for="fanDegree"><?php echo KT_I18N::translate('Degrees'); ?></label>
						<div class="grid-x grid-padding-x">
							<div class="cell small-9">
							  <div class="slider" data-slider data-start="180" data-step="30" data-end="360" data-initial-start="<?php echo $controller->fanDegree; ?>">
							    <span class="slider-handle"  data-slider-handle role="slider" tabindex="1" aria-controls="fanDegree"></span>
							    <span class="slider-fill" data-slider-fill></span>
							  </div>
							</div>
							<div class="cell small-3">
								<input type="number" id="fanDegree" name="fanDegree">
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
			<?php if ($controller->error_message) { ?>
				<p class="callout alert"><?php echo $controller->error_message; ?></p>
				<?php exit;
			} else { ?>
				<div id="fan_chart" class="cell text-center"></div>
			<?php }
		echo pageClose();
	}

	// Implement KT_Module_Chart
	public function getChartMenus() {
		global $controller;
		$person	= $controller->getSignificantIndividual();
		$menus	= array();
		$menu	= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $person->getXref() . '&amp;ged=' . KT_GEDURL,
			'menu-chart-fanchart'
		);
		$menus[] = $menu;
		return $menus;
	}

}
