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

class block_today_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('On this day');
	}

	// Extend class KT_Module
	public /* I18N: Description of the “On This Day” module */ function getDescription() {
		return KT_I18N::translate('A list of the anniversaries that occur today.');
	}

	// Extend class KT_Module_Block
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $KT_TREE, $iconStyle;

		require_once KT_ROOT.'includes/functions/functions_print_lists.php';

		$filter		= get_block_setting($block_id, 'filter',   true);
		$onlyBDM	= get_block_setting($block_id, 'onlyBDM',  true);
		$infoStyle	= get_block_setting($block_id, 'infoStyle','table');
		$sortStyle	= get_block_setting($block_id, 'sortStyle','alpha');
		$block		= get_block_setting($block_id, 'block',    true);

		if ($cfg) {
			foreach (array('filter', 'onlyBDM', 'infoStyle', 'sortStyle', 'block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name = $cfg[$name];
				}
			}
		}

		$todayjd	= KT_CLIENT_JD;
		$id			= $this->getName() . $block_id;
		$class		= $this->getName();
		$config		= true;
		$title 		= $this->getTitle();
		$content	= '';

		switch ($infoStyle) {
		case 'list':
			// Output style 1:  Old format, no visible tables, much smaller text.  Better suited to small blocks.
			$content .=
				'<div class="grid-x">
					<div class="cell font-small">' .
						print_events_list($todayjd, $todayjd, $onlyBDM ? 'BIRT MARR DEAT' : '', $filter, $sortStyle) . '
					</div>
				<div>';
			break;
		case 'table':
			// Style 2: New format, tables, larger text, etc.  Not suitable for small blocks
			ob_start();
			$content .=
				'<div class="grid-x">
					<div class="cell">' .
						print_events_table($todayjd, $todayjd, $onlyBDM ? 'BIRT MARR DEAT' : '', $filter, $sortStyle) . '
					</div>
				<div>';
			$content .= ob_get_clean();
			break;
		}

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
			set_block_setting($block_id, 'filter',    KT_Filter::postBool('filter'));
			set_block_setting($block_id, 'onlyBDM',   KT_Filter::postBool('onlyBDM'));
			set_block_setting($block_id, 'infoStyle', KT_Filter::post('infoStyle', 'list|table', 'table'));
			set_block_setting($block_id, 'sortStyle', KT_Filter::post('sortStyle', 'alpha|anniv', 'alpha'));
			set_block_setting($block_id, 'block',     KT_Filter::postBool('block'));
			exit;
		}

		$filter		= get_block_setting($block_id, 'filter', true);
		$onlyBDM	= get_block_setting($block_id, 'onlyBDM', true);
		$infoStyle	= get_block_setting($block_id, 'infoStyle', 'table');
		$sortStyle	= get_block_setting($block_id, 'sortStyle',  'alpha');
		$block		= get_block_setting($block_id, 'block', true);

		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		?>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Show only events of living people?'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('filter', $filter); ?>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Show only Births, Deaths, and Marriages?'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('onlyBDM', $onlyBDM); ?>
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
			 <label><?php echo KT_I18N::translate('Sort order'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo select_edit_control('sortStyle', array(
				/* I18N: An option in a list-box */ 'alpha'=>KT_I18N::translate('sort by name'),
				/* I18N: An option in a list-box */ 'anniv'=>KT_I18N::translate('sort by date'
			)), null, $sortStyle, ''); ?>
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
