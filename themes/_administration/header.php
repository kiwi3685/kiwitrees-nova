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

include 'templates/commonElements.php';
include 'templates/adminData.php';
include 'templates/functions.php';

global $iconStyle;

$searchTerm = KT_Filter::post('admin_query') ? KT_Filter::post('admin_query') : '';
$class      = '';


if (!$searchTerm && isset($_COOKIE["adminSearch"])){
   $searchTerm = $_COOKIE["adminSearch"];
} 


$this
	->addExternalJavascript(KT_KIWITREES_ADMIN_JS_URL)
	->addExternalJavascript(KT_JQUERY_COLORBOX_URL)
	->addExternalJavascript(KT_JQUERY_WHEELZOOM_URL)
	->addExternalJavascript(KT_JQUERY_AUTOSIZE)
	->addInlineJavascript('
		jQuery("textarea").autosize();

		// Manage cookies for admin search
		function save_data() {
			var input = document.getElementById("term")
			document.cookie = "adminSearch" + "=" + input.value;
		};
		jQuery("#searchClose").click(function() {
			document.cookie = "adminSearch=;expires=" + new Date(0).toUTCString()
			location.reload();
		});

	');

?>

<!DOCTYPE html>
<html <?php echo KT_I18N::html_markup(); ?>>
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<title><?php echo htmlspecialchars((string) $title); ?></title>
		<link rel="icon" href="<?php echo KT_THEME_URL; ?>images/kt.png" type="image/png">
		<link rel="stylesheet" href="<?php echo KT_DATATABLES_CSS; ?>">
		<link rel="stylesheet" href="<?php echo KT_DATEPICKER_CSS; ?>">
		<link rel="stylesheet" href="<?php echo KT_ICONPICKER_CSS; ?>">
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
							<form class="header-search" action="search.php" method="post">
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
							<a <?php echo (array_key_exists(KT_SCRIPT_NAME, $site_tools) ? 'class="current" ' : ''); ?>href="admin_summary_site.php">
								<i class="<?php echo $iconStyle; ?> fa-display"></i>
								<?php echo KT_I18N::translate('Website'); ?>
							</a>
						</li>
					<?php } ?>
					<li class="admin-menu-title">
						<a <?php echo (array_key_exists(KT_SCRIPT_NAME, $trees) ? 'class="current" ' : ''); ?>href="admin_summary_trees.php">
							<i class="<?php echo $iconStyle; ?> fa-tree"></i>
							<?php echo KT_I18N::translate('Family trees'); ?>
						</a>
					</li>
					</li>
					<?php if (KT_USER_IS_ADMIN) { ?>
						<li class="admin-menu-title">
							<a <?php echo (array_key_exists(KT_SCRIPT_NAME, $users) ? 'class="current" ' : ''); ?>href="admin_summary_users.php">
								<i class="<?php echo $iconStyle; ?> fa-users-gear"></i>
								<?php echo KT_I18N::translate('User management'); ?>
							</a>
						</li>

						<li class="admin-menu-title">
							<a <?php echo (array_key_exists(KT_SCRIPT_NAME, $media) ? 'class="current" ' : ''); ?>href="admin_summary_media.php">
								<i class="<?php echo $iconStyle; ?> fa-photo-film"></i>
								<?php echo KT_I18N::translate('Media objects'); ?>
							</a>
						</li>

						<li class="admin-menu-title">
							<?php $class = (array_key_exists(KT_SCRIPT_NAME, $module_config) ? 'current' : ''); ?>
							<a class="<?php echo $class ?>" href="admin_summary_modules.php">
								<i class="<?php echo $iconStyle; ?> fa-cubes"></i>
								<?php echo KT_I18N::translate('Modules'); ?>
							</a>
						</li>

						<li class="admin-menu-title">
							<?php $class = (array_key_exists(KT_SCRIPT_NAME, $custom) || KT_Filter::get('mod') === 'custom_js' ? 'current' : ''); ?>
							<a class="<?php echo $class ?>" href="admin_summary_custom.php">
								<i class="<?php echo $iconStyle; ?> fa-paint-brush"></i>
								<?php echo KT_I18N::translate('Customizing'); ?>
							</a>
						</li>

						<li class="admin-menu-title">
							<?php $class = (array_key_exists(KT_SCRIPT_NAME, $tools) || in_array(KT_Filter::get('mod'), $tools) ? 'current' : ''); ?>
							<a class="<?php echo $class ?>" href="admin_summary_tools.php">
								<i class="<?php echo $iconStyle; ?> fa-screwdriver-wrench"></i>
								<?php echo KT_I18N::translate('Tools'); ?>
							</a>
						</li>
						<?php if (in_array(KT_LOCALE, array('en_US', 'en_GB', 'en_AU'))) { ?>
							<li class="admin-menu-title">
								<form id="adminSearch" method="post" name="adminSearch" onsubmit="save_data()">
									<div class="input-group">
										<input id="term" type="search" name="admin_query" value="<?php echo $searchTerm; ?>" placeholder="<?php echo KT_I18N::translate('Administration search'); ?>" class="input-group-field">
										<span class="input-group-label">
											<a href="#" onclick="adminSearch.submit()">
												<i class="<?php echo $iconStyle; ?> fa-magnifying-glass"></i>
											</a>
										</span>
									</div>
								</form>
								<?php if ($searchTerm) {
									$result = adminSearch($searchTerm); ?>
									<div id="adminQueryResult" class="callout info-help" data-closable>
										<h6><?php echo KT_I18N::translate('The term <span>%s</span> can be found on these pages: ', $searchTerm); ?></h6>
										<button class="close-button" id="searchClose" type="button" data-close>
											<span aria-hidden="true">&times;</span>
										</button>
										<ul>
											<?php foreach ($result as $page) {
												foreach ($page as $file => $count)  {
													if (array_key_exists($file, $indirectAccess)) {
														$modules = KT_Module::getActiveModules(KT_GED_ID, KT_PRIV_HIDE);
														foreach ($modules as $module) {
															if ( $module->getName() === $indirectAccess[$file]) {
																echo '
																	<li>
																		<a href="' . $module->getConfigLink(str_replace(".php", "", $file)) . '" target="_blank">
																			' . $searchAdminFiles[$file]
																			. '&nbsp(' . $count . ')
																		</a>
																	</li>
																';
															}
														}
													} else {
														echo '
															<li>
																<a href="' . $file . '" target="_blank">
																	' . $searchAdminFiles[$file]
																	. '&nbsp(' . $count . ')
																</a>
															</li>
														';
													}
												}
											} ?>
										</ul>
									</div>
								<?php } elseif ($searchTerm) { ?>
									<div id="adminQueryResult">
										<?php echo  KT_I18N::translate('Nothing found'); ?>
									</div>
							   <?php } ?>
							</li>
						<?php }
					} ?>
				</ul>
			</aside>
			<div id="admin-content" class="cell large-10">
				<?php echo KT_FlashMessages::getHtmlMessages(); // Feedback from asynchronous actions ?>
