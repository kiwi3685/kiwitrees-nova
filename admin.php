<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin.php');

global $iconStyle;
require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Administration'))
	->pageHeader();

//Check for updates
$latest_version = fetch_latest_version();

//Check SQL server version
$version = KT_DB::prepare("select version()")->fetchColumn();

// Prepare statistic variables
$stats = new KT_Stats(KT_GEDCOM);
	$totusers	= 0;       // Total number of users
	$warnusers	= 0;       // Users with warning
	$applusers	= 0;       // Users who have not verified themselves
	$nverusers	= 0;       // Users not verified by admin but verified themselves
	$adminusers	= 0;       // Administrators
	$userlang	= array(); // Array for user languages
	$gedadmin	= array(); // Array for managers

// Server warnings
// Note that security support for 5.6 ends after security support for 7.0
$server_warnings = array();
if (
    // security
    PHP_VERSION_ID < 50600 ||
    PHP_VERSION_ID < 70000 && date('Y-m-d') >= '2018-12-31' ||
    PHP_VERSION_ID >= 70000 && PHP_VERSION_ID < 70100 && date('Y-m-d') >= '2018-12-03' ||
    PHP_VERSION_ID < 70200 && date('Y-m-d') >= '2019-12-01' ||
    PHP_VERSION_ID < 70300 && date('Y-m-d') >= '2020-11-30' ||
    PHP_VERSION_ID < 70400 && date('Y-m-d') >= '2021-12-06'
) {
	$server_warnings[] = '
		<span class="warning">' .
			KT_I18N::translate('Your web server is using PHP version %s, which is no longer receiving security updates.  You should insist your web service provider upgrades to a later version as soon as possible.', PHP_VERSION) . '
			<a href="https://www.php.net/supported-versions.php" target="_blank" rel="noopener noreferrer"><i class="icon-php"></i></a>
		<span>';
} elseif (
    // active support
	PHP_VERSION_ID < 50600 ||
	PHP_VERSION_ID < 70000 && date('Y-m-d') >= '2016-12-31' ||
	PHP_VERSION_ID < 70100 && date('Y-m-d') >= '2017-12-31' ||
    PHP_VERSION_ID < 70200 && date('Y-m-d') >= '2018-12-31' ||
    PHP_VERSION_ID < 70300 && date('Y-m-d') >= '2019-11-30' ||
    PHP_VERSION_ID < 70400 && date('Y-m-d') >= '2020-12-06'
) {
	$server_warnings[] = '
		<span class="accepted">' . KT_I18N::translate('Your web server is using PHP version %s, which is no longer maintained.  You should should ask your web service provider to upgrade to a later version.', PHP_VERSION) . '
		<a href="https://www.php.net/supported-versions.php" target="_blank" rel="noopener noreferrer"><i class="icon-php"></i></a>
		<span>';
}

// Total number of users
$total_users = count(get_all_users());

// Administrators
$administrators = KT_DB::prepare(
	"SELECT SQL_CACHE user_id, real_name FROM `##user` JOIN `##user_setting` USING (user_id) WHERE setting_name='canadmin' AND setting_value='1'"
)->fetchAll();

// Managers
$managers = KT_DB::prepare(
	"SELECT SQL_CACHE user_id, real_name FROM `##user` JOIN `##user_gedcom_setting` USING (user_id)" .
	" WHERE setting_name = 'canedit' AND setting_value='admin'" .
	" GROUP BY user_id, real_name" .
	" ORDER BY real_name"
)->fetchAll();

// Moderators
$moderators = KT_DB::prepare(
	"SELECT SQL_CACHE user_id, real_name FROM `##user` JOIN `##user_gedcom_setting` USING (user_id)" .
	" WHERE setting_name = 'canedit' AND setting_value='accept'" .
	" GROUP BY user_id, real_name" .
	" ORDER BY real_name"
)->fetchAll();

// Number of users who have not verified their email address
$unverified = KT_DB::prepare(
	"SELECT SQL_CACHE user_id, real_name FROM `##user` JOIN `##user_setting` USING (user_id)" .
	" WHERE setting_name = 'verified' AND setting_value = '0'" .
	" ORDER BY real_name"
)->fetchAll();

// Number of users whose accounts are not approved by an administrator
$unapproved = KT_DB::prepare(
	"SELECT SQL_CACHE user_id, real_name FROM `##user` JOIN `##user_setting` USING (user_id)" .
	" WHERE setting_name = 'verified_by_admin' AND setting_value = '0'" .
	" ORDER BY real_name"
)->fetchAll();

$incomplete = count(array_unique(array_merge($unverified, $unapproved), SORT_REGULAR));

// Users currently logged in
$logged_in = KT_DB::prepare(
	"SELECT SQL_NO_CACHE DISTINCT user_id, real_name FROM `##user` JOIN `##session` USING (user_id)" .
	" ORDER BY real_name"
)->fetchAll();

// Count of records
$individuals = KT_DB::prepare(
	"SELECT SQL_CACHE gedcom_id, COUNT(i_id) AS count FROM `##gedcom` LEFT JOIN `##individuals` ON gedcom_id = i_file GROUP BY gedcom_id"
)->fetchAssoc();
$families = KT_DB::prepare(
	"SELECT SQL_CACHE gedcom_id, COUNT(f_id) AS count FROM `##gedcom` LEFT JOIN `##families` ON gedcom_id = f_file GROUP BY gedcom_id"
)->fetchAssoc();
$sources = KT_DB::prepare(
	"SELECT SQL_CACHE gedcom_id, COUNT(s_id) AS count FROM `##gedcom` LEFT JOIN `##sources` ON gedcom_id = s_file GROUP BY gedcom_id"
)->fetchAssoc();
$media = KT_DB::prepare(
	"SELECT SQL_CACHE gedcom_id, COUNT(m_id) AS count FROM `##gedcom` LEFT JOIN `##media` ON gedcom_id = m_file GROUP BY gedcom_id"
)->fetchAssoc();
$repositories = KT_DB::prepare(
	"SELECT SQL_CACHE gedcom_id, COUNT(o_id) AS count FROM `##gedcom` LEFT JOIN `##other` ON gedcom_id = o_file AND o_type = 'REPO' GROUP BY gedcom_id"
)->fetchAssoc();
$notes = KT_DB::prepare(
	"SELECT SQL_CACHE gedcom_id, COUNT(o_id) AS count FROM `##gedcom` LEFT JOIN `##other` ON gedcom_id = o_file AND o_type = 'NOTE' GROUP BY gedcom_id"
)->fetchAssoc();
$changes = KT_DB::prepare(
	"SELECT SQL_CACHE g.gedcom_id, COUNT(change_id) AS count FROM `##gedcom` AS g LEFT JOIN `##change` AS c ON g.gedcom_id = c.gedcom_id AND status = 'pending' GROUP BY g.gedcom_id"
)->fetchAssoc();
?>


<div id="admin" class="cell">
	<h4><?php echo KT_I18N::translate('Dashboard'); ?></h4>
	<h6 class="subheader">
		<?php echo KT_I18N::translate('These pages provide access to all the configuration settings and management tools for this kiwitrees site.'); ?><br>
		<?php echo /* I18N: %s is a URL/link to the project website */ KT_I18N::translate('Support is available at %s.', ' <a class="current" href="' . KT_KIWITREES_URL . '/forums/">kiwitrees.net forums</a>'); ?>
	</h6>
	<?php // Server warnings
	if ($server_warnings): ?>
		<div class="callout warning">
			<h5 class=""><?php echo KT_I18N::translate('Server information'); ?></h5>
			<?php foreach ($server_warnings as $server_warning): ?>
				<?php echo $server_warning; ?>
			<?php endforeach; ?>
		</div>
	<?php endif;
	// Accordion block for DELETE OLD FILES - only shown when old files are found
	$old_files_found = false;
	foreach (old_paths() as $path) {
		if (file_exists($path)) {
			delete_recursively($path);
			// we may not have permission to delete.  Is it still there?
			if (file_exists($path)) {
				$old_files_found = true;
			}
		}
	}
	if (KT_USER_IS_ADMIN && $old_files_found) { ?>
		<div class="callout warning">
			<h5><?php echo KT_I18N::translate('Old files found'); ?></h5>
			<p>
				<?php echo KT_I18N::translate('Files have been found from a previous version of kiwitrees.  Old files can sometimes be a security risk.  You should delete them.'); ?>
			</p>
			<ul>
				<?php foreach (old_paths() as $path) {
					if (file_exists($path)) {
						echo '<li>', $path, '</li>';
					}
				} ?>
			</ul>
		</div>
	<?php } ?>
