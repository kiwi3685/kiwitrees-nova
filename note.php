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

define('KT_SCRIPT_NAME', 'note.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_print_lists.php';

$controller = new KT_Controller_Note();
$controller->pageHeader(); ?>

<div id="note-details-page" class="grid-x grid-padding-x">
	<div class="cell large-10 large-offset-1">
		<?php if ($controller->record && $controller->record->canDisplayDetails()) {
			if ($controller->record->isMarkedDeleted()) {
				if (KT_USER_CAN_ACCEPT) { ?>
					<div class="callout alert">
						<?php echo /* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
							'This note has been deleted. You should review the deletion and then %1$s or %2$s it.',
							'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\'' . $controller->record->getXref() . '\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'accept') . '</a>',
							'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\'' . $controller->record->getXref() . '\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'reject') . '</a>'
						); ?>
					</div>
					<?php echo helpDropdown('pending_changes');
				} elseif (KT_USER_CAN_EDIT) { ?>
					<div class="callout alert">',
						<?php echo KT_I18N::translate('This note has been deleted. The deletion will need to be reviewed by a moderator.'); ?>
					</div>
					<?php echo helpDropdown('pending_changes');
				}
			} elseif (find_updated_record($controller->record->getXref(), KT_GED_ID) !== null) {
				if (KT_USER_CAN_ACCEPT) { ?>
					<div class="callout alert">
						<?php echo /* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
							'This note has been edited.  You should review the changes and then %1$s or %2$s them.',
							'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'accept') . '</a>',
							'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'reject') . '</a>'
						); ?>
					</div>
					<?php echo helpDropdown('pending_changes');
				} elseif (KT_USER_CAN_EDIT) { ?>
					<div class="callout alert">
						<?php echo KT_I18N::translate('This note has been edited. The changes need to be reviewed by a moderator.'); ?>
					</div>
					<?php echo helpDropdown('pending_changes');
				}
			}
		} else {
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
			$controller->pageHeader(); ?>
			<div class="callout alert">
				<?php echo KT_I18N::translate('This note does not exist or you do not have permission to view it.'); ?>
			</div>
			<?php exit;
		} ?>
	</div>

	<?php
	$linkToID		= $controller->record->getXref(); // Tell addmedia.php what to link to

	$controller->addInlineJavascript('
		function show_gedcom_record() {
			var recwin=window.open("gedrecord.php?pid=' . $controller->record->getXref() . '", "_blank", edit_window_specs);
		}
	');

	$linked_indi	= $controller->record->fetchLinkedIndividuals();
	$linked_fam 	= $controller->record->fetchLinkedFamilies();
	$linked_obje	= $controller->record->fetchLinkedMedia();
	$linked_sour	= $controller->record->fetchLinkedSources();
	?>

	<div class="cell large-10 large-offset-1">
		<h3><?php echo $controller->record->getFullName(); ?></h3>
		<ul class="tabs" data-tabs id="note-tabs">
			<li class="tabs-title is-active"><a href="#note-edit" aria-selected="true"><span><?php echo KT_I18N::translate('Details'); ?></span></a></li>
			<?php if ($linked_indi) { ?>
				<li class="tabs-title"><a href="#indi-note"><span id="indisource"><?php echo KT_I18N::translate('Individuals'); ?></span></a></li>
			<?php }
			if ($linked_fam) { ?>
				<li class="tabs-title"><a href="#fam-note"><span id="famsource"><?php echo KT_I18N::translate('Families'); ?></span></a></li>
			<?php }
			if ($linked_obje) { ?>
				<li class="tabs-title"><a href="#media-note"><span id="mediasource"><?php echo KT_I18N::translate('Media objects'); ?></span></a></li>
			<?php }
			if ($linked_sour) { ?>
				<li class="tabs-title"><a href="#source-note"><span id="notesource"><?php echo KT_I18N::translate('Sources'); ?></span></a></li>
			<?php } ?>
		</ul>

		<?php
		$noterec = $controller->record->getGedcomRecord();
		preg_match("/0 @" . $controller->record->getXref() . "@ NOTE(.*)/", $noterec, $n1match);
		$note = print_note_record("<br>" . $n1match[1], 1, $noterec, false, true, true);
		?>

		<div class="tabs-content" data-tabs-content="note-tabs">
			<div class="tabs-panel is-active" id="note-edit">
				<div class="facts grid-x grid-margin-x grid-margin-y  grid-padding-x grid-padding-y">
					<div class="cell medium-2 fact-title">
						<?php if (KT_USER_CAN_EDIT) { ?>
							<a class="has-tip" onclick="return edit_note('<?php echo $controller->record->getXref(); ?>')" title="<?php echo KT_I18N::translate('Edit'); ?>" data-tooltip aria-haspopup="true" data-click-open="false" data-disable-hover="false">
							<?php echo KT_I18N::translate('Shared note'); ?></a>
							<div class="editfacts button-group">
								<a class="button clear has-tip" onclick="return edit_note('<?php echo $controller->record->getXref(); ?>')" title="<?php echo KT_I18N::translate('Edit'); ?>" data-tooltip aria-haspopup="true" data-click-open="false" data-disable-hover="false">
									<i class="fas fa-pen-to-square"></i>
									<span class="link_text"><?php echo KT_I18N::translate('Edit'); ?></span>
								</a>
							</div>
						<?php } else { ?>
							<?php echo KT_I18N::translate('Shared note');
						} ?>
					</div>
					<div class="cell medium-10 fact-detail">
						<?php echo $note; ?>
					</div>
					<?php
					$notefacts = $controller->record->getFacts();
					foreach ($notefacts as $fact) {
						if ($fact->getTag() != 'CONT') {
							print_fact($fact, $controller->record);
						}
					}
					// Print media
					print_main_media($controller->record->getXref());
					// new fact link
					if ($controller->record->canEdit()) {
						print_add_new_fact($controller->record->getXref(), $notefacts, 'NOTE');
					}
					?>
				</div>
			</div>
			<!-- Individuals linked to this shared note -->
			<?php if ($linked_indi) { ?>
				<div class="tabs-panel" id="indi-note">
					<?php echo format_indi_table($controller->record->fetchLinkedIndividuals(), $controller->record->getFullName()); ?>
				</div>
			<?php } ?>
			<!-- Families linked to this shared note -->
			<?php if ($linked_fam) { ?>
				<div class="tabs-panel" id="fam-note">
					<?php echo format_fam_table($controller->record->fetchLinkedFamilies(), $controller->record->getFullName()); ?>
				</div>
			<?php } ?>
			<!-- Media Items linked to this shared note -->
			<?php if ($linked_obje) { ?>
				<div class="tabs-panel" id="media-note">
					<?php echo format_media_table($controller->record->fetchLinkedMedia(), $controller->record->getFullName()); ?>
				</div>
			<?php } ?>
			<!-- Sources linked to this shared note -->
			<?php if ($linked_sour) { ?>
				<div class="tabs-panel" id="source-note">
					<?php echo format_sour_table($controller->record->fetchLinkedSources(), $controller->record->getFullName()); ?>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
<?php
