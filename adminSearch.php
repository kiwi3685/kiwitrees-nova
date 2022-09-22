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

define('KT_SCRIPT_NAME', 'adminSearch.php');
require './includes/session.php';
include KT_THEME_URL . 'templates/adminData.php';

global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Administration search results'))
	->pageHeader();

$searchTerm = KT_Filter::post('admin_query');
$result = adminSearch($searchTerm);

echo pageStart('admin_search', $controller->getPageTitle()); ?>
	<div class="cell">
		<div class="grid-x grid-margin-x grid-margin-y">
			<div class="cell">
				<h5><?php echo KT_I18N::translate('Searching for term: '); ?><span class="searchTerm"><?php echo $searchTerm; ?></span></h5>
			</div>
			<div class="cell medium-8 medium-offset-1">
		    	<?php if ($result) { ?>
					<table>
						<thead>
							<tr>
								<th><?php echo KT_I18N::translate('Page name'); ?></th>
								<th><?php echo KT_I18N::translate('Results'); ?></th>
							</tr>
						</thead>
						<tbody>
					        <?php foreach ($result as $page) {
								foreach ($page as $file => $count)  {
									if (array_key_exists($file, $indirectAccess)) {
										$modules = KT_Module::getActiveModules(KT_GED_ID, KT_PRIV_HIDE);
										foreach ($modules as $module) {
											if ( $module->getName() === $indirectAccess[$file]) {
												$link = '<a href="' . $module->getConfigLink(str_replace(".php", "", $file)) . '">' . $searchAdminFiles[$file] . '</a>';
											}
										}
									} else {
										$link = '<a href="' . $file . '">' . $searchAdminFiles[$file] . '</a>';
									} ?>
									<tr>
										<td><?php echo $link; ?></td>
										<td><span><?php echo KT_I18N::plural('%s result', '%s results', $count, $count); ?></span></td>
									</tr>
						        <?php }
							} ?>
						</tbody>
					</table>
			    <?php } else { ?>
			        <div class="cell callout warning"><?php echo KT_I18N::translate('Nothing found'); ?></div>
			    <?php } ?>
			</div>
		</div>
	</div>

<?php echo pageClose();


/**
 * Print links to related admin pages
 *
 * @param string $title name of page
 */
function adminSearch($searchfor) {

	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(KT_ROOT), RecursiveIteratorIterator::SELF_FIRST );

	foreach ($files as $file ) {
		$filename = basename($file);

		if (is_file($file) && strpos($filename, 'admin_') === 0) {
			$contents = file_get_contents($file);
			if (strpos($contents, $searchfor) !== false) {
				$count = 0;
				$fp = fopen($file, 'r');
				while (!feof($fp)) {
				    $line = fgets($fp);
					if (preg_match_all('/(KT_I18N::translate\(.*(' . $searchfor . ').*\))/i', trim($line), $match)) {
						$count ++;
					}
				}
				fclose($fp);
				if ($count > 0) {
					$result[] = array($filename => $count);
				}
			}
		}
	}

	return $result;
}


/**
* @param string $directory
*
* @return SplFileInfo[]
*/
function getAllFiles(): array {
//	$files	= scandir(KT_ROOT);
	 $result = [];

	 $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(KT_ROOT), RecursiveIteratorIterator::SELF_FIRST );

	 foreach ($files as $file ) {

		 $filename = basename($file);

		 if (is_file($file) && strpos($filename, 'admin_') === 0) {
			$result[] = $filename;
		 }
	 }

	 return $result;
}
