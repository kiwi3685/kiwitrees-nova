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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

/**
 * Array of site administration menu items
 * $site_tools [array]
 */

$site_tools = [
	'adminSummary_site.php'=>'',
	'admin_site_config.php'=>'',
	'admin_site_logs.php'=>'',
	'admin_site_info.php'=>'',
	'admin_site_access.php'=>'',
	'admin_site_clean.php'=>'',
	'admin_site_use.php'=>'',
];

/**
 * Array of Family tree tool menu items
 * $trees [array]
 */
$trees = [
	'adminSummary_trees.php'			=> KT_I18N::translate('Family trees'),
	'admin_trees_manage.php'			=> KT_I18N::translate('Manage all family trees'),
	'admin_trees_config.php'			=> KT_I18N::translate('Configure each family tree'),
	'admin_trees_check.php'				=> KT_I18N::translate('Check for GEDCOM errors'),
	'admin_trees_change.php'			=> KT_I18N::translate('Changes log'),
	'admin_trees_addunlinked.php'		=> KT_I18N::translate('Add unlinked records'),
	'admin_trees_places.php'			=> KT_I18N::translate('Place name editing'),
	'admin_trees_merge.php'				=> KT_I18N::translate('Merge records'),
	'admin_trees_renumber.php'			=> KT_I18N::translate('Renumber family tree'),
	'admin_trees_append.php'			=> KT_I18N::translate('Append family tree'),
	'admin_trees_duplicates.php'		=> KT_I18N::translate('Find duplicate individuals'),
	'admin_trees_findunlinked.php'		=> KT_I18N::translate('Find unlinked records'),
	'admin_trees_sanity.php'			=> KT_I18N::translate('Sanity check'),
	'admin_trees_source.php'			=> KT_I18N::translate('Sources - review'),
	'admin_trees_sourcecite.php'		=> KT_I18N::translate('Sources - review citations'),
	'admin_trees_missing.php'			=> KT_I18N::translate('Missing fact or event details'),
];

/**
 * Array of site administration menu items
 * $site_tools [array]
 */
$users = [
	'adminSummary_users.php'			=> KT_I18N::translate('User management'),
	'admin_users.php'					=> KT_I18N::translate('Manage users'),
	'admin_users.php?action=edit'		=> KT_I18N::translate('Add a new user'),
	'admin_users.php?action=messaging'	=> KT_I18N::translate('Broadcast messages'),
	'admin_users.php?action=cleanup'	=> KT_I18N::translate('Delete inactive users'),
];

/**
 * Array of family tree administration menu items
 * $ft_tools [array]
 */
$media = [
	'adminSummary_media.php'=>'',
	'admin_media.php'=>'',
	'admin_media_upload.php'=>'',
];

/**
 * Array of Module menu items
 * $module_cats [array]
 */
$module_config = [
	'adminSummary_modules.php'=>'',
	'admin_module_menus.php'=>'',
	'admin_module_tabs_indi.php'=>'',
	'admin_module_blocks.php'=>'',
	'admin_module_widgets.php'=>'',
	'admin_module_sidebar.php'=>'',
	'admin_module_reports.php'=>'',
	'admin_module_charts.php'=>'',
	'admin_module_lists.php'=>'',
	'admin_module_footers.php'=>'',
	'admin_module_tabs_fam.php'=>'',
];

/**
 * Array of site administration menu items
 * $custom [array]
 */
 $custom = [
	'adminSummary_custom.php'=>'',
	'admin_custom_lang.php'=>'',
	'admin_custom_theme.php'=>'',
 ];

 /**
  * Array of site administration menu items
  * $tools [array]
  */
$tools = ['adminSummary_tools.php'];
foreach (KT_Module::getActiveModules(true) as $tool) {
	if ($tool instanceof KT_Module_Config && $tool->getName() !== 'custom_js') {
		$tools[] = $tool->getName();
	}
}

$adminPagesList = array_merge(
	$site_tools,
	$trees,
	$users,
	$media,
	$module_config,
	$custom,
	$tools,
);
asort($adminPagesList);

/*
$pagesList = array(
	 'admin_users.php'					=> KT_I18N::translate('Manage users'),
	 'admin_users.php?action=edit'		=> KT_I18N::translate('Add a new user'),
	 'admin_users.php?action=messaging'	=> KT_I18N::translate('Broadcast messages'),
	 'admin_users.php?action=cleanup'	=> KT_I18N::translate('Delete inactive users'),

	 'admin_trees_manage.php'			=> KT_I18N::translate('Manage all family trees'),
	 'admin_trees_config.php'			=> KT_I18N::translate('Configure each family tree'),
	 'admin_trees_check.php'			=> KT_I18N::translate('Check for GEDCOM errors'),
	 'admin_trees_change.php'			=> KT_I18N::translate('Changes log'),
	 'admin_trees_addunlinked.php'		=> KT_I18N::translate('Add unlinked records'),
	 'admin_trees_places.php'			=> KT_I18N::translate('Place name editing'),
	 'admin_trees_merge.php'			=> KT_I18N::translate('Merge records'),
	 'admin_trees_renumber.php'			=> KT_I18N::translate('Renumber family tree'),
	 'admin_trees_append.php'			=> KT_I18N::translate('Append family tree'),
	 'admin_trees_duplicates.php'		=> KT_I18N::translate('Find duplicate individuals'),
	 'admin_trees_findunlinked.php'		=> KT_I18N::translate('Find unlinked records'),
	 'admin_trees_sanity.php'			=> KT_I18N::translate('Sanity check'),
	 'admin_trees_source.php'			=> KT_I18N::translate('Sources - review'),
	 'admin_trees_sourcecite.php'		=> KT_I18N::translate('Sources - review citations'),
	 'admin_trees_missing.php'			=> KT_I18N::translate('Missing fact or event details'),
);
asort($pagesList);
*/
