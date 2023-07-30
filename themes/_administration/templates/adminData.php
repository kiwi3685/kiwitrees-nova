<?php
/**
 * Kiwitrees: Web based Family History software Copyright (C) 2012 to 2023
 * kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net) Copyright (C) 2010 to 2012 webtrees
 * development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net) Copyright (C) 2002 to
 * 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version. This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>
 */
if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');

	exit;
}

/**
 * Array of site administration menu items $site_tools [array].
 */
$site_tools = [
	'admin_summary_site.php' => KT_I18N::translate('Website'),
	'admin_site_config.php'  => KT_I18N::translate('Configuration'),
	'admin_site_logs.php'    => KT_I18N::translate('%s logs', KT_KIWITREES),
	'admin_site_info.php'    => KT_I18N::translate('Server information'),
	'admin_site_access.php'  => KT_I18N::translate('Access rules'),
	'admin_site_clean.php'   => KT_I18N::translate('Data folder management'),
	'admin_site_use.php'     => KT_I18N::translate('Server usage'),
];

/**
 * Array of Family tree tool menu items $trees [array].
 */
$trees = [
	'admin_summary_trees.php'      => KT_I18N::translate('Family trees'),
	'admin_trees_manage.php'       => KT_I18N::translate('Manage all family trees'),
	'admin_trees_config.php'       => KT_I18N::translate('Configure each family tree'),
	'admin_trees_check.php'        => KT_I18N::translate('Check for GEDCOM errors'),
	'admin_trees_change.php'       => KT_I18N::translate('Changes log'),
	'admin_trees_addunlinked.php'  => KT_I18N::translate('Add unlinked records'),
	'admin_trees_places.php'       => KT_I18N::translate('Place name editing'),
	'admin_trees_merge.php'        => KT_I18N::translate('Merge records'),
	'admin_trees_renumber.php'     => KT_I18N::translate('Renumber family tree'),
	'admin_trees_append.php'       => KT_I18N::translate('Append family tree'),
	'admin_trees_duplicates.php'   => KT_I18N::translate('Find duplicate individuals'),
	'admin_trees_findunlinked.php' => KT_I18N::translate('Find unlinked records'),
	'admin_trees_sanity.php'       => KT_I18N::translate('Sanity check'),
	'admin_trees_source.php'       => KT_I18N::translate('Sources - review'),
	'admin_trees_sourcecite.php'   => KT_I18N::translate('Sources - review citations'),
	'admin_trees_missing.php'      => KT_I18N::translate('Missing fact or event details'),
];

/**
 * Array of site administration menu items $site_tools [array].
 */
$users = [
	'admin_summary_users.php' 			=> KT_I18N::translate('User management'),
	'admin_users.php'         			=> KT_I18N::translate('Manage users'),
	'admin_users_settings.php' 			=> KT_I18N::translate('List user settings per tree'),
	'admin_users.php?action=edit' 		=> KT_I18N::translate('Add a new user'),
	'admin_users.php?action=messaging' 	=> KT_I18N::translate('Broadcast messages'),
	'admin_users.php?action=cleanup' 	=> KT_I18N::translate('Delete inactive users'),

];

/**
 * Array of family tree administration menu items $ft_tools [array].
 */
$media = [
	'admin_summary_media.php' => KT_I18N::translate('Media objects'),
	'admin_media.php'         => KT_I18N::translate('Manage media'),
	'admin_media_upload.php'  => KT_I18N::translate('Upload media objects'),
];

/**
 * Array of Module menu items not sorted alphabetically $module_config [array]
 * Manually ordered items before sorted.
 */
$module_config = [
	'admin_summary_modules.php' => KT_I18N::translate('Modules'),
	'admin_modules.php' => KT_I18N::translate('Module administration'),
];

/**
 * Array of Module menu items sorted alphabetically $module_config_files
 * [array].
 */
$module_config_files = [
	'admin_module_menus.php'     => KT_I18N::translate('Top level menu items'),
	'admin_module_tabs_indi.php' => KT_I18N::translate('Tabs for individual page'),
	'admin_module_blocks.php'    => KT_I18N::translate('Home page blocks'),
	'admin_module_widgets.php'   => KT_I18N::translate('Widget bar modules'),
	'admin_module_sidebar.php'   => KT_I18N::translate('Sidebar modules'),
	'admin_module_reports.php'   => KT_I18N::translate('Menu - Report items'),
	'admin_module_charts.php'    => KT_I18N::translate('Menu - Chart items'),
	'admin_module_lists.php'     => KT_I18N::translate('Menu - List  items'),
	'admin_module_footers.php'   => KT_I18N::translate('Footer blocks'),
	'admin_module_tabs_fam.php'  => KT_I18N::translate('Tabs for family page'),
];
asort($module_config_files);

