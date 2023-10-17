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

define('KT_SCRIPT_NAME', 'admin_media.php');
require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';
require KT_ROOT . 'includes/functions/functions_media.php';
include KT_THEME_URL . 'templates/adminData.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Manage media'));

// type of file/object to include
$files = KT_Filter::get('files', 'local|external|unused', 'local');

// family tree setting MEDIA_DIRECTORY
$media_folders = all_media_folders();
$media_folder  = KT_Filter::get('media_folder', KT_REGEX_UNSAFE);
// User folders may contain special characters.  Restrict to actual folders.
if (!array_key_exists($media_folder, $media_folders)) {
	$media_folder = reset($media_folders);
}

// prefix to filename
$media_paths = media_paths($media_folder);
$media_path  = KT_Filter::get('media_path', KT_REGEX_UNSAFE);
// User paths may contain special characters.  Restrict to actual paths.
if (!array_key_exists($media_path, $media_paths)) {
	$media_path = reset($media_paths);
}

// subfolders within $media_path
$subfolders = KT_Filter::get('subfolders', 'include|exclude', 'exclude');
$action     = KT_Filter::get('action');

////////////////////////////////////////////////////////////////////////////////
// POST callback for file deletion
////////////////////////////////////////////////////////////////////////////////
$delete_file = KT_Filter::post('delete', KT_REGEX_UNSAFE);
if ($delete_file) {
	$controller = new KT_Controller_Ajax;
	// Only delete valid (i.e. unused) media files
	$media_folder = KT_Filter::post('media_folder', KT_REGEX_UNSAFE);
	$disk_files = all_disk_files ($media_folder, '', 'include', '');
	if (in_array($delete_file, $disk_files)) {
		$tmp = KT_DATA_DIR . $media_folder . $delete_file;
		if (@unlink($tmp)) {
			KT_FlashMessages::addMessage(KT_I18N::translate('The file %s was deleted.', $tmp));
		} else {
			KT_FlashMessages::addMessage(KT_I18N::translate('The file %s could not be deleted.', $tmp));
		}
		$tmp = KT_DATA_DIR . $media_folder . 'thumbs/' . $delete_file;
		if (file_exists($tmp)) {
			if (@unlink($tmp)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('The file %s was deleted.', $tmp));
			} else {
				KT_FlashMessages::addMessage(KT_I18N::translate('The file %s could not be deleted.', $tmp));
			}
		}
	} else {
		// File no longer exists?  Maybe it was already deleted or renamed.
	}
	$controller->pageHeader();
	exit;
}

// A unique list of media folders, from all trees.
function all_media_folders() {
	return KT_DB::prepare(
		"SELECT setting_value, setting_value" .
		" FROM `##gedcom_setting`" .
		" WHERE setting_name='MEDIA_DIRECTORY'" .
		" GROUP BY 1" .
		" ORDER BY 1"
	)->fetchAssoc();
}

function media_paths($media_folder) {
	$media_paths = KT_DB::prepare(
		"SELECT LEFT(m_filename, CHAR_LENGTH(m_filename) - CHAR_LENGTH(SUBSTRING_INDEX(m_filename, '/', -1))) AS media_path" .
		" FROM  `##media`" .
		" JOIN  `##gedcom_setting` ON (m_file = gedcom_id AND setting_name = 'MEDIA_DIRECTORY')" .
		" WHERE setting_value=?" .
		"	AND   m_filename NOT LIKE 'http://%'" .
		" AND   m_filename NOT LIKE 'https://%'" .
		" GROUP BY 1" .
		" ORDER BY 1"
	)->execute(array($media_folder))->fetchOneColumn();

	if (!$media_paths || reset($media_paths)!='') {
		// Always include a (possibly empty) top-level folder
		array_unshift($media_paths, '');
	}

	return array_combine($media_paths, $media_paths);
}

function scan_dirs($dir, $recursive, $filter) {
	$files = array();

	// $dir comes from the database.  The actual folder may not exist.
	if (is_dir($dir)) {
		foreach (scandir($dir) as $path) {
			if (is_dir($dir . $path)) {
				// TODO - but what if there are user-defined subfolders “thumbs” or “watermarks”…
				if ($path!='.' && $path!='..' && $path!='thumbs' && $path!='watermarks' && $recursive) {
					foreach (scan_dirs($dir . $path . '/', $recursive, $filter) as $subpath) {
						$files[] = $path . '/' . $subpath;
					}
				}
			} elseif (!$filter || stripos($path, $filter)!==false) {
				$files[] = $path;
			}
		}
	}
	return $files;
}

// Fetch a list of all files on disk
function all_disk_files($media_folder, $media_path, $subfolders, $filter) {
	return scan_dirs(KT_DATA_DIR . $media_folder . $media_path, $subfolders=='include', $filter);
}

function externalMedia() {
	$count = KT_DB::prepare("SELECT COUNT(*) FROM `##media` WHERE (m_filename LIKE 'http://%' OR m_filename LIKE 'https://%')")
		->execute()
		->fetchOne();
	return	$count;
}

