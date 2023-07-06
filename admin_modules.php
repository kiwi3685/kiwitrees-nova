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

define('KT_SCRIPT_NAME', 'admin_modules.php');
require 'includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Module administration'));

$modules = KT_Module::getInstalledModules('disabled');

$module_status = KT_DB::prepare("SELECT module_name, status FROM `##module`")->fetchAssoc();

switch (KT_Filter::post('action')) {
	case 'update_mods':
		if (KT_Filter::checkCsrf()) {
			foreach ($modules as $module_name=>$status) {
				$new_status = KT_Filter::post("status-{$module_name}", '[01]');
				if ($new_status !== null) {
					$new_status = $new_status ? 'enabled' : 'disabled';
					if ($new_status != $status) {
						KT_DB::prepare("UPDATE `##module` SET status=? WHERE module_name=?")->execute(array($new_status, $module_name));
						$module_status[$module_name] = $new_status;
					}
				}
			}
		}
		header('Location: admin_modules.php');
	break;
}

switch (KT_Filter::get('action')) {
	case 'delete_module':
		$module_name = KT_Filter::get('module_name');
		KT_DB::prepare(
			"DELETE `##block_setting`".
			" FROM `##block_setting`".
			" JOIN `##block` USING (block_id)".
			" JOIN `##module` USING (module_name)".
			" WHERE module_name=?"
		)->execute(array($module_name));
		KT_DB::prepare(
			"DELETE `##block`".
			" FROM `##block`".
			" JOIN `##module` USING (module_name)".
			" WHERE module_name=?"
		)->execute(array($module_name));
		KT_DB::prepare("DELETE FROM `##module_setting` WHERE module_name=?")->execute(array($module_name));
		KT_DB::prepare("DELETE FROM `##module_privacy` WHERE module_name=?")->execute(array($module_name));
		KT_DB::prepare("DELETE FROM `##module`         WHERE module_name=?")->execute(array($module_name));
		unset($modules[$module_name]);
		unset($module_status[$module_name]);
		break;
}

