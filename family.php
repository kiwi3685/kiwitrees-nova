<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'family.php');
require './includes/session.php';

$controller = new KT_Controller_Family();

if ($controller->record && $controller->record->canDisplayDetails()) {
	$controller->pageHeader();

	if ($controller->record->isMarkedDeleted()) {
		if (KT_USER_CAN_ACCEPT) { ?>
			<div class="callout secondary">
				<?php echo /* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
					'This family has been deleted.  You should review the deletion and then %1$s or %2$s it.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'); ?>
			</div>
		<?php } elseif (KT_USER_CAN_EDIT) { ?>
			<div class="callout secondary">
				<?php echo KT_I18N::translate('This family has been deleted.  The deletion will need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'); ?>
			</div>
		<?php }
	} elseif (find_updated_record($controller->record->getXref(), KT_GED_ID)!==null) {
		if (KT_USER_CAN_ACCEPT) { ?>
			<div class="callout secondary">
				<?php echo /* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
					'This family has been edited.  You should review the changes and then %1$s or %2$s them.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'); ?>
			</div>
		<?php } elseif (KT_USER_CAN_EDIT) { ?>
			<div class="callout secondary">
				<?php echo KT_I18N::translate('This family has been edited.  The changes need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'); ?>
			</div>
		<?php }
	}
} elseif ($controller->record && $SHOW_PRIVATE_RELATIONSHIPS) {
	$controller->pageHeader();
	// Continue - to display the children/parents/grandparents.
	// We'll check for showing the details again later
} else {
	header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
	$controller->pageHeader(); ?>
	<div class="callout alert">
		<?php echo KT_I18N::translate('This family does not exist or you do not have permission to view it.'); ?>
	</div>
	<?php exit;
}

$PEDIGREE_FULL_DETAILS = '1'; // Override GEDCOM configuration
$show_full = '1';

$controller->addInlineJavascript('
	// open specified tab, previously saved tab, or the first one
	if (window.location.hash) {
		var hash = window.location.hash;
	} else if (sessionStorage.getItem("fam-tab")) {
		var hash = sessionStorage.getItem("fam-tab");
	} else {
		var hash = jQuery("#famTabs li:first a").attr("href");
	};
	var openhash = hash.substr(1);
	jQuery("#famTabs li." + openhash).addClass("is-active");
	jQuery("div#" + openhash).addClass("is-active");
	jQuery("#famTabs li." + openhash + " a").attr("aria-selected","true");
	jQuery("#famTabs").on("change.zf.tabs", function() {
		sessionStorage.setItem("fam-tab", window.location.hash);
	});
');
?>

<div class="grid-x grid-padding-y" id="family-page">
	<div class="cell">
		<h3>
			<?php echo $controller->record->getFullName(); ?>
		</h3>
		<div class="grid-x">
			<div class="cell" id="family_chart">
					<?php print_parents($controller->record->getXref());
					if (KT_USER_CAN_EDIT) {
						if ($controller->diff_record) {
							$husb = $controller->diff_record->getHusband();
						} else {
							$husb = $controller->record->getHusband();
						}
						if ($controller->diff_record) {
							$wife = $controller->diff_record->getWife();
						} else {
							$wife = $controller->record->getWife();
						}
					} ?>
				<div id="children">
					<?php print_children($controller->record->getXref()); ?>
				</div>
			</div>
		</div>
	</div>

	<!-- =============== Family page tabs ====================== -->
	<?php foreach ($controller->tabs as $tab) {
		echo $tab->getPreLoadContent();
	} ?>
	<?php if (count($controller->tabs) > 1) { ?>
		<div class="cell">
			<ul class="tabs" id="famTabs" data-deep-link="true" data-allow-all-closed="true" data-responsive-accordion-tabs="tabs small-accordion medium-tabs" >
				<?php foreach ($controller->tabs as $tab) {
					if ($tab->isGrayedOut()) {
						$greyed_out = ' rela';
					} else {
						$greyed_out = '';
					}
					$ajax = '';
					if ($tab->hasTabContent()) { ?>
						<li class="<?php echo $tab->getName(); ?> tabs-title<?php echo $greyed_out; ?>">
							<a href="#<?php echo $tab->getName(); ?>" title="<?php echo $tab->getDescription(); ?>">
								<?php echo $tab->getTitle(); ?>
							</a>
						</li>
					<?php }
				} ?>
			</ul>
			<div class="tabs-content" data-tabs-content="famTabs">
				<?php foreach ($controller->tabs as $tab) {
					if ($tab->hasTabContent()) { ?>
						<div class="tabs-panel" id="<?php echo $tab->getName(); ?>">
							<?php echo $tab->getTabContent(); ?>
						</div>
					<?php }
				} ?>
			</div>
		</div>
	<?php } else { ?>
		<?php foreach ($controller->tabs as $tab) {
			if ($tab->hasTabContent()) { ?>
				<div class="cell" id="<?php echo $tab->getName(); ?>">
					<?php echo $tab->getTabContent(); ?>
				</div>
			<?php }
		} ?>
	<?php } ?>
</div>
<?php
