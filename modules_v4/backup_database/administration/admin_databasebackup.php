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

 require KT_ROOT . 'includes/functions/functions_edit.php';
 include KT_THEME_URL . 'templates/adminData.php';

 global $iconStyle;

 $action		= KT_Filter::post("action");

 $controller	= new KT_Controller_Page();
 $controller
	 ->requireAdminLogin()
	 ->setPageTitle(KT_I18N::translate('Backup database'))
	 ->pageHeader();

echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart('db_backup', $controller->getPageTitle()); ?>

	<div class="cell" id="database_backup">
		<iframe src="<?php echo KT_MODULES_DIR . $this->getName() . '/index.php" width="100%" height="700"'; ?>">
			<p>
				<?php echo KT_I18N::translate('Sorry, your browser does not support iframes'); ?>
			</p>
		</iframe>
	</div>

<?php pageClose();
