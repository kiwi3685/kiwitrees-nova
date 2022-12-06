<?php
/**
 * Print links to related admin pages.
 *
 * @param string $title     name of page
 * @param mixed  $searchfor
 */
function adminSearch($searchfor)
{
	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(KT_ROOT), RecursiveIteratorIterator::SELF_FIRST);
	$result = [];

	foreach ($files as $file) {
		$filename = basename($file);

		if (is_file($file) && 0 === strpos($filename, 'admin_')) {
			$contents = file_get_contents($file);
			if (false !== strpos($contents, $searchfor)) {
				$count = 0;
				$fp = fopen($file, 'r');
				while (!feof($fp)) {
					$line = fgets($fp);
					if (preg_match_all('/(KT_I18N::translate\(.*('.$searchfor.').*\))/i', trim($line), $match)) {
						++$count;
					}
				}
				fclose($fp);
				if ($count > 0) {
					$result[] = [$filename => $count];
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
function getAllFiles(): array
{
	$result = [];

	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(KT_ROOT), RecursiveIteratorIterator::SELF_FIRST);

	foreach ($files as $file) {
		$filename = basename($file);

		if (is_file($file) && 0 === strpos($filename, 'admin_')) {
			$result[] = $filename;
		}
	}

	return $result;
}
