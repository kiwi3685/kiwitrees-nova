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
require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';
$UNLINKED = 'no';

global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Add unlinked records'))
	->pageHeader()
	->addInlineJavascript('autocomplete();');

$gedID 	= KT_Filter::post('gedID') ? KT_Filter::post('gedID') : KT_GED_ID;
$tree 	= KT_Tree::getNameFromId($gedID);

echo relatedPages($trees, KT_SCRIPT_NAME);

echo pageStart('unlinked-records', $controller->getPageTitle()); ?>

	<div class="cell medium-4">
		<form method="post" action="#" name="tree">
			<?php echo select_ged_control('gedID', KT_Tree::getIdList(), null, $gedID, ' onchange="tree.submit();"'); ?>
		</form>
	</div>
	<div class="cell medium-8"></div>

	<div class="cell">
		<div class="grid-x grid-margin-x grid-margin-y">
			<div class="cell medium-2">
				<div class="button-group stacked">
				    <button class="button" data-toggle="indi">
				        <?php echo /* I18N: An individual that is not linked to any other record */
				            KT_I18N::translate('Create a new individual');
				        ?>
				    </button>
				    <button class="button" data-toggle="note">
				        <?php echo /* I18N: An note that is not linked to any other record */
				            KT_I18N::translate('Create a new shared note');
				        ?>
				    </button>
				    <button class="button" data-toggle="sour">
				        <?php echo /* I18N: A source that is not linked to any other record */
				            KT_I18N::translate('Create a new source');
				        ?>
				    </button>
				    <button class="button" data-toggle="repo">
				        <?php echo /* I18N: A repository that is not linked to any other repository */
				            KT_I18N::translate('Create a new repository');
				        ?>
				    </button>
				</div>
			</div>
		    <div class="cell medium-10 dropPanes">
				<div class="dropdown-pane"
					id="indi"
					data-dropdown
					data-parent-class="dropPanes"
					data-position="right"
					data-alignment="top"
					data-parent-class="dropPanes"
					data-h-offset="10"
					data-auto-focus="true"
				>
					<?php echo addchild(); ?>
				</div>
				<div class="dropdown-pane"
					id="note"
					data-dropdown data-parent-class="dropPanes"
					data-position="right"
					data-alignment="top"
					data-parent-class="dropPanes"
					data-h-offset="10"
					data-v-offset="-75"
					data-auto-focus="true"
				>
					<?php echo addnewnote(); ?>
				</div>
				<div class="dropdown-pane"
					id="sour"
					data-dropdown
					data-parent-class="dropPanes"
					data-position="right"
					data-alignment="top"
					data-parent-class="dropPanes"
					data-h-offset="10"
					data-v-offset="-150"
					data-auto-focus="true"
				>
					<?php echo addnewsource(); ?>
				</div>
				<div class="dropdown-pane"
					id="repo"
					data-dropdown
					data-parent-class="dropPanes"
					data-position="right"
					data-alignment="top"
					data-parent-class="dropPanes"
					data-h-offset="10"
					data-v-offset="-225"
					data-auto-focus="true"
				>
					<?php echo addnewrepository(); ?>
				</div>
		    </div>
		</div>
	</div>

<?php echo pageClose();

//functions for each option
function addchild() {
	global $iconStyle, $UNLINKED;
	$UNLINKED = 'indi';

	echo pageStart('edit_interface_indi', KT_I18N::translate('Create a new individual'));

		echo print_indi_form('addchildaction', '', '', '', 'CHIL', '');?>
	</div>

	<?php echo pageClose();

}

function addnewnote() {
	global $iconStyle, $UNLINKED;

	echo pageStart('edit_interface_note', KT_I18N::translate('Create a new shared note')); ?>

		<form class="cell" method="post" action="admin_trees_addunlinked.php" onsubmit="return check_form(this);">
			<input type="hidden" name="action" value="addnoteaction">
			<input type="hidden" name="noteid" value="newnote">
			<div class="grid-x" id="add_facts">
				<div class="cell callout info-help ">
					<?php echo KT_I18N::translate('Shared Notes are free-form text and will appear in the Fact Details section of the page. Each shared note can be linked to more than one person, family, source, or event.'); ?>
				</div>
				<div class="cell medium-3">
					<label for="NOTE"><?php echo KT_I18N::translate('Shared note'); ?></label>
				</div>
				<div class="cell medium-7">
					<textarea name="NOTE" id="NOTE"></textarea>
				</div>
				<div class="cell medium-2 popup_links">
					<?php echo print_specialchar_link('NOTE'); ?>
				</div>
				<?php echo no_update_chan(); ?>
			</div>
			<?php echo submitButtons('data-toggle="note"'); ?>
		</form>
	</div>

	<?php echo pageClose();

}

