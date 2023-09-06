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
	->addInlineJavascript('iconPicker("#floatIcon")')
;

if ($action == 'update') {
	set_module_setting($this->getName(), 'CONTACT_MAIN', KT_Filter::post('NEW_CONTACT_MAIN'));
	set_module_setting($this->getName(), 'CONTACT_OTHER', KT_Filter::post('NEW_CONTACT_OTHER'));
	set_module_setting($this->getName(), 'CONTACT_FLOAT', KT_Filter::post('NEW_CONTACT_FLOAT'));
	set_module_setting($this->getName(), 'CONTACT_FOOTER', KT_Filter::post('NEW_CONTACT_FOOTER'));
	set_module_setting($this->getName(), 'CONTACT_WIDGET', KT_Filter::post('NEW_CONTACT_WIDGET'));
	set_module_setting($this->getName(), 'FLOAT_POSITION', KT_Filter::post('NEW_FLOAT_POSITION'));
	set_module_setting($this->getName(), 'FLOAT_ICON',  str_replace($iconStyle . ' ', '', KT_Filter::post('NEW_FLOAT_ICON')), '');

	AddToLog($this->getName() . ' config updated', 'config');
}

$title1     = KT_I18N::translate('Main menu');
$title2     = KT_I18N::translate('Other menu');
$title3     = KT_I18N::translate('Floating icon');
$title4     = KT_I18N::translate('Footer block');
$title5     = KT_I18N::translate('User widget');

$menuMain   = get_module_setting($this->getName(), 'CONTACT_MAIN', '');
$menuOther  = get_module_setting($this->getName(), 'CONTACT_OTHER', '');
$float      = get_module_setting($this->getName(), 'CONTACT_FLOAT', '');
$footer     = get_module_setting($this->getName(), 'CONTACT_FOOTER', '');
$widget     = get_module_setting($this->getName(), 'CONTACT_WIDGET', '');
$position   = get_module_setting($this->getName(), 'FLOAT_POSITION', 'bottom_right');
$float_icon = get_module_setting($this->getName(), 'FLOAT_ICON', 'fa-comment-dots');

$positionOptions = [
	'top_left'     => KT_I18N::translate('Top Left'),
	'top_right'    => KT_I18N::translate('Top Right'),
	'bottom_left'  => KT_I18N::translate('Bottom Left'),
	'bottom_right' => KT_I18N::translate('Bottom Right')
];

echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart($this->getName(), $controller->getPageTitle(), '', '', ''); ?>
	<div class="cell">
		<div class="grid-x">
			<form class="cell" method="post" name="configform" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config">
				<input type="hidden" name="action" value="update">
				<div class="grid-x grid-margin-x grid-margin-y">
					<div class="cell callout info-help">
						<?php echo KT_I18N::translate('Enable your preferred position or positions from the options below. Then select any of the other options to enhance the links where appriopriate.'); ?>
					</div>
					<fieldset class="cell medium-4 fieldset">
						<legend class="h5"><?php echo $title1; ?></legend>
						<div class="grid-x grid-margin-x grid-margin-y">
							<div class="cell">
								<a class="thumbnail" href="#" data-open="contactImage1">
									<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Contact_locations_1.png" alt="<?php echo KT_I18N::translate('Main menu'); ?>">
								</a>
							</div>
							<label class="cell small-6 medium-3 medium-offset-3 text-right">
								<?php echo KT_I18N::translate('Select'); ?>
							</label>
							<div class="cell small-6 text-left">
								<?php echo simple_switch(
									'NEW_CONTACT_MAIN',
									'main',
									$menuMain,
								 ) ?>
							</div>
						</div>
					</fieldset>

					<fieldset class="cell medium-4 fieldset">
						<legend class="h5"><?php echo $title2; ?></legend>
						<div class="grid-x grid-margin-x grid-margin-y">
							<div class="cell">
								<a class="thumbnail" href="#" data-open="contactImage2">
									<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Contact_locations_2.png" alt="<?php echo KT_I18N::translate('Other menu'); ?>">
								</a>
							</div>
							<label class="cell small-6 medium-3 medium-offset-3 text-right">
								<?php echo KT_I18N::translate('Select'); ?>
							</label>
							<div class="cell small-6 text-left">
								<?php echo simple_switch(
									'NEW_CONTACT_OTHER',
									'other',
									$menuOther,
							 	) ?>
							</div>
						</div>
					</fieldset>

					<fieldset class="cell medium-4 fieldset">
						<legend class="h5"><?php echo $title3; ?></legend>
						<div class="grid-x grid-margin-x grid-margin-y">
							<div class="cell">
								<a class="thumbnail" href="#" data-open="contactImage3">
									<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Contact_locations_3.png" alt="<?php echo KT_I18N::translate('Floating link'); ?>">
								</a>
							</div>
							<label class="cell small-6 medium-3 medium-offset-3 text-right">
								<?php echo KT_I18N::translate('Select'); ?>
							</label>
							<div class="cell small-6 text-left">
								<?php echo simple_switch(
									'NEW_CONTACT_FLOAT',
									'float',
									$float,
							 	) ?>
							</div>
						</div>
					</fieldset>

					<fieldset class="cell medium-4 fieldset">
						<legend class="h5"><?php echo $title4; ?></legend>
						<div class="grid-x grid-margin-x grid-margin-y">
							<div class="cell">
								<a class="thumbnail" href="#" data-open="contactImage4">
									<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Contact_locations_4.png" alt="<?php echo KT_I18N::translate('Footer block'); ?>">
								</a>
							</div>
							<label class="cell small-6 medium-3 medium-offset-3 text-right">
								<?php echo KT_I18N::translate('Select'); ?>
							</label>
							<div class="cell small-6 text-left">
								<?php echo simple_switch(
									'NEW_CONTACT_FOOTER',
									'footer',
									$footer,
							 	) ?>
							</div>
						</div>
					</fieldset>

					<fieldset class="cell medium-4 fieldset">
						<legend class="h5"><?php echo $title5; ?></legend>
						<div class="grid-x grid-margin-x grid-margin-y">
							<div class="cell">
								<a class="thumbnail" href="#" data-open="contactImage5">
									<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Contact_locations_5.png" alt="<?php echo KT_I18N::translate('User widget'); ?>">
								</a>
							</div>
							<label class="cell small-6 medium-3 medium-offset-3 text-right">
								<?php echo KT_I18N::translate('Select'); ?>
							</label>
							<div class="cell small-6 text-left">
								<?php echo simple_switch(
									'NEW_CONTACT_WIDGET',
									'widget',
									$widget,
							 	) ?>
							</div>
						</div>
					</fieldset>

					<div class="cell" id="other_options">
						<h4><?php echo KT_I18N::translate('Other options'); ?></h4>
						<div class="grid-x grid-margin-x grid-margin-y">
							<label class="cell medium-2">
								<?php echo KT_I18N::translate('Floating link icon'); ?>
							</label>
							<div class="cell medium-4 input-group iconpicker-container">
								<input id="floatIcon" name="NEW_FLOAT_ICON" data-placement="bottomRight" class="form-control icp icp-auto iconpicker-input iconpicker-element" value="<?php echo $float_icon; ?>" type="text">
								<span class="input-group-label"><i class="<?php echo $iconStyle . ' ' . $float_icon; ?>"></i></span>
							</div>
							<div class="cell callout info-help medium-6">
								<?php echo KT_I18N::translate('Click in the input field to see a list of icons and click on the one to use. Although displayed in black here, they will be colored to match the theme when displayed on the front pages.'); ?>
							</div>
							<label class="cell medium-2">
								<?php echo KT_I18N::translate('Location options for the floating contact link'); ?>
							</label>
							<div class="cell medium-10 float_positions">
								<?php echo verticalRadioSwitch (
								'NEW_FLOAT_POSITION',
								$positionOptions,
								$position,
								'',
								'Yes',
								'No',
								'small'
							); ?>
							</div>
						</div>
					</div>

					<?php echo singleButton(); ?>

				</div>
			</form>
		</div>
	</div>

	<?php // hidden reveals - main image ?>
	<?php for ($x = 1; $x <= 5; ++$x) { ?>
		<div class="reveal" id="contactImage<?php echo $x; ?>" data-reveal>
			<h6 class="text-center">
				<?php echo ${'title' . $x}; ?>
				<button class="close-button" aria-label="Dismiss image" type="button" data-close>
					<span aria-hidden="true">
						<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
					</span>
				</button>
			</h6>
			<img src="<?php echo KT_THEME_DIR; ?>images/module-categories/Contact_locations_<?php echo $x; ?>.png" alt="<?php echo ${'title' . $x}; ?>">
		</div>
	<?php } ?>

<?php echo pageClose();
