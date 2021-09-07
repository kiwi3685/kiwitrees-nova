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
?>
<div class="card person_card_template <?php echo (isset($isF) ? $isF : ''); ?>">
	<div class="card-divider text-center">
		<?php if (isset($person)) { ?>
			<a class="h6" href="individual.php?pid=<?php echo $pid; ?>&amp;ged=<?php echo rawurlencode($GEDCOM); ?>">
				<span>
					<?php echo $name . $addname; ?>
				</span>
			</a>
		<?php } else { ?>
			<?php echo $name; ?>
		<?php } ?>
	</div>
	<div class="card-image text-center">
		<?php echo $thumbnail; ?>
	</div>
	<div class="card-section">
		<div class="cell notes">
			<?php if (isset($person)) { ?>
				<p class="text-center"><?php echo $person->getLifeSpan(); ?></p>
			<?php } else { ?>
				<p class="text-center"><?php echo $recordYear; ?></p>
			<?php } ?>
		</div>
		<div class="cell notes text-center">
			<?php if ($detailedView) { ?>
				<button class="button clear small expanded show-for-medium" type="button" data-toggle="<?php echo $dataToggle; ?>">
					<i class="<?php echo $iconStyle; ?> fa-search-plus fa-lg"></i>
				</button>
			<?php } ?>
		</div>
		<div class="cell">
			<?php echo $displayNote; ?>
		</div>
	</div>
</div>

<!-- details view -->
<div class="dropdown-pane compact_box_template card" id="<?php echo $dataToggle; ?>" data-dropdown data-closable data-close-on-click="true" >
	<div class="card-divider text-center">
		<?php if (isset($person)) { ?>
			<a class="h6" href="individual.php?pid=<?php echo $pid; ?>&amp;ged=<?php echo rawurlencode($GEDCOM); ?>">
				<span>
					<?php echo $name . $addname; ?>
				</span>
			</a>
		<?php } else { ?>
			<?php echo $name; ?>
		<?php } ?>
	</div>
	<div class="card-section">
		<div><?php echo $detailedView; ?></div>
		<?php if (isset($person)) {
			$personlinks;
		} ?>
	</div>
	<button class="close-button" aria-label="<?php echo KT_I18N::translate('Close details popup'); ?>" type="button" data-close>
		<span aria-hidden="true">&times;</span>
	</button>
</div>
<!-- end of zoom view -->

<?php
