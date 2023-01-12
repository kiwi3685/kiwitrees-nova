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

require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';
global $iconStyle;

$gedID  	= KT_Filter::post('gedID') ? KT_Filter::post('gedID') : KT_GED_ID;
$action     = KT_Filter::post('action');

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle($this->getTitle())
	->pageHeader()
	->addExternalJavascript(KT_ICON_PICKER_JS)
	->addExternalJavascript(KT_CKEDITOR_CLASSIC)
	->addInlineJavascript('
		jQuery("#gallery_tabs").tabs();

		ckeditorStandard();

		iconPicker();

	');

if ($action == 'update') {
	// ##module_setting table entries
	set_module_setting($this->getName(), 'HEADER_TITLE', KT_Filter::post('NEW_HEADER_TITLE'));
	set_module_setting($this->getName(), 'HEADER_ICON',  str_replace($iconStyle . ' ', '', KT_Filter::post('NEW_HEADER_ICON')));
	set_module_setting($this->getName(), 'HEADER_DESCRIPTION', KT_Filter::post('NEW_HEADER_DESCRIPTION', KT_REGEX_UNSAFE));

	AddToLog($this->getName() . ' config updated', 'config');
}

$items = KT_DB::prepare("
	SELECT block_id, block_order, gedcom_id, bs1.setting_value AS gallery_title, bs2.setting_value AS gallery_description
	FROM `##block` b 
	JOIN `##block_setting` bs1 USING (block_id) 
	JOIN `##block_setting` bs2 USING (block_id) 
	WHERE module_name = ? 
	AND bs1.setting_name='gallery_title' 
	AND bs2.setting_name='gallery_description' 
	AND IFNULL(gedcom_id, ?) = ?
	ORDER BY block_order
")->execute(array($this->getName(), $gedID, $gedID))->fetchAll();

$min_block_order = KT_DB::prepare(
	"SELECT MIN(block_order) FROM `##block` WHERE module_name=?"
)->execute(array($this->getName()))->fetchOne();

$max_block_order = KT_DB::prepare(
	"SELECT MAX(block_order) FROM `##block` WHERE module_name=?"
)->execute(array($this->getName()))->fetchOne();

echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart($this->getName(), $controller->getPageTitle(), '', '', '/kb/user-guide/gallery/'); ?>

	<fieldset class="cell fieldset">
		<legend class="h5"><?php echo KT_I18N::translate('Site menu and page header'); ?></legend>
		<div class="grid-x">
			<form class="cell" method="post" name="configform" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config">
				<input type="hidden" name="action" value="update">
				<div class="grid-x grid-margin-x grid-margin-y">
					<label class="cell medium-2">
						<?php echo KT_I18N::translate('Menu and page title'); ?>
					</label>
					<div class="cell medium-4">
						<input type="text" name="NEW_HEADER_TITLE" value="<?php echo $this->getMenuTitle(); ?>">
					</div>
					<div class="cell callout info-help medium-6">
						<?php echo KT_I18N::translate('Keep this short, and preferably a single word, to avoid overcrowding the menu bar.'); ?>
					</div>
					<label class="cell medium-2">
						<?php echo KT_I18N::translate('Menu icon'); ?>
					</label>
					<div class="cell medium-4 input-group iconpicker-container">
						<input id="menuIcon" name="NEW_HEADER_ICON" data-placement="bottomRight" class="form-control icp icp-auto iconpicker-input iconpicker-element" value="<?php echo $this->getMenuIcon(); ?>" type="text">
						<span class="input-group-label"><i class="<?php echo $iconStyle . ' ' . $this->getMenuIcon(); ?>"></i></span>
					</div>
					<div class="cell callout info-help medium-6">
						<?php echo KT_I18N::translate('Click in the input field to see a list of icons and click on the one to use. Although displayed in black here, they will be colored to match the theme when displayed on the front pages.'); ?>
					</div>
					<label class="cell medium-2">
						<?php echo KT_I18N::translate('Page description'); ?>
					</label>
					<div class="cell medium-9">
						<textarea name="NEW_HEADER_DESCRIPTION" class="html-edit" placeholder="<?php echo KT_I18N::translate('This text will be displayed at the top of the page.'); ?>"><?php echo $this->getSummaryDescription(); ?></textarea>
					</div>
				</div>

				<?php echo singleButton(); ?>
			</form>
		</div>
	</fieldset>

	<fieldset class="cell fieldset">
		<legend class="h5"><?php echo KT_I18N::translate('Page contents list'); ?></legend>
		<div class="grid-x">

			<?php echo familyTree($gedID); ?>

			<div class="cell medium-offset-1 auto text-right">
				<button class="button primary" type="submit" onclick="location.href='module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_add&amp;gedID=<?php echo $gedID; ?>'">
					<i class="<?php echo $iconStyle; ?> fa-plus"></i>
					<?php echo KT_I18N::translate('Add gallery'); ?>
				</button>
			</div>

			<?php if($items) { ?>
				<table class="cell" id="gallery_edit">
					<thead>
						<tr>
							<th style="width: 16%;">
								<?php echo KT_I18N::translate('Order'); ?>
							</th>
							<th style="width: 20%;">
								<?php echo KT_I18N::translate('Tree'); ?>
							</th>
							<th>
								<?php echo KT_I18N::translate('Title'); ?>
							</th>
							<th style="width: 20%;" colspan="4" class="text-center">
								<?php echo KT_I18N::translate('Actions'); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$trees = KT_Tree::getAll();
						foreach ($items as $gallery) { ?>
							<tr class="gallery_edit_pos">
								<td>
									<?php echo($gallery->block_order); ?>
								</td>
								<td>
									<?php
									if ($gallery->gedcom_id == null) {
										echo KT_I18N::translate('All');
									} else {
										echo $trees[$gallery->gedcom_id]->tree_title_html;
									} ?>
								</td>
								<td>
									<?php echo $gallery->gallery_title; ?>
								</td>
								<td>
									<?php if ($gallery->block_order == $min_block_order) { ?>
										&nbsp;
									<?php } else { ?>
										<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_moveup&amp;block_id=<?php echo $gallery->block_id; ?>">
											<i class="<?php echo $iconStyle; ?> fa-arrow-up"></i>
										</a>
									<?php } ?>
											<?php if ($gallery->block_order == $max_block_order) { ?>
										&nbsp;
									<?php } else { ?>
										<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_movedown&amp;block_id=<?php echo $gallery->block_id; ?>">
											<i class="<?php echo $iconStyle; ?> fa-arrow-down"></i>
										</a>
									<?php } ?>
								</td>
								<td>
									<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;block_id=<?php echo $gallery->block_id; ?>&amp;gedID=<?php echo $gedID; ?>">
										<?php echo KT_I18N::translate('Edit'); ?>
									</a>
								</td>
								<td>
									<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_delete&amp;block_id=<?php echo $gallery->block_id; ?>" onclick="return confirm('<?php echo KT_I18N::translate('Are you sure you want to delete this gallery?'); ?>');">
										<?php echo KT_I18N::translate('Delete'); ?>
									</a>
								</td>
								<td>
									<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;gallery_id=<?php echo $gallery->block_id; ?>&amp;gedID=<?php echo $gedID; ?>" target="_blank">
										<?php echo KT_I18N::translate('View'); ?>
									</a>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			<?php } else { ?>
				<div class="cell callout alert">
					<?php echo KT_I18N::translate('The gallery list is empty.'); ?>
				</div>
			<?php } ?>
		</div>
	</fieldset>

<?php echo pageClose();
