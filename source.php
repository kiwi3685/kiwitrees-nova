<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'source.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_print_lists.php';

$controller = new KT_Controller_Source();
$controller->pageHeader(); ?>

<div id="source-details-page" class="grid-x grid-padding-x">
	<div class="cell large-10 large-offset-1">
		<?php if ($controller->record && $controller->record->canDisplayDetails()) {
			if ($controller->record->isMarkedDeleted()) {
				if (KT_USER_CAN_ACCEPT) { ?>
					<div class="callout alert">
						<?php echo /* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
							'This source has been deleted.  You should review the deletion and then %1$s or %2$s it.',
							'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\'' . $controller->record->getXref() . '\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'accept') . '</a>',
							'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\'' . $controller->record->getXref() . '\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'reject') . '</a>'
						); ?>
					</div>
					<?php echo helpDropdown('pending_changes');
				} elseif (KT_USER_CAN_EDIT) { ?>
					<div class="callout alert">
						<?php echo KT_I18N::translate('This source has been deleted. The deletion will need to be reviewed by a moderator.'); ?>
					</div>
					<?php echo helpDropdown('pending_changes');
				}
			} elseif (find_updated_record($controller->record->getXref(), KT_GED_ID) !== null) {
				if (KT_USER_CAN_ACCEPT) { ?>
					<div class="callout alert">
						<?php echo /* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
							'This source has been edited.  You should review the changes and then %1$s or %2$s them.',
							'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\'' . $controller->record->getXref() . '\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'accept') . '</a>',
							'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\'' . $controller->record->getXref() . '\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'reject') . '</a>'
						); ?>
					</div>
				<?php } elseif (KT_USER_CAN_EDIT) { ?>
					<div class="callout alert">
						<?php echo KT_I18N::translate('This source has been edited. The changes need to be reviewed by a moderator.'); ?>
					</div>
					<?php echo helpDropdown('pending_changes');
				}
			}
		} else {
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
			$controller->pageHeader(); ?>
			<div class="callout alert">
				<?php echo KT_I18N::translate('This source does not exist or you do not have permission to view it.'); ?>
			</div>
			<?php exit;
		} ?>
	</div>

	<?php
	$linkToID = $controller->record->getXref(); // Tell addmedia.php what to link to

	$controller->addInlineJavascript('
		function show_gedcom_record() {
			var recwin=window.open("gedrecord.php?pid=' . $controller->record->getXref() . '", "_blank", edit_window_specs);
		}
	');

	$linked_indi = $controller->record->fetchLinkedIndividuals();
	$linked_fam  = $controller->record->fetchLinkedFamilies();
	$linked_obje = $controller->record->fetchLinkedMedia();
	$linked_note = $controller->record->fetchLinkedNotes();
	?>

	<div class="cell large-10 large-offset-1">
		<h3><?php echo $controller->record->getFullName(); ?></h3>
		<ul class="tabs" data-tabs id="source-tabs">
			<li class="tabs-title is-active"><a href="#source-edit"><span><?php echo KT_I18N::translate('Details'); ?></span></a></li>
			<?php if ($linked_indi) { ?>
				<li class="tabs-title"><a href="#indi-sources"><span id="indisource"><?php echo KT_I18N::translate('Individuals'); ?></span></a></li>
			<?php }
			if ($linked_fam) { ?>
				<li class="tabs-title"><a href="#fam-sources"><span id="famsource"><?php echo KT_I18N::translate('Families'); ?></span></a></li>
			<?php }
			if ($linked_obje) { ?>
				<li class="tabs-title"><a href="#media-sources"><span id="mediasource"><?php echo KT_I18N::translate('Media objects'); ?></span></a></li>
			<?php }
			if ($linked_note) { ?>
				<li class="tabs-title"><a href="#note-sources"><span id="notesource"><?php echo KT_I18N::translate('Notes'); ?></span></a></li>
			<?php } ?>
		</ul>
		<div class="tabs-content" data-tabs-content="source-tabs">
			<div class="tabs-panel is-active" id="source-edit">
				<div class="facts grid-x grid-margin-x grid-margin-y  grid-padding-x grid-padding-y">
					<?php
					$sourcefacts = $controller->record->getFacts();
					foreach ($sourcefacts as $fact) {
						print_fact($fact, $controller->record);
					}
					// Print media
					print_main_media($controller->record->getXref());
					// new fact link
					if ($controller->record->canEdit()) {
						print_add_new_fact($controller->record->getXref(), $sourcefacts, 'SOUR');
						// new media
						if (get_gedcom_setting(KT_GED_ID, 'MEDIA_UPLOAD') >= KT_USER_ACCESS_LEVEL) { ?>
							<div class="cell medium-3 fact-title">
								<?php echo KT_Gedcom_Tag::getLabel('OBJE'); ?>
							</div>
							<div class="cell medium-9 fact-detail">
								<p>
									<a href="#" onclick="window.open(\'addmedia.php?action=showmediaform&amp;linktoid=<?php echo $controller->record->getXref(); ?>\', \'_blank\', edit_window_specs); return false;">
										<?php echo KT_I18N::translate('Add a media object'); ?>
									</a>
								</p>
								<p>
									<a href="inverselink.php?linktoid=<?php echo $controller->record->getXref(); ?>&amp;linkto=source" target="_blank">
										<?php echo KT_I18N::translate('Link to an existing media object'); ?>
									</a>
								</p>
							</div>
						<?php }
					} ?>
				</div>
			</div>
			<?php
			// Individuals linked to this source
			if ($linked_indi) { ?>
				<div class="tabs-panel" id="indi-sources">
					<?php echo format_indi_table($linked_indi, $controller->record->getFullName()); ?>
				</div>
			<?php }
			// Families linked to this source
			if ($linked_fam) { ?>
				<div class="tabs-panel" id="fam-sources">
					<?php echo format_fam_table($linked_fam, $controller->record->getFullName()); ?>
				</div>
			<?php }
			// Media Items linked to this source
			if ($linked_obje) { ?>
				<div class="tabs-panel" id="media-sources">
					<?php echo format_media_table($linked_obje, $controller->record->getFullName()); ?>
				</div>
			<?php }
			// Shared Notes linked to this source
			if ($linked_note) { ?>
				<div class="tabs-panel" id="note-sources">
					<?php echo format_note_table($linked_note, $controller->record->getFullName()); ?>
				</div>
			<?php } ?>
		</div>
	</div>
</div> <!-- close div "source-details-page" -->
<?php
