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

define('KT_SCRIPT_NAME', 'admin_users_settings.php');
require './includes/session.php';
include KT_THEME_URL . 'templates/adminData.php';

global $iconStyle;

$gedID = KT_Filter::post('gedID') ? KT_Filter::post('gedID') : KT_GED_ID;
$tree  = KT_Tree::get($gedID);

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('List user settings per tree'))
	->pageHeader()
	->addExternalJavascript(KT_DATATABLES_JS)
	->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
	->addExternalJavascript(KT_DATATABLES_BUTTONS)
	->addExternalJavascript(KT_DATATABLES_HTML5)
	->addInlineJavascript('
		jQuery("#list").dataTable({
			dom: \'<"top"pBf<"clear">irl>t<"bottom"pl>\',
			' . KT_I18N::datatablesI18N() . ',
			buttons: [{extend: "csvHtml5", exportOptions: {columns: [1,2,3,4,5,6] }}],
			autoWidth: false,
			pagingType: "full_numbers",
			stateSave: true,
			stateSaveParams: function (settings, data) {
				data.columns.forEach(function(column) {
					delete column.search;
				});
			},
			stateDuration: -1,
			sorting: [[0,"asc"]],
			columns: [
				/*  0 user_id       */ { visible:false  },
				/*  1 user_name     */ null,
				/*  2 real_name     */ null,
				/*  3 user_canedit  */ null,
				/*  4 user_gedcomid */ null,
				/*  5 user_rootid   */ null,
				/*  6 user_relPath  */ null,
			],
		})
	');

echo relatedPages($users, KT_SCRIPT_NAME);

echo pageStart('admin_users_list', $controller->getPageTitle()); ?>

	<div class="cell">

		<div class="grid-x grid-margin-x">
			<div class="cell callout info-help">
				<?php echo KT_I18N::translate('User real names shown in red are site administrators'); ?>
			</div>
			<div class="cell medium-2">
				<label for="gedID"><?php echo KT_I18N::translate('Family tree'); ?></label>
			</div>
			<div class="cell medium-4">
				<form id="tree" method="post" action="#" name="tree">
					<?php echo select_ged_control('gedID', KT_Tree::getIdList(), null, $gedID, ' onchange="tree.submit();"'); ?>
				</form>
			</div>
			<div class="cell medium-6"></div>

			<div class="cell">
				<table id="list">
					<thead>
						<tr>
							<th>user_id</th>
							<th><?php echo KT_I18N::translate('Username'); ?></th>
							<th><?php echo KT_I18N::translate('Real name'); ?></th>
							<th><?php echo KT_I18N::translate('Role'); ?></th>
							<th><?php echo KT_I18N::translate('Default individual'); ?></th>
							<th><?php echo KT_I18N::translate('Individual record'); ?></th>
							<th><?php echo KT_I18N::translate('Restrict to close family'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach (get_all_users() as $user_id => $username) {
							$user_realname = getUserFullName($user_id);
							$user_canedit  = $tree->userPreference($user_id, 'canedit');
							$userClass     = 'class="' . $user_canedit . '"';
							$adminClass    =  (get_user_setting($user_id, 'canadmin') ? 'class="administrator"' : ''); ?>
							<tr>
								<td><?php // User ID - not displayed ?></td>
								<?php // User name ?>
								<td>
									<a
										href="?action=edit&amp;user_id=<?php echo $user_id; ?>"
										title="<?php echo  KT_I18N::translate('Edit user'); ?>"
									>
										<span dir="auto">
											<?php echo KT_Filter::escapeHtml($username); ?>
										</span>
									</a>
								</td>
								<?php // User real name ?>
								<td>
									<a
										href="?action=edit&amp;user_id=<?php echo $user_id; ?>"
										title="<?php echo  KT_I18N::translate('Edit user'); ?>"
									>
										<span dir="auto" <?php echo $adminClass; ?> >
											<?php echo KT_Filter::escapeHtml($user_realname) ; ?>
										</span>
									</a>
								</td>
								<?php // User role ?>
								<td>
									<span <?php echo $userClass; ?> >
										<?php echo $ALL_EDIT_OPTIONS[$tree->userPreference($user_id, 'canedit')]; ?>
									</span>
								</td>
								<?php // Pedigree root person - Default individual ?>
								<td>
									<?php $xref = $tree->userPreference($user_id, 'rootid');
									$rootID     = new KT_Person(find_gedcom_record($xref, $tree->tree_id, true));
									if ($xref) { ?>
										<a href="<?php echo $rootID->getHtmlUrl(); ?>">
											<?php echo strip_tags($rootID->getLifespanName()); ?>
										</a>
									<?php } else {
										echo '';
									} ?>
								</td>
								<?php // GEDCOM INDI Record - Individual record ?>
								<td>
									<?php $xref = $tree->userPreference($user_id, 'gedcomid');
									$gedcomID   = new KT_Person(find_gedcom_record($xref, $tree->tree_id, true));
									if ($xref) { ?>
										<a href="<?php echo $gedcomID->getHtmlUrl(); ?>">
											<?php echo strip_tags($gedcomID->getLifespanName()); ?>
										</a>
									<?php } else {
										echo '';
									} ?>
								</td>
								<?php // Relationship path ?>
								<td>
									<?php echo ($tree->userPreference($user_id, 'RELATIONSHIP_PATH_LENGTH') == 0 ?
										KT_I18N::translate('No')
										:
										KT_I18N::plural('%s step away', '%s steps away', $user_relPath, $user_relPath)
									); ?>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>

	</div>

<?php echo pageClose();
