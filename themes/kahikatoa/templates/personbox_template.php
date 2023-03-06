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
?>

<!--<div id="out-<?php echo $boxID; ?>" class="<?php echo $outBoxAdd; ?>">-->
	<div class="hide-for-print" id="icons-<?php echo $boxID; ?>" style="<?php echo $iconsStyleAdd; ?>">
		<?php echo $icons; ?>
	</div>
	<div class="grid-x grid-padding-x person_box_template <?php echo $isF; ?>">
		<div class="medium-2 large-1 show-for-medium">
			<?php echo $thumbnail; ?>
		</div>
		<div class="medium-10 large-11">
			<a class="h6" href="individual.php?pid=<?php echo $pid; ?>&amp;ged=<?php echo rawurlencode((string) $GEDCOM); ?>" onclick="event.cancelBubble=true;">
				<span id="namedef-<?php echo $boxID; ?>" class="name<?php echo $style; ?> <?php echo $classfacts; ?>">
					<?php echo $name . $addname; ?>
				</span>
				<span class="name<?php echo $style; ?>">
					<?php echo $genderImage; ?>
				</span>
			</a>
			<div id="fontdef-<?php echo $boxID; ?>" class="details<?php echo $style; ?>">
				<div id="inout2-<?php echo $boxID; ?>" style="max-height:<?php echo ($bheight * .9); ?>px;"><?php echo $BirthDeath; ?></div>
			</div>
		</div>
	</div>
	<div id="inout-<?php echo $boxID; ?>" style="display:none;">
		<div id="LOADING-inout-<?php echo $boxID; ?>"><?php echo KT_I18N::translate('Loading...'); ?></div>
	</div>
<!--</div>-->

<?php
