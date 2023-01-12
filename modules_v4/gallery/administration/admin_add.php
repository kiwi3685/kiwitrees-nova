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

require_once KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';
global $MEDIA_DIRECTORY, $iconStyle;

$gedID 	= KT_Filter::post('gedID');
$save	= KT_Filter::post('save', '');

$controller = new KT_Controller_Page();
$controller
	->pageHeader()
	->addExternalJavascript(KT_CKEDITOR_CLASSIC)
	->addInlineJavascript('
		ckeditorStandard();

		jQuery("input[name=gallery_plugin]").change(function(){
			var selected = jQuery(this).val();
			
			if(selected == "kiwitrees") {
				jQuery("[name=gallery_folder_w]").prop("disabled", false);
				jQuery("[name=gallery_folder_f]").prop("disabled", true);
			}
				
			if(selected == "flicker") {
				jQuery("[name=gallery_folder_w]").prop("disabled", true);
				jQuery("[name=gallery_folder_f]").prop("disabled", false);
			}

		});

	');

if ($save) {
	// ##block table entries
	$block_id 		= KT_Filter::postInteger('block_id');
	$block_order 	= KT_Filter::postInteger('block_order');
	$gedID 			= KT_Filter::post('gedID');
	$header      	= KT_Filter::post('header',  KT_REGEX_UNSAFE);
	$gallerybody    = KT_Filter::post('gallerybody', KT_REGEX_UNSAFE);
	$languages 		= array();

	KT_DB::prepare(
		"INSERT INTO `##block` (gedcom_id, module_name, block_order) VALUES (NULLIF(?, ''), ?, ?)"
	)->execute(array(
		$gedID,
		$this->getName(),
		$block_order
	));

	$block_id = KT_DB::getInstance()->lastInsertId();

	// ##block_setting table entries
	set_block_setting($block_id, 'gallery_title', KT_Filter::post('gallery_title', KT_REGEX_UNSAFE));
	set_block_setting($block_id, 'gallery_description', KT_Filter::post('gallery_description', KT_REGEX_UNSAFE));
	set_block_setting($block_id, 'gallery_folder_w', KT_Filter::post('gallery_folder_w', KT_REGEX_UNSAFE));
	set_block_setting($block_id, 'gallery_folder_f', KT_Filter::post('gallery_folder_f', KT_REGEX_UNSAFE));
	set_block_setting($block_id, 'gallery_access', KT_Filter::post('gallery_access', KT_REGEX_UNSAFE));
	set_block_setting($block_id, 'gallery_plugin', KT_Filter::post('gallery_plugin', KT_REGEX_UNSAFE));

	foreach (KT_I18N::used_languages() as $code=>$name) {
		if (KT_Filter::postBool('lang_' . $code)) {
			$languages[] = $code;
		}
	}
	set_block_setting($block_id, 'languages', implode(',', $languages));

	switch ($save) {
		case 1:
			// save and re-edit
			?><script>
				window.location='module.php?mod=gallery&mod_action=admin_edit&block_id=<?php echo $block_id; ?>&gedID=<?php echo $gedID; ?>
			</script><?php
		break;
		case 2:
			// save & close
			?><script>
				window.location='module.php?mod=gallery&mod_action=admin_config&gedID=<?php echo $gedID; ?>';
			</script><?php
		break;
		case 3:
			// save and add another
			?><script>
				window.location='module.php?mod=gallery&mod_action=admin_add';
			</script><?php
		break;
	}

}

$controller->setPageTitle(KT_I18N::translate('Add gallery'));

$block_id         = '';
$header           = '';
$gallerybody      = '';
$item_title       = '';
$item_description = '';
$item_folder_w    = $MEDIA_DIRECTORY;
$item_folder_f    = '';
$item_access      = KT_I18N::translate('All');
$gedID            = '';
$item_plugin      = 'kiwitrees';

$block_order = KT_DB::prepare(
	"SELECT IFNULL(MAX(block_order) + 1, 0) FROM `##block` WHERE module_name = ?"
)->execute(array($this->getName()))->fetchOne();

echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart($this->getName(), $controller->getPageTitle(), '', '', '/kb/user-guide/gallery/'); ?>

	<form class="cell" name="gallery" method="post" action="module.php?mod=gallery&amp;mod_action=admin_add">
		<input type="hidden" name="block_id" value="<?php echo $block_id; ?>">
		<div class="grid-x grid-margin-x grid-margin-y">
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
				<textarea name="gallery_description" class="html-edit"><?php echo htmlspecialchars((string) $item_description); ?></textarea>
			</div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Source'); ?>
			</label>
			<div class="cell medium-5" id="kiwitrees-div">
				<input 
					id="kiwitrees-radio" 
					type="radio" 
					name="gallery_plugin" 
					value="kiwitrees" 
					<?php echo ($item_plugin == 'kiwitrees') ? ' checked' :  ''; ?> 
				>
				<?php echo KT_I18N::translate('Kiwitrees');	?>
				<div class="input-group">
 					<span class="input-group-label"><?php echo KT_I18N::translate('Media folder name'); ?></span>
					<?php echo select_edit_control("gallery_folder_w", KT_Query_Media::folderList(), null, htmlspecialchars((string) $item_folder_w), ($item_plugin == 'kiwitrees') ? '' : 'disabled'); ?>
				</div>
			</div>
			<div class="cell medium-5" id="flickr-div">
				<input 
					id="flickr-radio" 
					type="radio" 
					name="gallery_plugin" 
					value="flicker" 
					<?php echo ($item_plugin == 'flickr') ? ' checked' : ''; ?> 
				>
				<?php echo KT_I18N::translate('Flickr'); ?>
				<div class="input-group">
					<span class="input-group-label"><?php echo KT_I18N::translate('Flickr set number'); ?></span>
					<input
					 	class="input-group-field" 
						id="flickr" 
						type="text" 
						name="gallery_folder_f" 
						value="<?php echo htmlspecialchars((string) $item_folder_f); ?>"
					>
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
				<button class="button primary" type="submit" name="save" value="3">
					<i class="<?php echo $iconStyle; ?> fa-save"></i>
					<?php echo KT_I18N::translate('Save and add another'); ?>
				</button>
				<button class="button hollow" type="button" onclick="window.location='module.php?mod=gallery&amp;mod_action=admin_config'">
					<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
					<?php echo KT_I18N::translate('Cancel'); ?>
				</button>
			</div>
		</div>
	</form>

<?php echo pageClose();
