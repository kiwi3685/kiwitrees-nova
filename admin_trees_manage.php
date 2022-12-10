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

define('KT_SCRIPT_NAME', 'admin_trees_manage.php');
require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';

global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Manage family trees'));

$default_tree_title  = /* I18N: Default name for a new tree */ KT_I18N::translate('My family tree');
$default_tree_name   = 'tree';
$default_tree_number = 1;
$existing_trees      = KT_Tree::getNameList();
while (array_key_exists($default_tree_name . $default_tree_number, $existing_trees)) {
	$default_tree_number ++;
}
$default_tree_name .= $default_tree_number;

// Process POST actions
switch (KT_Filter::post('action')) {
	case 'delete':
		$gedcom_id		= KT_Filter::postInteger('gedcom_id');
		if (KT_Filter::checkCsrf() && $gedcom_id) {
			KT_FlashMessages::addMessage(/* I18N: %s is the name of a family tree */ KT_I18N::translate('The family tree “%s” has been deleted.', KT_Filter::post('gedcom_title')), 'success');
			KT_Tree::delete($gedcom_id);
		}
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME);
		break;

	case 'setdefault':
		if (KT_Filter::checkCsrf()) {
			KT_Site::preference('DEFAULT_GEDCOM', KT_Filter::post('default_ged'));
			KT_FlashMessages::addMessage(/* I18N: %s is the name of a family tree */ KT_I18N::translate('The family tree "%s" will be shown to visitors when they first arrive at this website.', KT_Filter::post('default_title')), 'success');
		}
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME);
		return;

	case 'new_tree':
		$basename		= basename(KT_Filter::post('ged_name'));
		$gedcom_title	= KT_Filter::post('gedcom_title');
		if (KT_Filter::checkCsrf() && $basename && $gedcom_title) {
			if (KT_Tree::findByName($basename)) {
				KT_FlashMessages::addMessage(/* I18N: %s is the name of a family tree */ KT_I18N::translate('The family tree "%s" already exists.', KT_Filter::escapeHtml($basename)), 'alert');
			} else {
				KT_Tree::create($basename, $gedcom_title);
				KT_FlashMessages::addMessage(/* I18N: %s is the name of a family tree */ KT_I18N::translate('The family tree "%s" has been created.', KT_Filter::escapeHtml($basename)), 'success');
			}
		}
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#' . basename($basename, ".ged"));
		return;

	case 'replace_upload':
		$gedcom_id			= KT_Filter::postInteger('gedcom_id');
		$keep_media         = KT_Filter::post('keep_media', '1', '0');
		$GEDCOM_MEDIA_PATH  = KT_Filter::post('GEDCOM_MEDIA_PATH');
		$WORD_WRAPPED_NOTES = KT_Filter::post('WORD_WRAPPED_NOTES', '1', '0');
		$tree               = get_gedcom_from_id($gedcom_id);

		// Make sure the gedcom still exists
		if (KT_Filter::checkCsrf() && $tree) {
			set_gedcom_setting($gedcom_id, 'keep_media', $keep_media);
			set_gedcom_setting($gedcom_id, 'GEDCOM_MEDIA_PATH', $GEDCOM_MEDIA_PATH);
			set_gedcom_setting($gedcom_id, 'WORD_WRAPPED_NOTES', $WORD_WRAPPED_NOTES);
			if (isset($_FILES['tree_name'])) {
				if ($_FILES['tree_name']['error'] == 0 && is_readable($_FILES['tree_name']['tmp_name'])) {
					KT_Tree::import_gedcom_file($gedcom_id, $_FILES['tree_name']['tmp_name'], $_FILES['tree_name']['name']);
					KT_FlashMessages::addMessage(/* I18N: %s is the name of a family tree */ KT_I18N::translate('The family tree "%s" has been updated.', KT_Filter::escapeHtml($basename)), 'success');
				} else {
					KT_FlashMessages::addMessage(fileUploadErrorText($_FILES['tree_name']['error']), 'warning');
				}
			} else {
				KT_FlashMessages::addMessage(KT_I18N::translate('No GEDCOM file was received.'), 'warning');
			}
		}
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#' . basename($basename, ".ged"));
		return;

	case 'replace_import':
		$basename           = basename(KT_Filter::post('tree_name'));
		$gedcom_id			= KT_Filter::postInteger('gedcom_id');
		$keep_media         = KT_Filter::post('keep_media', '1', '0');
		$GEDCOM_MEDIA_PATH  = KT_Filter::post('GEDCOM_MEDIA_PATH');
		$WORD_WRAPPED_NOTES = KT_Filter::post('NEW_WORD_WRAPPED_NOTES', '1', '0');
		$tree               = get_gedcom_from_id($gedcom_id);

		// Make sure the gedcom still exists
		if (KT_Filter::checkCsrf() && $tree) {
			set_gedcom_setting(KT_GED_ID, 'keep_media', $keep_media);
			set_gedcom_setting(KT_GED_ID, 'GEDCOM_MEDIA_PATH', $GEDCOM_MEDIA_PATH);
			set_gedcom_setting(KT_GED_ID, 'WORD_WRAPPED_NOTES', $WORD_WRAPPED_NOTES);
			if ($basename) {
				KT_Tree::import_gedcom_file($gedcom_id, KT_DATA_DIR . $basename, $basename);
			} else {
				KT_FlashMessages::addMessage(KT_I18N::translate('No GEDCOM file was received.'), 'warning');
			}
		}
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#' . basename($basename, ".ged"));
		return;
}

