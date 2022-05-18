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

define('KT_SCRIPT_NAME', 'admin_trees_addunlinked.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';
global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Add unlinked records'))
	->pageHeader()
	->addInlineJavascript('
		function showAdd(addtype) {
			switch(addtype){
				case "indi":
					var addUnlinked = "addchild()";
				break;
				case "note":
					var addUnlinked = "addnewnote()";
				break;
				case "sour":
					var addUnlinked = "admin_edit_interface.php?action=addnewsource&pid=";
				break;
				case "repo":
					var addUnlinked = "admin_edit_interface.php?action=addnewrepository&pid=";
				break;
				case "obje":
					var addUnlinked = "addmedia.php?action=showmediaform&linktoid=new&format=simple";
				break;
				default:
				break;
			}
//			jQuery("#unlinked").load(addUnlinked, function() {
//				jQuery("#unlinked").hide().slideDown("slow");
//			});
		}

//		function showAddClose() {
//			jQuery("#unlinked").hide();
//		};

	');
?>

<div id="unlinked-records-page" class="cell">
	<div class="grid-x grid-padding-y">
		<div class="cell">
			<h4 class="inline"><?php echo KT_I18N::translate('Add unlinked records'); ?></h4>
		</div>
		<form method="post" action="#" name="tree">
			<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM, ' onchange="tree.submit();"'); ?>
		</form>
		<div class="cell grid-x">
			<div class="cell medium-2">
				<button class="button expanded" data-toggle="indi">
					<?php echo /* I18N: An individual that is not linked to any other record */ KT_I18N::translate('Create a new individual'); ?>
				</button>
				<button class="button expanded" data-toggle="note">
					<?php echo /* I18N: An note that is not linked to any other record */ KT_I18N::translate('Create a new shared note'); ?>
				</button>
				<button class="button expanded" onclick="showAdd('sour');">
					<?php echo /* I18N: A source that is not linked to any other record */ KT_I18N::translate('Create a new source'); ?>
				</button>
				<button class="button expanded" onclick="showAdd('repo');">
					<?php echo /* I18N: A repository that is not linked to any other repository */ KT_I18N::translate('Create a new repository'); ?>
				</button>
			</div>
			<div class="cell medium-9 medium-offset-1">
				<div class="hidden" id="indi" data-toggler="shown"><?php echo addchild(); ?></div>
				<div class="hidden" id="note" data-toggler="shown"><?php echo addnewnote(); ?></div>
			</div>
		</div>
	</div>
</div>

<?php

function addchild() {
	global $iconStyle; ?>

	<div class="grid-x" id="edit_interface-page">
		<h4><?php echo KT_I18N::translate('Add an unlinked person'); ?></h4>
		<?php echo print_indi_form('addchildaction', '', '', '', 'CHIL', ''); ?>
		<button class="button secondary" type="button" data-toggle="indi">
			<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
			<?php echo KT_I18N::translate('Cancel'); ?>
		</button>
	</div>
	<?php
}

function addnewnote() {
	global $iconStyle; ?>

	<div class="grid-x" id="edit_interface-page">
		<h4><?php echo KT_I18N::translate('Create a new shared note'); ?></h4>
		<form method="post" action="admin_trees_addunlinked.php" onsubmit="return check_form(this);">
			<input type="hidden" name="action" value="addnoteaction">
			<input type="hidden" name="noteid" value="newnote">
			<div class="grid-x" id="add_facts">
				<div class="cell helpcontent">
					<?php echo KT_I18N::translate('Shared Notes are free-form text and will appear in the Fact Details section of the page. Each shared note can be linked to more than one person, family, source, or event.'); ?>
				</div>
				<div class="cell large-3">
					<label for="NOTE"><?php echo KT_I18N::translate('Shared note'); ?></label>
				</div>
				<div class="cell large-9">
					<textarea name="NOTE" id="NOTE"></textarea>
					<?php echo print_specialchar_link('NOTE'); ?>
				</div>
				<?php echo no_update_chan(); ?>
			</div>
			<button class="button" type="submit">
				<i class="<?php echo $iconStyle; ?> fa-save"></i>
				<?php echo KT_I18N::translate('Save'); ?>
			</button>
			<button class="button secondary" type="button" data-toggle="note">
				<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
				<?php echo KT_I18N::translate('Cancel'); ?>
			</button>
		</form>
	</div>
	<?php
}
