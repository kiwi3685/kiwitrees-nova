<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net.
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
define('KT_SCRIPT_NAME', 'admin_summary_modules.php');

global $iconStyle;

require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Modules'))
	->pageHeader()
;

/**
 * Array of Module menu management items
 * $module_config [array].
 */
$module_config = [
	'admin_modules.php' => [
		KT_I18N::translate('Module administration'),
		KT_I18N::translate('A sortable list of all available modules.<br>Enable or disable them to suit your preferences.'),
		KT_I18N::translate('Administrator access only'),
		'alert',
		'',
	],
];

/**
 * Array of Module menu access position settings.
 *
 * @array $module_cats
 */
$module_cat = [
	'admin_module_menus.php' => [
		KT_I18N::translate('Top level menu items'),
		KT_I18N::translate('Set access and ordering for menu items at the very top of the page'),
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Top menu.png',
	],
	'admin_module_blocks.php' => [
		KT_I18N::translate('Home page blocks'),
		KT_I18N::translate('Set access and ordering for blocks on the site home page'),
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Home blocks.png',
	],
	'admin_module_footers.php' => [
		KT_I18N::translate('Footer blocks'),
		KT_I18N::translate('Set access and ordering blocks on the bottom or footer area of the site'),
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Footer blocks.png',
	],
	'admin_module_widgets.php' => [
		KT_I18N::translate('Widget bar modules'),
		KT_I18N::translate('Set access and ordering for blocks displayed on each users widget area'),
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Widget blocks.png',
	],
	'admin_module_tabs_indi.php' => [
		KT_I18N::translate('Tabs for individual page'),
		KT_I18N::translate('Set access and ordering for tabs used on the individual page'),
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Indi tabs.png',
	],
	'admin_module_sidebar.php' => [
		KT_I18N::translate('Sidebar modules'),
		KT_I18N::translate('Set access and ordering for items in the right-hand sidebar of individual pages'),
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Sidebar modules.png',
	],
	'admin_module_tabs_fam.php' => [
		KT_I18N::translate('Tabs for family page'),
		KT_I18N::translate('Set access and ordering for tabs used on the family page'),
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Family tabs.png',
	],
	'admin_module_charts.php' => [
		KT_I18N::translate('Menu - Chart items'),
		KT_I18N::translate('Set access and ordering for chart sub-menu items on the main menu'),
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Chart menus.png',
	],
	'admin_module_lists.php' => [
		KT_I18N::translate('Menu - List  items'),
		KT_I18N::translate('Set access and ordering for lists sub-menu items on the main menu'),
		KT_I18N::translate('Administrator access only'),
		'alert',
		'List menus.png',
	],
	'admin_module_reports.php' => [
		KT_I18N::translate('Menu - Report items'),
		KT_I18N::translate('Set access and ordering for reports sub-menu items on the main menu'),
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Report menus.png',
	],
];
asort($module_cat);

echo pageStart('modules_admin', $controller->getPageTitle()); ?>

	<div class="cell callout info-help summary">
		<?php echo KT_I18N::translate('
			Enable or disable modules, set access levels, and adjust their locations.
		'); ?>
	</div>
	<div class="cell">
		<div class="grid-x grid-margin-x grid-margin-y">
			<?php 
			$x = 1;

			foreach ($module_config as $link => $file) {
				$title   = $file[0];
				$descr   = $file[1];
				$tooltip = $file[2];
				$user    = $file[3];

				echo AdminSummaryCard ($link, $title, $user, $tooltip, $descr);

				$x ++;
			} ?>

			<hr class="cell">

			<div class="cell">
				<h4><?php echo KT_I18N::translate('Module settings'); ?></h4>
			</div>
			<?php $x = 1;
			foreach ($module_cat as $link => $file) {
				$title   = $file[0];
				$descr   = $file[1];
				$tooltip = $file[2];
				$user    = $file[3];
				$image   = '<a class="thumbnail" href="#" data-open="moduleImage' . $x . '">
								<img src ="' . KT_THEME_DIR . 'images/module-categories/' . $file[4] . '" alt="' . $file[1] . '">
							</a>';

				echo AdminSummaryCard ($link, $title, $user, $tooltip, $descr, $image, $x);

				$x ++;
			} ?>

		</div>
	</div>

<?php echo pageClose();
