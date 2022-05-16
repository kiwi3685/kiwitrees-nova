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
?>

<div id="<?php echo $id; ?>" class="block small-block shadow">
	<div class="blockheader">
		<?php echo $title;
		if (KT_USER_GEDCOM_ADMIN && $config) { ?>
			<a href="block_edit.php?block_id=<?php echo $block_id; ?>&amp;ged=<?php echo $KT_TREE->tree_name_url; ?>" title="<?php echo KT_I18N::translate('Configure'); ?>">
				<i class="<?php echo $iconStyle; ?> fa-cogs"></i>
			</a>
		<?php } ?>
	</div>
	<div class="blockcontent <?php echo $class; ?>">
		<?php echo $content; ?>
	</div>
</div>

<?php
