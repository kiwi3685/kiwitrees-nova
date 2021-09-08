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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class report_vital_records_KT_Module extends KT_Module implements KT_Module_Report {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module. */ KT_I18N::translate('Vital records');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Vital records” module */ KT_I18N::translate('A report of individuals\' births, marriages and deaths for a selected name, place or date range.');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Report
	public function getReportMenus() {
		global $controller;

		$indi_xref = $controller->getSignificantIndividual()->getXref();

		$menus = array();
		$menu  = new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
			'menu-report-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class KT_Module_Report
	public function show() {
		global $controller;
		require KT_ROOT . 'includes/functions/functions_resource.php';
		require KT_ROOT . 'includes/functions/functions_edit.php';

		$controller = new KT_Controller_Individual();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('
				autocomplete();
				// check that at least one filter has been used
				function checkform() {
					if (
						document.resource.name.value == "" &&
						document.resource.place.value == "" &&
						document.resource.b_from.value == "" &&
						document.resource.b_to.value == "" &&
						document.resource.d_from.value == "" &&
						document.resource.d_to.value == ""
					) {
						if (confirm("' . KT_I18N::translate('You have not set any filters. Kiwitrees will try to list records for every individual in your tree. Is this what you want to do?') . '")){
							document.resource.submit(); // OK
						} else {
							return false; // Cancel
						}
					}
				}
			');

		//Configuration settings ===== //
		$action = KT_Filter::post('action');
		$reset  = KT_Filter::post('reset');
		$name   = KT_Filter::post('name', '');
		$b_from = KT_Filter::post('b_from', '');
		$b_to   = KT_Filter::post('b_to', '');
		$d_from = KT_Filter::post('d_from', '');
		$d_to   = KT_Filter::post('d_to', '');
		$place  = KT_Filter::post('place', '');

		// dates for calculations
		$b_fromJD = (new KT_Date($b_from))->minJD();
		$b_toJD   = (new KT_Date($b_to))->minJD();
		$d_fromJD = (new KT_Date($d_from))->minJD();
		$d_toJD   = (new KT_Date($d_to))->minJD();

		// reset all variables
		if ($reset == 'reset') {
			$action = '';
			$name   = '';
			$b_from = '';
			$b_to   = '';
			$d_from = '';
			$d_to   = '';
			$place  = '';
		}

		?>
		<div id="page" class="vital_records">
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="noprint">
				<div class="help_text">
					<div class="help_content">
						<h5><?php echo $this->getDescription(); ?></h5>
						<a href="#" class="more noprint"><i class="' . $iconStyle . ' fa-question-circle-o icon-help"></i></a>
						<div class="hidden">
							<?php echo /* I18N: help for report vital records module */ KT_I18N::translate('Date filters can be full (04 APR 1842) or 4-digit year only (1823). Name and place can be any string of characters you expect to find in those data fields. Autocomplete will find any given or surname that contains the characters you enter. To include all names or all places leave those fields empty.'); ?>
						</div>
					</div>
				</div>
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>">
					<input type="hidden" name="action" value="go">
					<div class="chart_options">
						<label for = "NAME"><?php echo KT_Gedcom_Tag::getLabel('NAME'); ?></label>
						<input data-autocomplete-type="NAME" type="text" name="name" id="NAME" value="<?php echo KT_Filter::escapeHtml($name); ?>" dir="auto" placeholder="<?php echo /*I18N:placeholder for a name selection field */ KT_I18N::translate('Enter all or part of any name'); ?>">
					</div>
					<div class="chart_options">
						<label for = "PLAC"><?php echo KT_Gedcom_Tag::getLabel('PLAC'); ?></label>
						<input data-autocomplete-type="PLAC" type="text" name="place" id="PLAC" value="<?php echo KT_Filter::escapeHtml($place); ?>" dir="auto" placeholder="<?php echo /*I18N:placeholder for a place selection field */ KT_I18N::translate('Enter all or part of any place'); ?>">
					</div>
					<div class="chart_options">
					  <label for = "DATE1"><?php echo KT_I18N::translate('Birth date - from'); ?></label>
					  <input type="text" name="b_from" id="DATE1" value="<?php echo $b_from; ?>" onblur="valid_date(this);" onmouseout="valid_date(this);"><?php echo print_calendar_popup("DATE1"); ?>
					</div>
					<div class="chart_options">
					  <label for = "DATE2"><?php echo KT_I18N::translate('Birth date - to'); ?></label>
					  <input type="text" name="b_to" id="DATE2" value="<?php echo $b_to; ?>" onblur="valid_date(this);" onmouseout="valid_date(this);"><?php echo print_calendar_popup("DATE2"); ?>
					</div>
					<div class="chart_options">
					  <label for = "DATE3"><?php echo KT_I18N::translate('Death date - from'); ?></label>
					  <input type="text" name="d_from" id="DATE3" value="<?php echo $d_from; ?>" onblur="valid_date(this);" onmouseout="valid_date(this);"><?php echo print_calendar_popup("DATE3"); ?>
					</div>
					<div class="chart_options">
					  <label for = "DATE4"><?php echo KT_I18N::translate('Death date - to'); ?></label>
					  <input type="text" name="d_to" id="DATE4" value="<?php echo $d_to; ?>" onblur="valid_date(this);" onmouseout="valid_date(this);"><?php echo print_calendar_popup("DATE4"); ?>
					</div>
					<button class="btn btn-primary" type="submit" value="<?php echo KT_I18N::translate('show'); ?>" onclick="return checkform()">
						<i class="' . $iconStyle . ' fa-eye"></i>
						<?php echo KT_I18N::translate('show'); ?>
					</button>
					<button class="btn btn-primary" type="submit" name="reset" value="reset">
						<i class="' . $iconStyle . ' fa-sync"></i>
						<?php echo KT_I18N::translate('Reset'); ?>
					</button>
				</form>
			</div>
			<hr class="noprint" style="clear:both;">
			<!-- end of form -->
			<?php if ($action == 'go') {
				$controller
					->addExternalJavascript(KT_DATATABLES_JS)
					->addExternalJavascript(KT_DATATABLES_HTML5)
					->addExternalJavascript(KT_JQUERY_DT_BUTTONS)
					->addInlineJavascript('
						jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
						jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
						jQuery("#vital_records").dataTable({
							dom: \'<"H"pBf<"clear">irl>t<"F"pl>\',
							' . KT_I18N::datatablesI18N() . ',
							buttons: [{extend: "csv", exportOptions: {columns: ":visible"}}],
							autoWidth: false,
							paging: true,
							pagingType: "full_numbers",
							lengthChange: true,
							filter: true,
							info: true,
							jQueryUI: true,
							sorting: [0,"asc"],
							displayLength: 20,
							"aoColumns": [
								/* 0-name */			null,
								/* 1-birth date */		{ dataSort: 2 },
								/* 2-BIRT:DATE */		{ visible: false },
								/* 3-marr details */	null,
								/* 4-death date */		{ dataSort: 5 },
								/* 5-DEAT:DATE */		{ visible: false },
							]
						});
						jQuery("#vital_records").css("visibility", "visible");
						jQuery(".loading-image").css("display", "none");
					');

				($name) ? $filter1             = '<p>' . /* I18N: A filter on the Vital records report page */ KT_I18N::translate('Names containing <span>%1s</span>', $name) . '</p>' : $filter1             = '';
				($place) ? $filter2            = '<p>' . /* I18N: A filter on the Vital records report page */ KT_I18N::translate('Place names containing <span>%1s</span>', $place) . '</p>' : $filter2            = '';
				($b_from && !$b_to) ? $filter3 = '<p>' . /* I18N: A filter on the Vital records report page */ KT_I18N::translate('Births from <span>%1s</span>', $b_from) . '</p>' : $filter3 = '';
				(!$b_from && $b_to) ? $filter4 = '<p>' . /* I18N: A filter on the Vital records report page */ KT_I18N::translate('Births to <span>%1s</span>', $b_to) . '</p>' : $filter4 = '';
				($b_from && $b_to) ? $filter5  = '<p>' . /* I18N: A filter on the Vital records report page */ KT_I18N::translate('Births between <span>%1s</span> and <span>%2s</span> ', $b_from, $b_to) . '</p>' : $filter5  = '';
				($d_from && !$d_to) ? $filter6 = '<p>' . /* I18N: A filter on the Vital records report page */ KT_I18N::translate('Deaths from <span>%1s</span>', $d_from) . '</p>' : $filter6 = '';
				(!$d_from && $d_to) ? $filter7 = '<p>' . /* I18N: A filter on the Vital records report page */ KT_I18N::translate('Deaths to <span>%1s</span>', $d_to) . '</p>' : $filter7 = '';
				($d_from && $d_to) ? $filter8  = '<p>' . /* I18N: A filter on the Vital records report page */ KT_I18N::translate('Deaths between <span>%1s</span> and <span>%2s</span> ', $d_from, $d_to) . '</p>' : $filter8  = '';

				$filter_list = $filter1 . $filter2 . $filter3 . $filter4 . $filter5 . $filter6 . $filter7 . $filter8;

				$list = report_vital_records($name, $place, $b_fromJD, $b_toJD, $d_fromJD, $d_toJD, KT_GED_ID);

				// output display
				if ($list) { ?>
					<div id="report_header">
						<h4><?php echo KT_I18N::translate('Listing individuals based on these filters'); ?></h4>
						<p><?php echo $filter_list; ?></p>
					</div>
					<div class="loading-image">&nbsp;</div>
					<table id="vital_records" class="width100" style="visibility:hidden;">
						<thead>
							<tr>
								<th><?php echo KT_I18N::translate('Name'); ?></th>
								<th><?php echo KT_I18N::translate('Birth'); ?></th>
								<th><?php //SORT_BIRT ?></th>
								<th><?php echo KT_I18N::translate('Marriage'); ?></th>
								<th><?php echo KT_I18N::translate('Death'); ?></th>
								<th><?php //SORT_DEAT ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($list as $person) {
								if ($person && $person->canDisplayDetails()) {
									$person->add_family_facts();
									$indifacts = $person->getIndiFacts();
									?>
									<tr>
										<td>
											<div>
												<p class="first">
													<a href="<?php echo $person->getHtmlUrl(); ?>"><?php echo $person->getFullName(); ?></a>
												</p>
												<?php if ($person->getPrimaryChildFamily() && $person->getPrimaryChildFamily()->getHusband()) { ?>
													<p>
														<?php echo KT_I18N::translate('Father') . ': ' . $person->getPrimaryChildFamily()->getHusband()->getLifespanName(); ?>
													</p>
												<?php }
												if ($person->getPrimaryChildFamily() && $person->getPrimaryChildFamily()->getWife()) { ?>
													<p>
														<?php echo KT_I18N::translate('Mother') . ': ' . $person->getPrimaryChildFamily()->getWife()->getLifespanName(); ?>
													</p>
												<?php } ?>
											</div>
										</td>
										<td>
											<div>
												<?php foreach ($indifacts as $fact) {
													if ($fact->getTag() == 'BIRT') { ?>
														<p class="first">
															<?php echo ($person->getBirthDate() ? KT_I18N::translate('Date') . ': ' . format_fact_date($fact, $person, true, true, false) . '<br>' : '') .
															($person->getBirthPlace() ? KT_I18N::translate('Place') . ': ' . format_fact_place($fact, true, true, true) : ''); ?>
														</p>
														<?php $ct = preg_match_all("/(2 SOUR (.+))/", $fact->getGedcomRecord(), $match, PREG_SET_ORDER);
														for ($j = 0; $j < $ct; $j++) {
															$sid    = trim($match[$j][2], '@');
															$source = KT_Source::getInstance($sid);
															if ($source->canDisplayDetails()) {
																echo '<p>' . KT_I18N::translate('Source') . ': ' . $source->getFullName() . '</p>';
															}
														}
													}
												}
												?>
											</div>
										</td>
										<td><?php echo $person->getBirthDate()->JD(); ?></td><!-- used for sorting only -->
										<td>
											<div>
												<?php foreach ($indifacts as $fact) {
													if ($fact->getParentObject() instanceof KT_Family && ($fact->getTag() == 'MARR' || $fact->getTag() == '_NMR')) {
														foreach ($fact->getParentObject() as $family_fact) {
															$sex = $person->getSex();
															switch ($sex) {
																case 'M':
																	$spouse = $fact->getParentObject()->getWife();
																	break;
																case 'F':
																	$spouse = $fact->getParentObject()->getHusband();
																	break;
																default:
																	$spouse = '';
																	break;
															} ?>
															<?php
															if ($spouse) { ?>
																<div>
																	<p class="first">
																		<?php echo KT_I18N::translate('Spouse'); ?>: <a href="<?php echo $spouse->getHtmlUrl(); ?>"><?php echo $spouse->getFullName(); ?></a>
																	</p>
																	<p class="first">
																		<?php echo ($fact->getParentObject()->getMarriageDate() ? KT_I18N::translate('Date') . ': ' . format_fact_date($fact, $spouse, true, true, false) . '<br>' : '') .
																		($fact->getParentObject()->getMarriagePlace() ? KT_I18N::translate('Place') . ': ' . format_fact_place($fact, true, true, true) : ''); ?>
																	</p>
																	<?php $ct = preg_match_all("/(2 SOUR (.+))/", $fact->getGedcomRecord(), $match, PREG_SET_ORDER);
																	for ($j = 0; $j < $ct; $j++) {
																		$sid    = trim($match[$j][2], '@');
																		$source = KT_Source::getInstance($sid);
																		if ($source->canDisplayDetails()) {
																			echo '<p>' . KT_I18N::translate('Source') . ': ' . $source->getFullName() . '</p>';
																		}
																	} ?>
																</div>
															<?php }
														}
													}
												} ?>
											</div>
										</td>
										<td>
											<div>
												<?php foreach ($indifacts as $fact) {
													if ($fact->getTag() == 'DEAT') { ?>
														<p class="first">
															<?php echo ($person->getDeathDate() ? KT_I18N::translate('Date') . ': ' . format_fact_date($fact, $person, true, true, false) . '<br>' : '') .
															($person->getDeathPlace() ? KT_I18N::translate('Place') . ': ' . format_fact_place($fact, true, true, true) : ''); ?>
														</p>
														<?php $ct = preg_match_all("/(2 SOUR (.+))/", $fact->getGedcomRecord(), $match, PREG_SET_ORDER);
														for ($j = 0; $j < $ct; $j++) {
															$sid    = trim($match[$j][2], '@');
															$source = KT_Source::getInstance($sid);
															if ($source->canDisplayDetails()) {
																echo '<p>' . KT_I18N::translate('Source') . ': ' . $source->getFullName() . '</p>';
															}
														}
													}
												}
												?>
											</div>
										</td>
										<td><?php echo $person->getDeathDate()->JD(); ?></td><!-- used for sorting only -->
									</tr>
								<?php }
							}
							?>
						</tbody>
					</table>
				<?php } else { ?>
					<div id="noresult">
						<?php echo KT_I18N::translate('Nothing found'); ?>
					</div>
				<?php }
			}
		}
}