$controller
	->pageHeader()
	->addExternalJavascript(KT_DATATABLES_JS)
	->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
	->addExternalJavascript(KT_DATATABLES_BUTTONS)
	->addExternalJavascript(KT_DATATABLES_HTML5)
	->addInlineJavascript('
		function reindexMods(id) {
			jQuery("#"+id+" input").each(
				function (index, value) {
					value.value = index+1;
				});
		}

	  	jQuery("#installed_table").dataTable({
			dom: \'<"top"pBf<"clear">irl>t<"bottom"pl>\',
			' . KT_I18N::datatablesI18N() . ',
			buttons: [{extend: "csvHtml5"}],
			autoWidth: false,
			processing: true,
			pagingType: "full_numbers",
			stateSave: true,
			stateDuration: -1,
			sorting: [[ 3, "asc" ]],
			columns : [
				/*  0 enable		*/ { dataSort: 1, sClass: "center" },
				/*  1 status		*/ { type: "unicode", visible: false },
				/*	2 config		*/ { sType: "html"},
				/*  3 name			*/ { sType: "html"},
				/*  4 description	*/ null,
				/*  5 block        	*/ { sClass: "center" },
				/*  6 chart			*/ { sClass: "center" },
				/*  7 footer		*/ { sClass: "center" },
				/*  8 list			*/ { sClass: "center" },
				/*  9 menu			*/ { sClass: "center" },
				/* 10 report		*/ { sClass: "center" },
				/* 11 sidebar		*/ { sClass: "center" },
				/* 12 indi-tab		*/ { sClass: "center" },
				/* 13 widget		*/ { sClass: "center" },
				/* 14 fam-tab		*/ { sClass: "center" }
			]
		});
	');

echo relatedPages($module_config, KT_SCRIPT_NAME);

echo pageStart('manage_modules', $controller->getPageTitle()); ?>

	<form class="cell" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
			<input type="hidden" name="action" value="update_mods">
			<?php echo KT_Filter::getCsrf(); ?>
			<div class="grid-x grid-margin-y">
				<div class="cell">
					<table id="installed_table" class="scroll stack">
						<thead>
							<tr>
								<th><?php echo KT_I18N::translate('Enabled'); ?></th>
								<th>STATUS</th>
								<th style="width: 7.5rem;"><?php echo KT_I18N::translate('Module'); ?></th>
								<th style="width: 2rem;"><i class="<?php echo $iconStyle; ?> fa-gears"></i></th>
								<th style="width: 18rem;"><?php echo KT_I18N::translate('Description'); ?></th>
								<th class="hide-for-small-only"><?php echo KT_I18N::translate('Block'); ?></th>
								<th class="hide-for-small-only"><?php echo KT_I18N::translate('Chart'); ?></th>
								<th class="hide-for-small-only"><?php echo KT_I18N::translate('Footer'); ?></th>
								<th class="hide-for-small-only"><?php echo KT_I18N::translate('List'); ?></th>
								<th class="hide-for-small-only"><?php echo KT_I18N::translate('Menu'); ?></th>
								<th class="hide-for-small-only"><?php echo KT_I18N::translate('Report'); ?></th>
								<th class="hide-for-small-only"><?php echo KT_I18N::translate('Sidebar'); ?></th>
								<th class="hide-for-small-only"><?php echo KT_I18N::translate('Indi tab'); ?></th>
								<th class="hide-for-small-only"><?php echo KT_I18N::translate('Widget'); ?></th>
								<th class="hide-for-small-only"><?php echo KT_I18N::translate('Fam tab'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($module_status as $module_name => $status) {
								if (array_key_exists($module_name, $modules)) {
									$module = $modules[$module_name];
									echo
										'<tr>
											<td>' . two_state_checkbox('status-' . $module_name, $status == 'enabled') . '</td>
											<td>' . $status . '</td>
											<td>' . $module->getTitle() . '</td>
											<td>';
												if ( $module instanceof KT_Module_Config ) {
													echo '<a href="' . $module->getConfigLink() . '">';
												}
												if ( $module instanceof KT_Module_Config && array_key_exists( $module_name, KT_Module::getActiveModules() ) ) {
													echo ' <i class="' . $iconStyle . ' fa-gears"></i></a>';
												}
											echo '</td>
											<td>' . $module->getDescription() . '</td>
											<td class="hide-for-small-only">', $module instanceof KT_Module_Block   	? ($module->isGedcomBlock() ? KT_I18N::translate('Home') : KT_I18N::translate('Other')) : '-', '</td>
											<td class="hide-for-small-only">', $module instanceof KT_Module_Chart   	? KT_I18N::translate('Chart') : '-', '</td>
											<td class="hide-for-small-only">', $module instanceof KT_Module_Footer   	? KT_I18N::translate('Footer') : '-', '</td>
											<td class="hide-for-small-only">', $module instanceof KT_Module_List   		? KT_I18N::translate('List') : '-', '</td>
											<td class="hide-for-small-only">', $module instanceof KT_Module_Menu    	? KT_I18N::translate('Menu') : '-', '</td>
											<td class="hide-for-small-only">', $module instanceof KT_Module_Report  	? KT_I18N::translate('Report') : '-', '</td>
											<td class="hide-for-small-only">', $module instanceof KT_Module_Sidebar 	? KT_I18N::translate('Sidebar') : '-', '</td>
											<td class="hide-for-small-only">', $module instanceof KT_Module_IndiTab     ? KT_I18N::translate('Indi tab') : '-', '</td>
											<td class="hide-for-small-only">', $module instanceof KT_Module_Widget  	? KT_I18N::translate('Widget') : '-', '</td>
											<td class="hide-for-small-only">', $module instanceof KT_Module_FamTab      ? KT_I18N::translate('Fam tab') : '-', '</td>
										</tr>
									';
								} else {
									// Module can't be found on disk?
									// Don't delete it automatically.  It may be temporarily missing, after a re-installation, etc.
									echo
										'<tr>
											<td></td>
											<td></td>
											<td class="error">' . $module_name . '</td>
											<td></td>
											<td>
												<a class="error" href="' . KT_SCRIPT_NAME . '?action=delete_module&amp;module_name=' . $module_name .'">' .
													KT_I18N::translate('This module cannot be found.  Delete its configuration settings.') .
												'</a>
											</td>
											<td class="hide-for-small-only"></td>
											<td class="hide-for-small-only"></td>
											<td class="hide-for-small-only"></td>
											<td class="hide-for-small-only"></td>
											<td class="hide-for-small-only"></td>
											<td class="hide-for-small-only"></td>
											<td class="hide-for-small-only"></td>
											<td class="hide-for-small-only"></td>
											<td class="hide-for-small-only"></td>
											<td class="hide-for-small-only"></td>
										</tr>';
								}
							}
							?>
						</tbody>
					</table>
				</div>
				<?php echo singleButton('', 1); ?>
			</div>
		</form>

<?php echo pageClose();
