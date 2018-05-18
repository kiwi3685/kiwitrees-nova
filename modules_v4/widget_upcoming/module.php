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

class widget_upcoming_KT_Module extends KT_Module implements KT_Module_Widget {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Upcoming events');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Upcoming events” module */ KT_I18N::translate('A list of the anniversaries that will occur in the near future.');
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement class KT_Module_Widget
	public function getWidget($widget_id, $template=true, $cfg=null) {

		require_once KT_ROOT.'includes/functions/functions_print_lists.php';

		$days		= get_block_setting($widget_id, 'days',			7);
		$filter		= get_block_setting($widget_id, 'filter',		true);
		$onlyBDM	= get_block_setting($widget_id, 'onlyBDM',		false);
		$infoStyle	= get_block_setting($widget_id, 'infoStyle',	'table');
		$sortStyle	= get_block_setting($widget_id, 'sortStyle',	'alpha');
		if ($cfg) {
			foreach (array('days', 'filter', 'onlyBDM', 'infoStyle', 'sortStyle', 'widget') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name = $cfg[$name];
				}
			}
		}

		$startjd	= KT_CLIENT_JD + 1;
		$endjd		= KT_CLIENT_JD + $days;
		$id			= $this->getName();
		$class		= $this->getName();
		$title		= $this->getTitle();
		$content	= '';
		switch ($infoStyle) {
		case "list":
			// Output style 1:  Old format, no visible tables, much smaller text.
			$content.=print_events_list($startjd, $endjd, $onlyBDM?'BIRT MARR DEAT':'', $filter, $sortStyle);
			break;
		case "table":
			// Style 2: New format, tables, big text, etc.
			ob_start();
			$content.=print_events_table($startjd, $endjd, $onlyBDM?'BIRT MARR DEAT':'', $filter, $sortStyle);
			$content.=ob_get_clean();
			break;
		}

		if ($template) {
			require KT_THEME_DIR.'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Implement class KT_Module_Widget
	public function loadAjax() {
		return false;
	}

	// Implement KT_Module_Widget
	public function defaultWidgetOrder() {
		return 30;
	}


	// Implement class KT_Module_Widget
	public function configureBlock($widget_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($widget_id, 'days',      KT_Filter::postInteger('days', 1, 30, 7));
			set_block_setting($widget_id, 'filter',    KT_Filter::postBool('filter'));
			set_block_setting($widget_id, 'onlyBDM',   KT_Filter::postBool('onlyBDM'));
			set_block_setting($widget_id, 'infoStyle', KT_Filter::post('infoStyle', 'list|table', 'table'));
			set_block_setting($widget_id, 'sortStyle', KT_Filter::post('sortStyle', 'alpha|anniv', 'alpha'));
			exit;
		}

		require_once KT_ROOT . 'includes/functions/functions_edit.php';

		$days = get_block_setting($widget_id, 'days', 7);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Number of days to show');
		echo '</td><td class="optionbox">';
		echo '<input type="text" name="days" size="2" value="', $days, '">';
		echo ' <em>', KT_I18N::plural('maximum %d day', 'maximum %d days', 30, 30) ,'</em>';
		echo '</td></tr>';

		$filter = get_block_setting($widget_id, 'filter', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Show only events of living people?');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('filter', $filter);
		echo '</td></tr>';

		$onlyBDM = get_block_setting($widget_id, 'onlyBDM', false);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Show only Births, Deaths, and Marriages?');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('onlyBDM', $onlyBDM);
		echo '</td></tr>';

		$infoStyle = get_block_setting($widget_id, 'infoStyle', 'table');
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Presentation style');
		echo '</td><td class="optionbox">';
		echo select_edit_control('infoStyle', array('list'=>KT_I18N::translate('list'), 'table'=>KT_I18N::translate('table')), null, $infoStyle, '');
		echo '</td></tr>';

		$sortStyle = get_block_setting($widget_id, 'sortStyle',  'alpha');
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Sort order');
		echo '</td><td class="optionbox">';
		echo select_edit_control('sortStyle', array(
			/* I18N: An option in a list-box */ 'alpha'=>KT_I18N::translate('sort by name'),
			/* I18N: An option in a list-box */ 'anniv'=>KT_I18N::translate('sort by date')
		), null, $sortStyle, '');
		echo '</td></tr>';
	}
}
