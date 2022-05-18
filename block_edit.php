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

define('KT_SCRIPT_NAME', 'block_edit.php');
require './includes/session.php';

/**
 * Defined in session.php
 *
 * @global Tree $WT_TREE
 */
global $KT_TREE;

$block_id	= KT_Filter::getInteger('block_id');
$block		= KT_DB::prepare(
					"SELECT * FROM `##block` WHERE block_id=?"
				)->execute(array($block_id))->fetchOneRow();

// Select either footer blocks or homep page blocks
if (strpos($block->module_name, 'footer') !== false) {
	$blocks = KT_Module::getActiveFooters(KT_GED_ID);
} else {
	$blocks = KT_Module::getActiveBlocks(KT_GED_ID);
}

// Check access.  (1) the block must exist, (2) gedcom blocks require managers
if (
	!$block ||
	!array_key_exists($block->module_name, $blocks)
) {
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);

	return;
}

$block = $blocks[$block->module_name];

if (KT_Filter::post('save')) {
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'index.php?&ged=' . $KT_TREE->tree_name_url);
	$block->configureBlock($block_id);

	return;
}

$controller = new KT_Controller_Page();
$controller
	->setPageTitle($block->getTitle() . ' — ' . KT_I18N::translate('Configuration'))
	->pageHeader();

if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
	if (in_array($block->getTitle(), array('Home'))) {
		ckeditor_KT_Module::enableBasicEditor($controller);
	} else {
		ckeditor_KT_Module::enableEditor($controller);
	}
}
?>

<div class="grid-x block-edit">
	<div class="cell large-8 large-offset-2">
		<h3 class="text-center"><?php echo $controller->getPageTitle(); ?></h3>
		<h4 class="text-center"><?php echo $block->getDescription(); ?></h3>
		<form name="block" method="post" action="block_edit.php?block_id=<?php echo $block_id; ?>">
			<input type="hidden" name="save" value="1">
			<?php echo KT_Filter::getCsrf(); ?>
			<div class="grid-x grid-margin-y">
				<?php echo $block->configureBlock($block_id); ?>
			</div>
			<button class="button primary" type="submit">
				<i class="<?php echo $iconStyle; ?> fa-save"></i>
				<?php echo KT_I18N::translate('Save'); ?>
			</button>
			<button class="button secondary" type="button" onclick="window.location.href='index.php'">
				<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
				<?php echo KT_I18N::translate('Cancel'); ?>
			</button>
		</form>
	</div>
</div>
