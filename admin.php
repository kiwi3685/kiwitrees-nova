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

define('KT_SCRIPT_NAME', 'admin.php');

global $iconStyle;
require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Administration'))
	->pageHeader();

// Prepare statistic variables
$stats      = new KT_Stats(KT_GEDCOM);
$totusers   = 0;       // Total number of users
$warnusers  = 0;       // Users with warning
$applusers  = 0;       // Users who have not verified themselves
$nverusers  = 0;       // Users not verified by admin but verified themselves
$adminusers = 0;       // Administrators
$userlang   = array(); // Array for user languages
$gedadmin   = array(); // Array for managers

//Check for kiwitrees-nova updates
$latest_version = fetch_latest_version();

// Server warnings
$server_warnings = array();
$SqlAlertClass  = '';

//Check SQL server version
$versionNumber  	= KT_DB::prepare("select version()")->fetchColumn();
$versionType    	= substr($versionNumber, 0, strpos($versionNumber, '.', ));
if ($versionType < '10') {
	$type           = 'MySQL';
	$version        = $type . ' ' . $versionNumber;
	$minVersion     = KT_REQUIRED_MYSQL_VERSION;
	$versionNo      = $versionNumber;
	$versionCompare = version_compare($versionNo, KT_REQUIRED_MYSQL_VERSION, '>=');
	if ($versionCompare < 0) {
		$SqlAlertClass     = 'class="alert"';
		$server_warnings[] = '
			<span class="warning">' .
				KT_I18N::translate('
					Your web server is using %s1.
					This version of kiwitrees requires a minumum of %s2.',
					$version, $minVersion) .
			'<span>';
	}
} else {
	$type           = 'MariaDB';
	$version        = $type . ' ' . $versionNumber;
	$minVersion     = KT_REQUIRED_MARIADB_VERSION;
	$versionNo      = substr($versionNumber, 0, strpos($versionNumber, '-'));
	$versionCompare = version_compare($versionNo, KT_REQUIRED_MARIADB_VERSION, '>=');
	if ($versionCompare < 0) {
		$SqlAlertClass     = 'class="alert"';
		$server_warnings[] = '
		<span class="warning">' .
			KT_I18N::translate('
				Your database is using %s1.
				This version of kiwitrees requires a minumum of %s2.',
				$version, $minVersion
			) .
		'<span>';
	}
}

//Check SQL server version
$PhpAlertClass = '';
if (
    // security
	PHP_VERSION_ID < 70000 ||
	PHP_VERSION_ID < 70100 && date('Y-m-d') >= '2019-01-10' ||
	PHP_VERSION_ID < 70200 && date('Y-m-d') >= '2019-12-01' ||
	PHP_VERSION_ID < 70300 && date('Y-m-d') >= '2020-11-30' ||
	PHP_VERSION_ID < 70400 && date('Y-m-d') >= '2021-12-06'
) {
	$server_warnings[] = '
		<span class="warning">' .
			KT_I18N::translate('
				Your web server is using PHP version %s, which is no longer receiving security updates.
				You should insist your web service provider upgrades to a later version as soon as possible.',
				PHP_VERSION
			) . '
			<a href="https://www.php.net/supported-versions.php" target="_blank" rel="noopener noreferrer"><i class="icon-php"></i></a>
		<span>';
	$PhpAlertClass = 'class="alert"';
} elseif (
    // active support
	PHP_VERSION_ID < 70400 ||
	PHP_VERSION_ID < 80000 && date('Y-m-d') >= '2021-11-28' ||
	PHP_VERSION_ID < 80100 && date('Y-m-d') >= '2022-11-26' ||
	PHP_VERSION_ID < 80200 && date('Y-m-d') >= '2023-12-26'
) {
	$server_warnings[] = '
		<span class="accepted">' .
			KT_I18N::translate('Your web server is using PHP version %s, which is no longer maintained.
			You should should ask your web service provider to upgrade to a later version.',
			PHP_VERSION
		) . '
		<a href="https://www.php.net/supported-versions.php" target="_blank" rel="noopener noreferrer"><i class="icon-php"></i></a>
		<span>';
	$PhpAlertClass = 'class="alert"';
}