$controller->pageHeader();

// Process GET actions
switch (KT_Filter::get('action')) {
	case 'importform':
		$gedcom_id	 = KT_Filter::get('gedcom_id');
		$gedcom_name = get_gedcom_from_id($gedcom_id);
		// Check it exists
		if (!$gedcom_name) {
			break;
		}
		$gedcom_filename = get_gedcom_setting($gedcom_id, 'gedcom_filename');

		echo pageStart('importTrees', KT_I18N::translate('Import a GEDCOM file')); ?>
			<div class="callout alert small">
				<?php echo /* I18N: %s is the name of a family tree */ KT_I18N::translate('This will delete all the genealogy data from "%s" and replace it with data from a GEDCOM file.', KT_TREE_TITLE); ?>
			</div>
			<form class="cell" name="gedcomimportform" method="post" enctype="multipart/form-data" onsubmit="return checkGedcomImportForm('<?php echo KT_Filter::escapeHtml(KT_I18N::translate('You have selected a GEDCOM file with a different name. Is this correct?')) ?>');">
				<input type="hidden" name="gedcom_id" value="<?php echo $gedcom_id; ?>">
				<input type="hidden" id="gedcom_filename" value="<?php echo KT_Filter::escapeHtml($gedcom_filename) ?>">
				<?php echo KT_Filter::getCsrf(); ?>
				<div class="grid-x grid-margin-x grid-margin-y">
					<div class="cell h5"><?php echo KT_I18N::translate('Select a GEDCOM file to import'); ?></div>
					<div class="cell medium-1">
						<div class="grid-x grid-margin-y">
							<div class="switch tiny cell small-8 medium-4 large-2">
								<input class="switch-input" id="replace_upload" type="radio" name="action" value="replace_upload">
								<label class="switch-paddle" for="replace_upload">
									<span class="show-for-sr">'replace_upload'</span>
									<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
									<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('No'); ?></span>
								</label>
							</div>
						</div>
					</div>
					<label class="cell medium-2">
						<?php echo KT_I18N::translate('A file on your computer'); ?>
					</label>
					<div class="cell medium-9 success">
						<input type="file" name="tree_name" id="import-computer-file">
						<div class="callout info-help">
							<?php echo KT_I18N::translate('The maximum file size your server can upload is %s', format_size(detectMaxUploadFileSize())); ?>
						</div>
					</div>
					<div class="cell medium-1">
						<div class="grid-x grid-margin-y">
							<div class="switch tiny cell small-8 medium-4 large-2">
								<input class="switch-input" id="replace_import" type="radio" name="action" value="replace_import" checked>
								<label class="switch-paddle" for="replace_import">
									<span class="show-for-sr">'replace_import'</span>
									<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
									<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('No'); ?></span>
								</label>
							</div>
						</div>
					</div>
					<label class="cell medium-2">
						<?php echo KT_I18N::translate('A file on the server'); ?>
					</label>
					<div class="cell medium-9">
						<div class="input-group">
							<span class="input-group-label">
								<?php echo KT_DATA_DIR; ?>
							</span>
							<?php
							$d		= opendir(KT_DATA_DIR);
							$files	= array();
							while (($f = readdir($d)) !== false) {
								if (!is_dir(KT_DATA_DIR . $f) && is_readable(KT_DATA_DIR . $f)) {
									$fp		= fopen(KT_DATA_DIR . $f, 'rb');
									$header	= fread($fp, 64);
									fclose($fp);
									if (preg_match('/^(' . KT_UTF8_BOM . ')?0 *HEAD/', $header)) {
										$files[] = $f;
									}
								}
							}
							sort($files); ?>
							<select name="tree_name" id="import-server-file">
								<?php foreach ($files as $file) { ?>
									<option value="<?php echo htmlspecialchars($file); ?>"
										<?php if ($file == $gedcom_filename) { ?>
											selected="selected"
										<?php } ?>
									><?php echo htmlspecialchars($file); ?></option>
								<?php }
								if (!$files) { ?>
									<option disabled selected><?php echo KT_I18N::translate('No GEDCOM files found.'); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</div>
				<hr class="cell">
				<div class="grid-x grid-margin-x grid-margin-y">
					<div class="cell h4"><?php echo KT_I18N::translate('Import options'); ?></div>
					<label class="cell medium-3"><?php echo /* I18N: A media path (e.g. c:\aaa\bbb\ccc\ddd.jpeg) in a GEDCOM file */ KT_I18N::translate('Remove the GEDCOM media path from filenames'); ?></label>
					<div class="cell medium-9">
						<input type="text" name="NEW_GEDCOM_MEDIA_PATH" value="<?php echo $GEDCOM_MEDIA_PATH; ?>" maxlength="255">
						<div class="callout info-help">
							<?php echo
							// I18N: A "path" is something like "C:\Documents\My_User\Genealogy\Photos\Gravestones\John_Smith.jpeg"
							KT_I18N::translate('Some genealogy applications create GEDCOM files that contain media filenames with full paths.  These paths will not exist on the web-server.  To allow kiwitrees to find the file, the first part of the path must be removed.').
							// I18N: %s are all folder names; "GEDCOM media path" is a configuration setting
							KT_I18N::translate('For example, if the GEDCOM file contains %1$s and kiwitrees expects to find %2$s in the media folder, then the GEDCOM media path would be %3$s.', '<code class="alert">/home/familytree/documents/family/photo.jpeg</code>', '<code class="alert">family/photo.jpeg</code>', '<code class="alert">/home/familytree/documents/</code>').
							KT_I18N::translate('This setting is only used when you read or write GEDCOM files.'); ?>
						</div>
					</div>							
					<label class="cell medium-3"><?php echo KT_I18N::translate('Add spaces where notes were wrapped'); ?></label>
					<div class="cell medium-9">
						<?php echo simple_switch('NEW_WORD_WRAPPED_NOTES', 1, get_gedcom_setting(KT_GED_ID, 'WORD_WRAPPED_NOTES')); ?>
						<div class="callout info-help">
							<?php echo KT_I18N::translate('Some genealogy programs wrap notes at word boundaries while others wrap notes anywhere.  This can cause kiwitrees to run words together.  Setting this to <b>Yes</b> will add a space between words where they are wrapped in the original GEDCOM during the import process. If you have already imported the file you will need to re-import it.'); ?>
						</div>
					</div>							
					<label class="cell medium-3">
						<?php echo KT_I18N::translate('Keep media objects'); ?>
					</label>
					<div class="cell medium-9">
						<?php echo simple_switch('keep_media' . $gedcom_id, 1, KT_Filter::post('keep_media' . $tree->tree_id)); ?>
						<div class="callout info-help">
							<?php echo KT_I18N::translate('If you have created media objects in kiwitrees, <span class="alert strong">and edited your gedcom off-line using a program that deletes media objects</span>, then check this box to merge the current media objects with the new GEDCOM.  <a class="strong" href="https://www.kiwitrees.net/kb/faq/keep-media-object/" target="_blank" rel="noopener noreferrer">See this FAQ for more information.</a>'); ?>
						</div>
					</div>
				</div>
				<hr class="cell">

				<?php echo singleButton('Back'); ?>
				<?php echo singleButton('Import'); ?>

			</form>
		
		<?php echo pageClose();

	return;

}

echo relatedPages($trees, KT_SCRIPT_NAME);?>

<div id="trees_manage-page" class="cell">
	<div class="grid-x grid-margin-x grid-margin-y">
		<div class="cell">
			<h4 class="inline"><?php echo $controller->getPageTitle(); ?></h4>
			<?php //echo faqLink('administration/manage_trees/'); ?>
		</div>
		<ul class="cell accordion" data-accordion data-deep-link="true" data-update-history="true" data-allow-all-closed="true">
			<?php foreach (KT_Tree::GetAll() as $tree) { // List the gedcoms available to this user
				if (userGedcomAdmin(KT_USER_ID, $tree->tree_id)) { ?>
					<li class="accordion-item" data-accordion-item>
						<a class="accordion-title" href="#<?php echo $tree->tree_name; ?>">
                            <?php if ($tree->tree_name_url === KT_Site::preference('DEFAULT_GEDCOM')) { ?>
                                <i class="default <?php echo $iconStyle; ?> fa-star" title="<?php echo KT_I18N::translate('Default family tree'); ?>"></i>
                            <?php } ?>
							<?php echo $tree->tree_title_html; ?>
                            &nbsp;
                            <span class="subheader">(<?php echo $tree->tree_name_html; ?>)</span>
						</a>
						<div class="accordion-content" id="<?php echo $tree->tree_name; ?>" data-tab-content>
							<?php // An optional progress bar and a list of maintenance options
							$importing = KT_DB::prepare(
								"SELECT 1 FROM `##gedcom_chunk` WHERE gedcom_id=? AND imported= '0' LIMIT 1"
							)->execute(array($tree->tree_id))->fetchOne();
							if ($importing) { ?>
								<div id="import<?php echo $tree->tree_id; ?>" class="cell">
									<div class="progress" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuetext="<?php echo KT_I18N::translate('Deleting old genealogy data…'); ?>" aria-valuemax="100">
										<span class="progress-meter" style="width: 25%">
											<p class="progress-meter-text"><?php echo KT_I18N::translate('Preparing to import…'); ?></p>
										</span>
									</div>
								</div>
								<?php
								$controller->addInlineJavascript(
									'jQuery("#import' . $tree->tree_id . '").load("import.php?gedcom_id=' . $tree->tree_id . '&keep_media' . $tree->tree_id . '=' . KT_Filter::post('keep_media' . $tree->tree_id) . '");'
								);
							} ?>
							<div class="grid-x<?php echo $importing ? ' hide' : ''; ?>" id="actions<?php echo $tree->tree_id; ?>">
								<div class="button-group expanded stacked-for-small">
									<a class="button hollow" href="index.php?ged=<?php echo $tree->tree_name_url; ?>">
										<?php echo KT_I18N::translate('Go to this family tree'); ?>
									</a>
									<a class="button hollow" href="admin_trees_config.php?ged=<?php echo $tree->tree_name_url; ?>">
										<i class="<?php echo $iconStyle; ?> fa-gears"></i>
										<?php echo KT_I18N::translate('Configure this family tree'); ?>
									</a>
									<?php if (count(KT_Tree::GetAll()) > 1) { ?>
										<div class="button hollow">
											<?php if ($tree->tree_name_url === KT_Site::preference('DEFAULT_GEDCOM')) { ?>
												<span>
													<i class="<?php echo $iconStyle; ?> fa-star"></i>
													<?php echo KT_I18N::translate('Default family tree'); ?>
												</span>
											<?php } else { ?>
												<a href="#" onclick="document.defaultform<?php echo $tree->tree_id; ?>.submit();">
													<i class="<?php echo $iconStyle; ?> fa-star"></i>
													<?php echo KT_I18N::translate('Set as default') ?>
													<span class="sr-only">
														<?php echo $tree->tree_name_url; ?>
													</span>
												</a>
												<form name="defaultform<?php echo $tree->tree_id; ?>" method="post">
													<input type="hidden" name="action" value="setdefault">
													<input type="hidden" name="default_ged" value="<?php echo $tree->tree_name_url; ?>">
													<input type="hidden" name="default_title" value="<?php echo $tree->tree_title_html; ?>">
													<?php echo KT_Filter::getCsrf(); ?>
													<button class="sr-only" type="submit">
														<?php echo KT_I18N::translate('Set as default') ?>
													</button>
												</form>
											<?php } ?>
										</div>
									<?php } ?>
									<a class="button hollow" href="<?php echo KT_SCRIPT_NAME; ?>?action=importform&amp;gedcom_id=<?php echo $tree->tree_id; ?>">
										<i class="<?php echo $iconStyle; ?> fa-upload"></i>
										<?php echo KT_I18N::translate('Import a GEDCOM file'); ?>
									</a>
									<a class="button hollow" href="adminDownload.php?ged=<?php echo $tree->tree_name_url; ?>">
										<i class="<?php echo $iconStyle; ?> fa-download"></i>
										<?php echo KT_I18N::translate('Export a GEDCOM file'); ?>
									</a>
									<a class="button hollow" href="#" onclick="if (confirm('<?php echo KT_I18N::translate('Are you sure you want to delete “%s”?', KT_Filter::escapeJs($tree->tree_title)); ?>')) { document.delete_form<?php echo $tree->tree_id; ?>.submit(); } return false;">
										<i class="<?php echo $iconStyle; ?> fa-trash-can"></i>
										<?php echo KT_I18N::translate('Delete this family tree'); ?>
									</a>
									<form name="delete_form<?php echo $tree->tree_id; ?>" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
										<?php echo KT_Filter::getCsrf(); ?>
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="gedcom_id" value="<?php echo $tree->tree_id; ?>">
										<input type="hidden" name="gedcom_title" value="<?php echo KT_Filter::escapeHtml($tree->tree_title); ?>">
									</form>
								</div>
							</div>
						</div>
					</li>
				<?php }
			}
			if (KT_USER_IS_ADMIN) {
				KT_Tree::GetAll() ? $accordionClass = '' : $accordionClass = ' is-active'; ?>
				<li class="accordion-item<?php echo $accordionClass; ?>" data-accordion-item>
					<a class="accordion-title" href="#create">
						<?php echo KT_I18N::translate('Create a new family tree'); ?>
					</a>
					<div class="accordion-content" id="create" data-tab-content>
						<div class="">
							<?php if (!KT_Tree::GetAll()) { ?>
								<div class="callout alert">
									<?php echo KT_I18N::translate('You need to create a family tree before you can start adding your data.'); ?>
								</div>
							<?php } ?>
							<div class="cell callout alert small">
								<?php echo KT_I18N::translate('After creating the family tree you will be able to upload or import data from a GEDCOM file.'); ?>
							</div>
							<form name="createform" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
								<?php echo KT_Filter::getCsrf(); ?>
								<input type="hidden" name="action" value="new_tree">
								<div class="grid-x grid-padding-y">
									<div class="cell">
										<div class="input-group">
											<span class="input-group-label">
												<?php echo KT_I18N::translate('Family tree title'); ?>
											</span>
											<input
												type="text"
												id="gedcom_title"
												class="input-group-field"
												name="gedcom_title"
												size="50"
												maxlength="255"
												required
												placeholder="<?php echo $default_tree_title; ?>"
											>
										</div>
										<div class="callout info-help">
											<?php echo KT_I18N::translate('This is the name used for display.'); ?>
										</div>
									</div>
									<div class="cell">
										<div class="input-group">
											<span class="input-group-label">
												<?php echo KT_I18N::translate('URL'); ?>
												<span>
													<?php echo KT_SERVER_NAME . KT_SCRIPT_PATH; ?>?ged=
												</span>
											</span>
											<input
												type="text"
												id="ged_name"
												class="input-group-field"
												name="ged_name"
												pattern="[^&lt;&gt;&amp;&quot;#^$*?{}()\[\]/\\]*"
												maxlength="31"
												value="<?php echo $default_tree_name; ?>"
												required
											>
										</div>
										<div class="callout info-help">
											<?php echo KT_I18N::translate('Keep this short and avoid spaces and punctuation. A family name might be a good choice.'); ?>
										</div>
									</div>
									<div class="cell">
										<button class="button" type="submit">
											<i class="<?php echo $iconStyle; ?> fa-plus-circle"></i>
											<?php echo KT_I18N::translate('Create'); ?>
										</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</li>
			<?php } ?>
		</ul>
	</div>
</div>
<?php
