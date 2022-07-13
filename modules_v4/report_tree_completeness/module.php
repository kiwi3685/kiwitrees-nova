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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class report_tree_completeness_KT_Module extends KT_Module implements KT_Module_Report {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('Tree completeness');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the "Facts and events" module */ KT_I18N::translate('A summary of ancestors recorded.');
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
		return KT_PRIV_USER; // default privacy = "members"
	}

	// Implement KT_Module_Report
	public function getReportMenus() {
		global $controller, $iconStyle;

		$indi_xref = $controller->getSignificantIndividual()->getXref();

		$menus	= array();
		$menu	= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $indi_xref . '&amp;ged=' . KT_GEDURL,
			'menu-report-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement KT_Module_Tab
	public function show() {
		global $controller, $MAX_PEDIGREE_GENERATIONS, $iconStyle;
		require KT_ROOT . 'includes/functions/functions_resource.php';

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_Module::isActiveReport(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('autocomplete();');

		//-- args
		$maxGen		= empty(KT_Filter::post('generations')) ? $MAX_PEDIGREE_GENERATIONS : KT_Filter::post('generations');
		$rootid 	= KT_Filter::get('rootid');
		$indi	= KT_Filter::post('root_id', KT_REGEX_XREF, $rootid);
//		$rootid		= empty($indi) ? $rootid : $indi;
		$person		= KT_Person::getInstance($indi);

		?>
		<!-- Start page layout  -->
		<?php echo pageStart('report_tree_completeness-page', KT_I18N::translate('%1s for %2s', $this->getTitle(), $person->getFullName())); ?>
			<form name="complete" id="complete" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;rootid=<?php echo $indi; ?>&amp;ged=<?php echo KT_GEDURL; ?>">
				<div class="grid-x grid-margin-x">
					<div class="cell medium-4">
						<label class="h5" for="autocompleteInput"><?php echo KT_I18N::translate('Individual'); ?></label>
						<?php echo autocompleteHtml(
							'completeness', // id
							'INDI', // TYPE
							'', // autocomplete-ged
							strip_tags($person->getLifespanName()), // input value
							'', // placeholder
							'root_id', // hidden input name
							'' // hidden input value
						); ?>
					</div>
					<div class="cell medium-4">
						<label class="h5" for="generations"><?php echo KT_I18N::translate('Generations'); ?></label>
						<div class="grid-x grid-padding-x">
							<div class="cell small-9">
							  <div class="slider" data-slider data-initial-start="<?php echo floor($maxGen) ?>" data-start="2" data-step="1" data-end="<?php echo $MAX_PEDIGREE_GENERATIONS; ?>">
							    <span class="slider-handle"  data-slider-handle role="slider" tabindex="1" aria-controls="generations"></span>
							    <span class="slider-fill" data-slider-fill></span>
							  </div>
							</div>
							<div class="cell small-3">
							  <input type="number" id="generations" name="generations">
							</div>
						</div>
					</div>
				</div>
				<button class="button" type="submit">
					<i class="<?php echo $iconStyle; ?> fa-eye"></i>
					<?php echo KT_I18N::translate('Show'); ?>
				</button>
			</form>
			<hr style="clear:both;">
			<!-- end of form -->
			<div class="grid-x grid-margin-x">
				<div class="cell large-10 large-offset-1">
					<button class="button hollow">
						<a href="module.php?mod=chart_ancestry&mod_action=show&rootid=<?php echo $rootid; ?>&generations=<?php echo $maxGen; ?>&chart_style=1">
							<?php echo KT_I18N::translate('Go to a detailed list of these ancestors'); ?>
						</a>
					</button>
					<?php $list = array();
					if ($person && $person->canDisplayDetails()) {

						$countArray		= count_ancestors($rootid, $maxGen);
						$allRecorded	= 0;
						$allTarget		= 0;
						$duplicates		= $countArray['duplicates'];

						if ($duplicates) { ?>
						    <div class="callout warning" data-closable>
						        <button class="close-button" aria-label="Close alert" type="button" data-close>
						            <span aria-hidden="true"><i class="<?php echo $iconStyle; ?> fa-xmark"></i></span>
						        </button>
						        <h5><?php echo KT_I18N::translate('Pedigree collapse'); ?></h5>
						        <p>
						            <?php echo KT_I18N::translate('%s people are included twice', count($duplicates)); ?>
						            <button class="button hollow alert" data-open="listDuplicates">
						                <?php echo KT_I18N::translate('Show list'); ?>
						            </button>
						        </p>
						    </div>
						    <div class="tiny reveal" id="listDuplicates" data-reveal>
						        <button class="close-button" aria-label="Close alert" type="button" data-close>
						            <span aria-hidden="true">&times;</span>
						        </button>
						        <h5><?php echo KT_I18N::translate('Duplicates'); ?></h5>
						        <ul>
						            <?php foreach ($duplicates as $duplicate) {
						                $duplicatePerson = KT_Person::getInstance($duplicate); ?>
						                <?php $relationship = findRelationship($person, $duplicatePerson); ?>
						                <li>
						                    <a href="<?php echo $duplicatePerson->getHtmlUrl(); ?>">
						                        <span><?php echo $duplicatePerson->getLifespanName(); ?></span>
						                    </a>
						                    <span><?php echo ucfirst($relationship); ?></span>
						                </li>
						            <?php } ?>
						        </ul>
						    </div>
						<?php } ?>

						<table class="shadow" id="completenessTable">
							<thead>
								<tr>
									<th colspan="2" class="text-center"><?php echo KT_I18N::translate('Generation'); ?></th>
									<th class="text-right"><?php echo KT_I18N::translate('Ancestors recorded'); ?></th>
									<th class="text-right"><?php echo KT_I18N::translate('Completeness at this level'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($countArray['counts'] as $generation => $recorded) {
									$target = pow(2, $generation) ?>
									<tr>
										<td class="text-right">
											<?php echo KT_I18N::number($generation + 1); ?>
										</td>
										<td>
											<?php if ($generation == 0) {
												echo $person->getFullName();
											} else { ?>
												<?php echo get_generation_names($generation + 1); ?>
											<?php } ?>
										</td>
										<td class="text-right">
											<?php echo KT_I18N::translate('%1s of %2s', $recorded, $target); ?>
										</td>
										<td class="text-right">
											<?php echo KT_I18N::number($recorded / $target * 100, 2); ?>%
										</td>
									</tr>
									<?php
									$allRecorded	= $allRecorded + $recorded;
									$allTarget		= $allTarget + $target;
								} ?>
							</tbody>
							<tfoot>
								<tr>
									<th></th>
									<th><?php echo KT_I18N::translate('Total for %s generations', $maxGen); ?></th>
									<th class="text-right"><?php echo KT_I18N::translate('%1s of %2s', $allRecorded, $allTarget); ?></th>
									<th class="text-right"><?php echo KT_I18N::number($allRecorded / $allTarget * 100, 2); ?>%</th>
						</table>
					<?php } ?>
				</div>
			</div>
		<?php echo pageClose();

	}

}
