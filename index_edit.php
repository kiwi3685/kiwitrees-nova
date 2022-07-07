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

define('KT_SCRIPT_NAME', 'index_edit.php');
require './includes/session.php';

$controller = new KT_Controller_Page();

// $gedcom_id should be set
$gedcom_id = safe_REQUEST($_REQUEST, 'gedcom_id');

// Only an admin can edit the "default" page
// Only managers can edit the "home page"
if (
	$gedcom_id < 0 && !KT_USER_IS_ADMIN ||
	$gedcom_id > 0 && !userGedcomAdmin(KT_USER_ID, $gedcom_id)
) {
	$controller->pageHeader();
	$controller->addInlineJavascript('window.location.reload();');
	exit;
}

$action = KT_Filter::get('action');

if (KT_Filter::post('default') === '1') {
	$defaults	= get_gedcom_blocks(-1);
	$main  		= $defaults['main'];
	$right		= $defaults['side'];
} else {
	if (isset($_REQUEST['main'])) {
		$main = $_REQUEST['main'];
	} else {
		$main = array();
	}
	if (isset($_REQUEST['right'])) {
		$right = $_REQUEST['right'];
	} else {
		$right = array();
	}
}

// Define all the icons we're going to use
$IconUarrow			= 'fa fa-angle-up fa-2x';
$IconDarrow			= 'fa fa-angle-down fa-2x';
if($TEXT_DIRECTION	== 'ltr') {
	$IconRarrow		= 'fa fa-angle-right fa-2x';
	$IconLarrow		= 'fa fa-angle-left fa-2x';
	$IconRDarrow	= 'fa fa-angle-double-right fa-2x';
	$IconLDarrow	= 'fa fa-angle-double-left fa-2x';
} else {
	$IconRarrow		= 'fa fa-angle-left fa-2x';
	$IconLarrow		= 'fa fa-angle-right fa-2x';
	$IconRDarrow	= 'fa fa-angle-double-left fa-2x';
	$IconLDarrow	= 'fa fa-angle-double-right fa-2x';
}

$all_blocks = array();
foreach (KT_Module::getActiveBlocks(KT_GED_ID, KT_PRIV_HIDE) as $blockname => $block) {
	if ($gedcom_id && $block->isGedcomBlock()) {
		$all_blocks[$blockname] = $block;
	}
}

$blocks = get_gedcom_blocks($gedcom_id);

if ($action == 'update') {
	Zend_Session::writeClose();
	foreach (array('main', 'side') as $location) {
		if ($location == 'main') {
			$new_blocks = $main;
		} else {
			$new_blocks = $right;
		}
		foreach ($new_blocks as $order=>$block_name) {
			if (is_numeric($block_name)) {
				// existing block
				KT_DB::prepare("UPDATE `##block` SET block_order=? WHERE block_id=?")->execute(array($order, $block_name));
				// existing block moved location
				KT_DB::prepare("UPDATE `##block` SET location=? WHERE block_id=?")->execute(array($location, $block_name));
			} else {
				// new block
				KT_DB::prepare("INSERT INTO `##block` (gedcom_id, location, block_order, module_name) VALUES (?, ?, ?, ?)")
				->execute(array($gedcom_id, $location, $order, $block_name));
			}
		}
		// deleted blocks
		foreach ($blocks[$location] as $block_id=>$block_name) {
			if (!in_array($block_id, $main) && !in_array($block_id, $right)) {
				KT_DB::prepare("DELETE FROM `##block_setting` WHERE block_id=?")->execute(array($block_id));
				KT_DB::prepare("DELETE FROM `##block`         WHERE block_id=?")->execute(array($block_id));
			}
		}
	}

	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'index.php?ged=' . $KT_TREE->tree_name_url);

	return;
}

