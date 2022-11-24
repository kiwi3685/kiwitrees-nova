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

define('KT_SCRIPT_NAME', 'admin_trees_merge.php');
require './includes/session.php';
require_once KT_ROOT . 'includes/functions/functions_edit.php';
require_once KT_ROOT . 'includes/functions/functions_import.php';
include KT_THEME_URL . 'templates/adminData.php';

$recordTypes = array(
	KT_I18N::translate('Individuals')	=> 'INDI',
	KT_I18N::translate('Families')		=> 'FAM',
	KT_I18N::translate('Sources')		=> 'SOUR',
);

$controller = new KT_Controller_Page;
$controller
	->pageHeader()
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Merge records'))
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('autocomplete();');

$action  = KT_Filter::post('action', 'data_type|choose|select|merge', 'data_type');
$type	 = KT_Filter::post('record_type');

echo relatedPages($trees, KT_SCRIPT_NAME);

echo pageStart('merge_records', $controller->getPageTitle()); ?>
	<?php if ($action == 'data_type') { ?>
		<form class="cell" method="post" name="datatype" action="<?php echo KT_SCRIPT_NAME; ?>">
			<input type="hidden" name="action" value="choose">
			<div class="grid-x grid-margin-x grid-padding-x grid-padding-y">
				<div class="cell callout info-help ">
	                <?php echo KT_I18N::translate('
	                    Select the type of record you want to merge, then click "Next"
	                '); ?>
	            </div>
				<!-- Record type -->
				<div class="cell medium-2">
					<label for="record_type"><?php echo KT_I18N::translate('Select record type'); ?></label>
				</div>
				<div class="cell medium-2">
					<select id="record_type" name="record_type">
						<option> </option>
						<?php foreach ($recordTypes as $key => $value) { ?>
							<option value="<?php echo $value; ?>"
								<?php if ($type == $value) { ?>
									 selected
								<?php } ?>
							>
								<?php echo $key; ?>
							</option>
						<?php } ?>
					</select>
				</div>
			</div>

			<?php echo singleButton('Next'); ?>

		</form>

	<?php } ?>

	<?php if ($action == 'choose') {
	    $recordFrom = KT_Filter::post('recordFrom', KT_REGEX_XREF, null);
	    $recordTo   = KT_Filter::post('recordTo', KT_REGEX_XREF, null);
	    $gedIDFrom  = KT_Filter::post('gedIDFrom', null, null);
	    $gedIDTo    = KT_Filter::post('gedIDTo', null, null);
	    $person1    = $recordFrom ? KT_Person::getInstance($recordFrom) : '';
	    $person2    = $recordTo ? KT_Person::getInstance($recordTo) : '';
		?>

		<form class="cell" method="post" name="merge" action="<?php echo KT_SCRIPT_NAME; ?>" data-abide data-live-validate="true" novalidate>
			<input type="hidden" name="action" value="select">
			<input type="hidden" name="record_type" value="<?php echo $type; ?>">
			<input type="hidden" name="recordFrom" value="<?php echo $recordFrom; ?>">
            <input type="hidden" name="recordTo" value="<?php echo $recordTo; ?>">
            <input type="hidden" name="gedIDFrom" value="<?php echo $gedIDFrom; ?>">
            <input type="hidden" name="gedIDTo" value="<?php echo $gedIDTo; ?>">

			<div class="grid-x grid-margin-x">
				<div class="cell callout info-help ">
					<?php echo KT_I18N::translate('
						Select two GEDCOM records to merge.
						The records must be of the same type, but can be from
						different family trees that exist on this site.
					'); ?>
				</div>
				<!-- Record type -->
				<div class="cell medium-2">
					<label for="record_type"><?php echo KT_I18N::translate('Record type'); ?></label>
				</div>
				<div class="cell medium-2">
					<input type="text" value="<?php echo array_search($type, $recordTypes); ?>" disabled>
				</div>
				<div class="cell medium-8"></div>
				<!-- Merge From -->
				<div class="cell medium-2">
					<label for="gedIDFrom"><?php echo KT_I18N::translate('Merge from:'); ?></label>
				</div>
				<div class="cell medium-4">
					<?php echo select_ged_control('gedIDFrom', KT_Tree::getIdList(), null, KT_GEDCOM); ?>
				</div>
				<div class="cell medium-4">
					<?php echo autocompleteHtml(
						'recordFrom', // id
						$type, // TYPE
						'', // autocomplete-ged
						$person1 ? strip_tags($person1->getLifespanName()) : '', // input value
						array_search($type, $recordTypes), // placeholder
						'recordFrom', // hidden input name
						$recordFrom, // hidden input value
						' required ' // required
					); ?>
				</div>
				<div class="cell medium-2"></div>
				<!-- Merge to -->
				<div class="cell medium-2">
					<label for="recordTo"><?php echo KT_I18N::translate('Merge to:'); ?></label>
				</div>
				<div class="cell medium-4">
					<?php echo select_ged_control('gedIDTo', KT_Tree::getIdList(), null, KT_GEDCOM); ?>
				</div>
				<div class="cell medium-4">
					<?php echo autocompleteHtml(
					    'recordTo', // id
					    $type, // TYPE
					    '', // autocomplete-ged
					    $person2 ? strip_tags($person2->getLifespanName()) : '', // input value
					    array_search($type, $recordTypes), // placeholder
					    'recordTo', // hidden input name
					    $recordTo, // hidden input value
					    ' required ' , // required
						'', // other
					    ' data-validator="not_equalTo" data-not-equalTo="selectedValue-recordFrom" ' //validator
					); ?>
					<div class="cell alert callout" data-abide-error data-form-error-on="not_equalTo" style="display: none;">
						<?php echo KT_I18N::translate('You cannot merge the same records.'); ?>
					</div>
				</div>
				<div class="cell medium-2"></div>

				<?php echo singleButton('Next'); ?>

			</div>
		</form>

	<?php } ?>

	<?php if ($action == 'select') {
	    $recordFrom = KT_Filter::post('recordFrom', KT_REGEX_XREF, null);
	    $recordTo   = KT_Filter::post('recordTo', KT_REGEX_XREF, null);
	    $gedIDFrom  = KT_Filter::post('gedIDFrom', null, null);
	    $gedIDTo    = KT_Filter::post('gedIDTo', null, null);
	    $person1    = $recordFrom ? KT_Person::getInstance($recordFrom) : '';
	    $person2    = $recordTo ? KT_Person::getInstance($recordTo) : '';
		$gedrec1    = find_gedcom_record($recordFrom, $gedIDFrom, true);
		$gedrec2    = find_gedcom_record($recordTo, $gedIDTo, true);


		// Fetch the original XREF - may differ in case from the supplied value
		$tmp        = new KT_Person($gedrec1);
		$recordFrom = $tmp->getXref();
		$nameFrom   = $tmp->getLifespanName();

		$tmp        = new KT_Person($gedrec2);
		$recordTo   = $tmp->getXref();
		$nameTo     = $tmp->getLifespanName();

		$typeFrom      = '';
		$ct         = preg_match("/0 @$recordFrom@ (.*)/", $gedrec1, $match);
		if ($ct > 0) {
			$typeFrom  = trim($match[1]);
		}

		$typeTo      = '';
		$ct         = preg_match("/0 @$recordTo@ (.*)/", $gedrec2, $match);
		if ($ct > 0) {
			$typeTo  = trim($match[1]);
		}

		$factsFrom  = array();
		$factsTo    = array();
		$prev_tags  = array();

		$ct         = preg_match_all('/\n1 (\w+)/', $gedrec1, $match, PREG_SET_ORDER);
		for ($i = 0; $i < $ct; $i ++) {
			$fact = trim($match[$i][1]);
			if (isset($prev_tags[$fact])) {
				$prev_tags[$fact]++;
			} else {
				$prev_tags[$fact] = 1;
			}
			$subrec		= get_sub_record(1, "1 $fact", $gedrec1, $prev_tags[$fact]);
			$factsFrom[]	= array('fact'=>$fact, 'subrec'=>trim($subrec));
		}
		$prev_tags = array();
		$ct = preg_match_all('/\n1 (\w+)/', $gedrec2, $match, PREG_SET_ORDER);

		for ($i = 0; $i < $ct; $i ++) {
			$fact = trim($match[$i][1]);
			if (isset($prev_tags[$fact])) {
				$prev_tags[$fact]++;
			} else {
				$prev_tags[$fact] = 1;
			}
			$subrec		= get_sub_record(1, "1 $fact", $gedrec2, $prev_tags[$fact]);
			$factsTo[]	= array('fact'=>$fact, 'subrec'=>trim($subrec));
		}
		?>

		<form class="cell" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
            <input type="hidden" name="recordFrom" value="<?php echo $recordFrom; ?>">
            <input type="hidden" name="recordTo" value="<?php echo $recordTo; ?>">
            <input type="hidden" name="gedIDFrom" value="<?php echo $gedIDFrom; ?>">
            <input type="hidden" name="gedIDTo" value="<?php echo $gedIDTo; ?>">
			<input type="hidden" name="typeFrom" value="<?php echo $typeFrom; ?>">
			<input type="hidden" name="typeTo" value="<?php echo $typeTo; ?>">
			<input type="hidden" name="factsFrom" value="<?php echo $factsFrom; ?>">
			<input type="hidden" name="factsTo" value="<?php echo $factsTo; ?>">
            <input type="hidden" name="action" value="merge">
            <?php
            $equal_count	= 0;
            $skipFrom		= array();
            $skipTo			= array();
            ?>
            <div class="grid-x grid-margin-x grid-padding-x grid-padding-y">
                <div class="cell callout success">
                    <?php echo KT_I18N::translate('
                        The following facts were exactly the same in both records
                        and will be merged automatically.
                    '); ?>
                </div>
                <div class="cell medium-6">
                    <div class="grid-x grid-padding-x matched">
                        <?php foreach ($factsFrom as $i=>$factFrom) {
                            foreach ($factsTo as $j=>$factTo) {
                                if (utf8_strtoupper($factFrom['subrec']) == utf8_strtoupper($factTo['subrec'])) {
                                    $skipFrom[]	 = $i;
                                    $skipTo[] 	 = $j;
                                    $equal_count ++; ?>
                                    <div class="cell medium-2"><?php echo KT_I18N::translate($factFrom['fact']); ?>
                                        <input type="hidden" name="keepFrom[]" value="<?php echo $i; ?>">
                                    </div>
                                    <div class="cell medium-10">
                                        <?php echo nl2br($factFrom['subrec']); ?>
                                    </div>
                                <?php }
                            }
                        }
                        if ($equal_count == 0) { ?>
                            <div class="cell callout warning">
                                <?php echo KT_I18N::translate('No matching facts found'); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <hr class="cell">

                <div class="cell callout warning">
                    <?php echo KT_I18N::translate('
                        The following facts did not match.
                        Select the information you would like to keep
                        using the switches below.
                    '); ?>
                </div>
                <div class="cell">
                    <div class="grid-x grid-margin-x unmatched">
                        <div class="cell small-6 text-center header">
                            <?php echo KT_I18N::translate('From record') . ' ' . $recordFrom . ' - ' . $nameFrom; ?>
                        </div>
                        <div class="cell small-6 text-center header">
                            <?php echo KT_I18N::translate('To record') . ' ' . $recordTo . ' - ' . $nameTo; ?>
                        </div>
                        <div class="cell small-6">
                            <div class="grid-x unmatchedL">
                                <?php foreach ($factsFrom as $i => $factFrom) {
                                    if (($factFrom['fact'] != 'CHAN') && (!in_array($i, $skipFrom))) { ?>
                                        <div class="cell medium-1">
                                            <div class="grid-x grid-margin-y">
                                                <div class="switch tiny cell small-8 medium-4 large-2">
                                                    <input class="switch-input" id="keepFrom-<?php echo $i; ?>" type="checkbox" name="keepFrom[]" value="<?php echo $i; ?>" checked >
                                                    <label class="switch-paddle" for="keepFrom-<?php echo $i; ?>">
                                                        <span class="show-for-sr"><?php echo $i; ?></span>
                                                        <span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
                                                        <span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cell medium-11">
                                            <?php echo nl2br($factFrom['subrec']); ?>
                                        </div>
                                        <hr class="cell">
                                    <?php }
                                } ?>
                            </div>
                        </div>
                        <div class="cell small-6">
                            <div class="grid-x unmatchedR">
                                <?php foreach ($factsTo as $j => $factTo) {
                                    if (($factTo['fact'] != 'CHAN') && (!in_array($j, $skipTo))) { ?>
                                        <div class="cell medium-1">
                                            <div class="grid-x grid-margin-y">
                                                <div class="switch tiny cell small-8 medium-4 large-2">
                                                    <input class="switch-input" id="keepTo-<?php echo $j; ?>" type="checkbox" name="keepTo[]" value="<?php echo $j; ?>" checked >
                                                    <label class="switch-paddle" for="keepTo-<?php echo $j; ?>">
                                                        <span class="show-for-sr"><?php echo $j; ?></span>
                                                        <span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
                                                        <span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('Yes'); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cell medium-11">
                                            <?php echo nl2br($factTo['subrec']); ?>
                                        </div>
                                        <hr class="cell">
                                    <?php }
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>

				<?php echo singleButton('Previous'); ?>
				<?php echo singleButton('Save'); ?>

            </div>
        </form>
	<?php } ?>

	<?php if ($action == 'merge') {
	    $recordFrom = KT_Filter::post('recordFrom', KT_REGEX_XREF, null);
	    $recordTo   = KT_Filter::post('recordTo', KT_REGEX_XREF, null);
	    $gedIDFrom  = KT_Filter::post('gedIDFrom', null, null);
	    $gedIDTo    = KT_Filter::post('gedIDTo', null, null);
		$typeFrom   = KT_Filter::postArray('typeFrom');
		$typeTo     = KT_Filter::postArray('typeTo');
		$factsFrom  = KT_Filter::postArray('factsFrom');
		$factsTo    = KT_Filter::postArray('factsTo');
		$keepFrom   = KT_Filter::postArray('keepFrom');
		$keepTo     = KT_Filter::postArray('keepTo');

		if ($gedIDFrom == $gedIDTo) {
            $success = delete_gedrec($recordTo, $gedIDTo); ?>
            <div class="cell">
                <?php echo KT_I18N::translate('GEDCOM record successfully deleted.'); ?>
            </div>

            <?php
            // replace all the records that linked to recordTo //
            $ids = fetch_all_links($recordTo, $gedIDTo);

            foreach ($ids as $id) {
                $record = find_gedcom_record($id, $gedIDTo, true); ?>
                <div class="cell">
                    <?php echo KT_I18N::translate('Updating linked record'); ?>
                </div>

                <?php
                $newrec = str_replace("@$recordTo@", "@$recordFrom@", $record);
                $newrec = preg_replace(
                    '/(\n1.*@.+@.*(?:(?:\n[2-9].*)*))((?:\n1.*(?:\n[2-9].*)*)*\1)/',
                    '$2',
                    $newrec
                );
                replace_gedrec($id, $gedIDTo, $newrec);
            }

            // Update any linked user-accounts
            KT_DB::prepare(
                "UPDATE `##user_gedcom_setting`".
                " SET setting_value=?".
                " WHERE gedcom_id=? AND setting_name='gedcomid' AND setting_value=?"
            )->execute(array($recordTo, $gedIDTo, $recordFrom));

            // Merge hit counters
            $hits=KT_DB::prepare(
                "SELECT page_name, SUM(page_count)".
                " FROM `##hit_counter`".
                " WHERE gedcom_id=? AND page_parameter IN (?, ?)".
                " GROUP BY page_name"
            )->execute(array($gedIDTo, $recordFrom, $recordTo))->fetchAssoc();
            foreach ($hits as $page_name=>$page_count) {
                KT_DB::prepare(
                    "UPDATE `##hit_counter` SET page_count=?".
                    " WHERE gedcom_id=? AND page_name=? AND page_parameter=?"
                )->execute(array($page_count, $gedIDTo, $page_name, $recordFrom));
            }
            KT_DB::prepare(
                "DELETE FROM `##hit_counter`".
                " WHERE gedcom_id=? AND page_parameter=?"
            )->execute(array($gedIDTo, $recordTo));
        }

        $newgedrec = "0 @$recordFrom@ $typeFrom\n";

        if ((is_countable($$factsFrom) && count($factsFrom) > 0) || is_countable($$factsTo) && count($factsTo) > 0) {
        	for ($i = 0; ($i < count($factsFrom) || $i < count($factsTo)); $i ++) {
	            if (isset($factsFrom[$i])) {
	                if (in_array($i, $keepFrom)) {
	                    $newgedrec .= $factsFrom[$i]['subrec']."\n"; ?>
	                    <div class="cell">
	                        <?php echo
		                        KT_I18N::translate('Adding') . ' ' .
		                        $factsFrom[$i]['fact'] . ' ' .
		                        KT_I18N::translate('from') . ' ' .
		                        $recordFrom
		                    ; ?>
	                    </div>
	                <?php }
	            }

	            if (isset($factsTo[$i])) {
	                if (in_array($i, $keepTo)) {
	                    $newgedrec .= $factsTo[$i]['subrec']."\n"; ?>
	                    <div class="cell">
	                        <?php echo
		                        KT_I18N::translate('Adding') . ' ' .
		                        $factsTo[$i]['fact'] . ' ' .
		                        KT_I18N::translate('from') . ' ' .
		                        $recordTo
		                    ; ?>
	                    </div>
	                <?php }
	            }
	        }
	    }

        replace_gedrec($recordFrom, $gedIDFrom, $newgedrec);
        $rec = KT_GedcomRecord::getInstance($recordFrom); ?>
        <div class="cell">
            <?php echo KT_I18N::translate('Record %s successfully updated.', '<a href="' . $rec->getHtmlUrl() . '">' . $rec->getXref() . '</a>' ); ?>
        </div>

        <?php
        $fav_count = update_favorites($recordTo, $recordFrom);

        if ($fav_count > 0) { ?>
            <div class="cell">
                <?php echo KT_I18N::plural('%s favorite updated', '%s favorites updated', $fav_count, $fav_count); ?>
            </div>
        <?php }
	}

echo pageClose();
