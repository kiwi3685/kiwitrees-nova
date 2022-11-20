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

define('KT_SCRIPT_NAME', 'admin_trees_duplicates.php');

require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';
include KT_THEME_URL . 'templates/adminData.php';

global $iconStyle;

$gedID 	    = KT_Filter::post('gedID') ? KT_Filter::post('gedID') : KT_GED_ID;
$tree       = KT_Tree::getNameFromId($gedID);

$action		= KT_Filter::post('action','go', '');
$surn		= KT_Filter::post('surname', '[^<>&%{};]*');
$givn		= KT_Filter::post('given', '[^<>&%{};]*');
$exact_givn	= KT_Filter::postBool('exact_givn');
$exact_surn	= KT_Filter::postBool('exact_surn');
$married	= KT_Filter::postBool('married');
$gender		= KT_Filter::post('gender');
$date 		= KT_Filter::postInteger('date') ? KT_Filter::postInteger('date') : '';
$range 		= KT_Filter::postInteger('range');
$maxYear 	= date('Y') + 1;

if (KT_Filter::getBool('reset')) {
	$action		= '';
	$gedID		= KT_GED_ID;
	$surn		= '';
	$givn		= '';
	$exact_givn	= '';
	$exact_surn	= '';
	$married	= '';
	$gender		= '';
	$date 		= '';
	$range 		= '';
}

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Find duplicate individuals'))
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('
		autocomplete();

		//After submit, scroll to Results area
		if (jQuery("#duplicates_table").length != 0) {
			jQuery("html, body").animate({
				scrollTop: jQuery("#duplicates_table").offset().top
			}, 2000);
		}

		// prevent more than two boxes from being checked
		var checked = 0;
		function addCheck(box) {
			// allow checked box to be unchecked
			if(!box.checked) return true;
			// get ref to collection
			var boxes = document.getElementsByName(box.name);
			// count checked
			var cb, count=0, k=0;
			while(cb=boxes[k++])
				if(cb.checked && ++count>2){
					alert("Sorry, you can only merge 2 at a time");
					return false;
				}
			return true;
		}

		// loop through all checkboxes with class "check" and create input string for form
		function checkbox_test() {
			var i = 0
			var counter = 1;
			var gid = new Array();
			var record_type = new Array();
			var action = new Array();
			form = document.createElement("form");
			form.setAttribute("method", "POST");
			form.setAttribute("action", "admin_trees_merge.php");
			form.setAttribute("target", "_blank");

			// get a collection of objects with the specified class "check"
			input_obj = document.getElementsByClassName("check"); // this might fail on some old browsers (see http://caniuse.com/getelementsbyclassname)
			// loop through all collected objects
			for (i = 0; i < input_obj.length; i++) {
				if (input_obj[i].checked === true) {
					gid[i] = document.createElement("input");
					gid[i].setAttribute("name", "gid" + counter);
					gid[i].setAttribute("type", "hidden");
					gid[i].setAttribute("value", input_obj[i].value);
					form.appendChild(gid[i]);
					counter++;
				}
			}

			var record_type = document.createElement("input");
			record_type.setAttribute("name", "record_type");
			record_type.setAttribute("type", "hidden");
			record_type.setAttribute("value", "INDI");
			form.appendChild(record_type);
			var action = document.createElement("input");
			action.setAttribute("name", "action");
			action.setAttribute("type", "hidden");
			action.setAttribute("value", "choose");
			form.appendChild(action);

			// display send form or display message if there is only 1 or no checked checkboxes
			if (counter > 0) {
				if (counter == 1) {
					alert("Select TWO items to merge");
					return false;
				}
				// send checkbox values
				document.body.appendChild(form);
				form.submit();
			} else {
				alert("There is nothing selected");
			}
		}

	');

// the sql query used to identify duplicates
$sql = '
	SELECT DISTINCT n_id, n_full, n_type, n_sort
	FROM `##name`
';
if ($date || preg_match('/\d{4}(?<!0000)/', $date)) {
	$minDate = $date - $range;
	$maxDate = $date + $range;
	$sql .= '
		INNER JOIN `##dates` ON d_gid = n_id
		WHERE n_file = '. $gedID . '
		AND (
			(d_fact IN "'. KT_EVENTS_BIRT . '" AND d_year <= ' . $maxDate . ' AND d_year >= ' . $minDate . ')
			 OR
			(d_fact IN "'. KT_EVENTS_DEAT . '" AND d_year <= ' . $maxDate . ' AND d_year >= ' . $minDate . ')
		)
	';
} else {
	$sql .= 'WHERE n_file = '. $gedID . ' ';
}
	if ($exact_surn) {
	$sql .= 'AND n_surn = "' . $surn  . '" ';
	} else {
	$sql .= 'AND n_surn LIKE "%' . $surn . '%" ';
	}
	if ($exact_givn) {
		$sql .= 'AND n_givn = "' . $givn  . '" ';
	} else {
		$sql .= 'AND n_givn LIKE "%' . $givn . '%"';
	}
	if (!$married) {
		$sql .= 'AND n_type NOT LIKE "_MARNM" ';
	}
$sql .= 'ORDER BY n_sort ASC';

$SHOW_EST_LIST_DATES=get_gedcom_setting(KT_GED_ID, 'SHOW_EST_LIST_DATES');

echo relatedPages($trees, KT_SCRIPT_NAME);

echo pageStart('find_duplicates', $controller->getPageTitle()); ?>

		<div class="grid-x grid-margin-x">
			<div class="cell callout info-help ">
				<?php echo KT_I18N::translate('
					Search for possible duplicate individuals.
					The minimum required to start the search is either a surname or
					given name, although both are preferable.
					If too many results are displayed you can complete further fields
					to improve the accuracy, but this may also increase the chance of
					missing one or more duplicates. From the completed list choose
					any two (click checkboxes at right) then click "Merge duplicates" to take those to the "Merge duplicates" page.
					Results are only shown where a minimum of two similar people are found.
				'); ?>
			</div>
			<!-- Family tree -->
 			<div class="cell medium-2">
				<label for="gedID"><?php echo KT_I18N::translate('Family tree'); ?></label>
			</div>
			<div class="cell medium-4">
				<form id="tree" method="post" action="#" name="tree">
					<?php echo select_ged_control('gedID', KT_Tree::getIdList(), null, KT_GEDCOM, ' onchange="tree.submit();"'); ?>
				</form>
			</div>
			<div class="cell medium-6"></div>
			<form class="cell" method="post" name="duplicates_form" action="<?php echo KT_SCRIPT_NAME; ?>">
				<input type="hidden" name="action" value="go">
				<div class="grid-x grid-margin-x">
					<!-- Surnames -->
					<div class="cell medium-2">
						<label for="SURN"><?php echo KT_I18N::translate('Surname'); ?></label>
					</div>
					<div class="cell medium-4">
						<?php echo autocompleteHtml(
							'surname', // id
							'SURN', // TYPE
							$tree, // autocomplete-ged
							htmlspecialchars($surn), // input value
							KT_I18N::translate('A full or partial surname'), // placeholder
							'surname', // hidden input name
							htmlspecialchars($surn) // hidden input value
						); ?>
					</div>
					<div class="cell shrink">
						<label for="exact_surn"><?php echo KT_I18N::translate('Match exactly'); ?></label>
					</div>
					<div class="cell medium-4 auto tinySwitch">
						<?php echo simple_switch(
							"exact_surn",
							"1",
							$exact_surn,
							'',
							KT_I18N::translate('Yes'),
							KT_I18N::translate('No'),
							"tiny"
						); ?>
					</div>
					<!-- Given names -->
					<div class="cell medium-2">
						<label for="GIVN"><?php echo KT_I18N::translate('Given name(s)'); ?></label>
					</div>
					<div class="cell medium-4">
						<?php echo autocompleteHtml(
							'given', // id
							'GIVN', // TYPE
							$tree, // autocomplete-ged
							htmlspecialchars($givn), // input value
							KT_I18N::translate('The full or partial given name(s)'), // placeholder
							'given', // hidden input name
							htmlspecialchars($givn), // hidden input value
						); ?>
					</div>
					<div class="cell shrink">
						<label for="exact_givn"><?php echo KT_I18N::translate('Match exactly'); ?></label>
					</div>
					<div class="cell medium-4 auto tinySwitch">
						<?php echo simple_switch(
							"exact_givn",
							"1",
							$exact_givn,
							'',
							KT_I18N::translate('Yes'),
							KT_I18N::translate('No'),
							"tiny"
						); ?>
					</div>
					<!-- Gender -->
					<div class="cell medium-2">
						<label for="gender"><?php echo KT_I18N::translate('Gender'); ?></label>
					</div>
					<div class="cell medium-4">
						<select id="gender" name="gender" value="<?php echo $gender; ?>">
							<option value="A"
								<?php if ($gender == 'A' || empty($gender)) { ; ?>
									selected
								<?php } ?>
							>
								<?php echo KT_I18N::translate('Any'); ?>
							</option>
							<option value="M"
								<?php if ($gender == 'M') { ; ?>
									selected
								<?php } ?>
							>
								<?php echo KT_I18N::translate('Male'); ?>
							</option>
							<option value="F"
								<?php if ($gender == 'F') { ; ?>
									selected
								<?php } ?>
							>
								<?php echo KT_I18N::translate('Female'); ?>
							</option>
							<option value="U"
								<?php if ($gender == 'U') { ; ?>
									selected
								<?php } ?>
							>
								<?php echo KT_I18N::translate_c('unknown gender', 'Unknown'); ?>
							</option>
						</select>
					</div>
					<div class="cell medium-6"></div>
					<!-- Married name -->
					<div class="cell medium-2">
						<label for="checkbox3"><?php echo KT_I18N::translate('Include married names'); ?></label>
					</div>
					<div class="cell medium-4 auto tinySwitch">
						<?php echo simple_switch(
							"married",
							"1",
							$married,
							'',
							KT_I18N::translate('Yes'),
							KT_I18N::translate('No'),
							"tiny"
						); ?>
					</div>
					<div class="cell medium-6"></div>
					<!-- Date and range -->
					<div class="cell medium-2">
						<label for="date" class="align-self-middle"><?php echo KT_I18N::translate('Birth or death year'); ?></label>
					</div>
					<div class="cell medium-4 input-group">
						<input class="input-group-field" id="date" type="number" name="date" min="1200" max="<?php echo $maxYear; ?>" value="<?php echo $date; ?>" placeholder="<?php echo KT_I18N::translate('Year'); ?>">
						<span class="input-group-label"> + / - </span>
						<input class="input-group-field" id="range" type="number" name="range" min="0" max="10" value="<?php echo $range; ?>" >
						<span class="input-group-label"><?php echo KT_I18N::translate('Years'); ?></span>
					</div>
				</div>
				<?php echo singleButton('Show'); ?>
			</form>
			<form class="cell small-2 small-offset-7 medium-1 medium-offset-1" method="post" name="rela_form" action="#">
				<input type="hidden" name="reset" value="1">
				<button class="button hollow reset" type="submit">
					<i class="<?php echo $iconStyle; ?> fa-rotate"></i>
					 <?php echo KT_I18N::translate('Reset'); ?>
				</button>
			</form>
		</div>
	<?php
	if ($action == 'go') {
		$controller
			->addExternalJavascript(KT_DATATABLES_JS)
			->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
			->addExternalJavascript(KT_DATATABLES_BUTTONS)
			->addExternalJavascript(KT_DATATABLES_HTML5)
			->addInlineJavascript('
				jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
				jQuery("#duplicates_table").dataTable({
					dom: \'<"top"pBf<"clear">irl>t<"bottom"pl>\',
					' . KT_I18N::datatablesI18N() . ',
					buttons: [{extend: "csvHtml5", exportOptions: {columns: ":visible" }}],
					autoWidth: false,
					pagingType: "full_numbers",
					lengthChange: true,
					filter: true,
					info: true,
					displayLength: 25,
					stateSave: false,
					stateDuration: -1,
					columns: [
						/*  0 name        */ {},
						/*  1 BYEAR       */ { visible: false },
						/*  2 birth year  */ { dataSort: 1 },
						/*  3 birth place */ { type: "unicode" },
						/*  4 DYEAR       */ { visible: false },
						/*  5 death year  */ { dataSort: 4 },
						/*  6 death place */ { type: "unicode" },
						/*  7 merge       */ { orderable: false, width: 100 },
					],
					order: [[ 0, "asc" ], [ 1, "asc" ]],
				});
			');

		$rows = KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
		$count = 0;

		foreach ($rows as $row) {
			$count ++;
		}

		if ($rows && $count > 1) { ?>
			<hr class="cell">
			<h4 class="cell"><?php echo KT_I18N::translate('Results'); ?></h4>
			<div class="cell">
				<table id="duplicates_table">
					<thead>
						<tr>
							<th><?php echo KT_I18N::translate('Name'); ?></th>
							<th>BIRTH YEAR</th>
							<th><?php echo KT_I18N::translate('Birth Date'); ?></th>
							<th><?php echo KT_I18N::translate('Birth Place'); ?></th>
							<th>DEATH YEAR</th>
							<th><?php echo KT_I18N::translate('Death Date'); ?></th>
							<th><?php echo KT_I18N::translate('Death Place'); ?></th>
							<th style="text-align: center; white-space: normal;">
								<a href="#"  onclick="return checkbox_test();">
									<?php echo KT_I18N::translate('Merge selected'); ?>
								</a>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 0;
						foreach ($rows as $row) {
							$i++;
							$bdate	= '&nbsp;';
							$bplace	= '';
							$ddate	= '&nbsp;';
							$dplace	= '';
							if ($row['n_type'] == '_MARNM') {
								$marr = '<br><span style="font-style:italic; font-size:80%;">(' . KT_I18N::translate('Married name') . ')</span>';
							} else {
								$marr = '';
							}
							$id = $row['n_id'];
							$person = KT_Person::getInstance($id);
							if ($person->getSex() == $gender || $gender == 'A') {
								// find birth/death dates
								if ($birth_dates=$person->getAllBirthDates()) {
									foreach ($birth_dates as $num => $birth_date) {
										if ($num) {$bdate .= '<br>';}
										$bdate .= $birth_date->Display();
									}
								} else {
									$birth_date	= $person->getEstimatedBirthDate();
									if ($SHOW_EST_LIST_DATES) {
										$bdate .= $birth_date->Display();
									} else {
										$bdate .= '&nbsp;';
									}
									$birth_dates[0] = new KT_Date('');
								}

								//find birth places
								foreach ($person->getAllBirthPlaces() as $n => $birth_place) {
									$tmp = new KT_Place($birth_place, KT_GED_ID);
									if ($n) {$bplace .= '<br>';}
									$bplace .= $tmp->getShortName();
								}

								// find death dates
								if ($death_dates = $person->getAllDeathDates()) {
									foreach ($death_dates as $num => $death_date) {
										if ($num) {$ddate .= '<br>';}
										$ddate .= $death_date->Display();
									}
								} else {
									$death_date	= $person->getEstimatedDeathDate();
									if ($SHOW_EST_LIST_DATES) {
										$ddate .= $death_date->Display();
									} else if ($person->isDead()) {
										$ddate .= KT_I18N::translate('yes');
									} else {
										$ddate .= '&nbsp;';
									}
									$death_dates[0]=new KT_Date('');
								}

								// find death places
								foreach ($person->getAllDeathPlaces() as $n => $death_place) {
									$tmp = new KT_Place($death_place, KT_GED_ID);
									if ($n) {$dplace .= '<br>';}
									$dplace .= $tmp->getShortName();
								}

								if ($bdate !== '&nbsp;' && $ddate !== '&nbsp;') { ?>
									<tr>
										<td>
											<a href="<?php echo $person->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer">
												<?php echo $row['n_full']; ?> <?php echo $marr; ?>
											</a>
										</td>
										<td><?php echo $person->getBirthYear(); ?></td>
										<td><?php echo $bdate; ?></td>
										<td><?php echo $bplace; ?></td>
										<td><?php echo $person->getDeathYear(); ?></td>
										<td><?php echo $ddate; ?></td>
										<td><?php echo $dplace; ?></td>
										<td class="text-center"><input type="checkbox" name="gid[]" onclick="return addCheck(this);" class="check" value="<?php echo $id; ?>"></td>
									</tr>
								<?php }
							}
						} ?>
					</tbody>
				</table>
			</div>
		<?php } else { ?>
			<div class="cell callout alert text-center"><?php echo KT_I18N::translate('No duplicates to display'); ?></div>
		<?php }
	}

echo pageClose();
