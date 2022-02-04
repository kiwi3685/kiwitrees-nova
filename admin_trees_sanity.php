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
		KT_I18N::translate('Duplicated individual data'),
		KT_I18N::translate('Missing or invalid data'),
		KT_I18N::translate('Duplicated family data'),
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
		array (3, 'dupe_birt',		KT_I18N::translate('Birth')),
		array (3, 'dupe_bapm',		KT_I18N::translate('Baptism or christening')),
		array (3, 'dupe_deat',		KT_I18N::translate('Death')),
		array (3, 'dupe_crem',		KT_I18N::translate('Cremation')),
		array (3, 'dupe_buri',		KT_I18N::translate('Burial')),
		array (3, 'dupe_sex',		KT_I18N::translate('Gender')),
		array (3, 'dupe_name',		KT_I18N::translate('Name')),
		array (4, 'birt',			KT_I18N::translate('Missing or incomplete birth')),
		array (4, 'deat',			KT_I18N::translate('Missing or incomplete death')),
		array (4, 'sex',			KT_I18N::translate('No gender recorded')),
		array (4, 'age',			KT_I18N::translate('Invalid age recorded')),
		array (4, 'empty_tag',		KT_I18N::translate('Empty individual fact or event') . '<span class="alert">**</span>'),
		array (4, 'child_order',	KT_I18N::translate('Children not sorted by birth date')),
		array (4, 'fam_order',		KT_I18N::translate('Families not sorted by marriage date')),
		array (5, 'dupe_marr',		KT_I18N::translate('Marriage')),
		array (5, 'dupe_child',		KT_I18N::translate('Families with duplicately named children')),
	);

?>

