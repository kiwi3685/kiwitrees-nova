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
global $MAX_ALIVE_AGE, $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Sanity check'))
	->pageHeader();

// default ages
$bap_age	= 5;
$oldage		= $MAX_ALIVE_AGE;
$marr_age	= 14;
$spouseage	= 30;
$child_y	= 15;
$child_o	= 50;

if (KT_Filter::postBool('reset')) {
	set_gedcom_setting(KT_GED_ID, 'SANITY_BAPTISM', $bap_age);
	set_gedcom_setting(KT_GED_ID, 'SANITY_OLDAGE', $oldage);
	set_gedcom_setting(KT_GED_ID, 'SANITY_MARRIAGE', $marr_age);
	set_gedcom_setting(KT_GED_ID, 'SANITY_SPOUSE_AGE', $spouseage);
	set_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_Y', $child_y);
	set_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_O', $child_o);

	AddToLog($controller->getPageTitle() .' set to default values', 'config');
}

// save new values
if (KT_Filter::postBool('save')) {
	set_gedcom_setting(KT_GED_ID, 'SANITY_BAPTISM',		KT_Filter::post('NEW_SANITY_BAPTISM', KT_REGEX_INTEGER, $bap_age));
	set_gedcom_setting(KT_GED_ID, 'SANITY_OLDAGE',		KT_Filter::post('NEW_SANITY_OLDAGE', KT_REGEX_INTEGER, $oldage));
	set_gedcom_setting(KT_GED_ID, 'SANITY_MARRIAGE',	KT_Filter::post('NEW_SANITY_MARRIAGE', KT_REGEX_INTEGER, $marr_age));
	set_gedcom_setting(KT_GED_ID, 'SANITY_SPOUSE_AGE',	KT_Filter::post('NEW_SANITY_SPOUSE_AGE', KT_REGEX_INTEGER, $spouseage));
	set_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_Y',		KT_Filter::post('NEW_SANITY_CHILD_Y', KT_REGEX_INTEGER, $child_y));
	set_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_O',		KT_Filter::post('NEW_SANITY_CHILD_O', KT_REGEX_INTEGER, $child_o));

	AddToLog($controller->getPageTitle() .' set to new values', 'config');
}

// settings to use
$bap_age	= get_gedcom_setting(KT_GED_ID, 'SANITY_BAPTISM');
$oldage		= get_gedcom_setting(KT_GED_ID, 'SANITY_OLDAGE');
$marr_age	= get_gedcom_setting(KT_GED_ID, 'SANITY_MARRIAGE');
$spouseage	= get_gedcom_setting(KT_GED_ID, 'SANITY_SPOUSE_AGE');
$child_y	= get_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_Y');
$child_o	= get_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_O');

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
	array (1, 'birt_chil',		KT_I18N::translate('Birth after their children') . '<span class="alert">**</span>'),
	array (1, 'buri',			KT_I18N::translate('Burial before death')),
	array (2, 'bap_late',		KT_I18N::translate('Baptised after a certain age'), 'NEW_SANITY_BAPTISM', 'bap_age', $bap_age),
	array (2, 'old_age',		KT_I18N::translate('Alive after a certain age'), 'NEW_SANITY_OLDAGE', 'oldage', $oldage),
	array (2, 'marr_yng',		KT_I18N::translate('Married before a certain age') . '<span class="alert">**</span>', 'NEW_SANITY_MARRIAGE', 'marr_age', $marr_age),
	array (2, 'spouse_age',		KT_I18N::translate('Being much older than spouse'), 'NEW_SANITY_SPOUSE_AGE',	'spouseage', $spouseage),
	array (2, 'child_yng',		KT_I18N::translate('Mothers having children before a certain age'), 'NEW_SANITY_BAPTISM', 'child_y', $child_y),
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
	array (4, 'birt',			KT_I18N::translate('Missing or incomplete birth')),
	array (4, 'deat',			KT_I18N::translate('Missing or incomplete death')),
	array (4, 'sex',			KT_I18N::translate('No gender recorded')),
	array (4, 'age',			KT_I18N::translate('Invalid age recorded')),
	array (4, 'empty_tag',		KT_I18N::translate('Empty individual fact or event') . '<span class="alert">**</span>'),
	array (4, 'child_order',	KT_I18N::translate('Children not sorted by birth date')),
	array (4, 'fam_order',		KT_I18N::translate('Families not sorted by marriage date')),
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

