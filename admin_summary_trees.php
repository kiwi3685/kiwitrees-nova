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

define('KT_SCRIPT_NAME', 'admin_summary_trees.php');

global $iconStyle;
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_GEDCOM_ADMIN)
	->setPageTitle(KT_I18N::translate('Family trees'))
	->pageHeader();

/**
 * Array of family tree administration menu items
 * $ft_tools [array]
 */
$ftrees = [
	"admin_trees_manage.php"			=> [
		KT_I18N::translate('Manage all family trees'),
		KT_I18N::translate('Import or export GEDCOM files and <br>create or delete family trees '),
		KT_I18N::translate('Administrator access only'),
		'alert'
	],
	"admin_trees_config.php"		=> [
		KT_I18N::translate('Configure each family tree'),
		KT_I18N::translate('The complete range of configurations for displaying and editing each tree'),
		KT_I18N::translate('Administrator, or Managers authorized for specific trees'),
		'warning'
	],
];

/**
 * Array of family tree administration menu items
 * $ft_tools [array]
 */
$ft_tools = [
	"admin_trees_check.php"			=> [
		KT_I18N::translate('Check for GEDCOM errors'),
		KT_I18N::translate('Check your family tree for basic GEDCOM errors'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_change.php"		=> [
		KT_I18N::translate('Changes log'),
		KT_I18N::translate('A filterable log of changes made to the family tree data'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_addunlinked.php"	=> [
		KT_I18N::translate('Add unlinked records'),
		KT_I18N::translate('Add new individual, family, source, note, media, or repository records without links to any other data'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_places.php"		=> [
		KT_I18N::translate('Place name editing'),
		KT_I18N::translate('Carry out basic updates, in bulk, to family tree place names'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_merge.php"			=> [
		KT_I18N::translate('Merge records'),
		KT_I18N::translate('Merge two similar records, such as individuals, families, sources, within a single family tree'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_renumber.php"		=> [
		KT_I18N::translate('Renumber family tree'),
		KT_I18N::translate('Change IDs to prevent clashes between identical references, when appending one tree to another with the \'Append family tree\' tool'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_append.php"		=> [
		KT_I18N::translate('Append family tree'),
		KT_I18N::translate('Add one GEDCOM file to another, creating a combined tree.<br><small>Note: This is NOT merging. No duplication checks are done, so significant clean up may be required</small>'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_duplicates.php"	=> [
		KT_I18N::translate('Find duplicate individuals'),
		KT_I18N::translate('List individuals that MIGHT be duplicates. Based on a simple comparisons of names'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_findunlinked.php"	=> [
		KT_I18N::translate('Find unlinked records'),
		KT_I18N::translate('List records that are not linked to any other records.<br><small>Note: It does not include Families as a family record cannot exist without linking to at least one individual</small>'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_sanity.php"		=> [
		KT_I18N::translate('Sanity check'),
		KT_I18N::translate('A collection of checks to help you monitor the quality of your family history data'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_source.php"		=> [
		KT_I18N::translate('Sources - review'),
		KT_I18N::translate('Display a list of facts, events or records where a selected source is used'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_sourcecite.php"	=> [
		KT_I18N::translate('Sources - review citations'),
		KT_I18N::translate('Display a list of citations attached to any chosen source record. Usefull for reviewing citations for accuracy and consistency'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
	"admin_trees_missing.php"		=> [
		KT_I18N::translate('Missing fact or event details'),
		KT_I18N::translate('A list of information missing from events or facts of an individual and their relatives'),
		KT_I18N::translate('Administrator or Managers authorized for specific trees'),
		'warning'
	],
];
asort($ft_tools);

echo pageStart('tree_admin', $controller->getPageTitle()); ?>

	<div class="cell callout info-help summary">
		<?php echo KT_I18N::translate('
			All of the configuration settings and tools necessary to manage all family trees on your website
		'); ?>
	</div>
	<div class="cell">
		<div class="grid-x grid-margin-x grid-margin-y">
			<?php foreach ($ftrees as $link => $file) {
				if (($file[3] == 'alert' && KT_USER_IS_ADMIN) || ($file[3] != 'alert' && KT_USER_GEDCOM_ADMIN)) {
					$title   = $file[0];
					$descr   = $file[1];
					$tooltip = $file[2];
					$user    = $file[3];

					echo AdminSummaryCard ($link, $title, $user, $tooltip, $descr);
				}
			} ?>
			<hr class="cell">
			<div class="cell">
				<h4><?php echo KT_I18N::translate('Family tree tools'); ?></h4>
			</div>
			<?php foreach ($ft_tools as $title => $file) {
				if (($file[3] == 'alert' && KT_USER_IS_ADMIN) || ($file[3] != 'alert' && KT_USER_GEDCOM_ADMIN)) {
					$title   = $file[0];
					$descr   = $file[1];
					$tooltip = $file[2];
					$user    = $file[3];

					echo AdminSummaryCard ($link, $title, $user, $tooltip, $descr);
				}
			} ?>
		</div>
	</div>

<?php echo pageClose();
