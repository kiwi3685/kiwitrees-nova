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

define('KT_SCRIPT_NAME', 'individual.php');
require './includes/session.php';
if (get_gedcom_setting(KT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI') > 0) {
    include_once './includes/functions/functions_print_relations.php';
}

$controller = new KT_Controller_Individual();

if ($controller->record && $controller->record->canDisplayDetails()) {
    if (KT_Filter::get('action') == 'ajax') {
        $controller->ajaxRequest();
        exit;
    }
    // Generate the sidebar content *before* we display the page header,
    // as the clippings cart needs to have write access to the session.
    $sidebar_html = $controller->getSideBarContent();

    $controller->pageHeader();
    if ($controller->record->isMarkedDeleted()) {
        if (KT_USER_CAN_ACCEPT) { ?>
			<div class="callout alert">
				<?php echo /* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */
                    KT_I18N::translate(
                        'This individual has been deleted.  You should review the deletion and then %1$s or %2$s it.',
                        '<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\'' . $controller->record->getXref() . '\'},function(){location.reload();})">' .
                            KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'accept') . '
						</a>',
                        '<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\'' . $controller->record->getXref() . '\'},function(){location.reload();})">' .
                            KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'reject') . '
						</a>'
                    ) . ' ' . help_link('pending_changes');
                ?>
			</div>
		<?php } elseif (KT_USER_CAN_EDIT) { ?>
			<div class="callout alert">
				<?php echo KT_I18N::translate('This individual has been deleted.  The deletion will need to be reviewed by a moderator.') . ' ' . help_link('pending_changes'); ?>
			</div>
		<?php }
    } elseif (find_updated_record($controller->record->getXref(), KT_GED_ID) !== null) {
        if (KT_USER_CAN_ACCEPT) { ?>
			<div class="callout alert">
				<?php echo /* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */
                    KT_I18N::translate(
                        'This individual has been edited. You should review the changes and then %1$s or %2$s them.',
                        '<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\'' . $controller->record->getXref() . '\'},function(){location.reload();})">' .
                            KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'accept') . '
						</a>',
                        '<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\'' . $controller->record->getXref() . '\'},function(){location.reload();})">' .
                            KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'reject') . '
						</a>'
                    ) . ' ' . help_link('pending_changes');
                ?>
			</div>
		<?php } elseif (KT_USER_CAN_EDIT) { ?>
			<div class="callout alert">
				<?php echo KT_I18N::translate('This individual has been edited.  The changes need to be reviewed by a moderator.') . ' ' . help_link('pending_changes'); ?>
			</div>
		<?php }
    }
} elseif ($controller->record && $controller->record->canDisplayName()) {
    // Just show the name
    $controller->pageHeader(); ?>
	<div class="callout alert">
		<h3><?php echo $controller->record->getFullName(); ?></h3>
		<p><?php echo KT_I18N::translate('The details of this individual are private.'); ?></p>
	</div>
	<?php exit;
} else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    $controller->pageHeader(); ?>
	<div class="callout alert">
		<?php echo KT_I18N::translate('This individual does not exist or you do not have permission to view it.'); ?>
	</div>
	<?php exit;
}

/* Check for Highlight image or silhouette */
$highlightImage = false;
$image          = $controller->record->displayImage();
if ($image || $USE_SILHOUETTE) {
    $highlightImage = true;
}

$linkToID = $controller->record->getXref(); // -- Tell addmedia.php what to link to

