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

require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';
global $iconStyle;

$gedID  	= KT_Filter::post('gedID') ? KT_Filter::post('gedID') : '';
$action     = KT_Filter::post('action');

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle($this->getTitle())
	->pageHeader()
	->addExternalJavascript(KT_ICON_PICKER_JS)
	->addExternalJavascript(KT_CKEDITOR_CLASSIC)
	->addInlineJavascript('
		function disableSubMenu() {
			jQuery("#NEW_ADMIN_SUBMENU").prop( "disabled", true );
		}

		function enableSubMenu() {
			jQuery("#NEW_ADMIN_SUBMENU").prop( "disabled", false );
		}
	');

if ($action == 'update') {
	set_module_setting($this->getName(), 'ADMIN_LOCATION', KT_Filter::post('NEW_ADMIN_LOCATION'));
	set_module_setting($this->getName(), 'ADMIN_SUBMENU', KT_Filter::post('NEW_ADMIN_SUBMENU'));
	AddToLog($this->getName() . ' config updated', 'config');
}

$title1   = KT_I18N::translate('Main menu');
$title2   = KT_I18N::translate('Other menu');
$title3   = KT_I18N::translate('User menu');

$location = get_module_setting($this->getName(), 'ADMIN_LOCATION', '');
$selected = get_module_setting($this->getName(), 'ADMIN_SUBMENU', '');

echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart($this->getName(), $controller->getPageTitle(), '', '', ''); ?>
	<div class="cell">
		<div class="grid-x">
			<form class="cell" method="post" name="configform" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config">
				<input type="hidden" name="action" value="update">
				<div class="grid-x grid-margin-x grid-margin-y">
					<div class="cell callout info-help">
						<?php echo KT_I18N::translate('Select your preferred position for the Home page link to "Administration" from the options below'); ?>
					</div>
					<fieldset class="cell medium-4 fieldset">
						<legend class="h5"><?php echo $title1; ?></legend>
						<div class="grid-x grid-margin-x grid-margin-y">
							<div class="cell">
								<a class="thumbnail" href="#" data-open="adminImage1">
									<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Admin_locations_1.png" alt="<?php echo KT_I18N::translate('Main menu'); ?>">
								</a>
							</div>
							<label class="cell small-6 medium-3 medium-offset-3 text-right">
								<?php echo KT_I18N::translate('Select'); ?>
							</label>
							<div class="cell small-6 text-left">
								<div class="switch small">
									<input
										class="switch-input"
										id="adminSwitch1"
										type="radio"
										name="NEW_ADMIN_LOCATION"
										value="main"
										<?php echo $location == 'main' ? ' checked' : ''; ?>
										onclick = "enableSubMenu();"
									>
									<label class="switch-paddle" for="adminSwitch1">
										<span class="show-for-sr"><?php echo KT_I18N::translate('Administration link options'); ?></span>
										<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
										<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('No'); ?></span>
									</label>
								</div>
							</div>
						</div>
					</fieldset>

					<fieldset class="cell medium-4 fieldset">
						<legend class="h5"><?php echo $title2; ?></legend>
						<div class="grid-x grid-margin-x grid-margin-y">
							<div class="cell">
								<a class="thumbnail" href="#" data-open="adminImage2">
									<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Admin_locations_2.png" alt="<?php echo KT_I18N::translate('Other menu'); ?>">
								</a>
							</div>
							<label class="cell small-6 medium-3 medium-offset-3 text-right">
								<?php echo KT_I18N::translate('Select'); ?>
							</label>
							<div class="cell small-6 text-left">
								<div class="switch small">
									<input
										class="switch-input"
										id="adminSwitch2"
										type="radio"
										name="NEW_ADMIN_LOCATION"
										value="other"
										<?php echo $location == 'other' ? ' checked' : ''; ?>
										onclick = "enableSubMenu();"
									>
									<label class="switch-paddle" for="adminSwitch2">
										<span class="show-for-sr"><?php echo KT_I18N::translate('Administration link options'); ?></span>
										<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
										<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('No'); ?></span>
									</label>
								</div>
							</div>
						</div>
					</fieldset>

					<fieldset class="cell medium-4 fieldset">
						<legend class="h5"><?php echo $title3; ?></legend>
						<div class="grid-x grid-margin-x grid-margin-y">
							<div class="cell">
								<a class="thumbnail" href="#" data-open="adminImage3">
									<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Admin_locations_3.png" alt="<?php echo KT_I18N::translate('User menu'); ?>">
								</a>
							</div>
							<label class="cell small-6 medium-3 medium-offset-3 text-right">
								<?php echo KT_I18N::translate('Select'); ?>
							</label>
							<div class="cell small-6 text-left">
								<div class="switch small">
									<input
										class="switch-input"
										id="adminSwitch3"
										type="radio"
										name="NEW_ADMIN_LOCATION"
										value="user"
										<?php echo $location == 'user' ? ' checked' : ''; ?>
										onclick = "disableSubMenu();"
									>
									<label class="switch-paddle" for="adminSwitch3">
										<span class="show-for-sr"><?php echo KT_I18N::translate('Administration link options'); ?></span>
										<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
										<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('No'); ?></span>
									</label>
								</div>
							</div>
						</div>
					</fieldset>

					<div class="cell">
						<h4><?php echo KT_I18N::translate('Other options'); ?></h4>
						<div class="grid-x grid-margin-x grid-margin-y">
							<label class="cell medium-3">
								<?php echo KT_I18N::translate('Include sub-menu list'); ?>
							</label>
							<div class="cell medium-7">
								<?php echo simple_switch(
									'NEW_ADMIN_SUBMENU',
									1,
									$selected,
									$location == 'user' ? 'disabled' : '',
								); ?>
								<div class="cell callout info-help">
									<?php echo KT_I18N::translate('
										The submenu is a drop-down list of the main dashboard categories (see left).
										It facilitates direct access to those main administrative option groups.
										<strong>Note:</strong> The submenu option is not available if the menu location is set to "user".
									'); ?>
								</div>
							</div>

						</div>
					</div>

					<?php echo singleButton(); ?>

				</div>

			</form>
		</div>
	</div>

	<?php // hidden reveals - main image ?>
	<div class="reveal" id="adminImage1" data-reveal>
		<h6 class="text-center"><?php echo $title1; ?>
			<button class="close-button" aria-label="Dismiss image" type="button" data-close>
				<span aria-hidden="true">
					<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
				</span>
			</button>
		</h6>
		<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Admin_locations_1.png" alt="<?php echo KT_I18N::translate('Main menu'); ?>">
	</div>
	<div class="reveal" id="adminImage2" data-reveal>
		<h6 class="text-center"><?php echo $title2; ?>
			<button class="close-button" aria-label="Dismiss image" type="button" data-close>
				<span aria-hidden="true">
					<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
				</span>
			</button>
		</h6>
		<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Admin_locations_2.png" alt="<?php echo KT_I18N::translate('Other menu'); ?>">
	</div>
	<div class="reveal" id="adminImage3" data-reveal>
		<h6 class="text-center"><?php echo $title3; ?>
			<button class="close-button" aria-label="Dismiss image" type="button" data-close>
				<span aria-hidden="true">
					<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
				</span>
			</button>
		</h6>
		<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Admin_locations_3.png" alt="<?php echo KT_I18N::translate('User menu'); ?>">
	</div>

<?php echo pageClose();
