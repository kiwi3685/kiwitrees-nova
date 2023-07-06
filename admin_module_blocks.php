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

define('KT_SCRIPT_NAME', 'admin_module_blocks.php');
require 'includes/session.php';
include KT_THEME_URL . 'templates/adminModuleTemplate.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Home page blocks administration'))
	->pageHeader();

$modules    = KT_Module::getActiveBlocks(KT_GED_ID, KT_PRIV_HIDE);
$component  = 'block';
$infoHelp   = KT_I18N::translate('
		To change the order of these blocks go to the Home page,
		and select "Change the Home page blocks" from the options
		under your user name at the top of the page.
	') . '
	<br>' .
	KT_I18N::translate('
		The "Access level" setting "Hide from everyone" means exactly that,
		including Administrators.
	');
$pageTitle  = $controller->getPageTitle();
$col1Header = KT_I18N::translate('Block');

echo adminModules($modules, $component, $infoHelp, $pageTitle, $col1Header);
