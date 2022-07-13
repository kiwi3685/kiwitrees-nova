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

class block_givennames_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module.  Top=Most common */ KT_I18N::translate('Top given names');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Top given names” module */ KT_I18N::translate('A list of the most popular given names.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $config = null) {
		global $controller, $KT_TREE, $TEXT_DIRECTION, $iconStyle;

		$num		= get_block_setting($block_id, 'num', 10);
		$infoStyle	= get_block_setting($block_id, 'infoStyle', 'table');
		$block		= get_block_setting($block_id, 'block', false);

		if ($config) {
			foreach (array('num', 'infoStyle', 'block') as $name) {
				if (array_key_exists($name, $config)) {
					$$name = $config[$name];
				}
			}
		}

		$stats	= new KT_Stats(KT_GEDCOM);
		$id		= $this->getName() . $block_id;
		$class	= $this->getName();
		$config	= true;

		if ($num == 1) {
			// I18N: i.e. most popular given name.
			$title = KT_I18N::translate('Top given name');
		} else {
			// I18N: Title for a list of the most common given names, %s is a number.  Note that a separate translation exists when %s is 1
			$title = KT_I18N::plural('Top %s given name', 'Top %s given names', $num, KT_I18N::number($num));
		}
		$subtitle = '';

		$content = '<div class="grid-x">
			<div class="cell">';
				//Select List or Table
				switch ($infoStyle) {
					case "list": // Output style 1:  Simple list style.  Better suited to large blocks.
						$params = array(1, $num, 'rcount');
						//List Female names
						$totals = $stats->commonGivenFemaleTotals($params);
						if ($totals) {
							$content .= '
							<h6 class="font-bold">' . KT_I18N::translate('Females') . '</h6>
							<p class="indent">' . $totals . '</p>';
						}
						//List Male names
						$totals = $stats->commonGivenMaleTotals($params);
						if ($totals) {
							$content .= '
							<h6 class="font-bold">' . KT_I18N::translate('Males') . '</h6>
							<p class="indent">' . $totals . '</p>';
						}
					break;
					case "table": // Style 2: Tabular format.  Narrow, 2 or 3 column table, good in small blocks.
						$params = array(1, $num, 'rcount');
						$content .='
							<table>
								<tr>
									<td>' . $stats->commonGivenFemaleTable($params).'</td>
									<td>' . $stats->commonGivenMaleTable($params).'</td>
								</tr>
							</table>';
					break;
				}
			$content .=  '</div>
		</div>';

		if ($template) {
			if (get_block_location($block_id) === 'side') {
				require KT_THEME_DIR . 'templates/block_small_temp.php';
			} else {
				require KT_THEME_DIR . 'templates/block_main_temp.php';
			}
		} else {
			return $content;
		}
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return true;
	}

	// Implement class KT_Module_Block
	public function isUserBlock() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'num',       KT_Filter::postInteger('num', 1, 10000, 10));
			set_block_setting($block_id, 'infoStyle', KT_Filter::post('infoStyle', 'list|table', 'table'));
			set_block_setting($block_id, 'block',     KT_Filter::postBool('block'));
			exit;
		}

		$num		= get_block_setting($block_id, 'num', 10);
		$infoStyle	= get_block_setting($block_id, 'infoStyle', 'table');
		$block		= get_block_setting($block_id, 'block', false);

		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		?>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Number of items to show'); ?></label>
		</div>
		<div class="cell medium-7">
			<input type="text" name="num" size="2" value="<?php echo $num; ?>">
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Presentation style'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo select_edit_control('infoStyle', array('list'=>KT_I18N::translate('list'), 'table'=>KT_I18N::translate('table')), null, $infoStyle, ''); ?>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Add a scrollbar when block contents grow'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('block', $block); ?>
		</div>
		<hr>

	<?php }
}
