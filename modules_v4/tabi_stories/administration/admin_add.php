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
global $iconStyle;

$gedID  = KT_Filter::post('gedID') ? KT_Filter::post('gedID') : KT_GED_ID;
$tree   = KT_Tree::getNameFromId($gedID);
$save	= KT_Filter::post('save', '');

$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_I18N::translate('Add a story'))
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addExternalJavascript(KT_CKEDITOR_CLASSIC)
	->addInlineJavascript('
		ckeditorStandard();

		autocomplete();

		jQuery("#addField").click(function(){
			jQuery(".add_indi:last").clone().insertAfter(".add_indi:last");
			jQuery(".add_indi:last>input").attr("value", "");
		});
	')
;

if ($save) {
	$block_id     = KT_Filter::postInteger('block_id');
	$block_order  = KT_Filter::postInteger('block_order');
	$item_title   = KT_Filter::post('story_title',   KT_REGEX_UNSAFE);
	$item_content = KT_Filter::post('story_content', KT_REGEX_UNSAFE);
	$item_access  = KT_Filter::post('story_access',  KT_REGEX_UNSAFE);
	$languages    = array();
	$xref         = array();

	foreach (KT_Filter::post('xref') as $indi_ref => $name) {
		$xref[] = $name;
	}

	KT_DB::prepare(
		"INSERT INTO `##block` (gedcom_id, module_name, block_order) VALUES (NULLIF(?, ''), ?, ?)"
	)->execute(array(
		$gedID,
		$this->getName(),
		$block_order)
	);

	$block_id = KT_DB::getInstance()->lastInsertId();

	set_block_setting($block_id, 'xref', rtrim(implode(',', $xref)));
	set_block_setting($block_id, 'story_title',   $item_title);
	set_block_setting($block_id, 'story_content', $item_content); 
	set_block_setting($block_id, 'story_access',  $item_access); 

	foreach (KT_I18N::used_languages() as $code => $name) {
		if (KT_Filter::postBool('lang_' . $code)) {
			$languages[] = $code;
		}
	}
	set_block_setting($block_id, 'languages', rtrim(implode(',', $languages)));

	switch ($save) {
		case 1:
			// save and re-edit
			?><script>
				window.location='module.php?mod=<?php echo $this->getName(); ?>&mod_action=admin_edit&block_id=<?php echo $block_id; ?>&gedID=<?php echo $gedID; ?>
			</script><?php
		break;
		case 2:
			// save & close
			?><script>
				window.location='module.php?mod=<?php echo $this->getName(); ?>&mod_action=admin_config';
			</script><?php
		break;
		case 3:
			// save and add new
			?><script>
				window.location='module.php?mod=<?php echo $this->getName(); ?>&mod_action=admin_add';
			</script><?php
		break;
	}
}

$block_id     = '';
$item_title   = '';
$item_content = '';
$item_access  = KT_I18N::translate('All');
$xref         = KT_Filter::get('xref', KT_REGEX_XREF);
$count_xref   = 1;

$block_order = KT_DB::prepare(
	"SELECT IFNULL(MAX(block_order) + 1, 0) FROM `##block` WHERE module_name = ?"
)->execute(array($this->getName()))->fetchOne();

echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart($this->getName(), $controller->getPageTitle()); ?>

	<!-- Family tree -->
	<div class="cell medium-2">
		<label for="gedID"><?php echo KT_I18N::translate('Current family tree'); ?></label>
	</div>
	<div class="cell medium-4">
		<form id="tree" method="post" action="#" name="tree">
			<?php echo select_ged_control('gedID', KT_Tree::getIdList(), null, $tree, ' onchange="tree.submit();"'); ?>
		</form>
	</div>
	<div class="cell medium-6">
		<div class="cell callout info-help medium-6">
			<?php echo KT_I18N::translate('Ensure this is the tree you want to select "Linked individuals" from'); ?>
		</div>
	</div>
	<form class="cell" name="story" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_add">
		<input type="hidden" name="block_id" value="<?php echo $block_id; ?>">
		<div class="grid-x">
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Title'); ?>				
			</label>
			<div class="cell medium-10">
				<input type="text" name="story_title" value="<?php echo htmlspecialchars((string) $item_title); ?>">
			</div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Story'); ?>
			</label>
			<div class="cell medium-10">
				<textarea name="story_content" class="html-edit"><?php echo htmlspecialchars((string) $item_content); ?></textarea>
			</div>
			<hr class="cell">
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Linked individuals'); ?>
			</label>
			<div class="cell medium-10">
				<div class="grid-x grid-margin-x">
					<div class="cell medium-4">
						<?php for ($x = 1; $x < 3; $x++) {
							echo autocompleteHtml(
								'xref' . $x,
								'INDI',
								$tree,
								$xref,
								KT_I18N::translate('Individual name'),
								'xref[]',
								$xref,
							);
						} ?>
					</div>
					<div class="cell medium-8">
						<div class="cell callout info-help medium-6">
							<?php echo KT_I18N::translate('To add more than two linked individuals, complete the fields below as needed, then click "Save and re-edit"'); ?>
						</div>
					</div>
				</div>
			</div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Story order'); ?>
			</label>
			<div class="cell medium-1">
				<input type="number" name="block_order" value="<?php echo $block_order; ?>">
			</div>
			<div class="cell medium-9"></div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Access level'); ?>
			</label>
			<div class="cell medium-4">
				<?php echo edit_field_access_level('story_access', $item_access); ?>
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
				<button class="button hollow" type="button" onclick="window.location='module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config'">
					<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
					<?php echo KT_I18N::translate('Cancel'); ?>
				</button>
			</div>
		</div>
	</form>

<?php echo pageClose();
