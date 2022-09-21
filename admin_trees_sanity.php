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

define('KT_SCRIPT_NAME', 'admin_trees_sanity.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';
require KT_ROOT.'includes/functions/functions_print_facts.php';
require KT_ROOT.'includes/functions/functions_sanity_checks.php';
global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Sanity check'))
	->pageHeader();

if (KT_Filter::post('gedID')) {
	$gedID = KT_Filter::post('gedID');
} else {
	$gedID = KT_GED_ID;
}

$MAX_ALIVE_AGE	= get_gedcom_setting($gedID, 'MAX_ALIVE_AGE');

// default ages
$bap_age	= 5;
$oldage		= $MAX_ALIVE_AGE;
$marr_age	= 14;
$spouseage	= 30;
$child_y	= 15;
$child_o	= 50;
$bDate		= 1;
$bPlac		= 1;
$bSour		= 1;
$dDate		= 1;
$dPlac		= 1;
$dSour		= 1;
$Main		= 1;
$Thum		= 1;
$Zero		= 1;
$Link       = 1;

if (KT_Filter::postBool('reset')) {
	set_gedcom_setting($gedID, 'SANITY_BAPTISM', $bap_age);
	set_gedcom_setting($gedID, 'SANITY_OLDAGE', $oldage);
	set_gedcom_setting($gedID, 'SANITY_MARRIAGE', $marr_age);
	set_gedcom_setting($gedID, 'SANITY_SPOUSE_AGE', $spouseage);
	set_gedcom_setting($gedID, 'SANITY_CHILD_Y', $child_y);
	set_gedcom_setting($gedID, 'SANITY_CHILD_O', $child_o);
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_BD', $bDate);
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_BP', $bPlac);
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_BS', $bSour);
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_DD', $dDate);
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_DP', $dPlac);
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_DS', $dSour);
	set_gedcom_setting($gedID, 'MEDIA_ISSUE_M', $Main);
	set_gedcom_setting($gedID, 'MEDIA_ISSUE_T', $Thum);
	set_gedcom_setting($gedID, 'MEDIA_ISSUE_Z', $Zero);
	set_gedcom_setting($gedID, 'MEDIA_ISSUE_L', $Link);

	AddToLog($controller->getPageTitle() .' set to default values', 'config');
}

// save new values
if (KT_Filter::postBool('save') && !KT_Filter::postBool('reset')) {
	set_gedcom_setting($gedID, 'SANITY_BAPTISM',		KT_Filter::post('NEW_SANITY_BAPTISM', KT_REGEX_INTEGER, $bap_age));
	set_gedcom_setting($gedID, 'SANITY_OLDAGE',			KT_Filter::post('NEW_SANITY_OLDAGE', KT_REGEX_INTEGER, $oldage));
	set_gedcom_setting($gedID, 'SANITY_MARRIAGE',		KT_Filter::post('NEW_SANITY_MARRIAGE', KT_REGEX_INTEGER, $marr_age));
	set_gedcom_setting($gedID, 'SANITY_SPOUSE_AGE',		KT_Filter::post('NEW_SANITY_SPOUSE_AGE', KT_REGEX_INTEGER, $spouseage));
	set_gedcom_setting($gedID, 'SANITY_CHILD_Y',		KT_Filter::post('NEW_SANITY_CHILD_Y', KT_REGEX_INTEGER, $child_y));
	set_gedcom_setting($gedID, 'SANITY_CHILD_O',		KT_Filter::post('NEW_SANITY_CHILD_O', KT_REGEX_INTEGER, $child_o));
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_BD',	KT_Filter::postBool('NEW_SANITY_INCOMPLETE_BD', $bDate));
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_BP',	KT_Filter::postBool('NEW_SANITY_INCOMPLETE_BP', $bPlac));
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_BS',	KT_Filter::postBool('NEW_SANITY_INCOMPLETE_BS', $bSour));
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_DD',	KT_Filter::postBool('NEW_SANITY_INCOMPLETE_DD', $dDate));
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_DP',	KT_Filter::postBool('NEW_SANITY_INCOMPLETE_DP', $dPlac));
	set_gedcom_setting($gedID, 'SANITY_INCOMPLETE_DS',	KT_Filter::postBool('NEW_SANITY_INCOMPLETE_DS', $dSour));
	set_gedcom_setting($gedID, 'MEDIA_ISSUE_M',	        KT_Filter::postBool('NEW_MEDIA_ISSUE_M', $Main));
	set_gedcom_setting($gedID, 'MEDIA_ISSUE_T',	        KT_Filter::postBool('NEW_MEDIA_ISSUE_T', $Thum));
	set_gedcom_setting($gedID, 'MEDIA_ISSUE_Z',	        KT_Filter::postBool('NEW_MEDIA_ISSUE_Z', $Zero));
	set_gedcom_setting($gedID, 'MEDIA_ISSUE_L',	        KT_Filter::postBool('NEW_MEDIA_ISSUE_L', $Link));

	AddToLog($controller->getPageTitle() .' set to new values', 'config');
}

// settings to use
$bap_age	= get_gedcom_setting($gedID, 'SANITY_BAPTISM');
$oldage		= get_gedcom_setting($gedID, 'SANITY_OLDAGE');
$marr_age	= get_gedcom_setting($gedID, 'SANITY_MARRIAGE');
$spouseage	= get_gedcom_setting($gedID, 'SANITY_SPOUSE_AGE');
$child_y	= get_gedcom_setting($gedID, 'SANITY_CHILD_Y');
$child_o	= get_gedcom_setting($gedID, 'SANITY_CHILD_O');
$bDate		= get_gedcom_setting($gedID, 'SANITY_INCOMPLETE_BD');
$bPlac		= get_gedcom_setting($gedID, 'SANITY_INCOMPLETE_BP');
$bSour		= get_gedcom_setting($gedID, 'SANITY_INCOMPLETE_BS');
$dDate		= get_gedcom_setting($gedID, 'SANITY_INCOMPLETE_DD');
$dPlac		= get_gedcom_setting($gedID, 'SANITY_INCOMPLETE_DP');
$dSour		= get_gedcom_setting($gedID, 'SANITY_INCOMPLETE_DS');
$Main		= get_gedcom_setting($gedID, 'MEDIA_ISSUE_M');
$Thum		= get_gedcom_setting($gedID, 'MEDIA_ISSUE_T');
$Zero		= get_gedcom_setting($gedID, 'MEDIA_ISSUE_Z');
$Link		= get_gedcom_setting($gedID, 'MEDIA_ISSUE_L');

/**
 * Array of sanity check groupings
 * Single item - title of group
 */
$checkGroups = array (
	KT_I18N::translate('Date discrepancies'),
	KT_I18N::translate('Age related queries'),
	KT_I18N::translate('Duplicated data'),
	KT_I18N::translate('Missing or invalid data'),
);

/**
 * Array of options required to define "incomplete birth data""
 */
$birthOptions = array(
	'NEW_SANITY_INCOMPLETE_BD',
	$bDate,
	'NEW_SANITY_INCOMPLETE_BP',
	$bPlac,
	'NEW_SANITY_INCOMPLETE_BS',
	$bSour
);

$bDateTag = $bDate ? 'DATE' : '';
$bPlacTag = $bPlac ? 'PLAC' : '';
$bSourTag = $bSour ? 'SOUR' : '';

/**
 * Array of options required to define "incomplete death data""
 */
$deathOptions = array(
	'NEW_SANITY_INCOMPLETE_DD',
	$dDate,
	'NEW_SANITY_INCOMPLETE_DP',
	$dPlac,
	'NEW_SANITY_INCOMPLETE_DS',
	$dSour
);

$dDateTag = $dDate ? 'DATE' : '';
$dPlacTag = $dPlac ? 'PLAC' : '';
$dSourTag = $dSour ? 'SOUR' : '';

/**
 * Array of options required to include as Media Issues
 */
$mediaOptions = array(
	'NEW_MEDIA_ISSUE_M',
	$Main,
	'NEW_MEDIA_ISSUE_T',
	$Thum,
	'NEW_MEDIA_ISSUE_Z',
	$Zero,
	'NEW_MEDIA_ISSUE_L',
	$Link
);

/**
 * Array of items for sanity $checks
 *  1st = the group this item is listed under
 *  2nd = The id, name of the li tag, and the name and value of the input tag
 *  3rd = The label for the items
 *  4th = Any additional html required, such as asterixs for exceptionally slow options
 */
$checks = array (
	array (1, 'baptised',		KT_I18N::translate('Birth after baptism or christening')),
	array (1, 'died',			KT_I18N::translate('Birth after death or burial')),
	array (1, 'birt_marr',		KT_I18N::translate('Birth after marriage')),
	array (1, 'birt_chil',		KT_I18N::translate('Birth after their children') . ' <i class="' . $iconStyle . ' fa-triangle-exclamation alert"></i> '),
	array (1, 'buri',			KT_I18N::translate('Burial before death')),
	array (1, 'sib_ages',		KT_I18N::translate('Sibling age differences') . ' <i class="' . $iconStyle . ' fa-triangle-exclamation alert"></i> '),

	array (2, 'bap_late',		KT_I18N::translate('Baptised after a certain age'), 'NEW_SANITY_BAPTISM', 'bap_age', $bap_age),
	array (2, 'old_age',		KT_I18N::translate('Alive after a certain age'), 'NEW_SANITY_OLDAGE', 'oldage', $oldage),
	array (2, 'marr_yng',		KT_I18N::translate('Married before a certain age') . ' <i class="' . $iconStyle . ' fa-triangle-exclamation alert"></i> ', 'NEW_SANITY_MARRIAGE', 'marr_age', $marr_age),
	array (2, 'spouse_age',		KT_I18N::translate('Being much older than spouse'), 'NEW_SANITY_SPOUSE_AGE',	'spouseage', $spouseage),
	array (2, 'child_yng',		KT_I18N::translate('Mothers having children before a certain age'), 'NEW_SANITY_CHILD_Y', 'child_y', $child_y),
	array (2, 'child_old',		KT_I18N::translate('Mothers having children past a certain age'), 'NEW_SANITY_CHILD_O', 'child_o', $child_o),

	array (3, 'dupe_birt',		KT_I18N::translate('Individual - Birth')),
	array (3, 'dupe_bapm',		KT_I18N::translate('Individual - Baptism or christening')),
	array (3, 'dupe_deat',		KT_I18N::translate('Individual - Death')),
	array (3, 'dupe_crem',		KT_I18N::translate('Individual - Cremation')),
	array (3, 'dupe_buri',		KT_I18N::translate('Individual - Burial')),
	array (3, 'dupe_sex',		KT_I18N::translate('Individual - Gender')),
	array (3, 'dupe_name',		KT_I18N::translate('Individual - Name')),
	array (3, 'dupe_marr',		KT_I18N::translate('Family - Marriage')),
	array (3, 'dupe_child',		KT_I18N::translate('Family - Duplicately named children')),

	array (4, 'birt',			KT_I18N::translate('Missing or incomplete birth'), $birthOptions),
	array (4, 'deat',			KT_I18N::translate('Missing or incomplete death'), $deathOptions),
	array (4, 'sex',			KT_I18N::translate('No gender recorded')),
	array (4, 'age',			KT_I18N::translate('Invalid age recorded')),
	array (4, 'empty_tag',		KT_I18N::translate('Empty individual fact or event') . '<i class="' . $iconStyle . ' fa-triangle-exclamation alert"></i> '),
	array (4, 'child_order',	KT_I18N::translate('Children not sorted by birth date')),
	array (4, 'fam_order',		KT_I18N::translate('Families not sorted by marriage date')),
	array (4, 'media_issues',	KT_I18N::translate('Media object issues') . '<i class="' . $iconStyle . ' fa-triangle-exclamation alert"></i>', $mediaOptions),

);

// Base datatables structure //
$controller
	->addExternalJavascript(KT_DATATABLES_JS)
	->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
;
if (KT_USER_CAN_EDIT) {
	$controller
		->addExternalJavascript(KT_DATATABLES_BUTTONS)
		->addExternalJavascript(KT_DATATABLES_HTML5);
	$buttons = 'B';
} else {
	$buttons = '';
}

$controller
	->addInlineJavascript('
		// Create datatable
		jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
		jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
		jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
		jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
		jQuery("table.accordionDataTable").dataTable({
			dom: \'<"top"p' . $buttons . '<"clear">irl>t\',
			' . KT_I18N::datatablesI18N() . ',
			buttons: [{ extend: "csvHtml5" }],
			autoWidth: false,
			displayLength: 50,
			pagingType: "full_numbers",
		});

		// After submit, scroll Results area up
		jQuery("#sanityList").css("display", "block");
		jQuery(".loading-image").css("display", "none");
		jQuery("html, body").animate({scrollTop: jQuery("#admin-content").offset().top+2000}, 1000);

');

echo relatedPages($links = array(
    'admin_trees_manage.php',
    'admin_trees_config.php',
    'admin_trees_check.php',
    'admin_trees_change.php',
    'admin_trees_addunlinked.php',
    'admin_trees_places.php',
    'admin_trees_merge.php',
    'admin_trees_renumber.php',
    'admin_trees_append.php',
    'admin_trees_duplicates.php',
    'admin_trees_findunlinked.php',
    'admin_trees_source.php',
    'admin_trees_sourcecite.php',
    'admin_trees_missing.php',
));

// Start settings form
echo pageStart('sanity_check', $controller->getPageTitle(), 'y', KT_I18N::translate('%s checks to help you monitor the quality of your family history data', count($checks)), 'general-topics/sanity-check/'); ?>
	<div class="cell callout warning help-content">
		<?php echo KT_I18N::translate('
			Options marked <i class="' . $iconStyle . ' fa-triangle-exclamation alert"></i>
			are often very slow. If you have a large family tree or suspect large numbers of errors
			you should only select a few checks each time.
		'); ?>
	</div>
	<!-- Family tree -->
	<div class="cell medium-2">
		<label for="gedID"><?php echo KT_I18N::translate('Family tree'); ?></label>
	</div>
	<div class="cell medium-4">
		<form method="post" action="#" name="tree">
			<?php echo select_ged_control('gedID', KT_Tree::getIdList(), null, $gedID, ' onchange="tree.submit();"'); ?>
		</form>
	</div>
	<div class="cell medium-6"></div>

	<form class="cell" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
		<input type="hidden" name="save" value="1">
		<input type="hidden" name="gedID" value="<?php echo $gedID; ?>">
		<div class="grid-x grid-margin-x">
			<!-- Checking option categories -->
			<?php for ($i = 1; $i < count($checkGroups) + 1; $i ++) { ?>
				<fieldset class="fieldset cell medium-6">
					<legend><?php echo $checkGroups[$i-1]; ?></legend>
					<?php for ($row = 0; $row < count($checks); $row ++) {
						if ($checks[$row][0] == $i) { ?>
							<div class="input-group">
								<?php echo simple_switch($checks[$row][1], $checks[$row][1], KT_Filter::post($checks[$row][1]), '', KT_I18N::translate('Yes'), KT_I18N::translate('No'), $size = 'tiny'); ?>
								<span class="input-group-label">
									<?php echo $checks[$row][2]; ?>
								</span>
								<?php if (isset($checks[$row][3])) {
									if ($checks[$row][3] == $birthOptions) { ?>
										<?php echo simple_switch($birthOptions[0], "1", $birthOptions[1], '', KT_I18N::translate('Yes'), KT_I18N::translate('No'), $size = 'tiny'); ?><span class="switches"><?php echo KT_I18N::translate('Date'); ?></span>
										<?php echo simple_switch($birthOptions[2], "1", $birthOptions[3], '', KT_I18N::translate('Yes'), KT_I18N::translate('No'), $size = 'tiny'); ?><span class="switches"><?php echo KT_I18N::translate('Place'); ?></span>
										<?php echo simple_switch($birthOptions[4], "1", $birthOptions[5], '', KT_I18N::translate('Yes'), KT_I18N::translate('No'), $size = 'tiny'); ?><span class="switches"><?php echo KT_I18N::translate('Source'); ?></span>
									<?php } elseif ($checks[$row][3] == $deathOptions) { ?>
										<?php echo simple_switch($deathOptions[0], "1", $deathOptions[1], '', KT_I18N::translate('Yes'), KT_I18N::translate('No'), $size = 'tiny'); ?><span class="switches"><?php echo KT_I18N::translate('Date'); ?></span>
										<?php echo simple_switch($deathOptions[2], "1", $deathOptions[3], '', KT_I18N::translate('Yes'), KT_I18N::translate('No'), $size = 'tiny'); ?><span class="switches"><?php echo KT_I18N::translate('Place'); ?></span>
										<?php echo simple_switch($deathOptions[4], "1", $deathOptions[5], '', KT_I18N::translate('Yes'), KT_I18N::translate('No'), $size = 'tiny'); ?><span class="switches"><?php echo KT_I18N::translate('Source'); ?></span>
                                    <?php } elseif ($checks[$row][3] == $mediaOptions) { ?>
										<?php echo simple_switch($mediaOptions[0], "1", $mediaOptions[1], '', KT_I18N::translate('Yes'), KT_I18N::translate('No'), $size = 'tiny'); ?><span class="switches"><?php echo KT_I18N::translate('Main'); ?></span>
										<?php echo simple_switch($mediaOptions[2], "1", $mediaOptions[3], '', KT_I18N::translate('Yes'), KT_I18N::translate('No'), $size = 'tiny'); ?><span class="switches"><?php echo KT_I18N::translate('Thumb'); ?></span>
										<?php echo simple_switch($mediaOptions[4], "1", $mediaOptions[5], '', KT_I18N::translate('Yes'), KT_I18N::translate('No'), $size = 'tiny'); ?><span class="switches"><?php echo KT_I18N::translate('Zero'); ?></span>
										<?php echo simple_switch($mediaOptions[6], "1", $mediaOptions[7], '', KT_I18N::translate('Yes'), KT_I18N::translate('No'), $size = 'tiny'); ?><span class="switches"><?php echo KT_I18N::translate('Link'); ?></span>
									<?php } else { ?>
										<input class="input-group-field" type="text" name="<?php echo $checks[$row][3]; ?>" id="<?php echo $checks[$row][4]; ?>" value="<?php echo $checks[$row][5]; ?>" >
									<?php }
								} ?>
						 	</div>
						<?php }
					} ?>
				</fieldset>
			<?php } ?>
		</div>
		<div class="cell small-1">
			<?php echo singleButton('Show'); ?>
		</div>
	</form>
	<form class="cell small-2 small-offset-7 medium-1 medium-offset-1" method="post" name="rela_form" action="#">
		<input type="hidden" name="reset" value="1">
		<button class="button hollow reset" type="submit">
			<i class="<?php echo $iconStyle; ?> fa-rotate"></i>
			 <?php echo KT_I18N::translate('Reset'); ?>
		</button>
	</form>

	<!-- START RESULTS OUTOUT -->
	<?php if (KT_Filter::postBool('save')) { ?>
		<div class="cell" id="sanityList" style="display:none;">
			<?php echo loadingImage(); ?>
			<div class="grid-x">
				<h4><?php echo KT_I18N::translate('Results'); ?></h4>

				<div class="cell accordion" id="dataAccordion" data-accordion data-allow-all-closed="true">
					<!-- Start of each result -->

					<!-- Date discrepancies -->
					<?php if (KT_Filter::post('baptised')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = birth_comparisons($gedID, array('BAPM', 'CHR')); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s born after baptism or christening', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_Gedcom_Tag::getLabel('BIRT'); ?></th>
											<th><?php echo KT_I18N::translate('Baptism or christening'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('died')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = birth_comparisons($gedID, array('DEAT')); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s born after death or burial', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_Gedcom_Tag::getLabel('BIRT'); ?></th>
											<th><?php echo KT_I18N::translate('Death or burial'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('birt_marr')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = birth_comparisons($gedID, array('FAMS'), 'MARR'); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s born after marriage', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_Gedcom_Tag::getLabel('BIRT'); ?></th>
											<th><?php echo KT_I18N::translate('Marriage'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('birt_chil')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = birth_comparisons($gedID, array('FAMS'), 'CHIL'); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s born after their children', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_Gedcom_Tag::getLabel('BIRT'); ?></th>
											<th><?php echo KT_I18N::translate('Child'); ?></th>
											<th><?php echo KT_I18N::translate('Child\'s birth'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('buri')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = death_comparisons($gedID, array('BURI')); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s buried before death', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_Gedcom_Tag::getLabel('BURI'); ?></th>
											<th><?php echo KT_Gedcom_Tag::getLabel('DEAT'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('sib_ages')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = birth_comparisons($gedID, array('FAMS'), 'CHIL_AGES'); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s siblings with an age difference less than 9 months', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_I18N::translate('Age difference'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>

					<!-- Age related -->
					<?php if (KT_Filter::post('marr_yng')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = query_age($gedID, array('MARR'), $marr_age); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%1s married younger than %2s years', $data['count'], $marr_age); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_I18N::translate('Marriage Year'); ?></th>
											<th><?php echo KT_I18N::translate('Age at marriage'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('bap_late')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = query_age($gedID, array('BAPM', 'CHR'), $bap_age); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%1s baptised more than %2s years after birth', $data['count'], $bap_age); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_I18N::translate('Baptism Year'); ?></th>
											<th><?php echo KT_I18N::translate('Age at baptism'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('old_age')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = query_age($gedID, array('DEAT'), $oldage); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%1s living and older than %2s years', $data['count'], $oldage); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_I18N::translate('Birth Year'); ?></th>
											<th><?php echo KT_I18N::translate('Current age'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('spouse_age')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = query_age($gedID, array('FAMS'), $spouseage); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%1s spouses with more than %2s years age difference', $data['count'], $spouseage); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_I18N::translate('Age difference (years)'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('child_yng')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = query_age($gedID, array('CHIL_1'), $child_y); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::plural('%1s woman having children before age %2s years', '%1s women having children before age %2s years', $data['count'], $data['count'], $child_y); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Mother'); ?></th>
											<th><?php echo KT_I18N::translate('Age at birth'); ?></th>
											<th><?php echo KT_I18N::translate('Child'); ?></th>
											<th><?php echo KT_I18N::translate('Year of birth'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('child_old')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = query_age($gedID, array('CHIL_2'), $child_o); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%1s women having children after age %2s years', $data['count'], $child_o); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Mother'); ?></th>
											<th><?php echo KT_I18N::translate('Age at birth'); ?></th>
											<th><?php echo KT_I18N::translate('Child'); ?></th>
											<th><?php echo KT_I18N::translate('Year of birth'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>

					<!-- Duplicated data -->
					<?php if (KT_Filter::post('dupe_birt')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = duplicate_tag($gedID, 'BIRT'); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s with duplicate births recorded', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('dupe_bapm')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = duplicate_tag($gedID, 'BAPM'); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s with duplicate baptism or christenings recorded', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
							  <table class="accordionDataTable shadow">
							    <thead>
							      <tr>
							        <th><?php echo KT_I18N::translate('Name'); ?></th>
							      </tr>
							    </thead>
							    <tbody>
							      <?php echo $data['html']; ?>
							    </tbody>
							  </table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('dupe_deat')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = duplicate_tag($gedID, 'DEAT'); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s with duplicate deaths recorded', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
								</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('dupe_crem')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = duplicate_tag($gedID, 'CREM'); ?>
						<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s with duplicate cremations recorded', $data['count']); ?>
							<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
						</a>
						<div class="accordion-content" data-tab-content>
							<table class="accordionDataTable shadow">
								<thead>
									<tr>
										<th><?php echo KT_I18N::translate('Name'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php echo $data['html']; ?>
								</tbody>
							</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('dupe_buri')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = duplicate_tag($gedID, 'BURI'); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s with duplicate burial or cremations recorded', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('dupe_sex')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = duplicate_tag($gedID, 'SEX'); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s with duplicate genders recorded', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('dupe_name')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = identical_name($gedID); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s with duplicate names recorded', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('dupe_marr')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = duplicate_famtag($gedID, 'MARR'); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s with duplicate marriages recorded', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('dupe_child')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = duplicate_child($gedID); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s with duplicately named children', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Parents'); ?></th>
											<th><?php echo KT_I18N::translate('Children'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>

					<!-- Missing or invalid data-->
					<?php if (KT_Filter::post('birt')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = missing_vital($gedID, 'BIRT', $bDateTag, $bPlacTag, $bSourTag); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s have missing or incomplete birth data', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_I18N::translate('GEDCOM data'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('deat')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = missing_vital($gedID, 'DEAT', $dDateTag, $dPlacTag, $dSourTag); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s have missing or incomplete death data', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_I18N::translate('GEDCOM data'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('sex')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = missing_tag($gedID, 'SEX'); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s have no gender recorded', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('age')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = invalid_age($gedID); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%s individuals or families have age incorrectly recorded', $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_I18N::translate('GEDCOM data'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('empty_tag')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = empty_tag($gedID); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::plural('%s individual with empty fact or attribute tags', '%s individuals with empty fact or attribute tags', $data['count'], $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Name'); ?></th>
											<th><?php echo KT_I18N::translate('Details'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('child_order')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = child_order($gedID); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::plural('%s family with children not sorted by birth date', '%s families with children not sorted by birth date', $data['count'], $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Family name'); ?></th>
											<th><?php echo KT_I18N::translate('Edit link'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('fam_order')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = fam_order($gedID); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::plural('%s family not sorted by marriage date', '%s families not sorted by marriage date', $data['count'], $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Family name'); ?></th>
											<th><?php echo KT_I18N::translate('Edit link'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<?php if (KT_Filter::post('media_issues')) { ?>
						<div class="accordion-item" data-accordion-item>
							<?php $data = media_issues($gedID, $Main, $Thum, $Zero, $Link); ?>
							<a href="#" class="accordion-title"><?php echo KT_I18N::plural('%s object has one or more issues', '%s objects have one or more issues', $data['count'], $data['count']); ?>
								<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<table class="accordionDataTable shadow">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Family name'); ?></th>
											<th><?php echo KT_I18N::translate('Edit link'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php echo $data['html']; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<!-- End of each result -->
				</div>
			</div>
		</div>
	<?php }

echo pageClose();
