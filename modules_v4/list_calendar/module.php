<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class list_calendar_KT_Module extends KT_Module implements KT_Module_List {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Calendar');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the calendar module */ KT_I18N::translate('Calendar of events for day, month or year');
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

	// Implement KT_Module_List
	public function getListMenus() {
		global $controller;
		$menus = array();
		$menu  = new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
			'menu-list-calendar'
		);
		$menus[] = $menu;
		return $menus;
	}

	// Display list
	public function show() {
		global $controller, $HIDE_LIVE_PEOPLE, $CALENDAR_FORMAT, $WEEK_START, $males, $females, $iconStyle;
		require_once KT_ROOT.'includes/functions/functions_print_lists.php';

		$url = 'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;';

		$cal      = KT_Filter::get('cal', '@#D[A-Z ]+@');
		$day      = KT_Filter::get('day', '\d\d?');
		$month    = KT_Filter::get('month', '[A-Z]{3,5}');
		$year     = KT_Filter::get('year', '\d{1,4}(?: B\.C\.)?|\d\d\d\d\/\d\d|\d+(-\d+|[?]+)?');
		$view     = KT_Filter::get('view', 'day|month|year', 'day');
		$filterev = KT_Filter::get('filterev', '[_A-Z-]*', 'BIRT-MARR-DEAT');
		$filterof = KT_Filter::get('filterof', 'all|living|recent', 'all');
		$filtersx = KT_Filter::get('filtersx', '[MF]', '');

		if ($cal . $day . $month . $year == '') {
			// No date specified?  Use the most likely calendar
			switch (KT_LOCALE) {
			case 'fa': $cal = '@#DJALALI@';    break;
			case 'ar': $cal = '@#DHIJRI@';     break;
			case 'he': $cal = '@#DHEBREW@';    break;
			default:   $cal = '@#DGREGORIAN@'; break;
			}
		}

		// Create a Date_Calendar from the parameters

		// advance-year "year range"
		if ($year && preg_match('/^(\d+)-(\d+)$/', $year, $match)) {
			if (strlen($match[1]) > strlen($match[2])){
				$match[2] = substr($match[1], 0, strlen($match[1]) - strlen($match[2])) . $match[2];
			}
			$ged_date	= new KT_Date("FROM {$cal} {$match[1]} TO {$cal} {$match[2]}");
			$view		= 'year';
		} else
			// advanced-year "decade/century wildcard"
			if ($year && preg_match('/^(\d+)(\?+)$/', $year, $match)) {
				$y1				= $match[1] . str_replace('?', '0', $match[2]);
				$y2				= $match[1] . str_replace('?', '9', $match[2]);
				$ged_date	= new Date("FROM {$cal} {$y1} TO {$cal} {$y2}");
				$view		= 'year';
			} else {
				if ($year < 0) {
					$year = (-$year) . "B.C."; // need BC to parse date
				}
				$ged_date	= new KT_Date("{$cal} {$day} {$month} {$year}");
				$year			= $ged_date->date1->y; // need negative year for year entry field.
			}
		$cal_date = &$ged_date->date1;

		// Invalid month?  Pick a sensible one.
		if ($cal_date instanceof KT_Date_Jewish && $cal_date->m==7 && $cal_date->y!=0 && !$cal_date->IsLeapYear())
			$cal_date->m = 6;

		// Fill in any missing bits with todays date
		$today=$cal_date->Today();
		if ($cal_date->d == 0) $cal_date->d = $today->d;
		if ($cal_date->m == 0) $cal_date->m = $today->m;
		if ($cal_date->y == 0) $cal_date->y = $today->y;
		$cal_date->SetJDfromYMD();
		if ($year == 0)
			$year = $cal_date->y;

		// Extract values from date
		$days_in_month	= $cal_date->DaysInMonth();
		$days_in_week	= $cal_date->DaysInWeek();
		$cal_month		= $cal_date->Format('%O');
		$today_month	= $today->Format('%O');

		// Invalid dates?  Go to monthly view, where they'll be found.
		if ($cal_date->d > $days_in_month && $view == 'today'){
			$view = 'month';
		}

		// Convert event filter option to a list of gedcom event codes
		if ($filterev == 'ALL') {
			$events = '';
		} else {
			if ($filterev == 'BDM') {
				$events = 'BIRT MARR DEAT';
			} else {
				$events = $filterev;
			}
		}

		// Set active tab based on view parameter from url
		$active = 0;
		if ($view == 'month') {$active = 1;}
		if ($view == 'year')  {$active = 2;}

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_calendar', KT_USER_ACCESS_LEVEL))
			->setPageTitle(KT_I18N::translate('Anniversary calendar'))
			->pageHeader();

		// Calendar form
		?>
		<div id="calendar-page" class="grid-x grid-padding-x">
			<div class="cell large-10 large-offset-1">
				<h3><?php echo $controller->getPageTitle(); ?></h3>
				<form name="dateform" method="get" action="?">
					<input type="hidden" name="mod" value="<?php echo $this->getName(); ?>">
					<input type="hidden" name="mod_action" value="show">
					<input type="hidden" name="cal" value="<?php echo $cal; ?>">
					<input type="hidden" name="day" value="<?php echo $cal_date->d; ?>">
					<input type="hidden" name="month" value="<?php echo $cal_month; ?>">
					<input type="hidden" name="year" value="<?php echo $cal_date->y; ?>">
					<input type="hidden" name="action" value="<?php echo $view; ?>">
					<input type="hidden" name="filterev" value="<?php echo $filterev; ?>">
					<input type="hidden" name="filtersx" value="<?php echo $filtersx; ?>">
					<input type="hidden" name="filterof" value="<?php echo $filterof; ?>">

					<!-- All further uses of $cal are to generate URLs -->
					<?php $cal = rawurlencode((string) $cal); ?>

					<table class="unstriped">
						<!-- Day selector-->
						<tr>
							<th class="h6 cal-label"><?php echo KT_I18N::translate('Day'); ?></th>
							<td class="cal-input">
								<?php
								for ($d = 1; $d <= $days_in_month; $d++) {
									// Format the day number using the calendar
									$tmp	= new KT_Date($cal_date->Format("%@ {$d} %O %E"));
									$d_fmt	= $tmp->date1->Format('%j');
									if ($d == $cal_date->d) { ?>
										<span class="error"><?php echo $d_fmt; ?></span>
									<?php } else { ?>
										<a href="<?php echo $url; ?>cal=<?php echo $cal; ?>&amp;day=<?php echo $d; ?>&amp;month=<?php echo $cal_month; ?>&amp;year=<?php echo $cal_date->y; ?>&amp;filterev=<?php echo $filterev; ?>&amp;filterof=<?php echo $filterof; ?>&amp;filtersx=<?php echo $filtersx; ?>&amp;action=<?php echo $view; ?>"><?php echo $d_fmt; ?></a>
									<?php } ?>
										 |
								<?php }
								$tmp = new KT_Date($today->Format('%@ %A %O %E')); // Need a Date object to get localisation ?>
								<a href="<?php echo $url; ?>cal=<?php echo $cal; ?>&amp;day=<?php echo $today->d; ?>&amp;month=<?php echo $today_month; ?>&amp;year=<?php echo $today->y; ?>&amp;filterev=<?php echo $filterev; ?>&amp;filterof=<?php echo $filterof; ?>&amp;filtersx=<?php echo $filtersx; ?>&amp;action=<?php echo $view; ?>"><b><?php echo $tmp->Display(false, NULL, array()); ?></b></a>
							</td>
						</tr>
						<!-- Month selector -->
						<tr>
							<th class="h6 cal-label"><?php echo KT_I18N::translate('Month'); ?></th>
							<td class="cal-input">
								<?php
								for ($n = 1; $n <= $cal_date->NUM_MONTHS(); ++$n) {
									$month_name = $cal_date->NUM_TO_MONTH_NOMINATIVE($n, $cal_date->IsLeapYear());
									$m			= $cal_date->NUM_TO_GEDCOM_MONTH($n, $cal_date->IsLeapYear());
									if ($m == 'ADS' && $cal_date instanceof Date_Jewish && !$cal_date->IsLeapYear()) {
										// No month 7 in Jewish leap years.
										continue;
									}
									if ($n == $cal_date->m) {
										$month_name = '<span class="error">' . $month_name . '</span>';
									} ?>
									<a href="
										<?php echo $url; ?>cal=<?php echo $cal; ?>
										&amp;day=<?php echo $cal_date->d; ?>
										&amp;month=<?php echo $m; ?>
										&amp;year=<?php echo $cal_date->y; ?>
										&amp;filterev=<?php echo $filterev; ?>
										&amp;filterof=<?php echo $filterof; ?>
										&amp;filtersx=<?php echo $filtersx; ?>
										&amp;action=<?php echo $view; ?>"
									>
										<?php echo $month_name; ?>
									</a>
									 |
								<?php } ?>
								<a href="
									<?php echo $url; ?>cal=<?php echo $cal; ?>
									&amp;day=<?php echo min($cal_date->d, $today->DaysInMonth()); ?>
									&amp;month=<?php echo $today_month; ?>
									&amp;year=<?php echo $today->y; ?>
									&amp;filterev=<?php echo $filterev; ?>
									&amp;filterof=<?php echo $filterof; ?>
									&amp;filtersx=<?php echo $filtersx; ?>
									&amp;action=<?php echo $view; ?>"
								>
									<b><?php echo $today->Format('%F %Y'); ?></b>
								</a>
							</td>
						</tr>
						<!-- Year selector -->
						<tr>
							<th class="h6 cal-label"><?php echo KT_I18N::translate('Year'); ?></th>
							<td class="cal-input">
									<a href="
										<?php echo $url; ?>?cal=<?php echo $cal ?>
										&amp;day=<?php echo $cal_date->d ?>
										&amp;month=<?php echo $cal_month ?>
										&amp;year=<?php echo $cal_date->y === 1 ? -1 : $cal_date->y - 1 ?>
										&amp;filterev=<?php echo $filterev ?>
										&amp;filterof=<?php echo $filterof ?>
										&amp;filtersx=<?php echo $filtersx ?>
										&amp;view=<?php echo $view ?>"
									>
										-1
									</a>
									<input type="text" id="year" name="year" value="<?php echo $year ?>">
									<a href="
										<?php echo $url; ?>?cal=<?php echo $cal ?>
										&amp;day=<?php echo $cal_date->d ?>
										&amp;month=<?php echo $cal_month ?>
										&amp;year=<?php echo $cal_date->y === -1 ? 1 : $cal_date->y + 1 ?>
										&amp;filterev=<?php echo $filterev ?>
										&amp;filterof=<?php echo $filterof ?>
										&amp;filtersx=<?php echo $filtersx ?>
										&amp;view=<?php echo $view ?>"
									>
										+1
									</a>
									|
									<a href="
										<?php echo $url; ?>?cal=<?php echo $cal ?>
										&amp;day=<?php echo $cal_date->d ?>
										&amp;month=<?php echo $cal_month ?>
										&amp;year=<?php echo $today->y ?>
										&amp;filterev=<?php echo $filterev ?>
										&amp;filterof=<?php echo $filterof ?>
										&amp;filtersx=<?php echo $filtersx ?>
										&amp;view=<?php echo $view ?>"
									>
										<?php echo $today->format('%Y') ?>
									</a>
									<?php echo help_link('annivers_year_select'); ?>
							</td>
						</tr>
						<!-- Filtering options -->
						<tr>
							<th class="h6 cal-label"><?php echo KT_I18N::translate('Show'); ?></th>
							<td class="cal-input">
								<select class="list_value" name="filterof" onchange="document.dateform.submit();">
									<option value="all"
										<?php if ($filterof == "all") { ?>
											  selected="selected"
										<?php } ?>
										><?php echo KT_I18N::translate('All People'); ?>
									</option>
									<?php if (!$HIDE_LIVE_PEOPLE || KT_USER_ID) { ?>
										<option value="living"
											<?php if ($filterof == "living") { ?>
										  	 selected="selected"
											<?php } ?>
										>
											<?php echo KT_I18N::translate('Living People'); ?>
										</option>
									<?php } ?>
									<option value="recent"
										<?php if ($filterof == "recent") { ?>
											 selected="selected"
										<?php } ?>
										><?php echo KT_I18N::translate('Recent Years (&lt; 100 yrs)'); ?>
									</option>
								</select>
								&nbsp;&nbsp;&nbsp;
								<?php if ($filtersx == "") { ?>
									<span class="fa-layers fa-fw" title="<?php echo KT_I18N::translate('All People'); ?>">
										<i class="fal fa-circle-notch fa-2x" data-fa-transform="left-3"></i>
										<i class="<?php echo $iconStyle; ?> fa-users"></i>
									</span>
								<?php } else { ?>
									<a href="
										<?php echo $url; ?>cal=<?php echo $cal; ?>
										&amp;day=<?php echo $cal_date->d; ?>
										&amp;month=<?php echo $cal_month; ?>
										&amp;year=<?php echo $cal_date->y; ?>
										&amp;filterev=<?php echo $filterev; ?>
										&amp;filterof=<?php echo $filterof; ?>
										&amp;action=<?php echo $view; ?>
									">
										<i class="<?php echo $iconStyle; ?> fa-users" title="<?php echo KT_I18N::translate('All People'); ?>"></i>
									</a>
								<?php }
								if ($filtersx == "M") { ?>
									<span class="fa-layers fa-fw" title="<?php echo KT_I18N::translate('Males'); ?>">
										<i class="fal fa-circle-notch fa-2x" data-fa-transform="left-3"></i>
										<i class="<?php echo $iconStyle; ?> fa-male"></i>
									</span>
								<?php } else { ?>
									<a href="
										<?php echo $url; ?>cal=<?php echo $cal; ?>
										&amp;day=<?php echo $cal_date->d; ?>
										&amp;month=<?php echo $cal_month; ?>
										&amp;year=<?php echo $cal_date->y; ?>
										&amp;filterev=<?php echo $filterev; ?>
										&amp;filterof=<?php echo $filterof; ?>
										&amp;filtersx=M
										&amp;action=<?php echo $view; ?>
									">
										<i class="<?php echo $iconStyle; ?> fa-male" title="<?php echo KT_I18N::translate('Males'); ?>"></i>
								 	</a>
								<?php }
								if ($filtersx == "F") { ?>
									<span class="fa-layers fa-fw" title="<?php echo KT_I18N::translate('Females'); ?>">
										<i class="fal fa-circle-notch fa-2x" data-fa-transform="left-3"></i>
										<i class="<?php echo $iconStyle; ?> fa-female"></i>
									</span>
								<?php } else { ?>
									<a href="
										<?php echo $url; ?>cal=<?php echo $cal; ?>
										&amp;day=<?php echo $cal_date->d; ?>
										&amp;month=<?php echo $cal_month; ?>
										&amp;year=<?php echo $cal_date->y; ?>
										&amp;filterev=<?php echo $filterev; ?>
										&amp;filterof=<?php echo $filterof; ?>
										&amp;filtersx=F
										&amp;action=<?php echo $view; ?>
									">
										<i class="<?php echo $iconStyle; ?> fa-female" title="<?php echo KT_I18N::translate('Females'); ?>"></i>
									</a>
								<?php } ?>
								&nbsp;&nbsp;&nbsp;
								<input type="hidden" name="filterev" value="<?php echo $filterev; ?>">
								<select class="list_value" name="filterev" onchange="document.dateform.submit();">
									<option value="BDM" <?php echo $filterev === 'BDM' ? 'selected' : ''; ?>>
										<?php echo KT_I18N::translate('Vital records'); ?>
									</option>
									<option value="ALL" <?php echo $filterev === 'ALL' ? 'selected' : ''; ?>>
										<?php echo KT_I18N::translate('All'); ?>
									</option>
									<option value="BIRT" <?php echo $filterev === 'BIRT' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('BIRT'); ?>
									</option>
									<option value="CHR" <?php echo $filterev === 'CHR' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('CHR'); ?>
									</option>
									<option value="CHRA" <?php echo $filterev === 'CHRA' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('CHRA'); ?>
									</option>
									<option value="BAPM" <?php echo $filterev === 'BAPM' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('BAPM'); ?>
									</option>
									<option value="_COML" <?php echo $filterev === '_COML' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('_COML'); ?>
									</option>
									<option value="MARR" <?php echo $filterev === 'MARR' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('MARR'); ?>
									</option>
									<option value="_SEPR" <?php echo $filterev === '_SEPR' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('_SEPR'); ?>
									</option>
									<option value="DIV" <?php echo $filterev === 'DIV' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('DIV'); ?>
									</option>
									<option value="DEAT" <?php echo $filterev === 'DEAT' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('DEAT'); ?>
									</option>
									<option value="BURI" <?php echo $filterev === 'BURI' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('BURI'); ?>
									</option>
									<option value="IMMI" <?php echo $filterev === 'IMMI' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('IMMI'); ?>
									</option>
									<option value="EMIG" <?php echo $filterev === 'EMIG' ? 'selected' : ''; ?>>
										<?php echo KT_Gedcom_Tag::getLabel('EMIG'); ?>
									</option>
									<option value="EVEN" <?php echo $filterev === 'EVEN' ? 'selected' : ''; ?>>
										<?php echo KT_I18N::translate('Custom Event'); ?>
									</option>
								</select>
							</td>
						</tr>
						<!-- Calendar selector -->
						<tr class="cal-selectors">
							<th class="cal-label"><?php echo KT_I18N::translate('Calendar'); ?></label>
							<td class="cal-input">
								<?php
								$n=0;
								foreach (array(
										'gregorian'=> KT_Date_Gregorian::calendarName(),
										'julian'   => KT_Date_Julian::calendarName(),
										'jewish'   => KT_Date_Jewish::calendarName(),
										'french'   => KT_Date_French::calendarName(),
										'hijri'    => KT_Date_Hijri::calendarName(),
										'jalali'   => KT_Date_Jalali::calendarName(),
									) as $newcal=>$cal_name) {
									$tmp = $cal_date->convert_to_cal($newcal);
									if ($tmp->InValidRange()) {
										if ($n++) {
											echo ' | ';
										}
										if (get_class($tmp) == get_class($cal_date)) { ?>
											<span class="error"><?php echo $cal_name; ?></span>
										<?php } else {
											$newcalesc	= urlencode($tmp->Format('%@'));
											$tmpmonth		= $tmp->FormatGedcomMonth(); ?>
											<a href="<?php echo $url; ?>cal=<?php echo $newcalesc; ?>&amp;day=<?php echo $tmp->d; ?>&amp;month=<?php echo $tmpmonth; ?>&amp;year=<?php echo $tmp->y; ?>&amp;filterev=<?php echo $filterev; ?>&amp;filterof=<?php echo $filterof; ?>&amp;filtersx=<?php echo $filtersx; ?>&amp;action=<?php echo $view; ?>"><?php echo $cal_name; ?></a>
									<?php }
									}
								} ?>
							</td>
						</tr>
					</table>
				</form>
				<hr style="clear:both;">
				<!-- end of form -->

				<?php
				// Fetch data for day/month/year views
				$found_facts	= array();
				$numfams		= 0;
				?>

				<div id="cal-tabs">
					<ul class="tabs" data-deep-link="true" data-deep-link-smudge="true" data-deep-link-smudge-delay="600" data-tabs id="calendar_tabs">
						<li class="tabs-title is-active"><a href="#cal_day"><span><?php echo KT_I18N::translate('Day'); ?></span></a></li>
						<li class="tabs-title"><a href="#cal_month"><span><?php echo KT_I18N::translate('Month'); ?></span></a></li>
						<li class="tabs-title"><a href="#cal_year"><span><?php echo KT_I18N::translate('Year'); ?></span></a></li>
					</ul>
					<div class="tabs-content" data-tabs-content="calendar_tabs">
						<div class="tabs-panel is-active" id="cal_day">
							<h5 class="text-center"><?php echo KT_I18N::translate('On this day') . '&nbsp;' . $ged_date->Display(false); ?></h5>
							<?php
							$found_facts = $this->apply_filter(get_anniversary_events($cal_date->minJD, $events), $filterof, $filtersx);
							$indis	= array();
							$fams	= array();
							foreach ($found_facts as $fact) {
								$fact_text = $this->calendar_fact_text($fact, true);
								switch ($fact['objtype']) {
								case 'INDI':
									if (empty($indis[$fact['id']]))
										$indis[$fact['id']] = $fact_text;
									else
										$indis[$fact['id']] .= '<br>' . $fact_text;
									break;
								case 'FAM':
									if (empty($fams[$fact['id']]))
										$fams[$fact['id']] = $fact_text;
									else
										$fams[$fact['id']] .= '<br>' . $fact_text;
									break;
								}
							}
							?>
							<table class="unstriped">
								<tr>
									<!-- Table headings -->
									<th class="text-left"><i class="<?php echo $iconStyle; ?> fa-user"></i><?php echo KT_I18N::translate('Individuals'); ?></th>
									<th class="text-left"><i class="<?php echo $iconStyle; ?> fa-users"></i><?php echo KT_I18N::translate('Families'); ?></th>
								</tr>
								<tr>
									<!-- Table rows -->
									<td>
										<!-- Avoid an empty unordered list -->
										<?php
										ob_start();
										echo $this->calendar_list_text($indis, '<li>', '</li>', true);
										$content = ob_get_clean();
										if (!empty($content)) {
											echo '<ul>', $content, '</ul>';
										} ?>
									</td>
									<td width="48%">
										<!-- Avoid an empty unordered list -->
										<?php
										ob_start();
										echo $this->calendar_list_text($fams, "<li>", "</li>", true);
										$content = ob_get_clean();
										if (!empty($content)) {
											echo '<ul>', $content, '</ul>';
										} ?>
									</td>
								</tr>
								<tr>
									<!-- Table footers -->
									<th>
										<?php echo KT_I18N::translate('Total individuals: %s', count($indis)); ?>
										<br>
										<i class="<?php echo $iconStyle; ?> fa-lg fa-male" title="<?php echo KT_I18N::translate('Males'); ?>"></i><?php echo $males; ?> &nbsp;&nbsp;&nbsp;&nbsp;
										<i class="<?php echo $iconStyle; ?> fa-lg fa-female" title="<?php echo KT_I18N::translate('Females'); ?>"></i><?php echo $females; ?> &nbsp;&nbsp;&nbsp;&nbsp;
										<?php if (count($indis) != $males + $females) { ?>
											<i class="<?php echo $iconStyle; ?> fa-lg fa-genderless" title="<?php echo KT_I18N::translate('All People'); ?>"></i><?php echo count($indis) - $males - $females; ?>
										<?php } ?>
									</th>
									<th><?php echo KT_I18N::translate('Total families: %s', count($fams)); ?></th>
								</tr>
							</table>
						</div>
						<div class="tabs-panel" id="cal_month">
							<h5 class=text-center><?php echo KT_I18N::translate('In this month').'&nbsp;'.$ged_date->Display(false, '%F %Y'); ?></h5>
							<?php
							$cal_date->d = 0;
							$cal_date->SetJDfromYMD();
							// Make a separate list for each day.  Unspecified/invalid days go in day 0.
							$found_facts = array();
							for ($d = 0; $d <= $days_in_month; ++$d) {
								$found_facts[$d] = array();
							}
							// Fetch events for each day
							for ($jd = $cal_date->minJD; $jd <= $cal_date->maxJD; ++$jd){
								foreach ($this->apply_filter(get_anniversary_events($jd, $events), $filterof, $filtersx) as $event) {
									$tmp = $event['date']->MinDate();
									if ($tmp->d >= 1 && $tmp->y && $tmp->d <= $tmp->DaysInMonth()) {
										$d = $jd - $cal_date->minJD + 1;
									} else {
										$d = 0;
									}
									$found_facts[$d][] = $event;
								}
							}
							$cal_facts = array();
							foreach ($found_facts as $d => $facts) {
								$cal_facts[$d] = array();
								foreach ($facts as $fact) {
									$id = $fact['id'];
									if (empty($cal_facts[$d][$id]))
										$cal_facts[$d][$id] = $this->calendar_fact_text($fact, false);
									else
										$cal_facts[$d][$id] .= '<br>' . $this->calendar_fact_text($fact, false);
								}
							}
							// We use JD%7 = 0/Mon...6/Sun.  Config files use 0/Sun...6/Sat.  Add 6 to convert.
							$week_start = ($WEEK_START + 6) % $days_in_week;
							// The french  calendar has a 10-day week, but our config only lets us choose
							// mon-sun as a start day.  Force french calendars to start on primidi
							if ($days_in_week == 10) {
								$week_start = 0;
							} ?>
							<table>
							  <tr>
									<?php for ($week_day = 0; $week_day < $days_in_week; ++$week_day) {
							      $day_name = $cal_date->LONG_DAYS_OF_WEEK(($week_day + $week_start) % $days_in_week); ?>
							      <td width="<?php echo (100 / $days_in_week); ?>%"><?php echo $day_name; ?></td>
							    <?php } ?>
							  </tr>
								<?php // Print days 1-n of the month...
								// ...but extend to cover "empty" days before/after the month to make whole weeks.
							  // e.g. instead of 1 -> 30 (=30 days), we might have -1 -> 33 (=35 days)
								$start_d	= 1 - ($cal_date->minJD-$week_start) % $days_in_week;
								$end_d		= $days_in_month + ($days_in_week - ($cal_date->maxJD-$week_start + 1) % $days_in_week) % $days_in_week;
								// Make sure that there is an empty box for any leap/missing days
								if ($start_d === 1 && $end_d === $days_in_month && count($found_facts[0]) > 0) {
									$end_d += $days_in_week;
								}
								for ($d = $start_d; $d <= $end_d; ++$d) {
							    if (($d + $cal_date->minJD - $week_start) % $days_in_week === 1) {?>
							      <tr>
							    <?php } ?>
										<td>
							        <?php
											if ($d < 1 || $d > $days_in_month) {
							          if (count($cal_facts[0]) > 0) { ?>
							            <span class="cal_day"><?php echo KT_I18N::translate('Day not set'); ?></span>
							            <hr class="clearfix">
							            <div class="cal-month-days">
							              <?php echo $this->calendar_list_text($cal_facts[0], '', '', false); ?>
							            </div>
							            <?php
							            $cal_facts[0] = array();
							          }
							        } else {
										$tmp	= new KT_Date($cal_date->Format("%@ {$d} %O %E"));
										$d_fmt	= $tmp->date1->Format('%j');
							          if ($d === $today->d && $cal_date->m === $today->m) { ?>
							            <span class="cal_day current_day"><?php echo $d_fmt; ?></span>
							          <?php } else { ?>
							            <span class="cal_day"><?php echo $d_fmt; ?></span>
							          <?php }
							          // Show a converted date
									foreach (explode('_and_', $CALENDAR_FORMAT) as $convcal) {
							            $alt_date = $cal_date->convert_to_cal($convcal);
							            if (get_class($alt_date) != get_class($cal_date)) {
											[$alt_date->y, $alt_date->m, $alt_date->d] = $alt_date->JDtoYMD($cal_date->minJD + $d - 1);
											$alt_date->SetJDfromYMD(); ?>
											<span class="rtl_cal_day"><?php echo $alt_date->Format("%j %M"); ?></span>
											<?php break;
							            }
							        } ?>
									<hr class="clearfix">
							        <div class="cal-month-days">
							            <?php echo $this->calendar_list_text($cal_facts[$d], '', '', false); ?>
							        </div>
							        <?php } ?>
							      </td>
							    <?php if (($d + $cal_date->minJD - $week_start) % $days_in_week == 0) { ?>
							      </tr>
							    <?php }
								} ?>
							</table>
						</div>
						<div class="tabs-panel" id="cal_year">
							<h5 class=text-center><?php echo KT_I18N::translate('In this year') . '&nbsp;' . $ged_date->Display(false, '%Y'); ?></h5>
							<?php
							$cal_date->m = 0;
							$cal_date->setJdFromYmd();
							$found_facts = array();
							$found_facts = $this->apply_filter(get_calendar_events($ged_date->MinJD(), $ged_date->MaxJD(), $events), $filterof, $filtersx);
							// Eliminate duplicates (e.g. BET JUL 1900 AND SEP 1900 will appear twice in 1900)
							foreach ($found_facts as $key=>$value) {
								$found_facts[$key] = serialize($found_facts[$key]);
							}
							$found_facts = array_unique($found_facts);
							foreach ($found_facts as $key=>$value){
								$found_facts[$key] = unserialize($found_facts[$key]);
							}
							$indis	= array();
							$fams		= array();
							foreach ($found_facts as $fact) {
								$fact_text = $this->calendar_fact_text($fact, true);
								switch ($fact['objtype']) {
								case 'INDI':
									if (empty($indis[$fact['id']]))
										$indis[$fact['id']] = $fact_text;
									else
										$indis[$fact['id']] .= '<br>' . $fact_text;
									break;
								case 'FAM':
									if (empty($fams[$fact['id']]))
										$fams[$fact['id']] = $fact_text;
									else
										$fams[$fact['id']] .= '<br>' . $fact_text;
									break;
								}
							} ?>
							<table class="unstripe">
								<tr>
									<!-- Table headings -->
									<td><i class="<?php echo $iconStyle; ?> fa-user"></i><?php echo KT_I18N::translate('Individuals'); ?></td>
									<td><i class="<?php echo $iconStyle; ?> fa-users"></i><?php echo KT_I18N::translate('Families'); ?></td>
								</tr>
								<tr>
									<!-- Table rows -->
									<?php
									$males		= 0;
									$females	= 0;
									$numfams	= 0;
									?>
									<td width="48%">
										<!-- Avoid an empty unordered list -->
										<?php
										ob_start();
										echo $this->calendar_list_text($indis, '<li>', '</li>', true);
										$content = ob_get_clean();
										if (!empty($content)) {
											echo '<ul>', $content, '</ul>';
										} ?>
									</td>
									<td>
										<!-- Avoid an empty unordered list -->
										<?php
										ob_start();
										echo $this->calendar_list_text($fams, "<li>", "</li>", true);
										$content = ob_get_clean();
										if (!empty($content)) {
											echo '<ul>', $content, '</ul>';
										} ?>
									</td>
								</tr>
								<tr>
									<!-- Table footers -->
									<th>
										<?php echo KT_I18N::translate('Total individuals: %s', count($indis)); ?>
										<br>
										<i class="<?php echo $iconStyle; ?> fa-lg fa-male" title="<?php echo KT_I18N::translate('Males'); ?>"></i><?php echo $males; ?> &nbsp;&nbsp;&nbsp;&nbsp;
										<i class="<?php echo $iconStyle; ?> fa-lg fa-female" title="<?php echo KT_I18N::translate('Females'); ?>"></i><?php echo $females; ?> &nbsp;&nbsp;&nbsp;&nbsp;
										<?php if (count($indis) != $males + $females) { ?>
											<i class="<?php echo $iconStyle; ?> fa-lg fa-user" title="<?php echo KT_I18N::translate('All People'); ?>"></i><?php echo count($indis) - $males - $females; ?>
										<?php } ?>
									</th>
									<th><?php echo KT_I18N::translate('Total families: %s', count($fams)); ?></th>
								</tr>
							</table>
						</div>
					</div>
				</div> <!-- close "cal-tabs" -->
			</div> <!-- close "calendar-page" -->
		</div>
		<?php
	}

	/////////////////////////////////////////////////////////////////////////////////
	// Filter a list of facts
	/////////////////////////////////////////////////////////////////////////////////
	public function apply_filter($facts, $filterof, $filtersx) {
		$filtered		= array();
		$hundred_years	= KT_CLIENT_JD - 36525;
		foreach ($facts as $fact) {
			$tmp = KT_GedcomRecord::GetInstance($fact['id']);
			// Filter on sex
			if ($fact['objtype'] == 'INDI' && $filtersx != '' && $filtersx != $tmp->getSex())
				continue;
			// Can't display families if the sex filter is on.
			// TODO: but we could show same-sex partnerships....
			if ($fact['objtype'] == 'FAM' && $filtersx != '')
				continue;
			// Filter on age of event
			if ($filterof == 'living') {
				if ($fact['objtype'] == 'INDI' && $tmp->isDead())
				continue;
				if ($fact['objtype'] == 'FAM') {
					$husb = $tmp->getHusband();
					$wife = $tmp->getWife();
					if (!empty($husb) && $husb->isDead())
						continue;
					if (!empty($wife) && $wife->isDead())
						continue;
				}
			}
			if ($filterof == 'recent' && $fact['date']->MaxJD() < $hundred_years)
				continue;
			// Finally, check for privacy rules before adding fact.
			if ($tmp->canDisplayDetails())
				$filtered[] = $fact;
		}
		return $filtered;
	}

	////////////////////////////////////////////////////////////////////////////////
	// Format a fact for display.  Include the date, the event type, and optionally
	// the place.
	////////////////////////////////////////////////////////////////////////////////
	public function calendar_fact_text($fact, $show_places) {
		$text = KT_Gedcom_Tag::getLabel($fact['fact']) . ' - ' . $fact['date']->Display(true, "", array());
		if ($fact['anniv']>0)
			$text .=' (' . KT_I18N::plural('%s year', '%s years', $fact['anniv'], $fact['anniv']).')';
		if ($show_places && !empty($fact['plac']))
			$text .=' - ' . $fact['plac'];
		return $text;
	}

	////////////////////////////////////////////////////////////////////////////////
	// Format a list of facts for display
	////////////////////////////////////////////////////////////////////////////////
	public function calendar_list_text($list, $tag1, $tag2, $show_sex_symbols) {
		global $males, $females;

		foreach ($list as $id => $facts) {
			$tmp = KT_GedcomRecord::GetInstance($id);
			echo $tag1, '<a href="' . $tmp->getHtmlUrl() . '">' . $tmp->getFullName() . '</a> ';
			if ($show_sex_symbols && $tmp->getType() == 'INDI')
				switch ($tmp->getSex()) {
				case 'M':
					echo '<i class="<?php echo $iconStyle; ?> fa-male" title="' . KT_I18N::translate('Male') . '"></i>';
					++ $males;
					break;
				case 'F':
					echo '<i class="<?php echo $iconStyle; ?> fa-female" title="' . KT_I18N::translate('Female') . '"></i>';
					++ $females;
					break;
				default:
					echo '<i class="<?php echo $iconStyle; ?> fa-genderless" title="' .  KT_I18N::translate_c('unknown gender', 'Unknown'), '"></i>';
					break;
				}
				echo '<div class="indent">' . $facts . '</div>' . $tag2;
		}
	}

}
