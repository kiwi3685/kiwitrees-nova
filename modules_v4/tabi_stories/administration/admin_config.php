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

require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';
global $iconStyle;

$gedID  	= KT_Filter::post('gedID') ? KT_Filter::post('gedID') : '';
$action     = KT_Filter::post('action');

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle($this->getTitle())
	->pageHeader()
	->addExternalJavascript(KT_ICON_PICKER_JS)
	->addExternalJavascript(KT_CKEDITOR_CLASSIC)
	->addInlineJavascript('
		ckeditorStandard();

		iconPicker();

		tableSort();

	');

if ($action == 'update') {
	set_module_setting($this->getName(), 'HEADER_TITLE', KT_Filter::post('NEW_HEADER_TITLE'));
	set_module_setting($this->getName(), 'HEADER_ICON',  str_replace($iconStyle . ' ', '', KT_Filter::post('NEW_HEADER_ICON')));
	set_module_setting($this->getName(), 'HEADER_DESCRIPTION', KT_Filter::post('NEW_HEADER_DESCRIPTION', KT_REGEX_UNSAFE)); // allow html

	AddToLog($this->getName() . ' config updated', 'config');
}

$items = KT_DB::prepare("
	SELECT block_id, block_order, gedcom_id, xref, bs1.setting_value AS story_title, bs2.setting_value AS story_content
	FROM `##block` b
	JOIN `##block_setting` bs1 USING (block_id)
	JOIN `##block_setting` bs2 USING (block_id)
	WHERE module_name = ?
	AND bs1.setting_name = 'story_title'
	AND bs2.setting_name = 'story_content'
	ORDER BY block_order
")->execute(array($this->getName()))->fetchAll();

$new_xref = KT_Filter::get('xref', KT_REGEX_XREF);

// transfer old xref in ##block to new xref in ##block_setting
foreach ($items as $item) {
	if (null != $item->xref) {
		set_block_setting($item->block_id, 'xref', $item->xref);
		KT_DB::prepare(
			'UPDATE `##block` SET xref = NULL WHERE block_id=?'
		)->execute([$item->block_id]);
	}
}

//Update block order after move, and make the new order take effect immediately
foreach ($items as $this->getName => $item) {
	$order = KT_Filter::post('taborder-' . $item->block_id);
	if ($order) {
		KT_DB::prepare(
			'UPDATE `##block` SET block_order = ? WHERE block_id = ?'
		)->execute([$order, $item->block_id]);
		$item->block_order = $order;
	}
}
uasort($items, function ($x, $y) {
	return (int)($x->block_order > $y->block_order);
});

echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart($this->getName(), $controller->getPageTitle(), '', '', ''); ?>

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
						<textarea name="NEW_HEADER_DESCRIPTION" class="html-edit" placeholder="<?php echo KT_I18N::translate('This text will be displayed at the top of the story list page.'); ?>"><?php echo $this->getSummaryDescription(); ?></textarea>
					</div>
				</div>

				<?php echo singleButton(); ?>
			</form>
		</div>
	</fieldset>

	<fieldset class="cell fieldset">
		<legend class="h5"><?php echo KT_I18N::translate('Story list'); ?></legend>
		<div class="grid-x">
			<div class="cell medium-2">
				<label for="ged"><?php echo KT_I18N::translate('Family tree'); ?></label>
			</div>
			<div class="cell medium-4">
				<form method="post" action="#" name="tree">
					<?php echo select_edit_control('gedID', KT_Tree::getIdList(), KT_I18N::translate('All'), $gedID, ' onchange="tree.submit();"'); ?>
				</form>
			</div>
			<form class="cell" method="post" name="configform" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config">
				<?php if($items) { ?>
					<table class="cell" id="reorderTable">
						<thead>
							<tr>
								<th colspan=2>
									<?php echo KT_I18N::translate('Order'); ?>
								</th>
								<th>
									<?php echo KT_I18N::translate('ID'); ?>
								</th>
								<th>
									<?php echo KT_I18N::translate('Tree'); ?>
								</th>
								<th class="lang">
									<?php echo KT_I18N::translate('Language'); ?>
								</th>
								<th>
									<?php echo KT_I18N::translate('Story title'); ?>
								</th>
								<th>
									<?php echo KT_I18N::translate('Individual'); ?>
								</th>
								<?php $new_xref ? $columns = 3 : $columns = 2; ?>
								<th colspan="<?php echo $columns; ?>" class="text-center">
									<?php echo KT_I18N::translate('Actions'); ?>
								</th>
							</tr>
						</thead>
					<?php if ($items) { ?>
						<tbody>
							<?php
							$order      = 1;
							$trees      = KT_Tree::getAll();
							$countItems = 0;

							foreach ($items as $item) {
								if ($item->gedcom_id == $gedID || $gedID == '') {
									$xref       = explode(',', get_block_setting($item->block_id, 'xref'));
									$count_xref = count($xref); ?>

									<tr class="sortme">
										<td>
											<i class="<?php echo $iconStyle; ?> fa-bars"></i>
										</td>
										<td>
											<input type="text" value="<?php echo($item->block_order); ?>" name="taborder-<?php echo($item->block_id); ?>">
										</td>
										<td>
											<?php echo($item->block_id); ?>
										</td>
										<td>
											<?php
											if ($item->gedcom_id == null) {
												echo KT_I18N::translate('All');
											} else {
												echo $trees[$item->gedcom_id]->tree_title_html;
											} ?>
										</td>
										<td>
											<?php
											$languages     = get_block_setting($item->block_id, 'languages');
											$languageSet   = explode(',', $languages);
											$languagePrint = '';
											$printLang     = [];
											if ($languageSet) {
												foreach ($languageSet as $code) {
													foreach (KT_I18N::used_languages() as $lang => $name) {
														if ($lang == $code) {
															$printLang[] = $name;
														}
													}
												}
												$languagePrint = implode(', ', $printLang);
											}
											echo ($languagePrint ? $languagePrint : KT_I18N::translate('None set')); ?>
										</td>
										<td>
											<?php echo $item->story_title; ?>
										</td>
										<td class="small">

											<?php foreach($xref as $indiRef) {
												$sql    = "SELECT * FROM `##individuals` WHERE i_file LIKE " . $item->gedcom_id . " AND i_id = '" . $indiRef . "'";
												$row    = KT_DB::prepare($sql)->execute()->fetchOneRow();
												$person = $person = KT_Person::getInstance(array(
													'xref'   =>$row->i_id,
													'ged_id' =>$row->i_file,
													'type'   =>'INDI',
													'gedrec' =>$row->i_gedcom)
												);

												if ($person) { ?>
													<a href="<?php echo $person->getHtmlUrl(); ?>#stories" class="current">
														<?php echo $person->getFullName(); ?>
													</a>
												<?php } else { ?>
													<span class="error"><?php echo $indiRef; ?></span>
												<?php }

											} ?>

										</td>
										<td >
											<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;block_id=<?php echo $item->block_id; ?>&amp;gedID=<?php echo $gedID; ?>">
												<?php echo KT_I18N::translate('Edit'); ?>
											</a>
										</td>
										<td>
											<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_delete&amp;block_id=<?php echo $item->block_id; ?>" onclick="return confirm('<?php echo KT_I18N::translate('Are you sure you want to delete this story?'); ?>');">
												<?php echo KT_I18N::translate('Delete'); ?>
											</a>
										</td>

										<?php if ($new_xref) { ?>
											<td class="text-center">
												<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=story_link&amp;block_id=<?php echo $item->block_id; ?>&amp;xref=<?php echo $new_xref; ?>" onclick="return confirm('<?php echo KT_I18N::translate('Are you sure you want to link to this story?'); ?>');">
													<?php echo KT_I18N::translate('Link'); ?>
												</a>
											</td>

										<?php } ?>
									</tr>
									<?php
									++$order;
									++$countItems;
								} else {

								}
 							} ?>
						</tbody>
						<?php if($countItems == 0 && !is_null($gedID)) {
							$new_xref ? $columns = 8 : $columns = 7; ?>
							<tfoot><tr><td colspan="<?php echo $columns; ?>" >
								<div class="cell callout warning">
									<?php echo KT_I18N::translate('The stories list for this tree is empty.'); ?>
								</div>
							<tfoot><tr><td>
						<?php }
					} else {
						$new_xref ? $columns = 8 : $columns = 7; ?>
						<tfoot><tr><td colspan="<?php echo $columns; ?>" >
							<div class="cell callout warning">
								<?php echo KT_I18N::translate('The stories list is empty.'); ?>
							</div>
						<tfoot><tr><td>
					<?php } ?>
				</table>
				<?php } else { ?>
					<div class="cell callout warning">
						<?php echo KT_I18N::translate('The item list is empty.'); ?>
					</div>
				<?php } ?>

				<div class="grid-x">
					<?php echo singleButton('Save new order'); ?>
					<?php echo singleButton('Add another item', '', $gedID, $this->getName()); ?>
				</div>

			</form>
		</div>
	</fieldset>

<?php echo pageClose();
