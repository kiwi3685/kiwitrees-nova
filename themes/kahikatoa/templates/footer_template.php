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


$layouts = array(
	'1' => array('12'),
	'2' => array('6','6'),
	'3' => array('2','8','2'),
	'4' => array('3','3','3','3'),
	'5' => array('2','2','4','2','2')
);

$cells = '1'; ?>

</div></div></main><!-- close the main div -->

<?php if ($view != 'simple') { ?>
	<footer class="grid-x grid-padding-x hide-for-print">
		<?php if ($ct_footer_blocks > 0) {
			foreach ($blocks as $block_id => $module_name) {
				$class_name	= $module_name . '_KT_Module';
				$module		= new $class_name;
				switch ($ct_footer_blocks) {
					case '1': ?>
						<div class="cell footer-center">
							<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center">
						<?php
					break;
					case '2':
						switch ($cells) {
							case 1: ?>
								<div class="cell medium-6">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center medium-text-left">
							<?php
							break;
							case 2: ?>
								<div class="cell medium-6">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center medium-text-right">
							<?php
							break;
						}
					break;
					case '3':
						switch ($cells) {
							case 1: ?>
								<div class="cell medium-2">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center medium-text-left">
							<?php
							break;
							case 2: ?>
								<div class="cell medium-8 footer-center">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center">
							<?php
							break;
							case 3: ?>
								<div class="cell medium-2">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center medium-text-right">
							<?php
							break;
						}
					break;
					case '4':
						switch ($cells) {
							case 1: ?>
								<div class="cell medium-3">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center medium-text-left">
							<?php
							break;
							case 2: ?>
								<div class="cell medium-3 footer-center">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center">
							<?php
							break;
							case 3: ?>
								<div class="cell medium-3 footer-center">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center">
							<?php
							break;
							case 4: ?>
								<div class="cell medium-3">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center medium-text-right">
							<?php
							break;
						}
					break;
					case '5':
						switch ($cells) {
							case 1: ?>
								<div class="cell medium-2">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center medium-text-left">
							<?php
							break;
							case 2: ?>
								<div class="cell medium-2">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center medium-text-left">
							<?php
							break;
							case 3: ?>
								<div class="cell medium-4 footer-center">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center">
							<?php
							break;
							case 4: ?>
								<div class="cell medium-2">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center medium-text-right">
							<?php
							break;
							case 5: ?>
								<div class="cell medium-2">
									<div id="<?php echo 'footer_' . $block_id; ?>" class="card text-center medium-text-right">
							<?php
							break;
						}
					break;
				}
				echo $module->getFooter($block_id);
				$cells ++; ?>
					</div>
				</div>
				<?php

			}
		} ?>
	</footer>
	<?php if ($show_widgetbar) { ?>
		</div>
	<?php } ?>

<?php }
