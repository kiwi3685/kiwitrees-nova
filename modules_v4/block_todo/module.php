<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

class block_todo_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module.  Tasks that need further research.  */ KT_I18N::translate('Research tasks');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Research tasks” module */ KT_I18N::translate('A list of tasks and activities that are linked to the family tree.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $config = null) {
		global $KT_TREE, $controller, $iconStyle;

		$show_unassigned	= get_block_setting($block_id, 'show_unassigned', true);
		$show_other			= get_block_setting($block_id, 'show_other',      true);
		$show_future		= get_block_setting($block_id, 'show_future',     true);
		$block				= get_block_setting($block_id, 'block',           true);

		if ($config) {
			foreach (array('show_unassigned', 'show_other', 'show_future', 'block') as $name) {
				if (array_key_exists($name, $config)) {
					$$name=$config[$name];
				}
			}
		}

		$id		= $this->getName() . $block_id;
		$class	= $this->getName();
		$config	= true;
		$title	= $this->getTitle();

		$table_id = 'ID'.(int)(microtime(true)*1000000); // create a unique ID
		$controller
			->addExternalJavascript(KT_DATATABLES_JS)
			->addInlineJavascript('
				jQuery("#' . $table_id . '").dataTable( {
					dom: \'t\',
					' . KT_I18N::datatablesI18N() . ',
					autoWidth: false,
					filter: false,
					lengthChange: false,
					info: true,
					paging: false,
					columns: [
						/* 0-DATE */   		{ visible: false },
						/* 1-Date */		{ dataSort: 0 },
						/* 1-Record */ 		{},
						/* 2-Username */	{ class: "show-for-large" },
						/* 3-Text */		{}
					]
				});

				jQuery("#' . $table_id . '").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			');

		$content ='
			<div class="grid-x">
				<div class="cell">
					<div class="cell align-center loading-image"><i class="' . $iconStyle . ' fa-spinner fa-spin fa-3x"></i><span class="sr-only">Loading...</span></div>
					<table id="' . $table_id . '" style="visibility:hidden; width:100%;">
						<thead>
							<tr>
								<th>DATE</th>
								<th>' . KT_Gedcom_Tag::getLabel('DATE') . '</th>
								<th>' . KT_I18N::translate('Record') . '</th>';
								if ($show_unassigned || $show_other) {
									$content .= '<th>' . KT_I18N::translate('Username') . '</th>';
								}
								$content .= '<th>' . KT_Gedcom_Tag::getLabel('TEXT') . '</th>
							</tr>
						</thead>
						<tbody>';
							$found	= false;
							$end_jd	= $show_future ? 99999999 : KT_CLIENT_JD;
							foreach (get_calendar_events(0, $end_jd, '_TODO', KT_GED_ID) as $todo) {
								$record = KT_GedcomRecord::getInstance($todo['id']);
								if ($record && $record->canDisplayDetails()) {
									$user_name = preg_match('/\n2 _KT_USER (.+)/', $todo['factrec'], $match) ? $match[1] : '';
									if ($user_name == KT_USER_NAME || !$user_name && $show_unassigned || $user_name && $show_other) {
										$content.='
											<tr>
												<td>' . $todo['date']->JD() . '</td>
												<td>' . $todo['date']->Display(empty($SEARCH_SPIDER)) . '</td>
												<td><a href="' . $record->getHtmlUrl() . '">' . $record->getFullName() . '</a></td>';
												if ($show_unassigned || $show_other) {
													$content .= '<td>' . $user_name . '</td>';
												}
												$text = preg_match('/^1 _TODO (.+)/', $todo['factrec'], $match) ? $match[1] : '';
												$content .= '<td>' . $text . '</td>
											</tr>
										';
										$found = true;
									}
								}
							}
						$content .= '</tbody>
					</table>
				</div>
			</div>
		';

		if (!$found) {
			$content.='<div class="callout alert">'.KT_I18N::translate('There are no research tasks in this family tree.').'</div>';
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
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'show_other',      KT_Filter::postBool('show_other'));
			set_block_setting($block_id, 'show_unassigned', KT_Filter::postBool('show_unassigned'));
			set_block_setting($block_id, 'show_future',     KT_Filter::postBool('show_future'));
			set_block_setting($block_id, 'block',           KT_Filter::postBool('block'));
			exit;
		}

		$show_other			= get_block_setting($block_id, 'show_other', true);
		$show_unassigned	= get_block_setting($block_id, 'show_unassigned', true);
		$show_future		= get_block_setting($block_id, 'show_future', true);
		$block				= get_block_setting($block_id, 'block', true);

		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		?>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Show research tasks that are assigned to other users'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('show_other', $show_other); ?>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Show research tasks that are not assigned to any user'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('show_unassigned', $show_unassigned); ?>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Show research tasks that have a date in the future'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('show_future', $show_future); ?>
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