$controller->addInlineJavascript('
    // Open specified tab, previously saved tab, or the first one
    if (window.location.hash) {
        var hash = window.location.hash;
    } else if (sessionStorage.getItem("indi-tab")) {
        var hash = sessionStorage.getItem("indi-tab");
    } else {
        var hash = jQuery("#indiTabs li:first a").attr("href");
    };
    var openhash = hash.substr(1);
    jQuery("#indiTabs li." + openhash).addClass("is-active");
    jQuery("div#" + openhash).addClass("is-active");
    jQuery("#indiTabs li." + openhash + " a").attr("aria-selected","true");
    jQuery("#indiTabs").on("change.zf.tabs", function() {
        sessionStorage.setItem("indi-tab", window.location.hash);
    });

');

// Check if sidebar active and set widths accordingly
if (KT_Module::getActiveSidebars()) {
    $class = " large-9";
} else {
    $class = "";
}
// Check if hightlight image is active
if ($highlightImage) {
    $class2 = " medium-9 large-10";
} else {
    $class2 = "";
} ?>

<!-- Start page layout  -->
<div id="indi-page" class="grid-x grid-margin-x">
	<?php if ($controller->record->canDisplayDetails()) { ?>
		<div class="cell<?php echo $class; ?>">
				<!-- Header area -->
				<div class="grid-x indiContent">
					<?php $globalfacts = $controller->getGlobalFacts(); ?>
					<!-- Preferred name, age etc -->
					<div class="cell">
						<div class="grid-x grid-padding-x">
							<div class="cell medium-8 large-9">
								<h3 class="text-center medium-text-left"><?php echo $controller->record->getFullName(); ?></h3>
							</div>
							<div class="cell medium-4 large-3">
								<?php
                                $bdate = $controller->record->getBirthDate();
                                $ddate = $controller->record->getDeathDate();
                                ?>
								<h4 class="text-center medium-text-right">
									<?php foreach ($globalfacts as $key => $value) {
                                    $fact = $value->getTag();
                                    if ($fact == "SEX") {
                                        $controller->print_sex_record($value);
                                    }
                                } ?>
									<span class="header_age">
										<?php if ($bdate->isOK() && !$controller->record->isDead()) {
                                    // If living display age
                                    echo KT_Gedcom_Tag::getLabelValue('AGE', get_age_at_event(KT_Date::GetAgeGedcom($bdate), true), '', 'span');
                                } elseif ($bdate->isOK() && $ddate->isOK()) {
                                    // If dead, show age at death
                                    echo KT_Gedcom_Tag::getLabelValue('AGE', get_age_at_event(KT_Date::GetAgeGedcom($bdate, $ddate), false), '', 'span');
                                } ?>
									</span>
									<span id="dates">
										<?php echo $controller->record->getLifeSpan(); ?>
									</span>
								</h4>
							</div>
						</div>
					</div>
					<div class="cell">
						<div class="grid-x indiHeader">
							<?php if ($highlightImage) { ?>
								<div class="cell medium-3 large-2 small-text-center medium-text-left">
									<?php echo $controller->record->displayImage(); ?>
								</div>
							<?php } ?>
							<div class="cell<?php echo $class2; ?>">
								<!-- Name details -->
								<div class="accordion" data-accordion data-allow-all-closed="true" data-multi-open="false" data-slide-speed="500">
									<?php foreach ($globalfacts as $key => $value) {
                                    $fact = $value->getTag();
                                    if ($fact == "NAME") {
                                        $controller->print_name_record($value);
                                    }
                                } ?>
								</div>
							</div>
							<?php if (
                                // Relationship to default individual
                                array_key_exists('chart_relationship', KT_Module::getActiveModules()) && KT_USER_ID && get_gedcom_setting(KT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI') > 0
                                ) { ?>
									<div class="cell medium-12 text-right indi_rela"><?php echo printIndiRelationship(); ?></div>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php // =============== Individual page tabs ======================
            foreach ($controller->tabs as $tab) {
                echo $tab->getPreLoadContent();
            } ?>

			<div class="grid-x">
				<div class="cell">
					<ul class="tabs" data-tabs id="indiTabs" data-deep-link="true" data-allow-all-closed="true" data-responsive-accordion-tabs="tabs small-accordion medium-tabs" >
						<?php foreach ($controller->tabs as $tab) {
                            if ($tab->isGrayedOut()) {
                                $greyed_out = ' rela';
                            } else {
                                $greyed_out = '';
                            }
                            if ($tab->hasTabContent()) { ?>
								<li class="<?php echo $tab->getName(); ?> tabs-title<?php echo $greyed_out; ?>">
                                    <?php if ($tab->canLoadAjax()) { ?>
    									<!-- AJAX tabs load only when selected -->
    									<a href="<?php echo $controller->record->getHtmlUrl(); ?>&amp;action=ajax&amp;module=<?php echo $tab->getName(); ?>" title="<?php echo $tab->getDescription(); ?>" rel="nofollow">
								    <?php } else { ?>
    									<a href="#<?php echo $tab->getName(); ?>" title="<?php echo $tab->getDescription(); ?>" rel="nofollow">
                                    <?php } ?>
										<span><?php echo $tab->getTitle(); ?></span>
									</a>
								</li>
							<?php }
						} ?>
					</ul>
					<div class="tabs-content" data-tabs-content="indiTabs">
						<?php foreach ($controller->tabs as $tab) {
                            if ($tab->hasTabContent()) { ?>
								<div class="tabs-panel" id="<?php echo $tab->getName(); ?>">
                                    <?php if (!$tab->canLoadAjax()) {
    									echo $tab->getTabContent();
                                    } ?>
								</div>
							<?php }
                        } ?>
					</div>
				</div>
			</div>
		</div>
		<?php // Check if sidebar active and set widths accordingly
        if (KT_Module::getActiveSidebars()) { ?>
			<div class="cell large-3">
				<?php echo $sidebar_html; ?>
			</div>
		<?php }
    } ?>
