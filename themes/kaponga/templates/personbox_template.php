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

<div class="grid-x person_box_template <?php echo $isF; ?>">
	<div class="cell medium-2 large-1 show-for-medium">
		<?php echo $thumbnail; ?>
	</div>
	<div class="cell medium-10 large-11">
		<a class="h6" href="individual.php?pid=<?php echo $pid; ?>&amp;ged=<?php echo rawurlencode($GEDCOM); ?>">
			<span id="name_<?php echo $boxID; ?>" class="name<?php echo $style; ?> <?php echo $classfacts; ?>">
				<?php echo $name . $addname; ?>
			</span>
			<span class="name<?php echo $style; ?>">
				<?php echo $genderImage; ?>
			</span>
		</a>
		<button class="float-right clear button show-for-medium" type="button" data-toggle="<?php echo $dataToggle; ?>">
			<i class="<?php echo $iconStyle; ?> fa-search-plus fa-lg"></i>
		</button>
		<div id="birtdeat_<?php echo $boxID; ?>" style="max-height:<?php echo $bheight*.9; ?>px;">
			<?php echo $BirthDeath; ?>
		</div>
	</div>
</div>
<!-- details for details view -->
<div class="dropdown-pane person_box_template card shadow" data-position="bottom" data-alignment="left" id="<?php echo $dataToggle; ?>" data-dropdown data-auto-focus="true" data-v-offset=26 data-h-offset=-528 data-closable data-close-on-click="true" >
	<div class="card-divider text-center">
		<a class="h6" href="individual.php?pid=<?php echo $pid; ?>&amp;ged=<?php echo rawurlencode($GEDCOM); ?>">
			<span><?php echo $name . $addname; ?></span>
		</a>
	</div>
	<div class="card-section">
		<div><?php echo $detailedView; ?></div>
		<?php echo $personlinks; ?>
	</div>
	<button class="close-button" aria-label="<?php echo KT_I18N::translate('Close details popup'); ?>" type="button" data-close>
		<span aria-hidden="true">&times;</span>
	</button>
</div>
<!-- end of zoom view -->
<?php
