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

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle($this->getTitle())
	->pageHeader()
;

if (KT_Filter::postBool('save')) {
	$ALBUM_GROUPS  = KT_Filter::post('NEW_ALBUM_GROUPS');
	$ALBUM_TITLES  = KT_Filter::postArray('NEW_ALBUM_TITLES');
	$ALBUM_OPTIONS = KT_Filter::postArray('NEW_ALBUM_OPTIONS');
	if (isset($ALBUM_GROUPS)) {
		set_module_setting($this->getName(), 'ALBUM_GROUPS', $ALBUM_GROUPS);
	}
	if (!empty($ALBUM_TITLES)) {
		set_module_setting($this->getName(), 'ALBUM_TITLES', serialize($ALBUM_TITLES));
	}
	if (!empty($ALBUM_OPTIONS)) {
		set_module_setting($this->getName(), 'ALBUM_OPTIONS', serialize($ALBUM_OPTIONS));
	}

	AddToLog($this->getTitle() . ' set to new values', 'config');
}

$SHOW_FIND     = KT_Filter::post('show');
$HIDE_FIND     = KT_Filter::post('hide');
$ALBUM_GROUPS  = get_module_setting($this->getName(), 'ALBUM_GROUPS');
$ALBUM_TITLES  = get_module_setting($this->getName(), 'ALBUM_TITLES') ? unserialize(get_module_setting($this->getName(), 'ALBUM_TITLES')) : '';
$ALBUM_OPTIONS = get_module_setting($this->getName(), 'ALBUM_OPTIONS') ? unserialize(get_module_setting($this->getName(), 'ALBUM_OPTIONS')) : '';

if (!isset($ALBUM_GROUPS)) {
	$ALBUM_GROUPS = 4;
}

if (empty($ALBUM_TITLES)) {
	$ALBUM_TITLES = [
		KT_I18N::translate('Photos'),
		KT_I18N::translate('Documents'),
		KT_I18N::translate('Census'),
		KT_I18N::translate('Other'),
	];
}

$default_groups = [
	KT_I18N::translate('Other'),
	KT_I18N::translate('Other'),
	KT_I18N::translate('Documents'),
	KT_I18N::translate('Documents'),
	KT_I18N::translate('Other'),
	KT_I18N::translate('Documents'),
	KT_I18N::translate('Census'),
	KT_I18N::translate('Documents'),
	KT_I18N::translate('Documents'),
	KT_I18N::translate('Documents'),
	KT_I18N::translate('Census'),
	KT_I18N::translate('Census'),
	KT_I18N::translate('Documents'),
	KT_I18N::translate('Other'),
	KT_I18N::translate('Photos'),
	KT_I18N::translate('Photos'),
	KT_I18N::translate('Photos'),
	KT_I18N::translate('Other'),
];

if (empty($ALBUM_OPTIONS)) {
	$ALBUM_OPTIONS = array_combine(array_keys(KT_Gedcom_Tag::getFileFormTypes()), $default_groups);
}

echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart(
	$this->getName(), 
	$controller->getPageTitle(), 
	'', 
	KT_I18N::translate('Configure display of grouped media items using GEDCOM media tag TYPE.'), 
	'faqs/modules/album/'
);

	// check for empty groups
	$error = 0;
	foreach ($ALBUM_TITLES as $value) {
		if (!in_array($value, $ALBUM_OPTIONS)) {
			$error ++;
		}
	}
	if ($error > 0) { ?>
		<div class="cell callout alert">
			<?php echo KT_I18N::translate('You can not have any empty group.'); ?>"")
		</div>
	<?php } ?>

	<form class="cell" method="post" name="album_form" action="<?php echo $this->getConfigLink(); ?>">
		<input type="hidden" name="save" value="1">
		<div class="grid-x grid-margin-x">
			<label class="cell medium-2" for="NEW_ALBUM_GROUPS">
				<?php echo KT_I18N::translate('Number of groups'); ?>
					
				</label>
			<div class="cell medium-1">
				<?php echo select_edit_control(
					'NEW_ALBUM_GROUPS',
					[
						0 => KT_I18N::number(0),
						1 => KT_I18N::number(1),
						2 => KT_I18N::number(2),
						3 => KT_I18N::number(3),
						4 => KT_I18N::number(4),
						5 => KT_I18N::number(5),
						6 => KT_I18N::number(6),
						7 => KT_I18N::number(7),
					],
					null,
					$ALBUM_GROUPS
				); ?>
			</div>
			<fieldset class="cell fieldset">
				<legend><?php echo KT_I18N::translate('Match groups to types'); ?></legend>
				<div class="grid-x table-scroll">
					<table class="cell" id="album-config">
						<thead>
							<tr>
								<th colspan="2" rowspan="2"></th>
								<th colspan="<?php echo $ALBUM_GROUPS; ?>">
									<?php echo KT_I18N::translate('Groups (These must always be English titles)'); ?>
								</th>
							</tr>
							<tr>
								<?php for ($i = 0; $i < $ALBUM_GROUPS; $i++) { ?>
									<th class="albumGroup">
										<input type="input" name="NEW_ALBUM_TITLES[]" value="<?php echo ($ALBUM_TITLES[$i] ?? ''); ?>">
									</th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th rowspan="19">
									<span class="rotate">
										<?php echo KT_I18N::translate('Types'); ?>
									</span>
								</th>
							</tr>
							<?php  foreach ($ALBUM_OPTIONS as $key => $value) {
								$translated_type = KT_Gedcom_Tag::getFileFormTypeValue($key); ?>
								<tr>
									<td>
										<?php echo $translated_type; ?>
									</td>
									<?php for ($i = 0; $i < $ALBUM_GROUPS; $i++) {
										if (isset($ALBUM_TITLES[$i]) && $value == $ALBUM_TITLES[$i]) { ?>
											<td>
												<input type="radio" name="NEW_ALBUM_OPTIONS[<?php echo $key ; ?>]" value="<?php echo ($ALBUM_TITLES[$i] ?? ''); ?>" checked="checked">
											</td>
										<?php } else { ?>
											<td>
												<input type="radio" name="NEW_ALBUM_OPTIONS[<?php echo $key ; ?>]" value="<?php echo ($ALBUM_TITLES[$i] ?? ''); ?>">
											</td>
										<?php }
									} ?>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</fieldset>
					
			<?php echo singleButton(); ?>

		</div>
	</form>
	<div class="cell">
		<button 
			class="button primary reset" 
			type="submit" 
			onclick="if (confirm('<?php echo KT_I18N::translate('The settings will be reset to default (for all trees). Are you sure you want to do this?'); ?>')) window.location.href='module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_reset';"
		>
			<i class="<?php echo $iconStyle; ?> fa-rotate"></i>
			<?php echo KT_I18N::translate('Reset'); ?>
		</button>


	</div>
	<form class="cell" method="post" name="find_show" action="<?php echo $this->getConfigLink(); ?>">
		<div id="album_find">
			<input type="hidden" name="show">
			<a class="current" href="javascript:document.find_show.submit()"><?php echo KT_I18N::translate('Show media objects with no TYPE'); ?></a>
			<?php if (isset($SHOW_FIND) && !isset($HIDE_FIND)) { ?>
				<div id="show_list"><?php echo $this->find_no_type(); ?></div>
				<input class="button secondary" type="submit" name="hide" value="<?php echo KT_I18N::translate('close'); ?>">
			<?php } ?>
		</div>
	</form>

<?php echo pageClose();
