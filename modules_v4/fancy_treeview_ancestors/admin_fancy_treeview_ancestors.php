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

// Actions from the configuration page
require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';
global $iconStyle;

$ftv = new KT_Controller_FancyTreeView();

$controller = new KT_Controller_Page;
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle($this->getTitle())
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL);


if (KT_Filter::postBool('save')) {
	$surname = KT_Filter::post('NEW_FTV_SURNAME');
	$root_id = strtoupper(KT_Filter::post('NEW_FTV_ROOTID', KT_REGEX_XREF));
	if($surname || $root_id) {
		if($surname) {
			$soundex_std = KT_Filter::postBool('soundex_std');
			$soundex_dm = KT_Filter::postBool('soundex_dm');

			$indis = $ftv->indisArray($surname, $soundex_std, $soundex_dm);
			usort($indis, array('KT_Person', 'CompareBirtDate'));

			if (isset($indis) && count($indis) > 0) {
				$pid = $indis[0]->getXref();
			}
			else {
				$ftv->addMessage($controller, 'error', KT_I18N::translate('Error: The surname you entered doesn’t exist in this tree.'));
			}
		}

		if($root_id) {
			if ($ftv->getSurname($root_id)) {
				// check if this person has parents
				$person = $ftv->getPerson($root_id);
				if(!$person->getChildFamilies()) {
					$ftv->addMessage($ftv, 'error', KT_I18N::translate('Error: The root person you are trying to add has no parents. It is not possible to set this individual as root person.'));
				}
				else {
					$pid = $root_id;
				}
			}
			else {
				$ftv->addMessage($controller, 'error', KT_I18N::translate('Error: An individual with ID %s doesn’t exist in this tree.', $root_id));
			}
		}

		if(isset($pid)) {
			$FTV_SETTINGS = unserialize(get_module_setting($this->getName(), 'FTV_SETTINGS'));

			if(!empty($FTV_SETTINGS)) {
				$i = 0;
				foreach ($FTV_SETTINGS as $FTV_ITEM) {
					if ($FTV_ITEM['TREE'] == KT_Filter::postInteger('NEW_FTV_TREE')) {
						if($FTV_ITEM['PID'] == $pid) {
							$error = true;
							break;
						}
						else {
							$i++;
						}
					}
				}
				$count = $i + 1;
			}
			else {
				$count = 1;
			}
			if(isset($error) && $error == true) {
				if($surname) {
					$ftv->addMessage($controller, 'error', KT_I18N::translate('Error: The root person belonging to this surname already exists'));
				}
				if($root_id) {
					$ftv->addMessage($controller, 'error', KT_I18N::translate('Error: The root person you are trying to add already exists'));
				}
			} else {
				$NEW_FTV_SETTINGS = $FTV_SETTINGS;
				$NEW_FTV_SETTINGS[] = array(
					'TREE' 			=> KT_Filter::postInteger('NEW_FTV_TREE'),
					'SURNAME' 		=> $ftv->getSurname($pid),
					'DISPLAY_NAME'	=> $ftv->getSurname($pid),
					'PID'			=> $pid,
					'ACCESS_LEVEL'	=> '2', // default access level = show to visitors
					'SORT'			=> $count
				);
				set_module_setting($this->getName(), 'FTV_SETTINGS', serialize($NEW_FTV_SETTINGS));
				AddToLog($this->getTitle() . ' config updated', 'config');
			}
		}
	}

	$new_pids = KT_Filter::postArray('NEW_FTV_PID'); $new_display_name = KT_Filter::postArray('NEW_FTV_DISPLAY_NAME'); $new_access_level = KT_Filter::postArray('NEW_FTV_ACCESS_LEVEL'); $new_sort = KT_Filter::postArray('NEW_FTV_SORT');

	if($new_pids || $new_display_name || $new_access_level || $new_sort) {
		// retrieve the array again from the database because it could have been changed due to an add action.
		$FTV_SETTINGS = unserialize(get_module_setting($this->getName(), 'FTV_SETTINGS'));
		foreach ($new_pids as $key => $new_pid) {
			if(!empty($new_pid)) {
				$new_pid = strtoupper($new_pid); // make sure the PID is entered in the format I200 and not i200.
				if($FTV_SETTINGS[$key]['PID'] != $new_pid) {
					if (!$ftv->searchArray($FTV_SETTINGS, 'PID', $new_pid)) {
						if($ftv->getSurname($new_pid)) {
							// check if this person has parents
							$person = $ftv->getPerson($new_pid);
							if(!$person->getChildFamilies()) {
								$ftv->addMessage($controller, 'error', KT_I18N::translate('Error: The root person you are trying to add has no partner and/or children. It is not possible to set this individual as root person.'));
							}
							else {
								$FTV_SETTINGS[$key]['SURNAME'] = $ftv->getSurname($new_pid);
								$FTV_SETTINGS[$key]['DISPLAY_NAME'] = $ftv->getSurname($new_pid);
								$FTV_SETTINGS[$key]['PID'] = $new_pid;
							}
						}
						else {
							$ftv->addMessage($controller, 'error', KT_I18N::translate('Error: An individual with ID %s doesn’t exist in this tree.', $new_pid));
						}
					}
				}
				else {
					$FTV_SETTINGS[$key]['DISPLAY_NAME'] = $new_display_name[$key];
				}
			}
		}

		foreach ($new_access_level as $key => $new_access_level) {
			$FTV_SETTINGS[$key]['ACCESS_LEVEL'] = $new_access_level;
		}

		foreach ($new_sort as $key => $new_sort) {
			$FTV_SETTINGS[$key]['SORT'] = $new_sort;
		}

		$NEW_FTV_SETTINGS = $ftv->sortArray($FTV_SETTINGS, 'SORT');
		set_module_setting($this->getName(), 'FTV_SETTINGS', serialize($NEW_FTV_SETTINGS));
	}

	// retrieve the current options from the database
	$FTV_OPTIONS = unserialize(get_module_setting($this->getName(), 'FTV_OPTIONS'));
	$key = KT_Filter::postInteger('NEW_FTV_TREE');

	// check if options are not empty and if the options for the tree are already set. If not add them to the array.
	if ($FTV_OPTIONS) {
		// check if options are changed for the specific key (tree_id)
		if(!array_key_exists($key, $FTV_OPTIONS) || $FTV_OPTIONS[$key] != KT_Filter::postArray('NEW_FTV_TREE')) {
			$NEW_FTV_OPTIONS = $FTV_OPTIONS;
			$NEW_FTV_OPTIONS[KT_Filter::postInteger('NEW_FTV_TREE')] = KT_Filter::postArray('NEW_FTV_OPTIONS');
		}
	} else {
		$NEW_FTV_OPTIONS[KT_Filter::postInteger('NEW_FTV_TREE')] = KT_Filter::postArray('NEW_FTV_OPTIONS');
	}

	if(isset($NEW_FTV_OPTIONS)) {
		set_module_setting($this->getName(), 'FTV_OPTIONS', serialize($NEW_FTV_OPTIONS));
		AddToLog($this->getTitle() . ' config updated', 'config');
	}

	// the cache has to be recreated because the image options could have been changed
	$ftv->emptyCache($this->getName());
}

