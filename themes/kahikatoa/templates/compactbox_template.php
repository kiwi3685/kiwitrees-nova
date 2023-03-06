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

if ($missing) { ?>
	<div class="grid-x compact_box_template <?php echo $isF; ?>">
		<div class="cell medium-2 show-for-medium">
			<?php echo $thumbnail; ?>
		</div>
		<div class="cell medium-10">
			<a class="name" href="individual.php?pid=<?php echo $pid; ?>&amp;ged=<?php echo rawurlencode((string) $GEDCOM); ?>" title="<?php echo strip_tags($name.$addname); ?>">
				<?php echo $shortname; ?>
			</a>
			<button class="float-right clear tiny button show-for-medium" type="button" data-toggle="<?php echo $dataToggle; ?>">
				<i class="<?php echo $iconStyle; ?> fa-magnifying-glass-plus fa-lg"></i>
			</button>
			<p class="dates"><small><?php echo $person->getLifeSpan(); ?></small></p>
			<p class="places"><small><?php echo $birthplace; ?></small></p>
		</div>
	</div>
	<!-- details for details view -->
	<div class="dropdown-pane compact_box_template card shadow" data-position="bottom" data-alignment="left" id="<?php echo $dataToggle; ?>" data-dropdown data-auto-focus="true" data-v-offset=60 data-h-offset=-216 data-closable data-close-on-click="true" >
		<div class="card-divider text-center">
			<a href="individual.php?pid=<?php echo $pid; ?>&amp;ged=<?php echo rawurlencode((string) $GEDCOM); ?>" class="h6">
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
<?php } else { ?> <!-- empty box for missing individual -->
	<div class="grid-x compact_box_template U">
		<div class="cell medium-2 show-for-medium"></div>
	</div>
<?php }
