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

/**
 * Layout template for administration of enabled modules
 *
 * @return void
 * @author 
 **/
function adminModules($modules, $component = '', $infoHelp = '', $pageTitle = '', $col1Header = '', $sortAble = false)
{

	require KT_ROOT . 'includes/functions/functions_edit.php';
	include KT_THEME_URL . 'templates/adminData.php';

	global $iconStyle;

	$action = KT_Filter::post('action');

	$infoHelp .= ' &nbsp' . KT_I18N::translate('Enable or disable modules on the "Module administration" page.');

	switch ($sortAble) {
		case true:
			if ($action == 'update_mods' && KT_Filter::checkCsrf()) {
				foreach ($modules as $module_name=>$module) {
					foreach (KT_Tree::getAll() as $tree) {
						$access_level = KT_Filter::post("access-{$module_name}-{$tree->tree_id}", KT_REGEX_INTEGER, $module->defaultAccessLevel());
						KT_DB::prepare(
							"REPLACE INTO `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'menu', ?)"
						)->execute(array($module_name, $tree->tree_id, $access_level));
					}
					$order = KT_Filter::post('order-'.$module_name);
					KT_DB::prepare(
						"UPDATE `##module` SET menu_order=? WHERE module_name=?"
					)->execute(array($order, $module_name));
					$module->order = $order; // Make the new order take effect immediately
				}
				uasort($modules, function ($x, $y) {
					return $x->order <=> $y->order;
				});

			}
			break;
		case false :
			if ($action == 'update_mods' && KT_Filter::checkCsrf()) {
				foreach ($modules as $module_name => $module) {
					foreach (KT_Tree::getAll() as $tree) {
						$value = KT_Filter::post("access-{$module_name}-{$tree->tree_id}", KT_REGEX_INTEGER, $module->defaultAccessLevel());
						KT_DB::prepare(
							"REPLACE INTO `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, $component, ?)"
						)->execute(array($module_name, $tree->tree_id, $value));
					}
				}
			}
			break;
	}

	echo relatedPages($module_config, KT_SCRIPT_NAME);

	echo pageStart('module_' . $component, $pageTitle); ?>

		<form class="cell" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
			<input type="hidden" name="action" value="update_mods">
			<?php echo KT_Filter::getCsrf(); ?>
			<div class="grid-x">
				<div class="cell medium-10">
					<div class="cell callout info-help summary">
						<?php echo $infoHelp; ?>
					</div>
				</div>
				<div class="cell medium-1 medium-offset-1 vertical">
					<?php echo singleButton(); ?>
				</div>
				<table id="<?php echo $component . 'Table'; ?>" class="cell modules_table">
					<thead>
						<tr>
							<?php switch ($sortAble) {
								case true: ?>
									<th colspan="2"><?php echo $col1Header; ?></th>
									<th class="show-for-medium"><?php echo KT_I18N::translate('Description'); ?></th>
									<th class="order"><?php echo KT_I18N::translate('Order'); ?></th>
									<th><?php echo KT_I18N::translate('Access level'); ?></th>
									<?php break;
								case false : ?>
									<th><?php echo $col1Header; ?></th>
									<th class="show-for-medium"><?php echo KT_I18N::translate('Description'); ?></th>
									<th><?php echo KT_I18N::translate('Access level'); ?></th>
									<?php break;
							} ?>
						</tr>
					</thead>
					<tbody>
						<?php
						$order = 1;
						foreach ($modules as $module) { ?>

							<?php switch ($sortAble) {
								case true: ?>
									<tr class="sortme">
										<td>
											<i class="<?php echo $iconStyle; ?> fa-bars"></i>
										</td>
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
										<td class="show-for-medium">
											<?php echo $module->getDescription(); ?>
										</td>
										<td>
											<input type="number" size="3" value="<?php echo $order; ?>" name="order-<?php echo $module->getName(); ?>">
										</td>
									<?php break;
								case false : ?>
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
										<td class="show-for-medium">
											<?php echo $module->getDescription(); ?>
										</td>
									<?php break;
							} ?>

								<td>
									<?php foreach (KT_Tree::getAll() as $tree) { ?>
										<div class="grid-x grid-padding-x">
											<div class="cell medium-6">
												<label for="access-<?php echo $module->getName(); ?>" class="accessLevel"><?php echo $tree->tree_title_html; ?></label>
											</div>
											<div class="cell medium-6">
												<?php $access_level = KT_DB::prepare(
													"SELECT access_level FROM `##module_privacy` WHERE gedcom_id = ? AND module_name = ? AND component = ?"
												)->execute(array($tree->tree_id, $module->getName(), $component))->fetchOne();
												if ($access_level === null) {
													$access_level = $module->defaultAccessLevel();
												}
												echo edit_field_access_level('access-' . $module->getName() . '-' . $tree->tree_id, $access_level); ?>
											</div>
										</div>
									<?php } ?>
								</td>
							</tr>
							<?php $order ++;
						} ?>
					</tbody>
				</table>
			</div>
			<?php echo singleButton(); ?>
		</form>

	<?php echo pageClose();

}
