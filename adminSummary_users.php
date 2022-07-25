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

define('KT_SCRIPT_NAME', 'adminSummary_users.php');

global $iconStyle;

require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Users'))
	->pageHeader();

/**
 * Array of site administration menu items
 * $site_tools [array]
 */
$user_tools = array(
	 "admin_users.php"	=> array(
		KT_I18N::translate('Manage users'),
		KT_I18N::translate('List all users, with links to edit, delete, masquerade, and view details for each'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	),
	 "admin_users.php?action=edit"		=> array(
		KT_I18N::translate('Add a new user'),
		KT_I18N::translate('Direct link to manually add a new user'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	),
	 "admin_users_bulk.php"		=> array(
		KT_I18N::translate('Send broadcast messages'),
		KT_I18N::translate('Send an email message to various bulk groups of users, based on the length of their membership'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	),
	 "admin_users.php?action=cleanup"	=> array(
		KT_I18N::translate('Delete inactive users'),
		KT_I18N::translate('Remove users in bulk for a variety of optional reasons'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	),
);

echo pageStart('user_admin', $controller->getPageTitle()); ?>

	<div class="cell callout warning help_content">
		<?php echo KT_I18N::translate('
			Add, delete, edit and manage users
		'); ?>
	</div>
	<div class="grid-x grid-margin-x grid-margin-y small-up-1 medium-up-3 large-up-4">
		<?php foreach ($user_tools as $title => $file) { ?>
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
