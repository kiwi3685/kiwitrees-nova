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

/**
  *
  */
 class KT_Datatables_AdminMedia {
	 /**
	  *
	  * @return data array
	  * Converted to json on display page
	  */
	 public static function mediaList($files, $media_folder, $media_path, $subfolders, $search, $start, $length, $isort, $draw, $colsort, $sortdir) {

		switch ($files) {
			case 'local':
				// Filtered rows
				$SELECT1 = "
					SELECT
						SQL_CALC_FOUND_ROWS
						TRIM(LEADING ? FROM m_filename) AS media_path,
						'OBJE' AS type,
						m_titl,
						m_id AS xref,
						m_file AS ged_id,
						m_gedcom AS gedrec,
						m_filename
					FROM  `##media`
					JOIN  `##gedcom_setting` ON (m_file = gedcom_id AND setting_name = 'MEDIA_DIRECTORY')
					JOIN  `##gedcom` USING (gedcom_id) WHERE setting_value=?
					AND   m_filename LIKE CONCAT(?, '%')
					AND   (SUBSTRING_INDEX(m_filename, '/', -1) LIKE CONCAT('%', ?, '%')
					OR    m_titl LIKE CONCAT('%', ?, '%'))
					AND   m_filename NOT LIKE 'http://%'
					AND   m_filename NOT LIKE 'https://%'
				";
				$ARGS1 = array($media_path, $media_folder, $media_path, $search, $search);

				// Unfiltered rows
				$SELECT2 = "
					SELECT COUNT(*)
					FROM  `##media`
					JOIN  `##gedcom_setting` ON (m_file = gedcom_id AND setting_name = 'MEDIA_DIRECTORY')
					WHERE setting_value=?
					AND   m_filename LIKE CONCAT(?, '%')
					AND   m_filename NOT LIKE 'http://%'
					AND   m_filename NOT LIKE 'https://%'
				";
				$ARGS2 = array($media_folder, $media_path);

				if ($subfolders == 'exclude') {
					 $SELECT1 .= " AND m_filename NOT LIKE CONCAT(?, '%/%')";
					 $ARGS1[] = $media_path;
					 $SELECT2 .= " AND m_filename NOT LIKE CONCAT(?, '%/%')";
					 $ARGS2[] = $media_path;
				 }

				 if ($length > 0) {
					 $LIMIT = " LIMIT " . $start . ',' . $length;
				 } else {
					 $LIMIT = "";
				 }

				 if ($isort) {
					 $ORDER_BY = ' ORDER BY ';
					 for ($i = 0; $i < $isort; ++$i) {
						 // Datatables numbers columns 0, 1, 2, ...
						 // MySQL numbers columns 1, 2, 3, ...
						 switch ($sortdir[$i]) {
							 case 'asc':
								 $ORDER_BY .= (1 + $colsort[$i]) . ' ASC ';
								 break;
							 case 'desc':
								 $ORDER_BY .= (1 + $colsort[$i]) . ' DESC ';
								 break;
						 }
						 if ($i < $isort - 1) {
							 $ORDER_BY .= ',';
						 }
					 }
				} else {
					$ORDER_BY = '';
				}

				$rows = KT_DB::prepare($SELECT1 . $ORDER_BY . $LIMIT)->execute($ARGS1)->fetchAll(PDO::FETCH_ASSOC);

				// Total filtered/unfiltered rows
				$iTotalDisplayRecords = KT_DB::prepare("SELECT FOUND_ROWS()")->fetchColumn();
				$iTotalRecords        = KT_DB::prepare($SELECT2)->execute($ARGS2)->fetchColumn();

				$aaData = array();
				foreach ($rows as $row) {
					$media = KT_Media::getInstance($row);
					switch ($media->isPrimary()) {
						case 'Y':
							$highlight = KT_I18N::translate('Yes');
							break;
						case 'N':
							$highlight = KT_I18N::translate('No');
							break;
						default:
							$highlight = '';
							break;
					}
					$aaData[] = array(
						media_file_info($media_folder, $media_path, $row['media_path']),
						$media->displayImage(),
						media_object_info($media),
						$highlight,
						KT_Gedcom_Tag::getFileFormTypeValue($media->getMediaType()),
						'',
						$media_path . $row['media_path']

					);
				}
				break;

			 case 'external':
				 // Filtered rows
				 $SELECT1 = "
					 SELECT SQL_CALC_FOUND_ROWS m_filename AS media_path, 'OBJE' AS type, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec, m_titl, m_filename
					 FROM  `##media`
					 WHERE (m_filename LIKE 'http://%' OR m_filename LIKE 'https://%')
					 AND   (m_filename LIKE CONCAT('%', ?, '%') OR m_titl LIKE CONCAT('%', ?, '%'))
				 ";
				 $ARGS1 = array($search, $search);

				 // Unfiltered rows
				 $SELECT2 = "
					 SELECT COUNT(*)
					 FROM  `##media`
					 WHERE (m_filename LIKE 'http://%' OR m_filename LIKE 'https://%')
				 ";
				 $ARGS2 = array();

				 if ($iDisplayLength > 0) {
					 $LIMIT = " LIMIT " . $start . ',' . $length;
				 } else {
					 $LIMIT = "";
				 }

				if ($isort) {
					$ORDER_BY = ' ORDER BY ';
					for ($i = 0; $i < $isort; ++$i) {
						// Datatables numbers columns 0, 1, 2, ...
						// MySQL numbers columns 1, 2, 3, ...
						switch ($sortdir[$i]) {
							case 'asc':
								$ORDER_BY .= (1 + ($colsort[$i])) . ' ASC ';
								break;
							case 'desc':
								$ORDER_BY .= (1 + ($colsort[$i])) . ' DESC ';
								break;
						}
						if ($i < $isort - 1) {
							$ORDER_BY .= ',';
						}
					}
				} else {
					$ORDER_BY = '';
				}

				$rows = KT_DB::prepare($SELECT1 . $ORDER_BY . $LIMIT)->execute($ARGS1)->fetchAll(PDO::FETCH_ASSOC);

				// Total filtered/unfiltered rows
				$iTotalDisplayRecords = KT_DB::prepare("SELECT FOUND_ROWS()")->fetchColumn();
				$iTotalRecords        = KT_DB::prepare($SELECT2)->execute($ARGS2)->fetchColumn();

				 $aaData = [];
				 foreach ($rows as $row) {
					 $media = KT_Media::getInstance($row);
					 switch ($media->isPrimary()) {
					 case 'Y':
						 $highlight = KT_I18N::translate('Yes');
						 break;
					 case 'N':
						 $highlight = KT_I18N::translate('No');
						 break;
					 default:
						 $highlight = '';
						 break;
					 }
					 $aaData = [
						 KT_Gedcom_Tag::getLabelValue('URL', $row['m_filename']),
						 $media->displayImage(),
						 media_object_info($media),
						 media_object_edit($media),
						 $highlight,
						 KT_Gedcom_Tag::getFileFormTypeValue($media->getMediaType()),
					 ];
				 }
				 break;

			 case 'unused':
				// Which trees use this media folder?
				$media_trees = KT_DB::prepare("
					SELECT gedcom_name, gedcom_name
					FROM `##gedcom`
					JOIN `##gedcom_setting` USING (gedcom_id)
					WHERE setting_name='MEDIA_DIRECTORY' AND setting_value=? AND gedcom_id > 0
				"
				)->execute(array($media_folder))->fetchAssoc();

				$disk_files = all_disk_files ($media_folder, $media_path, $subfolders, $search);
				$db_files   = all_media_files($media_folder, $media_path, $subfolders, $search);

				// All unused files
				$unused_files  = array_diff($disk_files, $db_files);
				$iTotalRecords = count($unused_files);

				// Filter unused files
				if ($search) {
					$unused_files = array_filter($unused_files, function($x) use ($search) {return strpos($x, $search)!==false;});
				}
				$iTotalDisplayRecords = count($unused_files);

				// Sort files - only option is column 0
				sort($unused_files);
				if ($sortdir[0] == 'desc') {
					$unused_files = array_reverse($unused_files);
				}

				// Paginate unused files
				$unused_files = array_slice($unused_files, $start, $length);

				$aaData = array();
				foreach ($unused_files as $unused_file) {
					$full_path  = KT_DATA_DIR . $media_folder . $media_path . $unused_file;
					$thumb_path = KT_DATA_DIR . $media_folder . 'thumbs/' . $media_path . $unused_file;
					if (!file_exists($thumb_path)) {
						$thumb_path = $full_path;
					}

					$imgsize = getimagesize($thumb_path);
					if ($imgsize && $imgsize[0] && $imgsize[1]) {
						// We can’t create a URL (not in public_html) or use the media firewall (no such object)
						// so just the base64-encoded image inline.
						$img = '
						<img
							src="data:' . $imgsize['mime'] . ';base64,' . base64_encode(file_get_contents($thumb_path)) . '"
							class="thumbnail" ' . $imgsize[3] . '"
							style="max-width:100px;height:auto;
						">';
					} else {
						$img = '-';
					}

					// Is there a pending record for this file?
					$exists_pending = KT_DB::prepare("
						SELECT 1 FROM `##change`
						WHERE status='pending'
						 AND new_gedcom LIKE CONCAT('%\n1 FILE ', ?, '\n%')
					")->execute(array($unused_file))->fetchOne();

					// Form to create new media object in each tree
					$create_form = '';
					if (!$exists_pending) {
						foreach ($media_trees as $media_tree) {
							$create_form .= '
								<p>
									<a onclick="window.open(\'addmedia.php?action=showmediaform&amp;ged=' . rawurlencode((string) $media_tree) . '&amp;filename=' . rawurlencode((string) $unused_file) . '\'); return false;">' .
										KT_I18N::translate('Create') . '
									</a>
									— ' .
									KT_Filter::escapeHtml($media_tree) . '
								<p>
							';
						}
					}

					$conf        = KT_Filter::escapeJS(KT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($unused_file)));
					$delete_link = '
						<p>
							<a onclick="if (confirm(\'' . $conf . '\')) jQuery.post(\'admin_media.php\',{delete:\'' .addslashes($media_path . $unused_file) . '\',media_folder:\'' . addslashes($media_folder) . '\'},function(){location.reload();})" href="#">' .
								KT_I18N::Translate('Delete') . '
							</a>
						</p>
					';

					$aaData[] = [
						media_file_info($media_folder, $media_path, $unused_file),
						$img,
						$create_form,
						'',
						'',
						$delete_link,
						$media_path . $unused_file //for csv only
					];
				}
				 break;
		}

		// See http://www.datatables.net/usage/server-side
		$data = [
			'sEcho'                => $draw,
			'iTotalDisplayRecords' => $iTotalDisplayRecords,
			'iTotalRecords'        => $iTotalRecords,
			'aaData'               => $aaData
		];

		return $data;

	}
 }





