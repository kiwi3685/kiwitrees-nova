<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Add unlinked records'))
	->pageHeader();

global $iconStyle;

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
				<button class="button expanded" onclick="showAdd('indi');">
					<?php echo /* I18N: An individual that is not linked to any other record */ KT_I18N::translate('Create a new individual'); ?>
				</button>
				<button class="button expanded" onclick="showAdd('note');">
					<?php echo /* I18N: An note that is not linked to any other record */ KT_I18N::translate('Create a new note'); ?>
				</button>
				<button class="button expanded" onclick="showAdd('sour');">
					<?php echo /* I18N: A source that is not linked to any other record */ KT_I18N::translate('Create a new source'); ?>
				</button>
				<button class="button expanded" onclick="showAdd('repo');">
					<?php echo /* I18N: A repository that is not linked to any other repository */ KT_I18N::translate('Create a new repository'); ?>
				</button>
				<button class="button expanded" onclick="showAdd('obje');">
					<?php echo /* I18N: A media object that is not linked to any other record */ KT_I18N::translate('Create a new media object'); ?>
				</button>
			</div>
			<div class="cell medium-9 medium-offset-1 is-hidden" id="showhide">
				<button class="button small float-right" onclick="showAddClose();">
					<i class="<?php echo $iconStyle; ?> fa-times"></i>
					<?php echo KT_I18N::translate('Close'); ?>
				</button>
				<div id="unlinked"></div>
			</div>
		</div>
	</div>
</div>

<script>
	function showAdd(addtype) {
		switch(addtype){
			case "indi":
				var addUnlinked = 'edit_interface.php?action=addchild&gender=&famid='; // addnewchild
			break;
			case "note":
				var addUnlinked = 'edit_interface.php?action=addnewnote&noteid='; // addnewnote
			break;
			case "sour":
				var addUnlinked = 'edit_interface.php?action=addnewsource&pid='; // addnewsource
			break;
			case "repo":
				var addUnlinked = 'edit_interface.php?action=addnewrepository&pid='; // addnewrepository
			break;
			case "obje":
				var addUnlinked = 'addmedia.php?action=showmediaform&linktoid=new&format=simple';
			break;
			default:
			break;
		}

		jQuery('#unlinked').load(addUnlinked, function() {
			jQuery('#unlinked').hide().slideDown('slow');
		});
		jQuery("#showhide").removeClass("is-hidden");
	}

	function showAddClose() {
		jQuery("#showhide").addClass("is-hidden");
		jQuery('#unlinked').hide();
	};

</script>