<div id="sanity_check" class="cell">
	<a class="current faq_link" href="<?php echo KT_KIWITREES_URL; ?>/faqs/general/sanity-check/" target="_blank" rel="noopener noreferrer" title="<?php echo KT_I18N::translate('View FAQ for this page.'); ?>"><?php echo KT_I18N::translate('View FAQ for this page.'); ?><i class="<?php echo $iconStyle; ?> fa-comment-dots"></i></a>
	<h3><?php echo $controller->getPageTitle(); ?></h3>
	<h5><?php echo KT_I18N::translate('%s checks to help you monitor the quality of your family history data', count($checks)); ?></h5>
	<div class="callout warning">
		<?php echo KT_I18N::translate('This process can be slow. If you have a large family tree or suspect large numbers of errors you should only select a few checks each time.<br><br>Options marked <span class="alert">**</span> are often very slow.'); ?>
	</div>
	<div class="grid-x grid-padding-x">
		<form class="cell" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
			<input type="hidden" name="save" value="1">
			<div class="grid-x">
				<div class="cell medium-3">
					<label><?php echo KT_I18N::translate('Family tree'); ?></label>
					<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM); ?>
				</div>
			</div>
			<div class="grid-x grid-padding-x">
				<?php for ($i = 1; $i < count($checkGroups) + 1; $i ++) { ?>
						<div class="cell medium-2 large-3">
							<h5><?php echo $checkGroups[$i-1]; ?></h5>
							<ul>
								<?php for ($row = 0; $row < count($checks); $row ++) {
									if ($checks[$row][0] == $i) { ?>
										<li class="listStyleNone" name="<?php echo $checks[$row][1]; ?>" id="<?php echo $checks[$row][1]; ?>">
											<input type="checkbox" name="<?php echo $checks[$row][1]; ?>" value="<?php echo $checks[$row][1]; ?>"
												<?php if (KT_Filter::post($checks[$row][1])) echo ' checked="checked"'?>
											>
											<?php echo $checks[$row][2];
											if (isset($checks[$row][3])) { ?>
												<input name="<?php echo $checks[$row][3]; ?>" id="<?php echo $checks[$row][4]; ?>" type="text" value="<?php echo $checks[$row][5]; ?>" >
											<?php } ?>
									 	</li>
									<?php }
								} ?>
							</ul>
						</div>
				<?php } ?>
			</div>
			<div class="grid-x">
				<div class="cell small-6 medium-1">
					<button type="submit" class="button" >
						<i class="<?php echo $iconStyle; ?> fa-check"></i>
						<?php echo KT_I18N::translate('Check'); ?>
					</button>
				</div>
			</div>
		</form>
		<form class="cell" method="post" name="rela_form" action="#">
			<input type="hidden" name="reset" value="1">
			<div class="grid-x">
				<div class="cell small-6 medium-1">
					<button type="submit" class="button">
						<i class="<?php echo $iconStyle; ?> fa-sync"></i>
						<?php echo KT_I18N::translate('Reset'); ?>
					</button>
				</div>
			</div>
			<hr>
		</form>
	</div>
	<?php if (KT_Filter::post('save')) {?>
		<div class="loading-image"></div>
		<div class="accordion" data-accordion data-multi-expand="true" data-allow-all-closed="true">
			<?php
			if (KT_Filter::post('baptised')) {
				$data = birth_comparisons(array('BAPM', 'CHR'));
				echo '
				<div class="accordion-item" data-accordion-item>
					<a href="#" class="accordion-title">' . KT_I18N::translate('%s born after baptism or christening', $data['count']) . '
						<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
					</a>
					<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
				</div>';
			}
			if (KT_Filter::post('died')) {
				$data = birth_comparisons(array('DEAT'));
				echo '
				<div class="accordion-item" data-accordion-item>
					<a href="#" class="accordion-title">' . KT_I18N::translate('%s born after death or burial', $data['count']) . '
						<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
					</a>
					<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
				</div>';
			}
			if (KT_Filter::post('birt_marr')) {
				$data = birth_comparisons(array('FAMS'), 'MARR');
				echo '
				<div class="accordion-item" data-accordion-item>
					<a href="#" class="accordion-title">' . KT_I18N::translate('%s born after marriage', $data['count']) . '
						<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
					</a>
					<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
				</div>';
			}
			if (KT_Filter::post('birt_chil')) {
				$data = birth_comparisons(array('FAMS'), 'CHIL');
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s born after their children', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('buri')) {
				$data = death_comparisons(array('BURI'));
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s buried before death', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('bap_late')) {
				$data = query_age(array('BAPM', 'CHR'), $bap_age);
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%1s baptised more than %2s years after birth', $data['count'], $bap_age) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('old_age')) {
				$data = query_age(array('DEAT'), $oldage);
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%1s living and older than %2s years', $data['count'], $oldage) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('marr_yng')) {
				$data = query_age(array('MARR'), $marr_age);
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%1s married younger than %2s years', $data['count'], $marr_age) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('spouse_age')) {
				$data = query_age(array('FAMS'), $spouseage);
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%1s spouses with more than %2s years age difference', $data['count'], $spouseage) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('child_yng')) {
				$data = query_age(array('CHIL_1'), $child_y);
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%1s women having children before age %2s years', $data['count'], $child_y) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('child_old')) {
				$data = query_age(array('CHIL_2'), $child_o);
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%1s women having children after age %2s years', $data['count'], $child_o) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('dupe_birt')) {
				$data = duplicate_tag('BIRT');
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s with duplicate births recorded', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('dupe_bapm')) {
				$data = duplicate_tag('BAPM');
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s with duplicate baptism or christenings recorded', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('dupe_deat')) {
				$data = duplicate_tag('DEAT');
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s with duplicate deaths recorded', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('dupe_crem')) {
				$data = duplicate_tag('CREM');
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s with duplicate cremations recorded', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('dupe_buri')) {
				$data = duplicate_tag('BURI');
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s with duplicate burial or cremations recorded', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('dupe_sex')) {
				$data = duplicate_tag('SEX');
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s with duplicate genders recorded', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('dupe_name')) {
				$data = identical_name();
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s with duplicate names recorded', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('dupe_marr')) {
				$data = duplicate_famtag('MARR');
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s with duplicate marriages recorded', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('dupe_child')) {
				$data = duplicate_child();
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s with duplicately named children', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('sex')) {
				$data = missing_tag('SEX');
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s have no gender recorded', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('age')) {
				$data = invalid_age();
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s individuals or families have age incorrectly recorded', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('empty_tag')) {
				$data = empty_tag();
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s individuals with empty fact or event tags', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('child_order')) {
				$data = child_order();
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s families with children not sorted by birth date', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('fam_order')) {
				$data = fam_order();
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s families not sorted by marriage date', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('birt')) {
				$data = missing_vital('BIRT');
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s have missing or incomplete birth data', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			if (KT_Filter::post('deat')) {
				$data = missing_vital('DEAT');
				echo '
					<div class="accordion-item" data-accordion-item>
						<a href="#" class="accordion-title">' . KT_I18N::translate('%s have missing or incomplete death data', $data['count']) . '
							<span class="float-right">' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
						</a>
						<div class="accordion-content" data-tab-content>' . $data['html'] . '</div>
					</div>';
			}
			?>
		</div>
	<?php } ?>
</div> <!-- close sanity_check page div -->

<?php
