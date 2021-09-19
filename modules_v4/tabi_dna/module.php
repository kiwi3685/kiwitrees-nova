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

class tabi_dna_KT_Module extends KT_Module implements KT_Module_IndiTab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('DNA connections');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the "Facts and events" module */ KT_I18N::translate('A list of all recorded DNA links for an individual.');
	}

	// Implement KT_Module_Tab
	public function defaultTabOrder() {
		return 250;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_USER; // default privacy = "members"
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'add-dna':
			$this->addDNA('add');
			break;
		case 'edit-dna':
			$this->addDNA('edit');
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Tab
	public function getTabContent() {
		global $controller, $iconStyle;

		self::updateSchema(); // make sure the favorites table has been created

		$person		= $controller->getSignificantIndividual();
		$fullname	= $controller->record->getFullName();
		$xref		= $controller->record->getXref();

		$controller
			->addExternalJavascript(KT_DATATABLES_JS)
			->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS);

		if (KT_USER_CAN_EDIT) {
			$controller
				->addExternalJavascript(KT_DATATABLES_BUTTONS)
				->addExternalJavascript(KT_DATATABLES_HTML5);
			$buttons = 'B';
		} else {
			$buttons = '';
		}

		$controller
			->addInlineJavascript('
				jQuery("#dnaTable").dataTable({
					dom: \'<"top"p' . $buttons . 'f<"clear">irl>t<"bottom"pl>\',
					' . KT_I18N::datatablesI18N() . ',
					buttons: [{extend: "csvHtml5", exportOptions: {}}],
					autoWidth: false,
					processing: true,
					retrieve: true,
					displayLength: 30,
					pagingType: "full_numbers",
					stateSave: true,
					stateDuration: -1,
					columns: [
						/*  0 name				*/ { },
						/*  1 relationship		*/ { },
						/*  2 cms				*/ { className: "text-right" },
						/*  3 segments			*/ { className: "text-right" },
						/*  4 percent			*/ { className: "text-right" },
						/*  5 common ancestor	*/ null,
						/*  6 source			*/ null,
						/*  7 note				*/ null,
						/*  9 date added		*/ null,
						/*  9 edit				*/ { className: "text-center" },
						/* 10 delete			*/ { className: "text-center" },
					],
					sorting: [[2,"desc"]],
				});
			');

		ob_start();

		if (KT_USER_CAN_EDIT) { ?>
			<div class="cell tabHeader">
				<div class="grid-x">
					<div class="cell">
						<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=add-dna&amp;pid=<?php echo $xref; ?>&amp;ged=<?php echo KT_GEDCOM; ?>" target="_blank">
							<i class="<?php echo $iconStyle; ?> fa-dna"></i>
							<?php echo KT_I18N::translate('Add DNA data'); ?>
						</a>
					</div>
				</div>
			</div>
		<?php } ?>
		<?php if ($person && $person->canDisplayDetails()) { ?>
			<div class="cell indiFact">
				<h5><?php echo KT_I18N::translate('Recorded DNA connections for %s', $fullname); ?></h5>
				<p data-toggle="help-dropdown">
					<?php echo $this->getDescription(); ?>
					<i class="<?php echo $iconStyle; ?> fa-question-circle alert"></i>
				</p>
				<div class="dropdown-pane" id="help-dropdown" data-dropdown data-close-on-click="true">
					<?php echo $this->DNAhelp('cms'); ?>
					<br><br>
					<?php echo $this->DNAhelp('seg'); ?>
					<br><br>
					<?php echo $this->DNAhelp('pdna'); ?>
				</div>
				<hr>
				<table id="dnaTable">
					<thead>
						<tr>
							<th><?php echo KT_I18N::translate('Name'); ?></th>
							<th><?php echo KT_I18N::translate('Relationship'); ?></th>
							<th><?php echo KT_I18N::translate('cMs'); ?></th>
							<th><?php echo KT_I18N::translate('Segments'); ?></th>
							<th><?php echo KT_I18N::translate('%% DNA'); ?></th>
							<th><?php echo KT_I18N::translate('Common ancestors'); ?></th>
							<th><?php echo KT_I18N::translate('Source'); ?></th>
							<th><?php echo KT_I18N::translate('Note'); ?></th>
							<th><?php echo KT_I18N::translate('Date added'); ?></th>
							<th><?php echo KT_I18N::translate('Edit'); ?></th>
							<?php //-- Select & delete
							if (KT_USER_GEDCOM_ADMIN) { ?>
								<th>
                                    <div class="delete_dna">
                                        <button type="submit" class="button tiny" onclick="if (confirm('<?php echo htmlspecialchars(KT_I18N::translate('Permanently delete these records?')); ?>')) {return checkbox_delete('dna');} else {return false;}">
                                            <?php echo KT_I18N::translate('Delete'); ?>
                                        </button>
                                        <input type="checkbox" onclick="toggle_select(this)">
									</div>
								</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php $rows = $this->getData($xref);
						foreach ($rows as $row) {
							$relationship = '';
							($xref == $row->id_a) ? $xrefA = $row->id_b : $xrefA = $row->id_a;
							$personA = KT_Person::getInstance($xrefA); ?>
							<tr>
								<td>
									<?php if ($personA) { ?>
										<a href="<?php echo $personA->getHtmlUrl(); ?>" target="_blank">
											<?php echo $personA->getFullName(); ?>
										</a>
									<?php } else { ?>
										<span class="error" title="<?php echo KT_I18N::translate('Invalid reference'); ?>"><?php echo $xrefA; ?></span>
									<?php } ?>
								</td>
								<td>
									<?php $relationship = $this->findRelationship($person, $personA);
									if ($relationship) { ?>
										<a href="relationship.php?pid1=<?php echo $person->getXref(); ?>&amp;pid2=<?php echo $personA->getXref(); ?>&amp;ged=<?php echo KT_GEDCOM; ?>&amp;find=1" target="_blank">
											<?php echo ucfirst($relationship); ?>
										</a>
									<?php } else {
										echo KT_I18N::translate('No relationship found');
									} ?>
								</td>
								<td><?php echo $row->cms ? KT_I18N::number($row->cms) : ''; ?></td>
								<td><?php echo $row->seg ? KT_I18N::number($row->seg) : ''; ?></td>
								<td><?php echo $row->percent ? KT_I18N::percentage($row->percent / 100, 2) : ''; ?></td>
								<td>
									<?php if ($relationship == KT_I18N::translate('father') || $relationship == KT_I18N::translate('mother') || $relationship == KT_I18N::translate('parent')) {
										echo KT_I18N::translate('Not applicable');
									} else {
										echo $this->findCommonAncestor($person, $personA);
									} ?>
								<td>
									<?php $source = KT_Source::getInstance($row->source);
									if ($source) { ?>
										<a href="<?php echo $source->getHtmlUrl(); ?>" target="_blank">
											<?php echo $source->getFullName(); ?>
										</a>
									<?php } else { ?>
										<span class="error" title="<?php echo KT_I18N::translate('Invalid reference'); ?>"><?php echo $row->source; ?></span>
									<?php } ?>
								</td>
								<td class="italic"><?php echo $row->note; ?></td>
								<td>
									<?php echo timestamp_to_gedcom_date(strtotime($row->date))->Display(); ?>
								</td>
								<td>
									<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=edit-dna&amp;pid=<?php echo $xref; ?>&amp;ged=<?php echo KT_GEDCOM; ?>&amp;dna-id=<?php echo $row->dna_id; ?>" target="_blank" title="<?php echo KT_I18N::translate('Edit DNA data'); ?>">
										<i class="<?php echo $iconStyle; ?> fa-edit fa-lg"></i>
									</a>
								</td>
								<?php //-- Select & delete
								if (KT_USER_GEDCOM_ADMIN) { ?>
									<td>
										<div class="delete_src">
											<input type="checkbox" name="del_places[]" class="check" value="<?php echo $row->dna_id; ?>" title="<?php echo KT_I18N::translate('Delete'); ?>">
										</div>
									</td>
								<?php } ?>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		<?php } else {
			echo KT_I18N::translate('No results found');
		}

		return '
			<div id="' . $this->getName() . '_content" class="grid-x grid-padding-y">' .
				ob_get_clean() . '
			</div>
		';

	}

	// Implement KT_Module_Tab
	public function addDNA($type) {
		global $controller, $iconStyle;
		require KT_ROOT . 'includes/functions/functions_edit.php';

		$pid		= KT_Filter::get('pid');
		$person		= KT_Person::getInstance($pid);
		$fullname	= $person->getFullName();
		$xref		= $person->getXref();
		$action		= KT_Filter::post('action');
		$dna_id_b	= $cms = $seg = $percent = $source = $note = '';

		$controller	= new KT_Controller_Page;
		$controller
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('autocomplete();'); ?>

		<style>
			.help_content .hidden {display: none; margin: 0 10px; font-weight: normal;}
		</style>
		<?php switch ($type) {
			case 'add':
				$controller->setPageTitle(KT_I18N::translate('Add DNA data') . ' - ' . $person->getLifespanName());
				$dna_id_a	= $pid;
				$dna_id_b	= KT_Filter::post('dna_id_b');
				$cms		= KT_Filter::post('cms', NULL,'');
				$seg		= KT_Filter::post('seg', NULL,'');
				$percent	= str_replace('%', '', KT_Filter::post('percent', NULL, ''));
				$source		= KT_Filter::post('source');
				$note		= KT_Filter::post('note');

				$person_b	= '';
				$source_b	= '';

				if ($action == 'update_dna') {

					KT_DB::prepare("
						INSERT INTO `##dna` (id_a, id_b, cms, seg, percent, source, note) VALUES (?, ?, ?, ?, ?, ?, ?)
					")->execute(array($dna_id_a, $dna_id_b, $cms, $seg, $percent, $source, $note));

					echo '<script>
							opener.location.reload();
							window.close();
						</script>
					';
				} ?>

				<div id="edit_interface-page" class="grid-x">
					<div class="cell large-10 large-offset-1">
						<h4><?php echo $controller->getPageTitle(); ?></h4>
						<form name="adddna_form" method="post" action="">
							<input type="hidden" name="action" value="update_dna">
							<input type="hidden" name="pid" value="<?php echo $pid; ?>">
							<div class="grid-x">
								<div class="cell medium-3">
									<label class="h5" for="autocompleteInput-dna_id_b"><?php echo KT_I18N::translate('Person connected by DNA'); ?></label>
								</div>
								<div class="cell small-10 medium-6">
									<div class="input-group autocomplete_container">
										<input data-autocomplete-type="INDI" type="text" id="autocompleteInput-dna_id_b" value="" autofocus>
										<span class="input-group-label">
											<button class="clearAutocomplete">
												<i class="<?php echo $iconStyle; ?> fa-times"></i>
											</button>
										</span>
									</div>
									<input type="hidden" id="selectedValue-dna_id_b" name="indi" >
								</div>
								<div class="cell small-1 medium-2"></div>
								<div class="cell medium-3">
									<label class="h5" for="autocompleteInput-source"><?php echo KT_I18N::translate('Source'); ?></label>
								</div>
								<div class="cell small-10 medium-6">
									<div class="input-group autocomplete_container">
										<input data-autocomplete-type="SOUR" type="text" id="autocompleteInput-source" value="">
										<span class="input-group-label">
											<button class="clearAutocomplete">
												<i class="<?php echo $iconStyle; ?> fa-times"></i>
											</button>
										</span>
										<input type="hidden" id="selectedValue-source" name="indi" >
									</div>
								</div>
								<div class="cell small-1 medium-2">
									<a href="#" onclick="addnewsource(document.getElementById('SOUR')); return false;" title="Create a new source">
										<i class="<?php echo $iconStyle; ?> fa-book-medical fa-lg vertical"></i>
									</a>
								</div>
				<?php break;

			case 'edit':
				$controller->setPageTitle(KT_I18N::translate('Edit DNA data') . ' - ' . $person->getLifespanName());
				$dna_id_b	= $cms = $seg = $percent = $source = $note = '';

				$dna_id		= KT_Filter::get('dna-id');
				$row		= $this->getData($pid, $dna_id);
				$dna_id_b	= KT_Filter::post('dna_id_b', NULL, $row->id_b);
				$cms		= KT_Filter::post('cms', NULL, $row->cms);
				$seg		= KT_Filter::post('seg', NULL, $row->seg);
				$percent	= str_replace('%', '', KT_Filter::post('percent', NULL, $row->percent));
				$source		= KT_Filter::post('source', NULL, $row->source);
				$note		= KT_Filter::post('note', NULL, $row->note);

				$person_b	= KT_Person::getInstance($dna_id_b, KT_GED_ID);
				$source_b	= KT_Source::getInstance($source, KT_GED_ID);

				if ($action == 'update_dna') {
					KT_DB::prepare(
						"UPDATE `##dna`
							SET
								id_b		= ?,
								cms			= ?,
								seg			= ?,
								percent		= ?,
								source		= ?,
								note		= ?
							WHERE dna_id	= ?
						"
					)->execute(array($dna_id_b, $cms, $seg, $percent, $source, $note, $dna_id));
					echo "
						<script>
							opener.location.reload();
							window.close();
						</script>
					";
				} ?>

				<div id="edit_interface-page" class="grid-x">
					<div class="cell large-10 large-offset-1">
						<h4><?php echo $controller->getPageTitle(); ?></h4>
						<form name="adddna_form" method="post" action="">
							<input type="hidden" name="action" value="update_dna">
							<input type="hidden" name="pid" value="<?php echo $pid; ?>">
							<div class="grid-x">
								<div class="cell medium-3">
									<label class="h5" for="autocompleteInput-dna_id_b"><?php echo KT_I18N::translate('Person connected by DNA'); ?></label>
								</div>
								<div class="cell small-10 medium-6">
									<div class="input-group autocomplete_container">
										<?php if ($person_b) { ?>
											<input data-autocomplete-type="INDI" type="text" id="autocompleteInput-dna_id_b" value="<?php echo strip_tags(($person_b ? $person_b->getLifespanName() : '')); ?>">
										<?php } else { ?>
											<input class="error" data-autocomplete-type="INDI" type="text" id="autocompleteInput-dna_id_b" value="<?php echo strip_tags(($person_b ? $person_b->getLifespanName() : '')); ?>">
										<?php }?>
										<span class="input-group-label">
											<button class="clearAutocomplete">
												<i class="<?php echo $iconStyle; ?> fa-times"></i>
											</button>
										</span>
									</div>
									<input type="hidden" id="selectedValue-dna_id_b" name="indi" value="<?php echo $dna_id_b; ?>">
								</div>
								<div class="cell small-1 medium-2"></div>

								<div class="cell medium-3">
									<label class="h5" for="autocompleteInput-source"><?php echo KT_I18N::translate('Source'); ?></label>
								</div>
								<div class="cell small-10 medium-6">
									<div class="input-group autocomplete_container">
										<?php if ($source_b) { ?>
											<input data-autocomplete-type="SOUR" type="text" id="autocompleteInput-source" value="<?php echo ($source_b ? strip_tags($source_b->getFullName()) : ''); ?>">
										<?php } else { ?>
											<input class="error" data-autocomplete-type="SOUR" type="text" id="autocompleteInput-source" value="<?php echo ($source_b ? strip_tags($source_b->getFullName()) : ''); ?>">
										<?php }?>
										<span class="input-group-label">
											<button class="clearAutocomplete">
												<i class="<?php echo $iconStyle; ?> fa-times"></i>
											</button>
										</span>
										<input type="hidden" id="selectedValue-source" name="indi" value="<?php echo $row->source; ?>">
									</div>
								</div>
								<div class="cell small-1 medium-2">
									<a href="#" onclick="addnewsource(document.getElementById('SOUR')); return false;" title="Create a new source">
										<i class="<?php echo $iconStyle; ?> fa-book-medical fa-lg vertical"></i>
									</a>
								</div>
			<?php break;
		} ?>
								<div class="cell medium-3">
									<label for="help-dropdown-cms" class="h5">
										<span data-toggle="help-dropdown-cms">
											<?php echo KT_I18N::translate('CentiMorgans'); ?>
											<i class="<?php echo $iconStyle; ?> fa-question-circle alert"></i>
										</span>
										<div class="dropdown-pane" id="help-dropdown-cms" data-dropdown data-close-on-click="true">
											<?php echo $this->DNAhelp('cms'); ?>
										</div>
									</label>
								</div>
								<div class="cell small-10 medium-6">
									<input class="addDna_form" type="number" name="cms" id="cms" min="1" max="7500" value="<?php echo $cms; ?>" placeholder="<?php echo KT_I18N::translate('A whole number between 1 and 7500'); ?>">
								</div>
								<div class="cell small-1 medium-2"></div>
								<div class="cell medium-3">
									<label for="help-dropdown-seg" class="h5">
										<span data-toggle="help-dropdown-seg">
											<?php echo KT_I18N::translate('Segments'); ?>
											<i class="<?php echo $iconStyle; ?> fa-question-circle alert"></i>
										</span>
										<div class="dropdown-pane" id="help-dropdown-seg" data-dropdown data-close-on-click="true">
											<?php echo $this->DNAhelp('seg'); ?>
										</div>
									</label>
								</div>
								<div class="cell small-10 medium-6">
									<input class="addDna_form" type="number" name="seg" id="seg" min="1" max="22" value="<?php echo $seg; ?>" placeholder="<?php echo KT_I18N::translate('A whole number, between 1 and 22'); ?>">
								</div>
								<div class="cell small-1 medium-2"></div>
								<div class="cell medium-3">
									<label for="help-dropdown-pdna" class="h5">
										<span data-toggle="help-dropdown-pdna">
											<?php echo KT_I18N::translate('Percentage DNA shared'); ?>
											<i class="<?php echo $iconStyle; ?> fa-question-circle alert"></i>
										</span>
										<div class="dropdown-pane" id="help-dropdown-pdna" data-dropdown data-close-on-click="true">
											<?php echo $this->DNAhelp('pdna'); ?>
										</div>
									</label>
								</div>
								<div class="cell small-10 medium-6">
									<input class="addDna_form" type="number" name="percent" id="percent" min="1" max="100" value="<?php echo $percent; ?>"placeholder="<?php echo KT_I18N::translate('A whole number, between 1 and 100'); ?>">
								</div>
								<div class="cell small-1 medium-2"></div>
								<div class="cell medium-3">
									<label class="h5"><?php echo KT_I18N::translate('Note'); ?></label>
								</div>
								<div class="cell small-10 medium-6">
									<textarea id="note" name="note"><?php echo $note; ?></textarea>
								</div>
								<div class="cell small-1 medium-2"></div>
							</div>
							<button class="button" type="submit">
								<i class="<?php echo $iconStyle; ?> fa-save"></i>
								<?php echo KT_I18N::translate('Save'); ?>
							</button>
							<button class="button" type="button" onclick="window.close();">
								<i class="<?php echo $iconStyle; ?> fa-times"></i>
								<?php echo KT_I18N::translate('Cancel'); ?>
							</button>
						</form>
					</div>
				</div>

	<?php }

	// Implement KT_Module_Tab
	public function hasTabContent() {
		return KT_USER_CAN_EDIT || count($this->getData());
	}

	// Implement KT_Module_Tab
	public function isGrayedOut() {
		return count($this->getData()) == 0;
	}

	// Implement KT_Module_Tab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	// get data from ##dna table for specific individual
	public function getData($xref = false, $id = false) {
		global $controller;

		self::updateSchema(); // make sure the dna table has been created

		if (!$xref) {
			$xref = $controller->record->getXref();
		}

		if ($id) {
			$sql	= "SELECT * FROM `##dna` WHERE dna_id=?";
			$arr	= array($id);
			$rows	= KT_DB::prepare($sql)->execute($arr)->fetchOneRow();
		} else {
			$sql	= "SELECT * FROM `##dna` WHERE id_a=? OR id_b=? AND id_a <> id_b";
			$arr	= array($xref, $xref);
			$rows	= KT_DB::prepare($sql)->execute($arr)->fetchAll();
		}

		return $rows;

	}


	// check relationship between two individuals
	public function findRelationship($person, $personA) {
		if ($person && $personA) {
			$controller	 = new KT_Controller_Relationship();
			$paths		 = $controller->calculateRelationships_123456($person, $personA, 1, 0);
			foreach ($paths as $path) {
				$relationships = $controller->oldStyleRelationshipPath($path);
				if (empty($relationships)) {
					// Cannot see one of the families/individuals, due to privacy;
					continue;
				}
				return get_relationship_name_from_path(implode('', $relationships), $person, $personA);
			}
		} else {
			return false;
		}
	}

	// find common ancestor for two individuals
	public function findCommonAncestor($person, $personA) {
		if ($person && $personA) {
			global $GEDCOM_ID_PREFIX;
			$slcaController = new KT_Controller_Relationship;
			$caAndPaths = $slcaController->calculateCaAndPaths_123456($person, $personA, 1, 0, false);
			$html = '';
			foreach ($caAndPaths as $caAndPath) {
				$slcaKey = $caAndPath->getCommonAncestor();
				if (substr($slcaKey, 0, 1) === $GEDCOM_ID_PREFIX) {
					$indi = KT_Person::getInstance($slcaKey, KT_GED_ID);
					if (($person !== $indi) && ($personA !== $indi)) {
						$html = '';
						$html .= '<a href="' . $indi->getHtmlUrl() . '" title="' . strip_tags($indi->getFullName()) . '">';
						$html .= highlight_search_hits($indi->getFullName()) . '</a>';
					}
				} else {
					$fam = KT_Family::getInstance($slcaKey, KT_GED_ID);
					$names = array();
					foreach ($fam->getSpouses() as $indi) {
						$html = '';
						$html .= '<a href="' . $indi->getHtmlUrl() . '" title="' . strip_tags($indi->getFullName()) . '">';
						$html .= highlight_search_hits($indi->getFullName()) . '</a>';

						$names[] = $indi->getFullName();
					}
					$famName = implode(' & ', $names);
					$html = '';
					$html .= '<a href="' . $fam->getHtmlUrl() . '" title="' . strip_tags($famName) . '">';
					$html .= highlight_search_hits($famName) . '</a>';
				}
			}

			if ($html) {
				return $html;
			} else {
				return KT_I18N::translate('No common ancestor found');
			}
		} else {
			return false;
		}

	}

	protected static function updateSchema() {
		// Create tables, if not already present
		try {
			KT_DB::updateSchema(KT_ROOT . KT_MODULES_DIR . 'tabi_dna/db_schema/', 'DNA_SCHEMA_VERSION', 2);
		} catch (PDOException $ex) {
			// The schema update scripts should never fail.  If they do, there is no clean recovery.
			die($ex);
		}
	}

	// Help texts for this module
	public function DNAhelp($item) {
		switch ($item) {
			case 'cms':
				return /* I18N: help for DNA tab module */ KT_I18N::translate('
					A centiMorgan (cM) is a unit of measure for DNA. It tells you how much DNA you share with another match. In general, the more DNA you share with a match the higher the cM number will be and the more closely related you are.
					<br>
					Ref: https://www.yourdnaguide.com/scp
				');
				break;
			case 'seg':
				return /* I18N: help for DNA tab module */ KT_I18N::translate('
					A DNA segment is a block, chunk, piece, string of DNA on a chromosome. It is typically determined by a start location and an end location on a chromosome. A segment refers to all the DNA in between and including the start and end locations.
					<br>
					Ref: https://segmentology.org/2015/05/07/what-is-a-segment/
				');
				break;
			case 'pdna':
				return /* I18N: help for DNA tab module */ KT_I18N::translate('
					Percentage DNA is an alternative to Shared cMs as a way of describing DNA relationships. Used by some DNA testing companies, but not all.
					<br>
					Ref: https://isogg.org/wiki/Autosomal_DNA_statistics
				');
				break;
		}
	}


}
