<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

/**
 * Array of site administration menu items
 * $site_tools [array]
 */
$site_tools = array(
	 "admin_site_config.php"	=> KT_I18N::translate('Configuration'),
	 "admin_site_logs.php"		=> KT_I18N::translate('%s logs', KT_KIWITREES),
	 "admin_site_info.php"		=> KT_I18N::translate('Server information'),
	 "admin_site_access.php"	=> KT_I18N::translate('Access rules'),
	 "admin_site_clean.php"		=> KT_I18N::translate('Data folder management'),
	 "admin_site_use.php"		=> KT_I18N::translate('Server usage'),
);
asort($site_tools);

/**
 * Array of Family tree tool menu items
 * $ft_tools [array]
 */
$ft_tools = array(
	"admin_trees_check.php"			=> KT_I18N::translate('Check for GEDCOM errors'),
	"admin_trees_change.php"		=> KT_I18N::translate('Changes log'),
	"admin_trees_addunlinked.php"	=> KT_I18N::translate('Add unlinked records'),
	"admin_trees_places.php"		=> KT_I18N::translate('Place name editing'),
	"admin_trees_merge.php"			=> KT_I18N::translate('Merge records'),
	"admin_trees_renumber.php"		=> KT_I18N::translate('Renumber family tree'),
	"admin_trees_append.php"		=> KT_I18N::translate('Append family tree'),
	"admin_trees_duplicates.php"	=> KT_I18N::translate('Find duplicate individuals'),
	"admin_trees_unlinked.php"		=> KT_I18N::translate('Find unlinked records'),
	"admin_trees_sanity.php"		=> KT_I18N::translate('Sanity check'),
	"admin_trees_source.php"		=> KT_I18N::translate('Sources - review'),
	"admin_trees_sourcecite.php"	=> KT_I18N::translate('Sources - review citations'),
	"admin_trees_missing.php"		=> KT_I18N::translate('Missing data'),
);
asort($ft_tools);

/**
 * Array of site administration menu items
 * $custom [array]
 */
$custom = array(
	 "admin_custom_lang.php"		=> KT_I18N::translate('Custom translation'),
	 "admin_custom_theme.php"		=> KT_I18N::translate('Custom theme files'),
);
arsort($custom);

/**
 * Array of Module menu items
 * $module_cats [array]
 */
$module_cats = array(
	"admin_module_menus.php"		=> KT_I18N::translate('Menus'),
	"admin_module_tabs_indi.php"	=> KT_I18N::translate('Tabs for individual page'),
	"admin_module_blocks.php"		=> KT_I18N::translate('Blocks'),
	"admin_module_widgets.php"		=> KT_I18N::translate('Widgets'),
	"admin_module_sidebar.php"		=> KT_I18N::translate('Sidebar'),
	"admin_module_reports.php"		=> KT_I18N::translate('Reports'),
	"admin_module_charts.php"		=> KT_I18N::translate('Charts'),
	"admin_module_lists.php"		=> KT_I18N::translate('Lists'),
	"admin_module_footers.php"		=> KT_I18N::translate('Footer blocks'),
	"admin_module_tabs_fam.php"		=> KT_I18N::translate('Tabs for family page'),
);
asort($module_cats);