// Total number of users
$total_users = count(get_all_users());

// Administrators
$administrators = KT_DB::prepare(
	"SELECT user_id, real_name FROM `##user` JOIN `##user_setting` USING (user_id) WHERE setting_name='canadmin' AND setting_value='1'"
)->fetchAll();

// Managers
$managers = KT_DB::prepare(
	"SELECT user_id, real_name FROM `##user` JOIN `##user_gedcom_setting` USING (user_id)" .
	" WHERE setting_name = 'canedit' AND setting_value='admin'" .
	" GROUP BY user_id, real_name" .
	" ORDER BY real_name"
)->fetchAll();

// Moderators
$moderators = KT_DB::prepare(
	"SELECT user_id, real_name FROM `##user` JOIN `##user_gedcom_setting` USING (user_id)" .
	" WHERE setting_name = 'canedit' AND setting_value='accept'" .
	" GROUP BY user_id, real_name" .
	" ORDER BY real_name"
)->fetchAll();

// Number of users who have not verified their email address
$unverified = KT_DB::prepare(
	"SELECT user_id, real_name FROM `##user` JOIN `##user_setting` USING (user_id)" .
	" WHERE setting_name = 'verified' AND setting_value = '0'" .
	" ORDER BY real_name"
)->fetchAll();

// Number of users whose accounts are not approved by an administrator
$unapproved = KT_DB::prepare(
	"SELECT user_id, real_name FROM `##user` JOIN `##user_setting` USING (user_id)" .
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
	"SELECT gedcom_id, COUNT(i_id) AS count FROM `##gedcom` LEFT JOIN `##individuals` ON gedcom_id = i_file GROUP BY gedcom_id"
)->fetchAssoc();
$families = KT_DB::prepare(
	"SELECT gedcom_id, COUNT(f_id) AS count FROM `##gedcom` LEFT JOIN `##families` ON gedcom_id = f_file GROUP BY gedcom_id"
)->fetchAssoc();
$sources = KT_DB::prepare(
	"SELECT gedcom_id, COUNT(s_id) AS count FROM `##gedcom` LEFT JOIN `##sources` ON gedcom_id = s_file GROUP BY gedcom_id"
)->fetchAssoc();
$media = KT_DB::prepare(
	"SELECT gedcom_id, COUNT(m_id) AS count FROM `##gedcom` LEFT JOIN `##media` ON gedcom_id = m_file GROUP BY gedcom_id"
)->fetchAssoc();
$repositories = KT_DB::prepare(
	"SELECT gedcom_id, COUNT(o_id) AS count FROM `##gedcom` LEFT JOIN `##other` ON gedcom_id = o_file AND o_type = 'REPO' GROUP BY gedcom_id"
)->fetchAssoc();
$notes = KT_DB::prepare(
	"SELECT gedcom_id, COUNT(o_id) AS count FROM `##gedcom` LEFT JOIN `##other` ON gedcom_id = o_file AND o_type = 'NOTE' GROUP BY gedcom_id"
)->fetchAssoc();
$changes = KT_DB::prepare(
	"SELECT g.gedcom_id, COUNT(change_id) AS count FROM `##gedcom` AS g LEFT JOIN `##change` AS c ON g.gedcom_id = c.gedcom_id AND status = 'pending' GROUP BY g.gedcom_id"
)->fetchAssoc();

