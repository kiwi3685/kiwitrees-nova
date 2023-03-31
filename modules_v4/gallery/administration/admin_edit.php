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

require_once KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';
global $iconStyle;

$block_id = KT_Filter::getInteger('block_id', KT_Filter::postInteger('block_id'));;
$gedID    = KT_Filter::post('gedID') ? KT_Filter::post('gedID') : '';
$save     = KT_Filter::post('save', '');


$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_I18N::translate('Edit a gallery'))
	->pageHeader()
	->addExternalJavascript(KT_CKEDITOR_CLASSIC)
	->addInlineJavascript('ckeditorStandard();');

if ($save) {
	$block_id     = KT_Filter::postInteger('block_id');
	$block_order  = KT_Filter::postInteger('block_order');
	$item_title   = KT_Filter::post('gallery_title',   KT_REGEX_UNSAFE);
	$item_content = KT_Filter::post('gallery_content', KT_REGEX_UNSAFE);
	$item_access  = KT_Filter::post('gallery_access',  KT_REGEX_UNSAFE);
	$item_folder  = KT_Filter::post('gallery_folder',  KT_REGEX_UNSAFE);
	$item_plugin  = KT_Filter::post('gallery_plugin',  KT_REGEX_UNSAFE);
	$languages    = array();

	KT_DB::prepare(
		"UPDATE `##block` SET gedcom_id = NULLIF(?, ''), block_order = ? WHERE block_id = ?"
	)->execute(array(
		$gedID,
		$block_order,
		$block_id
	));

	set_block_setting($block_id, 'gallery_title',   $item_title);
	set_block_setting($block_id, 'gallery_content', $item_content);
	set_block_setting($block_id, 'gallery_access',  $item_access);
	set_block_setting($block_id, 'gallery_folder',  $item_folder);
	set_block_setting($block_id, 'gallery_plugin',  $item_plugin);

	foreach (KT_I18N::used_languages() as $code=>$name) {
		if (KT_Filter::postBool('lang_' . $code)) {
			$languages[] = $code;
		}
	}
	set_block_setting($block_id, 'languages', implode(',', $languages));

	// Check the "uploads" directory exists if it is needed
	if (get_block_setting($block_id, 'gallery_plugin') == 'uploads') {
		echo $this->checkUploadsDir();
	}

	switch ($save) {
		case 1:
			// save and re-edit
			?><script>
				window.location='module.php?mod=<?php echo $this->getName(); ?>&mod_action=admin_edit&block_id=' . $block_id . '&gedID=' . $gedID;
			</script><?php
		break;
		case 2:
			// save & close
			?><script>
				window.location='module.php?mod=<?php echo $this->getName(); ?>&mod_action=admin_config';
			</script><?php
		break;
	}

}

$item_title       = get_block_setting($block_id, 'gallery_title');
$item_content     = get_block_setting($block_id, 'gallery_content');
$item_access      = get_block_setting($block_id, 'gallery_access');
$item_folder      = get_block_setting($block_id, 'gallery_folder');
$item_plugin      = get_block_setting($block_id, 'gallery_plugin');

$block_order      = KT_DB::prepare(
"SELECT block_order FROM `##block` WHERE block_id =?"
)->execute(array($block_id))->fetchOne();

$gedID            = KT_DB::prepare(
	"SELECT gedcom_id FROM `##block` WHERE block_id=?"
)->execute(array($block_id))->fetchOne();

echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart($this->getName(), $controller->getPageTitle(), '', '', '/kb/user-guide/gallery/'); ?>

	<form class="cell" name="gallery" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit">
		<input type="hidden" name="block_id" value="<?php echo $block_id; ?>">

		<div class="grid-x grid-margin-y">

			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Title'); ?>				
			</label>
			<div class="cell medium-10">
				<input type="text" name="gallery_title" value="<?php echo htmlspecialchars((string) $item_title); ?>">
			</div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Description'); ?>
			</label>
			<div class="cell medium-10">
				<textarea name="gallery_content" class="html-edit"><?php echo htmlspecialchars((string) $item_content); ?></textarea>
			</div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Source'); ?>
			</label>
			<div class="cell medium-10">
				<div class="grid-x">
					<div class="switch cell medium-1 tiny">
						<?php $item_plugin == 'kiwitrees' ? $checked = 'checked' : $checked = ''; ?>
						<input class="switch-input" id="kiwitrees-radio" type="radio" <?php echo $checked; ?> name="gallery_plugin" value="kiwitrees" onclick="hide_fields();">
						<label class="switch-paddle" for="kiwitrees-radio">
							<span class="show-for-sr"><?php echo KT_I18N::translate('Kiwitrees family tree media folder'); ?>	</span>
							<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
							<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('No'); ?></span>
						</label>
					</div>
					<div class="cell auto">
						<?php echo KT_I18N::translate('Kiwitrees family tree media folder'); ?>						
					</div>
				</div>
				<div class="grid-x">
					<div class="switch cell medium-1 tiny">
						<?php $item_plugin == 'flickr' ? $checked = 'checked' : $checked = ''; ?>
						<input class="switch-input" id="flickr-radio" type="radio" <?php echo $checked; ?> name="gallery_plugin" value="flickr" onclick="hide_fields();">
						<label class="switch-paddle" for="flickr-radio">
							<span class="show-for-sr"><?php echo KT_I18N::translate('Flickr album set'); ?>	</span>
							<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
							<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('No'); ?></span>
						</label>
					</div>
					<div class="cell auto">
						<?php echo KT_I18N::translate('Flickr album set'); ?>						
					</div>
				</div>
				<div class="grid-x">
					<div class="switch cell medium-1 tiny">
						<?php $item_plugin == 'uploads' ? $checked = 'checked' : $checked = ''; ?>
						<input class="switch-input" id="uploads-radio" type="radio" <?php echo $checked; ?> name="gallery_plugin" value="uploads" onclick="hide_fields();">
						<label class="switch-paddle" for="uploads-radio">
							<span class="show-for-sr"><?php echo KT_I18N::translate('Images uploaded to a kiwitrees un-regulated uploads folder'); ?>	</span>
							<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
							<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('No'); ?></span>
						</label>
					</div>
					<div class="cell auto">
						<?php echo KT_I18N::translate('Kiwitrees un-regulated uploads folder'); ?>	
					</div>
				</div>
			</div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Folder'); ?>
			</label>
			<div class="cell medium-10">
				<?php $item_plugin == 'kiwitrees' ? $kiwitreesStyle = '' : $kiwitreesStyle = 'style="display:none;"'; ?>
				<?php $item_plugin == 'flickr'    ? $flickrStyle    = '' : $flickrStyle    = 'style="display:none;"'; ?>
				<?php $item_plugin == 'uploads'   ? $uploadstyle    = '' : $uploadstyle    = 'style="display:none;"'; ?>

				<div class="grid-x grid-margin-x kiwitreesInputGroup" <?php echo $kiwitreesStyle; ?>>
					<div class="input-group cell medium-6">
	 					<span class="input-group-label"><?php echo KT_I18N::translate('Media folder name'); ?></span>
						<select 
							id="kiwitrees" 
							name="gallery_folder"
						>
							<?php foreach (KT_Query_Media::folderListAll() as $key => $value) {
								if ($key == $item_folder) { ?>
									<option 
										value="<?php echo htmlspecialchars((string) $key); ?>" 
										selected="selected"
									>
										<?php echo htmlspecialchars((string) $value); ?>
									</option>
								<?php } else { ?>
									<option value="<?php echo htmlspecialchars((string) $key); ?>">
										<?php echo htmlspecialchars((string) $value); ?>
									</option>';
								<?php }
							} ?>
						</select>
					</div>
					<div class="cell callout info-help">
						<?php echo KT_I18N::translate('
							Select a folder from the dropdown. The list shows ALL media folders present in the /data/media/ folder of the server.
							The selected folder  must containing only media files registered to the family tree selected for this gallery. 
							See Faq page for more details.
						'); ?>
					</div>
				</div>
				<div class="grid-x grid-margin-x flickrInputGroup" <?php echo $flickrStyle; ?>>
					<div class="input-group cell medium-6">
						<span class="input-group-label"><?php echo KT_I18N::translate('Flickr set number'); ?></span>
						<input
						 	class="input-group-field" 
							id="flickr" 
							type="text" 
							name="gallery_folder" 
							value="<?php echo ($item_plugin == 'flickr' ? htmlspecialchars((string) $item_folder) : ''); ?>"
							placeholder="123456789123456789"
							<?php if ($item_plugin != 'flickr') {echo 'disabled';} ?>
						>
					</div>
					<div class="cell callout info-help">
						<?php echo KT_I18N::translate('This field requires the "set number" for a public Flickr album. The numbers will be something like 72157633272831222'); ?>
					</div>
				</div>
				<div class="grid-x grid-margin-x uploadsInputGroup" <?php echo $uploadstyle; ?>>
					<div class="input-group cell medium-6">
						<span class="input-group-label"><?php echo KT_I18N::translate('Uploads sub-folder'); ?></span>
						<input
						 	class="input-group-field" 
							id="uploads" 
							type="text" 
							name="gallery_folder" 
							value="<?php echo ($item_plugin == 'uploads' ? htmlspecialchars((string) $item_folder) : ''); ?>"
							placeholder="<?php echo KT_I18N::translate('my folder name'); ?>"
							<?php if($item_plugin != 'uploads') {echo 'disabled';} ?>
						>
					</div>
					<div class="cell callout info-help">
						<?php echo KT_I18N::translate('Enter the sub-folder name that contains the images, and exists in a "kiwitrees/uploads/" folder, created specifically for images not managed as part of  any family tree\'s GEDCOM data. If you have not already created the "uploads" folder it will be created automatically when you save this page.'); ?>
					</div>
				</div>
			</div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Gallery order'); ?>
			</label>
			<div class="cell medium-1">
				<input type="number" name="block_order" value="<?php echo $block_order; ?>">
			</div>
			<div class="cell medium-9"></div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Show for which family tree'); ?>
			</label>
			<div class="cell medium-4">
				<?php echo select_edit_control('gedID', KT_Tree::getIdList(), KT_I18N::translate('All'), $gedID); ?>
			</div>
			<div class="cell medium-6"></div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Access level'); ?>
			</label>
			<div class="cell medium-4">
				<?php echo edit_field_access_level('gallery_access', $item_access); ?>
			</div>
			<div class="cell medium-6"></div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Show this block for which languages?'); ?>
			</label>
			<div class="cell medium-10">
				<?php $languages = get_block_setting($block_id, 'languages');
				echo edit_language_checkboxes('lang_', $languages); ?>
			</div>
			<div class="cell align-left button-group">
				<button class="button primary" type="submit" name="save" value="1">
					<i class="<?php echo $iconStyle; ?> fa-save"></i> 
					<?php echo KT_I18N::translate('Save and re-edit'); ?>
				</button>
				<button class="button primary" type="submit" name="save" value="2">
					<i class="<?php echo $iconStyle; ?> fa-save"></i>
					<?php echo KT_I18N::translate('Save and close'); ?>
				</button>
				<button class="button hollow" type="button" onclick="window.location='module.php?mod=<?php echo $this->getName(); ?>&mod_action=admin_config'">
					<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
					<?php echo KT_I18N::translate('Cancel'); ?>
				</button>
			</div>
		</div>
	</form>

<?php echo pageClose();