</div>
<!-- Summary family tree information blocks -->
<div class="cell">
	<div class="grid-x grid-margin-x">
		<div class="cell accordion" data-accordion data-allow-all-closed="true" data-multi-open="false" data-slide-speed="500">
			<div class="accordion-item is-active" data-accordion-item>
				<a href="#" class="accordion-title">
					<span><?php echo KT_I18N::translate('System status'); ?></span>
				</a>
				<div class="accordion-content" data-tab-content>
					<div  id="system-status" class="grid-x grid-margin-x grid-margin-y">
						<div class="cell medium-2 large-1">
							<label class="h6"><?php echo KT_I18N::translate('Website'); ?></label>
						</div>
						<div class="cell medium-10 large-11">
							<p><?php echo KT_I18N::translate('URL'); ?>: <span><?php echo KT_SERVER_NAME; ?></span></p>
						</div>
						<div class="cell medium-2 large-1">
							<label class="h6"><?php echo KT_I18N::translate('Server'); ?></label>
						</div>
						<div class="cell medium-10 large-11">
							<p><?php echo KT_I18N::translate('Server'); ?>: <span><?php echo $_SERVER["SERVER_SOFTWARE"]; ?></span></p>
							<p><?php echo KT_I18N::translate('Operating System'); ?>: <span><?php echo PHP_OS; ?></span><p>
							<p><?php echo KT_I18N::translate('Hostname'); ?>: <span><?php echo $_SERVER['SERVER_NAME']; ?></span><p>
							<p><?php echo KT_I18N::translate('IP and Port'); ?>: <span><?php echo $_SERVER['SERVER_ADDR'] . ' (' . $_SERVER['SERVER_PORT']; ?>)</span><p>
						</div>
						<div class="cell medium-2 large-1">
							<label class="h6"><?php echo KT_I18N::translate('Software'); ?></label>
						</div>
						<div class="cell medium-10 large-11">
							<p><?php echo KT_I18N::translate('PHP Version'); ?>: <span><?php echo phpversion(); ?></span></p>
							<p><?php echo KT_I18N::translate('SQL Version'); ?>: <span><?php echo $version; ?></span></p>
							<p><?php echo KT_I18N::translate('Kiwitrees-nova'); ?>: <span><?php echo KT_VERSION; ?></span></p>
						</div>
						<?php // Alerts
						if (KT_USER_IS_ADMIN) {
							// Kiwitrees version check
							if ($latest_version) {
								if (version_compare(KT_VERSION, $latest_version) < 0) { ?>
									<div class="callout large-4 alert">
										<?php echo /* I18N: %s is a URL/link to the project website */ KT_I18N::translate('Version %s of kiwitrees is now available at %s.', $latest_version, ' <a class="current" href="' . KT_KIWITREES_URL . '/services/downloads/">kiwitrees.net downloads</a>'); ?>
									</div>
								<?php } else { ?>
									<div class="callout large-4 success">
										<?php echo /* I18N: %s is a URL/link to the project website */ KT_I18N::translate('Your version of kiwitrees is the latest available.'); ?>
									</div>
								<?php }
							} else { ?>
								<div class="callout large-4 warning">
									<?php echo /* I18N: %s is a URL/link to the project website */ KT_I18N::translate('No kiwitrees upgrade information is available.'); ?>
								</div>
							<?php }
							// PHP version check
							if (version_compare(phpversion(), '8.0', '<')) {
								if (version_compare(phpversion(), '5.6', '<')) { ?>
									<div class="callout large-4 alert">
										<?php echo  KT_I18N::translate('Kiwitrees is no longer compatible with versions of PHP older than 7.0'); ?>
									</div>
								<?php } else { ?>
									<div class="callout large-4 success">
										<?php echo  KT_I18N::translate('Kiwitrees is compatible with this version of PHP.'); ?>
									</div>
								<?php }
							} else { ?>
								<div class="callout large-4 warning">
									<?php echo  KT_I18N::translate('Kiwitrees is not yet tested for compatibility with your version of PHP. It might work, but if you find any issues please report them on the <a class="current" href="%s" target="_blank">kiwitrees support forum</a>', KT_SUPPORT_URL); ?>
								</div>
							<?php }
						} ?>
					</div>
				</div>
			</div>
			<div class="accordion-item" data-accordion-item>
				<a href="#" class="accordion-title">
					<span><?php echo KT_I18N::translate('Users'); ?></span>
					<span class="fa-layers fa-lg has-tip top" data-tooltip aria-haspopup="true" data-disable-hover="false" title="<?php echo KT_I18N::translate('Total number of users'); ?>">
						<i class="<?php echo $iconStyle; ?> fa-users"></i>
						<span class="fa-layers-counter fa-lg"><?php echo $total_users; ?></span>
					</span>
					<?php if ($incomplete) { ?>
						<span class="fa-layers fa-lg has-tip top warning" data-tooltip aria-haspopup="true" data-disable-hover="false" title="<?php echo KT_I18N::translate('Unverified or not approved users'); ?>">
							<i class="<?php echo $iconStyle; ?> fa-user-secret"></i>
							<span class="fa-layers-counter fa-lg"><?php echo $incomplete; ?></span>
						</span>
					<?php } ?>
				</a>
				<div class="accordion-content" data-tab-content>
					<table class="admin_users">
						<tbody>
							<tr>
								<th><a href="admin_users.php" ><?php echo KT_I18N::translate('Total number of users'); ?></a></th>
								<td><a href="admin_users.php" ><?php echo $total_users; ?></a></td>
							</td>
							<tr>
								<th><?php echo KT_I18N::translate('Administrators'); ?></h>
								<td>
									<?php foreach ($administrators as $n => $user) { ?>
										<?php echo $n ? KT_I18N::$list_separator : ''; ?>
										<a href="admin_users.php?action=edit&user_id=<?php echo $user->user_id; ?>">
											<?php echo KT_Filter::escapeHtml($user->real_name); ?>
										</a>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<th><?php echo KT_I18N::translate('Managers'); ?></th>
								<td>
									<?php foreach ($managers as $n => $user) { ?>
										<?php echo $n ? KT_I18N::$list_separator : ''; ?>
										<a href="admin_users.php?action=edit&user_id=<?php echo $user->user_id; ?>">
											<?php echo KT_Filter::escapeHtml($user->real_name); ?>
										</a>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<th><?php echo KT_I18N::translate('Moderators'); ?></th>
								<td>
									<?php foreach ($moderators as $n => $user) { ?>
										<?php echo $n ? KT_I18N::$list_separator : ''; ?>
										<a href="admin_users.php?action=edit&user_id=<?php echo $user->user_id; ?>">
											<?php echo KT_Filter::escapeHtml($user->real_name); ?>
										</a>
									<?php } ?>
								</td>
							</tr>
							<tr class="<?php echo $unverified ? 'warning' : ''; ?>">
								<th>
									<?php echo KT_I18N::translate('Not verified by user'); ?>
								</th>
								<td>
									<?php foreach ($unverified as $n => $user): ?>
										<?php echo $n ? KT_I18N::$list_separator : ''; ?>
										<a href="admin_users.php?action=edit&user_id=<?php echo $user->user_id; ?>">
											<?php echo KT_Filter::escapeHtml($user->real_name); ?>
										</a>
									<?php endforeach; ?>
								</td>
							</tr>
							<tr class="<?php echo $unapproved ? 'warning' : ''; ?>">
								<th><?php echo KT_I18N::translate('Not approved by administrator'); ?></th>
								<td>
									<?php foreach ($unapproved as $n => $user): ?>
										<?php echo $n ? KT_I18N::$list_separator : ''; ?>
										<a href="admin_users.php?action=edit&user_id=<?php echo $user->user_id; ?>">
											<?php echo KT_Filter::escapeHtml($user->real_name); ?>
										</a>
									<?php endforeach; ?>
								</td>
							</tr>
							<tr>
								<th><?php echo KT_I18N::translate('Users logged in'); ?></th>
								<td>
									<?php foreach ($logged_in as $n => $user): ?>
									<?php echo $n ? KT_I18N::$list_separator : ''; ?>
										<a href="admin_users.php?action=edit&user_id=<?php echo $user->user_id; ?>">
											<?php echo KT_Filter::escapeHtml($user->real_name); ?>
										</a>
									<?php endforeach; ?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="accordion-item" data-accordion-item>
				<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Family tree statistics'); ?></a>
				<div class="accordion-content" data-tab-content>
					<table class="admin_trees scroll">
						<thead>
							<tr>
								<th><?php echo KT_I18N::translate('Family tree'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Pending changes'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Individuals'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Families'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Sources'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Repositories'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Media objects'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Shared notes'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach (KT_Tree::getAll() as $tree) { ?>
								<tr>
									<td>
										<a href="index.php?ged=<?php echo $tree->tree_name_url; ?>">
											<?php echo $tree->tree_name_html; ?>
											-
											<?php echo $tree->tree_title_html; ?>
										</a>
									</td>
									<td class="text-right">
										<?php if ($changes[$tree->tree_id]) { ?>
											<a href="edit_changes.php?ged=<?php echo $tree->tree_name_url; ?>" target="_blank" rel="noopener noreferrer">
												<?php echo KT_I18N::number($changes[$tree->tree_id]); ?>
											</a>
										<?php } else { ?>
											-
										<?php } ?>
									</td>
									<td class="text-right">
										<?php if ($individuals[$tree->tree_id]) { ?>
											<a href="module.php?mod=list_individuals&amp;mod_action=show&amp;ged=<?php echo $tree->tree_name_url; ?>" target="_blank" rel="noopener noreferrer">
												<?php echo KT_I18N::number($individuals[$tree->tree_id]); ?>
											</a>
										<?php } else { ?>
											-
										<?php } ?>
									</td>
									<td class="text-right">
										<?php if ($families[$tree->tree_id]) { ?>
											<a href="module.php?mod=list_families&amp;mod_action=show&amp;ged=<?php echo $tree->tree_name_url; ?>" target="_blank" rel="noopener noreferrer">
												<?php echo KT_I18N::number($families[$tree->tree_id]); ?>
											</a>
										<?php } else { ?>
											-
										<?php } ?>
									</td>
									<td class="text-right">
										<?php if ($sources[$tree->tree_id]) { ?>
											<a href="module.php?mod=list_sources&amp;mod_action=show&amp;ged=<?php echo $tree->tree_name_url; ?>" target="_blank" rel="noopener noreferrer">
												<?php echo KT_I18N::number($sources[$tree->tree_id]); ?>
											</a>
										<?php } else { ?>
											-
										<?php } ?>
									</td>
									<td class="text-right">
										<?php if ($repositories[$tree->tree_id]) { ?>
											<a href="module.php?mod=list_repositories&amp;mod_action=show&amp;ged=<?php echo $tree->tree_name_url; ?>" target="_blank" rel="noopener noreferrer">
												<?php echo KT_I18N::number($repositories[$tree->tree_id]); ?>
											</a>
										<?php } else { ?>
											-
										<?php } ?>
									</td>
									<td class="text-right">
										<?php if ($media[$tree->tree_id]) { ?>
											<a href="module.php?mod=list_media&amp;mod_action=show&amp;ged=<?php echo $tree->tree_name_url; ?>" target="_blank" rel="noopener noreferrer">
												<?php echo KT_I18N::number($media[$tree->tree_id]); ?>
											</a>
										<?php } else { ?>
											-
										<?php } ?>
									</td>
									<td class="text-right">
										<?php if ($notes[$tree->tree_id]) { ?>
											<a href="module.php?mod=list_shared_notes&amp;mod_action=show&amp;ged=<?php echo $tree->tree_name_url; ?>" target="_blank" rel="noopener noreferrer">
												<?php echo KT_I18N::number($notes[$tree->tree_id]); ?>
											</a>
										<?php } else { ?>
											-
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
						<tfoot>
							<tr>
								<td>
									<?php echo KT_I18N::translate('Total'); ?>
									-
									<?php echo KT_I18N::plural('%s family tree', '%s family trees', count(KT_Tree::getAll()), KT_I18N::number(count(KT_Tree::getAll()))); ?>
								</td>
								<td class="text-right">
									<?php echo KT_I18N::number(array_sum($changes)); ?>
								</td>
								<td class="text-right">
									<?php echo KT_I18N::number(array_sum($individuals)); ?>
								</td>
								<td class="text-right">
									<?php echo KT_I18N::number(array_sum($families)); ?>
								</td>
								<td class="text-right">
									<?php echo KT_I18N::number(array_sum($sources)); ?>
								</td>
								<td class="text-right">
									<?php echo KT_I18N::number(array_sum($repositories)); ?>
								</td>
								<td class="text-right">
									<?php echo KT_I18N::number(array_sum($media)); ?>
								</td>
								<td class="text-right">
									<?php echo KT_I18N::number(array_sum($notes)); ?>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="accordion-item" data-accordion-item>
				<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Recent changes'); ?></a>
				<div class="accordion-content" data-tab-content>
					<table class="admin_recent scroll">
						<thead>
							<tr>
								<th colspan="2"><?php echo KT_I18N::translate('Family tree'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Individuals'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Families'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Sources'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Repositories'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Media objects'); ?></th>
								<th class="text-right"><?php echo KT_I18N::translate('Notes'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach (KT_Tree::getAll() as $tree) { ?>
								<tr>
									<td rowspan="3">
										<a href="index.php?ged=<?php echo $tree->tree_name_url; ?>">
											<?php echo $tree->tree_name_html; ?>
											-
											<?php echo $tree->tree_title_html; ?>
										</a>
									</td>
									<td><?php echo KT_I18N::translate('Day'); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countIndiChangesToday($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countFamChangesToday($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countSourChangesToday($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countRepoChangesToday($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countObjeChangesToday($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countNoteChangesToday($tree->tree_id); ?></td>
								</tr>
								<tr>
									<td><?php echo KT_I18N::translate('Week'); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countIndiChangesWeek($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countFamChangesWeek($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countSourChangesWeek($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countRepoChangesWeek($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countObjeChangesWeek($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countNoteChangesWeek($tree->tree_id); ?></td>
								</tr>
								<tr>
									<td><?php echo KT_I18N::translate('Month'); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countIndiChangesMonth($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countFamChangesMonth($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countSourChangesMonth($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countRepoChangesMonth($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countObjeChangesMonth($tree->tree_id); ?></td>
									<td class="text-right"><?php echo KT_Query_Admin::countNoteChangesMonth($tree->tree_id); ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php // This is a list of old files and directories, from earlier versions of kiwitrees, that can be deleted
function old_paths() {
	return array(
		// Removed in 1.0.2
		KT_ROOT . 'language/en.mo',
		// Removed in 1.0.3
		KT_ROOT . 'themechange.php',
		// Removed in 1.0.4
		KT_ROOT . 'themes/fab/images/notes.gif',
		// Removed in 1.0.5
		// Removed in 1.0.6
		KT_ROOT . 'includes/extras',
		// Removed in 1.1.0
		KT_ROOT . 'addremotelink.php',
		KT_ROOT . 'addsearchlink.php',
		KT_ROOT . 'client.php',
		KT_ROOT . 'dir_editor.php',
		KT_ROOT . 'editconfig_gedcom.php',
		KT_ROOT . 'editgedcoms.php',
		KT_ROOT . 'edit_merge.php',
		KT_ROOT . 'genservice.php',
		KT_ROOT . 'includes/classes',
		KT_ROOT . 'includes/controllers',
		KT_ROOT . 'includes/family_nav.php',
		KT_ROOT . 'includes/functions/functions_lang.php',
		KT_ROOT . 'includes/functions/functions_tools.php',
		KT_ROOT . 'js/conio',
		KT_ROOT . 'logs.php',
		KT_ROOT . 'manageservers.php',
		KT_ROOT . 'media.php',
		KT_ROOT . 'module_admin.php',
		//KT_ROOT . 'modules', // Do not delete - users may have stored custom modules/data here
		KT_ROOT . 'opensearch.php',
		KT_ROOT . 'PEAR.php',
		KT_ROOT . 'pgv_to_wt.php',
		KT_ROOT . 'places',
		//KT_ROOT . 'robots.txt', // Do not delete this - it may contain user data
		KT_ROOT . 'serviceClientTest.php',
		KT_ROOT . 'siteconfig.php',
		KT_ROOT . 'SOAP',
		KT_ROOT . 'themes/clouds/images/xml.gif',
		KT_ROOT . 'themes/clouds/mozilla.css',
		KT_ROOT . 'themes/clouds/netscape.css',
		KT_ROOT . 'themes/colors/images/xml.gif',
		KT_ROOT . 'themes/colors/mozilla.css',
		KT_ROOT . 'themes/colors/netscape.css',
		KT_ROOT . 'themes/fab/images/checked.gif',
		KT_ROOT . 'themes/fab/images/checked_qm.gif',
		KT_ROOT . 'themes/fab/images/feed-icon16x16.png',
		KT_ROOT . 'themes/fab/images/hcal.png',
		KT_ROOT . 'themes/fab/images/menu_punbb.gif',
		KT_ROOT . 'themes/fab/images/trashcan.gif',
		KT_ROOT . 'themes/fab/images/xml.gif',
		KT_ROOT . 'themes/fab/mozilla.css',
		KT_ROOT . 'themes/fab/netscape.css',
		KT_ROOT . 'themes/minimal/mozilla.css',
		KT_ROOT . 'themes/minimal/netscape.css',
		KT_ROOT . 'themes/webtrees/images/checked.gif',
		KT_ROOT . 'themes/webtrees/images/checked_qm.gif',
		KT_ROOT . 'themes/webtrees/images/feed-icon16x16.png',
		KT_ROOT . 'themes/webtrees/images/header.jpg',
		KT_ROOT . 'themes/webtrees/images/trashcan.gif',
		KT_ROOT . 'themes/webtrees/images/xml.gif',
		KT_ROOT . 'themes/webtrees/mozilla.css',
		KT_ROOT . 'themes/webtrees/netscape.css',
		KT_ROOT . 'themes/webtrees/style_rtl.css',
		KT_ROOT . 'themes/xenea/mozilla.css',
		KT_ROOT . 'themes/xenea/netscape.css',
		KT_ROOT . 'uploadmedia.php',
		KT_ROOT . 'useradmin.php',
		KT_ROOT . 'webservice',
		KT_ROOT . 'wtinfo.php',
		// Removed in 1.1.1
		KT_ROOT . 'themes/webtrees/images/add.gif',
		KT_ROOT . 'themes/webtrees/images/bubble.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/addmedia.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/addnote.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/addrepository.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/addsource.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/autocomplete.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/calendar.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/family.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/head.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/indi.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/keyboard.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/media.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/note.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/place.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/refresh.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/repository.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/source.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/target.gif',
		KT_ROOT . 'themes/webtrees/images/buttons/view_all.gif',
		KT_ROOT . 'themes/webtrees/images/cfamily.png',
		KT_ROOT . 'themes/webtrees/images/childless.gif',
		KT_ROOT . 'themes/webtrees/images/children.gif',
		KT_ROOT . 'themes/webtrees/images/darrow2.gif',
		KT_ROOT . 'themes/webtrees/images/darrow.gif',
		KT_ROOT . 'themes/webtrees/images/ddarrow.gif',
		KT_ROOT . 'themes/webtrees/images/dline2.gif',
		KT_ROOT . 'themes/webtrees/images/dline.gif',
		KT_ROOT . 'themes/webtrees/images/edit_sm.png',
		KT_ROOT . 'themes/webtrees/images/fambook.png',
		KT_ROOT . 'themes/webtrees/images/forbidden.gif',
		KT_ROOT . 'themes/webtrees/images/hline.gif',
		KT_ROOT . 'themes/webtrees/images/larrow2.gif',
		KT_ROOT . 'themes/webtrees/images/larrow.gif',
		KT_ROOT . 'themes/webtrees/images/ldarrow.gif',
		KT_ROOT . 'themes/webtrees/images/lsdnarrow.gif',
		KT_ROOT . 'themes/webtrees/images/lsltarrow.gif',
		KT_ROOT . 'themes/webtrees/images/lsrtarrow.gif',
		KT_ROOT . 'themes/webtrees/images/lsuparrow.gif',
		KT_ROOT . 'themes/webtrees/images/mapq.gif',
		KT_ROOT . 'themes/webtrees/images/media/doc.gif',
		KT_ROOT . 'themes/webtrees/images/media/ged.gif',
		KT_ROOT . 'themes/webtrees/images/media/globe.png',
		KT_ROOT . 'themes/webtrees/images/media/html.gif',
		KT_ROOT . 'themes/webtrees/images/media/pdf.gif',
		KT_ROOT . 'themes/webtrees/images/media/tex.gif',
		KT_ROOT . 'themes/webtrees/images/minus.gif',
		KT_ROOT . 'themes/webtrees/images/move.gif',
		KT_ROOT . 'themes/webtrees/images/multim.gif',
		KT_ROOT . 'themes/webtrees/images/pix1.gif',
		KT_ROOT . 'themes/webtrees/images/plus.gif',
		KT_ROOT . 'themes/webtrees/images/rarrow2.gif',
		KT_ROOT . 'themes/webtrees/images/rarrow.gif',
		KT_ROOT . 'themes/webtrees/images/rdarrow.gif',
		KT_ROOT . 'themes/webtrees/images/reminder.gif',
		KT_ROOT . 'themes/webtrees/images/remove-dis.png',
		KT_ROOT . 'themes/webtrees/images/remove.gif',
		KT_ROOT . 'themes/webtrees/images/RESN_confidential.gif',
		KT_ROOT . 'themes/webtrees/images/RESN_locked.gif',
		KT_ROOT . 'themes/webtrees/images/RESN_none.gif',
		KT_ROOT . 'themes/webtrees/images/RESN_privacy.gif',
		KT_ROOT . 'themes/webtrees/images/rings.gif',
		KT_ROOT . 'themes/webtrees/images/sex_f_15x15.gif',
		KT_ROOT . 'themes/webtrees/images/sex_f_9x9.gif',
		KT_ROOT . 'themes/webtrees/images/sex_m_15x15.gif',
		KT_ROOT . 'themes/webtrees/images/sex_m_9x9.gif',
		KT_ROOT . 'themes/webtrees/images/sex_u_15x15.gif',
		KT_ROOT . 'themes/webtrees/images/sex_u_9x9.gif',
		KT_ROOT . 'themes/webtrees/images/sfamily.png',
		KT_ROOT . 'themes/webtrees/images/silhouette_female.gif',
		KT_ROOT . 'themes/webtrees/images/silhouette_male.gif',
		KT_ROOT . 'themes/webtrees/images/silhouette_unknown.gif',
		KT_ROOT . 'themes/webtrees/images/spacer.gif',
		KT_ROOT . 'themes/webtrees/images/stop.gif',
		KT_ROOT . 'themes/webtrees/images/terrasrv.gif',
		KT_ROOT . 'themes/webtrees/images/timelineChunk.gif',
		KT_ROOT . 'themes/webtrees/images/topdown.gif',
		KT_ROOT . 'themes/webtrees/images/uarrow2.gif',
		KT_ROOT . 'themes/webtrees/images/uarrow3.gif',
		KT_ROOT . 'themes/webtrees/images/uarrow.gif',
		KT_ROOT . 'themes/webtrees/images/udarrow.gif',
		KT_ROOT . 'themes/webtrees/images/video.png',
		KT_ROOT . 'themes/webtrees/images/vline.gif',
		KT_ROOT . 'themes/webtrees/images/warning.gif',
		KT_ROOT . 'themes/webtrees/images/zoomin.gif',
		KT_ROOT . 'themes/webtrees/images/zoomout.gif',
		// Removed in 1.1.2
		KT_ROOT . 'js/treenav.js',
		KT_ROOT . 'library/KT/TreeNav.php',
		KT_ROOT . 'themes/clouds/images/background.jpg',
		KT_ROOT . 'themes/clouds/images/buttons/refresh.gif',
		KT_ROOT . 'themes/clouds/images/buttons/view_all.gif',
		KT_ROOT . 'themes/clouds/images/lsdnarrow.gif',
		KT_ROOT . 'themes/clouds/images/lsltarrow.gif',
		KT_ROOT . 'themes/clouds/images/lsrtarrow.gif',
		KT_ROOT . 'themes/clouds/images/lsuparrow.gif',
		KT_ROOT . 'themes/clouds/images/menu_gallery.gif',
		KT_ROOT . 'themes/clouds/images/menu_punbb.gif',
		KT_ROOT . 'themes/clouds/images/menu_research.gif',
		KT_ROOT . 'themes/clouds/images/silhouette_female.gif',
		KT_ROOT . 'themes/clouds/images/silhouette_male.gif',
		KT_ROOT . 'themes/clouds/images/silhouette_unknown.gif',
		KT_ROOT . 'themes/colors/images/buttons/refresh.gif',
		KT_ROOT . 'themes/colors/images/buttons/view_all.gif',
		KT_ROOT . 'themes/colors/images/lsdnarrow.gif',
		KT_ROOT . 'themes/colors/images/lsltarrow.gif',
		KT_ROOT . 'themes/colors/images/lsrtarrow.gif',
		KT_ROOT . 'themes/colors/images/lsuparrow.gif',
		KT_ROOT . 'themes/colors/images/menu_gallery.gif',
		KT_ROOT . 'themes/colors/images/menu_punbb.gif',
		KT_ROOT . 'themes/colors/images/menu_research.gif',
		KT_ROOT . 'themes/colors/images/silhouette_female.gif',
		KT_ROOT . 'themes/colors/images/silhouette_male.gif',
		KT_ROOT . 'themes/colors/images/silhouette_unknown.gif',
		KT_ROOT . 'themes/fab/images/bubble.gif',
		KT_ROOT . 'themes/fab/images/buttons/refresh.gif',
		KT_ROOT . 'themes/fab/images/buttons/view_all.gif',
		KT_ROOT . 'themes/fab/images/lsdnarrow.gif',
		KT_ROOT . 'themes/fab/images/lsltarrow.gif',
		KT_ROOT . 'themes/fab/images/lsrtarrow.gif',
		KT_ROOT . 'themes/fab/images/lsuparrow.gif',
		KT_ROOT . 'themes/fab/images/mapq.gif',
		KT_ROOT . 'themes/fab/images/menu_gallery.gif',
		KT_ROOT . 'themes/fab/images/menu_research.gif',
		KT_ROOT . 'themes/fab/images/multim.gif',
		KT_ROOT . 'themes/fab/images/RESN_confidential.gif',
		KT_ROOT . 'themes/fab/images/RESN_locked.gif',
		KT_ROOT . 'themes/fab/images/RESN_none.gif',
		KT_ROOT . 'themes/fab/images/RESN_privacy.gif',
		KT_ROOT . 'themes/fab/images/silhouette_female.gif',
		KT_ROOT . 'themes/fab/images/silhouette_male.gif',
		KT_ROOT . 'themes/fab/images/silhouette_unknown.gif',
		KT_ROOT . 'themes/fab/images/terrasrv.gif',
		KT_ROOT . 'themes/fab/images/timelineChunk.gif',
		KT_ROOT . 'themes/minimal/images/lsdnarrow.gif',
		KT_ROOT . 'themes/minimal/images/lsltarrow.gif',
		KT_ROOT . 'themes/minimal/images/lsrtarrow.gif',
		KT_ROOT . 'themes/minimal/images/lsuparrow.gif',
		KT_ROOT . 'themes/minimal/images/silhouette_female.gif',
		KT_ROOT . 'themes/minimal/images/silhouette_male.gif',
		KT_ROOT . 'themes/minimal/images/silhouette_unknown.gif',
		KT_ROOT . 'themes/webtrees/images/lsdnarrow.png',
		KT_ROOT . 'themes/webtrees/images/lsltarrow.png',
		KT_ROOT . 'themes/webtrees/images/lsrtarrow.png',
		KT_ROOT . 'themes/webtrees/images/lsuparrow.png',
		KT_ROOT . 'themes/xenea/images/add.gif',
		KT_ROOT . 'themes/xenea/images/admin.gif',
		KT_ROOT . 'themes/xenea/images/ancestry.gif',
		KT_ROOT . 'themes/xenea/images/barra.gif',
		KT_ROOT . 'themes/xenea/images/buttons/addmedia.gif',
		KT_ROOT . 'themes/xenea/images/buttons/addnote.gif',
		KT_ROOT . 'themes/xenea/images/buttons/addrepository.gif',
		KT_ROOT . 'themes/xenea/images/buttons/addsource.gif',
		KT_ROOT . 'themes/xenea/images/buttons/autocomplete.gif',
		KT_ROOT . 'themes/xenea/images/buttons/calendar.gif',
		KT_ROOT . 'themes/xenea/images/buttons/family.gif',
		KT_ROOT . 'themes/xenea/images/buttons/head.gif',
		KT_ROOT . 'themes/xenea/images/buttons/indi.gif',
		KT_ROOT . 'themes/xenea/images/buttons/keyboard.gif',
		KT_ROOT . 'themes/xenea/images/buttons/media.gif',
		KT_ROOT . 'themes/xenea/images/buttons/note.gif',
		KT_ROOT . 'themes/xenea/images/buttons/place.gif',
		KT_ROOT . 'themes/xenea/images/buttons/repository.gif',
		KT_ROOT . 'themes/xenea/images/buttons/source.gif',
		KT_ROOT . 'themes/xenea/images/buttons/target.gif',
		KT_ROOT . 'themes/xenea/images/cabeza.jpg',
		KT_ROOT . 'themes/xenea/images/cabeza_rtl.jpg',
		KT_ROOT . 'themes/xenea/images/calendar.gif',
		KT_ROOT . 'themes/xenea/images/cfamily.gif',
		KT_ROOT . 'themes/xenea/images/childless.gif',
		KT_ROOT . 'themes/xenea/images/children.gif',
		KT_ROOT . 'themes/xenea/images/clippings.gif',
		KT_ROOT . 'themes/xenea/images/darrow2.gif',
		KT_ROOT . 'themes/xenea/images/darrow.gif',
		KT_ROOT . 'themes/xenea/images/ddarrow.gif',
		KT_ROOT . 'themes/xenea/images/descendancy.gif',
		KT_ROOT . 'themes/xenea/images/dline2.gif',
		KT_ROOT . 'themes/xenea/images/dline.gif',
		KT_ROOT . 'themes/xenea/images/edit_fam.gif',
		KT_ROOT . 'themes/xenea/images/edit_indi.gif',
		KT_ROOT . 'themes/xenea/images/edit_repo.gif',
		KT_ROOT . 'themes/xenea/images/edit_sour.gif',
		KT_ROOT . 'themes/xenea/images/fambook.gif',
		KT_ROOT . 'themes/xenea/images/fanchart.gif',
		KT_ROOT . 'themes/xenea/images/gedcom.gif',
		KT_ROOT . 'themes/xenea/images/help.gif',
		KT_ROOT . 'themes/xenea/images/hline.gif',
		KT_ROOT . 'themes/xenea/images/home.gif',
		KT_ROOT . 'themes/xenea/images/hourglass.gif',
		KT_ROOT . 'themes/xenea/images/indis.gif',
		KT_ROOT . 'themes/xenea/images/larrow2.gif',
		KT_ROOT . 'themes/xenea/images/larrow.gif',
		KT_ROOT . 'themes/xenea/images/ldarrow.gif',
		KT_ROOT . 'themes/xenea/images/lists.gif',
		KT_ROOT . 'themes/xenea/images/lsdnarrow.gif',
		KT_ROOT . 'themes/xenea/images/lsltarrow.gif',
		KT_ROOT . 'themes/xenea/images/lsrtarrow.gif',
		KT_ROOT . 'themes/xenea/images/lsuparrow.gif',
		KT_ROOT . 'themes/xenea/images/media/doc.gif',
		KT_ROOT . 'themes/xenea/images/media/ged.gif',
		KT_ROOT . 'themes/xenea/images/media.gif',
		KT_ROOT . 'themes/xenea/images/media/html.gif',
		KT_ROOT . 'themes/xenea/images/media/pdf.gif',
		KT_ROOT . 'themes/xenea/images/media/tex.gif',
		KT_ROOT . 'themes/xenea/images/menu_gallery.gif',
		KT_ROOT . 'themes/xenea/images/menu_help.gif',
		KT_ROOT . 'themes/xenea/images/menu_media.gif',
		KT_ROOT . 'themes/xenea/images/menu_note.gif',
		KT_ROOT . 'themes/xenea/images/menu_punbb.gif',
		KT_ROOT . 'themes/xenea/images/menu_repository.gif',
		KT_ROOT . 'themes/xenea/images/menu_research.gif',
		KT_ROOT . 'themes/xenea/images/menu_source.gif',
		KT_ROOT . 'themes/xenea/images/minus.gif',
		KT_ROOT . 'themes/xenea/images/move.gif',
		KT_ROOT . 'themes/xenea/images/mypage.gif',
		KT_ROOT . 'themes/xenea/images/notes.gif',
		KT_ROOT . 'themes/xenea/images/patriarch.gif',
		KT_ROOT . 'themes/xenea/images/pedigree.gif',
		KT_ROOT . 'themes/xenea/images/place.gif',
		KT_ROOT . 'themes/xenea/images/plus.gif',
		KT_ROOT . 'themes/xenea/images/puntos2.gif',
		KT_ROOT . 'themes/xenea/images/puntos.gif',
		KT_ROOT . 'themes/xenea/images/rarrow2.gif',
		KT_ROOT . 'themes/xenea/images/rarrow.gif',
		KT_ROOT . 'themes/xenea/images/rdarrow.gif',
		KT_ROOT . 'themes/xenea/images/relationship.gif',
		KT_ROOT . 'themes/xenea/images/reminder.gif',
		KT_ROOT . 'themes/xenea/images/report.gif',
		KT_ROOT . 'themes/xenea/images/repository.gif',
		KT_ROOT . 'themes/xenea/images/rings.gif',
		KT_ROOT . 'themes/xenea/images/search.gif',
		KT_ROOT . 'themes/xenea/images/sex_f_15x15.gif',
		KT_ROOT . 'themes/xenea/images/sex_f_9x9.gif',
		KT_ROOT . 'themes/xenea/images/sex_m_15x15.gif',
		KT_ROOT . 'themes/xenea/images/sex_m_9x9.gif',
		KT_ROOT . 'themes/xenea/images/sex_u_15x15.gif',
		KT_ROOT . 'themes/xenea/images/sex_u_9x9.gif',
		KT_ROOT . 'themes/xenea/images/sfamily.gif',
		KT_ROOT . 'themes/xenea/images/silhouette_female.gif',
		KT_ROOT . 'themes/xenea/images/silhouette_male.gif',
		KT_ROOT . 'themes/xenea/images/silhouette_unknown.gif',
		KT_ROOT . 'themes/xenea/images/sombra.gif',
		KT_ROOT . 'themes/xenea/images/source.gif',
		KT_ROOT . 'themes/xenea/images/spacer.gif',
		KT_ROOT . 'themes/xenea/images/statistic.gif',
		KT_ROOT . 'themes/xenea/images/stop.gif',
		KT_ROOT . 'themes/xenea/images/timeline.gif',
		KT_ROOT . 'themes/xenea/images/tree.gif',
		KT_ROOT . 'themes/xenea/images/uarrow2.gif',
		KT_ROOT . 'themes/xenea/images/uarrow3.gif',
		KT_ROOT . 'themes/xenea/images/uarrow.gif',
		KT_ROOT . 'themes/xenea/images/udarrow.gif',
		KT_ROOT . 'themes/xenea/images/vline.gif',
		KT_ROOT . 'themes/xenea/images/warning.gif',
		KT_ROOT . 'themes/xenea/images/zoomin.gif',
		KT_ROOT . 'themes/xenea/images/zoomout.gif',
		KT_ROOT . 'treenav.php',
		// Removed in 1.2.0
		KT_ROOT . 'themes/clouds/images/close.png',
		// KT_ROOT . 'themes/clouds/images/copy.png', // Added back in 1.2.4
		KT_ROOT . 'themes/clouds/images/jquery',
		KT_ROOT . 'themes/clouds/images/left1G.gif',
		KT_ROOT . 'themes/clouds/images/left1R.gif',
		KT_ROOT . 'themes/clouds/images/left4.gif',
		KT_ROOT . 'themes/clouds/images/left5.gif',
		KT_ROOT . 'themes/clouds/images/left6.gif',
		KT_ROOT . 'themes/clouds/images/left7.gif',
		KT_ROOT . 'themes/clouds/images/left8.gif',
		KT_ROOT . 'themes/clouds/images/left9.gif',
		KT_ROOT . 'themes/clouds/images/open.png',
		KT_ROOT . 'themes/clouds/images/pin-in.png',
		KT_ROOT . 'themes/clouds/images/pin-out.png',
		KT_ROOT . 'themes/clouds/images/pixel.gif',
		KT_ROOT . 'themes/clouds/images/puntos2.gif',
		KT_ROOT . 'themes/clouds/images/puntos.gif',
		KT_ROOT . 'themes/clouds/images/right1G.gif',
		KT_ROOT . 'themes/clouds/images/right1R.gif',
		KT_ROOT . 'themes/clouds/images/sombra.gif',
		KT_ROOT . 'themes/clouds/images/th_5.gif',
		KT_ROOT . 'themes/clouds/images/th_c4.gif',
		KT_ROOT . 'themes/clouds/images/w_22.png',
		KT_ROOT . 'themes/clouds/jquery',
		KT_ROOT . 'themes/colors/images/close.png',
		KT_ROOT . 'themes/colors/images/jquery',
		KT_ROOT . 'themes/colors/images/left1G.gif',
		KT_ROOT . 'themes/colors/images/left1R.gif',
		KT_ROOT . 'themes/colors/images/left4.gif',
		KT_ROOT . 'themes/colors/images/left5.gif',
		KT_ROOT . 'themes/colors/images/left6.gif',
		KT_ROOT . 'themes/colors/images/left7.gif',
		KT_ROOT . 'themes/colors/images/left8.gif',
		KT_ROOT . 'themes/colors/images/left9.gif',
		KT_ROOT . 'themes/colors/images/open.png',
		KT_ROOT . 'themes/colors/images/pin-in.png',
		KT_ROOT . 'themes/colors/images/pin-out.png',
		KT_ROOT . 'themes/colors/images/pixel.gif',
		KT_ROOT . 'themes/colors/images/puntos2.gif',
		KT_ROOT . 'themes/colors/images/puntos.gif',
		KT_ROOT . 'themes/colors/images/right1G.gif',
		KT_ROOT . 'themes/colors/images/right1R.gif',
		KT_ROOT . 'themes/colors/images/sombra.gif',
		KT_ROOT . 'themes/colors/images/w_22.png',
		KT_ROOT . 'themes/colors/jquery',
		KT_ROOT . 'themes/fab/images/copy.png',
		KT_ROOT . 'themes/fab/images/delete.png',
		KT_ROOT . 'themes/fab/images/jquery',
		KT_ROOT . 'themes/fab/jquery',
		KT_ROOT . 'themes/minimal/images/close.png',
		KT_ROOT . 'themes/minimal/images/jquery',
		KT_ROOT . 'themes/minimal/images/open.png',
		KT_ROOT . 'themes/minimal/images/pin-in.png',
		KT_ROOT . 'themes/minimal/images/pin-out.png',
		KT_ROOT . 'themes/minimal/jquery',
		KT_ROOT . 'themes/webtrees/images/close.png',
		KT_ROOT . 'themes/webtrees/images/copy.png',
		KT_ROOT . 'themes/webtrees/images/delete.png',
		KT_ROOT . 'themes/webtrees/images/jquery',
		KT_ROOT . 'themes/webtrees/images/open.png',
		KT_ROOT . 'themes/webtrees/images/pin-in.png',
		KT_ROOT . 'themes/webtrees/images/pin-out.png',
		KT_ROOT . 'themes/webtrees/jquery',
		KT_ROOT . 'themes/xenea/images/close.png',
		KT_ROOT . 'themes/xenea/images/copy.png',
		KT_ROOT . 'themes/xenea/images/jquery',
		KT_ROOT . 'themes/xenea/images/open.png',
		KT_ROOT . 'themes/xenea/images/pin-in.png',
		KT_ROOT . 'themes/xenea/images/pin-out.png',
		KT_ROOT . 'themes/xenea/jquery',
		// Removed in 1.2.1
		// Removed in 1.2.2
		KT_ROOT . 'themes/clouds/chrome.css',
		KT_ROOT . 'themes/clouds/images/ancestry.gif',
		KT_ROOT . 'themes/clouds/images/calendar.gif',
		KT_ROOT . 'themes/clouds/images/charts.gif',
		KT_ROOT . 'themes/clouds/images/descendancy.gif',
		KT_ROOT . 'themes/clouds/images/edit_fam.gif',
		KT_ROOT . 'themes/clouds/images/edit_media.gif',
		KT_ROOT . 'themes/clouds/images/edit_note.gif',
		KT_ROOT . 'themes/clouds/images/edit_repo.gif',
		KT_ROOT . 'themes/clouds/images/edit_sm.png',
		KT_ROOT . 'themes/clouds/images/edit_sour.gif',
		KT_ROOT . 'themes/clouds/images/fambook.gif',
		KT_ROOT . 'themes/clouds/images/fanchart.gif',
		KT_ROOT . 'themes/clouds/images/gedcom.gif',
		KT_ROOT . 'themes/clouds/images/home.gif',
		KT_ROOT . 'themes/clouds/images/hourglass.gif',
		KT_ROOT . 'themes/clouds/images/indi_sprite.png',
		KT_ROOT . 'themes/clouds/images/menu_source.gif',
		KT_ROOT . 'themes/clouds/images/search.gif',
		KT_ROOT . 'themes/clouds/opera.css',
		KT_ROOT . 'themes/clouds/print.css',
		KT_ROOT . 'themes/clouds/style_rtl.css',
		KT_ROOT . 'themes/colors/chrome.css',
		KT_ROOT . 'themes/colors/css/common.css',
		KT_ROOT . 'themes/colors/images/ancestry.gif',
		KT_ROOT . 'themes/colors/images/buttons/addmedia.gif',
		KT_ROOT . 'themes/colors/images/buttons/addnote.gif',
		KT_ROOT . 'themes/colors/images/buttons/addrepository.gif',
		KT_ROOT . 'themes/colors/images/buttons/addsource.gif',
		KT_ROOT . 'themes/colors/images/buttons/autocomplete.gif',
		KT_ROOT . 'themes/colors/images/buttons/calendar.gif',
		KT_ROOT . 'themes/colors/images/buttons/family.gif',
		//KT_ROOT . 'themes/colors/images/buttons/find_facts.png', // Added back in 1.2.4
		KT_ROOT . 'themes/colors/images/buttons/head.gif',
		KT_ROOT . 'themes/colors/images/buttons/indi.gif',
		KT_ROOT . 'themes/colors/images/buttons/keyboard.gif',
		KT_ROOT . 'themes/colors/images/buttons/media.gif',
		KT_ROOT . 'themes/colors/images/buttons/note.gif',
		KT_ROOT . 'themes/colors/images/buttons/place.gif',
		KT_ROOT . 'themes/colors/images/buttons/repository.gif',
		KT_ROOT . 'themes/colors/images/buttons/source.gif',
		KT_ROOT . 'themes/colors/images/buttons/target.gif',
		KT_ROOT . 'themes/colors/images/calendar.gif',
		KT_ROOT . 'themes/colors/images/cfamily.gif',
		KT_ROOT . 'themes/colors/images/charts.gif',
		KT_ROOT . 'themes/colors/images/descendancy.gif',
		KT_ROOT . 'themes/colors/images/edit_fam.gif',
		KT_ROOT . 'themes/colors/images/edit_media.gif',
		KT_ROOT . 'themes/colors/images/edit_note.gif',
		KT_ROOT . 'themes/colors/images/edit_repo.gif',
		KT_ROOT . 'themes/colors/images/edit_sm.png',
		KT_ROOT . 'themes/colors/images/edit_sour.gif',
		KT_ROOT . 'themes/colors/images/fambook.gif',
		KT_ROOT . 'themes/colors/images/fanchart.gif',
		KT_ROOT . 'themes/colors/images/gedcom.gif',
		KT_ROOT . 'themes/colors/images/home.gif',
		KT_ROOT . 'themes/colors/images/hourglass.gif',
		KT_ROOT . 'themes/colors/images/indis.gif',
		KT_ROOT . 'themes/colors/images/indi_sprite.png',
		KT_ROOT . 'themes/colors/images/itree.gif',
		KT_ROOT . 'themes/colors/images/left1B.gif',
		KT_ROOT . 'themes/colors/images/left2.gif',
		KT_ROOT . 'themes/colors/images/left3.gif',
		KT_ROOT . 'themes/colors/images/li.gif',
		KT_ROOT . 'themes/colors/images/lists.gif',
		KT_ROOT . 'themes/colors/images/media/doc.gif',
		KT_ROOT . 'themes/colors/images/media/ged.gif',
		KT_ROOT . 'themes/colors/images/media/html.gif',
		KT_ROOT . 'themes/colors/images/media/pdf.gif',
		KT_ROOT . 'themes/colors/images/media/tex.gif',
		KT_ROOT . 'themes/colors/images/menu_help.gif',
		KT_ROOT . 'themes/colors/images/menu_note.gif',
		KT_ROOT . 'themes/colors/images/menu_source.gif',
		KT_ROOT . 'themes/colors/images/patriarch.gif',
		KT_ROOT . 'themes/colors/images/place.gif',
		KT_ROOT . 'themes/colors/images/relationship.gif',
		KT_ROOT . 'themes/colors/images/right1B.gif',
		KT_ROOT . 'themes/colors/images/right3.gif',
		KT_ROOT . 'themes/colors/images/search.gif',
		KT_ROOT . 'themes/colors/images/sfamily.gif',
		KT_ROOT . 'themes/colors/images/source.gif',
		KT_ROOT . 'themes/colors/images/statistic.gif',
		KT_ROOT . 'themes/colors/images/timeline.gif',
		KT_ROOT . 'themes/colors/images/wiki.png',
		KT_ROOT . 'themes/colors/opera.css',
		KT_ROOT . 'themes/colors/print.css',
		KT_ROOT . 'themes/colors/style_rtl.css',
		KT_ROOT . 'themes/fab/chrome.css',
		KT_ROOT . 'themes/fab/opera.css',
		KT_ROOT . 'themes/minimal/chrome.css',
		KT_ROOT . 'themes/minimal/opera.css',
		KT_ROOT . 'themes/minimal/print.css',
		KT_ROOT . 'themes/minimal/style_rtl.css',
		KT_ROOT . 'themes/webtrees/images/calendar.png',
		KT_ROOT . 'themes/webtrees/images/charts.png',
		KT_ROOT . 'themes/webtrees/images/edit_fam.png',
		KT_ROOT . 'themes/webtrees/images/edit_media.png',
		KT_ROOT . 'themes/webtrees/images/edit_note.png',
		KT_ROOT . 'themes/webtrees/images/edit_repo.png',
		KT_ROOT . 'themes/webtrees/images/edit_source.png',
		KT_ROOT . 'themes/webtrees/images/help.png',
		KT_ROOT . 'themes/webtrees/images/home.png',
		KT_ROOT . 'themes/webtrees/images/lists.png',
		KT_ROOT . 'themes/webtrees/images/reports.png',
		KT_ROOT . 'themes/xenea/chrome.css',
		KT_ROOT . 'themes/xenea/images/facts/ADDR.gif',
		KT_ROOT . 'themes/xenea/images/facts/BAPM.gif',
		KT_ROOT . 'themes/xenea/images/facts/BIRT.gif',
		KT_ROOT . 'themes/xenea/images/facts/BURI.gif',
		KT_ROOT . 'themes/xenea/images/facts/CEME.gif',
		KT_ROOT . 'themes/xenea/images/facts/CHAN.gif',
		KT_ROOT . 'themes/xenea/images/facts/CHR.gif',
		KT_ROOT . 'themes/xenea/images/facts/DEAT.gif',
		KT_ROOT . 'themes/xenea/images/facts/EDUC.gif',
		KT_ROOT . 'themes/xenea/images/facts/ENGA.gif',
		KT_ROOT . 'themes/xenea/images/facts/GRAD.gif',
		KT_ROOT . 'themes/xenea/images/facts/MARR.gif',
		KT_ROOT . 'themes/xenea/images/facts/_MDCL.if',
		KT_ROOT . 'themes/xenea/images/facts/_MILI.gif',
		KT_ROOT . 'themes/xenea/images/facts/OCCU.gif',
		KT_ROOT . 'themes/xenea/images/facts/ORDN.gif',
		KT_ROOT . 'themes/xenea/images/facts/PHON.gif',
		KT_ROOT . 'themes/xenea/images/facts/RELA.gif',
		KT_ROOT . 'themes/xenea/images/facts/RESI.gif',
		KT_ROOT . 'themes/xenea/opera.css',
		KT_ROOT . 'themes/xenea/print.css',
		KT_ROOT . 'themes/xenea/style_rtl.css',
		// Removed in 1.2.3
		//KT_ROOT . 'modules_v2', // Do not delete - users may have stored custom modules/data here
		// Removed in 1.2.4
		KT_ROOT . 'includes/cssparser.inc.php',
		KT_ROOT . 'js/strings.js',
		KT_ROOT . 'modules_v3/gedcom_favorites/help_text.php',
		KT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_3_find.php',
		KT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_3_search_add.php',
		KT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_5_input.js',
		KT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_5_input.php',
		KT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_7_parse_addLinksTbl.php',
		KT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_query_1a.php',
		KT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_query_2a.php',
		KT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_query_3a.php',
		KT_ROOT . 'modules_v3/lightbox/css/album_page_RTL2.css',
		KT_ROOT . 'modules_v3/lightbox/css/album_page_RTL.css',
		KT_ROOT . 'modules_v3/lightbox/css/album_page_RTL_ff.css',
		KT_ROOT . 'modules_v3/lightbox/css/clearbox_music.css',
		KT_ROOT . 'modules_v3/lightbox/css/clearbox_music_RTL.css',
		KT_ROOT . 'modules_v3/user_favorites/db_schema',
		KT_ROOT . 'modules_v3/user_favorites/help_text.php',
		KT_ROOT . 'search_engine.php',
		KT_ROOT . 'themes/_administration/images/darrow2.gif',
		KT_ROOT . 'themes/_administration/images/darrow.gif',
		KT_ROOT . 'themes/_administration/images/ddarrow.gif',
		KT_ROOT . 'themes/_administration/images/family.gif',
		KT_ROOT . 'themes/_administration/images/indi.gif',
		KT_ROOT . 'themes/_administration/images/larrow2.gif',
		KT_ROOT . 'themes/_administration/images/larrow.gif',
		KT_ROOT . 'themes/_administration/images/ldarrow.gif',
		KT_ROOT . 'themes/_administration/images/media.gif',
		KT_ROOT . 'themes/_administration/images/note.gif',
		KT_ROOT . 'themes/_administration/images/rarrow2.gif',
		KT_ROOT . 'themes/_administration/images/rarrow.gif',
		KT_ROOT . 'themes/_administration/images/rdarrow.gif',
		KT_ROOT . 'themes/_administration/images/repository.gif',
		KT_ROOT . 'themes/_administration/images/sex_f_9x9.gif',
		KT_ROOT . 'themes/_administration/images/sex_m_9x9.gif',
		KT_ROOT . 'themes/_administration/images/sex_u_9x9.gif',
		KT_ROOT . 'themes/_administration/images/source.gif',
		KT_ROOT . 'themes/_administration/images/trashcan.png',
		KT_ROOT . 'themes/_administration/images/uarrow2.gif',
		KT_ROOT . 'themes/_administration/images/uarrow.gif',
		KT_ROOT . 'themes/_administration/images/udarrow.gif',
		KT_ROOT . 'themes/clouds/images/add.gif',
		KT_ROOT . 'themes/clouds/images/admin.gif',
		KT_ROOT . 'themes/clouds/images/buttons/addmedia.gif',
		KT_ROOT . 'themes/clouds/images/buttons/addnote.gif',
		KT_ROOT . 'themes/clouds/images/buttons/addrepository.gif',
		KT_ROOT . 'themes/clouds/images/buttons/addsource.gif',
		KT_ROOT . 'themes/clouds/images/buttons/autocomplete.gif',
		KT_ROOT . 'themes/clouds/images/buttons/calendar.gif',
		KT_ROOT . 'themes/clouds/images/buttons/family.gif',
		KT_ROOT . 'themes/clouds/images/buttons/head.gif',
		KT_ROOT . 'themes/clouds/images/buttons/indi.gif',
		KT_ROOT . 'themes/clouds/images/buttons/keyboard.gif',
		KT_ROOT . 'themes/clouds/images/buttons/media.gif',
		KT_ROOT . 'themes/clouds/images/buttons/note.gif',
		KT_ROOT . 'themes/clouds/images/buttons/place.gif',
		KT_ROOT . 'themes/clouds/images/buttons/repository.gif',
		KT_ROOT . 'themes/clouds/images/buttons/source.gif',
		KT_ROOT . 'themes/clouds/images/buttons/target.gif',
		KT_ROOT . 'themes/clouds/images/center.gif',
		KT_ROOT . 'themes/clouds/images/cfamily.gif',
		KT_ROOT . 'themes/clouds/images/childless.gif',
		KT_ROOT . 'themes/clouds/images/children.gif',
		KT_ROOT . 'themes/clouds/images/clippings.gif',
		KT_ROOT . 'themes/clouds/images/clouds.gif',
		KT_ROOT . 'themes/clouds/images/darrow2.gif',
		KT_ROOT . 'themes/clouds/images/darrow.gif',
		KT_ROOT . 'themes/clouds/images/ddarrow.gif',
		KT_ROOT . 'themes/clouds/images/dline2.gif',
		KT_ROOT . 'themes/clouds/images/dline.gif',
		KT_ROOT . 'themes/clouds/images/edit_indi.gif',
		KT_ROOT . 'themes/clouds/images/favorites.gif',
		KT_ROOT . 'themes/clouds/images/fscreen.gif',
		KT_ROOT . 'themes/clouds/images/go.gif',
		KT_ROOT . 'themes/clouds/images/help.gif',
		KT_ROOT . 'themes/clouds/images/hline.gif',
		KT_ROOT . 'themes/clouds/images/indis.gif',
		KT_ROOT . 'themes/clouds/images/itree.gif',
		KT_ROOT . 'themes/clouds/images/larrow2.gif',
		KT_ROOT . 'themes/clouds/images/larrow.gif',
		KT_ROOT . 'themes/clouds/images/ldarrow.gif',
		KT_ROOT . 'themes/clouds/images/left1B.gif',
		KT_ROOT . 'themes/clouds/images/left2.gif',
		KT_ROOT . 'themes/clouds/images/left3.gif',
		KT_ROOT . 'themes/clouds/images/li.gif',
		KT_ROOT . 'themes/clouds/images/lists.gif',
		KT_ROOT . 'themes/clouds/images/media/doc.gif',
		KT_ROOT . 'themes/clouds/images/media/ged.gif',
		KT_ROOT . 'themes/clouds/images/media.gif',
		KT_ROOT . 'themes/clouds/images/media/html.gif',
		KT_ROOT . 'themes/clouds/images/media/pdf.gif',
		KT_ROOT . 'themes/clouds/images/media/tex.gif',
		KT_ROOT . 'themes/clouds/images/menu_help.gif',
		KT_ROOT . 'themes/clouds/images/menu_media.gif',
		KT_ROOT . 'themes/clouds/images/menu_note.gif',
		KT_ROOT . 'themes/clouds/images/menu_repository.gif',
		KT_ROOT . 'themes/clouds/images/minus.gif',
		KT_ROOT . 'themes/clouds/images/move.gif',
		KT_ROOT . 'themes/clouds/images/mypage.gif',
		KT_ROOT . 'themes/clouds/images/notes.gif',
		KT_ROOT . 'themes/clouds/images/patriarch.gif',
		KT_ROOT . 'themes/clouds/images/pedigree.gif',
		KT_ROOT . 'themes/clouds/images/place.gif',
		KT_ROOT . 'themes/clouds/images/plus.gif',
		KT_ROOT . 'themes/clouds/images/rarrow2.gif',
		KT_ROOT . 'themes/clouds/images/rarrow.gif',
		KT_ROOT . 'themes/clouds/images/rdarrow.gif',
		KT_ROOT . 'themes/clouds/images/readme.txt',
		KT_ROOT . 'themes/clouds/images/relationship.gif',
		KT_ROOT . 'themes/clouds/images/reminder.gif',
		KT_ROOT . 'themes/clouds/images/remove.gif',
		KT_ROOT . 'themes/clouds/images/report.gif',
		KT_ROOT . 'themes/clouds/images/repository.gif',
		KT_ROOT . 'themes/clouds/images/right1B.gif',
		KT_ROOT . 'themes/clouds/images/right3.gif',
		KT_ROOT . 'themes/clouds/images/rings.gif',
		KT_ROOT . 'themes/clouds/images/sex_f_15x15.gif',
		KT_ROOT . 'themes/clouds/images/sex_f_9x9.gif',
		KT_ROOT . 'themes/clouds/images/sex_m_15x15.gif',
		KT_ROOT . 'themes/clouds/images/sex_m_9x9.gif',
		KT_ROOT . 'themes/clouds/images/sex_u_15x15.gif',
		KT_ROOT . 'themes/clouds/images/sex_u_9x9.gif',
		KT_ROOT . 'themes/clouds/images/sfamily.gif',
		KT_ROOT . 'themes/clouds/images/source.gif',
		KT_ROOT . 'themes/clouds/images/spacer.gif',
		KT_ROOT . 'themes/clouds/images/statistic.gif',
		KT_ROOT . 'themes/clouds/images/stop.gif',
		KT_ROOT . 'themes/clouds/images/timeline.gif',
		KT_ROOT . 'themes/clouds/images/uarrow2.gif',
		KT_ROOT . 'themes/clouds/images/uarrow.gif',
		KT_ROOT . 'themes/clouds/images/udarrow.gif',
		KT_ROOT . 'themes/clouds/images/vline.gif',
		KT_ROOT . 'themes/clouds/images/warning.gif',
		KT_ROOT . 'themes/clouds/images/wiki.png',
		KT_ROOT . 'themes/clouds/images/zoomin.gif',
		KT_ROOT . 'themes/clouds/images/zoomout.gif',
		KT_ROOT . 'themes/clouds/modules.css',
		KT_ROOT . 'themes/colors/images/add.gif',
		KT_ROOT . 'themes/colors/images/admin.gif',
		KT_ROOT . 'themes/colors/images/center.gif',
		KT_ROOT . 'themes/colors/images/childless.gif',
		KT_ROOT . 'themes/colors/images/children.gif',
		KT_ROOT . 'themes/colors/images/clippings.gif',
		KT_ROOT . 'themes/colors/images/darrow2.gif',
		KT_ROOT . 'themes/colors/images/darrow.gif',
		KT_ROOT . 'themes/colors/images/ddarrow.gif',
		KT_ROOT . 'themes/colors/images/dline2.gif',
		KT_ROOT . 'themes/colors/images/dline.gif',
		KT_ROOT . 'themes/colors/images/edit_indi.gif',
		KT_ROOT . 'themes/colors/images/favorites.gif',
		KT_ROOT . 'themes/colors/images/fscreen.gif',
		KT_ROOT . 'themes/colors/images/go.gif',
		KT_ROOT . 'themes/colors/images/help.gif',
		KT_ROOT . 'themes/colors/images/hline.gif',
		KT_ROOT . 'themes/colors/images/larrow2.gif',
		KT_ROOT . 'themes/colors/images/larrow.gif',
		KT_ROOT . 'themes/colors/images/ldarrow.gif',
		KT_ROOT . 'themes/colors/images/media.gif',
		KT_ROOT . 'themes/colors/images/menu_media.gif',
		KT_ROOT . 'themes/colors/images/menu_repository.gif',
		KT_ROOT . 'themes/colors/images/minus.gif',
		KT_ROOT . 'themes/colors/images/move.gif',
		KT_ROOT . 'themes/colors/images/mypage.gif',
		KT_ROOT . 'themes/colors/images/notes.gif',
		KT_ROOT . 'themes/colors/images/pedigree.gif',
		KT_ROOT . 'themes/colors/images/plus.gif',
		KT_ROOT . 'themes/colors/images/rarrow2.gif',
		KT_ROOT . 'themes/colors/images/rarrow.gif',
		KT_ROOT . 'themes/colors/images/rdarrow.gif',
		KT_ROOT . 'themes/colors/images/reminder.gif',
		KT_ROOT . 'themes/colors/images/remove.gif',
		KT_ROOT . 'themes/colors/images/report.gif',
		KT_ROOT . 'themes/colors/images/repository.gif',
		KT_ROOT . 'themes/colors/images/rings.gif',
		KT_ROOT . 'themes/colors/images/sex_f_15x15.gif',
		KT_ROOT . 'themes/colors/images/sex_f_9x9.gif',
		KT_ROOT . 'themes/colors/images/sex_m_15x15.gif',
		KT_ROOT . 'themes/colors/images/sex_m_9x9.gif',
		KT_ROOT . 'themes/colors/images/sex_u_15x15.gif',
		KT_ROOT . 'themes/colors/images/sex_u_9x9.gif',
		KT_ROOT . 'themes/colors/images/spacer.gif',
		KT_ROOT . 'themes/colors/images/stop.gif',
		KT_ROOT . 'themes/colors/images/uarrow2.gif',
		KT_ROOT . 'themes/colors/images/uarrow.gif',
		KT_ROOT . 'themes/colors/images/udarrow.gif',
		KT_ROOT . 'themes/colors/images/vline.gif',
		KT_ROOT . 'themes/colors/images/warning.gif',
		KT_ROOT . 'themes/colors/images/zoomin.gif',
		KT_ROOT . 'themes/colors/images/zoomout.gif',
		KT_ROOT . 'themes/colors/modules.css',
		KT_ROOT . 'themes/fab/images/add.gif',
		KT_ROOT . 'themes/fab/images/admin.gif',
		KT_ROOT . 'themes/fab/images/ancestry.gif',
		KT_ROOT . 'themes/fab/images/buttons/addmedia.gif',
		KT_ROOT . 'themes/fab/images/buttons/addnote.gif',
		KT_ROOT . 'themes/fab/images/buttons/addrepository.gif',
		KT_ROOT . 'themes/fab/images/buttons/addsource.gif',
		KT_ROOT . 'themes/fab/images/buttons/autocomplete.gif',
		KT_ROOT . 'themes/fab/images/buttons/calendar.gif',
		KT_ROOT . 'themes/fab/images/buttons/family.gif',
		KT_ROOT . 'themes/fab/images/buttons/head.gif',
		KT_ROOT . 'themes/fab/images/buttons/indi.gif',
		KT_ROOT . 'themes/fab/images/buttons/keyboard.gif',
		KT_ROOT . 'themes/fab/images/buttons/media.gif',
		KT_ROOT . 'themes/fab/images/buttons/note.gif',
		KT_ROOT . 'themes/fab/images/buttons/place.gif',
		KT_ROOT . 'themes/fab/images/buttons/repository.gif',
		KT_ROOT . 'themes/fab/images/buttons/source.gif',
		KT_ROOT . 'themes/fab/images/buttons/target.gif',
		KT_ROOT . 'themes/fab/images/calendar.gif',
		KT_ROOT . 'themes/fab/images/center.gif',
		KT_ROOT . 'themes/fab/images/cfamily.gif',
		KT_ROOT . 'themes/fab/images/childless.gif',
		KT_ROOT . 'themes/fab/images/children.gif',
		KT_ROOT . 'themes/fab/images/clippings.gif',
		KT_ROOT . 'themes/fab/images/darrow2.gif',
		KT_ROOT . 'themes/fab/images/darrow.gif',
		KT_ROOT . 'themes/fab/images/ddarrow.gif',
		KT_ROOT . 'themes/fab/images/descendancy.gif',
		KT_ROOT . 'themes/fab/images/dline2.gif',
		KT_ROOT . 'themes/fab/images/dline.gif',
		KT_ROOT . 'themes/fab/images/edit_fam.gif',
		KT_ROOT . 'themes/fab/images/edit_indi.gif',
		KT_ROOT . 'themes/fab/images/edit_repo.gif',
		KT_ROOT . 'themes/fab/images/edit_sm.png',
		KT_ROOT . 'themes/fab/images/edit_sour.gif',
		KT_ROOT . 'themes/fab/images/fambook.gif',
		KT_ROOT . 'themes/fab/images/fanchart.gif',
		KT_ROOT . 'themes/fab/images/favorites.gif',
		KT_ROOT . 'themes/fab/images/forbidden.gif',
		KT_ROOT . 'themes/fab/images/fscreen.gif',
		KT_ROOT . 'themes/fab/images/gedcom.gif',
		KT_ROOT . 'themes/fab/images/help.gif',
		KT_ROOT . 'themes/fab/images/hline.gif',
		KT_ROOT . 'themes/fab/images/hourglass.gif',
		KT_ROOT . 'themes/fab/images/indis.gif',
		KT_ROOT . 'themes/fab/images/itree.gif',
		KT_ROOT . 'themes/fab/images/larrow2.gif',
		KT_ROOT . 'themes/fab/images/larrow.gif',
		KT_ROOT . 'themes/fab/images/ldarrow.gif',
		KT_ROOT . 'themes/fab/images/media/doc.gif',
		KT_ROOT . 'themes/fab/images/media/ged.gif',
		KT_ROOT . 'themes/fab/images/media.gif',
		KT_ROOT . 'themes/fab/images/media/html.gif',
		KT_ROOT . 'themes/fab/images/media/pdf.gif',
		KT_ROOT . 'themes/fab/images/media/tex.gif',
		KT_ROOT . 'themes/fab/images/minus.gif',
		KT_ROOT . 'themes/fab/images/move.gif',
		KT_ROOT . 'themes/fab/images/mypage.gif',
		KT_ROOT . 'themes/fab/images/patriarch.gif',
		KT_ROOT . 'themes/fab/images/pedigree.gif',
		KT_ROOT . 'themes/fab/images/pix1.gif',
		KT_ROOT . 'themes/fab/images/place.gif',
		KT_ROOT . 'themes/fab/images/plus.gif',
		KT_ROOT . 'themes/fab/images/rarrow2.gif',
		KT_ROOT . 'themes/fab/images/rarrow.gif',
		KT_ROOT . 'themes/fab/images/rdarrow.gif',
		KT_ROOT . 'themes/fab/images/relationship.gif',
		KT_ROOT . 'themes/fab/images/reminder.gif',
		KT_ROOT . 'themes/fab/images/remove.gif',
		KT_ROOT . 'themes/fab/images/reports.gif',
		KT_ROOT . 'themes/fab/images/repository.gif',
		KT_ROOT . 'themes/fab/images/rings.gif',
		KT_ROOT . 'themes/fab/images/search.gif',
		KT_ROOT . 'themes/fab/images/sex_f_15x15.gif',
		KT_ROOT . 'themes/fab/images/sex_f_9x9.gif',
		KT_ROOT . 'themes/fab/images/sex_m_15x15.gif',
		KT_ROOT . 'themes/fab/images/sex_m_9x9.gif',
		KT_ROOT . 'themes/fab/images/sex_u_15x15.gif',
		KT_ROOT . 'themes/fab/images/sex_u_9x9.gif',
		KT_ROOT . 'themes/fab/images/sfamily.gif',
		KT_ROOT . 'themes/fab/images/source.gif',
		KT_ROOT . 'themes/fab/images/spacer.gif',
		KT_ROOT . 'themes/fab/images/statistic.gif',
		KT_ROOT . 'themes/fab/images/stop.gif',
		KT_ROOT . 'themes/fab/images/timeline.gif',
		KT_ROOT . 'themes/fab/images/topdown.gif',
		KT_ROOT . 'themes/fab/images/uarrow2.gif',
		KT_ROOT . 'themes/fab/images/uarrow.gif',
		KT_ROOT . 'themes/fab/images/udarrow.gif',
		KT_ROOT . 'themes/fab/images/vline.gif',
		KT_ROOT . 'themes/fab/images/warning.gif',
		KT_ROOT . 'themes/fab/images/zoomin.gif',
		KT_ROOT . 'themes/fab/images/zoomout.gif',
		KT_ROOT . 'themes/fab/modules.css',
		KT_ROOT . 'themes/minimal/images/add.gif',
		KT_ROOT . 'themes/minimal/images/admin.gif',
		KT_ROOT . 'themes/minimal/images/ancestry.gif',
		KT_ROOT . 'themes/minimal/images/buttons/addmedia.gif',
		KT_ROOT . 'themes/minimal/images/buttons/addnote.gif',
		KT_ROOT . 'themes/minimal/images/buttons/addrepository.gif',
		KT_ROOT . 'themes/minimal/images/buttons/addsource.gif',
		KT_ROOT . 'themes/minimal/images/buttons/calendar.gif',
		KT_ROOT . 'themes/minimal/images/buttons/family.gif',
		KT_ROOT . 'themes/minimal/images/buttons/head.gif',
		KT_ROOT . 'themes/minimal/images/buttons/indi.gif',
		KT_ROOT . 'themes/minimal/images/buttons/keyboard.gif',
		KT_ROOT . 'themes/minimal/images/buttons/media.gif',
		KT_ROOT . 'themes/minimal/images/buttons/note.gif',
		KT_ROOT . 'themes/minimal/images/buttons/place.gif',
		KT_ROOT . 'themes/minimal/images/buttons/repository.gif',
		KT_ROOT . 'themes/minimal/images/buttons/source.gif',
		KT_ROOT . 'themes/minimal/images/buttons/target.gif',
		KT_ROOT . 'themes/minimal/images/calendar.gif',
		KT_ROOT . 'themes/minimal/images/center.gif',
		KT_ROOT . 'themes/minimal/images/cfamily.gif',
		KT_ROOT . 'themes/minimal/images/childless.gif',
		KT_ROOT . 'themes/minimal/images/children.gif',
		KT_ROOT . 'themes/minimal/images/clippings.gif',
		KT_ROOT . 'themes/minimal/images/darrow2.gif',
		KT_ROOT . 'themes/minimal/images/darrow.gif',
		KT_ROOT . 'themes/minimal/images/ddarrow.gif',
		KT_ROOT . 'themes/minimal/images/descendancy.gif',
		KT_ROOT . 'themes/minimal/images/dline2.gif',
		KT_ROOT . 'themes/minimal/images/dline.gif',
		KT_ROOT . 'themes/minimal/images/fambook.gif',
		KT_ROOT . 'themes/minimal/images/fanchart.gif',
		KT_ROOT . 'themes/minimal/images/fscreen.gif',
		KT_ROOT . 'themes/minimal/images/gedcom.gif',
		KT_ROOT . 'themes/minimal/images/help.gif',
		KT_ROOT . 'themes/minimal/images/hline.gif',
		KT_ROOT . 'themes/minimal/images/indis.gif',
		KT_ROOT . 'themes/minimal/images/itree.gif',
		KT_ROOT . 'themes/minimal/images/larrow2.gif',
		KT_ROOT . 'themes/minimal/images/larrow.gif',
		KT_ROOT . 'themes/minimal/images/ldarrow.gif',
		KT_ROOT . 'themes/minimal/images/media/doc.gif',
		KT_ROOT . 'themes/minimal/images/media/ged.gif',
		KT_ROOT . 'themes/minimal/images/media.gif',
		KT_ROOT . 'themes/minimal/images/media/html.gif',
		KT_ROOT . 'themes/minimal/images/media/pdf.gif',
		KT_ROOT . 'themes/minimal/images/media/tex.gif',
		KT_ROOT . 'themes/minimal/images/minus.gif',
		KT_ROOT . 'themes/minimal/images/move.gif',
		KT_ROOT . 'themes/minimal/images/mypage.gif',
		KT_ROOT . 'themes/minimal/images/notes.gif',
		KT_ROOT . 'themes/minimal/images/patriarch.gif',
		KT_ROOT . 'themes/minimal/images/pedigree.gif',
		KT_ROOT . 'themes/minimal/images/place.gif',
		KT_ROOT . 'themes/minimal/images/plus.gif',
		KT_ROOT . 'themes/minimal/images/rarrow2.gif',
		KT_ROOT . 'themes/minimal/images/rarrow.gif',
		KT_ROOT . 'themes/minimal/images/rdarrow.gif',
		KT_ROOT . 'themes/minimal/images/relationship.gif',
		KT_ROOT . 'themes/minimal/images/reminder.gif',
		KT_ROOT . 'themes/minimal/images/remove.gif',
		KT_ROOT . 'themes/minimal/images/report.gif',
		KT_ROOT . 'themes/minimal/images/repository.gif',
		KT_ROOT . 'themes/minimal/images/rings.gif',
		KT_ROOT . 'themes/minimal/images/search.gif',
		KT_ROOT . 'themes/minimal/images/sex_f_15x15.gif',
		KT_ROOT . 'themes/minimal/images/sex_f_9x9.gif',
		KT_ROOT . 'themes/minimal/images/sex_m_15x15.gif',
		KT_ROOT . 'themes/minimal/images/sex_m_9x9.gif',
		KT_ROOT . 'themes/minimal/images/sex_u_15x15.gif',
		KT_ROOT . 'themes/minimal/images/sex_u_9x9.gif',
		KT_ROOT . 'themes/minimal/images/sfamily.gif',
		KT_ROOT . 'themes/minimal/images/source.gif',
		KT_ROOT . 'themes/minimal/images/spacer.gif',
		KT_ROOT . 'themes/minimal/images/stop.gif',
		KT_ROOT . 'themes/minimal/images/timeline.gif',
		KT_ROOT . 'themes/minimal/images/uarrow2.gif',
		KT_ROOT . 'themes/minimal/images/uarrow.gif',
		KT_ROOT . 'themes/minimal/images/udarrow.gif',
		KT_ROOT . 'themes/minimal/images/vline.gif',
		KT_ROOT . 'themes/minimal/images/warning.gif',
		KT_ROOT . 'themes/minimal/images/zoomin.gif',
		KT_ROOT . 'themes/minimal/images/zoomout.gif',
		KT_ROOT . 'themes/minimal/modules.css',
		KT_ROOT . 'themes/webtrees/images/center.gif',
		KT_ROOT . 'themes/webtrees/images/fscreen.gif',
		KT_ROOT . 'themes/webtrees/modules.css',
		KT_ROOT . 'themes/xenea/images/center.gif',
		KT_ROOT . 'themes/xenea/images/fscreen.gif',
		KT_ROOT . 'themes/xenea/images/pixel.gif',
		KT_ROOT . 'themes/xenea/images/remove.gif',
		KT_ROOT . 'themes/xenea/modules.css',
		// Removed in 1.2.5
		KT_ROOT . 'includes/media_reorder_count.php',
		KT_ROOT . 'includes/media_tab_head.php',
		KT_ROOT . 'js/behaviour.js.htm',
		KT_ROOT . 'js/bennolan',
		KT_ROOT . 'js/bosrup',
		KT_ROOT . 'js/kryogenix',
		KT_ROOT . 'js/overlib.js.htm',
		KT_ROOT . 'js/scriptaculous',
		KT_ROOT . 'js/scriptaculous.js.htm',
		KT_ROOT . 'js/sorttable.js.htm',
		KT_ROOT . 'library/KT/JS.php',
		KT_ROOT . 'modules_v3/clippings/index.php',
		KT_ROOT . 'modules_v3/googlemap/css/googlemap_style.css',
		KT_ROOT . 'modules_v3/googlemap/css/wt_v3_places_edit.css',
		KT_ROOT . 'modules_v3/googlemap/index.php',
		KT_ROOT . 'modules_v3/lightbox/index.php',
		KT_ROOT . 'modules_v3/recent_changes/help_text.php',
		KT_ROOT . 'modules_v3/todays_events/help_text.php',
		KT_ROOT . 'sidebar.php',
		// Removed in 1.2.6
		KT_ROOT . 'modules_v3/sitemap/admin_index.php',
		KT_ROOT . 'modules_v3/sitemap/help_text.php',
		KT_ROOT . 'modules_v3/tree/css/styles',
		KT_ROOT . 'modules_v3/tree/css/treebottom.gif',
		KT_ROOT . 'modules_v3/tree/css/treebottomleft.gif',
		KT_ROOT . 'modules_v3/tree/css/treebottomright.gif',
		KT_ROOT . 'modules_v3/tree/css/tree.jpg',
		KT_ROOT . 'modules_v3/tree/css/treeleft.gif',
		KT_ROOT . 'modules_v3/tree/css/treeright.gif',
		KT_ROOT . 'modules_v3/tree/css/treetop.gif',
		KT_ROOT . 'modules_v3/tree/css/treetopleft.gif',
		KT_ROOT . 'modules_v3/tree/css/treetopright.gif',
		KT_ROOT . 'modules_v3/tree/css/treeview_print.css',
		KT_ROOT . 'modules_v3/tree/help_text.php',
		KT_ROOT . 'modules_v3/tree/images/print.png',
		KT_ROOT . 'themes/clouds/images/fscreen.png',
		KT_ROOT . 'themes/colors/images/fscreen.png',
		KT_ROOT . 'themes/fab/images/fscreen.png',
		KT_ROOT . 'themes/minimal/images/fscreen.png',
		KT_ROOT . 'themes/webtrees/images/fscreen.png',
		KT_ROOT . 'themes/xenea/images/fscreen.png',
		// Removed in 1.2.7
		KT_ROOT . 'login_register.php',
		KT_ROOT . 'modules_v3/top10_givnnames/help_text.php',
		KT_ROOT . 'modules_v3/top10_surnames/help_text.php',
		KT_ROOT . 'themes/clouds/images/center.png',
		KT_ROOT . 'themes/colors/images/center.png',
		KT_ROOT . 'themes/fab/images/center.png',
		KT_ROOT . 'themes/minimal/images/center.png',
		KT_ROOT . 'themes/webtrees/images/center.png',
		KT_ROOT . 'themes/xenea/images/center.png',
		// Removed in 1.3.0
		KT_ROOT . 'admin_site_ipaddress.php',
		KT_ROOT . 'downloadgedcom.php',
		KT_ROOT . 'export_gedcom.php',
		KT_ROOT . 'gedcheck.php',
		KT_ROOT . 'images',
		KT_ROOT . 'includes/dmsounds_UTF8.php',
		KT_ROOT . 'includes/functions/functions_name.php',
		KT_ROOT . 'includes/grampsxml.rng',
		KT_ROOT . 'includes/session_spider.php',
		KT_ROOT . 'js/autocomplete.js.htm',
		KT_ROOT . 'js/prototype',
		KT_ROOT . 'js/prototype.js.htm',
		KT_ROOT . 'modules_v3/googlemap/admin_editconfig.php',
		KT_ROOT . 'modules_v3/googlemap/admin_placecheck.php',
		KT_ROOT . 'modules_v3/googlemap/flags.php',
		KT_ROOT . 'modules_v3/googlemap/images/pedigree_map.gif',
		KT_ROOT . 'modules_v3/googlemap/pedigree_map.php',
		KT_ROOT . 'modules_v3/lightbox/admin_config.php',
		KT_ROOT . 'modules_v3/lightbox/album.php',
		KT_ROOT . 'modules_v3/lightbox/functions/lb_call_js.php',
		KT_ROOT . 'modules_v3/lightbox/functions/lb_head.php',
		KT_ROOT . 'modules_v3/lightbox/functions/lb_link.php',
		KT_ROOT . 'modules_v3/lightbox/functions/lightbox_print_media_row.php',
		KT_ROOT . 'modules_v3/tree/css/vline.jpg',
		KT_ROOT . 'themes/_administration/images/darrow2.png',
		KT_ROOT . 'themes/_administration/images/darrow.png',
		KT_ROOT . 'themes/_administration/images/ddarrow.png',
		KT_ROOT . 'themes/_administration/images/delete_grey.png',
		KT_ROOT . 'themes/_administration/images/family.png',
		KT_ROOT . 'themes/_administration/images/find_facts.png',
		KT_ROOT . 'themes/_administration/images/header.png',
		KT_ROOT . 'themes/_administration/images/help.png',
		KT_ROOT . 'themes/_administration/images/indi.png',
		KT_ROOT . 'themes/_administration/images/larrow2.png',
		KT_ROOT . 'themes/_administration/images/larrow.png',
		KT_ROOT . 'themes/_administration/images/ldarrow.png',
		KT_ROOT . 'themes/_administration/images/media.png',
		KT_ROOT . 'themes/_administration/images/note.png',
		KT_ROOT . 'themes/_administration/images/rarrow2.png',
		KT_ROOT . 'themes/_administration/images/rarrow.png',
		KT_ROOT . 'themes/_administration/images/rdarrow.png',
		KT_ROOT . 'themes/_administration/images/repository.png',
		KT_ROOT . 'themes/_administration/images/source.png',
		KT_ROOT . 'themes/_administration/images/uarrow2.png',
		KT_ROOT . 'themes/_administration/images/uarrow.png',
		KT_ROOT . 'themes/_administration/images/udarrow.png',
		KT_ROOT . 'themes/clouds/images/favorites.png',
		KT_ROOT . 'themes/clouds/images/lists.png',
		KT_ROOT . 'themes/clouds/images/menu_media.png',
		KT_ROOT . 'themes/clouds/images/menu_note.png',
		KT_ROOT . 'themes/clouds/images/menu_repository.png',
		KT_ROOT . 'themes/clouds/images/relationship.png',
		KT_ROOT . 'themes/clouds/images/reorder_images.png',
		KT_ROOT . 'themes/clouds/images/report.png',
		KT_ROOT . 'themes/colors/images/favorites.png',
		KT_ROOT . 'themes/colors/images/menu_media.png',
		KT_ROOT . 'themes/colors/images/menu_note.png',
		KT_ROOT . 'themes/colors/images/menu_repository.png',
		KT_ROOT . 'themes/colors/images/reorder_images.png',
		KT_ROOT . 'themes/fab/images/ancestry.png',
		KT_ROOT . 'themes/fab/images/calendar.png',
		KT_ROOT . 'themes/fab/images/descendancy.png',
		KT_ROOT . 'themes/fab/images/edit_fam.png',
		KT_ROOT . 'themes/fab/images/edit_repo.png',
		KT_ROOT . 'themes/fab/images/edit_sour.png',
		KT_ROOT . 'themes/fab/images/fanchart.png',
		KT_ROOT . 'themes/fab/images/favorites.png',
		KT_ROOT . 'themes/fab/images/hourglass.png',
		KT_ROOT . 'themes/fab/images/itree.png',
		KT_ROOT . 'themes/fab/images/relationship.png',
		KT_ROOT . 'themes/fab/images/reorder_images.png',
		KT_ROOT . 'themes/fab/images/reports.png',
		KT_ROOT . 'themes/fab/images/statistic.png',
		KT_ROOT . 'themes/fab/images/timeline.png',
		KT_ROOT . 'themes/minimal/images/ancestry.png',
		KT_ROOT . 'themes/minimal/images/buttons',
		KT_ROOT . 'themes/minimal/images/descendancy.png',
		KT_ROOT . 'themes/minimal/images/fanchart.png',
		KT_ROOT . 'themes/minimal/images/itree.png',
		KT_ROOT . 'themes/minimal/images/relationship.png',
		KT_ROOT . 'themes/minimal/images/report.png',
		KT_ROOT . 'themes/minimal/images/timeline.png',
		KT_ROOT . 'themes/minimal/images/webtrees.png',
		KT_ROOT . 'themes/webtrees/images/ancestry.png',
		KT_ROOT . 'themes/webtrees/images/descendancy.png',
		KT_ROOT . 'themes/webtrees/images/fanchart.png',
		KT_ROOT . 'themes/webtrees/images/favorites.png',
		KT_ROOT . 'themes/webtrees/images/hourglass.png',
		KT_ROOT . 'themes/webtrees/images/media/audio.png',
		KT_ROOT . 'themes/webtrees/images/media/doc.png',
		KT_ROOT . 'themes/webtrees/images/media/flash.png',
		KT_ROOT . 'themes/webtrees/images/media/flashrem.png',
		KT_ROOT . 'themes/webtrees/images/media/pdf.png',
		KT_ROOT . 'themes/webtrees/images/media/picasa.png',
		KT_ROOT . 'themes/webtrees/images/media/tex.png',
		KT_ROOT . 'themes/webtrees/images/media/unknown.png',
		KT_ROOT . 'themes/webtrees/images/media/wmv.png',
		KT_ROOT . 'themes/webtrees/images/media/wmvrem.png',
		KT_ROOT . 'themes/webtrees/images/media/www.png',
		KT_ROOT . 'themes/webtrees/images/relationship.png',
		KT_ROOT . 'themes/webtrees/images/reorder_images.png',
		KT_ROOT . 'themes/webtrees/images/statistic.png',
		KT_ROOT . 'themes/webtrees/images/timeline.png',
		KT_ROOT . 'themes/webtrees/images/w_22.png',
		KT_ROOT . 'themes/xenea/images/ancestry.png',
		KT_ROOT . 'themes/xenea/images/calendar.png',
		KT_ROOT . 'themes/xenea/images/descendancy.png',
		KT_ROOT . 'themes/xenea/images/edit_fam.png',
		KT_ROOT . 'themes/xenea/images/edit_repo.png',
		KT_ROOT . 'themes/xenea/images/edit_sour.png',
		KT_ROOT . 'themes/xenea/images/fanchart.png',
		KT_ROOT . 'themes/xenea/images/gedcom.png',
		KT_ROOT . 'themes/xenea/images/hourglass.png',
		KT_ROOT . 'themes/xenea/images/menu_help.png',
		KT_ROOT . 'themes/xenea/images/menu_media.png',
		KT_ROOT . 'themes/xenea/images/menu_note.png',
		KT_ROOT . 'themes/xenea/images/menu_repository.png',
		KT_ROOT . 'themes/xenea/images/menu_source.png',
		KT_ROOT . 'themes/xenea/images/relationship.png',
		KT_ROOT . 'themes/xenea/images/reorder_images.png',
		KT_ROOT . 'themes/xenea/images/report.png',
		KT_ROOT . 'themes/xenea/images/statistic.png',
		KT_ROOT . 'themes/xenea/images/timeline.png',
		KT_ROOT . 'themes/xenea/images/w_22.png',
		// Removed in 1.3.1
		KT_ROOT . 'imageflush.php',
		KT_ROOT . 'includes/functions/functions_places.php',
		KT_ROOT . 'js/html5.js',
		KT_ROOT . 'modules_v3/googlemap/wt_v3_pedigree_map.js.php',
		KT_ROOT . 'modules_v3/lightbox/js/tip_balloon_RTL.js',
		// Removed in 1.3.2
		KT_ROOT . 'modules_v3/address_report',
		// Removed in 1.4.0
		KT_ROOT . 'imageview.php',
		KT_ROOT . 'includes/functions/functions_media_reorder.php',
		KT_ROOT . 'js/jquery',
		KT_ROOT . 'js/jw_player',
		KT_ROOT . 'media/MediaInfo.txt',
		KT_ROOT . 'media/thumbs/ThumbsInfo.txt',
		KT_ROOT . 'modules_v3/GEDFact_assistant/css/media_0_inverselink.css',
		KT_ROOT . 'modules_v3/lightbox/help_text.php',
		KT_ROOT . 'modules_v3/lightbox/images/blank.gif',
		KT_ROOT . 'modules_v3/lightbox/images/close_1.gif',
		KT_ROOT . 'modules_v3/lightbox/images/image_add.gif',
		KT_ROOT . 'modules_v3/lightbox/images/image_copy.gif',
		KT_ROOT . 'modules_v3/lightbox/images/image_delete.gif',
		KT_ROOT . 'modules_v3/lightbox/images/image_edit.gif',
		KT_ROOT . 'modules_v3/lightbox/images/image_link.gif',
		KT_ROOT . 'modules_v3/lightbox/images/images.gif',
		KT_ROOT . 'modules_v3/lightbox/images/image_view.gif',
		KT_ROOT . 'modules_v3/lightbox/images/loading.gif',
		KT_ROOT . 'modules_v3/lightbox/images/next.gif',
		KT_ROOT . 'modules_v3/lightbox/images/nextlabel.gif',
		KT_ROOT . 'modules_v3/lightbox/images/norm_2.gif',
		KT_ROOT . 'modules_v3/lightbox/images/overlay.png',
		KT_ROOT . 'modules_v3/lightbox/images/prev.gif',
		KT_ROOT . 'modules_v3/lightbox/images/prevlabel.gif',
		KT_ROOT . 'modules_v3/lightbox/images/private.gif',
		KT_ROOT . 'modules_v3/lightbox/images/slideshow.jpg',
		KT_ROOT . 'modules_v3/lightbox/images/transp80px.gif',
		KT_ROOT . 'modules_v3/lightbox/images/zoom_1.gif',
		KT_ROOT . 'modules_v3/lightbox/js',
		KT_ROOT . 'modules_v3/lightbox/music',
		KT_ROOT . 'modules_v3/lightbox/pic',
		KT_ROOT . 'themes/_administration/images/media',
		KT_ROOT . 'themes/_administration/jquery',
		KT_ROOT . 'themes/clouds/images/media',
		KT_ROOT . 'themes/colors/images/media',
		KT_ROOT . 'themes/fab/images/media',
		KT_ROOT . 'themes/minimal/images/media',
		KT_ROOT . 'themes/webtrees/chrome.css',
		KT_ROOT . 'themes/webtrees/images/media',
		KT_ROOT . 'themes/xenea/images/media',
		// Removed in 1.4.1
		KT_ROOT . 'js/webtrees-1.4.0.js',
		KT_ROOT . 'modules_v3/lightbox/images/image_edit.png',
		KT_ROOT . 'modules_v3/lightbox/images/image_view.png',
		// Removed in 1.4.2
		KT_ROOT . 'modules_v3/lightbox/images/image_view.png',
		KT_ROOT . 'js/jquery.colorbox-1.4.3.js',
		KT_ROOT . 'js/jquery-ui-1.10.0.js',
		KT_ROOT . 'js/webtrees-1.4.1.js',
		KT_ROOT . 'modules_v3/top10_pageviews/help_text.php',
		KT_ROOT . 'themes/_administration/jquery-ui-1.10.0',
		KT_ROOT . 'themes/clouds/jquery-ui-1.10.0',
		KT_ROOT . 'themes/colors/jquery-ui-1.10.0',
		KT_ROOT . 'themes/fab/jquery-ui-1.10.0',
		KT_ROOT . 'themes/minimal/jquery-ui-1.10.0',
		KT_ROOT . 'themes/webtrees/jquery-ui-1.10.0',
		KT_ROOT . 'themes/xenea/jquery-ui-1.10.0',
		// Removed in kiwitrees 2.0.1
		KT_ROOT . 'modules_v3/simpl_research/plugins/findmypastuk.php',
		// Removed in kiwitrees 2.0.2
		KT_ROOT . 'js/jquery-1.10.2.js',
		KT_ROOT . 'js/webtrees-1.4.2.js',
		KT_ROOT . 'js/jquery-ui-1.10.3.js',
		KT_ROOT . 'js/jquery.wheelzoom-1.1.2.js',
		KT_ROOT . 'js/jquery.jeditable-1.7.1.js',
		KT_ROOT . 'js/jquery.cookie-1.4.0.js',
		KT_ROOT . 'js/jquery.datatables-1.9.4.js',
		KT_ROOT . 'js/jquery.colorbox-1.4.15.js',
		KT_ROOT . 'js/modernizr.custom-2.6.2.js',
		KT_ROOT . 'modules_v3/fancy_imagebar/style.css',
		KT_ROOT . 'modules_v3/fancy_imagebar/README.md',
		KT_ROOT . 'modules_v3/media',
		KT_ROOT . 'modules_v3/lightbox',
		// Removed in kiwitrees 3.0.0
		KT_ROOT . 'library/KT/Debug.php',
		KT_ROOT . 'modules_v3/simpl_duplicates',
		KT_ROOT . 'modules_v3/simpl_unlinked',
		KT_ROOT . 'modules_v3/gallery/galleria/galleria-1.3.5.js',
		KT_ROOT . 'modules_v3/gallery/galleria/galleria-1.3.5.min.js',
		KT_ROOT . 'modules_v3/gallery/galleria/galleria-1.3.6.js',
		KT_ROOT . 'modules_v3/gallery/galleria/galleria-1.3.6.min.js',
		KT_ROOT . 'themes/clouds',
		KT_ROOT . 'themes/fab',
		KT_ROOT . 'themes/minimal',
		KT_ROOT . 'themes/simpl_grey',
		KT_ROOT . 'themes/kiwitrees/jquery-ui-1.10.3',
		KT_ROOT . 'js/jquery.js',
		KT_ROOT . 'js/jquery.datatables.js',
		KT_ROOT . 'js/jquery.colorbox.js',
		KT_ROOT . 'modules_v3/user_welcome',
		KT_ROOT . 'modules_v3/user_favorites',
		KT_ROOT . 'modules_v3/user_messages',
		KT_ROOT . 'modules_v3/user_blog',
		KT_ROOT . 'modules_v3/theme_select',
		KT_ROOT . 'modules_v3/menu_mypage',
		// Removed in kiwitrees 3.0.1
		KT_ROOT . 'modules_v3/fancy_treeview_descendants/themes',
		KT_ROOT . 'nocensus.php',
		KT_ROOT . 'language/mi.mo',
		KT_ROOT . 'js/jquery.cookie.js',
		// Removed in kiwitrees 3.2.0
		KT_ROOT . 'library/framework/FontAwesome/css/KT_font-awesome.css',
		KT_ROOT . 'js/webtrees.js',
		KT_ROOT . 'modules_v3/individual_report',
		KT_ROOT . 'modules_v3/change_report',
		// Removed in kiwitrees 3.2.1
		KT_ROOT . 'modules_v3/occupation_report',
		KT_ROOT . 'modules_v3/menu_calendar',
		KT_ROOT . 'modules_v3/googlemap/places_edit.php', //old unused file should have been removed months ago!
		KT_ROOT . 'modules_v3/googlemap/help_text.php',
		KT_ROOT . 'modules_v3/googlemap/places/flags/SGS.png',
		// Removed in kiwitrees 3.2.2
		KT_ROOT . 'search-advanced.php',
		KT_ROOT . 'admin_pgv_to_wt.php',
		KT_ROOT . 'library/framework/Foundation',
		KT_ROOT . 'library/KT/Controller/AdvancedSearch.php',
		KT_ROOT . 'modules_v3/birth_report',
		KT_ROOT . 'modules_v3/death_report',
		KT_ROOT . 'modules_v3/cemetery_report',
		KT_ROOT . 'modules_v3/fancy_imagebar/style.js',
		KT_ROOT . 'modules_v3/simpl_research/plugins/alledrenten.php', //replaced for better sorting
		KT_ROOT . 'modules_v3/simpl_research/plugins/allefriezen.php', //replaced for better sorting
		KT_ROOT . 'modules_v3/simpl_research/plugins/allegroningers.php', //replaced for better sorting
		KT_ROOT . 'modules_v3/simpl_research/plugins/allelimburgers.php', //replaced for better sorting
		KT_ROOT . 'modules_v3/simpl_research/plugins/archief_amsterdam.php', //replaced for better sorting
		KT_ROOT . 'modules_v3/simpl_research/plugins/bhic_nl.php', //replaced for better sorting
		KT_ROOT . 'modules_v3/simpl_research/plugins/dsrotterdam.php', //replaced for better sorting
		KT_ROOT . 'modules_v3/simpl_research/plugins/historischcentrumoverijssel.php', //replaced for better sorting
		KT_ROOT . 'modules_v3/simpl_research/plugins/hetutrechtsarchief.php', //replaced for better sorting
		KT_ROOT . 'modules_v3/simpl_research/plugins/register1939.php', //replaced for better sorting
		KT_ROOT . 'modules_v3/simpl_research/plugins/rhcvechtenvenen.php', //replaced for better sorting
		// Removed in kiwitrees 3.2.3
		KT_ROOT . 'themes/_administration/images/kiwi_webtrees_logo_white.png', // file name changed to remove webtrees
		KT_ROOT . 'modules_v3/descendancy_report', // report replaced by resource version of fancy_treeview_descendants
		KT_ROOT . 'modules_v3/fancy_treeview_descendants/help_text.php', // no longer used
		KT_ROOT . 'library/framework', // content moved to library/
		KT_ROOT . 'admin_trees_sourcecheck.php', // name changed to admin_trees_sourcecite.php
		KT_ROOT . 'modules_v3/fact_sources', // report replaced by admin tool admin_trees_source
		KT_ROOT . 'modules_v3/relative_ext_report', // report replaced by resource version
		KT_ROOT . 'modules_v3/missing_facts_report', // report replaced by admin tool
		KT_ROOT . 'language/tt.mo', // tidying up redundant language files
		KT_ROOT . 'language/bs.mo', // tidying up redundant language files
		KT_ROOT . 'language/fa.mo', // tidying up redundant language files
		KT_ROOT . 'language/sl.mo', // tidying up redundant language files
		KT_ROOT . 'language/zh_CN.mo', // tidying up redundant language files
		KT_ROOT . 'language/ca@valencia.mo', // tidying up redundant language files
		KT_ROOT . 'language/extra', // tidying up redundant language files
		KT_ROOT . 'admin_site_readme.php', // Readme files moved to kiwitrees.net FAQs
		KT_ROOT . 'readme.html', // Readme files moved to kiwitrees.net FAQs
		// Removed in kiwitrees 3.3.0
		KT_ROOT . 'search_advanced.php', // old file no longer used
		KT_ROOT . 'fanchart.php', // file no longer used
		KT_ROOT . 'includes/fonts', // no longer needed in fanchart code
		KT_ROOT . 'GPL.txt', // replaced by LICENSE.md
		KT_ROOT . 'reportengine.php', // old report system removed
		KT_ROOT . 'includes/reportheader.php', // old report system removed
		KT_ROOT . 'reportengine.php', // old report system removed
		KT_ROOT . 'library/tcpdf', // old report system removed
		KT_ROOT . 'library/WT/Report', // old report system removed
		KT_ROOT . 'admin_module_resources.php', // old report system removed, this file renamed to admin_module_reports
		// Removed in kiwitrees 3.3.1
		KT_ROOT . 'modules_v3',
		KT_ROOT . 'modules_v4/gallery/galleria/galleria-1.5.1.js',
		KT_ROOT . 'modules_v4/gallery/galleria/galleria-1.5.1.min.js',
		KT_ROOT . 'admin_site_change.php',
		KT_ROOT . 'admin_site_other.php',
		KT_ROOT . 'admin_site_merge.php',
		KT_ROOT . 'modules_v4/census_assistant/addnoteaction_assisted.php',
		KT_ROOT . 'modules_v4/census_assistant/census_1_ctrl.php',
		KT_ROOT . 'modules_v4/census_assistant/census_2_source_input.php',
		KT_ROOT . 'modules_v4/census_assistant/census_3_find.php',
		KT_ROOT . 'modules_v4/census_assistant/census_3_search_add.php',
		KT_ROOT . 'modules_v4/census_assistant/census_4_text.php',
		KT_ROOT . 'modules_v4/census_assistant/census_5_input.php',
		KT_ROOT . 'modules_v4/census_assistant/census_asst_ctrl.php',
		KT_ROOT . 'modules_v4/census_assistant/census_asst_date.php',
		KT_ROOT . 'modules_v4/census_assistant/census_note_decode.php',
		KT_ROOT . 'modules_v4/census_assistant/js',
		KT_ROOT . 'modules_v4/report_ukregister',
		KT_ROOT . 'modules_v4/widget_messages',
		KT_ROOT . 'modules_v4/report_ukcensus',
		KT_ROOT . 'js/jquery.cookie-1.3.1.js',
		KT_ROOT . 'js/jquery-1.9.1.js',
		KT_ROOT . 'js/jquery-ui.js',
		KT_ROOT . 'js/modernizr.custom-2.6.1.js',
		KT_ROOT . 'js/jquery.autosize.js',
		KT_ROOT . 'includes/old_messages.php',
		KT_ROOT . 'library/phpmailer',
		// Removed in kiwitrees 3.3.2
		KT_ROOT . 'admin_trees_unlinked.php',
		KT_ROOT . 'modules_v4/googlemap/wt_v3_places_edit.js.php',
		KT_ROOT . 'modules_v4/googlemap/wt_v3_places_edit_overlays.js.php',
		KT_ROOT . 'modules_v4/googlemap/wt_v3_googlemap.js.php',
		KT_ROOT . 'modules_v4/googlemap/css/wt_v3_googlemap.css',
		KT_ROOT . 'modules_v4/backup_to_dropbox/backup.php',
		KT_ROOT . 'modules_v4/backup_to_dropbox/dropbox-sdk',
		KT_ROOT . 'modules_v4/research_links/plugins/rotterdamds.php',
		// Removed in kiwitrees 3.3.3
		KT_ROOT . 'modules_v4/research_links/plugins/vlaardingengeschiedenis.php', // archive no longer exists
		KT_ROOT . 'statistics.php', // moved to modules
		KT_ROOT . 'js/d3.v4.custom.min.js', //replaced by standard version
		// Removed in kiwitrees 3.3.8
		KT_ROOT . '/modules_v4/gallery/galleria/galleria-1.5.7.min.js',
		KT_ROOT . '/modules_v4/gallery/galleria/plugins/picasa', // no longer supported
		KT_ROOT . 'modules_v4/research_links/plugins/rijnlandsmiddensa.php', // archive replaced

		// *********************************************
		// Removed in kiwitrees-nova 1.0.0
		// Lists
		KT_ROOT . 'indilist.php',
		KT_ROOT . 'famlist.php',
		KT_ROOT . 'sourcelist.php',
		KT_ROOT . 'repolist.php',
		KT_ROOT . 'notelist.php',
		KT_ROOT . 'branches.php',
		KT_ROOT . 'medialist.php',
		KT_ROOT . 'placelist.php',
		KT_ROOT . 'calendar.php',
		KT_ROOT . 'modules_v4/calendar_utilities',
		// Blocks
		KT_ROOT . 'modules_v4/charts',
		KT_ROOT . 'modules_v4/gedcom_block',
		KT_ROOT . 'modules_v4/gedcom_favorites',
		KT_ROOT . 'modules_v4/gedcom_news',
		KT_ROOT . 'modules_v4/gedcom_stats',
		KT_ROOT . 'modules_v4/html',
		KT_ROOT . 'modules_v4/logged_in',
		KT_ROOT . 'modules_v4/login_block',
		KT_ROOT . 'modules_v4/page_menu',
		KT_ROOT . 'modules_v4/random_media',
		KT_ROOT . 'modules_v4/recent_changes',
		KT_ROOT . 'modules_v4/review_changes',
		KT_ROOT . 'modules_v4/tab_census',
		KT_ROOT . 'modules_v4/todays_events',
		KT_ROOT . 'modules_v4/todo',
		KT_ROOT . 'modules_v4/top10_givnnames',
		KT_ROOT . 'modules_v4/top10_pageviews',
		KT_ROOT . 'modules_v4/top10_surnames',
		KT_ROOT . 'modules_v4/upcoming_events',
		KT_ROOT . 'modules_v4/yahrzeit',
		// Charts
		KT_ROOT . 'ancestry.php',
		// tabs
		KT_ROOT . 'modules_v4/album',
		KT_ROOT . 'modules_v4/census',
		KT_ROOT . 'modules_v4/cousins',
		KT_ROOT . 'modules_v4/notes',
		KT_ROOT . 'modules_v4/personal_facts',
		KT_ROOT . 'modules_v4/relatives',
		KT_ROOT . 'modules_v4/sources_tab',
		KT_ROOT . 'modules_v4/tree',
		//sidebars
		KT_ROOT . 'modules_v4/descendacy',
		KT_ROOT . 'modules_v4/extra_info',
		KT_ROOT . 'modules_v4/families',
		KT_ROOT . 'modules_v4/family_nav',
		KT_ROOT . 'modules_v4/individuals',
		KT_ROOT . 'modules_v4/privacy',
		KT_ROOT . 'modules_v4/menu_login',
		KT_ROOT . 'modules_v4/stories',
		// Other
		KT_ROOT . 'expand_view.php',
		KT_ROOT . 'admin_site_lang.php',
		KT_ROOT . 'js/jquery.colorbox-min.js',
		KT_ROOT . 'modules_v4/pages/help_text.php',

	);
}

// Delete a file or folder, ignoring errors
function delete_recursively($path) {
	@chmod($path, 0777);
	if (is_dir($path)) {
		$dir=opendir($path);
		while ($dir!==false && (($file=readdir($dir))!==false)) {
			if ($file!='.' && $file!='..') {
				delete_recursively($path.'/'.$file);
			}
		}
		closedir($dir);
		@rmdir($path);
	} else {
		@unlink($path);
	}
}
