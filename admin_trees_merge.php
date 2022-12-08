<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net.
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

$recordTypes = [
	KT_I18N::translate('Individual') => 'INDI',
	KT_I18N::translate('Family') => 'FAM',
	KT_I18N::translate('Note') => 'NOTE',
	KT_I18N::translate('Repository') => 'REPO',
	KT_I18N::translate('Source') => 'SOUR',
];

$controller = new KT_Controller_Page();
$controller
	->pageHeader()
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Merge records'))
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('autocomplete();')
;

$action       = KT_Filter::post('action', 'data_type|choose|select|merge', 'data_type');
$type         = KT_Filter::post('record_type');
$recordIdFrom = KT_Filter::post('recordIdFrom', KT_REGEX_XREF, null);
$recordIdTo   = KT_Filter::post('recordIdTo', KT_REGEX_XREF, null);
$gedIDFrom    = KT_Filter::post('gedIDFrom') ? KT_Filter::post('gedIDFrom') : $GEDCOM;
$gedIDTo      = KT_Filter::post('gedIDTo') ? KT_Filter::post('gedIDFrom') : $GEDCOM;
$gedIDTo      = KT_Filter::post('gedIDTo', null, null);
$keepFrom     = KT_Filter::postArray('keepFrom', '', []);
$keepTo       = KT_Filter::postArray('keepTo', '', []);

$gedrecFrom   = find_gedcom_record($recordIdFrom, $gedIDFrom, true);
$gedrecTo     = find_gedcom_record($recordIdTo, $gedIDTo, true);

switch ($type) {
	case 'INDI':
		$recordFrom = $recordIdFrom ? KT_Person::getInstance($recordIdFrom) : '';
		$recordTo   = $recordIdTo ? KT_Person::getInstance($recordIdTo) : '';
		$nameFrom   = $recordIdFrom ? $recordFrom->getLifespanName() : '';
		$nameTo     = $recordIdTo ? $recordTo->getLifespanName() : '';

		break;

	case 'FAM':
		$recordFrom = $recordIdFrom ? KT_Family::getInstance($recordIdFrom) : '';
		$recordTo   = $recordIdTo ? KT_Family::getInstance($recordIdTo) : '';
		$nameFrom   = $recordIdFrom ? $recordFrom->getFullName() : '';
		$nameTo     = $recordIdTo ? $recordTo->getFullName() : '';

		break;

	case 'NOTE':
		$recordFrom = $recordIdFrom ? KT_Note::getInstance($recordIdFrom) : '';
		$recordTo   = $recordIdTo ? KT_Note::getInstance($recordIdTo) : '';
		$nameFrom   = $recordIdFrom ? $recordFrom->getFullName() : '';
		$nameTo     = $recordIdTo ? $recordTo->getFullName() : '';

		break;

	case 'REPO':
		$recordFrom = $recordIdFrom ? KT_Repository::getInstance($recordIdFrom) : '';
		$recordTo   = $recordIdTo ? KT_KT_Repository::getInstance($recordIdTo) : '';
		$nameFrom   = $recordIdFrom ? $recordFrom->getFullName() : '';
		$nameTo     = $recordIdTo ? $recordTo->getFullName() : '';

		break;

	case 'SOUR':
		$recordFrom = $recordIdFrom ? KT_Source::getInstance($recordIdFrom) : '';
		$recordTo   = $recordIdTo ? KT_Source::getInstance($recordIdTo) : '';
		$nameFrom   = $recordIdFrom ? $recordFrom->getFullName() : '';
		$nameTo     = $recordIdTo ? $recordTo->getFullName() : '';

		break;
}

echo relatedPages($trees, KT_SCRIPT_NAME);