function addnewsource() {
	global $iconStyle, $ADVANCED_NAME_FACTS, $NO_UPDATE_CHAN; ?>

	<script>
		function check_form(frm) {
			if (frm.selectedValue-TITLE.value=="") {
				alert('<?php echo KT_I18N::translate('You must provide a source title'); ?>');
				frm.autocompleteInput-TITL.focus();
				return false;
			}
			return true;
		}
	</script>

	<?php echo pageStart('edit_interface_sour', KT_I18N::translate('Create a new source')); ?>

		<form class="cell" method="post" action="<?php echo KT_SCRIPT_NAME; ?>" onsubmit="return check_form(this);">
			<input type="hidden" name="action" value="addsourceaction">
			<input type="hidden" name="pid" value="newsour">
			<div class="grid-x" id="add_facts">
				<div class="cell medium-3" id="TITLE_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('TITL'); ?>
					</label>
				</div>
				<div class="cell medium-7">
					<?php echo autocompleteHtml(
						'TITL',
						'SOUR_TITL',
						'',
						'',
						'',
						'TITL',
						'',
						'',
						''
					); ?>
				</div>
				<div class="cell medium-2 popup_links">
					<?php echo print_specialchar_link('TITL'); ?>
				</div>
				<div class="cell medium-3" id="ABBR_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('ABBR'); ?>
					</label>
				</div>
				<div class="cell medium-7">
					<input type="text" name="ABBR" id="ABBR" value="" maxlength="255">
				</div>
				<div class="cell medium-2 popup_links">
					<?php echo print_specialchar_link('ABBR'); ?>
				</div>
				<?php if (strstr($ADVANCED_NAME_FACTS, "_HEB") !== false) { ?>
					<div class="cell medium-3" id="_HEB_factdiv">
						<label>
							<?php echo KT_Gedcom_Tag::getLabel('_HEB'); ?>
						</label>
					</div>
					<div class="cell medium-9">
						<input type="text" name="_HEB" id="_HEB" value="">
						<?php echo print_specialchar_link('_HEB'); ?>
					</div>
				<?php } ?>
				<?php if (strstr($ADVANCED_NAME_FACTS, "ROMN") !== false) { ?>
					<div class="cell medium-3" id="ROMN_factdiv">
						<label>
							<?php echo KT_Gedcom_Tag::getLabel('_HEB'); ?>
						</label>
					</div>
					<div class="cell medium-7">
						<input type="text" name="ROMN" id="ROMN" value="">
					</div>
					<div class="cell medium-2 popup_links">
						<?php echo print_specialchar_link('ROMN'); ?>
					</div>
				<?php } ?>
				<div class="cell medium-3" id="AUTH_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('AUTH'); ?>
					</label>
				</div>
				<div class="cell medium-7">
					<input type="text" name="AUTH" id="AUTH" value="" maxlength="255">
				</div>
				<div class="cell medium-2 popup_links">
					<?php echo print_specialchar_link('AUTH'); ?>
				</div>
				<div  class="cell medium-3" id="PUBL_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('PUBL'); ?>
					</label>
				</div>
				<div class="cell medium-7">
					<textarea name="PUBL" id="PUBL" rows="5"></textarea>
				</div>
				<div class="cell medium-2 popup_links">
					<?php echo print_specialchar_link('PUBL'); ?>
				</div>
				<div class="cell medium-3" id="REPO_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('REPO'); ?>
					</label>
				</div>
				<div class="cell medium-7">
					<?php echo autocompleteHtml(
						'REPO',
						'REPO',
						'',
						'',
						'',
						'REPO',
						'',
						'',
						''
					); ?>
				</div>
				<div class="cell medium-2 popup_links">
					<?php echo print_specialchar_link('REPO'); ?>
				</div>
				<div class="cell medium-3" id="CALN_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('CALN'); ?>
					</label>
				</div>
				<div class="cell medium-7">
					<input type="text" name="CALN" id="CALN" value="">
				</div>
				<div class="cell medium-2 popup_links">
					<?php echo print_specialchar_link('CALN'); ?>
				</div>
				<div class="cell medium-3" id="WWW_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('WWW'); ?>
					</label>
				</div>
				<div class="cell medium-7">
					<input type="text" name="WWW" id="WWW" value="">
				</div>
				<div class="cell medium-2 popup_links">
					<?php echo print_specialchar_link('WWW'); ?>
				</div>
				<?php echo no_update_chan(); ?>
			</div>
			<?php echo submitButtons('data-toggle="sour"'); ?>
		</form>
	</div>
	<?php echo pageClose();

}

