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

if (!defined('KT_KIWITREES')) {
	 header('HTTP/1.0 403 Forbidden');
	 exit;
 }

require_once KT_ROOT.'includes/functions/functions_mediadb.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->requireEditorLogin() /* Editing may be disabled, even for admins */
	->setPageTitle(KT_I18N::translate('Upload a place image file'));

$action = KT_Filter::post('action');

if ($action == "upload") {
	if (!empty($_FILES['mediafile']["name"]) || !empty($_FILES['thumbnail']["name"])) {
		$folder = 'place_images';


		// Make sure the media folder exists
		if (!is_dir(KT_DATA_DIR . $MEDIA_DIRECTORY)) {
			if (@mkdir(KT_DATA_DIR . $MEDIA_DIRECTORY, KT_PERM_EXE, true)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s was created.', '<span class="filename">' . KT_DATA_DIR . $MEDIA_DIRECTORY . '</span>'));
			} else {
				KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . KT_DATA_DIR . $MEDIA_DIRECTORY . '</span>'));
			}
		}

		// Managers can create new media paths (subfolders).  Users must use existing folders.
		if ($folderName && !is_dir(KT_DATA_DIR . $MEDIA_DIRECTORY . $folderName)) {
			if (KT_USER_GEDCOM_ADMIN) {
				if (@mkdir(KT_DATA_DIR . $MEDIA_DIRECTORY . $folderName, KT_PERM_EXE, true)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s was created.', '<span class="filename">' . KT_DATA_DIR . $MEDIA_DIRECTORY . $folderName . '</span>'));
				} else {
					KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . KT_DATA_DIR . $MEDIA_DIRECTORY . $folderName . '</span>'));
				}
			}
		}

		// The media folder exists.  Now create a thumbnail folder to match it.
		if (!is_dir(KT_DATA_DIR . $MEDIA_DIRECTORY . 'thumbs/' . $folderName)) {
			if (!@mkdir(KT_DATA_DIR . $MEDIA_DIRECTORY . 'thumbs/' . $folderName, KT_PERM_EXE, true)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . KT_DATA_DIR . $MEDIA_DIRECTORY . 'thumbs/' . $folderName . '</span>'));
			}
		}

		// A thumbnail file with no main image?
		if (!empty($_FILES['thumbnail']['name']) && empty($_FILES['mediafile']['name'])) {
			// Assume the user used the wrong field, and treat this as a main image
			$_FILES['mediafile'] = $_FILES['thumbnail'];
			unset($_FILES['thumbnail']);
		}

		// Check for image having 0 bytes (corrupted)  or too large to import
		if ($_FILES['mediafile']['size'] && ($_FILES['mediafile']['size'] === 0 || $_FILES['mediafile']['size'] > int_from_bytestring(detectMaxUploadFileSize()))) {
			KT_FlashMessages::addMessage(KT_I18N::translate('The media file you selected either has a size of zero bytes or is too large to be uploaded.'));
			unset($_FILES['mediafile']);
		}

		// Thumbnails must be images.
		if (!empty($_FILES['thumbnail']['name']) && !preg_match('/^image\/(png|gif|jpeg)/', $_FILES['thumbnail']['type'])) {
			KT_FlashMessages::addMessage(KT_I18N::translate('Thumbnails must be images.'));
		}

		// User-specified filename?
		$filename = KT_Filter::post('filename', KT_REGEX_UNSAFE);
		// Use the name of the uploaded file?
		if (!$filename && !empty($_FILES['mediafile']['name'])) {
			$filename = $_FILES['mediafile']['name'];
		}

		// Validate the media path and filename
		if (preg_match('/([\/\\\\<>])/', $filename, $match)) {
			// Local media files cannot contain certain special characters
			KT_FlashMessages::addMessage(KT_I18N::translate('Filenames are not allowed to contain the character “%s”.', $match[1]));
			$filename = '';
		} elseif (preg_match('/(\.(php|pl|cgi|bash|sh|bat|exe|com|htm|html|shtml))$/i', $filename, $match)) {
			// Do not allow obvious script files.
			KT_FlashMessages::addMessage(KT_I18N::translate('Filenames are not allowed to have the extension “%s”.', $match[1]));
			$filename = '';
		} elseif (!$filename) {
			KT_FlashMessages::addMessage(KT_I18N::translate('No media file was provided.'));
		} else {
			$fileName = $filename;
		}

		// Now copy the file to the correct location.
		if (!empty($_FILES['mediafile']['name'])) {
			$serverFileName = KT_DATA_DIR . $MEDIA_DIRECTORY . $folderName . $fileName;
			if (file_exists($serverFileName)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('The file %s already exists.  Use another filename.', $folderName . $fileName));
				$filename = '';
			}
			if (move_uploaded_file($_FILES['mediafile']['tmp_name'], $serverFileName)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('The file %s was uploaded.', '<span class="filename">' . $serverFileName . '</span>'));
				chmod($serverFileName, KT_PERM_FILE);
				AddToLog('Media file ' . $serverFileName . ' uploaded', 'media');
			} else {
				KT_FlashMessages::addMessage(
					KT_I18N::translate('There was an error uploading your file.') .
					'<br>' .
					file_upload_error_text($_FILES['mediafile']['error'])
				);
				$filename = '';
			}

			// Now copy the (optional thumbnail)
			if (!empty($_FILES['thumbnail']['name']) && preg_match('/^image\/(png|gif|jpeg)/', $_FILES['thumbnail']['type'], $match)) {
				$extension = $match[1];
				$thumbFile = preg_replace('/\.[a-z0-9]{3,5}$/', '.' . $extension, $fileName);
				$serverFileName = KT_DATA_DIR . $MEDIA_DIRECTORY . 'thumbs/' . $folderName .  $thumbFile;
				if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $serverFileName)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('The file %s was uploaded.', '<span class="filename">' . $serverFileName . '</span>'));
					chmod($serverFileName, KT_PERM_FILE);
					AddToLog('Thumbnail file ' . $serverFileName . ' uploaded', 'media');
				}
			}
		}
	}
}