// Fetch a list of all files on in the database
function all_media_files($media_folder, $media_path, $subfolders, $filter) {
	return KT_DB::prepare(
		"SELECT SQL_CALC_FOUND_ROWS TRIM(LEADING ? FROM m_filename) AS media_path, 'OBJE' AS type, m_titl, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec, m_filename" .
		" FROM  `##media`" .
		" JOIN  `##gedcom_setting` ON (m_file = gedcom_id AND setting_name = 'MEDIA_DIRECTORY')" .
		" JOIN  `##gedcom`         USING (gedcom_id)" .
		" WHERE setting_value=?" .
		" AND   m_filename LIKE CONCAT(?, '%')" .
		" AND   (SUBSTRING_INDEX(m_filename, '/', -1) LIKE CONCAT('%', ?, '%')" .
		"  OR   m_titl LIKE CONCAT('%', ?, '%'))" .
		"	AND   m_filename NOT LIKE 'http://%'" .
		" AND   m_filename NOT LIKE 'https://%'"
	)->execute(array($media_path, $media_folder, $media_path, $filter, $filter))->fetchOneColumn();
}

function media_file_info($media_folder, $media_path, $file) {
	$html = '<b>' . htmlspecialchars((string) $file). '</b>';

	$full_path = KT_DATA_DIR . $media_folder . $media_path . $file;
	if ($file && file_exists($full_path)) {
		$size = @filesize($full_path);
		if ($size!==false) {
			$size = (int)(($size+1023)/1024); // Round up to next KB
			$size = /* I18N: size of file in KB */ KT_I18N::translate('%s KB', KT_I18N::number($size));
			$html .= KT_Gedcom_Tag::getLabelValue('__FILE_SIZE__', $size);
			$imgsize = @getimagesize($full_path);
			if (is_array($imgsize)) {
				$imgsize = /* I18N: image dimensions, width × height */ KT_I18N::translate('%1$s × %2$s pixels', KT_I18N::number($imgsize['0']), KT_I18N::number($imgsize['1']));
				$html .= KT_Gedcom_Tag::getLabelValue('__IMAGE_SIZE__', $imgsize);
			}
		} else {
			$html .= '<div class="error">' . KT_I18N::translate('This media file exists, but cannot be accessed.') . '</div>' ;
		}
	} else {
		$html .= '<div class="error">' . KT_I18N::translate('This media file does not exist.') . '</div>' ;
	}
	return $html;
}

function media_object_edit(KT_Media $media) {
	$xref   = $media->getXref();
	$gedcom = KT_Tree::getNameFromId($media->getGedId());
	$name   = $media->getFullName();
	$conf   = KT_Filter::escapeJS(KT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($name)));

	return '
		<a href="' . $media->getHtmlUrl() . '">' . KT_I18N::translate('View') . '</a>
		<a href="addmedia.php?action=editmedia&amp;pid=' . $xref . '&ged=' . $gedcom . '" target="_blank" >' . KT_I18N::Translate('Edit') . '</a>
		<a onclick="if (confirm(\'' . $conf . '\')) jQuery.post(\'action.php\',{action:\'delete-media\',xref:\'' . $xref . '\',ged:\'' . $gedcom . '\'},function(){location.reload();})" href="#">' . KT_I18N::Translate('Delete') . '</a>
		<a href="inverselink.php?mediaid=' . $xref . '&amp;linkto=manage" target="_blank">' . KT_I18N::Translate('Manage links') . '</a>
	';
}

switch (KT_Filter::get('action')) {
	case 'loadrows':

		$search    = KT_Filter::post('sSearch', '');
		$start     = KT_Filter::postInteger('iDisplayStart');
		$length    = KT_Filter::postInteger('iDisplayLength');
		$isort     = KT_Filter::postInteger('iSortingCols');
		$draw      = KT_Filter::postInteger('sEcho');
		$colsort   = [];
		$sortdir   = [];
		for ($i = 0; $i < $isort; ++$i) {
			$colsort[$i] = KT_Filter::postInteger('iSortCol_' . $i);
			$sortdir[$i] = KT_Filter::post('sSortDir_' . $i);
		}

		Zend_Session::writeClose();
		header('Content-type: application/json');
		echo json_encode(KT_DataTables_AdminMedia::mediaList($files, $media_folder, $media_path, $subfolders, $search, $start, $length, $isort, $draw, $colsort, $sortdir));
		exit;
}

// Start display code
// =======================

// Array for switch group
if (externalMedia() > 0){
	$filesArray = array(
		'local'		=> KT_I18N::translate('Local'),
		'external'	=> KT_I18N::translate('External'),
		'unused'	=> KT_I18N::translate('Unused')
	);
} else {
	$filesArray = array(
		'local'		=> KT_I18N::translate('Local'),
		'unused'	=> KT_I18N::translate('Unused')
	);
}

// Preserve the pagination/filtering/sorting between requests, so that the
// browser’s back button works.  Pagination is dependent on the currently
// selected folder.
$table_id  = md5($files . $media_folder . $media_path . $subfolders);

