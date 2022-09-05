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
global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Administration search results'))
	->pageHeader();

$searchTerm = KT_Filter::post('admin_query');
$result = adminSearch($searchTerm);

echo pageStart('admin_search', $controller->getPageTitle()); ?>
	<h5><?php echo KT_I18N::translate('Searching for: %s', $searchTerm); ?></h5>

    <?php if ($result) {
        foreach ($result as $page) {
			foreach ($page as $file => $count)  { ?>
	            <div class="cell">
					<a href="<?php echo $file; ?>">
						<?php echo 'This is the page name'; ?>
					</a>
					<span><?php echo KT_I18N::translate('(%s results)', $count); ?></span>
				</div>
	        <?php }
		}
    } else { ?>
        <div class="cell"><?php echo KT_I18N::translate('Nothing found'); ?></div>
    <?php }

echo pageClose();

/**
 * Print links to related admin pages
 *
 * //@param string $title name of page
 */
function adminSearch($searchfor) {

	$files	= scandir(KT_ROOT);
	$result = array();

	foreach($files as $file) {
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
