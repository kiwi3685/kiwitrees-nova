<?php
/**
 * Print links to related admin pages
 *
 * @param string $title name of page
 */
function adminSearch($searchfor) {

	$files  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(KT_ROOT), RecursiveIteratorIterator::SELF_FIRST );
	$result = array();

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
