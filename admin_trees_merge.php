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

$controller = new KT_Controller_Page;
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Merge records'))
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('autocomplete();')
	->pageHeader();

require_once KT_ROOT . 'includes/functions/functions_edit.php';
require_once KT_ROOT . 'includes/functions/functions_import.php';

$recordTypes = array(
	KT_I18N::translate('Individuals')	=> 'INDI',
	KT_I18N::translate('Families')		=> 'FAM',
	KT_I18N::translate('Sources')		=> 'SOUR',
);

$ged    = $GEDCOM;
$gid1   = KT_Filter::post('gid1', KT_REGEX_XREF);
$gid2   = KT_Filter::post('gid2', KT_REGEX_XREF);
$action = KT_Filter::post('action', 'choose|select|merge', 'choose');
$ged1   = KT_Filter::post('ged1', null, $ged);
$ged2   = KT_Filter::post('ged2', null, $ged);
$keep1  = KT_Filter::postArray('keep1');
$keep2  = KT_Filter::postArray('keep2');
$type	= KT_Filter::post('record_type');

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

echo pageStart('merge_records', $controller->getPageTitle());

	if ($action != 'choose') {
		if ($gid1 == $gid2 && $GEDCOM == $ged2) {
			$action='choose'; ?>
			<div class="cell callout alert">
				<?php echo KT_I18N::translate('You entered the same IDs.  You cannot merge the same records.'); ?>
			</div>

		<?php } else {
			$gedrec1 = find_gedcom_record($gid1, KT_GED_ID, true);
			$gedrec2 = find_gedcom_record($gid2, get_id_from_gedcom($ged2), true);

			// Fetch the original XREF - may differ in case from the supplied value
			$tmp	= new KT_Person($gedrec1);
			$gid1	= $tmp->getXref();
			$name1	= $tmp->getLifespanName();

			$tmp	= new KT_Person($gedrec2);
			$gid2	= $tmp->getXref();
			$name2	= $tmp->getLifespanName();

			if (empty($gedrec1)) { ?>
				<div class="cell callout alert">
					<?php if (is_null($gid1)) {
						$gid1 = '????';
					} ?>
					<?php echo KT_I18N::translate('Unable to find record with ID %s, in tree "%s"', $gid1, $ged1); ?>:
				</div>
				<?php
				$gid1	= null;
				$action = 'choose';

			} elseif (empty($gedrec2)) { ?>
				<?php if (is_null($gid2)) {
					$gid2 = '????';
				} ?>
				<div class="cell callout alert">
					<?php echo KT_I18N::translate('Unable to find record with ID %s, in tree "%s"', $gid2, $ged2); ?>:
				</div>
				<?php
				$gid2	= null;
				$action = 'choose';

			} else {
				$type1 = '';
				$ct = preg_match("/0 @$gid1@ (.*)/", $gedrec1, $match);
				if ($ct>0) {
					$type1 = trim($match[1]);
				}
				$type2	= "";
				$ct		= preg_match("/0 @$gid2@ (.*)/", $gedrec2, $match);

	 			if ($ct>0) {
					$type2 = trim($match[1]);
				}

				if (!empty($type1) && ($type1 != $type2)) { ?>
					<div class="cell callout alert">
						<?php echo KT_I18N::translate('Records are not the same type.  Cannot merge records that are not the same type.'); ?>
					</div>
					<?php $action = 'choose';

				} else {
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

					if ($action == 'select') { ?>

						<form class="cell" method="post" action="admin_trees_merge.php">
							<input type="hidden" name="gid1" value="<?php echo $gid1; ?>">
							<input type="hidden" name="gid2" value="<?php echo $gid2; ?>">
							<input type="hidden" name="ged" value="<?php echo $GEDCOM; ?>">
							<input type="hidden" name="ged2" value="<?php echo $ged2; ?>">
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

								<div class="cell callout success">
									<?php echo KT_I18N::translate('
										The following facts did not match.
										Select the information you would like to keep
										using the switches below.
									'); ?>
								</div>
								<div class="cell">
									<div class="grid-x grid-padding-x unmatched">
										<div class="cell small-6 text-center header">
											<?php echo KT_I18N::translate('Record') . ' ' . $gid1 . ' - ' . $name1; ?>
										</div>
										<div class="cell small-6 text-center header">
											<?php echo KT_I18N::translate('Record') . ' ' . $gid2 . ' - ' . $name2; ?>
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

					<?php } elseif ($action == 'merge') { ?>

							<?php if ($GEDCOM == $ged2) {
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
							<?php } ?>

					<?php }
				}
			}
		}
	}

	if ($action == 'choose') { ?>

		<form class="cell" method="post" name="merge" action="admin_trees_merge.php">
			<input type="hidden" name="action" value="select">
			<div class="grid-x grid-margin-x">
				<div class="cell callout warning helpcontent">
					<?php echo KT_I18N::translate('
						Select two GEDCOM records to merge.
						The records must be of the same type, but can from
						different family trees.
					'); ?>
				</div>
				<!-- Record type -->
				<div class="cell medium-2">
					<label for="ged"><?php echo KT_I18N::translate('Select record type'); ?></label>
				</div>
				<div class="cell medium-2">
					<select name="record_type" onchange="merge.submit();">
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
				<div class="cell medium-8"></div>
				<!-- Merge to -->
				<div class="cell medium-2">
					<label for="ged"><?php echo KT_I18N::translate('Merge to:'); ?></label>
				</div>
				<div class="cell medium-4">
					<?php echo select_ged_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM, ' onchange="merge.submit();"'); ?>
				</div>
				<div class="cell medium-4">
					<?php echo autocompleteHtml(
						'gid1',
						$type,
						'',
						$gid1,
						array_search($type, $recordTypes),
						'gid1'
					); ?>
				</div>
				<div class="cell medium-2"></div>
				<!-- Merge From -->
				<div class="cell medium-2">
					<label for="ged"><?php echo KT_I18N::translate('Merge from:'); ?></label>
				</div>
				<div class="cell medium-4">
					<?php echo select_ged_control('ged2', KT_Tree::getNameList(), null, KT_GEDCOM, ' onchange="merge.submit();"'); ?>
				</div>
				<div class="cell medium-4">
					<?php echo autocompleteHtml(
						'gid2',
						$type,
						'',
						$gid2,
						array_search($type, $recordTypes),
						'gid2'
					); ?>
				</div>
				<div class="cell medium-2"></div>

				<?php echo singleButton('Next'); ?>

			</div>
		</form>

	<?php }

echo pageClose();
