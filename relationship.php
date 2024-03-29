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

define('KT_SCRIPT_NAME', 'relationship.php');
require './includes/session.php';

global $controller, $iconStyle, $KT_TREE, $TEXT_DIRECTION, $KT_IMAGES;

$showCa = boolval(get_gedcom_setting(KT_GED_ID, 'CHART_SHOW_CAS', '1'));

$max_recursion = intval(get_gedcom_setting(KT_GED_ID, 'RELATIONSHIP_RECURSION'));

$controller = new KT_Controller_Relationship();
$controller->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('
				autocomplete();

				function swapIndis() {
					var xID   = jQuery("#selectedValue-pid1").val();
					var xName = jQuery("#autocompleteInput-pid1").val();
					var yID   = jQuery("#selectedValue-pid2").val();
					var yName = jQuery("#autocompleteInput-pid2").val();

					jQuery("#selectedValue-pid1").val(yID);
					jQuery("#selectedValue-pid2").val(xID);
					jQuery("#autocompleteInput-pid1").val(yName);
					jQuery("#autocompleteInput-pid2").val(xName);
				}
			');

$pid1       = KT_Filter::get('pid1', KT_REGEX_XREF);
$pid2       = KT_Filter::get('pid2', KT_REGEX_XREF);
$show_full  = KT_Filter::getInteger('show_full', 0, 1, get_gedcom_setting(KT_GED_ID, 'PEDIGREE_FULL_DETAILS'));
$recursion  = KT_Filter::getInteger('recursion', 0, $max_recursion, 0);
$find		= KT_Filter::getInteger('find', 1, 7, 1);
$beforeJD	= KT_Filter::getInteger('beforeJD', 0, PHP_INT_MAX, null);

$person1	= KT_Person::getInstance($pid1, $KT_TREE);
$person2	= KT_Person::getInstance($pid2, $KT_TREE);

if ($beforeJD) {
	$ymd = cal_from_jd($beforeJD, CAL_GREGORIAN);
	$date = new Date($ymd["day"] . ' '. strtoupper($ymd["abbrevmonth"]) . ' ' . $ymd["year"]);
	$dateDisplay = $date->display();
}

if ($person1 && $person2) {
	$pageTitle = KT_I18N::translate(/* I18N: %s are individual’s names */ 'Relationships between %1$s and %2$s', $person1->getFullName(), $person2->getFullName());
	if ($beforeJD) {
		//It's ok to always print this: common ancestors are always before the given date as well!
		$pageTitle .= ' (';
		$pageTitle .= KT_I18N::translate('established before %1$s', $dateDisplay);
		$pageTitle .= ')';
	}
	$controller
		->setPageTitle($pageTitle)
		->PageHeader();
	$caAndPaths = $controller->calculateCaAndPaths_123456($person1, $person2, $find, $recursion, $beforeJD);

} else {
	$controller
		->setPageTitle(KT_I18N::translate('Relationships'))
		->pageHeader();
	$caAndPaths = array();
}

$chart1 = ($find == 1) || (boolval(get_gedcom_setting(KT_GED_ID, 'CHART_1', '1')));
$chart2 = ($find == 2) || (boolval(get_gedcom_setting(KT_GED_ID, 'CHART_2', '0')));
$chart3 = ($find == 3) || (boolval(get_gedcom_setting(KT_GED_ID, 'CHART_3', '1')));
$chart4 = ($find == 4) || (boolval(get_gedcom_setting(KT_GED_ID, 'CHART_4', '1')));
$chart5 = ($find == 5) || (boolval(get_gedcom_setting(KT_GED_ID, 'CHART_5', '1')));
$chart6 = ($find == 6) || (boolval(get_gedcom_setting(KT_GED_ID, 'CHART_6', '0')));
$chart7 = ($find == 7) || (boolval(get_gedcom_setting(KT_GED_ID, 'CHART_7', '0')));


$titleConfigLink = '
	<a
		href="module.php?mod=chart_relationship&amp;mod_action=admin_config" 
		class="hide-for-print" 
		target="_blank" 
		rel="noopener noreferrer"
	>
		<i class="' . $iconStyle . ' fa-cog"></i>
	</a>
';

$extendedPageTitle = $controller->getPageTitle() . (KT_USER_IS_ADMIN ? $titleConfigLink : ''); 

echo pageStart('relationship', $extendedPageTitle); ?>

	<form class="cell" name="people" method="get" action="?">
		<?php if ($beforeJD !== null) { ?>
			<input type="hidden" name="beforeJD" value="<?php echo $beforeJD; ?>">
		<?php } ?>
		<input type="hidden" name="ged" value="<?php echo KT_GEDCOM; ?>">

		<div class="grid-x grid-margin-x">
			<div class="cell medium-4">
				<label class="h5" for="autocompleteInput"><?php echo KT_I18N::translate('Individual 1') ?></label>
				<?php echo autocompleteHtml(
					'pid1', // id
					'INDI', // TYPE
					'', // autocomplete-ged
					strip_tags($person1->getLifespanName()), // input value
					'', // placeholder
					'pid1', // hidden input name
					$pid1, // hidden input value
				); ?>
			</div>
			<div class="cell medium-4">
				<label class="h5" for="autocompleteInput"><?php echo KT_I18N::translate('Individual 1') ?></label>
				<?php echo autocompleteHtml(
					'pid2', // id
					'INDI', // TYPE
					'', // autocomplete-ged
					strip_tags($person2->getLifespanName()), // input value
					'', // placeholder
					'pid2', // hidden input name
					$pid2, // hidden input value
				); ?>
			</div>
			<div class="cell medium-2">
				<label class="h5" >&nbsp;</label>
				<button class="button primary swap" onclick="swapIndis(); return false;">
					<i class="<?php echo $iconStyle; ?> fa-shuffle"></i>
					<?php echo /* I18N: Reverse the order of two individuals */ KT_I18N::translate('Swap individuals') ?>
				</button>
			</div>
			<div class="cell medium-2">
				<label class="h5" >&nbsp;</label>
				<?php echo singleButton('fa-show', 'Show'); ?>
			</div>
		</div>

		<fieldset class="fieldset">
			<legend class="class h5">
				<?php echo KT_I18N::translate('Select detail level'); ?>
			</legend>
			<?php if ($chart1): ?>
				<div class="cell detailLevel">
					<input type="radio" class="inline" id="chart1" name="find" value="1" <?php echo ($find === 1) ? 'checked' : ''; ?>>
					<label for = "chart1" class="inline"><?php echo KT_I18N::translate('Find a closest relationship via common ancestors'); ?></label>
				</div>
			<?php endif; ?>
			<?php if ($chart2): ?>
				<div class="cell detailLevel">
					<input type="radio" class="inline" id="chart2" name="find" value="2"<?php echo ($find === 2) ? 'checked' : ''; ?>>
					<label for = "chart2" class="inline"><?php echo KT_I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'); ?></label>
				</div>
			<?php endif; ?>
			<?php if ($chart3): ?>
				<div class="cell detailLevel">
					<input type="radio" class="inline" id="chart3" name="find" value="3"<?php echo ($find === 3) ? 'checked' : ''; ?>>
					<label for = "chart3" class="inline"><?php echo KT_I18N::translate('Find all relationships via lowest common ancestors'); ?></label>
				</div>
			<?php endif; ?>
			<?php if ($beforeJD && ($chart4 || $chart5 || $chart6 || $chart7)): ?>
			<p class="small text-muted">
				<?php echo I18N::translate('The following options refer to overall connections established before %1$s.',$dateDisplay) ?>

			</p>
			<?php endif; ?>
			<?php if ($chart4): ?>
				<div class="cell detailLevel">
					<input type="radio" class="inline" id="chart4" name="find" value="4"<?php echo ($find === 4) ? 'checked' : ''; ?>>
					<label for = "chart4" class="inline"><?php echo KT_I18N::translate('Find the closest overall connections (preferably via common ancestors)'); ?></label>
				</div>
			<?php endif; ?>
			<?php if ($chart7): ?>
				<div class="cell detailLevel">
					<input type="radio" class="inline" id="chart7" name="find" value="7"<?php echo ($find === 7) ? 'checked' : ''; ?>>
					<label for = "chart7" class="inline"><?php echo KT_I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'); ?></label>
				</div>
			<?php endif; ?>
			<?php if ($max_recursion == 0): ?>
				<?php if ($chart5): ?>
					<div class="cell detailLevel">
						<input type="radio" class="inline" id="chart5" name="find" value="5"<?php echo ($find === 5) ? 'checked' : ''; ?>>
						<label for = "chart5" class="inline"><?php echo KT_I18N::translate('Find the closest overall connections') ?>
					</div>
				<?php endif; ?>
			<?php else: ?>
				<?php if ($chart5): ?>
					<div class="cell detailLevel">
						<input type="radio" class="inline" id="chart5" name="find" value="5"<?php echo ($find === 5) ? 'checked' : ''; ?>>
						<label for = "chart5" class="inline"><?php echo KT_I18N::translate('Find the closest overall connections') ?></label>
					</div>
				<?php endif; ?>
				<?php if ($chart6): ?>
					<div class="cell detailLevel">
						<input type="radio" class="inline" id="chart6" name="find" value="6"<?php echo ($find === 6) ? 'checked' : ''; ?>>
						<label for = "chart6" class="inline">
							<?php if ($max_recursion == '99'): ?>
								<?php echo KT_I18N::translate('Find all overall connections') ?>
							<?php else: ?>
								<?php echo KT_I18N::translate('Find other overall connections') ?>
							<?php endif; ?>
						</label>
						<input type="hidden" name="recursion" value="<?php echo $max_recursion ?>">
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</fieldset>
	</form>
	
	<?php if ($person1 && $person2) {
		if ($TEXT_DIRECTION=='ltr') {
			$horizontal_arrow = '<i class="' . $iconStyle . ' fa fa-arrow-right"></i>';
			$diagonal1        = $KT_IMAGES['dline'];
			$diagonal2        = $KT_IMAGES['dline2'];

		} else {
			$horizontal_arrow = '<i class="' . $iconStyle . ' fa fa-arrow-leftt"></i>';
			$diagonal1        = $KT_IMAGES['dline2'];
			$diagonal2        = $KT_IMAGES['dline'];
		}
		$up_arrow   = ' <i class="' . $iconStyle . ' fa fa-arrow-up"></i>';
		$down_arrow = ' <i class="' . $iconStyle . ' fa fa-arrow-down"></i>';

		if ($find == 3) {
			//$cor = $controller->getCorFromPaths($paths);
			$corPlus	= $controller->getCorFromCaAndPaths($caAndPaths);
			$cor		= $corPlus->getCor(); ?>
			<div class="callout warning">
				<?php echo KT_I18N::translate('Uncorrected CoR (Coefficient of Relationship): %s.', KT_I18n::percentage($cor, 2)); ?>
				<span>
					<br>
					<?php echo /* I18N: Configuration option */ KT_I18N::translate('All paths between the two individuals that contribute to the CoR (Coefficient of Relationship), as defined here: <a href = "http://www.genetic-genealogy.co.uk/Toc115570135.html" target="_blank" rel="noopener noreferrer">Coefficient of Relationship</a>'); ?>
				</span>
				<span>
					<br>
					<?php echo KT_I18N::translate('(Number of relationships: %s)', count($caAndPaths)); ?>
				<span>
			</div>
			
			<?php if (count($caAndPaths) > 1) {
				$er = $corPlus->getEquivalentRelationships();
				echo '(';
				if ($er === null) {
					echo KT_I18N::translate('that\'s overall not significantly closer than the closest relationship via common ancestors');
				} else {
					if ($corPlus->getActuallyBetterThan() === 0) {
						echo KT_I18N::translate('that\'s overall as close as:').' ';
					} else if ($corPlus->getActuallyBetterThan() < 0) {
						echo KT_I18N::translate('that\'s overall almost as close as:').' ';
					} else {
						echo KT_I18N::translate('that\'s overall closer than:').' ';
					}
					echo get_relationship_name_from_path(implode('', $er), $person1, $person2);
				}
				echo ')';
			}
		}

		$num_paths = 0;
		
		foreach ($caAndPaths as $caAndPath) {
			$path = $caAndPath->getPath();

			// Extract the relationship names between pairs of individuals
			$relationships = $controller->oldStyleRelationshipPath($path);
			if (empty($relationships)) {
				// Cannot see one of the families/individuals due to privacy
				continue;
			}
			$sosa = 0;
			$num_paths++; ?>
			
			<h3>
				<?php if (count($caAndPaths) > 1) { ?>
					<a href="#" onclick="return expand_layer('rel_<?php echo $num_paths; ?>');" class="top">
						<i id="'rel_<?php echo $num_paths; ?>'_img" class="icon-minus" title="<?php echo KT_I18N::translate('View Relationship'); ?>"></i>
					</a> 
				<?php }
				echo KT_I18N::translate('Relationship: %s', get_relationship_name_from_path(implode('', $relationships), $person1, $person2)); ?>
			</h3>

			<?php //add common ancestors if configured and not already included
			$slcaKey = $caAndPath->getCommonAncestor();
			$fam     = null;
			if ($slcaKey !== null && $showCa) {
				$record = KT_GedcomRecord::getInstance($slcaKey, $KT_TREE);
				if ($record->getType() === 'INDI') {
					//skip - slca is already in the path
				} else {
					$fam = $record;
				}
			}

			// Use a table/grid for layout
			$table = array();
			// Current position in the grid
			$x     = 0;
			$y     = 0;
			// Extent of the grid
			$min_y = 0;
			$max_y = 0;
			$max_x = 0;

			// For each node in the path
			foreach ($path as $n => $xref) {
				if ($n % 2 === 1) {
					switch ($relationships[$n]) {
						case 'hus':
						case 'wif':
						case 'spo':
						case 'bro':
						case 'sis':
						case 'sib':
							//only draw this in certain cases!
							if (!$fam || count($fam->getSpouses() === 0)) {
								$table[$x + 1][$y] = '
									<div class="hline">
										<div>' . $horizontal_arrow . '</div>
										<div>
											<span>' . get_relationship_name_from_path($relationships[$n], KT_Person::getInstance($path[$n - 1], $KT_TREE), KT_Person::getInstance($path[$n + 1], $KT_TREE)) . '</span>
										</div>
									</div>
								';
							} else {
								//keep the relationship for later
								$skippedRelationship = $relationships[$n];
							}
							$x += 2;
							break;
						case 'son':
						case 'dau':
						case 'chi':
							if ($n > 2 && preg_match('/fat|mot|par/', $relationships[$n - 2])) {
								$table[$x + 1][$y - 1] = '
									<div style="background:url(' . $diagonal2 . ') center; width: 64px; height: 64px; text-align: center;">
										<div style="height: 32px; text-align: end;"><span style="background-color:white;">' . get_relationship_name_from_path($relationships[$n], KT_Person::getInstance($path[$n - 1], $KT_TREE), KT_Person::getInstance($path[$n + 1], $KT_TREE)) . '</span></div>
										<div style="height: 32px; text-align: start;">' . $down_arrow . '</div>
									</div>
								';
								$x += 2;
							} else {
								$table[$x][$y - 1] = '
									<div class="vline">
										<div>' . $down_arrow . '</span></div>
										<div>
											<span>
												' . get_relationship_name_from_path($relationships[$n], KT_Person::getInstance($path[$n - 1], $KT_TREE), KT_Person::getInstance($path[$n + 1], $KT_TREE)) . '
												</span>
										</div>
										<div>' . $down_arrow . '</div>
									</div>
								';
							}
							$y -= 2;
							break;
						case 'fat':
						case 'mot':
						case 'par':
							if ($n > 2 && preg_match('/son|dau|chi/', $relationships[$n - 2])) {
								$table[$x + 1][$y + 1] = '
									<div style="background:url(' . $diagonal1 . ') center; width: 64px; height: 64px; text-align: center;">
										<div style="height: 32px; text-align: start;"><span style="background-color:white;">' . get_relationship_name_from_path($relationships[$n], KT_Person::getInstance($path[$n - 1], $KT_TREE), KT_Person::getInstance($path[$n + 1], $KT_TREE)) . '</span></div>
										<div style="height: 32px; text-align: end;">' . $up_arrow . '</div>
									</div>
								';
								$x += 2;
							} else {
								$table[$x][$y + 1] = '
									<div class="vline">
										<div>' . $up_arrow . '</div>
										<div>
											<span>
												' . get_relationship_name_from_path($relationships[$n], KT_Person::getInstance($path[$n - 1], $KT_TREE), KT_Person::getInstance($path[$n + 1], $KT_TREE)) . '
											</span>
										</div>
										<div>' . $up_arrow . '</div>
									</div>
								';
							}
							$y += 2;
							break;
					}

					$max_x = max($max_x, $x);
					$min_y = min($min_y, $y);
					$max_y = max($max_y, $y);

				} else {
					$individual = KT_Person::getInstance($xref, $KT_TREE);
					ob_start();
					print_pedigree_person($individual, $show_full);
					$table[$x][$y] = ob_get_clean();
				}
			}

			if ($fam) {
				$size = count($fam->getSpouses());

				if ($size > 0) { //there may be families with siblings only (we still have a ca in that case)
					$x = 0;
					$y = $max_y + count($fam->getSpouses()) + 1;
					foreach ($fam->getSpouses() as $indi) {
						$individual = KT_Person::getInstance($indi->getXref(), $KT_TREE);
						ob_start();
						print_pedigree_person($individual, $show_full);
						$table[$x][$y] = ob_get_clean();
						//$x += 2;
						$y -= 1;
					}

					//draw the extra lines
					$relUp = KT_I18N::translate('parents');
					if ($size == 1) {
						//single parent (spouse unknown)
						switch ($individual->getSex()) {
						case 'M':
							$relUp = KT_I18N::translate('father');
							break;
						case 'F':
							$relUp = KT_I18N::translate('mother');
							break;
						default:
							$relUp = KT_I18N::translate('parent');
						}
					}

					switch ($skippedRelationship) {
						case 'bro':
							$relDn = KT_I18N::translate('son');
							break;
						case 'sis':
							$relDn = KT_I18N::translate('daughter');
							break;
						default:
							$relDn = KT_I18N::translate('child');
					}

					$table[0][$max_y + 1] = '
						<div style="background:url(' . $KT_IMAGES['vline'] . ') repeat-y center; height: 64px; text-align:center; ">
							<div style="display: inline-block; width: 50%; line-height: 32px;"><span style="background-color:white;">' . $relUp . '</span></div>
							<div style="display: inline-block; width: 50%; line-height: 32px">' . $up_arrow . '</div>
						</div>
					';

					$table[1][$max_y + 1] = '
						<div style="background:url(' . $diagonal2 . '); width: 64px; height: 64px; text-align: center;">
							<div style="height: 32px; text-align: end;"><span style="background-color:white;">' . $relDn . '</span></div>
							<div style="height: 32px; text-align: start;">' . $down_arrow . '</div>
						</div>
					';

					$max_x = max($max_x, $x); //shouldn't actually make any difference
					$max_y += count($fam->getSpouses()) + 1;
				}
			} ?>

			<table id="rel_<?php echo $num_paths; ?>" class="unstriped">
				<?php for ($y = $max_y; $y >= $min_y; --$y) { ?>
					<tr>
						<?php for ($x = 0; $x <= $max_x; ++$x) { ?>
							<td style="padding: 0;">
								<?php if (isset($table[$x][$y])) {
									echo $table[$x][$y];
								} ?>
							</td>
						<?php } ?>
					</tr>
				<?php } ?>
			</table><?php

		}

		if (!$num_paths) { ?>
			<div class="callout alert">
				<?php echo KT_I18N::translate('No link between the two individuals could be found.');
				if ($beforeJD !== null) {
					if (KT_USER_GEDCOM_ADMIN) {
						echo ' ';
						echo KT_I18N::translate('If this is unexpected, and there are recent changes, you may have to follow this link: ');
						?>
						<a href="module.php?mod=batch_update&mod_action=admin_batch_update&xref=&action=&data=&ged=Osborne.ged&plugin=update_links_bu_plugin">
							<?php echo I18N::translate('Update missing relationship links'); ?>
						</a>
						<?php
					}
				} ?>
			</div>
		<?php }		

	}

echo pageClose();
