<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

class chart_compact_KT_Module extends KT_Module implements KT_Module_Chart {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Compact');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Compact chart” module */ KT_I18N::translate('An individual\'s compact chart');
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
		require_once KT_ROOT . 'library/Mobile-Detect/Mobile_Detect.php';
		$detect = new Mobile_Detect;
		if (!$detect->isMobile() ) {
			return true;
		} else {
			return false;
		}
	}

	// Implement KT_Module_Chart
	public function getChartMenus() {
		global $controller;

		$indi_xref	= $controller->getSignificantIndividual()->getXref();
		$menus		= array();
		$menu		= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $indi_xref . '&amp;ged=' . KT_GEDURL,
			'menu-chart-compact'
		);
		$menus[] = $menu;
		return $menus;

	}

	// Display chart
	public function show() {
		global $controller, $iconStyle;

		$controller = new KT_Controller_Compact();
		$controller
			->restrictAccess(KT_Module::isActiveChart(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
		    ->pageHeader()
		    ->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
		    ->addInlineJavascript('autocomplete();');

		$xref	= $controller->root->getXref();
		$person	= KT_Person::getInstance($xref);
		?>

		<!-- Start page layout  -->
		<?php echo pageStart('compact', $controller->getPageTitle()); ?>
			<form name="people" id="people" method="get" action="?">
				<input type="hidden" name="mod" value="<?php echo $this->getName(); ?>">
				<input type="hidden" name="mod_action" value="show">
				<div class="grid-x grid-margin-x">
					<div class="cell medium-6 large-3">
						<label class="h5" for="autocompleteInput"><?php echo KT_I18N::translate('Individual'); ?></label>
						<div class="input-group autocomplete_container">
							<input
								data-autocomplete-type="INDI"
								type="text"
								id="autocompleteInput"
								value="<?php echo strip_tags($person->getLifespanName()); ?>"
							>
							<span class="input-group-label">
								<button class="clearAutocomplete autocomplete_icon">
									<i class="<?php echo $iconStyle; ?> fa-times"></i>
								</button>
							</span>
						</div>
						<input type="hidden" name="rootid" id="selectedValue" value="<?php echo $controller->rootid; ?>">
					</div>
				</div>
				<button class="button" type="submit">
					<i class="<?php echo $iconStyle; ?> fa-eye"></i>
					<?php echo KT_I18N::translate('Show'); ?>
				</button>
			</form>
			<hr>

			<?php if ($controller->error_message) {
				echo '<div class="callout alert">', $controller->error_message, '</div>';
				exit;
			} ?>
			<div class="grid-x grid-margin-x">
				<div class="cell medium-2">

		<?php echo pageClose();

	}


}