$controller	->pageHeader();

// Determine file size limit
$filesize = detectMaxUploadFileSize();
if (empty($filesize)) $filesize = "2M";

// Print the form ?>
<div id="admin_media" class="cell">
	<h4><?php echo KT_I18N::translate('Upload a media file for a place'); ?></h4>
	<form method="post" name="uploadmedia" enctype="multipart/form-data" action="<?php echo KT_SCRIPT_NAME; ?>">
		<input type="hidden" name="action" value="upload">
		<div class="grid-x grid-margin-x grid-margin-y grid-padding-x grid-padding-y">
			<div class="cell callout warning">
				<?php echo KT_I18N::translate('Maximum simultaneous upload allowed by your server is %s MB', KT_I18N::number(detectMaxUploadFileSize() / 1048576)); ?>
			</div>

				<div class="cell medium-10 medium-offset-1 mediaUpload">
					<h5><?php echo KT_I18N::translate('Media file'); ?></h5>
					<div class="grid-x grid-margin-y">
						<div class="cell large-3">
							<label for="<?php echo 'mediafile'; ?>"><?php echo KT_I18N::translate('Media file to upload'); ?></label>
						</div>
						<div class="cell large-9">
							<label for="<?php echo 'mediafile'; ?>" class="button">
								<?php echo KT_I18N::translate('Choose File'); ?>
							</label>
							<span class="fileName"><?php echo KT_I18N::translate('No file chosen'); ?></span>
							<input id="<?php echo 'mediafile'; ?>" name="<?php echo 'mediafile'; ?>" class="show-for-sr" type="file">
						</div>
						<div class="cell large-3">
							<label for="<?php echo 'thumbnail'; ?>"><?php echo KT_I18N::translate('Thumbnail to upload'); ?></label>
						</div>
						<div class="cell large-9">
							<label for="<?php echo 'thumbnail'; ?>" class="button">
								<?php echo KT_I18N::translate('Choose File'); ?>
							</label>
							<span class="fileName"><?php echo KT_I18N::translate('No file chosen'); ?></span>
							<input id="<?php echo 'thumbnail'; ?>" name="<?php echo 'thumbnail'; ?>" class="show-for-sr" type="file">
						</div>
						<?php if (KT_USER_GEDCOM_ADMIN) { ?>
							<div class="cell large-3">
								<label for="<?php echo 'filename'; ?>"><?php echo KT_I18N::translate('File name on server'); ?></label>
							</div>
							<div class="cell large-9">
								<input id="<?php echo 'filename'; ?>" name="<?php echo 'filename'; ?>" type="text" placeholder="<?php echo KT_I18N::translate('Leave blank to the keep original file name.'); ?>">
							</div>
						<?php } else { ?>
							<div class="cell large-3"></div>
							<div class="cell large-9">
								<input type="hidden" name="<?php echo 'filename'; ?>" value="">
							</div>
						<?php } ?>
					</div>

				</div>

			<!-- Print the Submit button for uploading the media -->
			<div class="cell">
				<button type="submit" class="button">
					<i class="<?php echo $iconStyle; ?> fa-save"></i>
					<?php echo KT_I18N::translate('Upload'); ?>
				</button>
			</div>
		</div>
	</form>
</div>
