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
	->pageHeader()
	->addInlineJavascript('
		jQuery("#sanity_accordion").accordion({heightStyle: "content", collapsible: true, active: false, header: "h5"});
		jQuery("#sanity_accordion").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
	');

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
		KT_I18N::translate('Duplicated family data'),
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
		array (1, 'birt_chil',		KT_I18N::translate('Birth after their children') . '<span class="error">**</span>'),
		array (1, 'buri',			KT_I18N::translate('Burial before death')),
		array (2, 'bap_late',		KT_I18N::translate('Baptised after a certain age'), 'NEW_SANITY_BAPTISM', 'bap_age', $bap_age),
		array (2, 'old_age',		KT_I18N::translate('Alive after a certain age'), 'NEW_SANITY_OLDAGE', 'oldage', $oldage),
		array (2, 'marr_yng',		KT_I18N::translate('Married before a certain age') . '<span class="error">**</span>', 'NEW_SANITY_MARRIAGE', 'marr_age', $marr_age),
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
		array (4, 'dupe_marr',		KT_I18N::translate('Marriage')),
		array (4, 'dupe_child',		KT_I18N::translate('Families with duplicately named children')),
		array (5, 'birt',			KT_I18N::translate('Missing or incomplete birth')),
		array (5, 'deat',			KT_I18N::translate('Missing or incomplete death')),
		array (5, 'sex',			KT_I18N::translate('No gender recorded')),
		array (5, 'age',			KT_I18N::translate('Invalid age recorded')),
		array (5, 'empty_tag',		KT_I18N::translate('Empty individual fact or event') . '<span class="error">**</span>'),
		array (5, 'child_order',	KT_I18N::translate('Children not sorted by birth date')),
		array (5, 'fam_order',		KT_I18N::translate('Families not sorted by marriage date')),
	);

?>

