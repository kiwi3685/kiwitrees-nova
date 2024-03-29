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

class block_upcoming_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Upcoming events');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Upcoming events” module */ KT_I18N::translate('A list of the anniversaries that will occur in the near future.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $config = null) {
		global $KT_TREE, $iconStyle;

		require_once KT_ROOT.'includes/functions/functions_print_lists.php';

		$days		= get_block_setting($block_id, 'days',      7);
		$filter		= get_block_setting($block_id, 'filter',    true);
		$onlyBDM	= get_block_setting($block_id, 'onlyBDM',   false);
		$infoStyle	= get_block_setting($block_id, 'infoStyle', 'table');
		$sortStyle	= get_block_setting($block_id, 'sortStyle', 'alpha');
		$block		= get_block_setting($block_id, 'block',     true);
		if ($config) {
			foreach (array('days', 'filter', 'onlyBDM', 'infoStyle', 'sortStyle', 'block') as $name) {
				if (array_key_exists($name, $config)) {
					$$name = $config[$name];
				}
			}
		}

		$startjd	= KT_CLIENT_JD+1;
		$endjd  	= KT_CLIENT_JD+$days;

		// Output starts here
		$id			= $this->getName().$block_id;
		$class		= $this->getName().'_block';
		$config		= true;
		$title		= $this->getTitle();
		$subtitle		= '';

		$content = '';
		switch ($infoStyle) {
		case "list":
			// Output style 1:  Old format, no visible tables, much smaller text.  Better suited to right side of page.
			$content .= print_events_list($startjd, $endjd, $onlyBDM?'BIRT MARR DEAT':'', $filter, $sortStyle);
			break;
		case "table":
			// Style 2: New format, tables, big text, etc.  Not too good on right side of page
			ob_start();
			$content .= print_events_table($startjd, $endjd, $onlyBDM?'BIRT MARR DEAT':'', $filter, $sortStyle);
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
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'days',      KT_Filter::postInteger('days', 1, 30, 7));
			set_block_setting($block_id, 'filter',    KT_Filter::postBool('filter'));
			set_block_setting($block_id, 'onlyBDM',   KT_Filter::postBool('onlyBDM'));
			set_block_setting($block_id, 'infoStyle', KT_Filter::post('infoStyle', 'list|table', 'table'));
			set_block_setting($block_id, 'sortStyle', KT_Filter::post('sortStyle', 'alpha|anniv', 'alpha'));
			set_block_setting($block_id, 'block',     KT_Filter::postBool('block'));
			exit;
		}

		require_once KT_ROOT . 'includes/functions/functions_edit.php';

		$days		= get_block_setting($block_id, 'days', 7);
		$filter		= get_block_setting($block_id, 'filter',     true);
		$onlyBDM	= get_block_setting($block_id, 'onlyBDM',    false);
		$infoStyle	= get_block_setting($block_id, 'infoStyle', 'table');
		$sortStyle	= get_block_setting($block_id, 'sortStyle',  'alpha');
		$block		= get_block_setting($block_id, 'block', true);

		?>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Number of days to show'); ?></label>
		</div>
		<div class="cell medium-7">
			<input type="text" name="days" size="2" value="<?php echo $days; ?>">
			<div class="help"><?php echo KT_I18N::plural('maximum %d day', 'maximum %d days', 30, 30); ?></div>
		</div>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Show only events of living people?'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('filter', $filter); ?>
		</div>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Show only Births, Deaths, and Marriages?'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('onlyBDM', $onlyBDM); ?>
		</div>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Presentation style'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo select_edit_control(
				'infoStyle',
				array(
					'list'=>KT_I18N::translate('list'),
					'table'=>KT_I18N::translate('table')),
					null,
					$infoStyle,
					''
				)
			; ?>
		</div>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Sort order'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo select_edit_control('sortStyle', array(
				/* I18N: An option in a list-box */ 'alpha'=>KT_I18N::translate('sort by name'),
				/* I18N: An option in a list-box */ 'anniv'=>KT_I18N::translate('sort by date')
			), null, $sortStyle, ''); ?>
		</div>
		<div class="cell medium-5">
			 <label><?php echo /* I18N: label for a yes/no option */ KT_I18N::translate('Add a scrollbar when block contents grow'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('block', $block); ?>
		</div>

		<?php
	}
}