// Access default datatables settings
include_once KT_ROOT . 'library/KT/DataTables/KTdatatables.js.php';

$controller
	->pageHeader()
	->addExternalJavascript(KT_DATATABLES_KT_JS)
	->addInlineJavascript('
		datatable_defaults("' . KT_SCRIPT_NAME . '?action=loadrows&files=' . $files . '&media_folder=' . $media_folder . '&media_path=' . $media_path . '&subfolders=' . $subfolders . '");

		jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
		jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};

		jQuery("#media-table-' . $table_id . '").dataTable({
			buttons: [{extend: "csvHtml5", exportOptions: {columns: [0,2,3,4,6]}}],
			columns: [
				/*0 - media file */		{},
				/*1 - media object */	{sortable: false, class: "center"},
				/*2 - media name */		{sortable: ' . ($files === 'unused' ? 'false' : 'true') . '},
				/*3 - highlighted? */	{type: "text"},
				/*4 - media type */		{},
				/*5 - DELETE    */      { visible: ' . (KT_USER_GEDCOM_ADMIN && $files === 'unused' ? 'true' : 'false') . ', sortable: false, class: "center" },
				/*6 - path for CSV only */ { visible: false}
			]
		});

	');

// Start page display
echo relatedPages($media, KT_SCRIPT_NAME);

echo pageStart('admin_media', $controller->getPageTitle()); ?>

	<form class="cell" method="get" action="<?php echo KT_SCRIPT_NAME; ?>">
		<div class="grid-x">
			<div class="cell medium-2">
				<label class="h5"><?php echo KT_I18N::translate('Media folders'); ?></label>
			</div>
			<div class="cell medium-10">
				<div class="grid-x">
					<?php switch ($files) {
						case 'local':
						case 'unused': ?>
							<div class="cell shrink media-folders">
								<label class="middle"><?php echo KT_DATA_DIR; ?></label>
							</div>
							<div class="cell shrink media-folders">
								<?php // Don’t show a list of media folders if it just contains one folder
								$extra = 'onchange="this.form.submit();"';
								if (count($media_folders) > 1) { ?>
									<?php echo select_edit_control('media_folder', $media_folders, null, $media_folder, $extra); ?>
								<?php } else { ?>
									<label class="middle">
										<?php echo $media_folder; ?>
									</label>
									<input type="hidden" name="media_folder" value="<?php echo htmlspecialchars((string) $media_folder); ?>" >
								<?php } ?>
							</div>
							<?php
						break;
						case 'external': ?>
							<?php echo KT_I18N::translate('External media files have a URL instead of a filename.'); ?>
							<input type="hidden" name="media_folder" value="'<?php echo htmlspecialchars((string) $media_folder); ?>">
							<input type="hidden" name="media_path" value="'<?php echo htmlspecialchars((string) $media_path); ?>">
						<?php break;
					} ?>
					<div class="cell auto media-folders">
						<?php // Don’t show a list of subfolders if it just contains one subfolder
						if (count($media_paths) > 1) { ?>
							<?php echo select_edit_control('media_path', $media_paths, null, $media_path, $extra); ?>
						<?php } else { ?>
							<?php echo $media_path . '<input type="hidden" name="media_path" value="', htmlspecialchars((string) $media_path) . '">'; ?>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="cell medium-2">
				<label class="h6"><?php echo KT_I18N::translate('Subfolders'); ?></label>
			</div>
			<div class="cell medium-10">
				<?php echo radio_switch_group (
					'subfolders',
					array(
						'exclude' => KT_I18N::translate('Exclude subfolders'),
						'include' => KT_I18N::translate('Include subfolders')
					),
					$subfolders,
					'onchange="this.form.submit();"',
				); ?>
			</div>
			<hr class="cell">
			<div class="cell medium-2">
				<label class="h5"><?php echo KT_I18N::translate('Media files'); ?></label>
			</div>
			<div class="cell medium-10">
				<?php echo radio_switch_group (
					'files',
					$filesArray,
					$files,
					'onchange="this.form.submit();"',
				); ?>
			</div>
		</div>
	</form>
	<hr class="cell">
	<div class="cell">
		<table class="media_table stack" id="media-table-<?php echo $table_id ?>">
			<thead>
				<tr>
					<th><?php echo KT_I18N::translate('Media file'); ?></th>
					<th><?php echo KT_I18N::translate('Media'); ?></th>
					<th><?php echo KT_I18N::translate('Media object'); ?></th>
					<th><?php echo KT_I18N::translate('Highlight'); ?></th>
					<th><?php echo KT_I18N::translate('Media type'); ?></th>
					<?php if (KT_USER_GEDCOM_ADMIN && $files === 'unused') { ?>
						<th>
							<div class="delete_src">
								<input type="button" value="<?php echo KT_I18N::translate('Delete'); ?>" onclick="if (confirm('<?php echo htmlspecialchars(KT_I18N::translate('Permanently delete these records?')); ?>')) {return checkbox_delete('unusedmedia');} else {return false;}">
								<input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
							</div>
						</th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>

<?php echo pageClose();
