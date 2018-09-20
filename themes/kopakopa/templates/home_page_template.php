<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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
?>

<div class="grid-x grid-margin-x grid-margin-y home-page">
	<!-- left or top -->
	<div class="cell medium-8 large-7 large-offset-1">
		<?php foreach ($blocks['main'] as $block_id => $module_name) {
			$class_name	= $module_name . '_KT_Module';
			$module		= new $class_name;
			if ($SEARCH_SPIDER || !$module->loadAjax()) {
				// Load the block directly
				$module->getBlock($block_id);
			} else {
				// Load the block asynchronously ?>
				<div class="cell align-center loading-image">
					<i class="<?php echo $iconStyle; ?> fa-spinner fa-spin fa-3x"></i>
					<span class="sr-only">Loading...</span>
				</div>
				<div id="block_<?php echo $block_id; ?>"></div>
				<?php $controller->addInlineJavascript(
					'jQuery("#block_' . $block_id . '").load("index.php?action=ajax&block_id=' . $block_id . '");'
				);
			}
		} ?>
	</div>

	<!-- right or bottom -->
	<div class="cell medium-4 large-3">
		<?php foreach ($blocks['side'] as $block_id => $module_name) {
			$class_name	= $module_name . '_KT_Module';
			$module		= new $class_name;
			if ($SEARCH_SPIDER || !$module->loadAjax()) {
				// Load the block directly
				$module->getBlock($block_id);
			} else {
				// Load the block asynchronously ?>
				<div class="cell align-center loading-image">
					<i class="<?php echo $iconStyle; ?> fa-spinner fa-spin fa-3x"></i>
					<span class="sr-only">Loading...</span>
				</div>
				<div id="block_<?php echo $block_id; ?>"></div>
				<?php $controller->addInlineJavascript(
					'jQuery("#block_' . $block_id . '").load("index.php?action=ajax&block_id=' . $block_id . '");'
				);
			}
		} ?>
	</div>

</div>
<?php