// get module settings (options are coming from function options)
$FTV_SETTINGS = get_module_setting($this->getName(), 'FTV_SETTINGS') ? unserialize(get_module_setting($this->getName(), 'FTV_SETTINGS')) : '';
$controller
	->addExternalJavascript(KT_FANCY_TREEVIEW_JS_URL)
	->addInlineJavascript('autocomplete();')
	->addInlineJavascript('
		var OptionsNumBlocks	= ' . $ftv->options($this->getName(), 'numblocks') . ';
		var TextOk				= "' . KT_I18N::translate('ok') . '";
		var TextCancel			= "' . KT_I18N::translate('cancel') . '";
		', KT_Controller_Base::JS_PRIORITY_HIGH);


// Admin page content
echo relatedPages($moduleTools, $this->getConfigLink());

echo pageStart($this->getName(), $controller->getPageTitle()); ?>
	
	<form method="post" id="ftv-options-form" name="configform" action="<?php echo $this->getConfigLink(); ?>">
		<input type="hidden" name="save" value="1">

		<fieldset class="cell fieldset">
			<legend class="h5"><?php echo KT_I18N::translate('Basic settings'); ?></legend>
			<div class="grid-x">
				<div class="cell medium-2">
					<label for="NEW_FTV_TREE"><?php echo KT_I18N::translate('Family tree'); ?></label>
				</div>
				<div class="cell medium-4">
					<select name="NEW_FTV_TREE" id="NEW_FTV_TREE" class="ftvp_tree">
						<?php foreach (KT_Tree::getAll() as $tree) { ?>
								<option
									value="<?php echo $tree->tree_id; ?>"
									data-ged="<?php echo $tree->tree_name; ?>"
									<?php echo $tree->tree_id == KT_GED_ID ? 'selected="selected"' : ''; ?>
								>
									<?php echo $tree->tree_title; ?>
								</option>
						<?php } ?>
					</select>
				</div>
				<div class="cell medium-6"></div>
				<div class="cell medium-2">
					<label><?php echo KT_I18N::translate('Add a root person'); ?></label>
				</div>
				<div class="cell medium-4">
					<input data-autocomplete-type="INDI" type="text" name="NEW_FTV_ROOTID" id="NEW_FTV_ROOTID" value="" size="5" maxlength="20">
				</div>
				<div class="cell medium-6"></div>
				<?php if (!empty($FTV_SETTINGS) && $ftv->searchArray($FTV_SETTINGS, 'TREE', KT_GED_ID)):
					global $KT_IMAGES, $KT_TREE; ?>
					<table id="fancy_treeview_descendants-table" class="modules_table ui-sortable">
						<tr>
							<th><?php echo KT_I18N::translate('Surname'); ?></th>
							<th><?php echo KT_I18N::translate('Root person'); ?></th>
							<th><?php echo KT_I18N::translate('Menu'); ?></th>
							<th><?php echo KT_I18N::translate('Edit Root person'); ?></th>
							<th><?php echo KT_I18N::translate('Access level'); ?></th>
							<th><?php echo KT_I18N::translate('Delete'); ?></th>
						</tr>
						<?php foreach ($FTV_SETTINGS as $key=>$FTV_ITEM):
							if($FTV_ITEM['TREE'] == KT_GED_ID):
								if (KT_Person::getInstance($FTV_ITEM['PID'])): ?>
									<tr class="sortme">
										<td><input type="hidden" name="NEW_FTV_SORT[<?php echo $key; ?>]" id="NEW_FTV_SORT[<?php echo $key; ?>]" value="<?php echo $FTV_ITEM['SORT']; ?>" >
											<span class="showname"><?php echo $FTV_ITEM['DISPLAY_NAME']; ?></span>
											<span class="editname"><input type="text" name="NEW_FTV_DISPLAY_NAME[<?php echo $key; ?>]" id="NEW_FTV_DISPLAY_NAME[<?php echo $key; ?>]" value="<?php echo $FTV_ITEM['DISPLAY_NAME']; ?>"></span>
										</td>
										<td><?php echo KT_Person::getInstance($FTV_ITEM['PID'])->getFullName(); ?> (<?php echo $FTV_ITEM['PID']; ?>)</td>
										<td>
											<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo $KT_TREE->tree_name; ?>&amp;rootid='.($FTV_ITEM['PID']); ?>" target="_blank" rel="noopener noreferrer">
												<?php echo KT_I18N::translate('Ancestors of %s', KT_Person::getInstance($FTV_ITEM['PID'])->getFullName()); ?>
											</a>
										</td>
										<td class="wrap">
											<input data-autocomplete-type="INDI" type="text" name="NEW_FTV_PID[<?php echo $key; ?>]" id="NEW_FTV_PID[<?php echo $key; ?>]" value="<?php echo $FTV_ITEM['PID']; ?>" size="5" maxlength="20">
										</td>
										<td> <?php edit_field_access_level('NEW_FTV_ACCESS_LEVEL[<?php echo $key; ?>]', $FTV_ITEM['ACCESS_LEVEL']); ?></td>
										<td>
											<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_delete&amp;key=<?php echo $key; ?>">
												<i class="<?php echo $iconStyle; ?> fa-trash-can"></i>
											</a>
										</td>
									</tr>
								<?php else: ?>
									<tr>
										<td class="error">
											<input type="hidden" name="NEW_FTV_PID[<?php echo $key; ?>]" value="<?php echo $FTV_ITEM['PID']; ?>">
											<input type="hidden" name="NEW_FTV_ACCESS_LEVEL[<?php echo $key; ?>]" value="<?php echo KT_PRIV_HIDE; ?>">
											<input type="hidden" name="NEW_FTV_DISPLAY_NAME[<?php echo $key; ?>]" value="<?php echo $FTV_ITEM['DISPLAY_NAME']; ?>">
											<?php echo $FTV_ITEM['DISPLAY_NAME']; ?></td>
										<td colspan="4" class="error">
											<?php echo KT_I18N::translate('The person with root id %s doesn’t exist anymore in this tree', $FTV_ITEM['PID']); ?>
										</td>
										<td>
											<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_delete&amp;key=<?php echo $key; ?>">
												<img src="<?php echo $KT_IMAGES['remove']; ?>" alt="icon-delete">
											</a>
										</td>
									</tr>
								<?php endif;
							endif;
						endforeach; ?>
					</table>
				<?php endif; 

				echo singleButton(); ?>

			</div>
		</fieldset>

		<fieldset class="cell fieldset">
			<legend class="h5"><?php echo KT_I18N::translate('General Options'); ?></legend>
			<div class="grid-x grid-margin-x">

				<div class="cell medium-2">
					<label><?php echo KT_I18N::translate('Number of generation blocks to show'); ?></label>
				</div>
				<div class="cell medium-2">
					<?php echo select_edit_control('NEW_FTV_OPTIONS[NUMBLOCKS]', array(KT_I18N::translate('All'), '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'), null, $ftv->options($this->getName(), 'numblocks')); ?>
				</div>
				<div class="cell callout info-help medium-8">
					<?php echo /* I18N: Help text for the “Number of generation blocks to show” configuration setting */ KT_I18N::translate('This option is useful for large trees. Set the number of generation blocks to a low level to avoid slow page loading. Below the last generation block a button will appear to add the next set of generation blocks. The new blocks will be added to the blocks already loaded. Click on a “follow” link in the last visible generation block to load the next set of generation blocks.'); ?>
				</div>
				<hr class="cell">
				<div class="cell medium-2">				
					<label><?php echo KT_I18N::translate('Check relationship between partners'); ?></label>
				</div>
				<div class="cell medium-2">
					<?php echo edit_field_yes_no('NEW_FTV_OPTIONS[CHECK_RELATIONSHIP]', $ftv->options($this->getName(), 'check_relationship')); ?>
				</div>
				<div class="cell callout info-help medium-8">
					<?php echo /* I18N: Help text for the “Check relationship between partners” configuration setting */ KT_I18N::translate('With this option turned on, the script checks if a married couple has the same ancestors. If a relationship between the partners is found a text will appear between brackets after the spouses’ name to indicate the blood relationship. <br><b>Note:</b> this option can be time and/or memory consuming on large trees. It can cause very slow page loading or an ’execution time out error’ on your server. If you notice such a behavior reduce the number of generation blocks to load at once or don’t use it in combination with the option to show singles (see the previous options). If you still experience any problems, don’t use this option at all.');
					?>
				</div>
				<hr class="cell">
				<div class="cell medium-2">
					<label><?php echo KT_I18N::translate('Show single persons'); ?></label>
				</div>
				<div class="cell medium-2">					
					<?php echo edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_SINGLES]', $ftv->options($this->getName(), 'show_singles')); ?>
				</div>
				<div class="cell callout info-help medium-8">
					<?php echo /* I18N: Help text for the “Show single persons” configuration setting */ KT_I18N::translate('Turn this option on to show people who have no partner or children. With this option turned on every child of a family will be shown in a detailed way in the next generation block.'); ?>
				</div>
				<hr class="cell">
				<?php if ($ftv->getCountryList()) { ?>
					<div id="ftv_places" class="cell medium-2">
						<label><?php echo KT_I18N::translate('Show places'); ?></label>
					</div>
					<div class="cell medium-2">	
						<?php echo edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_PLACES]', $ftv->options($this->getName(), 'show_places')); ?>
					</div>
					<div class="cell medium-8"></div>	
					<hr class="cell">
					<div id="gedcom_places" class="cell medium-2">
						<label><?php echo KT_I18N::translate('Use default family tree settings to abbreviate place names'); ?></label>
					</div>
					<div class="cell medium-2">	
						<?php echo edit_field_yes_no('NEW_FTV_OPTIONS[USE_GEDCOM_PLACES]', $ftv->options($this->getName(), 'use_gedcom_places')); ?>
					</div>
					<div class="cell callout info-help medium-8">
						<?php echo /* I18N: Help text for the “Use default Gedcom settings to abbreviate place names” configuration setting */ KT_I18N::translate('If you have ticked the “Show places” option you can choose to use the default family tree settings to abbreviate placenames. If you don’t set this option full place names will be shown.'); ?>
					</div>
					<hr class="cell">
					<div id="country_list" class="cell medium-2">
						<label><?php echo KT_I18N::translate('Select your country'); ?></label>
					</div>
					<div class="cell medium-2">
						 <?php echo select_edit_control('NEW_FTV_OPTIONS[COUNTRY]', $ftv->getCountryList(), '', $ftv->options($this->getName(), 'country')); ?>
					</div>
					<div class="cell callout info-help medium-8">
						<?php echo /* I18N: Help text for the “Select your country” configuration setting */ KT_I18N::translate('If you have ticked the “Show places” option but NOT the option to abbreviate placenames, you can set your own country here. Full places will be listed on the Descendancy pages, but when a place includes the name of your own country, this name will be left out. If you don’t select a country then all countries will be shown, including your own.'); ?>
					</div>
					<hr class="cell">
				<?php  } ?>
				
				<div class="cell medium-2">
					<label><?php echo KT_I18N::translate('Show occupations'); ?></label>
					</div>
				<div class="cell medium-2">
					<?php echo edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_OCCU]', $ftv->options($this->getName(), 'show_occu')); ?>
				</div>
				<div class="cell medium-8"></div>
				<div class="cell medium-2">
					<label><?php echo KT_I18N::translate('Show sosa numbering'); ?></label>
					</div>
				<div class="cell medium-2">
					<?php echo edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_SOSA]', $ftv->options($this->getName(), 'show_sosa')); ?>
				</div>
				<div class="cell callout info-help medium-8">
					<?php echo /* I18N: Help text for the “Show sosa numbering” configuration setting */ KT_I18N::translate('if selected, the Sosa-Stradonitz or Ahnentafel number for each ancestor will be displayed after their name.'); ?>
				</div>
				<div class="cell medium-2">
					<label><?php echo KT_I18N::translate('Show children'); ?></label>
				</div>
				<div class="cell medium-2">
					<?php echo edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_CHIL]', $ftv->options($this->getName(), 'show_chil')); ?>
				</div>
				<div class="cell callout info-help medium-8">
					<?php echo /* I18N: Help text for the “Show children” configuration setting */ KT_I18N::translate('If selected, a list of the children of each ancestral family will be displayed after each couple.'); ?>
				</div>
				<div id="show_imgs" class="cell medium-2">
					<label><?php echo KT_I18N::translate('Show images'); ?></label>
				</div>
				<div class="cell medium-2">
					<?php echo edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_IMGS]', $ftv->options($this->getName(), 'show_imgs')); ?>
				</div>
				<div class="cell medium-8"></div>
				<div id="images">
				<div id="resize_thumbs" class="cell medium-2">
					<label><?php echo KT_I18N::translate('Resize thumbnails'); ?></label>
				</div>
				<div class="cell medium-8"></div>
				<div class="cell medium-2">
					<?php echo edit_field_yes_no('NEW_FTV_OPTIONS[RESIZE_THUMBS]', $ftv->options($this->getName(), 'resize_thumbs')); ?>
				</div>
				<div class="cell callout info-help medium-8">
					<?php echo /* I18N: Help text for the “Resize thumbnails */ KT_I18N::translate('Choose “yes” to resize the default thumbnails for the Descendency pages. You can set a custom size in percentage or in pixels.<dl><dt>Size in percentage</dt><dd>The original thumbnails will be proportionally resized. This may result in a different width and height for each thumbnail.</dd><dt>Size in pixels</dt><dd>The longest side of the image will be resized to match the size in pixels. The other side will be resized proportionally.</dd><dt>Square thumbs</dt><dd>When you use a square thumbnail, all thumbnails will have the same width and height and the thumbnails will be cropped.</dd></dl>If you choose “no” the default thumbnails will be used with the formats you have set on the family tree configuration page.'); ?>
				</div>
				<div id="thumb_size" class="cell medium-2">
					<label><?php echo KT_I18N::translate('Thumbnail size'); ?></label>
				</div>
				<div class="cell medium-2">
					<input type="text" size="3" id="NEW_FTV_OPTIONS[THUMB_SIZE]" name="NEW_FTV_OPTIONS[THUMB_SIZE]" value="<?php echo $ftv->options($this->getName(), 'thumb_size'); ?>" >
					&nbsp;
					 <?php echo select_edit_control('NEW_FTV_OPTIONS[THUMB_RESIZE_FORMAT]', array('1' => KT_I18N::translate('percent'), '2' => KT_I18N::translate('pixels')), null, $ftv->options($this->getName(), 'thumb_resize_format')); ?>
				</div>
				<div class="cell medium-8"></div>
				<div id="square_thumbs" class="cell medium-2">
					<label><?php echo KT_I18N::translate('Use square thumbnails'); ?></label>
				</div>
				<div class="cell medium-2">
						 <?php echo edit_field_yes_no('NEW_FTV_OPTIONS[USE_SQUARE_THUMBS]', $ftv->options($this->getName(), 'use_square_thumbs')); ?>
					</div>
				</div>
			</div>
			<hr>
			<div class="buttons">
				<button class="btn btn-primary save" type="submit">
					<i class="<?php echo $iconStyle; ?> fa-save"></i>
					<?php echo KT_I18N::translate('Save'); ?>
				</button>
				<button class="btn btn-primary ftvp_reset" type="reset">
					<i class="<?php echo $iconStyle; ?> fa-sync"></i>
					<?php echo KT_I18N::translate('Reset'); ?>
				</button>
				<div id="dialog-confirm" title="<?php echo KT_I18N::translate('Reset'); ?>" style="display:none">
					<p>
						<?php echo KT_I18N::translate('The settings will be reset to default (for all trees). Are you sure you want to do this?'); ?>
					</p>
				</div>
			</div>
		</div>
	</form>


<?php echo pageClose();