// Combine arrays to keep the "module_config" items at the front of the alpha list/
$module_config = array_merge($module_config, $module_config_files);

/**
 * Array of site administration menu items $custom [array] Excludes "Custom
 * javascript' page as that is not an "admin_xxxxx.php" file that can be
 * searched.
 */
$custom = [
	'admin_summary_custom.php' => KT_I18N::translate('Customizing'),
	'admin_custom_lang.php'    => KT_I18N::translate('Custom translations'),
	'admin_custom_theme.php'   => KT_I18N::translate('Custom file editing'),
	'module.php?mod=custom_js&mod_action=admin_config' => KT_I18N::translate('Custom JavaScript'),
];

/**
 * Array of site administration menu items $tools [array].
 */
$tools = ['admin_summary_tools.php' => KT_I18N::translate('Tools')];
foreach (KT_Module::getActiveModules(true) as $tool) {
	$tools[0] = $tool->getName();
	$tools[1] = $tool->getTitle();
}

/**
 * Array of items used in admin, but not as primary files Needed for admin
 * search tool $other [array].
 */
$other_admin_files = [
	'adminDownload.php'                    => KT_I18N::translate('Download a GEDCOM file'),
	'adminExport.php'                      => KT_I18N::translate('Export a GEDCOM file'),
	'admin_batch_update.php'               => KT_I18N::translate('Batch update'),
	'admin_databasebackup.php'             => KT_I18N::translate('Database backup'),
	'admin_flags.php'                      => KT_I18N::translate('Googlemap flags'),
	'admin_places.php'                     => KT_I18N::translate('Googlemap places'),
	'admin_placecheck.php'                 => KT_I18N::translate('Googlemap place check'),
	'admin_places_edit.php'                => KT_I18N::translate('Googlemap places edit'),
	'admin_preferences.php'                => KT_I18N::translate('Googlemap preferences'),
	'admin_fancy_treeview_ancestors.php'   => KT_I18N::translate('FancyTreeView ancesters'),
	'admin_fancy_treeview_descendants.php' => KT_I18N::translate('FancyTreeView descendants'),
];

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

$searchAdminFiles = array_merge(
	$site_tools,
	$trees,
	$users,
	$media,
	$module_config,
	$custom,
	$tools,
	$other_admin_files,
);
asort($searchAdminFiles);

/**
 * Array of items where file cannot be accessed directly file name => module
 * name.
 */
$indirectAccess = [
	'admin_batch_update.php'               => 'batch_update',
	'admin_databasebackup.php'             => 'backup_database',
	'admin_flags.php'                      => 'googlemap',
	'admin_places.php'                     => 'googlemap',
	'admin_placecheck.php'                 => 'googlemap',
	'admin_places_edit.php'                => 'googlemap',
	'admin_preferences.php'                => 'googlemap',
	'admin_fancy_treeview_ancestors.php'   => 'fancy_treeview_ancesters',
	'admin_fancy_treeview_descendants.php' => 'fancy_treeview_descendants',
];

/*
 * Array or module system tool with config options
 * file name => module name
 */
$moduleTools = [];
foreach (KT_Module::getActiveModules(true) as $module) {
	if ($module instanceof KT_Module_Config && 'custom_js' !== $module->getName()) {
		$key   = $module->getConfigLink();
		$value = $module->getTitle();
		$moduleTools[$key] = $value;
	}
};
asort($moduleTools);

/*
 * List custom theme files that might exist
 * Array of theme template files that can be customised
 * in admin_custom_theme.php
 *
 */
$customFiles = array(
		'mystyle.css',
		'mytheme.php',
		'myheader.php',
		'myfooter_template.php',
		'myhome_page_template.php'
	);

// Array of user roles and display versions
$ALL_EDIT_OPTIONS = array(
	'access'=> /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Member'),
	'edit'  => /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Editor'),
	'accept'=> /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Moderator'),
	'admin' => /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Manager')
);
