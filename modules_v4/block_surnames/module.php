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

class block_surnames_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module.  Top=Most common */ KT_I18N::translate('Top surnames');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Top surnames” module */ KT_I18N::translate('A list of the most popular surnames.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $KT_TREE, $SURNAME_LIST_STYLE, $iconStyle;

		require_once KT_ROOT . 'includes/functions/functions_print_lists.php';

		$COMMON_NAMES_REMOVE	= get_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_REMOVE');
		$COMMON_NAMES_THRESHOLD = get_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_THRESHOLD');

		$num		= get_block_setting($block_id, 'num', 10);
		$infoStyle	= get_block_setting($block_id, 'infoStyle', 'table');
		$block		= get_block_setting($block_id, 'block', false);
		$config		= true;

		if ($cfg) {
			foreach (array('num', 'infoStyle', 'block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name = $cfg[$name];
				}
			}
		}

		// This next function is a bit out of date, and doesn't cope well with surname variants
		$top_surnames = get_top_surnames(KT_GED_ID, $COMMON_NAMES_THRESHOLD, $num);

		// Remove names found in the "Remove Names" list
		if ($COMMON_NAMES_REMOVE) {
			foreach (preg_split("/[,; ]+/", $COMMON_NAMES_REMOVE) as $delname) {
				unset($top_surnames[$delname]);
				unset($top_surnames[utf8_strtoupper($delname)]);
			}
		}

		$all_surnames = array();
		$i = 0;
		foreach (array_keys($top_surnames) as $top_surname) {
			$all_surnames = array_merge($all_surnames, KT_Query_Name::surnames($top_surname, '', false, false, KT_GED_ID));
			if (++$i == $num) break;
		}
		if ($i < $num) $num = $i;

		$id		= $this->getName() . $block_id;
		$class	= $this->getName();
		$subtitle		= '';

		if ($num == 1) {
			// I18N: i.e. most popular surname.
			$title = KT_I18N::translate('Top surname');
		} else {
			// I18N: Title for a list of the most common surnames, %s is a number.  Note that a separate translation exists when %s is 1
			$title = KT_I18N::plural('Top %s surname', 'Top %s surnames', $num, KT_I18N::number($num));
		}

		$content = '<div class="grid-x">
			<div class="cell">';
				//Select List or Table
				switch ($infoStyle) {
					case 'tagcloud':
						uksort($all_surnames,'utf8_strcasecmp');
						$content = format_surname_tagcloud($all_surnames, 'indilist.php', true);
					break;
					case 'list':
						uasort($all_surnames,array($this->getName() . '_KT_Module', 'top_surname_sort'));
						$content = format_surname_list($all_surnames, '1', true, 'indilist.php');
					break;
					case 'array':
						uasort($all_surnames,array($this->getName() . '_KT_Module', 'top_surname_sort'));
						$content = format_surname_list($all_surnames, '2', true, 'indilist.php');
					break;
					case 'table':
					default:
						uasort($all_surnames, array($this->getName() . '_KT_Module', 'top_surname_sort'));
						$content = format_surname_table($all_surnames, 'indilist.php', '2');
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
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'num',       KT_Filter::postInteger('num', 1, 10000, 10));
			set_block_setting($block_id, 'infoStyle', KT_Filter::post('infoStyle', 'list|array|table|tagcloud', 'table'));
			set_block_setting($block_id, 'block',     KT_Filter::postBool('block'));
			exit;
		}

		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		?>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Number of items to show'); ?></label>
		</div>
		<div class="cell medium-7">
			<input type="text" name="num" value="<?php echo get_block_setting($block_id, 'num', 10); ?>">
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Presentation style'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo select_edit_control(
				'infoStyle',
				array('list' => KT_I18N::translate('bullet list'),
				'array' => KT_I18N::translate('compact list'),
				'table' => KT_I18N::translate('table'),
				'tagcloud' => KT_I18N::translate('tag cloud')),
				null,
				get_block_setting($block_id, 'infoStyle', 'table'),
				''
			); ?>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Add a scrollbar when block contents grow'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('block', get_block_setting($block_id, 'block', false)); ?>
		</div>
		<hr>

	<?php }

	public static function top_surname_sort($a, $b) {
		$counta = 0;
		foreach ($a as $x) {
			$counta += count($x);
		}
		$countb = 0;
		foreach ($b as $x) {
			$countb += count($x);
		}
		return $countb - $counta;
	}
}
