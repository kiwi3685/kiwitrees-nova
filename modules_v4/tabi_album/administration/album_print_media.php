*<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net.
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
if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');

	exit;
}

/**
 * -----------------------------------------------------------------------------
 * Print the links to media objects.
 *
 * @param string $pid     The the xref id of the object to find media records related to
 * @param int    $level   The level of media object to find
 * @param bool   $related Whether or not to grab media from related records
 * @param mixed  $kind
 * @param mixed  $noedit
 */
function album_print_media($pid, $level = 1, $related = false, $kind = 0, $noedit = false)
{
	global $GEDCOM;

	$ALBUM_GROUPS  = get_module_setting('tabi_album', 'ALBUM_GROUPS') ? get_module_setting('tabi_album', 'ALBUM_GROUPS') : '';
	$ALBUM_TITLES  = get_module_setting('tabi_album', 'ALBUM_TITLES') ? unserialize(get_module_setting('tabi_album', 'ALBUM_TITLES')) : [];
	$ALBUM_OPTIONS = get_module_setting('tabi_album', 'ALBUM_OPTIONS') ? unserialize(get_module_setting('tabi_album', 'ALBUM_OPTIONS')) : [];

	if (!isset($ALBUM_GROUPS)) {
		$ALBUM_GROUPS = 4;
	}

	if (empty($ALBUM_TITLES)) {
		$ALBUM_TITLES = [
			KT_I18N::translate('Photos'),
			KT_I18N::translate('Documents'),
			KT_I18N::translate('Census'),
			KT_I18N::translate('Other'),
		];
	}

	$default_groups = [
		KT_I18N::translate('Other'),
		KT_I18N::translate('Other'),
		KT_I18N::translate('Documents'),
		KT_I18N::translate('Documents'),
		KT_I18N::translate('Other'),
		KT_I18N::translate('Documents'),
		KT_I18N::translate('Census'),
		KT_I18N::translate('Documents'),
		KT_I18N::translate('Documents'),
		KT_I18N::translate('Documents'),
		KT_I18N::translate('Census'),
		KT_I18N::translate('Census'),
		KT_I18N::translate('Documents'),
		KT_I18N::translate('Other'),
		KT_I18N::translate('Photos'),
		KT_I18N::translate('Photos'),
		KT_I18N::translate('Photos'),
		KT_I18N::translate('Other'),
	];

	if (empty($ALBUM_OPTIONS)) {
		$ALBUM_OPTIONS = array_combine(array_keys(KT_Gedcom_Tag::getFileFormTypes()), $default_groups);
	}

	$ged_id = get_id_from_gedcom($GEDCOM);
	$person = KT_Person::getInstance($pid);
	$ctf = 0;
	if ($level > 0) {
		$regexp = '/\n' . $level . ' OBJE @(.*)@/';
	} else {
		$regexp = '/\n\d OBJE @(.*)@/';
	}
	// -- find all of the related individuals
	$ids = [$person->getXref()];
	if ($related) {
		foreach ($person->getSpouseFamilies() as $family) {
			$ids[] = $family->getXref();
			$ctf += preg_match_all($regexp, $family->getGedcomRecord(), $match, PREG_SET_ORDER);
		}
	}
	// -- If they exist, get a list of the sorted current objects in the indi gedcom record  -  (1 _KT_OBJE_SORT @xxx@ .... etc) ----------
	$sort_current_objes = [];
	$sort_ct = preg_match_all('/\n1 _KT_OBJE_SORT @(.*)@/', $person->getGedcomRecord(), $sort_match, PREG_SET_ORDER);
	for ($i = 0; $i < $sort_ct; $i++) {
		if (!isset($sort_current_objes[$sort_match[$i][1]])) {
			$sort_current_objes[$sort_match[$i][1]] = 1;
		} else {
			$sort_current_objes[$sort_match[$i][1]]++;
		}
		$sort_obje_links[$sort_match[$i][1]][] = $sort_match[$i][0];
	}

	// create ORDER BY list from Gedcom sorted records list  ---------------------------
	$orderbylist = ' ORDER BY '; // initialize
	foreach ($sort_match as $id) {
		$orderbylist .= "m_id='{$id[1]}' DESC, ";
	}
	$orderbylist = rtrim($orderbylist, ', ');

	// -- get a list of the current objects in the record
	$current_objes = [];
	$ct = preg_match_all($regexp, $person->getGedcomRecord(), $match, PREG_SET_ORDER);
	for ($i = 0; $i < $ct; $i++) {
		if (!isset($current_objes[$match[$i][1]])) {
			$current_objes[$match[$i][1]] = 1;
		} else {
			$current_objes[$match[$i][1]]++;
		}
		$obje_links[$match[$i][1]][] = $match[$i][0];
	}

	$media_found = false;

	// Get the related media items
	$sqlmm = "
        SELECT DISTINCT m_id, m_ext, m_filename, m_titl, m_file, m_gedcom, l_from AS pid
		FROM `##media`
		JOIN `##link` ON (m_id=l_to AND m_file=l_file AND l_type='OBJE')
		WHERE m_file=? AND l_from IN (
    ";
	$i = 0;
	$vars = [KT_GED_ID];
	foreach ($ids as $media_id) {
		if ($i > 0) {
			$sqlmm .= ', ';
		}
		$sqlmm .= '?';
		$vars[] = $media_id;
		$i++;
	}
	$sqlmm .= ')';

	$tt = '';

	if ($ALBUM_GROUPS > 0) {
		// Set type of media from call in album
		for ($i = 0; $i < $ALBUM_GROUPS; $i++) {
			if ($i == $kind) {
				$tt = $ALBUM_TITLES[$i];
				$sqlmm .= ' AND (';
				foreach ($ALBUM_OPTIONS as $key => $value) {
					if ($value == $tt) {
						$sqlmm .= "m_gedcom LIKE '%TYPE " . strtolower($key) . "%' OR ";
					}
					if ($tt == KT_I18N::translate('Other')) {
						$sqlmm .= "m_gedcom NOT LIKE '%TYPE %' OR ";
					}
				}
				$sqlmm = rtrim($sqlmm, ' OR ');
				$sqlmm .= ')';
			}
		}
	}

	if ($sort_ct > 0) {
		$sqlmm .= $orderbylist;
	}

	$rows      = KT_DB::prepare($sqlmm)->execute($vars)->fetchAll(PDO::FETCH_ASSOC);
	$numm      = count($rows);
	$foundObjs = [];

	// Begin to Layout the Album Media Rows
	if ($numm > 0) {
		if (0 != $ALBUM_GROUPS) { ?>
			<div class="grid-x grid-padding-x grid-padding-y albumSection">
				<div class="cell medium-2 albumGroup">
					<label class="h5"><?php echo KT_I18N::translate($tt); ?></label>
				</div>
				<div id="thumbcontainer<?php echo $kind; ?>" class="cell medium-10  thumbGroup">
					<?php // Start pulling media items into thumbcontainer div
					foreach ($rows as $rowm) {
						if (isset($foundObjs[$rowm['m_id']])) {
							if (isset($current_objes[$rowm['m_id']])) {
								$current_objes[$rowm['m_id']]--;
							}

							continue;
						}
						$rows = [];

						// -- if there is a change to this media item then get the
						// -- updated media item and show it
						if ($newrec = find_updated_record($rowm['m_id'], $ged_id)) {
							$row = [];
							$row['m_id'] = $rowm['m_id'];
							$row['m_file'] = $ged_id;
							$row['m_filename'] = get_gedcom_value('FILE', 1, $newrec);
							$row['m_titl'] = get_gedcom_value('TITL', 1, $newrec);
							if (empty($row['m_titl'])) {
								$row['m_titl'] = get_gedcom_value('FILE:TITL', 1, $newrec);
							}
							$row['m_gedcom'] = $newrec;
							$et = preg_match('/\.(\w+)$/', $row['m_filename'], $ematch);
							$ext = '';
							if ($et > 0) {
								$ext = $ematch[1];
							}
							$row['m_ext'] = $ext;
							$row['pid'] = $pid;
							$rows['new'] = $row;
							$rows['old'] = $rowm;
						} else {
							if (!isset($current_objes[$rowm['m_id']]) && ($rowm['pid'] == $pid)) {
								$rows['old'] = $rowm;
							} else {
								$rows['normal'] = $rowm;
								if (isset($current_objes[$rowm['m_id']])) {
									$current_objes[$rowm['m_id']]--;
								}
							}
						}
						foreach ($rows as $rtype => $rowm) {
							$res = album_print_media_row($rtype, $rowm, $pid);
							$media_found = $media_found || $res;
							$foundObjs[$rowm['m_id']] = true;
						}
					} ?>
				</div>
			</div>
		<?php }
	}
}

/**
 * print a media row in a table.
 *
 * @param string $rtype whether this is a 'new', 'old', or 'normal' media row... this is used to determine if the rows should be printed with an outline color
 * @param array  $rowm  - An array with the details about this media item
 * @param string $pid   - The record id this media item was attached to
 */
function album_print_media_row($rtype, $rowm, $pid)
{
	global $sort_i, $notes;

	$media = KT_Media::getInstance($rowm['m_id']);

	if ($media && !$media->canDisplayDetails()) {
		// This media object is private;
		return false;
	}

	if (!canDisplayFact($rowm['m_id'], $rowm['m_file'], $rowm['m_gedcom'])) {
		// The link to this media object is private.  e.g. 1 OBJE/2 RESN
		return false;
	}

	//  Get the title of the media
	if ($media) {
		$mediaTitle = $media->getFullName();
	} else {
		$mediaTitle = $rowm['m_id'];
	}

	// Get media item Notes
	$noteTitle = '';
	$noteItem  = '';
	if (strpos($rowm['m_gedcom'], "\n1 NOTE")) {
		$haystack  = $rowm['m_gedcom'];
		$needle    = '1 NOTE';
		$before    = substr($haystack, 0, strpos($haystack, $needle));
		$after     = substr(strstr($haystack, $needle), strlen($needle));
		$final     = $before . $needle . $after;
		$noteTitle = KT_I18N::translate('Note');
		$noteItem  = '<dd>' . htmlspecialchars(addslashes(print_fact_notes($final, 1, true, true)), ENT_QUOTES) . '</dd>';
	}

	// Get media item Sources
	$sourceTitle  = '';
	$sourceItems  = '';
	$countSources = 0;
	if ($media->getAllFactsByType('SOUR')) {
		foreach ($media->getAllFactsByType('SOUR') as $source_fact) {
			$source = KT_Source::getInstance(trim($source_fact->detail, '@'));
			if ($source && $source->canDisplayDetails()) {
				if (!$sourceItems || $countSources == 1) {
					$sourceTitle = KT_I18N::translate('Source');
				} else {
					$sourceTitle = KT_I18N::translate('Sources');
				}

				$sourceItems .= '<dd><a class="" href="' . $source->getHtmlUrl() . '">' . $source->getFullName() . '</a></dd>';

				$countSources ++;
			}
		}
		$sourceItems .= '</dd>';
	}



	// Highlight Album Thumbnails - Changed=new (blue), Changed=old (red), Changed=no (none)

	if ($rtype) {
		switch ($rtype) {
			case 'new' : ?>
				<div class="album_new text-center shadow">
				<?php break;
			case 'old' : ?>
				<div class="album_old text-center shadow">
				<?php break;
			default : ?>
				<div class="album_norm text-center shadow">
		<?php }
	} else { ?>
		<div class="album_norm text-center shadow">
	<?php }

		if ($noteItem || $sourceItems) {
			global $iconStyle;
			$dropdownID = 'dropdownID' . (int) (microtime(true) * 1000000); ?>
			<span 
				class="info" 
				data-toggle="<?php echo $dropdownID; ?>"
			>
					<i class ="<?php echo $iconStyle; ?> fa-circle-info"></i>
			</span>
			<div 
				class="dropdown-pane shadow" 
				id="<?php echo $dropdownID; ?>"
				data-parent-class="thumbGroup"
				data-v-offset="80"
				data-hover="true" 
				data-hover-pane="true"
				data-dropdown
			>
				<dl>
					<?php if ($noteItem) { ?>
				  		<dt><?php echo $noteTitle; ?></dt>
				  		<?php echo $noteItem;
				  	}
				  	if ($sourceItems) { ?>
					  <dt><?php echo $sourceTitle; ?></dt>
					  <?php echo $sourceItems;
					 } ?>
				</dl>					
			</div>
		<?php } ?>

		<div class="pic">

			<?php // Prepare Below Thumbnail  menu ----------------------------------------------------
			$menuID    = 'menu' . (int) (microtime(true) * 1000000);
			$menuTitle = '<a href="#" class="album_media_title" data-toggle="' . $menuID . '">' . $mediaTitle . '</a>';
			$menu      = new KT_Menu();
			$menu->addLabel($menuTitle);

			if ('old' == $rtype) {
				// Do not print menu if item has changed and this is the old item
			} else {
				// Continue printing menu
				$menu->addClass('', 'submenu');

				// View Details
				$submenu = new KT_Menu(KT_I18N::translate('View Details'), KT_SERVER_NAME . KT_SCRIPT_PATH . 'mediaviewer.php?mid=' . $rowm['m_id'] . '&amp;ged=' . KT_GEDURL, 'right');
				$submenu->addClass('submenuitem');
				$menu->addSubMenu($submenu);


				if (KT_USER_CAN_EDIT) {
					// Edit Media
					$submenu = new KT_Menu(KT_I18N::translate('Edit media'), 'addmedia.php?action=editmedia&amp;pid=' . $rowm['m_id']);
					$submenu->addTarget('_blank');
					$submenu->addClass('submenuitem');
					$menu->addSubMenu($submenu);
					// Manage Links
					if (KT_USER_IS_ADMIN) {
						$submenu = new KT_Menu(KT_I18N::translate('Manage links'));
						$submenu->addOnclick("window.open('inverselink.php?mediaid=" . $rowm['m_id'] . '&linkto=manage&ged=' . KT_GEDCOM . "', '_blank', null)");
						$submenu->addClass('submenuitem');
						$menu->addSubmenu($submenu);
						// Unlink Media
						$submenu = new KT_Menu(KT_I18N::translate('Unlink Media'));
						$submenu->addOnclick("return delete_fact('{$pid}', 'OBJE', '" . $rowm['m_id'] . "', '" . KT_I18N::translate('Are you sure you want to delete this link?') . "');");
						$submenu->addClass('submenuitem');
						$menu->addSubMenu($submenu);
					}
				}
			}

			// Thumbnail Enclosure
			if ($media) {
				echo $media->displayImage();
			} ?>

		</div>

		<ul class="album vertical menu dropdown align-center" data-dropdown-menu>
			<?php echo $menu->getFoundationDropdownMenu(); ?>
		</ul>

	</div>

	<?php

}
