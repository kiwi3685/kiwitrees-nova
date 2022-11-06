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

define('KT_SCRIPT_NAME', 'admin_trees_sourcecite.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';
require KT_ROOT.'includes/functions/functions_print_facts.php';
include KT_THEME_URL . 'templates/adminData.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Source citation check'))
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addExternalJavascript(KT_DATATABLES_JS)
	->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
	->addExternalJavascript(KT_DATATABLES_BUTTONS)
	->addExternalJavascript(KT_DATATABLES_HTML5)
	->addInlineJavascript('
		autocomplete();

		jQuery("#citation_table").dataTable({
			dom: \'<"H"pBf<"clear">irl>t<"F"pl>\',
			' . KT_I18N::datatablesI18N() . ',
			buttons: [{extend: "csv", exportOptions: {columns: ":visible"}}],
			autoWidth: false,
			paging: true,
			pagingType: "full_numbers",
			lengthChange: true,
			filter: true,
			info: true,
			jQueryUI: true,
			sorting: [[2,"asc"]],
			displayLength: 20,
			columns: [
				/* 0-type   */ null,
				/* 1-record */ { "className": "nowrap" },
				/* 2-cite   */ null
			],
			stateSave: true,
			stateDuration: -1
		});

		jQuery("#source_list").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
	');

$gedID 	= KT_Filter::post('gedID') ? KT_Filter::post('gedID') : KT_GED_ID;
$tree 	= KT_Tree::getNameFromId($gedID);

$sid	= KT_Filter::post('source') ? KT_Filter::post('source') : '';
$source	= $sid ? KT_Source::getInstance($sid) : '';

echo relatedPages($trees, KT_SCRIPT_NAME);

echo pageStart('source_review', $controller->getPageTitle()); ?>

	<form class="cell" name='sourceReview' method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
		<input type="hidden" name="go" value="1">
		<div class="grid-x grid-margin-x grid-padding-x">
			<div class="cell callout info-help ">
				<?php echo KT_I18N::translate('Display a list of citations attached to any chosen source record. Used to review citations for accuracy and consistency. Entries in the column <strong>Edit raw GEDCOM record</strong> can be clicked to open the edit raw GEDCOM page. Entries in the column <strong>Record</strong> can be clicked to the detail page of that record for further editing. If you have many similar edits you might prefer to use the <strong>Batch update</strong> tool.'); ?>
			</div>
			<div class="cell medium-2">
				<label for="gedID"><?php echo KT_I18N::translate('Family tree'); ?></label>
			</div>
			<div class="cell medium-4">
				<?php echo select_ged_control('gedID', KT_Tree::getIdList(), null, $gedID, ' onchange="sourceReview.submit();"'); ?>
			</div>
			<div class="cell medium-6"></div>
			<div class="cell medium-2">
				<label for='autocompleteInput-source'><?php echo KT_I18N::translate('Source'); ?></label>
			</div>
			<div class="cell medium-4">
				 <?php echo autocompleteHtml(
						'source',
						'SOUR',
						$tree,
						$source ? strip_tags($source->getFullName()) : '',
						'',
						'source',
						$sid,
				 ); ?>
			</div>
			<div class="cell medium-6"></div>
		</div>

		<?php echo singleButton('Show'); ?>

	</form>
	<hr class="cell">

	<?php if (KT_Filter::post('go') && $source) { 	?>
		<div id="source_list" class="cell" style="visibility: hidden;">
			<?php
			$data		 = citations($sid);
			$no_citation = count_sources($sid) - count($data);
			?>
			<h3>
				<span><?php echo KT_I18N::translate('Source'); ?><span>: <a href="<?php echo $source->getHtmlUrl(); ?>"><?php echo $source->getFullName(); ?></a>
			</h3>
			<?php if ($no_citation > 0) { ?>
				<h5>
				<?php echo KT_I18N::plural(
						'This source also appears in %s GEDCOM record without a citation attached',
						'This source also appears in %s GEDCOM records without a citation attached',
						$no_citation, $no_citation
					); ?>
				</h5>
			<?php } ?>
			<table id="citation_table" style="width: 100%;">
				<thead>
					<tr>
						<th style="min-width: 200px;"><?php echo KT_I18N::translate('Edit raw GEDCOM record'); ?></th>
						<th><?php echo KT_I18N::translate('Record'); ?></th>
						<th><?php echo KT_I18N::translate('Citation'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($data as $row) {
						preg_match('/\n\d SOUR @' . $sid . '@(?:\n[3-9].*)*\n\d PAGE (.*)\n/i', $row->gedrec, $match);
						if (KT_Person::getInstance($row->xref)) {
							$record = KT_Person::getInstance($row->xref);
						} elseif (KT_Family::getInstance($row->xref)) {
							$record = KT_Family::getInstance($row->xref);
						} else {
							$record = KT_Media::getInstance($row->xref);
						}
						?>
						<tr>
							<td>
								<?php
								if($record){
								switch ($record->getType()) {
									case "INDI":
										$icon = $record->getSexImage('small', '', '', false);
										$type = KT_I18N::translate('Individual');
										break;
									case "FAM":
										$icon = '<i class="icon-button_family"></i>';
										$type = KT_I18N::translate('Family');
										break;
									case "OBJE":
										$icon = '<i class="icon-button_media"></i>';
										$type = KT_I18N::translate('Media');
										break;
									default:
										$type = '&nbsp;';
										break;
								}}
								?>
								<span>
									<?php echo $icon; ?>
								</span>
								<a href="#" onclick="return edit_raw('<?php echo $row->xref; ?>');">
									<?php echo $type; ?>
								</a>
							</td>
							<td>
								<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $record->getFullName(); ?></a>
							<td>
								<?php echo $match[1]; ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php } ?>
</div> <!-- close source_check page div -->

<?php

// source functions
function citations($sid) {
	$rows = KT_DB::prepare("
		SELECT i_id AS xref, i_gedcom AS gedrec
		FROM `##individuals`
		WHERE `i_file` = ?
		AND `i_gedcom`
		REGEXP '2 SOUR @" . $sid . "@\n3 PAGE (.*)\n'
		UNION
		SELECT f_id AS xref, f_gedcom AS gedrec
		FROM `##families`
		WHERE `f_file` = ?
		AND `f_gedcom`
		REGEXP '2 SOUR @" . $sid . "@\n3 PAGE (.*)\n'
		UNION
		SELECT m_id AS xref, m_gedcom AS gedrec
		FROM `##media`
		WHERE `m_file` = ?
		AND `m_gedcom`
		REGEXP '1 SOUR @" . $sid . "@\n2 PAGE (.*)\n'
		UNION
		SELECT o_id AS xref, o_gedcom AS gedrec
		FROM `##other`
		WHERE `o_file` = ?
		AND `o_gedcom`
		REGEXP '2 SOUR @" . $sid . "@\n3 PAGE (.*)\n'
	")->execute(array(KT_GED_ID, KT_GED_ID, KT_GED_ID, KT_GED_ID))->fetchAll();

	return $rows;
}

// source functions ignoring citation
function count_sources($sid) {
	// Count the number of linked records.  These numbers include private records, but htis is only accessibel on admin pages
	$count = KT_DB::prepare("SELECT count(*) FROM `##link` WHERE `l_to` LIKE '" . $sid . "'")->fetchOne();
	return $count;
}