echo pageStart('admin', KT_I18N::translate('Dashboard')); ?>

	<div class="cell callout info-help">
		<?php echo KT_I18N::translate('These pages provide access to all the configuration settings and management tools for this kiwitrees site.'); ?><br>
		<?php echo /* I18N: %s is a URL/link to the project website */ KT_I18N::translate('Support is available at %s.', ' <a class="current" href="' . KT_KIWITREES_URL . '/forums/">kiwitrees.net forums</a>'); ?>
	</div>
	<?php // Server warnings
	if ($server_warnings) { ?>
		<div class="cell callout warning">
			<?php foreach ($server_warnings as $server_warning): ?>
				<?php echo $server_warning; ?>
			<?php endforeach; ?>
		</div>
	<?php };

	// Accordion block for DELETE OLD FILES - only shown when old files are found
	$old_files_found = false;
	include_once './includes/housekeeping_data.php';

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
		<div class="cell callout warning">
			<h5><?php echo KT_I18N::translate('Old files found'); ?></h5>
			<p>
				<?php echo KT_I18N::translate('Files have been found from a previous version of kiwitrees. Old files can sometimes be a security risk. You should delete them.'); ?>
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
	<!-- End // Accordion block for DELETE OLD FILES -->


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
								<p <?php echo $PhpAlertClass; ?> >
									<?php echo KT_I18N::translate('PHP Version'); ?>:
									<span><?php echo phpversion(); ?></span>
								</p>
								<p <?php echo $SqlAlertClass; ?> >
									<?php echo KT_I18N::translate('SQL Version'); ?>:
									<span><?php echo $version; ?></span>
								</p>
								<p><?php echo KT_I18N::translate('Kiwitrees-nova'); ?>: <span><?php echo KT_VERSION; ?></span></p>
								<p><?php echo KT_I18N::translate('Latest update schema'); ?>: <span><?php echo (int) KT_Site::preference('KT_SCHEMA_VERSION'); ?></span></p>
							</div>
							<?php // Alerts
							if (KT_USER_IS_ADMIN) {
								// Kiwitrees version check
								if ($latest_version) {
									if (version_compare(KT_VERSION, $latest_version) < 0) { ?>
										<div class="cell callout alert">
											<?php echo /* I18N: %s is a URL/link to the project website */ KT_I18N::translate('Version %s of kiwitrees is now available at %s.', $latest_version, ' <a class="current" href="' . KT_KIWITREES_URL . '/services/downloads/">kiwitrees.net downloads</a>'); ?>
										</div>
									<?php } else { ?>
										<div class="cell callout success">
											<?php echo /* I18N: %s is a URL/link to the project website */ KT_I18N::translate('Your version of kiwitrees is the latest available.'); ?>
										</div>
									<?php }
								} else { ?>
									<div class="cell callout warning">
										<?php echo /* I18N: %s is a URL/link to the project website */ KT_I18N::translate('No kiwitrees upgrade information is available.'); ?>
									</div>
								<?php }
								// PHP version check
								if (version_compare(phpversion(), '8.2', '<')) {
									if (version_compare(phpversion(), '7.1', '<')) { ?>
										<div class="cell callout alert">
											<?php echo  KT_I18N::translate('Kiwitrees is no longer compatible with versions of PHP older than 7.1'); ?>
										</div>
									<?php } else { ?>
										<div class="cell callout success">
											<?php echo  KT_I18N::translate('Kiwitrees is compatible with this version of PHP.'); ?>
										</div>
									<?php }
								} else { ?>
									<div class="cell callout warning auto">
										<?php echo  KT_I18N::translate('Kiwitrees is not yet tested for compatibility with your version of PHP. It might work, but if you find any issues please report them on the <a class="current" href="%s" target="_blank">kiwitrees support forum</a>', KT_SUPPORT_URL); ?>
									</div>
								<?php }
							} ?>
						</div>
					</div>
				</div>
				<div class="accordion-item" data-accordion-item>
					<a href="#" class="accordion-title">
						<span><?php echo KT_I18N::translate('User statistics'); ?></span>
						<?php echo hintElement("span", "fa-layers fa-lg ", "", KT_I18N::translate('Total number of users'), "<i class=\"' . $iconStyle . ' fa-users\"></i><span class=\"fa-layers-counter\">$total_users</span>"); ?>
						<?php if ($incomplete) {
							echo hintElement("span", "warning fa-layers fa-lg ", "", KT_I18N::translate('Unverified or not approved users'), "<i class=\"' . $iconStyle . ' fa-user-secret\"></i><span class=\"fa-layers-counter\">$incomplete</span>");
						} ?>
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

<?php echo pageClose();

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
