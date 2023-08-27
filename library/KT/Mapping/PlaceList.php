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

 class KT_Mapping_PlaceList {

	/**
   	* Produces a list of all places in the chosen place
   	* without using GoogleMap
   	* @return html data
   	*/
	public static function PlaceListNoMap(
  		$place_id,
  		$child_places,
  		$place,
  		$numColumns,
  		$columns,
  		$numfound,
  		$level,
  		$parent,
  		$linklevels,
  		$place_names,
  		$placelevels
	)
	{
  		?>
  		<div class="cell">
  		<h5 class="text-center" style="margin-bottom: 0.9375rem;">
	  		<a href="module.php?mod=list_places&amp;mod_action=show&amp;display=list&amp;ged=<?php echo KT_GEDURL; ?>"">
		  		<?php echo KT_I18N::translate('Switch to list view'); ?>
	  		</a>
  		</h5>
  		<div class="places grid-x grid-margin-y medium-up-<?php echo $numColumns; ?>" style="margin-bottom: 0.9375rem;">
	  		<?php foreach ($columns as $child_places) { ?>
		  		<ul class="cell text-center" style="margin: 0; padding: 0.5rem;">
			  		<?php foreach($child_places as $n => $child_place) { ?>
				  		<li style="list-style-type: none; padding: 0; text-align: left;">
					  		<a href="<?php echo $child_place->getURL(); ?>" class="list_item">
						  		<?php echo $child_place->getPlaceName(); ?>
					  		</a>
				  		</li>
			  		<?php } ?>
		  		</ul>
	  		<?php } ?>
  		</div>

	<?php }

	/**
	 *
	 * Produces a list of all places in the chosen place
	 * and includes a GoogleMap
	 * @return html data
	 */
	public static function PlaceListMap(
		$title,
		$place_id,
		$child_places,
		$place,
		$numColumns,
		$columns,
		$numfound,
		$level,
		$parent,
		$linklevels,
		$place_names,
		$placelevels
	)
	{
		$align = ($numfound < 6 ? 'center' : 'initial'); ?>

		<h4 class="cell text-center">
			<?php echo $title; ?>
		</h4>

		<?php require KT_ROOT . KT_MODULES_DIR . 'googlemap/placehierarchy.php'; ?>
		<div>
			<?php create_map($placelevels); ?>
		</div>
		<hr class="cell">
		<div class="cell">
			<div class="places grid-x grid-margin-y small-up-2 medium-up-<?php echo $numColumns; ?>" style="margin-bottom: 0.9375rem;">
				<?php foreach ($columns as $child_places) { ?>
						<ul class="cell text-center" style="margin: 0; padding: 0.5rem;">
							<?php foreach($child_places as $n => $child_place) { ?>
								<li style="list-style-type: none; padding: 0; text-align: <?php echo $align; ?>;">
									<a href="<?php echo $child_place->getURL(); ?>" class="list_item">
										<?php echo $child_place->getPlaceName(); ?>
									</a>
								</li>
								<?php $place_names[$n] = $child_place->getPlaceName();
							} ?>
						</ul>
				<?php } ?>
			</div>
		</div>

		<link type="text/css" href="<?php echo KT_STATIC_URL . KT_MODULES_DIR; ?>googlemap/css/googlemap.min.css" rel="stylesheet">
		<?php map_scripts($numfound, $level, $parent, $linklevels, $placelevels, $place_names); ?>

	<?php }

	public static function PlaceListRecords ($title, $place_id)
	{
		?>
		<h4 class="cell text-center">
			<?php echo KT_I18N::translate('Linked records') . ' - ' . $title; ?>
		</h4>

		<?php
		$myindilist	= array();
		$myfamlist	= array();
		$positions	=
			KT_DB::prepare("
				SELECT DISTINCT pl_gid
				FROM `##placelinks`
				WHERE pl_p_id=?
				AND pl_file=?
			")
			->execute(array($place_id, KT_GED_ID))
			->fetchOneColumn();

		foreach ($positions as $position) {
			$record = KT_GedcomRecord::getInstance($position);
			if ($record && $record->canDisplayDetails()) {
				switch ($record->getType()) {
				case 'INDI':
					$myindilist[]	= $record;
					break;
				case 'FAM':
					$myfamlist[]	= $record;
					break;
				}
			}
		} ?>

		<ul class="cell tabs" data-tabs id="records-tabs">
			<?php if ($myindilist) { ?>
				<li class="tabs-title is-active" aria-selected="true">
					<a href="#places-indi">
						<span><?php echo KT_I18N::translate('Individuals'); ?></span>
					</a>
				</li>
			<?php }
			if ($myfamlist) { ?>
				<li class="tabs-title">
					<a href="#places-fam">
						<span><?php echo KT_I18N::translate('Families'); ?></span>
					</a>
				</li>
			<?php } ?>
		</ul>
		<div class="cell tabs-content" data-tabs-content="records-tabs">
			<?php if ($myindilist) { ?>
				<div id="places-indi" class="tabs-panel is-active">
					<?php echo simple_indi_table($myindilist); ?>
				</div>
			<?php } ?>
			<?php if ($myfamlist) { ?>
				<div id="places-fam" class="tabs-panel">
					<?php echo simple_fam_table($myfamlist); ?>
				</div>
			<?php } ?>
			<?php if (!$myindilist && !$myfamlist) { ?>
				<div id="places-indi" class="tabs-panel">
					<?php echo format_indi_table(array()); ?>
				</div>
			<?php } ?>
		</div>

	<?php }

	public static function PlaceListDetails ($title, $placelevels)
	{
		?>
		<h4 class="cell text-center">
			<?php echo KT_I18N::translate('Place details') . ' - ' . $title; ?>
		</h4>

		<?php
		$place_image = '';
		$gm_place_id = getGmPlaceId($placelevels);
		if ($gm_place_id) {
			$place_image = KT_DB::prepare('
				SELECT pl_image
				FROM `##placelocation`
				WHERE pl_id = ?
			')->execute([$gm_place_id])->fetchOne();

			$media = KT_Media::getInstance($place_image);
			if ($media && $media->canDisplayDetails()) { ?>
				<div class="cell text-center">
					<div class="shadow" style="display:inline-block;">
						<?php echo $media->displayLargeImage(); ?>
					</div>
				</div>
			<?php } else { ?>
				<div class="cell callout warning">
					<?php echo KT_I18N::translate('No details are available for this place'); ?>
				</div>
			<?php }
		}

	}

	public static function PlaceListAll ($controller, $title)
	{
		?>
		<h4 class="cell text-center">
			<?php echo KT_I18N::translate('A list of all places'); ?>
		</h4>

		<?php
		$listPlaceNames = array();
		$placeName      = array();
		$maxParts       = 0;
		$list_places    = KT_Place::allPlaces(KT_GED_ID);

		foreach ($list_places as $n => $list_place) {
			$placeName	= explode(', ', $list_place->getReverseName());
			$countParts	= count($placeName);
			if ($countParts > $maxParts) {
				$maxParts = $countParts;
			}
			$listPlaceNames[]	= $placeName;
		}

		$controller
			->addExternalJavascript(KT_DATATABLES_JS)
			->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
		;

		if (KT_USER_CAN_EDIT) {
			$controller
				->addExternalJavascript(KT_DATATABLES_BUTTONS)
				->addExternalJavascript(KT_DATATABLES_HTML5);
			$buttons = 'B';
		} else {
			$buttons = '';
		}

		$controller->addInlineJavascript('
			jQuery("#placeListTable").dataTable({
				dom: \'<"top"p' . $buttons . 'f<"clear">irl>t<"bottom"pl>\',
				' . KT_I18N::datatablesI18N() . ',
				buttons: [{extend: "csvHtml5"}],
				autoWidth: false,
				processing: true,
				retrieve: true,
				displayLength: 20,
				pagingType: "full_numbers",
				stateSave: true,
				stateSaveParams: function (settings, data) {
					data.columns.forEach(function(column) {
						delete column.search;
					});
				},
				stateDuration: -1,
			});
		'); ?>

		<table id="placeListTable" class="scroll">
			<thead>
				<tr>
					<?php for ($i = 0; $i < $maxParts; $i++) {
						$level = $i + 1 ?>
						<th><?php echo KT_I18N::translate('Place level %s', $level); ?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($list_places as $n=>$list_place) {
					$placeName	= explode(', ', $list_place->getReverseName());?>
					<tr>
						<?php for ($i = 0; $i < $maxParts; $i++) { ?>
							<td>
								<?php if ($i < count($placeName)) { ?>
									<a href="<?php echo $list_place->getURL(); ?>"><?php echo $placeName[$i]; ?></a>
								<?php } else { ?>
									&nbsp;
								<?php }?>
							</td>
						<?php } ?>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php }


}
