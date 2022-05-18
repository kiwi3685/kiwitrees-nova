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
		if ($detect->isMobile() ) {
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
		global $SHOW_HIGHLIGHT_IMAGES, $controller, $iconStyle;

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
					<label class="h5 cell medium-6 large-4" for="autocompleteInput"><?php echo KT_I18N::translate('Individual'); ?>
						<div class="input-group autocomplete_container">
							<input
								data-autocomplete-type="INDI"
								type="text"
								id="autocompleteInput"
								value="<?php echo strip_tags($person->getLifespanName()); ?>"
							>
							<span class="input-group-label">
								<button class="clearAutocomplete autocomplete_icon">
									<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
								</button>
							</span>
						</div>
						<input type="hidden" name="rootid" id="selectedValue" value="<?php echo $controller->rootid; ?>">
					</label>
					<?php if ($SHOW_HIGHLIGHT_IMAGES) { ?>
						<label class="h5 cell medium-6 large-4" for="show_thumbs"><?php echo KT_I18N::translate('Show photo in people boxes'); ?>
							<div class="switch">
							  <input class="switch-input" id="show_thumbs" type="checkbox" name="show_thumbs" <?php echo $controller->show_thumbs ? 'checked="checked"' : ''; ?>>
							  <label class="switch-paddle" for="show_thumbs">
								  <span class="show-for-sr"><?php echo KT_I18N::translate('Show thumbs'); ?></span>
							      <span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
							      <span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('No'); ?></span>
							  </label>
							</div>
						</label>
					<?php } ?>
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

			<div class="grid-x grid-padding-x grid-padding-y">
				<div class="cell">
					<table class="unstriped">
					    <tr>
					        <?php echo $controller->sosa_person(16); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_person(18); ?>
					        <td></td>
					        <?php echo $controller->sosa_person(24); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_person(26); ?>
					    </tr>
					    <tr>
					        <?php echo $controller->sosa_arrow(16, 'up'); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_arrow(18, 'up'); ?>
					        <td></td>
					        <?php echo $controller->sosa_arrow(24, 'up'); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_arrow(26, 'up'); ?>
					    </tr>
					    <tr>
					        <?php echo $controller->sosa_person(8); ?>
					        <?php echo $controller->sosa_arrow(8, 'left'); ?>
					        <?php echo $controller->sosa_person(4); ?>
					        <?php echo $controller->sosa_arrow(9, 'right'); ?>
					        <?php echo $controller->sosa_person(9); ?>
					        <td></td>
					        <?php echo $controller->sosa_person(12); ?>
					        <?php echo $controller->sosa_arrow(12, 'left'); ?>
					        <?php echo $controller->sosa_person(6); ?>
					        <?php echo $controller->sosa_arrow(13, 'right'); ?>
					        <?php echo $controller->sosa_person(13); ?>
					    </tr>
					    <tr>
					        <?php echo $controller->sosa_arrow(17, 'down'); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_arrow(19, 'down'); ?>
					        <td></td>
					        <?php echo $controller->sosa_arrow(25, 'down'); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_arrow(27, 'down'); ?>
					    </tr>
					    <tr>
					        <?php echo $controller->sosa_person(17); ?>
					        <td></td>
					        <?php echo $controller->sosa_arrow(4, 'up'); ?>
					        <td></td>
					        <?php echo $controller->sosa_person(19); ?>
					        <td></td>
					        <?php echo $controller->sosa_person(25); ?>
					        <td></td>
					        <?php echo $controller->sosa_arrow(6, 'up'); ?>
					        <td></td>
					        <?php echo $controller->sosa_person(27); ?>
					    </tr>
						<tr>
							<td></td>
							<td></td>
							<?php echo $controller->sosa_person(2); ?>
							<td></td>
							<td colspan="3">
								<table class="sosa123 unstriped">
									<tr>
										<?php echo $controller->sosa_arrow(2, 'left'); ?>
										<?php echo $controller->sosa_person(1); ?>
										<?php echo $controller->sosa_arrow(3, 'right'); ?>
									</tr>
								</table>
							</td>
							<td></td>
							<?php echo $controller->sosa_person(3); ?>
							<td></td>
							<td></td>
						</tr>
					    <tr>
					        <?php echo $controller->sosa_person(20); ?>
					        <td></td>
					        <?php echo $controller->sosa_arrow(5, 'down'); ?>
					        <td></td>
					        <?php echo $controller->sosa_person(22); ?>
					        <td></td>
					        <?php echo $controller->sosa_person(28); ?>
					        <td></td>
					        <?php echo $controller->sosa_arrow(7, 'down'); ?>
					        <td></td>
					        <?php echo $controller->sosa_person(30); ?>
					    </tr>
					    <tr>
					        <?php echo $controller->sosa_arrow(20, 'up'); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_arrow(22, 'up'); ?>
					        <td></td>
					        <?php echo $controller->sosa_arrow(28, 'up'); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_arrow(30, 'up'); ?>
					    </tr>
					    <tr>
					        <?php echo $controller->sosa_person(10); ?>
					        <?php echo $controller->sosa_arrow(10, 'left'); ?>
					        <?php echo $controller->sosa_person(5); ?>
					        <?php echo $controller->sosa_arrow(11, 'right'); ?>
					        <?php echo $controller->sosa_person(11); ?>
					        <td></td>
					        <?php echo $controller->sosa_person(14); ?>
					        <?php echo $controller->sosa_arrow(14, 'left'); ?>
					        <?php echo $controller->sosa_person(7); ?>
					        <?php echo $controller->sosa_arrow(15, 'right'); ?>
					        <?php echo $controller->sosa_person(15); ?>
					    </tr>
					    <tr>
					        <?php echo $controller->sosa_arrow(21, 'down'); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_arrow(23, 'down'); ?>
					        <td></td>
					        <?php echo $controller->sosa_arrow(29, 'down'); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_arrow(31, 'down'); ?>
					    </tr>
					    <tr>
					        <?php echo $controller->sosa_person(21); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_person(23); ?>
					        <td></td>
					        <?php echo $controller->sosa_person(29); ?>
					        <td></td>
					        <td></td>
					        <td></td>
					        <?php echo $controller->sosa_person(31); ?>
					    </tr>
					</table>
				</div>
			</div>

		<?php echo pageClose();

	}


}
