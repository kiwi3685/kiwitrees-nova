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
$gedID  = KT_Filter::post('gedID') ? KT_Filter::post('gedID') : KT_GED_ID;
$tree   = KT_Tree::getNameFromId($gedID);
$save	= KT_Filter::post('save', '');

$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_I18N::translate('Edit a story'))
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addExternalJavascript(KT_CKEDITOR_CLASSIC)
	->addInlineJavascript('
		ckeditorStandard();

		autocomplete();
	');

if ($save) {
	$block_id     = KT_Filter::postInteger('block_id');
	$block_order  = KT_Filter::postInteger('block_order');
	$item_title   = KT_Filter::post('story_title',   KT_REGEX_UNSAFE);
	$item_content = KT_Filter::post('story_content', KT_REGEX_UNSAFE);
	$item_access  = KT_Filter::post('story_access',  KT_REGEX_UNSAFE);
	$languages    = array();
	foreach (KT_I18N::used_languages() as $code=>$name) {
		if (KT_Filter::postBool('lang_' . $code)) {
			$languages[] = $code;
		}
	}	
	$xref         = array();
	foreach (KT_Filter::post('xref') as $indi_ref => $name) {
		$xref[] = $name;
	}

	KT_DB::prepare(
		"UPDATE `##block` SET gedcom_id = NULLIF(?, ''), block_order = ? WHERE block_id = ?"
	)->execute(array(
		$gedID,
		$block_order,
		$block_id
	));

	set_block_setting($block_id, 'xref', rtrim(implode(',', $xref), ","));
	set_block_setting($block_id, 'story_title',   $item_title);
	set_block_setting($block_id, 'story_content', $item_content); 
	set_block_setting($block_id, 'story_access',  $item_access); 
	set_block_setting($block_id, 'languages', rtrim(implode(',', $languages), ","));

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

$item_title   = get_block_setting($block_id, 'story_title');
$item_content = get_block_setting($block_id, 'story_content');
$item_access  = KT_I18N::translate('All');
$xref         = explode(',', get_block_setting($block_id, 'xref'));

$xref ? $count_xref = count($xref) : $count_xref = 0;

$block_order  = KT_DB::prepare(
	"SELECT block_order FROM `##block` WHERE block_id = ?"
)->execute(array($block_id))->fetchOne();

$gedcom_id   = KT_DB::prepare(
	"SELECT gedcom_id FROM `##block` WHERE block_id = ?"
)->execute(array($block_id))->fetchOne();

echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart($this->getName(), $controller->getPageTitle()); ?>

	<form class="cell" name="story" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit">
		<input type="hidden" name="block_id" value="<?php echo $block_id; ?>">
		<div class="grid-x grid-margin-y">
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


			<div class="cell medium-2">
				<label for="gedID"><?php echo KT_I18N::translate('Active family tree'); ?></label>
			</div>
			<div class="cell medium-3 strong">
				<?php echo get_gedcom_setting($gedcom_id, 'title'); ?>
			</div>
			<div class="cell callout info-help medium-7">
				<?php echo KT_I18N::translate('
					To avoid confusion between the tree and the linked individuals it is not possible to edit the active tree.
					If you need to associated this story with a different tree, it must be re-entered as a new story.
				'); ?>
			</div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Linked individuals'); ?>
			</label>
			<div class="cell medium-10">
				<div class="grid-x grid-margin-x">
					<?php for ($x = 0; $x < $count_xref + 2; $x++) {
						if ($xref && $x < $count_xref) { ?>
								<?php $person = KT_Person::getInstance($xref[$x]);
								if ($person) { ?>
									<div class="cell medium-4">
										<?php echo $person->getLifespanName(); ?>
										<input type="hidden" name="xref[]" id="selectedValue-xref<?php echo $x; ?>" value="<?php echo $xref[$x]; ?>">
									</div>
									<div class="cell medium-2">
										<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=remove_indi&amp;indi_ref=<?php echo $xref[$x]; ?>&amp;block_id=<?php echo $block_id; ?>" class="current" onclick="return confirm('<?php echo KT_I18N::translate('Are you sure you want to remove this?'); ?>');">
											<i class="<?php echo $iconStyle; ?> fa-trash-can"></i>
											<?php echo KT_I18N::translate('Remove'); ?>
										</a>
									</div>
									<div class="cell medium-6"></div>
								<?php }
						} else {
							if( $x == $count_xref) { ?>
								<div class="cell linkedIndis"><?php echo KT_I18N::translate('Add more linked individuals'); ?></div>
							<?php } ?>
							<div class="cell medium-4">
								<div class="add_indi">				
									<?php echo autocompleteHtml(
										'xref' . $x,
										'INDI',
										$tree,
										'',
										KT_I18N::translate('Individual name'),
										'xref[]',
										'',
									); ?>	
								</div>
							</div>
							<div class="cell medium-8"></div>
						<?php }
					} ?>
					<div class="cell">
						<div class="cell callout info-help medium-6">
							<?php echo KT_I18N::translate('To add more than two linked individuals, complete the fields below as needed, then click "Save and re-edit"'); ?>
						</div>
					</div>
				</div>
			</div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Story menu order'); ?>
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
				<button class="button hollow" type="button" onclick="window.location='module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config'">
					<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
					<?php echo KT_I18N::translate('Cancel'); ?>
				</button>
			</div>
		</div>
	</form>

<?php echo pageClose();
