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

define('KT_SCRIPT_NAME', 'adminSummary_media.php');

global $iconStyle;
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Media objects'))
	->pageHeader();

/**
 * Array of family tree administration menu items
 * $ft_tools [array]
 */
$media = array(
	"admin_media.php"			=> array(
		KT_I18N::translate('Manage media'),
		KT_I18N::translate('List, filter, and edit all media objects'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	),
	"admin_media_upload.php"	=> array(
		KT_I18N::translate('Upload media objects'),
		KT_I18N::translate('Upload small batches of media objects at one time (1 to 6)'),
		KT_I18N::translate('Administrator access only'),
		'alert'
	),
);

echo pageStart('media_admin', $controller->getPageTitle()); ?>

	<div class="cell callout warning help_content">
		<?php echo KT_I18N::translate('
		Add, delete, edit and manage site-wide media objects
		'); ?>
	</div>
	<div class="cell">
		<div class="grid-x grid-margin-x grid-margin-y">
			<?php foreach ($media as $title => $file) { ?>
				<div class="card cell">
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
			<?php } ?>
		</div>
	</div>

<?php echo pageClose();
