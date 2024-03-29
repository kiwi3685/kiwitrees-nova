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

class chart_descendancy_KT_Module extends KT_Module implements KT_Module_Chart {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Descendancy');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Descendancy chart” module */ KT_I18N::translate('An individual\'s descendancy chart');
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

	// Implement KT_Module_Chart
	public function getChartMobile() {
		// exclude this module from mobile displays
		return false;
	}

	// Implement KT_Module_Chart
	public function getChartMenus() {
		global $controller;

		$indi_xref	= $controller->getSignificantIndividual()->getXref();
		$menus		= array();
		$menu		= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $indi_xref . '&amp;ged=' . KT_GEDURL,
			'menu-chart-descendancy'
		);
		$menus[] = $menu;

		return $menus;
	}

	// Display chart
	public function show() {
		global $SHOW_HIGHLIGHT_IMAGES, $controller, $iconStyle;

		$controller = new KT_Controller_Descendancy();
		$controller
			->restrictAccess(KT_Module::isActiveChart(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
		    ->pageHeader()
		    ->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
		    ->addInlineJavascript('autocomplete();');

		$xref	= $controller->root->getXref();
		$person	= KT_Person::getInstance($xref);
		?>

		<!-- Start page layout  -->
		<?php echo pageStart('descendancy', $controller->getPageTitle()); ?>
			<form name="people" id="people" method="get" action="?">
				<input type="hidden" name="mod" value="<?php echo $this->getName(); ?>">
				<input type="hidden" name="mod_action" value="show">
				<div class="grid-x grid-margin-x">
					<label class="cell medium-6 large-4" for="autocompleteInput"><?php echo KT_I18N::translate('Individual'); ?>
						<?php echo autocompleteHtml(
							'descendancy', // id
							'INDI', // TYPE
							'', // autocomplete-ged
							strip_tags($person->getLifespanName()), // input value
							'', // placeholder
							'rootid', // hidden input name
							$controller->rootid // hidden input value
						); ?>
					</label>
					<label class="cell medium-6 large-4" for="generations"><?php echo KT_I18N::translate('Generations'); ?>
						<div class="grid-x grid-padding-x">
							<div class="cell small-9">
							  <div class="slider" data-slider data-start="2" data-step="1" data-end="<?php echo $MAX_PEDIGREE_GENERATIONS; ?>" data-initial-start="<?php echo $controller->generations; ?>">
								<span class="slider-handle"  data-slider-handle role="slider" tabindex="1" aria-controls="generations"></span>
								<span class="slider-fill" data-slider-fill></span>
							  </div>
							</div>
							<div class="cell small-3">
							  <input type="number" id="generations" name="generations">
							</div>
						</div>
					</label>
					<div class="cell medium-6 large-4">
						<div class="grid-x grid-padding-x">
							<label class="cell small-8 medium-4 large-3"><?php echo KT_I18N::translate('Chart'); ?>
								<div class="switch">
									<input class="switch-input" type="radio" id="list" name="chart_style" value="0" onclick="statusDisable('show_cousins');" <?php echo $controller->chart_style == 0 ? 'checked' : ''; ?>>
									<label class="switch-paddle" for="list">
										<span class="show-for-sr"><?php echo KT_I18N::translate('List'); ?></span>
										<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('on'); ?></span>
										<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('off'); ?></span>
									</label>
								</div>
							</label>
							<label class="cell small-8 medium-4 large-5"><?php echo KT_I18N::translate('Individual list'); ?>
								<div class="switch">
									<input class="switch-input" type="radio" id="individual" name="chart_style" value="1" onclick="statusDisable('show_cousins');" <?php echo $controller->chart_style == 1 ? 'checked' : ''; ?>>
									<label class="switch-paddle" for="individual">
										<span class="show-for-sr"><?php echo KT_I18N::translate('Individual'); ?></span>
										<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('on'); ?></span>
										<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('off'); ?></span>
									</label>
								</div>
							</label>
							<label class="cell small-8 medium-4 large-4"><?php echo KT_I18N::translate('Family list'); ?>
								<div class="switch">
									<input class="switch-input" type="radio" id="family" name="chart_style" value="2" onclick="statusDisable('show_cousins');" <?php echo $controller->chart_style == 2 ? 'checked' : ''; ?>>
									<label class="switch-paddle" for="family">
										<span class="show-for-sr"><?php echo KT_I18N::translate('Family'); ?></span>
										<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('on'); ?></span>
										<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('off'); ?></span>
									</label>
								</div>
							</label>
						</div>
					</div>
				</div>
				<button class="button" type="submit">
					<i class="<?php echo $iconStyle; ?> fa-eye"></i>
					<?php echo KT_I18N::translate('Show'); ?>
				</button>
			</form>
			<hr>
			<?php

			if ($controller->error_message) {
				echo '<div class="callout alert">', $controller->error_message, '</div>';
				exit;
			}

			switch ($controller->chart_style) {
				case 0:
					// List
					?>
					<div class="cell chart">
						<ul>
							<?php echo $controller->print_child_descendancy($controller->root, $controller->generations); ?>
						</ul>
					</div>
					<?php
				break;
				case 1:
					// Individual list
					require_once KT_ROOT . 'includes/functions/functions_print_lists.php';
					$descendants = $this->indi_desc($controller->root, $controller->generations, array()); ?>
					<div class="cell list">
						<?php echo format_indi_table($descendants, KT_I18N::translate('Descendants of %s', $controller->name)); ?>
					</div>
					<?php
				break;
				case 2:
					// Family list
					require_once KT_ROOT . 'includes/functions/functions_print_lists.php';
					$descendants = $this->fam_desc($controller->root, $controller->generations, array()); ?>
					<div class="cell list">
						<?php echo format_fam_table($descendants, KT_I18N::translate('Descendants of %s', $controller->name)); ?>
					</div>
					<?php
				break;
			} ?>
		<?php echo pageClose();
	}

	function indi_desc($person, $n, $array) {
		if ($n < 1) {
			return $array;
		}

		$array[$person->getXref()]=$person;

		foreach ($person->getSpouseFamilies() as $family) {
			$spouse=$family->getSpouse($person);
			if (isset($spouse)) {
				$array[$spouse->getXref()] = $spouse;
			}

			foreach ($family->getChildren() as $child) {
				$array = $this->indi_desc($child, $n-1, $array);
			}
		}

		return $array;
	}

	function fam_desc($person, $n, $array) {
		if ($n < 1) {
			return $array;
		}

		foreach ($person->getSpouseFamilies() as $family) {
			$array[$family->getXref()] = $family;
			foreach ($family->getChildren() as $child) {
				$array = $this->fam_desc($child, $n-1, $array);
			}
		}

		return $array;
	}

}
