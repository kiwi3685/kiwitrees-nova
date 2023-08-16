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

 if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
 }

 global $ALL_CAPS, $iconStyle;
 include 'templates/commonElements.php';

 $this
	->addExternalJavascript (KT_JQUERY_COLORBOX_URL)
	->addExternalJavascript (KT_JQUERY_WHEELZOOM_URL)
	->addExternalJavascript (KT_AUTOSIZE)
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

		autosize(jQuery("textarea"));
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
			<link rel="stylesheet" href="<?php echo KT_DATATABLES_FOUNDATION_CSS; ?>">
			<link rel="stylesheet" href="<?php echo KT_DATATABLES_FOUNDATION_BUTTONS_CSS; ?>">
			<link rel="stylesheet" href="<?php echo KT_DATEPICKER_CSS; ?>">
			<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>css/foundation.min.css">
			<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>css/kahikatoa.min.css">
			<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>css/libraryfiles.min.css">
			<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>css/responsive.min.css">
			<?php if (file_exists(KT_THEME_URL . 'mystyle.css')) { ?>
				<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>mystyle.css" type="text/css">
			<?php }
		}?>
	</head>
	<body>
		<?php if ($view !='simple') { ?>
			<?php if ($show_widgetbar) { ?>
				<div class="widget-bar off-canvas position-left show-for-large" id="widgetBar" data-off-canvas>
					<?php include_once 'widget-bar.php'; ?>
				</div>
				<div class="cell off-canvas-content" data-off-canvas-content> <!-- closed in footer -->
			<?php } ?>

			<nav class="grid-x hide-for-print">

				<!-- Mobile top menu bar -->
				<div class="cell hide-for-large">
					<?php echo MobileTopBarMenu('MainMenu'); ?>
				</div>

				<!-- Standard top menu bar -->
				<div class="cell top-bar first-top-bar show-for-large">
					<?php echo TopBarMenu($show_widgetbar); ?>
				</div>

				<!-- Common menu parts -->
				<div class="top-bar second-top-bar">
					<div class="top-bar-left">
						<div class="treetitle text-center large-text-left"><?php echo KT_TREE_TITLE; ?></div>
						<div class="subtitle show-for-large"><?php echo KT_TREE_SUBTITLE; ?></div>
					</div>
					<div class="top-bar-right main-menu show-for-large" id="MainMenu" data-toggler="show-for-large">
						<ul id="kiwitrees-menu" class="menu vertical medium-horizontal icons icon-top align-right" data-responsive-menu="accordion medium-dropdown">
							<?php foreach (KT_MenuBar::getMainMenus() as $menu) {
								echo $menu->getMenuAsList();
							} ?>
						</ul>
					</div>
				</div>
			</nav>

			<?php
			// add floating contact link if it is configured
			if (
				array_key_exists('contact_links', KT_Module::getActiveMenus()) &&
				get_module_setting('contact_links', 'CONTACT_FLOAT') == 'float' &&
				KT_SCRIPT_NAME != 'message.php'
			) {
				contact_links_KT_Module::show();
			}
			echo KT_FlashMessages::getHtmlMessages() . // Feedback from asynchronous actions
			$javascript;

		} ?>

		<main class="grid-x grid-padding-x">
			<div class="cell"> <!-- container for all pages -->
