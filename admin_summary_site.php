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

define('KT_SCRIPT_NAME', 'admin_summary_site.php');

global $iconStyle;
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Website'))
	->pageHeader();

/**
 * Array of site administration menu items
 * $site_tools [array]
 */
$site_tools = [
	 "admin_site_config.php"	=> [
		KT_I18N::translate('Configuration'),
		KT_I18N::translate('Global settings that apply to all family trees'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	],
	 "admin_site_logs.php"		=> [
		KT_I18N::translate('%s logs', KT_KIWITREES),
		KT_I18N::translate('A filterable log of site-wide activities'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	],
	 "admin_site_info.php"		=> [
		KT_I18N::translate('Server information'),
		KT_I18N::translate('Information about the configuration of your PHP and SQL installation'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	],
	 "admin_site_access.php"	=> [
		KT_I18N::translate('Access rules'),
		KT_I18N::translate('Restrict access to the site, using IP addresses and user-agent strings'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	],
	 "admin_site_clean.php"		=> [
		KT_I18N::translate('Data folder management'),
		KT_I18N::translate('Delete any files or folders no longer required from the \'data\' folder'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	],
	 "admin_site_use.php"		=> [
		KT_I18N::translate('Server usage'),
		KT_I18N::translate('A quick summary of the space currently being used on your web server'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	]
];
asort($site_tools);

echo pageStart('site_admin', $controller->getPageTitle()); ?>

	<div class="cell callout info-help summary">
		<?php echo KT_I18N::translate('
		Configuration, management, logs, and other activities related to the overall website.
		'); ?>
	</div>
	<div class="cell">
		<div class="grid-x grid-margin-x grid-margin-y">
			<?php foreach ($site_tools as $link => $file) {
				$title   = $file[0];
				$descr   = $file[1];
				$tooltip = $file[2];
				$user    = $file[3];

				echo AdminSummaryCard ($link, $title, $user, $tooltip, $descr);

			} ?>
		</div>
	</div>

<?php echo pageClose();
