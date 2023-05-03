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

define('KT_SCRIPT_NAME', 'admin_trees_findunlinked.php');

require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';

global $NOTE_ID_PREFIX, $REPO_ID_PREFIX, $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Find unlinked records'))
	->pageHeader()
	->addInlineJavascript('
		jQuery("#unlinked_accordion").accordion({heightStyle: "content", collapsible: true, active: 0, header: "h3.drop"});
		jQuery("#unlinked_accordion").css("visibility", "visible");
	');

$action		= KT_Filter::post('action');
$gedcom_id	= KT_Filter::post('gedcom_id', null, KT_GED_ID);
$records	= KT_Filter::postArray('records');
$list		= array(
				'Individuals',
				'Sources',
				'Notes',
				'Repositories',
				'Media'
			);

// the sql queries used to identify unlinked indis
$sql_INDI = "
	SELECT i_id
	FROM `##individuals`
	LEFT OUTER JOIN ##link
	 ON (##individuals.i_id = ##link.l_from AND ##individuals.i_file = ##link.l_file)
	 WHERE ##individuals.i_file = " . $gedcom_id . "
	 AND ##link.l_to IS NULL
";
$sql_SOUR = "
	SELECT s_id
	FROM `##sources`
	LEFT OUTER JOIN ##link
	 ON (##sources.s_id = ##link.l_to AND ##sources.s_file = ##link.l_file)
	 WHERE ##sources.s_file = " . $gedcom_id . "
	 AND ##link.l_from IS NULL
";
$sql_MEDIA = "
	SELECT m_id, m_filename
	FROM `##media`
	LEFT OUTER JOIN ##link
	 ON (##media.m_id = ##link.l_to AND ##media.m_file = ##link.l_file)
	 WHERE ##media.m_file = " . $gedcom_id . "
	 AND ##link.l_from IS NULL
";
$sql_NOTE = "
	SELECT o_id
	FROM `##other`
	LEFT OUTER JOIN ##link
	 ON (##other.o_id = ##link.l_to AND ##other.o_file = ##link.l_file)
	 WHERE ##other.o_file = " . $gedcom_id . "
	 AND ##other.o_id LIKE '" . $NOTE_ID_PREFIX . "%'
	 AND ##link.l_from IS NULL
";
$sql_REPO = "
	SELECT o_id
	FROM `##other`
	LEFT OUTER JOIN ##link
	 ON (##other.o_id = ##link.l_to AND ##other.o_file = ##link.l_file)
	 WHERE ##other.o_file = " . $gedcom_id . "
	 AND ##other.o_id LIKE '" . $REPO_ID_PREFIX . "%'
	 AND ##link.l_from IS NULL
";

// Start of display
echo relatedPages($trees, KT_SCRIPT_NAME);?>

<div id="find-unlinked-records-page" class="cell">
	<div class="grid-x grid-margin-x">
		<div class="cell">
			<h4><?php echo $controller->getPageTitle(); ?></h4>
			<div class="cell callout info-help ">
				<?php echo /* I18N: Help text for the Find unlinked records tool. */ KT_I18N::translate('
					List records that are not linked to any other records. It does not include Families as a family record cannot exist without at least one family member.<br>
					The definition of unlinked for each type of record is:
					<ul><li>Individuals: a person who is not linked to any family, as a child or a spouse.</li>
					<li>Sources: a source record that is not used as a source for any record, fact, or event in the family tree.</li>
					<li>Repositories: a repository record that is not used as a repository for any source in the family tree.</li>
					<li>Notes: a shared note record that is not used as a note to any record, fact, or event in the family tree.</li>
					<li>Media: a media object that is registered in the family tree but not attached to any record, fact, or event.</li><ul>
				'); ?>
			</div>
			<form class="cell" method="post" name="unlinked_form" action="<?php echo KT_SCRIPT_NAME; ?>">
				<input type="hidden" name="action" value="view">
				<div class="grid-x grid-margin-x">
					<div class="cell medium-2">
						<label class="middle"><?php echo KT_I18N::translate('Family tree'); ?></label>
					</div>
					<div class="cell medium-4">
						<select name="ged">
							<?php foreach (KT_Tree::getAll() as $tree) { ?>
								<option value="<?php echo $tree->tree_name_html; ?>"
								<?php if (empty($ged) && $tree->tree_id == KT_GED_ID || !empty($ged) && $ged == $tree->tree_name) { ?>
									 selected="selected"
								<?php } ?>
								 dir="auto"><?php echo $tree->tree_title_html; ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="cell medium-6"></div>
					<div class="cell medium-2">
						<label class="middle"><?php echo KT_I18N::translate('Select / de-select all'); ?></label>
					</div>
					<div class="cell medium-1">
						<input type="checkbox" onclick="toggle_select(this)"  checked="checked">
					</div>
					<div class="cell medium-9"></div>
					<div class="cell medium-2"></div>
					<div class="cell medium-10 selectType">
						<?php
						foreach ($list as $selected) { ?>
							<input class="check" type="checkbox" name="records[]" id="record_<?php echo $selected; ?>"
								<?php if (($records && in_array($selected, $records)) || !$records) {
									echo ' checked="checked" ';
								} ?>
							value="<?php echo $selected; ?>">
							<label class="middle" for="record_'<?php echo $selected; ?>">
								<?php echo KT_I18N::translate($selected); ?>
							</label>
						<?php }	?>
					</div>
					
					<?php singleButton('Show'); ?>

				</div>
			</form>
		</div>
		<hr>
		<?php
		// START OUTPUT
		if ($action == 'view') { ?>
			<hr class="cell">
			<ul class="accordion" data-accordion data-multi-expand="true" data-allow-all-closed="true">
				<?php
				if ($records) {
					// -- Individuals --
					if (in_array('Individuals', $records)) {
						$rows_INDI	= KT_DB::prepare($sql_INDI)->fetchAll(PDO::FETCH_ASSOC);
						if ($rows_INDI) { ?>
							<li class="accordion-item" data-accordion-item>
							    <a href="#" class="accordion-title">
							    	<?php echo KT_I18N::plural('%s unlinked individual', '%s unlinked individuals', count($rows_INDI), count($rows_INDI)); ?>
						    	</a>
								<div class="accordion-content" data-tab-content>
									<?php foreach ($rows_INDI as $row) {
										$id = $row['i_id'];
										$record = KT_Person::getInstance($id);
										$fullname =  $record->getLifespanName(); ?>
										<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo $fullname; ?>
											<span class="id">(<?php echo $id; ?>)</span>
										</a>
									<?php } ?>
								</div>
							</li>
						<?php } else { ?>
							<li class="accordion-item" data-accordion-item><?php echo KT_I18N::translate('No unlinked individuals'); ?></li>
						<?php }
					}
					// -- Sources --
					if (in_array('Sources', $records)) {
						$rows_SOUR	= KT_DB::prepare($sql_SOUR)->fetchAll(PDO::FETCH_ASSOC);
						if ($rows_SOUR) { ?>
							<li class="accordion-item" data-accordion-item>
							    <a href="#" class="accordion-title">
							    	<?php echo KT_I18N::plural('%s unlinked source', '%s unlinked sources', count($rows_SOUR), count($rows_SOUR)); ?>
						    	</a>
								<div class="accordion-content" data-tab-content>
									<?php foreach ($rows_SOUR as $row) {
										$id = $row['s_id'];
										$record = KT_Source::getInstance($id);
										$fullname =  $record->getFullName(); ?>
										<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo $fullname; ?>
											<span class="id">(<?php echo $id; ?>)</span>
										</a>
									<?php } ?>
								</div>
							</li>
						<?php } else { ?>
							<li class="accordion-item" data-accordion-item><?php echo KT_I18N::translate('No unlinked sources'); ?></li>
						<?php }
					}
					// -- Notes --
					if (in_array('Notes', $records)) {
						$rows_NOTE	= KT_DB::prepare($sql_NOTE)->fetchAll(PDO::FETCH_ASSOC);
						if ($rows_NOTE) { ?>
							<li class="accordion-item" data-accordion-item>
							    <a href="#" class="accordion-title">
							    	<?php echo KT_I18N::plural('%s unlinked note', '%s unlinked notes', count($rows_NOTE), count($rows_NOTE)); ?>
						    	</a>
								<div class="accordion-content" data-tab-content>
									<?php foreach ($rows_NOTE as $row) {
										$id = $row['o_id'];
										$record = KT_Note::getInstance($id);
										$fullname =  $record->getFullName(); ?>
										<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo $fullname; ?>
											<span class="id">(<?php echo $id; ?>)</span>
										</a>
									<?php } ?>
								</div>
							</li>
						<?php } else { ?>
							<li class="accordion-item" data-accordion-item><?php echo KT_I18N::translate('No unlinked notes'); ?></li>
						<?php }
					}
					// -- Repositories --
					if (in_array('Repositories', $records)) {
						$rows_REPO	= KT_DB::prepare($sql_REPO)->fetchAll(PDO::FETCH_ASSOC);
						if ($rows_REPO) { ?>
							<li class="accordion-item" data-accordion-item>
							    <a href="#" class="accordion-title">
							    	<?php echo KT_I18N::plural('%s unlinked repository', '%s unlinked repositories', count($rows_REPO), count($rows_REPO)); ?>
						    	</a>
								<div class="accordion-content" data-tab-content>
									<?php foreach ($rows_REPO as $row) {
										$id = $row['o_id'];
										$record = KT_Repository::getInstance($id);
										$fullname =  $record->getFullName(); ?>
										<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo $fullname; ?>
											<span class="id">(<?php echo $id; ?>)</span>
										</a>
									<?php } ?>
								</div>
							</li>
						<?php } else { ?>
							<li class="accordion-item" data-accordion-item>
								<a href="#" class="accordion-title">
									<?php echo KT_I18N::translate('No unlinked repositories'); ?>
								</a>
								<div class="accordion-content" data-tab-content></div>
							</li>
						<?php }
					}
					// -- Media --
					if (in_array('Media', $records)) {
						$rows_MEDIA	= KT_DB::prepare($sql_MEDIA)->fetchAll(PDO::FETCH_ASSOC);
						if ($rows_MEDIA) { ?>
							<li class="accordion-item" data-accordion-item>
							    <a href="#" class="accordion-title">
							    	<?php echo KT_I18N::plural('%s unlinked media object', '%s unlinked media objects', count($rows_MEDIA), count($rows_MEDIA)); ?>
						    	</a>
								<div class="accordion-content" data-tab-content>
									<?php foreach ($rows_MEDIA as $row) {
										$id = $row['m_id'];
										$folder = $row['m_filename'];
										$record = KT_Media::getInstance($id);
										$title =  $record->getTitle(); ?>
										<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo $folder; ?>
											<span class="id">&nbsp;(<?php echo $id; ?>)&nbsp;</span>
											<?php echo $title !== $folder ? $title : ''; ?>
										</a>
									<?php } ?>
								</div>
							</li>
						<?php } else { ?>
							<li class="accordion-item" data-accordion-item>
								<a href="#" class="accordion-title">
									<?php echo KT_I18N::translate('No unlinked media objects'); ?>
								</a>
								<div class="accordion-content" data-tab-content></div>
							</li>
						<?php }
					}
				} else { ?>
					<div class="callout warning">
						<?php echo KT_I18N::translate('You must select at least one record type'); ?>
					</div>
				<?php } ?>
			</ul>
		<?php } ?>
	</div>
</div>