function addnewrepository() {
	global $iconStyle, $ADVANCED_NAME_FACTS, $NO_UPDATE_CHAN; ?>

	<script>
		function check_form(frm) {
			if (frm.selectedValue-NAME.value=="") {
				alert('<?php echo KT_I18N::translate('You must provide a repository name'); ?>');
				frm.autocompleteInput-Name.focus();
				return false;
			}
			return true;
		}
	</script>

	<?php echo pageStart('edit_interface_sour', KT_I18N::translate('Create a new source')); ?>

		<form class="cell" method="post" action="<?php echo KT_SCRIPT_NAME; ?>" onsubmit="return check_form(this);">
			<input type="hidden" name="action" value="addsourceaction">
			<input type="hidden" name="pid" value="newsour">
			<div id="add_facts">
				<div id="TITLE_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('TITL'); ?>
					</label>
					<div class="input">
						<input type="text" data-autocomplete-type="SOUR_TITL" name="TITL" id="TITL" value="">
						<div class="input-group-addon">
							<?php echo print_specialchar_link('TITL'); ?>
						</div>
					</div>
				</div>
				<div id="ABBR_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('ABBR'); ?>
					</label>
					<div class="input">
						<input type="text" name="ABBR" id="ABBR" value="" maxlength="255">
						<div class="input-group-addon">
							<?php echo print_specialchar_link('ABBR'); ?>
						</div>
					</div>
				</div>
				<div id="_HEB_factdiv">
					<?php if (strstr($ADVANCED_NAME_FACTS, "_HEB") !== false) { ?>
						<label>
							<?php echo KT_Gedcom_Tag::getLabel('_HEB'); ?>
						</label>
						<div class="input">
							<input type="text" name="_HEB" id="_HEB" value="">
							<?php echo print_specialchar_link('_HEB'); ?>
						</div>
					<?php } ?>
					</div>
					<?php if (strstr($ADVANCED_NAME_FACTS, "ROMN") !== false) { ?>
						<div id="ROMN_factdiv">
							<label>
								<?php echo KT_Gedcom_Tag::getLabel('_HEB'); ?>
							</label>
							<div class="input">
								<input type="text" name="ROMN" id="ROMN" value="">
								<?php echo print_specialchar_link('ROMN'); ?>
							</div>
						</div>
					<?php } ?>
				<div id="AUTH_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('AUTH'); ?>
					</label>
					<div class="input">
						<input type="text" name="AUTH" id="AUTH" value="" maxlength="255">
						<?php echo print_specialchar_link('AUTH'); ?>
					</div>
				</div>
				<div id="PUBL_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('PUBL'); ?>
					</label>
					<div class="input">
						<textarea name="PUBL" id="PUBL" rows="5"></textarea>
						<?php echo print_specialchar_link('PUBL'); ?>
					</div>
				</div>
				<div id="REPO_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('REPO'); ?>
					</label>
					<div class="input">
						<input type="text" data-autocomplete-type="REPO" name="REPO" id="REPO" value="">
						<?php echo print_findrepository_link('REPO') .
						' ' .
						print_addnewrepository_link('REPO'); ?>
					</div>
				</div>
				<div id="CALN_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('CALN'); ?>
					</label>
					<div class="input">
						<input type="text" name="CALN" id="CALN" value="">
						<?php echo print_specialchar_link('CALN'); ?>
					</div>
				</div>
				<div id="WWW_factdiv">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('WWW'); ?>
					</label>
					<div class="input">
						<input type="text" name="WWW" id="WWW" value="">
						<?php echo print_specialchar_link('WWW'); ?>
					</div>
				</div>
				<?php if (KT_USER_IS_ADMIN) { ?>
					<div class="last_change">
						<label>
							<?php echo KT_Gedcom_Tag::getLabel('CHAN'); ?>
						</label>
						<div class="input">
							<?php if ($NO_UPDATE_CHAN) { ?>
								<input type="checkbox" checked="checked" name="preserve_last_changed">
							<?php } else { ?>
								<input type="checkbox" name="preserve_last_changed">
							<?php }
							echo KT_I18N::translate('Do not update the “last change” record'), help_link('no_update_CHAN'); ?>
						</div>
					</div>
				<?php }?>
			</div>
			<?php echo submitButtons('data-toggle="sour"'); ?>
		</form>
	</div>
	<?php echo pageClose();

}
