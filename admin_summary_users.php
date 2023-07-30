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

define('KT_SCRIPT_NAME', 'admin_summary_users.php');

global $iconStyle;
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('User management'))
	->pageHeader();

/**
 * Array of site administration menu items
 * $site_tools [array]
 */
$user_tools = [
	 "admin_users.php"	=> [
		KT_I18N::translate('Manage users'),
		KT_I18N::translate('List all users, with links to edit, delete, masquerade, and view details for each'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	],
	"admin_users_settings.php"	=> [
		KT_I18N::translate('List user settings per tree'),
		KT_I18N::translate('List  users by tree, showing their settings for that tree'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	],

	 "admin_users.php?action=edit"		=> [
		KT_I18N::translate('Add a new user'),
		KT_I18N::translate('Direct link to manually add a new user'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	],
	 "admin_users.php?action=messaging"		=> [
		KT_I18N::translate('Broadcast messages'),
		KT_I18N::translate('Send an email message to various bulk groups of users, based on the length of their membership'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	],
	 "admin_users.php?action=cleanup"	=> [
		KT_I18N::translate('Delete inactive users'),
		KT_I18N::translate('Remove users in bulk for a variety of optional reasons'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	],
];

echo pageStart('user_admin', $controller->getPageTitle()); ?>

	<div class="cell callout info-help summary">
		<?php echo KT_I18N::translate('
			Add, delete, edit and manage users
		'); ?>
	</div>
	<div class="cell">
		<div class="grid-x grid-margin-x grid-margin-y">
			<?php foreach ($user_tools as $link => $file) {
				$title   = $file[0];
				$descr   = $file[1];
				$tooltip = $file[2];
				$user    = $file[3];

				echo AdminSummaryCard ($link, $title, $user, $tooltip, $descr);

			} ?>
		</div>
	</div>

<?php echo pageClose();