$this
	->addExternalJavascript(KT_JQUERY_COLORBOX_URL)
	->addExternalJavascript(KT_JQUERY_WHEELZOOM_URL)
	->addExternalJavascript(KT_JQUERY_AUTOSIZE)
	->addInlineJavascript('
//		display_help();
//		activate_colorbox();
		jQuery.extend(jQuery.colorbox.settings, {
			title:	function(){
				var img_title = jQuery(this).data("title");
				return img_title;
			}
		});
		jQuery("textarea").autosize();

		if( jQuery(".is-accordion-submenu-item a").hasClass("current") ) {
			jQuery(".current").parent().parent("ul").css({ "display": "flex" });
			jQuery(".current").parent().parent("ul").parent(".is-accordion-submenu-parent").addClass( "expanded");
		};

		jQuery(".accordion-menu").css("visibility", "visible");
	');

?>

<!DOCTYPE html>
<html <?php echo KT_I18N::html_markup(); ?>>
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<?php echo header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL); ?>
		<title><?php echo htmlspecialchars($title); ?></title>
		<link rel="icon" href="<?php echo KT_THEME_URL; ?>images/kt.png" type="image/png">
		<link rel="stylesheet" href="<?php echo KT_DATATABLES_CSS; ?>">
		<link rel="stylesheet" href="<?php echo KT_DATEPICKER_CSS; ?>">
		<link rel="stylesheet" href="<?php echo KT_CHOSEN_CSS; ?>">
		<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>css/administration.min.css">
		<?php echo $javascript; ?>
	</head>
	<body>
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
							<form action="search.php" method="post">
								<div class="input-group">
									<input type="hidden" name="action" value="general">
									<input type="hidden" name="topsearch" value="yes">
									<input type="search"  name="query" placeholder="<?php echo KT_I18N::translate('Search family tree'); ?>" class="input-group-field">
									<span class="input-group-label"><i class="<?php echo $iconStyle; ?> fa-search"></i></span>
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
		</nav> <!--  close admin_head -->
		<div id="admin-container" class="grid-x"> <!--  closed in footer.php -->
			<!-- normal menu -->
			<ul id="admin-menu" class="cell large-2 menu vertical accordion-menu" data-accordion-menu  data-allow-all-closed="true" style="visibility: hidden;">
				<li>
				    <a href="#" class="admin-menu-title">
						<span class="fa-stack" style="vertical-align: top;">
							<i class="<?php echo $iconStyle; ?> background fa-circle fa-stack-2x"></i>
							<i class="<?php echo $iconStyle; ?> foreground fa-tachometer-alt fa-fw fa-stack-1x"></i>
						</span>
						<span><?php echo KT_I18N::translate('Dashboard'); ?></span>
					</a>
					<ul class="menu  vertical">
						<li><a <?php echo (KT_SCRIPT_NAME == "admin.php" ? 'class="current" ' : ''); ?>href="admin.php"><?php echo KT_I18N::translate('Home'); ?></a></li>
					</ul>
				</li>
				<?php if (KT_USER_IS_ADMIN) { ?>
					<li>
			        	<a href="#" class="admin-menu-title">
							<span class="fa-stack" style="vertical-align: top;">
								<i class="<?php echo $iconStyle; ?> background fa-circle fa-stack-2x"></i>
								<i class="<?php echo $iconStyle; ?> foreground fa-cog fa-fw fa-stack-1x"></i>
							</span>
							<span><?php echo KT_I18N::translate('Site administration'); ?></span>
						</a>
					    <ul class="menu  vertical">
							<?php foreach ($site_tools as $file=>$title) { ?>
								<li><a <?php echo (KT_SCRIPT_NAME == $file ? 'class="current" ' : ''); ?>href="<?php echo $file; ?>"><?php echo $title; ?></a></li>
							<?php } ?>
						</ul>
					</li>
				<?php } ?>
			    <li>
			        <a href="#" class="admin-menu-title">
						<span class="fa-stack" style="vertical-align: top;">
							<i class="<?php echo $iconStyle; ?> background fa-circle fa-stack-2x"></i>
							<i class="<?php echo $iconStyle; ?> foreground fa-tree fa-fw fa-stack-1x"></i>
						</span>
						<span><?php echo KT_I18N::translate('Family trees'); ?></span>
					</a>
			        <ul class="menu  vertical">
						<?php if (KT_USER_IS_ADMIN) { ?>
							<li><a <?php echo (KT_SCRIPT_NAME == "admin_trees_manage.php" ? 'class="current" ' : ''); ?>href="admin_trees_manage.php"><?php echo KT_I18N::translate('Manage: <em>All family trees</em>'); ?></a></li>
						<?php }
						//-- gedcom list
						foreach (KT_Tree::getAll() as $tree) {
							if (userGedcomAdmin(KT_USER_ID, $tree->tree_id)) {
								// Add a title="" element, since long tree titles are cropped ?>
								<li>
									<span>
										<a <?php echo (KT_SCRIPT_NAME == "admin_trees_config.php" && KT_GED_ID == $tree->tree_id ? 'class="current" ' : ''); ?>href="admin_trees_config.php?ged=<?php echo $tree->tree_name_url; ?>" title="<?php echo htmlspecialchars($tree->tree_title); ?>" dir="auto">
											<?php echo /* I18N:%s is a tree name */ KT_I18N::translate('Configure: <em>%s</em>', $tree->tree_title_html); ?>
										</a>
									</span>
								</li>
							<?php }
						} ?>
					</ul>
				</li>
				<li>
		        	<a href="#" class="admin-menu-title">
						<span class="fa-stack" style="vertical-align: top;">
							<i class="<?php echo $iconStyle; ?> background fa-circle fa-stack-2x"></i>
							<i class="<?php echo $iconStyle; ?> foreground fa-wrench fa-fw fa-stack-1x"></i>
						</span>
						<span><?php echo KT_I18N::translate('Family tree tools'); ?></span>
					</a>
		        	<ul class="menu  vertical">
						<?php foreach ($ft_tools as $file=>$title) { ?>
							<li><a <?php echo (KT_SCRIPT_NAME == $file ? 'class="current" ' : ''); ?>href="<?php echo $file; ?>"><?php echo $title; ?></a></li>
						<?php } ?>
					</ul>
				</li>
				<?php if (KT_USER_IS_ADMIN) { ?>
					<li>
						<a href="#" class="admin-menu-title">
							<span class="fa-stack" style="vertical-align: top;">
								<i class="<?php echo $iconStyle; ?> background fa-circle fa-stack-2x"></i>
								<i class="<?php echo $iconStyle; ?> foreground fa-users fa-fw fa-stack-1x"></i>
							</span>
							<span><?php echo KT_I18N::translate('Users'); ?></span>
						</a>
						<ul class="menu  vertical">
							<li><a <?php echo (KT_SCRIPT_NAME == "admin_users.php" && safe_GET('action') != "cleanup" && safe_GET('action')!="edit" ? 'class="current" ' : ''); ?>href="admin_users.php"><?php echo KT_I18N::translate('Manage users'); ?></a></li>
							<li><a <?php echo (KT_SCRIPT_NAME == "admin_users.php" && safe_GET('action') == "edit" && safe_GET('user_id') == 0  ? 'class="current" ' : ''); ?>href="admin_users.php?action=edit"><?php echo KT_I18N::translate('Add a new user'); ?></a></li>
							<li><a <?php echo (KT_SCRIPT_NAME == "admin_users_bulk.php" ? 'class="current" ' : ''); ?>href="admin_users_bulk.php"><?php echo KT_I18N::translate('Send broadcast messages'); ?></a></li>
							<li><a <?php echo (KT_SCRIPT_NAME == "admin_users.php" && safe_GET('action') == "cleanup" ? 'class="current" ' : ''); ?>href="admin_users.php?action=cleanup"><?php echo KT_I18N::translate('Delete inactive users'); ?></a></li>
						</ul>
					</li>
				<?php }
				if (KT_USER_IS_ADMIN) { ?>
					<li>
						<a href="#" class="admin-menu-title">
							<span class="fa-stack" style="vertical-align: top;">
								<i class="<?php echo $iconStyle; ?> background fa-circle fa-stack-2x"></i>
								<i class="<?php echo $iconStyle; ?> foreground fa-camera fa-fw fa-stack-1x"></i>
							</span>
							<span><?php echo KT_I18N::translate('Media'); ?></span>
						</a>
						<ul class="menu  vertical">
							<li><a <?php echo (KT_SCRIPT_NAME == "admin_media.php" ? 'class="current" ' : ''); ?>href="admin_media.php"><?php echo KT_I18N::translate('Manage media'); ?></a></li>
							<li><a <?php echo (KT_SCRIPT_NAME == "admin_media_upload.php" ? 'class="current" ' : ''); ?>href="admin_media_upload.php"><?php echo KT_I18N::translate('Upload media files'); ?></a></li>
						</ul>
					</li>
				<?php }
				if (KT_USER_IS_ADMIN) { ?>
					<li>
						<a href="#" class="admin-menu-title">
							<span class="fa-stack" style="vertical-align: top;">
								<i class="<?php echo $iconStyle; ?> background fa-circle fa-stack-2x"></i>
								<i class="<?php echo $iconStyle; ?> foreground fa-puzzle-piece fa-fw fa-stack-1x"></i>
							</span>
							<span><?php echo KT_I18N::translate('Modules'); ?></span>
						</a>
						<ul class="menu  vertical">
							<li><a <?php echo (KT_SCRIPT_NAME == "admin_modules.php" ? 'class="current" ' : ''); ?>href="admin_modules.php"><?php echo KT_I18N::translate('Manage modules'); ?></a></li>
							<?php foreach ($module_cats as $file=>$title) { ?>
								<li><a <?php echo (KT_SCRIPT_NAME == $file ? 'class="current" ' : ''); ?>href="<?php echo $file; ?>"><?php echo $title; ?></a></li>
							<?php } ?>
						</ul>
					</li>
				<?php }
				if (KT_USER_GEDCOM_ADMIN) { ?>
					<li>
						<a href="#" class="admin-menu-title">
							<span class="fa-stack" style="vertical-align: top;">
								<i class="<?php echo $iconStyle; ?> background fa-circle fa-stack-2x"></i>
								<i class="<?php echo $iconStyle; ?> foreground fa-paint-brush fa-fw fa-stack-1x"></i>
							</span>
							<span><?php echo KT_I18N::translate('Customizing'); ?></span>
						</a>
						<ul class="menu  vertical">
							<?php foreach ($custom as $file=>$title) { ?>
								<li><a <?php echo (KT_SCRIPT_NAME == $file ? 'class="current" ' : ''); ?>href="<?php echo $file; ?>"><?php echo $title; ?></a></li>
							<?php } ?>
							<li><a href="index_edit.php?gedcom_id=-1" onclick="return modalDialog('index_edit.php?gedcom_id=-1, <?php echo  KT_I18N::translate('Set the default blocks for new family trees'); ?>');"><?php echo KT_I18N::translate('Set the default blocks'); ?></a></li>
						</ul>
					</li>
				<?php }
				if (KT_USER_IS_ADMIN) { ?>
					<li>
						<a href="#" class="admin-menu-title">
							<span class="fa-stack" style="vertical-align: top;">
								<i class="<?php echo $iconStyle; ?> background fa-circle fa-stack-2x"></i>
								<i class="<?php echo $iconStyle; ?> foreground fa-tools fa-fw fa-stack-1x"></i>
							</span>
							<span><?php echo KT_I18N::translate('Tools'); ?></span>
						</a>
						<ul class="menu  vertical">
							<?php foreach (KT_Module::getActiveModules(true) as $module) {
								if ($module instanceof KT_Module_Config) { ?>
									<li><span><a <?php echo (KT_SCRIPT_NAME == "module.php" && safe_GET('mod') == $module->getName() ? 'class="current" ' : ''); ?>href="<?php echo $module->getConfigLink(); ?>"><?php echo $module->getTitle(); ?></a></span></li>
								<?php }
							} ?>
						</ul>
					</li>
				<?php } ?>
			</ul>
			<div id="admin-content" class="cell large-10"> <!--  closed in footer.php -->
				<?php
				// begin content section
				echo KT_FlashMessages::getHtmlMessages(); // Feedback from asynchronous actions ?>
				<div id="content-container" class="grid-x grid-padding-x grid-padding-y"> <!--  closed in footer.php -->
