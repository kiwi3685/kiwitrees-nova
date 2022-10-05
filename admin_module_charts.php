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

define('KT_SCRIPT_NAME', 'admin_module_charts.php');
require 'includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';

global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Chart menu items administration'))
	->pageHeader();

$modules	= KT_Module::getActiveCharts(KT_GED_ID, KT_PRIV_HIDE);
$action		= KT_Filter::post('action');
$menuItem	= KT_I18N::translate('Charts');

if ($action == 'update_mods' && KT_Filter::checkCsrf()) {
	foreach ($modules as $module_name=>$module) {
		foreach (KT_Tree::getAll() as $tree) {
			$value = KT_Filter::post("access-{$module_name}-{$tree->tree_id}", KT_REGEX_INTEGER, $module->defaultAccessLevel());
			KT_DB::prepare(
				"REPLACE INTO `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'chart', ?)"
			)->execute(array($module_name, $tree->tree_id, $value));
		}
	}
}

echo relatedPages($module_config, KT_SCRIPT_NAME);

echo pageStart('charts', $controller->getPageTitle()); ?>

	<form class="cell" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
			<input type="hidden" name="action" value="update_mods">
			<?php echo KT_Filter::getCsrf(); ?>
			<div class="grid-x show-for-medium">
				<div class="cell medium-10">
					<div class="cell callout warning helpcontent">
						<?php echo KT_I18N::translate('The order of these items under the main menu "%s" is fixed at alphabetical, based on the current display language in use.', $menuItem); ?>
						<?php echo KT_I18N::translate('The "Access level" setting "Hide from everyone" means exactly that, including Administrators.'); ?>
					</div>
				</div>
				<div class="cell medium-1 medium-offset-1 vertical">
					<button class="button" type="submit">
						<i class="<?php echo $iconStyle; ?> fa-save"></i>
						<?php echo KT_I18N::translate('Save'); ?>
					</button>
				</div>
			</div>
			<table id="charts_table" class="modules_table">
				<thead>
					<tr>
						<th><?php echo KT_I18N::translate('Chart'); ?></th>
						<th><?php echo KT_I18N::translate('Description'); ?></th>
						<th><?php echo KT_I18N::translate('Access level'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($modules as $module) {
						?>
						<tr>
							<td>
								<?php
								if ( $module instanceof KT_Module_Config ) {
									echo '<a href="', $module->getConfigLink(), '">';
								}
								echo $module->getTitle();
								if ( $module instanceof KT_Module_Config && array_key_exists($module->getName(), KT_Module::getActiveModules() ) ) {
									echo ' <i class="' . $iconStyle . ' fa-gears"></i></a>';
								}
								?>
							</td>
							<td>
								<?php echo $module->getDescription(); ?>
							</td>
							<td>
								<table class="modules_table2">
									<?php foreach (KT_Tree::getAll() as $tree) { ?>
										<tr>
											<td>
												<?php echo $tree->tree_title_html; ?>
											</td>
											<td>
												<?php
													$access_level = KT_DB::prepare(
														"SELECT access_level FROM `##module_privacy` WHERE gedcom_id=? AND module_name=? AND component='chart'"
													)->execute(array($tree->tree_id, $module->getName()))->fetchOne();
													if ($access_level === null) {
														$access_level = $module->defaultAccessLevel();
													}
													echo edit_field_access_level('access-' . $module->getName() . '-' . $tree->tree_id, $access_level);
												?>
											</td>
										</tr>
									<?php } ?>
								</table>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<button class="button" type="submit">
				<i class="<?php echo $iconStyle; ?> fa-save"></i>
				<?php echo KT_I18N::translate('Save'); ?>
			</button>
		</form>

<?php echo pageClose();
