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

class block_recent_changes_KT_Module extends KT_Module implements KT_Module_Block {
	const DEFAULT_DAYS = 7;
	const MAX_DAYS = 90;

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Recent changes');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Recent changes” module */ KT_I18N::translate('A list of records that have been updated recently.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $KT_TREE, $iconStyle;
		require_once KT_ROOT.'includes/functions/functions_print_lists.php';

		$days		= get_block_setting($block_id, 'days', self::DEFAULT_DAYS);
		$infoStyle	= get_block_setting($block_id, 'infoStyle', 'table');
		$sortStyle	= get_block_setting($block_id, 'sortStyle', 'date_desc');
		$hide_empty	= get_block_setting($block_id, 'hide_empty', false);
		$block		= get_block_setting($block_id, 'block', true);

		if ($cfg) {
			foreach (array('days', 'infoStyle', 'show_parents', 'sortStyle', 'hide_empty', 'block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name = $cfg[$name];
				}
			}
		}

		$found_facts = get_recent_changes(KT_CLIENT_JD - $days);

		if (!$found_facts && $hide_empty) {
			return '';
		}

		// Print block header
		$id			= $this->getName() . $block_id;
		$class		= $this->getName();
		$config		= true;
		$title		= $this->getTitle();
		$subtitle	= '';
		$content	= '';

		// Print block content
		if (count($found_facts) == 0) {
      		$content .= '<div class="callout small secondary">' . KT_I18N::translate('There have been no changes within the last %s days.', KT_I18N::number($days)) . '</div>';
		} else {
			ob_start();
			switch ($infoStyle) {
				case 'list':
					$content .= print_changes_list($found_facts, $sortStyle);
					break;
				case 'table':
					// sortable table
					$content .= print_changes_table($found_facts, $sortStyle);
					break;
			}
			$content .= ob_get_clean();
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
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'days',       KT_Filter::postInteger('days', 1, self::MAX_DAYS, self::DEFAULT_DAYS));
			set_block_setting($block_id, 'infoStyle',  KT_Filter::post('infoStyle', 'list|table', 'table'));
			set_block_setting($block_id, 'sortStyle',  KT_Filter::post('sortStyle', 'name|date_asc|date_desc', 'date_desc'));
			set_block_setting($block_id, 'hide_empty', KT_Filter::postBool('hide_empty'));
			set_block_setting($block_id, 'block',      KT_Filter::postBool('block'));
			exit;
		}

		$days		= get_block_setting($block_id, 'days', self::DEFAULT_DAYS);
		$infoStyle	= get_block_setting($block_id, 'infoStyle', 'table');
		$sortStyle	= get_block_setting($block_id, 'sortStyle', 'date');
		$block		= get_block_setting($block_id, 'block', true);
		$hide_empty	= get_block_setting($block_id, 'hide_empty', true);

		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		?>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Number of days to show'); ?></label>
		</div>
		<div class="cell medium-7">
			<input type="text" name="days" size="2" value="<?php echo $days; ?>">
			<em><?php echo KT_I18N::plural('maximum %d day', 'maximum %d days', self::MAX_DAYS, self::MAX_DAYS); ?></em>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Presentation style'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo select_edit_control('infoStyle', array('list' => KT_I18N::translate('list'), 'table' => KT_I18N::translate('table')), null, $infoStyle, ''); ?>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Sort order'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo select_edit_control('sortStyle', array(
				'name'      => /* I18N: An option in a list-box */ KT_I18N::translate('sort by name'),
				'date_asc'  => /* I18N: An option in a list-box */ KT_I18N::translate('sort by date, oldest first'),
				'date_desc' => /* I18N: An option in a list-box */ KT_I18N::translate('sort by date, newest first')
			), null, $sortStyle, ''); ?>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Add a scrollbar when block contents grow'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('block', $block); ?>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Should this block be hidden when it is empty?'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('hide_empty', $hide_empty); ?>
		</div>
		<div class="callout alert">
			 <label><?php echo KT_I18N::translate('If you hide an empty block, you will not be able to change its configuration until it becomes visible by no longer being empty.'); ?></label>
		</div>
		<hr>

	<?php }

}
