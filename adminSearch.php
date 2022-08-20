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

echo pageStart('admin_search', $controller->getPageTitle());

	$result = array_unique(adminSearch('sanity'));
	if ($result) {
		foreach ($result as $output) {
			echo '<div class="cell">' . $output . '</div>';
		}
	} else {
		echo '<div class="cell">' . KT_I18N::translate('Nothing found') . '</div>';
	}

echo pageClose();



/**
 * Print links to related admin pages
 *
 * //@param string $title name of page
 */
function adminSearch() {

	$files	= scandir(KT_ROOT);
	$result	= array();

	$searchfor = 'sanity';

	foreach($files as $file) {
		$filename = basename($file);
		if(is_file($file) && strpos($filename, 'admin_') === 0) {

			$contents = file_get_contents($file);
			if (strpos($contents, $searchfor) !== false) {
				$lines=array();
				$fp=fopen($file, 'r');
				while (!feof($fp)) {
				    $line=fgets($fp);
					if (preg_match('/(KT_I18N::translate\(.*(' . $searchfor . ').*\))/i', trim($line), $match)) {
						$result[] = $filename;
				    }
				}
				fclose($fp);

			}

		}
	}

	return $result;
}