<div id="sanity_check">
	<a class="current faq_link" href="http://kiwitrees.net/faqs/general/sanity-check/" target="_blank" rel="noopener noreferrer" title="<?php echo KT_I18N::translate('View FAQ for this page.'); ?>"><?php echo KT_I18N::translate('View FAQ for this page.'); ?><i class="<?php echo $iconStyle; ?> fa-comments-o"></i></a>
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<h4><?php echo KT_I18N::translate('%s checks to help you monitor the quality of your family history data', count($checks)); ?></h3>
	<p class="alert">
		<?php echo KT_I18N::translate('This process can be slow. If you have a large family tree or suspect large numbers of errors you should only select a few checks each time.<br><br>Options marked <span class="warning">**</span> are often very slow.'); ?>
	</p>
	<form method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
		<input type="hidden" name="save" value="1">
		<div class="admin_options">
			<div class="input">
				<label><?php echo KT_I18N::translate('Family tree'); ?></label>
				<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM); ?>
			</div>
		</div>
		<div id="sanity_options">
			<?php for ($i = 1; $i < count($checkGroups) + 1; $i ++) { ?>
				<ul>
					<h3><?php echo $checkGroups[$i-1]; ?></h3>
					<?php for ($row = 0; $row < count($checks); $row ++) {
						if ($checks[$row][0] == $i) { ?>
							<li class="facts_value" name="<?php echo $checks[$row][1]; ?>" id="<?php echo $checks[$row][1]; ?>">
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
			<?php } ?>
		</div>
		<button type="submit" class="btn btn-primary clearfloat" >
			<i class="<?php echo $iconStyle; ?> fa-check"></i>
			<?php echo $controller->getPageTitle(); ?>
		</button>
	</form>
	<form method="post" name="rela_form" action="#">
		<input type="hidden" name="reset" value="1">
		<button class="btn btn-primary reset" type="submit">
			<i class="<?php echo $iconStyle; ?> fa-sync"></i>
			<?php echo KT_I18N::translate('Reset'); ?>
		</button>
	</form>
	<hr class="clearfloat">
	<?php if (KT_Filter::post('save')) {?>
		<div class="loading-image"></div>
		<div id="sanity_accordion" style="visibility: hidden;">
			<h3><?php echo KT_I18N::translate('Results'); ?></h3>
			<?php
			if (KT_Filter::post('baptised')) {
				$data = birth_comparisons(array('BAPM', 'CHR'));
				echo '<h5>' . KT_I18N::translate('%s born after baptism or christening', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('died')) {
				$data = birth_comparisons(array('DEAT'));
				echo '<h5>' . KT_I18N::translate('%s born after death or burial', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('birt_marr')) {
				$data = birth_comparisons(array('FAMS'), 'MARR');
				echo '<h5>' . KT_I18N::translate('%s born after marriage', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('birt_chil')) {
				$data = birth_comparisons(array('FAMS'), 'CHIL');
				echo '<h5>' . KT_I18N::translate('%s born after their children', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('buri')) {
				$data = death_comparisons(array('BURI'));
				echo '<h5>' . KT_I18N::translate('%s buried before death', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('bap_late')) {
				$data = query_age(array('BAPM', 'CHR'), $bap_age);
				echo '<h5>' . KT_I18N::translate('%1s baptised more than %2s years after birth', $data['count'], $bap_age) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('old_age')) {
				$data = query_age(array('DEAT'), $oldage);
				echo '<h5>' . KT_I18N::translate('%1s living and older than %2s years', $data['count'], $oldage) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('marr_yng')) {
				$data = query_age(array('MARR'), $marr_age);
				echo '<h5>' . KT_I18N::translate('%1s married younger than %2s years', $data['count'], $marr_age) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('spouse_age')) {
				$data = query_age(array('FAMS'), $spouseage);
				echo '<h5>' . KT_I18N::translate('%1s spouses with more than %2s years age difference', $data['count'], $spouseage) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('child_yng')) {
				$data = query_age(array('CHIL_1'), $child_y);
				echo '<h5>' . KT_I18N::translate('%1s women having children before age %2s years', $data['count'], $child_y) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('child_old')) {
				$data = query_age(array('CHIL_2'), $child_o);
				echo '<h5>' . KT_I18N::translate('%1s women having children after age %2s years', $data['count'], $child_o) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_birt')) {
				$data = duplicate_tag('BIRT');
				echo '<h5>' . KT_I18N::translate('%s with duplicate births recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_bapm')) {
				$data = duplicate_tag('BAPM');
				echo '<h5>' . KT_I18N::translate('%s with duplicate baptism or christenings recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_deat')) {
				$data = duplicate_tag('DEAT');
				echo '<h5>' . KT_I18N::translate('%s with duplicate deaths recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_crem')) {
				$data = duplicate_tag('CREM');
				echo '<h5>' . KT_I18N::translate('%s with duplicate cremations recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_buri')) {
				$data = duplicate_tag('BURI');
				echo '<h5>' . KT_I18N::translate('%s with duplicate burial or cremations recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_sex')) {
				$data = duplicate_tag('SEX');
				echo '<h5>' . KT_I18N::translate('%s with duplicate genders recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_name')) {
				$data = identical_name();
				echo '<h5>' . KT_I18N::translate('%s with duplicate names recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_marr')) {
				$data = duplicate_famtag('MARR');
				echo '<h5>' . KT_I18N::translate('%s with duplicate marriages recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_child')) {
				$data = duplicate_child();
				echo '<h5>' . KT_I18N::translate('%s with duplicately named children', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('sex')) {
				$data = missing_tag('SEX');
				echo '<h5>' . KT_I18N::translate('%s have no gender recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('age')) {
				$data = invalid_tag('AGE');
				echo '<h5>' . KT_I18N::translate('%s individuals or families have age incorrectly recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('empty_tag')) {
				$data = empty_tag();
				echo '<h5>' . KT_I18N::translate('%s individuals with empty fact or event tags', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('child_order')) {
				$data = child_order();
				echo '<h5>' . KT_I18N::translate('%s families with children not sorted by birth date', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('fam_order')) {
				$data = fam_order();
				echo '<h5>' . KT_I18N::translate('%s families not sorted by marriage date', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('birt')) {
				$data = missing_vital('BIRT');
				echo '<h5>' . KT_I18N::translate('%s have missing or incomplete birth data', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('deat')) {
				$data = missing_vital('DEAT');
				echo '<h5>' . KT_I18N::translate('%s have missing or incomplete death data', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}

			?>
		</div>
	<?php } ?>
</div> <!-- close sanity_check page div -->

<?php
