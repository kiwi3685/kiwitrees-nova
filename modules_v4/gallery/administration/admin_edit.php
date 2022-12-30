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

$block_id = KT_Filter::getInteger('block_id', KT_Filter::postInteger('block_id'));;
$gedID    = KT_Filter::get('gedID', KT_Filter::post('gedID'));
$save     = KT_Filter::post('save', '');

$controller = new KT_Controller_Page();
$controller
	->pageHeader()
	->addExternalJavascript(KT_CKEDITOR_CLASSIC)
	->addInlineJavascript('ckeditorStandard();');

if ($save) {
	$block_id 		= KT_Filter::postInteger('block_id');
	$header      	= KT_Filter::post('header',  KT_REGEX_UNSAFE); // allow html
	$faqbody     	= KT_Filter::post('faqbody', KT_REGEX_UNSAFE); // allow html
	$gedID 			= KT_Filter::post('gedID');
	$block_order 	= (int)KT_Filter::post('block_order');
	$languages 		= array();

	KT_DB::prepare(
		"UPDATE `##block` SET gedcom_id = NULLIF(?, ''), block_order = ? WHERE block_id = ?"
	)->execute(array(
	$gedID,
		$block_order,
		$block_id
	));

	set_block_setting($block_id, 'header', $header);
	set_block_setting($block_id, 'faqbody', $faqbody); 

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
				window.location='module.php?mod=faq&mod_action=admin_edit&block_id=' . $block_id . '&gedID=' . $gedID;
			</script><?php
		break;
		case 2:
			// save & close
			?><script>
				window.location='module.php?mod=faq&mod_action=admin_config';
			</script><?php
		break;
	}
}

$controller->setPageTitle(KT_I18N::translate('Edit faq item'));

$header   = get_block_setting($block_id, 'header');
$faqbody  = get_block_setting($block_id, 'faqbody');

$block_order = KT_DB::prepare(
	"SELECT block_order FROM `##block` WHERE block_id = ?"
)->execute(array($block_id))->fetchOne();

$gedcom_id = KT_DB::prepare(
	"SELECT gedcom_id FROM `##block` WHERE block_id = ?"
)->execute(array($block_id))->fetchOne();

echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart('faq_details', $controller->getPageTitle()); ?>

	<form class="cell" name="faq" method="post" action="module.php?mod=faq&amp;mod_action=admin_edit">
		<input type="hidden" name="block_id" value="<?php echo $block_id; ?>">

		<div class="grid-x grid-margin-y">

			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Question'); ?>				
			</label>
			<div class="cell medium-10">
				<input type="text" name="header" value="<?php echo htmlspecialchars($header); ?>">
			</div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Answer'); ?>
			</label>
			<div class="cell medium-10">
				<textarea name="faqbody" class="html-edit"><?php echo htmlspecialchars($faqbody); ?></textarea>
			</div>
			<label class="cell medium-2">
				<?php echo KT_I18N::translate('Faq menu order'); ?>
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
				<button class="button hollow" type="button" onclick="window.location='module.php?mod=faq&amp;mod_action=admin_config'">
					<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
					<?php echo KT_I18N::translate('Cancel'); ?>
				</button>
			</div>
		</div>
	</form>

<?php echo pageClose();
