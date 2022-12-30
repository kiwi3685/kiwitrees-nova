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

 global $ALL_CAPS, $iconStyle;
 include 'templates/commonElements.php';
 $this
 	->addExternalJavascript (KT_JQUERY_COLORBOX_URL)
 	->addExternalJavascript (KT_JQUERY_WHEELZOOM_URL)
 	->addExternalJavascript (KT_JQUERY_AUTOSIZE)
 	->addInlineJavascript ('
 		activate_colorbox();
 		jQuery.extend(jQuery.colorbox.settings, {
 			slideshowStart	:"' . KT_I18N::translate('Play') . '",
 			slideshowStop	:"' . KT_I18N::translate('Stop') . '",
            previous        :"<i class=\"' . $iconStyle . ' fa-angle-left\"></i>",
            next            :"<i class=\"' . $iconStyle . ' fa-angle-right\"></i>",
            close           :"<i class=\"' . $iconStyle . ' fa-xmark\"></i>",
 		});
 		// Add colorbox to pdf-files
 		jQuery("body").on("click", "a.gallery", function(event) {
 			jQuery("a[type^=application].gallery").colorbox({
 				title: function(){
 							var url = jQuery(this).attr("href");
 							var img_title = jQuery(this).data("title");
 							return "<a href=\"" + url + "\" target=\"_blank\">" + img_title + " - '.
 							KT_I18N::translate('Open in full browser window').'</a>";
 						}
 			});
 		});

 		jQuery("textarea").autosize();

 	');


 if ($ALL_CAPS) {
 	$this->addInlineJavascript('all_caps();');
 }

 if (KT_USER_ID && KT_SCRIPT_NAME != 'index.php') {
 	$show_widgetbar = true;
 	$this->addInlineJavascript ('widget_bar();');
 } else {
 	$show_widgetbar = false;
 }

 ?>

<!DOCTYPE html>
<html <?php echo KT_I18N::html_markup(); ?>>
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<?php echo header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL); ?>
		<title><?php echo htmlspecialchars((string) $title); ?></title>
        <!--Generic favicons-->
        <link rel="icon" sizes="16x16" href="<?php echo KT_THEME_URL; ?>images/favicon.png">
        <link rel="icon" sizes="32x32" href="<?php echo KT_THEME_URL; ?>images/favicon-32.png">
        <link rel="icon" sizes="128x128" href="<?php echo KT_THEME_URL; ?>images/favicon-128.png">
        <link rel="icon" sizes="192x192" href="<?php echo KT_THEME_URL; ?>images/favicon-192.png">
        <!--Android-->
        <link rel="shortcut icon" sizes="196x196" href="<?php echo KT_THEME_URL; ?>images/favicon-196.png">
        <!--iPad-->
        <link rel="apple-touch-icon" sizes="152x152" href="<?php echo KT_THEME_URL; ?>images/favicon-152.png">
        <!--iPhone-->
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo KT_THEME_URL; ?>images/apple-touch-icon.png">
		<?php if ($view !='simple') { ?>
		<link rel="stylesheet" href="<?php echo KT_DATATABLES_CSS; ?>">
		<link rel="stylesheet" href="<?php echo KT_DATEPICKER_CSS; ?>">
		<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>css/kopakopa.min.css">
		<?php if (file_exists(KT_THEME_URL . 'mystyle.css')) { ?>
				<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>mystyle.css" type="text/css">
			<?php }
		}?>
	</head>
	<body>
		<?php if ($view !='simple') { ?>
			<?php if ($show_widgetbar) { ?>
				<div class="widget-bar off-canvas position-left" id="widgetBar" data-off-canvas>
					<?php include_once 'widget-bar.php'; ?>
				</div>
				<div class="cell off-canvas-content" data-off-canvas-content> <!-- closed in footer -->
			<?php } ?>
			<nav class="grid-x hide-for-print">
				<div class="top-bar stack-for-small">
					<div class="top-bar-left">
						<ul class="dropdown menu" data-dropdown-menu>
							<?php if ($show_widgetbar) { ?>
								<li>
									<button class="button clear widget" type="button" data-toggle="widgetBar" title="<?php echo KT_I18N::translate('Widget bar'); ?>">
										<i class="<?php echo $iconStyle; ?> fa-bars fa-2x"></i>
									</button>
								</li>
							<?php } ?>
							<li class="show-for-large">
								<i class="kiwitrees_logo"></i>
							</li>
							<?php foreach (KT_MenuBar::getOtherMenus() as $menu) {
      					if (strpos($menu, KT_I18N::translate('Login')) && !KT_USER_ID && KT_Module::getModuleByName('login_block')) {
      						$class_name	= 'login_block_KT_Module';
      						$module		= new $class_name; ?>
									<li>
      							<a href="#">
      								<?php echo (KT_Site::preference('USE_REGISTRATION_MODULE') ? KT_I18N::translate('Login or Register') : KT_I18N::translate('Login')); ?>
      							</a>
      							<ul id="login_popup">
      								<li><?php echo $module->getBlock('login_block'); ?></li>
										</ul>
									</li>
								<?php } else {
									echo $menu->getMenuAsList();
								}
							} ?>
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
										<span class="input-group-label"><i class="<?php echo $iconStyle; ?> fa-magnifying-glass"></i></span>
					                </div>
								</form>
							</li>
					    </ul>
					</div>
				</div>
				<div class="top-bar second-top-bar">
					<div class="top-bar-left">
						<div class="treetitle text-center medium-text-left"><?php echo KT_TREE_TITLE; ?></div>
						<div class="subtitle show-for-large"><?php echo KT_TREE_SUBTITLE; ?></div>
				</div>
					<div class="top-bar-right main-menu">
				<!-- responsive menu -->
				<div class="title-bar" data-hide-for="medium" data-responsive-toggle="kiwitrees-menu">
				  <button class="menu-icon" type="button" data-toggle="kiwitrees-menu"></button>
				  <div class="title-bar-title"><?php echo KT_I18N::translate('Menu'); ?></div>
				</div>
				<!-- normal menu -->
				<ul id="kiwitrees-menu" class="menu vertical medium-horizontal icons icon-top align-center full" data-responsive-menu="accordion medium-dropdown">
					<?php foreach (KT_MenuBar::getMainMenus() as $menu) {
						echo $menu->getMenuAsList();
					} ?>
				</ul>
					</div>
				</div>
			</nav>

			<?php echo KT_FlashMessages::getHtmlMessages(), // Feedback from asynchronous actions

			$javascript;
		} ?>
		<main class="grid-x grid-padding-x">
			<div class="cell"> <!-- container for all pages -->
