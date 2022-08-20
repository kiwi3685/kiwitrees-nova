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

global $iconStyle;
include 'templates/commonElements.php';
include 'templates/relatedPages.php';

/**
 * Array of site administration menu items
 * $site_tools [array]
 */

$site_tools = [
	"adminSummary_site.php",
	"admin_site_config.php",
	"admin_site_logs.php",
	"admin_site_info.php",
	"admin_site_access.php",
	"admin_site_clean.php",
	"admin_site_use.php",
];

/**
 * Array of Family tree tool menu items
 * $trees [array]
 */
$trees = [
	"adminSummary_trees.php",
	"admin_trees_manage.php",
	"admin_trees_config.php",
	"admin_trees_check.php",
	"admin_trees_change.php",
	"admin_trees_addunlinked.php",
	"admin_trees_places.php",
	"admin_trees_merge.php",
	"admin_trees_renumber.php",
	"admin_trees_append.php",
	"admin_trees_duplicates.php",
	"admin_trees_findunlinked.php",
	"admin_trees_sanity.php",
	"admin_trees_source.php",
	"admin_trees_sourcecite.php",
	"admin_trees_missing.php",
];

/**
 * Array of site administration menu items
 * $site_tools [array]
 */
$users = [
	"adminSummary_users.php",
	"admin_users.php",
	"admin_users_bulk.php",
];

/**
 * Array of family tree administration menu items
 * $ft_tools [array]
 */
$media = [
	"adminSummary_media.php",
	"admin_media.php",
	"admin_media_upload.php",
];

/**
 * Array of Module menu items
 * $module_cats [array]
 */
$module_config = [
	"adminSummary_modules.php",
	"admin_module_menus.php",
	"admin_module_tabs_indi.php",
	"admin_module_blocks.php",
	"admin_module_widgets.php",
	"admin_module_sidebar.php",
	"admin_module_reports.php",
	"admin_module_charts.php",
	"admin_module_lists.php",
	"admin_module_footers.php",
	"admin_module_tabs_fam.php",
];

/**
 * Array of site administration menu items
 * $custom [array]
 */
 $custom = [
	"adminSummary_custom.php",
	"admin_custom_lang.php",
	"admin_custom_theme.php",
 ];

 /**
  * Array of site administration menu items
  * $tools [array]
  */
$tools = ["adminSummary_tools.php"];
foreach (KT_Module::getActiveModules(true) as $tool) {
	if ($tool instanceof KT_Module_Config && $tool->getName() !== 'custom_js') {
		$tools[] = $tool->getName();
	}
}

$class='';

$this
	->addExternalJavascript(KT_KIWITREES_ADMIN_JS_URL)
	->addExternalJavascript(KT_JQUERY_COLORBOX_URL)
	->addExternalJavascript(KT_JQUERY_WHEELZOOM_URL)
	->addExternalJavascript(KT_JQUERY_AUTOSIZE)
	->addInlineJavascript('jQuery("textarea").autosize();');
?>

