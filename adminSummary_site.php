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

define('KT_SCRIPT_NAME', 'adminSummary_site.php');

global $iconStyle;

require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Website'))
	->pageHeader();

/**
 * Array of site administration menu items
 * $site_tools [array]
 */
$site_tools = array(
	 "admin_site_config.php"	=> array(
		KT_I18N::translate('Configuration'),
		KT_I18N::translate('Global settings that apply to all family trees'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	),
	 "admin_site_logs.php"		=> array(
		KT_I18N::translate('%s logs', KT_KIWITREES),
		KT_I18N::translate('A filterable log of site-wide activities'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	),
	 "admin_site_info.php"		=> array(
		KT_I18N::translate('Server information'),
		KT_I18N::translate('Information about the configuration of your PHP and SQL installation'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	),
	 "admin_site_access.php"	=> array(
		KT_I18N::translate('Access rules'),
		KT_I18N::translate('Restrict access to the site, using IP addresses and user-agent strings'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	),
	 "admin_site_clean.php"		=> array(
		KT_I18N::translate('Data folder management'),
		KT_I18N::translate('Restrict access to the site, using IP addresses and user-agent strings'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	),
	 "admin_site_use.php"		=> array(
		KT_I18N::translate('Server usage'),
		KT_I18N::translate('Restrict access to the site, using IP addresses and user-agent strings'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	)
);
//var_dump($site_tools);
asort($site_tools);

echo pageStart('site_access', $controller->getPageTitle()); ?>

	<div class="cell callout warning help_content">
		<?php echo KT_I18N::translate('
			Configuration, management, logs, and other activities related to the overall website.
		'); ?>
	</div>
	<div class="grid-x grid-margin-x grid-margin-y small-up-1 medium-up-3 large-up-4">
		<?php foreach ($site_tools as $title => $file) { ?>
			<div class="cell">
				<div class="card">
					<div class="card-divider">
						<a href="<?php echo $title; ?>">
							<?php echo $file[0]; ?>
						</a>
						<span class="<?php echo $file[3]; ?>" data-tooltip title="<?php echo $file[2]; ?>" data-position="top" data-alignment="right"><i class="<?php echo $iconStyle; ?> fa-user"></i>
					</div>
					<div class="card-section">
						<?php echo $file[1]; ?>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>

<?php pageClose();