echo pageStart('merge_records', $controller->getPageTitle()); ?>
	<?php if ('data_type' == $action) { ?>
		<form class="cell" method="post" name="datatype" action="<?php echo KT_SCRIPT_NAME; ?>">
			<input type="hidden" name="action" value="choose">
			<input type="hidden" name="record_type" value="<?php echo $type; ?>">
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

	<?php if ('choose' != $action) {
		$factsFrom = [];
		$factsTo   = [];

		$prev_tags = [];
		$ct = preg_match_all('/\n1 (\w+)/', $gedrecTo, $match, PREG_SET_ORDER);
		for ($i = 0; $i < $ct; $i++) {
			$fact = trim($match[$i][1]);
			if (isset($prev_tags[$fact])) {
				$prev_tags[$fact]++;
			} else {
				$prev_tags[$fact] = 1;
			}
			$subrec = get_sub_record(1, "1 $fact", $gedrecTo, $prev_tags[$fact]);
			$factsTo[] = ['fact' => $fact, 'subrec' => trim($subrec)];
		}

		$prev_tags = [];
		$ct = preg_match_all('/\n1 (\w+)/', $gedrecFrom, $match, PREG_SET_ORDER);
		for ($i = 0; $i < $ct; $i++) {
			$fact = trim($match[$i][1]);
			if (isset($prev_tags[$fact])) {
				$prev_tags[$fact]++;
			} else {
				$prev_tags[$fact] = 1;
			}
			$subrec = get_sub_record(1, "1 $fact", $gedrecFrom, $prev_tags[$fact]);
			$factsFrom[] = ['fact' => $fact, 'subrec' => trim($subrec)];
		}

		if ('select' == $action) { ?>

			<form class="cell" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
	            <input type ="hidden" name="recordIdTo" value="<?php echo $recordIdTo; ?>">
	            <input type ="hidden" name="recordIdFrom" value="<?php echo $recordIdFrom; ?>">
	            <input type ="hidden" name="gedIDTo" value="<?php echo $gedIDTo; ?>">
	            <input type ="hidden" name="gedIDFrom" value="<?php echo $gedIDFrom; ?>">
				<input type ="hidden" name="record_type" value="<?php echo $type; ?>">
	          	<input type ="hidden" name="action" value="merge"> 

	            <?php
				$equal_count = 0;
				$skipTo = [];
				$skipFrom = [];
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
	                        <?php foreach ($factsTo as $i => $factTo) {
	                        	foreach ($factsFrom as $j => $factFrom) {
	                        		if (utf8_strtoupper($factTo['subrec']) == utf8_strtoupper($factFrom['subrec'])) {
	                        			$skipTo[] = $i;
	                        			$skipFrom[] = $j;
	                        			$equal_count++; ?>
	                                    <div class="cell medium-2"><?php echo KT_I18N::translate($factTo['fact']); ?>
	                                        <input type="hidden" name="keepTo[]" value="<?php echo $i; ?>">
	                                    </div>
	                                    <div class="cell medium-10">
	                                        <?php echo nl2br($factTo['subrec']); ?>
	                                    </div>
	                                <?php }
	                        		}
	                        }
						if (0 == $equal_count) { ?>
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
	                        from each person, using the switches below.
	                    '); ?>
	                </div>
	                <div class="cell">
	                    <div class="grid-x grid-margin-x unmatched">
	                        <div class="cell small-6 text-center header">
	                            <?php echo KT_I18N::translate('From record') . ' ' . $recordIdFrom . ' - ' . $nameFrom; ?>
	                        </div>
	                        <div class="cell small-6 text-center header">
	                            <?php echo KT_I18N::translate('To record') . ' ' . $recordIdTo . ' - ' . $nameTo; ?>
	                        </div>
	                        <div class="cell small-6">
	                            <div class="grid-x unmatchedL">
	                                <?php foreach ($factsFrom as $i => $factFrom) {
	                                	if (('CHAN' != $factFrom['fact']) && (!in_array($i, $skipFrom))) { ?>
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
	                                	if (('CHAN' != $factTo['fact']) && (!in_array($j, $skipTo))) { ?>
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

					<?php echo singleButton('Back'); ?>
					<?php echo singleButton('Merge'); ?>

	            </div>
	        </form>

		<?php } elseif ('merge' == $action) {

			$step = 1; ?>

			<dl class="cell grid-x grid-margin-y">

				<?php if ($gedIDTo == $gedIDFrom) {
					delete_gedrec($recordIdFrom, $gedIDFrom); ?>
		            <dt class="cell">
		            	<span><?php echo KT_I18N::translate('Step %s', $step); ?></span>
		            	<span><?php echo KT_I18N::translate('Delete the "Merge from" record'); ?></span>
		           	</dt>
					<dd class="cell">
						<?php echo KT_I18N::translate('Record <em>%1s (%2s)</em> has been deleted.', $nameFrom, $recordIdFrom); ?>
					</dd>

		            <?php
		            // replace all the links from and to recordIdFrom
					$ids = fetch_all_links($recordIdFrom, $gedIDFrom);
					$step++; ?>
		            <dt class="cell">
		            	<span><?php echo KT_I18N::translate('Step %s', $step); ?></span>
		            	<span><?php echo KT_I18N::translate('Change all GEDCOM links from or to the "Merge from" record to the "Merge to" record'); ?></span>
		           	</dt>
		           	<dd>
						<ul class="cell medium-10">
							<?php foreach ($ids as $id) {
								$record = find_gedcom_record($id, $gedIDFrom, true); ?>

					            <li class="cell medium-7 medium-offset-4">
					                <?php echo KT_I18N::translate('Updating linked record'), ' ', $id; ?>
					            </li>

				                <?php
								$newrec = str_replace("@{$recordIdFrom}@", "@{$recordIdTo}@", $record);
								$newrec = preg_replace(
									'/(\n1.*@.+@.*(?:(?:\n[2-9].*)*))((?:\n1.*(?:\n[2-9].*)*)*\1)/',
									'$2',
									$newrec
								);

								replace_gedrec($id, $gedIDTo, $newrec); ?>
				 
				            <?php } ?>
			        	</ul>
			        </dd>

		        	<?php $step++; ?>
		            <dt class="cell">
		            	<span><?php echo KT_I18N::translate('Step %s', $step); ?><span>
					   <span> <?php echo KT_I18N::translate('Updating other linked data'); ?><span>
		           	</dt>
					<?php
					// Linked user-accounts
					KT_DB::prepare(
						'UPDATE `##user_gedcom_setting`' .
						' SET setting_value=?' .
						" WHERE gedcom_id=? AND setting_name='gedcomid' AND setting_value=?"
					)->execute([$recordIdFrom, $gedIDFrom, $recordIdTo]); ?>
					<dd class="cell">
					    <?php echo KT_I18N::translate('Linked user accounts updated'); ?>
					</dd>

			        <?php
			        // Hit counters
					$hits = KT_DB::prepare('
			            SELECT page_name, SUM(page_count)
			             FROM `##hit_counter`
			             WHERE gedcom_id=? AND page_parameter IN (?, ?)
			            GROUP BY page_name
			           ')->execute([$gedIDFrom, $recordIdTo, $recordIdFrom])->fetchAssoc();

					foreach ($hits as $page_name => $page_count) {
						KT_DB::prepare(
							'UPDATE `##hit_counter` SET page_count=?' .
							' WHERE gedcom_id=? AND page_name=? AND page_parameter=?'
						)->execute([$page_count, $gedIDFrom, $page_name, $recordIdTo]);
					}

					KT_DB::prepare(
						'DELETE FROM `##hit_counter`' .
						' WHERE gedcom_id=? AND page_parameter=?'
					)->execute([$gedIDFrom, $recordIdFrom]);
					?>
					<dd class="cell">
					    <?php echo KT_I18N::translate('Hit counters updated'); ?>
					</dd>
		 
			         <?php
			        // Favorites
					$fav_count = update_favorites($recordIdFrom, $recordIdTo, $gedIDTo);
					if ($fav_count > 0) { ?>
					<dd class="cell">
		                <?php echo KT_I18N::plural('%s favorite updated', '%s favorites updated', $fav_count, $fav_count); ?>
		            </dd>
			        <?php }
					}

					$newgedrec = "0 @{$recordIdTo}@ {$type}\n";
					$step++; ?>
					<dt class="cell">
						<span><?php echo KT_I18N::translate('Step %s', $step); ?></span>
						<span><?php echo KT_I18N::translate('Collect all merged facts for "Merge to" record'); ?></span>
					</dt>

		            <?php 
		            for ($i = 0; $i < count($factsTo) || $i < count($factsFrom); $i++) {

			           	if (isset($factsTo[$i])) {
			           		if (in_array($i, $keepTo)) {
		           				$newgedrec .= $factsTo[$i]['subrec'] . "\n"; ?>
		                        <dd class="cell">
		                            <?php echo KT_I18N::translate('Adding') . ' ' .
									   $factsTo[$i]['fact'] . ' ' .
									   KT_I18N::translate('from') . ' ' .
									   $recordIdTo; ?>
		                        </dd>
		                    <?php }
		           		}

			           	if (isset($factsFrom[$i])) {
			           		if (in_array($i, $keepFrom)) {
			           			$newgedrec .= $factsFrom[$i]['subrec'] . "\n"; ?>
		                        <dd class="cell">
		                            <?php echo KT_I18N::translate('Adding') . ' ' .
									   $factsFrom[$i]['fact'] . ' ' .
									   KT_I18N::translate('from') . ' ' .
									   $recordIdFrom; ?>
		                        </dd>
		                    <?php }
		           		}

		        }

				$step++; ?>
	            <dt class="cell">
					<span><?php echo KT_I18N::translate('Step %s', $step); ?></span>
					<span><?php echo KT_I18N::translate('Update the "Merge to" record'); ?></span>
				</dt>
				<?php
				replace_gedrec($recordIdTo, $gedIDTo, $newgedrec);
				$rec = KT_GedcomRecord::getInstance($recordIdTo); ?>
				<dd class="cell">
					<?php echo KT_I18N::translate('Record <a href="%1s"><em>%2s (%3s)</em> has been updated</a>', $rec->getHtmlUrl(), $nameTo, $rec->getXref()); ?>
				</dd>

		    </dl>

			<button class="button hollow returnToStart" type="button" onclick="window.location.href='<?php echo KT_SCRIPT_NAME; ?>'">
				<?php echo KT_I18N::translate('Merge another record'); ?>
			</button>

		<?php }
	}

	if ('choose' == $action) { ?>
		<form class="cell" method="post" name="merge" action="<?php echo KT_SCRIPT_NAME; ?>" data-abide data-live-validate="true" novalidate>
			<input type="hidden" name="action" value="select">
			<input type="hidden" name="record_type" value="<?php echo $type; ?>">

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
					<?php echo select_ged_control('gedIDFrom', KT_Tree::getIdList(), null, $gedIDFrom); ?>
				</div>
				<div class="cell medium-4">
					<?php echo autocompleteHtml('recordIdFrom', $type, '', $recordFrom ? strip_tags($recordFrom->getLifespanName()) : '', array_search($type, $recordTypes), 'recordIdFrom', $recordIdFrom, ' required ', 'autofocus'); ?>
				</div>
				<div class="cell medium-2"></div>
				<!-- Merge to -->
				<div class="cell medium-2">
					<label for="recordIdTo"><?php echo KT_I18N::translate('Merge to:'); ?></label>
				</div>
				<div class="cell medium-4">
					<?php echo select_ged_control('gedIDTo', KT_Tree::getIdList(), null, $gedIDTo); ?>
				</div>
				<div class="cell medium-4">
					<?php echo autocompleteHtml('recordIdTo', $type, '', $recordTo ? strip_tags($recordTo->getLifespanName()) : '', array_search($type, $recordTypes), 'recordIdTo', $recordIdTo, ' required ', '', ' data-validator="not_equalTo" data-not-equalTo="selectedValue-recordIdFrom" ' ); ?>
					<div class="cell alert callout" data-abide-error data-form-error-on="not_equalTo" style="display: none;">
						<?php echo KT_I18N::translate('You cannot merge the same records.'); ?>
					</div>
				</div>
				<div class="cell medium-2"></div>

				<?php echo singleButton('Back'); ?>
				<?php echo singleButton('Next'); ?>

			</div>
		</form>

	<?php }

	echo pageClose();
