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

define('KT_SCRIPT_NAME', 'index.php');
require './includes/session.php';

// The only option for action is "ajax"
$action			= KT_Filter::get('action');
$blocks			= get_gedcom_blocks(KT_GED_ID);
$active_blocks	= KT_Module::getActiveBlocks();

// Remove empty blocks_pending
if (!exists_pending_change()) {
	foreach($blocks['main'] as $key => $value) {
	    if($value == 'block_pending') {
	        unset($blocks['main'][$key]);
		}
	}
	foreach($blocks['side'] as $key => $value) {
	    if($value == 'block_pending') {
	        unset($blocks['side'][$key]);
		}
	}
}

// We generate some individual blocks using AJAX
if ($action === 'ajax') {
	$controller = new KT_Controller_Ajax();
	$controller->pageHeader();

	// Check we're displaying an allowable block.
	$block_id = KT_Filter::get('block_id');
	if (array_key_exists($block_id, $blocks['main'])) {
		$module_name = $blocks['main'][$block_id];
	} elseif (array_key_exists($block_id, $blocks['side'])) {
		$module_name = $blocks['side'][$block_id];
	} else {
		exit;
	}
	if (array_key_exists($module_name, $active_blocks)) {
		$class_name	= $module_name . '_KT_Module';
		$module		= new $class_name;
		$module->getBlock($block_id);
	}
	if (KT_DEBUG_SQL) {
		echo KT_DB::getQueryLog();
	}
	exit;
}

$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_TREE_TITLE)
	->setMetaRobots('index,follow')
	->setCanonicalUrl(KT_SCRIPT_NAME . '?ged=' . KT_GEDCOM)
	->pageHeader()
	// By default jQuery modifies AJAX URLs to disable caching, causing JS libraries to be loaded many times.
	->addInlineJavascript('jQuery.ajaxSetup({cache:true});');

// use the home page layout or customised version of it for the currently active theme
if (file_exists(KT_THEME_URL . 'myhome_page_template.php')) {
	include KT_THEME_DIR . 'myhome_page_template.php';
} else {
	include KT_THEME_DIR . 'templates/home_page_template.php';
}
