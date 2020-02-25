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

define('KT_SCRIPT_NAME', 'admin_trees_download.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_export.php';

global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_I18N::translate('Export a GEDCOM file'))
	->requireManagerLogin();

// Validate user parameters
$action           = safe_GET('action',           'download');
$convert          = safe_GET('convert',          'yes', 'no');
$zip              = safe_GET('zip',              'yes', 'no');
$conv_path        = safe_GET('conv_path',        KT_REGEX_NOSCRIPT);
$privatize_export = safe_GET('privatize_export', array('none', 'visitor', 'user', 'gedadmin'));

if ($action == 'download') {
	$exportOptions = array();
	$exportOptions['privatize'] = $privatize_export;
	$exportOptions['toANSI'] = $convert;
	$exportOptions['path'] = $conv_path;
}

$fileName = KT_GEDCOM;
if ($action == "download" && $zip == "yes") {
	require KT_ROOT.'library/pclzip.lib.php';

	$temppath	= KT_Site::preference('INDEX_DIRECTORY') . "tmp/";
	$zipname	= "dl" . date("YmdHis") . $fileName . ".zip";
	$zipfile	= KT_Site::preference('INDEX_DIRECTORY') . $zipname;
	$gedname	= $temppath . $fileName;

	$removeTempDir = false;
	if (!is_dir($temppath)) {
		$res = mkdir($temppath);
		if ($res !== true) {
			echo "Error : Could not create temporary path!";
			exit;
		}
		$removeTempDir = true;
	}
	$gedout = fopen($gedname, "w");
	export_gedcom($GEDCOM, $gedout, $exportOptions);
	fclose($gedout);
	$comment	= "Created by " . KT_KIWITREES . " " . KT_VERSION_TEXT . " on " . date("r") . ".";
	$archive	= new PclZip($zipfile);
	$v_list		= $archive->create($gedname, PCLZIP_OPT_COMMENT, $comment, PCLZIP_OPT_REMOVE_PATH, $temppath);
	if ($v_list == 0) echo "Error : " . $archive->errorInfo(true);
	else {
		unlink($gedname);
		if ($removeTempDir) rmdir($temppath);
		header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH."downloadbackup.php?fname=" . $zipname);
		exit;
	}
	exit;
}

if ($action == "download") {
	Zend_Session::writeClose();
	header('Content-Type: text/plain; charset=UTF-8');
	// We could open "php://compress.zlib" to create a .gz file or "php://compress.bzip2" to create a .bz2 file
	$gedout = fopen('php://output', 'w');
	if (strtolower(substr($fileName, -4, 4))!='.ged') {
		$fileName.='.ged';
	}
	header('Content-Disposition: attachment; filename="'.$fileName.'"');
	export_gedcom(KT_GEDCOM, $gedout, $exportOptions);
	fclose($gedout);
	exit;
}

$controller->pageHeader();

?>
<div id="tree-download" class="cell">
	<h4><?php echo $controller->getPageTitle(); ?> - <?php echo $tree->tree_title_html; ?></h4>
	<form id="tree-export" method="post" action="admin_trees_export.php">
		<?php echo KT_Filter::getCsrf(); ?>
		<input type="hidden" name="ged" value="<?php echo $tree->tree_name_url; ?>">
		<div class="grid-x grid-margin-y">
			<div class="cell medium-3 h5">
				<?php echo KT_I18N::translate('A file on the server'); ?>
			</div>
			<div class="cell medium-3 h5">
				<button id="submit-export" class="button" type="submit" onclick="return modalDialog('admin_trees_export.php?ged=<?php echo $tree->tree_name_url; ?>', '<?php echo KT_I18N::translate('Export'); ?>');">
					<i class="<?php echo $iconStyle; ?> fa-play"></i>
					<?php echo KT_I18N::translate('Continue'); ?>
				</button>
			</div>
		</div>
	</form>
	<hr class="cell">
	<form name="convertform" method="get">
		<input type="hidden" name="action" value="download">
		<input type="hidden" name="ged" value="<?php echo KT_GEDCOM; ?>">
		<div class="grid-x grid-margin-y">
			<div class="cell medium-3 h5">
				<?php echo KT_I18N::translate('A file on your computer'); ?>
			</div>
			<div class="cell medium-9">
				<div class="grid-x grid-margin-y">
					<div class="cell">
						<input type="checkbox" name="zip" value="yes">
						<label><?php echo KT_I18N::translate('Zip File(s)'); ?></label>
						<div class="help-text">
							<?php echo KT_I18N::translate('To reduce the size of the download, you can compress the data into a .ZIP file. You will need to uncompress the .ZIP file before you can use it.'); ?>
						</div>
					</div>
					<fieldset class="cell">
						<legend><?php echo KT_I18N::translate('Apply privacy settings?'); ?></legend>
						<input type="radio" name="privatize_export" value="none" id="none" checked="checked"><label for="none"><?php echo KT_I18N::translate('None'); ?></label>
						<input type="radio" name="privatize_export" value="gedadmin" id="gedadmin"><label for="gedadmin"><?php echo KT_I18N::translate('Manager'); ?></label>
						<input type="radio" name="privatize_export" value="user" id="user"><label for="user"><?php echo KT_I18N::translate('Member'); ?></label>
						<input type="radio" name="privatize_export" value="visitor" id="visitor"><label for="visitor"><?php echo KT_I18N::translate('Visitor'); ?></label>
						<div class="help-text">
							<?php echo KT_I18N::translate('This option will remove private data from the downloaded GEDCOM file.  The file will be filtered according to the privacy settings that apply to each access level.  Privacy settings are specified on the GEDCOM configuration page.'); ?>
						</div>
				    </fieldset>
					<div class="cell">
						<input type="checkbox" name="convert" value="yes">
						<label><?php echo KT_I18N::translate('Convert from UTF-8 to ANSI (ISO-8859-1)'); ?></label>
						<div class="help-text">
							<?php echo KT_I18N::translate('Kiwitrees uses UTF-8 encoding for accented letters, special characters and non-latin scripts. If you want to use this GEDCOM file with genealogy software that does not support UTF-8, then you can create it using ISO-8859-1 encoding.'); ?>
						</div>
					</div>
					<?php if (get_gedcom_setting(KT_GED_ID, 'GEDCOM_MEDIA_PATH')) { ?>
						<div class="cell">
							<input type="checkbox" name="conv_path" value="<?php echo KT_Filter::escapeHtml(get_gedcom_setting(KT_GED_ID, 'GEDCOM_MEDIA_PATH')); ?>">
							<label><?php echo KT_I18N::translate('Add the GEDCOM media path to filenames'); ?></label>
							<div>
								<?php echo KT_I18N::translate('Media filenames will be prefixed by %s.', '<code class="alert">' . KT_Filter::escapeHtml(get_gedcom_setting(KT_GED_ID, 'GEDCOM_MEDIA_PATH')) . '</code>'); ?>
							</div>
						</div>
					<?php } ?>
					<button class="button" type="submit">
						<i class="<?php echo $iconStyle; ?> fa-play"></i>
						<?php echo KT_I18N::translate('Continue'); ?>
					</button>
				</div>
			</div>
		</div>
	</form>
</div>