$table_id = 'sanityTable'.(int)(microtime(true)*1000000); // lists requires a unique ID in case there are multiple lists per page

$controller
	->addInlineJavascript('
		jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
		jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
		jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
		jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
		jQuery("#' . $table_id . '").dataTable({
			dom: \'<"top"p' . $buttons . 'f<"clear">irl>t<"bottom"pl>\',
			' . KT_I18N::datatablesI18N() . ',
			buttons: [{extend: "csvHtml5", exportOptions: {columns: [0,1,2] }}],
			autoWidth: false,
			processing: true,
			retrieve: true,
			displayLength: 50,
			pagingType: "full_numbers",
			stateSave: true,
			stateSaveParams: function (settings, data) {
				data.columns.forEach(function(column) {
					delete column.search;
				});
			},
			stateDuration: -1,
			columns: [
				/*  0  */ { },
				/*  1  */ { },
				/*  2  */ { }
			],

		});

		jQuery(".sanityList").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
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
			This process can be slow.
			If you have a large family tree or suspect large numbers of errors
			you should only select a few checks each time.
			<br>Options marked
			<span class="alert">**</span> are often very slow.
		'); ?>
	</div>
	<form class="cell" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
		<input type="hidden" name="save" value="1">
		<div class="grid-x grid-margin-x">
			<div class="cell medium-4">
				<label><?php echo KT_I18N::translate('Family tree'); ?></label>
				<?php echo select_ged_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM); ?>
			</div>
			<div class="cell medium-8"></div>
			<?php for ($i = 1; $i < count($checkGroups) + 1; $i ++) { ?>
				<div class="cell medium-4 large-3">
					<h5><?php echo $checkGroups[$i-1]; ?></h5>
					<?php for ($row = 0; $row < count($checks); $row ++) {
						if ($checks[$row][0] == $i) { ?>
							<div class="input-group">
								<input class="input-group-field check" type="checkbox" name="<?php echo $checks[$row][1]; ?>" value="<?php echo $checks[$row][1]; ?>"
									<?php if (KT_Filter::post($checks[$row][1])) echo ' checked="checked"'?>
								>
								<?php if (isset($checks[$row][3])) { ?>
									<span class="input-group-label size"><?php echo $checks[$row][2]; ?></span>
									<input class="input-group-field size" name="<?php echo $checks[$row][3]; ?>" id="<?php echo $checks[$row][4]; ?>" type="text" value="<?php echo $checks[$row][5]; ?>" >
								<?php } else { ?>
									<span class="input-group-label"><?php echo $checks[$row][2]; ?></span>
								<?php } ?>
							</div>
						<?php }
					} ?>
				</div>
			<?php } ?>
		</div>
		<hr class="cell">
		<?php echo singleButton('Show'); ?>
	</form>
	<form method="post" name="rela_form" action="#">
		<input type="hidden" name="reset" value="1">
		<button class="button hollow reset" type="submit">
			<i class="<?php echo $iconStyle; ?> fa-rotate"></i>
			 <?php echo KT_I18N::translate('Reset'); ?>
		</button>
	</form>
</div>

<!-- START RESULTS OUTOUT -->
<?php echo loadingImage(); ?>

<div class="grid-x sanityList">
	<?php if (KT_Filter::post('save')) { ?>
		<h4><?php echo KT_I18N::translate('Results'); ?></h4>
		<div class="cell accordion" data-accordion data-multi-expand="true" data-allow-all-closed="true">
			<!-- Start of each result -->
			<?php
			if (KT_Filter::post('marr_yng')) { ?>
				<div class="accordion-item" data-accordion-item>
					<?php $data = query_age(array('MARR'), $marr_age); ?>
					<a href="#" class="accordion-title"><?php echo KT_I18N::translate('%1s married younger than %2s years', $data['count'], $marr_age); ?>
						<span class="float-right"><?php echo KT_I18N::translate('query time: %1s secs', $data['time']); ?></span>
					</a>
					<div class="accordion-content" data-tab-content>
						<table class="shadow" id="<?php echo $table_id; ?>">
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
			<!-- End of each result -->
		</div>
	<?php } ?>
</div>