<!DOCTYPE html>
<html <?php echo KT_I18N::html_markup(); ?>>
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<title><?php echo htmlspecialchars($title); ?></title>
		<link rel="icon" href="<?php echo KT_THEME_URL; ?>images/kt.png" type="image/png">
		<link rel="stylesheet" href="<?php echo KT_DATATABLES_CSS; ?>">
		<link rel="stylesheet" href="<?php echo KT_DATEPICKER_CSS; ?>">
		<link rel="stylesheet" href="<?php echo KT_CHOSEN_CSS; ?>">
		<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>css/administration.min.css">
		<?php echo $javascript; ?>
	</head>

	<body id="body">

		<nav id="admin-head" class="grid-x">
			<div class="top-bar first-top-bar">
				<div class="top-bar-left">
					<ul class="dropdown menu" data-dropdown-menu>
						<li class="show-for-large">
							<p class="kiwitrees_logo"></p>
						</li>
						<li>
							<?php echo KT_MenuBar::getGedcomMenu(); ?>
						</li>
						<li>
							<?php if (KT_USER_GEDCOM_ID) { ?>
								<a href="individual.php?pid=<?php echo KT_USER_GEDCOM_ID; ?>&amp;ged=<?php echo KT_GEDURL; ?>">
									<i class="<?php echo $iconStyle; ?> fa-male show-for-medium"></i>
									<?php echo KT_I18N::translate('My individual record'); ?>
								</a>
							<?php } ?>
						</li>
						<?php $language_menu = KT_MenuBar::getLanguageMenu();
						if ($language_menu) { ?>
							<li>
								<?php echo $language_menu->getMenuAsList(); ?>
							</li>
						<?php } ?>
						<li>
							<?php echo logout_link(true); ?>
						</li>
						<?php if (KT_USER_CAN_ACCEPT && exists_pending_change()) { ?>
							<li>
								<p><a href="edit_changes.php" target="_blank" rel="noopener noreferrer" class="alert"><?php echo KT_I18N::translate('Pending changes'); ?></a></p>
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="top-bar-right">
					<ul class="menu">
						<li>
							<form class="header-search"action="search.php" method="post">
								<div class="input-group">
									<input type="hidden" name="action" value="general">
									<input type="hidden" name="topsearch" value="yes">
									<input type="search"  name="query" placeholder="<?php echo KT_I18N::translate('Search family tree'); ?>" class="input-group-field">
									<span class="input-group-label"><i class="<?php echo $iconStyle; ?> fa-magnifying-glass"></i></span>
								</div>
							</form>
						</li>
					</ul>
				</div>
			</div>
			<div class="top-bar second-top-bar">
				<div id="admin-title" class="top-bar-left text-center h3">
					<?php echo KT_I18N::translate('Administration'); ?>
				</div>
			</div>
			<!-- responsive menu -->
			<div class="title-bar" data-hide-for="large" data-responsive-toggle="admin-menu" style="display: none;">
				<div class="reponsiveMenu">
					<button type="button" data-toggle="admin-menu">
						<i class="menu-icon"></i>
						<div class="title-bar-title"><?php echo KT_I18N::translate('Menu'); ?></div>
					</button>
				<div>
			</div>
		</nav>

		<main class="grid-x grid-padding-x">
			<aside class="cell large-2">
				<ul id="admin-menu" class="vertical menu">
					<li class="admin-menu-title">
						<a <?php echo (KT_SCRIPT_NAME == "admin.php" ? 'class="current" ' : ''); ?>href="admin.php">
							<i class="<?php echo $iconStyle; ?> fa-gauge"></i>
							<?php echo KT_I18N::translate('Dashboard'); ?>
						</a>
					</li>
					<?php if (KT_USER_IS_ADMIN) { ?>
						<li class="admin-menu-title">
							<a <?php echo (in_array(KT_SCRIPT_NAME, $site_tools) ? 'class="current" ' : ''); ?>href="adminSummary_site.php">
								<i class="<?php echo $iconStyle; ?> fa-display"></i>
								<?php echo KT_I18N::translate('Website'); ?>
							</a>
						</li>
					<?php } ?>
				    <li class="admin-menu-title">
						<a <?php echo (in_array(KT_SCRIPT_NAME, $trees) ? 'class="current" ' : ''); ?>href="adminSummary_trees.php">
							<i class="<?php echo $iconStyle; ?> fa-tree"></i>
							<?php echo KT_I18N::translate('Family trees'); ?>
						</a>
					</li>
					</li>
					<?php if (KT_USER_IS_ADMIN) { ?>
						<li class="admin-menu-title">
							<a <?php echo (in_array(KT_SCRIPT_NAME, $users) ? 'class="current" ' : ''); ?>href="adminSummary_users.php">
								<i class="<?php echo $iconStyle; ?> fa-users-gear"></i>
								<?php echo KT_I18N::translate('User management'); ?>
							</a>
						</li>

						<li class="admin-menu-title">
							<a <?php echo (in_array(KT_SCRIPT_NAME, $media) ? 'class="current" ' : ''); ?>href="adminSummary_media.php">
								<i class="<?php echo $iconStyle; ?> fa-photo-film"></i>
								<?php echo KT_I18N::translate('Media objects'); ?>
							</a>
						</li>

						<li class="admin-menu-title">
							<?php $class = (in_array(KT_SCRIPT_NAME, $module_config) ? 'current' : ''); ?>
							<a class="<?php echo $class ?>" href="adminSummary_modules.php">
								<i class="<?php echo $iconStyle; ?> fa-cubes"></i>
								<?php echo KT_I18N::translate('Modules'); ?>
							</a>
						</li>

						<li class="admin-menu-title">
							<?php $class = (in_array(KT_SCRIPT_NAME, $custom) || KT_Filter::get('mod') === 'custom_js' ? 'current' : ''); ?>
							<a class="<?php echo $class ?>" href="adminSummary_custom.php">
								<i class="<?php echo $iconStyle; ?> fa-paint-brush"></i>
								<?php echo KT_I18N::translate('Customizing'); ?>
							</a>
						</li>

						<li class="admin-menu-title">
							<?php $class = (in_array(KT_SCRIPT_NAME, $tools) || in_array(KT_Filter::get('mod'), $tools) ? 'current' : ''); ?>
							<a class="<?php echo $class ?>" href="adminSummary_tools.php">
								<i class="<?php echo $iconStyle; ?> fa-screwdriver-wrench"></i>
								<?php echo KT_I18N::translate('Tools'); ?>
							</a>
						</li>

						<li class="admin-menu-title">
							<?php $class = (in_array(KT_SCRIPT_NAME, $tools) || in_array(KT_Filter::get('mod'), $tools) ? 'current' : ''); ?>
							<a class="<?php echo $class ?>" href="adminSearch.php">
								<i class="<?php echo $iconStyle; ?> fa-magnifying-glass"></i>
								<?php echo KT_I18N::translate('Search administration pages'); ?>
							</a>
						</li>
					<?php } ?>
				</ul>
			</aside>
			<div id="admin-content" class="cell large-10">
				<?php echo KT_FlashMessages::getHtmlMessages(); // Feedback from asynchronous actions ?>
