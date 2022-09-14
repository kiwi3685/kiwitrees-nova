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

echo relatedPages($links = array(
    'admin_trees_manage.php',
    'admin_trees_config.php',
    'admin_trees_check.php',
    'admin_trees_change.php',
    'admin_trees_addunlinked.php',
    'admin_trees_places.php',
    'admin_trees_renumber.php',
    'admin_trees_append.php',
    'admin_trees_duplicates.php',
    'admin_trees_findunlinked.php',
    'admin_trees_sanity.php',
    'admin_trees_source.php',
    'admin_trees_sourcecite.php',
    'admin_trees_missing.php',
));

echo pageStart('merge_records', $controller->getPageTitle()); ?>
	<?php if ($action == 'data_type') { ?>
		<form class="cell" method="post" name="datatype" action="<?php echo KT_SCRIPT_NAME; ?>">
			<input type="hidden" name="action" value="choose">
			<div class="grid-x grid-margin-x grid-padding-x grid-padding-y">
				<div class="cell callout warning helpcontent">
	                <?php echo KT_I18N::translate('
	                    Select the type of record you want to merge, then click "Next"
	                '); ?>
	            </div>
				<!-- Record type -->
				<div class="cell medium-2">
					<label for="ged"><?php echo KT_I18N::translate('Select record type'); ?></label>
				</div>
				<div class="cell medium-2">
					<select name="record_type">
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
	    $ged     = $GEDCOM;
	    $gid1    = KT_Filter::post('gid1', KT_REGEX_XREF, null);
	    $gid2    = KT_Filter::post('gid2', KT_REGEX_XREF, null);
	    $ged1    = KT_Filter::post('ged1', null, null);
	    $ged2    = KT_Filter::post('ged2', null, null);
	    $person1 = $gid1 ? KT_Person::getInstance($gid1) : '';
	    $person2 = $gid2 ? KT_Person::getInstance($gid2) : '';

		?>
		<form class="cell" method="post" name="merge" action="<?php echo KT_SCRIPT_NAME; ?>" data-abide data-live-validate="true" novalidate>
			<input type="hidden" name="action" value="select">
			<input type="hidden" name="record_type" value="<?php echo $type; ?>">
			<input type="hidden" name="gid1" value="<?php echo $gid1; ?>">
            <input type="hidden" name="gid2" value="<?php echo $gid2; ?>">
            <input type="hidden" name="ged" value="<?php echo $GEDCOM; ?>">
            <input type="hidden" name="ged2" value="<?php echo $ged2; ?>">

			<div class="grid-x grid-margin-x">
				<div class="cell callout warning helpcontent">
					<?php echo KT_I18N::translate('
						Select two GEDCOM records to merge.
						The records must be of the same type, but can be from
						different family trees that exist on this site.
					'); ?>
				</div>
				<!-- Record type -->
				<div class="cell medium-2">
					<label for="ged"><?php echo KT_I18N::translate('Record type'); ?></label>
				</div>
				<div class="cell medium-2">
					<input type="text" value="<?php echo array_search($type, $recordTypes); ?>" disabled>
				</div>
				<div class="cell medium-8"></div>
				<!-- Merge to -->
				<div class="cell medium-2">
					<label for="ged"><?php echo KT_I18N::translate('Merge to:'); ?></label>
				</div>
				<div class="cell medium-4">
					<form method="post" action="#" name="tree">
						<?php echo select_ged_control('ged', KT_Tree::getIdList(), null, KT_GEDCOM, ' onchange="tree.submit();"'); ?>
					</form>
				</div>
				<div class="cell medium-4">
					<?php echo autocompleteHtml(
						'gid1', // id
						$type, // TYPE
						'', // autocomplete-ged
						$person1 ? strip_tags($person1->getLifespanName()) : '', // input value
						array_search($type, $recordTypes), // placeholder
						'gid1', // hidden input name
						$gid1, // hidden input value
						' required ' // required
					); ?>
				</div>
				<div class="cell medium-2"></div>
				<!-- Merge From -->
				<div class="cell medium-2">
					<label for="ged"><?php echo KT_I18N::translate('Merge from:'); ?></label>
				</div>
				<div class="cell medium-4">
					<form method="post" action="#" name="tree">
						<?php echo select_ged_control('ged', KT_Tree::getIdList(), null, KT_GEDCOM, ' onchange="tree.submit();"'); ?>
					</form>
				</div>
				<div class="cell medium-4">
					<?php echo autocompleteHtml(
					    'gid2', // id
					    $type, // TYPE
					    '', // autocomplete-ged
					    $person2 ? strip_tags($person1->getLifespanName()) : '', // input value
					    array_search($type, $recordTypes), // placeholder
					    'gid2', // hidden input name
					    $gid2, // hidden input value
					    ' required ' , // required
					    ' data-validator="not_equalTo" data-not-equalTo="autocompleteInput-gid1" ' //other
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
		$ged     = $GEDCOM;
	    $gid1    = KT_Filter::post('gid1', KT_REGEX_XREF, null);
	    $gid2    = KT_Filter::post('gid2', KT_REGEX_XREF, null);
	    $ged1    = KT_Filter::post('ged1', null, null);
	    $ged2    = KT_Filter::post('ged2', null, null);
	    $person1 = $gid1 ? KT_Person::getInstance($gid1) : '';
	    $person2 = $gid2 ? KT_Person::getInstance($gid2) : '';
		$gedrec1 = find_gedcom_record($gid1, KT_GED_ID, true);
		$gedrec2 = find_gedcom_record($gid2, get_id_from_gedcom($ged2), true);

		// Fetch the original XREF - may differ in case from the supplied value
		$tmp	= new KT_Person($gedrec1);
		$gid1	= $tmp->getXref();
		$name1	= $tmp->getLifespanName();

		$tmp	= new KT_Person($gedrec2);
		$gid2	= $tmp->getXref();
		$name2	= $tmp->getLifespanName();

		$type1 = '';
		$ct = preg_match("/0 @$gid1@ (.*)/", $gedrec1, $match);
		if ($ct > 0) {
			$type1 = trim($match[1]);
		}

		$type2	= '';
		$ct		= preg_match("/0 @$gid2@ (.*)/", $gedrec2, $match);
		if ($ct > 0) {
			$type2 = trim($match[1]);
		}

		$facts1		= array();
		$facts2		= array();
		$prev_tags	= array();

		$ct = preg_match_all('/\n1 (\w+)/', $gedrec1, $match, PREG_SET_ORDER);
		for ($i = 0; $i < $ct; $i ++) {
			$fact = trim($match[$i][1]);
			if (isset($prev_tags[$fact])) {
				$prev_tags[$fact]++;
			} else {
				$prev_tags[$fact] = 1;
			}
			$subrec		= get_sub_record(1, "1 $fact", $gedrec1, $prev_tags[$fact]);
			$facts1[]	= array('fact'=>$fact, 'subrec'=>trim($subrec));
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
			$facts2[]	= array('fact'=>$fact, 'subrec'=>trim($subrec));
		}
		?>

		<form class="cell" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
            <input type="hidden" name="gid1" value="<?php echo $gid1; ?>">
            <input type="hidden" name="gid2" value="<?php echo $gid2; ?>">
            <input type="hidden" name="ged" value="<?php echo $GEDCOM; ?>">
            <input type="hidden" name="ged2" value="<?php echo $ged2; ?>">
			<input type="hidden" name="type1" value="<?php echo $type1; ?>">
			<input type="hidden" name="type2" value="<?php echo $type2; ?>">
			<input type="hidden" name="facts1" value="<?php echo $facts1; ?>">
			<input type="hidden" name="facts2" value="<?php echo $facts2; ?>">
            <input type="hidden" name="action" value="merge">
            <?php
            $equal_count	= 0;
            $skip1			= array();
            $skip2			= array();
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
                        <?php foreach ($facts1 as $i=>$fact1) {
                            foreach ($facts2 as $j=>$fact2) {
                                if (utf8_strtoupper($fact1['subrec']) == utf8_strtoupper($fact2['subrec'])) {
                                    $skip1[] = $i;
                                    $skip2[] = $j;
                                    $equal_count++; ?>
                                    <div class="cell medium-2"><?php echo KT_I18N::translate($fact1['fact']); ?>
                                        <input type="hidden" name="keep1[]" value="<?php echo $i; ?>">
                                    </div>
                                    <div class="cell medium-10">
                                        <?php echo nl2br($fact1['subrec']); ?>
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
                    <div class="grid-x grid-padding-x unmatched">
                        <div class="cell small-6 text-center header">
                            <?php echo KT_I18N::translate('From record') . ' ' . $gid1 . ' - ' . $name1; ?>
                        </div>
                        <div class="cell small-6 text-center header">
                            <?php echo KT_I18N::translate('To record') . ' ' . $gid2 . ' - ' . $name2; ?>
                        </div>
                        <div class="cell small-6">
                            <div class="grid-x grid-margin-x unmatchedL">
                                <?php foreach ($facts1 as $i => $fact1) {
                                    if (($fact1['fact'] != 'CHAN') && (!in_array($i, $skip1))) { ?>
                                        <div class="cell medium-1">
                                            <div class="grid-x grid-margin-y">
                                                <div class="switch tiny cell small-8 medium-4 large-2">
                                                    <input class="switch-input" id="keep1-<?php echo $i; ?>" type="checkbox" name="keep1[]" value="<?php echo $i; ?>" checked >
                                                    <label class="switch-paddle" for="keep1-<?php echo $i; ?>">
                                                        <span class="show-for-sr"><?php echo $i; ?></span>
                                                        <span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('yes'); ?></span>
                                                        <span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('yes'); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cell medium-11">
                                            <?php echo nl2br($fact1['subrec']); ?>
                                        </div>
                                        <hr class="cell">
                                    <?php }
                                } ?>
                            </div>
                        </div>
                        <div class="cell small-6">
                            <div class="grid-x grid-margin-x unmatchedR">
                                <?php foreach ($facts2 as $j => $fact2) {
                                    if (($fact2['fact'] != 'CHAN') && (!in_array($j, $skip2))) { ?>
                                        <div class="cell medium-1">
                                            <div class="grid-x grid-margin-y">
                                                <div class="switch tiny cell small-8 medium-4 large-2">
                                                    <input class="switch-input" id="keep2-<?php echo $j; ?>" type="checkbox" name="keep2[]" value="<?php echo $j; ?>" checked >
                                                    <label class="switch-paddle" for="keep2-<?php echo $j; ?>">
                                                        <span class="show-for-sr"><?php echo $j; ?></span>
                                                        <span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('yes'); ?></span>
                                                        <span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('yes'); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cell medium-11">
                                            <?php echo nl2br($fact2['subrec']); ?>
                                        </div>
                                        <hr class="cell">
                                    <?php }
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php echo singleButton(); ?>

            </div>
        </form>
	<?php } ?>

	<?php if ($action == 'merge') {

		$ged     = $GEDCOM;
	    $gid1    = KT_Filter::post('gid1', KT_REGEX_XREF, null);
	    $gid2    = KT_Filter::post('gid2', KT_REGEX_XREF, null);
	    $ged1    = KT_Filter::post('ged1', null, null);
	    $ged2    = KT_Filter::post('ged2', null, null);
		$type1   = KT_Filter::postArray('type1');
		$type2   = KT_Filter::postArray('type2');
		$facts1  = KT_Filter::postArray('facts1');
		$facts2  = KT_Filter::postArray('facts2');
		$keep1   = KT_Filter::postArray('keep1');
		$keep2   = KT_Filter::postArray('keep2');

		if ($GEDCOM == $ged2) {
            $success = delete_gedrec($gid2, KT_GED_ID); ?>
            <div class="cell">
                <?php echo KT_I18N::translate('GEDCOM record successfully deleted.'); ?>
            </div>

            <?php
            // replace all the records that linked to gid2 //
            $ids = fetch_all_links($gid2, KT_GED_ID);

            foreach ($ids as $id) {
                $record = find_gedcom_record($id, KT_GED_ID, true); ?>
                <div class="cell">
                    <?php echo KT_I18N::translate('Updating linked record'); ?>
                </div>

                <?php
                $newrec = str_replace("@$gid2@", "@$gid1@", $record);
                $newrec = preg_replace(
                    '/(\n1.*@.+@.*(?:(?:\n[2-9].*)*))((?:\n1.*(?:\n[2-9].*)*)*\1)/',
                    '$2',
                    $newrec
                );
                replace_gedrec($id, KT_GED_ID, $newrec);
            }

            // Update any linked user-accounts
            KT_DB::prepare(
                "UPDATE `##user_gedcom_setting`".
                " SET setting_value=?".
                " WHERE gedcom_id=? AND setting_name='gedcomid' AND setting_value=?"
            )->execute(array($gid2, KT_GED_ID, $gid1));

            // Merge hit counters
            $hits=KT_DB::prepare(
                "SELECT page_name, SUM(page_count)".
                " FROM `##hit_counter`".
                " WHERE gedcom_id=? AND page_parameter IN (?, ?)".
                " GROUP BY page_name"
            )->execute(array(KT_GED_ID, $gid1, $gid2))->fetchAssoc();
            foreach ($hits as $page_name=>$page_count) {
                KT_DB::prepare(
                    "UPDATE `##hit_counter` SET page_count=?".
                    " WHERE gedcom_id=? AND page_name=? AND page_parameter=?"
                )->execute(array($page_count, KT_GED_ID, $page_name, $gid1));
            }
            KT_DB::prepare(
                "DELETE FROM `##hit_counter`".
                " WHERE gedcom_id=? AND page_parameter=?"
            )->execute(array(KT_GED_ID, $gid2));
        }

        $newgedrec = "0 @$gid1@ $type1\n";

        for ($i = 0; ($i < count($facts1) || $i < count($facts2)); $i ++) {
            if (isset($facts1[$i])) {
                if (in_array($i, $keep1)) {
                    $newgedrec .= $facts1[$i]['subrec']."\n"; ?>
                    <div class="cell">
                        <?php echo
                        KT_I18N::translate('Adding') . ' ' .
                        $facts1[$i]['fact'] . ' ' .
                        KT_I18N::translate('from') . ' ' .
                        $gid1; ?>
                    </div>
                <?php }
            }

            if (isset($facts2[$i])) {
                if (in_array($i, $keep2)) {
                    $newgedrec .= $facts2[$i]['subrec']."\n"; ?>
                    <div class="cell">
                        <?php echo
                        KT_I18N::translate('Adding') . ' ' .
                        $facts2[$i]['fact'] . ' ' .
                        KT_I18N::translate('from') . ' ' .
                        $gid2; ?>
                    </div>
                <?php }
            }
        }

        replace_gedrec($gid1, KT_GED_ID, $newgedrec);
        $rec = KT_GedcomRecord::getInstance($gid1); ?>
        <div class="cell">
            <?php echo KT_I18N::translate('Record %s successfully updated.', '<a href="' . $rec->getHtmlUrl() . '">' . $rec->getXref() . '</a>' ); ?>
        </div>

        <?php
        $fav_count = update_favorites($gid2, $gid1);

        if ($fav_count > 0) { ?>
            <div class="cell">
                <?php echo KT_I18N::plural('%s favorite updated', '%s favorites updated', $fav_count, $fav_count); ?>
            </div>
        <?php }
	}

echo pageClose();
