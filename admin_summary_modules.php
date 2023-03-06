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

define('KT_SCRIPT_NAME', 'admin_summary_modules.php');

global $iconStyle;
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Modules'))
	->pageHeader();

/**
 * Array of Module menu management items
 * $module_config [array]
 */
$module_config = array(
	"admin_modules.php"		=> array(
	   KT_I18N::translate('Module administration'),
	   KT_I18N::translate('A sortable list of all available modules.<br>Enable or disable them to suit your preferences.'),
	   KT_I18N::translate('Administrator access only'),
	   'alert',
	   ''
   ),
);

/**
 * Array of Module menu access position settings
 * $module_cats [array]
 */
$module_cat = array(
	"admin_module_menus.php"		=> array(
	   KT_I18N::translate('Top level menu items'),
	   '',
	   KT_I18N::translate('Administrator access only'),
	   'alert',
	   'Top menu.png'
   ),
   "admin_module_blocks.php"	=> array(
	  KT_I18N::translate('Home page blocks'),
	  '',
	  KT_I18N::translate('Administrator access only'),
	  'alert',
	  'Home blocks.png'
  ),
	"admin_module_footers.php"	=> array(
		KT_I18N::translate('Footer blocks'),
		'',
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Footer blocks.png'
	),
	 "admin_module_widgets.php"	=> array(
		KT_I18N::translate('Widget bar modules'),
		'',
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Widget blocks.png'
	),
	"admin_module_tabs_indi.php"	=> array(
		KT_I18N::translate('Tabs for individual page'),
		'',
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Indi tabs.png'
   ),
	"admin_module_sidebar.php"	=> array(
		KT_I18N::translate('Sidebar modules'),
		'',
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Sidebar modules.png'
   ),
   "admin_module_tabs_fam.php"	=> array(
	   KT_I18N::translate('Tabs for family page'),
	   '',
	   KT_I18N::translate('Administrator access only'),
	   'alert',
	   'Family tabs.png'
  ),
   "admin_module_charts.php"	=> array(
		KT_I18N::translate('Menu - Chart items'),
		'',
		KT_I18N::translate('Administrator access only'),
		'alert',
		'Chart menus.png'
  ),
	"admin_module_lists.php"	=> array(
	   KT_I18N::translate('Menu - List  items'),
	   '',
	   KT_I18N::translate('Administrator access only'),
	   'alert',
	   'List menus.png'
   ),
	"admin_module_reports.php"	=> array(
		KT_I18N::translate('Menu - Report items'),
		'',
		KT_I18N::translate('Administrator access only'),
		'alert',
 	   'Report menus.png'
  ),
);

echo pageStart('modules_admin', $controller->getPageTitle()); ?>

	<div class="cell callout info-help help_content">
		<?php echo KT_I18N::translate('
			Enable or disable modules, set access levels, and adjust their locations.
		'); ?>
	</div>
	<div class="cell">
		<div class="grid-x grid-margin-x grid-margin-y">
			<div class="cell">
				<h4><?php echo KT_I18N::translate('Enable & disable modules'); ?></h4>
			</div>
			<?php $x = 1;
			foreach ($module_config as $title => $file) { ?>
				<div class="card cell">
					<div class="card-divider">
						<a href="<?php echo $title; ?>">
							<?php echo $file[0]; ?>
						</a>
						<span class="<?php echo $file[3]; ?>" data-tooltip title="<?php echo $file[2]; ?>" data-position="top" data-alignment="right">
							<i class="<?php echo $iconStyle; ?> fa-user"></i>
						</span>
					</div>
					<div class="card-section">
						<?php if($title == 'admin_modules.php') {
							echo $file[1];
						} else { ?>
							<a class="thumbnail" href="#" data-open="moduleImage<?php echo $x; ?>">
								<img src="<?php echo KT_THEME_DIR . 'images/module-categories/' . $file[4]; ?>" alt="<?php echo $file[1]; ?>">
							</a>
						<?php } ?>
					</div>
				</div>
				<!-- hidden reveal - main image -->
				<div class="reveal" id="moduleImage<?php echo $x; ?>" data-reveal>
					<h6 class="text-center">
						<?php echo $file[0]; ?>
						<button class="close-button" aria-label="Dismiss image" type="button" data-close>
							<span aria-hidden="true">
								<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
							</span>
						</button>
					</h6>
					<img src="<?php echo KT_THEME_DIR . 'images/module-categories/' . $file[4]; ?>">
				</div>
				<?php $x ++;
			} ?>

			<hr class="cell">

			<div class="cell">
				<h4><?php echo KT_I18N::translate('Module settings'); ?></h4>
			</div>
			<?php $x = 1;
			foreach ($module_cat as $title => $file) { ?>
				<div class="card cell">
					<div class="card-divider">
						<a href="<?php echo $title; ?>">
							<?php echo $file[0]; ?>
						</a>
						<span class="<?php echo $file[3]; ?>" data-tooltip title="<?php echo $file[2]; ?>" data-position="top" data-alignment="right">
							<i class="<?php echo $iconStyle; ?> fa-user"></i>
						</span>
					</div>
					<div class="card-section">
						<?php if($title == 'admin_modules.php') {
							echo $file[1];
						} else { ?>
							<a class="thumbnail" href="#" data-open="moduleImage<?php echo $x; ?>">
								<img src="<?php echo KT_THEME_DIR . 'images/module-categories/' . $file[4]; ?>" alt="<?php echo $file[1]; ?>">
							</a>
						<?php } ?>
					</div>
				</div>
				<!-- hidden reveal - main image -->
				<div class="reveal" id="moduleImage<?php echo $x; ?>" data-reveal>
					<h6 class="text-center">
						<?php echo $file[0]; ?>
						<button class="close-button" aria-label="Dismiss image" type="button" data-close>
							<span aria-hidden="true">
								<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
							</span>
						</button>
					</h6>
					<img src="<?php echo KT_THEME_DIR . 'images/module-categories/' . $file[4]; ?>">
				</div>
				<?php $x ++;
			} ?>
		</div>
	</div>





<?php echo pageClose();