$controller
	->pageHeader()
	->setPageTitle(KT_I18N::translate('Change the Home page blocks'))
	->addInlineJavascript('
	/**
	* Move Up Block Javascript function
	*
	* This function moves the selected option up in the given select list
	* @param String section_name the name of the select to move the options
	*/
	function move_up_block(section_name) {
		section_select = document.getElementById(section_name);
		if (section_select) {
			if (section_select.selectedIndex <= 0) return false;
			index = section_select.selectedIndex;
			temp = new Option(section_select.options[index-1].text, section_select.options[index-1].value);
			section_select.options[index-1] = new Option(section_select.options[index].text, section_select.options[index].value);
			section_select.options[index] = temp;
			section_select.selectedIndex = index-1;
		}
	}

	/**
	* Move Down Block Javascript function
	*
	* This function moves the selected option down in the given select list
	* @param String section_name the name of the select to move the options
	*/
	function move_down_block(section_name) {
		section_select = document.getElementById(section_name);
		if (section_select) {
			if (section_select.selectedIndex < 0) return false;
			if (section_select.selectedIndex >= section_select.length-1) return false;
			index = section_select.selectedIndex;
			temp = new Option(section_select.options[index+1].text, section_select.options[index+1].value);
			section_select.options[index+1] = new Option(section_select.options[index].text, section_select.options[index].value);
			section_select.options[index] = temp;
			section_select.selectedIndex = index+1;
		}
	}

	/**
	* Move Block from one column to the other Javascript function
	*
	* This function moves the selected option down in the given select list
	* @author KosherJava
	* @param String from_column the name of the select to move the option from
	* @param String to_column the name of the select to remove the option to
	*/
	function move_left_right_block(from_column, to_column) {
		to_select = document.getElementById(to_column);
		from_select = document.getElementById(from_column);
		instruct = document.getElementById("instructions");
		if ((to_select) && (from_select)) {
			add_option = from_select.options[from_select.selectedIndex];
			if (to_column != "available_select") {
				to_select.options[to_select.length] = new Option(add_option.text, add_option.value);
			}
			if (from_column != "available_select") {
				from_select.options[from_select.selectedIndex] = null; //remove from list
			}
		}
	}
	/**
	* Select Options Javascript function
	*
	* This function selects all the options in the multiple select lists
	*/
	function select_options() {
		section_select = document.getElementById("main_select");
		if (section_select) {
			for (i=0; i<section_select.length; i++) {
				section_select.options[i].selected=true;
			}
		}
		section_select = document.getElementById("right_select");
		if (section_select) {
			for (i=0; i<section_select.length; i++) {
				section_select.options[i].selected=true;
			}
		}
		return true;
	}
	/**
	* Show Block Description Javascript function
	*
	* This function shows a description for the selected option
	* @param String list_name the name of the select to get the option from
	*/
	function show_description(list_name) {
		list_select = document.getElementById(list_name);
		instruct = document.getElementById("instructions");
		if (block_descr[list_select.options[list_select.selectedIndex].value] && instruct) {
			instruct.innerHTML = block_descr[list_select.options[list_select.selectedIndex].value];
		} else {
			instruct.innerHTML = block_descr["advice1"];
		}
		list1 = document.getElementById("main_select");
		list2 = document.getElementById("available_select");
		list3 = document.getElementById("right_select");
		if (list_name=="main_select") {
			list2.selectedIndex = -1;
			list3.selectedIndex = -1;
		}
		if (list_name=="available_select") {
			list1.selectedIndex = -1;
			list3.selectedIndex = -1;
		}
		if (list_name=="right_select") {
			list1.selectedIndex = -1;
			list2.selectedIndex = -1;
		}
	}
	var block_descr = new Array();
	');


	// Load Block Description array for use by javascript
	foreach ($all_blocks as $block_name => $block) {
		$controller->addInlineJavascript(
			'block_descr["' . $block_name . '"] = "' . addslashes($block->getDescription()) . '";'
		);
	}
	$controller->addInlineJavascript(
		'block_descr["advice1"] = "' . KT_I18N::translate('Highlight a block name and then click on one of the arrow icons to move that highlighted block in the indicated direction.') . '";'
	);

	$accessColor = array(
		2   => 'information',
		1	=> 'success',
		0	=> 'warning',
		-1	=> 'alert'
	);

?>
<div class="grid-x">
	<div class="cell large-6 large-offset-3">
		<h3 class="text-center"><?php echo $controller->getPageTitle(); ?></h3>
		<div id="accessTip" class="callout secondary">
			<?php echo KT_I18N::translate('
				Colour of items under "All available blocks" indicate current access setting, controlling who can see these blocks.<br>
				<span class="strong alert">Red = No-one</span> | <span class="strong warning">Orange= Managers / admin only</span> | <span class="success">Green = All members</span> | <span class="strong information">Blue = Visitors</span><br>
				These settings can be changed at <a href="admin_module_blocks.php" target="_blank">at this link <i class="' . $iconStyle . ' fa-link"></i></a>'); ?>
		</div>

		<form name="config_setup" method="post" action="index_edit.php?action=update" onsubmit="select_options();" >
			<input type="hidden" name="gedcom_id" value="<?php echo $gedcom_id; ?>">
			<table id="change_blocks">
				<!-- NOTE: Row 1: Column legends -->
				<thead>
					<tr>
						<th class="text-center" colspan="2">
							<?php echo KT_I18N::translate('Large Blocks'); ?>
						</th>
						<th class="text-center" colspan="3">
							<?php echo KT_I18N::translate('All available Blocks'); ?>
						</th>
						<th class="text-center" colspan="2">
							<?php echo KT_I18N::translate('Small Blocks'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<!-- NOTE: Row 2 column 1: Up/Down buttons for left (main) block list -->
						<td>
							<a onclick="move_up_block('main_select');" title="<?php echo KT_I18N::translate('Move up'); ?>"class="<?php echo $IconUarrow; ?>"></a>
							<br>
							<a onclick="move_down_block('main_select');" title="<?php echo KT_I18N::translate('Move down'); ?>"class="<?php echo $IconDarrow; ?>"></a>
						</td>
						<!-- NOTE: Row 2 column 2: Left (Main) block list -->
						<td>
							<select multiple="multiple" id="main_select" name="main[]" size="10" onchange="show_description('main_select');">
								<?php foreach ($blocks['main'] as $block_id => $block_name) { ?>
									<option value="<?php echo $block_id; ?>"><?php echo $all_blocks[$block_name]->getTitle() . ' (id ' . $block_id . ')'; ?></option>
								<?php } ?>
							</select>
						</td>
						<!-- NOTE: Row 2 column 3: Left/Right buttons for left (main) block list -->
						<td>
							<a onclick="move_left_right_block('main_select', 'right_select');" title="<?php echo KT_I18N::translate('Move Right'); ?>"class="<?php echo $IconRDarrow; ?>"></a>
							<br>
							<a onclick="move_left_right_block('main_select', 'available_select');" title="<?php echo KT_I18N::translate('Remove'); ?>"class="<?php echo $IconRarrow; ?>"></a>
							<br>
							<a onclick="move_left_right_block('available_select', 'main_select');" title="<?php echo KT_I18N::translate('Add'); ?>"class="<?php echo $IconLarrow; ?>"></a>
						</td>
						<!-- NOTE: Row 2 column 4: Middle (Available) block list -->
						<td>
							<select class="strong" id="available_select" name="available[]" size="10" onchange="show_description('available_select');">
								<?php
								 foreach ($all_blocks as $blockname => $block) {
									 $access_level = KT_DB::prepare(
										 "SELECT access_level FROM `##module_privacy` WHERE gedcom_id=? AND module_name=? AND component='block'"
									 )->execute(array($gedcom_id, $blockname))->fetchOne();
									 if ($access_level === null) {
										 $access_level = $block->defaultAccessLevel();
									 } ?>
									<option class="<?php echo $accessColor[$access_level]; ?>" value="<?php echo $blockname; ?>"><?php echo $all_blocks[$blockname]->getTitle(); ?></option>
								<?php } ?>
							</select>
						</td>
						<!-- NOTE: Row 2 column 5: Left/Right buttons for right block list -->
						<td>
							<a onclick="move_left_right_block('right_select', 'main_select');" title="<?php echo KT_I18N::translate('Move Left'); ?>"class="<?php echo $IconLDarrow; ?>"></a>
							<br>
							<a onclick="move_left_right_block('right_select', 'available_select');" title="<?php echo KT_I18N::translate('Remove'); ?>"class="<?php echo $IconLarrow; ?>"></a>
							<br>
							<a onclick="move_left_right_block('available_select', 'right_select');" title="<?php echo KT_I18N::translate('Add'); ?>"class="<?php echo $IconRarrow; ?>"></a>
						</td>
						<!-- NOTE: Row 2 column 6: Right block list -->
						<td>
							<select multiple="multiple" id="right_select" name="right[]" size="10" onchange="show_description('right_select');">
								<?php foreach ($blocks['side'] as $block_id=>$block_name) { ?>
									<option value="<?php echo $block_id; ?>"><?php echo $all_blocks[$block_name]->getTitle() . ' (id ' . $block_id . ')'; ?></option>
								<?php } ?>
							</select>
						</td>
						<!-- NOTE: Row 2 column 7: Up/Down buttons for right block list -->
						<td>
							<a onclick="move_up_block('right_select');" title="<?php echo KT_I18N::translate('Move up'); ?>"class="<?php echo $IconUarrow; ?>"></a>
							<br>
							<a onclick="move_down_block('right_select');" title="<?php echo KT_I18N::translate('Move down'); ?>"class="<?php echo $IconDarrow; ?>"></a>
						</td>
					</tr>
				</tbody>
			</table>
			<div id="instructions" class="callout alert">
				<p>
					<?php echo KT_I18N::translate('Highlight a block name and then click on one of the arrow icons to move that highlighted block in the indicated direction.'); ?>
				</p><p>
					<?php echo KT_I18N::translate('Note that small and large blocks can be displayed either to screen left or right, depending on the theme used. The above settings do not therefore specify left ot right.'); ?>
				</p>
			</div>
			<div class="cell text-center">
				<input id="default" type="checkbox" name="default" value="1">
				<label for="default" class="h6"><?php echo KT_I18N::translate('Restore the default block layout'); ?></label>
			</div>
			<button class="button" type="submit">
				<i class="fas fa-save"></i>
				<?php echo KT_I18N::translate('Save'); ?>
			</button>
			<button class="button secondary" type="button"  onclick="window.location.href='index.php'">
				<i class="fas fa-xmark"></i>
				<?php echo KT_I18N::translate('Cancel'); ?>
			</button>
		</form>
	</div>
</div>
